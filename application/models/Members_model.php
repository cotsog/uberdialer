<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Members_model extends CI_Model
{

    public $egMemberTable = 'members';
    public $members_qa = 'members_qa';
    public $members_qa_history = 'members_qa_history';

    /* Dev_NV Region Start */

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //setting the second parameter to TRUE (Boolean) the function will return the database object.
//        $this->db2 = $this->load->database('db2', TRUE);
    }

    function get_question_responses_from_tm_qa($member_id,$question_ids) {

        $sql = "SELECT qr.question_id,qr.response
                FROM question_responses_qa qr ";
        $sql .= "where qr.member_id = ? AND qr.question_id IN ? order by qr.question_id";

        $query = $this->db->query($sql,array($member_id,explode(',',$question_ids)));
        return $query->result();
    }

    function get_survey_responses_from_tm_qa($member_id, $question_ids)
    {
        $sql = "SELECT sr.question_id,sr.response,sr.incentive_offered 
            FROM survey_responses_qa sr 
            WHERE sr.member_id = ? AND sr.question_id IN ({$question_ids}) 
            ORDER BY sr.question_id";
        $query = $this->db->query($sql, array($member_id)
        );
        return $query->result();
    }

    function get_question_responses($member_id,$question_ids){
        $sql = "SELECT * from question_responses ";
        $sql .= "where member_id = ? AND question_id IN ? order by question_id";

        $query = $this->db2->query($sql,array($member_id,explode(',',$question_ids)));
        return $query->result();
    }

    function get_question_responses_from_tm_qa_by_member($member_id) {

        $sql = "SELECT qr.question_id,qr.response
                FROM question_responses_qa qr ";
        $sql .= "where qr.member_id = ? order by qr.question_id";

        $query = $this->db->query($sql,array($member_id));
        return $query->result();
    }
    
    function get_question_responses_by_member($member_id){
        $sql = "SELECT * from question_responses ";
        $sql .= "where member_id = ? order by question_id";

        $query = $this->db2->query($sql,array($member_id));
        return $query->result();
    }
    
    function unset_nulls($obj)
    {
        foreach ($obj as $key => $value) {
            if ($value == NULL) {
                unset($obj->$key);
            }
        }
        return $obj;
    }

    function get_member_normalization_rules()
    {
        $query = $this->db2->query("SELECT * FROM member_normalizing_rules ORDER BY question_id,processing_step,id");
        return $query->result();
    }

    function check_email_domain($domain)
    {
        $sql = "SELECT * FROM personal_email_domains WHERE domain = ? ";
        $query = $this->db2->query($sql,array($domain));
        $result = $query->result_array();
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    function get_one($id)
    {
        $sql = "select * from members where id=? ";
        $query = $this->db2->query($sql, array($id));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }

        return $array;
    }

    public function create_history($member_id)
    {
        $sql = 'insert into members_history(member_id,first_name,last_name,address1,address2,city,state,zip,country,email,password,phone,phone_verified,phone_type,company_name,source,site_id,type,';
        $sql .= 'is_valid,can_email,ip,ml_title,dex_title,job_title,job_level,industry,company_size,silo,hq_phone,hq_address,hq_city,hq_state,hq_zip,hq_country,last_login,created_at,updated_at,updated_by,company_url)';
        $sql .= 'select id,first_name,last_name,address1,address2,city,state,zip,country,email,password,phone,phone_verified,phone_type,company_name,source,site_id,type,';
        $sql .= 'is_valid,can_email,ip,ml_title,dex_title,job_title,job_level,industry,company_size,silo,hq_phone,hq_address,hq_city,hq_state,hq_zip,hq_country,last_login,created_at,"' . date('Y-m-d H:i:s', time()) . '","'.$this->session->userdata('uid').'",company_url from members where id= ?';
        $query = $this->db2->query($sql,array($member_id));
    }

    function unset_questions($obj)
    {
        foreach ($obj as $key => $value) {
            if (strstr($key, 'qid')) {
                unset($obj->$key);
            }
        }
        return $obj;
    }

    /**
     *  Dynamic Function is coded for set null field while fields want to store with empty value by Ravindra
     *  Make sure to pass an array of set specific values even null [29-08-2013]
     */
    function set_member_empty_values($array)
    {
        if (!empty($array)) {
            foreach ($array as $arr => $val) {
                if ($val == 'NULL') {
                    $array->$arr = '';
                }
            }
        }
        return $array;
    }

    /*
     *  get_selected_columns_by_id method
     *  ex: $columns = 'id, first_name, last_name'
     *
     */
    function get_selected_columns_by_id($columns, $id)
    {
        $sql = 'SELECT ' . $columns;
        $sql .= ' FROM members WHERE id=' . $id;
        $query = $this->db2->query($sql);

        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }

    function clear_sic_or_company_size_integer($action, $member_id)
    {
        if ($action == "industry") {
            $data = array('sic_primary' => '', 'sic_secondary' => '');
        }

        if ($action == "company_size") {
            $data = array('company_size_integer' => NULL);
        }

        // run update query // contact id instead of member id
        $this->db2->update("contacts", $data, array("member_id" => $member_id));
    }

    function check_emails_exist($email)
    {
        $sql = "SELECT id, email, do_not_call FROM members WHERE email = ?  ORDER BY id DESC ";
        $query = $this->db2->query($sql, array($email));

        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }

    function get_one_from_tm_qa($tempMemberID)
    {
        $sql = "select * from {$this->members_qa} where id=? ";
        $query = $this->db->query($sql, array($tempMemberID));
        $array = $query->result();
        if (!empty($array)) {
            $array = $array[0];
        }

        return $array;
    }

    function member_exist_check($email, $bulk = 0)
    {
        if (!$bulk) {
            $sql = "select id,email from {$this->members_qa} where email=? ";
            $query = $this->db->query($sql, array($email));
            $array = $query->result();
            if (!empty($array)) {
                $array = $array[0];
            }
        } else {
            $contact_emails = array();
            
            foreach($email as $email_dtl){ 
                $contact_emails[] = "'" . $email_dtl ."'";
            }
            
            $email_str = implode(",", $contact_emails);
            $sql = "select email from {$this->members_qa} where email IN (" . $email_str . ") ";
            
            $query = $this->db->query($sql);
            $array = $query->result_array();
        }

        return $array;
    }
	function member_exist_check_using_id($id)
    {
        $sql = "select count(*) as total from {$this->members_qa} where id=? ";
        $query = $this->db->query($sql, array($id));
        $result = $query->result();
        return $result[0]->total;
    }
 	function eg_member_exist_check_using_id($id)
    {
        $sql = "select count(*) as total from members where id=? ";
        $query = $this->db2->query($sql, array($id));
        $result = $query->result();
        return $result[0]->total;
    }
    function insert_members_qa_history($member_id){

        $sql = 'insert into members_qa_history(member_id,first_name,last_name,address1,address2,city,state,zip,country,email,password,phone,ext,phone_verified,phone_type,company_name,source,site_id,type,';
        $sql .= 'is_valid,can_email,ip,ml_title,dex_title,job_title,job_level,industry,company_size,silo,hq_phone,hq_address,hq_city,hq_state,hq_zip,hq_country,last_login,created_at,updated_at,updated_by,company_url,company_revenue)';
        $sql .= 'select id,first_name,last_name,address1,address2,city,state,zip,country,email,password,phone,ext,phone_verified,phone_type,company_name,source,site_id,type,';
        $sql .= 'is_valid,can_email,"' .$_SERVER['REMOTE_ADDR']. '",ml_title,dex_title,job_title,job_level,industry,company_size,silo,hq_phone,hq_address,hq_city,hq_state,hq_zip,hq_country,last_login,created_at,"' . date('Y-m-d H:i:s', time()) . '","'.$this->session->userdata('uid').'",company_url,company_revenue from members_qa where id= ?';
        $this->db->query($sql,array($member_id));
     }

    function insert_member($member)
    {
        //check if member already exist on members_qa, if yes then we dont need to insert it on members table and return the id
        //if no then we'll have to re-insert to eg.members table
        $members_qa_exist = $this->member_exist_check(trim($member->email));
        if(empty($members_qa_exist)){
            if (is_array($member)) {
                $email_exp = explode('@', $member['email']);
                if ($this->check_email_domain($email_exp[1])) {
                    $member['email_type'] = 'personal';
                }
            } else {
                $email_exp = explode('@', $member->email);
                if ($this->check_email_domain($email_exp[1])) {
                    $member->email_type = 'personal';
                }
            }
            $member = $this->unset_nulls($member);
            $insert_member = $this->db2->insert($this->egMemberTable, $member);
            if ($insert_member) {
                //$this->db2->insert($this->egMemberTable, $member);

                $id = $this->db2->insert_id();

                return $id;
            } else {
                return 0;
               /* $data['message'] = "Sorry, Oops! Something went wrong while inserting new member";
                $data['status'] = false;
                echo json_encode($data);
                exit();*/
            }
        }else{
            return $members_qa_exist->id;
        }
    }

    function update($member, $history = "YES")
    {
        if ($history == "YES") {
            $this->create_history($member->id);
        }
        $member = $this->unset_nulls($member);
        $member = $this->unset_questions($member);
        if (!empty($member->country) && ($member->country == 'US' || $member->country == 'CA')) {
            $member->province = NULL;
        }
        $member_current_data = null;
        //Value should be assigned with "Null" string to replace with empty value By Ravindra[29-08-2013]
        $member = $this->set_member_empty_values($member);
        if($this->app == 'eg'){
            $member_current_data = $this->get_selected_columns_by_id('`company_size`, `industry`', $member->id);

            if (isset($member->industry) && !empty($member_current_data->industry) && $member_current_data->industry != $member->industry) {
                // clear sic_primary and sic_secondary values from contacts table
                $this->clear_sic_or_company_size_integer('industry', $member->id);
            }
        }
        if($this->app == 'mpg'){
            $question_responses = array();

            //save job_level (question_id 16), company_size (question_id 5) 
            if(!empty($member->job_title)){
                $question_responses[] = array('question_id' => 16, 'member_id' => $member->id, 'response' => $member->job_title);
            }
            if(!empty($member->company_size)){
                $question_responses[] = array('question_id' => 5, 'member_id' => $member->id, 'response' => $member->company_size);
            }
            $this->upsert_question_responses($question_responses);
            //UNSET FIELDS: job_title, job_level, industry, silo, company_size, province
            unset($member->job_title);
            unset($member->job_level);
            unset($member->industry);
            unset($member->silo);
            unset($member->company_size);
            unset($member->province);
        }
        

        if (isset($member->company_size) && !empty($member_current_data->company_size) && $member_current_data->company_size != $member->company_size) {
            $member->company_size_integer = NULL;

            // clear company_size_integer value from contacts table
            $this->clear_sic_or_company_size_integer('company_size', $member->id);
        }
        $status = $this->db2->update('members', $member, array('id' => $member->id));
        // Add a record for this member_id to the members_update_history table; this table is used in the member_changes cron to determine which member records were changed
        // during the selected time period
        $updated_at = date('Y-m-d H:i:s', time());
        $this->db2->query("INSERT INTO members_update_history (member_id,updated_at) VALUES (".$member->id.",'".$updated_at."') ON DUPLICATE KEY UPDATE updated_at='".$updated_at."'");
        if ($status) {
            return $status;
        } else {
            $data['message'] = "Sorry, Oops! Something went wrong.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }

    }

    function upsert_question_responses($array, $hql = false, $survey = false, $survey_response_constants = array())
    {
        if($survey == true){
            // Get lead history's data first
            $sql = "SELECT * FROM lead_history WHERE id = " . $survey_response_constants['lead_id'];
            $query = $this->db->query($sql);

            $lead_history = $query->result();
        }
        /**
         * TODO: survey_responses has a lot more fields required than question_responses
         * Got to update this whole function to support inserting to that table
         *
         */
        $sql = '';
        foreach ($array as $response) {
            if (!empty($response['response'])) {
                if ($survey == false) {
                    $sql = 'INSERT INTO question_responses (
                            question_id, member_id, response, created_at) 
                        VALUES (
                            ' . $response['question_id'] . ',
                            ' . $response['member_id'] . ',
                            "' . addslashes($response['response']) . '",NOW()
                        ) ON DUPLICATE KEY UPDATE response = 
                            "' . addslashes($response['response']) . '"; ';
                } else {
                    $sql = "INSERT INTO survey_responses (
                        `survey_id`, `question_id`, `member_id`, `resource_id`, `email`, 
                        `ip`, `site_id`, `response`, `other_info`, `source`, 
                        `campaign_id`, `created_at`, `updated_at`, 
                        `released_from_qa`,`incentive_offered`) 
                        VALUES (
                            (SELECT survey FROM campaigns 
                            WHERE id = " .
                            $survey_response_constants['campaign_id'] . "), 
                            " . $response['question_id'] . ", 
                            " . $response['member_id'] . ", 
                            " . $survey_response_constants['resource_id'] . ", 
                            '" . $survey_response_constants['email'] . "', 
                            '', " . $survey_response_constants['site_id'] . ", 
                            '" . addslashes($response['response']) . "', '', 
                            'telemarketing', 
                            " . $survey_response_constants['campaign_id'] . ", 
                            '".$lead_history[0]->created_at."', NOW(), 1,
                            (if(".$response['incentive_offered'].",(SELECT incentives_available FROM campaigns 
                                WHERE id = " .
                                $survey_response_constants['campaign_id'] . "),0))
                        )";
                }
                $query = $this->db2->query($sql);
            } elseif ($hql) {
                $sql = 'DELETE FROM {$qr_table} WHERE 
                    question_id = ' . $response['question_id'] . ' AND 
                    member_id=' . $response['member_id'];
                $query = $this->db2->query($sql);
            }
        }
    }

    function upsert_question_responses_from_tm_qa($array, $hql=false, $survey = false){
        $sql = '';
        foreach($array as $response){
            if ($response['response']!='') {
                if ($survey == false) {
                    $sql = 'INSERT INTO question_responses_qa 
                        (question_id, member_id, response) 
                        VALUES 
                        ('.$response['question_id'].', 
                        '.$response['member_id'].', 
                        "'.$response['response'].'") 
                        ON DUPLICATE KEY 
                            UPDATE response="'.$response['response'].'"; ';
                } else {
                    $sql = 'INSERT INTO survey_responses_qa 
                        (question_id, member_id, response, incentive_offered) 
                        VALUES 
                        ('.$response['question_id'].', 
                        '.$response['member_id'].', 
                        "'.$response['response'].'", 
                        '.$response['incentive_offered'].') 
                        ON DUPLICATE KEY 
                            UPDATE response="'.$response['response'].'", incentive_offered = '.$response['incentive_offered'].' ; ';
                }
                $query = $this->db->query($sql);
            }elseif($hql){
                $sql = 'DELETE FROM question_responses_qa  where question_id='.$response['question_id'].' AND member_id='.$response['member_id'];
                $query = $this->db->query($sql);
            }
        }
    }

    function get_by_id($id)
    {
        $sql = 'SELECT m.*,';
        $sql .= '(SELECT response FROM question_responses WHERE question_id = 25 AND member_id = ' . $id . ' LIMIT 1) AS `company_revenue`,';
        $sql .= '(select name from sites where id = m.site_id and m.id=' . $id . ' LIMIT 1) as site_name ';
        $sql .= 'FROM members m WHERE m.id=' . $id;
        $query = $this->db2->query($sql);

        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        } else {
            return $array;
        }
    }
    
    function insert_member_qa($member){
        //check if member already exist on members_qa, if yes then we dont need to insert it on members table and return the id
        //if no then we'll have to re-insert to eg.members table
        $members_qa_exist = $this->member_exist_check(trim($member->email));
        if(empty($members_qa_exist)){
            if (is_array($member)) {
                $email_exp = explode('@', $member['email']);
                if ($this->check_email_domain($email_exp[1])) {
                    $member['email_type'] = 'personal';
                }
            } else {
                $email_exp = explode('@', $member->email);
                if ($this->check_email_domain($email_exp[1])) {
                    $member->email_type = 'personal';
                }
            }
            $member = $this->unset_nulls($member);
            $insert_member = $this->db->insert($this->members_qa, $member);
            if ($insert_member) {
                //$this->db2->insert($this->egMemberTable, $member);

                $id = $this->db->insert_id();

                return $id;
            } else {
                return 0;
               /* $data['message'] = "Sorry, Oops! Something went wrong while inserting new member";
                $data['status'] = false;
                echo json_encode($data);
                exit();*/
            }
        }else{
            return $members_qa_exist->id;
        }
    
    }
    
    function insert_eg_members_to_members_qa($email){
        $eg_member_get_sql = "SELECT email,first_name,last_name,address1,city,state,zip,country,phone,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province,company_revenue,first_qa_date,final_qa_date FROM {$this->egMemberTable} WHERE email = ?";
        $eg_member_get_query = $this->db2->query($eg_member_get_sql, array($email));echo "<pre>",print_r(array($email)), "</pre>";
        $result_eg_member = $eg_member_get_query->result();
        $result_eg_member = $result_eg_member[0];
        if(!empty($result_eg_member)) {
            $values = array();
            foreach($result_eg_member as $member_qa_field => $member_qa){
                $values[] = $member_qa;
            }
            $updated_at = date('Y-m-d H:i:s', time());
            $values[] = $updated_at;
            $sql = "insert into {$this->members_qa} (email,first_name,last_name,address1,city,state,zip,country,phone,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province,company_revenue,first_qa_date,final_qa_date,updated_at) ";
            $sql .= "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ";
            $this->db->query($sql,$values);
            $last_insert_id = $this->db->insert_id();
            return $last_insert_id;
        }
        return 0;
    }

    function insert_member_to_tm_qa_eg($member_id) {
        $eg_member_get_sql = "SELECT id,email,first_name,last_name,address1,city,state,zip,country,phone,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province,company_revenue FROM {$this->egMemberTable} WHERE id = ?";
        $eg_member_get_query = $this->db2->query($eg_member_get_sql, array($member_id));
        $result_eg_member = $eg_member_get_query->result();
        if (!empty($result_eg_member)) {
            $result_eg_member = $result_eg_member[0];
        } else {
            $result_eg_member;
        }

        if (!empty($_POST['first_name'])){
            $first_name = $_POST['first_name'];
        }
        else{
            $first_name = $result_eg_member->first_name;
        }

        if (!empty($_POST['last_name'])){
            $last_name = $_POST['last_name'];
        }
        else{
            $last_name = $result_eg_member->last_name;
        }

        if (!empty($_POST['address'])){
            $address = $_POST['address'];
        }
        else{
            $address = $result_eg_member->address1;
        }

        if (!empty($_POST['city'])){
            $city = $_POST['city'];
        }
        else{
            $city = $result_eg_member->city;
        }

        if (!empty($_POST['state'])){
            $state = $_POST['state'];
        }
        else{
            $state = $result_eg_member->state;
        }

        if (!empty($_POST['zip'])){
            $zip = $_POST['zip'];
        }
        else{
            $zip = $result_eg_member->zip;
        }

        if (!empty($_POST['country'])){
            $country = $_POST['country'];
        }
        else{
            $country = $result_eg_member->country;
        }

        if (!empty($_POST['phone'])){
            // save phone no. without country code
            $phone = substr($_POST['phone'],strlen($_POST['dial_code']));//$_POST['phone'];
        }
        else{
            $phone = $result_eg_member->phone;
        }
        if(!empty($_POST['ext'])){
            $ext = $_POST['ext'];
        }else{
            $ext = "";
        }

        if (!empty($_POST['company'])){
            $company_name = $_POST['company'];
        }
        else{
            $company_name = $result_eg_member->company_name;
        }

        if (!empty($_POST['job_title'])){
            $job_title = $_POST['job_title'];
        }
        else{
            $job_title = $result_eg_member->job_title;
        }

        if (!empty($_POST['industry'])){
            $industry = $_POST['industry'];
        }
        else{
            $industry = $result_eg_member->industry;
        }

        if (!empty($_POST['company_size'])){
            $company_size = $_POST['company_size'];
        }
        else{
            $company_size = $result_eg_member->company_size;
        }

        if (!empty($_POST['email'])){
            $email = $_POST['email'];
        }
        else{
            $email = $result_eg_member->email;
        }

        if (!empty($_POST['campaign_site'])){
            $site_id = $_POST['campaign_site'];
        }
        else{
            $site_id = $result_eg_member->site_id;
        }
        
        if (!empty($_POST['company_revenue'])){
            $company_revenue = $_POST['company_revenue'];
        }
        else{
            $company_revenue = $result_eg_member->company_revenue;
        }

        $created_at = date('Y-m-d H:i:s', time());
        $loggedUserID = $this->session->userdata('uid');
        $do_not_call = $result_eg_member->do_not_call;
        if (!empty($post_data['call_disposition'])) {
            $allowedDoNotCallDispositionCreateLeadArray = array('7','11','16','17','18');
            $allowedDoNotCallStatus = in_array($_POST['call_disposition'], $allowedDoNotCallDispositionCreateLeadArray);
            if ($allowedDoNotCallStatus) {
                $do_not_call = 1;
            }
        }

        if(!empty($result_eg_member)) {
            $default_connection = MYSQLConnectOFDefaultDB();
            $sql = " INSERT INTO {$this->members_qa} (id,email,first_name,last_name,address1,city,state,zip,country,phone,ext,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province,company_revenue)
                    VALUES
                     ('" . $result_eg_member->id . "','" . SQLInjectionOFDefaultDB($email,$default_connection) . "','" . SQLInjectionOFDefaultDB($first_name,$default_connection) . "',
                     '" . SQLInjectionOFDefaultDB($last_name,$default_connection) . "','" . SQLInjectionOFDefaultDB($address,$default_connection) . "','" . SQLInjectionOFDefaultDB($city,$default_connection) . "',
                     '" . SQLInjectionOFDefaultDB($state,$default_connection) . "','" . $zip ."','" . SQLInjectionOFDefaultDB($country,$default_connection) . "',
                     '" . SQLInjectionOFDefaultDB($phone,$default_connection) . "','" . SQLInjectionOFDefaultDB($ext,$default_connection) . "','" . SQLInjectionOFDefaultDB($company_name,$default_connection) . "','" . SQLInjectionOFDefaultDB($job_title,$default_connection) . "',
                     '" . SQLInjectionOFDefaultDB($result_eg_member->job_level,$default_connection) ." ','" . SQLInjectionOFDefaultDB($industry,$default_connection) . "','" . SQLInjectionOFDefaultDB($company_size,$default_connection) . "',
                     '" . $do_not_call . "','" . $result_eg_member->password . "','" . $result_eg_member->silo . "',
                     '" . $result_eg_member->ml_title . "','" . $loggedUserID . "','" . $result_eg_member->type . "',
                     '" . $site_id . "','" . $result_eg_member->is_valid . "','" . $result_eg_member->can_email . "',
                     '" . $result_eg_member->phone_verified . "','" . $result_eg_member->last_login . "','" . $created_at ."',
                     '" . $result_eg_member->province . "','" . SQLInjectionOFDefaultDB($company_revenue,$default_connection) . "') ";
            $this->db->query($sql);

            return $this->db->insert_id();
            //$result = $this->db2->insert('resource_views', $result_eg_member[0]);
    }

    }

    function insert_member_to_tm_qa_mpg($member_id) {
        $eg_member_get_sql = "SELECT id,email,first_name,last_name,address1,address2,city,state,zip,country,phone,company_name,do_not_call,password,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at FROM {$this->egMemberTable} WHERE id = ?";
        $eg_member_get_query = $this->db2->query($eg_member_get_sql, array($member_id));
        $result_eg_member = $eg_member_get_query->result();
        if (!empty($result_eg_member)) {
            $result_eg_member = $result_eg_member[0];
        } else {
              $result_eg_member;
        }

        if (!empty($_POST['first_name'])){
            $first_name = $_POST['first_name'];
        }
        else{
            $first_name = $result_eg_member->first_name;
        }

        if (!empty($_POST['last_name'])){
            $last_name = $_POST['last_name'];
        }
        else{
            $last_name = $result_eg_member->last_name;
        }

        if (!empty($_POST['address'])){
            $address = $_POST['address'];
        }
        else{
            $address = $result_eg_member->address1;
        }

        if (!empty($_POST['city'])){
            $city = $_POST['city'];
        }
        else{
            $city = $result_eg_member->city;
        }

        if (!empty($_POST['state'])){
            $state = $_POST['state'];
        }
        else{
            $state = $result_eg_member->state;
        }

        if (!empty($_POST['zip'])){
            $zip = $_POST['zip'];
        }
        else{
            $zip = $result_eg_member->zip;
        }

        if (!empty($_POST['country'])){
            $country = $_POST['country'];
        }
        else{
            $country = $result_eg_member->country;
        }

        if (!empty($_POST['phone'])){
            $phone = $_POST['phone'];
        }
        else{
            $phone = $result_eg_member->phone;
        }

        if (!empty($_POST['company'])){
            $company_name = $_POST['company'];
        }
        else{
            $company_name = $result_eg_member->company_name;
        }


        if (!empty($_POST['email'])){
            $email = $_POST['email'];
        }
        else{
            $email = $result_eg_member->email;
        }

        if (!empty($_POST['campaign_site'])){
            $site_id = $_POST['campaign_site'];
        }
        else{
            $site_id = $result_eg_member->site_id;
        }

        $created_at = date('Y-m-d H:i:s', time());
        $loggedUserID = $this->session->userdata('uid');
        $do_not_call = $result_eg_member->do_not_call;
        if (!empty($post_data['call_disposition'])) {
            $allowedDoNotCallDispositionCreateLeadArray = array('7','11','16','17','18');
            $allowedDoNotCallStatus = in_array($_POST['call_disposition'], $allowedDoNotCallDispositionCreateLeadArray);
            if ($allowedDoNotCallStatus) {
                $do_not_call = 1;
            }
        }

        if(!empty($result_eg_member)) {
            $sql = " INSERT INTO {$this->members_qa} (id,email,first_name,last_name,address1,city,state,zip,country,phone,company_name,do_not_call,password,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at)
                    VALUES
                     ('" . $result_eg_member->id . "','" . SQLInjectionOFDefaultDB($email) . "','" . SQLInjectionOFDefaultDB($first_name) . "',
                     '" . SQLInjectionOFDefaultDB($last_name) . "','" . SQLInjectionOFDefaultDB($address) . "','" . SQLInjectionOFDefaultDB($city) . "',
                     '" . SQLInjectionOFDefaultDB($state) . "','" . $zip ."','" . SQLInjectionOFDefaultDB($country) . "',
                     '" . SQLInjectionOFDefaultDB($phone) . "','" . SQLInjectionOFDefaultDB($company_name) . "',
                     '" . $do_not_call . "','" . $result_eg_member->password . "',
                     '" . $loggedUserID . "','" . SQLInjectionOFEGDB($result_eg_member->type) . "',
                     '" . $site_id . "','" . $result_eg_member->is_valid . "','" . $result_eg_member->can_email . "',
                     '" . $result_eg_member->phone_verified . "','" . $result_eg_member->last_login . "','" . $created_at ."')";
            $this->db->query($sql);

            return $this->db->insert_id();
            //$result = $this->db2->insert('resource_views', $result_eg_member[0]);
    }

    }

    function update_member_from_tm_qa($member) {

        $member = $this->unset_nulls($member);
        $member = $this->unset_questions($member);
        if(!empty($member->country)){
            $member->country = strtoupper($member->country);
        }
        if(!empty($member->country) && ($member->country=='US' || $member->country=='CA')){
            $member->province=NULL;
        }
        //#1244 - Campaign Report Edit Error

        if(!empty($member->country) && !in_array($member->country, array('US','CA'))){
            $member->state = null;
    }

        //Value should be assigned with "Null" string to replace with empty value By Ravindra[29-08-2013]
        $member = $this->set_member_empty_values($member);
        if($this->db->update($this->members_qa, $member, array('id' => $member->id))) {
            $status = $this->db->update($this->members_qa, $member, array('id' => $member->id));
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong.');
            redirect('/dialer/leads');
        }
        return $status;
    }

    function update_members_qa($member, $filter){
        //update members_qa from upload base on the given member_id
        //Value should be assigned with "Null" string to replace with empty value
        $member = $this->set_member_empty_values($member);
        $status = $this->db->update($this->members_qa, $member, $filter);
        return $status;
    }

    /* Dev_NV Region End */
    
    /* dev_GC region Start */
    function get_med_schools_by_location($med_school_location, $location_op) {
        $filter = "";
        if ($location_op == 'country')
            $filter = " country='" . $med_school_location . "'";
        else if ($location_op == 'state')
            $filter = " state='" . $med_school_location . "'";

        $sql = "select id,country,state,school from med_schools where " . $filter;
        $query = $this->db2->query($sql, array($med_school_location));
        #echo $this->db->last_query();exit;
        return $query->result();
    }
    
    function get_location_by_school($med_school) {
        $sql = "select id,country,state,school from med_schools where school = ?";
        $query = $this->db2->query($sql, array($med_school));
        $array = $query->result();
        if (!empty($array)) {
            return $array[0];
        }
    }
    /* dev_GC region End */

    public function delete_member_record($id){
        $sql = "DELETE FROM {$this->egMemberTable} where id={$id}";
        $query = $this->db2->query($sql);
    }

    function reinsert_member_to_eg($id,$first_qa_date,$final_qa_date,$source = 'call_file') {
        $eg_id = false;
        $originalOwner = "";
        $originalOwnerValue = "";
        if(in_array($source, array('add_diff','form'))){
            $originalOwner = ",original_owner";
            $originalOwnerValue = ",'PureB2B'";
        }
        $member_qa_get_sql = "SELECT email,first_name,last_name,address1,city,state,zip,country,phone,ext,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,NOW() as last_login,NOW() as created_at,company_revenue,province,source {$originalOwner} FROM {$this->members_qa} WHERE id = ?";
        $member_qa_get_query = $this->db->query($member_qa_get_sql, array($id));
        $result_member_qa = $member_qa_get_query->result_array();
        if (!empty($result_member_qa)) {
            $new_id = '';
            $result_member_qa = $result_member_qa[0];
        
            //#3068 check first if member has been reinserted already on eg.members table. 
            //If yes then update contacts,lead_history, members_qa,members_qa_history,question_responses_qa
            //if no then just reinsert
            // $id = $this->updated_member_ids($result_member_qa['email'], $result_member_qa['id'],$result_member_qa['email'],$id);
            
            $values = "";            
            $on_dupe = "";
            foreach($result_member_qa as $member_qa_field => $member_qa){
                if($member_qa_field == 'original_owner'){
                    $member_qa = "PureB2B";
                }else{
                    $member_qa = addslashes($member_qa);
                }
                $values .= "'{$member_qa}'";
                $values .= ",";
                if($member_qa_field != 'created_at' && $member_qa_field != 'source' && $member_qa_field != 'original_owner'){
                    $on_dupe .= "{$member_qa_field} = VALUES({$member_qa_field})";
                    $on_dupe .= ",";
                }
            }
            $updated_at = date('Y-m-d H:i:s', time());
            $values .= "'{$updated_at}',";
            $values .= "'{$first_qa_date}',";
            $values .= "'{$final_qa_date}'";
            $on_dupe .= "updated_at = VALUES(updated_at),first_qa_date = VALUES(first_qa_date),final_qa_date = VALUES(final_qa_date)";
            if(in_array($source, array('add_diff','form'))){
                $on_dupe .= ",original_owner = VALUES(original_owner)";
            }
            $sql = "insert into {$this->egMemberTable} (email,first_name,last_name,address1,city,state,zip,country,phone,ext,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,company_revenue,province,source{$originalOwner},updated_at,first_qa_date,final_qa_date) ";
            $sql .= "VALUES ({$values}) ";
            $sql .= "ON DUPLICATE KEY UPDATE {$on_dupe}";
            

            if($this->db2->query($sql)) {

                // get member_id from EG
                $sql = "SELECT id FROM members WHERE email = ?";
                $get_member_id_query = $this->db2->query($sql, array($result_member_qa['email']));

                $eg_id = $get_member_id_query->result_array()[0]['id'];

                // during the selected time period
                $this->db2->query("INSERT INTO members_update_history (member_id,updated_at) VALUES (".$eg_id.",'".$updated_at."') ON DUPLICATE KEY UPDATE updated_at='".$updated_at."'");

                // insert eg_member_id to members_qa table
                $this->db->query("UPDATE {$this->members_qa} SET eg_member_id=".$eg_id." WHERE id=".$id);                
            }
        }
        return $eg_id;
    }
    
    function insert_datateam_qa_history($obj) {
        $this->db2->insert('datateam_member_qa_history', $obj);                
    }

    function updated_member_ids($email, $old_id, $old_email='', $old_member_qa_id=''){
        //#3068 check first if member has been reinserted already on eg.members table. 
        //If yes then update contacts,lead_history, members_qa,members_qa_history,question_responses_qa
        $check_eg_members = "select id,email from {$this->egMemberTable} where email = '".addslashes($email)."'";
        $eg_member_query = $this->db2->query($check_eg_members);
        $eg_member_data = $eg_member_query->result_array();
        if(!empty($eg_member_data) &&  $eg_member_data[0]['id'] <> $old_id){
            $eg_member_data = $eg_member_data[0];
            $this->db->where('member_id',  $old_id);
            $this->db->update("contacts", array("member_id" => $eg_member_data['id']));
            
            $this->db->where('member_id',  $old_id);
            $this->db->update("lead_history", array("member_id" => $eg_member_data['id']));
            
            $this->db->where('id',  $old_id);
            $this->db->update("members_qa", array("id" => $eg_member_data['id']));
            
            $this->db->where('member_id',  $old_id);
            $this->db->update("members_qa_history", array("member_id" => $eg_member_data['id']));
            
            $this->db->where('member_id',  $old_id);
            $this->db->update("question_responses_qa", array("member_id" => $eg_member_data['id']));
            $id = $eg_member_data['id'];
            
            //update contact_history 
            $this->db->where('members_qa_id',  $old_id);
            $this->db->update("contacts_history", array("members_qa_id" => $eg_member_data['id']));
            
            if( $old_email!='' && $old_member_qa_id!='' && ( $old_email != $email || $eg_member_data['id'] != $old_id ) ) {
                //insert contact history for member_id/email changed
                $changeset['members_qa_id'] = $old_id;
                $changeset['new_email'] = $email;
                $changeset['old_email'] = $old_email;
                $changeset['new_id'] = $eg_member_data['id'];
                $changeset['module'] = 'TM Calls';

                $this->create_contact_history( $changeset );
            }
            return $id;
        }else{
            if( $old_email!='' && $old_member_qa_id!='' && ( $old_email != $email || $old_member_qa_id != $old_id ) ){
               //insert contact history for member_id/email changed
                $changeset['members_qa_id'] = $old_member_qa_id;
                $changeset['new_email'] = $email;
                $changeset['old_email'] = $old_email;
                $changeset['new_id'] = $old_id;
                $changeset['module'] = 'TM Calls';

                $this->create_contact_history( $changeset );
            }
            return $old_id;
        }
        
        
    }

    function update_join_contacts_batch($filter,$filter_type='member_id'){
        if($filter_type=='email'){
            $sql = "UPDATE members_qa t1 join contacts t2 on t1.email = t2.email
                 SET 
                    t1.email = t2.email,
                    t1.first_name = t2.first_name,
                    t1.last_name = t2.last_name,
                    t1.job_title = t2.job_title,
                    t1.job_level = t2.job_level,
                    t1.silo = t2.job_function,
                    t1.company_name = t2.company,
                    t1.address1 = t2.address,
                    t1.city = t2.city,
                    t1.zip = t2.zip,
                    t1.state = t2.state,
                    t1.country = t2.country,
                    t1.industry = t2.industry,
                    t1.company_size = t2.company_size,
                    t1.phone = t2.phone,
                    t1.ext = t2.ext,
                    t1.original_owner = t2.original_owner,
                    t1.company_revenue = t2.company_revenue
                 WHERE  t1.email in ({$filter})";
        }else{
            $sql = "UPDATE members_qa t1 join contacts t2 on t1.id = t2.member_id
                 SET 
                    t1.email = t2.email,
                    t1.first_name = t2.first_name,
                    t1.last_name = t2.last_name,
                    t1.job_title = t2.job_title,
                    t1.job_level = t2.job_level,
                    t1.silo = t2.job_function,
                    t1.company_name = t2.company,
                    t1.address1 = t2.address,
                    t1.city = t2.city,
                    t1.zip = t2.zip,
                    t1.state = t2.state,
                    t1.country = t2.country,
                    t1.industry = t2.industry,
                    t1.company_size = t2.company_size,
                    t1.phone = t2.phone,
                    t1.ext = t2.ext,
                    t1.original_owner = t2.original_owner,
                    t1.company_revenue = t2.company_revenue
                 WHERE  t1.id in ({$filter})";
        }
        //echo $sql;
        $this->db->query($sql);
    }
    
    function create_contact_history( $changeset = array() ){
        if ($changeset['members_qa_id'] != $changeset['new_id'] || $changeset['old_email'] != $changeset['new_email']){
            $sql = "INSERT INTO `members_email_history` ( `module`, `members_id_from`, `members_id_to`, `email_from`, `email_to`, `updated_by`, `updated_at` ) ";
            $sql .= "VALUES( '" . $changeset['module'] . "', " . $changeset['members_qa_id'] . "," . $changeset['new_id'] . ",'" . $changeset['old_email'] . "','" . $changeset['new_email'] . "'," . $this->session->userdata('uid') . ", '" . date('Y-m-d H:i:s', time()) . "')";
            $this->db->query($sql);
        }
        
        return 1;
    }
    
    function insert_member_to_members_qa($email) {
        $data = $this->set_members_qa_value();

        if(!empty($data)) {
            $sql_header = "`email`,`first_name`,`last_name`,`address1`,`city`,`state`,`zip`,`country`,`phone`,`ext`,`company_name`,`job_title`,`job_level`,`industry`,`company_size`,`company_revenue`,`do_not_call`,`updated_by`,`site_id`,`created_at`,`province`";
            $insert_sql = "";
            $update_sql = "";
            
            foreach( $data as $key => $item ){
                $insert_sql .= "'" . $item . "',"; 
                $update_sql .= $key . " = '" . $item . "',"; 
            }
            
            $insert_sql = substr( $insert_sql, 0, -1 );
            $update_sql = substr( $update_sql, 0, -1 );
            
            $sql = "INSERT IGNORE INTO " . $this->members_qa . " (" . $sql_header . ") VALUES ( %s ) ";
            $sql .= "ON DUPLICATE KEY UPDATE " . $update_sql;
            $sql = sprintf( $sql, $insert_sql );
           
            $this->db->query($sql);
            
            return $this->db->insert_id();
        }

    }
    
    function set_members_qa_value(){
        $data = array();
     
        $first_name = "";
        $last_name = "";
        $address = "";
        $city = "";
        $state = "";
        $zip = "";
        $country = "";
        $phone = "";
        $company_name = "";
        $job_title = "";
        $company_size = "";
        $company_revenue = "";
        $email = "";
        $site_id = "";
        $industry = "";
        $ext = "";
        $do_not_call = 0;
        $country = "";
        $job_level = "";
        $province = "";
        
        if (!empty($_POST['first_name'])){
            $first_name = $_POST['first_name'];
        }
        
        if (!empty($_POST['last_name'])){
            $last_name = $_POST['last_name'];
        }

        if (!empty($_POST['address'])){
            $address = $_POST['address'];
        }

        if (!empty($_POST['city'])){
            $city = $_POST['city'];
        }

        if (!empty($_POST['state'])){
            $state = $_POST['state'];
        }

        if (!empty($_POST['zip'])){
            $zip = $_POST['zip'];
        }

        if (!empty($_POST['country'])){
            $country = strtoupper( $_POST['country'] );
        }

        if (!empty($_POST['phone'])){
            // save phone no. without country code
            $phone = substr($_POST['phone'],strlen($_POST['dial_code']));//$_POST['phone'];
        }
        
        if(!empty($_POST['ext'])){
            $ext = $_POST['ext'];
        }

        if (!empty($_POST['company'])){
            $company_name = $_POST['company'];
        }
        
        if (!empty($_POST['job_title'])){
            $job_title = $_POST['job_title'];
        }

        if (!empty($_POST['industry'])){
            $industry = $_POST['industry'];
        }

        if (!empty($_POST['company_size'])){
            $company_size = $_POST['company_size'];
        }
        
         if (!empty($_POST['company_revenue'])){
            $company_revenue = $_POST['company_revenue'];
        }

        if (!empty($_POST['email'])){
            $email = $_POST['email'];
        }
        if (!empty($_POST['campaign_site'])){
            $site_id = $_POST['campaign_site'];
        }

        if (!empty($post_data['call_disposition'])) {
            $allowedDoNotCallDispositionCreateLeadArray = array('7','11','16','17','18');
            $allowedDoNotCallStatus = in_array($_POST['call_disposition'], $allowedDoNotCallDispositionCreateLeadArray);
            if ($allowedDoNotCallStatus) {
                $do_not_call = 1;
            }
        }
        
        if( !in_array( $country, array('US','CA') ) ){
            $province = null;
            $state = null;
        }
        
        $data = array(  'email' => $email,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'address1' => $address,
                        'city' => $city,
                        'state' => $state,
                        'zip' => $zip,
                        'country' => $country,
                        'phone' => $phone,
                        'ext' => $ext,
                        'company_name' => $company_name,
                        'job_title' => $job_title,
                        'job_level' => $job_level,
                        'industry' => $industry,
                        'company_size' => $company_size,
                        'company_revenue' => $company_revenue,
                        'do_not_call' => $do_not_call,
                        'updated_by' => $this->session->userdata('uid'),
                        'site_id' => $site_id,
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'province' => $province );
        return $data;
    }
    
    function getOriginalContact($campaignId, $email, $field = '*'){
        $sql = "select " . $field . " from members_qa m ";
        $sql .= "JOIN contacts c ON m.id = c.member_id ";
        $sql .= "JOIN campaign_contacts cc ON c.id = cc.contact_id ";
        $sql .= "WHERE member_id > 0 and cc.campaign_id = " . $campaignId . " and m.email IN (" . $email . ")";
        
        $query = $this->db->query($sql);
        $res = $query->result();
        
        return $res;
    }
    
    function updateMembersQA ($values, $filter = 'id', $tmp_table = 'contacts') {
        $sql = "UPDATE members_qa t1 ";
        $sql .= "INNER JOIN ( ";
            $sql .= "SELECT t2.email,t2.first_name,t2.last_name,t2.job_title,t2.job_level,t2.job_function,t2.company,t2.address,t2.city,t2.zip,t2.state,t2.country,t2.industry,t2.company_size,t2.phone,t2.ext,t2.original_owner,t2.company_revenue ";
            $sql .= "FROM " . $tmp_table . " t2 ";
            $sql .= "WHERE t2." . $filter . " IN (" . $values . ")";
            $sql .= ") t3 on t1.email = t3.email ";
        $sql .= "SET ";
        $sql .= "t1.email = t3.email, ";
        $sql .= "t1.first_name = t3.first_name, ";
        $sql .= "t1.last_name = t3.last_name, ";
        $sql .= "t1.job_title = t3.job_title, ";
        $sql .= "t1.job_level = t3.job_level, ";
        $sql .= "t1.silo = t3.job_function, ";
        $sql .= "t1.company_name = t3.company, ";
        $sql .= "t1.address1 = t3.address, ";
        $sql .= "t1.city = t3.city, ";
        $sql .= "t1.zip = t3.zip, ";
        $sql .= "t1.state = t3.state, ";
        $sql .= "t1.country = t3.country, ";
        $sql .= "t1.industry = t3.industry, ";
        $sql .= "t1.company_size = t3.company_size, ";
        $sql .= "t1.phone = t3.phone, ";
        $sql .= "t1.ext = t3.ext, ";
        $sql .= "t1.original_owner = t3.original_owner, ";
        $sql .= "t1.company_revenue = t3.company_revenue ";
        
        $this->db->query($sql);
    }
    
    function updateMembersQAOnDupe ($insert_string = array()) {
        $sql = 'INSERT INTO members_qa (email,first_name,last_name,job_title,job_level,silo,company_name,address1,city,state,country,zip,phone,ext,company_size,company_revenue,industry,original_owner,last_login,created_at,updated_at,last_edit_form_display,last_edit_form_update) VALUES %s ';
        $sql .= 'ON DUPLICATE KEY UPDATE first_name = VALUES(first_name),last_name = VALUES(last_name),job_title = VALUES(job_title),job_level = VALUES(job_level),silo = VALUES(silo),company_name = VALUES(company_name),address1 = VALUES(address1),city = VALUES(city),zip = VALUES(zip),state = VALUES(state),country = VALUES(country),industry = VALUES(industry),company_size = VALUES(company_size),phone = VALUES(phone),ext = VALUES(ext),original_owner = VALUES(original_owner),company_revenue = VALUES(company_revenue)';
        $sql = sprintf($sql, implode(",", $insert_string));
        
        $result = $this->db->query($sql);
        unset($insert_string);
        unset($sql);
        return 1;
    }
}

class Member
{

    public $id;
    public $first_name;
    public $last_name;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $province;
    public $country;
    public $email;
    public $email_type;
    public $password;
    public $phone;
    public $phone_verified;
    public $phone_verified_date;
    public $phone_type;
    public $company_name;
    public $source;
    public $site_id;
    public $type;
    public $is_valid;
    public $can_email;
    public $ip;
    public $ml_title;
    public $dex_title;
    public $job_title;
    public $job_level;
    public $industry;
    public $company_size;
    public $company_size_integer;
    public $silo;
    public $hq_phone;
    public $hq_phone_verified;
    public $hq_address;
    public $hq_city;
    public $hq_state;
    public $hq_zip;
    public $hq_country;
    public $hq_company_name;
    public $updated_by;
    public $last_login;
    public $created_at;
    public $updated_at;
    public $last_edit_form_display;
    public $last_edit_form_update;
    public $company_url;
    public $do_not_call;
    public $first_qa_date;
    public $final_qa_date;
    public $email_problem;
    public $cant_find_online;
    public $previous_company;
    public $linkedin_url;
    public $qa_file_id;
}

class Membermpg{
    public $id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $email;
    public $npi;
    public $email_type;
    public $password;
    public $user_name;
    public $profile_name;
    public $phone;
    public $phone_old;
    public $phone_verified;
    public $phone_confirmed;
    public $mobile_phone;
    public $company_name;
    public $source;
    public $site_id;
    public $type;
    public $is_valid;
    public $can_email;
    public $photo;
    public $ip;
    public $last_login;
    public $created_at;
    public $updated_at;
    public $practice_size_integer;
    public $office_manager;
    public $office_manager_first_name;
    public $office_manager_middle_name;
    public $office_manager_last_name;
    public $office_mgr_email;
    public $total_employees;
    public $daily_patient_volume;
    public $annual_sales;
    public $group_name;
    public $parent_company_name;
    public $hds;
    public $status_of_provider;
    public $inactivation_reason_code;
    public $inactivation_reason_description;
    public $practice_phone;
    public $updated_by;
    public $last_edit_form_display;
    public $last_edit_form_updated;
    public $do_not_call;
    public $email_problem;
    public $cant_find_online;
    public $previous_company;
    public $linkedin_url;
    public $admin_edit;
    public $qa_file_id;
}

?>
