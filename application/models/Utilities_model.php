<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Utilities_model extends CI_Model
{
    public $contacts = 'contacts';
    public $campaignContactsTable = 'campaign_contacts';
    public $campaignTable = 'campaigns';
    public $callHistoryTable = 'call_history';

    /* Dev_NV Region Start */

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //setting the second parameter to TRUE (Boolean) the function will return the database object.
        //$this->db2 = $this->load->database('db2', TRUE);
    }

    public function get_call_history_data($filter,$by='campaign_contact_id')
    {
        $fields = 'ch.id,ch.contact_id,(select eg_campaign_id from campaigns where id = ch.campaign_id) as `eg_campaign_id`,';
        $fields .= 'ch.number_dialed,ch.user_id,ch.call_start_datetime,ch.call_end_datetime,ch.created_at,ch.count_flag,ch.module_type,';
        $fields .= 'ch.channel,ch.agent_id,ch.sid,ch.conf_sid,ch.hangup_cause,ch.recording_url,ch.updated_at,ch.campaign_contact_id';    
        
        $this->db->select($fields);
        $this->db->from($this->callHistoryTable.' AS ch');
        $this->db->join('contacts as c', 'c.id = ch.contact_id');
        $this->db->where($by, $filter);
        $this->db->order_by("ch.id", "desc");
        $this->db->limit(10);
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;

    }

    public function get_call_history_data_agent_name($filter,$by='campaign_contact_id')
    {
        $fields = "ch.id,(select eg_campaign_id from campaigns where id = ch.campaign_id) as `eg_campaign_id`,";
        $fields .= "(select name from campaigns where id = ch.campaign_id) as `name`,";
        $fields .= "(select CONCAT(u.first_name, '', u.last_name) from users u where u.id = ch.user_id) AS Agent,";
        $fields .= "ch.number_dialed,ch.created_at,ch.sid,ch.conf_sid,ch.recording_url,ch.campaign_contact_id";
        $this->db->select($fields);
        $this->db->from($this->callHistoryTable.' AS ch');
        $this->db->join('contacts as c', 'c.id = ch.contact_id');
        $this->db->where($by, $filter);
        //$this->db->where("u.telemarketing_offices", $telemarketing_office);
        $this->db->order_by("ch.id", "desc");
        $this->db->limit(10);
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;

    }
    
    public function update_plivo_recording_url_by_id($id, $recording,$api='plivo')
    {
        $this->db->where('id', $id);
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
    
    public function searchCampaignContact($filters, $fields = 'id',$orderBy="c.id",$order='desc',$limit=''){
        $this->db->select($fields);
        $this->db->from('contacts AS c');
        $this->db->join('campaign_contacts AS cc', 'c.id = cc.contact_id');
        
        $this->db->where($filters);
        if(!empty($orderBy)){
            $this->db->order_by($orderBy, $order);
        }
        if(!empty($limit)){
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }
    
    public function searchMembersQa($filters, $fields , $limit = ""){
        $this->db->select($fields);
        $this->db->from('members_qa');
        $this->db->where($filters);
        if(!empty($limit)){
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }
    
    public function getEmailHistory($filters,$selectedFields='c.*',$from = true, $notInContacts = false){
        if(!$from){
            $joinOn = 'c.email = meh.email_from';
        }else{
            $joinOn = 'c.email = meh.email_to';
        }
        $this->db->select($selectedFields);
        $this->db->from('members_email_history meh');
        if($notInContacts){
            $this->db->join('members_qa AS c', $joinOn);
        }else{
            $this->db->join('contacts AS c', $joinOn);
        }
        $this->db->join('campaign_contacts AS cc', 'c.id = cc.contact_id');
        foreach($filters as $idx => $filter){
            $this->db->where($idx, $filter);
        }
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }
    
    public function checkEmailChangeHistory($email){
        $selectedFields = "*, if('{$email}' = email_from, 'from','to') as email_change,if('{$email}' = email_from, 1,0) as in_contact ";
        $this->db->select($selectedFields);
        $this->db->from('members_email_history meh');
        $this->db->where("email_from = '{$email}' OR email_to = '{$email}'");
        $query = $this->db->get();
        $array = $query->result_array();
        if (!empty($array)) {
            $array = $array;
        }
        return $array;
    }
    
    public function getCampaignId($egCampaign_id){
        $this->db->select('id');
        $this->db->from('campaigns');
        $this->db->where('eg_campaign_id', $egCampaign_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $array = $query->result_array();
        if(!empty($array[0])){
            return $array[0];
        }
        return 0;
    }
        
    public function searchOriginalEmail($email, $campaign_id){
        $sql = "select CONCAT(cnt.first_name,' ', cnt.last_name) as full_name,cnt.email,cc.id as campaignContactId,cc.list_id from contacts cnt ";
        $sql .= "JOIN campaign_contacts cc ON cc.contact_id = cnt.id " ;  
        $sql .= "JOIN campaigns c ON cc.campaign_id = c.id AND c.eg_campaign_id = " . $campaign_id . " ";
        $sql .= "WHERE (member_id IN(select id from members_qa where email = '" . $email ."') "; 
        $sql .= "OR member_id IN (select id from members_qa where email IN (SELECT email_to from members_email_history eh WHERE eh.email_to =  '" . $email ."')) ) ";
        $sql .= "AND c.eg_campaign_id = " . $campaign_id . " and email != '{$email}'";
        
        $query = $this->db->query($sql);

        $array = $query->result_array();
        return $array; 
    }
    
    public function getAssignedCampaigns(){
        $loggedInUserID = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $sql = "SELECT 
            group_concat(distinct c.eg_campaign_id) as eg_campaign_id
        FROM
            campaigns c
                LEFT JOIN
            agent_sessions ag ON ag.campaign_id = c.id
                LEFT JOIN
            `campaign_tm_offices` cto ON c.id = cto.campaign_id
                LEFT JOIN
            `campaign_assign_tl` cl ON cl.campaign_id = c.id
                LEFT JOIN
            `campaign_assign` ca ON ca.teamleader_id = cl.user_id
                AND ca.campaign_id = cl.campaign_id
        WHERE
            ca.agent_id = '{$loggedInUserID}'
                AND (c.status = 'active'
                OR c.status = 'pending')
                AND cto.tm_office = '{$logged_tm_office}' ";
        $sql .= " AND cto.tm_office = '" . $logged_tm_office . "' ";
        $sql .= " AND c.module_type = '".$this->app_module_type."' AND c.business = '{$this->app}' ";
        $query = $this->db->query($sql);

        $array = $query->result_array();
        if(!empty($array)){
            $array = explode(",", $array[0]['eg_campaign_id']);
        }
        return $array;
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

?>
