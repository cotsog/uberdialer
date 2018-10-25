<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Campaigns extends MY_Controller
{
    /* Dev_NV Region Start */
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';
    private $_uploaded_email = array();
    public function __construct()
    {
        parent::__construct();

        $isApiCall = $this->input->post('is_api');
        $protected_methods = array('add_lead');
        if (! in_array($this->router->method, $protected_methods)  && empty($isApiCall)) {
            if (!$this->session->userdata('uid')) {
                $this->session->set_flashdata('prev_action', 'loginfail');
                redirect('/login');
            }
        }
        $this->load->library(array('form_validation')); // load form validation library
        $this->load->helper(array('url', 'html', 'form','common','utils'));
        $this->load->model('Campaigns_model');
    }
    
    public function index($status="active",$page_num=1,$sortField='ID',$order='')
    {
        if($status == "active"){
            $data['page_for'] = "active";
            if($order != "" && $this->session->userdata('tm_active_order') != ""){
                $this->session->set_userdata('tm_active_order', $order);
                $this->session->set_userdata('tm_active_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($order != ""){
                $this->session->set_userdata('tm_active_order', $order);
                $this->session->set_userdata('tm_active_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($this->session->userdata('tm_active_order') != ""){
                $this->session->set_userdata('tm_active_order', $this->session->userdata('tm_active_order'));
                $order = $this->session->userdata('tm_active_order');
                $this->session->set_userdata('tm_active_sortfield', $this->session->userdata('tm_active_sortfield'));
                $sortField = $this->session->userdata('tm_active_sortfield');
            }else{
                $order = 'asc';
                $this->session->set_userdata('tm_active_order','asc');
                $this->session->set_userdata('tm_active_sortfield', 'ID');
            }
        }elseif($status == "completed"){
            $data['page_for'] = "completed";
            if($order != "" && $this->session->userdata('tm_completed_order') != ""){
                $this->session->set_userdata('tm_completed_order', $order);
                $this->session->set_userdata('tm_completed_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($order != ""){
                $this->session->set_userdata('tm_completed_order', $order);
                $this->session->set_userdata('tm_completed_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($this->session->userdata('tm_completed_order') != ""){
                $this->session->set_userdata('tm_completed_order', $this->session->userdata('tm_completed_order'));
                $order = $this->session->userdata('tm_completed_order');
                $this->session->set_userdata('tm_completed_sortfield', $this->session->userdata('tm_completed_sortfield'));
                $sortField = $this->session->userdata('tm_completed_sortfield');
            }else{
                $order = 'asc';
                $this->session->set_userdata('tm_completed_order','asc');
                $this->session->set_userdata('tm_completed_sortfield', 'ID');
            }
        }else{
            if($order != "" && $this->session->userdata('tm_active_order') != ""){
                $this->session->set_userdata('tm_active_order', $order);
                $this->session->set_userdata('tm_active_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($order != ""){
                $this->session->set_userdata('tm_active_order', $order);
                $this->session->set_userdata('tm_active_sortfield', $sortField);
                $order = $order;
                $sortField = $sortField;
            }elseif($this->session->userdata('tm_active_order') != ""){
                $this->session->set_userdata('tm_active_order', $this->session->userdata('tm_active_order'));
                $order = $this->session->userdata('tm_active_order');
                $this->session->set_userdata('tm_active_sortfield', $this->session->userdata('tm_active_sortfield'));
                $sortField = $this->session->userdata('tm_active_sortfield');
            }else{
                $order = 'asc';
                $this->session->set_userdata('tm_active_order','asc');
                $this->session->set_userdata('tm_active_sortfield', 'ID');
            }
        }

        $campaignsModel = new Campaigns_model();

        // load offices model
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();
        
        // load helper file which is located in helper folder named : campaignjobdetail_helper.php 
        $this->load->helper('campaignjobdetail');  
        // load codigniter pagination library
        $this->load->library('pagination');
        // Get filters array 
        $searchBy = $this->input->post();
        // set pagination row numbers to display on page 
        $recs_per_page = 20;
        // get page number && set how many records are display
        $page_number = (int) $this->input->get('per_page', TRUE);
        if(empty($page_number))$page_number = 1;
        $offset = (int) $this->input->get('per_page', TRUE);
        $tot_records = $campaignsModel->getTotalCampaignRecord($searchBy,$status);
        $data['num_recs'] = $tot_records;

        $module_type = $this->app_module_type;
        
        //$campaign_ary = $campaignsModel->getTotalCampaignList("","",$module_type);
        if($status=="active"){
            $config['base_url'] =  '/dialer/campaigns/index/'.$data['page_for']."/";
            $data['base_url'] = $config['base_url'];
            $campaign_ary = "";
            $campaign_ary = $campaignsModel->getTotalCampaignList($searchBy,$recs_per_page,$offset,$order,$sortField,$module_type);
            $autodialerUrl = $this->config->item('autodialer_url');
            if (!empty($campaign_ary)) {

                foreach ($campaign_ary as $key=>$campaigns) {
                    $campaigns->name = htmlspecialchars($campaigns->name);
                    $campaigns->status = ucfirst($campaigns->status);

                    if ($this->session->userdata('user_type') == 'agent' || $this->session->userdata('user_type') == 'team_leader') {
                        // Start RP UAD-49: If campaign is enable for autodial then redirect to autodial application
                        // get campaign is autodialer enable or not
                        if ($campaigns->auto_dial == 1) { // if enable then redirect to autodialer application
                            $campaignId = $campaigns->id;
                            $loggedUserID = $this->session->userdata('uid');
                            $encryptedString = base64_encode($campaignId.'/'.$loggedUserID);
                            $autodialerLink = $autodialerUrl."/".$encryptedString;
                            $campaigns->autodialer_link = $autodialerLink;
                        } else {
                            $campaigns->autodialer_link = "";
                        }
                        // End RP UAD-49: If campaign is enable for autodial then redirect to autodial application
                        
                        if ($campaigns->AgentSignInOutValue == 'out' && (!isset($is_agent_session))) {
                            $agent_session_data = $campaignsModel->check_agent_signout($this->session->userdata('uid'), $campaigns->id);
                            if ($agent_session_data) {
                                $this->session->set_userdata('AgentSessionID', $agent_session_data->id);
                                $this->session->set_userdata('AgentSessionCampaignID', $agent_session_data->campaign_id);
                            }
                        }
                    }
                }
            }
            $data['campaigns'] = $campaign_ary;
        }elseif ($status=="completed" && $this->session->userdata('user_type') != 'team_leader' && $this->session->userdata('user_type') != 'agent'){
            $config['base_url'] =  '/dialer/campaigns/index/'.$data['page_for']."/";
            $data['base_url'] = $config['base_url'];
            $completed_campaign_ary = $campaignsModel->getTotalCampaignListCompleted($searchBy,$recs_per_page,$offset,$order,$sortField,$module_type);
            if (!empty($completed_campaign_ary)) {
                foreach ($completed_campaign_ary as $key=>$completed_campaigns) {
                    $completed_campaigns->name = htmlspecialchars($completed_campaigns->name);
                    $completed_campaigns->status = ucfirst($completed_campaigns->status);
                }
            }
            $data['campaigns'] = $completed_campaign_ary;
        }
        if ($this->session->userdata('user_type') == 'manager') {
            $officeLists = $this->session->userdata('sub_telemarketing_offices');
            array_unshift($officeLists, $this->session->userdata('telemarketing_offices'));
            foreach ($officeLists as $office) {
                $data['tm_offices'][$office] = $office;
            }
        } else {
            $data['tm_offices'] = format_array($officesModel->get_all('is_active = 1'),'name','name');
        }
        
        $func = 'getCampaignTypeValues'.ucfirst($this->app);
        $campaignTypeList = array_merge(array("" => "All"), $func());
        $data['campaignTypeList'] = json_encode($campaignTypeList);
        
        $data['filterBy'] = !empty($searchBy['filter_by']) ? $searchBy['filter_by'] : 'updated_at';
        // set base url for blade file & pagination 
        
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
        // create pagination links & Send to the balde file 
        $data['page_links']=$this->pagination->create_links();
        // set blade variables for blade 
        $data['num_pages'] = ceil($tot_records / $recs_per_page);
        $data['current_page'] = ($offset / $recs_per_page) + 1;
        $data['offset'] = $offset;
        $data['upperManagement'] =  $this->config->item('upper_management_types');
        $data['meta_title'] = 'Campaigns';
        $data['title'] = 'Campaigns';
        $data['main'] = $this->app_module_name.'/campaigns/clist';
        $data['user_id'] = $this->session->userdata('uid');
        $data['logged_user_type'] = $this->session->userdata('user_type');
        $data['crumbs'] = $this->crumbs . ' > Campaigns';

        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function create()
    {
        $logged_user_type = $this->session->userdata('user_type');
        $isAuthorized = IsAdminAuthorized($logged_user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect($this->app_module_name.'/campaigns');
        }
        $loggedInUser = $this->session->userdata('uid');
        $campaignsModel = new Campaigns_model();

        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        //Load Helpers
        $this->load->helper('campaignjobdetail');

        //Set Validation Rules
        $this->campaignFormValidation($this->input->post());

        //set default tm office value and module value
        // set telemarketing office value based on logged user type
        if($logged_user_type != 'admin'){
            $logged_tm_office = $this->session->userdata('telemarketing_offices');
        }else{
            $logged_tm_office = "'Davao TM'";
        }
        $module_value = $this->selected_module_name;

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true,$logged_tm_office,$module_value);

            //get existed EG Campaign Id value(s)
            $existedEGCampaignID = $campaignsModel->getEGCampaignID();

            // Extract Specific property value from array and get CSV String
            $propertyKey = 'eg_campaign_id';

            $existedEGCampaignIDValues = $this->getPropertyValueFromArray($existedEGCampaignID, $propertyKey);
            $existedCSVEGCampaignID = "";
            if (!empty($existedEGCampaignIDValues)) {
                $existedCSVEGCampaignID = getCSVFromArrayElement($existedEGCampaignIDValues);
            }

            $getEGCampaignList = $campaignsModel->getEGCampaignList($existedCSVEGCampaignID);
            $propertyFilterKey = 'id';
            $existedEGCampaignIDFilterValues = $this->getPropertyKeyValueFromArray($getEGCampaignList, $propertyFilterKey);
            $existedEGCampaignIDStringValues = implode(", ", $existedEGCampaignIDFilterValues);

            $getEGCampaignArray = array();
            foreach ($getEGCampaignList as $eGCampaignValue) {
                $getEGCampaignArray[] = array(
                    'value' => $eGCampaignValue->id,
                    'label' => $eGCampaignValue->id . " - " . $eGCampaignValue->name
                );
            }

            $data['existedEGCampaignIDStringValues'] = $existedEGCampaignIDStringValues;
            $func = 'getCampaignTypeValues'.ucfirst($this->app);
            $data['campaignTypeValues'] = $func();
            
            $officeList = format_array($officesModel->get_all('is_active = 1'),'name','name');
            $data['getEGWebsitesList'] = $officeList;
            $data['eGCampaignList'] = json_encode($getEGCampaignArray);

            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > Create';
            $data['meta_title'] = 'Create Campaign';
            $data['title'] = 'Create Campaign';
            $data['main'] = $this->app_module_name.'/campaigns/create';

            $this->load->vars($data);
            $this->load->view('layout');
        } else {
           $existedEGCampaignIDFilterValues = explode(', ',$_POST['existedEGCampaignIDFilterValues']);

           $eg_campaign_id =  $_POST['eg_campaign_id'];
            if(!empty($existedEGCampaignIDFilterValues)){
                if (!in_array($eg_campaign_id, $existedEGCampaignIDFilterValues)) {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Please enter valid Eg-Campaign Id!');

                    redirect($this->app_module_name.'/campaigns');
                }
            }

            //Set Campaign object properties
            $campaignName = SQLInjectionOFDefaultDB($_POST['name']);
            $checkCampaignNameExist = $campaignsModel->checkCampaignNameExist($campaignName);
            if (count($checkCampaignNameExist) > 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This Campaign Name already exists in this system.!');
                redirect($this->app_module_name.'/campaigns');
            }

            $campaign = $this->setCampaignObjectData();

            $campaign->created_by = $loggedInUser;
            $campaign->created_at = date('Y-m-d H:i:s', time());

            $campaignID = $campaignsModel->insertCampaignDetail($campaign);
            if ($campaignID > 0) {
                $created_at = date('Y-m-d H:i:s', time());
                if (isset($_POST['assign_team_id']) && !empty($_POST['assign_team_id'])) {
                    $line_mapping_arr = array();
                    foreach ($_POST['assign_team_id'] as $id) {
                        $line_mapping_arr[] = '("' . $campaignID . '","' . $id . '","' . $loggedInUser . '","' . $created_at . '")';
                    }
                    $campaignsModel->insert_assign_campaign_tl_csv($line_mapping_arr);  
                }
                if(!empty($_POST['telemarketing_offices'])){
                    $campaign_tm_mapping_array = array();
                    foreach ($_POST['telemarketing_offices'] as $tm_office) {
                        $campaign_tm_mapping_array[] = '("' . $campaignID . '","' . $tm_office . '","' . $loggedInUser . '","' . $created_at . '")';
                    }

                    $campaignsModel->insert_campaign_tm_office_csv($campaign_tm_mapping_array);
                }

                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Campaign added successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occurred.');
            }
            redirect($this->app_module_name.'/campaigns');
        }
    }

    public function edit($campaignID = null)
    {
        $loggedInUser = $this->session->userdata('uid');
        $loggedInUserType = $this->session->userdata('user_type');
        $isAuthorized = IsManagerUpperManagementAuthorized($loggedInUserType);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect($this->app_module_name.'/campaigns');
        }

        $campaignsModel = new Campaigns_model();

        $this->load->model('Offices_model');
        $officesModel = new Offices_model();

        //Load Helpers
        $this->load->helper('campaignjobdetail');
        //Set Validation Rules
        $this->campaignFormValidation($this->input->post());
        $oldtlList = $campaignsModel->getSelectedTeamLeaderList(true, $campaignID);
        if ($oldtlList) {
            $oldtlList = array_column($oldtlList, 'user_id');
        }

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            if (!empty($campaignID)) {
            $campaign = $campaignsModel->getCampaignDetailByID($campaignID);
                $this->check_authorized_tm_office($loggedInUserType, $campaign);
                $campaign_tm_marketing_offices = $campaignsModel->get_campaign_tm_office_by_id($campaignID);
                $data['tm_offices'] = array_column($campaign_tm_marketing_offices, 'tm_office');
            } else {
                $campaign = null;
            }

            if (empty($campaign)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
                redirect($this->app_module_name.'/campaigns');
            }

            if (!empty($campaign->start_date)) {
                $campaign->start_date = date('m/d/Y', strtotime($campaign->start_date));
            }
            if (!empty($campaign->end_date)) {
                $campaign->end_date = date('m/d/Y', strtotime($campaign->end_date));
            }
            if (!empty($campaign->call_filerequest_date)) {
                $campaign->call_filerequest_date = date('m/d/Y', strtotime($campaign->call_filerequest_date));
            }
            if (!empty($campaign->materials_sent_to_tm_Date)) {
                $campaign->materials_sent_to_tm_Date = date('m/d/Y', strtotime($campaign->materials_sent_to_tm_Date));
            }

            $data['campaign'] = $campaign;
            //get team leader user list based on tm offices and selected module
            $array_tm_ofc = explode(',',$campaign->telemarketing_offices);
            $csv_tm_ofc = getCSVFromArrayElement($array_tm_ofc);

            $module_value = $this->selected_module_name;

            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true,$csv_tm_ofc,$module_value);
            $data['selectedTL'] = $oldtlList;
            
            $officeList = format_array($officesModel->get_all('is_active = 1'),'name','name');
            $data['getEGWebsitesList'] = $officeList;

            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > Edit';
            $data['meta_title'] = 'Edit Campaign';
            $data['title'] = 'Edit Campaign';
            $data['main'] = $this->app_module_name.'/campaigns/edit';
            $this->load->vars($data);
            $this->load->view('layout');
        } else {

            //Set Campaign object properties
            $edit_campaign = $this->setCampaignObjectData();

            $update = $campaignsModel->updateCampaignDetailByID($campaignID, $edit_campaign);
            if ($update) {
                $newTlList = array();
                $newTlList = isset($_POST['assign_team_id']) ? $_POST['assign_team_id'] : '';
                $created_at = date('Y-m-d H:i:s', time());
                if (!empty($newTlList)) {
                    $difference = array_diff($oldtlList, $newTlList);

                    if (!empty($oldtlList)) {
                        if (count($difference) > 0) {
                            $isAssignToAgent = $this->checkAssignCampaign($campaignID, $difference);
                            if ($isAssignToAgent) {
                $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', 'This campaign is already assigned to the agent under the selected Team leader(s)');
                                redirect($this->app_module_name.'/campaigns/edit/' . $campaignID);
            }
                }
                    $campaignsModel->delete_assign_tl($campaignID); 
                }

                $line_mapping_arr = array();
                    foreach ($newTlList as $id) {
                        $line_mapping_arr[] = '("' . $campaignID . '","' . $id . '","' . $loggedInUser . '","' . $created_at . '")';
                }
                $campaignsModel->insert_assign_campaign_tl_csv($line_mapping_arr); 
                } else if (!empty($oldtlList)) {
                    $campaignsModel->delete_assign_tl($campaignID);
                }           

                if(!empty($_POST['telemarketing_offices'])){
                    $campaignsModel->delete_campaign_tm_office_by_id($campaignID);
                    if(!empty($_POST['telemarketing_offices'])){
                        $campaign_tm_mapping_array = array();
                        foreach ($_POST['telemarketing_offices'] as $tm_office) {
                            $campaign_tm_mapping_array[] = '("' . $campaignID . '","' . $tm_office . '","' . $loggedInUser . '","' . $created_at . '")';
                        }
                        $campaignsModel->insert_campaign_tm_office_csv($campaign_tm_mapping_array);
                    }
                }
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Campaign updated successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occurred.');
            }
            redirect($this->app_module_name.'/campaigns');
        }
    }

    public function view($campaign_id = null)
    {
        $campaignsModel = new Campaigns_model();
        $loggedUserID = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');

        $campaign = $campaignsModel->getCampaignViewDetail($campaign_id);

        if (empty($campaign)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect($this->app_module_name.'/campaigns');
        }
        $this->check_authorized_tm_office($userType, $campaign);

        //$TLList = $campaignsModel->getSelectedTeamLeaderList(true, $campaign_id);
        if ($campaign->uname!="") {
            $data['tlList'] = explode(",",$campaign->uname);
        }
        
        if ($userType == 'agent' ) {
            $isCampaignAssign = $campaignsModel->IsCampaignAssignToAgent($campaign_id, $loggedUserID);
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have not been assigned this campaign and contacts, please contact to administrator!');
                redirect($this->app_module_name.'/campaigns');
            }
        } else if ($userType == 'team_leader') {
            $isCampaignAssign = $campaignsModel->IsCampaignAssignToTL($campaign_id, $loggedUserID);
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have not assigned this campaign, please contact to administrator!');
                redirect($this->app_module_name.'/campaigns');
            }
        }

        $campaignName = "";
        if (!empty($campaign)) {
            if ($campaign->custom_questions != 1) {
                $campaign->custom_questions = "No";
            } else {
                $campaign->custom_questions = "Yes";
            }
            $campaign->status = ucfirst($campaign->status);
            $campaignName = htmlspecialchars($campaign->name);
        }

        if($this->app == 'mpg'){
            $response = $campaignsModel->getEGCampaignDataByID($campaign->eg_campaign_id);
            $campaign->filters = $response->filters;
        }
        $countContact = $campaignsModel->checkCampaignContacts($campaign_id);

        /*if(!empty($campaign_id)){
            $campaign_tm_marketing_offices = $campaignsModel->get_campaign_tm_office_by_id($campaign_id);
            $data['tm_offices'] = array_column($campaign_tm_marketing_offices, 'tm_office');
        }*/

         $data['logged_user_type'] = $this->session->userdata('user_type');
        $data['countContact'] = $countContact;
        $data['campaign'] = $campaign;
        $data['meta_title'] = $campaignName . ' - ' . 'Campaign Details';
        $data['title'] = 'Campaign Details';
        $data['main'] = $this->app_module_name.'/campaigns/view';

        if (!empty($campaign->name)) {
            $campaignDetail = $campaignName;
        } else {
            $campaignDetail = "Campaign Details";
        }
        $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > ' . $campaignDetail;

        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function delete()
    {
        $isAuthorized = IsAdminManagerAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $data['message'] = 'You are unauthorized person for access this page.';
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
        $campaign_id = $this->input->post('campaignID');

        $campaignsModel = new Campaigns_model();

        $is_deleted = $campaignsModel->deleteCampaign($campaign_id);
        if ($is_deleted) {
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Campaign deleted successfully!');
            $data['message'] = 'Campaign deleted successfully!';
            $data['status'] = true;
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occurred.');
            $data['message'] = 'Sorry, an error has occurred.';
            $data['status'] = false;
        }
        echo json_encode($data);
        exit();
    }

    /**
     * @return Campaign
     */
    public function setCampaignObjectData()
    {
        $campaign = new Campaign();
        $campaignData = (object)$this->input->post();

        // Start HP UAD-3 configure campaign
        if ($this->config->item('auto_dialer_toggle')) {
            if ((isset($campaignData->autodial)) && $campaignData->autodial == 1) {
                $campaign->auto_dial = '1';
                // Start HP UAD-18 configure campaign
                $campaign->auto_hopper_level = isset($campaignData->minimum_hopper_level)?$campaignData->minimum_hopper_level:1;
                // Start HP UAD-47 enhancement of UAD-43
                // Start RP UAD-8 : set autodial filed values in object
                 if (isset($campaignData->auto_abandoned_rate))
                    $campaign->auto_abandoned_rate = $campaignData->auto_abandoned_rate;
                 if (isset($campaignData->auto_time_threshold_one))
                    $campaign->auto_time_threshold_one = $campaignData->auto_time_threshold_one;
                 if (isset($campaignData->auto_recorded_message_one))
                    $campaign->auto_recorded_msg_one = $campaignData->auto_recorded_message_one;
                 if (isset($campaignData->auto_time_threshold_two))
                    $campaign->auto_time_threshold_two = $campaignData->auto_time_threshold_two;
                 if (isset($campaignData->auto_recorded_message_two))
                    $campaign->auto_recorded_msg_two = $campaignData->auto_recorded_message_two;

                 // End RP UAD-8 : set autodial filed values in object
                 // End HP UAD-47 enhancement of UAD-43
                // End HP UAD-18 configure campaign
            } else {
                $campaign->auto_dial = '0';
                // Start HP UAD-18 configure campaign
                // Start HP UAD-47 enhancement of UAD-43
                //$campaign->auto_hopper_level = 'null';
                // End HP UAD-47 enhancement of UAD-43
                // End HP UAD-18 configure campaign
            }
        }

        // echo "camp hopper level is : " . $campaign->auto_hopper_level;
        // exit(0);
        // End HP UAD-3 configure campaign

        if (!empty($campaignData->start_date)) {
            $start_date = new DateTime($campaignData->start_date);
            $startDate = $start_date->format('Y-m-d');
            $campaign->start_date = $startDate;
        }
        if (!empty($campaignData->end_date)) {
            $end_date = new DateTime($campaignData->end_date);
            $endDate = $end_date->format('Y-m-d');
            $campaign->end_date = $endDate;
        }

        if (isset($campaignData->eg_campaign_id) && $campaignData->eg_campaign_id > 0)
            $campaign->eg_campaign_id = $campaignData->eg_campaign_id;
        
        if (isset($campaignData->company_name))
            $campaign->company_name = $campaignData->company_name;
        
        if (isset($campaignData->industries))
            $campaign->industries = $campaignData->industries;

        if (isset($campaignData->country))
            $campaign->country = $campaignData->country;
            
        if (isset($campaignData->script_alt))
            $campaign->script_alt = $campaignData->script_alt;

        if (isset($campaignData->job_function))
            $campaign->job_function = $campaignData->job_function;

        if (isset($campaignData->job_level))
            $campaign->job_level = $campaignData->job_level;

        if (isset($campaignData->company_size))
            $campaign->company_size = $campaignData->company_size;

        if ($campaignData->custom_questions == 1 && (isset($campaignData->custom_question_value))) {
            $campaign->custom_question_value = trim($campaignData->custom_question_value);
        }
        if (!empty($campaignData->call_filerequest_date)) {
            $file_request_date = new DateTime($campaignData->call_filerequest_date);
            $call_file_request_date = $file_request_date->format('Y-m-d');
            $campaign->call_filerequest_date = $call_file_request_date;
        }
        if (!empty($campaignData->materials_sent_to_tm_Date)) {
            $materials_sent_Date = new DateTime($campaignData->materials_sent_to_tm_Date);
            $materials_sent_to_tm_Date = $materials_sent_Date->format('Y-m-d');
            $campaign->materials_sent_to_tm_Date = $materials_sent_to_tm_Date;
        }
        if (isset($campaignData->name))
        $campaign->name = trim($campaignData->name);
        if (isset($campaignData->campaign_previous_status) && $campaignData->campaign_previous_status == $campaignData->status) {
            unset($campaign->tm_launch_date);
        } else if (isset($campaignData->status) && $campaignData->status == 'active') {
            $campaign->tm_launch_date = date('Y-m-d', time());
        } else {
            unset($campaign->tm_launch_date);
        }

        if (isset($campaignData->status) && $campaignData->status == 'completed') {
            $campaign->completion_date = date('Y-m-d', time());
        } else {
            $campaign->completion_date = 'NULL';
        }

        if (isset($campaignData->type))
            $campaign->type = $campaignData->type;

         if (isset($campaignData->cpl))
            $campaign->cpl = $campaignData->cpl;

         if (isset($campaignData->lead_goal))
            $campaign->lead_goal = $campaignData->lead_goal;

         

        $campaign->status = $campaignData->status;
        $campaign->custom_questions = trim($campaignData->custom_questions);
        $campaign->script_main = trim($campaignData->script_main);
        $campaign->updated_at = date('Y-m-d H:i:s', time());
        $campaign->modified_by = $this->session->userdata('uid');
        $campaign->site_id = 1;

        if(isset($campaignData->site_name)){
            $campaign->site_name = $campaignData->site_name;
        }else{
            $campaign->site_name = "Enterprise Guide";
        }
        $campaign->business = $this->app;
        $campaign->module_type = 'tm';
        return $campaign;
    }

    function maximumcheck($num)
    {
        if ($num > 30000) {
            $this->form_validation->set_message(
                'maximumcheck',
                'The %s field must be less than 30000'
            );
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function campaignFormValidation($postDataValue)
    {
        //Set Validation Rules
        $this->form_validation->set_rules('eg_campaign_id', 'EG Campaign ID', 'trim|required|max_length[7]|is_natural_no_zero');
        $this->form_validation->set_rules('status', 'Status', 'required|trim|max_length[10]');
        if (!empty($postDataValue['lead_goal'])) {
            $this->form_validation->set_rules('lead_goal', 'Lead Goal', 'required|trim|integer|max_length[6]|callback_maximumcheck');
        }
        if (!isset($postDataValue['telemarketing_offices']) && $this->session->userdata('user_type') == 'admin') {
        $this->form_validation->set_rules('telemarketing_offices', 'Telemarketing Offices', 'required|trim|max_length[200]');
        }
        if (isset($postDataValue['status']) && $postDataValue['status'] == 'active') {
            $this->form_validation->set_rules('eg_campaign_id', 'EG Campaign ID', 'trim|max_length[10]|is_natural_no_zero');
            $this->form_validation->set_rules('script_main', 'script main', 'required|trim|min_length[5]');
            if (isset($postDataValue['custom_questions']) && $postDataValue['custom_questions'] == 1) {
                $this->form_validation->set_rules('custom_question_value', 'Custom Question Text', 'required|trim|max_length[200]');
            }
        }
        // Start RP UAD-8 : Add server side validation for when auto dial option is enable
        if (isset($postDataValue['autodial']) && $postDataValue['autodial'] == '1') {
            $this->form_validation->set_rules('auto_abandoned_rate', 'Abandon Rate', 'required|trim');
            $this->form_validation->set_rules('auto_recorded_message_one', 'Recorded message 1 ', 'required|trim|min_length[10]');
            $this->form_validation->set_rules('auto_recorded_message_two', 'Recorded message 2', 'required|trim|min_length[10]');
    }
        // End RP UAD-8 : Add server side validation for when auto dial option is enable
    }

    public function getEGCampaignDataByID()
    {
        $campaignsModel = new Campaigns_model();

        $egCampaignObjectData = $this->input->post();

        if (!empty($egCampaignObjectData['egCampaignID'])) {
            $egCampaignID = $egCampaignObjectData['egCampaignID'];
        } else {
            $egCampaignID = 0;
        }

        $response = $campaignsModel->getEGCampaignDataByID($egCampaignID);

        if (!empty($response)) {
            if (!empty($response->start_date)) {
                $response->start_date = date('m/d/Y', strtotime($response->start_date));
            }
            if (!empty($response->program_end_date)) {
                $response->program_end_date = date('m/d/Y', strtotime($response->program_end_date));
            }

            if($this->app == 'mpg'){
                $response->filters = (!empty($response->filters)) ? $response->filters : 'None';
            }else{
                if (!empty($response->report_builder_data)) {
                    $report_builder_data = unserialize($response->report_builder_data);
                    //job function
                    if(isset($report_builder_data['silo_filter'][0])){
                        $silo_filter = array_map("filter", $report_builder_data['silo_filter'][0]);
                        $response->silo_filter = implode("<br>",$silo_filter);
                        $response->silo_filter_db = implode("|",$silo_filter);
                    }

                    //job level
                    if(isset($report_builder_data['job_level_filter'][0])) {
                        $job_level_filter = array_map("filter", $report_builder_data['job_level_filter'][0]);
                        $response->job_level = implode("<br>", $job_level_filter);
                        $response->job_level_db = implode("|", $job_level_filter);
                    }
                    //company size
                    if(isset($report_builder_data['company_size_filter'][0])) {
                        $company_size_filter = array_map("filter", $report_builder_data['company_size_filter'][0]);
                        $response->company_size = implode("<br>", $company_size_filter);
                        $response->company_size_db = implode("|", $company_size_filter);
                    }
                    //Industries
                    if(isset($report_builder_data['industry_filter'][0])) {
                        $industry_filter = array_map("country_filter", $report_builder_data['industry_filter'][0]);
                        $response->industry = implode("<br>", $industry_filter);
                        $response->industry_db = implode("|", $industry_filter);
                    }
                    //Country
                    if(isset($report_builder_data['country_filter'][0])) {
                        $country_filter = array_map("country_filter", $report_builder_data['country_filter'][0]);
                        $response->country = implode("<br>", $country_filter);
                        $response->country_db = implode("|", $country_filter);
                    }
                }
            }

            $data['data'] = $response;
            $data['status'] = true;
        } else {
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
        }
        echo json_encode($data);
        exit();
    }

    public function get_tl_user_list(){
        $campaignsModel = new Campaigns_model();
        $tm_offices = $this->input->post('tm_offices');
        $module_value = $this->input->post('module_value');

        if($tm_offices){
            // create csv string value from logged user module value
        $array_tm_ofc = explode(',',$tm_offices);
            $csv_tm_ofc = getCSVFromArrayElement($array_tm_ofc);

            $csv_module_value = "";
            $count_array_module_value = "";
            if(!empty($module_value)){
                $array_module_value = explode(',',$module_value);
                $csv_module_value = getCSVFromArrayElement($array_module_value);
                $count_array_module_value = count($array_module_value);
            }

            $tl_user = $campaignsModel->getTeamLeaderUsersList(true,$csv_tm_ofc,$csv_module_value,$count_array_module_value);

            if($tl_user){
                $data['data'] = $tl_user;
                $data['status'] = true;
            }else{
                $data['status'] = true;
            }
        }
        else{
            $data['message'] = 'Please select any telemarketing office!';
            $data['status'] = false;
        }
        echo json_encode($data);
        exit;
    }

    /**
     * @param $userType
     * @param $campaign
     */
    private function check_authorized_tm_office($userType, $campaign)
    {
        if ($userType != 'admin' && $userType != 'qa') {
            $string_to_array = explode(',', $campaign->telemarketing_offices);

            if (!empty($campaign) && !in_array($this->session->userdata('telemarketing_offices'), $string_to_array)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This Campaign is not in your TM office, You can not view it!');
                redirect($this->app_module_name.'/campaigns');
            }
        }
    }

    // Check agent session while user id idle or not
    public function check_agent_session(){
        $logged_user_id = $this->session->userdata('uid');
        $check_agent_session = $this->Campaigns_model->check_agent_session($logged_user_id);

        if(!empty($check_agent_session)){
            $data['message'] = '';
            $data['data'] = $check_agent_session;
            $data['status'] = false;
            echo json_encode($data);
            exit;
        }else{
            $data['message'] = '';
            $data['status'] = true;
            echo json_encode($data);
            exit;
        }

    }

    function eg_header(){
        return array('Member ID','Email','First Name','Last Name','Job Title','Job Level','Job Function','Company','Address','City','State','Country','Zip','Phone','Ext','Time Zone','Employee Size','Company Revenue','Industry','Priority','Owner');
    }

    /* Dev_NV Region End */
    
    /* Dev_KR Region Start */
    public function createContacts($campaignId = null,$list_id=null)
    {
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        $isAuthorized = IsTLManagerUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect($this->app_module_name.'/campaigns');
        }
        $editFlag = 0;
        $isEdit = isset($_POST['for_revision']) && $_POST['for_revision'] == 'Yes' ? 1 : 0;
        
        $headers = array();
        if($this->app == 'eg'){
            $headers = $this->eg_header();
        }
        $this->form_validation->set_rules('campaign_name', 'Campaign Name', 'required|trim');

        if(isset($_POST) && empty($_POST['list_name']) && empty($_POST['select_list_name'])) {
            $this->form_validation->set_rules('list_name', 'List Name', 'required|trim');
        }

        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            $data['campaign'] = $this->Campaigns_model->get_campaign_by_userLogin($user_type, $loggedUserID);

            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > Add Contacts/Lists';
            $data['meta_title'] = 'Create List';
            $data['title'] = 'Create List';
            $data['campaignId'] = $campaignId;
            if(!empty($list_id)){
                $data['hidden_list_id'] = $list_id;
            }
            $data['main'] = $this->app_module_name.'/campaigns/createcontacts';

            $this->load->vars($data);
            $this->load->view('layout');
        }else{
            $checkActiveCampaign =  $this->Campaigns_model->get_campaign_by_name($_POST['campaign_name']);
            
            $campaignId = isset($checkActiveCampaign['0']->id)?$checkActiveCampaign['0']->id:0;
            $list_type = isset($_POST['list_type']) ? $_POST['list_type'] : "";    

            $cu_file_name = ENVIRONMENT.'contact_upload.pid';

            if (file_exists('/tmp/'.$cu_file_name)) {
                // PID file exists; 
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', "You cannot upload at the moment. Another upload is still ongoing.");
                redirect($this->app_module_name.'/Campaigns/createContacts');
                exit;
            }
            
            if ($campaignId > 0) {
                $iscontacts =  $this->Campaigns_model->checkCampaignContacts($campaignId);
                if($iscontacts){
                    $editFlag = 1;
                }
                if(!empty($_FILES) && $_FILES['userfile']['error'] == 0){
                    $allowed =  array('zip');
                    $filename = $_FILES['userfile']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if(!in_array($ext,$allowed) ) {
                        $this->session->set_flashdata('page_data',$_POST);
                        $this->session->set_flashdata('msg', 'File must be in ZIP format.');
                        redirect($this->app_module_name.'/Campaigns/createContacts/'.$campaignId.'/'.$list_id);
                    } else {
                        $getActualFileName = explode(".zip", $filename);
                        $originalFileName = $getActualFileName[0] . '.csv'; 
                        $newFileName = $getActualFileName[0].'_'.round(microtime(true)) . '.' . $ext;
                        //$target_dir = "./uploads/";
                        $target_dir = '/tmp/';
                        $target_file = $target_dir . $newFileName;

                        if(isset($_POST['edit_mode']) && $_POST['edit_mode'] !=""){
                            $list_type = 'on';
                        }
                        if (!move_uploaded_file($_FILES["userfile"]["tmp_name"], $target_file)) {
                            $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', 'File not uploaded.');
                            redirect($this->app_module_name.'/Campaigns/createContacts');
                        }                    
                    }
                }else{
                    if($list_type == 'on'){
                        $this->session->set_flashdata('msg', 'No file was uploaded');
                        redirect($this->app_module_name.'/Campaigns/createContacts');
                    }  
                }

                $fh = fopen('/tmp/'.$cu_file_name, 'a');
                fwrite($fh, 'contact upload running');
                fclose($fh);

                //add list name in appt_lists
                $this->load->model('Lists_model');
                $list = new ListsTable();
                
                $list->campaign_id = $_POST['campaign_name'];
                if($list_type == 'on'){ $list->file_name =$newFileName; }
                $list->status = $_POST['status'];
                if(isset($_POST['list_name']) && $_POST['list_name']!=''){
                    $list->list_name = $_POST['list_name'];
                    $campaign_list = $this->Lists_model->get_list_by_name("list_name = '" . $_POST['list_name'] . "' AND campaign_id = " . $_POST['campaign_name'], 1);
                    if (!empty($campaign_list[0]->id)) {
                        unlink('/tmp/'.$cu_file_name);
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', "List Name already exists in this campaign.");
                        redirect($this->app_module_name.'/Campaigns/createContacts');
                        exit;
                    }
                    
                    $list->created_at = date('Y-m-d H:i:s', time());
                    $list->created_by = $this->session->userdata('uid');
                }else if(isset($_POST['select_list_name']) && $_POST['select_list_name']!=''){
                    $list->list_id = $_POST['select_list_name'];
                    $editFlag = 1;
                }else if(!empty($list_id)){
                    $list->list_id = $list_id;
                    $editFlag = 1;
                }
                $list->updated_at = date('Y-m-d H:i:s', time());
                $list->updated_by = $this->session->userdata('uid');
                $list_id = $this->Lists_model->insert_list($list);
                
                //process zip file
                $path = "/tmp/";
                $file = $path . $newFileName;
                $originalFileName = $path . $originalFileName;
                
                $zip = new ZipArchive;
                $res = $zip->open($file);
                if ($res === TRUE) {
                  $zip->extractTo('/tmp/');
                  $zip->close();
                } else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', "Something went wrong while uploading ZIP data. Please check CSV file.");
                }
                unset($zip);
        
        
                if($list_type == 'on'){  
                    if (in_array($campaignId, $this->isUploadTransferCampaign)) {
                        $uploadcontact = $this->uploadContacts($campaignId, $originalFileName, $list_id, $headers, $list->updated_at, $list->updated_by, $isEdit);
                    } else {
                        //$uploadcontact = $this->upload_csv_file($campaignId, $newFileName,$list_id,$headers,$list->updated_at,$list->updated_by,$isEdit);
                        $uploadcontact = $this->uploadContactsAjax($campaignId, $originalFileName, $list_id, $headers, $list->updated_at, $list->updated_by, $isEdit);
                    }
                    //var_dump($uploadcontact);exit;
                    if(is_array($uploadcontact)) {
                        $result = $uploadcontact;
                    } else {
                        $upload_result = explode('#', $uploadcontact);
                        $result = isset($upload_result[0]) ? $upload_result[0] : "";
                        $msg    = isset($upload_result[1]) ? $upload_result[1] : "";
                        if($result != 1 && $msg == "delete" && $_POST['list_name'] != ""){
                            //$this->Lists_model->delete_list($campaignId,$list_id);
                        }
                    }
                }else{
                    $result = 1;
                }

                // we're done, so let's remove the PID file
                unlink('/tmp/'.$cu_file_name);
                unlink($filename); //replace .zip to csv
                
                if($result==1 && $editFlag==0 ){
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'Contacts Added successfully!');
                }else if($result==1 && $editFlag==1){
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'Contacts Updated successfully!');
                }else if($result==3){
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'No records found in csv file!');
                    //$this->Lists_model->delete_list($campaignId,$list_id);
                }else if ($result == 4) {
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'No new list created, all contacts on this list already exists in other list(s) for this campaign.');
                    //$this->Lists_model->delete_list($campaignId,$list_id);
                }else if($result==2){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', "Something went wrong while uploading CSV data. Please check CSV file.");
                    //$this->Lists_model->delete_list($campaignId,$list_id);
                }else{
                    //$this->Lists_model->delete_list($campaignId,$list_id);
                    if(!empty($result) && is_array($result) && (isset($result['redirect_url']) && $result['redirect_url']!='')){
                        $this->session->set_flashdata('class', $result['class']);
                        $this->session->set_flashdata('msg', $result['msg']);
                        redirect($this->app_module_name.$result['redirect_url']);
                    }else if(!empty($result) && is_array($result)){
                        $this->session->set_flashdata('class', $result['class']);
                        $this->session->set_flashdata('msg', $result['msg']);
                        redirect($this->app_module_name.'/Campaigns/createContacts');
                    }else{
                        $this->load->model('Contacts_model');
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', "Something went wrong while uploading CSV data. Please check CSV file.");
                    }
                }
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please Select valid Campaign.');
            }
            redirect($this->app_module_name.'/Campaigns/');
        }
    }
    
    public function IsCampaignContacts($campaignID)
    {
        echo $this->Campaigns_model->checkCampaignContacts($campaignID);
        exit;        
    }
    
    public function upload_csv_file($id,$filename,$camp_list_id,$headers,$updated_at='',$updated_by='',$editFlag=''){
        //$path = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
        $path = '/tmp/';
        $newFileName = $path . $filename;
        $handle = fopen($newFileName, "r");
        $lines =  fgetcsv($handle, 1000, ",");
        $admin_id = $this->session->userdata('uid');
        
        if (!empty($lines)) {
            if($this->app == 'mpg'){
                /* 
                *  Mpg changes  start
                */
                $count_expected_rows = 0;
                $count_actual_rows = 0;
                if(count($lines) != 17){                    
                    return '2#delete';
                }
                $this->load->model('Contacts_model');
                $contacts_model = new Contacts_model();
                $upload_result = array();
                while (($lines = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $count_expected_rows++;
                    if(!empty($lines[0]) && count($lines) == 17){
                        $line = array();//the following may also be applied to eg fields
                        foreach($lines as $value){
                            $line[] = is_string($value) ? addslashes($value) : $value;
                        }
                        $columns_csv = implode('","', $line);
                        $record = '( "' . $columns_csv . '","'. date('Y-m-d H:i:s') . '")';

                        if (!empty($line)) {
                            $response = $contacts_model->insert_csv_contacts_mpg($record,$id);
                            $response  = 1;
                            $upload_result[$line[0]] = $response;
                            $count_actual_rows++;
                        }
                    }
                   
                   
               }
               fclose($handle);
               if($count_actual_rows == $count_expected_rows){
                   $list_id = 1;
               }else{
                   $list_id = 3;
               }
               
               return $list_id;
               /*
               *  MPG Changes End   
               */ 
            }else{
                if(count($lines) != 21){               
                    return '2#delete';
                }            
                $error_msg = null;
                foreach($lines as $index => $header){
                    $key = array_search(trim($header), $headers);
                    if($index != $key){
                        $error_msg = "Column header: {$header} is not properly placed.";
                        break;
                    }
                }
                if(!empty($error_msg)){
                   return array('class' => 'bad', 'msg' => $error_msg); 
                }else{
                    $count = $list_id = 0;
                    $ctr = 2;
                    $line_arr = $line_mapping_arr=array();
                    $line_arr2 = array();
                    $this->load->model('Contacts_model');
                    $contacts_model = new Contacts_model();
                    $this->load->model('Members_model');
                    $members_model = new Members_model();
                    $emails = array();
                    $count_exist = 0;
                    $fail = array();
                    $member_id_filter = array();
                    $member_email_filter = array();

                    //$eg_myqli_connection = MYSQLConnectOFEGDB();

                    while (($lines = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $lines = array_map("utf8_encode", $lines);
                        $count++;
                        if(!empty($lines[1])){
                            $error = false;
                            $email = addslashes($lines[1]);
                            $empty_values = $this->validate($lines, $headers);
                            if(!empty($empty_values)){
                                $error = true;
                            }

                            if($error){
                                $fail[] = $ctr;
                            }else{
                                $lines[0] = "";
                                $owner = !empty(trim($lines[20])) ? addslashes($lines[20]) : '';
                                $line_arr[] = '("' . $lines[0] . '","' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes($lines[11]) . '","' . $lines[12] . '","' . $lines[13] . '","' . $lines[14] . '","' . $lines[15] . '","' . $lines[16] . '","' . addslashes($lines[17]) . '","' . addslashes($lines[18]) . '","' . addslashes($lines[19]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                                $line_arr2[] = '("' . $lines[0] . '","' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes($lines[11]) . '","' . $lines[12] . '","' . $lines[13] . '","' . $lines[14] . '","' . $lines[15] . '","' . $lines[16] . '","' . addslashes($lines[17]) . '","' . addslashes($lines[18]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                                $emails[] = array(
                                    'email' => $email,
                                    'date' => date('Y-m-d H:i:s')
                                );
                                $update_arr = $lines;

                                $member_email_filter[] = $email;
                            }  
                            
                        }
                        $ctr++;
                    }
                    $count_fail = count($fail);
                    if($count_fail == $count){
                        //nothing was inserted
                        return array( 'class' => 'bad', 'msg' => "Something went wrong while uploading the file. Please check the format of the Email and other columns except for Member ID and Priority (optional). ZIP, State and Time zone should have values if the Country is US."); 
                    }else{
                        if ($count > 0 && !empty($line_arr)) {
                            $list_id = $contacts_model->insert_csv_file_contacts($line_arr);

                            if(!empty($member_email_filter)){
                                $members_qa_filter = implode("','", $member_email_filter);
                                $members_model->update_join_contacts_batch("'{$members_qa_filter}'",'email');
                            }
                        }
                        if(!empty($emails)){
                            $count_emails = 0;
                            $sqlInsert = array();
                            
                            foreach($emails as $detail){
                                $count_emails++;
                                $getContact = $contacts_model->EmailContactDetails($detail['email']);

                                if(isset($getContact['id'])){
                                    $sqlInsert[] = '("'. $id . '","' . $getContact['id']. '","'.$camp_list_id . '","'.$admin_id.'","'.$detail['date'].'")';
                                }
                            }

                            if(!empty($sqlInsert)){
                               
                                $list_id = $contacts_model->insert_mapping_csv_contacts_file($sqlInsert,$updated_at);
                                
                                if($list_id){
                                    
                                    if( $editFlag != 1 ){
                                        //extract dupes from campaign_contacts table  
                                        $dupes_lists = $contacts_model->select_dupes_list( $id, $camp_list_id, $updated_at );
                                        $this->load->model('Lists_model');
                                        $list_model = new Lists_model();
                                        
                                        $no_of_dupes = isset($dupes_lists['dupes']) ? array_sum($dupes_lists['dupes']):0;
                                        $no_of_uploaded = $count_emails - $no_of_dupes;

                                        //insert upload summary history
                                        //get list name. List is deleted when 0 uploads thus we need to keep the list name instead of the id
                                        $lists = $list_model->get_one($camp_list_id);
                                        $list_name = isset( $lists->list_name ) ? $lists->list_name : '';

                                        $list_dupes_history_id = $list_model->insert_campaign_list_dupes_history( $id, $list_name, $no_of_dupes, $no_of_uploaded, $updated_at, $updated_by );
                                        //insert upload summary history - end
                                        
                                        if( count($dupes_lists) > 0 ){
                                            $no_of_dupes = array_sum($dupes_lists['dupes']);
                                            $no_of_uploaded = $count_emails - $no_of_dupes; 

                                            $sqlDupesInsert = array();
                                            //insert dupes list on a another table
                                            $ct = 0;
                                            foreach($dupes_lists['list_id'] as $dupes_list_id){
                                                if($dupes_list_id!=''){
                                                    $sqlDupesInsert[] = '("'.$list_dupes_history_id.'","'. $dupes_list_id . '","' . $list_name . '","'. $dupes_lists['contact_id'][$ct] .'")';
                                                }
                                                $ct++;
                                            }

                                            $inserted_dupes_list = $contacts_model->insert_dupes_lists($sqlDupesInsert);

                                            //clear tagging of dupes in campaign_contacts
                                            $res = $contacts_model->clear_dupes_list( $id, $camp_list_id, $updated_at );

                                            return array( 'class' => 'good', 'msg' => $no_of_uploaded . " out of " . $count_emails . " contacts in this list were successfully uploaded. " . $no_of_dupes . " contacts on this list were already exists in other list(s) for this campaign.", "redirect_url"=>"/campaigns"); 
                                        }
                                    }else{
                                       //clear tagging of dupes in campaign_contacts
                                        $res = $contacts_model->clear_dupes_list( $id, $camp_list_id, $updated_at );
                                    }
                                }
                            }
                            if($list_id == 0 && ($count_emails == $count_exist)){
                                $list_id = 1;
                            }
                        }
                        if($count_fail > 0){
                            return array('class' => 'bad', 'msg' => "Some records were not uploaded. Kindly check the values according to the following row number/s: " . implode(",", $fail)); 
                        }else{
                            return $list_id;
                        }
                            
                    }
                }
            }
    } else {
            return '3#delete';
        }
    }
    
    function _set_members_qa_update($line){
        $member_qa = array();
        $member_qa['email'] = $line[1];
        $member_qa['first_name'] = $line[2];
        $member_qa['last_name'] = $line[3];
        $member_qa['job_title'] = $line[4];
        $member_qa['job_level'] = $line[5];
        $member_qa['company_name'] = $line[7];
        $member_qa['address1'] = $line[8];
        $member_qa['city'] = $line[9];
        $member_qa['zip'] = $line[12];
        $member_qa['state'] = $line[10];
        $member_qa['country'] = $line[11];
        $member_qa['industry'] = $line[18];
        $member_qa['company_size'] = $line[16];
        $member_qa['phone'] = $line[13];
        $member_qa['ext'] = $line[14];
        return (object) $member_qa;
    }
    
    /**
     *
     * Array
        (
            [0] => Member ID
            [1] => Email
            [2] => First Name
            [3] => Last Name
            [4] => Job Title
            [5] => Job Level
            [6] => Job Function
            [7] => Company
            [8] => Address
            [9] => City
            [10] => State
            [11] => Country
            [12] => Zip
            [13] => Phone
            [14] => Ext
            [15] => Time Zone
            [16] => Employee Size
            [17] => Company Revenue
            [18] => Industry
            [19] => Priority
            [20] => Owner
        )
     * @param type $data
     * @param type $header
     * @return type
     */
    function validate($data,$header){
        $return = array();
        $required_for_us = array(10,12,15);
        foreach($data as $key => $value){
            if($key != 0 && $key != 19 && $key != 14 && $key != 17) {
                if($key != 0 && empty(trim($value))){
                    if(in_array($key, $required_for_us)){
                        if(strtolower(trim($data[11])) == 'us'){
                            $return[] = $header[$key];break;
                        }
                    }else{
                        $return[] = $header[$key];break;
                    }
                }else if($key == 13 && (int) $value == 0){
                    $return[] = $header[$key];break;
                }
            }
        }
        return $return;
    }
    
    public function add_lead($campaignId = 0)
    {
        if ($this->input->server('REQUEST_METHOD') != 'POST') {
            header('HTTP/1.1 404 Not Found', true, 404);
            //echo "The file you're looking for ~does not~ exist.";
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'));
        $total_errors = array();
        $errors = array();
        
        if($campaignId){
            $isCampaignId = $this->Campaigns_model->getCampaignDataByEGID($campaignId);
            if(empty($isCampaignId)){
                 $errors[] = 'invalid value: campaign_id is invalid ';
            }else{
                $campaign_id = $isCampaignId->id;
            }
        }else{
            if(!isset($data->campaign_id)){
                $errors[] = 'missing field: campaign_id';
            }else{
                $isCampaignId = $this->Campaigns_model->getCampaignDataByEGID($data->campaign_id);
                if (empty($isCampaignId)) {
                     $errors[] = 'invalid value: campaign_id is invalid ';
                }else{
                    $campaign_id = $isCampaignId->id;
                }
            }
        }

        $this->load->model('contacts_model');
        $contact_model = new Contacts_model();

        if ($data) {

            if(!isset($data->email)){
                $errors[] = 'missing field: email';
            }else if(empty($data->email)){
                 $errors[] = 'empty field: email';
            }else if(!($this->isValidEmail($data->email))){
                $errors[] = 'invalid value: email is invalid ';
            } else {
                if ($contact_model->email_exists($data->email)) {
                        $errors[] = 'invalid value: Email already exists';
                }else{
                    $id = $contact_model->eg_email_exists($data->email);
                    if($id){
                        $data->id = $id;
                    }
                }
            }

            if(!isset($data->first_name)){
                $errors[] = 'missing field: first_name';
            }else if(empty($data->first_name)){
                 $errors[] = 'empty field: first_name';
            }else if (($this->validStrLen($data->first_name,'first_name', 5, 20))!= 1){
                $errors[] = $this->validStrLen($data->first_name,'first_name', 5, 20);
            }

            if(!isset($data->last_name)){
                $errors[] = 'missing field: last_name';
            }else if(empty($data->last_name)){
                 $errors[] = 'empty field: last_name';
            }else if (($this->validStrLen($data->last_name,'last_name', 5, 20))!= 1){
                $errors[] = $this->validStrLen($data->last_name,'last_name', 5, 20);
            }

            if(!isset($data->phone)){
                $errors[] = 'missing field: phone';
            }else if(empty($data->phone)){
                 $errors[] = 'empty field: phone';
            }
            if(!isset($data->country)){
                $errors[] = 'missing field: country';
            }else if(empty($data->country)){
                 $errors[] = 'empty field: country';
            }
        }else{
            $errors[] = " Data not available. ";
        }

        if(!empty($errors)){
            $result['status'] = false;
            $total_errors[] = array("errors"=>$errors);
            $result['Errors'] = $total_errors;
        }else{
            $resourceId =  isset($data->resource_id)?$data->resource_id:0;
            unset($data->username);
            unset($data->password);
            unset($data->campaign_id);
            unset($data->resource_id);
            $data->created_at = date('Y-m-d H:i:s');
           
            $contact_id = $contact_model->insert_contact($data);
            if($contact_id){
                $contact_lists_data = array();
                $contact_lists_data ['contact_id'] = $contact_id;
                $contact_lists_data['campaign_id'] = $campaign_id;                   
                $contact_lists_data['source'] = 'api';
                $contact_lists_data['resource_id'] = $resourceId;
                $contact_lists_data['created_at'] = date('Y-m-d H:i:s', time());
                $contact_model->insert_contact_lists($contact_lists_data);
                $result['status'] = true;
                $result['Errors'] = "";
            }else{
                $result['status'] = false;
                $result['Errors'] = "";
            }
        }
        echo json_encode($result);
    }

    public function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validStrLen($str, $fieldName, $min, $max)
    {
        $len = strlen($str);
       /* if($len < $min){
            return "invalid value: ".$fieldName." is too short, minimum is $min characters ($max max).";
        }
        else*/
        if($len > $max){
            return "invalid value: ".$fieldName." is too long, maximum is $max characters ";// ($min min).";
        }
        return TRUE;
    }
    
    public function campaignAssign($teamID=null,$campaignID=null)
    {
        $loggedInUserType = $this->session->userdata('user_type');
        $isAuthorized = IsAdminQAManagerAuthorized($loggedInUserType);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect($this->app_module_name.'/campaigns');
        }

        $campaign = $this->Campaigns_model->getCampaignViewDetail($campaignID);
        if (empty($campaign)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect($this->app_module_name.'/campaigns');
        }
        $this->load->model('Users_model');
        
        // set current module type and get agent data based on that module type
        $module_value = $this->app_module_type;
        $csv_module_value = "";
        $count_array_module_value = "";
        if(!empty($module_value)){
            $array_module_value = explode(',',$module_value);
            $csv_module_value = getCSVFromArrayElement($array_module_value);
            $count_array_module_value = count($array_module_value);
        }

        $campaignAssign = $this->Users_model->get_agent_by_id($teamID,$csv_module_value,$count_array_module_value);
        if ($campaignAssign) {
            $data['campaignDetail'] = $campaign;
            $data['campaignAssign'] = $campaignAssign;
            $data['meta_title'] = 'Agent Detail';
            $data['title'] = 'Agent Details';
            $data['main'] = $this->app_module_name.'/campaigns/viewagent';
            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > Campaign Assignment ';
            $this->load->vars($data);
            $this->load->view('layout');
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Agent(s) not found.');
            redirect($this->app_module_name.'/campaigns');
        }
    }

    public function get_list_by_campaign(){
        $campaignID = $this->input->post('campaignId');
        $list_type = $this->input->post('list_type');
        if(!empty($list_type) && $list_type == 'existinglist'){
            $this->load->model('Lists_model');
            $get_list_by_campaign = $this->Lists_model->get_list_by_campaign_id($campaignID);
            $data['get_list_by_campaign'] = $get_list_by_campaign;
            /*echo "<pre>";
            print_r($get_list_by_campaign);exit;*/
        }

        $data['status'] = true;

        echo json_encode($data);
        exit;
    }

    public function get_agent_by_selected_list(){
        $list_id = $this->input->post('list_id');
        $campaignID = $this->input->post('campaignId');
        $this->load->model('Lists_model');

        $get_list_detail = $this->Lists_model->get_one($list_id);
        //$agent_list = $this->Campaigns_model->get_agent_list_by_campaign($campaignID);
        $agent_list = $this->Campaigns_model->getAssignAgentByCampaign($campaignID);

        if(!empty($get_list_detail)){
            $get_list_detail->agent_id = explode(',',$get_list_detail->agent_id);
        }

        if(!empty($get_list_detail)){
            $data['agent_list'] = $agent_list;
            $data['data'] = $get_list_detail;
            $data['status'] = true;
        }else{
            $data['message'] = "Sorry, This list have no agent(s).";
            $data['status'] = false;
        }
        echo json_encode($data);
        exit;
    }

    /**
     * @param $array ,$propertyKey
     */
    public function getPropertyValueFromArray($array, $propertyKey)
    {
        return array_column($array, $propertyKey);
    }

    public function getPropertyKeyValueFromArray($EGCampaigns, $propertyFilterKey)
    {
        $ids = array_map(create_function('$ar', 'return $ar->' . $propertyFilterKey . ';'), $EGCampaigns);
        return $ids;
    }
    
    public function checkAssignCampaign($campaignId, $TlIds)
    {
        return $res = $this->Campaigns_model->isAssignCampaignToTL($campaignId, $TlIds);
  
        echo json_encode($data);
        exit();        
    }

    public function helpcontactfile()
    {
        $isAuthorized = IsAdminTLManagerAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect($this->app_module_name.'/campaigns');
        }
        $data['headers'] = $this->eg_header();
        $this->load->view('helpcontactfile'.$this->fileAppend,$data);
    }

    /* Dev_KR Region End */
    
    
    public function uploadContacts ($id, $filename, $camp_list_id, $headers, $updated_at='', $updated_by='', $editFlag='') {
        //$path = '/tmp/';
        
        //$file = $path . $filename;
        $handle = fopen($filename, "r");
        $lines =  fgetcsv($handle, 1000, ",");
        
        if (!empty($lines)) {
            $uid = $this->session->userdata('uid');
            
            if (count($lines) != 21){               
                return '2#delete';
            }      
            
            $errorMsg = null;
            
            foreach($lines as $index => $header){
                $key = array_search(trim($header), $headers);
                if($index != $key){
                    $errorMsg = "Column header: {$header} is not properly placed.";
                    break;
                }
            }
            
            if (!empty($errorMsg)) {
               return array('class' => 'bad', 'msg' => $errorMsg); 
            } else {
                $this->load->model('Contacts_model');
                $contacts_model = new Contacts_model();
                $this->load->model('Members_model');
                $members_model = new Members_model();
                
                $rowCt = 2;
                $list_id = 0;
                $batchRec = array();
                $ccEmails = array();
                $noOfEmailExist = 0;
                $invalidRows = array();
                $membersEmails = array();
                
                while (($lines = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $lines = array_map("utf8_encode", $lines);
                    if (!empty($lines[1])) {
                        $error = false;
                        $email = addslashes($lines[1]);
                        $invalidValues = $this->validate($lines, $headers);
                        
                        if(!empty($invalidValues)){
                            $error = true;
                        }

                        if ($error) { //assign row numbers containing invalid values
                            $invalidRows[] = $rowCt; 
                        } else { //create batch insert for contacts table
                            $lines[0] = "";
                            $owner = !empty(trim($lines[20])) ? addslashes($lines[20]) : '';
                            
                            $batchRec[] = '("' . $lines[0] . '","' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes($lines[11]) . '","' . $lines[12] . '","' . $lines[13] . '","' . $lines[14] . '","' . $lines[15] . '","' . $lines[16] . '","' . addslashes($lines[17]) . '","' . addslashes($lines[18]) . '","' . addslashes($lines[19]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                            
                            $ccEmails[] = array(
                                'email' => $email,
                                'date' => date('Y-m-d H:i:s')
                            );
                            
                            $membersEmails[] = $email;
                        }  

                    }
                    $rowCt++;
                }
                
                $rowCt = $rowCt - 1; //deduct headers from the number of rows
                $invalidRowsCt = count($invalidRows);
                
                if ($invalidRowsCt == $rowCt) { //nothing was inserted
                    return array( 'class' => 'bad', 'msg' => "Something went wrong while uploading the file. Please check the format of the Email and other columns except for Member ID and Priority (optional). ZIP, State and Time zone should have values if the Country is US."); 
                } else {
                    if ($rowCt > 0 && !empty($batchRec)) {
                        $list_id = $contacts_model->insert_csv_file_contacts($batchRec); //insert to contacts table
                        
                        //update members_qa table
                        if (!empty($membersEmails)) {
                            $membersEmailFilter = implode("','", $membersEmails);
                            $members_model->updateMembersQA("'{$membersEmailFilter}'",'email');
                        }
                    }
                    
                    //batch insert on campaign contacts table
                    $noOfEmailExist = 0;
                    if (!empty($ccEmails)) {
                        $campaignContactsRec = array();

                        foreach($ccEmails as $detail){ //retrieve contact_id from contacts table and create batch insert sql for campaign contacts table
                            $getContact = $contacts_model->EmailContactDetails($detail['email']);

                            if(isset($getContact['id'])){
                                $noOfEmailExist++;
                                $campaignContactsRec[] = '("'. $id . '","' . $getContact['id']. '","'.$camp_list_id . '","'.$uid.'","'.$detail['date'].'")';
                            }
                        }

                        if(!empty($campaignContactsRec)){ //batch insert in campaign_contacts table
                            $list_id = $contacts_model->insertCampaignContacts($campaignContactsRec);
                        }
                        
                        $noOfEmail = count($ccEmails);
                        if ($list_id && ($noOfEmail == $noOfEmailExist)) {
                            $list_id = 1;
                        }
                    }
                    
                    if ($invalidRowsCt > 0) {
                        return array('class' => 'bad', 'msg' => "Some records were not uploaded. Kindly check the values of the following row number/s: " . implode(",", $invalidRows)); 
                    } else {
                        return $list_id;
                    }

                }
            }
        } else {
            return '3#delete';
        }
    }
    
    public function uploadContactsAjax ($id, $originalFileName, $camp_list_id, $headers, $updated_at = '', $updated_by = '', $email_has_changed = 0) {
        $limit = 25000;
        $data = array();

        $handle = fopen($originalFileName, "r");
        $lines =  fgetcsv($handle, 1000, ",");
        
        if (!empty($lines)) {
            if (count($lines) != 21){
                return '2#delete';
            }      
            
            $error_msg = null;
            
            foreach($lines as $index => $header){
                $key = array_search(trim($header), $headers);
                if($index != $key){
                    $errorMsg = "Column header is not properly placed.";
                    break;
                }
            }
            unset($lines);
            if (!empty($error_msg)) {
                return array('class' => 'bad', 'msg' => $errorMsg); 
            } else {
                $list_id = 0;
                
                $res = $this->readFile($originalFileName, $handle, $headers, $limit); //update members_qa and contacts table
                
                $res['row_ct'] = $res['row_ct'] - 1; //deduct headers from the number of rows
                $invalid_rows_ct = count($res['invalid_rows']);
                
                if ($invalid_rows_ct == $res['row_ct']) { //nothing was inserted
                    return array( 'class' => 'bad', 'msg' => "Something went wrong while uploading the file. Please check the format of the Email and other columns except for Member ID and Priority (optional). ZIP, State and Time zone should have values if the Country is US.");
                } else {
                                        
                    unset($res['row_ct']);
                    //unset($res['invalid_rows']);
                    $this->upsertCampaignContacts($id, $camp_list_id, $res['members_email'], $email_has_changed, $limit);
                    
                    if ($invalid_rows_ct > 0) {
                        return array('class' => 'bad', 'msg' => "Some records were not uploaded. Kindly check the values of the following row number/s: " . implode(",", $res['invalid_rows'])); 
                    } else {
                        return 1;
                    }
                }
                unset($res);
            }
        } else {
            return array( 'class' => 'bad', 'msg' => "File is empty");
        }
    }
    
    function upsertContacts ($lines, $handle, $headers, $temp_table_on, $limit) {
        $this->load->model('Contacts_model');
        $contacts_model = new Contacts_model();
                
        if ($temp_table_on) {
            //truncate contacts temp_table
            $contacts_model->truncateContactsTmpTable();
        }
        
        $ct_rec = 0; 
        $row_ct = 2;
        $members_email = array();
        $invalid_rows = array();
        $batch_rec = array();
        
        //read files line  by line
        while (($lines = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $lines = array_map("utf8_encode", $lines);
            if (!empty($lines[1])) {
                $email = addslashes($lines[1]);
                $invalid_values = $this->validate($lines, $headers);

                if (!empty($invalid_values)) { //assign row numbers containing invalid values
                    $invalid_rows[] = $row_ct; 
                } else { //create batch insert for contacts table
                    $lines[0] = "";
                    $owner = !empty(trim($lines[20])) ? addslashes($lines[20]) : '';

                    $batch_rec[] = '("' . $lines[0] . '","' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes($lines[11]) . '","' . $lines[12] . '","' . $lines[13] . '","' . $lines[14] . '","' . $lines[15] . '","' . $lines[16] . '","' . addslashes($lines[17]) . '","' . addslashes($lines[18]) . '","' . addslashes($lines[19]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                    $ct_rec++;
                    if ($ct_rec == $limit) { //must limit to 25k since we are getting packet error on mysql when beyond that
                        $list_id = $contacts_model->insert_csv_file_contacts($batch_rec); //insert to contacts table
                        if ($temp_table_on) {
                            //insert on temp table for members_qa update used
                            $temp_contacts_table = $contacts_model->insert_csv_file_contacts($batch_rec, 'tmp_contacts_upload'); //insert to contacts table
                        }
                        $ct_rec = 0; //set to 0 for another batch of rec
                        $batch_rec = array(); //empty array for the next batch
                    }
                    $members_email[] = $email;
                }  
            }
            $row_ct++;
        }
        //insert remaining batchrec if there are
        if (!empty($batch_rec)) {
            $list_id = $contacts_model->insert_csv_file_contacts($batch_rec); //insert to contacts table
            if ($temp_table_on) {
                //insert on temp table for members_qa update
                $temp_contacts_table = $contacts_model->insert_csv_file_contacts($batch_rec, 'tmp_contacts_upload'); //insert to contacts table    
            }
        }
        unset($batch_rec);
        $data['members_email'] = $members_email;
        $data['row_ct'] = $row_ct;
        $data['invalid_rows'] = $invalid_rows;
        
        return $data;
    }
    
    function readFile ($lines, $handle, $headers, $limit) {
        $ct_rec = 0; 
        $row_ct = 2;
        $members_email = array();
        $invalid_rows = array();
        $batch_rec = array();
        $members_qa_rec = array();
        $this->load->model('Contacts_model');
        $contacts_model = new Contacts_model();
        
        //read files line  by line
        while (($lines = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $lines = array_map("utf8_encode", $lines);
            if (!empty($lines[1])) {
                $email = addslashes($lines[1]);
                $invalid_values = $this->validate($lines, $headers);

                if (!empty($invalid_values)) { //assign row numbers containing invalid values
                    $invalid_rows[] = $row_ct; 
                } else { //create batch insert for contacts table
                    $lines[0] = "";
                    $owner = !empty(trim($lines[20])) ? addslashes($lines[20]) : '';

                    $batch_rec[] = '("' . $lines[0] . '","' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes(strtoupper($lines[11])) . '","' . $lines[12] . '","' . $lines[13] . '","' . $lines[14] . '","' . $lines[15] . '","' . $lines[16] . '","' . addslashes($lines[17]) . '","' . addslashes($lines[18]) . '","' . addslashes($lines[19]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                    $members_qa_rec[$email] = '("' . $email . '","' . addslashes($lines[2]) . '","' . addslashes($lines[3]) . '","' . addslashes($lines[4]) . '","' . addslashes($lines[5]) . '","' . addslashes($lines[6]) . '","' . addslashes($lines[7]) . '","' . addslashes($lines[8]) . '","' . addslashes($lines[9]) . '","' . addslashes($lines[10]) . '","' . addslashes(strtoupper($lines[11])) . '","' . addslashes($lines[12]) . '", CAST(' .$lines[13] . ' AS UNSIGNED),"' . $lines[14] . '","' . $lines[16] . '","' . $lines[17] . '","' . addslashes($lines[18]) . '","' . $owner . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '","' . date('Y-m-d H:i:s') . '")';
                    
                    $ct_rec++;
                    if ($ct_rec == $limit) { //must limit to 25k since we are getting packet error on mysql when beyond that
                        //upsert contacts table
                        $list_id = $contacts_model->insert_csv_file_contacts($batch_rec); //insert to contacts table
                        //insert on dupe members qa
                        $this->onDupeMembersQa($members_qa_rec);
                        $ct_rec = 0; //set to 0 for another batch of rec
                        $batch_rec = array(); //empty array for the next batch
                        $members_qa_rec = array();
                        $this->_uploaded_email = array();
                    }
                    $members_email[] = $email;
                    $this->_uploaded_email[] = $email;
                }  
            }
            $row_ct++;
        }
        //insert remaining batchrec if there are
        if (!empty($batch_rec)) {
            $list_id = $contacts_model->insert_csv_file_contacts($batch_rec); //insert to contacts table
            $this->onDupeMembersQa($members_qa_rec); //insert on dupe members qa
        }
        unset($lines);
        unset($batch_rec);
        unset($members_qa_rec);
        unset($this->_uploaded_email);
        $data['members_email'] = $members_email;
        $data['row_ct'] = $row_ct;
        $data['invalid_rows'] = $invalid_rows;
        
        return $data;
    }
    
    function updateMembersQa ($members_emails, $contacts_table = 'contacts', $fld = 'email') {
        $this->load->model('Members_model');
        $members_model = new Members_model();
        
        if (!empty($members_emails)) {
            $members_email_filter = implode("','", $members_emails);
            $members_model->updateMembersQA("'{$members_email_filter}'", $fld, $contacts_table);
        }
        unset($members_emails);
        
        return 1;
    }
    
    function onDupeMembersQa ($members_recs = array()) {
        $this->load->model('Members_model');
        $members_model = new Members_model();
        //get existing emails from members_qa table
        $email_exists = $members_model->member_exist_check($this->_uploaded_email, 1);
        
        $_email_exists = array_column($email_exists,"email");
        $none_existing_emails = array();
        $none_existing_emails = array_diff($this->_uploaded_email, $_email_exists);
        unset($email_exists);
        
        $this->_uploaded_email = array();
      
        foreach ( $none_existing_emails as $none_existing_email ) {
            unset($members_recs[$none_existing_email]);
        }
        
        unset($members_rec);
        unset($key);
        
        $members_model->updateMembersQAOnDupe($members_recs);
        unset($members_recs);
        unset($_email_exists);
        
        return 1;
    }
    
    function upsertCampaignContacts($campaign_id, $cc_list_id, $members_emails, $email_has_changed, $limit) {
        $this->load->model('Contacts_model');
        $contacts_model = new Contacts_model();
        
        $no_of_email_exist = 0;
        if (!empty($members_emails)) {
            $contact_emails = array();
           
            //prepare email where clause
            foreach($members_emails as $members_email){ 
                $contact_emails[] = "'" . $members_email ."'";
            }
            unset($members_emails);
            $email_str = implode(",", $contact_emails);
            //retrieve all contact_id from contacts table based on the email values
            if (count($contact_emails) > 0) {
                unset($contact_emails);
                $original_contacts = $contacts_model->EmailContactDetails($email_str, null, 'id,email', false, 'bulk');
                $original_details = $modified_details = array();
                ini_set('memory_limit', '1024M');
                foreach ($original_contacts as $original_contact) {
                    $original_details[$original_contact->email] = $original_contact->id;
                }

                $modified_details = array();

                if ($email_has_changed) {
                    $modified_details = $this->checkEmailHasChange($email_str, $campaign_id);
                } 
                
                unset($email_str);
                $final_ids = array_merge($original_details, $modified_details);
                unset($original_details);
                unset($modified_details);
            }
            
            //insert upserted contacts on campaign contacts table
            $ct_rec = 0;
            $campaign_contacts_rec = array();
            foreach ($final_ids as $final_id){
                if($final_id > 0){
                    $no_of_email_exist++;
                    $ct_rec++;
                    $campaign_contacts_rec[] = '("'. $campaign_id . '","' . $final_id . '","'. $cc_list_id . '","'. $this->session->userdata('uid') .'","' . date('Y-m-d H:i:s') . '")';
                    if ($ct_rec == $limit) { //limit to 25k only to prevent memory limit error
                        $ct_rec = 0;
                        $list_id = $contacts_model->insertCampaignContacts($campaign_contacts_rec);
                        $campaign_contacts_rec = array();
                    }
                }
            }

            //insert remaining to campaign contacts table if there are
            if(!empty($campaign_contacts_rec)){ 
                $list_id = $contacts_model->insertCampaignContacts($campaign_contacts_rec);
            }
            unset($campaign_contacts_rec);
        }
        
        return $no_of_email_exist;
    }
    
    public function checkEmailHasChange($emails, $campaign_id)
    {
        $this->load->model('Members_model');
        $members_model = new Members_model();
        
        $details = $members_model->getOriginalContact($campaign_id, $emails, "c.id as contact_id, m.email, c.member_id");
        
        $data = array();
        foreach ($details as $detail) {
            $data[$detail->email] = $detail->contact_id;
        }
        
        return $data;    
    }
    
    function plupload(){
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        /* 
        // Support CORS
        header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        */

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        //$targetDir = "/tmp";
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/contacts/';

        //$targetDir = 'uploads';
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        // Remove old temp files	
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }	

        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {	
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, filesize($_FILES["file"]["tmp_name"]))) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }
}
  
?>