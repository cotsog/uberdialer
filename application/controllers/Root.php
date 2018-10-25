<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Root extends MY_Controller
{

    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a> ';

    function __construct()
    {
	
        parent::__construct();
        $this->load->helper('utils');
    }

    function login()
    {
        $this->load->helper('utils_helper');
        if($this->config->item('sso_is_enabled')) {
            if (get_cookie('PIAuth'))
            {
                $this->load->model('Users_model');
               $adminuser_model = new Users_model();
                $piauth_cookie = _decode_auth_cookie(get_cookie('PIAuth'));
             
                $piauth = explode("||", $piauth_cookie);
                $piauth_email = $piauth[0];
                $piauth_token = $piauth[1];
                $obj = $adminuser_model->get_details_by_email($piauth_email);
                if(!empty($obj) && $obj->status == 'Active'){
                    $this->_set_user_session($obj,$adminuser_model);
                    
                    if ($this->session->userdata('user_type') == 'qa') {
                           /* if ($target_url != '') {
                                redirect($target_url);
                            }else{*/
                        $this->redirect_based_on_qa_login();
                           // redirect('dialer/leads/');
                            //}
                    } else if ($this->session->userdata('user_type') == 'dataresearch_user') {
                            $this->redirect_based_on_login_dataresearch();
                            //redirect('dialer/datateam/');
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
                        $this->redirect_based_on_login_usertype();
                        //redirect($this->redirect_segment_name.'/dashboards/');
                            //}
                        }
                        
                } else {
                    redirect($this->config->item('sso_url'));
                }
            }else{
                
                $target_url = full_url($_SERVER);
                redirect($this->config->item('sso_url').'?redir='.$target_url);
            }
            
        }
        
        //Load Helpers
         if($this->session->userdata('uid')){  
            if($this->session->userdata('user_type') == 'qa'){
                $this->redirect_based_on_qa_login();
                //redirect($this->redirect_segment_name.'/leads/');
            }else {
                $this->redirect_based_on_login_usertype();
               //redirect($this->redirect_segment_name.'/dashboards/');
            }

        }else{
            $this->load->helper('form');
            $this->load->library('form_validation');

            //set validation rules
            $this->form_validation->set_rules('email', 'Email', 'required|trim|min_length[4]|max_length[100]');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');
            $this->session->set_userdata('is_in_login_page', TRUE);

            $session_set_value = $this->session->all_userdata();
            /*if (isset($session_set_value['remember_me']) && $session_set_value['remember_me'] == "1") {
                $this->load->view('login');
            } else {*/
            
            if ($this->form_validation->run() == FALSE) { //Validation failed
                $this->load->view('login');
            } else {
				 $this->load->model('Users_model');
             	$users_model = new Users_model();

                $check_email_exist = $users_model->email_exists(set_value('email'));
                if(!$check_email_exist){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, Email does not exist.');
                    redirect('/login');
                }

                $this->load->helper('common');
                $logged_email = set_value('email');
                $ip_address = _get_user_ip();
                $fail_login_count = $users_model->get_login_failure_count_by_email($logged_email,$ip_address);

                if($fail_login_count > 4){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Locked out. Too many failed login attempts. Please reset your password.');
                    redirect('/login');
                }
                //$result = $Member_model->authenticate(set_value('email'),$this->_encrypt_password(set_value('password')));
                $user = $users_model->authenticate_user($logged_email, set_value('password'));
                $user_id = 0;
                if (!empty($user)) { //check array to see if not empty

                    $user = $user[0];
                        $user_id = $user->id;
                    $user_type = $user->user_type;
                    // If user type is agent / TL then thier assigned campaign store in session.
                    if($user_type == "agent" || $user_type == "team_leader"){
                        $this->load->model('Campaigns_model');
                        $campaignsModel = new Campaigns_model();
                        $get_assign_campaign_ids = $campaignsModel->getAssignedCampaignIdsOfUser($user_type,$user_id);
                    }else{
                        $get_assign_campaign_ids = "";
                    }
                    
                    
                    if($user->status != "Active"){
                        $this->session->set_flashdata('prev_action', 'inactiveuser');
                        redirect('/login');
                    }

                    // Update logged in user activation key // avoid single user login from multiple location
                    $activation_key_update_data = md5(uniqid($user_id, TRUE));
                    $user_data = array();
                    $user_data['active_session_key'] = $activation_key_update_data;
                    $users_model->update_user($user_id,$user_data);

                    //Set Custom Session Values
                    $this->session->set_userdata('uid', $user->id);
                    $this->session->set_userdata('user_fname', $user->first_name);
                    $this->session->set_userdata('user_lname', $user->last_name);
                    $this->session->set_userdata('user_readonly', $user->is_readonly);
                    $this->session->set_userdata('user_email', $user->email);
                    $this->session->set_userdata('last_reset', $user->last_reset);
                    $this->session->set_userdata('user_type', $user->user_type);
                    $this->session->set_userdata('group', $user->User_Group_Name);
                    $this->session->set_userdata('project', $user->project);
                    $this->session->set_userdata('schedule', $user->schedule);
                    $this->session->set_userdata('telemarketing_offices', $user->telemarketing_offices);
                    $this->session->set_userdata('active_session_key', $activation_key_update_data);
                    $this->session->set_userdata('module', explode(',',$user->module));
                    $this->session->set_userdata('assigned_campaign_ids', $get_assign_campaign_ids);

                    //Load Helpers
                   // $this->load->helper('campaignjobdetail');
                    //$getModuleTypeValues = getModuleTypeValues();
                   // $this->session->set_userdata('getModuleTypeValues', $getModuleTypeValues);
                    $remember = $this->input->post('remember_me');
                    if ($remember) {
                        // Set remember me value in session  
                        $this->session->set_userdata('remember_me', TRUE);
                    }
                    //$target_url = $this->session->userdata('target_url');          //comment this
                   // if ($target_url != '') {
                      //  redirect($target_url);
                    //} else {
                        //redirect('users');
                       
                        $users_model->clearing_failed_login($ip_address,$logged_email,'0');

                        $result = '1';
                        $login_history_id = $this->login_attempt_result($ip_address, $user_id,$logged_email,$result);
                        if($login_history_id <= 0){
                            $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', 'Sorry, an error has occurred due to login.');
                            redirect('/login');
                        }

                        if($this->session->userdata('user_type') == 'qa'){
                           /* if ($target_url != '') {
                                redirect($target_url);
                            }else{*/
                            $this->redirect_based_on_qa_login();
                            //redirect('leads/');
                            //}

                        }
                        else if($this->session->userdata('user_type') == 'dataresearch_user'){
                            $this->redirect_based_on_login_dataresearch();
                            //redirect('datateam/');
                        }
                        else{
                            //check active session
                            $this->load->model('Campaigns_model');
                            $campaignsModel = new Campaigns_model();

                            $previous_session_active_data = $campaignsModel->check_agent_session($this->session->userdata('uid'));
                            if(!empty($previous_session_active_data)){
                            $agentData = array();
                                if(empty($previous_session_active_data->session_end)){

                                    $create_previous_session_end_date = strtotime('+2 minutes', strtotime($previous_session_active_data->session_start));
                                    $agentData['session_end'] = date('Y-m-d H:i:s', $create_previous_session_end_date);

                                }
                            $agentData['is_session_deactive'] = 1;
                            $campaignsModel->update_agent_session_by_user($this->session->userdata('uid'),$agentData);

                        }
                           /* if ($target_url != '') {
                                redirect($target_url);
                            }else{*/
                            $this->redirect_based_on_login_usertype();
                            //redirect($this->redirect_segment_name.'/dashboards/');

                            //}
                        }
                    //}

                } else {

                    //log failed login attempt
                    $result = '0';
                    $login_history_id = $this->login_attempt_result($ip_address, $user_id,$logged_email,$result);
                    if($login_history_id <= 0){
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, an error has occurred due to Too many failed login attempts.');
                        redirect('/login');
                    }

                    //Authentication failed.		
                    $this->session->set_flashdata('prev_action', 'loginfail');
                    redirect('/login');
                }
            }
        }
    }

    function logout() {

        if($this->config->item('sso_is_enabled')) {
            delete_cookie('PIAuth','pureincubation.com');
            delete_cookie('PIAuthSession','pureincubation.com');
        }
        $is_ajax_request = $this->input->is_ajax_request();

        if ($this->session->userdata('uid')) {            

            if($this->session->userdata('user_type') == 'agent' || $this->session->userdata('user_type') == 'team_leader'){
                $this->load->model('Campaigns_model');
                $isAgentSession = $this->Campaigns_model->check_agent_session($this->session->userdata('uid'));
                
                // Start HP UAD 46:
                $this->cache->memcached->delete($this->session->userdata('uid').'_AutoDialEnable');
                $this->cache->memcached->delete($this->session->userdata('uid').'_AgentAutoSessionStatus');
                $this->Campaigns_model->deleteLiveAutoAgent($this->session->userdata('uid'));  
                // End HP UAD 46:
                
                if(!empty($isAgentSession)){
                    $agentData = array();
                    $agentData['session_end'] = date('Y-m-d H:i:s',time());
                    $agentData['is_session_deactive'] = 1;
                    $this->Campaigns_model->update_agent_session($isAgentSession->id,$agentData);
                    
                    $this->session->unset_userdata('AgentSessionID');
                    $this->session->unset_userdata('AgentSessionCampaignID');
                }
            }

            $this->load->model('Users_model');
            $usersModel = new Users_model();

            // remove user session
            $user_session = $usersModel->check_user_session($this->session->userdata('uid'));

            if($user_session) {
                $user_session_update = array(
                        'is_session_active' => 0,
                    );
                 // remove user session
                $usersModel->update_user_session($this->session->userdata('uid'), $user_session_update);

            } 

            $user_data = array('uid' => '', 'user_fname' => '', 'target_url' => '', 'user_email' => '');
            
            $user_data = $this->session->all_userdata();

            foreach ($user_data as $key => $value) {
                if ($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent') {
                    $this->session->unset_userdata($key);
                }
            }
            if(!$is_ajax_request)
            $this->session->set_flashdata('prev_action', 'logout');
        }
        if(!$is_ajax_request)
        redirect('/login');
        else{
            // $data['message'] = "You are logged-out by administrator. You will be redirected to login page.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
    }
    }

    function index() {

        $this->load->helper('url');
        if ($this->session->userdata('uid')) {

            $this->load->helper('utils');
            $this->load->library(array('form_validation')); // load form lidation libaray & session library
            $this->load->helper(array('url', 'html', 'form'));
            $this->load->model('Campaigns_model');

            $totalCampaignRecord = $this->Campaigns_model->getCampaignNumberRecord();
            $this->load->helper('campaignjobdetail');
            $func = 'getCampaignTypeValues'.ucfirst($this->app);
            $campaignTypeList = array_merge(array("" => "All"), $func());
            $data['campaignTypeList'] = json_encode($campaignTypeList);
            $data['totalCampaignRecord'] = $totalCampaignRecord;
            $campaign_ary = $this->Campaigns_model->getCampaignList();
            $data['campaigns'] = json_encode($campaign_ary);
            $completed_campaign_ary = $this->Campaigns_model->getCampaignListCompleted();
            $data['completed_campaigns'] = json_encode($completed_campaign_ary);
            $data['teamMemberUserList'] = $this->Campaigns_model->getTeamLeaderUsersList();
            $data['meta_title'] = 'Campaigns';
            $data['title'] = 'Campaigns';
            $data['main'] = $this->app_module_name.'/campaigns/index';
            $data['logged_user_type'] = $this->session->userdata('user_type');
            $this->load->vars($data);
            $this->load->view('layout');
        } else {
            redirect('/login');
        }
    }

    /**
     * @param $ip_address
     * @param $user_id
     * @param $users_model
     * @return mixed
     */
    public function login_attempt_result($ip_address, $user_id,$logged_email,$result)
    {
        $this->load->model('Users_model');
        $users_model = new Users_model();

        $data = array(
            'ip' => $ip_address,
            'user_id' => $user_id,
            'email' => $logged_email,
            'result' => $result,
            'created_at' => date('Y-m-d H:i:s', time())
        );

        $login_history_id = $users_model->log_failed_login($data);
        return $login_history_id;
    }

    //------------------------------------------------------------------------>
    // Helper Methods Follow
    //------------------------------------------------------------------------>

    private function _encrypt_password($password)
    {
        //use 1-way hash encryption via sha1 function and add 'salt'
        //'salt' = appending of 32 char encryption key to password
        return sha1($password . $this->config->item('encryption_key'));
    }
    
    public function password()
    {
            //Form Submitted and validation passed
            $email = $this->input->post('email');
            $this->load->model('Users_model');
            $users_model = new Users_model();
            $member = $users_model->get_by_email($email);     
            
            $this->load->model('Password_reset_model');
            $pass_reset_model = new Password_reset_model();
            $pass_reset = new Passwordreset();
		
            if (!empty($member)) { 
                $token = $this->_create_random_token(15);
                $pass_reset->user_id = $member->id;
                $pass_reset->token = $token;
                $pass_reset->is_reset = 0;
                $pass_reset->created_at = date('Y-m-d H:i:s', time());
                $pass_reset->updated_at = date('Y-m-d H:i:s', time());
                $pass_reset_model->insert($pass_reset);

                $this->_send_password_reset_mail($member->email, $token);
                $this->session->set_flashdata('forgotpass', 'passwordsent');
                redirect('/login');
            } else {
                $this->session->set_flashdata('forgotpass', 'doesntexist');
                redirect('/login');
            }            
    }
    
    private function _create_random_token($length)
    {
        $string = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
    
    private function _send_password_reset_mail($email, $token) {
        $message ='<div><font face="Arial, Verdana" size="2">You recently requested a password reset on #site_name# To complete the process, click the link below.</font></div><div><font face="Arial, Verdana" size="2">This link will expire 24 hours after this email was sent.</font></div><div><font face="Arial, Verdana" size="2"><br></font></div><div><font face="Arial, Verdana" size="2">#reset_link#</font></div><div><font face="Arial, Verdana" size="2"><br></font></div><div><font face="Arial, Verdana" size="2">If you didn\'\'t make this request, it\'\'s likely that another user has entered your email address by mistake and your account is still secure.</font></div><div><font face="Arial, Verdana" size="2">Thank you,</font></div><div><font face="Arial, Verdana" size="2">Support at #site_name#</font></div>';

            //build password reset link
            $reset_link = base_url() . 'passwordreset/' . $token;

            //LOAD LIBRARY AND SET TO,SUBJECT AND HTML
            $subject = 'Password Reset Instructions';
            $body = str_replace('#site_name#', $this->site_name, str_replace('#reset_link#', $reset_link, $message));

            
//            $this->load->library('Postmark');
//            $this->postmark->to($email);
//            $this->postmark->subject($subject);
//            $this->postmark->message_html($body);
//
//            $this->postmark->send();
            
            $this->load->helper('common');
        send_email_sparkpost($email, $subject, $body);
    }
    
    public function passwordreset($token)
    {
		
        //Load Helpers
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('utils');
        $this->load->model('Password_reset_model');
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $this->crumbs .= '> Reset Password';

        //Set Validation Rules
        $this->form_validation->set_rules('r_id', '', 'required|trim|integer');
        $this->form_validation->set_rules('m_id', '', 'required|trim|integer');
        $this->form_validation->set_rules('password', 'New Password', 'required|trim|matches[passconf]|min_length[5]|max_length[15]');
        $this->form_validation->set_rules('passconf', 'Confirm Password', 'required|trim');
        $this->form_validation->set_error_delimiters('<span class="error">', '</span>');

        $pass_reset_model = new Password_reset_model();
        $pass_reset = array();

        if ($this->form_validation->run() == FALSE) { //display form
		
            //Validate token to verify alpha-numeric chars. You can use the CI form validation class
            //by calling its alpha_numeric() function directly
            if ($this->form_validation->alpha_numeric($token)) {
                $pass_reset = $pass_reset_model->get_by_token($token);
            }
            //If a current token is found then send pass_reset data to the view to display password reset form
            if (!empty($pass_reset)) {
                $data['pass_reset'] = $pass_reset[0];
                $data['token'] = $token; //append token to form post url to retrieve on postabck
            } else { //otherwise don't show a form and explain why
                $this->session->set_flashdata('resetpass', 'tokenexpired');
                redirect('/login');
            }
        } else { //form submitted
	
            $user = new User();
            $user->id = set_value('m_id');
            $logged_email = set_value('email_id');
            $password_hash =  password_hash(set_value('password'), CRYPT_BLOWFISH);
           
			$this->db->set('password', $password_hash);
            $this->db->set('updated_at', date('Y-m-d H:i:s', time()));
            
            $this->db->where('id', $user->id);
            //$new_values = $this->db->ar_set;
            $update = $this->db->update('users');
            
           if ($update) {
                $passReset = new passwordreset();
                $passReset->is_reset = 1;
                $passReset->id = set_value('r_id');
                $passReset->updated_at = date('Y-m-d H:i:s', time());
                $pass_reset_model->update($passReset);

                $m = $users_model->get_by_id($user->id);
                $this->_send_password_has_been_reset_mail($m->email);

                $this->load->helper('common');
                $ip_address = _get_user_ip();
                $users_model->clearing_failed_login($ip_address, $m->email, '0');

                $this->session->set_flashdata('resetpass', 'resetsuccess');
            } else {
                $this->session->set_flashdata('resetpass', 'resetfailed');
            }
            redirect('/login');
        }

        $data['crumbs'] = $this->crumbs;
        $data['meta_title'] = 'Reset Password';
        $this->load->vars($data);
        $this->load->view('passwordreset');
    }
    
    private function _send_password_has_been_reset_mail($email)
    {
        $msg = '<p>This is just a notification to alert you that your password has been successfully changed.</p>';
        $msg .= '<p>Visit <a href="'. base_url() .'">uberdialer.com</a></p>';

//        $this->load->library('postmark');
//        $this->postmark->to($email);
//        $this->postmark->subject("Password Change Notification");
//        $this->postmark->message_html($msg);
//        $this->postmark->send();
        $this->load->helper('common');
        send_email_sparkpost($email, 'Password Change Notification', $msg);
    }

    public function php_info(){
        echo phpinfo(); exit;
    }

    private function redirect_based_on_qa_login()
    {
        if (in_array('tm', $this->session->userdata('module')) && $this->session->userdata('user_type') != 'admin') {
            redirect('dialer/leads');
        } else {
            redirect('dialer/leads');
}
    }

    private function redirect_based_on_login_usertype()
    {redirect('users/profile');exit;
        if (in_array('tm', $this->session->userdata('module')) && $this->session->userdata('user_type') != 'admin') {
            redirect('dialer/dashboards');
        } else {
            redirect('dialer/dashboards');
        }
    }

    private function redirect_based_on_login_dataresearch()
    {
        if (in_array('tm', $this->session->userdata('module')) && $this->session->userdata('user_type') != 'admin') {
            redirect('dialer/datateam');
        } else {
            redirect('dialer/datateam');
        }
    }
}
?>