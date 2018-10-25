<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Calls extends MY_Controller
{
    /* Dev_NV Region Start */

    // set Default Breadcrumb
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';
    public $callsModel = null;
    public $campaignModel = null;
    public $resourceViewModel = null;
    public $contactsModel = null;
    private $_nonWorkableDispo = array(1,7,11,14,15,16,17,18,20,21,22,23,24,25);
    private $do_not_call_ever_callDisposition_array = array('7', '11', '16', '17', '18','20','21');
    
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            //Check User logged-in or Not
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->library(array('form_validation')); // load form validation library
        $this->load->helper(array('url', 'html', 'form','utils'));
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

    public function index($campaignContactListID = null, $list_id = null, $requestPage = null)
    {
        if(!empty($_POST['change_dispo']) || !empty($_POST['decision'])){
            $this->submit($list_id, $requestPage);
        }else{
            // Load model - Calls,Campaigns,Resourceview,contacts model
            $this->load->model('Calls_model');
            $this->callsModel = new Calls_model();
            $this->load->model('Campaigns_model');
            $this->campaignModel = new Campaigns_model();
            $this->load->model('Resourceview_model');
            $this->resourceViewModel = new Resourceview_model;
            $this->load->model('Contacts_model');
            $this->contactsModel = new Contacts_model();
            $this->load->helper('campaignjobdetail');
            
            
            if((!empty($requestPage) && $requestPage == 'qa' && $this->input->get('action') != 'view')){
                $data = $this->qa_view($campaignContactListID, $list_id, $requestPage);
            } else {
                $data = $this->agentView($campaignContactListID, $list_id, $requestPage);
            }
                    
            $data['title'] = 'View Contact';
            $data['main'] = 'dialer/calls/view'.$this->fileAppend;
            
            $this->load->vars($data);
            $this->load->view('layout');
        }
    }
    
    public function create($campaign_id, $list_id) {
        $data = array();
        $this->load->model('Calls_model');
        $callsModel = new Calls_model;
        $this->load->model('Resourceview_model');
        $resourceViewModel = new Resourceview_model;
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
        $this->load->model('Contacts_model');
        $this->contactsModel = new Contacts_model();
        
        $data['campaign_id'] = $campaign_id;
        
        $campaigns = $campaignsModel->getOneCampaignByIdData($campaign_id);
        if(!empty($campaigns)){
            unset($campaigns->country);
            $contactCallDetail = $campaigns;
            $contactCallDetail->campaign_type = $contactCallDetail->type;
            $contactCallDetail->id = 0;
        }else{
           $this->session->set_flashdata('class', 'bad');
           $this->session->set_flashdata('msg', 'Campaign not found!');
           redirect('/dialer/campaigns');
        }
        $loggedUserID = $this->session->userdata('uid');
        $user_type = $this->session->userdata('user_type');
        $contactCallDetail->logged_user_type = $user_type;
        $contactCallDetail->list_id = $list_id;
        $contactCallDetail->campaign_id = $campaign_id;
        
        $this->check_multiple_contact_access($loggedUserID, 0,$contactCallDetail->campaign_id,$list_id);
        // checking eg-campaign id is exist or not
        $egCampaign = $this->get_eg_campaign($contactCallDetail, $callsModel, $list_id);
        $data['egCampaign'] = $egCampaign;
        if (isset($egCampaign->incentives_available) &&
            $egCampaign->incentives_available > 0) {
            $contactCallDetail->incentives_available = $egCampaign->incentives_available;
            $getIncentive = $campaignsModel->getIncentive($egCampaign->incentives_available);
            $contactCallDetail->incentive = !empty($getIncentive) ? $getIncentive[0]->incentive : '';
        }
        $this->set_data_display($egCampaign, $contactCallDetail, $data, $resourceViewModel);
        
        if($this->session->contactdata("ContactFilter")){
            $middle_crumb_menu = '<a href="/dialer/contacts/index/' . $campaign_id .'/'.$list_id.'/contactsort">Contacts</a> > ';
        }else{
            $middle_crumb_menu = '<a href="/dialer/contacts/index/' . $campaign_id .'/'.$list_id. '">Contacts</a> > ';
        }
        $finalCrumbs = $this->crumbs . ' > '.$middle_crumb_menu . "New Contact";
        $data['jobLevels'] = getJobLevelValues();
        $data['Qaing'] = false;
        $data['isAddPage'] = true;
        $data['editableField'] = true;
        $data['contactCallDetail'] = $contactCallDetail;
        $data['campaign_id'] = $campaign_id;
        $data['list_id'] = $list_id;
        $data['campaign_name'] = $contactCallDetail->name;
        $data['eg_campaign_id'] = $contactCallDetail->eg_campaign_id;
        $data['title'] = 'Add Contact';
        $data['meta_title'] = 'Add Contact';
        $data['crumbs'] = $finalCrumbs;
        $data['main'] = 'dialer/calls/view_create';
        $data['callDispositionList'] = $callsModel->getCallDispositionsByModule('tm');
        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    public function save($listId){
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $campaign_id = $this->process_agent($callsModel, $listId);
        $this->is_lead_generated($campaign_id, $listId, false);
    }
    
    function set_actions(&$data, $requestPage, $user_type){
        // declare variable - Call detail page display as a view page True/False
        $data['isViewPage'] = false;
        // declare variable - check current page is "Add as a different person" page or not
        $data['isAddPage'] = false;
        // declare variable - added this variable after add feature -
        // for Manager and TL also can do process as Before Agent "agent" and After QA "QA" Process.
        $data['Qaing'] = false;
        
        // if for qa(QA = true), get all ids from resource_views
        $data['qa'] = false;
        if (!empty($requestPage)) {
            if ($requestPage == 'add' && $user_type != 'qa') {
                $data['isAddPage'] = true;
            } else if ($user_type != 'agent') {
                if ($requestPage == 'qa' && $this->input->get('action') == 'qa') {
                    $data['Qaing'] = true;
                    $data['qa'] = true;
                } else {
                    $data['isViewPage'] = true;
                    $data['qa'] = true;
                }
            } else {
                $data['isViewPage'] = true;
            }
        } else {
            if ($user_type != 'qa') {
                $data['qa'] = true;
            } else {
                $data['isViewPage'] = true;
            }
        }
    }
    function agentView($campaignContactListID, $list_id = null, $requestPage = null){
        $data = array();
        $callsModel = $this->callsModel;
        $campaignModel = $this->campaignModel;
        $resourceViewModel = $this->resourceViewModel;
        $contactsModel = $this->contactsModel;
        
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        // Load - user_agent - library for get Referrer URL and set into Back button
        $controller_part = $this->check_url($data);
        
        $this->set_actions($data, $requestPage, $user_type);
        
        // Declare variable for display data at "Call History" and "Email History" section
        $contactCallHistoryList = '';
        $contact_email_history_list = '';
        
        // get contact detail for add as a different person page
        if ($data['isAddPage']) {
            $contactCallDetail = $callsModel->getContact($campaignContactListID);
            
            $this->check_if_contact_exist($contactCallDetail, $data, $list_id);
            
            //if add as a different page, set call history id and plivo comm id pass to the newly created contact
            $data['original_call_history_id'] = $this->get_call_history($callsModel, $contactCallDetail);
        
        
        }
        else {
            // get contact detail if that page not as an add as a different page
            $contactCallDetail = $callsModel->getCallDetailOne($campaignContactListID);
            
            $this->check_if_contact_exist($contactCallDetail, $data, $list_id);
        }
        
        //check agent user is signed-in into campaign or not
        if($user_type == 'agent'){
            $this->check_agent_session($contactCallDetail, $loggedUserID, $campaignModel);
        }
        if(!$data['qa']) {
            $this->check_authorized_tm_office($user_type,$contactCallDetail);
        }
        $list_id = ((int) $list_id == 0) ? $callsModel->getListID($campaignContactListID,$contactCallDetail->campaign_id) : $list_id;
       
        //check agent user is signed-in into campaign or not
        if (empty($data['Qaing']) && !$data['isViewPage']) {
            // checking: same user can not access multiple contact at same time.
            $this->check_multiple_contact_access($loggedUserID, $contactCallDetail->id,$contactCallDetail->campaign_id,$list_id);
        }
        $lead_id = $contactCallDetail->lead_id;
        $member_id = $contactCallDetail->member_id;
        if (!$data['isAddPage']) {
            // check other user working on this contact or not
            if (!$data['isViewPage'] && !empty($contactCallDetail->locked_by) && $contactCallDetail->locked_by != $loggedUserID) {
                $this->redirect_if_locked($data, $contactCallDetail->campaign_id, $list_id);
            }
            // agent can access as a view page If lead submitted with "Lead for Qualification" disposition
            if (!empty($contactCallDetail->call_disposition_id) && $contactCallDetail->call_disposition_id == '1'  && empty($requestPage)) {
                $data['isViewPage'] = true;
            }
            if ($contactCallDetail->status != 'Follow-up') {
                
                //check if this is call back by logged agent, if yes then allow agent to edit, if not the agent is VIEW mode only
                $getAgentLeadCount = $callsModel->getAgentLeadCount($campaignContactListID);
                $data['isViewPage'] = (!$this->is_callback_by_agent($getAgentLeadCount, $campaignContactListID, $contactCallDetail, $callsModel, $loggedUserID)) ? true :  $data['isViewPage'];
            
                
            }
            
            $data['user_team_leader_id'] = false;
            if ($contactCallDetail->status == 'Follow-up') {
                //Check QA Follow-up with current time is exceed to one week or not                
                $this->can_follow_up($data,$campaignContactListID, $contactCallDetail, $loggedUserID, $contactsModel, $callsModel);
            }
            
            
            
            $resource_id = $contactCallDetail->resource_id;
            $status = $get_member_tmp_data = false;
            
            // get data of "Call History" and "Email History"
            $contactCallHistoryList = $callsModel->getCallHistoryList($contactCallDetail->id);
            $contact_email_history_list = $callsModel->getEmailHistoryList($campaignContactListID);

            //check if add as a diff, and campaign_contact already exist in the campaign_contact_changes, if yes then get the latest source and createdby 
            if(!empty($contactCallDetail->original_list)) {
                $newCampaignContactDetails = $contactsModel->getCampaignContactsChangesLatest($campaignContactListID);
                if(!empty($newCampaignContactDetails)){
                    $contactCallDetail->source = $newCampaignContactDetails[0]['new_source'];
                    $contactCallDetail->contact_created_by = $newCampaignContactDetails[0]['contact_created_by'];
                }
            }
        }        
       
        
        $action = $this->input->get('action');
        
        if ($data['qa']) {
            $data["action"] = $action;
            //$lead = $resourceViewModel->get_one($lead_id);
            if (!empty($lead_id)) {
                // Checking: once Lead generated and agent can not access some type of lead such as "Pending","Approved","Rejected" etc..
                $this->user_is_agent($data, $action, $resourceViewModel, $lead_id, $contactCallDetail->status);
                //todo // eg - admin_users -> Uber - users                
                $resource_id = $data["resource_id"] = $contactCallDetail->resource_id;
                $status = $contactCallDetail->status;
            }
        }
        
        // ------------------- common code -------------
        
        $data["qa_accepted_by_user"] = $data["user_is_agent"] = false;
        
        // checking eg-campaign id is exist or not
        $egCampaign = $this->get_eg_campaign($contactCallDetail, $callsModel, $list_id);

        $this->set_data_display($egCampaign, $contactCallDetail, $data, $resourceViewModel, $member_id);
        
        $data['isTodayExceedCallDial'] = false;
        
        if($user_type != 'qa' && !empty($contactCallDetail->call_disposition_id)) {

            $isTodayCallbackExceeded = $this->checkTodayCallbackDial($callsModel, $contactCallDetail->campaign_contact_id);

            if(!empty($isTodayCallbackExceeded) && 
                (($isTodayCallbackExceeded['isTodayExceedCallDial'] && $contactCallDetail->call_disposition_id == 2) || 
                    !$isTodayCallbackExceeded['isTodayExceedCallDial']
                    )){
                $data = array_merge($data,$isTodayCallbackExceeded);
            }
            
        }
        // You can't call/dial more than 3 times per day, apply for mention call disposition in business rules.
        // after that change total 3 call per day.. even there selected any disposition
        //if disposition is call back
        
        if(empty($contactCallDetail->call_disposition_id) || (!empty($contactCallDetail->call_disposition_id) && $contactCallDetail->call_disposition_id != 2)){
            $this->isTodayExceedCallDial($data, $callsModel, $user_type, $contactCallDetail);
        }

        $data['callLimitReached'] = $data['isTodayExceedCallDial'];
        if($data['isTodayExceedCallDial'] == 1 && !empty($contactCallDetail->status) && $contactCallDetail->status == 'Follow-up') {
            if($this->allowLastFollowUpToday($contactCallDetail->last_follow_up_date)) {
                $data['todayCallDiallerMessage'] = "";
                $data['isTodayExceedCallDial'] = 0;
            }
            unset($data['errorType']);
        }

        // as per business rules sheet this condition checked
        // TODO - need to verify business rules once again..
        $isNonWorkable = false;
        if (!empty($contactCallDetail->call_disposition_id)) {
            $isNonWorkable = $this->is_nonworkable($contactCallDetail->call_disposition_id);
            $data['isViewPage'] = ($isNonWorkable) ? true : $data['isViewPage'];
        }
        
        if($data['isViewPage'] && $contactCallDetail->lifted == 1){
            //check if call dispo has been lifted. if Yes then, allow agent to resubmit
            $data['isViewPage'] = false;
        }
            
        if (!$data['isViewPage'] && !$data['isAddPage']) {
            //check first if contact is already assigned to another user. If yes, redirect,if no lock to the user who opened it first
            $this->lock_unassigned_contact($contactsModel, $contactCallDetail, $data, $list_id);
        }
        
        //check pureb2b consent from eg.contacts table
        if(!empty($contactCallDetail->email)){
            $gdprConsent = $this->getGdprConsent($contactCallDetail->email, $contactCallDetail->eg_campaign_id, $contactsModel);
            $data['pureB2bConsent'] = $gdprConsent['pureb2bConsent'];
            $data['clientConsent'] = $gdprConsent['clientConsent'];
        }
        $leadStatus = !empty($contactCallDetail->status) ? $contactCallDetail->status : false;
        $data['status'] = $leadStatus;
        
        
        //UBER-36 b. For client consent questions, it should be based on the client. If they said no to IBM, they shouldn't be asked to the IBM campaigns. 
        //If they said yes to Microsoft, they should still be asked for that campaign.
        //force agent to submit Do not call this campaign, if client consent for this contact is no even if it is for a different campaign
        if((!empty($gdprConsent['clientConsent']) && $gdprConsent['clientConsent'] == "no" && empty($_REQUEST['action'])) || 
                (!empty($gdprConsent['clientConsent']) && $gdprConsent['clientConsent'] == "no" && !empty($_REQUEST['action']) && $_REQUEST['action'] != 'view')){
                if(!empty($contactCallDetail->call_disposition_id) && $contactCallDetail->call_disposition_id == '1' && $leadStatus != 'Follow-up'){
                    $isNonWorkable = true;
                }
            if(!$isNonWorkable && !empty($data['pureB2bConsent']) && $data['pureB2bConsent'] == 'yes'){
                $data['gdprErrorMessage'] = "This prospect opted out of the client's call list. Submit as 'Do Not Call for this Campaign' and proceed to the next contact.";
                $data['gdprClientNo'] = true;
            }
        }
        
        $contactCallDetail->logged_user_type = $user_type;

        
        
        // breadcrumb set for regular contact detail page and add as a different person
        
        $finalCrumbs = $this->set_crumb($contactCallDetail, $controller_part, $data, $contactCallDetail->campaign_id, $list_id);

        $this->save_contact_visible($data, $loggedUserID, $contactCallDetail);
        
        if($this->app == 'mpg'){
            $this->set_mpg_data($data,$egCampaign, $contactCallDetail, $member_id, $callsModel);
        }
        if (isset($egCampaign->incentives_available) &&
            $egCampaign->incentives_available > 0) {
                $contactCallDetail->incentives_available =
                    $egCampaign->incentives_available;
                $getIncentive = $campaignModel->getIncentive($egCampaign->incentives_available);
                $contactCallDetail->incentive = !empty($getIncentive) ? $getIncentive[0]->incentive : '';
            }
        $this->load->helper('common');
        //if PureMQL Campaign, Employee Size, Industry, and Company Rev should be editable
        /*$data['editableField'] = ($contactCallDetail->source == 'add_diff' && $this->input->get('add_diff') == 'true'  && $user_type == 'agent' && ($egCampaign->type == 'puremql' || $egCampaign->type == 'pureresearch' || $egCampaign->type == 'smartleads')) ? true : false;
        if (!empty($this->input->get('manual_create'))) {
            $data['editableField'] = true;
        }*/
        if($data['isAddPage']){
            $data['editableField'] = true;
            $data['editableFieldAll'] = true;
        }else{
            $data['editableField'] = false;
            $data['editableFieldAll'] = false;
        }
        $contactCallDetail->list_id = $list_id;
        $data['contactCallDetail'] = $contactCallDetail;
        $data['contactCallHistoryList'] = $contactCallHistoryList;
        $data['contact_email_history_list'] = $contact_email_history_list;
        $data['callDispositionList'] = $callsModel->getCallDispositionsByModule('tm');
        $data['nonWorkableDispositions'] = $this->_nonWorkableDispo;
        $data['meta_title'] = 'View Contact';
        $data['crumbs'] = $finalCrumbs;
        $data['egCampaign'] = $egCampaign;
        return $data;
    }

    //check if campaign contact is still allowed to be called for followup for today
    function allowLastFollowUpToday($lastFollowUpDateSet)
    {
        if(!empty($lastFollowUpDateSet)) {
            $lastFollowupDatetime = $lastFollowUpDateSet;              
            $lastFollowupDatetime = strtotime($lastFollowupDatetime);
            $lastFollowUpDate = date('Y-m-d', $lastFollowupDatetime);
            $currentDate = date('Y-m-d', time());
            if($currentDate != $lastFollowUpDate) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }
    
    //check pureb2b consent from eg.contacts table
    function getGdprConsent($email, $egCampaignId, $contactsModel = null){
        if(empty($contactsModel)){
            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();
        }
        $getEgContacts = $contactsModel->getEGContactDetailByEmail($email, "pureb2b_consent");
        $getEgClientConsent = $contactsModel->getEGClientConsent($email, $egCampaignId, "cc.consent");
        $return = array();
        $return['pureb2bConsent'] = null;
        if(isset($getEgContacts->pureb2b_consent) && $getEgContacts->pureb2b_consent ==0){
            $return['pureb2bConsent'] = "no";
        }else if(isset($getEgContacts->pureb2b_consent) && $getEgContacts->pureb2b_consent ==1){
            $return['pureb2bConsent'] = "yes";
        }
        $return['clientConsent'] = null;
        if(isset($getEgClientConsent->consent) && $getEgClientConsent->consent ==0){
            $return['clientConsent'] = "no";
        }else if(isset($getEgClientConsent->consent) && $getEgClientConsent->consent ==1){
            $return['clientConsent'] = "yes";
        }

        return $return;
    }
    
    function saveGdprConsent($email, $egCampaignId, $pureb2bConsent,  $contactsModel = null){
        if(empty($contactsModel)){
            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();
        }
        //insert on contacts table if not yet existing, but if existing, update only the pureb2b_consent 
        //and set pureb2b_consent_updated_at if the pureb2b_consent is different
        
    }


    function callbackReminder()
    {
        $loggedUserID = $this->session->userdata('uid');

        if(!empty($loggedUserID) && $loggedUserID > 0){
            $this->load->model('Calls_model');
            $callsModel = new Calls_model();

            $callbackReminder = $callsModel->retrieveCallback($loggedUserID);

            $data = array();
            $callbackData = array();
            $status = 0;
            if(!empty($callbackReminder)){
                $callbackData = $callbackReminder;
                $status = 1;
            }            
            $data['status'] = $status;
            $data['details'] = $callbackData;
            echo json_encode($data);
            exit();
        }
    }

    
    function qa_view($campaignContactListID, $list_id = null, $requestPage = null){
        $data = array();
        $callsModel = $this->callsModel;
        $resourceViewModel = $this->resourceViewModel;
        $contactsModel = $this->contactsModel;
        
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        // Load - user_agent - library for get Referrer URL and set into Back button
        $controller_part = $this->check_url($data);
        
        $this->set_actions($data, $requestPage, $user_type);
        $action = $this->input->get('action');
        $data["action"] = $action;
       
        // get contact detail if that page not as an add as a different page
        $contactCallDetail = $callsModel->getCallDetailOne($campaignContactListID);

       $this->check_if_contact_exist($contactCallDetail, $data, $list_id);
        
        if(!$data['qa']) {
            $this->check_authorized_tm_office($user_type,$contactCallDetail);
        }
        
        $list_id = empty($list_id) ? $callsModel->getListID($campaignContactListID,$contactCallDetail->campaign_id) : $list_id; 

        //Checking: Any one can accept this lead as an QA process or not
        $data['isViewPage'] = !($this->can_user_qa_lead($campaignContactListID, $loggedUserID, $callsModel)) ? true : $data['isViewPage'];

        // up_insert "is_qa_in_progress" and "QA" field for access this lead by logged qa user. if lead is not yet approved
        if(!in_array($contactCallDetail->status, array('Approve','Follow-up','Reject'))){
            $this->set_qa_in_progress($data, $contactCallDetail, $resourceViewModel);
        }

        $data['user_team_leader_id'] = false;
        if ($contactCallDetail->status == 'Follow-up') {
            //Check QA Follow-up with current time is exceed to one week or not                
            $this->can_follow_up($data, $campaignContactListID, $contactCallDetail, $loggedUserID, $contactsModel, $callsModel);
        }
        
        // get data of "Call History" and "Email History"https://tmstage.pureincubation.com/dialer/calls/index/11460533/1267n in
        $contactCallHistoryList = $callsModel->getCallHistoryList($contactCallDetail->id);
        $contact_email_history_list = $callsModel->getEmailHistoryList($campaignContactListID);
            
            
        $member_id = $contactCallDetail->member_id;
        $resource_id = $data["resource_id"] = $contactCallDetail->resource_id;
        $lead_id = $contactCallDetail->lead_id;
        // QA can not access if lead is not generated
        $this->check_lead_id($lead_id);
        

// ------------------- common code -------------
        
        // checking eg-campaign id is exist or not
        $egCampaign = $this->get_eg_campaign($contactCallDetail, $callsModel, $list_id);
        
        
        $this->set_data_display($egCampaign, $contactCallDetail, $data, $resourceViewModel, $member_id);

        $data['rejectedReason'] = array();
        if ($data['qa']) {
            $data["qa_accepted_by_user"] = $data["user_is_agent"] = false;
            $data['rejectedReason'] = getRejectReasonValues();
            $data['followupReason'] = getFollowUpReasonValues();
                // Get existed call flow findings data which submit as an QA process
                $data["qa_accepted_by_user"] = true;
                if (isset($lead_id)) {
                        $callFlowFindingsData = $callsModel->getCallFlowFindingsData($lead_id);
                        $data['callFlowFindingsData'] = $callFlowFindingsData;
                    }

                // Checking someone have already accept this lead for QA process.
                // can we remove condition for "$user_type != 'agent'" && !data['qa_accepted_by_user']
                $this->is_qa_in_progress($action,$contactCallDetail,$data,$user_type);
                
                $accessQAStatusArray = array('Pending', 'QA in progress', 'Follow-up');
                if ($requestPage == 'qa' && $this->input->get('action') == 'qa' && $user_type != 'agent' 
                        && !in_array($contactCallDetail->status, $accessQAStatusArray)) {
                    $data['isViewPage'] = true;
                }
        }
        $status = !empty($contactCallDetail->status) ? $contactCallDetail->status : false;
        
        // for any user this lead as a view page while lead status "Approve" or "Reject"
        if (!empty($status) && ($status == 'Approve' || $status == 'Reject')) {
            $data['isViewPage'] = true;
        }
        //For Followup status: Any QA should be able to access it at any time - User type - QA
        if(!empty($status) && $action == 'qa' && $user_type != 'qa' && $status == 'Follow-up'){
            $data['isViewPage'] = true;
        }else if(!empty($status) && $action == 'view' && $user_type == 'qa'){
            $data['isViewPage'] = true;
        }
        // as per business rules sheet this condition checked
        // TODO - need to verify business rules once again..
        $data['isViewPage'] = ($this->do_not_call_ever_logic($contactCallDetail->call_disposition_id)) ? true : $data['isViewPage'];
        
        $contactCallDetail->logged_user_type = $user_type;        

        $data['status'] = $status;
        
        // breadcrumb set for regular contact detail page and add as a different person
        $finalCrumbs = $this->set_crumb($contactCallDetail, $controller_part, $data, $contactCallDetail->campaign_id, $list_id);

        $this->save_contact_visible($data, $loggedUserID, $contactCallDetail);
        
        if($this->app == 'mpg'){
            $this->set_mpg_data($data,$egCampaign, $contactCallDetail, $member_id, $callsModel);
        }
        if (isset($egCampaign->incentives_available) &&
            $egCampaign->incentives_available > 0) {
                $contactCallDetail->incentives_available =
                    $egCampaign->incentives_available;
                $campaignModel = $this->campaignModel;
                $getIncentive = $campaignModel->getIncentive($egCampaign->incentives_available);
                $contactCallDetail->incentive = !empty($getIncentive) ? $getIncentive[0]->incentive : '';
            }
        //check pureb2b consent from eg.contacts table
        $gdprConsent = $this->getGdprConsent($contactCallDetail->email, $contactCallDetail->eg_campaign_id, $contactsModel);
        $data['pureB2bConsent'] = $gdprConsent['pureb2bConsent'];
        $data['clientConsent'] = $gdprConsent['clientConsent'];
        
        //if PureMQL Campaign, Employee Size, Industry, and Company Rev should be editable
        $data['editableField'] = ($user_type == 'qa' && ($egCampaign->type == 'puremql' || $egCampaign->type == 'pureresearch' || $egCampaign->type == 'smartleads')) ? true : false;    
        //$data['editableField'] = true;
        $data['editableFieldAll'] = true;    
        $this->load->helper('common');
        $contactCallDetail->list_id = $list_id;
        $data['contactCallDetail'] = $contactCallDetail;
        $data['contactCallHistoryList'] = $contactCallHistoryList;
        $data['contact_email_history_list'] = $contact_email_history_list;
        $data['callDispositionList'] = $callsModel->getCallDispositions();
        $data['meta_title'] = 'View Contact';
        $data['crumbs'] = $finalCrumbs;
        $data['title'] = 'View Contact';
        $data['egCampaign'] = $egCampaign;
        return $data;
    }
    
    /**
     * 
     * @param type $data
     * @param type $contactCallDetail
     * @param type $resourceViewModel
     */
    function set_qa_in_progress(&$data, $contactCallDetail, $resourceViewModel){
        if ($data['qa'] && !$data['isViewPage'] && !empty($contactCallDetail->lead_id)) {
            $data['lead_id'] = $contactCallDetail->lead_id;
            // up_insert "is_qa_in_progress" and "QA" field for access this lead by logged qa user.
            if (!empty($contactCallDetail->status) && $contactCallDetail->status != 'In Progress' 
                    && $contactCallDetail->status != 'Follow-up' && $data["action"] == "qa" && !$contactCallDetail->is_qa_in_progress) {
                $resourceViewModel->set_qa_in_progress($data["lead_id"], $this->session->userdata('uid'));
            }
        }
    }
    
    function save_contact_visible(&$data,$loggedUserID,$contactCallDetail){
        $data['save_contact_visible'] = false;
        if (!empty($contactCallDetail->status) && ((isset($_GET['action']) && $_GET['action'] == 'qa' && $contactCallDetail->status == 'Reject') || 
                (isset($_GET['action']) && $_GET['action'] == 'view' && $contactCallDetail->status == 'Approve')) && 
                isset($contactCallDetail->qa) && $contactCallDetail->qa == $loggedUserID && $contactCallDetail->logged_user_type != 'agent') {
            $data['save_contact_visible'] = true;
        }
    }
    
    function is_qa_in_progress($action,$contactCallDetail,$data,$user_type){
        // Checking someone have already accept this lead for QA process.
        // can we remove condition for "$user_type != 'agent'" && !data['qa_accepted_by_user']
        if ($action == "qa" && $user_type != 'agent' && $contactCallDetail->is_qa_in_progress && !$data["qa_accepted_by_user"] && !$data['isViewPage']) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', "Someone already accepted to QA the lead");
            redirect('/dialer/leads');exit;
        }
    }
    
    function check_lead_id($lead_id){
        if ((empty($lead_id) || $lead_id <= 0)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Missing Lead ID, please check');
                redirect('/dialer/leads');
            }
    }
    
    function set_data_display($egCampaign, &$contactCallDetail, &$data, $resourceViewModel, $member_id = 0){
        // Get Custom questions
        $questions = $egCampaign->custom_questions;
        // Get Intent questions
        $intentQuestions = $egCampaign->intentQuestions;
        // Get Survey questions
        $surveyQuestions = $egCampaign->survey_questions;

        // Load library for campaign job filter section
        $this->load->helper('campaignjobdetail');
        $data['companySizeValues'] = getCompanySizeValues();
        $data['companyRevenueForm'] = getCompanyRevenue();
        $data['industriesValues'] = getIndustriesValues();
        $countryList = $this->Contacts_model->get_countries();
        $data['countries'] = $countryList;
        // get list of resources for send email on specific resource
        $data['resources'] = ($egCampaign->resources!="") ? $resourceViewModel->get_list_by_ids($egCampaign->resources) : "";
        // Load registration library
        $this->load->library('registration');
        $reg_obj = new Registration();
        
        $this->load->model('Members_model');
        $membersModel = new Members_model();
        // check if given member id is valid
        if (!empty($member_id) && $member_id > 0) {
            $member = $membersModel->get_one_from_tm_qa($member_id);
            $reg_obj->set_user_info($member);
        }
        $responses = array();
        if (isset($member_id) && (!empty($questions) || !empty($intentQuestions))) {
            $cqs = !empty($questions) ? $egCampaign->questions : "";
            if(!empty($intentQuestions)) {
                $cqs = !empty($cqs) ? $cqs . "," . $egCampaign->intent_questions : $egCampaign->intent_questions;
            }
            if(!empty($cqs)) {
                // get question responses
                $responses = $membersModel->get_question_responses_from_tm_qa($member_id, $cqs);
            }
        }
        if (isset($member_id) && ! empty($surveyQuestions)) {
            $responses = $membersModel->get_survey_responses_from_tm_qa($member_id, $egCampaign->questions);
        }
        // create Field for Custom Questions
        $question_fields = "";
        $regRules = unserialize($egCampaign->reg_rules);
        
        $regDataXml = simplexml_load_string($egCampaign->reg_data);
        
        //re-arrange custom questions UBER-40 Uber TM: As all users, I need to match the sequence of custom questions in Uber with Admin in order to make it friendly to the respondents
        if (!empty($questions)) {
            $newQuestions = array();
            foreach($questions as $question){
                $newQuestions["qid_".$question->id] = $question;
            }
            $rearrangedQuestions = array();
            if(!empty($regRules)){
                foreach($regRules as $regRule){
                    if(!empty($newQuestions[$regRule['field']])){
                        $rearrangedQuestions[$regRule['field']] = $newQuestions[$regRule['field']];
                    }
                }
            }
            if(!empty($rearrangedQuestions)){
                $questions = $rearrangedQuestions;
            }

            //get new labels, new form type from registration form from eg admin setup
            if(isset($regDataXml->fieldset) && !empty($regDataXml->fieldset[1]) && !empty($regDataXml->fieldset[1])){
                $xmlSubForm = $regDataXml->fieldset[1];
                foreach($xmlSubForm as $formElement){
                    
                    if(!empty($newQuestions["{$formElement->id}"])){
                        $label = (array) $formElement->label;
                        $formType = (array) $formElement->form_type;
                        $serverValidation = (array) $formElement->server_validation;
                        $questions["{$formElement->id}"]->question = !empty($label[0]) ? $label[0] : '';
                        $questions["{$formElement->id}"]->form_type = $formType[0];
                        /*if(!empty($serverValidation)){
                            $validation = str_replace("[",":", $serverValidation[0]);
                            $validation = str_replace("]","", $validation);
                            $validation = str_replace("max_length","maxlength", $validation);
                            if($formType != 'input'){
                                $validation = str_replace("maxlength","", $validation);
                            }
                            $questions["{$formElement->id}"]->serverValidation = $validation;
                        }*/
                    }
                }
                $data['customQuestions'] = $questions;
            }       
            $question_fields = $reg_obj->custom_questions($questions, $responses);
            //get new questions and list them
            $cqIds = array();
            foreach($questions as $cq){
                $cqIds[] = $cq->id;
            }
            $egCampaign->questions = implode(",", $cqIds);
        }
        
        $contactCallDetail->question_element_html = $question_fields;
        // create Field for Intent Questions
        $intent_question_fields = "";
        if (!empty($intentQuestions)) {
            $intent_question_fields = $reg_obj->intent_custom_questions($intentQuestions, $responses);
        }
        $contactCallDetail->intent_question_element_html = $intent_question_fields;
        // create Fields for Survey questions
        $survey_question_fields = "";
        if (!empty($surveyQuestions)) {
            $survey_question_fields = $reg_obj->
                survey_custom_questions($surveyQuestions, $responses);
        }
        $contactCallDetail->survey_question_element_html = $survey_question_fields;

        if (! empty($responses[0]->incentive_offered) && $responses[0]->incentive_offered == 1) {
            $contactCallDetail->incentiveOffered = 1;
        } else {
            $contactCallDetail->incentiveOffered = 0;
        }
        // Checking resource id is already come from campaign_contacts table which insert with CSV upload
        // currently we have remove resource id from uploading CSV
        if (isset($contactCallDetail->tlh_resource_id)) {
            $data['tlh_resource_id'] = $contactCallDetail->tlh_resource_id;
        }
        if (!empty($data['tlh_resource_id']) && $data['tlh_resource_id'] > 0) {
            $contactCallDetail->resource_id = $data['tlh_resource_id'];
        }
        
        //build GDPR form
        $isGdpr = false;
        if($egCampaign->gdpr_required && !empty($egCampaign->reg_data)/* && $this->session->userdata('user_type') == 'agent'*/){
            $gdprLabels = $this->getGdprLabels($regDataXml);
            if(!empty($gdprLabels[0]) && !empty($gdprLabels[1])){
                $data['pureB2bConsentLabel'] = $gdprLabels[0];
                $data['clientConsentLabel'] = $gdprLabels[1];
                $isGdpr = true;
            }
        }
        $data['gdprRequired'] = $isGdpr;
        $data['clientId'] = $egCampaign->client;
    }
    
    /**
     * return first param pub2b consent label, 2nd is client consent label
     * @param type $regDataXml
     * @return type
     */
    function getGdprLabels($regDataXml){
        $pureb2bConsent = 'qid_1808';
        $clientConsent = 'qid_1809';
        $qids = array($pureb2bConsent,$clientConsent);
        //$xml = simplexml_load_string($regData);
        $xml = $regDataXml;
        $pureB2bConsentLabel="";
        $clientbConsentLabel="";
        if(isset($xml->fieldset) && !empty($xml->fieldset[1]) && !empty($xml->fieldset[1])){
            $xmlSubForm = $xml->fieldset[1];
            foreach($xmlSubForm as $formElement){
                if(isset($formElement->id) && strstr($formElement->id,'qid_') && in_array($formElement->id, $qids)){
                    $label = (array) $formElement->label;
                    switch($formElement->id){
                        case $pureb2bConsent:
                            $pureB2bConsentLabel = $label[0];
                            break;
                        case $clientConsent:
                            $clientbConsentLabel = $label[0];
                            break;
                    }
    
                }
            }
            
        }
        return array($pureB2bConsentLabel,$clientbConsentLabel);
    }
    
    
    /**
     * 
     * @param boolean $data
     * @param type $contactCallDetail
     */
    function do_not_call_ever_logic($call_disposition_id){
        // as per business rules sheet this condition checked
        // TODO - need to verify business rules once again..
        //$do_not_call_ever_callDisposition_array = array('7', '11', '16', '17', '18','20','21');
        $do_not_call_ever_status = false;
        $do_not_call_ever_status = in_array($call_disposition_id, $this->do_not_call_ever_callDisposition_array);
        if(!empty($_POST['pureb2bConsent']) && $_POST['pureb2bConsent'] == 'no'){
            $do_not_call_ever_status = true;
        }
        return $do_not_call_ever_status;
    }

    /**
     * 
     * @param boolean $data
     * @param type $contactCallDetail
     */
    function is_nonworkable($call_disposition_id){
        // as per business rules sheet this condition checked
        // TODO - need to verify business rules once again..
        $is_nonworkable_array = array('7', '11', '14', '15', '16', '17', '18', '19','20','21','22','23','24','25','34');
        $is_nonworkable = false;
        $is_nonworkable = in_array($call_disposition_id, $is_nonworkable_array);
        return $is_nonworkable;
    }
    
    function set_mpg_data(&$data, $egCampaign, $contactCallDetail, $member_id, $callsModel){
        $data_mpg = $this->data_mpg($egCampaign, $contactCallDetail, $member_id, $callsModel);
        $data['member_id'] = $member_id;
        $data['regXml'] = $data_mpg['regXml'];
        $data['regRules'] = $data_mpg['regRules'];
        $data['jsRegRules'] = $data_mpg['jsRegRules'];
        $data['question_options'] = $data_mpg['question_options'];
        $data['cq_responses'] = $data_mpg['cq_responses'];
        $data['med_location'] = $data_mpg['med_location'];
        $data['med_country'] = $data_mpg['med_country'];
        
    }
    
    function set_crumb($contactCallDetail, $controller_part, $data, $contactCallCampaignID, $list_id){
        // breadcrumb set for regular contact detail page and add as a different person
        $thirdCrumb = '';
        $finalCrumbs = '';
        if (!empty($contactCallDetail->first_name)) {
            $thirdCrumb = $contactCallDetail->first_name . ' ' . $contactCallDetail->last_name;
        }
        if (!empty($controller_part) && $controller_part == 'leads') {
            $finalCrumbs = $this->crumbs . ' > <a href="/dialer/leads">Leads Status</a> > ' . htmlspecialchars($thirdCrumb);
        } else {
            if ($data['isAddPage']) {
                $thirdCrumb = "New Contact";
            }
            if($this->session->contactdata("ContactFilter")){
                $middle_crumb_menu = '<a href="/dialer/contacts/index/' . $contactCallCampaignID .'/'.$list_id.'/contactsort">Contacts</a> > ';
            }else{
                $middle_crumb_menu = '<a href="/dialer/contacts/index/' . $contactCallCampaignID .'/'.$list_id. '">Contacts</a> > ';
            }
            $finalCrumbs = $this->crumbs . ' > '.$middle_crumb_menu . htmlspecialchars($thirdCrumb);
        }
        return $finalCrumbs;
    }
    
    function check_url(&$data){
        $controller_part = "";
        // Load - user_agent - library for get Referrer URL and set into Back button
        $this->load->library('user_agent');
        $referrerURL = $this->agent->referrer();
        $data['referrerURL'] = "";
        if (!empty($referrerURL)) {
            $components = explode('/', $referrerURL);
            if (!empty($components[3])) {
                $controller_part = $components[3];
            }
            $data['referrerURL'] = $referrerURL;
        }
        return $controller_part;
    }
    
    /**
     * check if today the contact is called more than 3 times already
     * @param type $data
     * @param type $callsModel
     * @param type $user_type
     * @param type $contactCallDetail
     */
    function isTodayExceedCallDial(&$data, $callsModel, $user_type, $contactCallDetail)
    {
        $callLimit = $this->config->item('call_limit');
        
        if ($user_type != 'qa') {
            $isExceeded = $callsModel->CheckTodayCallDialledLimit($contactCallDetail->id, $callLimit);
            
            if (!empty($isExceeded)) {
                $data['todayCallDiallerMessage'] = "Sorry, You can't call/dial more than {$callLimit} times per day.";
                $data['isTodayExceedCallDial'] = true;
            }
        }
    }
    
    /**
     * check if today the contact is called and set to callback more than 2 times already
     * @param type $callsModel
     * @param type $contactCallDetail
     */
    
    function checkTodayCallbackDial($callsModel, $campaignContactId)
    {
        $data = array();
        $isExceeded = $callsModel->checkTodayCallback($campaignContactId);

        if(!empty($isExceeded) && $isExceeded[0]->callbackCount > 0) {
            $message = "Callback limit reached.";
            $isTodayExceedCallDial = true;
            if ($isExceeded[0]->callbackCount == 1) {
                $message = "WARNING: If you need to schedule another Callback, ";
                $message .= "you are about to reach the limit. You may reschedule tomorrow onward.";
                $isTodayExceedCallDial = false;
                $data['errorType'] = 'warning';
            }
            $data['todayCallDiallerMessage'] = $message;
            $data['isTodayExceedCallDial'] = $isTodayExceedCallDial;
        }
        return $data;
    }
    
    /**
     * check first if contact is already assigned to another user. If yes, redirect,if no lock to the user who opened it first
     * @param type $contactsModel
     * @param type $contactCallDetail
     * @param type $data
     * @param type $list_id
     */
    function lock_unassigned_contact($contactsModel, $contactCallDetail, $data, $list_id){
        $is_locked = $contactsModel->is_locked_by_other($contactCallDetail->id,$this->session->userdata('uid'));
        if($is_locked) {
            // check other user working on this contact or not
            $this->redirect_if_locked($data, $contactCallDetail->campaign_id, $list_id);
        }
        if($this->input->get('action') != 'view' && $this->input->get('action') != 'qa'){
            $this->lock_contact($contactCallDetail->id, $contactsModel);
        }
    }
    
    function user_is_agent(&$data,$action,$resourceViewModel,$lead_id,$lead_status){
        if($action != 'qa' && $action != 'view'){
            $agent = $resourceViewModel->get_agent_lead_id($lead_id);
            if (!empty($agent[0])) {
                $accessAgentStatusArray = array('', 'In Progress', 'Follow-up');
                if ($this->session->userdata('uid') == $agent[0]->agent_id && in_array($lead_status, $accessAgentStatusArray)) {
                    $data["user_is_agent"] = true;
                    $data['isViewPage'] = false;
                }
            }
        }
        return false;
    }
    
    /**
     * 
     * @param type $contactCallDetail
     * @param type $loggedUserID
     * @param type $campaignModel
     */
    function check_agent_session($contactCallDetail, $loggedUserID, $campaignModel){
        $agentSessionCampaignID = $this->session->userdata('AgentSessionCampaignID');
        if (empty($agentSessionCampaignID)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
            redirect('/dialer/campaigns');
        } else {
            $this->check_campaign_active_session($agentSessionCampaignID, $contactCallDetail->campaign_id);
        }

        // this campaign assign to agent or not
        $isCampaignAssign = $campaignModel->IsCampaignAssignToAgent($contactCallDetail->campaign_id, $loggedUserID);
        if ($isCampaignAssign == 0) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You have to not assign this campaign and contacts, please contact to administrator!');
            redirect('/dialer/campaigns');
        }
    }
    
    /**
     * 
     * @param type $campaignContactListID
     * @param type $contactCallDetail
     * @param type $loggedUserID
     * @param type $contactsModel
     * @param type $callsModel
     * @return boolean
     */
    function can_follow_up(&$data,$campaignContactListID, $contactCallDetail, $loggedUserID, $contactsModel, $callsModel){
        //Check QA Follow-up with current time is exceed to one week or not
        $isFollowUpOneWeek = $callsModel->IsFollowUpOneWeek($campaignContactListID);
        if (!empty($isFollowUpOneWeek)) {
              // Access only agent, Agent's Team leader & any Qa, If Follow-up lead time not exceed to one week
              if (($loggedUserID == $isFollowUpOneWeek->qa) || ($loggedUserID == $isFollowUpOneWeek->agent_id) || ($loggedUserID == $isFollowUpOneWeek->user_team_leader_id)) {
                  $data['isViewPage'] = false;
                  $data['user_team_leader_id'] = true;
              }
          } else {
              // unlock user : all users can access If Follow-up lead time exceed to one week
              $this->unlock_contact($contactCallDetail->id, $contactsModel);

              // after followup time out then this lead have "QA" and "QA in progress" field should be blank.
              $update_at = date('Y-m-d H:i:s', time());
              $leadRetractData['id'] = $contactCallDetail->lead_id;
              $leadRetractData['updated_at'] = $update_at;
              $leadRetractData['qa'] = '';
              $leadRetractData['is_qa_in_progress'] = '';
              $callsModel->updateRetractLeadHistory((object)$leadRetractData);
          }
    }
    
    /**
     * Lead can only be access by the QA who is already doing the QA.
     * @param type $campaignContactListID
     * @param type $loggedUserID
     * @param type $callsModel
     * @return boolean
     */
    function can_user_qa_lead($campaignContactListID, $loggedUserID, $callsModel){
        //Checking: Any one can accept this lead as an QA process or not
        ////Uber bug fixes and rules changes
        $getQALeadCount = $callsModel->getQALeadAccessCount($campaignContactListID);
        if (count($getQALeadCount) > 0 && !empty($getQALeadCount)) {
            //lead can access only those users whom have worked as an QA process, other QA can only view the page
            if (!empty($getQALeadCount->qa) && $getQALeadCount->qa != $loggedUserID) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 
     * @param type $campaignContactListID
     * @param type $contactCallDetail
     * @param type $callsModel
     * @return boolean
     */
    function is_callback_by_agent($getAgentLeadCount, $campaignContactListID, $contactCallDetail, $callsModel, $loggedUserID){
        if (count($getAgentLeadCount) > 0 && !empty($getAgentLeadCount)) {
            // If callback is set more than one time and by two different agent then last agent from agent_lead table would have access to edit the contact detail data
            if (!empty($contactCallDetail->call_disposition_id) && $contactCallDetail->call_disposition_id == '2') {
                $isCallBackByAgent = $callsModel->IsCallBackByAgent($campaignContactListID);
                if (!empty($isCallBackByAgent)) {
                    //if last agent called is resigned then any agent can open the contact
                    if ($loggedUserID != $isCallBackByAgent->agent_id && $isCallBackByAgent->status == 'Active') {
                        return false;
                    } else if ($getAgentLeadCount->agent_id != $loggedUserID && $getAgentLeadCount->status == 'Active') {
                         return false;
                    }
                }
            }
        }
        return true;
    }
    
    
    /**
     * 
     * @param type $contactCallDetail
     * @param type $callsModel
     * @param type $list_id
     * @return type
     */
    function get_eg_campaign($contactCallDetail,$callsModel,$list_id){
        if (!empty($contactCallDetail->eg_campaign_id) && $contactCallDetail->eg_campaign_id > 0) {
            $egCampaignID = $contactCallDetail->eg_campaign_id;
        } else {
            $egCampaignID = 0;
        }
        $egCampaign = $callsModel->getEGCampaignDataByID_calls($egCampaignID);
        if (empty($egCampaign)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Eg Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/contacts/index/' . $contactCallDetail->campaign_id.'/'.$list_id);
        }else{
            $egCampaign->survey_questions = array();
            $egCampaign->custom_questions = array();
            $egCampaign->intentQuestions = array();
            if ($egCampaign->type == 'pureresearch' || $egCampaign->type == 'puremql' || $egCampaign->type == 'smartleads') {
                // Get Survey questions
                $egCampaign->survey_questions = $callsModel->getEGSurveyQuestion('WHERE id in (' . $egCampaign->questions . ')');
            } else {
                // Get Custom questions
                if (!empty($egCampaign->questions)) {
                    $egCampaign->custom_questions = $callsModel->getEGCampaignQuestion('WHERE id in (' . $egCampaign->questions . ')');
                }
                // Get Intent questions
                if (!empty($egCampaign->intent_questions)) {
                    $egCampaign->intentQuestions = $callsModel->getEGCampaignQuestion('WHERE id in (' . $egCampaign->intent_questions . ')');
                }
            }
            return $egCampaign;
        }
    }
    
    /**
     * 
     * @param type $callsModel
     * @param type $contactCallDetail
     * @return type
     */
    function get_call_history($callsModel, $contactCallDetail){
        $original_call_history_id = null;
        $fields = 'id, id as call_history_id';
        $plivo_data = $callsModel->fetch_plivo_com_record($contactCallDetail->campaign_contact_id,$fields);
        if(!empty($plivo_data)){
            $original_call_history_id = $plivo_data['call_history_id'];
        }
        return $original_call_history_id;
    }
    
    /**
     * 
     * @param type $contactCallDetail
     * @param type $list_id
     */
    function check_if_contact_exist($contactCallDetail, $data, $list_id=null){
        if(empty($contactCallDetail->id)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Contact does not exist for the campaign lead.');
             if ($data['Qaing']) {
                    redirect('/dialer/leads');
                } else {
                    redirect('/dialer/contacts/index/' . $data['campaign_id'] .'/'.$list_id);
                }
        exit;}
    }
    
    function redirect_if_locked($data,$campaign_id,$list_id=null){
        $this->session->set_flashdata('class', 'bad');
        $this->session->set_flashdata('msg', 'Sorry, you can`t access this lead. Another QA Or User is currently working on it.');
        if ($data['Qaing']) {
            redirect('/dialer/leads');
        } else {
            redirect('/dialer/contacts/index/' . $campaign_id.'/'.$list_id);
        }
        exit;
    }
    
    function submit($list_id, $requestPage = null){
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
//echo "post data is : ";
        //print_r($_POST);
        //exit(0);
        //if change dispo only
        if(!empty($_POST['change_dispo'])){
            $this->liftDispo($callsModel);
        }else{
            if (!empty($_POST['decision'])) {
                if ($_POST['decision'] == 'Save Contact')
                    $this->update_contact();
                else
                    $this->saveContactCallDetail($list_id, $callsModel);

                exit;
            }
        }
    }

    function change_dispo_prev($callsModel){
        //insert new calldisposition_history record;
            $created_date = date('Y-m-d H:i:s', time());
            $line_arr = '("'.$_POST['lead_id'].'","'.$_POST['last_call_history_id'].'","'.$this->session->userdata('uid').'","'.$_POST['call_disposition'].'","'.$created_date.'")';
            $callsModel->insert_call_disposition_history($line_arr);
            //update call_disposition_id from lead_history table and update status = 'In Progress'; if lead for qual set to pending.
            $update_at = date('Y-m-d H:i:s', time());
            $lead_history = array();
            $lead_history['id'] = $_POST['lead_id'];
            $lead_history['updated_at'] = $update_at;
            $lead_history['call_disposition_id'] = $_POST['call_disposition'];
            if($_POST['call_disposition'] == 1){
                $lead_history['status'] = 'Pending';
            }else{
                $lead_history['status'] = 'In Progress';
            }
            $callsModel->updateRetractLeadHistory((object)$lead_history);

            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();
            $contactDetail = $contactsModel->get_one_contact($_POST['contact_id']);
            if(isset($contactDetail->do_not_call_ever) && $contactDetail->do_not_call_ever == 1 && !in_array($_POST['call_disposition'], array(18,19))){
                $callStatusUpdateArray = array();
                $callStatusUpdateArray['do_not_call_ever'] = 0;
                $contactsModel->update_contact($_POST['contact_id'], $callStatusUpdateArray);
            }

            redirect('/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$_POST['hidden_list_id']);exit;
    }
    
    function liftDispo($callsModel) {
        //check if contact can be lifted
        $campaignContactId = $_POST['campaign_contact_id'];
        $campaignId = $_POST['campaign_id'];
        $listId = $_POST['hidden_list_id'];
        $campaignContactsData = $callsModel->getCampaignContacts($campaignContactId);
        
        if(empty($campaignContactsData)){
            $msg = 'Contact not found.';
            $this->redirectToCallQueue('bad', $msg, $campaignId, $listId);
        }
        
        if($campaignContactsData->lifted == 1){
            $msg = 'Contact already lifted.';
            $this->redirectToCallQueue('bad', $msg, $campaignId, $listId);
        }
        
        $this->load->model('Contacts_model');
        $contactModel = new Contacts_model();
        
        $campaignContactDataUpdate = array(
            'lifted' => 1, 
            'updated_at' => date('Y-m-d H:i:s', time())
            );
        $contactModel->updateCampaignContact($campaignContactId, $campaignContactDataUpdate);
        
        $msg = 'Contact successfully lifted.';
        $this->redirectToCallQueue('good', $msg, $campaignId, $listId);
    }
    
    function redirectToCallQueue($class, $msg, $campaignId, $listId){
        $this->session->set_flashdata('class', $class);
        $this->session->set_flashdata('msg', $msg);
        redirect('/dialer/contacts/index/' . $campaignId.'/'.$listId);
    }
    
    function arrangeCQResponses($cq_responses){
        $new_cq_responses = array();
        foreach($cq_responses as $cq){
            $question_id = ($cq->question_id == 26) ? 0 : $cq->question_id;
            $new_cq_responses["qid_{$question_id}"] = $cq->response;
        }

        return $new_cq_responses;
    }

    public function calldetail_validation_check($contactCallDetail,$user_type,$campaignContactListID,$loggedUserID,$callsModel,$Qaing,$isViewPage){
        // sanity check passed contact ID is valid or not
        if (empty($contactCallDetail)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Contact not found, please make sure that the Contact ID is correct!');
            redirect('/dialer/campaigns');
        }

        if (empty($Qaing) && !$isViewPage) {
            /*$check_multiple_lock_contact = $callsModel->get_previous_lock_detail_by_logged_user($contactCallDetail->campaign_id,$loggedUserID);

            $previous_lock_campaign_contact_id = "";
            if(!empty($check_multiple_lock_contact)){
                $previous_lock_campaign_contact_id = $check_multiple_lock_contact['campaign_contact_id'];
            }*/
            // checking: same user can not access multiple contact at same time.
            $list_id = $callsModel->getListID($campaignContactListID,$contactCallDetail->campaign_id);
            $this->check_multiple_contact_access($loggedUserID, $contactCallDetail->id,$contactCallDetail->campaign_id,$list_id); //
        }

        //check agent user is signed-in into campaign or not
        if ($user_type == 'agent') {
            $agentSessionCampaignID = $this->session->userdata('AgentSessionCampaignID');
            if (empty($agentSessionCampaignID)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                redirect('/dialer/campaigns');
            } else {
                $this->check_campaign_active_session($agentSessionCampaignID, $contactCallDetail->campaign_id);
            }
        }

        // this campaign assign to agent or not
        if ($user_type == 'agent') {
            $this->load->model('Campaigns_model');
            $isCampaignAssign = $this->Campaigns_model->IsCampaignAssignToAgent($contactCallDetail->campaign_id, $loggedUserID);
            if ($isCampaignAssign == 0) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You have to not assign this campaign and contacts, please contact to administrator!');
                redirect('/dialer/campaigns');
            }
        }
    }

    function _insert_member_qa($member_model, $post_data)
    {
        //Get member normalization rules to generate new member responses like silo and job level.
        //These values are not submitted with the file upload. This is loaded once and will be used in the loop below.
        $normalization_rules = $member_model->get_member_normalization_rules();

        //Set field counter
        $num_fields = 0;

        //Set member object properties
        $member = new Member();

        if (!empty($post_data['call_disposition'])) {
            $allowedDoNotCallDispositionCreateLeadArray = array('7', '11', '16', '17', '18','20','21');//'7', '11', '16', '17', '18'
            $allowedDoNotCallStatus = in_array($_POST['call_disposition'], $allowedDoNotCallDispositionCreateLeadArray);
            if ($allowedDoNotCallStatus) {
                $post_data['do_not_call'] = 1;
            }
        }

        $num_fields = $this->setMemberObjectDataEg($post_data, $member, $num_fields);
        // as per EG-script
        if (isset($member->job_title)) {
            $this->load->library('Normalize');
            $normalize = new Normalize();
            $member->password = $normalize->generate_password($this->app);
            $member->job_level = $normalize->job_level($member->job_title, $normalization_rules);
            $num_fields++;
            $member->silo = $normalize->silo($member->job_title, $normalization_rules);
            $member->ml_title = $normalize->ml_title($member->silo, $member->job_level);
            $member->updated_by = $this->session->userdata('uid');
        }

        if ($num_fields >= 14) {
            $member->type = 'M'; // Member
        } else {
            $member->type = 'I'; // Incomplete member
        }

        // Site id set as per added in campaign
        $member->site_id = $post_data['campaign_site']; // Default 1 Value set in EG Source - Telemarketing - _insert_member function
        $member->ip = $_SERVER['REMOTE_ADDR'];
        $member->is_valid = true;
        $member->can_email = true;
        $member->phone_verified = 1;
        $member->last_login = date('Y-m-d H:i:s', time());
        $member->created_at = date('Y-m-d H:i:s', time());

        if(!empty($post_data['original_owner']) && $post_data['original_owner'] == "Pureb2b"){
            $member->original_owner = $post_data['original_owner'];
        }
        //Insert new member to DB
        $member_id = $member_model->insert_member_qa($member);
        
        return $member_id;
    }

    // each contact update should be store in member_qa_history table
    function _create_member_qa_history($membersModel, $member_id)
    {
        $membersModel->insert_members_qa_history($member_id);
    }

    function _log_lead_from_partner($callsModel, $member_id, $campaign_id = "", $distinct_leads = "", $post_data, $create_parent = false, $source = 'telemarketing', $site = 1)
    {
        $resource_id = '';
        if (isset($post_data['resource_id'])) {
            $resource_id = $post_data['resource_id'];
        }

        $this->load->model('Resourceview_model');
        $resourceViewModel = new Resourceview_model();
        
        $resourceView = $this->set_resource_view($site, $resource_id, $member_id, $campaign_id, $create_parent, $source, $distinct_leads, $resourceViewModel);

        // insert record in call_disposition_history
        // insert record in agent_lead table
        $this->insertCallDisposition($callsModel);
        //update campaign contacts, insert lead status, insert notes
        $callsModel->updateCampaignContact($_POST);
        $resourceView_id = 0;
        if($post_data['call_disposition'] == '1') {
            
            $tm_lead_history = $this->set_tm_lead_history($resourceView);
            
            $existed_lead_id = new stdClass();
            //Check already lead was created for same campaign contact id
            if($_POST['lead_id'] == "" || $_POST['lead_id'] == 0){
               $existed_lead_id = $callsModel->check_already_created_lead_by_campaign_contact_id($post_data['campaign_contact_id']);
            }else{
               $existed_lead_id->id = $_POST['lead_id'];
            }
                                
            //echo $_POST['lead_id']; print_r($existed_lead_id); exit;
            if(!empty($existed_lead_id)){
                $_POST['lead_id'] = $existed_lead_id->id;
            }
            
            if (isset($_POST['lead_id']) && $_POST['lead_id'] > 0) {
                $resourceView_id = $this->update_lead_history($tm_lead_history, $resource_id, $member_id, $resourceViewModel, $callsModel);
            } else {
                //insert into lead history only if the lead is LFQ
                $resourceView_id = $resourceViewModel->insert_temp($tm_lead_history,$callsModel);
                
            }

            // unset some object variable as per declare in resources class
            $this->unset_resource_views_objects($resourceView);

        }

        return $resourceView_id;
    }

    function insertCallDisposition($callsModel) {
        $post_data = $_POST;
        $loggedUserID = $this->session->userdata('uid');
        $multi_call_id = (!empty($post_data['all_call_history_id'])) ? explode(',', $post_data['all_call_history_id']) : 0;
        $countMultiCallId= count($multi_call_id);
        if(!empty($post_data['original_call_history_id']) && empty($post_data['newCallHistoryId'])){
            $last_call_history_id = $post_data['original_call_history_id'];
        }else if($countMultiCallId == 1){
            // get proper last call history id for set last recording url for that particular logged user
            $last_call_history_id = $this->get_last_call_history_id($multi_call_id, $loggedUserID, $callsModel);
        }else if(!empty($post_data['newCallHistoryId'])){
            $last_call_history_id = $post_data['newCallHistoryId'];
        }
        
        // insert Call disposition history
        $this->insert_call_disposition_history($last_call_history_id, $post_data['campaign_contact_id'], $loggedUserID, $multi_call_id, $callsModel);
        //$this->insert_agent_lead($post_data['campaign_contact_id'], $created_at);
    }
    
    function unset_resource_views_objects(&$resourceView){
        unset($resourceView->call_disposition_update_date);
        unset($resourceView->call_disposition_id);
        unset($resourceView->status);
        unset($resourceView->campaign_contact_id);
        unset($resourceView->campaign_id);
        unset($resourceView->reference_link);
        unset($resourceView->resource_name);
    }
    
    /**
     * 
     * @param type $resourceView_id
     * @param type $resourceView
     */
    function insert_agent_lead($campaign_contact_id, $created_at){
        $agentLead = array();
        // TODO - there are ONLY two function in this agentlead model
        $this->load->model('Agentlead_model');
        $agentLeadModel = new Agentlead_model();
        $agentLead['campaign_contact_id'] = $campaign_contact_id;
        $agentLead['agent_id'] = $this->session->userdata('uid');
        $agentLead['submitted_at'] = $created_at;
        //insert to agent_lead table
        //$agentLeadModel->insert((object)$agentLead);
    }
    
    /**
     * 
     * @param type $last_call_history_id
     * @param type $resourceView_id
     * @param type $loggedUserID
     * @param type $multi_call_id
     * @param type $callsModel
     * @return type
     */
    function insert_call_disposition_history($last_call_history_id, $campaign_contact_id, $loggedUserID, $multi_call_id, $callsModel){
        $call_disposition_history_data['call_history_id'] = $last_call_history_id;

        $call_disposition_history_data['campaign_contact_id'] = $campaign_contact_id;
        if (!empty($_POST['call_disposition'])) {
            $call_disposition_history_data['call_disposition_id'] = $_POST['call_disposition'];
        }
        if ($_POST['call_disposition'] == '2' && !empty($_POST['call_disposition_update_date'])) {
            $call_disposition_history_data['call_disposition_update_at'] = date('Y-m-d H:i:s', strtotime($_POST['call_disposition_update_date']));
        }
        $call_disposition_history_data['user_id'] = $loggedUserID;
        $call_disposition_history_data['created_at'] = date('Y-m-d H:i:s', time());
        $countMultiCallId = count($multi_call_id);
        if($countMultiCallId > 1){
            $line_arr = "";
            for($i=0;$i<count($multi_call_id);$i++){
                $add_comma = (($i+1) != count($multi_call_id)) ? "," : "";
                $line_arr .= '("'.$campaign_contact_id.'","'.$multi_call_id[$i].'","'.$call_disposition_history_data['user_id'].'","'.$call_disposition_history_data['call_disposition_id'].'","'.$call_disposition_history_data['created_at'].'")'.$add_comma;
            }
        }else if($countMultiCallId == 1 && isset($multi_call_id[0]) && $multi_call_id[0] != $last_call_history_id){
            $line_arr = '("'.$campaign_contact_id.'","'.$multi_call_id[0].'","'.$call_disposition_history_data['user_id'].'","'.$call_disposition_history_data['call_disposition_id'].'","'.$call_disposition_history_data['created_at'].'")';
            $last_call_history_id = $multi_call_id[0];
        }else{
            $line_arr = '("'.$campaign_contact_id.'","'.$call_disposition_history_data['call_history_id'].'","'.$call_disposition_history_data['user_id'].'","'.$call_disposition_history_data['call_disposition_id'].'","'.$call_disposition_history_data['created_at'].'")';
        }

        //if lifted, get last disposition  submitted by agent and update it.
        if(!empty($_POST['isLifted']) && $_POST['isLifted'] == 1){
            $dataSet = array(
                'call_disposition_id' => $call_disposition_history_data['call_disposition_id']
                    );
            
            $where = array(
                'call_history_id' => $last_call_history_id,
                'user_id' => $call_disposition_history_data['user_id'],
                'call_disposition_id' => $_POST['previous_call_disposition_id'],
                'campaign_contact_id' => $campaign_contact_id
                    );
            
            $callsModel->updateCallDispositionHistory($dataSet, $where);
        }else{
            $callsModel->insert_call_disposition_history($line_arr);
        }
        if(!empty($_POST['is_add_page'])){
            $dataSet = array('call_disposition_id' => $call_disposition_history_data['call_disposition_id'],
                'campaign_contact_id' => $campaign_contact_id,
                'updated_at' => date('Y-m-d H:i:s', time())
                    );
        }else{
            $dataSet = array('call_disposition_id' => $call_disposition_history_data['call_disposition_id'],
                'updated_at' => date('Y-m-d H:i:s', time()));
        }    
        $where = array('call_history_id' => $last_call_history_id);
        $callsModel->updateCallHistoryCampaignContact($dataSet,$where);
    }
    
    function get_last_call_history_id($multi_call_id, $loggedUserID, $callsModel){
        $last_call_history_id = "";
        // get proper last call history id for set last recording url for that particular logged user
        if(isset($_POST['campaign_contact_id'])){
            if (!empty($_POST['is_add_page'])){
                // When agent call for more than one time at single time open any contact so get thier multiple call_history id and update it to plivo_communication table
                $plivo_communication_data['campaign_contact_id'] = $_POST['campaign_contact_id'];
                $last_call_id = (count($multi_call_id) > 1) ?  $multi_call_id : $_POST['last_call_history_id'];
                $callsModel->update_plivo_communication_detail($last_call_id, $plivo_communication_data);
            }
            $last_call_id = $callsModel->getLastCallHistoryId($_POST['campaign_contact_id']);
            if (isset($last_call_id['call_history_id'])){
                $last_call_history_id = $last_call_id['call_history_id'];
            }else{
                $last_call_history_id = $_POST['last_call_history_id'];
            }
        }
        return $last_call_history_id;
    }
    function update_lead_history($tm_lead_history, $resource_id, $member_id, $resourceViewModel, $callsModel){
        $is_agent = false;
        $tmp = array();
        $userType = $this->session->userdata('user_type');
        if ($userType != 'qa') {
            $is_agent = true;
            $tmp['agent_id'] = $this->session->userdata('uid');
        }
        $resource_name = "";
        if (isset($_POST['resource_name'])) {
            $resource_name = $_POST['resource_name'];
        }
        $timestamp = date('Y-m-d H:i:s', time());
        $tmp['id'] = $_POST['lead_id'];
        $tmp['status'] = $tm_lead_history->status;
        $tmp['updated_at'] = $timestamp;
        $tmp['qa'] = $this->session->userdata('uid');
        $tmp['is_qa_in_progress'] = 0;
        $tmp['resource_id'] = $resource_id;
        $tmp['resource_name'] = $resource_name;
        $tmp['member_id'] = $member_id;
       

         $resourceViewModel->update_tmp($callsModel,(object)$tmp, $is_agent);
         return $_POST['lead_id'];
    }
    
    function set_tm_lead_history($resourceView){
        $tm_lead_history = $resourceView;
        
        $allowedCallDispositionCreateLeadArray = array('1'); //array('1', '14', '15', '16', '17', '18');
        $allowedCreateLeadStatus = false;
        if (!empty($_POST['call_disposition'])) {
            $allowedCreateLeadStatus = in_array($_POST['call_disposition'], $allowedCallDispositionCreateLeadArray);
        }
        if ($this->session->userdata('user_type') != 'qa') {
            if (!$allowedCreateLeadStatus) {
                $tm_lead_history->status = 'In Progress';
            } else {
                $tm_lead_history->status = 'Pending';
            }
        } else {
            $tm_lead_history->status = null;
        }

        if (!empty($_POST['campaign_site'])) {
            $tm_lead_history->site_id = $_POST['campaign_site'];
        }
        if (!empty($_POST['resource_name'])) {
            $tm_lead_history->resource_name = $_POST['resource_name'];
        } else {
            $tm_lead_history->resource_name = NULL;
        }
        $tm_lead_history->agent_id = $this->session->userdata('uid');
        $tm_lead_history->campaign_contact_id = $_POST['campaign_contact_id'];
        return $tm_lead_history;
    }
    
    function set_resource_view($site,$resource_id,$member_id,$campaign_id,$create_parent,$source,$distinct_leads,$resourceViewModel){
        $resourceView = new Resourceview();
        $resourceView->site_id = $site;
        $resourceView->resource_id = $resource_id;
        $resourceView->member_id = $member_id;
        $resourceView->qualified = 0;
        $resourceView->is_downloaded = 0;
        $resourceView->campaign_id = $campaign_id;
        $resourceView->ip = $_SERVER['REMOTE_ADDR'];
        if ($create_parent === true) {
            $resourceView->source = 'telemarketing';
        } else {
            $resourceView->source = $source;
        }

        if (isset($_POST["created_at"])) {
            $created_at = $_POST["created_at"];
        } else {
            $created_at = date('Y-m-d H:i:s', time());
        }
        $resourceView->partner = NULL;
        //$resourceView->notes = $_POST['notes'];
        $resourceView->created_at = $created_at;
        $resourceView->updated_at = $created_at;
        if (!$distinct_leads) {
            $resourceView->report_display = 1;
        } else {
            $set_report_display = $resourceViewModel->is_set_report_display($member_id, $campaign_id);
            if (!$set_report_display) {
                $resourceView->report_display = 1;
            }
        }

        // set admin user id of the person creating the lead; only used to insert into the the notes table
        // $resourceView->user_id = $resourceView->notes != '' ? $this->session->userdata('uid') : null;
        $resourceView->user_id = $this->session->userdata('uid');
        return $resourceView;
    }
    
    /**
     * Check if member_id is set in $_POST, if yes then return that member_id
     * if not, then check if the email from post exists on eg->members table
     *  if the member exists, then return the existing member->id
     *  if the member does not exist, then insert the member on eg->members table and return the last id inserted.
     *  Also set the new member id in the $_POST['new_member']
     * @param type $membersModel
     * @param type $list_id
     */
    function get_member_id($membersModel, $list_id, $contactsModel=null){
        if (!empty($_POST['member_id'])) {
            $member_id = $_POST['member_id'];
        } else {
            $check_emails_exist = $membersModel->check_emails_exist($_POST['email']);
            if(!empty($check_emails_exist)){
                $member_id = $check_emails_exist->id;
            } else{
                $member_id = $this->_insert_member($membersModel, $_POST,$list_id);
                if($member_id<=0){
                    if($contactsModel == null){
                            $this->load->model('Contacts_model');
                            $contactsModel = new Contacts_model();
                        }
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong while inserting new member.');
                    redirect('/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id);
                    
                    $msg = 'Sorry, Oops! Something went wrong while inserting new member.';
                    $path = '/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id;
                    $this->unlock_next_contact_and_redirect($contactsModel, $msg, $path);
                }
                $_POST['new_member'] = $member_id;
            }
            
        }
        return $member_id;
    }
    
    public function process_agent($callsModel,$list_id)
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();
        
        // apply validation for mandatory fields.
        $this->ContactCallFormValidation();
        if ($this->form_validation->run() == FALSE) {// && !$qa
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please fill up mandatory fields.');
            redirect('/dialer/calls/index/' . $_POST['campaign_contact_id'].'/'.$list_id);
        } else {
            $callData = $this->input->post();
            $campaign_id = (!empty($callData['campaign_id'])) ? $callData['campaign_id'] : '';
            //check campaign working on
            $this->validate_campaign($campaign_id);
            
            $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
            
            $this->agent_check_campaign_sign_in($campaign_id, $agentSessionCampaignId, $contactsModel);

            // set variable for logged user id
            $loggedUserID = $this->session->userdata('uid');

            /*
            ** Validate if the user can still process this contact
            ** If not, redirect the user to the call queue with error message
            ** Also, skip validation for manual form and add diff form
            */
            if(!$callsModel->isLeadWorkable($_POST['campaign_contact_id']) && $_POST['is_manual_create'] == 'false' && empty($_POST['is_add_page']) && $this->session->userdata('user_type') == 'agent')
            {
                $canProcess = false;

                if($callsModel->isLeadForFollowup($_POST['campaign_contact_id'])) {
                    $canProcess = true;
                }
                
                if(!empty($_POST['isLifted']) && $_POST['isLifted'] == 1){
                    $canProcess = true;
                }
                    
                    
                if(!$canProcess) {

                    // unlock if it's agent's contact
                    $contactsModel->unlockContact($_POST['contact_id'], ' AND locked_by = ' . $loggedUserID);

                    // redirect
                    $msg = 'Contact in QA progress or already disposed as non-workable.';
                    $path = '/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id;
                    $this->unlock_next_contact_and_redirect($contactsModel, $msg, $path);
                }
            }

            //agent submit and go to the next
            //TODO: This query should be call while user submit with go to next contact
            if (isset($_POST['decision']) && $_POST['decision'] == 'Submit and go to next Contact') {
                $this->submit_go_to_next($list_id, $campaign_id, $contactsModel, $callsModel);
            } else {
                if (!empty($_POST['is_add_page']) && !empty($_POST['contact_id'])){
                    //lead is generated so please unlock the contact
                    $this->unlock_contact($_POST['contact_id'], $contactsModel);
                }
            }
            
            
            // save dispo and redirect immediately for calls that weren't answered.
            // redirect to contacts list
            if (!empty($post_data['call_disposition'])) {
                $notAnsweredDispo = array('7', '9', '11', '13','16');
                $notAnswered = in_array($_POST['call_disposition'], $notAnsweredDispo);
                if ($notAnswered) {
                    $this->insertCallDisposition($callsModel);
                    //update campaign contacts, insert lead status, insert notes
                    $callsModel->updateCampaignContact($_POST);
                    //lead is generated so please unlock the contact
                    $this->unlock_contact($_POST['contact_id'], $contactsModel);
                    
                    return $campaign_id;
                }
            }
            
            //preserve old email and old member_id for contact history used
            $old_email = $_POST['email'];
            $old_member_qa_id = trim( $_POST['member_id'] );
            // checking email has changed by user
            if (isset($_POST['emailChange']) && $_POST['emailChange'] == '1') {
                unset($_POST['member_id']);

                
                $_POST['email'] = $_POST['newemail'];
            }
            $this->load->model('Members_model');
            $membersModel = new Members_model();

            //If manual create (contact discovery), and if country is US, then map the next 3 digits after 1 on the phone number
            //Get dial code to be removed when saving phone number
            if(!empty($_POST['is_manual_create']) && $_POST['is_manual_create'] == 'true'){
                $phoneValue = $_POST['phone'];
                if(strtoupper($_POST['country']) == 'US' && $phoneValue[0] == 1 && strlen($phoneValue) > 4){
                    //get timezone by areacode
                    $timezoneByAreaCode = getTimezoneByAreacodes();
                    $areaCode = $phoneValue[1] . $phoneValue[2] . $phoneValue[3];
                    $_POST['time_zone'] = isset($timezoneByAreaCode[$areaCode]) ? $timezoneByAreaCode[$areaCode] : '';
                }
                //select countries table to get dial_code
                $dialCode = $contactsModel->getDialCodeByCountryCode($_POST['country']);
                $_POST['dial_code'] = $dialCode;
            }
            
            //If questions exist then we need to map CQ IDs to form field names.
            $question_mapping = $this->set_question_mapping();
            
            $hql = false;
            if (!empty($callData['campaign_type']) && ($callData['campaign_type'] == 'mql' || $callData['campaign_type'] == 'mrl')) {
                $this->load->model('Resourceview_model');
                $resourceViewModel = new Resourceview_model();
                list($hql, $create_parent, $parent_source, $parent_site) = $this->verify_hql($question_mapping, $resourceViewModel, $list_id);
            } 

            $eGCampaignData = new stdClass();
            $eGCampaignData->distinct_leads = (empty($_POST['distinct_leads'])) ? 0 : $_POST['distinct_leads'];
            $eGCampaignData->campaign_id = $campaign_id;

            $upsert = false;
            //check uber database
            if(!empty($_POST['member_id'])){
                $members_qa_exist = $membersModel->member_exist_check($_POST['email']);
                if(!empty($members_qa_exist)){
                    $_POST['member_id'] = $members_qa_exist->id;
                }else{
                    //set member_id to null so that it would be inserted into members_qa
                    $_POST['member_id'] = null;
                }
            }
            if(empty($_POST['member_id'])){
                //create members_qa record if not yet existing
                $member_id = $this->_insert_member_qa($membersModel, $_POST);
                $upsert = $member_id;
                //insert contact history for member_id/email changed
                $this->create_contact_history($membersModel, $member_id, $old_email, $old_member_qa_id);
            }else{
                $member_id = $_POST['member_id'];
                $upsert = $this->_update_member($membersModel, $_POST, $member_id, 1);
            }
            if (!$upsert) {       
                $this->unlock_next_contact_and_redirect($contactsModel, 'Sorry, Oops! Something went wrong.', '/dialer/contacts/index/' . $_POST['campaign_id'] . '/' . $list_id);
            }
        
            
            $_POST['member_id'] = $member_id;

            
            
            //Insert CQ Respsonses if any
            if (!empty($question_mapping)) {
                // Update insert Custom Question Responses
                $this->_insert_cq_responses($membersModel, $_POST, $question_mapping, 1);

            }

            if($this->app == 'mpg'){
                $this->_insert_cq_regform_responses($_POST, $membersModel, 1);
            }

            $func = 'setContactObjectData'.ucfirst($this->app);
            $contactsData = $this->$func();
            $contactsData->member_id = $member_id;
            $contactsData->edit_lead_status = '0';
            $contactsData->locked_by = '';
            $callStatusUpdateArray = (array)$contactsData;

            if(!empty($_POST['ext'])){

                $callStatusUpdateArray['ext'] = $_POST['ext'];
            }

            //check if disposition should be set to do_not_call_ver
            $do_not_call_ever_status = $this->do_not_call_ever_logic($_POST['call_disposition']);
            
            if(!empty($_POST['isLifted']) && $_POST['isLifted'] == 1 && !$do_not_call_ever_status && 
                in_array($_POST['previous_call_disposition_id'], $this->do_not_call_ever_callDisposition_array)){
                $callStatusUpdateArray['do_not_call_ever'] = 0;
            }
            
            if(!empty($_POST['pureb2bConsent']) && $_POST['pureb2bConsent']== 'yes'){
                $callStatusUpdateArray['do_not_call_ever'] = 0;
            }
            
            
            
            if (!empty($_POST['is_add_page'])) {
                $callStatusUpdateArray['original_owner'] = 'Pureb2b';
                if ($do_not_call_ever_status) {
                    $callStatusUpdateArray['do_not_call_ever'] = 1;
                }
                
                $callStatusUpdateArray['created_at'] = date('Y-m-d H:i:s', time());


                $this->apply_normalization_rules($callStatusUpdateArray, $membersModel);
                $source = "add_diff";
                if(!empty($_POST['is_manual_create']) && $_POST['is_manual_create'] == 'true'){
                    $source = "form";
                }
                // Checking: passed email is exist on eg-contact table or not.
                $_POST['contact_id'] = $this->create_eg_contacts($contactsModel, $list_id, $source);


                $newContactCallID = $contactsModel->insert_contact($callStatusUpdateArray);
                if ($newContactCallID) {
                    //Insert new campaign_contact when using add as a different person functionality
                    $_POST['newCallHistoryId'] = $this->add_different_person($newContactCallID, $list_id, $contactsModel, $callsModel);

                }else{
                    $this->unlock_next_contact_and_redirect($contactsModel, 'Sorry, Duplicate entry while inserting new contact (ContactId - '. $_POST['contact_id'].') in Uber application, No lead was created.', '/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id);
                }
            } else {
                if(!empty($_POST['pureb2bConsent']) && !empty($_POST['clientConsent'])){
                    //UPSERT ON EG.CONTACTS table for GDPR pureb2b consent and client consent
                    //for pureb2b consent
                    $eg_contact_detail = $this->setEgContactObjectData();
                    $savePureb2bConsent = $contactsModel->savePureb2bConsent($_POST['pureb2bConsent'], $eg_contact_detail);
                    //get contact_id
                    $egContactId = $contactsModel->eg_email_exists($_POST['email']);
                    //for client consent
                    $saveClientConsent = $contactsModel->saveClientConsent($egContactId, $_POST);
                }
                $this->update_contact_edit_lead_status($contactsModel, $do_not_call_ever_status, $member_id, $callStatusUpdateArray);
            }

            $isWorkable = true;
            if (($do_not_call_ever_status || (isset($_POST['call_disposition']) && (in_array($_POST['call_disposition'], $this->_nonWorkableDispo)))) && isset($_POST['campaign_contact_id'])) {
                $isWorkable = false;
            }

            $callsModel->updateLeadWorkable($_POST['campaign_contact_id'],$isWorkable);
            
            if ((empty($_POST['Qaing'])) && $this->session->userdata('user_type') != 'qa' && !empty($_POST['decision']) && $_POST['decision'] != 'Follow Up') {
                if($hql){
                    $resourceview_id = $this->_log_lead_from_partner($callsModel, $member_id, $eGCampaignData->campaign_id, $eGCampaignData->distinct_leads, $_POST, $create_parent, $parent_source, $parent_site);
                }else{
                //Insert lead record // call_disposition instead of decision
                    $resourceview_id = $this->_log_lead_from_partner($callsModel, $member_id, $eGCampaignData->campaign_id, $eGCampaignData->distinct_leads, $_POST);
                }
            }

            // Start HP UAD-16 login and start session
            $this->load->model('Campaigns_model');
            $campaignModel = new Campaigns_model();
            $sessionData = $this->session->userdata();
            $agentId = $sessionData['uid'];

            // Get agent's sessin status from auto_live_agents_logs table
            $agentLastSessionStatus = $campaignModel->getAutoAgentSessionLog($agentId);         
            $campaignModel->updateAutoAgentSessionStatus($agentId, $campaign_id, $agentLastSessionStatus, 0);
            // End HP UAD-16 login and start session
            
            //insert call disposition on campaign_contacts and call_disposition_history
            $this->_create_member_qa_history($membersModel, $_POST['member_id']);
            //lead is generated so please unlock the contact
            $this->unlock_contact($_POST['contact_id'], $contactsModel);
            
            return $campaign_id;
        }
        
    }
    
    function verify_hql($question_mapping, $resourceViewModel, $list_id){
        $hql = false;
        $parent_source = NULL;
        $parent_site = NULL;
        if(isset($_POST['parent_id']) && $_POST['parent_id'] == 0){
            $hql = true;
            //------------------------------------------------------>
            //Step 1) Validate data
            //------------------------------------------------------>
            $result = $this->_verify_partner_hql_post($question_mapping, $resourceViewModel);
            
            if (empty($result['parent_lead'])) {
                $create_parent = true;
            } else {
                $parent_source = $result['parent_lead']['source'];
                $parent_site = $result['parent_lead']['site_id'];
                $create_parent = false;
            }
            if (empty($parent_site) && isset($_POST['campaign_site'])) {
                $parent_site = $_POST['campaign_site'];
            }
            
            if (empty($_POST['resource_id']) && !empty($result['parent_lead'])) {
                $_POST['resource_id'] = $result['parent_lead']['resource_id'];
            }
            
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, Oops! Already parent id is exist.');
            redirect('/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id);
        }
        return array($hql, $create_parent, $parent_source, $parent_site);
    }
    
    public function process_qa($callsModel,$list_id){
        // apply validation for mandatory fields.
        $this->ContactCallFormValidation(true);
        if ($this->form_validation->run() == FALSE) {// && !$qa
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please fill up mandatory fields.');
            redirect('/dialer/leads');
        } else {
            //check member_id, member should already be generated on members_qa, $_POST['member_id']  should be set
            if(empty($_POST['member_id'])){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occurred! Member is not yet generated on Members QA');
                redirect('/dialer/leads');
            }
            
            $callData = $this->input->post();
            $campaign_id = (!empty($callData['campaign_id'])) ? $callData['campaign_id'] : '';
            //check campaign working on
            $this->validate_campaign($campaign_id);

            $this->load->model('Calls_model');
            $callsModel = new Calls_model();
            
            //check first if qa already approved this, if yes then redirect and dispaly error message
            $leadStatus = $callsModel->check_already_created_lead_by_campaign_contact_id($_POST['campaign_contact_id']);
            if(!empty($leadStatus) && $leadStatus->status == 'Approve'){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This lead has already been approved.');
                redirect('/dialer/leads');
            }

            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();

            $this->load->model('Members_model');
            $membersModel = new Members_model();

            //preserve old email and old member_id for contact history used
            $old_email = $_POST['email'];
            $old_member_qa_id = trim( $_POST['member_id'] );
            // checking email has changed by user
            if (isset($_POST['emailChange']) && $_POST['emailChange'] == '1') {
                unset($_POST['member_id']);
                
                $_POST['email'] = $_POST['newemail'];
            }
            if(empty($_POST['member_id'])){
                //create members_qa record if not yet existing
                $member_id = $this->_insert_member_qa($membersModel, $_POST);
                $_POST['member_id'] = $member_id;
                //insert contact history for member_id/email changed
                $this->create_contact_history($membersModel, $member_id, $old_email, $old_member_qa_id);
            }else{
                $member_id = $_POST['member_id'];
                $this->_update_member($membersModel, $_POST, $member_id, 1);
            }
            //If questions exist then we need to map CQ IDs to form field names.
            $question_mapping = $this->set_question_mapping();
            if (!empty($question_mapping)) {
                // Update insert Custom Question Responses
                $this->_insert_cq_responses($membersModel, $_POST, $question_mapping,1);
            }

            if($this->app == 'mpg'){
                if ($_POST['statusValue'] == 'Approve') {
                    $this->_insert_cq_regform_responses($_POST, $membersModel);
                }
            }
            
            $hql = false;
            if (!empty($callData['campaign_type']) && ($callData['campaign_type'] == 'mql' || $callData['campaign_type'] == 'mrl')) {
                $this->load->model('Resourceview_model');
                $resourceViewModel = new Resourceview_model();
                list($hql, $create_parent, $parent_source, $parent_site) = $this->verify_hql($question_mapping, $resourceViewModel, $list_id);
            }

            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();
            $func = 'setContactObjectData'.ucfirst($this->app);
            $contactsData = $this->$func();
            $contactsData->member_id = $member_id;
            $contactsData->edit_lead_status = '0';
            $callStatusUpdateArray = (array)$contactsData;
            
            if(!empty($_POST['ext'])){
                $callStatusUpdateArray['ext'] = $_POST['ext'];
            }
            $callStatusUpdateArray['updated_by'] = $this->session->userdata('uid');
            unset($callStatusUpdateArray['created_at']);
            if(isset($_POST['pureb2bConsent']) && $_POST['pureb2bConsent']== 'yes'){
                $callStatusUpdateArray['do_not_call_ever'] = 0;
            }else if(!empty($_POST['pureb2bConsent']) && $_POST['pureb2bConsent'] == 'no' && !empty($_POST['clientConsent']) && $_POST['clientConsent'] == 'no'){
                $_POST['call_disposition'] = '18';
            }else if(isset($_POST['pureb2bConsent']) && $_POST['pureb2bConsent']== 'no'){
                $callStatusUpdateArray['do_not_call_ever'] = 1;
            }
            $this->apply_normalization_rules($callStatusUpdateArray, $membersModel);
            
            unset($callStatusUpdateArray['email']);
            
            //fixed updating of contact UB-28
            $contactsModel->update_contact($_POST['contact_id'], $callStatusUpdateArray);

            if(!empty($_POST['pureb2bConsent']) && !empty($_POST['clientConsent'])){
                //UPDATE ON EG.CONTACTS table for GDPR pureb2b consent and client consent
                
                //get contact_id
                $eg_email_exist = $contactsModel->eg_email_exists($_POST['email'], 'id,');
                if(!empty($eg_email_exist)){
                    if($eg_email_exist->pureb2b_consent != $_POST['pureb2bConsent']){
                        $pureb2bConsentUpdatedAt = date('Y-m-d H:i:s', time());
                        $contactsModel->updateEgContactsPureb2bConsent($eg_email_exist->id,$_POST['pureb2bConsent'],$pureb2bConsentUpdatedAt);
                    }
                    if(!empty($_POST['clientConsent'])){
                        //insert/update client consent GDPR
                        $saveClientConsent = $contactsModel->saveClientConsent($eg_email_exist->id, $_POST);
                    }
                }
                
            }

            $this->_qa_decision($campaignsModel,$callsModel, $membersModel, $this->input->post('lead_id'), $this->input->post('decision'), $list_id, $question_mapping, $hql); // decision
            $this->_create_member_qa_history($membersModel, $member_id);

            return $campaign_id;
        }
    }
    
    function update_contact_edit_lead_status($contactsModel, $do_not_call_ever_status, $member_id, $callStatusUpdateArray){

        unset($callStatusUpdateArray['email']);

        if (isset($_POST['emailChange']) && $_POST['emailChange'] == '1') {
            $emailChangeContactData['edit_lead_status'] = '0';
            $emailChangeContactData['locked_by'] = '';
            $emailChangeContactData['member_id'] = $member_id;
            if ($do_not_call_ever_status) {
                $emailChangeContactData['do_not_call_ever'] = 1;
            }
            $contactsModel->update_contact($_POST['contact_id'], $emailChangeContactData);
        } else {
            if ($do_not_call_ever_status) {
                $callStatusUpdateArray['do_not_call_ever'] = 1;
            }
            $callStatusUpdateArray['edit_lead_status'] = '0';
            $callStatusUpdateArray['locked_by'] = '';
            $callStatusUpdateArray['updated_by'] = $this->session->userdata('uid');
            unset($callStatusUpdateArray['created_at']);
            $contactsModel->update_contact($_POST['contact_id'], $callStatusUpdateArray);
        }
    }
    
    /**
     * Insert new campaign_contact when using add as a different person functionality
     * @param type $newContactCallID
     * @param type $list_id
     * @param type $contactsModel
     * @param type $callsModel
     */
    function add_different_person($newContactCallID, $list_id, $contactsModel, $callsModel){
        $contact_lists_data = array();
        $_POST['new_added_contact_id'] = $newContactCallID;
        $contact_lists_data['contact_id'] = $newContactCallID;
        $contact_lists_data['campaign_id'] = $_POST['campaign_id'];
        $contact_lists_data['list_id'] = $list_id;
        $contact_lists_data['resource_id'] = isset($_POST['resource_id']) ? $_POST['resource_id'] : 0;
        $contact_lists_data['source'] = 'add_diff';
        $contact_lists_data['created_by'] = $this->session->userdata('uid');
        $contact_lists_data['created_at'] = date('Y-m-d H:i:s', time());
        $contact_lists_data['from_campaign_contact_id'] = $_POST['campaign_contact_id'];
        if(!empty($_POST['is_manual_create']) && $_POST['is_manual_create'] == 'true'){
            $contact_lists_data['source'] = 'form';
        }
        $newcampaignContactID = $contactsModel->insert_contact_lists($contact_lists_data);
        if ($newcampaignContactID){
            $_POST['campaign_contact_id'] = $newcampaignContactID;
            if(!empty($_POST['original_call_history_id'])){
                return $this->copy_call_history($newContactCallID, $_POST['original_call_history_id'], $callsModel, $newcampaignContactID);
            }
        }
        return null;
    }
    
    
    function create_eg_contacts($contactsModel, $listId, $source = '')
    {
        $egEmailExist = $contactsModel->eg_email_exists($_POST['email'],'id,pureb2b_consent');
        $contactIdExist = $contactsModel->EmailContactDetails($_POST['email']);
        if(empty($egEmailExist)){
            $egContactDetail = $this->setEgContactObjectData();
            //insert pureb2b_consent, pureb2b_consent_updated_at value
            if(!empty($_POST['pureb2bConsent'])){
                $egContactDetail->pureb2b_consent = ($_POST['pureb2bConsent'] == 'yes') ? 1:0;
                $egContactDetail->pureb2b_consent_updated_at = date('Y-m-d H:i:s', time());
            }
            if(!empty($source)){
                $egContactDetail->data_source = $source;
            }
            $egContactDetail->created_at = date('Y-m-d H:i:s', time());
            $returnEgContactId = $contactsModel->insert_eg_contact((array)$egContactDetail);
            if(!empty($_POST['clientConsent'])){
                //insert/update client consent GDPR
                $saveClientConsent = $contactsModel->saveClientConsent($returnEgContactId, $_POST);
            }
            if($contactIdExist){
                $this->unlock_next_contact_and_redirect($contactsModel, 
                    'Created EG-ContactID: '.$returnEgContactId.' exists on uber application, No lead was created.', 
                    '/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$listId);
            }
        } else {
            //update pureb2b_consent if value from eg.contacts table is not same with value from form submitted,
            //if yes to not same value then update the: pureb2b_consent_updated_at value
            if(!empty($_POST['pureb2bConsent'])){
                if($egEmailExist->pureb2b_consent != $_POST['pureb2bConsent']){
                    $pureb2bConsentUpdatedAt = date('Y-m-d H:i:s', time());
                    $contactsModel->updateEgContactsPureb2bConsent($egEmailExist->id,$_POST['pureb2bConsent'],$pureb2bConsentUpdatedAt);
                }
                if(!empty($_POST['clientConsent'])){
                    //insert/update client consent GDPR
                    $saveClientConsent = $contactsModel->saveClientConsent($egEmailExist->id, $_POST);
                }
            }
        }
        
        return $contactIdExist;
    }
    
    function apply_normalization_rules(&$callStatusUpdateArray, $membersModel){
        if(empty($callStatusUpdateArray['job_level']) || empty($callStatusUpdateArray['job_function'])) {
            $this->load->library('Normalize');
            $normalize = new Normalize();

            $normalization_rules = $membersModel->get_member_normalization_rules();

            $callStatusUpdateArray['job_level'] = empty($callStatusUpdateArray['job_level']) ? $normalize->job_level($callStatusUpdateArray['job_title'], $normalization_rules) : $callStatusUpdateArray['job_level'];
            $callStatusUpdateArray['job_function'] = empty($callStatusUpdateArray['job_function']) ? $normalize->silo($callStatusUpdateArray['job_title'], $normalization_rules) : $callStatusUpdateArray['job_function'];
        }
    }
    
    /**
     * https://app.assembla.com/spaces/agile-development/tickets/2527-uber--association-of-call-recordings-/details
     * Copy call history if the contact was added as a different person
     * @param type $contact_id
     * @param type $original_call_history_id
     * @param Calls_model $callsModel
     * @return type
     */
    function copy_call_history($contact_id,$original_call_history_id,$callsModel=null,$newCampaignContactId){
        $call_history_id = null;

        if($callsModel == null){
            $this->load->model('Calls_model');
            $callsModel = new Calls_model();
        }
            //copy call history data except contact id
        $call_history_id = $callsModel->copy_call_history_record($contact_id, $original_call_history_id, $newCampaignContactId);
        //copy call_history_campaign_contact
        $callsModel->copyCallHistoryCampaignContact($original_call_history_id, $call_history_id,  $newCampaignContactId);
        return $call_history_id;
    }
    
    /**
     * 
     * @param type $membersModel
     * @param type $member_id
     * @param type $old_email
     * @param type $old_member_qa_id
     */
    function create_contact_history($membersModel, $member_id, $old_email, $old_member_qa_id){
        //insert contact history for member_id/email changed
        if ( $_POST['email'] != $old_email ) {
            $changeset['members_qa_id'] = $old_member_qa_id;
            $changeset['new_id'] = $member_id;
            $changeset['new_email'] = $_POST['email'];
            $changeset['old_email'] = $old_email;
            $changeset['module'] = 'TM Calls';

            $membersModel->create_contact_history( $changeset );
        }
    }

    public function setEgContactObjectData()
    {
        $this->load->model('Contacts_model');

        $call = new EgContactTable();
        $viewListData = (object)$this->input->post();

        if (isset($viewListData->first_name))
            $call->first_name = $viewListData->first_name;
        if (isset($viewListData->last_name))
            $call->last_name = $viewListData->last_name;
        if (isset($viewListData->email))
            $call->email = $viewListData->email;
        if (isset($viewListData->phone)) {
            // save phone no. without country code
            $call->phone = substr($viewListData->phone, strlen($viewListData->dial_code));//$viewListData->phone;
        }
        if (isset($viewListData->job_title))
            $call->job_title = $viewListData->job_title;
        if (isset($viewListData->job_level))
            $call->job_level = $viewListData->job_level;

        if (isset($viewListData->company))
            $call->company_name = $viewListData->company;
        if (isset($viewListData->address))
            $call->address1 = $viewListData->address;
        if (isset($viewListData->city))
            $call->city = $viewListData->city;
        if (isset($viewListData->zip))
            $call->zip = $viewListData->zip;
        if (isset($viewListData->state))
            $call->state = $viewListData->state;
        if (isset($viewListData->country))
            $call->country = $viewListData->country;
        if (isset($viewListData->industry))
            $call->industry = $viewListData->industry;
        if (isset($viewListData->company_size))
            $call->company_size = $viewListData->company_size;
        if (isset($viewListData->company_revenue))
            $call->company_revenue = $viewListData->company_revenue;

        $call->updated_at = date('Y-m-d H:i:s', time());

        return $call;
    }

    public function setContactObjectDataEg()
    {
        $this->load->model('Contacts_model');

        $call = new ContactsTable();
        $viewListData = (object)$this->input->post();

        if (isset($viewListData->first_name))
            $call->first_name = $viewListData->first_name;
        if (isset($viewListData->last_name))
            $call->last_name = $viewListData->last_name;
        if (isset($viewListData->email))
            $call->email = $viewListData->email;
        if (isset($viewListData->phone)) {
            // save phone no. without country code
            $call->phone = substr($viewListData->phone, strlen($viewListData->dial_code));//$viewListData->phone;
        }
        if (isset($viewListData->alternate_no)){
            // save phone no. without country code
            $call->alternate_no = substr($viewListData->alternate_no, strlen($viewListData->dial_code));//$viewListData->alternate_no;
        }
        if (isset($viewListData->job_title))
            $call->job_title = $viewListData->job_title;
        if (!empty($viewListData->job_level)){
            $call->job_level = $viewListData->job_level;
        }else{
            unset($call->job_level);
        }
        if (!empty($viewListData->job_function)) {
            $call->job_function = $viewListData->job_function;
        }else{
            unset($call->job_function);
        }
        if (isset($viewListData->company))
            $call->company = $viewListData->company;
        if (isset($viewListData->address))
            $call->address = $viewListData->address;
        if (isset($viewListData->city))
            $call->city = $viewListData->city;
        if (isset($viewListData->zip))
            $call->zip = $viewListData->zip;
        if (isset($viewListData->state))
            $call->state = $viewListData->state;
        if (isset($viewListData->country))
            $call->country = $viewListData->country;
        if (isset($viewListData->industry))
            $call->industry = $viewListData->industry;
        if (isset($viewListData->company_size))
            $call->company_size = $viewListData->company_size;
        if (isset($viewListData->notes))
            $call->notes = $viewListData->notes;
        if (isset($viewListData->time_zone)) {
            $call->time_zone = $viewListData->time_zone;
        }
        if (isset($viewListData->company_revenue)) {
            $call->company_revenue = $viewListData->company_revenue;
        }

        if (isset($viewListData->state))
            $call->state = $viewListData->state;

        $call->edit_lead_status = '0';
        $call->updated_at = date('Y-m-d H:i:s', time());

        return $call;
    }

    public function setContactObjectDataMpg()
    {
        $this->load->model('Contacts_model');

        $call = new ContactsTableMpg();
        $viewListData = (object)$this->input->post();

        if (isset($viewListData->first_name))
            $call->first_name = $viewListData->first_name;
        if (isset($viewListData->last_name))
            $call->last_name = $viewListData->last_name;
        if (isset($viewListData->email))
            $call->email = $viewListData->email;
        if (isset($viewListData->phone)) {
            // save phone no. without country code
            $call->phone = substr($viewListData->phone, strlen($viewListData->dial_code));//$viewListData->phone;
        }
        if (isset($viewListData->alternate_no)){
            // save phone no. without country code
            $call->alternate_no = substr($viewListData->alternate_no, strlen($viewListData->dial_code));//$viewListData->alternate_no;
        }
        if (isset($viewListData->job_title))
            $call->job_title = $viewListData->job_title;
        if (isset($viewListData->job_level))
            $call->job_level = $viewListData->job_level;
        if (isset($viewListData->company))
            $call->company = $viewListData->company;
        if (isset($viewListData->address1))
            $call->address = $viewListData->address1;
        if (isset($viewListData->address2))
            $call->address = $viewListData->address2;
        if (isset($viewListData->city))
            $call->city = $viewListData->city;
        if (isset($viewListData->zip))
            $call->zip = $viewListData->zip;
        if (isset($viewListData->state))
            $call->state = $viewListData->state;
        if (isset($viewListData->country))
            $call->country = $viewListData->country;
        if (isset($viewListData->employee_size))
            $call->employee_size = $viewListData->employee_size;
        if (isset($viewListData->notes))
            $call->notes = $viewListData->notes;
        if (isset($viewListData->time_zone)) {
            $call->time_zone = $viewListData->time_zone;
        }

        if (isset($viewListData->state))
            $call->state = $viewListData->state;

        $call->edit_lead_status = '0';
        $call->updated_at = date('Y-m-d H:i:s', time());

        //$call = $this->unset_nulls($call);

        return $call;
    }

    public function setCallFlowFindingsObjectData()
    {
        $callFlowFindingsData = new stdClass();
        $callFlowFindings = (object)$this->input->post();

        if (isset($callFlowFindings->proper_greeting)) {
            $callFlowFindingsData->proper_greeting = $callFlowFindings->proper_greeting;
        }
        if (isset($callFlowFindings->right_decision_maker_identified)) {
            $callFlowFindingsData->right_decision_maker_identified = $callFlowFindings->right_decision_maker_identified;
        }
        if (isset($callFlowFindings->proper_branding)) {
            $callFlowFindingsData->proper_branding = $callFlowFindings->proper_branding;
        }
        if (isset($callFlowFindings->clearly_stated_the_purpose_of_the_call)) {
            $callFlowFindingsData->clearly_stated_the_purpose_of_the_call = $callFlowFindings->clearly_stated_the_purpose_of_the_call;
        }
        if (isset($callFlowFindings->provided_a_clear_overview_of_the_content_of_the_asset)) {
            $callFlowFindingsData->provided_a_clear_overview_of_the_content_of_the_asset = $callFlowFindings->provided_a_clear_overview_of_the_content_of_the_asset;
        }
        if (isset($callFlowFindings->prospect_agreed_to_receiving_a_copy_of_the_asset)) {
            $callFlowFindingsData->prospect_agreed_to_receiving_a_copy_of_the_asset = $callFlowFindings->prospect_agreed_to_receiving_a_copy_of_the_asset;
        }
        if (isset($callFlowFindings->accurate_and_effective_rebuttals)) {
            $callFlowFindingsData->accurate_and_effective_rebuttals = $callFlowFindings->accurate_and_effective_rebuttals;
        }
        if (isset($callFlowFindings->agent_was_able_to_probe)) {
            $callFlowFindingsData->agent_was_able_to_probe = $callFlowFindings->agent_was_able_to_probe;
        }
        if (isset($callFlowFindings->clear_delivery_of_the_questions)) {
            $callFlowFindingsData->clear_delivery_of_the_questions = $callFlowFindings->clear_delivery_of_the_questions;
        }
        if (isset($callFlowFindings->custom_questions_were_all_answered)) {
            $callFlowFindingsData->custom_questions_were_all_answered = $callFlowFindings->custom_questions_were_all_answered;
        }
        if (isset($callFlowFindings->all_pertinent_information_verified)) {
            $callFlowFindingsData->all_pertinent_information_verified = $callFlowFindings->all_pertinent_information_verified;
        }
        if (isset($callFlowFindings->proper_closing)) {
            $callFlowFindingsData->proper_closing = $callFlowFindings->proper_closing;
        }
        if (isset($callFlowFindings->right_expectations_were_set)) {
            $callFlowFindingsData->right_expectations_were_set = $callFlowFindings->right_expectations_were_set;
        }

        return $callFlowFindingsData;
    }

    private function _qa_decision($campaignModel,$callsModel, $membersModel, $lead, $decision, $list_id, $question_mapping, $hql = false)
    {
        $this->load->model('Resourceview_model');
        $resourceViewModel = new Resourceview_model();

        $loggedUserID = $this->session->userdata('uid');

        $tmp['notes'] = $this->input->post('notes');

        $timestamp = date('Y-m-d H:i:s', time());
        $tmp['id'] = $lead;
        $tmp['updated_at'] = $timestamp;
        $tmp['is_qa_in_progress'] = 0;
        $tmp['resource_id'] = $this->input->post('resource_id');
        $tmp['resource_name'] = $this->input->post('resource_name');
        $tmp['member_id'] = $this->input->post('member_id');
        $eg_member_id = null;
        $source = $_POST['source'];
        if($decision == 'Approve'){
            //get member_id from eg->members table
            //if member does not exist insert the member from members_qa table then get id
            $eg_member_id = $membersModel->reinsert_member_to_eg($_POST['member_id'],$_POST['first_qa_date'],$timestamp, $source);
            if(!$eg_member_id){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occurred! Member was not created in EG');
                redirect('/dialer/leads');
            }
   
            //Insert CQ Respsonses if any
            if ((!empty($_POST['questions']) || !empty($_POST['intent_questions'])) && $_POST['decision'] == 'Approve') {
                // Update insert Custom Question Responses
                $post_data = $_POST;
                $post_data['member_id'] = $eg_member_id;
                $this->_insert_cq_responses($membersModel, $post_data, $question_mapping);
            }
        }
        if ($decision == 'Approve' && $hql == false) {            
            $tmp['status'] = 'Approve';
            $tmp['qa'] = $loggedUserID;
            $resourceViewModel->update_tmp($callsModel,(object)$tmp);
            // copy data at second db while lead approve without HQL campaign type
            $eg_approve_response = $resourceViewModel->convert_temp($lead, 0, $eg_member_id);
            if(!empty($eg_approve_response)){
                $update_at = date('Y-m-d H:i:s', time());
                $approve_lead_error_data['id'] = $lead;
                $approve_lead_error_data['updated_at'] = $update_at;
                $approve_lead_error_data['eg_approve_log'] = $eg_approve_response;
                $callsModel->updateRetractLeadHistory((object)$approve_lead_error_data);
                $rv_lead_id = $eg_approve_response;
            }else{
                $rv_lead_id = $lead;
            }
            // Grab the created_at and updatred_at fields from the tm_lead_history record
            // We'll use those to insert new members_qa_history records, and to update the 
            // member record's first_qa_date & final_qa_date
            $this->insert_datateam_qa_history($eg_member_id, $_POST['previous_agent_id'], $_POST['first_qa_date'], $loggedUserID, $timestamp, $rv_lead_id, $membersModel);
            $callsModel->_update_agent_submitted_lead_status($lead, $eg_member_id, $_POST['email']);
        }else if ($decision == 'Approve' && $hql == true) {
            $tmp['status'] = 'Approve';
            $tmp['qa'] = $loggedUserID;
            $resourceViewModel->update_tmp($callsModel,(object)$tmp);
            $parent_lead = $resourceViewModel->get_eg_campaign_one($lead, $eg_member_id);

            // create parent lead to EG resource_views table
            $eg_approve_response = $resourceViewModel->convert_temp($lead, 1, $eg_member_id);

            $ids = $campaignModel->get_child_campaign_ids($parent_lead->campaign_id);
            $data = array();

            //foreach child campaign copy the parent lead data into an array for batch insert of child leads.
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    // insert new tm_lead_history records for each child, then copy the notes over
                    $eg_tm_lh = $resourceViewModel->insert_eg_tm_lead_history($parent_lead);
                    $resourceViewModel->insert_eg_tm_notes($parent_lead->id, $eg_tm_lh);
                    $array = array();
                    foreach ($parent_lead as $key => $val) {
                        switch ($key) {
                            case 'id':
                                break;
                            case 'partner':
                                $array['partner'] = NULL;
                                break;
                            case 'updated_at':
                                $array['updated_at'] = $timestamp;
                                break;
                            case 'created_at':
                                $array['created_at'] = $timestamp;
                                break;
                            case 'campaign_id':
                                $array['campaign_id'] = $id['id'];
                                break;
                            case 'qualified':
                                $array['qualified'] = 0;
                                break;
                            case 'processed':
                                $array['processed'] = 0;
                                break;
                            case 'status':
                                break;
                            case 'disapprove_reason':
                                break;
                            case 'notes':
                            case 'qa':
                            case 'is_qa_in_progress':
                                break;
                            default:
                                $array[$key] = $val;
                        }

                    }
                    array_push($data, $array);
                }
            }

            if (!empty($data)) {
                // copy data at second db while lead approve with HQL campaign type
                $eg_approve_response = $resourceViewModel->copy_parent_lead_to_child($data);
                if(!empty($eg_approve_response)){
                    $update_at = date('Y-m-d H:i:s', time());
                    $approve_lead_error_data['id'] = $lead;
                    $approve_lead_error_data['updated_at'] = $update_at;
                    $approve_lead_error_data['eg_approve_log'] = $eg_approve_response;
                    $callsModel->updateRetractLeadHistory((object)$approve_lead_error_data);
                    $rv_lead_id = $eg_approve_response;
                }else{
                    $rv_lead_id = $lead;
                }
                // Grab the created_at and updatred_at fields from the tm_lead_history record
                // We'll use those to insert new members_qa_history records, and to update the 
                // member record's first_qa_date & final_qa_date
                $this->insert_datateam_qa_history($eg_member_id, $_POST['previous_agent_id'], $_POST['first_qa_date'], $loggedUserID, $timestamp, $rv_lead_id, $membersModel);
                $callsModel->_update_agent_submitted_lead_status($lead, $eg_member_id, $_POST['email']);
            }
        }

        if ($decision == 'Follow Up') {
            $tmp['status'] = 'Follow-up';
            $tmp['qa'] = $loggedUserID;
            $resourceViewModel->update_tmp($callsModel,(object)$tmp);

            // insert follow up reason batch with only one insert query
            $this->insert_followup_reason($lead,$loggedUserID,$callsModel);//echo "<pre>",print_r($this->db->queries), "</pre>";die('yey');
        }
        // TODO for Update and submit status there should be update "Call flow findings" data
        if ($decision == 'Update and Submit') {
            $tmp['status'] = 'Pending';
            $tmp['qa'] = " "; //$this->session->userdata('uid')
            $resourceViewModel->update_tmp($callsModel, (object)$tmp);
        }

        if ($decision == 'Reject') {
            $tmp['status'] = 'Reject';
            $tmp['qa'] = $loggedUserID;

            if ($this->input->post('reject_reason') != 'Other') {
                $tmp['disapprove_reason'] = $this->input->post('reject_reason');
            } else {
                $tmp['disapprove_reason'] = $this->input->post('reason_text');
            }
            $resourceViewModel->update_tmp($callsModel,(object)$tmp);

            // insert Reject reason batch with only one insert query
            $this->insert_reject_reason($lead,$loggedUserID,$callsModel);
        }

        //#2085- added new status "Duplicate lead"
        if ($decision == 'Duplicate Lead') {
            $tmp['status'] = $decision;
            $tmp['qa'] = $loggedUserID;
            $resourceViewModel->update_tmp($callsModel, (object)$tmp);
        }
        
        //update campaign contacts, insert lead status, insert notes
        $callsModel->updateCampaignContact($_POST);

        $setCallFlowDetail = $this->setCallFlowFindingsObjectData();

        $callFlowResponse = array();
        if (!empty($setCallFlowDetail)) {
            foreach ($setCallFlowDetail as $key => $callFlowValue) {
                $callFlowResponse[] = array('lead_history_id' => $lead, 'user_id' => $loggedUserID, 'status' => $decision, 'call_flow_text' => $key, 'call_flow_value' => $callFlowValue);
            }
        }

        // update/insert call flow finding response
        if (!empty($callFlowResponse)) {
            $response = $callsModel->update_insert_call_flow_findings_responses($callFlowResponse);
        }
    }
    
    function insert_datateam_qa_history($member_id, $agent, $first_qa_date, $qa, $final_qa_date, $rv_lead_id, $membersModel){
        $qa_history_data = array();
        $qa_history_data["member_id"] = $member_id;
        $qa_history_data["qa_date"] = $first_qa_date;
        $qa_history_data["qa_by"] = $agent;
        $qa_history_data["qa_type"] = 'tm agent';
        $qa_history_data["lead_id"] = isset($rv_lead_id) ? $rv_lead_id : '';
        $membersModel->insert_datateam_qa_history($qa_history_data);
        $qa_history_data["qa_date"] = $final_qa_date;
        $qa_history_data["qa_by"] = $qa;
        $qa_history_data["qa_type"] = 'tm qa';
        $membersModel->insert_datateam_qa_history($qa_history_data);
    }

    function insert_reject_reason($lead, $loggedUserID, $callsModel)
    {
        $default_connection = MYSQLConnectOFDefaultDB();
            if (!empty($_POST['reject_reason'])) {
                $keyFields = '';

                $created_at = date('Y-m-d H:i:s', time());

                $i = 0;

                foreach ($_POST['reject_reason'] as $key => $value) {

                    if (!empty($value)) {
                            $keyFields .= '(';
                        $keyFields .= "'" . $lead . "'" . ', ';
                        $keyFields .= "'" . $loggedUserID . "'" . ', ';
                        $keyFields .= "'Reject'" . ', ';
                        $keyFields .= "'" . SQLInjectionOFDefaultDB($value,$default_connection) . "'";

        }

                    if (!empty($_POST['reason_text']) && (!empty($value))) {
                        foreach ($_POST['reason_text'] as $sub_others_key => $sub_others_value) {
                            $subDropDownValue = "";
                            if (isset($_POST['sub_drop_down_value_' . ($key + 1)])) {
                                $subDropDownValue = $_POST['sub_drop_down_value_' . ($key + 1)];

    }
                            if (!empty($sub_others_value) && $key == $sub_others_key) {
                                $keyFields .= ', ' . "'" . SQLInjectionOFDefaultDB($sub_others_value,$default_connection) . "'";
                            } else if (!empty($subDropDownValue) && $key == $sub_others_key) {
                                $keyFields .= ', ' . "'" . SQLInjectionOFDefaultDB($subDropDownValue,$default_connection) . "'";
                            } else if ($key == $sub_others_key) {
                                $keyFields .= ', ' . "' '";
                            }
                        }
                    }
                    if (!empty($value)) {
                        $keyFields .= ', ' . "'" . $created_at . "'";
                        $keyFields .= ')';
                    }

                    if ($i++ < count($_POST['reject_reason']) - 1 && (!empty($value))) {
                        $keyFields .= ",";
                    }
                }

                $callsModel->InsertLeadReasonQA($keyFields);
            }
    }

    function insert_followup_reason($lead, $loggedUserID, $callsModel)
    {
        $default_connection = MYSQLConnectOFDefaultDB();
        if (!empty($_POST['follow_up_reason'])) {
            $keyFields = '';

            $created_at = date('Y-m-d H:i:s', time());
            $i = 0;
            foreach ($_POST['follow_up_reason'] as $key => $value) {

                if (!empty($value)) {
                    $keyFields .= '(';
                    $keyFields .= "'" . $lead . "'" . ', ';
                    $keyFields .= "'" . $loggedUserID . "'" . ', ';
                    $keyFields .= "'Follow-up'" . ', ';
                    $keyFields .= "'" . SQLInjectionOFDefaultDB($value,$default_connection) . "'";
        }

                if (!empty($_POST['follow_up_text']) && (!empty($value))) {
                    foreach ($_POST['follow_up_text'] as $sub_others_key => $sub_others_value) {
                        $subDropDownValue = "";
                        if (isset($_POST['sub_drop_down_value_follow_up_' . ($key + 1)])) {
                            $subDropDownValue = $_POST['sub_drop_down_value_follow_up_' . ($key + 1)];
            }
                        if (!empty($sub_others_value) && $key == $sub_others_key) {
                            $keyFields .= ', ' . "'" . SQLInjectionOFDefaultDB($sub_others_value,$default_connection) . "'";
                        } else if (!empty($subDropDownValue) && $key == $sub_others_key) {
                            $keyFields .= ', ' . "'" . SQLInjectionOFDefaultDB($subDropDownValue,$default_connection) . "'";
                        } else if ($key == $sub_others_key) {
                            $keyFields .= ', ' . "' '";
        }
                    }
                }
                if (!empty($value)) {
                    $keyFields .= ', ' . "'" . $created_at . "'";
                    $keyFields .= ')';
                }

                if ($i++ < count($_POST['follow_up_reason']) - 1 && (!empty($value))) {
                    $keyFields .= ",";
        }
    }
            $callsModel->InsertLeadReasonQA($keyFields);
        }
    }

    function _insert_cq_responses($membersModel, $post_data, $question_mapping, $for_tm_qa = 0)
    {
        $i = 1;
        $is_survey = false;
        unset($post_data['questions']);
        unset($post_data['intent_questions']);
        if ($post_data['campaign_type'] == 'pureresearch' ||
            $post_data['campaign_type'] == 'puremql' || $post_data['campaign_type'] == 'smartleads') {
                $is_survey = true;
                $incentive_offered = !empty($post_data['incentiveOffered']) && $post_data['incentiveOffered'] == '1' ? 1 : 0;
        }
        $question_responses = array();
        
        //Uber TM: As all users, I need to add a textbox on questions with option as other so we could specify the answer
        $otherQuestions = array();
        foreach ($question_mapping as $key => $value) {
            if (isset($post_data["{$key}_other"])){
                if(isset($post_data["{$key}"]) && is_array($post_data["{$key}"]) && !empty($post_data["{$key}_other"])){
                    foreach($post_data["{$key}"] as $idx => $selVal){
                        if(substr(strtolower(trim($selVal)), 0, 5) === 'other'){
                            $post_data["{$key}"][$idx] = $post_data["{$key}"][$idx] . ":" .  $post_data["{$key}_other"];
                        }
                    }
                }else{
                    if(!empty($post_data["{$key}_other"])){
                        $otherQuestions["{$value}"] = $post_data["{$key}_other"];
                    }
                }
                unset($post_data["{$key}_other"]);
            }
        }
        foreach ($post_data as $key => $value) {
            //we want to find fields starting with 'question'
            if (substr($key, 0, 8) == 'question') {
                $qid = $question_mapping[$key];
                if (is_array($value)) {
                    $value = implode("|", $value);

                }
                if(!empty($otherQuestions["{$qid}"])){
                    $optionOther = explode(":", $value);
                    if(substr(strtolower(trim($optionOther[0])), 0, 5) === 'other'){
                        $value = "{$optionOther[0]}:{$otherQuestions["{$qid}"]}";
                    }
                }
                if ($is_survey == true) {
                    $question_responses[] = array('question_id' => $qid,
                        'member_id' => $post_data['member_id'],
                        'response' => $value,
                        'incentive_offered' => $incentive_offered
                    );
                } else {
                    $question_responses[] = array('question_id' => $qid, 'member_id' => $post_data['member_id'], 'response' => $value);
                }

                $i++;
            }else if(substr($key, 0, 4) == 'qid_' && $this->app =='mpg'){
                $qid = (int)strtolower(substr($key,4));
                if($qid == 0){
                    $qid = 26;
                }
                $question_responses[] = array('question_id' => $qid, 'member_id' => $post_data['member_id'], 'response' => $value);
            }
        }

        foreach ($question_mapping as $key => $value) {
            if (!isset($post_data[$key])) {
                $qid = $question_mapping[$key];
                $value = !empty($post_data['qid_'.$qid]) ? $post_data['qid_'.$qid] : "";
                //if there is _other response save in the database
                if(!empty($post_data['qid_'.$qid . '_other']) && !is_array($value)){
                    $value .= ":{$post_data['qid_'.$qid . '_other']}";
                }else if(is_array($value)){
                    $optionResponses = $value;
                    if(!empty($post_data['qid_'. $qid . '_other'])){
                        foreach ($value as $idx => $option){
                            if(substr(strtolower(trim($option)), 0, 5) === 'other'){
                                $otherValue = $post_data['qid_'. $qid . '_other'];
                                $optionResponses[$idx] = $option . ':' . $otherValue;
                            }
                        }
                    }
                    
                    $value = implode("|", $optionResponses);
                }
                if ($is_survey == true) {
                    $question_responses[] = array('question_id' => $qid,
                        'member_id' => $post_data['member_id'],
                        'response' => $value,
                        'incentive_offered' => $incentive_offered
                    );
                } else {
                    $question_responses[] = array('question_id' => $qid, 'member_id' => $post_data['member_id'], 'response' => $value);
                }
            }
        }

        if (count($question_responses) > 0 && !empty($question_responses)) {

            if (!$for_tm_qa) {
                if ($is_survey == true) {
                    $survey_response_constants = array(
                        'campaign_id' => $post_data['eg_campaign_id'],
                        'email' => $post_data['email'],
                        'ip' => '',
                        'site_id' => $post_data['campaign_site'],
                        'source' => $post_data['source'],
                        'lead_id' => $post_data['lead_id'],
                        'resource_id' => !empty($post_data['resource_id']) && $post_data['resource_id'] > 0 ? $post_data['resource_id'] : 'NULL' ,
                        'released_from_qa' => 1
                    );
                    $result = $membersModel->upsert_question_responses(
                        $question_responses,
                        false,
                        true,
                        $survey_response_constants
                    );
                } else {
                    $result = $membersModel->upsert_question_responses($question_responses, true);
                }
            } else {
                if ($is_survey == true) {
                    $result = $membersModel->upsert_question_responses_from_tm_qa($question_responses, false, true);
                } else {
                    $result = $membersModel->upsert_question_responses_from_tm_qa($question_responses, true);
                }
            }
            return $result;
        }
        return true;
    }

    function _insert_cq_regform_responses($post_data,$membersModel=null,$for_tm_qa=0){


        $i = 1;

        foreach ($post_data as $key => $value) {
            //we want to find fields starting with 'question'
            if(substr($key, 0, 4) == 'qid_'){
                $qid = (int)strtolower(substr($key,4));
                if($qid == 0){
                    $qid = 26;
                }
                $question_responses[] = array('question_id' => $qid, 'member_id' => $post_data['member_id'], 'response' => $value);
            }
        }
        if (count($question_responses) > 0) {
            if(empty($membersModel)){
                $this->load->Model('Member_model');
                $membersModel = new Member_model();
            }
            if (!$for_tm_qa) {
                $result = $membersModel->upsert_question_responses($question_responses, true);
            } else {
                $result = $membersModel->upsert_question_responses_from_tm_qa($question_responses, true);
            }
        }

        return $result;
    }

    function _update_member($membersModel, $post_data, $member_id, $for_tm_qa = 0)
    {
        //This version of the update function doesn't assume
        //all contact fields will be posted all the time. So
        //we need to loop through post fields and set the ones
        //that have actually been passed. In some cases maybe none.
        $result = false;
        $normalization_rules = $membersModel->get_member_normalization_rules();

        //Set field counter
        $num_fields = 0;

        //Set member object properties
        $member = new Member();

        if (!empty($post_data['call_disposition'])) {
            $allowedDoNotCallDispositionCreateLeadArray = array('7', '11', '16', '17', '18','20','21');//'7', '11', '16', '17', '18'
            $allowedDoNotCallStatus = in_array($_POST['call_disposition'], $allowedDoNotCallDispositionCreateLeadArray);
            if ($allowedDoNotCallStatus) {
                $post_data['do_not_call'] = 1;
            }
        }
        $func = 'setMemberObjectData'.ucfirst($this->app);

        $num_fields = $this->$func($post_data, $member, $num_fields);

        if (isset($member->job_title)) {
            $this->load->library('Normalize');
            $normalize = new Normalize();
            $member->job_level = $normalize->job_level($member->job_title, $normalization_rules);
            $member->silo = $normalize->silo($member->job_title, $normalization_rules);
            $member->ml_title = $normalize->ml_title($member->silo,$member->job_level);
        }

        if ($num_fields > 0) {
            $member->id = $member_id;
            $member->ip = $_SERVER["REMOTE_ADDR"];
            $member->phone_verified = 1;
            $member->updated_at = date('Y-m-d H:i:s', time());
            $member->updated_by = $this->session->userdata('uid');

            //Try to insert new member to DB

            $result = $membersModel->update_member_from_tm_qa($member);

            $member_id = $member->id;
           
        }
        return $result;
    }

    function _set_question_field_name_mapping($cq_ids)
    {
        //create array from questions ids and sort it
        $array = explode(',', $cq_ids);
        // sort($array);

        //create a new array to hold question field name as key with actual
        //cq id as value. This array will hold mapped field names to actual ids.
        $questions = array();
        $counter = 1;
        foreach ($array as $a) {
            $a = (int)$a;
            if ($a > 0) {
                $key = 'question' . $counter;
                $questions[$key] = $a;
                $counter++;
            }
        }
        return $questions;
    }

    public function saveContactCallDetail($list_id, $callsModel)
    {
        $campaign_id = null;
        $qa = false;
        if (!empty($_POST['Qaing'])) {
            $qa = true;
            $campaign_id = $this->process_qa($callsModel, $list_id);
        }else{
            $campaign_id = $this->process_agent($callsModel, $list_id);
        }
        
        $this->is_lead_generated($campaign_id, $list_id, $qa);
    }
    
    function set_question_mapping(){
        $cqs = '';
        $question_mapping = array();
        if(isset($_POST['questions']) && !empty($_POST['questions'])){
            $cqs = $_POST['questions'];
        }
        if(isset($_POST['intent_questions']) && !empty($_POST['intent_questions'])){
            $cqs = !empty($cqs) ? $cqs.','.$_POST['intent_questions'] : $_POST['intent_questions'];
        }
        if(!empty($cqs)) {
            $question_mapping = $this->_set_question_field_name_mapping($cqs);
        }
        return $question_mapping;
    }
    
    function unlock_next_contact_and_redirect($contactsModel,$msg, $path){
        if(!empty($_POST['next_campaign_contact_id'])) {
            //lead is generated so please unlock the contact
            $this->unlock_contact($_POST['next_campaign_contact_id'], $contactsModel);
        }
        $this->session->set_flashdata('class', 'bad');
        $this->session->set_flashdata('msg', $msg);
        redirect($path);
    }
    
    /**
     * 
     * @param type $contact_id
     * @param type $contactsModel
     */
    function unlock_contact($contact_id, $contactsModel){
        $unlockContactData = array();
        $unlockContactData['edit_lead_status'] = '0';
        $unlockContactData['locked_by'] = '';
        $contactsModel->update_contact($contact_id, $unlockContactData);
    }
    
    
    /**
     * 
     * @param type $contact_id
     * @param type $contactsModel
     */
    function lock_contact($contact_id, $contactsModel){
        $lockContactData = array();
        $lockContactData['edit_lead_status'] = '1';
        $lockContactData['locked_by'] = $this->session->userdata('uid');
        $contactsModel->update_contact($contact_id, $lockContactData);
    }
    
    function is_lead_generated($campaign_id,$list_id, $qa){
        if (!empty($campaign_id)) {

                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Call detail added successfully!');

                if ($qa && (!empty($_POST['Qaing']))) {
                    //$this->session->set_flashdata('class', 'bad');
                    //$this->session->set_flashdata('msg', 'Sorry, an error has occurred!');
                    redirect('/dialer/leads');

                } else if (isset($_POST['next_campaign_contact_id'])) {
                    if(!empty($_POST['next_campaign_contact_id'])) {
                        redirect("/dialer/calls/index/" . $_POST['next_campaign_contact_id']."/".$list_id);
                    } else {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, there are no more workable contacts.');
                        redirect('/dialer/contacts/index/' . $campaign_id.'/'.$list_id);
                    }
                } else if (isset($_POST['decision']) && $_POST['decision'] == 'Add as a different person') {
                    redirect("/dialer/calls/index/" . $_POST['campaign_contact_id'] ."/".$list_id . "/add");
                } else if ($this->session->contactdata("ContactFilter")) {
                    redirect("/dialer/contacts/index/" . $campaign_id ."/".$list_id . "/contactsort");
                } else {
                    redirect("/dialer/contacts/index/" . $campaign_id ."/".$list_id);
                }

            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occurred!');
                redirect('/dialer/contacts/index/' . $_POST['campaign_id'].'/'.$list_id);

            }
    }
    
    /**
     * Check if the campaign working on 
     * @param type $campaign_id
     * @param type $agentSessionCampaignId
     * @param type $contactsModel
     */
    function validate_campaign($campaign_id){
        if(empty($campaign_id)){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please make sure that you are working with correct campaign!.');
                redirect('/dialer/campaigns');

            }
    }
    
    function agent_check_campaign_sign_in($campaign_id, $agentSessionCampaignId, $contactsModel){
        //Checking: before submit lead, Current contact's campaign should be Sign-in by TL and agent.
        if (!empty($campaign_id) && empty($_POST['Qaing']) && ($this->session->userdata('user_type') == 'agent' || $this->session->userdata('user_type') == 'team_leader') && (empty($agentSessionCampaignId) || ($agentSessionCampaignId != $campaign_id))) {
            $emailChangeContactData['edit_lead_status'] = '0';
            $emailChangeContactData['locked_by'] = '';
            $contactsModel->update_contact($_POST['contact_id'], $emailChangeContactData);
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please sign In with this Campaign First.');
            redirect('/dialer/campaigns');
        }
    }
    
    function submit_go_to_next($list_id, $campaign_id, $contactsModel, $callsModel){

        $where = "";
        if ($this->session->contactdata("ContactFilter") && !empty($this->session->set_contactdata)) {
            $where = $this->getFilterQuery($this->session->set_contactdata);
        }
        $contact_filter = $contactsModel->get_contact_filter($list_id);
        if(!empty($contact_filter)){
            $contact_filters = explode("|", $contact_filter);
            $build_filter = array();
            foreach($contact_filters as $filter){
                $get_filter = explode(":", $filter);
                $build_filter[] = "c.{$get_filter[0]} in ('{$get_filter[1]}') ";
            }
            $where = !empty($where) ? $where . " AND ": $where;
            $where .= implode(" AND ", $build_filter);
        }

        $sort_index = "asc";

        $sort_field = "cd.id = 0, cd.id >0,FIELD (cl.filter_status,'1') DESC ,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),
        FIELD (c.time_zone,'EST','CST','MST','PST') ,FIELD(cl.source,'api','file') ASC,c.id ";
        
        $get_next_campaign_contact = $callsModel->get_next_contact($where, $sort_field, $sort_index,$_POST['campaign_contact_id'],$list_id);
        if(empty($get_next_campaign_contact)){
            $_POST['next_campaign_contact_id'] = null;

            // unlock current lead
            $this->unlock_contact($_POST['contact_id'], $contactsModel);

        } else {
            $is_locked = $contactsModel->is_locked_by_other($get_next_campaign_contact->contact_id,$this->session->userdata('uid'));

            // unlock current lead
            $this->unlock_contact($_POST['contact_id'], $contactsModel);

            $nonWorkableDispositions = array_merge($this->_nonWorkableDispo, array(2));
            $disposition = !empty($_POST['call_disposition']) ? $_POST['call_disposition'] : 0;
            if($get_next_campaign_contact->contact_list_id == $_POST['campaign_contact_id'] && !empty($disposition) && in_array($disposition, $nonWorkableDispositions)){
                $_POST['next_campaign_contact_id'] = null;
            }else{
                $_POST['next_campaign_contact_id'] = $get_next_campaign_contact->contact_list_id;
                if(!$is_locked) {
                    $this->lock_contact($get_next_campaign_contact->contact_id, $contactsModel);
                }
            }
        }
    }

    // call from  getContacts() to set query filters
    public function getFilterQuery($filters, $isContactListPage = 0)
    {
        $where_cond = "";

        foreach ($filters->rules as $k => $cond) {
            if ($where_cond != "") {
                $where_cond .= " " . $filters->groupOp . " ";
            }
            if ($cond->field == "full_name") {
                $where_cond .= 'CONCAT(c.first_name," ",c.last_name)';
            } else if ($cond->field == "agent_name" && (empty($isContactListPage))) {
                //$where_cond .= 'u.first_name';
                $where_cond = ""; break;
            } else {
                if ($cond->field == "status" && (empty($isContactListPage))) {
                    $where_cond .= "tlh.";
                }
                $where_cond .= $cond->field;
            }

            if ($cond->op == "cn") {
                if($cond->field != "time_zone"){
                    $where_cond .= " like '%" . $cond->data . "%'";
                }else if($cond->field == "time_zone"){
                    $where_cond .= " = '".$cond->data."'";
                }
            } else {
                $where_cond .= " = '" . $cond->data . "'";
            }
        }
        return $where_cond;
    }

    // member detail inser into uber's members_qa table
    function _insert_member_to_tm_qa($membersModel, $member_id)
    {
        $membersModel = new Members_model();
        $func = 'insert_member_to_tm_qa_'.$this->app;
        $member_id = $membersModel->$func($member_id);
        return $member_id;
    }

    //as per EG-admin source
    function _verify_partner_hql_post($question_mapping, $rv)
    {
        $errors = array();
        $q_error = array();
        //build array of required fields. campaign_id already checked in post() function
        $fields = array('campaign_id');
        // For QA
        if ((!empty($_POST) && !empty($_POST['decision']) && $_POST['decision'] == 'Approve')) { //decision - Approve
            //Now append campaign cq to $fields array
            foreach ($question_mapping as $key => $value) {
                array_push($fields, $key);
            }
        }

        //now check if parent lead exists. If not we'll need to create it and will need resource_id
        $parent_lead = array();

        if (!empty($_POST['member_id']) && trim($_POST['member_id']) > 0) { // trim($_POST['member_id']) != '' &&

            $parent_lead = $rv->get_parent_lead(trim($_POST['campaign_id']), trim($_POST['member_id']));
        }

        //Finish
        $result = array('parent_lead' => $parent_lead, 'errors' => $errors, 'questions' => $q_error);

        return $result;
    }

    // Contact form server side - validation
    public function ContactCallFormValidation($qa=false)
    {
        $userType = $this->session->userdata('user_type');
        if($qa){
            $this->form_validation->set_rules('first_name', 'first_name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('last_name', 'last_name', 'required|trim|max_length[100]');
            // $this->form_validation->set_rules('email', 'Email', 'required|trim|max_length[255]|valid_email');
            $this->form_validation->set_rules('phone', 'phone', 'required|trim|max_length[20]');
        }
        if ($userType != 'qa' && (!$_POST['Qaing'])) {
            $this->form_validation->set_rules('call_disposition', 'Call Disposition', 'required|trim|max_length[100]');
        }
        
        if (!in_array($_POST['campaign_type'], array('pureresearch','puremql','smartleads')) && ((isset($_POST['call_disposition']) && $_POST['call_disposition'] == 1) || isset($_POST['decision']) && $_POST['decision'] == 'Approve')) {
            $this->form_validation->set_rules('resource_id', 'Resource', 'required|trim');
        }
            
        if (isset($_POST['emailChange']) && $_POST['emailChange'] == '1') {
            $this->form_validation->set_rules('newemail', 'Email', 'required|trim|max_length[255]|valid_email');
        }
    }

    public function checkDisposOfCall(){
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $campaignContactID = $this->input->post('campaign_contact_id');
        $checkIfHasOngoingCall = $callsModel->getCallHistoryByCampaignContactId($campaignContactID, "ch.id,ch.call_disposition_id as callDispo");
        
        if($campaignContactID > 0 && !empty($checkIfHasOngoingCall) && empty($checkIfHasOngoingCall[0]->callDispo)){
            $data['message'] = "Previous call made to this contact was not disposed";
            $data['status'] = false;
            
        }else{
            $data['message'] = "";
            $data['status'] = true;
        }
        echo json_encode($data);
        exit();
    }
    
    public function checkIfDispoHasCall(){
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $campaignContactID = $this->input->post('campaignContactId');
        $checkLastCall = $callsModel->getLastCallHistoryId($campaignContactID);
        $errorMessage = "";
        $status = true;
        if(empty($checkLastCall) || (!empty($checkLastCall) && $checkLastCall['call_disposition_id'] > 0)){
            $errorMessage = "Please call before submitting a disposition.";
            $status = false;
        }
        
        $data['message'] = $errorMessage;
        $data['status'] = $status;
        echo json_encode($data);
        exit();
    }
    
    // agent call start and insert entry into call history table
    public function agentStartCallDial()
    {
        $loggedUserID = $this->session->userdata('uid');

        $agentCallDialObjectData = $this->input->post();

        $data['makecall'] = true;

        if (!empty($agentCallDialObjectData)) {

            $this->load->model('Calls_model');
            $callsModel = new Calls_model();

            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();

            $redirect = '';
            $makeCall = true;
            $contactID = $this->input->post('contact_id');
            $campaignID = $this->input->post('campaign_id');
            $campaignContactID = $this->input->post('campaign_contact_id');
            $listID = $this->input->post('list_id');
            $moduleType = $this->input->post('module_type');
            $dialedNumber = $this->input->post('phone');
            $callDispositionId = $this->input->post('call_disposition');
            $previousCallDispositionId = $this->input->post('previous_call_disposition_id');
            // get call limit from config file
            $callLimit = $this->config->item('call_limit');

            $controllerPath = "dialer";

            $isLockedBy = $contactID > 0 ? $callsModel->isLockedBy($contactID) : 0;

            // skip validations for manual add and add diff form
            $isNormalForm = $_POST['is_manual_create'] == 'false' && empty($_POST['is_add_page']) ? true : false;            
            
            /*
            ** Check if the contact is locked to the user.
            ** If not, redirect the user to the call queue
            */
            if($isLockedBy > 0 && $loggedUserID != $isLockedBy && $isNormalForm) {
                $makeCall = false;
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, you can`t access this lead. Another QA Or User is currently working on it.');
            } else {

                /*
                ** Validate if the user can still call the contact
                ** If not, redirect the user to the call queue with error message
                */
                $followUpDetails = array();
                if($isNormalForm && $campaignContactID > 0 && $moduleType == 'tm') {

                    $checkIfHasOngoingCall = $callsModel->getCallHistoryByCampaignContactId($campaignContactID,"ch.id,ch.call_disposition_id as callDispo");
                    if(!empty($checkIfHasOngoingCall) && empty($checkIfHasOngoingCall[0]->callDispo)){
                        $data['nodispo'] = true;
                        $data['message'] = "Previous call made to this contact was not disposed";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }

                    if(!$callsModel->isLeadWorkable($campaignContactID))
                    {
                        $makeCall = false;

                        $followUpDetails = $callsModel->getLeadFollowup($campaignContactID,'id,last_follow_up_date');
                        if(!empty($followUpDetails)) {
                            $makeCall = true;
                        }

                        if(!$makeCall) {

                            // unlock if it's agent's contact
                            $contactsModel->unlockContact($contactID, ' AND locked_by = ' . $loggedUserID);

                            $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', 'Contact in QA progress or already disposed as non-workable. ');
                        }
                    }
                    
                }
                
                /*
                ** Check if the dialed number already reached it's limit
                ** If yes, redirect the user to call queue
                */
                if($callsModel->isPhoneReachedLimit($dialedNumber, $callLimit) && $callDispositionId != '2' && $makeCall) {
                    $makeCall = false;


                    if(!empty($followUpDetails)) {
        
                            $makeCall = $this->allowLastFollowUpToday($followUpDetails->last_follow_up_date);

                    } else {

                        // unlock if it's agent's contact
                        $contactsModel->unlockContact($contactID, ' AND locked_by = ' . $loggedUserID);
                        $errorMessage = "Sorry, You can't call/dial the contact's phone number more than {$callLimit} times per day.";
                        if($_POST['is_manual_create'] == 'true' || $campaignContactID == 'undefined'){
                            $data['reachedLimitCall'] = true;
                            $data['message'] = $errorMessage;
                            $data['status'] = false;
                            echo json_encode($data);
                            exit();
                        }else{
                            $this->session->set_flashdata('class', 'bad');
                            $this->session->set_flashdata('msg', $errorMessage);
                        }

                    }

                    
                    



                } else if($makeCall && $previousCallDispositionId == '2') {
                    $callbackTodayLimit = $this->checkTodayCallbackDial($callsModel, $campaignContactID);
                    if(!empty($callbackTodayLimit) && $callbackTodayLimit['isTodayExceedCallDial']) {
                        $makeCall = false;
                        $data['reachedLimitCall'] = true;
                        $data['message'] = $callbackTodayLimit['todayCallDiallerMessage'];
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                }
            }

            if($makeCall) {
                //Set Call History object properties
                $agentStartCallHistoryDetail = $callsModel->setCallHistoryObjectData();
                $agentStartCallHistoryDetail->created_at = date('Y-m-d H:i:s', time());
                $agentStartCallHistoryDetail->count_flag = 0;

                $agentStartCallHistoryID = $callsModel->InsertAgentStartCallHistory((array)$agentStartCallHistoryDetail);
                if ($agentStartCallHistoryID) {
                    $callsModel->InsertAgentCallHistoryCampaignContact($agentStartCallHistoryID, $campaignContactID, 0);
                    $data['status'] = true;
                    $data['data'] = $agentStartCallHistoryID;
                } else {
                    $data['message'] = "Sorry, an error has occurred.";
                    $data['status'] = false;
                }
            } else {
                $data['message'] = "Call Validation Failed.";
                $data['status'] = false;
                $data['makecall'] = false;
            }
        } else {
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
        }
        echo json_encode($data);
        exit();
    }

    // Update all the information based on contact after call end
    public function agentEndCallDial()
    {
        $agentCallDialObjectData = $this->input->post();

        if (!empty($agentCallDialObjectData)) {

            $last_call_history_id = "";
            if (isset($_POST['last_call_history_id']) && !empty($_POST['last_call_history_id'])) {
                $last_call_history_id = $_POST['last_call_history_id'];
            }
            if (!empty($last_call_history_id)) {
                $this->load->model('Calls_model');
                $callsModel = new Calls_model();

                $check_call_history_exist = $callsModel->get_call_history_data($last_call_history_id);

                if (!empty($check_call_history_exist)) {

                    //Set Call History object properties
                    $callHistoryDetail = new stdClass();
                    $callHistoryDetail->id = $last_call_history_id;
                    //remove overwriting of
                    //if (isset($_POST['call_start_datetime'])) {
                        //$callHistoryDetail->call_start_datetime = date('Y-m-d H:i:s', strtotime($_POST['call_start_datetime']));
                    //}

                    //$callHistoryDetail->call_end_datetime = date('Y-m-d H:i:s', strtotime($_POST['call_end_datetime']));
                    $callHistoryDetail = $this->unset_nulls($callHistoryDetail);
                    // check count flag for 15 minutes - if call conversation more than 15 seconds then and then only count flag should be "1"
                    $countFlag = 0;
                    if (!empty($check_call_history_exist->call_end_datetime)) {
                        $callHistoryDetail->count_flag = $this->check_dial_count_flag($check_call_history_exist->call_start_datetime, $check_call_history_exist->call_end_datetime);
                        $countFlag = $callHistoryDetail->count_flag;
                    }

                    $callsModel->updateAgentCallHistory($callHistoryDetail);
                    $data['recLink'] = $recLink = $check_call_history_exist->recording_url;
                    // get last recording URL from plivo communication table for specific worked campaign contact id
                }
            }
            $data['status'] = true;
            echo json_encode($data);
            exit();
        }
    }

    //Checking  any user working on contact and based on that change status of lock & unlock contact
    public function lockUnlockContact()
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();
        $userType = $this->session->userdata('user_type');
        $callObjectData = $this->input->post();

        $loggedUserID = $this->session->userdata('uid');
        if (!empty($_POST['contact_id']) && $_POST['contact_id'] > 0) {
            $contact_id = $_POST['contact_id'];
        } else {
            $contact_id = 0;
        }
        $agentSessionCampaignID = $this->session->userdata('AgentSessionCampaignID');
        if (empty($_POST['action_qa'])) {
            $this->check_campaign_active_session($agentSessionCampaignID, $_POST['campaign_id']);
        }

        if (!empty($_POST['contact_id']) && $_POST['contact_id'] > 0) {

            $contactEditable = $contactsModel->get_one_contact($_POST['contact_id']);

            if (!empty($contactEditable)) {
                // Checking already another user is working or not
                if (!empty($callObjectData['LeadEditBy']) && $callObjectData['LeadEditBy'] != $loggedUserID) {
                    if ($contactEditable->edit_lead_status == 1 && $contactEditable->locked_by != $loggedUserID) {
                        $data['message'] = "Sorry, you have not permission already using by other user.";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                } else if (empty($callObjectData['LeadEditBy'])) {
                    if ($contactEditable->edit_lead_status == 1 && $contactEditable->locked_by != $loggedUserID) {
                        $data['message'] = "Sorry, you have not permission already using by other user.";
                        $data['status'] = false;
                        echo json_encode($data);
                        exit();
                    }
                }
            }
        }
        if (empty($_POST['action_qa'])) {
            $this->check_multiple_contact_access($loggedUserID, $contact_id);//,$_POST['contact_id']
        }
        if (!empty($_POST['campaign_contact_id'])) {
            $this->load->model('Calls_model');
            $callsModel = new Calls_model();
            // Checking for already any other user is working with Call back Disposition
            $isCallBackByAgent = $callsModel->IsCallBackByAgent($_POST['campaign_contact_id']);

            if (!empty($isCallBackByAgent)) {
                if ($loggedUserID != $isCallBackByAgent->agent_id) {
                    $data['message'] = "Sorry, you have not permission for accept this contact already other user working as a Callback.";
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }
            }

        }

        if (!empty($callObjectData)) {
            if (!empty($callObjectData['edit_lead_status'])) {
                $edit_call['edit_lead_status'] = $callObjectData['edit_lead_status'];
                $edit_call['locked_by'] = $this->session->userdata('uid');
            } else {
                $edit_call['edit_lead_status'] = '0';
                $edit_call['locked_by'] = '';
            }
            if (!empty($callObjectData['contact_id']))
                $contact_id = $callObjectData['contact_id'];

            $updateLock = $contactsModel->update_contact($contact_id, $edit_call);
            if ($updateLock) {
                $data['status'] = true;
            } else {
                $data['message'] = "Sorry, an error has occurred.";
                $data['status'] = false;
            }
        } else {
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
        }
        echo json_encode($data);
        exit();
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
                case 'address1':
                    $member->address1 = $value;
                    $num_fields++;
                    break;
                case 'address': // Manually Added
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
                    $member->phone = substr($value, strlen($_POST['dial_code']));//$value;
                    $num_fields++;
                    break;
                case 'ext':
                    $member->ext = $value;
                    $num_fields++;
                    break;
                case 'company_name':
                    $member->company_name = $value;
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
                case 'qid_26':
                    $member->job_title = $value;
                    $num_fields++;
                    break;
                case 'qid_16':
                    $member->job_level = $value;
                    $num_fields++;
                    break;
                case 'qid_8':
                    $member->industry = $value;
                    $num_fields++;
                    break;
                case 'industry': // Manually Added
                    $member->industry = $value;
                    $num_fields++;
                    break;
                case 'qid_5':
                    $member->company_size = $value;
                    $num_fields++;
                    break;
                case 'company_size': // Manually Added
                    $member->company_size = $value;
                    $num_fields++;
                    break;
                case 'original_owner': // Manually Added
                    $member->original_owner = $value;
                    $num_fields++;
                    break;
                case 'company_revenue': // Manually Added
                    $member->company_revenue = $value;
                    $num_fields++;
                    break;
                case 'source':
                    $member->source = ($value == 'call_file' ? 'tm_call_file' : $value);
                    break;
                case 'do_not_call':
                    $member->do_not_call = 1;
                    break;
            }
        }

        return $num_fields;
    }

    /**
     * @param $post_data
     * @param $member
     * @param $num_fields
     * @return mixed
     */
    public function setMemberObjectDataMpg($post_data, $member, $num_fields)
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
                case 'address1':
                    $member->address1 = $value;
                    $num_fields++;
                    break;
                case 'address': // Manually Added
                    $member->address2 = $value;
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
                    $member->phone = substr($value,strlen($_POST['dial_code']));//$value;
                    $num_fields++;
                    break;
                case 'company_name':
                    $member->company_name = $value;
                    $num_fields++;
                    break;
                case 'source':
                    $member->source = $value;
                    break;
                case 'do_not_call':
                    $member->do_not_call = 1;
                    break;
            }
        }

        return $num_fields;
    }

    // Send Email resource as per selected resource from drop-down
    function send_resource()
    {
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();

        if (!empty($_POST['contact_id']) && empty($_POST['action_qa'])) {
            $loggedUserID = $this->session->userdata('uid');
            $this->check_multiple_contact_access($loggedUserID, $_POST['contact_id']); //
        }

        $campaignId = $this->input->post('campaign_id');

        $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');

        if (empty($_POST['action_qa'])) {
            $this->check_campaign_active_session($agentSessionCampaignId, $campaignId);
        }

        if (empty($_POST['action_qa']) && ($this->session->userdata('user_type') == 'agent' || $this->session->userdata('user_type') == 'team_leader') && (empty($agentSessionCampaignId) || ($agentSessionCampaignId != $campaignId))) {
            $this->load->model('Contacts_model');

            $emailChangeContactData['edit_lead_status'] = '0';
            $emailChangeContactData['locked_by'] = '';
            $this->Contacts_model->update_contact($_POST['contact_id'], $emailChangeContactData);

            $data['message'] = "Please sign In with this Campaign First.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
        
        
        // Checking for validate field value
        if (!($this->input->post('name'))) {
            $data['message'] = "Please enter first name!";
            $data['status'] = false;

            echo json_encode($data);
            exit();
            //exit('Please check email');
        }
        if (!($this->input->post('email'))) {
            $data['message'] = "Please enter email!";
            $data['status'] = false;

            echo json_encode($data);
            exit();
        }
        if (!($this->input->post('resource'))) {
            $data['message'] = "Please select a resource.";
            $data['status'] = false;

            echo json_encode($data);
            exit();
        }
        if (!($this->input->post('campaign'))) {
            $data['message'] = "Please check campaign!";
            $data['status'] = false;

            echo json_encode($data);
            exit();
        }
        $campaignId = $this->input->post('campaign');
        $resourceId = $this->input->post('resource');
        $emailTo = $this->input->post('email');
        $name = $this->input->post('name');

        $this->load->model('Resourceview_model');
        $resourceViewModel = new Resourceview_model();

        $site = "enterpriseguide.com";
        switch ($this->input->post('campaign')) {
            case 831:
                $site = "guidesforvoip.com";
                $from = 'content@'.$site;
                $team = 'Guides for VOIP Team';
                break;
            case 1272:
                $brand = "guidesforcrm.com";
                $from = 'content@'.$site;
                $team = 'Guides for CRM Team';
                break;
            default:
                $from = 'content@'.$site;
                $team = 'EnterpriseGuide Team';
                break;
        }
        
        // get campaign
        $campaign = $callsModel->getEGCampaignDataByID_calls($campaignId);
        
        if(!empty($campaign->tm_brand)) {
            $brand = str_replace(array("'"," "),"", strtolower($campaign->tm_brand));
            $site = $brand.'.com';
            $from = 'content@'.$site;
            $team = $campaign->tm_brand.' Team';
        }
        
        $resources = array();
        if($campaign->resources!="")
        {
        // get resources for the campaign
        $resources = $resourceViewModel->get_list_by_ids($campaign->resources);
        }

        if (empty($resources)) {
            $data['message'] = "Resource not exist!";
            $data['status'] = false;

            echo json_encode($data);
            exit();
        }

        // get selected resource
        $selectedResource = $resourceViewModel->get_resource_by_id($resourceId);

        $this->load->model('Emailtemplates_model');
        $getEmailTemplate = $this->Emailtemplates_model->get_emailTemplate_by_campaignResource($this->input->post('campaignId'), $resourceId);

        $userName = $this->session->user_fname . " " . $this->session->user_lname;

        // build the email content.

        if (!empty($getEmailTemplate)) {
            $subjectLine = str_replace('#DATETIME#', date("m/d/Y h:i:s"), $getEmailTemplate->subject_line);
            $body = str_replace('#DATETIME#', date("m/d/Y h:i:s"), $getEmailTemplate->body);
            $signatureLine = str_replace('#AGENTNAME#', $userName, $getEmailTemplate->signature_line);
            $subject = $subjectLine;
            $bodyContent = $body . "<br/>" . $signatureLine;
        } else {

            $bodyContent = "Hi " . $name . ",<br><br>
                Thank you for taking the time to speak with me today. Please click on the link below to access your " . $selectedResource->type . ":<br><br>";

            // include selected resource in the email content
            $bodyContent .= '<a href="' . $selectedResource->file . '">' . $selectedResource->name . '</a><br/><br>';

            // get top resources
            $otherResources = $resourceViewModel->get_top_resources($campaignId,2,$resourceId,$campaign->resources);

            // only include other resources if there is any.
            if (count($otherResources) > 0) {

                $bodyContent .= "<br>You may also download our other assets here:<br><ul>";

                foreach($otherResources as $resource) {
                    // include other resources in the email content
                    $bodyContent .= '<li><a href="'.$resource->file.'">'.$resource->name.'</a></li>';
                }

                $bodyContent .= "</ul>";
            }

            $bodyContent .= 'You should receive a follow-up from our sponsor of this ' . $selectedResource->type . ' soon. Thank you and hope you have a great day.<br/>';
            $bodyContent .= "<br>All the Best,<br> $team"; // $team
            $subject = "$selectedResource->name";
        }
        
        $bodyContent .= "<br><br><span style='font-size:11px;font-weight:bold'>www.{$site} ";
        $bodyContent .= "| <a href='{$site}/terms'>Terms of Use</a> ";
        $bodyContent .= "| <a href='{$site}/privacy'>Privacy Policy</a> |</span>";
                
        $this->load->helper('common'); // lead common helper to send mail

        // if add calendar event attchment with resource type is webcast
        // TODO:study  swift mailer to use webcast type resources
//        if ($selectedResource->type == 'Webcast') {
//            $emailResult = send_mail_swift_mailer(trim($emailTo), $subject, $bodyContent, $selectedResource,$userName);
//        } else {
            // else sparkpost api
            $emailResult = send_email_sparkpost($emailTo, $subject, $bodyContent,$from);
        //}
        $returnMsg = "Email has been sent";

        if (!$emailResult['status']) {
            $returnMsg = 'Error! Please contact the dev team (devteam@pureincubation.com)';
            $data['status'] = false;
        } else {
            if (!empty($_POST['resource'])) {
                $emailHistory = $callsModel->setEmailHistoryObjectData();
                if(!empty($emailResult['data'])){
                    $emailHistory->sparkpost_message_id = $emailResult['data'];
                }
                // store history of send email resource detail
                $emailHistoryId = $callsModel->insert_sent_email_history($emailHistory);

                if ($emailHistoryId <= 0) {
                    $data['status'] = false;
                    $data['message'] = "Sorry, Oops! Something went wrong.";
                    echo json_encode($data);
                    exit();
                }
            }
            $data['status'] = true;
        }

        // log resources
        $data['message'] = $returnMsg;

        echo json_encode($data);
        exit();
    }

    // Checking: When user entered email id on add as a diff. person page we are checking that entered email id already exist on eg-members table or not.
    function check_add_email_contact_exist()
    {
        $this->load->model('Contacts_model');
        $contactModel = new Contacts_model();

        if (!empty($_POST['email'])) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('email', 'Email', 'required|trim|max_length[255]|valid_email');    
            
            if ($this->form_validation->run() == FALSE) {
                $data['message'] = "Please enter valid email.";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            //check email exists in uber app
            $contactEmailExist = $contactModel->checkEmailExist($_POST['email']);
            
            if($contactEmailExist) {
                $data['id'] = $contactEmailExist->id;
                $data['campaign_id'] = $_POST['campaign_id'];
                
                $this->load->model('Calls_model');
                $callsModel = new Calls_model();
                // get call limit from config file
                $callLimit = $this->config->item('call_limit');
                $isExceeded = $callsModel->CheckTodayCallDialledLimit($contactEmailExist->id, $callLimit);
                if($isExceeded && !empty($_POST['manual'])){
                    $data['message'] = "Sorry, You can't call/dial the contact's phone number more than {$callLimit} times per day.";
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }
                
                $isexist =  $contactModel->isContactExist($data);

                if (!empty($_POST['manual']) && $contactEmailExist->do_not_call_ever) {
                    $DncDispositions = $this->do_not_call_ever_callDisposition_array;
                    if (($key = array_search('18', $DncDispositions)) !== false) {
                        unset($DncDispositions[$key]);
                    }
                    //check if manual
                    if(in_array($isexist->call_disposition_id, $DncDispositions)){
                        $data['message'] = "The email is tagged as Non Workable. This cannot proceed.";
                    }else{
                        
                        $data['message'] = "The email is tagged as Do Not Call Ever. This cannot proceed.";
                    }
                    
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }else if(!empty($isexist->id) && !empty($_POST['manual'])){
                    $data['message'] = "This contact already exists in the same campaign.";
                    $data['status'] = false;
                    echo json_encode($data);
                    exit();
                }
            
                if(!empty($isexist->id)){

                    $campaignContactID = $contactModel->campaign_contact_id($data);
                    if (!empty($campaignContactID['id']) && !empty($_POST['original_call_history_id'])){
                        $callHistoryId = $this->copy_call_history($contactEmailExist->id, $_POST['original_call_history_id'], null, $campaignContactID['id']);
                    }

                    // update list id
                    $ccdata['list_id'] = $_POST['list_id'];
                    $contactModel->updateCampaignContact($campaignContactID['id'], $ccdata);

                    // save changes for this campaign_contact record.
                    $ccChanges['campaign_contact_id'] = $campaignContactID['id'];
                    $ccChanges['from_list_id'] = $campaignContactID['list_id'];
                    $ccChanges['new_source'] = 'add_diff';
                    $ccChanges['created_by'] = $this->session->userdata('uid');
                    $ccChanges['created_at'] = date('Y-m-d H:i:s', time());
                    $contactModel->insertCampaignContactChanges($ccChanges);

                    $rdata['cid'] = $campaignContactID['id'];
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'This contact was found in our database.');
                    echo json_encode($rdata);
                    exit();
                }else{
                    $contactListsData = array();
                    $contactListsData['contact_id']  = $contactEmailExist->id;
                    $contactListsData['campaign_id'] = $_POST['campaign_id'];
                    $contactListsData['source'] = 'add_diff';
                    $contactListsData['list_id'] = $_POST['list_id'];
                    $contactListsData['created_by'] = $this->session->userdata('uid');
                    $contactListsData['created_at'] = date('Y-m-d H:i:s', time());
                    if(!empty($_POST['from_campaign_contact_id'])){
                        $contactListsData['from_campaign_contact_id'] = $_POST['from_campaign_contact_id'];
                    }

                    if(!empty($_POST['manual'])){
                        $contactListsData['source'] = 'form';
                    }
                    
                    //check if contact is locked by another agent before locking. if Locked. redirect
                    //lock contact to agent before creating campaign_contact record
                    $this->lock_contact($contactEmailExist->id, $contactModel);
                    
                    $campaignContactID = $contactModel->insert_contact_lists($contactListsData);
                    if ($campaignContactID && !empty($_POST['original_call_history_id'])){
                        $callHistoryId = $this->copy_call_history($contactEmailExist->id, $_POST['original_call_history_id'], null, $campaignContactID);
                        //update call_history_campaign_contact
                        
                    }
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'This contact was found in our database.');
                    $rdata['cid'] = $campaignContactID;
                    echo json_encode($rdata);
                    exit();
                }

             }else{
                //#2984 - Check the Uber databases first if contact is existing or not
                //if contact is not found on contacts table, search on EG.members table and load details from there. But get the time_zone from original contact
                $checkEgMembers = $contactModel->get_eg_members($_POST['email']);
                if(!empty($checkEgMembers)){
                    $checkEgMembers['country'] = strtolower($checkEgMembers['country']);
                    $data['member_details'] = $checkEgMembers;
                    $data['is_eg_member'] = true;
                    $data['status'] = true;
                    echo json_encode($data);
                    exit();
                }
             }
                // Checking entered email exist on EG-contacts table or not.
                $check_contact_exist_id = $contactModel->eg_email_exists($_POST['email']);
                if(!empty($check_contact_exist_id)){
                    // Checking Contact Id exist on uber-contacts table or not.
                    $contactIdExist = $contactModel->contactId_exists($check_contact_exist_id);
                    if($contactIdExist){
                        $data['message'] = "Entered email's contact id already exist in Uberdialer's app.";
                    }
                    $data['status'] = true;
                    echo json_encode($data);
                    exit();
                }else{
                    $data['status'] = true;
                    echo json_encode($data);
                    exit();
                }
        } else {
            #3394 - [BUG] Error Message in Add as a Different Person Page keeps on displaying
            #removed because the form already has validation upon submission
            $data['status'] = true;
            echo json_encode($data);
            exit();
        }
    }

    //Checking: While user enter existing email id or not into "New Email" field, which you can see after check checkbox from contact detail page
    function check_email_member_exist()
    {
        $this->load->model('Members_model');
        $membersModel = new Members_model();
        $data['title_message'] = 'Oops! Something went wrong';
        $data['prev_email'] = $_POST['currentemail'];
        if (!empty($_POST['newemail'])) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('newemail', 'Email', 'required|trim|max_length[255]|valid_email');
            if ($this->form_validation->run() == FALSE) {
                $data['span_message'] = "Please enter valid email.";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            if($_POST['newemail']==$_POST['currentemail'])
            {
                $data['span_message'] = "Please enter new mail";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
            
            $check_emails_exist = $membersModel->check_emails_exist($_POST['newemail']);

            if (!empty($check_emails_exist)) {
                $data['message'] = "Entered Email already exist, load info for this member?";
                $data['status'] = true;
                $data['data'] = $check_emails_exist->email;
                echo json_encode($data);
                exit();
            } else {
		$data['title_message'] = "Email Has Changed";
		$data['message'] = "You are changing the email to " . $_POST['newemail'];
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
        }
    }

    // update member detail while user enter already existing email id in contact detail page
    function update_member_id_contact()
    {
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $this->load->model('Members_model');
        $membersModel = new Members_model();
        if (!empty($_POST) && !empty($_POST['email']) && !empty($_POST['contact_id'])) {
            $update_at = date('Y-m-d H:i:s', time());

            $contact_id = $_POST['contact_id'];
            $contactData['edit_lead_status'] = '0';
            //$contactData['member_id'] = $_POST['member_id'];
            $contactData['updated_at'] = $update_at;
            // Member entry in to temp table as member clone data
            $isTempMemberExist = $membersModel->member_exist_check($_POST['email']);

            if (empty($isTempMemberExist)) {
                $member_id = $membersModel->insert_eg_members_to_members_qa($_POST['email']);
                if(!empty($member_id)){
                    $this->_create_member_qa_history($membersModel, $member_id);
                    $contactData['member_id'] = $member_id;
                }
                
            } else {
                if ($isTempMemberExist->id > 0) {
                    $contactData['member_id'] = $isTempMemberExist->id;
                }
            }
            
            $response = $callsModel->updateContactCallDetail($contact_id, $contactData);
            if ($response) {
                $this->create_contact_history($membersModel, $contactData['member_id'], $_POST['prev_email'], $contactData['member_id']);
                $data['status'] = true;
                echo json_encode($data);
                exit();
            } else {
                $data['message'] = "Sorry, Oops! Something went wrong.";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
        } else {
            $data['message'] = "Sorry, Oops! Something went wrong.";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
    }

    // While lead status are Follow-up or Reject at that time QA can retract that contact
    function retractContact($lead_history_id)
    {
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $userType = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        if (!empty($lead_history_id)) {

            $leadData = $callsModel->getTmLeadHistoryData($lead_history_id);

            if (!empty($leadData)) {
                if ($userType != 'agent' && $loggedUserID == $leadData->qa && ($leadData->status == 'Reject' || $leadData->status == 'Follow-up')) {
                    $update_at = date('Y-m-d H:i:s', time());
                    $leadRetractData['id'] = $lead_history_id;
                    $leadRetractData['status'] = 'Pending';
                    $leadRetractData['updated_at'] = $update_at;
                    $leadRetractData['qa'] = '';
                    $leadRetractData['is_qa_in_progress'] = '';
                    $response = $callsModel->updateRetractLeadHistory((object)$leadRetractData);
                    $lead_status_array = array('lead_history_id' => $lead_history_id,'user_id' => $loggedUserID,'status'=> 'Pending',  'created_at' => $update_at);
                    $callsModel->insert_lead_status($lead_status_array);
                    if ($response) {
                        $get_contact_id = $callsModel->getCampaignContacts($leadData->campaign_contact_id);

                        $this->load->model('Contacts_model');
                        $contactsModel = new Contacts_model();
                        $contactsData = new stdClass();
                        $contactsData->edit_lead_status = '0';
                        $contactsData->locked_by = '';
                        $contactRetractUpdateArray = (array)$contactsData;

                        $contactsModel->update_contact($get_contact_id->contact_id, $contactRetractUpdateArray);
                        $this->session->set_flashdata('class', 'good');
                        $this->session->set_flashdata('msg', 'Retract contact successfully.');
                        redirect('/dialer/leads');
                    } else {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong.');
                        redirect('/dialer/leads');
                    }
                } else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, You are unauthorized user, allowed only the lead was Rejected or flagged for follow-up.');
                    redirect('/dialer/leads');
                }
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Lead ID not found, please make sure that the Lead ID is correct!');
                redirect('/dialer/leads');
            }
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'ID not found, please make sure that the ID is correct!');
            redirect('/dialer/leads');
        }
    }

    // While lead status are Follow-up or Reject at that time QA can retract that contact
    function revertToPending($lead_history_id)
    {
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $userType = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
        if (!empty($lead_history_id)) {

            $leadData = $callsModel->getTmLeadHistoryData($lead_history_id);

            $status_revert = array('Reject','Duplicate Lead');
            $only_admin_revert = array('Approve','Follow-up');
            if (!empty($leadData)) {
                if((in_array($leadData->status, $status_revert) && $this->session->userdata('user_type') != 'agent') || (in_array($leadData->status, $only_admin_revert) && $this->session->userdata('user_type') == 'admin')){
                    $update_at = date('Y-m-d H:i:s', time());
                    $leadRetractData['id'] = $lead_history_id;
                    $leadRetractData['status'] = 'Pending';
                    $leadRetractData['updated_at'] = $update_at;
                    $leadRetractData['qa'] = '';
                    $leadRetractData['is_qa_in_progress'] = '';
                    $response = $callsModel->updateRetractLeadHistory((object)$leadRetractData);
                    $lead_status_array = array('lead_history_id' => $lead_history_id,'user_id' => $loggedUserID,'status'=> 'Pending',  'created_at' => $update_at);
                    $callsModel->insert_lead_status($lead_status_array);
                    if ($response) {
                        $get_contact_id = $callsModel->getCampaignContacts($leadData->campaign_contact_id);

                        $this->load->model('Contacts_model');
                        $contactsModel = new Contacts_model();
                        $contactsData = new stdClass();
                        $contactsData->edit_lead_status = '0';
                        $contactsData->locked_by = '';
                        $contactRetractUpdateArray = (array)$contactsData;

                        $contactsModel->update_contact($get_contact_id->contact_id, $contactRetractUpdateArray);
                        $this->session->set_flashdata('class', 'good');
                        $this->session->set_flashdata('msg', 'Status reverted to Pending');
                        redirect('/dialer/leads');
                    } else {
                        $this->session->set_flashdata('class', 'bad');
                        $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong.');
                        redirect('/dialer/leads');
                    }
                } else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, You are unauthorized user revert lead.');
                    redirect('/dialer/leads');
                }
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Lead ID not found, please make sure that the Lead ID is correct!');
                redirect('/dialer/leads');
            }
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'ID not found, please make sure that the ID is correct!');
            redirect('/dialer/leads');
        }
    }

    function update_contact()
    {
        if (empty($_POST['member_id'])) {

            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, Member id not exist.!');
            redirect('/dialer/leads');
        }

        $this->load->model('Members_model');
        $membersModel = new Members_model();
        $normalization_rules = $membersModel->get_member_normalization_rules();
        //Set field counter
        $num_fields = 0;

        //Set member object properties
        $member = new Member();

        $num_fields = $this->setMemberObjectDataEg($_POST, $member, $num_fields);

        if (isset($member->job_title)) {
            $this->load->library('Normalize');
            $normalize = new Normalize();
            $member->job_level = $normalize->job_level($member->job_title, $normalization_rules);
            $member->silo = $normalize->silo($member->job_title, $normalization_rules);
            // $member->ml_title = Normalize::ml_title($member->silo,$member->job_level);
        }

        if ($num_fields > 0) {
            $member->id = $_POST['member_id'];
            $member->ip = $_SERVER["REMOTE_ADDR"];
            $member->phone_verified = 1;
            $member->updated_at = date('Y-m-d H:i:s', time());
            $member->updated_by = $this->session->userdata('uid');

            // Check passed member ID id exist in EG system or not
            $checkMemberExist = $membersModel->get_one($_POST['member_id']);
            if (empty($checkMemberExist)) {

                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Member id is not match in EG system.!');
                redirect('/dialer/leads');
            }else{
                if (isset($_POST['lead_status']) && $_POST['lead_status'] == 'Approve') {
                    $this->_update_member($membersModel, $_POST, $_POST['member_id']);
                }
            }
            $membersModel->update_member_from_tm_qa($member);

            $member_id = $member->id;
            // Edit contact detail when Save Contact by QA.
            $this->load->model('Contacts_model');
            $contactsModel = new Contacts_model();
            $func = 'setContactObjectData'.ucfirst($this->app);
            $contactsData = $this->$func();
            $contactsData->member_id = $member_id;
            $contactsData->edit_lead_status = '0';
            $contactsData->locked_by = '';
            $callStatusUpdateArray = (array)$contactsData;
            if(!empty($_POST['ext'])){
                $callStatusUpdateArray['ext'] = $_POST['ext'];
            }
            $contactsModel->update_contact($_POST['contact_id'], $callStatusUpdateArray);

            $this->_create_member_qa_history($membersModel, $_POST['member_id']);

            if ($member_id) {
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Call detail updated successfully!.');
                redirect('/dialer/leads');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong.');
                redirect('/dialer/leads');
            }
        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, Oops! Something went wrong.');
            redirect('/dialer/leads');
        }
    }

    // Display view all call history while you have more that 5 email history into contact detail page
    public function view_all_call_history($contact_id,$list_id)
    {
        if (empty($contact_id) && $contact_id <= 0) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Contact not found, please make sure that the Contact ID is correct!');
            redirect('/dialer/campaigns');
        }

        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $view_all_call_history = $callsModel->getCallHistoryList($contact_id);

        $data['view_all_call_history'] = $view_all_call_history;
        $data['meta_title'] = 'View Call History';
        if (!empty($view_all_call_history)) {
           $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/contacts/index/' . $view_all_call_history[0]->campaign_id . '/'.$list_id. '">Contacts</a> > ' . 'View Call History';
        } else {
            $data['crumbs'] = $this->crumbs;
        }
        $data['title'] = 'View Call History';
        $data['main'] = 'dialer/calls/viewcallhistory';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    // While user logout at that time all lock contact should be un-lock for specific logged user
    public function update_multiple_lock_contact($ajax=false)
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();
        $loggedUserId = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');
        if(!empty($this->input->post('campaignContactId'))){
            $campaignContactId = $this->input->post('campaignContactId');
            $getContactId = $contactsModel->getContactIdByCampaignContactId($campaignContactId);
            if(!empty($getContactId)){
                $contactId = $getContactId['contact_id'];
                $campaignId = $getContactId['campaign_id'];
                if(in_array($userType, array('agent','team_leader'))){
                    $data['edit_lead_status']= '0';
                    $data['locked_by'] = '';
                    $successUnlock =  $contactsModel->update_contact($contactId, $data);
                }else{
                    $checkPreviousCall = $contactsModel->checkContactCallIfDisposed($getContactId['contact_id']);
                    if(!empty($checkPreviousCall) && in_array($checkPreviousCall->userType, array('agent','team_leader')) && $checkPreviousCall->userStatus == 'Active'){
                        $data['error'] = "Unable to unlock. Last call to this contact was undisposed.";
                        echo json_encode($data);exit;
                    }else{
                        $data['edit_lead_status']= '0';
                        $data['locked_by'] = '';
                        $successUnlock =  $contactsModel->update_contact($contactId, $data);
                    }
                }
                //log unlocking
                $contactsModel->insertUnlockLog($loggedUserId, $contactId, $campaignId, "button");
            }
        }else{
            $unlock_contact['edit_lead_status'] = '0';
            $unlock_contact['locked_by'] = '';
            $successUnlock = $contactsModel->update_multiple_lock_contact($loggedUserId, $unlock_contact);
        }
            
        //check if previous call is disposed, if not redirect user to the contact form
        $hasPreviousCall = $this->checkPreviousCallUnDisposed($contactsModel, $loggedUserId);
        if($hasPreviousCall){
            if($ajax){
                $data['campaign_contact_id'] = $hasPreviousCall->campaign_contact_id;
                $data['list_id'] = $hasPreviousCall->list_id;
                echo json_encode($data);exit;
            }else{
                redirect('/dialer/calls/index/' . $hasPreviousCall->campaign_contact_id.'/'.$hasPreviousCall->list_id);
            }
        }else{
            
            if ($successUnlock) {
                $data['message'] = "you have successfully unlock previous contact.";
                $data['status'] = true;
                $data['data'] = 'reload';
                echo json_encode($data);
                exit();
            }
        }
        
    }
    
    function checkPreviousCallUnDisposed($contactsModel, $loggedUserId){
        $userType = $this->session->userdata('user_type');
        if(!in_array($userType, array('agent','team_leader'))){
            return false;
            exit();
        }
        //check if previous call is disposed, if not redirect user to the contact form
        $checkPreviousCall = $contactsModel->checkPreviousCallIfDisposed($loggedUserId);
        
        if(!empty($checkPreviousCall)){
            
            $this->load->model('Campaigns_model');
            $campaignModel = new Campaigns_model();
            //check if agent is assigned to campaign 
            $isCampaignAssign = $campaignModel->IsCampaignAssignToAgent($checkPreviousCall->campaign_id, $loggedUserId);
            
            if ($isCampaignAssign == 0 && ($userType == 'agent' || $userType == 'team_leader')) {
                return false;
            }
            
            $currentCampaignId = $this->session->userdata('AgentSessionCampaignID');
            if ($currentCampaignId != $checkPreviousCall->campaign_id && ($userType == 'agent' || $userType == 'team_leader')) {

                    $this->load->model('Campaigns_model');
                    $campaignsModel = new Campaigns_model();
                    //if user is not sign in for the current campaign then we need to deactive all other campaign and activate user in current campaign
                    $agentUpdateData = array();
                    $agentUpdateData['session_end'] = date('Y-m-d H:i:s', time());
                    $agentUpdateData['is_session_deactive'] = 1;

                    //update entries to deactivate of all the campaigns
                    $campaignsModel->update_agent_session_by_user($loggedUserId, $agentUpdateData);

                    $this->session->unset_userdata('AgentSessionID');
                    $this->session->unset_userdata('AgentSessionCampaignID');

                    //activate current campaign
                    $agentData = array();
                    $agentData['user_id'] = $loggedUserId;
                    $agentData['campaign_id'] = $checkPreviousCall->campaign_id;
                    $agentData['session_start'] = date('Y-m-d H:i:s', time());
                    $agentSessionId = $campaignsModel->insert_agent_session($agentData);

                    $this->session->set_userdata('AgentSessionID', $agentSessionId);
                    $this->session->set_userdata('AgentSessionCampaignID', $checkPreviousCall->campaign_id);
                    $this->session->unset_contactdata('ContactFilter');
                }
            //echo "<pre>",print_r($this->db->queries), "</pre>";exit;
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please dispose this call first before proceeding to another call/contact.');
            return $checkPreviousCall; 
        }else{
            return false;
        }
    }

    /**
     * @param $loggedUserID
     */
    // User can not access multiple contact at a same time while already open other contact
    public function check_multiple_contact_access($loggedUserID, $contact_id = 0, $campaign_id = 0, $list_id = NULL)
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();
        $is_ajax_request = $this->input->is_ajax_request();

        // checking same user can not access multiple contact at same time.
        $check_multiple_lock_contact = $contactsModel->check_multiple_lock_contact($loggedUserID, $contact_id);

        if ($check_multiple_lock_contact['lock_contact_count'] >= 1) {
            if ($is_ajax_request) {
                $data['message'] = "your call is already pending, Do you want to access this contact then will unlock previous all calls ?";
                $data['status'] = false;
                $data['data'] = 'locked';
                echo json_encode($data);
                exit();
            } else {
                if(!empty($check_multiple_lock_contact) && $check_multiple_lock_contact['contact_id'] > 0){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Previous lead is still active and locked. please unlock previous contact, <a class="btn btn-success" style="text-decoration: underline;" href="/dialer/calls/unlock_previous_contact/'.$campaign_id.'/'.$list_id.'/'.$check_multiple_lock_contact['contact_id'].'">'.$check_multiple_lock_contact['contact_id'].'</a>');
                }
                redirect('/dialer/contacts/index/' .$campaign_id.'/'.$list_id);
        }
    }
    }

    /*// Not usage now
    public function on_before_unload_update_contact()
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();

        $unlock_contact_data['edit_lead_status'] = '0';
        $unlock_contact_data['locked_by'] = '';
        if (!empty($_POST['contact_id'])) {
            $response = $contactsModel->update_contact($_POST['contact_id'], $unlock_contact_data);
            if ($response) {
                $data['message'] = "This contact is successfully unlock.";
                $data['status'] = true;
                echo json_encode($data);
                exit();
            } else {
                $data['message'] = "Sorry, Oops! Something went wrong.";
                $data['status'] = false;
                echo json_encode($data);
                exit();
            }
        } else {
            $data['message'] = "Contact not found, please make sure that the Contact ID is correct!";
            $data['status'] = false;
            echo json_encode($data);
            exit();
        }
    }*/

    // get Active calls (Active Conference call) which was display in QA module
    public function active_calls()
    {
        $user_type = $this->session->userdata('user_type');

        if ($user_type == 'agent') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'you are unauthorized person for this page!');
            redirect('/dialer/campaigns');
        }
        // Load calls model
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();

        $activeCalls = $callsModel->active_calls();

        $data['activeCalls'] = $activeCalls;
        $data['crumbs'] = $this->crumbs . ' > Calls in Progress';
        $data['meta_title'] = 'Calls in Progress';
        $data['title'] = 'Plivo Active calls';
        $data['main'] = 'dialer/calls/plivo_active_calls';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    /**
     * Load the UI and tap the conference call.
     *
     * @param $communication_id
     */
    // get Tap calls page after access conference call which was display in QA module
    public function tap($communication_id)
    {
        // system sanity check
        $qa_endpoint_username = $this->session->userdata('plivo_endpoint_username');//$this->config->item('QA_ENDPOINT_USERNAME');
        $qa_endpoint_password = $this->session->userdata('plivo_endpoint_password'); // $this->config->item('QA_ENDPOINT_PASSWORD');
        if (empty($qa_endpoint_username) OR empty($qa_endpoint_password)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Error: No QA Endpoints defined in environment file!');
            redirect('/dialer/dashboards/realtimemonitoring');
           // return 'Error: No QA Endpoints defined in environment file';
        }

        // Get the QA endpoint from dev
        $endpoint = [
            'username' => $qa_endpoint_username,
            'password' => $qa_endpoint_password,
            'endpoint_uuid' => null, // deliberate
        ];
        // some crazy casting
        $endpoint = (object)$endpoint;

        // Load calls model
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $communication = $callsModel->get_communication_by_id($communication_id);

        // check
        if (empty($communication)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'This call does not exit.');
            redirect('/dialer/dashboards/realtimemonitoring');
            //return 'This call does not exit.';
        }
        if (!empty($communication->hangup_cause)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'This call is not live anymore.');
            redirect('/dialer/dashboards/realtimemonitoring');
            //return 'This call is not live anymore.';
        }

        $data['communication'] = $communication;
        $data['endpoint'] = $endpoint;
        $this->load->view('dialer/calls/tap', $data);
    }
    
    public function call_history($page_num = 1, $sortField = 'Date', $order = 'asc') {
        // Load users model
        $this->load->model('Users_model');
        $usersModel = new Users_model();
        $this->load->helper('campaignjobdetail');
        $this->load->library('pagination');
        $this->load->model('Leads_model');
        $leads_model = new Leads_model();

        $user_type = $this->session->userdata('user_type');

        if ($user_type == 'agent') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'you are unauthorized person for this page!');
            redirect('/dialer/campaigns');
        }
        
        // Load calls model
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $data['call_dispositions'] = $callsModel->getCallDispositionsByModule("tm","","id,calldisposition_name as name");
        $agent_status = array();
        foreach ($data['call_dispositions'] as $calldisposition) {
            $agent_status[$calldisposition->id] = $calldisposition->name;
        }

        if (!empty(array_count_values($_POST)) || !empty($this->input->get()) || !empty($this->input->post()) ) {
            $searchBy = $this->input->post();

            if (empty($searchBy) && count($this->input->get()) > 0) {
                $searchBy = $this->input->get();
                $_POST['status'] = $this->input->get('status');
                $_POST['start_date'] = $this->input->get('start_date');
                $_POST['end_date'] = $this->input->get('end_date');
                $_POST['telemarketer'] = $this->input->get('telemarketer');
                $_POST['contact_name'] = $this->input->get('contact_name');
                $_POST['company'] = $this->input->get('company');
                $_POST['email'] = $this->input->get('email');
                $_POST['campaign'] = $this->input->get('campaign');
                $_POST['qa'] = $this->input->get('qa');
                $_POST['calldisposition_name'] = $this->input->get('calldisposition_name');
            }
            
            $isReport = $this->input->post('file_type') != "" ? true : false;
            $tot_records = $callsModel->call_recording_list_count2($searchBy);

            if ($isReport && $this->input->post('file_type') == 'csv') {
                ini_set('memory_limit', '1024M');
                if ($tot_records < 230000) {
                    $call_recording_list = $callsModel->call_recording_list2($searchBy, "", "", $sortField, $order, $isReport);
                    foreach( $call_recording_list as $items ){
                        $items->duration = $this->getDuration($items->recording_url,$items->call_start_datetime,$items->call_end_datetime);
                        $items->call_disposition_id = $agent_status[$items->call_disposition_id];
                        if ($isReport) {
                            unset($items->call_start_datetime);
                            unset($items->call_end_datetime); 
                        }
                    }
                } else {
                    if ($tot_records > 0) {
                        $filename = "/tmp/call_history_report_" . date("Y_m_d_H:i:s") . ".csv";
                        $fp = fopen($filename, 'w');
                        //set headers
                        $arrCsv = array();
                        $arrCsv[] = "Campaign ID";
                        $arrCsv[] = "Campaign Name";
                        $arrCsv[] = "Type";
                        $arrCsv[] = "Full Name";
                        $arrCsv[] = "Company";
                        $arrCsv[] = "Email";
                        $arrCsv[] = "Dialed No.";
                        $arrCsv[] = "Date & Time";
                        $arrCsv[] = "Agent";
                        $arrCsv[] = "Agent Status";
                        $arrCsv[] = "QA";
                        $arrCsv[] = "QA Status";
                        $arrCsv[] = "Notes";
                        $arrCsv[] = "Rec. Link";
                        $arrCsv[] = "Rec. Duration";
                        fputcsv($fp, $arrCsv);
                        $limit_per_loop = 200000;
                        $offset = 0;
                        $no_of_loop = ceil($tot_records / 200000);
                        for ($i = 1;$i <= $no_of_loop; $i++) {
                            $reports = $callsModel->call_recording_list2($searchBy, $limit_per_loop, $offset, $sortField, $order, $isReport);

                            if (count($reports) > 0) {
                                foreach ($reports as $report) {
                                    $report->duration = $this->getDuration($report->recording_url,$report->call_start_datetime,$report->call_end_datetime);
                                    $arrCsv = array();
                                    $arrCsv[] = $report->eg_campaign_id;
                                    $arrCsv[] = $report->campaign_name;
                                    $arrCsv[] = $report->campaign_type;
                                    $arrCsv[] = $report->full_name;
                                    $arrCsv[] = $report->company;
                                    $arrCsv[] = $report->contact_email;
                                    $arrCsv[] = $report->phone;
                                    $arrCsv[] = $report->call_created_at;
                                    $arrCsv[] = $report->agent_name;
                                    $arrCsv[] = $agent_status[$report->call_disposition_id];
                                    $arrCsv[] = $report->qa_name;
                                    $arrCsv[] = $report->Status;
                                    $arrCsv[] = $report->notes;
                                    $arrCsv[] = $report->recording_url;
                                    $arrCsv[] = $report->duration;
                                    fputcsv($fp, $arrCsv);
                                }
                            }
                            if ($i == $no_of_loop) {
                                $offset = $tot_records - $limit_per_loop;
                            } else {
                                $offset += $limit_per_loop;
                            }
                        }
                        fclose($fp);
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename='. $filename);
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($filename));
                        flush(); // Flush system output buffer
                        readfile($filename);
                        unlink($filename);

                        $this->load->model("Audittrail_model");
                        $audittrail_model = new Audittrail_model();
                        $audittrail_model->log("download", "tm", "Call History List", $searchBy);
                        exit;
                    }
                }
            } else if ($isReport && $this->input->post('file_type') == 'excel') { 
                ini_set('memory_limit', '1024M');
                $call_recording_list = $callsModel->call_recording_list2($searchBy, "", "", $sortField, $order, $isReport);
                foreach( $call_recording_list as $items ){
                    $items->duration = $this->getDuration($items->recording_url,$items->call_start_datetime,$items->call_end_datetime);
                    $items->call_disposition_id = $agent_status[$items->call_disposition_id];
                    if ($isReport) {
                        unset($items->call_start_datetime);
                        unset($items->call_end_datetime); 
                    }
                }
            } else {
                $recs_per_page = 100;
                $page_number = (int)$this->input->get('per_page', TRUE);
                if (empty($page_number)) $page_number = 1;
                //$offset = ($page_number-1)*$recs_per_page;
                $offset = (int)$this->input->get('per_page', TRUE);
                
                $call_recording_list = $callsModel->call_recording_list2($searchBy, $recs_per_page, $offset, $sortField, $order, $isReport);
                
                foreach( $call_recording_list as $items ){
                    $items->duration = $this->getDuration($items->recording_url,$items->call_start_datetime,$items->call_end_datetime);
                    $items->call_disposition = $agent_status[$items->call_disposition_id];
                }
            }
            
            if ($isReport && !empty($call_recording_list)) {
                $this->load->model("Audittrail_model");
                $audittrail_model = new Audittrail_model();
                $audittrail_model->log("download", "tm", "Call History List", $searchBy);
                //$main_array = $callsModel->call_recording_list($searchBy, $recs_per_page, $offset, $sortField, $order, '1');
                $this->export_data($this->input->post('file_type'), "Call History", (array)$call_recording_list);
            }

            $data['num_recs'] = $tot_records;
            $data['call_recording_list'] = $call_recording_list;
            //Load pagination and configure
            $this->load->library('pagination');
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
            $this->pagination->cur_page = $offset;
            $this->pagination->initialize($config);
            $data['page_links'] = $this->pagination->create_links();
            $data['num_pages'] = ceil($tot_records / $recs_per_page);
            $data['current_page'] = ($offset / $recs_per_page) + 1;
            $data['offset'] = $offset;

        }
        
        $data['leadStatus'] = getLeadStatusValues();
        $config['base_url'] = '/dialer/calls/call_history/';
        $data['base_url'] = $config['base_url'];
        $data['telemarketerList'] = $usersModel->getAgents();
        $data['qaList'] = $leads_model->getQaList();
        $data['allCampaignList'] = $leads_model->getAllCampaignList();
        $data['user_id'] = $this->session->userdata('uid');
        $data['crumbs'] = $this->crumbs . ' > QA > Call History List';
        $data['meta_title'] = 'Call History';
        $data['title'] = 'Call History';
        $data['main'] = 'dialer/calls/call_recording_search';
        $this->load->vars($data);
        $this->load->view('layout');

    }
    
    function getDuration($recording_url, $call_start_datetime, $call_end_datetime) {
        $duration = '';
        if ($recording_url != '') {
            $start = new \DateTime($call_start_datetime);
            $end = new \DateTime($call_end_datetime);

            $interval = $end->diff($start);

            $duration = sprintf(
                                '%02d:%02d',
                                $interval->i,
                                $interval->s
                             );
        } 
        
        return $duration;
    }
    
    // Get all call history page - QA module
    public function call_history2($page_num = 1, $sortField = 'Date', $order = 'asc')
    {
        $user_type = $this->session->userdata('user_type');

        if ($user_type == 'agent') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'you are unauthorized person for this page!');
            redirect('/dialer/campaigns');
        }

        $this->load->helper('campaignjobdetail');
        $this->load->library('pagination');

        // Load calls model
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();

        $this->load->model('Leads_model');
        $leads_model = new Leads_model();

        $searchBy = $this->input->post();

        if (empty($searchBy) && count($this->input->get()) > 0) {
            $searchBy = $this->input->get();
            $_POST['status'] = $this->input->get('status');
            $_POST['start_date'] = $this->input->get('start_date');
            $_POST['end_date'] = $this->input->get('end_date');
            $_POST['telemarketer'] = $this->input->get('telemarketer');
            $_POST['contact_name'] = $this->input->get('contact_name');
            $_POST['company'] = $this->input->get('company');
            $_POST['email'] = $this->input->get('email');
            $_POST['campaign'] = $this->input->get('campaign');
            $_POST['qa'] = $this->input->get('qa');
        }
        $recs_per_page = 100;
        // $offset = 0;

        $page_number = (int)$this->input->get('per_page', TRUE);
        $isReport = $this->input->post('file_type') != "" ? true : false;

        $tot_records = $callsModel->call_recording_list_count2($searchBy);
        if ($isReport) {
	    ini_set('memory_limit', '1024M');
            $call_recording_list = $callsModel->call_recording_list2($searchBy, "", "", $sortField, $order, $isReport);
//            $limit_per_loop = 40000;
//            $offset = 0;
//            $call_recording_list = array();
//            if ($tot_records > 0) {
//                $no_of_loop = ceil($tot_records / 40000);
//                for ($i = 1;$i <= $no_of_loop; $i++) {
//                    $reports = $callsModel->call_recording_list2($searchBy, $limit_per_loop, $offset, $sortField, $order, $isReport);
//                    
//                    if (count($reports) > 0) {
//                        $call_recording_list = array_merge($call_recording_list, $reports);
//                    }
//                    if ($i == $no_of_loop) {
//                        $offset = $tot_records - $limit_per_loop;
//                    } else {
//                        $offset += $limit_per_loop;
//                    }
//                }
//            }
        } else {
            if (empty($page_number)) $page_number = 1;
            //$offset = ($page_number-1)*$recs_per_page;
            $offset = (int)$this->input->get('per_page', TRUE);
            
            $call_recording_list = $callsModel->call_recording_list2($searchBy, $recs_per_page, $offset, $sortField, $order, $isReport);
        }
       
        foreach( $call_recording_list as $items ){
            if ($items->recording_url != '') {
                $start = new \DateTime($items->call_start_datetime);
                $end = new \DateTime($items->call_end_datetime);

                $interval = $end->diff($start);

                $items->duration = sprintf(
                                    '%02d:%02d',
                                    $interval->i,
                                    $interval->s
                                 );
            } else {
                $items->duration = '';
            }
            
            if ($isReport) {
                unset($items->call_start_datetime);
                unset($items->call_end_datetime); 
            }
        }
        
        if($isReport && !empty($call_recording_list)) {
            $this->load->model("Audittrail_model");
            $audittrail_model = new Audittrail_model();
            $audittrail_model->log("download", "tm", "Call History List", $searchBy);
            //$main_array = $callsModel->call_recording_list($searchBy, $recs_per_page, $offset, $sortField, $order, '1');
            $this->export_data($this->input->post('file_type'), "Call History", (array)$call_recording_list);
        }
        
        $data['call_recording_list'] = $call_recording_list;
        $data['num_recs'] = $tot_records;
        $data['leadStatus'] = getLeadStatusValues();
        //Load pagination and configure
        $this->load->library('pagination');

        $config['base_url'] = '/dialer/calls/call_history/';
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
        $this->pagination->cur_page = $offset;
        $this->pagination->initialize($config);
        $data['page_links'] = $this->pagination->create_links();
        $data['num_pages'] = ceil($tot_records / $recs_per_page);
        $data['current_page'] = ($offset / $recs_per_page) + 1;
        $data['offset'] = $offset;
        $data['telemarketerList'] = $leads_model->get_telemarketer();
        $data['qaList'] = $leads_model->getQaList();
        $data['allCampaignList'] = $leads_model->getAllCampaignList();
        $data['user_id'] = $this->session->userdata('uid');

        $data['crumbs'] = $this->crumbs . ' > QA > Call History List';
        $data['meta_title'] = 'Call History';
        $data['title'] = 'Call History';
        $data['call_dispositions'] = $callsModel->getCallDispositionsByModule("tm","","id,calldisposition_name as name");
        $data['main'] = 'dialer/calls/call_recording_search';

        $this->load->vars($data);
        $this->load->view('layout');

    }

    // Listing of email history which was sent via email resource functionality on contact detail page
    public function email_history($campaign_contact_id,$list_id)
    {
        if (empty($campaign_contact_id) && $campaign_contact_id <= 0) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Contact not found, please make sure that the Contact ID is correct!');
            redirect('/dialer/campaigns');
        }

        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $view_all_call_history = $callsModel->getEmailHistoryList($campaign_contact_id);
        $data['contact_email_history_list'] = $view_all_call_history;
        $data['meta_title'] = 'View Email History';
        $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/contacts/index/' . $view_all_call_history[0]->campaign_id.'/'.$list_id . '">Contacts</a> > ' . 'View Email History';
        $data['title'] = 'View Email History';
        $data['main'] = 'dialer/calls/viewemailhistory';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    // Onclick "Read more" Notes you can see full notes text
    public function get_more_notes_by_call()
    {
        if (!empty($_POST)) {

            if (isset($_POST['campaign_contact_ids']))
                $campaign_contact_ids = $_POST['campaign_contact_ids'];

            if (!empty($campaign_contact_ids)) {
                $this->load->model('Calls_model');
                $calls_model = new Calls_model();
                $get_all_notes = $calls_model->get_more_notes_by_call($campaign_contact_ids);
                $data['status'] = true;
                $data['data'] = $get_all_notes;
            } else {
                $data['message'] = "Sorry, an error has occurred.";
                $data['status'] = false;
            }
            echo json_encode($data);
            exit();
        }
    }

    // Get all notes By Member ID
    public function getNotesByMemberID()
    {
        if (!empty($_POST['member_id'])) {

            $this->load->model('Resourceview_model');
            $resourceViewModel = new Resourceview_model;

            $notes = $resourceViewModel->get_notes($_POST['member_id']);
            echo json_encode($notes);
        }
    }

    /**
     * @param $user_type
     * @param $campaignData
     */
    // Check you can access this campaign detail or not except "Admin" & "QA" User type
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

    public function unlock_previous_contact($campaignId, $listId, $contactId)
    {
        $this->load->model('Contacts_model');
        $contactsModel = new Contacts_model();
        $loggedUserID = $this->session->userdata('uid');
        //Un-lock previous contact
        if(!empty($campaignId) && !empty($contactId)){
            $unlcokPreviousContact = $contactsModel->unloackContact($contactId, $loggedUserID);
            //log unlocking
            $contactsModel->insertUnlockLog($loggedUserID, $contactId, $campaignId, "link");
            //check if previous call is disposed, if not redirect user to the contact form
            $hasPreviousCall = $this->checkPreviousCallUnDisposed($contactsModel, $loggedUserID);
            if($hasPreviousCall){
                redirect('/dialer/calls/index/' . $hasPreviousCall->campaign_contact_id.'/'.$hasPreviousCall->listId);
            }else{
                if(!$unlcokPreviousContact){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, Something was wrong when unlock Previous contact.');
                }else{
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'Previous contact is successfully unlock.');
                }
                redirect('/dialer/contacts/index/' . $campaignId.'/'.$listId);
            }
        }
    }
        /* Dev_NV Region End */

    // Get Inbound calls - Ajax call - QA module
    public function get_inbound_calls()
    {
        // Load calls model
        $this->load->model('Calls_model');
        $callsModel = new Calls_model();
        $inbound_calls = $callsModel->get_inbound_calls();
        echo json_encode($inbound_calls);
    }
    /* Dev_DP region End */

    /* dev_KR region Start */
    // Checking dialled call time - 15minutes
    public function check_dial_count_flag($startDate, $endDate)
    {

        $date_a = new DateTime($startDate);
        $date_b = new DateTime($endDate);
        $interval = date_diff($date_a, $date_b);

        $total = $interval->format('%h:%i:%s');

        if (strtotime($total) > strtotime('00:00:15')) {
            return 1;
        } else {
            return 0;
        }
    }
    /* dev_KR region End */

    /* dev_GC region Start */
    //ajax method to get med schools
    public function ajax_get_med_schools() {
        $this->load->model('Members_model');
        $membersModel = new Members_model();
        $med_school_location = $this->input->post('med_school_location', TRUE);
        $location_op = $this->input->post('location_op', TRUE);

        if (!empty($med_school_location)) {
            $med_schools = $membersModel->get_med_schools_by_location($med_school_location, $location_op);
            $arr = array();
            foreach ($med_schools as $school) {
                array_push($arr, array('val' => $school->school, 'text' => $school->school));
            }
            echo json_encode($arr);
        }
    }
    /* dev_GC region End */

    function export_data($file_type, $report_name, $data) {
        switch (trim($file_type)) {
            case 'excel':
                $this->_export_excel($report_name."_Report", $data);
                break;
            case 'csv':
                $this->_export_csv($report_name."_Report", $data);
                break;
            default:
                echo "file type not specified";
                break;
        }
        exit;
    }

    function _export_excel($report_title, $rows) {
        //ini_set('memory_limit', '1024M');
        $report_header = array('Campaign ID','Campaign Name','Type','Full Name','Company','Email','Dialed No.','Date & Time','Agent','Agent Status','QA','QA Status','Notes', 'Rec. Link', 'Rec. Duration');
        $i = 0;
//        foreach ($rows as $row) {
//            foreach($row as $key => $value) {
//                switch ($key) {
//                    case 'eg_campaign_id':
//                        $report_values[$i][0] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'campaign_name':
//                        $report_values[$i][1] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'campaign_type':
//                        $report_values[$i][2] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'full_name':
//                        $report_values[$i][3] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'company':
//                        $report_values[$i][4] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'contact_email':
//                        $report_values[$i][5] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'phone':
//                        $report_values[$i][6] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'call_created_at':
//                        $report_values[$i][7] = $value != '00/00/0000' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : NULL;
//                        break;
//                    case 'agent_name':
//                        $report_values[$i][8] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'qa_name':
//                        $report_values[$i][9] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'Status':
//                        $report_values[$i][10] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'notes':
//                        $report_values[$i][11] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    case 'recording_url':
//                        $report_values[$i][12] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    case 'duration':
//                        $report_values[$i][13] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    default:
//                        break;
//                }
//            }
//            ksort($report_values[$i]);
//            $i++;
//        }
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".xls"; // The file name you want any resulting file to be called.
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel($filename);
        $excel_obj->setHeadersAndValues($report_header, $rows);
        //now generate the excel file with the data and headers set
        $excel_obj->GenerateExcelFile();
        exit;
    }

    function _export_csv($report_title, $rows) {
        //ini_set('memory_limit', '1024M');
        $report_header = array('Campaign ID','Campaign Name','Type','Full Name','Company','Email','Dialed No.','Date & Time','Agent','Agent Status','QA','QA Status','Notes', 'Rec. Link', 'Rec. Duration');
        $i = 0;
//        foreach ($rows as $row) {
//            foreach($row as $key => $value) {
//                switch ($key) {
//                    case 'eg_campaign_id':
//                        $report_values[$i][0] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'campaign_name':
//                        $report_values[$i][1] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'campaign_type':
//                        $report_values[$i][2] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'full_name':
//                        $report_values[$i][3] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'company':
//                        $report_values[$i][4] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'contact_email':
//                        $report_values[$i][5] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'phone':
//                        $report_values[$i][6] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'call_created_at':
//                        $report_values[$i][7] = $value != '00/00/0000' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : NULL;
//                        break;
//                    case 'agent_name':
//                        $report_values[$i][8] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'qa_name':
//                        $report_values[$i][9] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'Status':
//                        $report_values[$i][10] = (!empty($value) ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ');
//                        break;
//                    case 'notes':
//                        $report_values[$i][11] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    case 'recording_url':
//                        $report_values[$i][12] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    case 'duration':
//                        $report_values[$i][13] = str_replace('<br>', "\r\n", iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value));
//                        break;
//                    default:
//                        break;
//                }
//            }
//            ksort($report_values[$i]);
//            $i++;
//        }
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".csv"; // The file name you want any resulting file to be called.
        //create the instance of the exportexcel format
        $csv_obj = new ExportCSV($filename);
        $csv_obj->setHeadersAndValues($report_header, $rows);
        //now generate the excel file with the data and headers set
        $csv_obj->GenerateCSVFile();
        exit;
    }
    
    
    function data_mpg($egCampaign, $contactCallDetail, $member_id, $callsModel){
        $data = array();
        $this->load->library('regxmlmpg');
        $regObject = new Regxmlmpg();
        $regObject->setInfo($egCampaign);

        $question_options = null;
        $questions = $callsModel->getEGCampaignQuestion('WHERE id in (173)');
        if(!empty($questions)){
            $question_options = $questions[0]->options;
        }
        $cq_responses = null;
        if (isset($member_id)) {
            $this->load->model('Members_model');
            $membersModel = new Members_model();
            // get question responses
            $cq_responses = $membersModel->get_question_responses_from_tm_qa_by_member($member_id);
            if(!empty($cq_responses)){
                $cq_responses = $this->arrangeCQResponses($cq_responses);
                if(!empty($cq_responses['qid_0'])){
                    $contactCallDetail->qid_0 = $cq_responses['qid_0'];
                }
            }
        }
        $qid_46 = !empty($_POST['qid_46']) ? $_POST['qid_46'] : !empty($cq_responses['qid_46']) ? $cq_responses['qid_46'] : '';
        $med_location = null;
        $med_country = null;
        if (!empty($qid_46)) {
            $location = $membersModel->get_location_by_school($qid_46);
            $med_location = $location->state;
            $med_country = $location->country;
        }
        $data['regXml'] = $regObject->regXml;
        $data['regRules'] = $regObject->regRules;
        $data['jsRegRules'] = $regObject->jsRegRules;
        $data['question_options'] = $question_options;
        $data['cq_responses'] = $cq_responses;
        $data['med_location'] = $med_location;
        $data['med_country'] = $med_country;
        return $data;
    }
    
    function get_eg_campaign2($campaign_id,$callsModel,$list_id){
        if (!empty($campaign_id) && $campaign_id > 0) {
            $egCampaignID = $campaign_id;
        } else {
            $egCampaignID = 0;
        }
        $egCampaign = $callsModel->getEGCampaignDataByID_calls($egCampaignID);
        if (empty($egCampaign)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Eg Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/contacts/index/' . $campaign_id.'/'.$list_id);
        }else{
            $egCampaign->survey_questions = array();
            $egCampaign->custom_questions = array();
            $egCampaign->intentQuestions = array();
            if ($egCampaign->type == 'pureresearch' || $egCampaign->type == 'puremql' || $egCampaign->type == 'smartleads') {
                // Get Survey questions
                $egCampaign->survey_questions = $callsModel->getEGSurveyQuestion('WHERE id in (' . $egCampaign->questions . ')');
            } else {
                // Get Custom questions
                if (!empty($egCampaign->questions)) {
                    $egCampaign->custom_questions = $callsModel->getEGCampaignQuestion('WHERE id in (' . $egCampaign->questions . ')');
                }
                // Get Intent questions
                if (!empty($egCampaign->intent_questions)) {
                    $egCampaign->intentQuestions = $callsModel->getEGCampaignQuestion('WHERE id in (' . $egCampaign->intent_questions . ')');
                }
            }
            return $egCampaign;
        }
    }
    
    function check_agent_session2($campaign_id, $loggedUserID, $campaignModel){
        $agentSessionCampaignID = $this->session->userdata('AgentSessionCampaignID');
        if (empty($agentSessionCampaignID)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
            redirect('/dialer/campaigns');
        } else {
            $this->check_campaign_active_session($agentSessionCampaignID, $campaign_id);
        }

        // this campaign assign to agent or not
        $isCampaignAssign = $campaignModel->IsCampaignAssignToAgent($campaign_id, $loggedUserID);
        if ($isCampaignAssign == 0) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You have to not assign this campaign and contacts, please contact to administrator!');
            redirect('/dialer/campaigns');
        }
    }
    // Start HP UAD-64 : Contact Details
    // Function for fetch details of contacts
    public function autoCalls($campId) 
    {
        $data['meta_title'] = 'Contact detail';
        $data['crumbs'] = $this->crumbs;
        $data['title'] = 'View Contact';
        $data['main'] = 'dialer/calls/view_auto_call';
        $this->load->vars($data);
        $this->load->view('layout');
    }
    // End HP UAD-64 : Contact Details
}

?>
