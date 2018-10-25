<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contacts_model extends CI_Model
{
    public $table = 'contacts';
    public $membersQaTable = 'members_qa';
    public $campaign_table = 'campaigns';
    public $users_table = 'users';
    public $mapping_table = 'campaign_contacts'; 
    public $mappingChangesTable = 'campaign_contact_changes'; 
    public $dupes_table = 'campaign_lists_dupes';
    public $tmLeadHistoryTable = 'lead_history';
    public $callDispositionsTable = 'calldispositions';
    public $agentLeadTable = 'agent_lead';
    public $tmNotesTable = 'notes';
    public $campaign_lists = 'campaign_lists';
    public $egMembers = 'members';
    public $unlockContactLog = 'unlock_contact_log';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //$this->db2 = $this->load->database('db2', TRUE);
        $this->table .= $this->fileAppend;
    }

    /* dev_kr Region Start */

    function insert_csv_contacts($insert_string)
    {
        $sql = "INSERT INTO " . $this->table . " (id,member_id,email,first_name,last_name,job_title,job_level,job_function,company,address,city,state,country,zip,phone,time_zone,company_size,industry,priority,original_owner,";
        $sql .= "created_at,updated_at) VALUES %s";
        $sql .= " ON DUPLICATE KEY UPDATE first_name=VALUES(first_name),last_name=VALUES(last_name),job_title=VALUES(job_title),job_level=VALUES(job_level),job_function=VALUES(job_function),company=VALUES(company),address=VALUES(address),city=VALUES(city),state=VALUES(state),country=VALUES(country),zip=VALUES(zip),phone=VALUES(phone),time_zone=VALUES(time_zone),company_size=VALUES(company_size),industry=VALUES(industry),priority=VALUES(priority),original_owner=VALUES(original_owner),updated_at=VALUES(updated_at)";
        $sql = sprintf($sql, implode(",", $insert_string));
        
        try {
        $result = $this->db->query($sql);
            $list_id = 1;
            } catch (Exception $e) {
            $list_id = 0;
        }
        return $list_id;
    }
    
    function insert_csv_file_contacts($insert_string, $table = '')
    {
        if ( $table == '' ) {
            $table = $this->table;
        }
        
        $sql = "INSERT INTO " . $table . " (member_id,email,first_name,last_name,job_title,job_level,job_function,company,address,city,state,country,zip,phone,ext,time_zone,company_size,company_revenue,industry,priority,original_owner,created_at,updated_at) VALUES %s ";
        $sql .= "ON DUPLICATE KEY UPDATE first_name = VALUES(first_name),last_name = VALUES(last_name),job_title = VALUES(job_title),job_level = VALUES(job_level),job_function = VALUES(job_function),company = VALUES(company),address = VALUES(address),city = VALUES(city),state = VALUES(state),country = VALUES(country),zip = VALUES(zip),phone = VALUES(phone),ext = VALUES(ext),time_zone = VALUES(time_zone),company_size = VALUES(company_size),company_revenue = VALUES(company_revenue),industry = VALUES(industry),priority = VALUES(priority),original_owner = VALUES(original_owner),updated_at = VALUES(updated_at)";
        $sql = sprintf($sql, implode(",", $insert_string));
        
        try {
            $result = $this->db->query($sql);
            $list_id = 1;
        } catch (Exception $e) {
            $list_id = 0;
        }
        return $list_id;
    }

    function truncateContactsTmpTable($table = 'tmp_contacts_upload'){
        $sql = "DELETE FROM tmp_contacts_upload";
        
        $result = $this->db->query($sql);
        
        return 1;
    }

    function insert_csv_file_members_qa($insert_string)
    {
        $sql = "INSERT INTO members_qa (id,email,first_name,last_name,job_title,job_level,job_function,company,address,city,state,country,zip,phone,ext,time_zone,company_size,company_revenue,industry,original_owner,created_at,updated_at) VALUES %s ";
        $sql .= "ON DUPLICATE KEY UPDATE first_name = VALUES(first_name),last_name = VALUES(last_name),job_title = VALUES(job_title),job_level = VALUES(job_level),job_function = VALUES(job_function),company = VALUES(company),address = VALUES(address),city = VALUES(city),state = VALUES(state),country = VALUES(country),zip = VALUES(zip),phone = VALUES(phone),ext = VALUES(ext),time_zone = VALUES(time_zone),company_size = VALUES(company_size),company_revenue = VALUES(company_revenue),industry = VALUES(industry),original_owner = VALUES(original_owner),updated_at = VALUES(updated_at)";
        $sql = sprintf($sql, implode(",", $insert_string));
        try {
            $result = $this->db->query($sql);
            $list_id = 1;
        } catch (Exception $e) {
            $list_id = 0;
        }
        return $list_id;
    }

    function insert_csv_contacts_mpg($insert_string, $id){
        $sql = "INSERT IGNORE INTO " . $this->table . " (email,first_name,last_name,job_title,job_level,company,address1,address2,city,state,zip,country,time_zone,phone,alternate_no,bed_size,employee_size,";
        $sql .= "created_at) VALUES %s";
        $sql = sprintf($sql, $insert_string);
        try {
            $result = $this->db->query($sql);
            $list_id = 1;//echo('\n\ra batch of 1000 records was inserted.');
        } catch (Exception $e) {
            $list_id = 0;
        }
        $last_insert_id = $this->db->insert_id();
        $array = array();
        $array[] = '("'. $id . '","' . $last_insert_id . '")';
        $mappingSql = "INSERT INTO " . $this->mapping_table . " (campaign_id,contact_id) VALUES %s";
        $mappingSql = sprintf($mappingSql, implode(",", $array));
        $mappingSql .= '  ON DUPLICATE KEY UPDATE `campaign_id` = VALUES(campaign_id),`contact_id` = VALUES(contact_id)';
        
        try {
            $result = $this->db->query($mappingSql);
        } catch (Exception $e) {
            return 0;
        }
        return $list_id;
    }
    
    public function insert_mapping_csv_contacts($string)
    {
        $mappingSql = "INSERT INTO " . $this->mapping_table . " (campaign_id,contact_id,resource_id) VALUES %s";
         $mappingSql = sprintf($mappingSql, implode(",", $string));
        $mappingSql .= '  ON DUPLICATE KEY UPDATE `campaign_id` = VALUES(campaign_id),`contact_id` = VALUES(contact_id),`resource_id` = VALUES(resource_id)';

        try {
            $result = $this->db->query($mappingSql);
            return 1;
            //echo('\n\ra batch of 1000 records was inserted.');
        } catch (Exception $e) {
            return 0;
        }
    }

    public function insertCampaignContactChanges($data)
    {
        $result = $this->db->insert($this->mappingChangesTable, $data);

        if($result){
            $last_insert_id = $this->db->insert_id();
            return $last_insert_id;
        }else{
            return false;
        }
    }

    public function getCampaignContactsChangesLatest($campaignContactId)
    {
        $this->db->select("ccc.new_source, concat(u.first_name,' ',u.last_name) as contact_created_by");
        $this->db->from($this->mappingChangesTable.' AS ccc');
        $this->db->join('users as u', 'u.id = ccc.created_by');
        $this->db->where('ccc.campaign_contact_id', $campaignContactId);
        
        $this->db->order_by("ccc.id", "desc");
        $this->db->limit(1);    
        
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }
    
    public function select_dupes_list( $campaign_id='', $list_id='', $dupes_attempt_inserted_at='' ){
        $sql = "Select `contact_id`,`dupes`,`list_id` from " . $this->mapping_table . " cc ";
        $sql .= "INNER JOIN `contacts` c ON cc.contact_id=c.id ";
        $sql .= "WHERE `dupes` > 0 AND `dupes_attempt_inserted_at` = '" . $dupes_attempt_inserted_at ."'";
        $sql .= " AND campaign_id = " . $campaign_id;
       
        $query =  $this->db->query($sql);
        
        $response = array();

        if(count($query->result())>0)
        {
            foreach ($query->result() as $key=>$row){
                $response['list_id'][]=$row->list_id;    
                $response['contact_id'][]=$row->contact_id;        
                $response['dupes'][]=$row->dupes;        
            }
        }
        
        return $response;
    }
    
    public function clear_dupes_list( $campaign_id='', $camp_list_id='', $dupes_attempt_inserted_at='' ){
        $sql = "UPDATE " . $this->mapping_table . " ";
        $sql .= "SET `dupes`= 0, `dupes_attempt_inserted_at`='' ";
        $sql .= "WHERE `dupes` > 0 AND `dupes_attempt_inserted_at` = '" . $dupes_attempt_inserted_at ."'";
        $sql .= " AND campaign_id = " . $campaign_id;

        try {
            $result = $this->db->query($sql);
            return 1;                                    
        } catch (Exception $e) {
            return 0;
        }
    }

    public function insert_dupes_lists( $string=array() ){
        $sql = "INSERT INTO " . $this->dupes_table . " (campaign_list_dupes_history_id,dupes_list_id,list_name,contact_id) VALUES %s";
        $sql = sprintf($sql, implode(",", $string));
      
        try {
            $result = $this->db->query($sql);
            return 1;                                    
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function insert_mapping_csv_contacts_file($string,$dupes_attempt_inserted_at=''){
        $mappingSql = "INSERT IGNORE INTO " . $this->mapping_table . " (campaign_id,contact_id,list_id,created_by,created_at) VALUES %s";
        //tag dupes=1 and dupes_attempt_inserted_at for dupes list extraction purposes
        if($dupes_attempt_inserted_at!=''){
            $mappingSql .= '  ON DUPLICATE KEY UPDATE `dupes` = `dupes`+1,`dupes_attempt_inserted_at` = "%s"';
            $mappingSql = sprintf($mappingSql, implode(",", $string),$dupes_attempt_inserted_at);
        }else{
            $mappingSql = sprintf($mappingSql, implode(",", $string));
        }
        //$mappingSql .= '  ON DUPLICATE KEY UPDATE `campaign_id` = VALUES(campaign_id),`contact_id` = VALUES(contact_id),`created_by` = VALUES(created_by),`created_at` = VALUES(created_at)';
        
        try {
            $result = $this->db->query($mappingSql);
            return 1;                                    
        } catch (Exception $e) {
            return 0;
        }
    }

    public function remove_contact_list_data($campaignId)
    {
        $this->db->where('campaign_id', $campaignId);
        return $this->db->delete($this->mapping_table);
    }

    function get_list($id = "", $userType = "",$where,$sidx,$sord,$start,$limit,$call_disposition_filter_flag,$lead_status_filter_flag, $list_id) 
    {
        $callLimit = $this->config->item('call_limit');

        //get contact_filter
        $contact_filter = $this->get_contact_filter($list_id);
        $build_filter = "";
        if(!empty($contact_filter)){
            $contact_filters = explode("|", $contact_filter);
            $build_filter = array();
            foreach($contact_filters as $filter){
                $get_filter = explode(":", $filter);
                $build_filter[] = "c.{$get_filter[0]} in ('{$get_filter[1]}') ";
            }
            $build_filter = implode(" AND ", $build_filter);
            $build_filter = " AND {$build_filter} ";
        }
        $sql = "Select c.id,lockeduser.user_type,cl.source,c.phone,c.company,c.time_zone,cl.id as contact_list_id ,c.edit_lead_status,c.locked_by,cl.call_disposition_id AS call_disposition_id, ";
        $sql .= "tlh.status,cl.call_disposition_update_date as callback_date,cd.calldisposition_name AS calldisposition_name,CONCAT(c.first_name,' ',c.last_name) as full_name, ";
        $sql .= "CONCAT(u.first_name,' ',u.last_name) AS agent_name,cl.notes as note ";
        $sql .= "FROM ".$this->mapping_table." cl ";
        $sql .= "JOIN ".$this->table." c ON c.id = cl.contact_id ";
        $sql .= "LEFT JOIN ".$this->membersQaTable." m ON c.member_id = m.id ";
        $sql .= "LEFT JOIN ".$this->tmLeadHistoryTable." tlh ON cl.id = tlh.campaign_contact_id ";
        //$sql .= "LEFT JOIN ".$this->tmNotesTable." notes ON notes.campaign_contact_id = cl.id AND notes.id IN (SELECT MAX(id) FROM ".$this->tmNotesTable." GROUP BY campaign_contact_id) ";
        $sql .= "LEFT JOIN ".$this->callDispositionsTable." cd ON cl.call_disposition_id = cd.id ";
        $sql .= "LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ";
        $sql .= "LEFT JOIN ".$this->users_table ." AS u ON u.id=cdh.user_id ";
        $sql .= "LEFT JOIN ".$this->users_table." lockeduser ON c.locked_by = lockeduser.id ";
        $sql .= "LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country) ";
        $sql .= "LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone)) ";
        $sql .= "WHERE (dnf.count < 3 OR dnf.count IS NULL) ";
        $sql .= "AND cl.list_id =".$list_id . " ";
        if(!$call_disposition_filter_flag) {
            $sql .= " AND c.do_not_call_ever = 0 ";
        }
        $sql .=" AND c.original_owner != 'Netwise' ";
        if($where!="") {
            $sql .= " AND ".$where. " {$build_filter} ";
        }else{
            $sql .= " {$build_filter} ";
        }
        if ($userType == 'qa') {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag){
                $sql .= ' AND cl.call_disposition_id not in (7,2,11,14,15,16,17,18,19,20,21,22,23,24,25) '; // (14,15,19)
                $sql .= " AND tlh.status in ('Pending','QA in progress','Follow-up') ";
            }
        } else {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag) {
                $sql .= ' AND (cl.call_disposition_id not in (1,7,2,11,14,15,16,17,18,19,20,21,22,23,24,25) || cl.call_disposition_id IS null) '; //(1,14,15,19)
                $sql .= " AND (tlh.status in ('','In Progress','Follow-up') OR  tlh.status IS NULL) ";
            }
        }
        if($userType == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        $sql .= " GROUP BY c.id ORDER BY (case when c.locked_by = ".$this->session->userdata('uid')." then 1 else 0 end) desc, ".$sidx." ".$sord . " ";
        $sql .= "  LIMIT ? OFFSET ?";  
        
        $query =  $this->db->query($sql, array(intval($limit),intval($start)));//echo $this->db->last_query();exit;
        $response = new stdClass();

        $i=0;
        if(count($query->result())>0) {
            foreach ($query->result() as $key=>$row){
                $response->rows[$i]['id']=$row->id;
                $response->rows[$i]['cell']=$row;
                $i++;
            }
        }
        return $response;
    }

    public function get_contact_filter($list_id){
        $sql = "select contact_filter from {$this->campaign_lists} where id = {$list_id} limit 1";
        $query = $this->db->query($sql);
        $result=$query->result();
        return !empty($result) ? $result[0]->contact_filter : '';
    }
    
    function get_list_count($id = "", $userType = "",$where,$call_disposition_filter_flag,$lead_status_filter_flag, $list_id) 
    {
        $callLimit = $this->config->item('call_limit');

        //get contact_filter
        $contact_filter = $this->get_contact_filter($list_id);
        $build_filter = "";
        if(!empty($contact_filter)){
            $contact_filters = explode("|", $contact_filter);
            $build_filter = array();
            foreach($contact_filters as $filter){
                $get_filter = explode(":", $filter);
                $build_filter[] = "c.{$get_filter[0]} in ('{$get_filter[1]}') ";
            }
            $build_filter = implode(" AND ", $build_filter);
            $build_filter = " AND {$build_filter} ";
        }

        $sql = "Select DISTINCT(c.id) AS count FROM ".$this->mapping_table." cl JOIN ".$this->table." c ON c.id = cl.contact_id LEFT JOIN ".$this->membersQaTable." m ON c.member_id = m.id
            LEFT JOIN ".$this->tmLeadHistoryTable." tlh ON cl.id = tlh.campaign_contact_id LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country) 
            LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone)) 
            WHERE (dnf.count < 3 OR dnf.count IS NULL) 
            AND cl.list_id =".$list_id;
        if(!$call_disposition_filter_flag) {
            $sql .= " AND c.do_not_call_ever = 0 ";
        }
        $sql .=" AND c.original_owner != 'Netwise' ";
        if($where!="") {
            $sql .= " AND ".$where. " {$build_filter}";
        }else{
            $sql .= " {$build_filter}";
        }
        if ($userType == 'qa') {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag){
                $sql .= ' AND tlh.call_disposition_id not in (7,11,14,15,16,17,18,19,20,21,22,23,24,25) ';
                $sql .= " AND tlh.status in ('Pending','QA in progress','Follow-up') ";
            }
        } else {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag) {
                $sql .= ' AND (tlh.call_disposition_id not in (1,7,11,14,15,16,17,18,19,20,21,22,23,24,25) || tlh.call_disposition_id IS null) ';
                $sql .= " AND (tlh.status in ('','In Progress','Follow-up') OR  tlh.status IS NULL)";
            }
        }
        if($userType == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        
        $query = $this->db->query($sql);
        $result=$query->result();
        return count($result);
    }
    
    // // Non-workable contact count
    function get_non_workable_count($id = "", $logged_user_id = "",$where,$list_id)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = ' WHERE 1=1 ';
        if($logged_user_type == 'agent'){
            $customWhere .= ' AND u.id = '.$logged_user_id.' ';
        }
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$logged_user_id.' OR u.parent_id = '.$logged_user_id.')';
        }
        if($logged_user_type == 'manager'){
            $customWhere .= ' AND (u.id = '.$logged_user_id.' ';
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }

        $sql = 'Select DISTINCT(c.id) ';
        $sql .= 'FROM ' . $this->mapping_table . ' cl ';
        $sql .= 'LEFT JOIN ' . $this->table . ' c ON c.id = cl.contact_id ';
        $sql .= 'LEFT JOIN ' . $this->tmLeadHistoryTable . ' tlh ON cl.id = tlh.campaign_contact_id ';
        $sql .= 'LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ';
        //$sql .= 'LEFT JOIN '.$this->tmNotesTable .' notes ON notes.lead_history_id = tlh.id AND notes.id IN (SELECT MAX(id) FROM '.$this->tmNotesTable.' GROUP BY lead_history_id) ';
        $sql .= 'LEFT JOIN ' . $this->callDispositionsTable . ' cd ON cl.call_disposition_id = cd.id ';
        $sql .= 'LEFT JOIN ' . $this->users_table . ' u ON cdh.`user_id` = u.id ';
        $sql .= 'LEFT JOIN ' . $this->users_table . ' lockeduser ON c.locked_by = lockeduser.id ';

        $sql .= $customWhere;

        if($where!="")
        {
            $sql .= " AND ".$where;
        }
        if (!empty($id)) {
            $sql .= " AND cl.list_id=".$list_id ;
        }
        // as per #2026-uber--make-call-history-list-accessible-to-agents mention call disposition
        $sql .= " AND cl.call_disposition_id IN (1,7,11,14,15,16,17,18,20,21,22,23,24,25)  AND c.id IS NOT NULL ";
        if($logged_user_type == 'agent')
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '')";

        $query = $this->db->query($sql);
        $result=$query->result();

        return count($result);
    }

    // Non-workable contact list
    function get_non_workable_list($id = "", $logged_user_id = "",$where,$sidx,$sord,$start,$limit,$list_id)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = ' WHERE 1=1 ';
        if($logged_user_type == 'agent'){
            $customWhere .= ' AND u.id = '.$logged_user_id.' ';
        }
        if($logged_user_type == 'team_leader'){
            $customWhere .= ' AND (u.id = '.$logged_user_id.' OR u.parent_id = '.$logged_user_id.')';
        }
        if($logged_user_type == 'manager'){
            $customWhere .= ' AND (u.id = '.$logged_user_id.' ';
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }

        $sql = 'Select DISTINCT(c.id), ';
        $sql .= 'lockeduser.user_type, ';
        $sql .= 'c.phone, ';
        $sql .= 'c.company, ';
        $sql .= 'c.time_zone,cl.id as contact_list_id, ';
        $sql .= 'c.edit_lead_status, ';
        $sql .= 'c.locked_by, ';
        $sql .= 'cl.call_disposition_id AS call_disposition_id, ';
        $sql .= 'tlh.status, ';
        $sql .= 'cl.call_disposition_update_date as callback_date, ';
        $sql .= 'cd.calldisposition_name AS calldisposition_name, ';
        $sql .= 'CONCAT(c.first_name," ",c.last_name) as full_name,u.id as user_id, ';
        $sql .= 'CONCAT(u.first_name," ",u.last_name) AS agent_name, ';
        $sql .= 'tlh.agent_id AS userid,  ';
        $sql .= 'cl.notes note ';
        $sql .= 'FROM ' . $this->mapping_table . ' cl ';
        $sql .= 'JOIN ' . $this->table . ' c ON c.id = cl.contact_id ';
        $sql .= 'LEFT JOIN ' . $this->tmLeadHistoryTable . ' tlh ON cl.id = tlh.campaign_contact_id ';
        $sql .= 'LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ';
        //$sql .= 'LEFT JOIN '.$this->tmNotesTable .' notes ON notes.lead_history_id = tlh.id AND notes.id IN (SELECT MAX(id) FROM '.$this->tmNotesTable.' GROUP BY lead_history_id) ';
        $sql .= 'LEFT JOIN ' . $this->callDispositionsTable . ' cd ON cl.call_disposition_id = cd.id ';
        $sql .= 'LEFT JOIN ' . $this->users_table . '  AS u ON u.id=cdh.user_id  ';
        $sql .= 'LEFT JOIN ' . $this->users_table . ' lockeduser ON c.locked_by = lockeduser.id ';

        $sql .= $customWhere;
        if($where!="")
        {
            $sql .= " AND ".$where;
        }
        if (!empty($id)) {
            $sql .= " AND cl.list_id=".$list_id ;
        }
        // as per #2026-uber--make-call-history-list-accessible-to-agents mention call disposition
        $sql .= " AND cl.call_disposition_id IN (1,7,11,14,15,16,17,18,19,20,21,22,23,24,25) ";
        if($logged_user_type == 'agent')
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '')";

        $sql .=" GROUP BY c.id";
        $sql .= " ORDER BY ".$sidx." ".$sord;   
        $sql .= "  LIMIT ?,?";

        $query =  $this->db->query($sql, array(intval($start),intval($limit)));
        
        $response = new stdClass();

        $i=0;
        if(count($query->result())>0)
        {
            foreach ($query->result() as $key=>$row){
                $response->rows[$i]['id']=$row->id;
                $response->rows[$i]['cell']=$row;
                $i++;
            }
        }
        return $response;
    }

    function get_one_contact($callListId)
    {
        $this->db->select('*, CONCAT(first_name," ",last_name) as full_name');
        $this->db->from($this->table);
        $this->db->where("id", $callListId);
        $array = $this->db->get()->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;

    }
    
    function get_countries(){
        $this->db->select('country,country_code');
        $query = $this->db->get('countries')->result(); 
        return $query;
    }

    function getCountryName($code){
        $code = strtolower(str_replace(",","','" ,$code));
        $sql = "SELECT GROUP_CONCAT(country) as countries FROM countries WHERE country_code IN('$code')";
        $query = $this->db->query($sql);

        if ($query->num_rows() == 0) {
            return false;
        } else {
            $array = $query->result();
            if (!empty($array)) {
                return  $array[0]->countries; 
            }
        }
    }
    
    function getDialCodeByCountryCode($countryCode){
        $this->db->select('dial_code');
        $this->db->from("countries");
        $this->db->where("country_code", $countryCode);
        $array = $this->db->get()->result();
        if (!empty($array)) {
            return $array[0]->dial_code;
        }
        return '';
    }

    function update_contact($id, $data)
    {
        //$data = $this->unset_array_nulls($data);
        $this->db->where('id', $id);
        if(is_array($data)){
            $data['updated_at'] = date('Y-m-d H:i:s', time());
        }
        else if(is_object($data)){
        $data->updated_at = date('Y-m-d H:i:s', time());
        }

        $result = $this->db->update($this->table, $data);
        return $result;
    }

    function unlockContact($id, $filter = '')
    {
        $sql = "UPDATE contacts SET edit_lead_status = 0, locked_by = '' WHERE id = " . $id;

        if(!empty($filter)) {
            $sql .= $filter;
        }

        return $this->db->query($sql);
    }

    function updateCampaignContact($id, $data)
    {
        $this->db->where('id', $id);
        $result = $this->db->update($this->mapping_table, $data);
        return $result;
    }
    
    function unset_array_nulls($obj)
    {
        foreach ($obj as $key => $value) {
            if ($value == NULL) {
                unset($obj[$key]);
            }
        }
        return $obj;
    }

    function insert_contact($array)
    {        
        $result = $this->db->insert($this->table, $array);
        if($result){
            $last_insert_id = $this->db->insert_id();
            return $last_insert_id;
        }else{
            return false;
        }
    }

    function insert_eg_contact($array)
    {
        $this->db2->insert($this->table, $array);
        $last_insert_id = $this->db2->insert_id();
        return $last_insert_id;
    }
    function delete($id)
    {
        $this->db->where('campaign_id', $id);
        $this->db->delete($this->mapping_table);
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function insert_contact_lists($array)
    {
        $this->db->insert($this->mapping_table, $array);
        return $this->db->insert_id();
        /*$insert_query = $this->db->insert_string($this->mapping_table, $array);
        $insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
        if($this->db->query($insert_query)){
            return $this->db->insert_id();
        }else{
            return false;
        }*/
    }

    function email_exists($email)
    {
        $sql = 'SELECT id FROM ' . $this->table . ' WHERE email = ?';
        $query = $this->db->query($sql, array($email));

        if ($query->num_rows() == 0) {
            return false;
        } else {
            $array = $query->result();
            if (!empty($array)) {
                return  $array[0]->id; 
        }
    }
    }

    function eg_email_exists($email,$fields= 'id')
    {
        $this->table = 'contacts';
        $array = $this->db2->select($fields)->get_where($this->table, array('email' => $email))->row();
        if (!empty($array)) {
            if($fields == 'id'){
           return $array = $array->id;
            }
           return $array;
        }else{
            return false;
        }
    }
    
    function contactId_exists($id)
    {
        $sql = 'SELECT id FROM ' . $this->table . ' WHERE id = ?';
        $query = $this->db->query($sql, array($id));

        if ($query->num_rows() == 0) {
            return false;
        } else {
            return true;
        }
    }
    
    public function CheckIsQALead($campaignContactId = null){
        $sql = "SELECT cc.*,lh.id AS lead_id FROM ".$this->mapping_table." cc 
                LEFT JOIN ".$this->tmLeadHistoryTable." lh ON lh.campaign_contact_id =cc.id  AND 
                    (lh.status = 'QA in progress' OR  lh.status = 'Follow-up') AND lh.is_qa_in_progress = 1 AND lh.qa IS NOT NULL 
                WHERE 
                cc.id = ? ";
        $query = $this->db->query($sql,array($campaignContactId));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    function update_lead($id, $data)
    {
        $this->db->where('id', $id);
        $result = $this->db->update($this->tmLeadHistoryTable, $data);
        return $result;
    }
    
    function unloackContact($contactID,$logedInID){  
        $this->db->set('edit_lead_status','0');
        $this->db->set('locked_by','');
        $this->db->where('id', $contactID);
        $this->db->where('locked_by', $logedInID);
        $result=$this->db->update($this->table);
        return $result;
    }
    
    public function remove_edit_contact_data($data)
    {
        $campaignId = $data['campaign_id'];
        $list_id = $data['list_id'];
        $contacts = $data['IDs'];
        
        $customewhere = 'Where 1 = 1 ';
        
        $customewhere .= " AND cc.campaign_id = ".$campaignId;
        if($list_id!="")
            $customewhere .= " AND cc.list_id = ".$list_id;
        
        // Set Filters 
        
        if(!empty($data['company_size'])){
            $companySize = implode("','",$data['company_size']);

            $companySize = str_replace("1-9", "1 to 9", $companySize);
            $companySize = str_replace("10-24", "10 to 24", $companySize);

            $customewhere .= " AND (c.company_size in  ('".$companySize."')" ;

            $companySize_format = implode("','",$data['company_size']);
            $companySize_format = str_replace("1 to 9", "1-9", $companySize_format);
            $companySize_format = str_replace("10 to 24", "10-24", $companySize_format);

            $customewhere .= " OR c.company_size in  ('".$companySize_format."')) " ;
            /*$companySize = implode("','",$data['company_size']);
            $companySize = str_replace("1-9", "1 to 9", $companySize);
            $companySize = str_replace("10-24", "10 to 24", $companySize);

            $customewhere .= " AND c.company_size in  ('".$companySize."')" ;*/
        }
        if(!empty($data['job_function'])){
             $customewhere .= " AND c.job_function in  ('".implode("','",$data['job_function'])."')" ;
        }
        if(!empty($data['job_level'])){
             $customewhere .= " AND c.job_level in  ('".implode("','",$data['job_level'])."')" ;
        }
        if(!empty($data['industry'])){
             $customewhere .= " AND c.industry in  ('".implode("','",$data['industry'])."')" ;
        }
        if(!empty($data['country'])){
             $customewhere .= " AND c.country in  ('".implode("','",$data['country'])."')" ;
        }
        
        // To Check selected contacts  will be deleted  or all contacts will be deleted
        
        if(!empty($contacts)){
          if($data['checkedflage']){
            $customewhere .= " AND cc.id in(".$contacts.")";
            }else{
                $customewhere .= " AND cc.id not in(".$contacts.")";
            }
        }
        $sql = "delete cc from ".$this->mapping_table." cc left join ".$this->table." c ON c.id = cc.contact_id ";
        $sql .= "LEFT JOIN `lead_history` lh ON lh.`campaign_contact_id` = cc.id ";
        $customewhere .= " AND (cc.call_disposition_id is null OR cc.call_disposition_id=0) ";
        
        $sql .= $customewhere;
        
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function isContactExist($data){
        
        $this->db->where('id',$data['id']);
        $contact = $this->db->get($this->table)->row();
        if($contact){
            $this->db->where('contact_id',$data['id']);
            $this->db->where('campaign_id',$data['campaign_id']);
            $mapping_contact = $this->db->get($this->mapping_table)->row();
            if($mapping_contact){
                return $mapping_contact;
            }else{
                return 2;
            }
        }else{
            return 0;
        }
    }
    
    public function isContactExistMpg($data){
        
        $this->db->where('email',trim($data['email']));
        $contact = $this->db->get($this->table)->row();
        if($contact){
            $this->db->where('contact_id',$contact->id);
            $this->db->where('campaign_id',$data['campaign_id']);
            $mapping_contact = $this->db->get($this->mapping_table)->row();
            if($mapping_contact){
                return array(2, $contact->id);
            }else{
                return array(1, $contact->id);
            }
        }else{
            return 0;
        }
    }
    
    public function get_all_contacts($id,$where,$count=0, $list_id,$sidx= "",$sord= "asc",$start= "",$limit= ""){
        
        if($count)
            $sql = 'Select COUNT(*) AS count';
        else{
            $sql = 'Select c.id,cl.id as contact_list_id,cl.list_id,cl.source,CONCAT(c.first_name," ",c.last_name) as full_name,c.phone,c.email,c.company,c.time_zone,c.updated_at';
        }        
        
        $sql .= '
            FROM ' . $this->mapping_table . ' cl
            LEFT JOIN ' . $this->table . ' c ON c.id = cl.contact_id';
        $sql .= " WHERE original_owner != 'Netwise' AND cl.list_id =".$list_id;
        if($where!="")
        {
            $sql .= " AND ".$where;
        }
        
        if($count){
            $query = $this->db->query($sql);
    
            $result=$query->result();
        
            return $result[0]->count;
        }else{
            $sql .= " ORDER BY ".$sidx." ".$sord;
            $sql .= "  LIMIT ? OFFSET ?";
            $query =  $this->db->query($sql, array(intval($limit),intval($start)));

            $response = new stdClass();
            $i=0;
            if( count($query->result()) > 0 )
            {
                foreach ($query->result() as $key=>$row){
                    $response->rows[$i]['id']=$row->id;
                    $response->rows[$i]['cell']=$row;
                    $i++;
                }
            }
            return $response;
        }      
    }

    /* dev_kr Region End */

    /* Dev_NV Region Start */

    public function checkContactEmailExist($email, $contactID = null)
    {
        $this->db->select('email,id');
        $this->db->from($this->table);
        $this->db->where('email', $email);
        if (!empty($contactID) && $contactID > 0) {
            $this->db->where('id !=', $contactID);
        }
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            return false;
        } else {
            $array = $query->result();
            if (!empty($array)) {
                return  $array[0]->id; 
    }
        }
    }

    function check_multiple_lock_contact($logged_user_id,$contact_id=0)
    {
        $this->db->select('COUNT(*) as lock_contact_count,contacts.id as contact_id');
        
        
        $this->db->from($this->table);
        $this->db->where("locked_by", $logged_user_id);
        if($contact_id > 0)
            $this->db->where("id != ", $contact_id);

        $array = $this->db->get()->result_array();

        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function update_multiple_lock_contact($logged_user_id,$data)
    {
        $this->db->where('locked_by', $logged_user_id);
        $result = $this->db->update($this->table, $data);
        return $result;
    }

    //get campaign contacts for filter page
    
    function getCampaignContacts($campaign_id,$list_id,$IsNumRecord=0,$searchBy="",$limit = "", $offset = "", $sortField="",$order="",$report=0,$is_status_approve=0)       
    {
        if ($IsNumRecord) {
            $contactSQL = "SELECT count(*) as total_record ";
        } else {
            $func = 'getCampaignContactFields'.ucfirst($this->app);
            $fields = $this->$func();
            $contactSQL = "SELECT {$fields} ";
        }
        
        $contactSQL .= "FROM " .$this->mapping_table." cc ";
        $contactSQL .= "INNER JOIN ".$this->table." c ON c.id=cc.contact_id ";
        //$contactSQL .= "LEFT JOIN `lead_history` lh ON lh.`campaign_contact_id` = cc.id ";
        $contactSQL .= "WHERE  cc.campaign_id=".$campaign_id." AND cc.list_id=".$list_id."  AND (cc.call_disposition_id is null OR cc.call_disposition_id=0) ";

         if (!empty($searchBy)) {
            if (!empty($searchBy['company_size'])) {
                $companySize = implode("','",$searchBy['company_size']);
                
                $companySize = str_replace("1-9", "1 to 9", $companySize);
                $companySize = str_replace("10-24", "10 to 24", $companySize);

                $contactSQL .= " AND (c.company_size in  ('".$companySize."')" ;

                $companySize_format = implode("','",$searchBy['company_size']);
                $companySize_format = str_replace("1 to 9", "1-9", $companySize_format);
                $companySize_format = str_replace("10 to 24", "10-24", $companySize_format);

                $contactSQL .= " OR c.company_size in  ('".$companySize_format."')) " ;
            }   

            if (!empty($searchBy['job_function'])) {
                $contactSQL .= " AND c.job_function in  ('".implode("','",$searchBy['job_function'])."')" ;
            }
            if (!empty($searchBy['job_level'])) {
                $contactSQL .= " AND c.job_level in  ('".implode("','",$searchBy['job_level'])."')" ;
            }

            if (!empty($searchBy['industry'])) {
                $contactSQL .= " AND c.industry in  ('".implode("','",$searchBy['industry'])."')" ;
            }
            if (!empty($searchBy['country'])) {
                $contactSQL .= " AND c.country in  ('".implode("','",$searchBy['country'])."')" ;
            }           
        } 

    $contactSQL .= " AND c.original_owner != 'Netwise' "; 
           
        if ($IsNumRecord) {
            $query = $this->db->query($contactSQL);
            $result=$query->result();
        
            return $result[0]->total_record;
        }
        
        $sort = ' ORDER BY c.'.$sortField;
        $contactSQL .= $sort . ' ' . $order . ' ';
        $contactSQL .= "LIMIT ? OFFSET ?";
            
        $query = $this->db->query($contactSQL, array($limit, $offset));
        return $query->result();
    }

    public function update_campaign_contacts_filter($campaign_id,$list_id,$searchBy,$edit_campaign_filter_by){

        $filter_status = '1';
        if(empty($edit_campaign_filter_by)){
            $filter_status = '0';
        }
        $update_filter_sql = " UPDATE " .$this->mapping_table." ccnt INNER JOIN ".$this->table." c ON ccnt.contact_id = c.id SET filter_status = $filter_status
                               WHERE ccnt.campaign_id=".$campaign_id." AND ccnt.list_id=".$list_id;

        if (!empty($searchBy))
        {
            if(!empty($searchBy['company_size'])){
                $companySize = implode("','",$searchBy['company_size']);

                $companySize = str_replace("1-9", "1 to 9", $companySize);
                $companySize = str_replace("10-24", "10 to 24", $companySize);

                $update_filter_sql .= " AND (c.company_size in  ('".$companySize."')" ;

                $companySize_format = implode("','",$searchBy['company_size']);
                $companySize_format = str_replace("1 to 9", "1-9", $companySize_format);
                $companySize_format = str_replace("10 to 24", "10-24", $companySize_format);

                $update_filter_sql .= " OR c.company_size in  ('".$companySize_format."')) " ;
                /*$companySize = implode("','",$searchBy['company_size']);

                $companySize = str_replace("1-9", "1 to 9", $companySize);
                $companySize = str_replace("10-24", "10 to 24", $companySize);

                $update_filter_sql .= " AND c.company_size in  ('".$companySize."')" ;*/
            }
            if(!empty($searchBy['job_function'])){
                $update_filter_sql .= " AND c.job_function in  ('".implode("','",$searchBy['job_function'])."')" ;
            }
            if(!empty($searchBy['job_level'])){
                $update_filter_sql .= " AND c.job_level in  ('".implode("','",$searchBy['job_level'])."')" ;
            }

            if(!empty($searchBy['industry'])){
                $update_filter_sql .= " AND c.industry in  ('".implode("','",$searchBy['industry'])."')" ;
            }
            if(!empty($searchBy['country'])){
                $update_filter_sql .= " AND c.country in  ('".implode("','",$searchBy['country'])."')" ;
            }
        }
        $query = $this->db->query($update_filter_sql);
        if(!$query){
            return false;
        }else{
            return true;
        }
    }

    function getCampaignContactFieldsEg(){
        return "c.id,c.first_name,c.last_name,c.phone,c.company,c.job_function,c.job_level,c.company_size,c.industry,c.country,c.time_zone,cc.id as campaign_contact_id";
    }
    
    function getCampaignContactFieldsMpg(){
        return "c.id,c.first_name,c.last_name,c.phone,c.company,c.job_level,c.country,c.time_zone,cc.id as campaign_contact_id";
    }
    /* Dev_NV Region END*/
    /* Dev_RP Region START*/
        public function EmailContactDetails($email, $contactID = null, $field = 'id', $ismanual=false, $bulk = '')
        {
            if ($bulk != '') {
                $sql = "SELECT " . $field . " FROM " . $this->table . " ";
                $sql .= "WHERE email IN(" . $email .")";
                $query = $this->db->query($sql);
                return $query->result();
            }else{
                $this->db->select($field);
                $this->db->from($this->table);
                $this->db->where('email', $email);
                $this->db->limit(1);
                if (!empty($contactID) && $contactID > 0) {
                    $this->db->where('id !=', $contactID);
                }
                $query = $this->db->get()->result_array();

                if ($ismanual) {
                    if (!empty($query[0])) {
                        if (!$query[0]['do_not_call_ever']) {
                            //check eg do not call ever
                            $eg_member_info = $this->get_eg_members($email, 'do_not_call');
                            if ($eg_member_info['do_not_call']) {
                                $query[0]['do_not_call_ever'] = $eg_member_info['do_not_call'];

                            }
                        }
                        return $query[0];
                    } else {
                        //check eg do not call ever
                        $eg_member_info = $this->get_eg_members($email, 'do_not_call');
                        if ($eg_member_info['do_not_call']) {
                            $query[0]['do_not_call_ever'] = $eg_member_info['do_not_call'];
                            return $query[0];
                        }
                    }
                } else {
                    if (!empty($query[0])) {
                        return $query[0];
                    }
                }
            }
            return 0;
            
        }

        public function checkEmailExist($email)
        {
            $sql = "
              SELECT 
                    c.id, c.do_not_call_ever
                FROM
                    contacts c                         
                WHERE
                    c.email = ?
                UNION
                SELECT 
                     c.id, c.do_not_call_ever
                FROM
                    contacts c 
                        LEFT JOIN
                    members_qa mq ON c.member_id = mq.id
                        
                WHERE
                    mq.email = ?
                ";                    
            $query = $this->db->query($sql,array($email, $email));
            $result = $query->result();
            if (!empty($result[0])) {
                return $result[0];
            }
            return 0;
        }


        public function campaign_contact_id($data){
            $this->db->select('id,list_id');
            $this->db->from($this->mapping_table);
            $this->db->where('contact_id',$data['id']);
            $this->db->where('campaign_id',$data['campaign_id']);
            $query = $this->db->get()->result_array();
            return $query[0];
        }
        
        public function getContactIdByCampaignContactId($campaignContactId)
        {
            $this->db->select('id,contact_id,campaign_id');
            $this->db->from($this->mapping_table);
            $this->db->where('id',$campaignContactId);
            $query = $this->db->get()->result_array();
            return $query[0];
        }
        
        
    /* Dev_RP Region END*/

    function get_eg_members($email, $fld = '*'){
        $this->db2->select($fld);
        $this->db2->from($this->egMembers);
        $this->db2->where('email', $email);
        $this->db2->limit(1);
        $query = $this->db2->get()->result_array();
        if(!empty($query[0])){
            return $query[0];
        }
        return 0;
    }

    function is_locked_by_other($contact_id,$uid) {
        $this->db->select('id');
        $this->db->from($this->table);
        $this->db->where("locked_by > 0 and locked_by IS NOT NULL");
        $this->db->where('id', $contact_id);
        $this->db->where('locked_by !=', $uid);

        $query = $this->db->get()->result_array();

        return !empty($query[0]) ? 1 : 0;
    }
   
    function create_history( $contact_id ){
        $sql_source = "SELECT `id`, `member_id`, `email`, `first_name`, `last_name`, `job_title`, `job_level`, `job_function`, `company`, `address`, `city`, `zip`, `state`, `country`, `industry`, `company_size`, `company_revenue`, `phone`, `ext`, `alternate_no`, `notes`, `time_zone`, `priority`, `edit_lead_status`, `locked_by`, `do_not_call_ever`, `original_owner`, `created_at`, `updated_at`, `updated_by` ";
        $sql_source .= "FROM " . $this->table . " ";
        $sql_source .= "WHERE id = " . $contact_id . " ";
        
        $sql_insert = "INSERT INTO `contact_history` (`contact_id`,`member_id`,`email`,`first_name`,`last_name`,`job_title`,`job_level`,`job_function`,`company`,`address`,`city`,`zip`,`state`,`country`,`industry`,`company_size`,`company_revenue`,`phone`,`ext`,`alternate_no`,`notes`,`time_zone`,`priority`,`edit_lead_status`,`locked_by`,`do_not_call_ever`,`original_owner`,`created_at`,`updated_at`,`updated_by`) ";
        $sql_insert .= $sql_source;
        
        $query = $this->db->query($sql_insert);
    }
    
    function getContactIdByEmail( $emails = array(), $table = 'appt_contacts' ){
        $response = array();
        $email = implode( ",", $emails );
        
        $sql = "SELECT `id` FROM `" . $table . "` ";
        $sql .= "WHERE `email` IN ( " . $email . ")";
        
        $query =  $this->db->query($sql);
        
        if( count( $query->result() ) > 0 ){
            foreach ($query->result() as $key=>$row){
                $response['contact_id'][] = $row->id;    
            }
        }
        
        return $response;
    }
    
    /* replacement of get_list_count */
    
    function get_workable_contacts_count($id = "", $userType = "",$where,$call_disposition_filter_flag,$lead_status_filter_flag, $list_id) 
    {
        $callLimit = $this->config->item('call_limit');

        //get contact_filter
        $contact_filter = $this->get_contact_filter($list_id);
        $build_filter = "";
        if(!empty($contact_filter)){
            $contact_filters = explode("|", $contact_filter);
            $build_filter = array();
            foreach($contact_filters as $filter){
                $get_filter = explode(":", $filter);
                $build_filter[] = "c.{$get_filter[0]} in ('{$get_filter[1]}') ";
            }
            $build_filter = implode(" AND ", $build_filter);
            $build_filter = " AND {$build_filter} ";
        }

        $sql = "Select DISTINCT(c.id) AS count FROM ".$this->mapping_table." cl JOIN ".$this->table." c ON c.id = cl.contact_id LEFT JOIN ".$this->membersQaTable." m ON c.member_id = m.id 
            LEFT JOIN ".$this->tmLeadHistoryTable." tlh ON cl.id = tlh.campaign_contact_id
            LEFT JOIN users u ON u.id = tlh.agent_id LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country) 
            LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone))
            WHERE (dnf.count < 3 OR dnf.count IS NULL) 
            AND cl.list_id =".$list_id;
        if(!$call_disposition_filter_flag) {
            $sql .= " AND c.do_not_call_ever = 0 ";
        }
        $sql .=" AND c.original_owner != 'Netwise' ";
        if($where!="") {
            $sql .= " AND ".$where. " {$build_filter}";
        }else{
            $sql .= " {$build_filter}";
        }
        if ($userType == 'qa') {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag){
                $sql .= ' AND cl.call_disposition_id not in (7,2,11,14,15,16,17,18,19,20,21,22,23,24,25) ';
                $sql .= " AND tlh.status in ('Pending','QA in progress','Follow-up') ";
            }
        } else {
            if(!$call_disposition_filter_flag && !$lead_status_filter_flag) {
                $sql .= ' AND (cl.call_disposition_id not in (1,7,2,11,14,15,16,17,18,19,20,21,22,23,24,25) || cl.call_disposition_id IS null) ';
                $sql .= " AND (tlh.status in ('','In Progress','Follow-up') OR  tlh.status IS NULL)";
            }
        }
        if($userType == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        
        $query = $this->db->query($sql);
        $result=$query->result();
        return count($result);
    }
    
    
    function getContactsByDispositionId($id = "", $logged_user_id = "", $where, $sidx, $sord, $start, $limit, $list_id, $disposition_id)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = ' WHERE 1=1 ';

        if ($logged_user_type == 'agent') {
            $customWhere .= "AND ( u.id = {$logged_user_id} OR  u.status <> 'Active' OR DATE_SUB(NOW(), INTERVAL 2 DAY) >= cl.`call_disposition_update_date`)";
        }
        
        if ($logged_user_type == 'team_leader') {
            $customWhere .= ' AND (u.id = '.$logged_user_id.' OR u.parent_id = '.$logged_user_id.')';
        }
        if ($logged_user_type == 'manager') {
            $customWhere .= ' AND (u.id = '.$logged_user_id.' ';
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }

        $sql = "SELECT DISTINCT(c.id),lockeduser.user_type,c.phone,c.company,c.time_zone,cl.id as contact_list_id, ";
        $sql .= "c.edit_lead_status,c.locked_by,cl.call_disposition_id AS call_disposition_id,tlh.status,cl.call_disposition_update_date as callback_date, ";
        $sql .= "cd.calldisposition_name AS calldisposition_name, ";
        $sql .= "CONCAT(c.first_name,' ',c.last_name) as full_name,u.id as user_id, ";
        $sql .= "CONCAT(u.first_name, ' ', u.last_name) AS agent_name, ";
        $sql .= "cdh.user_id AS userid, ";
        $sql .= "cl.notes as note ";
        $sql .= "FROM " . $this->mapping_table . " cl ";
        $sql .= "JOIN " . $this->table . " c ON c.id = cl.contact_id ";
        $sql .= "LEFT JOIN " . $this->tmLeadHistoryTable . " tlh ON cl.id = tlh.campaign_contact_id ";
        $sql .= "LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ";
        //$sql .= "LEFT JOIN " .$this->tmNotesTable . " notes ON notes.lead_history_id = tlh.id AND notes.id IN (SELECT MAX(id) FROM " . $this->tmNotesTable . " GROUP BY lead_history_id) ";
        $sql .= "LEFT JOIN " . $this->callDispositionsTable . " cd ON cl.call_disposition_id = cd.id ";
        $sql .= "LEFT JOIN " . $this->users_table . "  AS u ON u.id=cdh.user_id ";
        $sql .= "LEFT JOIN " . $this->users_table . " lockeduser ON c.locked_by = lockeduser.id ";
        $sql .= $customWhere;
        
        if ($where!="") {
            $sql .= " AND " . $where;
        }
        
        if (!empty($id)) {
            $sql .= " AND cl.list_id=".$list_id ;
        }
        
        $sql .= " AND cl.call_disposition_id = " . $disposition_id . " AND c.id IS NOT NULL AND c.do_not_call_ever <> 1 ";
        if($logged_user_type == 'agent')
        {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        $sql .= " GROUP BY c.id";
        $sql .= " ORDER BY ".$sidx." ".$sord;   
        $sql .= " LIMIT ?,?";

        $query =  $this->db->query($sql, array(intval($start),intval($limit)));
        
        $response = new stdClass();

        $i=0;
        if (count($query->result())>0) {
            foreach ($query->result() as $key=>$row) {
                $response->rows[$i]['id']=$row->id;
                $response->rows[$i]['cell']=$row;
                $i++;
            }
        }
        
        return $response;
    }
    
    function getContactsByDispositionIdCount($id = "", $logged_user_id = "", $where, $list_id, $disposition_id)
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $customWhere = ' WHERE 1=1 ';
        
        if ($logged_user_type == 'agent') {
            $customWhere .= "AND ( u.id = {$logged_user_id} OR  u.status <> 'Active' OR DATE_SUB(NOW(), INTERVAL 2 DAY) >= cl.`call_disposition_update_date`)";
        }
        if ($logged_user_type == 'team_leader') {
            $customWhere .= ' AND (u.id = '.$logged_user_id.' OR u.parent_id = '.$logged_user_id.')';
        }
        if ($logged_user_type == 'manager') {
            $customWhere .= ' AND (u.id = '.$logged_user_id.' ';
            $customWhere .= " OR u.telemarketing_offices = '" . $logged_tm_office . "') ";
        }

        $sql = 'SELECT DISTINCT(c.id) ';
        $sql .= 'FROM ' . $this->mapping_table . ' cl ';
        $sql .= 'LEFT JOIN ' . $this->table . ' c ON c.id = cl.contact_id ';
        //$sql .= 'LEFT JOIN ' . $this->tmLeadHistoryTable . ' tlh ON cl.id = tlh.campaign_contact_id ';
        $sql .= 'LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ';
        //$sql .= 'LEFT JOIN ' . $this->tmNotesTable .' notes ON notes.lead_history_id = tlh.id AND notes.id IN (SELECT MAX(id) FROM '.$this->tmNotesTable.' GROUP BY lead_history_id) ';
        $sql .= 'LEFT JOIN ' . $this->callDispositionsTable . ' cd ON cl.call_disposition_id = cd.id ';
        $sql .= 'LEFT JOIN ' . $this->users_table . ' u ON cdh.`user_id` = u.id ';
        $sql .= 'LEFT JOIN ' . $this->users_table . ' lockeduser ON c.locked_by = lockeduser.id ';

        $sql .= $customWhere;

        if ($where!="") {
            $sql .= " AND ".$where;
        }
        if (!empty($id)) {
            $sql .= " AND cl.list_id=".$list_id ;
        }
        
        $sql .= " AND cl.call_disposition_id = " . $disposition_id . "  AND c.id IS NOT NULL AND c.do_not_call_ever <> 1 ";
        
        if ($logged_user_type == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        
        $query = $this->db->query($sql);
        $result=$query->result();

        return count($result);
    }
    
    public function insertCampaignContacts($values){
        $sql = "INSERT IGNORE INTO " . $this->mapping_table . " (campaign_id,contact_id,list_id,created_by,created_at) VALUES %s ";
        $sql .= "ON DUPLICATE KEY UPDATE `campaign_id` = VALUES(campaign_id),`contact_id` = VALUES(contact_id),`list_id` = VALUES(list_id),`created_by` = VALUES(created_by),`created_at` = VALUES(created_at)";
        $sql = sprintf($sql, implode(",", $values));
        
        try {
            $result = $this->db->query($sql);
            return 1;                                    
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function unlockContactByUserId($userId){
        $this->db->set('edit_lead_status','0');
        $this->db->set('locked_by','');
        $this->db->where('locked_by', $userId);
        $result=$this->db->update($this->table);
        return $result;
    }
    
    public function getEGContactDetailByEmail($email,$fields = "*"){
        $this->db2->select($fields);
        $this->db2->from('contacts');
        $this->db2->where('email', $email);
        $this->db2->limit(1);
        $query = $this->db2->get();
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
}
        return $array;
    }

    public function getEGClientConsent($email,$egCampaignId,$fields = "cc.*, c.email, c.id, c.pureb2b_consent"){
        $sql = "select {$fields} from contacts c join client_consent cc on c.id = cc.contact_id ";
        $sql .= "join campaigns camp on camp.client = cc.client_id ";
        $sql .= "where c.email = ? and camp.id = ? limit 1";
        $query = $this->db2->query($sql, array($email, $egCampaignId));
        $array=$query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    
    public function savePureb2bConsent($pureb2bConsentSubmitted,$data){
        $pureb2bConsentValue = ($pureb2bConsentSubmitted == 'yes') ? 1:0;
        unset($data->created_at);
        unset($data->member_id);
        $eg_contact_detail = (array) $data;
        $contactDetailFields =  implode(",", array_keys((array) $eg_contact_detail));
        $contactDetailValues =  "'" . implode("','", $eg_contact_detail) . "'";
        $contactDetailValues .= ",NOW(),{$pureb2bConsentValue},NOW()";
        $sql = "insert into contacts ({$contactDetailFields} ";
        $sql .= ",created_at,pureb2b_consent,pureb2b_consent_updated_at) ";
        $sql .= "VALUES ({$contactDetailValues}) ";
        $sql .= "ON DUPLICATE KEY UPDATE ";
        $sql .= "pureb2b_consent_updated_at = IF(pureb2b_consent = Values(pureb2b_consent), pureb2b_consent_updated_at, NOW()), ";
        $sql .= "pureb2b_consent = Values(pureb2b_consent)";
        return $query = $this->db2->query($sql);
    }
    
    public function saveClientConsent($contactId,$data){
        $clientConsentValue = ($_POST['clientConsent'] == 'yes') ? 1:0;
            //check if contact_id exists for client on client_conset table,
            //insert if not existing
            $this->db2->select('id,consent');
            $this->db2->from('client_consent');
            $this->db2->where('contact_id', $contactId);
            $this->db2->where('client_id', $data['client_id']);
            $this->db2->limit(1);
            $query = $this->db2->get();
            $array = $query->result();
        if (!empty($array)) {
            //update updated_at if consent submitted does not match with existing data
            if($array[0]->consent != $clientConsentValue){
                $this->db2->set('consent', $clientConsentValue);
                $this->db2->set('updated_at',date('Y-m-d H:i:s', time()));
                $this->db2->where('id', $array[0]->id);
                $result=$this->db2->update('client_consent');
            }
        }else{
            $sql = "insert into client_consent (contact_id, client_id, consent, created_at, updated_at) ";
            $sql .= "VALUES ({$contactId},'{$data['client_id']}',{$clientConsentValue},NOW(),NOW()) ";
            $result = $this->db2->query($sql);
        }
    }
    
    function updateEgContactsPureb2bConsent($contactID,$pureb2bConsent,$pureb2bConsentUpdatedAt){
        $pureb2bConsent = ($pureb2bConsent == 'yes') ? 1:0;
        $this->db2->set('pureb2b_consent',$pureb2bConsent);
        $this->db2->set('pureb2b_consent_updated_at',$pureb2bConsentUpdatedAt);
        $this->db2->where('id', $contactID);
        $result=$this->db2->update('contacts');
        return $result;
        }
        
    function checkPreviousCallIfDisposed($userId){
        $sql = "select * from (SELECT 
                chcc.campaign_contact_id, cc.list_id, cc.campaign_id, chcc.call_disposition_id
            FROM
                call_history_campaign_contact chcc
                    JOIN
                campaign_contacts cc ON chcc.campaign_contact_id = cc.id
                    JOIN
                call_history ch ON ch.id = chcc.call_history_id
                    JOIN 
                contacts c on c.id = cc.contact_id
            WHERE
                ch.user_id = {$userId}
                AND (c.locked_by is null or c.locked_by = '' or c.locked_by = 0 or c.locked_by = {$userId})
                AND cc.call_disposition_id not in (1,7,11,14,15,16,17,18,20,21,22,23,24,25)
            ORDER BY chcc.id DESC
            LIMIT 1) as prev
            where prev.call_disposition_id = 0";
        $query = $this->db->query($sql);
        if($query){
            $result = $query->result();
            return !empty($result[0]) ? $result[0] : null;
        }
        return null;
        
    }
    
    function checkContactCallIfDisposed($contactId){
        $sql = "select * from (SELECT 
                chcc.campaign_contact_id, cc.list_id, cc.campaign_id, chcc.call_disposition_id,
                (select user_type from users where id = c.locked_by) as userType,
                (select status from users where id = c.locked_by) as userStatus
            FROM
                call_history_campaign_contact chcc
                    JOIN
                campaign_contacts cc ON chcc.campaign_contact_id = cc.id
                    JOIN
                call_history ch ON ch.id = chcc.call_history_id
                    JOIN 
                contacts c on c.id = cc.contact_id
            WHERE
                c.id = {$contactId}
                AND c.locked_by > 0
            ORDER BY chcc.id DESC
            LIMIT 1) as prev
            where prev.call_disposition_id = 0";
        $query = $this->db->query($sql);
        if($query){
            $result = $query->result();
            return !empty($result[0]) ? $result[0] : null;
        }
        return null;
        
    }

    function insertUnlockLog($userId, $contactId, $campaignId, $source)
    {
        $data = array(
            'user_id' => $userId,
            'contact_id' => $contactId,
            'campaign_id' => $campaignId,
            'created_at' => date('Y-m-d H:i:s', time()),
            'source' => $source
        );
        return $this->db->insert($this->unlockContactLog, $data);
    }

    function getCampaigContactDetails($filters, $fields, $setLimit = false)
    {
        $this->db->select($fields);
        $this->db->from($this->mapping_table.' AS cc');
        $this->db->join('contacts as c', 'c.id = cc.contact_id');
        $this->db->join('members_qa as mq', 'c.member_id = mq.id', 'left');
        foreach($filters as $filter){
            $this->db->where($filter);
        }
        $this->db->order_by("c.id", "desc");
        $setLimit = (int) $setLimit;
        if($setLimit){
            $this->db->limit($setLimit);    
        }
        
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }

    // Start RP - UAD-81 - get follw-up, callback and lifted contacts
    function getAutoContacts($id = "", $userType = "", $where, $sidx, $sord, $start, $limit, $call_disposition_filter_flag, $lead_status_filter_flag)
    {
        $callLimit = $this->config->item('call_limit');

        $sql = "Select c.id,cl.list_id,lockeduser.user_type,cl.source,c.phone,c.company,c.time_zone,cl.id as contact_list_id ,c.edit_lead_status,c.locked_by,cl.call_disposition_id AS call_disposition_id, ";
        $sql .= "tlh.status,cl.call_disposition_update_date as callback_date,cd.calldisposition_name AS calldisposition_name,CONCAT(c.first_name,' ',c.last_name) as full_name, ";
        $sql .= "CONCAT(u.first_name,' ',u.last_name) AS agent_name,cl.notes as note ";
        $sql .= "FROM ".$this->mapping_table." cl ";
        $sql .= "JOIN ".$this->table." c ON c.id = cl.contact_id ";
        $sql .= "LEFT JOIN ".$this->membersQaTable." m ON c.member_id = m.id ";
        $sql .= "LEFT JOIN ".$this->tmLeadHistoryTable." tlh ON cl.id = tlh.campaign_contact_id ";
        //$sql .= "LEFT JOIN ".$this->tmNotesTable." notes ON notes.campaign_contact_id = cl.id AND notes.id IN (SELECT MAX(id) FROM ".$this->tmNotesTable." GROUP BY campaign_contact_id) ";
        $sql .= "LEFT JOIN ".$this->callDispositionsTable." cd ON cl.call_disposition_id = cd.id ";
        $sql .= "LEFT JOIN call_disposition_history cdh ON cdh.campaign_contact_id = cl.id AND cdh.id = (SELECT MAX(id) FROM call_disposition_history where campaign_contact_id = cl.id) ";
        $sql .= "LEFT JOIN ".$this->users_table ." AS u ON u.id=cdh.user_id ";
        $sql .= "LEFT JOIN ".$this->users_table." lockeduser ON c.locked_by = lockeduser.id ";
        $sql .= "LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country) ";
        $sql .= "LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone)) ";
        $sql .= "WHERE (dnf.count < 3 OR dnf.count IS NULL) ";
        $sql .= " AND cl.campaign_id =". $id;

        if ( !$call_disposition_filter_flag) {
            $sql .= " AND c.do_not_call_ever = 0 ";
        }
        $sql .=" AND c.original_owner != 'Netwise' ";
        if ($where != "") {
            $sql .= " AND ".$where;
        }

        $sql .= " AND (tlh.status ='Follow-up' OR  cl.call_disposition_id = 2 OR cl.lifted =1)";

        if ($userType == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }
        $sql .= " GROUP BY c.id ORDER BY (case when c.locked_by = ".$this->session->userdata('uid')." then 1 else 0 end) desc, ".$sidx." ".$sord . " ";
        $sql .= "  LIMIT ? OFFSET ?";

        $query =  $this->db->query($sql, array(intval($limit),intval($start)));//echo $this->db->last_query();exit;
        $response = new stdClass();

        $i=0;
        if (count($query->result())>0) {
            foreach ($query->result() as $key=>$row) {
                $response->rows[$i]['id']=$row->id;
                $response->rows[$i]['cell']=$row;
                $i++;
            }
        }
        return $response;
    }
    
    function getAutoContactsCount($id = "", $userType = "", $where, $call_disposition_filter_flag, $lead_status_filter_flag)
    {
        $callLimit = $this->config->item('call_limit');

        $sql = "Select DISTINCT(c.id) AS count FROM ".$this->mapping_table." cl JOIN ".$this->table." c ON c.id = cl.contact_id LEFT JOIN ".$this->membersQaTable." m ON c.member_id = m.id
            LEFT JOIN ".$this->tmLeadHistoryTable." tlh ON cl.id = tlh.campaign_contact_id
            LEFT JOIN users u ON u.id = tlh.agent_id LEFT JOIN countries cou ON cou.country_code = IF(m.country != '', m.country, c.country)
            LEFT JOIN dialed_numbers_fortheday dnf ON dnf.phone=CONCAT(cou.dial_code,IF(m.phone != '', m.phone, c.phone))
            WHERE (dnf.count < 3 OR dnf.count IS NULL)";
        $sql .= " AND cl.campaign_id =". $id;
        if ( !$call_disposition_filter_flag) {
            $sql .= " AND c.do_not_call_ever = 0 ";
        }
        $sql .=" AND c.original_owner != 'Netwise' ";
        if ($where != "") {
            $sql .= " AND ".$where;
        }

        $sql .= " AND (tlh.status ='Follow-up' OR  cl.call_disposition_id = 2 OR cl.lifted =1)";

        if ($userType == 'agent') {
            $sql .= " AND (c.locked_by = '".$this->session->userdata('uid')."' || c.locked_by IS NULL || c.locked_by = '') ";
        }

        $query = $this->db->query($sql);
        $result=$query->result();
        return count($result);
    }
    // End RP - UAD-81 - get follw-up, callback and lifted contacts
}

class ContactsTable
{

    public $email;
    public $member_id;
    public $first_name;
    public $last_name;
    public $job_title;
    public $job_level;
    public $job_function;
    public $company;
    public $address;
    public $city;
    public $zip;
    public $state;
    public $country;
    public $industry;
    public $company_size;
    public $phone;
    public $alternate_no;
    public $notes;
    public $time_zone;
    public $created_at;
    public $updated_at;
}

class ContactsTableMpg
{

    public $member_id;
    public $email;
    public $priority;
    public $first_name;
    public $last_name;
    public $job_title;
    public $job_level;
    public $company;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $time_zone;
    public $phone;
    public $alternate_no;
    public $bed_size;
    public $employee_size;
    public $notes;
    public $created_at;
    public $updated_at;
    public $edit_lead_status;
}

class EgContactTable
{
    public $email;
    public $member_id;
    public $first_name;
    public $last_name;
    public $job_title;
    public $job_level;
    public $company_name;
    public $address1;
    public $city;
    public $zip;
    public $state;
    public $country;
    public $industry;
    public $company_size;
    public $phone;
    public $created_at;
    public $updated_at;
}
