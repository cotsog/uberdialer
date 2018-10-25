<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users extends MY_Controller {

    // set Default Breadcrumb
    public $crumbs = '<a href="#">Dashboard</a>';
    public $dataresearch_crumbs = '<a href="#">Data Research</a>';
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
        $this->load->model('Users_model'); 
        $this->load->model('Offices_model'); 
    }

    public function index() {
        // To check Authorised User OR not with the help of helper Function
        $isAuthorized = IsTLManagerUpperManagementAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/dialer/dashboards');
        }

        $upperManagement =  $this->config->item('upper_management_types');

        // Get Users  
        $users_data = $this->Users_model->get_user_filtered_list();
        if (!empty($users_data)) {
            foreach ($users_data as $key=>$user_detail) {
                if($user_detail->module == 'tm'){
                    $module_name= 'Telemarketing';
                } else {
                    $module_name= 'Telemarketing, Appointment';
                }
        
                $user_detail->module =$module_name;
            }
        }
        if($this->session->userdata('user_type') == 'manager'){
            $subOffices = $this->session->userdata('sub_telemarketing_offices');
            $officeParent = array($this->session->userdata('telemarketing_offices') => $this->session->userdata('telemarketing_offices'));
            $allSites = array();
            if(!empty($subOffices)){
                foreach ($subOffices as $subTmOffice) {
                    $allSites[$subTmOffice] = $subTmOffice;
                }
                $officeList = array_merge(array("" => "All"),$officeParent,$allSites);
            }else{
                $officeList = array_merge(array("" => "All"),$officeParent);
            }
            
        }else{
            $officeList = array_merge(array("" => "All"), format_array($this->Offices_model->get_all('is_active = 1'),'name','name'));
        }
        
        $data['tm_offices'] = json_encode($officeList);
        $data['getEGWebsitesList'] = $officeList;
        $allUserTypes = array_merge(array("" => "All"), $this->config->item('user_types'));
        $data['allUserTypes'] = json_encode($allUserTypes);
        $data['upperManagementTypes'] = $upperManagement;
        $data['users'] = json_encode($users_data);
        $data['meta_title'] = 'Users';
        $data['title'] = 'Users';
        if(in_array($this->session->userdata('user_type'), $upperManagement) || $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'team_leader'){
            $data['crumbs'] = 'User Management > Manage Users';
        }else{
            $data['crumbs'] = $this->crumbs . ' > Users';
        }
        $data['main'] = 'users/index';     
        $this->load->vars($data);
        $this->load->view('layout');
    }
     
    public function create()
    {
        if($this->config->item('sso_is_enabled')) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Creation of users should only be done on auth.pureincubation.com');
            redirect('/users');
        }
        $logged_user_type = $this->session->userdata('user_type');
        
        // To check Authorised User OR not with the help of helper Function  
        $isAuthorized = IsAdminTLAuthorized($logged_user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/dialer/campaigns');
        }
        
        //Set Validation Rules
        $this->userFormValidation($this->input->post());

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            // set telemarketing office value based on logged user type
            if($logged_user_type !='admin'){
                $logged_tm_office = $this->session->userdata('telemarketing_offices');
            }else{
                $logged_tm_office = "Davao TM";
            }

            // create csv string value from logged user module value
            $module_value =  $this->session->userdata('module');
            $csv_module_value = "";
            $count_array_module_value = "";
            if(!empty($module_value)){
                $csv_module_value = getCSVFromArrayElement($module_value);
                $count_array_module_value = count($module_value);
            }
            //Load Helpers
            $this->load->helper('campaignjobdetail');
            $data['getModuleTypeValues'] = getModuleTypeValues();
            $officeList = format_array($this->Offices_model->get_all('is_active = 1'),'name','name');
            $data['getEGWebsitesList'] = $officeList;
            $data['members_list']   = $this->Users_model->get_team_leads($logged_tm_office,$csv_module_value,$count_array_module_value,true);
            $data['crumbs']         = $this->crumbs . ' > User Management > Create';
            $data['meta_title']     = 'Create User';
            $data['title']          = 'Create User';
            $data['main']           = 'users/create';

            $this->load->vars($data);
            $this->load->view('layout');
        }else{
            
            // Check email is already exist or not from EG Database
            $eg_already_exists = $this->Users_model->get_data_from_eg_by_email($_POST['email']);
            
            // Encrypt user Password for security purpose
            $password_hash =  password_hash($_POST['password'], CRYPT_BLOWFISH);
            $today_date = date('Y-m-d H:i:s', time());
            $isInsertUserFlag = 0; // flag  insert Data in uber database or not 
            $egid = 0; // set eg_user_id
            
            if (!$eg_already_exists) {

                // set user object 
                $eg_user = $this->set_eg_user_object_data();
                $eg_user->password = $password_hash;
                $eg_user->created_at = $today_date;
                $eg_user->created_by = $this->session->userdata('uid');
                
                // insert  record in EG DATABASE and Get EG_user_id
                $eg_user_id = $this->Users_model->insert_eg_user($eg_user);
                if($eg_user_id>0){
                    $isInsertUserFlag = 1;
                    $egid = $eg_user_id;
                }
                else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, an error has occurred during add user in eg application.');
                }
            }else {
                $isInsertUserFlag = 1;
                $egid = $eg_already_exists->id;
            }
            
            // Insert Data on UBER Database
            if($isInsertUserFlag){
                // Check email is already exist or not in UBER Database
                $uber_email_already_exists = $this->Users_model->get_by_email($_POST['email']);

                if(!$uber_email_already_exists){
                    $users = $this->setUserObjectData();
                    $users->password = $password_hash;
                    $users->created_at = $today_date;
                    $users->id = $egid;
                    $user_id = $this->Users_model->insert_user($users);
                    if ($user_id > 0) {
                        $this->session->set_flashdata('class', 'good');
                        $this->session->set_flashdata('msg', 'User Added successfully!');
                    } else {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, an error has occurred.');
                    }   
                }else{
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, Email already exists in both application.');
                }
            }
            redirect('/users');
        }
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

    public function edit($userId){
        // To check Authorised User OR not with the help of helper Function  
        $isAuthorized = IsTLManagerUpperManagementAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/dialer/campaigns');
        }

        //Set Validation Rules
        $this->userFormValidation($this->input->post());
        
        // Get User Info
        $user = $this->Users_model->get_one($userId);

        // create csv string value from user module value
        $user->module = explode(',',$user->module);
        $count_array_module_value = count($user->module);
        $csv_module_value = getCSVFromArrayElement($user->module);

        $loggedInUserType = $this->session->userdata('user_type');
        $upperManagement =  $this->config->item('upper_management_types');
        if (!in_array($loggedInUserType, $upperManagement)) {
            $subOffices = $this->session->userdata('sub_telemarketing_offices');
            $logged_tm_office = $this->session->userdata('telemarketing_offices');
            if(!empty($subOffices)){
                array_push($subOffices, $logged_tm_office);
            }else{
                $subOffices = array($this->session->userdata('telemarketing_offices'));
            }
            if(!in_array($user->telemarketing_offices, $subOffices)){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This user is not in your TM office, You can not edit it!');
                redirect('/dialer/campaigns');
            }
        }
        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            //Load Helpers
            $this->load->helper('campaignjobdetail');
            $data['getModuleTypeValues'] = getModuleTypeValues();
            if($this->session->userdata('user_type') == 'manager'){
                $subOffices = $this->session->userdata('sub_telemarketing_offices');
                $officeParent = array($this->session->userdata('telemarketing_offices') => $this->session->userdata('telemarketing_offices'));
                $allSites = array();
                if(!empty($subOffices)){
                    foreach ($subOffices as $subTmOffice) {
                        $allSites[$subTmOffice] = $subTmOffice;
                    }
                    $officeList = array_merge($officeParent,$allSites);
                }else{
                    $officeList = $officeParent;
                }

            }else{
                $officeList = format_array($this->Offices_model->get_all('is_active = 1'),'name','name');
            }
            $data['getEGWebsitesList'] = $officeList;
            // Set Object If hired_date is not empty
            if (!empty($user->hired_date)) {
                $user->hired_date = date('m/d/Y', strtotime($user->hired_date));
            }

            // get default team leader data based on "Davao TM" office.
            if(empty($user->telemarketing_offices)){
                $telemarketing_offices = 'Davao TM';
            }else{
                $telemarketing_offices = $user->telemarketing_offices;
            }

            $data['members_list']   = $this->Users_model->get_team_leads($telemarketing_offices,$csv_module_value,$count_array_module_value,true);
            $data['upperManagementTypes'] = $upperManagement;
            $data['allUserTypes'] = $this->config->item('user_types');
            $data['members']        = $user;
            $data['meta_title']     = 'Edit User';
            $data['title']          = 'Edit User';
            $data['crumbs']         = '<a href="/users/">Users</a> > Edit';
            $data['main']           = 'users/edit';
            
            $this->load->vars($data);
            $this->load->view('layout');
        }
        else{
            // Set User Object
            $edit_user = $this->setUserObjectData();

            // Check Old Email with new user email 
            if($edit_user->email != $user->email){
                
                // Check already exists Email in database 
                $already_exists = $this->Users_model->get_by_email($edit_user->email);
                if ($already_exists) {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, Email already exists.');
                    redirect('users/');
                }
            }
            
            // Encrypt user Password for security purpose if old password is not matcht To New password
            if(set_value('password') != "" && set_value('password') != $user->password){
                if(strlen(set_value('')) <= 15){
                    $edit_user->password = password_hash(set_value('password'), CRYPT_BLOWFISH);
                }
            }else{
                $edit_user->password =  $user->password;
            }
            $edit_user->created_at =  $user->created_at;
            $edit_user->updated_at = date('Y-m-d H:i:s', time());
            
            $eg_already_exists = $this->Users_model->get_data_from_eg_by_email($_POST['email']);
             // Encrypt user Password for security purpose
            if(isset($_POST['password']))
                $password_hash =  password_hash($_POST['password'], CRYPT_BLOWFISH);
            $today_date = date('Y-m-d H:i:s', time());
            $egid = 0; // set eg_user_id
            
            $edit_user->id =$userId;
            
            //if user status is originally not active and user is trying to set this user to active, display error message
            if($this->input->post('temp_status') != 'Active' &&  $edit_user->status == 'Active'){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', "Sorry, You can't set user back to Active in Uber Dialer. Please ask the Administrator to set this user back to Active.");
                redirect('users/edit/'.$userId);
            }
            
            //set value of is_readonly field enabled & disabled
            $is_readonly = $edit_user->is_readonly;
            $is_tier = $edit_user->tier;
            $this->unset_nulls($edit_user);
            $edit_user->is_readonly = $is_readonly;
            $edit_user->tier = $is_tier;

            // update New Changes
            $update = $this->Users_model->update_user($userId,$edit_user);
            if ($update) {
                if($_POST['previous_parent_id'] != $_POST['teamleads']){
                    $this->load->model('AssignCampaigns_model');
                    $customWhere['teamleader_id'] = $_POST['previous_parent_id'];
                    $customWhere['agent_id'] = $userId;
                    $this->AssignCampaigns_model->delete_assign_agents_by_group_team($customWhere);
                }
                $ip_address = _get_user_ip();
                $this->Users_model->clearing_failed_login($ip_address,$edit_user->email,'0');
                
                //if status is set to resigned/released/Inactive then unlock contacts under this user
                if($edit_user->status != 'Active'){
                    $this->load->model('Contacts_model');
                    $contactsModel = new Contacts_model();
                    $contactsModel->unlockContactByUserId($userId);
                }

                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'User Updated successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
            }
            redirect('users/');
        }
    }

    public function bulkupdate()
    {
        // To check Authorised User OR not with the help of helper Function
        $isAuthorized = IsManagerUpperManagementAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect('/users/profile');
        }
        
        $this->form_validation->set_rules('tmOfficeFrom', 'TM Office From', 'required|trim');
        $this->form_validation->set_rules('tmOfficeTo', 'TM Office To', 'required|trim');
        $this->form_validation->set_rules('teamlead', 'Team Leader', 'required|trim');

        $upperManagement =  $this->config->item('upper_management_types');
        
        if ($this->form_validation->run() == FALSE) {
            if(in_array($this->session->userdata('user_type'), $upperManagement)){
                $offices = array_merge(array("" => " ---SELECT ONE--- ","no_site" => "No site"), format_array($this->Offices_model->get_all('is_active = 1'),'name','name'));
            }else{
                $subOffices = $this->session->userdata('sub_telemarketing_offices');
                $officeParent = array($this->session->userdata('telemarketing_offices') => $this->session->userdata('telemarketing_offices'));
                $allSites = array();
                if(!empty($subOffices)){
                    foreach ($subOffices as $subTmOffice) {
                        $allSites[$subTmOffice] = $subTmOffice;
                    }
                    $officeList = array_merge($officeParent,$allSites);
                }else{
                    $officeList = $officeParent;
                }
                $offices = array_merge(array("" => " ---SELECT ONE--- "), $officeList);
            }
            $officeList = $offices;
            $data['tmOffices'] = $officeList;
            $data['meta_title'] = 'Users';
            $data['title'] = 'Users';
            $data['crumbs'] = 'User Management > Bulk Update';
            $data['main'] = 'users/bulk_edit';     
            $this->load->vars($data);
            $this->load->view('layout');
        }else{
             $this->load->model('AssignCampaigns_model');
            $assignCampaignsModel = new AssignCampaigns_model();
        
            $userIds = $this->input->post('newselectedagents');
            $tmOffice = $this->input->post('tmOfficeTo');
            $tmLead = $this->input->post('teamlead');

            $updatedAt = date('Y-m-d H:i:s', time());

            $users = $this->Users_model->getUsersByIds($userIds, 'id,parent_id');
            
            foreach ($users as $user) {
                $userUpdate = array();
                $userUpdate['telemarketing_offices'] = $tmOffice;
                $userUpdate['parent_id'] = $tmLead;
                $userUpdate['updated_at'] = $updatedAt;

                $update = $this->Users_model->update_user($user->id,$userUpdate);
                
                if ($update) {
                    if($user->parent_id != $tmLead){
                        $customWhere['teamleader_id'] = $user->parent_id;
                        $customWhere['agent_id'] = $user->id;
                        $assignCampaignsModel->delete_assign_agents_by_group_team($customWhere);
                    }
                }
            }
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Users Updated successfully!');
            redirect('/users/bulkupdate');
        }

        
    }
    
    // get campaign based on selected agent from combo box
    public function getSiteAgents()
    {       
        $tmOffice = $_POST['tmOffice'] == 'no_site' ? '' : $_POST['tmOffice'];
        $filter = "";
        if(!empty($_POST['teamLead'])){
            $teamLead = (int) $_POST['teamLead'];
            $filter = "AND parent_id != {$teamLead} ";
        }
        // get list of agent as per selected campaign id
        $response['agentList'] = $this->Users_model->getAgentsByOffice($tmOffice,$filter);
        if (!empty($response['agentList'])) {
            $data['data'] = $response;
            $data['status'] = true; 
        } else {
          $data['message'] = 'Agents not found for this Telemarketing Office!';
          $data['status'] = false; 
        }            
        echo json_encode($data);
        exit;
    }
    
    public function check_tl_with_agent(){
        $user_id = 0;
        if(isset($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }
        // create csv string value from passed user module value
        $module_value = $this->input->post('module_value');
        $csv_module_value = "";
        $count_array_module_value = "";
        if(!empty($module_value)){
            $array_module_value = explode(',',$module_value);
            //$csv_module_value = getCSVFromArrayElement($array_module_value);
            $count_array_module_value = count($array_module_value);
        }
        $agent_data = ($count_array_module_value != 2) ? $this->Users_model->get_agent_by_tl_id($user_id,$module_value) : "";
        if(count($agent_data)> 0 && $count_array_module_value != 2){
            $msg = ($module_value == 'tm') ? "Appointment" : "Telemarketing";
            $data['status'] = false;
            $data['message'] ="This user have already assigned to the agent under the selected Team leader in ".$msg;
        }else{
            $data['status'] = true;
        }
        echo json_encode($data);
        exit();
    }

    public function changepassword($id=NULL) { 

        if($this->config->item('sso_is_enabled')) {redirect($this->config->item('sso_url').'/users/changepassword');}
        
        //Set Validation Rules
        $this->form_validation->set_rules('old_password', 'Old Password', 'required|trim|callback_password_check');  // call password_check function
        $this->form_validation->set_rules('password', 'New Password', 'required|trim|matches[passconf]');
        $this->form_validation->set_rules('passconf', 'Confirm Password', 'required|trim');        
        $email = $this->session->userdata('user_email');

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed                
            $data['meta_title'] = 'Change Password ';            
            $data['logged_in_user']= $this->Users_model->get_by_email($email);
            
            // set Bread Crumbs for Data research Users
            if($this->session->userdata('user_type') == 'dataresearch_user'){
                $bread_crumb_title =$this->dataresearch_crumbs . ' > Change Password';
            }else{
                $bread_crumb_title = $this->crumbs . ' > Change Password';
            }
            
            $data['crumbs'] = $bread_crumb_title;
            $data['main'] = 'users/changepassword';
            $this->load->vars($data);
            $this->load->view('layout');
        } else {
            
            if (!empty($_POST['password'])) {
               
                // Encrypt user Password for security purpose
                $this->db->set('password', password_hash($_POST['password'], CRYPT_BLOWFISH));
                $this->db->set('last_reset',date('Y-m-d'));
                $this->db->where('id', $this->user_id);
                
                // Update Password
                $user_id = $this->db->update('users');  

                $this->session->set_userdata('last_reset', date('Y-m-d'));

                if ($user_id) {
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'The password update successfully!');
                } else {
                     $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'password update failed!');
                }
            }
            redirect('users/changepassword/');
        }
    }

    // Common Vaditation Funciton 
    public function userFormValidation($postDataValue)
    {
        $this->form_validation->set_rules('first_name', 'first_name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'last_name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|max_length[255]|valid_email');
        if (!empty($postDataValue['user_type']) && $postDataValue['user_type']== 'admin') {
            $this->form_validation->set_rules('telemarketing_offices', 'Telemarketing Offices', 'required|trim|max_length[100]');
        }
        if (!empty($postDataValue['password'])) {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[5]|trim');
        }
        if(!isset($_POST['module']) && $this->session->userdata('user_type') == 'admin'){
            $this->form_validation->set_rules('module[]', 'Module', 'required');
        }
        $this->form_validation->set_error_delimiters('<div class="validation_error"><ul><li>', '</li></ul></div>');
    }

     // Common Set User Object Function for UBER DATABASE
    public function setUserObjectData()
    {
        $user = new User();
        $viewUserData = (object)$this->input->post();

        $user->first_name = $viewUserData->first_name;
        $user->last_name = $viewUserData->last_name;
        $user->email = $viewUserData->email;

        $user->is_readonly = (isset($viewUserData->is_readonly)) ? 1 : 0;
        $user->status =$viewUserData->status;
        $user->user_type = $viewUserData->user_type;
        $user->user_type_value ="";

        $user->tier = $viewUserData->tier;
        

        if(!empty($viewUserData->module)){
            $concat_module =  implode(',',$viewUserData->module);
            $user->module = $concat_module;
        }else{
            $module_value = $this->session->userdata('module');
            $csv_module_value = "";
            if(!empty($module_value)){
                $csv_module_value = implode(',',$module_value);
            }
            $user->module = $csv_module_value;
        }
        if($user->user_type == 'admin' ){
            $user->telemarketing_offices = NULL;
            $user->module = "tm,appt";
        }else if(isset($viewUserData->telemarketing_offices)){
            $user->telemarketing_offices = $viewUserData->telemarketing_offices;
        }

        if($user->user_type == 'agent' ){
            $user->parent_id = $viewUserData->teamleads;
        }else{
            $user->parent_id = 0;
        }        
        if (isset($viewUserData->project))
            $user->project = $viewUserData->project;               
        if (!empty($viewUserData->hired_date))
            $user->hired_date = date('Y-m-d H:i:s', strtotime($viewUserData->hired_date));
        if (isset($viewUserData->schedule))
            $user->schedule = $viewUserData->schedule;
        
        if ($user->status == "Released"){
            $user->released_date = date('Y-m-d H:i:s', time());
            $user->resigned_date = NULL;
            $user->inactive_date = NULL;   
        }
        else if ($user->status == "Resigned"){
            $user->resigned_date = date('Y-m-d H:i:s', time());
            $user->released_date = NULL;           
            $user->inactive_date = NULL; 
            
        }else if ($user->status == "InActive"){
            $user->inactive_date = date('Y-m-d H:i:s', time());
            $user->resigned_date = NULL;   
            $user->released_date = NULL;  
            
        }else{
            $user->released_date = NULL; 
            $user->resigned_date = NULL;
            $user->inactive_date = NULL;
        }
        $user->updated_at = date('Y-m-d H:i:s', time());
        
        return $user;
    }

    // Common Set User Object Funciton for EG DATABASE   
    public function set_eg_user_object_data()
    {
        $eg_user = new eg_user();
        $view_eg_user_data = (object)$this->input->post();
        $eg_user->first_name = $view_eg_user_data->first_name;
        $eg_user->last_name = $view_eg_user_data->last_name;
        $eg_user->email = $view_eg_user_data->email;
        $eg_user->is_readonly = (isset($view_eg_user_data->is_readonly) && $view_eg_user_data->is_readonly) ? 1 : 0;
        $eg_user->user_type = $view_eg_user_data->user_type;
        $eg_user->user_type_value ="";
        if($eg_user->user_type == 'agent' ){
            $eg_user->group = $view_eg_user_data->teamleads;
        }else{
            $eg_user->group = 0;
        }
        $eg_user->is_active = '0';
        $eg_user->updated_at = date('Y-m-d H:i:s', time());
        $eg_user->updated_by = $this->session->userdata('uid');
        return $eg_user;
    }

    //  Update LoggedIn User Profile
    public function updateProfile(){
        
        $updateProfileData = $this->input->post();
        $id = $this->session->userdata('uid');
        
        $this->db->set('first_name', set_value('first_name'));
        $this->db->set('last_name', set_value('last_name'));
        $this->db->set('email', set_value('email'));          
        $this->db->set('updated_at', date('Y-m-d H:i:s', time()));
        $this->db->where('id', $id);
        //$this->db->ar_set;
        $update = $this->db->update('users');
        
        // Set NEW Session With Updated Data 
        if ($update) {
            $this->session->set_userdata('user_fname', $updateProfileData['first_name']);
            $this->session->set_userdata('user_lname', $updateProfileData['last_name']);
            $this->session->set_userdata('user_email', $updateProfileData['email']);

            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Your Profile Updated successfully!');
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
        }        
        $session_data = array('user_fname' => set_value('first_name'), 'last_name' => set_value('last_name'),'email'=>set_value('email'));
        $this->session->set_userdata($session_data); 
        if ($this->session->userdata('user_type') == 'dataresearch_user') {
             redirect('dialer/datateam/');
        } else {
        redirect('dialer/Campaigns/');
    }
    }
    
    // Old Password check Validation - Called From change password Function
    public function password_check($old_password) {
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $db_pass = $users_model->authenticate_id_password($this->session->userdata('uid'),$old_password);

        if ($db_pass === NULL) {
            //Match found
            $this->form_validation->set_message('password_check', 'Old password is invalid');
            return FALSE;
        } else {
            return true;
        }
    }
    
   // Check campaign is Assigned to Agent OR NOT
    public function isAssignCampaignToAgent(){
       
        if (!empty($_POST['teamID'])){
            $this->load->model('Users_model');
            $res = $this->Users_model->isAssignCampaignToAgent($_POST['teamID']);
            if(count($res) > 0){
                
                $campaigns = '<ul>';
                $campaigns .= '<li>' . implode( '</li><li>', (array_column($res, 'campaign_name'))) . '</li>';
                $campaigns .=  '</ul>';
                //$campaigns = implode(" <br/> ",(array_column($res, 'campaign_name')));
                $data['status'] = false; 
                $data['message'] ="following campaign(s) already assigned to the agent under this Team leader.<br/><b>".$campaigns."</b>" ;//This team leader assign campaign to agent.
            }else{
                $data['status'] = true;                 
            }           
        }else{
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
        }    

        echo json_encode($data);
        exit();   
        
    }
    
    // SET LOG IN/OUT Process if No Activity from last 15 minutes on page.
    function heartbeatcall(){
        $loggedUserID = $this->session->userdata('uid');

        if(!empty($loggedUserID) && $loggedUserID > 0){
            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();
            $agentSessionID = $this->session->userdata('AgentSessionID');

            // Update SESSION_END Field in agent_Session Table - Uber DATABASE 
            if(!empty($agentSessionID) && $agentSessionID > 0){
                $agentData = array();
                $agentData['session_end'] = date('Y-m-d H:i:s',time());
                $campaignsModel->update_agent_session($agentSessionID,$agentData);
            }
            $data['status'] = true;
            echo json_encode($data);
            exit();
        }else{
            $data['message'] = "You are logged-out by administrator. You will be redirected to login page.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
    }

    // Start RP UAD-11 : for start/stop session by user
    function updateAutoAgentStatus() 
    {
        $post_data = $this->input->post();
        $campaign_id = $post_data['campaignId'];
        $session_value = $post_data['sessionValue'];
        $loggedUserID = $this->session->userdata('uid');
        if($session_value == "start") {
          $this->cache->memcached->save($loggedUserID.'_AgentAutoSessionStatus', 1, 84600); 
        } else {
          $this->cache->memcached->save($loggedUserID.'_AgentAutoSessionStatus', 0, 84600); 
        }
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
        $update_status = $this->Campaigns_model->updateAutoAgentSessionStatus($loggedUserID, $campaign_id, $session_value);
        
        $data['status'] = true;
        echo json_encode($data);
        exit();
    }
    
    // Update User last activity time
    function updateuserlastactivity(){
        $loggedUserID = $this->session->userdata('uid');

        if(!empty($loggedUserID) && $loggedUserID > 0){
            $this->load->model('Users_model');
            $usersModel = new Users_model();

            $userSession = $usersModel->check_user_session($loggedUserID);

            $userSessionData = array();
            $userSessionData['last_activity'] = date('Y-m-d H:i:s',time());

            $usersModel->update_user_session($loggedUserID,$userSessionData);
            
            $data['status'] = true;
            echo json_encode($data);
            exit();
        }
    }
    
    function profile() {

        $uid = $this->session->userdata('uid');

        $this->load->model('Users_model');
        $adminusers_model = new Users_model();

        $user = $adminusers_model->get_one($uid);

        if($user) {
            $data['members_list'] = $this->Users_model->get_team_leads($user->telemarketing_offices);
            $data['userTypes'] =  $this->config->item('user_types');
            $data['user'] = $user;
            $data['meta_title'] = 'User Profile';
            $data['title'] = 'User Profile';
            $data['crumbs'] = 'User Profile';
            $data['main'] = 'users/profile';
            $this->load->vars($data);
            $this->load->view('layout');
        } else {
            redirect('/');
        }
        
    }

    //Added script for Agent status report negative values update based on plivo duration time
    function update_call_start_time(){
        $updated_start_time = $this->Users_model->update_call_start_time();
        foreach($updated_start_time as $key=>$value){
            $this->Users_model->update_final_plivo_time($value->id,$value->updated_call_starttime);
        }
    }

}
?>
