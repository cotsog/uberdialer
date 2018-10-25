<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Emailtemplates extends MY_Controller
{
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->library(array('form_validation', 'session')); // load form lidation libaray & session library
        $this->load->helper(array('url', 'html', 'form', 'utils','common'));
        $this->load->model('Emailtemplates_model');
        
        // To check Authorised User OR not with the help of helper Function  
        $isAuthorized = IsAdminManagerAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
    }
    
    // Set Email Template Dashboard Layout 
    public function index($id = null)
    {
        $data['meta_title'] = 'Email Templates';
        $data['title'] = 'Email Templates';
        $data['main'] = 'dialer/emailtemplates/index';
        $data['crumbs'] = $this->crumbs . ' > Email Templates > Manage Templates';

        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    // Fetch Template(s) to Display Templates (Ajax Call)
    public function get_templates()
    {
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
           
        $response = $this->Emailtemplates_model->getTemplateList($logged_tm_office,$logged_user_type);
        if (!empty($response)) {
            foreach ($response as $emailTemplate) {
                $emailTemplate->name = htmlspecialchars($emailTemplate->name);
                $emailTemplate->status = ucfirst($emailTemplate->status);
            }
        }
        echo json_encode($response);
    }

   // Create email Template 
    public function create()
    {
        $this->load->model('Campaigns_model');
        
        $logedInUser = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');
              
        //Set Validation Rules
        $this->templateFormValidation($this->input->post());
         if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
             
            $data['campaigns']   = $this->Campaigns_model->get_campaign_by_userLogin($userType);
            $data['crumbs']         = $this->crumbs . ' > Email Templates > Create New';
            $data['meta_title']     = 'Create Temptate';
            $data['title']          = 'Create Temptate';
            $data['main']           = 'dialer/emailtemplates/create';
                        
            $this->load->vars($data);
            $this->load->view('layout');
        }else{
             // set Email Template object 
            $template = $this->setTemplateObjectData();            
            $template->created_at = date('Y-m-d H:i:s', time());
            $template->created_by = $logedInUser;
            
            // Insert Email Template in 'email_templates' table 
            $template_id = $this->Emailtemplates_model->insert_template($template);
            if ($template_id > 0) {
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Template Added successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
            }                
            redirect('/dialer/emailtemplates/');
        }
    }
    
    // Edit email Template 
    public function edit($id)
    {
        $logedInUser = $this->session->userdata('uid');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        //Set Validation Rules
        $this->templateFormValidation($this->input->post());
        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            $template = null;
            
            if(!empty($id)){
                $template = $this->Emailtemplates_model->get_one_template($id,$logged_user_type,$logged_tm_office);
            }
            if (empty($template)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Template not found, please make sure that the Template ID is correct!');
                redirect('/dialer/emailtemplates');
            }
            if($logged_user_type == 'manager' && $template->tm_office != $logged_tm_office){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This Campaign is not in your TM office, You can not edit it!');
                redirect('/dialer/emailtemplates');
            }
            $data['template'] = $template;

            $data['crumbs']         = $this->crumbs . ' > <a href="/dialer/emailtemplates/">Email Templates</a> > Edit';
            $data['meta_title']     = 'Edit Temptate';
            $data['title']          = 'Edit Temptate';
            $data['main']           = 'dialer/emailtemplates/edit';                       
            $this->load->vars($data);
            $this->load->view('layout');            
        }else{
            
            // set Email Template object 
            $template = $this->setTemplateObjectData();
            $template->updated_at = date('Y-m-d H:i:s', time());
            $template->updated_by = $logedInUser;
            
            // update New Changes
            $template_id = $this->Emailtemplates_model->update_template($id,$template);
            if ($template_id > 0) {
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Template updated successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
            }                
            redirect('/dialer/emailtemplates/');
        }        
    }
    
    // Create Clone OF Existing Template
    public function templateclone($id=0)
    {
        $logedInUser = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $this->load->model('Campaigns_model');
        
        //Set Validation Rules
        $this->templateFormValidation($this->input->post());
        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            $template = null;
            if(!empty($id)){
                $template = $this->Emailtemplates_model->get_one_template($id,$userType,$logged_tm_office);
            }
            if (empty($template)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Template not found, please make sure that the Template ID is correct!');
                redirect('/dialer/emailtemplates');
            }
            if($userType == 'manager' && $template->tm_office != $logged_tm_office){
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'This Campaign is not in your TM office, You can not edit it!');
                redirect('/dialer/emailtemplates');
            }
            $data['campaigns']   = $this->Campaigns_model->get_campaign_by_userLogin($userType);
            
            // Get Resources From EG DATABASE
            $this->load->model('Resourceview_model');
            $resources  = $this->Resourceview_model->GetResourcesByEgcampaignID($template->eg_campaign_id);
            
            $data['resources'] = $resources;
            $data['status'] = true;
            
            $data['template'] = $template;

            $data['crumbs']         = $this->crumbs . ' > <a href="/dialer/emailtemplates/">Email Templates</a> > Clone';
            $data['meta_title']     = 'Clone Temptate';
            $data['title']          = 'Clone Temptate';
            $data['main']           = 'dialer/emailtemplates/clone';                       
            $this->load->vars($data);
            $this->load->view('layout');            
        }else{
            
            // set Email Template object 
            $template = $this->setTemplateObjectData();
            $template->created_at = date('Y-m-d H:i:s', time());
            $template->created_by = $logedInUser;
            
            //  Insert record in table
            $template_id = $this->Emailtemplates_model->insert_template($template);
            if ($template_id > 0) {
                $this->session->set_flashdata('class', 'good');
                $this->session->set_flashdata('msg', 'Template Added successfully!');
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
            }                
            redirect('/dialer/emailtemplates/');
        }
    }
    
    // View The Template Information
    public function view($id)
    {           
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $logged_user_type = $this->session->userdata('user_type');
        // Get Template Info
        $template = $this->Emailtemplates_model->get_one_template($id,$logged_user_type,$logged_tm_office);
        if($logged_user_type == 'manager' && $template->tm_office != $logged_tm_office){
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'This Campaign is not in your TM office, You can not edit it!');
            redirect('/dialer/emailtemplates');
        }

        $template->name = htmlspecialchars($template->name);
        $data['template'] = $template;
        $data['meta_title'] = 'Template Details';
        $data['title'] = 'Template Details';
        $data['main'] = 'dialer/emailtemplates/view';
       
        $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/emailtemplates/">Email Templates</a> > Template Details ';

        $this->load->vars($data);
        $this->load->view('layout');
    }
    
    // Delete email Template(s)  (Ajax Call)
    public function delete()
    {   
        $template_id = $this->input->post('templateID');

        // Delete Template(s)
        $is_deleted = $this->Emailtemplates_model->delete_template($template_id);
        if ($is_deleted) {          
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

    // Get Campaign Wise Resourses from EG DAATABASE  (Ajax Call)
    public function getCampaignResources()
    {
        $campaignId = $this->input->post('campaignID');
        $this->load->model('Campaigns_model');
        if($campaignId){           
            $this->load->model('Resourceview_model');
            $campaignData = $this->Campaigns_model->getCampaignDetailByID($campaignId);
            
            $data['sitename'] = $campaignData->site_name;
            $data['compnayname'] = $campaignData->company_name;
            
            $resources  = $this->Resourceview_model->GetResourcesByEgcampaignID($campaignData->eg_campaign_id);
            $data['resources'] = $resources;
            $data['status'] = true;
        }else{
            $data['message'] = 'Campaign not found, please make sure that the campaign ID is correct!';
            $data['status'] = false;
        }
        echo json_encode($data);
        exit;
    }
    
    // Set Ckeditor Body Part with the help of common function   (Ajax Call)
    public function getBodyByResourceObject()
    {
        $array = json_decode($this->input->post('array'));  
        
        if(!empty($array)){
            $type = $array->type;
            $resourceName = $array->name;
            $company = $array->company;
            $url = $array->file;
            $discription = $array->brief_desc;
         
            // get template Body as per Resource Type 
            if($type == 'White Paper' || $type == 'eBook' ){
                $temp = EmailTemplate_WhitepaperEBook_Type($type,$resourceName,$company,$url,$discription);
            }else if($type == 'Webcast'){
                $temp = EmailTemplate_Webcast_Type($type,$resourceName,$company,$url,$discription);                
            }else if($type == 'Webcast On Demand'){
                $temp = EmailTemplate_WebcastOnDemand_Type($type,$resourceName,$company,$url,$discription); 
            }else{
                $temp = EmailTemplate_Other_Type($type,$resourceName,$company,$url,$discription);
            }
            $data['status'] = true;
            $data['Temp'] = $temp;
            
        }else{
            $data['message'] = 'Resouce not found, please make sure that the Resurce ID is correct!';
            $data['status'] = false;
        }
        echo json_encode($data);
        exit;
    } 

    // Common Vaditation Funciton 
    public function templateFormValidation($postDataValue)
    {
        $this->form_validation->set_rules('campaign_id', 'Campaign Name', 'required|trim');
        $this->form_validation->set_rules('resource_id', 'Resource ID', 'required|trim');
        $this->form_validation->set_rules('subject_line', 'Subject Line', 'required|trim');
        if (!empty($postDataValue['body'])) {
            $this->form_validation->set_rules('body', 'Body', 'required|trim');
        }
        if (!empty($postDataValue['signature_line'])) {
            $this->form_validation->set_rules('signature_line', 'Signature Line', 'required|trim');
        }
        $this->form_validation->set_error_delimiters('<div class="validation_error"><ul><li>', '</li></ul></div>');
    }
    
    // Common Set Email Template Object Funciton for UBER DATABASE
    public function setTemplateObjectData()
    {
        $template = new Templates();
        $viewUserData = (object)$this->input->post();
        $template->campaign_id = $viewUserData->campaign_id;
        $template->resource_id = $viewUserData->resource_id;
        $template->resource_name = $viewUserData->resource_name;
        $template->subject_line = $viewUserData->subject_line;
        $template->body =$viewUserData->body;
        $template->signature_line = $viewUserData->signature_line;       
        return $template;
    }    
}