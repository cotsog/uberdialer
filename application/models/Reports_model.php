<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reports_model extends CI_Model
{    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        //setting the second parameter to TRUE (Boolean) the function will return the database object.
        //$this->db2 = $this->load->database('db2', TRUE);
    }

    /* Dev_NV region Start */

    function staffing_attrition($loggedUserID){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = '1=1 ';
        $customWhere .= ' AND u.user_type = "agent" ';
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (tluser.id = '.$loggedUserID.' OR tluser.parent_id = '.$loggedUserID.')';
        }
        if($logged_user_type != 'admin'){
            $customWhere .= " AND u.telemarketing_offices = '" . $logged_tm_office . "' ";
        }

             $sql =  "SELECT CONCAT(u.first_name,' ',u.last_name) AS Agents,u.tier as Tier,CONCAT(tluser.first_name,' ',tluser.last_name) AS TL,u.project as Project,u.schedule as Schedule,DATE_FORMAT(u.hired_date, '%m/%d/%Y') as 'Date Hired',u.status as Status,
        (CASE
        WHEN u.`status` = 'Released' THEN DATE_FORMAT(u.released_date, '%m/%d/%Y')
        WHEN u.`status` = 'Resigned' THEN DATE_FORMAT(u.resigned_date, '%m/%d/%Y')
		WHEN u.`status` = 'InActive' THEN DATE_FORMAT(u.inactive_date, '%m/%d/%Y')
        ELSE NULL
        END) AS Date
        FROM users u
        LEFT JOIN users tluser ON u.parent_id = tluser.id
        WHERE ".$customWhere." ";
        $query = $this->db->query($sql);
        $array = $query->result_array();
		
        return $array;
    }

    function pure_b2b_team_orders(){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $sql = " SELECT c.id,c.eg_campaign_id,c.type,c.name,c.lead_goal,
                DATE_FORMAT(c.call_filerequest_date, '%m/%d/%Y') AS call_filerequest_date,
                DATE_FORMAT(c.end_date, '%m/%d/%Y') AS end_date,
                DATE_FORMAT(c.tm_launch_date, '%m/%d/%Y') AS tm_launch_date,c.status,
                DATE_FORMAT(c.completion_date,'%m/%d/%Y') AS completion_date,
                DATE_FORMAT(c.materials_sent_to_tm_Date, '%m/%d/%Y') AS materials_sent_to_tm_Date,
                COUNT(CASE WHEN lh.status = 'Approve' THEN 1 ELSE NULL END) AS 'qa_approve_leads'
                FROM campaigns c
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                LEFT JOIN lead_history lh ON c.id = lh.campaign_id where c.business = '{$this->app}' AND c.module_type = 'tm'";
            if($logged_user_type == 'manager'){
                $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
            }
           $sql .= " GROUP BY c.id";

        $query = $this->db->query($sql);
        $array = $query->result_array();
        return $array;
    }

    function rejected_lead_summary($from_date, $to_date, $loggedUserID){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = '1=1 ';
        $customWhere .= ' AND lh.`status` = "Reject" AND DATE(lh.updated_at) BETWEEN "'.$from_date.'" AND "'.$to_date.'" ';
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$loggedUserID.' OR u.parent_id = '.$loggedUserID.')';
        }
        if($logged_user_type == 'manager'){
            $customWhere .= ' AND (u.id = '.$loggedUserID.' ';
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }

        $sql = "SELECT lh.id AS lead_id,cnt.company as company_name,c.name AS campaign_name,lh.`status`,DATE_FORMAT(lh.updated_at, '%m/%d/%Y') as Last_Updated,
                cnt.id AS contact_id,cnt.job_title, GROUP_CONCAT(DISTINCT(cdh.user_id)) AS agent_id,GROUP_CONCAT(DISTINCT(TRIM(u.first_name)) ORDER BY cdh.user_id ASC SEPARATOR ', ') AS agent_name,
                CONCAT(cnt.first_name,' ',cnt.last_name) AS prospect_name,
                GROUP_CONCAT(DISTINCT(CONCAT(TRIM(lrq.reason), IF(lrq.reason_text != '', ': ', ' '),lrq.reason_text)) SEPARATOR ', \\n') AS lead_reason,
                (select CONCAT(first_name,' ',last_name) from users where id = lh.qa order by id desc limit 1) as qa_name
                FROM lead_history lh 
                LEFT JOIN `call_disposition_history` cdh ON cdh.lead_history_id = lh.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history WHERE lead_history_id = lh.id) 
                LEFT JOIN `users` u ON cdh.user_id = u.id
                LEFT JOIN `campaigns` c ON lh.campaign_id = c.id
                LEFT JOIN `lead_reason_qa` lrq ON lh.id = lrq.lead_history_id
                LEFT JOIN `campaign_contacts` cc ON lh.campaign_contact_id = cc.id
                LEFT JOIN `contacts` cnt ON cc.contact_id = cnt.id
                WHERE " . $customWhere . " ";

           /* if ($logged_user_type != 'admin' && $logged_user_type != 'qa') {
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
            }*/
            $sql .= " GROUP BY lh.id ORDER BY DATE(lh.updated_at) DESC";
        $query = $this->db->query($sql);
        $array = $query->result_array();
        return $array;
    }

    function qa_escalation($from_date, $to_date, $loggedUserID){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = '1=1 ';
        $customWhere .= ' AND DATE(n.created_at) BETWEEN "'.$from_date.'" AND "'.$to_date.'" ';
        $customWhere .= ' AND u.user_type != "manager" AND u.user_type != "admin" ';
        $sql = " SELECT lh.id,lh.call_disposition_id,
                lh.campaign_id,c.name AS campaign_name,
                cnt.company AS company_name,cd.calldisposition_name,
                CONCAT(u.first_name,' ',u.last_name) AS agent_name,
                CONCAT(ur.first_name,' ',ur.last_name) AS qa_name,
                u.parent_id AS team_leader_id,
                (SELECT first_name FROM users WHERE id = u.parent_id LIMIT 1) AS team_leader_name,
                GROUP_CONCAT(DISTINCT(TRIM(lh.id)) ORDER BY n.id ASC SEPARATOR ', ') AS lead_history_ids,
                DATE_FORMAT(n.created_at,'%m/%d/%Y')notes_created_date,cdh.user_id,
                GROUP_CONCAT(DISTINCT(TRIM(n.note)) SEPARATOR ' <br> ') AS notes,
                GROUP_CONCAT(DISTINCT(TRIM(n.user_id)) ORDER BY n.id ASC SEPARATOR ', ') AS notes_user,
                CONCAT(cnt.first_name,' ',cnt.last_name) AS prospect_name
                FROM notes n
                LEFT JOIN `lead_history` lh ON n.lead_history_id = lh.id
                LEFT JOIN `campaigns` c ON lh.campaign_id = c.id
                LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id 
                LEFT JOIN `calldispositions` cd ON lh.call_disposition_id = cd.id 
                LEFT JOIN `call_disposition_history` cdh ON cdh.lead_history_id = lh.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history WHERE lead_history_id = lh.id) 
                LEFT JOIN `users` u ON cdh.user_id = u.id
                LEFT JOIN `users` ur ON lh.qa = ur.id
               
                LEFT JOIN `campaign_contacts` cc ON lh.campaign_contact_id = cc.id
                LEFT JOIN `contacts` cnt ON cc.contact_id = cnt.id
                WHERE " . $customWhere . " and c.business = '{$this->app}' ";
        if ($logged_user_type != 'admin' && $logged_user_type != 'qa') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .= " GROUP BY DATE(n.created_at),lh.id ORDER BY DATE(n.created_at) DESC ";
        $query = $this->db->query($sql);
        $array = $query->result_array();
        return $array;
    }
    
    public function get_more_notes($campaign_contact_id){
       $sql = "SELECT n.*,u.first_name,u.user_type FROM notes n
               LEFT JOIN users u ON n.user_id = u.id
               WHERE n.campaign_contact_id IN ? ORDER BY n.created_at ASC ";

        $query = $this->db->query($sql,array(explode(',', $campaign_contact_id)));

        $array = $query->result_array();
        return $array;
    }

    public function get_all_active_team_leaders($user_type,$logged_tm_office){
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_type','team_leader');
        $this->db->where('status','Active');
        if($user_type != 'admin' && $user_type != 'qa'){
            $this->db->where('telemarketing_offices',$logged_tm_office);
        }
        $query = $this->db->get();
        $array = $query->result();

        return $array;
    }

    public function admin_qualified_leads($from_date, $to_date, $campaign_id, $sortField="",$order=""){
        $sort = "";
        if($sortField){
            switch($sortField){
                case 'first_name':
                    $sort = 'ORDER BY first_name';
                    break;
                case 'last_name':
                    $sort = ' ORDER BY last_name';
                    break;
                case 'email':
                    $sort=' ORDER BY email';
                    break;
                case 'company':
                    $sort = 'ORDER BY company';
                case 'company_size':
                    $sort = ' ORDER BY company_size';
                    break;
                case 'qualified_status':
                    $sort = ' ORDER BY qualified_status';
                    break;
                case ($this->app_module_type == "tm") ? 'lead_id' : 'appointment_id';
                $sort = ($this->app_module_type == "tm") ? ' ORDER BY lh.id' : ' ORDER BY aps.id';
                break;
            }
        }else{
            $sort = ($this->app_module_type == "tm") ? 'ORDER BY lh.updated_at DESC' : 'ORDER BY aps.updated_at DESC';
        }
        
        $func = 'selectQualified'.ucfirst($this->app);
        $sql = $this->$func();
           
        
        
        if(!empty($from_date) && !empty($to_date)){
            $sql .= ($this->app_module_type == "tm") ? " AND DATE(lh.created_at) BETWEEN '".$from_date."' AND '".$to_date."'" : " AND DATE(aps.created_at) BETWEEN '".$from_date."' AND '".$to_date."'";
        }

        $sql .= ($this->app_module_type == "tm") ? " GROUP BY lh.id " : " GROUP BY aps.id ";
        $sql .= $sort.' '.$order;
        $query = $this->db->query($sql,array($campaign_id));
        //echo $this->db->last_query();exit;
        $array = $query->result_array();
        return $array;
    }

    function selectQualifiedEg(){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        
        $sql = " SELECT lh.id as lead_id,
                IF(m.email != '', m.email, c.email) AS email,
                IF(m.first_name != '', m.first_name, c.first_name) AS first_name,
                IF(m.last_name != '', m.last_name, c.last_name) AS last_name,
                IF(m.job_title != '', m.job_title, c.job_title) AS job_title,
                IF(m.job_level != '', m.job_level, c.job_level) AS job_level,
                c.job_function,
                IF(m.company_name != '', m.company_name, c.company) AS company,
                IF(m.address1 != '', m.address1, c.address) AS address,
                IF(m.city != '', m.city, c.city) AS city,
                IF(m.zip != '', m.zip, c.zip) AS zip,
                IF(m.state != '', m.state, c.state) AS state,
                IF(m.country != '', m.country, c.country) AS country,
                IF(m.industry != '', m.industry, c.industry) AS industry,
                IF(m.company_size  != '', m.company_size , c.company_size) AS company_size,
                IF(m.phone  != '', m.phone , c.phone) AS phone,
                IF(m.silo  != '', m.silo , '') AS silo,
                lh.resource_id,lh.resource_name,
                lh.status,
                (CASE WHEN lh.status='Approve' THEN 'Yes' ELSE 'No' END)
                AS qualified_status,
                GROUP_CONCAT(DISTINCT(CONCAT(TRIM(lrq.reason), IF(lrq.reason_text != '', ': ', ' '),lrq.reason_text)) SEPARATOR ', \\n') AS lead_reason
                FROM lead_history lh
                LEFT JOIN campaign_contacts ccnt ON lh.campaign_contact_id = ccnt.id
                LEFT JOIN `campaign_tm_offices` cto ON ccnt.campaign_id = cto.campaign_id
                LEFT JOIN contacts c ON ccnt.contact_id = c.id
                LEFT JOIN members_qa m ON m.id = c.member_id
                LEFT JOIN `lead_reason_qa` lrq ON lh.id = lrq.lead_history_id
                WHERE lh.source = 'telemarketing' AND lh.campaign_id = ? AND (lh.status='Approve' || lh.status='Reject') ";

        if($logged_user_type != 'admin' && $logged_user_type != 'qa'){
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        return $sql;
    }
    
    function selectQualifiedMpg(){
        return " SELECT lh.id as lead_id,
                IF(m.email != '', m.email, c.email) AS email,
                IF(m.first_name != '', m.first_name, c.first_name) AS first_name,
                IF(m.last_name != '', m.last_name, c.last_name) AS last_name,
                IF(m.job_title != '', m.job_title, c.job_title) AS job_title,
                IF(m.job_level != '', m.job_level, c.job_level) AS job_level,
                IF(m.company_name != '', m.company_name, c.company) AS company,
                IF(m.address1 != '', m.address1, c.address1) AS address,
                IF(m.city != '', m.city, c.city) AS city,
                IF(m.zip != '', m.zip, c.zip) AS zip,
                IF(m.state != '', m.state, c.state) AS state,
                IF(m.country != '', m.country, c.country) AS country,
                IF(m.company_size  != '', m.company_size, c.employee_size) AS company_size,
                IF(m.phone  != '', m.phone , c.phone) AS phone,
                IF(m.silo  != '', m.silo , '') AS silo,
                lh.resource_id,lh.resource_name,
                lh.status,
                (CASE WHEN lh.status='Approve' THEN 'Yes' ELSE 'No' END)
                AS qualified_status,
                GROUP_CONCAT(DISTINCT(CONCAT(TRIM(lrq.reason), IF(lrq.reason_text != '', ': ', ' '),lrq.reason_text)) SEPARATOR ', \\n') AS lead_reason
                FROM lead_history lh
                LEFT JOIN campaign_contacts ccnt ON lh.campaign_contact_id = ccnt.id
                LEFT JOIN contacts_mpg c ON ccnt.contact_id = c.id
                LEFT JOIN members_qa m ON m.id = c.member_id
                LEFT JOIN `lead_reason_qa` lrq ON lh.id = lrq.lead_history_id
                WHERE lh.source = 'telemarketing' AND lh.campaign_id = ? AND (lh.status='Approve' || lh.status='Reject') ";
    }
    
    public function disposition_report($IsNumRecord = 0, $searchBy = "", $limit = "", $offset = "", $sortField = "", $order = "", $report = 0, $is_status_approve = 0, $export=false)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        if ($sortField) {
            switch ($sortField) {
                case 'email':
                    $sort = ' ORDER BY cnt.email';
                    break;
                case 'calldisposition_name':
                    $sort = ' ORDER BY cld.calldisposition_name';
                    break;
                case 'phone':
                    $sort = ' ORDER BY number_dialed';
                    break;
                case 'first_name':
                    $sort = ' ORDER BY cnt.first_name';
                    break;
                case 'last_name':
                    $sort = ' ORDER BY cnt.last_name';
                    break;
                case 'company':
                    $sort = ' ORDER BY cnt.company';
                    break;
                case 'campaign_id':
                    $sort = 'ORDER BY c.eg_campaign_id';
                    break;
                case 'campaign':
                    $sort = 'ORDER BY c.name';
                    break;
                case 'date':
                    $sort = 'ORDER BY cdh.created_at';
                    break;
            }
        } else {
            $sort = ' ORDER BY cdh.created_at DESC';
        }

        $customWhere = '1=1 ';
        if (!empty($searchBy)) {
            if (!empty($searchBy['email'])) {
                $customWhere .= ' AND cnt.email LIKE "%' . $searchBy['email'] . '%"';
            }
            if (!empty($searchBy['calldisposition_name'])) {
                $call_dispositions = implode(",", $searchBy['calldisposition_name']);
                $customWhere .= " AND cdh.call_disposition_id in ({$call_dispositions})";
            }
            if (!empty($searchBy['phone'])) {
                $customWhere .= 'AND (number_dialed LIKE "%' . $searchBy['phone'] . '%" || cnt.phone LIKE "%' . $searchBy['phone'] . '%" )';
                //$customWhere .= 'AND (ch.number_dialed LIKE "%' . $searchBy['phone'] . '%" OR cnt.phone LIKE "%' . $searchBy['phone'] . '%")';
            }
            if (!empty($searchBy['first_name'])) {
                $customWhere .= ' AND cnt.first_name LIKE "%' . $searchBy['first_name'] . '%"';
            }
            if (!empty($searchBy['last_name'])) {
                $customWhere .= ' AND cnt.last_name LIKE "%' . $searchBy['last_name'] . '%"';
            }
            if (!empty($searchBy['company'])) {
                $customWhere .= ' AND cnt.company LIKE "%' . $searchBy['company'] . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND lh.campaign_id in ({$campaigns})";
            }
            if (!empty($searchBy['site'])) {
                $customWhere .= " AND u.telemarketing_offices = '{$searchBy['site']}'";
            }
            //Date Validation

            if(!empty($searchBy['from_date']) && empty($searchBy['to_date'])){
                $customWhere .= ' AND ( date_format(cdh.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
            }else if(!empty($searchBy['to_date']) && empty($searchBy['from_date'])){
                $customWhere .= ' AND ( date_format(cdh.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';
            }else if (!empty($searchBy['from_date']) && !empty($searchBy['to_date'])) {
                if (!empty($searchBy['from_date'])) {
                    $from_date = date('Y-m-d', strtotime($searchBy['from_date']));
                } else {
                    $from_date = '0000-00-00';
                }
                if (!empty($searchBy['to_date'])) {
                    $to_date = date('Y-m-d', strtotime($searchBy['to_date']));
                } else {
                    $to_date = '0000-00-00';
                }
                $customWhere .= ' AND date_format(cdh.created_at,"%Y-%m-%d") BETWEEN "' . $from_date . '" AND "' . $to_date . '" ';
            }else{
                $customWhere .= ' AND date_format(cdh.created_at,"%Y-%m-%d") = CURDATE()';
            }

            if (!empty($searchBy['dialer'])) {
                $customWhere .= ' AND (u.first_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR u.last_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR CONCAT(u.first_name, " ", u.last_name) like "%' . addslashes(trim($searchBy['dialer'])) . '%") ';
            }
        }
        if($logged_user_type == 'manager'){
            $customWhere .= " AND (u.id = '".$loggedUserID."' ";
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }
        if ($logged_user_type == 'team_leader') {
            $customWhere .= ' AND (u.id = ' . $loggedUserID . ' OR u.parent_id = ' . $loggedUserID . ')';
            $customWhere .= " AND u.telemarketing_offices = '{$logged_tm_office}'";
        }
        if($export){
            $select = "cnt.email as `Email`,cld.calldisposition_name as `Disposition`,IF(ch.number_dialed != '', ch.number_dialed, cnt.phone) AS `Phone`,
                        cnt.first_name as `First Name`,cnt.last_name as `Last Name`,cnt.company as `Company Name`,c.eg_campaign_id as `ID`,c.name AS `Campaign`,
                        cdh.created_at as `Date`,CONCAT(u.first_name,' ', u.last_name) AS `Dialer`";
        }else{
            $select = "cdh.lead_history_id,cdh.call_disposition_id,cmpcnt.campaign_id,cmpcnt.id,cld.calldisposition_name,
                    cnt.id AS contact_id,cnt.first_name,cnt.last_name,cnt.email,cnt.company,IF(ch.number_dialed != '', ch.number_dialed, cnt.phone) AS number_dialed,cdh.created_at,cdh.user_id,
                    CONCAT(u.first_name,' ', u.last_name) AS dialer,ch.id AS call_history_id,c.eg_campaign_id as campaign_id, c.name AS campaign_name";
        }
        if($IsNumRecord){
            $select = "count(distinct ch.id) as count";
        }
        $disposition_report_sql =
                    "SELECT {$select}
                    FROM `campaign_contacts` cmpcnt
                    JOIN `call_disposition_history` cdh ON cdh.campaign_contact_id = cmpcnt.id
                    JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id
                    LEFT JOIN `campaign_tm_offices` cto ON cmpcnt.campaign_id = cto.campaign_id
                    JOIN contacts cnt ON cmpcnt.contact_id = cnt.id
                    JOIN `campaigns` c ON c.id = cmpcnt.campaign_id
                    LEFT JOIN users u ON cdh.user_id = u.id AND u.`status` = 'Active'
                    JOIN `call_history` ch ON ch.id = cdh.call_history_id and ch.user_id = u.id
                    WHERE   " . $customWhere;
        if(empty($IsNumRecord)){
            $disposition_report_sql .= " GROUP BY ch.id ";
        }

        if ($IsNumRecord) {
            $query = $this->db->query($disposition_report_sql);
            $result = $query->result_array();
            return $result[0]['count'];
        }

        $disposition_report_sql .= $sort . ' ' . $order;

        if ($report) {
	   $disposition_report_sql .= " LIMIT ?,?";
           
            $query = $this->db->query($disposition_report_sql, array($limit, $offset));
            return $query->result_array();
        } else {
            if(!empty($limit)){
                if(empty($offset)){
                    $offset = 0;
                }
                $disposition_report_sql .= " LIMIT ? OFFSET ?";
                $query = $this->db->query($disposition_report_sql, array($limit, $offset));
            }else{
                $query = $this->db->query($disposition_report_sql);
            }
            if($export){
                return $query->result_array();
            }else{
                return $query->result();
            }
            
        }
    }

    /* Dev_NV region END */

     /* Dev_KR region Start */
    
     public function call_file_analysis($campaignID,$from_date = null, $to_date=null) {

        $sql = "SELECT DATE(chd.cdh_updated_at) AS lead_dates, chd.human_answer, chd.contact_rate, ";
        $sql .= "COALESCE(SUM(lh.status = 'Approve'), 0) AS lead_conversion , chd.dials, c.time_zone ";
        $sql .= "FROM `campaign_contacts` cc ";
        $sql .= "LEFT JOIN `lead_history` lh ON  lh.campaign_contact_id = cc.id AND DATE(lh.updated_at) BETWEEN '".$from_date."' AND '".$to_date."'";
        $sql .= "LEFT JOIN `contacts` c ON  cc.`contact_id` = c.id ";
        $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT ch.campaign_id,ch.created_at, cdh.created_at cdh_updated_at,";
        $sql .= "COALESCE(SUM(ch.`count_flag` = 1),0) AS dials, ";
        $sql .= "COALESCE(SUM(cdh.call_disposition_id IN (1,2,3,6,10,14,15,16,17,18,19,35)), 0) AS human_answer, ";
        $sql .= "COALESCE(SUM(cdh.call_disposition_id IN (1,2,6,14,15,18,19)), 0) AS contact_rate,c.time_zone ";
        $sql .= "FROM call_history ch ";
        $sql .= "JOIN `call_disposition_history` cdh  ON cdh.`call_history_id` = ch.id ";
        $sql .= "LEFT JOIN `contacts` c ON ch.`contact_id` = c.id ";
        $sql .= "WHERE DATE(ch.created_at) BETWEEN '".$from_date."' AND '".$to_date."'  AND ch.campaign_id=".$campaignID . " ";
        $sql .= "GROUP BY DATE(ch.created_at), c.time_zone ";
        $sql .= ") chd ON chd.campaign_id=cc.campaign_id AND DATE(chd.created_at) = DATE(chd.cdh_updated_at) AND chd.time_zone=c.time_zone ";
        $sql .= "WHERE "; 
        $sql .= "c.time_zone IN ('EST','CST','MST','PST') ";
        $sql .= "AND cc.campaign_id = " . $campaignID . " ";
        $sql .= "AND (DATE(chd.cdh_updated_at) BETWEEN '".$from_date."' AND '".$to_date."' OR DATE(lh.updated_at) BETWEEN '".$from_date."' AND '".$to_date."' ) ";
        $sql .= "GROUP BY DATE(chd.cdh_updated_at), c.time_zone ORDER BY DATE(chd.cdh_updated_at), FIELD (c.time_zone,'EST','CST','MST','PST') ";

        $query = $this->db->query($sql);
        $array = $query->result_array();
        return $array;
        
    }
    
    public function get_agent_status($from_date = null, $to_date=null,$userID){
        $call_disposition_history = "call_disposition_history";
        /*$sql = "SELECT cd.calldisposition_name,
                COUNT(ch.id) AS TotalDials ,
                SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND,ch.call_start_datetime,ch.call_end_datetime))) AS TotalTime
                FROM calldispositions cd
                LEFT JOIN $call_disposition_history cdh ON cd.id = cdh.call_disposition_id
                LEFT JOIN `call_history` ch ON ch.id = cdh.call_history_id  AND ch.`count_flag` = 1 AND cdh.user_id = ch.user_id  
                AND DATE(ch.created_at) BETWEEN ? AND ? ";
        if($userID){
            $sql .= " AND ch.user_id IN ($userID) ";
        }

        $query = $this->db->query($sql,array($from_date,$to_date));

        $array = $query->result_array();

        return $array;*/
        
        //get all dispositions first
        $select_dispositions_sql = "select id,calldisposition_name from calldispositions where module like '%tm%'";
        $dispo_query = $this->db->query($select_dispositions_sql);
        $dispo_array = $dispo_query->result_array();
        $calldispositions = array_column($dispo_array, "id");
        $dispo_data = array();
        foreach ($dispo_array as $key => $value) {
            $dispo_data[$value['id']] = array(
                //'id' => $value['id'],
                'calldisposition_name'=> $value['calldisposition_name'],
                'TotalDials'=>0,
                'TotalTime'=>null);
        }
        $from_date = $from_date . " 00:00:00";
        $to_date = $to_date . " 23:59:59";
        $sql = "SELECT cdh.call_disposition_id,
                COUNT(distinct ch.id) AS TotalDials ,
                SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND,ch.call_start_datetime,ch.call_end_datetime))) AS TotalTime 
                FROM call_history ch 
                JOIN {$call_disposition_history} cdh ON ch.id = cdh.call_history_id WHERE cdh.user_id = ch.user_id  
                AND cdh.created_at >= ? AND cdh.created_at <= ?  ";
        if($userID){
            $sql .= " AND ch.user_id IN ($userID) ";
        }
        $sql .= " GROUP BY cdh.call_disposition_id";

        $query = $this->db->query($sql,array($from_date,$to_date));

        $array = $query->result_array();

        if(!empty($array)){
            foreach($array as $k => $val){
                if (in_array($val['call_disposition_id'], $calldispositions)) {
                    $dispo_data[$val['call_disposition_id']] = array(
                        //'id' => $val['call_disposition_id'],
                        'calldisposition_name' => $dispo_data[$val['call_disposition_id']]['calldisposition_name'],
                        'TotalDials'=>$val['TotalDials'],
                        'TotalTime'=>$val['TotalTime']);
                }
            }
        }

        return $dispo_data;   
    }
    
    public function get_loginout_sesion_recods($from_date = null, $to_date=null,$userID){
        $sql = "SELECT DATE_FORMAT(ags.session_start, '%m/%d/%Y') AS `Date`, 
                c.name as `Campaign Name`,
                DATE_FORMAT(ags.session_start, '%h:%i:%s') AS `LogIn`,
                (IF(is_session_deactive = 1, DATE_FORMAT(ags.session_end, '%h:%i:%s'),NULL)) AS `LogOut`,
               (IF(is_session_deactive = 1, SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND,ags.session_start,ags.session_end))),NULL)) AS `Total Spent Time` 
                FROM agent_sessions ags LEFT JOIN campaigns  c ON c.id = ags.campaign_id 
                WHERE ags.user_id =? AND   DATE(ags.session_start) BETWEEN ? AND ? 
                GROUP BY ags.id";
        $query = $this->db->query($sql,array($userID,$from_date,$to_date));
       
        $array = $query->result_array();
        return $array;      
    }
    /*
     * Get QA wise Approve / Reject / Follow-up lead count 
     */
    public function get_qa_leads($date){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        $customWhere = ' 1=1 ';
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$loggedUserID.' OR u.parent_id = '.$loggedUserID.')';
        }
        if($logged_user_type == 'manager'){
           //$customWhere .= ' AND u.id = '.$loggedUserID.' ';
        }
        $customWhere .= " AND u.user_type != 'agent' ";

        $sql = "SELECT 
                COALESCE(SUM(ls.status = 'Approve'),0) AS approved_leads ,
                COALESCE(SUM(ls.status = 'Reject'), 0) AS Reject_leads ,
                COALESCE(SUM(ls.status = 'Follow-up'), 0) AS Followup_leads,
                COALESCE(SUM(ls.status = 'Duplicate Lead'), 0) AS Duplicate_leads,
                u.id,
                u.first_name
                FROM  lead_history ls
                LEFT JOIN users u  ON u.id = ls.qa AND (ls.status = 'Approve' OR ls.status = 'Reject' OR ls.status = 'Follow-up' OR ls.status = 'Duplicate Lead')
                WHERE $customWhere ";
        $sql .= " and DATE(ls.created_at) = ?";
        if($logged_user_type == 'manager'){
            $sql .= " AND u.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $sql .= " GROUP BY u.id order by  u.first_name ";
        $query = $this->db->query($sql,array($date));
        $array = $query->result_array();
        return $array;      
    }
    
    function get_campaign_qa_leads($date){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        $customWhere = ' 1=1 ';
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (um.id = '.$loggedUserID.' OR um.parent_id = '.$loggedUserID.')';
        }
        if($logged_user_type == 'manager'){
            //$customWhere .= ' AND u.id = '.$loggedUserID.' ';
        }
        $customWhere .= " AND um.user_type != 'agent' ";
        $sql = " SELECT 
                c.name,c.type ,c.id AS campaign_id,
                COALESCE(SUM(ls.status = 'Approve' OR ls.status = 'Reject' OR ls.status = 'Follow-up' OR ls.status = 'Duplicate Lead'), 0) AS leads,
                um.id,
                um.first_name 
                FROM `lead_history` ls
                LEFT JOIN users um ON ls.qa = um.id 
                LEFT JOIN campaigns c ON c.id = ls.campaign_id
                WHERE $customWhere ";
        $sql .= " and DATE(ls.created_at) = ?";
        if($logged_user_type == 'manager'){
            $sql .= " AND um.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $sql .= " GROUP BY um.id,c.id ORDER BY c.type,um.first_name";
        //echo $sql."__".$date;
        $query = $this->db->query($sql,array($date));
    
        $array = $query->result_array();
        return $array;      
    }
    
    /* Dev_KR Region End */
    
    function get_agent_calls_by_date($loggedInUserType, $logged_tm_office,$from_date,$to_date,$group_by='',$time_range=''){
        $additional_filter = "";
        if($loggedInUserType == 'manager' || $loggedInUserType == 'team_leader'){
            $additional_filter .= " and u.telemarketing_offices = '{$logged_tm_office}'";
        }
        $sql = "SELECT mr.user_id";
        if($loggedInUserType == 'admin'){
            $sql .= ",u.telemarketing_offices AS office";
        }
        if($time_range == '') {
            $sql .= ",mr.call_date";
        } elseif($time_range == 'weekly') {
            $sql .= ",WEEKOFYEAR(mr.call_date) AS `week_of_year`";
        } elseif($time_range == 'monthly') {
            $sql .= ",DATE_FORMAT(mr.call_date, '%Y-%m') AS `call_month`";
        }
        $sql .= ",CONCAT(u.first_name, ' ', u.last_name) AS agent";
        if($time_range == '') {
            $sql .= ",mr.total_call_duration";
        } elseif($time_range == 'weekly') {
            $sql .= ",SEC_TO_TIME(SUM(TIME_TO_SEC(mr.total_call_duration))) AS `total_call_duration`";
        } elseif($time_range == 'monthly') {
            $sql .= ",SEC_TO_TIME(SUM(TIME_TO_SEC(mr.total_call_duration))) AS `total_call_duration`";
        }
        $sql .= ",mr.eg_campaign_id AS campaign_id,c.name AS campaign_name";
        if($time_range == '') {
            $sql .= ",mr.total_count_calls ";
        } elseif($time_range == 'weekly') {
            $sql .= ",SUM(mr.total_count_calls) AS `total_count_calls` ";
        } elseif($time_range == 'monthly') {
            $sql .= ",SUM(mr.total_count_calls) AS `total_count_calls` ";
        }
        $sql .= "FROM monitoring_report mr JOIN users u ON mr.user_id = u.id JOIN campaigns c ON c.eg_campaign_id=mr.eg_campaign_id 
            WHERE mr.call_date BETWEEN '{$from_date}' AND '{$to_date}' {$additional_filter} {$group_by}";
        $query = $this->db->query($sql);
        $array = $query->result_array();
        
        return $array;
    }


    public function get_call_file_status($campaign_id){
        $sql = "SELECT count(distinct lh.id) as `count`, cld.calldisposition_name as `name`, cld.is_workable
                FROM `lead_history` lh
                    JOIN `calldispositions` cld ON lh.call_disposition_id = cld.id
                    JOIN `campaign_contacts` cmpcnt ON cmpcnt.id = lh.campaign_contact_id
                    JOIN `call_disposition_history` cdh ON cdh.lead_history_id = lh.id 
                WHERE lh.campaign_id=?
                GROUP BY cld.calldisposition_name
                ORDER BY cld.is_workable DESC, cld.id";

        $query = $this->db->query($sql,array($campaign_id));
        $array = $query->result_array();
        
        return $array;         
    }

    public function get_created_contacts(){
        $contacts_table = "contacts";
        $campaign_contacts_table = "campaign_contacts";
        $module_type = $this->app_module_type;
        $sql = "SELECT 
                camp.id,
                camp.eg_campaign_id as 'Campaign ID',camp.name as 'Campaign Name',
                count(case when cc.source ='form' then c.id else null end) as 'Total numbers of contact/s added manually',
                count(case when cc.source ='add_diff' then c.id else null end) as 'Total numbers of contact/s added as a different person'
                FROM
                $campaign_contacts_table cc
                    JOIN
                $contacts_table c ON cc.contact_id = c.id
                    JOIN
                campaigns camp ON camp.id = cc.campaign_id
                where cc.source in ('form','add_diff')
                AND camp.module_type = '$module_type'
                Group by camp.id";
        $query = $this->db->query($sql);
        return $query->result_array();        
    }

    public function get_created_contacts_by_campaign($campaign_id){
        $contacts_table = "contacts";
        $campaign_contacts_table = "campaign_contacts";
        $module_type = $this->app_module_type;
        $where_condition = ($this->app_module_type == "tm") ? "camp.eg_campaign_id = '$campaign_id'" : "camp.id = '$campaign_id'";
        $sql = "SELECT 
                camp.eg_campaign_id as 'Campaign ID',camp.name as 'Campaign Name',
                c.email as 'Email',
                cc.created_at as 'Created Date',
                (select concat(first_name,' ', last_name) from users where id = cc.created_by) as 'Creator',
                (case when cc.source = 'add_diff' then 'added as a different person' else 'manually added' end) as 'Source'
            FROM
                $campaign_contacts_table cc
                    JOIN
                $contacts_table c ON cc.contact_id = c.id
                    JOIN
                campaigns camp ON camp.id = cc.campaign_id
                where cc.source in ('form','add_diff')  AND camp.module_type = '$module_type'
                and $where_condition
                ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function uploadsummary_report($IsNumRecord = 0, $searchBy = "", $limit = "", $offset = "", $sortField = "", $order = "", $report = 0, $export=false)
    {
        if ($sortField) {
            switch ($sortField) {
                case 'created_at':
                    $sort = ' ORDER BY ch.created_at';
                    break;
                case 'campaign':
                    $sort = ' ORDER BY c.id';
                    break;
                case 'list':
                    $sort = ' ORDER BY cl.list_name';
                    break;
                case 'ct_uploaded':
                    $sort = ' ORDER BY ch.ct_uploaded';
                    break;
                case 'ct_dupes':
                    $sort = ' ORDER BY ch.ct_dupes';
                    break;
                default:
                    $sort = ' ORDER BY ch.created_at';
                    break;
            }
        } else {
            $sort = ' ORDER BY ch.created_at DESC';
        }

        $customWhere = '1 = 1 ';
        if (!empty($searchBy)) {
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND c.id in ({$campaigns})";
            }
           
            //Date Validation

            if(!empty($searchBy['from_date']) && empty($searchBy['to_date'])){
                $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
            }else if(!empty($searchBy['to_date']) && empty($searchBy['from_date'])){
                $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';
            }else if (!empty($searchBy['from_date']) && !empty($searchBy['to_date'])) {
                if (!empty($searchBy['from_date'])) {
                    $from_date = date('Y-m-d', strtotime($searchBy['from_date']));
                } else {
                    $from_date = '0000-00-00';
                }
                if (!empty($searchBy['to_date'])) {
                    $to_date = date('Y-m-d', strtotime($searchBy['to_date']));
                } else {
                    $to_date = '0000-00-00';
                }
                $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") BETWEEN "' . $from_date . '" AND "' . $to_date . '" ';
            }else{
                $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") = CURDATE()';
            }

        }
        
        if($export){
            $field= "ch.created_at `Date`,c.id as `Campaign ID`,ch.list_name as `List`,ch.ct_uploaded as `No_of_Uploaded`,ch.ct_dupes as `No_of_Dupes`";
        }else{
            $field= "c.id as cid,ch.id as chid,ch.created_at,c.name,ch.list_name,ch.ct_uploaded,ch.ct_dupes";
        }
        
                
        if($IsNumRecord){
            $field = "count(ch.id) as count";
        }
        $sql ="SELECT {$field}
            FROM `campaign_list_dupes_history` ch
            JOIN `campaigns` c ON c.id = ch.campaign_id
            WHERE   " . $customWhere;
        
        if ($IsNumRecord) {
            $query = $this->db->query($sql);
            $result = $query->result_array();
            return $result[0]['count'];
        }

        $sql .= $sort . ' ' . $order;

        if ($report) {
            $query = $this->db->query($sql);
            return $query->result_array();
        } else {
            if(!empty($limit)){
                if(empty($offset)){
                    $offset = 0;
                }
                $sql .= " LIMIT ? OFFSET ?";
                $query = $this->db->query($sql, array($limit, $offset));
            }else{
                $query = $this->db->query($sql);
            }
            if($export){
                return $query->result_array();
            }else{
                return $query->result();
            }
            
        }
    }
    
    public function dupes_report($IsNumRecord = 0, $searchBy, $limit = "", $offset = "", $sortField = "", $order = "", $report = 0, $export=false)
    {
        if ($sortField) {
            switch ($sortField) {
                case 'email':
                    $sort = ' ORDER BY c.email';
                    break;
                case 'first_name':
                    $sort = ' ORDER BY c.first_name';
                    break;
                case 'last_name':
                    $sort = ' ORDER BY c.last_name';
                    break;
                case 'list_name':
                    $sort = ' ORDER BY cl.list_name';
                    break;
                default:
                    $sort = ' ORDER BY c.email';
                    break;
            }
        } else {
            $sort = ' ORDER BY c.email DESC';
        }
        
         if($export){
            $field= "c.email as `Email`,c.first_name as `First Name`,c.last_name as `Last Name`,cl.list_name as `List`";
        }else{
            $field= "cl.list_name as dupes_list_name,d.list_name,c.first_name,c.last_name,c.email";
        }
        
        if($IsNumRecord){
            $field = "count(d.id) as count";
        }
        $sql ="SELECT {$field}
            FROM campaign_lists_dupes d
            JOIN contacts  c ON d.contact_id = c.id
            JOIN campaign_lists cl ON cl.id = d.dupes_list_id
            WHERE d.campaign_list_dupes_history_id=" . $searchBy['list_history_id'];
        
        if ($IsNumRecord) {
            $query = $this->db->query($sql);
            $result = $query->result_array();
            return $result[0]['count'];
        }

        $sql .= $sort . ' ' . $order;

        if ($report) {
            $query = $this->db->query($sql);
            return $query->result_array();
        } else {
            if(!empty($limit)){
                if(empty($offset)){
                    $offset = 0;
                }
                $sql .= " LIMIT ? OFFSET ?";
                $query = $this->db->query($sql, array($limit, $offset));
            }else{
                $query = $this->db->query($sql);
            }
            if($export){
                return $query->result_array();
            }else{
                return $query->result();
            }
            
        }
    }
    
    public function get_call_file_status_per_agent($campaign_id,$startDate,$endDate){
        $sql = "SELECT CONCAT( u.first_name, ' ', u.last_name ) as agent, cdh.user_id, cdh.call_disposition_id,cld.calldisposition_name as `name`, count(cmpcnt.id) as `count`, cld.is_workable ";
        $sql .= "FROM `campaign_contacts` cmpcnt ";
        $sql .= "JOIN `call_disposition_history` cdh ON cdh.campaign_contact_id = cmpcnt.id ";
        $sql .= "JOIN `users` u ON cdh.user_id = u.id ";
        $sql .= "JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id ";
        $sql .= "JOIN `call_history` ch ON ch.id = cdh.call_history_id ";
        $sql .= "WHERE ch.module_type='tm' AND cmpcnt.campaign_id=? and cdh.created_at BETWEEN '" . $startDate . "' AND '". $endDate . "  11:59:59' ";
        $sql .= "GROUP BY cdh.user_id, cdh.call_disposition_id ";
        $sql .= "ORDER BY cld.is_workable DESC, cld.id";

        $query = $this->db->query($sql,array($campaign_id));
        $array = $query->result_array();
        
        return $array;         
    }
    
    public function get_counts_per_owner( $campaign_id = '', $startDate = '', $endDate = '' ){
        $sql = "SELECT c.original_owner as `name`,cdh.call_disposition_id,cld.calldisposition_name,cdh.user_id, count(cmpcnt.id) as count, cld.is_workable ";
        $sql .= "FROM `campaign_contacts` cmpcnt ";
        $sql .= "LEFT JOIN `contacts` c ON c.id = cmpcnt.contact_id ";
        $sql .= "JOIN `call_disposition_history` cdh ON cdh.campaign_contact_id = cmpcnt.id ";
        $sql .= "JOIN `users` u ON cdh.user_id = u.id ";
        $sql .= "JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id ";
        $sql .= "JOIN `call_history` ch ON ch.id = cdh.call_history_id ";
        $sql .= "WHERE ch.module_type='tm' AND  cmpcnt.campaign_id=? and cdh.created_at BETWEEN '" . $startDate . "' AND '". $endDate . " 11:59:59' ";
        $sql .= "GROUP BY c.original_owner,cld.is_workable,cdh.call_disposition_id";
        
        $query = $this->db->query( $sql, array($campaign_id) );
        
        $array = $query->result_array();
        
        return $array;         
    }
    
    public function getCampaignsWDispo($searchBy) {
        if(!empty($searchBy['from_date']) && empty($searchBy['to_date'])){
            $customWhere = '( date_format(cdh.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
        }else if(!empty($searchBy['to_date']) && empty($searchBy['from_date'])){
            $customWhere = '( date_format(cdh.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';
        }else if (!empty($searchBy['from_date']) && !empty($searchBy['to_date'])) {
            if (!empty($searchBy['from_date'])) {
                $from_date = date('Y-m-d', strtotime($searchBy['from_date']));
            } else {
                $from_date = '0000-00-00';
            }
            if (!empty($searchBy['to_date'])) {
                $to_date = date('Y-m-d', strtotime($searchBy['to_date']));
            } else {
                $to_date = '0000-00-00';
            }
            $customWhere = 'date_format(cdh.created_at,"%Y-%m-%d") BETWEEN "' . $from_date . '" AND "' . $to_date . '" ';
        }else{
            $customWhere = 'date_format(cdh.created_at,"%Y-%m-%d") = CURDATE()';
        }
        
        if (!empty($searchBy['calldisposition_name'])) {
            $call_dispositions = implode(",", $searchBy['calldisposition_name']);
            $customWhere .= " AND cdh.call_disposition_id in ({$call_dispositions})";
        }
        
        $sql = "SELECT DISTINCT campaign_id FROM campaign_contacts cc ";
        $sql .= "JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cc.id ";
        $sql .= "WHERE " . $customWhere;
        
        $query = $this->db->query($sql);
        $res = $query->result_array();
        $campaigns = array_column($res, 'campaign_id');
      
        return $campaigns;
    }
    
    public function dispositionReport($searchBy = "", $limit = "", $offset = "", $sortField = "", $order = "", $export=false) {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        if ($sortField) {
            switch ($sortField) {
                case 'calldisposition_name':
                    $sort = ' ORDER BY cld.calldisposition_name';
                    break;
                case 'campaign_id':
                    $sort = 'ORDER BY c.eg_campaign_id';
                    break;
                case 'campaign':
                    $sort = 'ORDER BY c.name';
                    break;
                case 'date':
                    $sort = 'ORDER BY cdh.created_at';
                    break;
            }
        } else {
            $sort = ' ORDER BY cdh.created_at DESC';
        }

        $customWhere = '1=1 ';
        if (!empty($searchBy)) {
            if (!empty($searchBy['calldisposition_name'])) {
                $call_dispositions = implode(",", $searchBy['calldisposition_name']);
                $customWhere .= " AND cdh.call_disposition_id in ({$call_dispositions})";
            }
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND c.id in ({$campaigns})";
            }
            //Date Validation
            $dtRange = '1 ';
            if(!empty($searchBy['from_date']) && empty($searchBy['to_date'])){
               // $customWhere .= ' AND ( date_format(cdh.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
                $dtRange .= 'AND ( date_format(subcdh.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
            }else if(!empty($searchBy['to_date']) && empty($searchBy['from_date'])){
              //  $customWhere .= ' AND ( date_format(cdh.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';
                $dtRange .= 'AND ( date_format(subcdh.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';;
            }else if (!empty($searchBy['from_date']) && !empty($searchBy['to_date'])) {
                if (!empty($searchBy['from_date'])) {
                    $from_date = date('Y-m-d', strtotime($searchBy['from_date']));
                } else {
                    $from_date = '0000-00-00';
                }
                if (!empty($searchBy['to_date'])) {
                    $to_date = date('Y-m-d', strtotime($searchBy['to_date']));
                } else {
                    $to_date = '0000-00-00';
                }
                $fromDate = $from_date . " 00:00:00";
                $toDate = $to_date . " 23:59:59";
                $dtRange .= 'AND subcdh.created_at >= "' . $fromDate . '" AND subcdh.created_at <= "' . $toDate . '" ';
            }else{
                $dateToday = date("Y-m-d 00:00:00");
                $dtRange .= 'AND subcdh.created_at >= "' . $dateToday . '"  ';
            }

            if (!empty($searchBy['dialer'])) {
                $customWhere .= ' AND (u.first_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR u.last_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR CONCAT(u.first_name, " ", u.last_name) like "%' . addslashes(trim($searchBy['dialer'])) . '%") ';
                //$customWhere .= ' AND cdh.user_id = ' . $searchBy['dialer'] .  ' ';
            }
        }
        
        if($logged_user_type == 'manager'){
            $customWhere .= " AND (u.id = '".$loggedUserID."' ";
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "' ";
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');
            
            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR u.telemarketing_offices = '" . $sub_telemarketing_office . "'";
            }
            $customWhere .= ')';
        }
        if ($logged_user_type == 'team_leader') {
            $customWhere .= ' AND (u.id = ' . $loggedUserID . ' OR u.parent_id = ' . $loggedUserID . ')';
        }
        
        if ($export) {
            $select = "cld.calldisposition_name as `Disposition`, ";
            $select .= "c.eg_campaign_id as `ID`,c.name AS `Campaign`, ";
            $select .= "cdh.created_at as `Date`,CONCAT(u.first_name,' ', u.last_name) AS `Telemarketer` ";
        }else{
            $select = "cdh.call_disposition_id,cdh.campaign_id,cdh.campaign_contact_id,cld.calldisposition_name, ";
            $select .= "cdh.created_at,cdh.user_id, ";
            $select .= "CONCAT(u.first_name,' ', u.last_name) AS dialer,ch.id AS call_history_id,c.eg_campaign_id as campaign_id, c.name AS campaign_name";
        }
        
        $disposition_report_sql = "SELECT {$select} ";
        $disposition_report_sql .= "FROM (SELECT subcmpcnt.id campaign_contact_id, subcmpcnt.campaign_id, subcmpcnt.contact_id, subcdh.created_at, subcdh.user_id, subcdh.call_disposition_id, subcdh.call_history_id, subcdh.lead_history_id ";
        $disposition_report_sql .= "FROM `call_disposition_history` subcdh ";
        $disposition_report_sql .= "JOIN `campaign_contacts` subcmpcnt ON subcmpcnt.id = subcdh.campaign_contact_id ";
        $disposition_report_sql .= "WHERE " . $dtRange . " AND subcdh.call_history_id != 0 GROUP BY subcdh.call_disposition_id,subcdh.call_history_id ORDER BY subcdh.created_at DESC) as cdh ";
        $disposition_report_sql .= "JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id ";
        $disposition_report_sql .= "JOIN `campaigns` c ON c.id = cdh.campaign_id ";
        $disposition_report_sql .= "LEFT JOIN users u ON cdh.user_id = u.id AND u.`status` = 'Active' ";
        $disposition_report_sql .= "JOIN `call_history` ch ON ch.id = cdh.call_history_id and ch.user_id = u.id ";
        $disposition_report_sql .= "WHERE ch.module_type = 'tm' AND " .  $customWhere . " ";
        $disposition_report_sql .= $sort . ' ' . $order;
 
        if ($export) {
	    $disposition_report_sql .= " LIMIT ?,?";
            $query = $this->db->query($disposition_report_sql, array($offset, $limit));
            return $query->result_array();
        } else {
            if (!empty($limit)) {
                if (empty($offset)) {
                    $offset = 0;
                }
        
                $disposition_report_sql .= " LIMIT ? OFFSET ?";
                
                $query = $this->db->query($disposition_report_sql, array($limit, $offset));
            } else {
                $query = $this->db->query($disposition_report_sql);
            }
            
            return $query->result();
        }
    }
    
    public function dispositionReportCounts($searchBy = "") {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        
        $customWhere = '1=1 ';
        $calldispositionHistoryWhere = '';
        $contactsWhere = '';
        $callHistoryWhere = '';
        $usersWhere = '';
        if (!empty($searchBy)) {
            //contacts table dependent
            
            //call_disposition_history table dependent
            if (!empty($searchBy['calldisposition_name'])) {
                $call_dispositions = implode(",", $searchBy['calldisposition_name']);
                $calldispositionHistoryWhere .= " AND subcdh.call_disposition_id in ({$call_dispositions})";
            }
            
            //campaign_contacts table dependent
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND cdh.campaign_id in ({$campaigns})";
            }
            
            $dtRange = ' ';
            //call_disposition_history table dependent
            //Date Validation
            if(!empty($searchBy['from_date']) && empty($searchBy['to_date'])){
                $dtRange .= 'AND ( date_format(subcdh.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['from_date'])).'")';
            }else if(!empty($searchBy['to_date']) && empty($searchBy['from_date'])){
                $dtRange .= 'AND ( date_format(subcdh.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['to_date'])).'")';
            }else if (!empty($searchBy['from_date']) && !empty($searchBy['to_date'])) {
                if (!empty($searchBy['from_date'])) {
                    $from_date = date('Y-m-d', strtotime($searchBy['from_date']));
                } else {
                    $from_date = '0000-00-00';
                }
                if (!empty($searchBy['to_date'])) {
                    $to_date = date('Y-m-d', strtotime($searchBy['to_date']));
                } else {
                    $to_date = '0000-00-00';
                }
                $fromDate = $from_date . " 00:00:00";
                $toDate = $to_date . " 23:59:59";
                $dtRange .= 'AND subcdh.created_at >= "' . $fromDate . '" AND subcdh.created_at <= "' . $toDate . '" ';
            }else{
                $dateToday = date("Y-m-d 00:00:00");
                $dtRange .= 'AND subcdh.created_at >= "' . $dateToday . '"  ';
            }

            if (!empty($searchBy['dialer'])) {
                $usersWhere .= ' AND (u.first_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR u.last_name LIKE "%' . addslashes(trim($searchBy['dialer'])) . '%" OR CONCAT(u.first_name, " ", u.last_name) like "%' . addslashes(trim($searchBy['dialer'])) . '%") ';
            }
        }
        
        if ($logged_user_type == 'manager') {
            $usersWhere .= " AND (u.id = '".$loggedUserID."' ";
            $usersWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "' ";
            
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');
            
            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $usersWhere .= " OR u.telemarketing_offices = '" . $sub_telemarketing_office . "'";
            }
            
            $usersWhere .= ')';
        }
        
        if ($logged_user_type == 'team_leader') {
            $usersWhere .= ' AND (u.id = ' . $loggedUserID . ' OR u.parent_id = ' . $loggedUserID . ')';
        }
        
        $disposition_report_sql = "SELECT
                                    count(1) AS count
                            FROM
                            (
                            SELECT 
                                subcmpcnt.campaign_id,
                                subcmpcnt.contact_id,
                                subcdh.user_id,
                                subcdh.call_disposition_id,
                                subcdh.call_history_id
                            FROM
                                `call_disposition_history` subcdh
                                    JOIN
                                `campaign_contacts` subcmpcnt ON subcmpcnt.id = subcdh.campaign_contact_id
                                    {$dtRange}
                                    AND subcdh.call_history_id != 0 {$calldispositionHistoryWhere}
                            WHERE
                                 1
                            GROUP BY  subcdh.call_disposition_id , subcdh.call_history_id
                            ) AS cdh
                            WHERE 
                            {$customWhere}
                            AND Exists
                            (
                                Select 1 From call_history ch Where ch.id = cdh.call_history_id AND ch.user_id = cdh.user_id AND ch.module_type = 'tm' {$callHistoryWhere}
                            )
                            AND Exists
                            (
                                select 1 from users u where u.status = 'Active' and u.id = cdh.user_id {$usersWhere}
                            )";

        $query = $this->db->query($disposition_report_sql);
        
        $result = $query->result_array();
        
        return $result[0]['count'];
    }
}
?>
