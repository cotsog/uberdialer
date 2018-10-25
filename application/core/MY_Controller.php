<?php

class MY_Controller extends CI_Controller {

    public $site_id = 1; //default
    public $plivo_switch = 0;
    public $site_name = "Uber Dialer";
    public $template = 'layout'; //default
    public $uid = 0;
    public $custom_session_data = array();
    public $user_id;
    protected $app_config;
    public $db2;
    public $app ='eg';
    public $fileAppend ='';
    public $dbconfig = 'db2';
    public $uri_route;
    public $app_logo = '';
    public $business_name = 'Enterprise Guide';
    public $path = 'https://s3.amazonaws.com/uberdialer';
    public $cache_buster = '?v1.183';
    public $app_access_list = "";
    public $app_module_name ='dialer';
    public $selected_module_name = '';
    public $isConference = 1;
    public $isConferenceOffices = array('Survey Team Cebu');
    #EU Campaigns
    #1155,1159,1167,1215,1231,1276,1281
    public $isUploadTransferCampaign = array(1);
    //public $isConferenceOffices = array();

    function __construct() {
	
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        parent::__construct();
        
        $isApiCall = $this->input->post('is_api');
        if (empty($isApiCall)) {
            // check if the remote address is the app server IP,
            if($_SERVER['REMOTE_ADDR'] != '104.130.41.52' && $_SERVER['REMOTE_ADDR'] != '172.24.16.52' && $this->uri->segment(1) != 'email') {
                // check if sso auth is enabled
                if($this->config->item('sso_is_enabled')) {
                    //We need to verify 2 types of additional session variables:
                    //1) Auth related
                    $this->validate_auth();
                    //2) Member related
                    $this->check_user_session();
                }
            }
        }
        $uri = uri_string();     

        if($this->uri->segment(1) == 'mpg'){
            $this->business_name = 'Medical Product Guide';
            $this->app = $this->uri->segment(1);
            $this->dbconfig = $this->app;
            $this->fileAppend = '_' . $this->app;
            $uri_parts = explode('/', $uri);
            $this->app_logo = '<img src="/images/mdg.png" />';
            foreach($uri_parts as $idx => $uri_name){
                if($uri_name == 'mpg'){
                    unset($uri_parts[$idx]);
                }
            }
            $this->uri_route = '/'. implode('/', $uri_parts);
        }else{
            $this->uri_route = "/mpg/".uri_string();
        }

        $switch = $this->input->get('switch');
        // check if the previous url contain 'mpg' in the url, if so then redirect this to /mpg/uri
        if(strpos($uri, 'mpg') === false && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'mpg') !== false && !$switch && $uri != 'login' && $uri != 'logout'){
            $uri_ref = $_SERVER['HTTP_REFERER'];
            $uri_ref_parts =  explode('/', $uri_ref);
           
            $this->business_name = 'Medical Product Guide';
            $this->app = $uri_ref_parts[3];
            $this->dbconfig = $this->app;
            $this->fileAppend = '_' . $this->app;
        }
        
        $this->db2 = $this->load->database($this->dbconfig, TRUE);
        
        $this->path = (ENVIRONMENT == 'production') ? $this->path : "";
        
        //We need to verify 2 types of additional session variables:
        //2) Member related
        //write custom session data if any
        if(!empty($this->custom_session_data))
        {
            $this->session->set_userdata($this->custom_session_data);
            $this->custom_session_data = null;
        }
        $loggedUserID = $this->session->userdata('uid');
        $telemarketing_offices = $this->session->userdata('telemarketing_offices');
        if(!$telemarketing_offices){
            // Load users model
            $this->load->model('Users_model');
            $Users_model = new Users_model();
            $user = $Users_model->get_one($loggedUserID);
            if(isset($user->telemarketing_offices)){
                $this->session->set_userdata('user_type', $user->user_type);
                $this->session->set_userdata('telemarketing_offices', $user->telemarketing_offices);
                $this->session->set_userdata('module', explode(',',$user->module));
            }
        }
        $session_module = $this->session->userdata('module');
        //sanity: checking logged user have permission for access current URL module
        if($this->uri->segment(1) == 'dialer' && !empty($session_module)){
            if(!in_array('tm',$session_module) && $this->session->userdata('user_type') != 'admin'){
                $this->session->set_flashdata('msg', 'You can not access this module as you have not permission');
                redirect('/users/profile');
            }
        }
        
        // Common module set as a global variable
        if($this->app_module_name == 'dialer'){
            $this->selected_module_name = "'tm'";
            $this->app_module_type = "tm";
        }
        if(!empty($loggedUserID)){
        
            $plivo_endpoint_username = $this->session->userdata('plivo_endpoint_username');
            $plivo_endpoint_password = $this->session->userdata('plivo_endpoint_password');

            if(empty($plivo_endpoint_username) && empty($plivo_endpoint_password)){
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );  
                $json = file_get_contents($this->config->item('webrtc_url').$loggedUserID, false, stream_context_create($arrContextOptions)); // this will require php.ini to be setup to allow fopen over URLs
                $webRTC_login_agent_data = json_decode($json);
                if(!empty($webRTC_login_agent_data)){
                    $this->session->set_userdata('plivo_endpoint_username', $webRTC_login_agent_data->username);
                    $this->session->set_userdata('plivo_endpoint_password', $webRTC_login_agent_data->password);
                }
            }
        }

        session_write_close();

        $server_uri = $_SERVER['REQUEST_URI'];

        if($this->session->userdata('user_type') == 'dataresearch_user' &&
            ((strpos($server_uri, "datateam") == false) && (strpos($server_uri, "agentStartCallDial") == false))
            && (strpos($server_uri, "changepassword") == false) && (strpos($server_uri, "agentEndCallDial") == false)
        && (strpos($server_uri, "logout") == false) && (strpos($server_uri, "updateProfile") == false) && (strpos($server_uri, "login") == false) && (strpos($server_uri, "profile") == false))
        {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/datateam');
        }

        if(!empty($_POST) ){
            if($this->session->userdata('user_readonly')){
                
                if((strpos($server_uri, "registration") !== false) || 
                        (strpos($server_uri, "edit") !== false) ||
                        (strpos($server_uri, "delete") !== false) ||
                        (strpos($server_uri, "add") !== false) ||
                        (strpos($server_uri, "create") !== false)
                        || (strpos($server_uri, "agentStartCallDial") !== false)    
                        || (strpos($server_uri, "lockUnlockContact") !== false)  
                        || (strpos($server_uri, "saveContactCallDetail") !== false)  
                        || (strpos($server_uri, "assigncampaigns") !== false)
                        ){
                    if((strpos($server_uri, "saveContactCallDetail") !== false)|| (strpos($server_uri, "agentStartCallDial") !== false)    
                        || (strpos($server_uri, "lockUnlockContact") !== false) || (strpos($server_uri, "delete") !== false)){
                        $data['message'] = "Sorry, Your account may not be allowed to perform this action.";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                    else{
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Your account may not be allowed to perform this action.');
                        redirect($server_uri); 
                    }
                }
            }
        }

       
//        if ($this->session->userdata('uid') && $_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '/users/changepassword' && $_SERVER['REQUEST_URI'] != '/login' && $_SERVER['REQUEST_URI'] != '/logout') {
//            if($this->session->userdata('last_reset') === NULL || $this->session->userdata('last_reset') ==false){
//                $this->session->set_flashdata('class', 'bad');
//                        $this->session->set_flashdata('msg', 'Please change your password to continue');
//                redirect('users/changepassword');
//            }
//        }else 
            if($this->session->userdata('uid') && ($_SERVER['REQUEST_URI'] == '/') || $_SERVER['REQUEST_URI'] == ''){
                if($this->session->userdata('user_type') == 'qa'){
                    redirect('dialer/leads/');
                }else {
                    redirect('users/profile');
                }
            }

        $this->user_id = $this->session->userdata('uid');
        
        $this->app_config = $this->config->item('app');
        $this->load->driver('cache');
    }

    
    function check_user_session()
    {
        $this->load->helper('utils_helper');
        //persistent cookie exists. "log in" user by creating new session
        $this->load->model('Users_model');
        $adminuser_model = new Users_model();
                
        //Check if there's a user session already
        if (!$this->session->userdata('uid'))
        {
            $ajax_request = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
            if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '/login' && $_SERVER['REQUEST_URI'] != '/logout' && $this->uri->segment(1) != 'password' && $this->uri->segment(1) != 'passwordreset' && !$ajax_request) {

                $this->session->set_userdata('target_url', $_SERVER['REQUEST_URI']);              
                
            }

            //No session yet. Try to set Member session variables
            //We need to check for persistent cookie
            //If persistent cookie exists we need to start a new session for this user
            //This situation should only happen on first page load for users who previously
            //selected Remember Me option when logging in.
            if (get_cookie('PIAuth'))
            {
                $piauth_cookie = _decode_auth_cookie(get_cookie('PIAuth'));
             
                $piauth = explode("||", $piauth_cookie);

                $piauth_email = $piauth[0];
                $piauth_token = $piauth[1];

                $obj = $adminuser_model->get_details_by_email($piauth_email);

                if(!empty($obj) && $obj->status == 'Active'){
                    $this->_set_user_session($obj,$adminuser_model);
                    $this->app_access_list = $this->_get_app_access_list($piauth_email);

                    // save user session
                    $user_session = $adminuser_model->check_user_session($this->session->userdata('uid'));

                    if($user_session) {
                        $user_session_update = array(
                                'is_session_active' => 0,
                            );
                        //update and deactivate last session
                        $adminuser_model->update_user_session($this->session->userdata('uid'), $user_session_update);

                    } 
                    
                    //insert new session
                    $user_session_insert = array(
                            'created_at' =>  date('Y-m-d H:i:s',time()),
                            'last_activity' => date('Y-m-d H:i:s',time()),
                            'is_session_active' => 1,
                            'user_id' => $this->session->userdata('uid')

                        );
                    $adminuser_model->insert_user_session($this->session->userdata('uid'), $user_session_insert);
                    

                    if ($this->session->userdata('user_type') == 'qa') {
                           /* if ($target_url != '') {
                                redirect($target_url);
                            }else{*/
                            redirect('dialer/leads/');
                            //}

                    } else if ($this->session->userdata('user_type') == 'dataresearch_user') {
                            redirect('dialer/datateam/');
                    } else {
                            //check active session
                            $this->load->model('Campaigns_model');
                            $campaignsModel = new Campaigns_model();

                            $previous_session_active_data = $campaignsModel->check_agent_session($this->session->userdata('uid'));
                        if (!empty($previous_session_active_data)) {
                            $agentData = array();
                            if (empty($previous_session_active_data->session_end)) {

                                    $create_previous_session_end_date = strtotime('+2 minutes', strtotime($previous_session_active_data->session_start));
                                    $agentData['session_end'] = date('Y-m-d H:i:s', $create_previous_session_end_date);

                                }
                            $agentData['is_session_deactive'] = 1;
                            $campaignsModel->update_agent_session_by_user($this->session->userdata('uid'), $agentData);

                        }
                           /* if ($target_url != '') {
                                redirect($target_url);
                            }else{*/
                                redirect('user/profile/');
                            //}
                        }
                        
                } else {
                    redirect($this->config->item('sso_url'));
                }
            }
        }
        else
        {
            
            
            if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '/login' && $_SERVER['REQUEST_URI'] != '/logout' && $this->uri->segment(1) != 'password' && $this->uri->segment(1) != 'passwordreset' ) {
                $this->session->set_userdata('target_url', $_SERVER['REQUEST_URI']);
            }
            if(get_cookie('PIAuth')) {
                // persistent cookie exists
                // Check to see if this user is still active
                $piauth_cookie = _decode_auth_cookie(get_cookie('PIAuth'));
                $piauth = explode("||", $piauth_cookie);
                $piauth_email = $piauth[0];
                $piauth_token = $piauth[1];
                $obj = $adminuser_model->get_details_by_email($piauth_email);
                if($obj->status != 'Active') {
                    // not active on this app; redirect them back to the sso_url
                    redirect($this->config->item('sso_url'));
                } else {

                    // save user session
                    $user_session = $adminuser_model->check_user_session($this->session->userdata('uid'));

                    if($user_session) {
                        $user_session_update = array(
                                'last_activity' => date('Y-m-d H:i:s',time()),
                            );
                        //update current session
                        $adminuser_model->update_user_session($this->session->userdata('uid'), $user_session_update);

                    } else {
                        //insert new session
                        $user_session_insert = array(
                                'created_at' =>  date('Y-m-d H:i:s',time()),
                                'last_activity' => date('Y-m-d H:i:s',time()),
                                'is_session_active' => 1,
                                'user_id' => $this->session->userdata('uid')

                            );
                        $adminuser_model->insert_user_session($user_session_insert);
                    }

                    // user is active; set convenience variables
                    $this->uid = $this->session->userdata('uid');
                    $this->app_access_list = $this->_get_app_access_list($piauth_email);
                    $active_session_key =  $this->session->userdata('active_session_key');
                    $logged_user_id = $this->session->userdata('uid');
                    if(isset($active_session_key) && isset($logged_user_id)){

                        $this->check_user_multiple_login_at_once($logged_user_id, $active_session_key);
                    }
                }
            } else {
                // no persistent cookie exists, and user does not have an active session; redirect them back to the sso_url
                redirect($this->config->item('sso_url'));
            }
        }
        
        //Try to set Referral Source
        if(!$this->session->userdata('src'))
        {

            //first try querystring for src variable
            $src = $this->input->get('src', TRUE);
            if($src!=false)
            {
                $this->custom_session_data['src'] = $src;
            }
            else
            {
                if(isset($_SERVER["HTTP_REFERER"]))
                {
                    $src = $_SERVER["HTTP_REFERER"];
                    if(strlen($src>100))
                    {
                        $src=substr($src,0,99);
                    }
                    $this->custom_session_data['src'] = $src;
                }      
            }            
        }
    }

    //------------------------------------------------------------------------>
    // Helper Methods Follow
    //------------------------------------------------------------------------>
    function _set_user_session($member,$member_model)
    {
        // Update logged in user activation key // avoid single user login from multiple location
        $activation_key_update_data = md5(uniqid($member->id, TRUE));
        $user_data = array();
        $user_data['active_session_key'] = $activation_key_update_data;
        $member_model->update_user($member->id, $user_data);
        //Set Custom Session Values
        $this->session->set_userdata('uid', $member->id);
        $this->session->set_userdata('user_fname', $member->first_name);
        $this->session->set_userdata('user_lname', $member->last_name);
        $this->session->set_userdata('user_readonly', $member->is_readonly);
        $this->session->set_userdata('user_email', $member->email);
        $this->session->set_userdata('last_reset', $member->last_reset);
        $this->session->set_userdata('user_type', $member->user_type);
        $this->session->set_userdata('group', $member->User_Group_Name);
        $this->session->set_userdata('project', $member->project);
        $this->session->set_userdata('schedule', $member->schedule);
        $this->session->set_userdata('active_session_key', $activation_key_update_data);
        $this->session->set_userdata('telemarketing_offices', $member->telemarketing_offices);
        $this->session->set_userdata('assigned_campaign_ids', '123');
        $this->session->set_userdata('module', explode(',',$member->module));
        
        if ($member->user_type == 'manager') {
            $this->load->model('Offices_model');
            $officeModel = new Offices_model();
            $subOffice = $officeModel->getSubOffices($member->telemarketing_offices);
            $subOffices = array_column($subOffice,"name");
            $this->session->set_userdata('sub_telemarketing_offices', $subOffices);
        }
        
        if($member->user_type == "agent" || $member->user_type == "team_leader"){
            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();
            $get_assign_campaign_ids = $campaignsModel->getAssignedCampaignIdsOfUser($member->user_type,$member->id);
            $this->session->set_userdata('assigned_campaign_ids', $get_assign_campaign_ids);
        }    
        //update member's last login
        $member_model->update_last_login($member->id);

        //reset persistant cookie for another 30 days
        $this->load->helper('cookie');

        $cookie = array(
            'name' => 'c_uid',
            'value' => $member->id,
            'expire' => 2592000, //30 days                      
        );
        set_cookie($cookie);

        $this->uid = $member->id;
    }
    
    function _get_app_access_list($email)
    {
        $ch = curl_init();

        // append the post data to the base url to pass as a query string
        $query_string = $this->config->item('sso_url').'api/users/get_access_list?email='.$email.'&app=4';

        //set the url; this will actually be our full query string with all params appended
        curl_setopt($ch, CURLOPT_URL, $query_string);

        //Don't need headers returned. set to true if you want http status code
        curl_setopt($ch, CURLOPT_HEADER, false);

        //but want the response returned
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //turn off cert verificaiton
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //execute post
        $response = curl_exec($ch);

        $res = json_decode($response);

        return empty($res->error) ? (!empty($res->result) ? $res->result : "") : "";
    }

    // Clear session after X minutes of user inactivity in application

    function _clear_session_x_minutes()
    {
        $user_logged_in = $this->session->userdata('uid');
        if (isset($user_logged_in)) {

            $idle_time = 1800; //after 60 seconds the user gets logged out //900000

            $user_login_timestamp = $this->session->userdata('user_login_timestamp');
            if(isset($user_login_timestamp)){

                if (time() - $this->session->userdata('user_login_timestamp') > $idle_time) {
                    session_destroy();
                    session_unset();
                    $this->session->set_flashdata('prev_action', 'logout');
                    redirect('/logout');
                } else {
                    $this->session->set_userdata('user_login_timestamp', time());
                    //$_SESSION['user_login_timestamp'] = time();
}
            }

//on session creation
            $this->session->set_userdata('user_login_timestamp', time());
            //$_SESSION['user_login_timestamp'] = time();
        }
    }

    public function check_campaign_active_session($agentSessionCampaignId,$campaign_id){
        $is_ajax_request = $this->input->is_ajax_request();
        if(isset($agentSessionCampaignId) && isset($campaign_id) && ($agentSessionCampaignId != $campaign_id)) {
            if($is_ajax_request)
            {
                $data['message'] = "Campaign not sign in, please make sure that the Campaign is Sign in!";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }else{
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                redirect($this->app_module_name.'/campaigns');
            }
        }else if($agentSessionCampaignId == $campaign_id){
            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();
            $checkforsignout = $campaignsModel->check_agent_signout($this->session->userdata('uid'),$agentSessionCampaignId);
            if(empty($checkforsignout))
            {
                if($is_ajax_request)
                {
                    $data['message'] = "Campaign not sign in, please make sure that the Campaign is Sign in!";
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }else{
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                    redirect($this->app_module_name.'/campaigns');
                }
            }
        }
    }

    /**
     * @param $logged_user_id
     * @param $active_session_key
     */
    public function check_user_multiple_login_at_once($logged_user_id, $active_session_key)
    {
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $logged_user_data = $users_model->get_one($logged_user_id);
        if (!empty($logged_user_data)) {
            if ($logged_user_data->active_session_key != $active_session_key) {

                if ($this->session->userdata('uid')) {
                    if($this->session->userdata('user_type') == 'agent' || $this->session->userdata('user_type') == 'team_leader'){
                        $this->load->model('Campaigns_model');
                        $isAgentSession = $this->Campaigns_model->check_agent_session($this->session->userdata('uid'));
                        if(!empty($isAgentSession)){
                            $agentData = array();
                            $agentData['session_end'] = date('Y-m-d H:i:s',time());
                            $agentData['is_session_deactive'] = 1;
                            $this->Campaigns_model->update_agent_session($isAgentSession->id,$agentData);
                            $this->session->unset_userdata('AgentSessionID');
                            $this->session->unset_userdata('AgentSessionCampaignID');
                        }
                    }
                    $user_data = array('uid' => '', 'user_fname' => '', 'target_url' => '', 'user_email' => '');

                    $user_data = $this->session->all_userdata();

                    foreach ($user_data as $key => $value) {
                        if ($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent') {
                            $this->session->unset_userdata($key);
                        }
                    }
                    //$this->session->set_flashdata('prev_action', 'logout');
                }

                if (!$this->input->is_ajax_request()) {

                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'You are logged-out by administrator. you are login from another location.');
                    redirect('/login');
                }else{

                    $data['message'] = "You are logged-out by administrator. you are login from another location.";
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }
            }
        }
    }
    
    function validate_auth()
    {
        if (!get_cookie('PIAuth') || !get_cookie('PIAuthSession') || get_cookie('PIAuth') != get_cookie('PIAuthSession')) {
            if ($_SERVER['REQUEST_URI'] != '/login' && $_SERVER['REQUEST_URI'] != '/logout' && $this->uri->segment(1) != 'password' && $this->uri->segment(1) != 'passwordreset' ) {
                $this->load->helper('utils_helper');
                $target_url = full_url($_SERVER);
            }
            $this->_remove_user_sessions();
            redirect($this->config->item('sso_url').'?redir='.$target_url);
        }
    }
    
    
    function _remove_user_sessions()
    {
        $this->session->unset_userdata('uid');
        $this->session->unset_userdata('user_fname');
        $this->session->unset_userdata('user_lname');
        $this->session->unset_userdata('user_readonly');
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('last_reset');
        $this->session->unset_userdata('user_type');
        $this->session->unset_userdata('group');
        $this->session->unset_userdata('project');
        $this->session->unset_userdata('schedule');
        $this->session->unset_userdata('active_session_key');
        
    }
}
?>
