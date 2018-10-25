<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Resourceview_model extends CI_Model
{
    public $table = 'resource_views';
	public $company_table = 'companies';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
//        $this->db2 = $this->load->database('db2', TRUE);
    }

    function set_ongoing_qa($lead_id)
    {
        return $this->db->update('lead_history', array('is_qa_in_progress' => 1), array('id' => $lead_id));
    }

    function set_qa($lead_id, $member_id)
    {
        return $this->db->update('lead_history', array('qa' => $member_id, 'status' => 'QA in progress', 'updated_at' => date('Y-m-d H:i:s', time())), array('id' => $lead_id));
    }
    
    function set_qa_in_progress($lead_id, $user_id){
        return $this->db->update('lead_history', array('qa' => $user_id, 'status' => 'QA in progress', 'updated_at' => date('Y-m-d H:i:s', time()), 'is_qa_in_progress' => 1), array('id' => $lead_id));
    }

    function is_set_report_display($member_id, $campaign_id)
    {
        $sql = "SELECT id FROM resource_views WHERE member_id = ? AND campaign_id = ? and report_display = 1 LIMIT 1";
        $query = $this->db2->query($sql, (array($member_id, $campaign_id)));
        $result = $query->result();
        if (!empty($result)) {
            return true;
        } else {
            return false;
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

    function insert_temp($obj,$callsModel)
    {
        //$notes = trim($obj->notes);
        //$obj->notes = '';
        $loggedUserID = $this->session->userdata('uid');
        if (isset($obj->user_id)) {
            $uid = $obj->user_id;
            unset($obj->user_id);
        }

        $obj = $this->unset_nulls($obj);
        $result = $this->db->insert('lead_history', $obj);

        $resourceViewID = $this->db->insert_id();
        // if ($result) {
        //     $lead_history_id = $resourceViewID;

        //     $userType = $this->session->userdata('user_type');
        //     $last_call_history_id = "";
        //     if(isset($_POST['last_call_history_id']) && !empty($_POST['last_call_history_id'])){
        //         $last_call_history_id = $_POST['last_call_history_id'];
        //         $this->check_call_history_exist($userType, $last_call_history_id,$callsModel);
        //     }

        //     if($resourceViewID > 0){

        //         $lead_status = null;
        //         $allowedCallDispositionCreateLeadArray = array('1');
        //         $allowedCreateLeadStatus = false;
        //         if(!empty($_POST['call_disposition'])){
        //             $allowedCreateLeadStatus = in_array($_POST['call_disposition'],$allowedCallDispositionCreateLeadArray);
        //         }
        //         if($userType != 'qa'){
        //             if (!$allowedCreateLeadStatus) {
        //                 $lead_status = 'In Progress';
        //             }else{
        //                 $lead_status = 'Pending';
        //             }
        //         }
        //         if($userType != 'agent'){
        //             if(isset($_POST['decision']) && $_POST['decision'] == 'Approve'){
        //                 $lead_status = 'Approve';
        //             }else if(isset($_POST['decision']) && $_POST['decision'] == 'Follow Up'){
        //                 $lead_status = 'Follow-up';
        //             }else if(isset($_POST['decision']) && $_POST['decision'] == 'Update and Submit'){
        //                 $lead_status = 'Pending';
        //             }else if(isset($_POST['decision']) && $_POST['decision'] == 'Reject'){
        //                 $lead_status = 'Reject';
        //             }else if (isset($_POST['decision']) && $_POST['decision'] == 'Duplicate Lead') {
        //                 $lead_status = 'Duplicate Lead';
        //             }
        //         }

        //         $lead_status_id = "0";
        //         $lead_status_sql = $this->db->insert('lead_status', array('lead_history_id' => $resourceViewID, 'user_id' => $loggedUserID, 'status' => $lead_status, 'created_at' => $obj->updated_at)); // , 'user_id' => $uid
        //         if (!$lead_status_sql) {
        //                 $this->session->set_flashdata('class', 'bad');
        //                 $this->session->set_flashdata('msg', 'Sorry, lead status was not created.');
        //             redirect('/dialer/contacts/index/' . $_POST['campaign_id']);
        //         } else {
        //                 $lead_status_id = $this->db->insert_id();
        //             }
        //         if (!empty($notes)) {
        //             // insert notes into the notes table
        //             $notes_sql = $this->db->insert('notes', array('lead_history_id' => $resourceViewID, 'lead_status_id' => $lead_status_id, 'call_history_id' => $last_call_history_id, 'note' => $notes, 'user_id' => $loggedUserID, 'created_at' => $obj->updated_at)); // , 'user_id' => $uid
        //             if (!$notes_sql) {
        //                 $this->session->set_flashdata('class', 'bad');
        //                 $this->session->set_flashdata('msg', 'Sorry, notes were not created.');
        //                 redirect('/dialer/contacts/index/' . $_POST['campaign_id']);
        //             }
        //         }
        //     }
        // } else {
        //     $resourceViewID = 0;
        // }

        return $resourceViewID;
    }
    
    function insert($obj)
    {
        try {
            $tm_note = new stdClass();
            if(isset($obj->notes)) {
                $tm_note->note = $obj->notes;
            } else {
                $tm_notes_query = $this->db->query("SELECT * FROM notes WHERE lead_history_id = " . $this->input->post('lead_id'));
                $tm_notes = $tm_notes_query->result();
                $tm_note->note = (!empty($tm_notes) ? $tm_notes[0]->note : '');
            }
            unset($obj->notes);
            $lead_history_id = (isset($obj->lead_history_id) ? $obj->lead_history_id : $this->input->post('lead_id'));
            unset($obj->lead_history_id);
            $result = $this->db2->insert($this->table, $obj);

            if ($result) {
                // set resource_views id first
                $resourceView_id = $this->db2->insert_id();
                // then insert the tm_members_qa and tm_lead_history records
                $sql = " INSERT IGNORE INTO tm_members_qa (id,email,first_name,last_name,address1,city,state,zip,country,phone,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province) SELECT id,email,first_name,last_name,address1,city,state,zip,country,phone,company_name,job_title,job_level,industry,company_size,do_not_call,password,silo,ml_title,updated_by,type,site_id,is_valid,can_email,phone_verified,last_login,created_at,province FROM members WHERE id = ?";
                $this->db2->query($sql, array($obj->member_id));
                $this->db2->insert('tm_lead_history', $obj);
                // lastly, insert tm notes
                if($tm_note->note != '') {
                    $tm_note->lead_history_id = $this->db2->insert_id();
                    $tm_note->user_id = $this->session->userdata('uid');
                    $tm_note->created_at = $obj->created_at;
                    $this->db2->insert('tm_notes', $tm_note);
                }
            } else {
                $resourceView_id = 0;
            }
            return $resourceView_id;
        } catch(Exception $e) {
            return 'Error inserting: ' . $e->getMessage();
        }
    }

    function get_parent_lead($campaign_id, $member_id)
    {
       $sql = "select * from resource_views where campaign_id = ? and member_id = ? and report_display=1  order by created_at desc limit 1";
        $query = $this->db2->query($sql, array($campaign_id, $member_id));

        $array = $query->result_array();
        if (!empty($array)) {
            return $array[0];
        } else {
            return null;
        }
    }

    function convert_temp($id, $create_parent = false, $eg_member_id)
    {
        $func = 'getResourceviewsFields'.ucfirst($this->app);
        $fields = $this->$func($create_parent);
        $tm_lead_history_sql = "SELECT {$fields}, {$eg_member_id} as `member_id` FROM lead_history lh
                                JOIN `campaigns` c ON c.id = lh.campaign_id WHERE lh.id=?";
        $tm_lead_history_query = $this->db->query($tm_lead_history_sql, array($id));

        $result_tm_lead_history = $tm_lead_history_query->result();

        if(!empty($result_tm_lead_history)){
            return $this->insert($result_tm_lead_history[0]);
        }
        return false;
    }
    
    function insert_eg_tm_lead_history($tm_lead_history) {
        try {
            // successfully inserted this resource_views record into the EG database; first retrieve the lead_history record and insert that
            $this->db2->insert('tm_lead_history', $tm_lead_history);
            $eg_lh_id = $this->db2->insert_id();
            return $eg_lh_id;
        } catch (Exception $e) {
            throw new Eception("DB Exception when inserting tm lead history: " . $e->getMEssage(), 1);
        }
    }
    
    function insert_eg_tm_notes($id, $eg_lh_id) {
        try {
            // Now retrieve tm notes for this lead and copy those over too
            $sqlNotes = "SELECT user_id,note,created_at FROM notes WHERE lead_history_id = ?";
            $tm_notes = $this->db->query($sqlNotes, array($id));
            $tm_notes_results = $tm_notes->result();
            if(!empty($tm_notes_results)) {
                foreach($tm_notes_results as &$tm_note) {
                    $tm_note->lead_history_id = $eg_lh_id;
                    $this->db2->insert('tm_notes', $tm_note);
                }
            }
        } catch(Exception $e) {
            throw new Exception("DB Exception when inserting tm notes: " . $e->getMEssage(), 1);
        }
    }

    function getResourceviewsFieldsEg($for_parent = false){
        if($for_parent) {
            return 'lh.site_id,lh.resource_id,c.eg_campaign_id AS campaign_id,1 as qualified,lh.is_downloaded,
                                lh.source,ip,lh.created_at,lh.updated_at,lh.message_sent,lh.report_display,
                                lh.nurture_sent,lh.lead_posted,lh.unqualified_reason,partner,lh.returned,
                                lh.reason_for_return,lh.date_returned,lh.useragent,lh.is_dismissed,1 as processed';
        } else {
            return 'lh.site_id,lh.resource_id,c.eg_campaign_id AS campaign_id,lh.qualified,lh.is_downloaded,
                                lh.source,ip,lh.created_at,lh.updated_at,lh.message_sent,lh.report_display,
                                lh.nurture_sent,lh.lead_posted,lh.unqualified_reason,partner,lh.returned,
                                lh.reason_for_return,lh.date_returned,lh.useragent,lh.is_dismissed,lh.processed';
        }
    }
    
    function getResourceviewsFieldsMpg($for_parent = false){
        return 'lh.site_id,lh.member_id,lh.resource_id,c.eg_campaign_id AS campaign_id,lh.qualified,lh.is_downloaded,
                                lh.source,ip,lh.created_at,lh.updated_at,lh.nurture_sent,partner,lh.processed';
    }

    /**
     * Update lead history,
     * @param type $callsModel
     * @param type $obj
     * @param type $is_agent
     */
    function update_tmp($callsModel,$obj,$is_agent=false)
    {
        //$notes = trim($obj->notes);
        unset($obj->notes);
        if(isset($obj->resource_id)){
            $resource_id = $obj->resource_id;
            $resource_name = $obj->resource_name;  
        }  
        
        $loggedUserID = $this->session->userdata('uid');
        
        $obj = $this->unset_nulls($obj);
        if($is_agent){           
            $obj->qa = '';
        }else{
            $obj->qa = $loggedUserID;
        }

        if(isset($resource_id)){
            $obj->resource_id = $resource_id;
            $obj->resource_name = $resource_name;
        }
        if(!empty($obj->call_disposition_id) && $obj->call_disposition_id != '2'){
            $obj->call_disposition_update_date = '';
        }
        
        $update_tm_lead_history = $this->db->update('lead_history', $obj, array('id' => $obj->id));

        if(!$update_tm_lead_history){
            $data['message'] = "Sorry, Oops! Something went wrong in existing lead history.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
        // $resourceViewID = $obj->id;
        // $lead_history_id = $resourceViewID;

        // $userType = $this->session->userdata('user_type');
        // $last_call_history_id = "";
        // if(isset($_POST['last_call_history_id']) && !empty($_POST['last_call_history_id'])){
        //     $last_call_history_id = $_POST['last_call_history_id'];
        //     $this->check_call_history_exist($userType, $last_call_history_id,$callsModel);
        // }

        // if($resourceViewID > 0){

        //     $lead_status = null;
        //     $allowedCallDispositionCreateLeadArray = array('1');
        //     $allowedCreateLeadStatus = false;
        //     if(!empty($_POST['call_disposition'])){
        //         $allowedCreateLeadStatus = in_array($_POST['call_disposition'],$allowedCallDispositionCreateLeadArray);
        //     }

        //     if($userType != 'qa'){
        //         if (!$allowedCreateLeadStatus) {
        //             $lead_status = 'In Progress';
        //         }else{
        //             $lead_status = 'Pending';
        //         }
        //     }
        //     if($userType != 'agent'){
        //         if(isset($_POST['decision']) && $_POST['decision'] == 'Approve'){
        //             $lead_status = 'Approve';
        //         }else if(isset($_POST['decision']) && $_POST['decision'] == 'Follow Up'){
        //             $lead_status = 'Follow-up';
        //         }else if(isset($_POST['decision']) && $_POST['decision'] == 'Update and Submit'){
        //             $lead_status = 'Pending';
        //         }else if(isset($_POST['decision']) && $_POST['decision'] == 'Reject'){
        //             $lead_status = 'Reject';
        //         }else if (isset($_POST['decision']) && $_POST['decision'] == 'Duplicate Lead') {
        //             $lead_status = 'Duplicate Lead';
        //         }
        //     }

        //     $lead_status_id = '0';
        //     $lead_status_sql = $this->db->insert('lead_status', array('l' => $resourceViewID, 'user_id' => $loggedUserID, 'status' => $lead_status, 'created_at' => $obj->updated_at)); // , 'user_id' => $uid
        //     if (!$lead_status_sql) {
        //             $this->session->set_flashdata('class', 'bad');
        //             $this->session->set_flashdata('msg', 'Sorry, lead status was not created.');
        //         redirect('/dialer/contacts/index/' . $_POST['campaign_id']);
        //     } else {
        //             $lead_status_id = $this->db->insert_id();
        //         }
        //     if (!empty($notes)) {
        //         $notes_sql = $this->db->insert('notes', array('lead_history_id' => $resourceViewID, 'lead_status_id' => $lead_status_id, 'call_history_id' => $last_call_history_id, 'note' => $notes, 'user_id' => $loggedUserID, 'created_at' => $obj->updated_at)); // , 'user_id' => $uid
        //         if (!$notes_sql) {
        //             $this->session->set_flashdata('class', 'bad');
        //             $this->session->set_flashdata('msg', 'Sorry, notes were not created.');
        //                 redirect('/dialer/contacts/index/' . $_POST['campaign_id']);
        //         }
        //     }
        // }
    }

    function get_one($id)
    {
        $sql = 'SELECT * FROM lead_history where id = ?';
        $query = $this->db->query($sql, array($id));
        $result = $query->result();
        if (!empty($result)) {
        return $result[0];
        } else {
            return $result;
    }
    }

    function get_eg_campaign_one($id, $eg_member_id)
    {
        $sql = "SELECT tlh.`id`,tlh.`site_id`,{$eg_member_id} as `member_id`,tlh.`resource_id`,tlh.`qualified`,tlh.`is_downloaded`,tlh.`source`,tlh.`ip`,
                tlh.`created_at`,tlh.`updated_at`,tlh.`message_sent`,tlh.`report_display`,tlh.`nurture_sent`,tlh.`lead_posted`,tlh.`unqualified_reason`,tlh.`partner`,
                tlh.`returned`,tlh.`reason_for_return`,tlh.`date_returned`,tlh.`useragent`,tlh.`is_dismissed`,tlh.`processed`,tlh.`status`,tlh.`disapprove_reason`,tlh.`qa`,
                cc.notes,tlh.`is_qa_in_progress`,c.eg_campaign_id  AS campaign_id
                FROM lead_history tlh
                JOIN campaigns c ON tlh.campaign_id = c.id JOIN campaign_contacts cc on cc.id = tlh.campaign_contact_id where tlh.id = ?";

        $query = $this->db->query($sql, array($id));
        $result = $query->result();

        if (!empty($result)) {
        return $result[0];
        } else {
            return $result;
    }
    }

    function copy_parent_lead_to_child($data)
    {
        //GET parent lead
        $result = $this->db2->insert_batch($this->table, $data);
        if(!$result){
            $error = 'ERROR inserting approve lead: '. $this->db2->last_query();
            return $error;
    }
        return;
    }

    function get_agent_lead_id($id)
    {
        $sql = "Select rv.agent_id from lead_history rv  where rv.id = ?";
        $query = $this->db->query($sql,array($id));
        return $query->result();
    }

    function get_notes($member_id)
    {
        $sql = "SELECT ca.eg_campaign_id,tn.note,tn.created_at,u.first_name,u.last_name
                FROM notes tn
                JOIN users u ON tn.user_id=u.id 
                -- JOIN lead_history lh ON lh.id=tn.lead_history_id
                JOIN campaigns ca ON tn.campaign_id=ca.id
                WHERE tn.member_id = ? ORDER BY tn.created_at DESC";

        $query = $this->db->query($sql,array($member_id));
        $result = $query->result();
        return $result;
    }
    
    function get_notes_by_lead_history_id($lead_history_id)
    {
        $sql = "SELECT ca.eg_campaign_id,tn.note,tn.created_at,u.first_name,u.last_name
                FROM notes tn
                JOIN users u ON tn.user_id=u.id JOIN lead_history lh ON lh.id=tn.lead_history_id
                JOIN campaigns ca ON lh.campaign_id=ca.id
                WHERE lh.id = ? ORDER BY tn.created_at DESC";

        $query = $this->db->query($sql,array($lead_history_id));
        $result = $query->result();
        return $result;
    }

    function get_list_by_ids($resource_ids)
    {
        $query = $this->db2->query("SELECT r.*,c.name as company_name,c.seo_url_name as seo_url_name,c.logo as company_logo,concat('/resources/',r.id,'/',r.seo_url_name) as url FROM resources r inner join ".$this->company_table." c on r.company_id = c.id where r.id in ({$resource_ids})");
		
        if(!$query){
            return;
        }
        return $query->result();
    }

    function get_resource_by_id($id)
    {
        $sql = 'SELECT r.*,c.name as company_name FROM resources r inner join '.$this->company_table.' c on r.company_id = c.id where r.id = ? ';
        $query = $this->db2->query($sql,array($id));

        $result = $query->result();
        if (!empty($result)) {
            return $result[0];
        } else {
            return $result;
        }
    }

    function get_top_resources($campaign_id,$number=1,$exclude_campaign_id=null,$campaign_resources){

        $exclude_campaign_id_filter = ($exclude_campaign_id != null) ? ' AND r.id != '.$exclude_campaign_id : '';

        $sql = 'SELECT distinct res.name, res.file from (SELECT name,file,count(*),is_downloaded from resource_views rv JOIN resources r ON resource_id=r.id WHERE r.id in ('.$campaign_resources.') and rv.campaign_id='.$campaign_id.$exclude_campaign_id_filter.' GROUP BY resource_id, is_downloaded ORDER BY is_downloaded desc ,count(*) desc) AS res limit '.$number;
        $query = $this->db2->query($sql);
        $top_resources = $query->result();

        if(count($top_resources) != $number) {
            $sql = 'SELECT distinct r.name, r.file FROM (SELECT distinct res.name, res.file from (SELECT name,file,count(*),is_downloaded from resource_views rv JOIN resources r ON resource_id=r.id WHERE r.id in ('.$campaign_resources.') and rv.campaign_id='.$campaign_id.$exclude_campaign_id_filter.' GROUP BY resource_id, is_downloaded ORDER BY is_downloaded desc ,count(*) desc) AS res UNION ALL (SELECT name, file from resources as r where id in ('.$campaign_resources.')'.$exclude_campaign_id_filter.')) AS r limit '.$number;
            $query = $this->db2->query($sql);
            $top_resources = $query->result();
        }

        return $top_resources;
    }
    
    function GetResourcesByEgcampaignID($egcampignID){
        $sql = "SELECT r.*,CONCAT('/resources/',r.id,'/',r.seo_url_name) AS url,c.company FROM resources r LEFT JOIN campaigns c ON c.id = r.campaign_id  WHERE c.id = ?";
        $query = $this->db2->query($sql,array($egcampignID));
        $resources = $query->result_array();
        return $resources;
    }

    /**
     * @param $userType
     * @param $last_call_history_id
     */
    public function check_call_history_exist($userType, $last_call_history_id,$callsModel)
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
                        $callsModel->updateAgentCallHistory($callHistoryDetail);
                    }
                }else{
                $check_call_history_exist = $callsModel->get_call_history_data($last_call_history_id);
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
                    $callsModel->updateAgentCallHistory($callHistoryDetail);
                    // update contact id for multiple call after submit lead with add as a diff. person and update based on campaign & logged user
                        //if(isset($_POST['new_added_contact_id']) && $_POST['new_added_contact_id'] != '') {
                            //$callsModel->update_contact_id_by_new_diff_person($_POST['new_added_contact_id'], $last_call_history_id);
                        //}
                    }
}
            }
        }
    }
    
    function check_tm_lead_history($member_id,$campaign_id) {
        $sql = "SELECT * FROM tm_lead_history WHERE member_id = ? AND campaign_id = ?";
        $query = $this->db2->query($sql,array($member_id,$campaign_id));
        $tm_lead_history = $query->result_array();
        return $tm_lead_history;
    }
    
    function delete_tm_notes($eg_tm_lh) {
        $this->db2->where('lead_history_id', $eg_tm_lh);
        $this->db2->delete('tm_notes');
    }
    
    function update_agent_submitted_lead_status($id, $rv_id) {
        try {
            $query1 = $this->db->query("SELECT `status` FROM lead_history WHERE id = ".$id);
            $result1 = $query1->result();
            return $this->db2->query("UPDATE agent_submitted_leads SET resource_views_id = " . $rv_id . ", `status` = '" . $result1[0]->status . "' WHERE uber_lead_history_id = " . $id);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
}

class Resourceview
{

    public $id = '';
    public $site_id;
    public $member_id;
    public $resource_id;
    public $is_downloaded;
    public $source;
    public $ip;
    public $created_at;
    public $updated_at;
    public $campaign_id;
    public $qualified;
    public $report_display;
}

?>