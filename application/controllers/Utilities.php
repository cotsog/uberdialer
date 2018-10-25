<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Utilities extends MY_Controller {

    // set Default Breadcrumb
    public $crumbs = '<a href="#">Utilities</a>';
    public $dataresearch_crumbs = '<a href="#">Tools</a>';
    public $bucket = 'uberdialer';
    public $recs_per_page = 10;

    public function __construct() {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            //Admin User Not logged in
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }        
        $this->load->library(array('form_validation','session')); // load form validation library & session library
        $this->load->helper(array('url','html','form','utils','common'));
        
    }

    public function retrieve_recording() {
        $user_type = $this->session->userdata('user_type');
        // To check Authorised User OR not with the help of helper Function
        $isAuthorized = IsTLManagerQAUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/users/profile');
        }

        if (!empty($_POST)) {
            $this->load->model('Utilities_model'); 
            $utilities_model = new Utilities_model();
            //if post campaign_contact_id is not empty
            $allowedusers = array('team_leader','qa','manager');
            if(!empty($_POST['campaign_contact_id'])){
                //search call history  (and campaingns table for the eg_campaign_id) directly
                $campaign_contact_id = (int) $_POST['campaign_contact_id'];
                if(in_array($user_type, $allowedusers)){
                        $call_history_records = $utilities_model->get_call_history_data_agent_name($campaign_contact_id); 
                }else{
                        $call_history_records = $utilities_model->get_call_history_data($campaign_contact_id);
                }
            }else if(!empty($_POST['email'])){
                //search contacts, get contact id then search call_history (and campaingns table for the eg_campaign_id) table using contact_id
                $email = addslashes($_POST['email']);
                $this->load->model('Contacts_model');
                $contacts_model = new Contacts_model();
                $contact_detail = $contacts_model->EmailContactDetails($email,null,'member_id');
                if(empty($contact_detail)){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Email does not exist.');
                    redirect('/utilities/retrieve_recording');
                }else{
                    if(in_array($user_type, $allowedusers)){
                        $call_history_records = $utilities_model->get_call_history_data_agent_name($contact_detail['member_id'],'member_id'); 
                    }else{
                        $call_history_records = $utilities_model->get_call_history_data($contact_detail['member_id'],'member_id'); 
                    }
                     
                }
            }else if(!empty($_POST['phone'])){
                //search call history  (and campaingns table for the eg_campaign_id) directly
                $phone = (int) $_POST['phone'];
                if(in_array($user_type, $allowedusers)){
                        $call_history_records = $utilities_model->get_call_history_data_agent_name($phone,'ch.number_dialed'); 
                }else{
                        $call_history_records = $utilities_model->get_call_history_data($phone,'ch.number_dialed');
                }
                
            }else{
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please enter either Email or campaign_contact_id.');
                redirect('/utilities/retrieve_recording');
            }
            $data['call_history_records'] = $call_history_records;
        }
       
        $data['meta_title'] = 'Utilities';
        $data['title'] = 'Retrieve Recording';
        $data['crumbs'] = $this->crumbs . ' >  Tools';
        $data['main'] = 'utilities/retrieve_recording';     
        $this->load->vars($data);
        $this->load->view('layout');
    }

    function retrieve_call_recording($call_uuid, $plivo_id, $confSid = ""){
        $plivoRetrieved = false;
        $params = array('call_uuid' => !empty($confSid) ? $confSid : $call_uuid);

        $result = $this->request('GET', '/Recording/', $params);
        if(!empty($result['response']['objects'][0]->recording_url)){
            $recording = $result['response']['objects'][0]->recording_url;
            //update voip_communications table with recording_url
            $this->load->model('Utilities_model');
            $utilities_model = new Utilities_model();
            $utilities_model->update_plivo_recording_url_by_id($plivo_id, $recording);
            $return = array('recording' => $recording);
            echo json_encode($return);
            $plivoRetrieved = true;
        }else{
            $params = array('call_uuid' => $call_uuid);
            $result = $this->request('GET', '/Recording/', $params);
            if(!empty($result['response']['objects'][0]->recording_url)){
                $recording = $result['response']['objects'][0]->recording_url;
                //update voip_communications table with recording_url
                $this->load->model('Utilities_model'); 
                $utilities_model = new Utilities_model();
                $utilities_model->update_plivo_recording_url_by_id($plivo_id, $recording);
                $return = array('recording' => $recording);
                echo json_encode($return);
                $plivoRetrieved = true;
            }
        }
        
        if(!$plivoRetrieved){

            $result = $this->twilio_request('GET', '/2010-04-01/Accounts/AC16b9cbc56f8dfe874f68292bf84d834c/Calls/'.$call_uuid.'.json');

        //echo "<pre>",print_r($result), "</pre>";

        if(isset($result['response']['subresource_uris']->recordings)){
            $date = new DateTime($result['response']['start_time'], new DateTimeZone('UTC'));
            $start= $date->format('Y-m-d H:i:s');
            $date = new DateTime($result['response']['end_time'], new DateTimeZone('UTC'));
            $end = $date->format('Y-m-d H:i:s');

            $rec_result = $this->twilio_request('GET', $result['response']['subresource_uris']->recordings);

            if(isset($rec_result['response']['recordings'][0]->sid)){
                $recording=  "https://api.twilio.com/2010-04-01/Accounts/AC16b9cbc56f8dfe874f68292bf84d834c/Recordings/{$rec_result['response']['recordings'][0]->sid}";
                $duration = $result['response']['duration'];
                $hangup_cause = $result['response']['status'];

                    //update voip_communications table with recording_url
                    $this->load->model('Utilities_model'); 
                    $utilities_model = new Utilities_model();
                    $count_flag = ($duration >= 15) ? 1 : 0;
                    $recordata = array(
                        'call_start_datetime'=>$start,
                        'call_end_datetime'=>$end,
                        'recording_url' => $recording,
                        'duration'=>$duration,
                        'hangup_cause'=>$hangup_cause,
                        'count_flag'=>$count_flag,
                        'retrieved' => '1');
                    $utilities_model->update_plivo_recording_url_by_id($plivo_id, $recordata,'twilio');
                    $return = array('recording' => $recording);
                    echo json_encode($return);
                }else{
                    echo json_encode(array('error'=>'no recording found.'));
                }
            }else{
                echo json_encode(array('error'=>'no recording found.'));
            }
        }exit;
    }

    function twilio_request($method = "GET", $path, $vars = array())
    {

        $encoded = "";
        foreach ($vars AS $key => $value)
            $encoded .= "$key=" . urlencode($value) . "&";
        $encoded = substr($encoded, 0, -1);

        /*
         * Create the full URL
         */
        $auth_id = 'AC16b9cbc56f8dfe874f68292bf84d834c';
        $auth_token = '17761abc75fc40504487391eaaf3c554';
        $api = "https://api.twilio.com";
        $url = $api.rtrim($path, '/').'/';
        //$url = $this->api . '/' . $path;

        /*
         * if GET and vars, append them
         */
        if ($method == "GET")
            $url .= (FALSE === strpos($path, '?') ? "?" : "&") . $encoded;

        /*
         * initialize a new curl object
         */
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));

        switch (strtoupper($method))
        {
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                curl_setopt($curl, CURLOPT_HTTPGET, json_encode($vars));
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($vars));
                break;
        }
        
        // send credentials
        curl_setopt($curl, CURLOPT_USERPWD, $pwd = "{$auth_id}:{$auth_token}");

        // initiate the request
        $json_response = curl_exec($curl);
        $responseData = json_decode($json_response);
        // get result code
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //return array($response_code, $json_response);
        return array("status" => $response_code, "response" => (array)$responseData);
    }

    function request($method = "GET", $path, $vars = array())
    {

        $encoded = "";
        foreach ($vars AS $key => $value)
            $encoded .= "$key=" . urlencode($value) . "&";
        $encoded = substr($encoded, 0, -1);

        /*
         * Create the full URL
         */
        $auth_id = 'MAN2YZYTBMYJCZMTFIYZ';
        $auth_token = 'MDJiM2MwY2Y3NTk0YzJiMTg5YTlmMzVhOWY2MWMx';
        $api = "https://api.plivo.com/v1/Account/" . $auth_id;
        $url = $api.rtrim($path, '/').'/';
        //$url = $this->api . '/' . $path;

        /*
         * if GET and vars, append them
         */
        if ($method == "GET")
            $url .= (FALSE === strpos($path, '?') ? "?" : "&") . $encoded;

        /*
         * initialize a new curl object
         */
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        switch (strtoupper($method))
        {
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                curl_setopt($curl, CURLOPT_HTTPGET, json_encode($vars));
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($vars));
                break;
        }

        // send credentials
        curl_setopt($curl, CURLOPT_USERPWD, $pwd = "{$auth_id}:{$auth_token}");

        // initiate the request
        $json_response = curl_exec($curl);
        $responseData = json_decode($json_response);
        // get result code
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //return array($response_code, $json_response);
        return array("status" => $response_code, "response" => (array)$responseData);
    }
    
    public function voipRelogin(){
        $relog = $this->config->item('webrtc_url') . $this->session->userdata('uid');
        $data['meta_title'] = 'Utilities';
        $data['title'] = 'Re-login to VOIP';
        $data['crumbs'] = $this->crumbs . ' >  Tools';
        $data['main'] = 'utilities/relogin';     
        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function sites()
    {
        // load offices model
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        $offices = $officesModel->get_all('is_active = 1','id,name,parent_id');
        
        $parentOffices = array();
        $subOffices = array();
        foreach($offices as $value){
            if(!empty($value['parent_id'])){
                $subOffices[$value['parent_id']][] = $value;
            }else{
                $parentOffices[] = $value;
            }
        }

        $data['tm_offices'] = $parentOffices;
        $data['subOffices'] = $subOffices;

        $data['meta_title'] = 'Sites';
        $data['title'] = 'Sites';
        $data['main'] = 'utilities/sites';
        $data['user_id'] = $this->session->userdata('uid');
        $data['logged_user_type'] = $this->session->userdata('user_type');
        $data['crumbs'] = $this->crumbs . ' > Sites';

        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function createSite()
    {
        // load offices model
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        //Load Helpers
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('utils');

        //Set Validation Rules      
        $this->form_validation->set_rules('name', 'Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            //get site ids
            $siteIds = $officesModel->get_all("is_active = 1 AND (parent_id is null OR parent_id = 0)");
            
            $data['siteIds'] = $siteIds;
            $data['meta_title'] = 'Create Site';
            $data['title'] = 'Create Site';
            $data['main'] = 'utilities/create_site';
            $data['user_id'] = $this->session->userdata('uid');
            $data['logged_user_type'] = $this->session->userdata('user_type');
            $data['crumbs'] = $this->crumbs . ' > <a href="/utilities/sites">Sites</a> > Create TM Site';

            $this->load->vars($data);
            $this->load->view('layout');
        } else {

            $name = $this->input->post('name');

            $site = $officesModel->getByName($name);

            if ( !empty($site[0]) ) {

                if ( $site[0]['is_active'] == '0' ) {
                    $office = array(
                        'is_active' => 1
                        );

                    $createSite = $officesModel->update($site[0]['id'], $office);
                } else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'TM Site already exists.');
                    redirect('/utilities/createsite');
                    exit;
                }
            } else {

                $office = array(
                    'name' => $name, 
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'created_by' => $this->session->userdata('uid'),
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'updated_by' => $this->session->userdata('uid'),
                    'parent_id' => $_POST['siteId']
                    );

                $createSite = $officesModel->create($office);
            }

            if ( $createSite ) {
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'TM Office was successfully created.');
                redirect('/utilities/sites');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'An error occured. Please contact the devteam.');
                redirect('/utilities/sites');
            }
        }

        
    }

    public function editSite($id)
    {
        // load offices model
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        // load users model
        $this->load->model('Users_model');
        $usersModel = new Users_model();

        // load campaigns model
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();

        //Load Helpers
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('utils');

        $tmOffice = $officesModel->get($id);

        if (empty($tmOffice)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'TM Site does not exist.');
            redirect('/utilities/sites');
            exit;
        }

        //Set Validation Rules      
        $this->form_validation->set_rules('name', 'Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            //get site ids
            $siteIds = $officesModel->get_all("id <> {$id} AND (parent_id is null OR parent_id = 0) AND is_active = 1");
            
            $data['siteIds'] = $siteIds;
            $data['id'] = $id;
            $data['tm_office'] = $tmOffice;
            $data['meta_title'] = 'Edit Site';
            $data['title'] = 'Edit Site';
            $data['main'] = 'utilities/edit_site';
            $data['user_id'] = $this->session->userdata('uid');
            $data['logged_user_type'] = $this->session->userdata('user_type');
            $data['crumbs'] = $this->crumbs . ' > <a href="/utilities/sites">Sites</a> > Edit TM Site';

            $this->load->vars($data);
            $this->load->view('layout');
        } else {

            $name = $this->input->post('name');

            $site = $officesModel->getByName($name);

            if ( !empty($site[0]) && $site[0]['id'] != $id) {

                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'TM Site already exists.');
                redirect('/utilities/sites');
                exit;
            }else {
                //check if parent site selected is a child, if it is a child, it should not be assigned to the site as parent
                $tmOfficeChecking = $officesModel->get($_POST['siteId']);
                if(!empty($tmOfficeChecking[0]['parent_id'])){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'TM Site cannot be assigned as a parent site; it is currently a sub site.');
                    redirect('/utilities/editSite/'.$id);
                    exit;
                }else{
                    //check if site is parent, if yes then it should not be assigned to another site as subsite.
                    $checkIfParent = $officesModel->getByFilter("parent_id = {$id}");
                    if(!empty($checkIfParent)){
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'TM Site cannot be assigned as a sub site; it is currently a parent site.');
                        redirect('/utilities/editSite/'.$id);
                        exit;
                    }
                }
            }

            $office = array(
                    'name' => $name, 
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'updated_by' => $this->session->userdata('uid'),
                    'parent_id' => $_POST['siteId']
                    );

            if ( $officesModel->update($id,$office) ) {

                $data = array('tm_office' => $name);
                $campaignsModel->update_by_office($tmOffice[0]['name'],$data);

                $data = array('telemarketing_offices' => $name);
                $usersModel->update_by_office($tmOffice[0]['name'],$data);

                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'TM Office was successfully updated.');
                redirect('/utilities/sites');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'An error occured. Please contact the devteam.');
                redirect('/utilities/sites');
            }
        }
    }

    public function removeSite($id)
    {
        // load offices model
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        if ( $office = $officesModel->get($id) ) {

            $checkAssigned = $officesModel->getCampaignsAndUsersBySite($office[0]['name']);

            if( !empty($checkAssigned[0]['id']) ) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Failed! There are still active campaigns or users assigned to the selected site.');
                redirect('/utilities/sites');
                exit;
            }

            $officesModel->remove($id);

            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'TM Site was successfully removed.');
            redirect('/utilities/sites');
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'An error occured. Please contact the devteam.');
            redirect('/utilities/sites');
        }
    }

    public function normalizationtool()
    {
        if (!empty($_POST)) {

            $this->load->Model('Members_model');
            $membersModel = new Members_model();

            $normalization_rules = $membersModel->get_member_normalization_rules();

            $this->load->library('Normalize');
            $normalize = new Normalize();

            $data['normalize']['job_level'] = $normalize->job_level($_POST['job_title'], $normalization_rules);
            $data['normalize']['silo'] = $normalize->silo($_POST['job_title'], $normalization_rules);
        }
        
        $data['meta_title'] = 'Utilities';
        $data['title'] = 'Normalization Tool';
        $data['crumbs'] = $this->crumbs . ' >  Tools';
        $data['main'] = 'utilities/normalizationtool';     
        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    public function emailChangeLookup(){
        $user_type = $this->session->userdata('user_type');
        
        $searchResult = array();
        if (!empty($_POST)) {
            if (!empty($_POST['email']) && !empty($_POST['campaign_id'])) {
                $email = addslashes(trim($_POST['email']));
                $campaign_id = (int) $_POST['campaign_id'];
                $this->load->model('Utilities_model'); 
                $utilities_model = new Utilities_model();
                $campaigns_ids = $utilities_model->getAssignedCampaigns();
                $campaignAssigned = false;
                if($user_type != 'agent' || ($user_type == 'agent' && in_array($_POST['campaign_id'], $campaigns_ids))){
                    $campaignAssigned = true;
                }
                if($campaignAssigned){
                    $searchResult = $utilities_model->searchOriginalEmail($_POST['email'],$_POST['campaign_id']);
                }else{
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Campaign is not assigned to agent');
                    $result = '?campaign_id='.$_POST['campaign_id'].'&email='.$_POST['email'];
                    redirect('/utilities/emailChangeLookup'.$result);
                }
                
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please enter both Email and Campaign ID.');
                $request = "";
                if(!empty($_POST['campaign_id'])){
                    $result = '?campaign_id='.$_POST['campaign_id'];
                }else if(!empty($_POST['email'])){
                    $result = '?email='.$_POST['email'];
                }
                redirect('/utilities/emailChangeLookup'.$result);
            }
        }
       // echo "<pre>", print_r($searchResult);exit;
        $data['searchResult'] = $searchResult;
        
        $data['meta_title'] = 'Utilities';
        $data['title'] = 'Email has changed Lookup for TM';
        $data['crumbs'] = $this->crumbs . ' >  Tools';
        $data['main'] = 'utilities/email_change_lookup';     
        $this->load->vars($data);
        $this->load->view('layout');
            
    }
    public function emailChangeLookup2() {
        $user_type = $this->session->userdata('user_type');
        // To check Authorised User OR not with the help of helper Function
        $isAuthorized = IsAdminTLManagerAgentAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/users/profile');
        }
        
        if (!empty($_POST)) {
            $this->load->model('Utilities_model'); 
            $utilities_model = new Utilities_model();
            //if post campaign_contact_id is not empty
            $searchResult = array();
            if(!empty($_POST['email']) && !empty($_POST['campaign_id'])){
                //search contacts, get contact id then search call_history (and campaingns table for the eg_campaign_id) table using contact_id
                $email = addslashes($_POST['email']);
                $campaign_id = (int) $_POST['campaign_id'];
                $uberCampaignId = $utilities_model->getCampaignId($campaign_id);
                if(empty($uberCampaignId)){
                    $searchResult = array();
                }else{
                    $uberCampaignId = $uberCampaignId['id'];
                    $contactDetail = $utilities_model->checkEmailChangeHistory($email);
                    $emailTo = "";
                    if(empty($contactDetail)){
                        $searchResult = array();
                    }

                    if(!empty($contactDetail)){
                        #if contactDetail return only one result, its email_from
                        #get other contacts for the same campaign
                        if(count($contactDetail) == 1 && $contactDetail[0]['email_change'] == 'from'){
                            $contactDetail = $utilities_model->checkEmailChangeHistory($contactDetail[0]['email_to']);
                        }else if(count($contactDetail) == 1 && $contactDetail[0]['email_change'] == 'to'){
                            $contactDetail = $utilities_model->checkEmailChangeHistory($contactDetail[0]['email_from']);
                        }
                        $contactEmails = array();
                        #loop through the result to know where to find the contacts
                        foreach($contactDetail as $changeData){
                            $contactEmails[] = $changeData['email_from'];
                            $allEmailsTo[] = $changeData['email_to'];
                            //$emailTo = $changeData['email_to'];
                        }
                        
                        $contactEmails = implode("','", $contactEmails);
                        $filters = "c.email in ('{$contactEmails}') and cc.campaign_id = {$uberCampaignId}";
                        $result = $utilities_model->searchCampaignContact($filters, "c.id,member_id,CONCAT(first_name, ' ', last_name) AS full_name,email,cc.id as campaignContactId,cc.list_id,cc.campaign_id");
                        
                        
                        if(!empty($result)){
                            foreach($result as $data){
                                $searchResult[$data['email']] =  $data;
                            }
                        }
                        
                            if(!isset($searchResult[$email])){
                                $searchResult = $this->findContacts($utilities_model, $email, $uberCampaignId, $searchResult);
                            }


                            foreach($allEmailsTo as $emailChange){
                                if(!isset($searchResult[$emailChange])){
                                    $searchResult = $this->findContacts($utilities_model, $emailChange, $uberCampaignId, $searchResult);
                                }
                            }
                            $hasAtleastOneToOpen = false;
                            foreach($searchResult as $emailsHistory){
                                if(!empty($emailsHistory['campaignContactId'])){
                                    $hasAtleastOneToOpen = true;
                                }
                            }
                            if(!$hasAtleastOneToOpen){
                                $searchResult = array();
                            }
//                        echo "<pre>",print_r($searchResult), "</pre>";
//                        echo "<pre>",print_r($this->db->queries), "</pre>";exit;
                    }
                }
            }else{
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please enter both Email and Campaign ID.');
                $request = "";
                if(!empty($_POST['campaign_id'])){
                    $result = '?campaign_id='.$_POST['campaign_id'];
                }else if(!empty($_POST['email'])){
                    $result = '?email='.$_POST['email'];
                }
                redirect('/utilities/emailChangeLookup'.$result);
            }
            $data['searchResult'] = $searchResult;
        }
       
        $data['meta_title'] = 'Utilities';
        $data['title'] = 'Email has changed Lookup for TM';
        $data['crumbs'] = $this->crumbs . ' >  Tools';
        $data['main'] = 'utilities/email_change_lookup';     
        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    function findContacts($utilities_model,$email,$uberCampaignId,$searchResult){
        #search contacts
        $filters = "c.email = '{$email}' and cc.campaign_id = {$uberCampaignId}";
        $searchContact = $utilities_model->searchCampaignContact($filters, "c.id,member_id,CONCAT(first_name, ' ', last_name) AS full_name,email,cc.id as campaignContactId,cc.list_id,cc.campaign_id");
        if(!empty($searchContact)){
            $searchResult[$searchContact[0]['email']] =  $searchContact[0];
        }else{
            #search members_qa
            $filters = "email = '{$email}'";
            $searchEmailHistory = $utilities_model->searchMembersQa($filters, "id as member_id,CONCAT(first_name, ' ', last_name) AS full_name,email");
            if(!empty($searchEmailHistory)){
                $searchEmailHistory = array_merge($searchEmailHistory[0], array('campaignContactId' =>'','list_id' =>'','campaign_id' =>''));
                $searchResult[$searchEmailHistory['email']] = $searchEmailHistory;
            }
        }
        return $searchResult;
    }

}
?>
