<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users_model extends CI_Model {

    public $table = 'users';
    public $login_history = 'login_history';
    public $admin_users = 'admin_users';
    public $userSessionTable = 'user_sessions';
    public $voip_communications = 'voip_communications';
    
    function __construct() {
        parent::__construct();
        $this->load->database();
       // $this->db2 = $this->load->database('db2', TRUE);
    }

    function authenticate_user($email, $pwd) {
        $sql = 'SELECT us.id,us.first_name,us.last_name,us.is_readonly,us.email,us.last_reset,us.user_type,us.schedule,us.status,us.telemarketing_offices,us.module,us.project,us.password,CONCAT(u.first_name," ",u.last_name) AS "User_Group_Name"
                FROM ' . $this->table . ' us LEFT JOIN users u ON us.parent_id = u.id WHERE  us.email = ?';
        $sql .=" GROUP BY us.id ";
        $query = $this->db->query($sql, array($email));
		
        $array = $query->result();
        if (!empty($array)) {
            //if (password_verify($pwd, $array[0]->password) =='1') {
                $update_last_login = $this->update_last_login($array[0]->id);
                return $array;
            //} else {
            //    return null;
            //}
        } else {
            return null;
        }
    }

    function get_by_id($id) {
        $sql = 'SELECT email,first_name,last_name FROM ' . $this->table . ' WHERE id=?';
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }

    function get_by_email($email) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE email=?';
        $query = $this->db->query($sql, array($email));
        $array = $query->result();

        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }
    
    function get_details_by_email($email) {
        $sql = 'SELECT us.*,CONCAT(u.first_name," ",u.last_name) AS "User_Group_Name" 
                FROM ' . $this->table . ' us LEFT JOIN users u ON us.parent_id = u.id WHERE  us.email = ?';
        $sql .=" GROUP BY us.id ";
        $query = $this->db->query($sql, array($email));
        $array = $query->result();

        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }
    
    function update_last_login($id) {
        $sql = "UPDATE {$this->table} SET last_login = ? WHERE id = ?";
        return $this->db->query($sql, (array(date('Y-m-d H:i:s', time()), $id)));
    }

    function password_check($user_id, $pass) {
        $sql = 'SELECT id FROM ' . $this->table . ' WHERE id = ? and password = ?';
        
        $query = $this->db->query($sql, array($user_id, $pass));

        if ($query->num_rows() == 0) {
            return false;
        } else {
            return true;
        }
    }

    function unset_nulls($obj) {

        foreach ($obj as $key => $value) {
            if ($value == NULL) {
                unset($obj->$key);
            }
        }
        return $obj;
    }

    function get_user_filtered_list($limit = "", $offset = "") {
        $loggedInUserType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        
        $csv_module_value = getCSVFromArrayElement($this->session->userdata('module'));

        $sql = 'Select id,telemarketing_offices,group_concat(DISTINCT module) as module ,email,user_type,`status`,created_at,concat(first_name," ",last_name) as full_name FROM ' . $this->table . ' ';
        $sql .= ' WHERE 1=1' ;
        // if LoggedIn user is TL then, he/she can access/see only their agent(s)
        if($loggedInUserType =='team_leader'){
            $sql .= ' AND user_type="agent" AND parent_id = '.$this->session->userdata['uid'];
        }
        if ($loggedInUserType != 'admin') {
            $sql .= " AND telemarketing_offices = '" . $logged_tm_office . "' ";
            if(sizeof($this->session->userdata('module')) < 2)
                $sql .= " AND FIND_IN_SET($csv_module_value,module) "; // IN ($csv_module_value)
        }
        $sql .= " group by id ORDER BY created_at desc";

        if ($limit != "" && $offset != "")
            $sql .= " LIMIT ? OFFSET ?";

        $query = $this->db->query($sql, array($limit, $offset));
        return $query->result();
    }

    function get_one($id) {        
        $sql = "select * from {$this->table} where id IN (?) ";
        $query = $this->db->query($sql, array($id));              
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }
        return $array;
    }

    function insert_user($array) {
        $result = $this->db->insert($this->table, $array);
        if ($result) {
            $user_id = $this->db->insert_id();
        } else {
            $user_id = 0;
        }
        return $user_id;
    }

    function delete($id) { 
        
        $user_ids = str_getcsv($id);
        $this->db->set('parent_id','0');
        $this->db->set('updated_at', date('Y-m-d H:i:s', time()));       
        $this->db->where_in('parent_id', $user_ids);    
        $update = $this->db->update('users');     
        return  $update;
    }
    
    function get_team_leads($logged_tm_office="",$csv_module_value="",$count_array_module_value=""){
        
        $sql = "SELECT id,CONCAT(first_name,' ',last_name) AS member_name from {$this->table} WHERE user_type='team_leader' and status = 'Active'";

        if($this->session->userdata['user_type'] =='team_leader'){
            $sql .= " AND id = ".$this->session->userdata['uid'];
        }
        if(!empty($logged_tm_office)){
            $sql .= " AND telemarketing_offices = '".$logged_tm_office."'";
        }

        if(!empty($csv_module_value)){
            if($count_array_module_value < 2)
                $sql .= " AND FIND_IN_SET($csv_module_value,module)"; // module IN ($module_type)
        }

        $query = $this->db->query($sql);                
        $array = $query->result();         
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }

    function update_user($userId, $data)
    {
        $this->db->where('id', $userId);
        return $this->db->update($this->table, $data);
    }

    function authenticate_id_password($id, $pwd) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';
        $query = $this->db->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            if (password_verify($pwd, $array[0]->password) == '1') {
                $update_last_login = $this->update_last_login($array[0]->id);
                return $array;
            } else {
                return null;
            }

        } else {
            return null;
        }
    }
    function email_exists($email) {
        $sql = 'SELECT id FROM ' . $this->table . ' WHERE email = ?';
        $query = $this->db->query($sql, array($email));

        if ($query->num_rows() == 0) {
            return false;
        } else {
            return true;
        }
    }
    public function get_agent_by_id($id,$module_value='',$count_array_module_value=""){
        $sql = 'SELECT *  FROM ' . $this->table . ' WHERE parent_id = ? and user_type = "agent"';
         if(!empty($module_value)){
             if($count_array_module_value < 2)
                 $sql .= " AND FIND_IN_SET($module_value,module) "; // IN ($csv_module_value)
             // $sql .= " AND module IN ($module_value) ";
         }

        $query = $this->db->query($sql,array($id));
        //echo $this->db->last_query();exit;
        $array = $query->result();

        return $array;
    }
    
    public function get_agent_by_tl_id($id,$module_value){
        if($module_value == "tm"){
            $module = "appt";
        }elseif($module_value == "appt"){
            $module = "tm";
        }
        
        $sql = "SELECT DISTINCT(u.id) AS cnt  FROM {$this->table} AS u ";
        $sql .= "LEFT JOIN campaign_assign AS ca ON ca.agent_id = u.id ";
        $sql .= "LEFT JOIN campaigns AS c ON c.id = ca.campaign_id ";
        $sql .= "WHERE parent_id = ? and user_type = 'agent'";
        $sql .= " AND FIND_IN_SET('$module_value',module) and u.status='Active' AND c.module_type ='$module'"; 
        $query = $this->db->query($sql,array($id));
        //echo $this->db->last_query();
        $array = $query->result();
        return $array;
    }
    
    public function isAssignCampaignToAgent($teamId){
        /*$sql =  "SELECT CONCAT(c.eg_campaign_id,' ',c.name) AS campaign_name
                FROM `campaign_assign` ca
                LEFT JOIN campaigns c ON c.id = ca.campaign_id  AND c.assign_team_id = ? 
                WHERE teamleader_id = ? GROUP BY ca.campaign_id"; */
        $sql = "SELECT CONCAT(c.eg_campaign_id,' ',c.name) AS campaign_name
                FROM `campaign_assign` ca
                LEFT JOIN `campaign_assign_tl` cl ON cl.user_id = ca.teamleader_id
                LEFT JOIN `campaigns` c ON c.id = cl.campaign_id 
                WHERE cl.user_id = ? and c.business = '{$this->app}'
                GROUP BY c.id";
        $query = $this->db->query($sql,array($teamId));
        //echo $this->db->last_query();exit;
        return  $query->result_array();
    }
    
    public function  getusers(){
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        $sql = 'SELECT id,CONCAT(first_name," ",last_name) AS member_name  FROM ' . $this->table . ' WHERE status = "active"';
        
        if($logged_user_type =='team_leader'){
            $sql .= " AND (parent_id = ".$this->session->userdata['uid']." || id= ".$this->session->userdata['uid'].")";
        }
       
        if ($logged_user_type =='manager'){
            $sql .= " AND (user_type IN ('team_leader','agent') || id= ".$this->session->userdata['uid'].")";
        }
        if($logged_user_type != 'admin'){
            $sql .= " AND telemarketing_offices = '" . $logged_tm_office . "' ";
        }
        $query = $this->db->query($sql);
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }

    public function get_login_failure_count_by_email($email,$ip){
        $sql = "SELECT * FROM {$this->login_history} WHERE email = ? AND ip = ? AND result = '0' AND DATE(created_at) = CURDATE()";
        $query = $this->db->query($sql, array($email, $ip));
		$count=$query->num_rows();
        //$array = $query->result();
		return $count;
        }
    // insert login history
    public function log_failed_login($logged_failed_data){
        $result = $this->db->insert($this->login_history, $logged_failed_data);
        if ($result) {
            $login_history_id = $this->db->insert_id();
        } else {
            $login_history_id = 0;
        }
        return $login_history_id;
    }

    // Function to Delete failed login attempts record from login history table.
    public function clearing_failed_login($ip,$email,$result){
        $this->db->where('ip', $ip);
        $this->db->where('email', $email);
        $this->db->where('result', $result);
        $this->db->delete('login_history');
        //echo $this->db->last_query();exit;
    }

    // get data from eg by passed user id
    function get_data_from_eg_by_email($email) {
        $sql = 'SELECT * FROM ' . $this->admin_users . ' WHERE email=?';
        $query = $this->db2->query($sql, array($email));
        $array = $query->result();

        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
}
    }

    // Insert user from Uber-dialler application to eg application
    function insert_eg_user($array) {
        $result = $this->db2->insert($this->admin_users, $array);

        if ($result) {
            $eg_user_id = $this->db2->insert_id();
        } else {
            $eg_user_id = 0;
        }
        return $eg_user_id;
    }

    function update_user_session_by_email($user_session_id,$data){
        $this->db->limit(1);
        $this->db->order_by('id','DESC');
        $this->db->where('user_id', $user_session_id);
        $this->db->where('is_session_active !=' , 1);
        return $this->db->update($this->userSessionTable, $data);
    }

    function insert_user_session($data){

        $result = $this->db->insert($this->userSessionTable, $data);
        return ($result) ? $this->db->insert_id() : 0;
    }

    function check_user_session($userId)
    {
        $sql = 'SELECT * FROM ' . $this->userSessionTable . '  WHERE is_session_active = 1 AND user_id = ? ORDER BY id desc limit 1';
        $queryuser = $this->db->query($sql, array($userId));

        $array = $queryuser->result();
        return ($array) ? $array[0] : 0;
    }
        
    function check_user_signout($userId, $campaignId)
    {
        $sql = 'SELECT id,campaign_id FROM ' . $this->userSessionTable . '  WHERE  is_session_deactive = 0 AND user_id = ? and campaign_id = ? ';
        $queryuser = $this->db->query($sql, array($userId, $campaignId));
        $array = $queryuser->result();
        return ($array) ? $array[0] : 0;
    }

    function update_user_session($user_id, $data)
    {
         $this->db->where('user_id', $user_id);
         $this->db->where('is_session_active', 1);
        return $this->db->update($this->userSessionTable, $data);
    }

    function get_online_users($filter="")
    {
        $sql = 'SELECT u.* FROM ' . $this->userSessionTable . ' us JOIN users u ON u.id = user_id WHERE us.is_session_active = 1 AND us.last_activity > NOW() - INTERVAL 1 HOUR ' . $filter;
        $queryuser = $this->db->query($sql, array($userId));

        $array = $queryuser->result();
        return ($array) ? $array[0] : 0;
    }

    //Added script for Agent status report negative values update based on plivo duration time
    function update_call_start_time(){
        if($this->plivo_switch){
            $sql = " SELECT ch.id,
                  (DATE_SUB(ch.call_end_datetime, INTERVAL plc.duration SECOND) )AS updated_call_starttime,
                  plc.duration,ch.call_start_datetime,ch.call_end_datetime,
                  SEC_TO_TIME(TIMESTAMPDIFF(SECOND,ch.call_start_datetime, ch.call_end_datetime)) diff,
                  (DATE_SUB(ch.call_end_datetime, INTERVAL plc.duration SECOND) )AS updated_call_starttime,
                  TIMESTAMP(ch.call_end_datetime - duration + duration) AS actuallendtime
                  FROM calldispositions cd
                  LEFT JOIN `call_disposition_history` cdh ON cd.id = cdh.call_disposition_id
                  LEFT JOIN `call_history` ch ON ch.id = cdh.call_history_id
                  JOIN {$this->voip_communications} plc ON plc.call_history_id = ch.id
                  WHERE ch.call_start_datetime > ch.call_end_datetime
                   ";
        }else{
            $sql = " SELECT ch.id,
                    (DATE_SUB(ch.call_end_datetime, INTERVAL ch.duration SECOND) )AS updated_call_starttime,
                    ch.duration,ch.call_start_datetime,ch.call_end_datetime,
                    SEC_TO_TIME(TIMESTAMPDIFF(SECOND,ch.call_start_datetime, ch.call_end_datetime)) diff,
                    (DATE_SUB(ch.call_end_datetime, INTERVAL ch.duration SECOND) )AS updated_call_starttime,
                    TIMESTAMP(ch.call_end_datetime - duration + duration) AS actuallendtime
                    FROM calldispositions cd
                    LEFT JOIN `call_disposition_history` cdh ON cd.id = cdh.call_disposition_id
                    LEFT JOIN `call_history` ch ON ch.id = cdh.call_history_id
                    WHERE ch.call_start_datetime > ch.call_end_datetime
                     ";
        }
        $query = $this->db->query($sql);
        $array = $query->result();
        return $array;
    }

    function update_final_plivo_time($id,$updated_call_starttime){
        $sql =  "UPDATE call_history set call_start_datetime = '".$updated_call_starttime."' where id = '".$id."'";
        $query = $this->db->query($sql);
    }
    
    function get_tm_offices(){
        $sql = "select distinct telemarketing_offices as office from users where telemarketing_offices is not null";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function get_agent_list_assignin_campaign($TL_id,$campaign_id){
        $sql = "SELECT CONCAT(u.first_name,' ',u.last_name) AS agent_name,ca.campaign_id,ca.teamleader_id,ca.agent_id
                FROM `campaign_assign` ca
                LEFT JOIN `users` u ON u.id = ca.agent_id 
                WHERE ca.teamleader_id = '$TL_id' AND ca.campaign_id = '$campaign_id'";
        $query = $this->db->query($sql);
        $array = $query->result();
        return $array;
    }

    function get_by_site($tm_site,$user_type = '') {
        $sql = "SELECT id,CONCAT(first_name,' ',last_name) AS `name`,user_type FROM users WHERE telemarketing_offices LIKE '%".$tm_site."%'";
        if($user_type == '') {
            $sql .= " AND user_type IN ('manager','team_leader')";
        } elseif($user_type == 'manager') {
            $sql .= " AND user_type IN ('team_leader')";
        }
        $query = $this->db->query($sql);
        return $query->result();
    }

}

class User {
    public $email;
    public $first_name;
    public $last_name;
    public $password;
    public $user_type;    
    public $user_type_value;    
    public $parent_id;
    public $status;
    public $created_at;
    public $updated_at;
    public $is_readonly;
    public $tier;
    public $project;
    public $hired_date;
    public $schedule;
    public $released_date;
    public $resigned_date;
    public $inactive_date;
    public $module;

}

class eg_user {
    public $email;
    public $first_name;
    public $last_name;
    public $password;
    public $user_type;
    public $user_type_value;
    public $group; // $parent_id
    public $created_at;
    public $updated_at;
    public $is_readonly;
    public $is_active;
    public $created_by;
    public $updated_by;
}