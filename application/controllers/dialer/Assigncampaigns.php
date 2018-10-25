<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Assigncampaigns extends MY_Controller
{
    /* Dev_KR Region Start */
    
    // Set bread crumbs 
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';
    
    public function __construct()
    {
        parent::__construct();
        //Check User logged-in or Not
       if (!$this->session->userdata('uid')) {
          
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        // load form validation library
        $this->load->library(array('form_validation'));
        $this->load->helper(array('url', 'html', 'form','common','utils'));
        $this->load->model('AssignCampaigns_model');
        $this->load->model('Users_model');
        
        //checking:  You are authorized person or not for access this page.
        $isAuthorized = IsTLManagerUpperManagementAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
            redirect($this->app_module_name.'/campaigns');
        }
    }

    public function index()
    {
        $userType = $this->session->userdata('user_type');
        if ($userType == 'team_leader') {
        $logedInUser = $this->session->userdata('uid');
        } else {
            $logedInUser = 0;
        }       
       
        $this->form_validation->set_rules('campaign_name', 'Campaign Name', 'required|trim');
        $this->form_validation->set_rules('team_leader', 'Team Leader Name', 'required|trim');
        
 
        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed            
            
            // Get Assign Campaign(s) of the LoggedIn User
            $campaignData = $this->AssignCampaigns_model->get_campaign_by_TL($logedInUser); 

            if (empty($campaignData)) {
                $this->session->set_flashdata('class', 'bad');
                if ($logedInUser) {
                    $this->session->set_flashdata('msg', 'You have not assigned any campaign. please contact to administrator!');
                } else {
                    $this->session->set_flashdata('msg', 'Campaign(s) not found!');
                }
                redirect($this->app_module_name.'/campaigns');
            }
            $upperManagement =  $this->config->item('upper_management_types');
            // Get TL List for Drop down
            $logged_tm_office = '';
            if(!in_array($userType, $upperManagement))
                $logged_tm_office = $this->session->userdata('telemarketing_offices');

            $data['teamleader'] = $this->Users_model->get_team_leads($logged_tm_office,$this->selected_module_name);
            $data['upperManagement'] =  $upperManagement;
            $data['campaign'] = $campaignData;
            $data['logedInUser'] = $logedInUser;
            $data['meta_title'] = 'Assign Campaign';
            $data['title'] = 'Assign Campaign';
            $data['main'] = $this->app_module_name.'/assigncampaigns/campaignassign';
            $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns/">Campaigns</a> > Assign Campaign';

            $this->load->vars($data);
            $this->load->view('layout');
        } else {
           // selected & old agent_id array
            $agentIDFilterValues = explode(',', $_POST['newselectedagents']);
            $oldarray = explode(',', $_POST['oldselectedagents']);         
            
            $newListCount = count(array_filter($agentIDFilterValues));
           
            // difference of old array & new array
            $agentDifference = array_diff($oldarray, $agentIDFilterValues);
            $agentDifference = array_filter($agentDifference);
            
            // delete old agents from campaign
            if ((empty($newListCount) && (count($agentDifference) > 0)) || ((count($agentDifference) > 0) && !empty($newListCount))) {
                $customWhere['campaignid'] = $_POST['campaign_name'];
                $customWhere['teamleader_id'] = $_POST['team_leader'];
                $customWhere['agent_id'] = $agentDifference;
                $result = $this->AssignCampaigns_model->delete_assign_agents($customWhere);
            }
            
            // Insert new agents in campaign
            if ($newListCount > 0) {
                $string = '';
                foreach ($agentIDFilterValues as $value) {
                     $string .= '(';
                     $string .= "'" . $_POST['campaign_name'] . "',";
                     $string .= "'" . $_POST['team_leader'] . "',";
                     $string .= "'" . $value . "',";                     
                     $string .= "'" . date('Y-m-d H:i:s', time()) . "',";
                     $string .= "'" . $this->session->userdata('uid') . "',";
                     $string .= "'" . date('Y-m-d H:i:s', time()) . "'),";
                }
                $insert_string = trim($string, ",");
               
                $result = $this->AssignCampaigns_model->insert_assign_campaign($insert_string);
                if ($result) {
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'Campaign assigned successfully!');
                } else {
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Sorry, an error has occurred.');
                }
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Please Assign Campaign at least one Agent!');
            } 
            redirect($this->app_module_name.'/assigncampaigns/');
        }  
    }
    
    // get campaign based on selected agent from combo box
    public function getCampaignAgent($campaignId = null)
    {
        $tl = isset($_POST['tl']) ? $_POST['tl'] : "";
        $searchByEgCampaignId = isset($_POST['searchByEgCampaignId']) ? (int) $_POST['searchByEgCampaignId'] : 0;
       
        if (!empty($campaignId) && !empty($tl)) {
            // get list of agent as per selected campaign id
            $response['campaignList'] = $this->AssignCampaigns_model->get_agent_by_campaign($campaignId, $tl, $searchByEgCampaignId);
            if (!empty($response['campaignList'])) {
                $campaignId = $response['campaignList'][0]->campaign_id;
                $response['agentAssign'] = $this->AssignCampaigns_model->get_assign_campaign_data($campaignId, $tl);
                $data['campaign_id'] = $campaignId;
                $data['eg_campaign_id'] = $response['campaignList'][0]->eg_campaign_id;
                $data['data'] = $response;
                $data['status'] = true; 
            } else {
              $data['message'] = 'Campaign not found for this Team Leader!';
              $data['status'] = false; 
            }            
        } else {
            $data['message'] = 'Campaign not found, please make sure that the campaign ID is correct!';
            $data['status'] = false;
        }
        echo json_encode($data);
        exit;
    }
    
    // GET TL Assign Campaign(s)
    public function getTLCampaign($teamID = 0)
    {
        if (!empty($teamID)) {
            // get list of campaign based on selected TL
            $campaignData = $this->AssignCampaigns_model->get_campaign_by_TL($teamID); 
            if (empty($campaignData)) {
                $data['status'] = false; 
                $data['message'] = 'This Team leader has not been assign any campaign!';
            } else {
                $data['status'] = true; 
                $data['data'] = $campaignData;
            }
        } else {
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
        }     
        echo json_encode($data);
        exit;
            
    }
    /* Dev_KR Region End */
}
?>