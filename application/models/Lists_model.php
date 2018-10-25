<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Lists_model extends CI_Model {

    public $table = 'campaign_lists';
    public $contact_table = 'contacts';
    public $campaign_contacts = 'campaign_contacts';
    public $campaign_table = 'campaigns';
    public $users_table = 'users';
    public $call_list_table = 'contact_lists';
    public $campaign_lists_table = 'campaign_lists';
    public $campaign_list_dupes_history_table = 'campaign_list_dupes_history';
    public $tmLeadHistoryTable = 'lead_history';

    function __construct() {
        parent::__construct();
        $this->load->database();

        $this->contact_table = 'contacts';
        $this->campaign_contacts = 'campaign_contacts';
    }

    function get_all_list_by_campaign($campaign_id, $userType = '') {
        $userType = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        $sql = 'SELECT al.id as list_id,al.campaign_id,al.list_name,al.created_at,al.status,al.contact_filter,
                    (SELECT COUNT(1)
                    FROM '.$this->campaign_contacts.' apcc
                    JOIN '.$this->contact_table.' apc ON apc.id = apcc.contact_id
                    WHERE apcc.list_id = al.id AND apcc.campaign_id = '.$campaign_id.' and apc.original_owner != "Netwise" ) AS contact_list_count
                FROM '.$this->table.' al 
                WHERE al.campaign_id = ? ';
        
        if ($userType == 'agent') {
            $sql .= " AND `status`='Active' ";
        }
        
        $sql .='  GROUP BY al.id ORDER BY al.id DESC ';
        
        $query = $this->db->query($sql, array($campaign_id));
        $result = $query->result();
        foreach ($result as $key => $value) {
            $where = "";
            if(!empty($value->contact_filter)){
               $contact_filter = $value->contact_filter;
                if(!empty($contact_filter)){
                    $contact_filters = explode("|", $contact_filter);
                    $build_filter = array();
                    foreach($contact_filters as $filter){
                        $get_filter = explode(":", $filter);
                        $build_filter[] = "{$get_filter[0]} in ('{$get_filter[1]}') ";
                    }
                    $where = " AND " . implode(" AND ", $build_filter);
                } 
            }
            $list_id = $value->list_id;
            //$sub_sql = 'SELECT COUNT(*)AS workable_count_list FROM '.$this->campaign_contacts.' WHERE campaign_id ="'.$campaign_id.'" AND list_id ="'.$list_id.'" AND workable_status = "W" ';
            if($this->app_module_name == 'dialer'){
                $sub_sql = "SELECT COUNT(1) AS workable_count_list FROM  {$this->contact_table} AS ac "
                         . "JOIN  {$this->campaign_contacts} AS acc ON ac.id = acc.contact_id "
                         . "WHERE acc.campaign_id ='$campaign_id' AND acc.list_id ='$list_id' "
                         . "AND ac.do_not_call_ever = 0 and ac.original_owner <> 'Netwise' "
                         . "AND ((acc.call_disposition_id NOT IN (1,7,11,14,15,16,17,18,19,20,21,22,23,24,25)";
                $sub_sql .= $where . ")";
                $sub_sql .= " OR acc.call_disposition_id =2 ) ";
            }else{
                $sub_sql = 'SELECT COUNT(1) AS workable_count_list FROM '.$this->campaign_contacts.' AS acc JOIN '.$this->contact_table.' AS ac ON ac.id = acc.contact_id WHERE acc.campaign_id ="'.$campaign_id.'" AND acc.list_id ="'.$list_id.'" AND acc.workable_status = "W" AND ac.do_not_call_ever = 0 AND acc.call_disposition_id <> 2 ';
                $sub_sql .= " and ac.original_owner != 'Netwise' ";
                $sub_sql .= $where;
            }//echo $sub_sql . ";<br><br><br>";
            
            $sub_query = $this->db->query($sub_sql);
            $sub_result = $sub_query->result();
            $listresult = isset($sub_result) ? $sub_result[0] : "";
            $workable_list = $listresult->workable_count_list;
            $result[$key]->workable_count_list = $workable_list;
        }
        
        return $result;
    }
    
    function getAllListByCampaignWithoutCount($campaign_id, $userType = '') {
        $userType = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        $sql = 'SELECT al.id as list_id,al.campaign_id,al.list_name,al.created_at,al.status,
                    ""  AS contact_list_count
                FROM '.$this->table.' al
                WHERE al.campaign_id = ? ';

        if ($userType == 'agent') {
            $sql .= " AND `status`='Active' ";
        }

        $sql .='  GROUP BY al.id ORDER BY al.id DESC ';

        $query = $this->db->query($sql, array($campaign_id));
        $result = $query->result();
        foreach ($result as $key => $value) {
                        $result[$key]->workable_count_list = "";
        }

        return $result;
    }
    
    function getAllListByCampaign($campaign_id, $userType = '', $recsPerPage = '', $offset = '') {
        $userType = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        $sql = "SELECT 
                    al.id AS list_id,
                    al.campaign_id,
                    al.list_name,
                    al.created_at,
                    al.status,
                    al.contact_filter
                FROM
                    campaign_lists al
                WHERE
                    al.campaign_id = ?";
        
        if ($userType == 'agent') {
            $sql .= " AND `status`='Active' ";
        }
        
        $sql .='  GROUP BY al.id ORDER BY al.id DESC ';
        if(!empty($recsPerPage)){
            $sql .= " LIMIT {$recsPerPage} OFFSET {$offset}";
        }
        $this->db->trans_start();
        $this->db->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;');
        $query = $this->db->query($sql, array($campaign_id));
        $result = $query->result();
        $whereArray = array();
        $newResult = array();
        $listIds = array();
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $where = "";
                $list_id = $value->list_id;
                if(!empty($value->contact_filter)){
                   $contact_filter = $value->contact_filter;
                    if(!empty($contact_filter)){
                        $contact_filters = explode("|", $contact_filter);
                        $build_filter = array();
                        foreach($contact_filters as $filter){
                            $get_filter = explode(":", $filter);
                            $build_filter[] = "{$get_filter[0]} in ('{$get_filter[1]}') ";
                        }
                        $where =  implode(" AND ", $build_filter);
                    }
                    $whereArray[$list_id] = " ({$where} AND acc.list_id = {$list_id} AND acc.call_disposition_id NOT IN (1,7,11,14,15,16,17,18,19,20,21,22,23,24,25) )";
                }else{
                    $whereArray[$list_id] = " (acc.list_id = {$list_id} AND acc.call_disposition_id NOT IN (1,7,11,14,15,16,17,18,19,20,21,22,23,24,25)) ";
                }
                $listIds[] = $list_id;
                $value->contact_list_count = 0;
                $newResult[$list_id] = $value;
                $newResult[$list_id]->workable_count_list = 0;
            }
            $whereClause = "";
            if(!empty($whereArray)){
                $whereClause = implode(" OR ", $whereArray);
            }
            
            $listIdFilter = implode(",", $listIds);
            
            //get total counts
            
            $mainCounts = "SELECT 
                        COUNT(1) AS count, apcc.list_id
                    FROM
                        campaign_contacts apcc
                    JOIN contacts apc ON apc.id = apcc.contact_id
                    WHERE
                        apcc.list_id in ({$listIdFilter})
                            AND apc.original_owner != 'Netwise'
                             group by apcc.list_id";
            $query = $this->db->query($mainCounts);
            $resultMain = $query->result();
            
            foreach($resultMain as $mainVal){
                $newResult[$mainVal->list_id]->contact_list_count = $mainVal->count;
            }
            $listFilter = "";
            if(!empty($listIdFilter)){
                $listFilter = "AND acc.list_id in ({$listIdFilter})";
            }
            $sub_sql = " 
                SELECT 
                    acc.list_id, COUNT(1) AS workable_count_list
                FROM
                    campaign_contacts AS acc
                WHERE
                    acc.campaign_id = '{$campaign_id}' {$listFilter}
                        AND EXISTS( SELECT 
                            1
                        FROM
                            contacts AS ac
                        WHERE
                            ac.id = acc.contact_id
                                AND ac.do_not_call_ever = 0
                                AND ac.original_owner != 'Netwise'
                                AND (acc.call_disposition_id = 2
                                    OR ({$whereClause}) ))
                                
              group by acc.list_id";
            $sub_query = $this->db->query($sub_sql);    
            $sub_result = $sub_query->result();
            $this->db->trans_complete();
            foreach ($sub_result as $subCount){
                $newResult[$subCount->list_id]->workable_count_list = $subCount->workable_count_list;
            }
        }
        
        $this->db->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;');
        return $newResult;
    }
    
    function get_list_by_name($where = '', $limit = '') {
        $sql = "SELECT id,list_name FROM {$this->table}";
        if ($where != '') {
            $sql .= " WHERE " . $where;
        }
        
        if ($limit != '') {
            $sql .= " limit " . $limit;
        }
        
        $query = $this->db->query($sql);
        return $query->result();
    }

    function insert_list($array) {
        if (isset($array->list_id) && $array->list_id != '') {
             $this->db->where('id', $array->list_id);
            $list_id = $array->list_id;
            unset($array->list_id);
            unset($array->list_name);            
            $this->db->update($this->table, $array);
            return $list_id;
        } else {
            $result = $this->db->insert($this->table, $array);
            if ($result) {
                $list_id = $this->db->insert_id();
            } else {
                $list_id = 0;
            }
            return $list_id;
        }
    }
    function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    function get_one($id, $fld = "*") {
        $sql = "select " . $fld . " from {$this->table} where id IN (?) ";
        
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }
    function get_campaign_name_by_list_id($list_id){
        $sql = "SELECT l.*,c.id AS campaignID ,c.name AS CampaignName FROM ".$this->table." l  LEFT JOIN ".$this->campaign_lists_table." cl on cl.list_id=l.id LEFT JOIN ".$this->campaign_table." c ON cl.campaign_id = c.id ";
        $sql .= " WHERE l.id=".$list_id;
        $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }

    function get_list_by_campaign_id($campaignID){
        $sql = "select * from {$this->table} where campaign_id IN (?) ";
        $query = $this->db->query($sql, array($campaignID));
        $array = $query->result();

        return $array;

    }
    
    function delete_list($campaign_id,$list_id) {
        $this->db->where('id', $list_id);
        $this->db->where('campaign_id', $campaign_id);
        return $this->db->delete($this->table);
    }
    
    function update_dupes_list( $camp_list_id='', $no_of_dupes='', $no_of_uploaded='', $updated_at='', $updated_by='' ){
        $sql = "UPDATE `campaign_lists` SET `no_of_dupes` = `no_of_dupes`+ ". $no_of_dupes .", `no_of_uploaded` = `no_of_uploaded`+".$no_of_uploaded .", `updated_at` = '".$updated_at . "', `updated_by` = '".$updated_by."' ";
        $sql .= "WHERE `id` = " . $camp_list_id;
        $update_dupes_list = $this->db->query($sql);
        //$update_dupes_list = $this->db->update($this->table, array("no_of_dupes"=>"`no_of_dupes`+ $no_of_dupes", "no_of_uploaded"=>"`no_of_uploaded`+$no_of_uploaded", 'updated_at'=>$updated_at, 'updated_by'=>$updated_by ), array('campaign_id' => $campaign_id, 'list_name'=>$list_name, 'updated_at'=>$updated_at));
        //echo $this->db->last_query();
        return $update_dupes_list;
    }  
    
    function insert_campaign_list_dupes_history( $campaign_id = '', $name='', $no_of_dupes='', $no_of_uploaded='', $created_at='', $created_by='' ){
        $campaign_list = array("campaign_id" => $campaign_id,
                       "list_name" => $name,
                       "ct_uploaded" => $no_of_uploaded,
                       "ct_dupes" => $no_of_dupes,
                       "created_at" => $created_at,
                       "created_by" => $created_by);
        
        $result = $this->db->insert($this->campaign_list_dupes_history_table, $campaign_list);
        if ($result) {
            $list_id = $this->db->insert_id();
        } else {
            $list_id = 0;
        }
        return $list_id;
    }  
    
    function delete_campaign_contact_list($campaign_id,$list_id) {
        $this->db->where('list_id', $list_id);
        $this->db->where('campaign_id', $campaign_id);
        $this->db->where('workable_status', 'W');
        return $this->db->delete($this->campaign_contacts);
    }
    
    function is_exits_campaign_contact_list($campaign_id,$list_id){
        $sql = "SELECT count(*) as CNT FROM {$this->campaign_contacts} where campaign_id='$campaign_id' AND list_id='$list_id' AND workable_status='NW'";
        $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            return $array[0]->CNT;
}
    }
	 function updateCampaignList_filterByID($campaign_id, $list_id,$data)
    {
        $this->db->where('campaign_id', $campaign_id);
		$this->db->where('id', $list_id);
        return $this->db->update($this->table, $data);
}
	function get_campaign_listdata($campaign_id, $list_id)
	{
		$sql = "SELECT c.id,c.eg_campaign_id,c.name,GROUP_CONCAT(DISTINCT cto.tm_office) AS telemarketing_offices,l.contact_filter FROM {$this->campaign_table} c LEFT JOIN `campaign_tm_offices` cto ON c.id = cto.campaign_id LEFT JOIN {$this->table} l on l.campaign_id=c.id where c.id='$campaign_id' AND l.id='$list_id'";
		 $query = $this->db->query($sql);
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
	}
        
        function getTotalListCount($campaignId, $active='all'){
            $activeFilter = "";
            if($active=="yes"){
                $activeFilter = " AND status = 'Active'";
            }else if($active=="no"){
                $activeFilter = " AND status = 'InActive'";
            }
            $sql = "SELECT count(1) as CNT FROM {$this->campaign_lists_table} where campaign_id='{$campaignId}'";
            $query = $this->db->query($sql);
            $array = $query->result();
            if (!empty($array)) {
                return $array[0]->CNT;
            }
        }

}

class ListsTable {

    public $campaign_id;
    public $list_name;
    public $agent_id;
    public $file_name;
    public $status;
    public $updated_at;
    public $updated_by;

}
