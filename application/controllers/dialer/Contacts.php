<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contacts extends MY_Controller
{
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';

    public function __construct()
    {
        parent::__construct();

        // Start RP UAD-86 for if function call from api then pass is_api value 1 to prevent rediraction on login
        $isApiCall = $this->input->post('is_api');
        if (! $this->session->userdata('uid') && empty($isApiCall)) {
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->driver('cache');
        $this->load->library(array('form_validation', 'session', 'util')); // load form validation library & session library
        $this->load->helper(array('url', 'html', 'form', 'utils','common','campaignjobdetail'));
        $this->load->model('Contacts_model'); 
    }

    public function index($id = null, $list_id = null, $agentSigninStatus = null)
    {
        $user_type = $this->session->userdata('user_type');
        
        if ($user_type == 'qa') {
            redirect('/dialer/leads');
        }
        
        $loggedUserID = $this->session->userdata('uid');
        $is_ajax_request = $this->input->is_ajax_request();

        //check for campaign id in url
        if (empty($id)) {
            if($is_ajax_request){
                $data['message'] = "Please select campaign.!";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please select campaign.');
            redirect('/dialer/campaigns/');
        }
        //check for list_id in url
        if(empty($list_id)){
            redirect('/dialer/campaigns/');
        }
        
        //check if list is inactive when agent usertype
        if ($user_type == 'agent' && $list_id != null &&  $list_id != 'auto') {
            $this->load->model('Lists_model');
            $listModel = new Lists_model();
            $list = $listModel->get_one($list_id, 'status');
            
            if (strtolower($list->status) == 'inactive') {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign List #' . $list_id . ' is already inactive');
                redirect('/dialer/campaigns');
            }
        }
                
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
        //Validate that campaign is assign to agent
        $this->campaign_is_assign_or_not_by_user_type($id, $user_type, $campaignsModel, $loggedUserID);
        // Get Campaign Information	 
        $campaignData = $campaignsModel->get_campaign_data($id);
        //check for tm offices
        $this->check_authorized_tm_office($user_type, $campaignData);           
        if (!empty($agentSigninStatus) && $agentSigninStatus!="ext") {
            if ($user_type == 'agent' || $user_type == 'team_leader') {
                
                //check that current campaign is already sign in by user
                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                $agentSessionId = $this->session->userdata('AgentSessionID');
                if($agentSessionCampaignId == $id && $agentSessionId == $loggedUserID){
                    $checkforsignout = "1";
                }else{
                    $checkforsignout = "";
                }					
                if (empty($checkforsignout) && ($agentSigninStatus == 'in')) {
                    //if user is not sign in for the current campaign then we need to deactive all other campaign and activate user in current campaign
                    $agentUpdateData = array();
                    $agentUpdateData['session_end'] = date('Y-m-d H:i:s', time());
                    $agentUpdateData['is_session_deactive'] = 1;
                    //update entries to deactivate of all the campaigns
                    $campaignsModel->update_agent_session_by_user($loggedUserID, $agentUpdateData);
                    $this->session->unset_userdata('AgentSessionID');
                    $this->session->unset_userdata('AgentSessionCampaignID');
                    $this->session->unset_userdata('AutoDialEnable');
                    $this->session->unset_userdata('AgentAutoSessionStatus');
                    
                    //activate current campaign 			
                    $agentData = array();
                    $agentData['user_id'] = $loggedUserID;
                    $agentData['campaign_id'] = $id;
                    $agentData['session_start'] = date('Y-m-d H:i:s', time());
                    $agentSessionId = $campaignsModel->insert_agent_session($agentData);
                    $this->session->set_userdata('AgentSessionID', $agentSessionId);
                    $this->session->set_userdata('AgentSessionCampaignID', $id);
                    $this->session->unset_contactdata('ContactFilter');
                    // Start RP UAD-11 : value store in session for campaign is enable or not for autodial 
                        $agent_session_status = $campaignsModel->getAutoAgentStatus($loggedUserID);
                        $campaignAutoDialEnable = $campaignsModel->checkCampaignAutodailYesOrNo($id);
                        $this->session->set_userdata('AutoDialEnable', $campaignAutoDialEnable);
                        $this->session->set_userdata('AgentAutoSessionStatus', $agent_session_status);
                    // End RP UAD-11 : value store in session for campaign is enable or not for autodial
                } else if (!empty($checkforsignout) && ($agentSigninStatus == 'out')) {
                    //deactivate campaign on sign out click
                    $agentData = array();
                    $agentData['session_end'] = date('Y-m-d H:i:s', time());
                    $agentData['is_session_deactive'] = 1;
                    $campaignsModel->update_agent_session_by_user($loggedUserID, $agentData);
                    
                    
                    $this->session->unset_userdata('AgentSessionID');
                    $this->session->unset_userdata('AgentSessionCampaignID');
                    
                    // Start RP UAD 11 :
                    $this->session->set_userdata('AutoDialEnable', 0);
                    //$this->session->unset_userdata('AgentAutoSessionStatus');
                    $this->Campaigns_model->updateAutoAgentSessionStatus($loggedUserID, $id, 'stop');
                    // End RP UAD 11:
                    
                    if($is_ajax_request){
                        $data['message'] = "";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                    redirect('/dialer/campaigns');
                }
            }
        }//for back to call list link second segment is empty
        else if ($user_type == 'agent') {
            $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
            if($agentSessionCampaignId!=$id){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                redirect('/dialer/campaigns');
            }
        }
        $data['meta_title'] = 'Call Queue';
        $data['title'] = 'Call Queue';
        $this->crumbs .= '> <a href="/dialer/campaigns">Campaigns</a> > Call Queue';
        $data['crumbs'] = $this->crumbs;
        // Start HP UAD-85: Redirected to a new landing page after signing in to the campaign
        // if enable then redirect to autodialer application
        if ($campaignData->auto_dial == 1 && $this->config->item('auto_dialer_toggle')) {
            $data['main'] = 'dialer/contacts/autocontactlist';
        } else{
            $data['main'] = 'dialer/contacts/index';
        }
        // End HP UAD-85: Redirected to a new landing page after signing in to the campaign
        $data['campaign_id'] = $id;
        $data['listId'] = $list_id;
        $data['campaignData'] = $campaignData;
        $data['upperManagement'] =  $this->config->item('upper_management_types');
        $workableDispositionName = $this->config->item('workableDispositionName');
        $nonWorkableDispositionName = $this->config->item('nonWorkableDispositionName');
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        // Start HP UAD-85: Redirected to a new landing page after signing in to the campaign
		//get workable disposition for Workable List
        if ($campaignData->auto_dial == 1 && $this->config->item('auto_dialer_toggle')) {
            if ( ! $callDispositionValues = $this->cache->get('callDispositionValuesTemp')) {
                $this->load->model('Calls_model');
                $gcallDispositionData = array();
                $gcallDispositionData['callData'] = $this->Calls_model->getCallDispositionsByModule('tm');
                // Set call disposition array for disposition filter on contact list page 
                $callDispositionAry = array();
                foreach ($gcallDispositionData['callData'] as $callDisp) {
                    $callDispositionAry[$callDisp->id] = $callDisp->calldisposition_name;
                }
                $callDispositionValues = json_encode($callDispositionAry);
                $this->cache->save('callDispositionValuesTemp', $callDispositionValues, 86400);
            }
            $data['CallDispositionValues'] = $callDispositionValues;
        } else {
            if ( ! $workable_calldisposition_values = $this->cache->get('workable_calldisposition_values_temp')) {
                $this->load->model('Calls_model');
                $gcallWorkableDispositiondata = array();
                $gcallWorkableDispositiondata['callData'] = $this->Calls_model->getCallDispositionsByModule('tm', 1);
                // Set call disposition array for disposition filter on contact list page 
                $workable_calldispositionary = array();
                foreach ($gcallWorkableDispositiondata['callData'] as $calldisp) {
                    $workable_calldispositionary[$calldisp->id] = $calldisp->calldisposition_name;
                }
                $workable_calldisposition_values = json_encode($workable_calldispositionary);
                $this->cache->save('workable_calldisposition_values_temp', $workable_calldisposition_values, 86400);
            }
            //get workable disposition for Non-Workable List
            if ( ! $non_workable_calldisposition_values = $this->cache->get('non_workable_calldisposition_values_temp1')) {
                $this->load->model('Calls_model');
                $gcallNonWorkableDispositiondata = array();
                $gcallNonWorkableDispositiondata['callData'] = $this->Calls_model->getCallDispositionsByModule('tm', "0");
                // Set call disposition array for disposition filter on contact list page 
                $non_workable_calldispositionary = array();
                foreach ($gcallNonWorkableDispositiondata['callData'] as $calldisp) {
                    $non_workable_calldispositionary[$calldisp->id] = $calldisp->calldisposition_name;
                }
                //remove callback dispo from the call dispo filter under non-workable tab
                unset($non_workable_calldispositionary[2]);
                $non_workable_calldisposition_values = json_encode($non_workable_calldispositionary);
                    $this->cache->save('non_workable_calldisposition_values_temp1', $non_workable_calldisposition_values, 86400);
            }
            $data['CallWorkableDispositionValues'] = $workable_calldisposition_values;
            $data['CallNonWorkableDispositionValues'] = $non_workable_calldisposition_values;
        }
        // End HP UAD-85: Redirected to a new landing page after signing in to the campaign
        $Leadarray = array();
        $Leadarray = getLeadStatusValues();
        if ($campaignData->auto_dial == 1 && $this->config->item('auto_dialer_toggle')) {
            $a = array("" => "All","Follow-up" => "Follow-up");
        } else {
        $a = array_merge(array("" => "All"), $Leadarray);
        }
        $data['LeadStatusValues'] = json_encode($a);
        if ($agentSigninStatus == 'contactsort' || !empty($this->session->contactdata('set_contactdata')->rules)) {
            $iscontactfilter = 1;
        } else {
            $iscontactfilter = 0;
            $this->session->unset_contactdata('ContactFilter');
            $this->session->unset_contactdata('sortfield');
            $this->session->unset_contactdata('sortindex');
        }
        // Get workable contact list
        $data['contacts'] = $this->getContacts($id, 0,$iscontactfilter, 0, $list_id);
        // set filters, if applicable
        if(!empty($this->session->contactdata('set_contactdata')->rules)) {
            foreach($this->session->contactdata('set_contactdata')->rules as $k => $filter_rule) {
                $filter_rules[$filter_rule->field] = $filter_rule->data;
                //$json_filters[] = $filter_rules;
            }
            $data['filter_rules'] = json_encode($filter_rules);
        } else {
            $data['filter_rules'] = '{"":""}';
        }
        $this->load->vars($data);
        $this->load->view('layout');
    }

    // Check Campaign Sign out or not
    public function campaign_sign_in_out($id = null, $agentSigninStatus = null)
    {
        $loggedUserID = $this->session->userdata('uid');
        $user_type = $this->session->userdata('user_type');

        $is_ajax_request = $this->input->is_ajax_request();

        //check for campaign id in url
        if (empty($id)) {
            if($is_ajax_request){
                $data['message'] = "Please select campaign.!";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please select campaign.');
            redirect('/dialer/campaigns/');
        }

        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();

        //Validate that campaign is assign to agent
        $this->campaign_is_assign_or_not_by_user_type($id, $user_type, $campaignsModel, $loggedUserID);

        // Get Campaign Information
        $campaignData = $campaignsModel->get_one($id);

        //validation to check that campaign is available in campaign table
        if (empty($campaignData)) {
            if($is_ajax_request){
                $data['message'] = "Campaign not found, please make sure that the campaign ID is correct!";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/campaigns');
        } else {
            // If user is manager then only check below condition.
            if($user_type == 'manager'){
                $this->check_authorized_tm_office($user_type, $campaignData);
            }    
            //second segment passed from campaign list page for sign in/sign out functionality
            if (!empty($agentSigninStatus)) {
                if ($user_type == 'agent' || $user_type == 'team_leader') {

                    //check that current campaign is already sign in by user
                    $checkforsignout = $campaignsModel->check_agent_signout($loggedUserID, $id);

                    if (empty($checkforsignout) && ($agentSigninStatus == 'in')) {
                        //if user is not sign in for the current campaign then we need to deactive all other campaign and activate user in current campaign
                        $agentUpdateData = array();
                        $agentUpdateData['session_end'] = date('Y-m-d H:i:s', time());
                        $agentUpdateData['is_session_deactive'] = 1;

                        //update entries to deactivate of all the campaigns
                        $campaignsModel->update_agent_session_by_user($loggedUserID, $agentUpdateData);

                        $this->session->unset_userdata('AgentSessionID');
                        $this->session->unset_userdata('AgentSessionCampaignID');
                        $this->session->unset_userdata('AutoDialEnable');
                        $this->session->unset_userdata('AgentAutoSessionStatus');

                        //activate current campaign
                        $agentData = array();
                        $agentData['user_id'] = $loggedUserID;
                        $agentData['campaign_id'] = $id;
                        $agentData['session_start'] = date('Y-m-d H:i:s', time());
                        $agentSessionId = $campaignsModel->insert_agent_session($agentData);

                        $this->session->set_userdata('AgentSessionID', $agentSessionId);
                        $this->session->set_userdata('AgentSessionCampaignID', $id);
                        $this->session->unset_contactdata('ContactFilter');
                        // Start HP UAD-46 : check previous campaign is auto dial or not
                        $previousCampaignAutoDialStatus = $this->cache->memcached->get($loggedUserID.'_AutoDialEnable');
                        if (empty($previousCampaignAutoDialStatus)) {
                            $previousCampaignAutoDialStatus = 0;
                        }
                        $currentCampaignAutoDialStatus = $campaignData->auto_dial;
                        if ($previousCampaignAutoDialStatus == 1 && $currentCampaignAutoDialStatus == 1) {
                            $this->cache->memcached->save($loggedUserID.'_AgentAutoSessionStatus', 0, 84600); 
                            $campaignsModel->updateAutoAgentSessionStatus($loggedUserID, $id, 'stop');
                        } else if ($previousCampaignAutoDialStatus == 0 && $currentCampaignAutoDialStatus == 1) {
                            $this->cache->memcached->save($loggedUserID.'_AutoDialEnable', $currentCampaignAutoDialStatus, 84600); 
                            $this->cache->memcached->save($loggedUserID.'_AgentAutoSessionStatus', 0, 84600); 
                            $campaignsModel->insertLiveAutoAgent($loggedUserID,$id);
                        } else if ($previousCampaignAutoDialStatus == 1 && $currentCampaignAutoDialStatus == 0) {
                            $this->cache->memcached->delete($loggedUserID.'_AutoDialEnable');
                            $this->cache->memcached->delete($loggedUserID.'_AgentAutoSessionStatus');
                            $campaignsModel->deleteLiveAutoAgent($loggedUserID);
                        } else {
                            $this->cache->memcached->delete($loggedUserID.'_AutoDialEnable');
                            $this->cache->memcached->delete($loggedUserID.'_AgentAutoSessionStatus');
                        }
                        // End HP UAD-46 : check previous campaign is auto dial or not 
                    } else if (!empty($checkforsignout) && ($agentSigninStatus == 'out')) {
                        //deactivate campaign on sign out click
                        $agentData = array();
                        $agentData['session_end'] = date('Y-m-d H:i:s', time());
                        $agentData['is_session_deactive'] = 1;
                        $campaignsModel->update_agent_session_by_user($loggedUserID, $agentData);
                        
                        $this->session->unset_userdata('AgentSessionID');
                        $this->session->unset_userdata('AgentSessionCampaignID');
  
                        // Start HP UAD 46
                        $this->cache->memcached->delete($loggedUserID.'_AutoDialEnable');
                        $this->cache->memcached->delete($loggedUserID.'_AgentAutoSessionStatus');
                        $campaignsModel->deleteLiveAutoAgent($loggedUserID);
                        // End HP UAD 46    
                        
                        if($is_ajax_request){
                            $data['message'] = "";
                            $data['status'] = false;
                            echo json_encode($data);
                            exit();
                        }

                        redirect('/dialer/campaigns');
                    }
                }
            }//for back to call list link second segment is empty
            else if ($user_type == 'agent') {

                $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
                //if user has opened two tabs and in one tab he signed out all the campaigns and then through url if he try to access signed out campaign
                if (empty($agentSessionCampaignId)) {
                    if($is_ajax_request){
                        $data['message'] = "Campaign not sign in, please make sure that the Campaign is Sign in!";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                    redirect('/dialer/campaigns');
                } else {
                    //if user has open two tabs and in one tab he clicked signed in for one campaign and second tab he clicked signed in to another campaign.in this case active campaign session would be changed to last campaign that is signed in.so we just checking that active campaign session is matching to current campaign id.
                    $this->check_campaign_active_session($agentSessionCampaignId, $id);
                }
            }
        }
        // Start HP UAD-66: If campaign is enable for autodial then redirect to autodial application
        // get campaign is autodialer enable or not
        $checkCampaignAutoDialEnable = $campaignData->auto_dial;
        if ($checkCampaignAutoDialEnable == "1" && $this->config->item('auto_dialer_toggle') && ($user_type == 'agent' || $user_type == 'team_leader')) {
            $str = "\n== Cache log (" . date('d:m:y') . ") : Start cache logic  == "
                   ."\n Cache log : Camp id is : " . $id
                   ."\n Cache log : Agent id is : " . $loggedUserID
                   ."\n Cache log : Before start of cache process";
            
            // Cache process
            $this->cacheProcess($id, $loggedUserID);
            
            $str .="\n Cache log : After start of cache process"; 
            $this->util->addLog("cache_log.txt", $str);
            
            // Start HP UAD-85: Redirected to a new landing page after signing in to the campaign
            // if enable then redirect to autodialer application
            redirect('/dialer/contacts/index/' . $id.'/auto');
            // End HP UAD-85: Redirected to a new landing page after signing in to the campaign
        } else {
            redirect('/dialer/lists/index/'.$id);
        }
        // End HP UAD-66: If campaign is enable for autodial then redirect to autodial application
    }

    public function create($campaignID = null,$list_id = null)
    {
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        
        $isAuthorized = IsAdminTLManagerAgentAuthorized($user_type);
         if (!$isAuthorized) {
             $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
         }
        
         
         //check if exists, if it exist, then load the contact details in the form and set that to edit mode
         $contact_id = $this->input->get('cid');
         $old_contact_id = "";
         $isexist = false;
         if(!empty($contact_id)){
             $contact_id = (int) $contact_id;
             $contact_details = $this->Contacts_model->get_one_contact(SQLInjectionOFEGDB($contact_id));
             $data['contact_details'] = $contact_details;
             $old_contact_id = $contact_id;
             $isexist = 1;
         }
         
         
        //Set Validation Rules
        $this->contactFormValidation($this->input->post());

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed

            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();

            //Validate that campaign is assign to agent
            $this->campaign_is_assign_or_not_by_user_type($campaignID, $user_type, $campaignsModel, $loggedUserID);

            $campaign = $campaignsModel->getCampaignDetailByID($campaignID);
            if (empty($campaign)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
                redirect('/dialer/campaigns');
            }
            // check logged user is authorized for access this page
            $this->check_authorized_tm_office($user_type,$campaign);
            $countryList = $this->Contacts_model->get_countries();

            $data['countries'] = $countryList;
            $data['campaign_id'] = $campaignID;
            $data['list_id'] = $list_id;
            //Fetch Filters From helper files 
            $data['jobLevelValues'] = getJobLevelValues();
            $data['jobFunctionValues'] = getJobFunctionValues();
            $data['companySizeValues'] = getCompanySizeValues();
            $data['industriesValues'] = getIndustriesValues();
            
            $newContactCallID = 0;
            
            //if business is MPG get bed size drop down from mpg.questions table where id = 242
            if($this->app == 'mpg'){
                $this->load->model('Calls_model');
                $bedSizeFilter = "Where id = 242";
                $bedSizeOptions = $this->Calls_model->getEGCampaignQuestion($bedSizeFilter);
                if(!empty($bedSizeOptions)){
                    $data['bedSizeOptions'] = explode('|', $bedSizeOptions[0]->options);
                }
                $data['state'] = get_states_array();
            }
            
            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/contacts/index/'.$campaignID.'/'.$list_id.'">Contacts</a> > Create';
            if(empty($old_contact_id)){
                $data['meta_title'] = 'Create Contact';
            }else{
                $data['meta_title'] = 'Edit Contact';
            }
            $data['title'] = 'Create Contact';
            $data['main'] = 'dialer/contacts/create'.$this->fileAppend;
            $this->load->vars($data);
            $this->load->view('layout');
        } else {
            $viewListData = (object)$this->input->post();
            $data['campaign_id'] = $campaignID;
            $data['list_id'] = $list_id;
            if(empty($old_contact_id)){
                if($this->app == 'eg'){
                    $email = SQLInjectionOFEGDB($_POST['email']);
                    $isexist = $this->Contacts_model->EmailContactDetails($email);
                    //$isexist =  $this->Contacts_model->isContactExist($data);
                    if(!empty($isexist)){
                        $viewListData->id = $isexist['id'];
                        $data['id']= $isexist['id'];
                    }
                }else if($this->app == 'mpg'){
                    $data['email'] = $_POST['email'];
                    $getExist =  $this->Contacts_model->isContactExistMpg($data);
                    $isexist = ($getExist) ? $getExist[0]: 0;
                    $viewListData->id = ($getExist) ? $getExist[1]: 0;
                }
            }
            
            if($isexist == 1){                
                $data['id'] = !empty($old_contact_id) ? $old_contact_id : $viewListData->id;
                //Set Contact object properties
                $func = 'setContactObjectData'. (!empty($this->fileAppend) ? ucfirst($this->app) : '');
                $individualContactCallDetail = $this->$func();
                unset($individualContactCallDetail->member_id);
                unset($individualContactCallDetail->created_at);
                $individualContactCallDetail->updated_by = $loggedUserID;
                $newContactCallID = $this->Contacts_model->update_contact($old_contact_id, $individualContactCallDetail);
                $newContactCallID = $data['id'];
                $class = 'good';
                //$msg = '"'.$viewListData->id.'" '. strtoupper($this->app).' Contact ID already Exists in contacts list; Contact Added successfully in this Campaign!';
                $msg = 'Contact updated and added to Campaign successfully!';
            } // if contact is  not exist in database 
            else{
                //Set Contact object properties
                $func = 'setContactObjectData'. (!empty($this->fileAppend) ? ucfirst($this->app) : '');
                $individualContactCallDetail = $this->$func();
                $individualContactCallDetail->created_at = date('Y-m-d H:i:s', time());
                //$individualContactCallDetail->id = $viewListData->id;
                $individualContactCallDetail->do_not_call_ever = 0;
                $individualContactCallDetail->original_owner = 'Pureb2b';
                $newContactCallID = $this->Contacts_model->insert_contact($individualContactCallDetail);                
                $class = 'good';
                $msg = 'Contact inserted and added to Campaign successfully!';
            }
            // insert contact in mapping table if contact id is not empty (0)
            $filter = array(
                'id' => $newContactCallID,
                'campaign_id' => $campaignID
            );
            $assignedToCampaign = $this->Contacts_model->campaign_contact_id($filter);
            if(empty($assignedToCampaign)){
                if ($newContactCallID > 0) {
                    $contact_lists_data = array();
                    $contact_lists_data['contact_id'] = $newContactCallID;
                    $contact_lists_data['campaign_id'] = $campaignID;
                    $contact_lists_data['list_id'] = $list_id;
                    $contact_lists_data['source'] = 'form';
                    $contact_lists_data['created_by'] = $loggedUserID;
                    $contact_lists_data['created_at'] = date('Y-m-d H:i:s');
                    $this->Contacts_model->insert_contact_lists($contact_lists_data);
                } else {
                    $class = 'bad';
                    $msg = 'Sorry, an error has occurred.';
                }
            }
            $this->session->set_flashdata('class', $class);
            $this->session->set_flashdata('msg', $msg);
            if(!empty($old_contact_id)){
                redirect('/dialer/contacts/create/' . $campaignID.'/'.$list_id . '/?cid=' . $old_contact_id);
            }else{
                redirect('/dialer/contacts/index/' . $campaignID.'/'.$list_id);
            }
            
        }
    }

    // Check contact email exist or not
    public function contacts_email_exists(){
        if(!empty($_POST['email'])){
            $email = $_POST['email'];
        }else{
            $data['message'] = "Please enter email.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }

        if(!empty($_POST['contact_id'])){
            $isEmailExist = $this->Contacts_model->checkContactEmailExist($email,$_POST['contact_id']);
        }else{
            $isEmailExist = $this->Contacts_model->email_exists($email);
        }

        if($isEmailExist){
            $data['message'] = "Sorry, Email already exist.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }else{
            $data['status'] = true;
            echo json_encode($data);
            exit();
        }
    }
    
    public function edit($contactID = null, $campaign_id = null, $list_id = null)
    {
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        $userReadonly = $this->session->userdata('user_readonly');
        
        //Set Validation Rules
        $this->contactFormValidation($this->input->post());

        if ($contactID > 0) {

            if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed

                $this->load->model('Campaigns_model');
                $campaignsModel = new Campaigns_model();

                //Validate that campaign is assign to agent
                $this->campaign_is_assign_or_not_by_user_type($campaign_id, $user_type, $campaignsModel, $loggedUserID);

                $campaign = $campaignsModel->getCampaignDetailByID($campaign_id);
                if (empty($campaign)) {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
                    redirect('/dialer/campaigns');
                }
                // check logged user is authorized for access this page
                $this->check_authorized_tm_office($user_type,$campaign);

                // checking same user can not access multiple contact at same time.
                $this->load->model('Calls_model');
                $callsModel = new Calls_model();

                // checking same user can not access multiple contact at same time.
                $check_multiple_lock_contact = $this->Contacts_model->check_multiple_lock_contact($loggedUserID, $contactID);
                //$check_multiple_lock_contact = $callsModel->get_previous_lock_detail_by_logged_user($campaign_id,$loggedUserID);

                if ($check_multiple_lock_contact['lock_contact_count'] >= 1 && $check_multiple_lock_contact['contact_id'] > 0) {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Unable to save - previous lead is still active and locked. please unlock previous contact, <a class="btn btn-success" style="text-decoration: underline;" href="/dialer/calls/unlock_previous_contact/'.$campaign_id.'/'.$list_id.'/'.$check_multiple_lock_contact['contact_id'].'">'.$check_multiple_lock_contact['contact_id'].'</a>');
                        redirect('/dialer/contacts/index/' .$campaign_id.'/'.$list_id);
                }

                // get contact Information
                $contactDetail = $this->Contacts_model->get_one_contact($contactID);
                $contactDetail->campaign_id = $campaign_id;
                $contactDetail->list_id = $list_id;
                                
                // check  lock/unlock contact 
                if ($contactDetail->edit_lead_status == 0 && empty($userReadonly)) {
                    //no one is working on this contact
                        $edit_call['edit_lead_status'] = '1';
                        $edit_call['locked_by'] = $loggedUserID;
                        $this->Contacts_model->update_contact($contactDetail->id, $edit_call);   
                }else if($contactDetail->edit_lead_status == 1 && $loggedUserID!=$contactDetail->locked_by && empty($userReadonly)) {
                    //if contact is locked
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Some other user is accessing this details');
                    redirect('/dialer/contacts/index/' . $campaign_id.'/'.$list_id);
                }

                $countryList = $this->Contacts_model->get_countries();
                $data['countries'] = $countryList;
                
                // Fetch Job Filters from Helper File
                $data['jobLevelValues'] = getJobLevelValues();
                $data['jobFunctionValues'] = getJobFunctionValues();
                $data['companySizeValues'] = getCompanySizeValues();
                $data['industriesValues'] = getIndustriesValues();

                $data['contactDetail'] = $contactDetail;
                $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/contacts/index/' . $contactDetail->list_id . '">Contacts</a> > Edit';
                $data['meta_title'] = 'Edit Contact';
                $data['title'] = 'Edit Contact';
                $data['main'] = 'dialer/contacts/edit';
                $this->load->vars($data);
                $this->load->view('layout');
            } else {
                if (isset($_POST['btncancel']) && $_POST['btncancel'] != "") {
                    // unlock Contact & update new changes 
                    $edit_call['edit_lead_status'] = '0';
                    $edit_call['locked_by'] = '';
                    $this->Contacts_model->update_contact($contactID, $edit_call);
                } else {
                    //Set Contact object properties
                    $edit_call = $this->setContactObjectData();

                    $edit_call->edit_lead_status = '0';
                    $edit_call->locked_by = '';
                    $edit_call->updated_by = $loggedUserID;
                    $checkContactEmailExist = $this->Contacts_model->checkContactEmailExist($edit_call->email, $contactID);
                    unset($edit_call->created_at);

                    if ($checkContactEmailExist == 0) {
                        $update = $this->Contacts_model->update_contact($contactID, $edit_call);
                        if ($update) {
                            if(!empty($_POST['member_id'])){
                                $this->load->model('Members_model');
                                $membersModel = new Members_model();
                                $normalization_rules = $membersModel->get_member_normalization_rules();
                                //update if there's members_qa record
                                //Set member object properties
                                $member = new Member();
                                $num_fields = 0;
                                $num_fields = $this->setMemberObjectDataEg($_POST, $member, $num_fields);

                                if (isset($member->job_title)) {
                                    $this->load->library('Normalize');
                                    $normalize = new Normalize();
                                    $member->job_level = $normalize->job_level($_POST['job_title'], $normalization_rules);
                                    $member->silo = $normalize->silo($_POST['job_title'], $normalization_rules);
                                    $member->ml_title = $normalize->ml_title($_POST['job_function'],$_POST['job_level']);
                                }

                                if ($num_fields > 0) {
                                    $member->id = $_POST['member_id'];
                                    $member->ip = $_SERVER["REMOTE_ADDR"];
                                    $member->phone_verified = 1;
                                    $member->updated_at = date('Y-m-d H:i:s', time());
                                    $member->updated_by = $this->session->userdata('uid');

                                    //Try to insert new member to DB

                                    $result = $membersModel->update_member_from_tm_qa($member);

                                }
                                $membersModel->update_member_from_tm_qa($member);
                            }
                            $this->session->set_flashdata('class', 'good');
                            $this->session->set_flashdata('msg', 'Contact Updated successfully!');
                        } else {
                            $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
                        }
                    } else {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, an email already exist.');
                    }
                }
                redirect('/dialer/contacts/index/' . $campaign_id.'/'.$list_id);
            }

        } else {
            echo "show 404 error page";
            exit;
        }

    }
    
    /**
     * @param $post_data
     * @param $member
     * @param $num_fields
     * @return mixed
     */
    public function setMemberObjectDataEg($post_data, $member, $num_fields)
    {
        foreach ($post_data as $key => $value) {

            switch (strtolower(trim($key))) {
                case 'email':
                    $member->email = $value;
                    $num_fields++;
                    break;
                case 'first_name':
                    $member->first_name = $value;
                    $num_fields++;
                    break;
                case 'last_name':
                    $member->last_name = $value;
                    $num_fields++;
                    break;
                case 'address':
                    $member->address1 = $value;
                    $num_fields++;
                    break;
                case 'city':
                    $member->city = $value;
                    $num_fields++;
                    break;
                case 'state':
                    $member->state = $value;
                    $num_fields++;
                    break;
                case 'zip':
                    $member->zip = $value;
                    $num_fields++;
                    break;
                case 'country':
                    $member->country = $value;
                    $num_fields++;
                    break;
                case 'phone':
                    // save phone no. without country code
                    $member->phone = substr($value, strlen($post_data['phone']));//$value;
                    $num_fields++;
                    break;
                case 'company': // Manually Added
                    $member->company_name = $value;
                    $num_fields++;
                    break;
                case 'job_title': // Manually Added
                    $member->job_title = $value;
                    $num_fields++;
                    break;
                case 'job_level':
                    $member->job_level = $value;
                    $num_fields++;
                    break;
                case 'industry': // Manually Added
                    $member->industry = $value;
                    $num_fields++;
                    break;
                case 'company_size': // Manually Added
                    $member->company_size = $value;
                    $num_fields++;
                    break;
                case 'company_revenue': // Manually Added
                    $member->company_revenue = $value;
                    $num_fields++;
                    break;
                case 'source':
                    $member->source = ($value == 'call_file' ? 'tm_call_file' : $value);
                    break;
            }
        }

        return $num_fields;
    }

    public function getContacts($id = null,$ajax=1,$contactFilter =0,$isContactListPage=0, $list_id)
    {
        // set pagination & sorting filters 
        if(isset($_POST['page'])){$page = $_POST['page']; }else{$page = 1;}
        if(isset($_POST['rows'])){$limit = $_POST['rows']; }else {$limit = 20;}
       
        
        if(!empty($contactFilter) && $this->session->contactdata('sortfield')){
            $sidx = $this->session->contactdata('sortfield');
        }else if(isset($_POST['sidx'])){
            $sidx = $_POST['sidx'];    
            $this->session->set_contactdata("sortfield",$sidx);
        }
        else if(!$isContactListPage){           
            $sidx = " cl.updated_at ASC,FIELD (cl.filter_status,'1') DESC , tlh.status,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST'), 
            ISNULL(cd.calldisposition_name),cd.calldisposition_name,FIELD(cl.source,'api','call_file'),c.id";            
        }else{
            $sidx = " FIELD (cl.filter_status,'1') DESC , FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST'),FIELD(cl.source,'api','call_file') ASC,c.id";
        }
        
	if(!empty($contactFilter) && $this->session->contactdata('sortindex')){
            $sord = $this->session->contactdata('sortindex');            
        }else if(isset($_POST['sord'])){
            $sord = $_POST['sord']; 
            $this->session->set_contactdata("sortindex",$sord);
            $this->session->set_contactdata('ContactFilter',1);
        }else {
            $sord = "asc";            
        }
        if($contactFilter){
            $this->session->set_contactdata($sidx,$sord);
        }
      
        $where="";
        
        if (!empty($this->session->set_contactdata->rules) && (!empty($contactFilter))) {
            $where = $this->getFilterQuery($this->session->set_contactdata, $isContactListPage,$list_id);
        } else if (isset($_POST['filters'])) {
            $searcharray = json_decode($_POST['filters']);

            if (empty($isContactListPage)) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $this->session->set_contactdata = $searcharray; 
                session_write_close(); 
                $this->session->set_contactdata('ContactFilter', 1);
            }
            $where = $this->getFilterQuery($searcharray, $isContactListPage,$list_id);
        }   

        // filter with selected call disposition
        $call_disposition_filter_flag = false;
        
        if (isset($_POST['filters'])) {
            $filters_calldisposition = json_decode($_POST['filters']);
        
            $call_disposition_filter_data = array_values(array_filter($filters_calldisposition->rules, function ($elem) {
                if (isset($elem->field)) {
                    return "call_disposition_id" == $elem->field;
                }
            }));

            if (!empty($call_disposition_filter_data)) {
                $call_disposition_filter_flag = true;
            }
        } else {
            if(!empty($this->session->set_contactdata->rules)){
                foreach($this->session->set_contactdata->rules as $filter_rules) {
                    if($filter_rules->field == 'call_disposition_id') {
                        $call_disposition_filter_data = array('call_disposition_id' => $filter_rules->data);
                        $call_disposition_filter_flag = true;
                    }
                }
            }
        }
        // filter with selected lead status
        $lead_status_filter_flag = false;
        if (isset($_POST['filters'])) {
            $filters_lead_status = json_decode($_POST['filters']);

            $lead_status_filter_data = array_values(array_filter($filters_lead_status->rules, function ($elem) {
                if (isset($elem->field)) {
                    return "status" == $elem->field;
                }
            }));

            if (!empty($lead_status_filter_data)) {
                $lead_status_filter_flag = true;
            }
        } else {
            if(!empty($this->session->set_contactdata->rules)){
                foreach($this->session->set_contactdata->rules as $filter_rules) {
                    if($filter_rules->field == 'status') {
                        $lead_status_filter_data = array('status' => $filter_rules->data);
                        $lead_status_filter_flag = true;
                    }
                }
            }
        }

        if (!$sidx) $sidx = 1;
        $userType = $this->session->userdata('user_type');

        if ($isContactListPage) {
            $count = $this->Contacts_model->get_all_contacts($id, $where, 1, $list_id);
        } else if($list_id == 'auto' && $this->config->item('auto_dialer_toggle')) {
            $count = $this->Contacts_model->getAutoContactsCount($id, $userType, $where, $call_disposition_filter_flag, $lead_status_filter_flag);
        } else {
            $count = $this->Contacts_model->get_workable_contacts_count($id, $userType, $where, $call_disposition_filter_flag, $lead_status_filter_flag, $list_id);
        }
        // calculation of pagination with the help of contact counts 
        if ($count > 0 && $limit > 0) {
            $total_pages = ceil($count / $limit);
        } else { 
        $total_pages = 0; 
        } 

        if ($page > $total_pages) $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0) $start = 0;
        if ($isContactListPage) {
            $response = $this->Contacts_model->get_all_contacts($id, $where, 0, $list_id, $sidx, $sord, $start, $limit);
        } else if($list_id == 'auto' && $this->config->item('auto_dialer_toggle')) {
            $response = $this->Contacts_model->getAutoContacts($id, $userType, $where, $sidx, $sord, $start, $limit, $call_disposition_filter_flag, $lead_status_filter_flag);
        } else {
            $response = $this->Contacts_model->get_list($id, $userType, $where, $sidx, $sord, $start, $limit, $call_disposition_filter_flag, $lead_status_filter_flag, $list_id);
        }   

        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;
        $response->filterRules = array();
        if( !empty( $this->session->set_contactdata->rules ) ) {
            foreach( $this->session->set_contactdata->rules as $k => $filter_rule ) {
                $nw_filter_rules[$filter_rule->field] = str_replace("\'", "'", $filter_rule->data);
                $response->filterRules = json_encode($nw_filter_rules);
            }
        }
        if ($ajax == 1) {
            echo json_encode($response);
        } else {
            return json_encode($response);
    }
    }

    // Non- workable contact dispositions
    public function get_non_workable_contacts($id = null, $ajax = 1, $contactFilter = 0,$list_id)
    {
        // set pagination & sorting filters
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
        } else {
            $page = 1;
        }
        if (isset($_POST['rows'])) {
            $limit = $_POST['rows'];
        } else {
            $limit = 50;
        }
        
        if( !empty( $contactFilter ) && $this->session->tm_nw_sidx ){
            $sidx = $this->session->tm_nw_sidx;
        }else if( isset( $_POST['sidx'] ) ){
            $sidx = $_POST['sidx'];
            $this->session->set_userdata( "tm_nw_sidx", $sidx );
        }else {
            $sidx = "FIELD (cl.filter_status,'1') DESC ,tlh.status,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST') , ISNULL(cd.calldisposition_name), cd.calldisposition_name ,FIELD(cl.source,'api','call_file') ASC,c.id";
        }
        
        if( !empty( $contactFilter ) && $this->session->tm_nw_sord ){
            $sord = $this->session->tm_nw_sord;
        }else if( isset( $_POST['sord'] ) ) {
            $sord = $_POST['sord'];
            $this->session->set_userdata( "tm_nw_sord", $sord );
        } else {
            $sord = "asc";
        }
        
        $where = "";
        
        if( !empty( $this->session->set_tmnwcontactdata->rules ) && !empty( $contactFilter ) ) {
            $where = $this->getFilterQuery( $this->session->set_tmnwcontactdata );
        }else if( isset( $_POST['filters'] ) ){
            $searcharray = json_decode( $_POST['filters'] );

            $where = $this->getFilterQuery( $searcharray );
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $this->session->set_tmnwcontactdata = $searcharray;
            session_write_close(); 
        }
        
        if (!$sidx) $sidx = 1;
        $userType = $this->session->userdata('user_type');
        $logged_user_id = $this->session->userdata('uid');

        $count = $this->Contacts_model->get_non_workable_count($id, $logged_user_id, $where, $list_id);

        // calculation of pagination with the help of contact counts
        if ($count > 0 && $limit > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0) $start = 0;
        //$response = new stdClass();
        $response = $this->Contacts_model->get_non_workable_list($id, $logged_user_id, $where, $sidx, $sord, $start, $limit, $list_id);

        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;
        
        //filters for non-workable dispo
        $response->filterRules = array();
        if( !empty( $this->session->set_tmnwcontactdata->rules ) ) {
            foreach( $this->session->set_tmnwcontactdata->rules as $k => $filter_rule ) {
                $nw_filter_rules[$filter_rule->field] = $filter_rule->data;
                $response->filterRules = json_encode($nw_filter_rules);
            }
        } 
        
        if ($ajax == 1) {
            echo json_encode($response);
        } else {
            return json_encode($response);
        }
    }

    // Common Vaditation Funciton 
    public function contactFormValidation($postDataValue)
    {
        $this->form_validation->set_rules('first_name', 'first_name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'last_name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|max_length[255]|valid_email');
        $this->form_validation->set_rules('phone', 'phone', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('country', 'country', 'required|trim');

        $this->form_validation->set_error_delimiters('<div class="validation_error"><ul><li>', '</li></ul></div>');
    }

    // Common Set Contact Object Funciton for UBER DATABASE   
    public function setContactObjectData()
    {
        $call = new ContactsTable();
        $viewListData = (object)$this->input->post();
        if (!empty($viewListData->member_id))
            $call->member_id = $viewListData->member_id;
        if (!empty($viewListData->locked_by))
            $call->updated_by = $viewListData->locked_by;
        if (!empty($viewListData->first_name))
            $call->first_name = $viewListData->first_name;
        if (!empty($viewListData->last_name))
            $call->last_name = $viewListData->last_name;
        if (!empty($viewListData->email))
            $call->email = $viewListData->email;
        if (!empty($viewListData->phone))
            $call->phone = $viewListData->phone;
        if (!empty($viewListData->alternate_no))
            $call->alternate_no = $viewListData->alternate_no;
        if (!empty($viewListData->job_title))
            $call->job_title = $viewListData->job_title;
        if (!empty($viewListData->job_level))
            $call->job_level = $viewListData->job_level;
        if (!empty($viewListData->job_function)) {
            $call->job_function = $viewListData->job_function;
        }
        if (!empty($viewListData->company))
            $call->company = $viewListData->company;
        if (!empty($viewListData->address))
            $call->address = $viewListData->address;
        if (!empty($viewListData->city))
            $call->city = $viewListData->city;
        if (!empty($viewListData->zip))
            $call->zip = $viewListData->zip;
        if (!empty($viewListData->state))
            $call->state = $viewListData->state;
        if (!empty($viewListData->country))
            $call->country = $viewListData->country;
        if (!empty($viewListData->industry))
            $call->industry = $viewListData->industry;
        if (!empty($viewListData->company_size))
            $call->company_size = $viewListData->company_size;
        if (!empty($viewListData->notes))
            $call->notes = $viewListData->notes;
        if (!empty($viewListData->time_zone))
            $call->time_zone = strtoupper($viewListData->time_zone);
        if (!empty($viewListData->state))
            $call->state = $viewListData->state;
        if (!empty($viewListData->priority))
            $call->priority = $viewListData->priority;

        $call->edit_lead_status = '0';
        $call->updated_at = date('Y-m-d H:i:s', time());
        return $call;
    }
    
    // Common Set Contact Object Funciton for UBER DATABASE   
    public function setContactObjectDataMpg()
    {
        $call = new ContactsTableMpg();
        $viewListData = (object)$this->input->post();
        
        foreach($viewListData as $key => $value){
            if(property_exists($call, $key)){
                $call->$key = $value;
            }
        }        
        $call->edit_lead_status = '0';
        $call->created_at = date('Y-m-d H:i:s', time());
        return $call;
    }

    // Lock/Unlock Function called in Edit contact page
    public function lockEditContact($status, $id = null)
    {
        $edit_call['edit_lead_status'] = $status;
        $edit_call['locked_by'] = "";
        $updateLock = $this->Contacts_model->update_contact($id, $edit_call);
        if ($updateLock) {
            echo json_encode($updateLock);
            exit();
        }
    }

    // delete contact   
    public function delete($id)
    {
        $is_deleted = $this->Contacts_model->delete($id);
        if ($is_deleted) {
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Contact Deleted successfully!');

        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
        }
    }
    
    // unlock contact
    public function unLockContact($campaignContactID=null){
        $isQalead = $this->Contacts_model->CheckIsQALead($campaignContactID);
        if($isQalead->lead_id){
            $lead_data['status']= 'Pending';
            $lead_data['qa'] = NULL;
            $lead_data['is_qa_in_progress']= 0;
            $updateLeadStatus =  $this->Contacts_model->update_lead($isQalead->lead_id, $lead_data);
        }        
        $data['edit_lead_status']= '0';
        $data['locked_by'] = '';
        $updateLock =  $this->Contacts_model->update_contact($isQalead->contact_id, $data);
        if ($updateLock) {
            echo json_encode($updateLock);
            exit();
        }
    }
    
    // call from  getContacts() to set query filters
    public function getFilterQuery($filters,$isContactListPage = 0)
    {
       
        $where_cond="";
              
        foreach($filters->rules as $k=>$cond)
        {
            if ($where_cond!="") {
                $where_cond.=" ".$filters->groupOp." ";
            }
            if ($cond->field == "full_name") {
                $where_cond .='(CONCAT(c.first_name," ",c.last_name)';
            }
            else if($cond->field == "agent_name" && (empty($isContactListPage))) {
                $where_cond .='(u.first_name';
            } else if($cond->field == "phone") {
                $where_cond .= 'c.phone';
            } else {
                if ($cond->field == "status" && (empty($isContactListPage))) {
                    $where_cond .= "tlh.";
                }
                if ($cond->field == "call_disposition_id" && (empty($isContactListPage))) {
                    $where_cond .= "cl.";
                }
                $where_cond .= $cond->field;
                
            }
            if ( !empty($cond->data)) {
                $cond->data = addslashes($cond->data);
            }
            if ($cond->op == "cn") {
                if ($cond->field != "agent_name" && $cond->field != "time_zone") {
                   $where_cond .= " like '".$cond->data."%'";
                }
                if ($cond->field == "full_name") {
                    $where_cond .=   "OR CONCAT(c.first_name,' ',c.last_name) like '%".$cond->data."%' OR c.first_name like '%".$cond->data."%' OR c.last_name like '%".$cond->data."%')";
                }
                
                if ($cond->field=="agent_name" && (empty($isContactListPage))) {
                    $where_cond .= " like '%".$cond->data."%' OR u.last_name like '%".$cond->data."%' OR CONCAT(u.first_name,' ',u.last_name) like '".$cond->data."%')";
                }
                if ($cond->field == "time_zone") {
                    $where_cond .= " = '".$cond->data."'";
                }
            } else {
                $where_cond .= " = '".$cond->data."'";
            }
            
            //if($cond->field=="status")
        }
        return $where_cond;
    }
    
    // Delete campaign wise contacts ( call from view campaign page by clicking 'Clear List')   
    public function delete_campaign_contacts($campaign_id)
    {
        $is_deleted = $this->Contacts_model->remove_contact_list_data($campaign_id);
        if ($is_deleted) {
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Campaign Contact Deleted successfully!');
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
}
        redirect('dialer/campaigns/view/' . $campaign_id);
    }
    
    /*
     *  this function is used to delete filtered contact(s).
     *  call from view campaign page by clicking 'Edit'
     */
    
    function edit_campaign_contacts($campaign_id, $list_id, $page_num = 1, $sortField = 'priority', $order = 'asc')
    {
        $check_event_action = $this->uri->segment('6');
        $user_type = $this->session->userdata('user_type');
        $isAuthorized = IsTLManagerUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        
        $this->load->model('Lists_model');
        $listModel = new Lists_model();
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
	if(!empty($check_event_action) && $check_event_action == 'clear'){
            $campaign_filter = array("contact_filter"=>'');
            $listModel->updateCampaignList_filterByID($campaign_id,$list_id,$campaign_filter);
            $pre_filter_status_data = array("filter_status"=>'0');
            $campaignsModel->update_campaign_contact($pre_filter_status_data,$campaign_id,$list_id);
        }
		 
  
        $searchBy = $this->input->post();
  
        if(!empty($_POST['save_filter'])){
            $edit_campaign_filter_by = "";
            if(!empty($searchBy['company_size'])){
                $companySize = implode("','",$searchBy['company_size']);
                $edit_campaign_filter_by .= "company_size:".$companySize.'|';

            }
            if(!empty($searchBy['job_function'])){
                $job_function = implode("','",$searchBy['job_function']);
                $edit_campaign_filter_by .= "job_function:".$job_function.'|';
            }
            if(!empty($searchBy['job_level'])){
                $job_level = implode("','",$searchBy['job_level']);
                $edit_campaign_filter_by .= "job_level:".$job_level.'|';
            }

            if(!empty($searchBy['industry'])){
                $industry = implode("',  '",$searchBy['industry']);
                $edit_campaign_filter_by .= "industry:".$industry.'|';
            }
            if(!empty($searchBy['country'])){
                $country = implode("','",$searchBy['country']);
                $edit_campaign_filter_by .= "country:".$country;
            }

            $edit_campaign_filter_by = rtrim($edit_campaign_filter_by,'|');

            $edit_campaign_filter_data = new stdClass();
            if(empty($edit_campaign_filter_by)){
                $edit_campaign_filter_by = null;
            }
            $edit_campaign_filter_data->contact_filter = $edit_campaign_filter_by;

            //$campaignsModel->updateCampaign_filterByID($campaign_id,$edit_campaign_filter_data);
            $listModel->updateCampaignList_filterByID($campaign_id,$list_id,$edit_campaign_filter_data);

            //first update previous filter status from 1 -> 0
            $pre_filter_status_data = array("filter_status"=>'0');
            $campaignsModel->update_campaign_contact($pre_filter_status_data,$campaign_id,$list_id);

            //update filter status as per post filter
            $filter_response  =$this->Contacts_model->update_campaign_contacts_filter($campaign_id,$list_id,$searchBy,$edit_campaign_filter_by);
           if(!$filter_response){
               $this->session->set_flashdata('class', 'bad');
               $this->session->set_flashdata('msg', 'Oops Something went wrong while you save list!');
               redirect('/dialer/campaigns/');
           }

        }

         $campaignData = $listModel->get_campaign_listdata($campaign_id,$list_id);
       
        if (empty($campaignData)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/campaigns/');
        }
        // check logged user is authorized for access this page
        $this->check_authorized_tm_office($user_type,$campaignData);
        // fetch data to job dropdown filter from  
        $data['jobLevelValues'] = getJobLevelValues();

        $data['jobFunctionValues'] = getJobFunctionValues();
        $data['companySizeValues'] = getCompanySizeValues();
        $data['industriesValues'] = getIndustriesValues();

        $data['countryValues'] = get_countries_array();

        if(!empty($campaignData->contact_filter) && empty($_POST) && $check_event_action!='clear'){

            $contact_filter_explode_array = explode('|', $campaignData->contact_filter);

            foreach($contact_filter_explode_array as $key=>$contact_filter_value){
                if(!empty($contact_filter_value)){
                    $contact_filter_label_key = explode(':',$contact_filter_value);
                    if($contact_filter_label_key[0] == 'industry'){
                        $filter_inner_value =  explode(',  ', str_replace("'","", $contact_filter_label_key[1]));
                    }else{
                        $filter_inner_value =  explode(',', str_replace("'","", $contact_filter_label_key[1]));
                    }
                       
                    $_POST[$contact_filter_label_key[0]] = $filter_inner_value;
                }
            }
        }
        
        $searchBy = $this->input->post();
        if(!empty($_POST['country'])){ $data['srh_country'] = $this->Contacts_model->getCountryName(implode(",",$_POST["country"])); }
        // pagination valiable initialization 
        $recs_per_page = 100;
        $page_number = (int)$this->input->get('per_page', TRUE);
        if (empty($page_number)) $page_number = 1;
        $offset = (int)$this->input->get('per_page', TRUE);
        $tot_records = $this->Contacts_model->getCampaignContacts($campaign_id,$list_id, $IsNumRecord = 1, $searchBy,"","","","",0,0);

        $data['campaignData'] = $campaignData;
        $data['list_id'] = $list_id;
        $data['contactsdata'] = $this->Contacts_model->getCampaignContacts($campaign_id,$list_id, "", $searchBy, $recs_per_page, $offset, $sortField, $order,0,0);
        $data['num_recs'] = $tot_records;

       
       // $campaign_filter_data = $campaignsModel->get_one($campaign_id);
        if(!empty($campaignData->contact_filter)){
            $data['contact_filter'] = $campaignData->contact_filter;
        }

       if(!empty($check_event_action) && $check_event_action == 'clear'){
            $_POST = "";
       }

        $this->load->library('pagination');
        
        $config['base_url'] = '/dialer/contacts/edit_campaign_contacts/' . $campaign_id .'/'.$list_id;
        $data['base_url'] = $config['base_url'];
        $config['total_rows'] = $tot_records;
        $config['per_page'] = $recs_per_page;
        $config['page_query_string'] = TRUE;
        $config['full_tag_open'] = '<div class="dataTables_paginate paging_bootstrap pagination"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;
        $config['prev_link'] = 'Previous';
        $config['prev_tag_open'] = '<li class="prev{class}">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="next{class}">';
        $config['next_tag_close'] = '</li>';
        $config['next_link'] = 'Next';
        $config['display_prev_link'] = TRUE;
        $config['display_next_link'] = TRUE;
        $config['num_links'] = 7;
        $this->pagination->cur_page=$offset;
        $this->pagination->initialize($config);
        $data['page_links']=$this->pagination->create_links();
		
	$data['num_pages'] = ceil($tot_records / $recs_per_page);
        $data['current_page'] = ($offset / $recs_per_page) + 1;
        $data['offset'] = $offset;
		
	$data['meta_title'] = 'Edit Contacts';
        $data['title'] = 'Edit Contacts';
        $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > <a href="/dialer/lists/index/'.$campaign_id.'">List Management</a> > Edit Contacts';
        $data['main'] = 'dialer/contacts/filter_campaign_contacts';
		
        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    // delete filter contact(s)
    public function delete_selected_contacts()
    {
        $is_deleted = $this->Contacts_model->remove_edit_contact_data($_POST);
        if ($is_deleted) {
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Campaign Contact Deleted successfully!');
            $data['status'] = true;  
        } else {
            $data['message'] = "Sorry, an error has occured.";
            $data['status'] = false;  
        }
        echo json_encode($data);
        exit();       
    }
    
    public function contactlist($id = null, $list_id,$agentSigninStatus = null)
    {
        $loggedUserID = $this->session->userdata('uid');
        $user_type = $this->session->userdata('user_type');
        
        $isAuthorized = IsTLManagerQAUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        //check for campaign id in url
        if (empty($id)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please select campaign.');
            redirect('/dialer/campaigns/');
        }
         
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
	
        // Get Campaign Information	 
        $campaignData = $campaignsModel->get_one($id);
	
        //validation to check that campaign is available in campaign table
        if (empty($campaignData)) {
           $this->session->set_flashdata('class', 'bad');
           $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/campaigns');
        } 
        // check logged user is authorized for access this page
        $this->check_authorized_tm_office($user_type, $campaignData);
        $data['meta_title'] = 'Contact List';
        $data['title'] = 'Contact List';
        $this->crumbs .= '> <a href="/dialer/campaigns">Campaigns</a> > Contact List';
        $data['crumbs'] = $this->crumbs;

        $data['main'] = 'dialer/contacts/contactlist';
        $data['listId'] = $list_id;

        $data['campaignData'] = $campaignData;
        	
        $data['contacts'] = $this->getContacts($id, 0, 0, 1,$list_id);

        $this->load->vars($data);
        $this->load->view('layout');
    }

    /**
     * @param $user_type
     * @param $campaignData
     */
    private function check_authorized_tm_office($user_type, $campaignData)
    {
        if (!in_array($user_type, $this->config->item('upper_management_types')) && $user_type != 'qa') {
            $string_to_array = explode(',', $campaignData->telemarketing_offices);

            if (!empty($campaignData) && !in_array($this->session->userdata('telemarketing_offices'), $string_to_array)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This Campaign is not in your TM office, You can not access it!');
                redirect('/dialer/campaigns');
}
        }
    }

    /**
     * @param $id
     * @param $user_type
     * @param $campaignsModel
     * @param $loggedUserID
     */
    private function campaign_is_assign_or_not_by_user_type($id, $user_type, $campaignsModel, $loggedUserID)
    {
        if($user_type == "agent" || $user_type == "team_leader"){
            $isCampaignAssign = in_array($id,$this->session->userdata('assigned_campaign_ids')); 
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have not been assigned this campaign and contacts, please contact to administrator!');
                redirect($this->app_module_name . '/campaigns');
            }
        }    
        /*if ($user_type == 'agent') {
            $isCampaignAssign = $campaignsModel->IsCampaignAssignToAgent($id, $loggedUserID);
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have not been assigned this campaign and contacts, please contact to administrator!');
                redirect($this->app_module_name . '/campaigns');
            }
        } else if ($user_type == 'team_leader') {
            $isCampaignAssign = $campaignsModel->IsCampaignAssignToTL($id, $loggedUserID);
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have not assigned this campaign, please contact to administrator!');
                redirect($this->app_module_name . '/campaigns');
            }
        } */
        }
    
    
    public function contact_email_exist(){
        if(!empty($_POST['email'])){
            $email = SQLInjectionOFEGDB($_POST['email']);
            $contact_email_exist = $this->Contacts_model->EmailContactDetails($email);
            if($contact_email_exist){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Contact already exists in our system!');
                $return = array('contact_id' => $contact_email_exist['id']); 
            }else{
                $return = array('message' => 'Email: ' . $email . ' does not exist.', 'status' => false);
            }
        }else{
            $return = array('message' => 'Please enter email.', 'status' => false); 
        }
        
        echo json_encode($return);exit;
    }
    
    // get contact by disposition

    public function getContactByDisposition($id = null, $ajax = 1, $contactFilter = 0, $list_id, $disposition_id)
    {
        // set pagination & sorting filters
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
        } else {
            $page = 1;
        }
        if (isset($_POST['rows'])) {
            $limit = $_POST['rows'];
        } else {
            $limit = 50;
        }
        
        if( !empty( $contactFilter ) && $this->session->tm_callback_sidx ){
            $sidx = $this->session->tm_callback_sidx;
        }else if( isset( $_POST['sidx'] ) ){
            $sidx = $_POST['sidx'];
            $this->session->set_userdata( "tm_callback_sidx", $sidx );
        }else {
            $sidx = "FIELD (cl.filter_status,'1') DESC ,tlh.status,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST') , ISNULL(cd.calldisposition_name), cd.calldisposition_name ,FIELD(cl.source,'api','call_file') ASC,c.id";
        }
        
        if( !empty( $contactFilter ) && $this->session->tm_callback_sord ){
            $sord = $this->session->tm_callback_sord;
        }else if( isset( $_POST['sord'] ) ) {
            $sord = $_POST['sord'];
            $this->session->set_userdata( "tm_nw_sord", $sord );
        } else {
            $sord = "asc";
        }
        
        $where = "";
        
        if( !empty( $this->session->set_tmcallbackcontactdata->rules ) && !empty( $contactFilter ) ) {
            $where = $this->getFilterQuery( $this->session->set_tmcallbackcontactdata );
        }else if( isset( $_POST['filters'] ) ){
            $searcharray = json_decode( $_POST['filters'] );

            $where = $this->getFilterQuery( $searcharray );
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $this->session->set_tmcallbackcontactdata = $searcharray;
            session_write_close(); 
        }
        
        if (!$sidx) $sidx = 1;
        $userType = $this->session->userdata('user_type');
        $logged_user_id = $this->session->userdata('uid');

        $count = $this->Contacts_model->getContactsByDispositionIdCount($id, $logged_user_id, $where, $list_id, $disposition_id);

        // calculation of pagination with the help of contact counts
        if ($count > 0 && $limit > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0) $start = 0;
        //$response = new stdClass();
        $response = $this->Contacts_model->getContactsByDispositionId($id, $logged_user_id, $where, $sidx, $sord, $start, $limit, $list_id, $disposition_id);

        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;
        
        //filters for non-workable dispo
        $response->filterRules = array();
        if( !empty( $this->session->set_tmcallbackcontactdata->rules ) ) {
            foreach( $this->session->set_tmcallbackcontactdata->rules as $k => $filter_rule ) {
                $nw_filter_rules[$filter_rule->field] = $filter_rule->data;
                $response->filterRules = json_encode($nw_filter_rules);
            }
        } 
        
        if ($ajax == 1) {
            echo json_encode($response);
        } else {
            return json_encode($response);
        }
    }

    // retrieve contacts using phone number
    public function getCampaignContactsByPhone($phone, $campaignId, $listId, $excludedContactId, $ajax=true)
    {
        $filters = array();
        $filters[] = "cc.campaign_id = {$campaignId}";
        $filters[] = "cc.list_id = {$listId}";
        $filters[] = "(c.phone = '{$phone}' OR mq.phone = '{$phone}')";
        $filters[] = "c.id != {$excludedContactId}";
        $url = $this->config->item('base_url') . "/dialer/calls/index/";
        $fields = "concat(c.first_name,' ', c.last_name) as prospect, c.job_title, cc.id as campaign_contact_id,
            concat('{$url}','',cc.id,'/{$listId}') as url ";
        $limit = $ajax ? 3 : 0;
        $retrieve = $this->Contacts_model->getCampaigContactDetails($filters, $fields, $limit);

        if ($ajax) {
            echo !empty($retrieve) ? json_encode($retrieve) : false;
        } else {
            return $retrieve;
        }
        
    }

    public function viewAllOtherContacts($phone, $campaignId, $listId, $excludedContactId)
    {
        
        $retrieve = $this->getCampaignContactsByPhone($phone, $campaignId, $listId, $excludedContactId, 0);

        $data['viewAllOtherContacts'] = $retrieve;
        $data['meta_title'] = 'View All Other Contacts';
        if (!empty($retrieve)) {
           $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/contacts/index/' . $campaignId . '/'.$listId. '">Contacts</a> > ' . 'View All Other Contacts';
        } else {
            $data['crumbs'] = $this->crumbs;
        }
        $data['title'] = 'View Call History';
        $data['main'] = 'dialer/contacts/viewallothercontacts';
        $this->load->vars($data);
        $this->load->view('layout');
    }
    // Start HP UAD-66 : Caching
    // Function to do the process of verify/create cache
    public function cacheProcess($campId, $agentId)
    {
        $this->util->addLog('cache_log.txt', "\nCache log : in cache process");

        // Cache        
        $campDetails = $this->cache->memcached->get('uadCampDetails');
        $this->util->addLog("cache_log.txt", "\nCache log : Before cache array is : " . print_r($campDetails, 1));

        if ($campDetails != '') {
            // Cache exist
            $this->util->addLog("cache_log.txt", "\nCache log : Cache array exist...");

            // check for campaign id in cache
            $cacheCamps = array_keys($campDetails['campData']);
            
            if (in_array($campId, $cacheCamps)) {
                // Campaign already exist
                $this->util->addLog("cache_log.txt", "\nCache log : Campaign already exist...");

                if (! in_array($agentId, $campDetails['campData'][$campId]['agentIds'])) {
                    // Agent for that campaign do not exist
                    $this->util->addLog("cache_log.txt", "\nCache log : Just increament agentcount for that campaign");

                    // Just increament agentCount by 1
                    $campDetails['campData'][$campId]['agentCount'] = $campDetails['campData'][$campId]['agentCount'] + 1;
                    $campDetails['campData'][$campId]['agentIds'][] = $agentId;
                    $this->cache->memcached->save('uadCampDetails', $campDetails, 86400);
                    $this->util->addLog("cache_log.txt", "\nCache log : Cache array is : " . print_r($this->cache->memcached->get('uadCampDetails'), 1));
                }
            } else {
                // Campaign do not exist
                $this->util->addLog("cache_log.txt", "\nCache log : Campaign do not exist...");
                $this->util->addLog("cache_log.txt", "\nCache log : Add campaign to cache array");

                // Add new array of campaign to campDetails
                $campDetails['campData'][$campId] = array(
                    'agentCount' => 1,
                    'agentIds' => array($agentId)
                );
                $this->cache->memcached->save('uadCampDetails', $campDetails, 86400);
                $this->util->addLog("cache_log.txt", "\nCache log : Cache array is : " . print_r($this->cache->memcached->get('uadCampDetails'), 1));
            }
        } else {
            // Cache do not exist
            $this->util->addLog("cache_log.txt", "\nCache log : Cache array do not exist...");

            $this->util->addLog("cache_log.txt", "\nCache log : Before cache array create");
            // Create cache & cron start
            $campDetails = array(
                'campData' => array($campId => array('agentCount' => 1, 'agentIds' => array($agentId)))
            );
            $this->cache->memcached->save('uadCampDetails', $campDetails, 86400);

            $this->util->addLog("cache_log.txt", "\nCache log : After cache array create");
            $this->util->addLog("cache_log.txt", "\nCache log : Cache array is : " . print_r($this->cache->memcached->get('uadCampDetails'), 1));
        }
    }
    // End HP UAD-66 : Caching
    // Start HP UAD-46 : display new landing page
    public function landing()
    {
       $this->crumbs .= '> <a href="/dialer/campaigns">Campaigns</a> > Landing Page';
       $data['crumbs'] = $this->crumbs;
       $data['main'] = 'dialer/contacts/autocontactlist';
       
       $this->load->vars($data);
       $this->load->view('layout');
    }
    // End HP UAD-46 : display new landing page

    public function removeCache()
    {
        // Cache
        $this->load->driver('cache');
        $this->cache->memcached->delete('uadCampDetails');
        redirect('/dialer/campaigns');
    }
}
?>