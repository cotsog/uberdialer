<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Lists extends MY_Controller {
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';

    public function __construct() {
        parent::__construct();

        $protected_methods = array('add_lead');
        if(!in_array($this->router->method, $protected_methods)) {
            if (!$this->session->userdata('uid')) {
                $this->session->set_flashdata('prev_action', 'loginfail');
                redirect('/login');
            }
        }
        $this->load->library(array('form_validation','session')); // load form lidation libaray & session library
        $this->load->helper(array('url','html','form','utils','common'));
        $this->load->model('Lists_model');
    }

    public function index($campaign_id){
        $userType = $this->session->userdata('user_type');
        if ($userType == 'agent' || $userType == 'team_leader') {
            $agentSessionCampaignId = $this->session->userdata('AgentSessionCampaignID');
            //if user has opened two tabs and in one tab he signed out all the campaigns and then through url if he try to access signed out campaign
            if (empty($agentSessionCampaignId)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Campaign not sign in, please make sure that the Campaign is Sign in!');
                redirect('/dialer/campaigns');
            }
        }else{
            $isAuthorized = IsTLManagerQAUpperManagementAuthorized($userType);
            if (!$isAuthorized) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'You are unauthorized person for access this page.');
                redirect($this->app_module_name.'/campaigns');
            }
        }
        $this->load->model('Campaigns_model');
        $campaignsModel = new Campaigns_model();
        // Get Campaign Information
        $campaignData = $campaignsModel->get_one($campaign_id);
        
        //validation to check that campaign is available in campaign table
        if (empty($campaignData)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign not found, please make sure that the campaign ID is correct!');
            redirect('/dialer/campaigns');
        }
        if(empty($_REQUEST['pagination'])){
            if(in_array($campaign_id, array(0))){
                $get_all_list_by_campaign = $this->Lists_model->getAllListByCampaignWithoutCount($campaign_id, $userType);
            }else{
                $get_all_list_by_campaign = $this->Lists_model->getAllListByCampaign($campaign_id, $userType);
            }
        }else{
        
            $this->load->library('pagination');
            // set pagination row numbers to display on page 
            $recsPerPage = 10;
            // get page number && set how many records are display
            $pageNumber = (int) $this->input->get('per_page', TRUE);
            if(empty($pageNumber))$pageNumber = 1;
            $offset = (int) $this->input->get('per_page', TRUE);
            //get total count of list for pagination
            $checkActive = ($userType == 'agent') ? 'yes' : "all";
            $getTotalCount = $this->Lists_model->getTotalListCount($campaign_id, $checkActive);
            $data['num_recs'] = $getTotalCount;

            // fetch leads data from database
            if(in_array($campaign_id, array(0))){
                $get_all_list_by_campaign = $this->Lists_model->getAllListByCampaignWithoutCount($campaign_id, $userType);
            }else{
                $get_all_list_by_campaign = $this->Lists_model->getAllListByCampaign($campaign_id, $userType, $recsPerPage, $offset);

            }
            $config['base_url'] =  "/dialer/lists/index/{$campaign_id}/?pagination=true";
            $data['base_url'] = $config['base_url'];
            $config['total_rows'] = $getTotalCount;
            $config['per_page'] = $recsPerPage;
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
            $data['num_pages'] = ceil($getTotalCount / $recsPerPage);
            $data['current_page'] = ($offset / $recsPerPage) + 1;
            $data['offset'] = $offset;
            $data['getTotalCount'] = $getTotalCount;
        }
        $data['upperManagement'] =  $this->config->item('upper_management_types');
        $data['campaignData'] = $campaignData;
        $data['get_all_list_by_campaign'] = $get_all_list_by_campaign;
        $data['crumbs'] = $this->crumbs . ' > <a href="/dialer/campaigns">Campaigns</a> > List Management';
        $data['meta_title'] = 'List Management';
        $data['title'] = 'List Management';
        $data['main'] = $this->app_module_name.'/lists/index';
        $this->load->vars($data);
        $this->load->view('layout');
        }

    public function create(){
        $this->load->model('Campaigns_model');
        $this->listFormValidation($this->input->post());
        //$this->input->post();
        $editFlag = 0;
        if ($this->form_validation->run() == FALSE) { //Either first run through or validation failed
            $data['campaign'] = $this->Campaigns_model->get_campaign_by_name();
            $data['lists'] = $this->Lists_model->get_list_by_name();
            $data['crumbs'] =  $this->crumbs . ' > <a href="/dialer/lists">List</a> > Create List';
            $data['meta_title'] = 'Create List';
            $data['title'] = 'Create List';
            $data['main'] = $this->app_module_name.'/lists/create';

            $this->load->vars($data);
            $this->load->view('layout');
        }else{
            if(!empty($_FILES)){
                $allowed =  array('csv');
                $filename = $_FILES['userfile']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!in_array($ext,$allowed) ) {
                    $this->session->set_flashdata('msg', 'File must be in CSV format.');
                    redirect('Lists/create');
                }else{
                    $newFileName = round(microtime(true)) . '.' . $ext;
                    $target_dir = "./uploads/";
                    $target_file = $target_dir. $newFileName;
                    move_uploaded_file($_FILES["userfile"]["tmp_name"], $target_file);
                }
            }else{
                $this->session->set_flashdata('msg', 'No file was uploaded');
                redirect('Lists/create');
                
            }

            $list = new ListsTable();

//            $list->campaign_id = $_POST['eg_campaign_name'];
            $list->file_name = $newFileName;
            if(isset($_POST['list_name']) && $_POST['list_name']!=''){
                $list->list_name = $_POST['list_name'];
                $list->created_at = date('Y-m-d H:i:s', time());
            }else if(isset($_POST['select_list_name']) && $_POST['select_list_name']!=''){
                $list->list_id = $_POST['select_list_name'];
                $list->updated_at = date('Y-m-d H:i:s', time());
                $editFlag = 1;
            }

            $list->created_by = $this->session->userdata('uid');
            $list_id = $this->Lists_model->insert_list($list);

            if ($list_id > 0) {

                $result = $this->upload_csv($list_id,$newFileName,$editFlag);

                if($result==1){
                    $this->session->set_flashdata('class', 'good');
                    $this->session->set_flashdata('msg', 'List Added successfully!');
                }else{
                    if($result==2){$msg="Records exist!";}
                    else if($result==3){$msg="No records found in csv file!";}
                    else{
                        $this->load->model('Contacts_model');
                        $this->Contacts_model->delete($list_id);
                        $msg="Something Wrong While Uploading CSV Data...!"; 
                    }
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', $msg);
                }
            } else {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
            }
            redirect('Campaigns/');
        }
    }

    public function delete($id) {
        $list_details = $this->Lists_model->get_one($id);
        $is_deleted = $this->Lists_model->delete($id);
        if ($is_deleted) {
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'List Deleted successfully!');

        } else {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
        }
    }

    public function get_list(){
        $response = $this->Lists_model->get_all_lists();
        echo json_encode($response);
    }

    public function listFormValidation($postDataValue)
    {
        if(isset($postDataValue['list_name']) && $postDataValue['list_name']!=''){
            $this->form_validation->set_rules('list_name', 'List Name', 'required|trim');
        } else if(isset($postDataValue['select_list_name']) && $postDataValue['select_list_name']!=''){
            $this->form_validation->set_rules('select_list_name', 'List Name', 'required|trim');
        }       
    }

    /* Dev_kr Region Start */
    public function add_lead(){

        if ($this->input->server('REQUEST_METHOD') != 'POST') {
            //header("HTTP/1.1 404 Not Found");
            header('HTTP/1.1 404 Not Found', true, 404);
            //echo "The file you're looking for ~does not~ exist.";
            exit;
        }

        $data = json_decode(file_get_contents('php://input'));
        $total_errors = array();
        $errors = array();
        $this->load->model('contacts_model');
        $contact_model = new Contacts_model();

        if($data){
            if(!isset($data->username)){
                $errors[] = 'missing field: username';
            }else if(empty($data->username)){
                 $errors[] = 'empty field: username';
            }else if (($this->validStrLen($data->username, 'username', 5, 20))!= 1) {
                    $errors[] = $this->validStrLen($data->username, 'username', 5, 20);
            }

            if(!isset($data->password)){
                $errors[] = 'missing field: password';
            }else if(empty($data->password)){
                 $errors[] = 'empty field: password';
            }else if(($this->validStrLen($data->password,'password', 5, 20))!= 1){
                     $errors[] = $this->validStrLen($data->password,'password', 5, 20);
                 }


            if(!isset($data->list_id)){
                $errors[] = 'missing field: list_id';
            }else if(empty($data->list_id)){
                 $errors[] = 'empty field: list_id';
            }

            if(!isset($data->email)){
                $errors[] = 'missing field: email';
            }else if(empty($data->email)){
                 $errors[] = 'empty field: email';
            }else if(!($this->isValidEmail($data->email))){
                $errors[] = 'invalid value: email is invalid ';
            }else{

                $emails = $contact_model->get_contact_emails();
                $emailArray = array();
                $iq = 0;
                foreach ($emails as $key => $value) {
                    $emailArray[$iq] = $value['email'];
                    $iq++;
                }
                if (in_array($data->email, $emailArray)) {
                    $errors[] = 'invalid value: data already exists ';
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

            if(!empty($errors)){
                $result['status'] = false;
                $total_errors[] = array("errors"=>$errors);
                $result['Errors'] = $total_errors;
            }else{
                unset($data->username);
                unset($data->password);
                $data->created_at = date('Y-m-d H:i:s');
                $list_id = $contact_model->insert_contact($data);
                if($list_id){
                    $contact_lists_data = array();
                    $contact_lists_data ['contact_id'] = $list_id;
                    $contact_lists_data['list_id'] = $data->list_id;
                    $this->Contacts_model->insert_contact_lists($contact_lists_data);
                    $result['status'] = true;
                    $result['Errors'] = "";
                }else{
                    $result['status'] = false;
                    $result['Errors'] = "";
                }
            }
        }else{
             $result['status'] = false;
            $result['Errors'] = " Data not available. ";
        }
        echo json_encode($result);
    }

    public function isValidEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validStrLen($str,$fieldName, $min, $max){
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


    function upload_csv($id,$filename,$editFlag)
    {

        $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
        $newFileName = $path . $filename;
        $csvData = file_get_contents($newFileName);

        $fd = fopen($newFileName, "r");

        $lines = explode(PHP_EOL, $csvData);
        $csvArray = array();
        foreach ($lines as $line) {
            $csvArray[] = str_getcsv($line);
        }
        $csvLines = file($newFileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if ((count($csvLines) > 0)) {
            $this->load->model('Contacts_model');
            $contacts_model = new Contacts_model();
            $contactIdArray = array();
            /*$emails = $contacts_model->get_contact_emails();
            $emailArray = array();

            $iq = 0;
            foreach ($emails as $key => $value) {
                $emailArray[$iq] = $value['email'];
                $iq++;
            }*/

            foreach ($csvArray as $key => $value) {
                $insert_string = '';
                $insert_mapping_table_string = '';
                fgets($fd);
                while (!feof($fd)) {
                    $buffer = fgetcsv($fd, 4096);
                    $t_str = '';
                    if(!empty($buffer[0])){
                        /*if (!in_array($buffer[0], $emailArray)) {
                            array_push($emailArray,$buffer[0]);*/

                            for ($i = 0; $i < count($buffer); $i++) {
                                $code = str_replace(" ", "", $buffer[$i]);
                                $t_str .= "'" . $code . "',";
                            }
                           // $t_str .="'" .$id . "',";
                            $t_str .= "'".date('Y-m-d H:i:s')."'";
                            $insert_string .= '(' . trim($t_str, ",") . '),';

                            $mapping_table_insert = "'".$buffer[0]."',";
                            $mapping_table_insert .= "'" .$id . "'";
                            $insert_mapping_table_string .= '(' . trim($mapping_table_insert, ",") . '),';
                        //}
                        }
                    }
                $mapping_table_string = trim($insert_mapping_table_string,",");
                if($editFlag){
                    $contacts_model->remove_contact_list_data($id);
                }
                $contacts_model->insert_mapping_csv_contacts($mapping_table_string);

                $insert_string = trim($insert_string, ",");
                if ($insert_string != '') {
                    $list_id = $contacts_model->insert_csv_contacts($insert_string);
                    return $list_id;
                }
                else{
                    return  2;
                }
            }
            fclose($fd);
        }
        else
        {
            return 3;
        }
    }
  
    function deletelist(){
        $campaign_id = $this->input->post('campaign_id');
        $list_id  = $this->input->post('list_id');
        $is_deleted_campaign_contact = $this->Lists_model->delete_campaign_contact_list($campaign_id,$list_id);
        $is_exists_contact = $this->Lists_model->is_exits_campaign_contact_list($campaign_id,$list_id);
        
        if($is_exists_contact == 0){
            $is_deleted_list = $this->Lists_model->delete_list($campaign_id,$list_id);
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', 'Contact List Deleted successfully!');
        }elseif($is_exists_contact > 0){
            $this->session->set_flashdata('class', 'good');
            $this->session->set_flashdata('msg', "List Contact's Deleted sucessfully!");
        }else{
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Sorry, an error has occured.');
        }
        
    }
    /* Dev_kr Region End */

}