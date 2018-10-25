<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Campaigns_model extends CI_Model
{
    public $table = 'campaigns';
    public $userTable = 'users';
    public $campaignContactsMappingTable = 'campaign_contacts';
    public $agentSessionTable = 'agent_sessions';
    public $campaign_assign_tl = 'campaign_assign_tl';
    public $campaign_tm_offices = 'campaign_tm_offices';
    public $campaign_lists = 'campaign_lists';

    public $auto_live_agents = 'auto_live_agents';
    public $auto_live_agents_logs = 'auto_live_agents_logs';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //setting the second parameter to TRUE (Boolean) the function will return the database object.
        //$this->db2 = $this->load->database('db2', TRUE);
    }

    function checkCampaignNameExist($campaignName, $campaignID = 0)
    {
        $sql = "SELECT id, name FROM {$this->table} WHERE name = ? ";
        if ($campaignID) {
            $sql .= ' AND id != '.$campaignID;
        }
        $query = $this->db->query($sql, array($campaignName));
        return $query->result_array();
    }

    public function insertCampaignDetail($campaignArray)
    {
        $result = $this->db->insert($this->table, $campaignArray);

        if ($result) {
            $campaign_id = $this->db->insert_id();
        } else {
            $campaign_id = 0;
        }
        return $campaign_id;
    }

    function getCampaignNumberRecord($status="")
    {
        $sql = "SELECT count(*) as count FROM {$this->table} c";
        if($status!="")
        {
                if($status=="completed")
                {
                        $lead_table = 'lead_history';
                    
                        $sql .= " LEFT JOIN $lead_table lh ON lh.campaign_id = c.id  ";
                        $sql .= ' WHERE ';
                        $sql .= " c.status = '" . $status . "'";
                }
                else
                {
                    $loggedInUserType = $this->session->userdata('user_type');
                    $loggedInUserID = $this->session->userdata('uid');
                    $statusActive = 'active';
                    $statusCompleted = 'completed';
                    $statusPending = 'pending';
                    if ($loggedInUserType == 'agent') {
                    $sql .= ' LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id
                    LEFT JOIN `campaign_assign` ca ON ca.teamleader_id = cl.user_id  AND ca.campaign_id = cl.campaign_id
                    WHERE ca.agent_id = '.$loggedInUserID.' AND ';
                    } else if ($loggedInUserType == 'team_leader') {
                        $sql .= " LEFT JOIN `campaign_assign_tl` ca ON ca.campaign_id = c.id ";
                        $sql .= " WHERE ca.user_id = '" . $loggedInUserID . "' AND ";
                    } else {
                            $sql .= ' WHERE ';
                    }
                    if($loggedInUserType == 'manager'){
                            $sql .= " c.status != '".$statusCompleted."'";
                    }else{
                            $sql .= " (c.status = '".$statusActive."' OR c.status = '".$statusPending."')";
                    }

                }
                $sql .= " and c.business = '{$this->app}' ";
        }else{
            $sql .= " Where c.business = '{$this->app}' ";
        }
		
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result[0]->count;
    }

    function getCampaignList($limit = "", $offset = "",$module_type="")
    {
        $loggedInUserType = $this->session->userdata('user_type');
        $loggedInUserID = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $statusActive = 'active';
        $statusCompleted = 'completed';
        $statusPending = 'pending';

        
        
        if ($loggedInUserType == 'agent') {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'Sign Out' ELSE 'Sign In' END) AS AgentSignInOut,"
                . "(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'out' ELSE 'in' END) AS AgentSignInOutValue "
                . " FROM {$this->table} c LEFT JOIN " . $this->agentSessionTable . " ag ON ag.campaign_id = c.id  and ag.id IN (SELECT MAX(id) FROM agent_sessions WHERE user_id = $loggedInUserID GROUP BY campaign_id) "
                . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        
            $sql .= ' LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id
                    LEFT JOIN `campaign_assign` ca ON ca.teamleader_id = cl.user_id  AND ca.campaign_id = cl.campaign_id
                    WHERE ca.agent_id = '.$loggedInUserID.' AND ';
        } else if ($loggedInUserType == 'team_leader') {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,COUNT(cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads,(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'Sign Out' ELSE 'Sign In' END) AS AgentSignInOut,"
            . "(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'out' ELSE 'in' END) AS AgentSignInOutValue "
            . " FROM {$this->table} c LEFT JOIN " . $this->agentSessionTable . " ag ON ag.campaign_id = c.id  and ag.id IN (SELECT MAX(id) FROM agent_sessions WHERE user_id = $loggedInUserID GROUP BY campaign_id) LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $sql .= " LEFT JOIN `campaign_assign_tl` ca ON ca.campaign_id = c.id ";
            $sql .= " WHERE ca.user_id = '" . $loggedInUserID . "' AND ";
        } else {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,COUNT(cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads "
            . " FROM {$this->table} c LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $sql .= ' WHERE ';
        }
        if ($loggedInUserType == 'manager' || $loggedInUserType == 'admin') {
            $sql .= " c.status != '" . $statusCompleted . "'";
        } else {
            $sql .= " (c.status = '" . $statusActive . "' OR c.status = '" . $statusPending . "')";
        }
        if ($loggedInUserType != 'admin' && $loggedInUserType != 'qa') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        $sql .= " and c.business = '{$this->app}' ";
        $sql .= " AND c.module_type = '" . $module_type . "' ";
        
        $sql .= " GROUP BY c.id ORDER BY c.id DESC";
        if ($limit != "")
            $sql .= " LIMIT ? OFFSET ?";
		//echo $sql;die();
        $query = $this->db->query($sql, array($limit, $offset));
		
        return $query->result();
    }
    
    
    function getTotalCampaignRecord($searchBy,$status)
    {
        $loggedInUserType = $this->session->userdata('user_type');
        //echo "<pre>"; print_r($_POST); echo "</pre>";
        $sql = "SELECT count(*) as count FROM {$this->table} c LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id";
        $customWhere = $customHaving_tl = $customHaving_a = $customHaving_r = "";
        if (!empty($searchBy['campaign_id'])){
            $customWhere .= " AND c.eg_campaign_id ='".trim($searchBy['campaign_id'])."'";
        }
        if (!empty($searchBy['campaign_name'])){
            $camp_name = addslashes(trim($searchBy['campaign_name']));
            $customWhere .= ' AND c.name LIKE "%'.$camp_name.'%"';
        }
        if (!empty($searchBy['telemarketing_office'])){
            $this->load->model('Offices_model');
            $officeModel = new Offices_model();
            $subOffice = $officeModel->getSubOffices($searchBy['telemarketing_office']);
            $sub_telemarketing_offices = array_column($subOffice,"name");

            $customWhere .= " AND ( cto.tm_office ='".trim($searchBy['telemarketing_office']) . "'";

            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR cto.tm_office = '" . $sub_telemarketing_office . "'";
            }
            $customWhere .= ') ';
        }else{
            $logged_tm_office = $this->session->userdata('telemarketing_offices');
            
            if ($loggedInUserType == 'manager') {
                $customWhere .= " AND (cto.tm_office = '" . $logged_tm_office . "' ";
                $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $customWhere .= " OR cto.tm_office = '" . $sub_telemarketing_office . "'";
                }
                $customWhere .= ') ';
            } else if (!in_array($loggedInUserType, $this->config->item('upper_management_types')) && $loggedInUserType != 'qa') {
                $customWhere .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
            }
        }
        if (!empty($searchBy['campaign_type'])){
            $customWhere .= " AND c.type ='".$searchBy['campaign_type']."'";
        }
        if (!empty($searchBy['start_date'])){
            $customWhere .= " AND c.start_date ='".date('Y-m-d', strtotime($searchBy['start_date']))."'";
        }
        if (!empty($searchBy['end_date'])){
            $customWhere .= " AND c.end_date ='".date('Y-m-d', strtotime($searchBy['end_date']))."'";
        }
        if (!empty($searchBy['ordered'])){
            $customWhere .= " AND c.lead_goal ='".trim($searchBy['ordered'])."'";
        }
        if (!empty($searchBy['lead_today'])){
            //$customWhere .= " AND COUNT(cdh.id) AS  total_Leads ='".$searchBy['lead_today']."'";
            $customHaving_tl .= " COUNT(lh.id) ='".trim($searchBy['lead_today'])."'";
        }
        if (!empty($searchBy['qa_approved'])){
            $customWhere  .= " AND lh.status = 'Approve'";
            $customHaving_a .= " COUNT(lh.status) = ".$searchBy['qa_approved']."";
        }
        if (!empty($searchBy['rejected'])){
            $customWhere  .= " AND lh.status = 'Reject'";
            $customHaving_r .= " COUNT(lh.status) = ".$searchBy['rejected']."";
        }
        
        if (!empty($searchBy['cpl_cpa'])){
            $customWhere .= " AND c.cpl ='".trim($searchBy['cpl_cpa'])."'";
        }
        
        if (!empty($searchBy['status'])){
            $customWhere .= " AND c.`status` ='".trim($searchBy['status'])."'";
        }
        
        if($status!="")
        {
            $lead_table = 'lead_history';
            if($status=="completed")
            {
                $sql .= " LEFT JOIN $lead_table lh ON lh.campaign_id = c.id  ";
                $sql .= ' WHERE ';
                $sql .= " c.status = '" . $status . "'";
            }else{
                $loggedInUserType = $this->session->userdata('user_type');
                $loggedInUserID = $this->session->userdata('uid');
                $statusActive = 'active';
                $statusCompleted = 'completed';
                $statusPending = 'pending';
                                
                $sql .= " LEFT JOIN $lead_table lh ON lh.campaign_id = c.id  ";
                if ($loggedInUserType == 'agent') {
                $sql .= ' LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id
                LEFT JOIN `campaign_assign` ca ON ca.teamleader_id = cl.user_id  AND ca.campaign_id = cl.campaign_id
                WHERE ca.agent_id = '.$loggedInUserID.' AND ';
                } else if ($loggedInUserType == 'team_leader') {
                    $sql .= " LEFT JOIN `campaign_assign_tl` ca ON ca.campaign_id = c.id ";
                    $sql .= " WHERE ca.user_id = '" . $loggedInUserID . "' AND ";
                } else {
                        $sql .= ' WHERE ';
                }
                if($loggedInUserType == 'manager'){
                        $sql .= " c.status != '".$statusCompleted."'";
                }else{
                        $sql .= " (c.status = '".$statusActive."' OR c.status = '".$statusPending."')";
                }

            }
            $sql .= " AND c.module_type = '".$this->app_module_type."' AND c.business = '{$this->app}' ";
           
            $sql .= $customWhere;
            //$sql .= " GROUP BY c.eg_campaign_id $customHaving";
            $sql .= " GROUP BY c.id";   
            if($customHaving_tl !=""){
                $sql .= " HAVING ". $customHaving_tl;
            }elseif($customHaving_tl !='' && ($customHaving_a !='' || $customHaving_r !='')){
                $sql .= " HAVING ". $customHaving_tl. " AND ". $customHaving_a;
            }
        }else{
            $sql .= " Where c.business = '{$this->app}' GROUP BY c.eg_campaign_id";
        }

        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        $result = $query->result();
        return count($result);
    }
    
    function getTotalCampaignList($searchBy="",$limit = "", $offset = "", $order="", $sortField,$module_type)
    {
        $loggedInUserType = $this->session->userdata('user_type');
        $loggedInUserID = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $statusActive = 'active';
        $statusCompleted = 'completed';
        $statusPending = 'pending';
        $customWhere = $customHaving_tl = $customHaving_a = $customHaving_r = "";
        
        if (!empty($searchBy['campaign_id'])){
            $customWhere .= " AND c.eg_campaign_id ='".trim($searchBy['campaign_id'])."'";
        }
        if (!empty($searchBy['campaign_name'])){
            $camp_name = addslashes(trim($searchBy['campaign_name']));
            $customWhere .= ' AND c.name LIKE "%'.$camp_name.'%"';
        }
        if (!empty($searchBy['telemarketing_office'])){
            $this->load->model('Offices_model');
            $officeModel = new Offices_model();
            $subOffice = $officeModel->getSubOffices($searchBy['telemarketing_office']);
            $sub_telemarketing_offices = array_column($subOffice,"name");

            $customWhere .= " AND ( cto.tm_office ='".trim($searchBy['telemarketing_office']) . "'";

            foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                $customWhere .= " OR cto.tm_office = '" . $sub_telemarketing_office . "'";
            }
            $customWhere .= ') ';
        } else {
            if ($loggedInUserType == 'manager' ) {
                $customWhere .= " AND (cto.tm_office = '" . $logged_tm_office . "' ";
                $sub_telemarketing_offices = $this->session->userdata('sub_telemarketing_offices');

                foreach ($sub_telemarketing_offices as $sub_telemarketing_office) {
                    $customWhere .= " OR cto.tm_office = '" . $sub_telemarketing_office . "'";
                }
                $customWhere .= ') ';
            } else if (!in_array($loggedInUserType, $this->config->item('upper_management_types')) && $loggedInUserType != 'qa') {
                $customWhere .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
            }
        }
        if (!empty($searchBy['campaign_type'])){
            $customWhere .= " AND c.type ='".$searchBy['campaign_type']."'";
        }
        if (!empty($searchBy['start_date'])){
            $customWhere .= " AND c.start_date ='".date('Y-m-d', strtotime($searchBy['start_date']))."'";
        }
        if (!empty($searchBy['end_date'])){
            $customWhere .= " AND c.end_date ='".date('Y-m-d', strtotime($searchBy['end_date']))."'";
        }
        if (!empty($searchBy['ordered'])){
            $customWhere .= " AND c.lead_goal ='".trim($searchBy['ordered'])."'";
        }
        if (!empty($searchBy['lead_today'])){
            //$customWhere .= " AND COUNT(cdh.id) AS  total_Leads ='".$searchBy['lead_today']."'";
            $lead_today_set = trim($searchBy['lead_today']);
            $customHaving_tl .= " COUNT(DISTINCT cdh.id) = $lead_today_set";
        }
        if (!empty($searchBy['qa_approved'])){
            $customWhere  .= " AND lh.status = 'Approve'";
            $customHaving_a .= " COUNT(lh.status) = ".$searchBy['qa_approved']."";
        }
        if (!empty($searchBy['rejected'])){
            $customWhere  .= " AND lh.status = 'Reject'";
            $customHaving_r .= " COUNT(lh.status) = ".$searchBy['rejected']."";
        }
        if (!empty($searchBy['cpl_cpa'])){
            $customWhere .= " AND c.cpl ='".trim($searchBy['cpl_cpa'])."'";
        }
        
        if (!empty($searchBy['status'])){
            $customWhere .= " AND c.`status` ='".$searchBy['status']."'";
        }
        
        $sort = "";
        if($sortField){
            switch($sortField){
                case 'ID':  $sort = 'ORDER BY c.id '.$order;  break;
                case 'EGID':  $sort = 'ORDER BY c.eg_campaign_id '.$order;  break;
                case 'Name':  $sort = 'ORDER BY c.`name` '.$order;  break;
                case 'Office':  $sort = 'ORDER BY telemarketing_offices '.$order;  break;
                case 'Start':  $sort = 'ORDER BY c.`start_date` '.$order;  break;
                case 'End':  $sort = 'ORDER BY c.`end_date` '.$order;  break;
                case 'Completion':  $sort = 'ORDER BY c.`completion_date` '.$order;  break;
                case 'Type':  $sort = 'ORDER BY c.type '.$order;  break;
                case 'CPL':  $sort = 'ORDER BY CAST(c.`cpl` AS SIGNED)'.$order;  break;
                case 'Status':  $sort = 'ORDER BY c.`status` '.$order;  break;
            }
        }else{
          $sort = " ORDER BY c.id DESC";   
        }
        
        
        if ($loggedInUserType == 'agent') {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,c.completion_date, c.auto_dial,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'Sign Out' ELSE 'Sign In' END) AS AgentSignInOut,"
                . "(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'out' ELSE 'in' END) AS AgentSignInOutValue "
                . " FROM {$this->table} c LEFT JOIN " . $this->agentSessionTable . " ag ON ag.campaign_id = c.id  and ag.id IN (SELECT MAX(id) FROM agent_sessions WHERE user_id = $loggedInUserID GROUP BY campaign_id) "
                . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        
            $sql .= ' LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id
                    LEFT JOIN `campaign_assign` ca ON ca.teamleader_id = cl.user_id  AND ca.campaign_id = cl.campaign_id
                    WHERE ca.agent_id = '.$loggedInUserID.' AND ';
        } else if ($loggedInUserType == 'team_leader') {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,c.completion_date, c.auto_dial,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,COUNT(DISTINCT cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads,(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'Sign Out' ELSE 'Sign In' END) AS AgentSignInOut,"
            . "(CASE WHEN (ag.user_id = $loggedInUserID AND ag.is_session_deactive = 0)  THEN 'out' ELSE 'in' END) AS AgentSignInOutValue "
            . " FROM {$this->table} c LEFT JOIN " . $this->agentSessionTable . " ag ON ag.campaign_id = c.id  and ag.id IN (SELECT MAX(id) FROM agent_sessions WHERE user_id = $loggedInUserID GROUP BY campaign_id) LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject' OR lh.status = 'Inprogress' OR lh.status = 'Pending') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $sql .= " LEFT JOIN `campaign_assign_tl` ca ON ca.campaign_id = c.id ";
            $sql .= " WHERE ca.user_id = '" . $loggedInUserID . "' AND ";
        } else {
            $sql = "SELECT c.id, c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,c.completion_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.id as campaign_id,COUNT(DISTINCT cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads "
            . " FROM {$this->table} c LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject' OR lh.status = 'Inprogress' OR lh.status = 'Pending') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
            $sql .= ' WHERE ';
        }
        if ($loggedInUserType == 'manager' || in_array($loggedInUserType, $this->config->item('upper_management_types'))) {
            $sql .= " c.status != '" . $statusCompleted . "'";
        } else {
            $sql .= " (c.status = '" . $statusActive . "' OR c.status = '" . $statusPending . "')";
        }
        
        $sql .= " and c.business = '{$this->app}' ";
        $sql .= " AND c.module_type = '" . $module_type . "' ";
        $sql .= $customWhere;
        $sql .= " GROUP BY c.id";

        if(!empty($customHaving_tl) || !empty($customHaving_a) || !empty($customHaving_r)) {
            $sql .= " HAVING";
            if(!empty($customHaving_tl)){
                $sql .= $customHaving_tl;
            }
            if(!empty($customHaving_a)){
                $sql .= " AND ".$customHaving_a;
            }
            if(!empty($customHaving_r)){
                $sql .= " AND ".$customHaving_r;
            }

            $sql = str_replace("HAVING AND ", "HAVING ", $sql);
        }
        
        $sql .= ' '.$sort;
        
        if ($limit != "")
            $sql .= " LIMIT ? OFFSET ?";
        
        $query = $this->db->query($sql, array($limit, $offset));
        //echo $this->db->last_query();
        return $query->result();
    }
    
    function getTotalCampaignListCompleted($searchBy="",$limit = "", $offset = "", $order="",  $sortField, $module_type)
    {
        $customWhere = $customHaving_tl = $customHaving_a = $customHaving_r = "";
        
        if (!empty($searchBy['campaign_id'])){
            $customWhere .= " AND c.eg_campaign_id ='".$searchBy['campaign_id']."'";
        }
        if (!empty($searchBy['campaign_name'])){
           $camp_name = addslashes(trim($searchBy['campaign_name']));
            $customWhere .= ' AND c.name LIKE "%'.$camp_name.'%"';
        }
        if (!empty($searchBy['telemarketing_office'])){
            $customWhere .= " AND cto.tm_office ='".$searchBy['telemarketing_office']."'";
        }
        if (!empty($searchBy['campaign_type'])){
            $customWhere .= " AND c.type ='".$searchBy['campaign_type']."'";
        }
        if (!empty($searchBy['start_date'])){
            $customWhere .= " AND c.start_date ='".date('Y-m-d', strtotime($searchBy['start_date']))."'";
        }
        if (!empty($searchBy['end_date'])){
            $customWhere .= " AND c.end_date ='".date('Y-m-d', strtotime($searchBy['end_date']))."'";
        }
        if (!empty($searchBy['ordered'])){
            $customWhere .= " AND c.lead_goal ='".$searchBy['ordered']."'";
        }
        if (!empty($searchBy['lead_today'])){
            //$customWhere .= " AND COUNT(cdh.id) AS  total_Leads ='".$searchBy['lead_today']."'";
            $lead_today_set = trim($searchBy['lead_today']);
            $customHaving_tl .= " COUNT(DISTINCT cdh.id) = $lead_today_set";
        }
        if (!empty($searchBy['qa_approved'])){
            $customWhere  .= " AND lh.status = 'Approve'";
            $customHaving_a .= " COUNT(lh.status) = ".$searchBy['qa_approved']."";
        }
        if (!empty($searchBy['rejected'])){
            $customWhere  .= " AND lh.status = 'Reject'";
            $customHaving_r .= " COUNT(lh.status) = ".$searchBy['rejected']."";
        }
        if (!empty($searchBy['cpl_cpa'])){
            $customWhere .= " AND c.cpl ='".$searchBy['cpl_cpa']."'";
        }
        
        if (!empty($searchBy['status'])){
            $customWhere .= " AND c.`status` ='".$searchBy['status']."'";
        }
        
        $sort = "";
        if($sortField){
            switch($sortField){
                case 'ID':  $sort = 'ORDER BY c.id '.$order;  break;
                case 'EGID':  $sort = 'ORDER BY c.eg_campaign_id '.$order;  break;
                case 'Name':  $sort = 'ORDER BY c.`name` '.$order;  break;
                case 'Office':  $sort = 'ORDER BY telemarketing_offices '.$order;  break;
                case 'Start':  $sort = 'ORDER BY c.`start_date` '.$order;  break;
                case 'End':  $sort = 'ORDER BY c.`end_date` '.$order;  break;
                case 'Completion':  $sort = 'ORDER BY c.`completion_date` '.$order;  break;
                case 'Type':  $sort = 'ORDER BY c.type '.$order;  break;
                case 'CPL':  $sort = 'ORDER BY CAST(c.`cpl` AS SIGNED) '.$order;  break;
                case 'Status':  $sort = 'ORDER BY c.`status` '.$order;  break;
            }
        }else{
          $sort = " ORDER BY c.id DESC";   
        }
        
        $statusCompleted = 'completed';
        $loggedInUserType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        
        $sql = "SELECT 	c.id,c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,c.completion_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,COUNT(cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads "
            . "FROM {$this->table} c LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        
        $sql .= ' WHERE ';
        if ($loggedInUserType != 'admin') {
            $sql .= " cto.tm_office = '" . $logged_tm_office . "' AND ";
        }
        $sql .= " c.status = '" . $statusCompleted . "'";
        $sql .= " AND c.module_type = '" . $module_type . "' ";
        $sql .= " AND c.business = '{$this->app}'  ";
        $sql .= $customWhere;
        $sql .= " GROUP BY c.id";   
        
        if($customHaving_tl !='' || $customHaving_a !='' || $customHaving_r !=''){
            $sql .= " HAVING ";
        }
        if($customHaving_tl !='' && $customHaving_a !=''){
            $sql .= $customHaving_tl. " AND ". $customHaving_a;
        }elseif($customHaving_tl !='' && $customHaving_r !=''){
            $sql .= $customHaving_tl. " AND ". $customHaving_r;
        }elseif($customHaving_a !='' && $customHaving_r !=''){
            $sql .= $customHaving_a. " AND ". $customHaving_r;
        }elseif($customHaving_tl !=""){ $sql .= $customHaving_tl; }
        elseif($customHaving_a !=""){ $sql .= $customHaving_a; }
        elseif($customHaving_r !=""){ $sql .= $customHaving_r; }
        
        $sql .= ' '.$sort;
        
        if ($limit != "")
            $sql .= " LIMIT ? OFFSET ?";

        $query = $this->db->query($sql, array($limit, $offset));
        //echo $this->db->last_query();
        return $query->result();
    }
    
    function getCampaignListCompleted($limit = "", $offset = "",$module_type="")
    {
        $statusCompleted = 'completed';
        $loggedInUserType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $sql = "SELECT 	c.id,c.id as campaign_id,c.eg_campaign_id,c.name,c.type,c.`status`,c.cpl,c.lead_goal,c.start_date,c.end_date,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,COUNT(cdh.id) AS  total_Leads,COALESCE(SUM(lh.status = 'Approve'), 0) aprroved_leads,COALESCE(SUM(lh.status = 'Reject'), 0) rejected_leads "
            . "FROM {$this->table} c LEFT JOIN lead_history lh ON lh.campaign_id = c.id AND (lh.status = 'Approve' OR lh.status = 'Reject') LEFT JOIN call_disposition_history cdh ON cdh.lead_history_id = lh.id  AND  DATE(cdh.created_at) = CURDATE() AND cdh.`call_disposition_id` = 1 "
            . " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        
        $sql .= ' WHERE ';
        if ($loggedInUserType != 'admin') {
            $sql .= " cto.tm_office = '" . $logged_tm_office . "' AND ";
        }
        $sql .= " c.status = '" . $statusCompleted . "'";
        $sql .= " AND c.module_type = '" . $module_type . "' ";
        $sql .= " AND c.business = '{$this->app}'  ";
        $sql .= " GROUP BY c.id ORDER BY c.id DESC";      
              
        if ($limit != "" && $offset != "")
            $sql .= "LIMIT ? OFFSET ?";

        $query = $this->db->query($sql, array($limit, $offset));

        return $query->result();
    }

    function getTeamLeaderUsersList($active = false,$tm_offices="",$module_value='',$count_array_module_value="")
    {
        $sql = "SELECT id,CONCAT(first_name,' ',last_name) AS first_name FROM users WHERE user_type = 'team_leader' ";
        if(!empty($tm_offices)){
            $sql .= " AND telemarketing_offices IN ($tm_offices)";
        }
        if ($active) {
            $sql .= " AND status = 'active'";
        }

        if(!empty($module_value)){
            if($count_array_module_value < 2)
                $sql .= " AND FIND_IN_SET($module_value,module) "; // IN ($csv_module_value)
        }
        $sql .= "ORDER BY first_name ASC";
        $query = $this->db->query($sql);

        return $query->result();
    }

    // Start HP UAD-3 configure campaign 
    // Start RP UAD-8 : add coulumn c.auto_abandoned_rate,c.auto_time_threshold_one,c.auto_recorded_msg_one,c.auto_time_threshold_two,c.auto_recorded_msg_two
    // Start HP UAD-18 configure campaign
    function getCampaignDetailByID($id)
    {

        $sql = "select c.id, c.eg_campaign_id,c.`name`,c.`status`,c.type,c.cpl,c.lead_goal,c.job_function,c.job_level,c.company_size,c.industries,c.industries,c.country,
            c.custom_questions,c.custom_question_value,c.script_main,c.script_alt,c.created_by,c.created_at,c.updated_at,c.start_date,c.end_date,
            c.assign_team_id,c.modified_by,c.call_filerequest_date,c.tm_launch_date,c.completion_date,c.materials_sent_to_tm_Date,c.company_name,
            c.site_id,c.site_name,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.auto_dial,c.auto_abandoned_rate,c.auto_time_threshold_one,c.auto_recorded_msg_one,c.auto_time_threshold_two,c.auto_recorded_msg_two,c.auto_hopper_level 
            from {$this->table} c
            LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id
            WHERE c.id = ?";
        
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    // End HP UAD-18 configure campaign
    // End HP UAD-3 configure campaign

    function get_campaign_tm_office_by_id($campaign_id="",$user_type="",$user_tm_ofc=""){
        $this->db->select('*');
        $this->db->from($this->campaign_tm_offices);
        if(!empty($campaign_id))
            $this->db->where('campaign_id', $campaign_id);

        if($user_type != 'admin' && !empty($user_tm_ofc)){
            $this->db->where('tm_office', $user_tm_ofc);
        }
        $query = $this->db->get();

        return $query->result_array();
    }

    function delete_campaign_tm_office_by_id($campaign_id){
        $this->db->where('campaign_id', $campaign_id);
        $this->db->delete($this->campaign_tm_offices);
    }

    public function deleteCampaign($campaign_id)
    {
        $sql1 = "DELETE c,tn,al,ls,tlh
                FROM campaigns c
                LEFT JOIN `lead_history` tlh ON c.id = tlh.campaign_id
                LEFT JOIN `notes` tn ON tlh.id = tn.lead_history_id
                LEFT JOIN `agent_lead` al ON tlh.id = al.lead_id
                LEFT JOIN `lead_status` ls ON tlh.id = ls.lead_history_id
                WHERE c.id IN ($campaign_id)";
        $query1 = $this->db->query($sql1);

        $sql2 = "DELETE  FROM  `agent_sessions` WHERE campaign_id ='$campaign_id'";
        $query2 = $this->db->query($sql2);
        
        $sql3 = "DELETE  FROM  `call_history`   WHERE campaign_id ='$campaign_id'";
        $query3 = $this->db->query($sql3);
        
        $sql5 = "DELETE  FROM  `campaign_contacts`   WHERE campaign_id ='$campaign_id'";
        $query5 = $this->db->query($sql5);
        
        return $query5;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data); 
    }

    public function update_by_office($tm_office, $data)
    {
        $this->db->where('tm_office', $tm_office);
        $this->db->update($this->campaign_tm_offices, $data); 
    }

    function updateCampaignDetailByID($campaign_id, $data)
    {
	$exclude_fields=array("script_alt","custom_question_value");
	$data = $this->unset_nulls($data,$exclude_fields);
        $this->db->where('id', $campaign_id);
        return $this->db->update($this->table, $data);
    }
        
    function updateCampaign_filterByID($campaign_id, $data)
    {
        $this->db->where('id', $campaign_id);
        return $this->db->update($this->table, $data);
    }

    function update_campaign_contact($filter_status,$campaign_id="",$list_id="")
    {
        $campaign_contact_table = $this->campaignContactsMappingTable;
        $this->db->where('campaign_id', $campaign_id);
		if(!empty($list_id)){$this->db->where('list_id', $list_id);}
        return $this->db->update($campaign_contact_table,$filter_status);
    }

    public function getCampaignViewDetail($campaign_id)
    {

        $sql = "SELECT c.id, c.eg_campaign_id,c.`name`,c.`status`,c.type,c.cpl,c.lead_goal,c.job_function,c.job_level,c.company_size,c.industries,c.industries,c.country,
            c.custom_questions,c.custom_question_value,c.script_main,c.script_alt,c.created_by,c.created_at,c.updated_at,c.start_date,c.end_date,
            c.assign_team_id,c.modified_by,c.call_filerequest_date,c.tm_launch_date,c.completion_date,c.materials_sent_to_tm_Date,c.company_name,
            c.site_id,c.site_name,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,
                    GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name,'|',u.id)) AS uname
            FROM {$this->table} c 
			LEFT JOIN {$this->campaign_tm_offices} cto ON c.id = cto.campaign_id
			LEFT JOIN {$this->campaign_assign_tl} ctl ON c.id = ctl.campaign_id 
			LEFT JOIN {$this->userTable} u ON u.id = ctl.user_id
            WHERE c.id  = ? ";
        

        $query = $this->db->query($sql,array($campaign_id));
        $array = $query->result();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    // Start RP UAD-11 : get auto_dial value
    function get_one($id)
    {
        $sql = 'SELECT c.id, c.eg_campaign_id,c.`name`,c.`status`,c.type,c.cpl,c.lead_goal,c.job_function,c.job_level,c.company_size,c.industries,c.industries,c.country,
        c.custom_questions,c.custom_question_value,c.script_main,c.script_alt,c.created_by,c.created_at,c.updated_at,c.start_date,c.end_date,
        c.assign_team_id,c.modified_by,c.call_filerequest_date,c.tm_launch_date,c.completion_date,c.materials_sent_to_tm_Date,c.company_name,
        c.site_id,c.site_name,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,c.contact_filter,c.auto_dial  FROM  ' . $this->table . ' c LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id WHERE c.id = ?';
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function get_campaign_by_userLogin($user_type,$loggedUserID=0,$isPaused=0)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $sql = "SELECT c.id,c.name FROM {$this->table} c ";
        $sql .= " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        if (!empty($user_type) && $user_type == 'team_leader') {
            $sql .= " LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id LEFT JOIN users u ON cl.user_id = u.id ";
            $sql .= " where u.user_type = '" . $user_type . "'";
            $sql .= " AND u.id = '" . $loggedUserID . "'";
            $sql .= " AND c.status = 'Active' ";
        } else {

            $sql .= " WHERE 1 = 1";
        }        
        if ($user_type != 'admin') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        if ($isPaused) {
            $sql .= " AND (c.status !='completed' AND c.status !='pending')";
        } else {
            $sql .= " AND c.status !='completed'";
        }
        $sql .= " AND c.module_type = '" . $this->app_module_type . "' "; 
        $sql .= " AND c.business = '{$this->app}'";
        $sql .= " GROUP BY c.id order by  c.name asc";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function get_egcampaign_by_userLogin($user_type,$loggedUserID=0,$isPaused=0)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');

        $sql = "SELECT c.id,c.eg_campaign_id,c.name FROM {$this->table} c ";
        $sql .= " LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id ";
        if (!empty($user_type) && $user_type == 'team_leader') {
            $sql .= " LEFT JOIN `campaign_assign_tl` cl ON cl.campaign_id = c.id LEFT JOIN users u ON cl.user_id = u.id ";
            $sql .= " where u.user_type = '" . $user_type . "'";
            $sql .= " AND u.id = '" . $loggedUserID . "'";
            $sql .= " AND c.status = 'Active' ";
        } else {

            $sql .= " WHERE 1 = 1";
        }        
        if ($user_type != 'admin') {
            $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        }
        if ($isPaused) {
            $sql .= " AND (c.status !='completed' AND c.status !='pending')";
        } else {
            $sql .= " AND c.status !='completed'";
        }
        $sql .= " AND c.module_type = '" . $this->app_module_type . "' "; 
        $sql .= " AND c.business = '{$this->app}'";
        $sql .= " GROUP BY c.id order by  c.name asc";

        $query = $this->db->query($sql);
        
        return $query->result();
    }
    
    function get_campaign_by_name($id=0)
    {
        $sql = "SELECT id,name FROM {$this->table} where status !='completed' and business = '{$this->app}' " ;
        if($id){
            $sql .= " and id = ?";
        }
        $query = $this->db->query($sql,array($id));        
        return $query->result();
    }

    function getEGCampaignList($existedCSVEGCampaignID = "")
    {
        $sql = "SELECT id,`name`,`type`,`parent_id` FROM {$this->table} WHERE `status` = 'active'";
        $sql.= " AND id NOT IN (SELECT id FROM {$this->table} WHERE parent_id != 0 AND `type` = 'mql')";
        if (!empty($existedCSVEGCampaignID)) {
            $sql .= " AND id NOT IN ($existedCSVEGCampaignID) ";
        }
        $sql .= "ORDER BY id ASC";

        $query = $this->db2->query($sql);
        return $query->result();
    }

    function getEGCampaignID()
    {
        $sql = "SELECT eg_campaign_id FROM {$this->table} c
              WHERE (c.eg_campaign_id IS NOT NULL OR c.eg_campaign_id != '')";

        $query = $this->db->query($sql);
        $array = $query->result_array();

        return $array;
    }

    function getEGCampaignDataByID($egCampaignID)
    {
        //$this->db2->select('id,parent_id,filters,questions,resources,report_builder_data,start_date,program_end_date,distinct_leads,reg_data,reg_rules');
        $this->db2->select('*');
        $this->db2->from($this->table);
        $this->db2->where('id', $egCampaignID);
        $this->db2->limit(1);
        $query = $this->db2->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function getCampaignDataByEGID($egCampaignID)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('eg_campaign_id', $egCampaignID);
        $this->db->limit(1);
        $query = $this->db->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function get_child_campaign_ids($parent_id)
    {
        $sql = "SELECT id FROM {$this->table} WHERE status='active' AND parent_id = ?";
        $query = $this->db2->query($sql, array($parent_id));

        $array = $query->result_array();
        if (!empty($array)) {
            return $array;
        } else {
            return null;
        }
    }

    function get_call_file_script($campaign_id, $call_file_script)
    {
        $sql = "SELECT script FROM call_scripts WHERE campaign_id= ?  AND call_file_script= ? ";
        $query = $this->db->query($sql, array($campaign_id, $call_file_script));

        $result = $query->result_array();
        return !empty($result) ? $result[0]['script'] : "";
    }
    
    function IsCampaignAssignToAgent($campaign_id, $userID)
    {
        $sql = "SELECT COUNT(*) AS isassigncampaign
                FROM campaigns c
                JOIN `campaign_assign` ca ON c.id = ca.campaign_id 
                WHERE ca.campaign_id = ? AND ca.agent_id = ? AND c.id = ? and c.business = '{$this->app}' ";
        $query = $this->db->query($sql,array($campaign_id,$userID,$campaign_id));
        $result = $query->result();
        return $result[0]->isassigncampaign;
    }

    function IsCampaignAssignToTL($campaign_id,$userID){

        $sql =  "SELECT COUNT(*) AS isassigncampaign
                FROM {$this->table}  c
                JOIN `campaign_assign_tl` cl ON c.id = cl.`campaign_id`     
                LEFT JOIN {$this->userTable} u ON cl.user_id = u.id 
                WHERE c.id = ? AND u.id = ? AND u.user_type = 'team_leader' and c.business = '{$this->app}' ";

        $query = $this->db->query($sql,array($campaign_id,$userID));
        $result = $query->result();

        return $result[0]->isassigncampaign;
    }

    function update_agent_session_by_user($agent_session_id,$data){
        $this->db->limit(1);
        $this->db->order_by('id','DESC');
        $this->db->where('user_id', $agent_session_id);
        $this->db->where('is_session_deactive !=' , 1);
        return $this->db->update($this->agentSessionTable, $data);
    }

    public function get_agent_list_by_campaign($campaignId)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $sql = "SELECT u.id as agent_id, CONCAT(u.first_name,' ',u.last_name) AS agent_name
                FROM `users` u
                LEFT JOIN `campaign_assign_tl` cl ON cl.user_id = u.parent_id
                LEFT JOIN `campaigns` c ON c.id = cl.campaign_id
                WHERE u.user_type = 'agent' AND u.status = 'Active' AND c.id = ? ";
        if($logged_user_type != 'admin'){
            $sql .= " AND u.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $sql .= " AND FIND_IN_SET('".$this->app_module_type."',u.module) ";

        $query = $this->db->query($sql, array($campaignId));
        // echo $this->db->last_query();exit;
        return $query->result();
    }

    public function getAssignAgentByCampaign($campaignId)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');

        $sql = "SELECT u.id as agent_id, CONCAT(u.first_name,' ',u.last_name) AS agent_name
                FROM `users` u
                LEFT JOIN `campaign_assign` cl ON cl.agent_id = u.id
                LEFT JOIN `campaigns` c ON c.id = cl.campaign_id
                WHERE u.user_type = 'agent' AND u.status = 'Active' AND c.id = ? ";
        if($logged_user_type != 'admin'){
            $sql .= " AND u.telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $sql .= " AND FIND_IN_SET('".$this->app_module_type."',u.module) ";

        $query = $this->db->query($sql, array($campaignId));
        //echo $this->db->last_query();exit;
        return $query->result();
    }

    /*Dev_kr Region Start */
    function checkCampaignContacts($campaignID){
        $campaign_contact_table = $this->campaignContactsMappingTable;
        $sql = " SELECT count(*) as count FROM {$campaign_contact_table} WHERE campaign_id = ? ";
        $query = $this->db->query($sql,array($campaignID));
        $result = $query->result();

        return $result[0]->count;
        
    }
    function insert_agent_session($data){
        $result = $this->db->insert($this->agentSessionTable, $data);
        if ($result) {
            $agent_session_id = $this->db->insert_id();
        } else {
            $agent_session_id = 0;
        }
        return $agent_session_id;
    }

    function check_agent_session($userId)
    {
        $sql = 'SELECT * FROM ' . $this->agentSessionTable . '  WHERE is_session_deactive = 0 AND user_id = ? ';
        $queryagent = $this->db->query($sql, array($userId));

        $array = $queryagent->result();
        return ($array) ? $array[0] : 0;
    }
        
    function check_agent_signout($userId, $campaignId)
    {
        $sql = 'SELECT user_id as id,campaign_id FROM ' . $this->agentSessionTable . '  WHERE  is_session_deactive = 0 AND user_id = ? and campaign_id = ? ';
        $queryagent = $this->db->query($sql, array($userId, $campaignId));
        $array = $queryagent->result();
        return ($array) ? $array[0] : 0;
    }

    function update_agent_session($agent_session_id, $data)
    {
         $this->db->where('id', $agent_session_id);
        return $this->db->update($this->agentSessionTable, $data);
    }

    function isAssignCampaignToTL($campaignID, $teamID)
    {
        $sql = "SELECT COUNT(*) AS `countdata` FROM `campaign_assign` WHERE campaign_id = ? AND teamleader_id in ? ";
        $queryagent = $this->db->query($sql, array($campaignID, $teamID));
        $array = $queryagent->result_array();
        return $array[0]['countdata'];
    }
    
    function unset_nulls($obj, $exclude)
    {
        foreach ($obj as $key => $value) {
            if ($value == NULL && !in_array($key, $exclude)) {
                unset($obj->$key);
            }
        }
        return $obj;
    }

    public function  insert_assign_campaign_tl_csv($string)
    {
        $mappingSql = "INSERT INTO " . $this->campaign_assign_tl . " (campaign_id,user_id,created_by,created_at) VALUES %s";
        $mappingSql = sprintf($mappingSql, implode(",", $string));
        try {
            $result = $this->db->query($mappingSql);
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function  insert_campaign_tm_office_csv($string)
    {
        $mapping_tm_Sql = "INSERT INTO " . $this->campaign_tm_offices . " (campaign_id,tm_office,created_by,created_at) VALUES %s";
        $mapping_tm_Sql = sprintf($mapping_tm_Sql, implode(",", $string));
        try {
            $result = $this->db->query($mapping_tm_Sql);
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    function getSelectedTeamLeaderList($active = false, $campaignID,$array_of_ids="")
    {
        $this->db->select('campaign_assign_tl.user_id,CONCAT(users.first_name," ",users.last_name) AS uname');
        $this->db->from('campaign_assign_tl');
        $this->db->join('users', 'users.id = campaign_assign_tl.user_id', 'left'); 
        $this->db->where('users.user_type', 'team_leader');
        if(!empty($array_of_ids))
            $this->db->where_in('campaign_assign_tl.campaign_id', $campaignID);
        else
            $this->db->where('campaign_assign_tl.campaign_id', $campaignID);

        if($active){
            $this->db->where('users.status', 'active');
        }
        $query = $this->db->get();
        $result = $query->result_array();
        if(!empty($result)){
            return $result;
        }else{
            return 0;
        }
    }

    function delete_assign_tl($campaignID)
    {
        $result = $this->db->delete('campaign_assign_tl', array('campaign_id' => $campaignID)); 
        return $result;
    }
    
    function is_exits_client_in_campaign($client_id){
        $sql = "SELECT count(*) as CNT FROM {$this->table} where client='$client_id'";
        $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            return $array[0]->CNT;
        }
    }
    
    function is_exits_company_in_campaign($company_id){
        $sql = "SELECT count(*) as CNT FROM {$this->table} where company_id='$company_id'";
        $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            return $array[0]->CNT;
        }
    }
    
    function is_exits_salesper_in_campaign($sales_id){
        $sql = "SELECT count(*) as CNT FROM {$this->table} WHERE FIND_IN_SET('$sales_id',sales_reps) <> 0";
        $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            return $array[0]->CNT;
        }
    }
    
    // When agent / team leader login at the time store their assign campaign ids in session.   --- 15-05-2017
    function getAssignedCampaignIdsOfUser($user_type,$userID)
    {
        if($user_type == "agent"){
            $sql = "SELECT campaign_id AS campaign_ids FROM {$this->table} c JOIN `campaign_assign` ca ON c.id = ca.campaign_id 
                WHERE ca.agent_id = ? AND c.business = '{$this->app}' ";
        }elseif($user_type == "team_leader"){
           $sql = "SELECT campaign_id AS campaign_ids FROM {$this->table} c JOIN `campaign_assign_tl` cl ON c.id = cl.`campaign_id`     
                WHERE cl.user_id = ? AND c.business = '{$this->app}' "; 
        }
        
        $query = $this->db->query($sql,array($userID));
        $result = $query->result_array();
        $campaign_ids = array();
        if(count($result) > 0){
            foreach ($result as $key => $value){
                $campaign_ids[] = $value['campaign_ids'];
            }
        }    
        return $campaign_ids;
    }

    function get_campaign_data($id)
    {
       $sql = 'SELECT c.id, c.eg_campaign_id,c.`name`,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices, c.multi_touch, c.auto_dial FROM  ' . $this->table . ' c LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id WHERE c.id = ?';
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    /*Dev_kr Region End */
    
    
    /**
     * get incentive name from eg.pureresearch_incentives
     * @param type $id
     * @return type
     */
    function getIncentive($id) {
        $query = $this->db2->query("SELECT incentive FROM pureresearch_incentives WHERE id = {$id}");
        return $query->result();
    }
    
    function getOneCampaignByIdData($id){
        $sql = 'SELECT * FROM  ' . $this->table . ' WHERE id = ?';
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    // Start RP UAD-46 : agent session set for autodialer 
    function insertLiveAutoAgent($agentId,$campaignId) 
    {
        $dateTime = date('Y-m-d H:i:s', time());
        $liveAgents = new Live_agents_table();
        $liveAgents->agent_id       = $agentId;
        $liveAgents->campaign_id    = $campaignId;
        $liveAgents->active_status  = 'Paused';
        $liveAgents->agent_session  = '0';
        $liveAgents->created_datetime  = $dateTime;
        $liveAgents->updated_datetime  = $dateTime;
        $this->db->insert($this->auto_live_agents, $liveAgents);
        //$last_insert_id = $this->db->insert_id();
       
        
        // Insert session start/stop log
        $agentLogData = array();
        $agentLogData['agent_id'] = $agentId;
        $agentLogData['campaign_id'] = $campaignId;
        $agentLogData['session_status'] = 'stop';
        $agentLogData['status_changed_by'] = 'agent';
        $agentLogData['created_datetime'] = $dateTime;
        $result = $this->insertAutoAgentSessionLog($agentLogData);
        
        return $result;
    }

    function deleteLiveAutoAgent($agentId) 
    {
        $this->db->where('agent_id', $agentId);
        $this->db->delete($this->auto_live_agents);        
    }
    
    
    // insert auto agent start/stop log
    function insertAutoAgentSessionLog($data) 
    {
        $result = $this->db->insert($this->auto_live_agents_logs, $data);
        if ($result) {
            $agent_session_id = $this->db->insert_id();
        } else {
            $agent_session_id = 0;
        }
        return $agent_session_id;
    }
    
    // Start HP UAD-16 login and start session
    // Function to get agent's last session status from auto_live_agents_logs table
    function getAutoAgentSessionLog($agentId) {
        $agentLastSessionQuery = $this->db->query("SELECT session_status FROM  $this->auto_live_agents_logs WHERE agent_id = ? order by id desc limit 1", array($agentId));
        $agentLastSessionResult = $agentLastSessionQuery->result();
        return $agentLastSessionResult[0]->session_status;
    }
    // End HP UAD-16 login and start session

    // get live agent session status
    function getAutoAgentStatus($agent_id) 
    {
        $sql = 'SELECT agent_session FROM  ' . $this->auto_live_agents . ' WHERE agent_id = ?';
        $query = $this->db->query($sql, array($agent_id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
            $agent_session_status = (isset($array->agent_session) && $array->agent_session != "") ? $array->agent_session : 0 ;
        }else {
            $agent_session_status = 0;    
        }
        return $agent_session_status;
    }
    
    // when user click on session start and stop button change value in table
    function updateAutoAgentSessionStatus($agentId, $campaignId, $sessionValue, $sessionLog = 1) 
    {
        $dateTime = date('Y-m-d H:i:s', time());
        $agentData = array();
        $agentData['campaign_id'] = $campaignId; 
        $agentData['active_status'] = ($sessionValue == "start") ? 'Ready' : 'Paused';
        $agentData['agent_session'] = ($sessionValue == "start") ? '1' : '0';
        $agentData['updated_datetime'] = $dateTime;
        $this->db->where('agent_id', $agentId);
        $this->db->update($this->auto_live_agents, $agentData);
        
        if ($sessionLog) {
            // Insert session start/stop log
            $agentLogData = array();
            $agentLogData['agent_id'] = $agentId;
            $agentLogData['campaign_id'] = $campaignId;
            $agentLogData['session_status'] = $sessionValue;
            $agentLogData['status_changed_by'] = 'agent';
            $agentLogData['created_datetime'] = $dateTime;
            $result = $this->insertAutoAgentSessionLog($agentLogData);
        }
         
        return $result;
    }
    
    // End RP UAD-46 : agent session set for autodialer 
}

class Campaign
{
    public $eg_campaign_id;
    public $name;
    public $status;
    public $type;
    public $cpl;
    public $lead_goal;
    public $job_function;
    public $job_level;
    public $company_size;
    public $industries;
    public $country;
    public $custom_questions;
    public $custom_question_value;
    public $script_main;
    public $script_alt;
    public $created_by;
    public $created_at;
    public $updated_at;
    public $call_filerequest_date;
    public $tm_launch_date;
    public $completion_date;
    public $materials_sent_to_tm_Date;
    public $site_id;
    public $site_name;
    public $module_type;
    public $company_id;
}

class Campaign_List
{
    public $campaign_id;
    public $list_id;
}

class Live_agents_table
{
    public $id;
    public $agent_id;
    public $campaign_id;
    public $active_status;
    public $agent_session;
    public $created_datetime;
    public $updated_datetime;
}
