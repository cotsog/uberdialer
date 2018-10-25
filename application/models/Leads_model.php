<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Leads_model extends CI_Model
{
    public $userTable = 'users';
    public $campaignsTable = 'campaigns';
    public $campaign_lists = 'campaign_lists';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        
    }

	 public function getLeadsStatusListCount($searchBy="")       
    {
            $campaigncontacts_table = '`campaign_contacts`';
            $contact_table = '`contacts`';
        // sorting by filters 
        $company = 'company_name';
        $member_qa_join=$agent_join=$users_join="";
        // set query as per filters
        $customWhere = " WHERE 1=1 ";
        $users_join=' LEFT JOIN users ua ON tlh.agent_id = ua.id  ';

        // table to use base on selected filter_by
        // 'tlh' (lead_history) if Time Submitted
        // 'cc' (campaign_contacts) if Last Updated
        $filterBySource = isset($searchBy["filter_by"]) && $searchBy["filter_by"] == 'created_at' ? 'tlh' : 'cc';

        if (!empty($searchBy)) 
        {
            if (!empty($searchBy['status'])) {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }
                $statuses = implode("','", $searchBy['status']);
                $customWhere .= " AND tlh.`status` in ('{$statuses}')";
            }else{
                $customWhere .= ' AND tlh.`status` = "Pending"';
            }
            if (!empty($searchBy['qa'])) {
                $qas = implode(",", $searchBy['qa']);
                $customWhere .= " AND tlh.`qa` in ({$qas})";
            }
            if (!empty($searchBy['contact_name'])) {
				$member_qa_join=' LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ';
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" ';
                $customWhere .= 'OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
				$member_qa_join=' LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ';
                $customWhere .= ' AND cnt.'.$company.' LIKE "%' . trim($searchBy['company']) . '%"';
            }
            if (!empty($searchBy['email'])) {
				$member_qa_join=' LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ';
                $customWhere .= ' AND cnt.email LIKE "%' . trim($searchBy['email']) . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND tlh.campaign_id in ({$campaigns})";
            }
            if (!empty($searchBy['telemarketer'])){
                //$agent_join=' LEFT JOIN `agent_lead` al ON al.lead_id = tlh.id AND al.id = (SELECT MAX(id) FROM agent_lead WHERE lead_id = tlh.id) ';
                $telemarketers = implode(",", $searchBy['telemarketer']);
                $customWhere .= " AND tlh.`agent_id` in ({$telemarketers})";
            }
            
            if (!empty($searchBy['sites'])) {
                $sites = implode("','", $searchBy['sites']);
                $this->load->model('Offices_model');
                $officeModel = new Offices_model();
                $subOffice = $officeModel->getSubOffices($searchBy['sites']);
                $sub_telemarketing_offices = array_column($subOffice,"name");

                $tmOffice = array();
                foreach ($searchBy['sites'] as $site) {
                    $tmOffice[] = " ua.`telemarketing_offices` = '" . $site . "'";
                }

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $tmOffice[] = " ua.`telemarketing_offices` = '" . $sub_telemarketing_office . "'";
                }

                $customWhere .= " AND (";
                $customWhere .= implode("OR", $tmOffice);
                $customWhere .= ') ';
            }
        }else{
            $customWhere .= ' AND tlh.`status` = "Pending"';
            
            if ($this->session->userdata('user_type') == 'manager') {
                $customWhere .= " AND (ua.`telemarketing_offices` = '" . $this->session->userdata('telemarketing_offices') . "' ";
                $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $customWhere .= " OR ua.`telemarketing_offices` = '" . $sub_telemarketing_office . "'";
                }
                $customWhere .= ') ';
            } 
        }
        if(!isset($searchBy['show_non_active']) || empty($searchBy['show_non_active'])){
            $customWhere .= '  AND camp.status = "active" ';
        }
        //Date Validation 
        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            $customWhere .= ' AND ( date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            $customWhere .= ' AND ( date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
        }else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
            if (!empty($searchBy['start_date'])) {
                $start_date = date('Y-m-d', strtotime($searchBy['start_date']));
            } else {
                $start_date = '0000-00-00';
            }
            if (!empty($searchBy['end_date'])) {
                $end_date = date('Y-m-d', strtotime($searchBy['end_date']));
            } else {
                $end_date = '0000-00-00';
            }
            $customWhere .= ' AND date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
        }else{

            if(!empty($searchBy['status'])) {//echo '<pre>'; print_r($statuses);
                if(count($searchBy['status']) > 1 || (count($searchBy['status']) == 1 && $searchBy['status'][0] != 'Pending')) {
                    $customWhere .= ' AND date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") = CURDATE()';
                }
            }
        }
        // user wise set conditions 
        if($this->session->userdata('user_type') == 'agent'){
			$customWhere .= ' AND tlh.agent_id = '.$this->session->userdata('uid');
			$lead_statusSQL = "SELECT COUNT(*) count FROM `lead_history` tlh JOIN 
                campaign_contacts cc on cc.id = tlh.campaign_contact_id
			LEFT JOIN `campaigns` camp ON tlh.campaign_id = camp.id
			".$member_qa_join."
            ".$users_join."
			" . $customWhere . " ";   
        }
        else if($this->session->userdata('user_type') == 'team_leader'){
            $customWhere .= ' AND (tlh.agent_id = ' . $this->session->userdata('uid') . ' OR ua.parent_id = ' . $this->session->userdata('uid') . ')';
            
			$lead_statusSQL = "SELECT COUNT(*) count FROM `lead_history` tlh
			LEFT JOIN `campaigns` camp ON tlh.campaign_id = camp.id 
			INNER JOIN $campaigncontacts_table cc ON cc.id = tlh.campaign_contact_id
			".$member_qa_join."
			LEFT JOIN users ua ON tlh.agent_id = ua.id 
			" . $customWhere . " "; 
        }
        else{
			$lead_statusSQL = "SELECT COUNT(*) count FROM `lead_history` tlh
			LEFT JOIN `campaigns` camp ON tlh.campaign_id = camp.id 
			INNER JOIN $campaigncontacts_table cc ON cc.id = tlh.campaign_contact_id
			".$member_qa_join."
            ".$agent_join."
			".$users_join."
			" . $customWhere . " "; 
		}
        //$lead_statusSQL .= " GROUP BY tlh.id ";
        // to fetch No. of Records 
      	//echo $lead_statusSQL;die();
            $query = $this->db->query($lead_statusSQL);
            $return = $query->result();
            if(!empty($return)){
                return $return[0]->count;
            }
            return 0;
    }
    public function getLeadsStatusList($searchBy="",$limit = "", $offset = "", $sortField="",$order="",$report=0,$is_status_approve=0)       
    {
            $campaigncontacts_table = '`campaign_contacts`';
            $contact_table = '`contacts`';
        // sorting by filters 
        $company = 'company_name';

        // table to use base on selected filter_by
        // 'tlh' (lead_history) if Time Submitted
        // 'cmcnt' (campaign_contacts) if Last Updated
        $filterBySource = isset($searchBy["filter_by"]) && $searchBy["filter_by"] == 'created_at' ? 'tlh' : 'cmcnt';

        // set query as per filters
        $customWhere = " WHERE 1=1 ";
        if($sortField){
            switch($sortField){
                case 'Site':
                    $sort = 'ORDER BY ua.`telemarketing_offices`';
                     break;
                case 'Id':
                    $sort = 'ORDER BY tlh.campaign_id';
                     break;
                case 'Name':
                    $sort = ' ORDER BY campaign_name';
                    break;
                case 'Type':
                    $sort=' ORDER BY campaign_Type';
                    break;
                case 'FirstName':
                    $sort = 'ORDER BY cnt.first_name';
                    break;
                case 'LastName':
                    $sort = 'ORDER BY cnt.Last_name';
                    break;
                case 'Company':
                    $sort = ' ORDER BY company';
                    break;
                case 'Email':
                    $sort = ' ORDER BY contact_email';
                    break;
                case 'Email':
                    $sort = ' ORDER BY contact_email';
                    break;
                case 'JobTitle':
                    $sort = ' ORDER BY cnt.job_title';
                    break;
                case 'Phone':
                    $sort = ' ORDER BY cnt.phone';
                    break;
                case 'LastUpdated':
                    $sort = ' ORDER BY cc.updated_at';
                    break;
                case 'Agent':
                    $sort = ' ORDER BY agent_name';
                    break;
                case 'Qa':
                    $sort = ' ORDER BY qa_name';
                    break;
                case 'Status':
                    $sort = ' ORDER BY Status';
                    break;
                case 'Date':
                     $sort = 'ORDER BY cmcnt.updated_at';
                     break;
                case 'Day':
                    $sort = 'ORDER BY tlh.day';
                    break;
            }
        }else{
            $sort = 'ORDER BY '.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at');
        }
        
        // set query as per filters
        if (!empty($searchBy)) 
        {
            if (!empty($searchBy['status'])) {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }
                $statuses = implode("','", $searchBy['status']);
                $customWhere .= " AND tlh.`status` in ('{$statuses}')";
            }else{
                $customWhere .= ' AND tlh.`status` = "Pending"';
            }
            if (!empty($searchBy['eg_campaign_id'])) {
                $customWhere .= ' AND camp.eg_campaign_id = ' . trim($searchBy['eg_campaign_id']) . ' ';
            }
            if (!empty($searchBy['qa'])) {
                $qas = implode(",", $searchBy['qa']);
                $customWhere .= " AND tlh.`qa` in ({$qas})";
            }
            if (!empty($searchBy['contact_name'])) {
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
                $customWhere .= ' AND cnt.'.$company.' LIKE "%' . trim($searchBy['company']) . '%"';
            }
            if (!empty($searchBy['email'])) {
                $customWhere .= ' AND cnt.email LIKE "%' . trim($searchBy['email']) . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $campaigns = implode(",", $searchBy['campaign']);
                $customWhere .= " AND tlh.campaign_id in ({$campaigns})";
            }
            if (!empty($searchBy['telemarketer'])){
                $telemarketers = implode(",", $searchBy['telemarketer']);
                $customWhere .= " AND tlh.`agent_id` in ({$telemarketers})";
            }
            if (!empty($searchBy['sites'])) {
                $this->load->model('Offices_model');
                $officeModel = new Offices_model();
                $subOffice = $officeModel->getSubOffices($searchBy['sites']);
                $sub_telemarketing_offices = array_column($subOffice,"name");

                $tmOffice = array();
                foreach ($searchBy['sites'] as $site) {
                    $tmOffice[] = " ua.`telemarketing_offices` = '" . $site . "'";
                }

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $tmOffice[] = " ua.`telemarketing_offices` = '" . $sub_telemarketing_office . "'";
                }

                $customWhere .= " AND (";
                $customWhere .= implode("OR", $tmOffice);
                $customWhere .= ') ';
            } else {
                if ($this->session->userdata('user_type') == 'manager') {
                    $customWhere .= " AND (ua.`telemarketing_offices` = '" . $this->session->userdata('telemarketing_offices') . "' ";
                    $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

                    foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                        $customWhere .= " OR ua.`telemarketing_offices` = '" . $sub_telemarketing_office . "'";
                    }
                    $customWhere .= ') ';
                } 
            }
        }else{
            $customWhere .= ' AND tlh.`status` = "Pending"';
            
            if ($this->session->userdata('user_type') == 'manager') {
                $customWhere .= " AND (ua.`telemarketing_offices` = '" . $this->session->userdata('telemarketing_offices') . "' ";
                $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $customWhere .= " OR ua.`telemarketing_offices` = '" . $sub_telemarketing_office . "'";
                }
                $customWhere .= ') ';
            } 
        }
        if(!isset($searchBy['show_non_active']) || empty($searchBy['show_non_active'])){
            $customWhere .= '  AND camp.status = "active" ';
        }
        //Date Validation 
        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            $customWhere .= ' AND ( date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            $customWhere .= ' AND ( date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
        }else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
            if (!empty($searchBy['start_date'])) {
                $start_date = date('Y-m-d', strtotime($searchBy['start_date']));
            } else {
                $start_date = '0000-00-00';
            }
            if (!empty($searchBy['end_date'])) {
                $end_date = date('Y-m-d', strtotime($searchBy['end_date']));
            } else {
                $end_date = '0000-00-00';
            }
            $customWhere .= ' AND date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
        }else{

            if(!empty($searchBy['status'])) {//echo '<pre>'; print_r($statuses);
                if(count($searchBy['status']) > 1 || (count($searchBy['status']) == 1 && $searchBy['status'][0] != 'Pending')) {
                    $customWhere .= ' AND date_format('.$filterBySource.'.'.(isset($searchBy["filter_by"]) ? $searchBy["filter_by"] : 'updated_at').',"%Y-%m-%d") = CURDATE()';
                }
            }
        }
        // user wise set conditions 
        if($this->session->userdata('user_type')!= 'qa'){     
			if($this->session->userdata('user_type') == 'agent')
			{
				$customWhere .= ' AND tlh.agent_id = '.$this->session->userdata('uid');
            }
            
            if ($this->session->userdata('user_type') === 'team_leader') {
                $customWhere .= ' AND (tlh.agent_id = ' . $this->session->userdata('uid') . ' OR ua.parent_id = ' . $this->session->userdata('uid') . ')';
            }

			$lead_statusSQL = "SELECT ua.`telemarketing_offices`,tlh.id as lead_id,tlh.campaign_id,tlh.member_id,tlh.campaign_contact_id,cmcnt.list_id, ";
            $lead_statusSQL .= "tlh.is_qa_in_progress,tlh.qa, ";
            $lead_statusSQL .= "(CASE WHEN ((SELECT COUNT(*) FROM `lead_reason_qa` WHERE (status ='Reject') and  lead_history_id = tlh.id ) >0)  THEN 'Reason' ELSE '-' END) AS rejection_reasons, ";
            $lead_statusSQL .= "(CASE WHEN ((SELECT COUNT(*) FROM `lead_reason_qa` WHERE (status = 'Follow-up') and  lead_history_id = tlh.id ) >0)  THEN 'Reason' ELSE '-' END) AS followup_reasons, ";
            $lead_statusSQL .= "tlh.disapprove_reason as rejection_reason,camp.eg_campaign_id AS eg_campaign_id,camp.name AS campaign_name,camp.type AS campaign_Type, ";
            $lead_statusSQL .= "cnt.first_name,cnt.last_name,cnt.{$company} AS company,cnt.email AS contact_email,cnt.job_title,cnt.phone,cnt.created_at AS created_time, ";
            $lead_statusSQL .= "cnt.phone AS `Phone`,cnt.id AS Contact_id,cmcnt.notes,CONCAT(ua.first_name,' ',ua.last_name) AS agent_name, ";
            $lead_statusSQL .= "CONCAT(uq.first_name,' ',uq.last_name) AS qa_name,tlh.created_at AS `Time_Submitted`,cmcnt.updated_at AS `Last_Updated`,tlh.status AS `Status` ";
			$lead_statusSQL .= "FROM `lead_history` tlh ";
			$lead_statusSQL .= "LEFT JOIN `campaigns` camp ON tlh.campaign_id = camp.id ";
			$lead_statusSQL .= "INNER JOIN $campaigncontacts_table cmcnt ON cmcnt.id = tlh.campaign_contact_id ";
            $lead_statusSQL .= "LEFT JOIN $contact_table cont ON cmcnt.contact_id = cont.id ";
			$lead_statusSQL .= "LEFT JOIN members_qa cnt ON cont.member_id = cnt.id ";
			//$lead_statusSQL .= "LEFT JOIN notes nts ON nts.lead_history_id = tlh.id AND nts.id IN (SELECT MAX(id) FROM notes WHERE lead_history_id = tlh.id) ";
			$lead_statusSQL .= "LEFT JOIN users ua ON tlh.agent_id = ua.id  ";
			$lead_statusSQL .= "LEFT JOIN users uq ON tlh.qa = uq.id ";
			$lead_statusSQL .= $customWhere . " "; 
        }
		else if($this->session->userdata('user_type')== 'qa')
		{
			$lead_statusSQL = "SELECT ua.`telemarketing_offices`,cnt_lock.edit_lead_status,tlh.id as lead_id,tlh.campaign_id,tlh.member_id,tlh.campaign_contact_id,cmcnt.list_id, ";
            $lead_statusSQL .= "tlh.is_qa_in_progress,tlh.qa, ";
            $lead_statusSQL .= "(CASE WHEN ((SELECT COUNT(*) FROM `lead_reason_qa` WHERE (status ='Reject') and  lead_history_id = tlh.id ) >0)  THEN 'Reason' ELSE '-' END) AS rejection_reasons, ";
            $lead_statusSQL .= "(CASE WHEN ((SELECT COUNT(*) FROM `lead_reason_qa` WHERE (status = 'Follow-up') and  lead_history_id = tlh.id ) >0)  THEN 'Reason' ELSE '-' END) AS followup_reasons, ";
            $lead_statusSQL .= "tlh.disapprove_reason as rejection_reason,camp.eg_campaign_id AS eg_campaign_id,camp.name AS campaign_name,camp.type AS campaign_Type, ";
            $lead_statusSQL .= "cnt.first_name, cnt.last_name,cnt.{$company} AS company,cnt.email AS contact_email,cnt.job_title,cnt.phone,cnt.created_at AS created_time, ";
            $lead_statusSQL .= "cnt.phone AS `Phone`,cnt.id AS Contact_id,cmcnt.notes,CONCAT(ua.first_name,' ',ua.last_name) AS agent_name, ";
            $lead_statusSQL .= "CONCAT(uq.first_name,' ',uq.last_name) AS qa_name,tlh.created_at AS `Time_Submitted`,cmcnt.updated_at AS `Last_Updated`,tlh.status AS `Status`  ";
			$lead_statusSQL .= "FROM `lead_history` tlh ";
			$lead_statusSQL .= "LEFT JOIN `campaigns` camp ON tlh.campaign_id = camp.id ";
            $lead_statusSQL .= "INNER JOIN $campaigncontacts_table cmcnt ON tlh.campaign_contact_id = cmcnt.id ";
            $lead_statusSQL .= "LEFT JOIN $contact_table cnt_lock ON cmcnt.contact_id = cnt_lock.id ";
            $lead_statusSQL .= "LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ";
			//$lead_statusSQL .= "LEFT JOIN notes nts ON nts.lead_history_id = tlh.id AND nts.id IN (SELECT MAX(id) FROM notes WHERE lead_history_id = tlh.id) ";
			$lead_statusSQL .= "LEFT JOIN users ua ON tlh.agent_id = ua.id  ";
			$lead_statusSQL .= "LEFT JOIN users uq ON tlh.qa = uq.id ";
			$lead_statusSQL .= $customWhere . " "; 
		}
        $lead_statusSQL .= " GROUP BY lead_id ";
        
        // to fetch No. of Records 
        $lead_statusSQL .= $sort.' '.$order;
        
        if($report){
            $query = $this->db->query($lead_statusSQL);
            return $query->result_array();
        }else{
            $lead_statusSQL .= " LIMIT ? OFFSET ?";
            $query = $this->db->query($lead_statusSQL, array($limit, $offset));
            return $query->result();
        }
    }
    
    /*
     * Get Filter Data from 'Lead_history' Table 
    */
    
    // Fetch QA  to QA Filter  
    public function getQaList()
    {
        $userType = $this->session->userdata('user_type');

        $sql = "SELECT a.id, a.first_name, a.last_name, a.module
                FROM users a 
                WHERE Exists (Select 1 From lead_history lh where lh.qa = a.id) ";

        if($userType != 'admin' && $userType != 'qa'){
            $loggedTmOffice = $this->session->userdata('telemarketing_offices');
            $sql .= " AND a.telemarketing_offices = '" . $loggedTmOffice . "' ";
        }
        
        $sql .=" GROUP BY a.id, a.first_name, a.last_name, a.module ORDER BY a.first_name";
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    public function getAllQAList(){
        $user_type = $this->session->userdata('user_type');
        $sql = "SELECT a.id, a.first_name, a.last_name, a.module from users a WHERE FIND_IN_SET('tm',a.module) <> 0 and a.status = 'Active' and user_type = 'qa' ";
         
        if($user_type != 'admin' && $user_type != 'qa'){
            $logged_tm_office = $this->session->userdata('telemarketing_offices');
            $sql .= " AND a.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $sql .=' ORDER BY a.first_name';
        $query = $this->db->query($sql);

        return $query->result();
    }

    // Fetch Campaigns to Campaign Filter  
    public function getAllCampaignList()
    {
        $user_type = $this->session->userdata('user_type');
        $customWhere = " c.module_type = 'tm' AND c.status!='completed'";
        $sql = 'SELECT DISTINCT c.id, c.name from lead_history JOIN campaigns c ON campaign_id=c.id ';
        //$sql = 'SELECT c.id, c.name from campaigns c ';
        if($user_type == 'agent'){            
            $sql .= ' LEFT JOIN `campaign_assign` ca ON ca.campaign_id = c.id '  ;
			 $customWhere .= ' AND ca.agent_id = '.$this->session->userdata('uid');
        }
        if($user_type == 'team_leader'){            
            $sql .= ' LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id';
            $customWhere .= ' AND cl.user_id = '.$this->session->userdata('uid');
        }
		if($user_type == 'manager'){            
			$sql .= ' LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id';
            $customWhere .= " AND (cto.tm_office = '" . $this->session->userdata('telemarketing_offices') . "' ";
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR cto.tm_office = '" . $sub_telemarketing_office . "'";
            }
            $customWhere .= ') ';
        }
        $sql .=  " WHERE ";
        $sql .=  $customWhere." ORDER BY c.name";

        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    
    // Fetch users who generated Leads to Telemarketer Filter  
    function get_telemarketer(){
        $user_type = $this->session->userdata('user_type');
        
        $sql = "SELECT 
                a.id, a.first_name, a.last_name
            FROM
                users a
            WHERE
                EXISTS ( SELECT 
                        1
                    FROM
                        lead_history lh
                    WHERE
                        lh.agent_id = a.id) ";
         
        if ($user_type == 'manager') {
            $sql .= " AND (a.telemarketing_offices = '" . $this->session->userdata('telemarketing_offices') . "' ";
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $sql .= " OR a.telemarketing_offices = '" . $sub_telemarketing_office . "'";
            }
            $sql .= ') ';
        } else {
            if($user_type != 'admin' && $user_type != 'qa'){
                $sql .= " AND a.telemarketing_offices = '" . $this->session->userdata('telemarketing_offices') . "' ";
            }
        }
        $sql .= "GROUP BY a.id , a.first_name , a.last_name ";
        $sql .= "ORDER BY a.first_name ";

        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    
    function get_all_telemarketer(){

        $user_type = $this->session->userdata('user_type');

        $sql = "SELECT a.id, a.first_name, a.last_name 
                FROM users a where status ='Active' and user_type ='agent'";
        if($user_type != 'admin' && $user_type != 'qa'){
            $sql .= " and a.telemarketing_offices = '" . $this->session->userdata('telemarketing_offices') . "' ";
        }
       $sql .=' ORDER BY a.first_name ';

        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    
    function getReasonsByLeadId($leadId, $extraFilter = 1){
        $sql = 'SELECT reason,reason_text,status FROM `lead_reason_qa` WHERE lead_history_id = ? ' ;
        $sql .= "AND " . $extraFilter;
        $query = $this->db->query($sql,array($leadId));
        $result = $query->result_array();
        return $result;
    }
    
}

