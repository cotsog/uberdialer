<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Calls_model extends CI_Model
{
    public $table = 'contacts';
    public $campaignContactsTable = 'campaign_contacts';
    public $campaignTable = 'campaigns';
    public $campaign_tm_offices = 'campaign_tm_offices';
    public $call_disposition_history = 'call_disposition_history';
    public $usersTable = 'users';
    public $contactListsTable = 'contact_lists'; 
    public $callDispositionsTable = 'calldispositions';
    public $egQuestionsTable = 'questions';
    public $tmLeadHistoryTable = 'lead_history';
    public $agentLeadTable = 'agent_lead';
    public $tmNotesTable = 'notes';
    public $callHistoryTable = 'call_history';
    public $lead_reason_qa = 'lead_reason_qa';
    public $callFlowFindingsQa = 'call_flow_findings_qa';
    public $members_qa = 'members_qa';
    public $voip_communications = 'voip_communications';
    public $email_history = 'email_history';

    /* Dev_NV Region Start */

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //setting the second parameter to TRUE (Boolean) the function will return the database object.
        //$this->db2 = $this->load->database('db2', TRUE);
        $this->table .= $this->fileAppend;
    }

    public function getCallDetailOne($campaignContactListID)
    {
        if($this->app == 'mpg'){
            $fields = "c.*,cou.dial_code,IF(m.email != '', m.email, c.email) as email,IF(m.first_name != '', m.first_name, c.first_name) as first_name,
                IF(m.last_name != '', m.last_name, c.last_name) as last_name,c.job_title as job_title,c.job_level as job_level,
                IF(m.company_name != '', m.company_name, c.company) as company_name,IF(m.address1 != '', m.address1, c.address1) as address,IF(m.city != '', m.city, c.city) as city,
                IF(m.zip != '', m.zip, c.zip) as zip,IF(m.state != '', m.state, c.state) as state,IF(m.country != '', m.country, c.country) as country, ccnt.id AS campaign_contact_id, 
                ccnt.campaign_id AS campaign_id,ccnt.source AS source,ccnt.resource_id, cs.custom_question_value AS custom_question_value,cs.site_id,cs.eg_campaign_id,cs.name,
                cs.type AS campaign_type, cs.script_main AS script_main,cs.script_alt AS script_alt ,al.agent_id, al.lead_id,tlh.resource_id AS tlh_resource_id,
                tlh.resource_name as resource_name,ccnt.call_disposition_id,ccnt.call_disposition_update_date,tlh.status AS `status`,tlh.qa,tlh.is_qa_in_progress,ccnt.reference_link,tlh.email_resource_sent";
        }else{
            
            $fields = "c.id,
                c.locked_by,
                c.member_id,
                cou.dial_code,
                IF(m.email != '', m.email, c.email) AS email,
                IF(m.first_name != '',
                    m.first_name,
                    c.first_name) AS first_name,
                IF(m.last_name != '',
                    m.last_name,
                    c.last_name) AS last_name,
                IF(m.job_title != '',
                    m.job_title,
                    c.job_title) AS job_title,
                IF(m.job_level != '',
                    m.job_level,
                    c.job_level) AS job_level,
                IF(m.silo != '',
                    m.silo,
                    c.job_function) AS job_function,
                IF(m.company_name != '',
                    m.company_name,
                    c.company) AS company,
                IF(m.address1 != '',
                    m.address1,
                    c.address) AS address,
                IF(m.city != '', m.city, c.city) AS city,
                IF(m.zip != '', m.zip, c.zip) AS zip,
                IF(m.state != '', m.state, c.state) AS state,
                IF(m.country != '',
                    m.country,
                    c.country) AS country,
                IF(m.industry != '',
                    m.industry,
                    c.industry) AS industry,
                IF(m.company_size != '',
                    m.company_size,
                    c.company_size) AS company_size,
                IF(m.company_revenue != '',
                    m.company_revenue,
                    c.company_revenue) AS company_revenue,
                IF(m.phone != '', m.phone, c.phone) AS phone,
                IF(m.ext != '', m.ext, c.ext) AS ext,
                c.alternate_no,
                c.notes,
                c.time_zone,
                c.edit_lead_status,
                c.locked_by,
                c.created_at,
                c.updated_at,
                ccnt.id AS campaign_contact_id,
                ccnt.campaign_id AS campaign_id,
                ccnt.source AS source,
                ccnt.resource_id,
                ccnt.lifted,
                ccnt.last_follow_up_date,
                c.original_owner,
                ccnt.source,
                ccnt.created_at,
                (SELECT 
                        CONCAT(first_name, ' ', last_name)
                    FROM
                        users
                    WHERE
                        id = ccnt.created_by) AS contact_created_by,
                (SELECT 
                        user_type
                    FROM
                        users
                    WHERE
                        id = ccnt.created_by) AS contact_created_usertype,
                cs.custom_question_value AS custom_question_value,
                cs.site_id,
                cs.eg_campaign_id,
                cs.name,
                cs.type AS campaign_type,
                cs.script_main AS script_main,
                cs.script_alt AS script_alt,
                tlh.agent_id,
                tlh.id as lead_id,
                tlh.resource_id AS tlh_resource_id,
                tlh.resource_name AS resource_name,
                ccnt.call_disposition_id,
                ccnt.call_disposition_update_date,
                tlh.status AS `status`,
                tlh.qa,
                tlh.is_qa_in_progress,
                ccnt.reference_link,
                tlh.email_resource_sent,
                tlh.created_at AS `first_qa_date`,
                GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,
                (SELECT list_name FROM campaign_lists cl JOIN campaign_contact_changes ccc ON cl.id = ccc.from_list_id WHERE campaign_contact_id = ccnt.id ORDER BY ccc.id DESC limit 1) as `original_list`,
                CASE cs.site_name
                    WHEN 'Enterprise Guide' THEN 'EG'
                    WHEN 'Marketing Solutions Insider' THEN 'MSI'
                    WHEN 'Smart Tech Resource' THEN 'STR'
                    WHEN 'Tech Product Insider' THEN 'TPI'
                    WHEN 'Technology Buyer\'s Guide' THEN 'TBG'
                    WHEN 'Technology Resource Insider' THEN 'TRI'
                    WHEN 'PureB2B' THEN 'PB2B'
                    WHEN 'Spiralytics' THEN 'SNA'
                END AS site_name";
        }
        // Start HP UAD-16 login and start session
        $sql = "SELECT {$fields}, auto_dial FROM {$this->campaignContactsTable} ccnt 
                    LEFT JOIN {$this->table} c ON c.id = ccnt.contact_id
                    LEFT JOIN `countries` cou ON c.country = cou.country_code
                    LEFT JOIN {$this->members_qa} m ON m.id = c.member_id
                    INNER JOIN {$this->campaignTable} cs ON cs.id = ccnt.campaign_id
					LEFT JOIN {$this->campaign_tm_offices} cto ON cs.id = cto.campaign_id
                    LEFT JOIN {$this->tmLeadHistoryTable} tlh ON ccnt.id = tlh.campaign_contact_id";
        $sql .= ' WHERE  ccnt.id  = ?  order by tlh.updated_at desc';
        $sql .= ' LIMIT 1 ';

        // End HP UAD-16 login and start session

        $query = $this->db->query($sql,array($campaignContactListID));
        $array = $query->result();
        
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    public function updateContactCallDetail($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);

    }

    public function getCallHistoryList($contact_id)
    {
        $sql = 'SELECT cp.calldisposition_name AS `result_Status`,
                clh.created_at AS created_at,
                c.name AS campaignName,
                u.first_name AS agent_tl_first_name,
                tn.note AS notes,
                c.eg_campaign_id as `campaign_id`,
                clh.id,
                clh.recording_url,
                CASE c.site_name
                WHEN "Enterprise Guide" THEN "EG" 
                WHEN "Marketing Solutions Insider" THEN "MSI" 
                WHEN "Smart Tech Resource" THEN "STR" 
                WHEN "Tech Product Insider" THEN "TPI" 
                WHEN "Technology Buyer\'s Guide" THEN "TBG" 
                WHEN "Technology Resource Insider" THEN "TRI" 
                WHEN "PureB2B" THEN "PB2B" 
                WHEN "Spiralytics" THEN "SNA" END AS site_name,
                clh.sid as call_uuid,
                clh.id as plivo_id,
                clh.conf_sid,
                clh.duration
                FROM ' . $this->callHistoryTable . ' clh
                LEFT JOIN ' . $this->usersTable . ' u ON clh.user_id = u.id
                LEFT JOIN ' . $this->campaignTable . ' c ON clh.campaign_id = c.id
                LEFT JOIN ' . $this->call_disposition_history . ' cdh ON clh.id = cdh.call_history_id
                LEFT JOIN calldispositions cp ON cdh.call_disposition_id = cp.id
                LEFT JOIN ' . $this->tmNotesTable . ' tn ON  clh.id = tn.call_history_id
                WHERE clh.contact_id = ? GROUP BY clh.id ORDER BY clh.created_at DESC';

        $query = $this->db->query($sql, array($contact_id));

        $array = $query->result();

        return $array;
    }

    public function getCallDispositions( $module = '' )
    {
        $this->db->select('*');
        $this->db->from($this->callDispositionsTable);
        
        if( $module == '' ){
            $this->db->where('id != ', 30);
        }
        
        $this->db->order_by("calldisposition_name", "asc");
        $array = $this->db->get()->result();
        return $array;        
    }
    
    public function getCallWorkableDispositions( $module = '' )
    {
        $this->db->select('*');
        $this->db->from($this->callDispositionsTable);
        
        if( $module == '' ){
            $this->db->where( array( 'id != ' => 30, 'is_workable' => 1 ) ) ;
        }else{
            $this->db->where( array( 'is_workable' => 1 ) ) ;
        }
        
        $this->db->order_by("calldisposition_name", "asc");
        $array = $this->db->get()->result();
        return $array;        
    }
    
    public function getCallNonWorkableDispositions( $module = '' )
    {
        $this->db->select('*');
        $this->db->from($this->callDispositionsTable);
        
        if( $module == '' ){
            $this->db->where( array( 'id != ' => 30, 'is_workable' => 0 ) ) ;
        }else{
            $this->db->where( array( 'is_workable' => 0 ) ) ;
        }
        
        $this->db->order_by("calldisposition_name", "asc");
        $array = $this->db->get()->result();
        return $array;        
    }
    
   function getEGCampaignDataByID_calls($egCampaignID)
    {
        $this->db2->select('id,`type`,questions,intent_questions,
            resources,incentives_available,client,gdpr_required,reg_data,reg_rules,tm_brand');
        $this->db2->from($this->campaignTable);
        $this->db2->where('id', $egCampaignID);
        $this->db2->limit(1);
        $query = $this->db2->get();
        $array = array();
        if( !empty($query)) { 
            $array = $query->result();
            if (!empty($array)) {
                $array = $array[0];
            }
        }
        return $array;
    }

    function getEGCampaignQuestion($filter)
    {
        $sql = 'SELECT * FROM ' . $this->egQuestionsTable . ' ' . $filter;
        $query = $this->db2->query($sql);

        return $query->result();
    }

    function getEGSurveyQuestion($filter)
    {
        $sql = "SELECT * FROM survey_questions " . $filter;
        $query = $this->db2->query($sql);
        return $query->result();
    }

    function getAgentLeadCount($campaignContactListID)
    {

	$sql = "SELECT cdh.user_id as agent_id, u.status ";
        $sql .= "FROM campaign_contacts cc ";
        $sql .= "JOIN call_disposition_history cdh ON cc.id = cdh.campaign_contact_id ";
        $sql .= "LEFT JOIN users u ON u.id = cdh.user_id ";
        $sql .= "WHERE cc.id = ? ";
        $sql .= "ORDER BY cdh.id DESC  limit 1 ";

        $query = $this->db->query($sql,array($campaignContactListID));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    function getQALeadAccessCount($campaignContactListID){
        //Uber bug fixes and rules changes

        $sql = "SELECT tlh.qa,tlh.status,tlh.is_qa_in_progress
                FROM {$this->tmLeadHistoryTable}  tlh
                WHERE tlh.campaign_contact_id = ?
               AND (tlh.status IN ('Pending','QA in progress')) limit 1";

        $query = $this->db->query($sql,array($campaignContactListID));

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function isLeadForFollowup($campaignContactID)
    {

        $result = $this->getLeadFollowup($campaignContactID,'1');

        return !empty($result) ? true : false;
    }

    function getLeadFollowup($campaignContactID, $fields)
    {
        $sql = "SELECT {$fields} FROM campaign_contacts cc WHERE call_disposition_id = 1 and exists (SELECT 1 FROM lead_history WHERE campaign_contact_id = cc.id and status = 'Follow-up') and id = ?";

        $query = $this->db->query($sql,array($campaignContactID));

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function isLeadWorkable($campaignContactID)
    {
        $sql = "SELECT 1 as `result` FROM campaign_contacts WHERE workable_status ='W' and id = ?";

        $query = $this->db->query($sql,array($campaignContactID));

        return !empty($query->result()) ? true : false;
    }

    function IsFollowUpOneWeek($campaignContactListID)
    {
        $sql = "SELECT tlh.qa,tlh.agent_id,u.parent_id as user_team_leader_id,u.id
                FROM {$this->tmLeadHistoryTable} tlh
                JOIN `lead_status` ls ON tlh.campaign_contact_id = ls.campaign_contact_id
                JOIN users u ON u.id = tlh.agent_id
                WHERE tlh.campaign_contact_id = ? AND ls.`status` = 'Follow-up' AND tlh.updated_at > ADDDATE(NOW(), INTERVAL -1 WEEK) ORDER BY ls.id DESC,tlh.id DESC LIMIT 0,1";

        $query = $this->db->query($sql,array($campaignContactListID));

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function IsCallBackByAgent($campaignContactListID){

        $sql = "SELECT cdh.user_id as agent_id, u.status ";
        $sql .= "FROM campaign_contacts cc ";
        $sql .= "JOIN call_disposition_history cdh ON cc.id = cdh.campaign_contact_id ";
        $sql .= "LEFT JOIN users u ON u.id = cdh.user_id ";
        $sql .= "WHERE cc.id = ? AND cc.`call_disposition_id` = '2' AND cc.`call_disposition_update_date` > NOW() ";
        $sql .= "AND DATE_SUB(NOW(), INTERVAL 2 DAY) >= cc.`call_disposition_update_date` ";
        $sql .= "ORDER BY cdh.id DESC  limit 1 ";

        $query = $this->db->query($sql,array($campaignContactListID));

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    public function CheckTodayCallDialledLimit($contactID=0, $callLimit=3, $auto=0, $campaignId=0){

        $fileds = ($auto == 1) ? "cc.id as campaign_contact_id" : "1";
        $join = ($auto == 1) ? "LEFT JOIN campaign_contacts cc ON c.id = cc.contact_id " : "";
        $sql = "SELECT ". $fileds . "
                FROM contacts c " . $join . "
                LEFT JOIN members_qa m ON c.member_id = m.id 
                LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country) 
                LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone)) 
                WHERE dnf.count >= {$callLimit} ";
        
        $sql .= ($auto != 1) ? ' AND c.id = ? ' : '';
        $sql .= ($auto == 1) ? "AND cc.campaign_id=$campaignId AND cc.auto_added_as_hopper='1'" : "";
        
        $query = $this->db->query($sql , array($contactID));
        $array = $query->result();

        return $array;
    }
    
    public function checkTodayCallback($campaignContactId)
    {
        $date = date('Y-m-d');
        $sql = "SELECT 
                COUNT(*) AS callbackCount
            FROM
                call_disposition_history
            WHERE
                campaign_contact_id = ?
                AND created_at >= '{$date} 00:00:00'
                AND created_at <= '{$date} 23:59:59'
                AND call_disposition_id = 2
            ORDER BY id DESC";
                
        $query = $this->db->query($sql , array($campaignContactId));
        $array = $query->result();

        return $array;
    }

    public function retrieveCallback($agentId)
    {
        $date = date('Y-m-d');
        
        $sql = "SELECT 
            CONCAT(c.first_name, ' ' , c.last_name) AS prospect,
            cc.call_disposition_update_date,
            c.company,
            c.phone,
            (TIME_TO_SEC(TIMEDIFF(DATE_FORMAT(cc.call_disposition_update_date,
                                    '%Y-%m-%d %H:%i'),
                            DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i'))) / 60) AS timediff
            FROM
                campaign_contacts cc
                    JOIN
                call_disposition_history cdh ON cdh.campaign_contact_id = cc.id
                    AND cdh.call_disposition_id = cc.call_disposition_id
                    JOIN
                contacts c ON c.id = cc.contact_id
            WHERE
                cdh.user_id = {$agentId}
                    AND cc.call_disposition_id = 2
                    AND cc.call_disposition_update_date >= '{$date} 00:00:00'
                    AND cc.call_disposition_update_date <= '{$date} 23:59:59'
                    AND (TIME_TO_SEC(TIMEDIFF(DATE_FORMAT(cc.call_disposition_update_date,
                            '%Y-%m-%d %H:%i'),
                    DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i'))) / 60) between 0 and 5
            GROUP BY cc.id
            ORDER BY cc.id desc
            LIMIT 1
        ";

        $query = $this->db->query($sql);

        $array = $query->result_array();

        return $array;
    }

    
    public function setCallHistoryObjectData()
    {
        $callHistory = new CallHistoryTable();
        $viewListData = (object)$this->input->post();
        $loggedUserID = $this->session->userdata('uid');

        if(!empty($viewListData->phone)){
                $callHistory->number_dialed = $viewListData->phone;
            }

        if (isset($viewListData->contact_id))
            $callHistory->contact_id = $viewListData->contact_id;

        if (!empty($viewListData->campaign_id))
            $callHistory->campaign_id = $viewListData->campaign_id;

        if(!empty($loggedUserID)){
            $callHistory->user_id = $loggedUserID;
        }

        if (isset($viewListData->count_flag))
            $callHistory->count_flag = $viewListData->count_flag;

        $callHistory->ip = $_SERVER['REMOTE_ADDR'];

        return $callHistory;
    }

    public function get_call_history_data($call_history_id)
    {
        $this->db->select('id,call_start_datetime,call_end_datetime,recording_url');
        $this->db->from($this->callHistoryTable);
        $this->db->where('id', $call_history_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;

    }

    function updateAgentCallHistory($callHistoryArray){
        $update_tm_lead_history = $this->db->update($this->callHistoryTable, $callHistoryArray, array('id' => $callHistoryArray->id));

        return $update_tm_lead_history;
    }
    function InsertAgentStartCallHistory($agentStartCallHistoryArray){
        $this->db->insert($this->callHistoryTable, $agentStartCallHistoryArray);
        $last_insert_id = $this->db->insert_id();
        return $last_insert_id;
    }
    
    function InsertAgentCallHistoryCampaignContact($callHistoryId, $campaignContactId, $countFlag){
        $sql = "INSERT INTO call_history_campaign_contact
          (`campaign_contact_id`, `call_history_id`, `count_flag`, `created_at`, `updated_at`) VALUES (?, ?, ?, NOW(), NOW())";

        $query = $this->db->query($sql,array($campaignContactId, $callHistoryId, $countFlag));
        if($query){
            $last_insert_id = $this->db->insert_id();
            return $last_insert_id;
        }else{
            return 0;
        }
    }
        
    function updateCallHistoryCampaignContact($set,$where){
        $updateCallHistoryCampaignContact = $this->db->update('call_history_campaign_contact', $set, $where);

        return $updateCallHistoryCampaignContact;
    }

    function InsertLeadReasonQA($build_query_fields){
        if(!empty($build_query_fields)){
            $sql = "INSERT INTO {$this->lead_reason_qa}
          (`lead_history_id`, `user_id`, `status`, `reason`, `reason_text`, `created_at`) VALUES $build_query_fields";

            $query = $this->db->query($sql);
            if($query){
                $last_insert_id = $this->db->insert_id();
                return $last_insert_id;
            }else{
                return 0;
            }
        }
    }

    function updateRetractLeadHistory($lead_update_data_array){
        $update_tm_lead_history = $this->db->update($this->tmLeadHistoryTable, $lead_update_data_array, array('id' => $lead_update_data_array->id));
        $this->_update_agent_submitted_lead_status($lead_update_data_array->id);
        return $update_tm_lead_history;
    }

    function insert_lead_status($lead_update_data_array){
        $this->db->insert('lead_status', $lead_update_data_array);
    }

    public function getTmLeadHistoryData($lead_history_id)
    {
        $this->db->select('status,qa,campaign_contact_id');
        $this->db->from($this->tmLeadHistoryTable);
        $this->db->where('id', $lead_history_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;

    }

    public function check_already_created_lead_by_campaign_contact_id($campaign_contact_id)
    {
        $this->db->select('id,campaign_contact_id,status');
        $this->db->from($this->tmLeadHistoryTable);
        $this->db->where('campaign_contact_id', $campaign_contact_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;

    }

    public function update_insert_call_flow_findings_responses($callFlowFindingsArray){
        foreach ($callFlowFindingsArray as $response) {
            if (isset($response['call_flow_value'])) {
               $sql = 'INSERT INTO `call_flow_findings_qa` (lead_history_id, user_id, `status`,call_flow_text,call_flow_value) VALUES(' . $response['lead_history_id'] . ', ' . $response['user_id'] . ',"' . $response['status'] . '","' . $response['call_flow_text'] . '", ' . $response['call_flow_value'] . ') ON DUPLICATE KEY UPDATE user_id=' . $response['user_id'] . ',status="' . $response['status'] . '",call_flow_value=' . $response['call_flow_value'] . ' ';
               $query = $this->db->query($sql);
            }
        }
    }

    public function getCallFlowFindingsData($lead_history_id)
    {
        $this->db->select('*');
        $this->db->from($this->callFlowFindingsQa);
        $this->db->where('lead_history_id', $lead_history_id);
        $this->db->order_By('id', 'ASC');
        $query = $this->db->get();
        $array = $query->result();
        return $array;
    }

    function insert_call_disposition_history($obj)
    {
        //$result = $this->db->insert($this->call_disposition_history, $obj);
        $sql = "INSERT INTO " . $this->call_disposition_history."(campaign_contact_id,call_history_id,user_id,call_disposition_id,created_at) VALUES $obj";
        
        $result = $this->db->query($sql);
        if ($result) {
            //$call_disposition_history_id = $this->db->insert_id();
            $call_disposition_history_id = 0;
        } else {
            $call_disposition_history_id = 0;
        }
        return $call_disposition_history_id;
    }
    
    function updateCallDispositionHistory($set, $where){
        $updateCallDispositionHistory = $this->db->update($this->call_disposition_history, $set, $where);

        return $updateCallDispositionHistory;
    }

    function getCampaignContacts($campaign_contact_id){
        $sql = " SELECT * FROM {$this->campaignContactsTable} WHERE id = ? ";
        $query = $this->db->query($sql,array($campaign_contact_id));
        $array = $query->result();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;

    }

    function view_all_call_history($campaign_contact_id){
        $recording_field = ($this->plivo_switch) ? 'plc.recording_url' : 'ch.recording_url';
        $recording_table = ($this->plivo_switch) ? 'LEFT JOIN ' . $this->voip_communications . ' plc ON ch.id = plc.call_history_id' : '' ;
        $sql = " SELECT
                    cp.calldisposition_name AS `result_status`,
                    ch.created_at AS created_at,
                    c.name AS campaignName,
                    u.first_name AS agent_first_name,
                    tn.note AS notes,
                    ch.campaign_id,
                    ch.id,
                    {$recording_field}
                    FROM call_history ch
                    LEFT JOIN users u ON ch.user_id = u.id
                    LEFT JOIN campaigns c ON ch.campaign_id = c.id
                    LEFT JOIN call_disposition_history cdh ON ch.id = cdh.call_history_id
                    LEFT JOIN calldispositions cp ON cdh.call_disposition_id = cp.id
                    LEFT JOIN notes tn ON  ch.id = tn.call_history_id
                    {$recording_table}
                    WHERE plc.campaign_contact_id = ?
                    GROUP BY ch.id ORDER BY ch.created_at DESC ";

        $query = $this->db->query($sql,array($campaign_contact_id));

        $array = $query->result();

        return $array;
    }

    public function active_calls()
    {
      //  $logged_tm_office = $this->session->userdata('telemarketing_offices');
      ///  $logged_user_type = $this->session->userdata('user_type');
            $campaing_contact = $this->campaignContactsTable;
            $contact = $this->table;
            $call_table = ($this->plivo_switch) ? $this->voip_communications : $this->callHistoryTable;
        $sql = 'SELECT 	pl.id,pl.target,
                CONCAT(u.`first_name`," ",u.last_name) AS first_name 
                , IF(c.first_name != "",CONCAT(c.`first_name`," ",c.last_name), "Anonymous") AS target_name 
                FROM 
                '.$call_table.' pl
                LEFT JOIN '.$campaing_contact.' cc ON cc.id = pl.`campaign_contact_id`
                LEFT JOIN `campaign_tm_offices` cto ON cc.campaign_id = cto.`campaign_id`
                LEFT JOIN '.$contact.' c ON c.id =  cc.contact_id
                JOIN `users` u ON u.`id` = pl.`agent_id` 
                WHERE  1=1
                    AND pl.`hangup_cause` IS NULL  
                    AND pl.`dialer_mode` = "1"';
        /*if ($logged_user_type == 'manager') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }*/
        $sql .= ' GROUP BY pl.id ORDER BY pl.id DESC ';
        
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function get_communication_by_id($communication_id)
    {
        $call_table = ($this->plivo_switch) ? $this->voip_communications : $this->callHistoryTable;
        $this->db->select('*');
        $this->db->from($call_table);
        $this->db->where('id',$communication_id);

        $query = $this->db->get();
        $array = $query->result();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
	public function call_recording_list_count($searchBy = "")
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $company = 'company';
       
        $customWhere = " WHERE ch.module_type ='tm' AND ch.count_flag = 1 ";
        $campaign_status = 'AND 1=1 ';
		$agent_join = $member_join =$lead_history_join="";
        if (!empty($searchBy))
    {
            if (!empty($searchBy['status']) && $searchBy['status'] != 'ALL') {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }

                if($searchBy['status'] == 'In Progress') {
                    $customWhere .= " AND (lh.`status` = '" . $searchBy['status'] . "' OR (lh.`status` IS NULL and cc.call_disposition_id > 0))";
                } else {
                    $customWhere .= ' AND lh.`status` = "' . $searchBy['status'] . '"';
                }
				$lead_history_join=" LEFT JOIN `campaign_contacts` cc ON ch.campaign_contact_id = cc.id LEFT JOIN `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id ";
            }
            if (!empty($searchBy['qa'])) {
				$lead_history_join=" LEFT JOIN `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id ";
                $customWhere .= ' AND lh.qa = "' . $searchBy['qa'] . '"';
            }
            if (!empty($searchBy['contact_name'])) {
				$member_join=" LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ";
				if($lead_history_join==""){$member_join=$lead_history_join.$member_join;}
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
				$member_join=" LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ";
				if($lead_history_join==""){$member_join=$lead_history_join.$member_join;}
                $customWhere .= ' AND cnt.'.$company.' LIKE "%' . $searchBy['company'] . '%"';
            }
            if (!empty($searchBy['email'])) {
				$member_join=" LEFT JOIN members_qa cnt ON tlh.member_id = cnt.id ";
				if($lead_history_join==""){$member_join=$lead_history_join.$member_join;}
                $customWhere .= ' AND cnt.email LIKE "%' . $searchBy['email'] . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $customWhere .= ' AND ch.campaign_id = '.$searchBy['campaign'];
            }
            if (!empty($searchBy['telemarketer'])){
                // $lead_history_join=" LEFT JOIN `users` u ON ch.campaign_contact_id = lh.campaign_contact_id ";
                $customWhere .=  ' AND ch.user_id='.$searchBy['telemarketer'];
            }
        }
        if(!isset($searchBy['show_non_active']) || empty($searchBy['show_non_active'])){
            $campaign_status .= ' AND c.status = "active" ';
        }

        //Date Validation

        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
        }
        else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
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
            $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
        }else{
            $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") = CURDATE()';
        }
        
		if($this->session->userdata('user_type') == 'team_leader'){
		$customWhere .= ' AND (ua.id = '.$this->session->userdata('uid').' OR ua.parent_id = '.$this->session->userdata('uid').')';
			$call_recording_sql =
                "SELECT count(distinct ch.id) as total
                    FROM `call_history` ch
					".$lead_history_join."
                    ".$agent_join."
                    LEFT JOIN {$this->table} cnt ON ch.contact_id = cnt.id
                    LEFT JOIN users ua ON ch.user_id = ua.id
                    LEFT JOIN `campaigns` c ON ch.campaign_id = c.id $campaign_status
                    " . $customWhere ;
		}
		else if($this->session->userdata('user_type') == 'manager')
		{
			$customWhere .= " AND cto.tm_office = '" . $logged_tm_office ."'";
			$call_recording_sql =
                "SELECT count(distinct ch.id) as total
                    FROM `call_history` ch
					".$lead_history_join."
                    ".$agent_join."
                    LEFT JOIN {$this->table} cnt ON ch.contact_id = cnt.id
                    LEFT JOIN `campaigns` c ON ch.campaign_id = c.id $campaign_status
					LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                    " . $customWhere ;
		}
		else
		{
			$call_recording_sql =
                "SELECT count(distinct ch.id) as total
                    FROM `call_history` ch
					".$lead_history_join."
                    ".$agent_join."
                    LEFT JOIN {$this->table} cnt ON ch.contact_id = cnt.id
                    LEFT JOIN `campaigns` c ON ch.campaign_id = c.id $campaign_status
                    " . $customWhere ;
		}
        $query = $this->db->query($call_recording_sql);
			
			
			$array=$query->result();
            return $array[0]->total;
    }
    
    public function call_recording_list($searchBy = "", $limit = "", $offset = "", $sortField = "", $order = "", $report = 0, $is_status_approve = 0)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $company = 'company';
        if($sortField){
            switch($sortField){
                case 'Id':
                    $sort = ' ORDER BY ch.campaign_id';
                    break;
                case 'Name':
                    $sort = ' ORDER BY campaign_name';
                    break;
                case 'Type':
                    $sort=' ORDER BY campaign_Type';
                    break;
                case 'FullName':
                    $sort = ' ORDER BY full_name';
                    break;
                case 'Company':
                    $sort = ' ORDER BY company';
                    break;
                case 'Email':
                    $sort = ' ORDER BY contact_email';
                    break;
                case 'Time':
                    $sort = ' ORDER BY call_created_at';
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
                    $sort = ' ORDER BY lh.updated_at';
                    break;
            }
        } else {
            $sort = ' ORDER BY ch.created_at';
        }
        $customWhere = '1=1 ';
        $campaign_status = 'AND 1=1 ';
        if (!empty($searchBy))
        {
            if (!empty($searchBy['status']) && $searchBy['status'] != 'ALL') {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }
                if($searchBy['status'] == 'In Progress') {
                    $customWhere .= " AND (lh.`status` = '" . $searchBy['status'] . "' OR (lh.`status` IS NULL and cc.call_disposition_id > 0))";
                } else {
                    $customWhere .= ' AND lh.`status` = "' . $searchBy['status'] . '"';
                }
            }
            if (!empty($searchBy['qa'])) {
                $customWhere .= ' AND lh.qa = "' . $searchBy['qa'] . '"';
            }
            if (!empty($searchBy['contact_name'])) {
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
                $customWhere .= ' AND cnt.'.$company.' LIKE "%' . $searchBy['company'] . '%"';
            }
            if (!empty($searchBy['email'])) {
                $customWhere .= ' AND cnt.email LIKE "%' . $searchBy['email'] . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $customWhere .= ' AND ch.campaign_id = '.$searchBy['campaign'];
            }
            if (!empty($searchBy['telemarketer'])){
                $customWhere .=  ' AND ua.id='.$searchBy['telemarketer'];
            }
        }
        if(!isset($searchBy['show_non_active']) || empty($searchBy['show_non_active'])){
            $campaign_status .= ' AND c.status = "active" ';
        }

        //Date Validation

        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            $customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
        }
        else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
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
            $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
        }else{
            $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") = CURDATE()';
        }
        if($this->session->userdata('user_type') == 'team_leader'){
            $customWhere .= ' AND (ua.id = '.$this->session->userdata('uid').' OR ua.parent_id = '.$this->session->userdata('uid').')';
        }
        $recording_field = ($this->plivo_switch) ? 'plc.recording_url,plc.sid as call_uuid,plc.id as plivo_id' : 'ch.recording_url,ch.sid as call_uuid,ch.id as plivo_id';
        $recording_table = ($this->plivo_switch) ? "LEFT JOIN {$this->voip_communications} plc ON plc.call_history_id = ch.id" : '' ;
        $call_recording_sql =
                "SELECT ch.call_start_datetime,ch.call_end_datetime,ch.id AS call_id,ch.created_at AS call_created_at,ch.number_dialed AS phone,
                    c.id AS campaign_id,c.eg_campaign_id, c.name AS campaign_name, c.type AS campaign_type,
                    cnt.id AS contact_id, cnt.company,CONCAT(cnt.first_name,' ',cnt.last_name) AS full_name,cnt.email AS contact_email,
                    ch.campaign_contact_id AS campaign_contact_id, 
                    IF(lh.status is null, IF(cc.call_disposition_id > 0, 'In Progress' , NULL) ,lh.status) AS Status,
                    n.note as notes,
                    CONCAT(ua.first_name,' ',ua.last_name) AS agent_name,
                    CONCAT(uq.first_name,' ',uq.last_name) AS qa_name,
                    {$recording_field}
                    FROM `call_history` ch
                    LEFT JOIN {$this->table} cnt ON ch.contact_id = cnt.id
                    LEFT JOIN `notes` n ON ch.id = n.call_history_id
                    LEFT JOIN `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id
                    LEFT JOIN `campaign_contacts` cc ON ch.campaign_contact_id = cc.id 
                    JOIN users ua ON ch.user_id = ua.id
                    LEFT JOIN users uq ON lh.qa = uq.id
                    LEFT JOIN `campaigns` c ON ch.campaign_id = c.id $campaign_status
                    LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
                    {$recording_table}
                    WHERE   " . $customWhere . " AND ch.module_type ='tm' 
                    ";

        if ($logged_user_type != 'admin' && $logged_user_type != 'qa') {
            $customWhere .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $call_recording_sql .= " GROUP BY ch.id ";


        $call_recording_sql .= $sort.' '.$order;
        if($report){
            $query = $this->db->query($call_recording_sql);
            return $query->result();
        }else{
            $call_recording_sql .= " LIMIT ? OFFSET ?";
		  
            $query = $this->db->query($call_recording_sql, array($limit, $offset));

            return $query->result();
        }
    }

    public function call_recording_list2($searchBy = "", $limit = "", $offset = "", $sortField = "", $order = "", $report = 0, $is_status_approve = 0)
    {
        $loggedUserID = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        
        if($sortField){
            switch($sortField){
                case 'Id':
                    $sort = ' ORDER BY c.id';
                    break;
                case 'Name':
                    $sort = ' ORDER BY c.name';
                    break;
                case 'Type':
                    $sort=' ORDER BY c.type';
                    break;
                case 'FullName':
                    $sort = ' ORDER BY cnt.first_name, cnt.last_name';
                    break;
                case 'Company':
                    $sort = ' ORDER BY company';
                    break;
                case 'Email':
                    $sort = ' ORDER BY cnt.email';
                    break;
                case 'Time':
                    $sort = ' ORDER BY ch.created_at';
                    break;
                case 'Agent':
                    $sort = ' ORDER BY u.first_name, u.last_name';
                    break;
                case 'Qa':
                    $sort = ' ORDER BY uq.first_name, uq.last_name';
                    break;
                case 'Status':
                    $sort = ' ORDER BY Status';
                    break;
                case 'Date':
                    $sort = ' ORDER BY ch.ch_created_at';
                    break;
            }
        } else {
            $sort = ' ORDER BY ch.ch_created_at';
        }
        
        $customWhere = '1=1 ';
        if (!empty($searchBy)) {
            if (!empty($searchBy['status']) && $searchBy['status'] != 'ALL') {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }
                
                if($searchBy['status'] == 'In Progress') {
                    $customWhere .= " AND (lh.status = 'In Progress' OR lh.status IS NULL) AND ch.call_disposition_id > 0 ";
                } else {
                    $customWhere .= ' AND lh.`status` = "' . $searchBy['status'] . '"';
                }
            }
            if (!empty($searchBy['qa'])) {
                $customWhere .= ' AND lh.qa = "' . $searchBy['qa'] . '"';
            }
            if (!empty($searchBy['contact_name'])) {
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
                $customWhere .= ' AND cnt.company LIKE "%' . $searchBy['company'] . '%"';
            }
            if (!empty($searchBy['email'])) {
                $customWhere .= ' AND cnt.email LIKE "%' . $searchBy['email'] . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $customWhere .= ' AND c.id = '.$searchBy['campaign'];
            }
            if (!empty($searchBy['telemarketer'])){
                $customWhere .=  ' AND u.id='.$searchBy['telemarketer'];
            }
            //call_disposition_history table dependent
            if (!empty($searchBy['calldisposition_name']) && $searchBy['calldisposition_name'] != 'ALL') {
               // $call_dispositions = implode(",", $searchBy['calldisposition_name']);
                $customWhere .= " AND ch.call_disposition_id = " . $searchBy['calldisposition_name'];
            }
        }

        //Date Validation
        $dtRange = "1 = 1 ";
        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            //$customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
            //$dtRange .= ' AND ( date_format(subch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
            $startDate = $searchBy['start_date'] . " 00:00:00";
            $dtRange .= " AND subch.created_at >=  '{$startDate}'";
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            //$customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
            //$dtRange .= ' AND ( date_format(subch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
            $endDate = $searchBy['end_date'] . " 23:59:59";
            $dtRange .= " AND subch.created_at <=  '{$endDate}'";
        }
        else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
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
            $start_date .= " 00:00:00";
            $end_date .= " 23:59:59";
           // $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
           // $dtRange .= ' AND date_format(subch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
            $dtRange .= " AND subch.created_at BETWEEN '{$start_date}' and '{$end_date}' ";
        } else {
            //$customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") = CURDATE()';
            //$dtRange .= ' AND date_format(subch.created_at,"%Y-%m-%d") = CURDATE()';
            $startDate = date("Y-m-d 00:00:00");
            $endDate = date("Y-m-d 23:59:59");
            $dtRange .= " AND subch.created_at BETWEEN '{$startDate}' and  '{$endDate}' ";
        }
        if($this->session->userdata('user_type') == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$this->session->userdata('uid').' OR u.parent_id = '.$this->session->userdata('uid').')';
        }
        if ($report) {
            $select = "c.id AS eg_campaign_id,c.name AS campaign_name,c.type AS campaign_type,CONCAT(cnt.first_name,' ',cnt.last_name) AS full_name,cnt.company,";
            $select .= "cnt.email AS contact_email,ch.number_dialed AS phone,ch.ch_created_at AS call_created_at,CONCAT(u.first_name, ' ', u.last_name) AS agent_name,ch.call_disposition_id,";
            $select .= "CONCAT(uq.first_name, ' ', uq.last_name) AS qa_name, IF(lh.status IS NULL,IF(ch.call_disposition_id > 0,'In Progress',NULL),lh.status) AS Status, ";
            $select .= "n.note as notes, ch.recording_url,ch.call_start_datetime, ch.call_end_datetime ";
        }else{
            $select = "ch.call_disposition_id,ch.campaign_contact_id,ch.call_history_id  AS plivo_id,ch.ch_created_at AS call_created_at,ch.recording_url,ch.conf_sid,ch.sid,ch.call_start_datetime, ";
            $select .= "CONCAT(cnt.first_name,' ',cnt.last_name) AS full_name,ch.call_end_datetime,c.id AS eg_campaign_id ,c.type AS campaign_type,cc.notes,cnt.id AS contact_id, ";
            $select .= "n.note as notes, CONCAT(u.first_name, ' ', u.last_name) AS agent_name,cnt.email AS contact_email,cnt.company,CONCAT(uq.first_name, ' ', uq.last_name) AS qa_name, ";
            $select .= "ch.number_dialed AS phone, c.eg_campaign_id AS campaign_id, c.name AS campaign_name, ";
            $select .= "IF(lh.status IS NULL,IF(ch.call_disposition_id > 0,'In Progress',NULL),lh.status) AS Status ";
        }
        
        $call_recording_sql = "SELECT {$select} ";
        $call_recording_sql .= "FROM ( SELECT cdh.call_disposition_id,subch.conf_sid,subch.campaign_id,subch.user_id,subch.contact_id,subch.campaign_contact_id,subch.recording_url,subch.sid,subch.call_start_datetime,subch.call_end_datetime, subch.id as call_history_id, subch.created_at as ch_created_at, subch.number_dialed ";
        $call_recording_sql .= "FROM call_history subch USE INDEX (ID , IDX_CREATED_AT) ";
        $call_recording_sql .= "JOIN `call_disposition_history` cdh USE INDEX (CALL_HISTORY_ID , CAMPAIGN_CONTACT_ID) ON subch.id = cdh.call_history_id AND cdh.campaign_contact_id = subch.campaign_contact_id ";
        $call_recording_sql .= "WHERE " . $dtRange . " AND subch.module_type = 'tm'  ) ch "; 
       // $call_recording_sql .= "JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id ";
        /*$call_recording_sql .= "JOIN contacts cnt ON ch.contact_id = cnt.id ";
        $call_recording_sql .= "JOIN `campaign_contacts` cc ON ch.campaign_contact_id = cc.id ";
        $call_recording_sql .= "JOIN users u ON ch.user_id = u.id ";
        $call_recording_sql .= "JOIN `campaigns` c ON c.id = ch.campaign_id ";
        $call_recording_sql .= "LEFT JOIN `notes` n ON ch.call_history_id = n.call_history_id ";
        $call_recording_sql .= "LEFT JOIN `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id ";
        $call_recording_sql .= "LEFT JOIN users uq ON lh.qa = uq.id ";
        $call_recording_sql .= "WHERE " . $customWhere . " ";*/
        $call_recording_sql .= "INNER JOIN
                            users u ON ch.user_id = u.id
                                INNER JOIN
                            `campaigns` c ON c.id = ch.campaign_id
                                INNER JOIN
                            contacts cnt ON ch.contact_id = cnt.id
                                INNER JOIN
                            `campaign_contacts` cc ON ch.campaign_contact_id = cc.id
                                LEFT JOIN
                            `notes` n ON ch.call_history_id = n.call_history_id
                                LEFT JOIN
                            `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id
                                LEFT JOIN
                            users uq ON lh.qa = uq.id";
        
        if($this->session->userdata('user_type') == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$this->session->userdata('uid').' OR u.parent_id = '.$this->session->userdata('uid').') ';
        }
        
        if ($logged_user_type == 'manager') {
            $customWhere .= " AND (u.telemarketing_offices = '" . $logged_tm_office . "' ";
            
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');
            
            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR u.telemarketing_offices = '" . $sub_telemarketing_office . "'";
            }
            
            $customWhere .= ')';
        }
        
        $call_recording_sql .= " WHERE " . $customWhere . " ";
        
        $call_recording_sql .= " GROUP BY ch.call_history_id ";
        
        $call_recording_sql .= $sort.' '.$order;

        if($report){
            if ($offset == "" && $limit == ""){
                $query = $this->db->query($call_recording_sql);
            }else{
                $call_recording_sql .= " LIMIT ?,?";
                $query = $this->db->query($call_recording_sql, array($offset, $limit));
            }
            return $query->result();
        }else{
            $call_recording_sql .= " LIMIT ? OFFSET ?";
           // echo $call_recording_sql . $limit . " " . $offset;exit;
            $query = $this->db->query($call_recording_sql, array($limit, $offset));

            return $query->result();
        }
    }
    
    public function call_recording_list_count2($searchBy = "")
    {
        $loggedUserID = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $customWhere = '1=1 ';
        if (!empty($searchBy)) {
            if (!empty($searchBy['status']) && $searchBy['status'] != 'ALL') {
                if($searchBy['status'] == 'Approve'){
                    $is_status_approve = 1;
                }
                
                if($searchBy['status'] == 'In Progress') {
                    $customWhere .= " AND (lh.status = 'In Progress' OR lh.status IS NULL) AND ch.call_disposition_id > 0 ";
                } else {
                    $customWhere .= ' AND lh.`status` = "' . $searchBy['status'] . '"';
                }
            }
            if (!empty($searchBy['qa'])) {
                $customWhere .= ' AND lh.qa = "' . $searchBy['qa'] . '"';
            }
            if (!empty($searchBy['contact_name'])) {
                $customWhere .= ' AND (cnt.first_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR cnt.last_name LIKE "%' . addslashes(trim($searchBy['contact_name'])) . '%" OR CONCAT(cnt.first_name, " ", cnt.last_name) like "%'.addslashes(trim($searchBy['contact_name'])).'%") ';
            }
            if (!empty($searchBy['company'])) {
                $customWhere .= ' AND cnt.company LIKE "%' . $searchBy['company'] . '%"';
            }
            if (!empty($searchBy['email'])) {
                $customWhere .= ' AND cnt.email LIKE "%' . $searchBy['email'] . '%"';
            }
            if (!empty($searchBy['campaign'])) {
                $customWhere .= ' AND ch.campaign_id = '.$searchBy['campaign'];
            }
            if (!empty($searchBy['telemarketer'])){
                $customWhere .=  ' AND u.id='.$searchBy['telemarketer'];
            }
            //call_disposition_history table dependent
            if (!empty($searchBy['calldisposition_name']) && $searchBy['calldisposition_name'] != 'ALL') {
              //  $call_dispositions = implode(",", $searchBy['calldisposition_name']);
                $customWhere .= " AND ch.call_disposition_id = " . $searchBy['calldisposition_name'];
            }
        }
        
        //Date Validation
        $dtRange = "1 = 1 ";
        if((isset($searchBy['start_date']) && !empty($searchBy['start_date'])) && empty($searchBy['end_date'])){
            //$customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
            //$dtRange .= ' AND ( date_format(subch.created_at,"%Y-%m-%d") >= "'.date('Y-m-d', strtotime($searchBy['start_date'])).'")';
            $startDate = $searchBy['start_date'] . " 00:00:00";
            $dtRange .= " AND subch.created_at >=  '{$startDate}'";
        }else if((isset($searchBy['end_date']) && !empty($searchBy['end_date'])) && empty($searchBy['start_date'])){
            //$customWhere .= ' AND ( date_format(ch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
            //$dtRange .= ' AND ( date_format(subch.created_at,"%Y-%m-%d") <= "'.date('Y-m-d', strtotime($searchBy['end_date'])).'")';
            $endDate = $searchBy['end_date'] . " 23:59:59";
            $dtRange .= " AND subch.created_at <=  '{$endDate}'";
        }
        else if (!empty($searchBy['start_date']) && !empty($searchBy['end_date'])) {
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
            $start_date .= " 00:00:00";
            $end_date .= " 23:59:59";
           // $customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
           // $dtRange .= ' AND date_format(subch.created_at,"%Y-%m-%d") BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
            $dtRange .= " AND subch.created_at BETWEEN '{$start_date}' and '{$end_date}' ";
        } else {
            //$customWhere .= ' AND date_format(ch.created_at,"%Y-%m-%d") = CURDATE()';
            //$dtRange .= ' AND date_format(subch.created_at,"%Y-%m-%d") = CURDATE()';
            $startDate = date("Y-m-d 00:00:00");
            $endDate = date("Y-m-d 23:59:59");
            $dtRange .= " AND subch.created_at BETWEEN '{$startDate}' and  '{$endDate}' ";
        }
        if($this->session->userdata('user_type') == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$this->session->userdata('uid').' OR u.parent_id = '.$this->session->userdata('uid').') ';
        }
        
        if ($logged_user_type == 'manager') {
            $customWhere .= " AND (u.id = '".$loggedUserID."' ";
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "' ";
            
            $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');
            
            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR u.telemarketing_offices = '" . $sub_telemarketing_office . "'";
            }
            
            $customWhere .= ')';
        }
        
        $call_recording_sql = "SELECT count(distinct ch.call_history_id) as count ";
        $call_recording_sql .= "FROM ( SELECT subch.campaign_id,cdh.call_disposition_id,subch.id call_history_id, subch.contact_id, subch.user_id, subch.campaign_contact_id ";
        $call_recording_sql .= "FROM call_history subch ";
        $call_recording_sql .= "JOIN `call_disposition_history` cdh ON subch.id = cdh.call_history_id AND cdh.campaign_contact_id = subch.campaign_contact_id ";
        $call_recording_sql .= "WHERE " . $dtRange . " AND subch.module_type = 'tm'  ) ch  ";
       // $call_recording_sql .= "JOIN `calldispositions` cld ON cdh.call_disposition_id = cld.id ";
        $call_recording_sql .= "INNER JOIN users u ON ch.user_id = u.id ";
        $call_recording_sql .= "INNER JOIN contacts cnt ON ch.contact_id = cnt.id ";
        // $call_recording_sql .= "JOIN `campaign_contacts` cc ON ch.campaign_contact_id = cc.id ";
        $call_recording_sql .= "LEFT JOIN `lead_history` lh ON ch.campaign_contact_id = lh.campaign_contact_id ";
        //$call_recording_sql .= "LEFT JOIN users uq ON lh.qa = uq.id ";
        //$call_recording_sql .= "JOIN `campaigns` c ON c.id = ch.campaign_id ";
        
        if (!in_array($logged_user_type, $this->config->item('upper_management_types'))  && $logged_user_type != 'qa') {
            $customWhere .= " AND u.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        
        $call_recording_sql .= "WHERE " . $customWhere . " ";
        
        $query = $this->db->query($call_recording_sql);
		
        $array=$query->result();
        
        return $array[0]->count;
    }

    public function setEmailHistoryObjectData()
    {
        $emailHistory = new EmailHistoryTable();
        $viewListData = (object)$this->input->post();

        $loggedUserID = $this->session->userdata('uid');

        if(!empty($viewListData->campaign_contact_id)){
            $emailHistory->campaign_contact_id = $viewListData->campaign_contact_id;
        }

        if(!empty($viewListData->resource)){
            $emailHistory->resource_id = $viewListData->resource;
        }

        if (isset($viewListData->resource_name))
            $emailHistory->resource_name = $viewListData->resource_name;

        if (!empty($loggedUserID))
            $emailHistory->user_id = $loggedUserID;

        $emailHistory->created_at = date('Y-m-d H:i:s', time());

        return $emailHistory;
    }

    function insert_sent_email_history($obj)
    {
        $result = $this->db->insert($this->email_history, $obj);

        if ($result) {
            $email_history_id = $this->db->insert_id();
        } else {
            $email_history_id = 0;
        }
        return $email_history_id;
    }

    public function getEmailHistoryList($campaign_contact_id){
        $this->db->select('eh.resource_name,eh.created_at,ccnt.campaign_id, CONCAT(u.first_name," ",u.last_name) AS agent_name');
        $this->db->from('email_history eh');
        $this->db->join('users u','eh.user_id = u.id','LEFT');
        $this->db->join('campaign_contacts ccnt ',' eh.campaign_contact_id = ccnt.id','LEFT');
        $this->db->where('eh.campaign_contact_id',$campaign_contact_id);

        $query = $this->db->get();
        $array = $query->result();

        return $array;

    }

    public function get_next_contact($where, $sort_field, $sort_index,$campaign_contact_id,$list_id)
    {
        $callLimit = $this->config->item('call_limit');

        $session_filter = $this->session->set_contactdata;
        $sql = "SELECT cl.call_disposition_id,cl.id AS contact_list_id,cl.contact_id
                FROM ".$this->campaignContactsTable." cl
                JOIN ".$this->table." c ON c.id = cl.contact_id 
                LEFT JOIN ".$this->members_qa." m ON c.member_id = m.id 
                LEFT JOIN lead_history tlh ON cl.id = tlh.campaign_contact_id
                LEFT JOIN calldispositions cd ON cl.call_disposition_id = cd.id 
                LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country)
                LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone))  ";
        $sql .= " WHERE (locked_by is null or locked_by = '' OR locked_by = {$this->session->userdata('uid')}) and 1 = 1 and cl.list_id = $list_id and
        (dnf.count < 3 OR dnf.count IS NULL) ";


        $sql .= " AND c.do_not_call_ever = 0";
        $sql .=" AND c.original_owner != 'Netwise' ";

        $sql .= " AND (cd.is_workable != 0 OR cd.is_workable IS NULL)
                   AND (tlh.status IN ('','In Progress') OR  tlh.status IS NULL) ";
        if ($where != "") {
            $sql .= " AND " . $where;
        }
        $sql .= " ORDER BY " . $sort_field . " " . $sort_index;
        $query = $this->db->query($sql);
        
        $array = $query->result();
        if (!empty($array)) {
            
            foreach($array as $k => $result) {
                if($result->contact_list_id == $campaign_contact_id) {
                    if(!empty($array[$k+1]->call_disposition_id)) {
                            $return = $array[$k+1];
                    } else {
                        $return = isset($array[$k+1]) ? $array[$k+1] : $array[0];
                    }
                    break;
                }
            }
            if(!isset($return)) {
                $return = $array[0];
            }
        }
        return !empty($return) ? $return : array();

    }
    
    public function get_last_call_id_from_plivo($agent_id,$campaign_contact_id){
        $call_table = ($this->plivo_switch) ? $this->voip_communications : $this->callHistoryTable;
       $sql =  "SELECT id, id as call_history_id FROM {$call_table}
          WHERE user_id = '$agent_id' AND campaign_contact_id = '$campaign_contact_id'
           ORDER BY id DESC LIMIT 1";

        $query = $this->db->query($sql);
        $array = $query->result_array();

        if (!empty($array)) {
            $array = $array[0];
        }
        else
        {
            $sql =  "SELECT id, id as call_history_id FROM {$call_table}
          WHERE user_id = '$agent_id' AND campaign_contact_id = '$campaign_contact_id'
          ORDER BY id DESC LIMIT 1";

            $query = $this->db->query($sql);
            $array = $query->result_array();

            if (!empty($array)) {
                $array = $array[0];
            }
        }
        return $array;
    }
    
    public function getLastCallHistoryId($campaign_contact_id){
        $sql =  "SELECT  call_history_id, call_disposition_id FROM call_history_campaign_contact
          WHERE campaign_contact_id = '{$campaign_contact_id}'
           ORDER BY id DESC LIMIT 1";

        $query = $this->db->query($sql);
        $array = $query->result_array();

        if (!empty($array)) {
            return $array[0];
        }else{
            return null;
        }
    }
        

    public function get_more_notes_by_call($campaign_contact_ids){ // , $agent_id, $call_disposition_id,$campaign_id // DATE(n.created_at) = '".$notesCreatedDate."' AND n.user_id = '".$agent_id."' AND lh.call_disposition_id = '".$call_disposition_id."' AND lh.campaign_id = '".$campaign_id."'
        $sql = "SELECT n.note,u.first_name,u.user_type FROM notes n
               LEFT JOIN users u ON n.user_id = u.id
               JOIN `call_history` ch ON n.call_history_id = ch.id
               WHERE n.campaign_contact_id IN ? ";

        $query = $this->db->query($sql,array(explode(',', $campaign_contact_ids)));

        $array = $query->result_array();
        return $array;
    }

    public function update_plivo_communication_detail($call_history_id, $data)
    {
        $this->db->where_in('id', $call_history_id);
        return $this->db->update($this->callHistoryTable, $data);

    }

    public function update_plivo_recording_url_by_id($id, $recording,$api='plivo')
    {
        $this->db->where('id', $id);
        if($this->plivo_switch){
            return $this->db->update($this->voip_communications,  array('recording_url' => $recording,'rackspace_recording_url' => $recording,'retrieved' => '1'));
        }else{
            if($api == 'twilio'){
                if(is_array($recording)){
                    $set = $recording;
                }else{
                    $set = array(
                        'recording_url' => $recording);
                }
                return $this->db->update($this->callHistoryTable, $set);
            }else{
                return $this->db->update($this->callHistoryTable,  array('recording_url' => $recording,'rackspace_recording_url' => $recording,'retrieved' => '1'));
            }
            
        }
    }

    public function update_contact_id_by_new_diff_person($contact_id,$last_call_history_id){
        $sql = "UPDATE {$this->callHistoryTable} set contact_id='".$contact_id."' WHERE user_id = '".$this->session->userdata('uid')."' AND (contact_id = '' || contact_id = '0') AND id = $last_call_history_id";
        $query = $this->db->query($sql);
        if($query)
            return true;
        else{
            $error = 'ERROR update multiple contact id for add as a diff. person: '. $this->db->last_query();
            return $error;
        }
    }

    public function get_eg_contact_detail($eg_contact_id){
        $this->db2->select('contacts.*,contacts.address1 as address,contacts.company_name as company');
        $this->db2->from('contacts');
        $this->db2->where('id', $eg_contact_id);
        $this->db2->limit(1);
        $query = $this->db2->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    public function get_campaign_contact_detail($campaignContactListID){
        $this->db->select('campaigns.eg_campaign_id,campaigns.name,campaign_contacts.*');
        $this->db->from($this->campaignContactsTable);
        $this->db->join($this->campaignTable,'campaigns.id=campaign_contacts.campaign_id','left');
        $this->db->where('campaign_contacts.id', $campaignContactListID);
        $this->db->limit(1);
        $query = $this->db->get();

        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    /* Dev_NV Region End */

    /* Dev_KR Region Start */

     public function getContact($campaignContactListID)
    {
        $sql = "SELECT c.id,cou.dial_code,'' as member_id,
                IF(m.job_level != '', m.job_level, c.job_level) AS job_level,
                c.job_function,
                c.company,
                IF(m.address1 != '', m.address1, c.address) AS address,
                IF(m.city != '', m.city, c.city) AS city,
                IF(m.zip != '', m.zip, c.zip) AS zip,
                IF(m.state != '', m.state, c.state) AS state,
                IF(m.country != '', m.country, c.country) AS country,
                IF(m.industry != '', m.industry, c.industry) AS industry,
                IF(m.company_size != '', m.company_size , c.company_size) AS company_size,
                IF(m.phone != '', m.phone, c.phone) AS phone,
                IF(m.ext != '', m.ext, c.ext) AS ext,
                c.notes,
                c.time_zone,
                c.edit_lead_status,
                c.locked_by,
                c.created_at ,
                c.updated_at,
                c.company_revenue,
                ccnt.id AS campaign_contact_id, 
                ccnt.campaign_id AS campaign_id,
                ccnt.source AS source,ccnt.resource_id,
                cs.custom_question_value AS custom_question_value,
                cs.eg_campaign_id,
                cs.name,
                cs.type AS campaign_type,
                cs.site_id,
                cs.script_main AS script_main,
                ccnt.lifted,
                ccnt.last_follow_up_date,
                cs.script_alt AS script_alt, ccnt.source,'Pureb2b' as original_owner,(select user_type from users where id=ccnt.created_by) as contact_created_usertype, ccnt.created_at,(select concat(first_name,' ',last_name) from users where id=ccnt.created_by) as contact_created_by
                ,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,
                (SELECT list_name FROM campaign_lists cl JOIN campaign_contact_changes ccc ON cl.id = ccc.from_list_id WHERE campaign_contact_id = ccnt.id ORDER BY ccc.id DESC limit 1) as `original_list`,
                CASE cs.site_name 
                WHEN 'Enterprise Guide' THEN 'EG' 
                WHEN 'Marketing Solutions Insider' THEN 'MSI' 
                WHEN 'Smart Tech Resource' THEN 'STR' 
                WHEN 'Tech Product Insider' THEN 'TPI' 
                WHEN 'Technology Buyer\'s Guide' THEN 'TBG' 
                WHEN 'Technology Resource Insider' THEN 'TRI' 
                WHEN 'PureB2B' THEN 'PB2B' 
                WHEN 'Spiralytics' THEN 'SNA' END AS site_name, 0 as lead_id
                    FROM {$this->campaignContactsTable} ccnt
                    LEFT JOIN  {$this->table}  c ON c.id = ccnt.contact_id
                    LEFT JOIN `countries` cou ON c.country = cou.country_code
                    LEFT JOIN {$this->members_qa} m ON m.id = c.member_id
                    LEFT JOIN {$this->campaignTable} cs ON cs.id = ccnt.campaign_id 
                    LEFT JOIN {$this->campaign_tm_offices} cto ON cs.id = cto.campaign_id";
        $sql .= ' WHERE  ccnt.id  = ? ';
        $sql .= ' LIMIT 1 ';
        
        $query = $this->db->query($sql,array($campaignContactListID));

        $array = $query->result();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    public function getContactByContactAndCampaign($contactId, $campaignId)
    {
        $fields = "c.id,
                c.locked_by,
                c.member_id,
                cou.dial_code,
                IF(m.email != '', m.email, c.email) AS email,
                IF(m.first_name != '',
                    m.first_name,
                    c.first_name) AS first_name,
                IF(m.last_name != '',
                    m.last_name,
                    c.last_name) AS last_name,
                IF(m.job_title != '',
                    m.job_title,
                    c.job_title) AS job_title,
                IF(m.job_level != '',
                    m.job_level,
                    c.job_level) AS job_level,
                c.job_function,
                IF(m.company_name != '',
                    m.company_name,
                    c.company) AS company,
                IF(m.address1 != '',
                    m.address1,
                    c.address) AS address,
                IF(m.city != '', m.city, c.city) AS city,
                IF(m.zip != '', m.zip, c.zip) AS zip,
                IF(m.state != '', m.state, c.state) AS state,
                IF(m.country != '',
                    m.country,
                    c.country) AS country,
                IF(m.industry != '',
                    m.industry,
                    c.industry) AS industry,
                IF(m.company_size != '',
                    m.company_size,
                    c.company_size) AS company_size,
                IF(m.company_revenue != '',
                    m.company_revenue,
                    c.company_revenue) AS company_revenue,
                IF(m.phone != '', m.phone, c.phone) AS phone,
                IF(m.ext != '', m.ext, c.ext) AS ext,
                c.alternate_no,
                c.notes,
                c.time_zone,
                c.edit_lead_status,
                c.locked_by,
                c.created_at,
                c.updated_at,
                ccnt.id AS campaign_contact_id,
                ccnt.campaign_id AS campaign_id,
                ccnt.source AS source,
                ccnt.resource_id,
                c.original_owner,
                ccnt.source,
                ccnt.created_at,
                (SELECT 
                        CONCAT(first_name, ' ', last_name)
                    FROM
                        users
                    WHERE
                        id = ccnt.created_by) AS contact_created_by,
                (SELECT 
                        user_type
                    FROM
                        users
                    WHERE
                        id = ccnt.created_by) AS contact_created_usertype,
                cs.custom_question_value AS custom_question_value,
                cs.site_id,
                cs.eg_campaign_id,
                cs.name,
                cs.type AS campaign_type,
                cs.script_main AS script_main,
                cs.script_alt AS script_alt,
                tlh.agent_id,
                tlh.id as lead_id,
                tlh.resource_id AS tlh_resource_id,
                tlh.resource_name AS resource_name,
                ccnt.call_disposition_id,
                ccnt.call_disposition_update_date,
                tlh.status AS `status`,
                tlh.qa,
                tlh.is_qa_in_progress,
                ccnt.reference_link,
                tlh.email_resource_sent,
                tlh.created_at AS `first_qa_date`,
                GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,
                CASE cs.site_name
                    WHEN 'Enterprise Guide' THEN 'EG'
                    WHEN 'Marketing Solutions Insider' THEN 'MSI'
                    WHEN 'Smart Tech Resource' THEN 'STR'
                    WHEN 'Tech Product Insider' THEN 'TPI'
                    WHEN 'Technology Buyer\'s Guide' THEN 'TBG'
                    WHEN 'Technology Resource Insider' THEN 'TRI'
                    WHEN 'PureB2B' THEN 'PB2B'
                    WHEN 'Spiralytics' THEN 'SNA'
                END AS site_name";
        
        $sql = "SELECT {$fields} FROM {$this->campaignContactsTable} ccnt 
                    LEFT JOIN {$this->table} c ON c.id = ccnt.contact_id
                    LEFT JOIN `countries` cou ON c.country = cou.country_code
                    LEFT JOIN {$this->members_qa} m ON m.id = c.member_id
                    INNER JOIN {$this->campaignTable} cs ON cs.id = ccnt.campaign_id
                    LEFT JOIN {$this->campaign_tm_offices} cto ON cs.id = cto.campaign_id
                    LEFT JOIN {$this->tmLeadHistoryTable} tlh ON ccnt.id = tlh.campaign_contact_id";
        $sql .= ' WHERE  c.id  = ? AND cs.id = ?  order by tlh.updated_at desc';
        $sql .= ' LIMIT 1 ';
        
        $query = $this->db->query($sql,array($contactId, $campaignId));

        $array = $query->result();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    public function getPlivoRecLink($campaingContactID)
    {
        $call_table = ($this->plivo_switch) ? $this->voip_communications : $this->callHistoryTable;
        $sql = "SELECT recording_url FROM {$call_table}
          WHERE campaign_contact_id = '$campaingContactID' ORDER BY id DESC LIMIT 1";

        $query = $this->db->query($sql);
        $array = $query->result_array();

        if (!empty($array)) {
            return $array = $array[0]['recording_url'];
        }else{
            return false;
        }
        
    }
    /* Dev_KR Region End */

   /* Dev_DP Region Start */
    public function get_inbound_calls()
    {
        $call_table = ($this->plivo_switch) ? $this->voip_communications : $this->callHistoryTable;
        $sql = "SELECT pl.id,pl.target,pl.channel,pl.created_at,pl.recording_url,pl.duration
                FROM 
                {$call_table} pl
                WHERE (pl.recording_url != '' || pl.recording_url != null)
                AND pl.direction = 1";

        $sql .= " ORDER BY pl.id DESC ";
        
        $query = $this->db->query($sql);
        $array = $query->result_array();
        return $array;      
    }
    
    public function getListID($contact_id,$campaign_id)
    {
        $sql = "SELECT list_id FROM  campaign_contacts WHERE  id = '$contact_id' AND campaign_id = '$campaign_id' ";
        $query = $this->db->query($sql);
        $array = $query->result_array();
        $list_id = $array[0]['list_id'];
        return $list_id;      
    }

    /* Dev_DP Region End */
    
    public function get_call_dispositions(){
        $sql = "select id,calldisposition_name as name from calldispositions";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function insert_csv_events($insert_string) {

        $sql = "INSERT IGNORE INTO " . $this->table . " (title,startdate,enddate,event_location,event_info) VALUES %s";
        $sql = sprintf($sql, implode(",", $insert_string));

        try {
            $result = $this->db->query($sql);
            $list_id = 1;
            //echo('\n\ra batch of 1000 records was inserted.');
        } catch (Exception $e) {
            $list_id = 0;
}
        return $list_id;
    }

    //When any lead submit that change flag of workable
    function updateLeadWorkable($campaign_contact_id, $isWorkable = true){

        $table = $this->campaignContactsTable;

        $workableStatus = $isWorkable ? 'W' : 'NW';

        $data = array('workable_status'=>$workableStatus);
        $this->db->where('id', $campaign_contact_id);
        $status = $this->db->update($table, $data);
        return $status;
    }

    /**
     * Update campaign_contacts table
     * Insert notes
     * Insert Lead status record
     * @param type $post_data
     */
    function updateCampaignContact($post_data) {

        $campaign_contact_id = $post_data['campaign_contact_id'];

        $cc = array();
        $timestamp = date('Y-m-d H:i:s', time());
        $cc['updated_at'] = $timestamp;
        $cc['notes'] = $notes = trim($post_data['notes']);
        $cc['resource_id'] = $post_data['resource_id'];
        if(!empty($_POST['isLifted']) && $_POST['isLifted'] == 1){
            $cc['lifted'] = 0;
        }
        if(!empty($post_data['call_disposition'])){
            $cc['call_disposition_id'] = $post_data['call_disposition'];

            if ($post_data['call_disposition'] == '2' && !empty($post_data['call_disposition_update_date'])) {
                $cc['call_disposition_update_date'] = date('Y-m-d H:i:s', strtotime($post_data['call_disposition_update_date']));
            } else {
                $cc['call_disposition_update_date'] = NULL;
            }
        }
        if (isset($post_data['reference_link'])) {
            $cc['reference_link'] = $post_data['reference_link'];
        }

        if(!empty($post_data['callLimitReached'])){
            $cc['last_follow_up_date'] = $timestamp;
        }

        $this->db->where('id', $campaign_contact_id);
        $status = $this->db->update($this->campaignContactsTable, $cc);

        $loggedUserID = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');

        $last_call_history_id = "";
        if(isset($post_data['last_call_history_id']) && !empty($post_data['last_call_history_id'])){
            $last_call_history_id = $post_data['last_call_history_id'];
            $this->check_call_history_exist($userType, $last_call_history_id);
        }

        $lead_status = null;
        $allowedCallDispositionCreateLeadArray = array('1');
        $allowedCreateLeadStatus = false;
        if(!empty($post_data['call_disposition'])){
            $allowedCreateLeadStatus = in_array($post_data['call_disposition'],$allowedCallDispositionCreateLeadArray);
        }

        if($userType != 'qa'){
            if (!$allowedCreateLeadStatus) {
                $lead_status = 'In Progress';
            }else{
                $lead_status = 'Pending';
            }
        }
        if($userType != 'agent'){
            if(isset($post_data['decision']) && $post_data['decision'] == 'Approve'){
                $lead_status = 'Approve';
            }else if(isset($post_data['decision']) && $post_data['decision'] == 'Follow Up'){
                $lead_status = 'Follow-up';
            }else if(isset($post_data['decision']) && $post_data['decision'] == 'Update and Submit'){
                $lead_status = 'Pending';
            }else if(isset($post_data['decision']) && $post_data['decision'] == 'Reject'){
                $lead_status = 'Reject';
            }else if (isset($post_data['decision']) && $post_data['decision'] == 'Duplicate Lead') {
                $lead_status = 'Duplicate Lead';
            }
        }

        $lead_status_id = '0';
        $lead_status_sql = $this->db->insert('lead_status', array('campaign_contact_id' => $campaign_contact_id, 'user_id' => $loggedUserID, 'status' => $lead_status, 'created_at' => $timestamp)); // , 'user_id' => $uid
        if (!$lead_status_sql) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, lead status was not created.');
            redirect('/dialer/contacts/index/' . $post_data['campaign_id']);
        } else {
            $lead_status_id = $this->db->insert_id();
        }
        if (!empty($notes)) {
//            $notes_sql = $this->db->insert('notes', array('campaign_contact_id' => $campaign_contact_id, 'lead_status_id' => $lead_status_id, 'call_history_id' => $last_call_history_id, 'note' => $notes, 'user_id' => $loggedUserID, 'created_at' => $timestamp)); // , 'user_id' => $uid
            $notes_sql = $this->db->insert('notes', array('campaign_contact_id' => $campaign_contact_id, 
                'lead_status_id' => $lead_status_id, 'call_history_id' => $last_call_history_id, 
                'note' => $notes, 'user_id' => $loggedUserID, 'created_at' => $timestamp,'member_id' => $post_data['member_id'],
                    'campaign_id' => $post_data['campaign_id']));
            if (!$notes_sql) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, notes were not created.');
                    redirect('/dialer/contacts/index/' . $post_data['campaign_id']);
            }
        }
    }

    /**
     * @param $userType
     * @param $last_call_history_id
     */
    public function check_call_history_exist($userType, $last_call_history_id)
    {
        if ($userType != 'qa') {
            if (!empty($last_call_history_id)) {
                $multi_call_id = (isset($_POST['all_call_history_id']) && $_POST['all_call_history_id'] !='') ? explode(',', $_POST['all_call_history_id']) : "";
                // When agent call for more than one time at single time open any contact so update thier call_history records for that contact records
                if(count($multi_call_id) > 1){
                    $all_call_start_datetime = (isset($_POST['all_call_start_datetime']) && $_POST['all_call_start_datetime'] !='') ? explode(',', $_POST['all_call_start_datetime']) : "";
                    $all_call_end_datetime = (isset($_POST['all_call_end_datetime']) && $_POST['all_call_end_datetime'] !='') ? explode(',', $_POST['all_call_end_datetime']) : "";
                    //echo "<pre>"; print_r($_POST); print_r($multi_call_id); print_r($all_call_end_datetime); print_r($all_call_start_datetime); echo "</pre>";  exit;
                    $callHistoryDetail = new stdClass();
                    for($i=0;$i<count($multi_call_id);$i++){
                        $callHistoryDetail->id = $multi_call_id[$i];
                        $callHistoryDetail->call_start_datetime = $all_call_start_datetime[$i];
                        $callHistoryDetail->call_end_datetime = $all_call_end_datetime[$i];
                        $interval = date_diff(new DateTime($callHistoryDetail->call_start_datetime), new DateTime($callHistoryDetail->call_end_datetime));
                        $total = $interval->format('%h:%i:%s');
                        $callHistoryDetail->count_flag = (strtotime($total) > strtotime('00:00:15')) ? 1 : 0;
                        if(isset($_POST['new_added_contact_id']) && $_POST['new_added_contact_id'] != '') {
                            $callHistoryDetail->contact_id = $_POST['new_added_contact_id'];
                        }
                        $callHistoryDetail = $this->unset_nulls($callHistoryDetail);
                        $this->updateAgentCallHistory($callHistoryDetail);
                    }
                }else{
                $check_call_history_exist = $this->get_call_history_data($last_call_history_id);
                    if (!empty($check_call_history_exist) && (empty($check_call_history_exist->call_end_datetime) || $_POST['is_add_page']=1)){
                    //Set Call History object properties
                    $callHistoryDetail = new stdClass();
                    $callHistoryDetail->id = $last_call_history_id;
                    //$callHistoryDetail->call_start_datetime = $_POST['call_start_datetime'];
                    //$callHistoryDetail->call_end_datetime = $_POST['call_end_datetime'];
                    //$callHistoryDetail->count_flag = $_POST['count_flag'];
                        if(isset($_POST['new_added_contact_id']) && $_POST['new_added_contact_id'] != '') {
                            $callHistoryDetail->contact_id = $_POST['new_added_contact_id'];
                        }
                    $callHistoryDetail = $this->unset_nulls($callHistoryDetail);
                    $this->updateAgentCallHistory($callHistoryDetail);
                    // update contact id for multiple call after submit lead with add as a diff. person and update based on campaign & logged user
                        //if(isset($_POST['new_added_contact_id']) && $_POST['new_added_contact_id'] != '') {
                            //$callsModel->update_contact_id_by_new_diff_person($_POST['new_added_contact_id'], $last_call_history_id);
                        //}
                    }
}
            }
        }
    }
    
    function fetch_plivo_com_record($campaign_contact_id, $fields = 'id'){
        $call_table = $this->callHistoryTable;
       $sql = "SELECT {$fields} FROM {$call_table} WHERE campaign_contact_id = '{$campaign_contact_id}' order by id desc limit 1";
        $query = $this->db->query($sql);
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    function copy_plivo_com_record($new_campaign_contact_id,$call_history_id,$plivo_id){
       $sql = "insert into {$this->voip_communications} (direction,target,channel,agent_id,sid,duration,call_start_datetime,call_end_datetime,cost,hangup_cause,recording_url,rackspace_recording_url,created_at,updated_at,dialer_mode,call_history_id,campaign_contact_id,business,retrieved)
       (select direction,target,channel,agent_id,sid,duration,call_start_datetime,call_end_datetime,cost,hangup_cause,recording_url,rackspace_recording_url,created_at,updated_at,dialer_mode,{$call_history_id},{$new_campaign_contact_id},business,retrieved from {$this->voip_communications} where id = {$plivo_id})";
        return $this->db->query($sql);
    }
    
    function copy_call_history_record($new_contact_id, $call_history_id, $newCampaignContactId){
       $sql = "insert into {$this->callHistoryTable} (contact_id,campaign_id,number_dialed,user_id,call_start_datetime,call_end_datetime,call_duration,ip,created_at,count_flag,module_type,direction,target,channel,agent_id,sid,duration,cost,hangup_cause,recording_url,rackspace_recording_url,updated_at,dialer_mode,campaign_contact_id,business,retrieved,add_diff) ";
       $sql .= "(select {$new_contact_id},campaign_id,number_dialed,user_id,call_start_datetime,call_end_datetime,call_duration,ip,created_at,count_flag,module_type,direction,target,channel,agent_id,sid,duration,cost,hangup_cause,recording_url,rackspace_recording_url,updated_at,dialer_mode,{$newCampaignContactId},business,retrieved,1 from {$this->callHistoryTable} where id = {$call_history_id})";
       $this->db->query($sql);
       return $this->db->insert_id();
    }
    
    function copyCallHistoryCampaignContact($callHistoryId, $newCallHistoryId, $newCampaignContactId){
        $callDisposition = !empty($_POST['call_disposition']) ? $_POST['call_disposition'] : 0;
        $sql = "INSERT INTO call_history_campaign_contact
          (`campaign_contact_id`, `call_history_id`, `count_flag`, `call_disposition_id`,`created_at`,`updated_at`) ";
        $sql .= "(select {$newCampaignContactId},{$newCallHistoryId},count_flag, {$callDisposition}, NOW(), NOW() from call_history_campaign_contact where call_history_id = {$callHistoryId} order by id desc limit 1)";
       $this->db->query($sql);
       return $this->db->insert_id();
    }

    public function getEmailTemplates($campaign_id) {
        $query = $this->db->query("SELECT id,template_name FROM email_templates WHERE campaign_id ='$campaign_id'");
        $array = $query->result();
        return $array;        
    }
    
    function _update_agent_submitted_lead_status($uber_lead_history_id, $eg_member_id='', $email='') {
        $set_eg_member_id = !empty($eg_member_id) ? ', member_id='.$eg_member_id : '';
        $set_email = !empty($email) ? ", email='{$email}'" : '';
        try {
            $query1 = $this->db->query("SELECT `status` FROM lead_history WHERE id = ".$uber_lead_history_id);
            $result1 = $query1->result();
            return $this->db2->query("UPDATE agent_submitted_leads SET `status` = '" . $result1[0]->status . "' " . $set_eg_member_id . $set_email ." WHERE uber_lead_history_id = " . $uber_lead_history_id);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }


    function unset_nulls($obj)
    {
        foreach ($obj as $key => $value) {
            if ($value == NULL && !is_int($value)) {
                unset($obj->$key);
            }
        }
        return $obj;
    }
    
    function updateCampaignContactsInfo($campaign_contact_id, $cc) 
    {
        $this->db->where('id', $campaign_contact_id);
        $res = $this->db->update($this->campaignContactsTable, $cc);
        
        return $res;
    }
        
	public function getCallDispositionsByModule($module = 'tm', $is_workable = "", $flds = '*') 
    {
    	$sql = "SELECT " . $flds . " FROM $this->callDispositionsTable ";
    	$sql .= "WHERE module like '%" . $module. "%' ";
    
    	if ($is_workable != '') {
        		$sql .= "AND is_workable = " . $is_workable . " ";
    	}
    
    	$sql .= "ORDER BY calldisposition_name asc";
   
    	$query = $this->db->query($sql);

    	$array = $query->result();
    	return $array; 
	}

    public function isLockedBy($id)
    {
        $contactTable = $this->table;

        $sql = "SELECT locked_by FROM {$contactTable} WHERE id = ?";

        $query = $this->db->query($sql, $id);

        return $query->result()[0]->locked_by;
    }

    public function getCallHistoryByCampaignContactId($campaignContactId, $fields = "ch.id,ch.call_disposition_id")
    {
       
        $sql = "SELECT 
                {$fields}
            FROM
                call_history_campaign_contact ch
            WHERE
                ch.campaign_contact_id = ?
            ORDER BY ch.id DESC
            LIMIT 1";
        $query = $this->db->query($sql,array($campaignContactId));

        $array = $query->result();

    	return $array;
    }

    public function isPhoneReachedLimit($phone, $limit)
    {
        $sql = "SELECT `count` FROM dialed_numbers_fortheday WHERE phone = ?";

        $query = $this->db->query($sql, $phone);

        return !empty($query->result()) && $query->result()[0]->count >= $limit ? true : false;
    }
}

class CallHistoryTable
{
    public $contact_id;
    public $campaign_id;
    public $number_dialed;
    public $user_id;
    public $call_start_datetime;
    public $call_end_datetime;
    public $ip;
    public $created_at;
    public $count_flag;
}

class EmailHistoryTable
{
    public $campaign_contact_id;
    public $resource_id;
    public $resource_name;
    public $user_id;
    public $sparkpost_message_id;
    public $send_result;
    public $created_at;
}

?>
