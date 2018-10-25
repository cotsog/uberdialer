<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Leads extends MY_Controller
{
    public $crumbs = '<a href="/dashboards/">Dashboard</a>';

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            //Admin User Not logged in
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->helper(array('url', 'html', 'form','utils'));
        $this->load->model('Leads_model');
    }

    public function index($page_num=1,$sortField='Date',$order='asc'){
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
        $recs_per_page = 100;
        // get page number && set how many records are display
        $page_number = (int) $this->input->get('per_page', TRUE);
        if(empty($page_number))$page_number = 1;
        $offset = (int) $this->input->get('per_page', TRUE);
        // get total rows from Lead_history table with the help of filters 
        $tot_records = $this->Leads_model->getLeadsStatusListCount($searchBy);
        // fetch leads data from database 
        $lead_status_list = $this->Leads_model->getLeadsStatusList($searchBy,$recs_per_page,$offset,$sortField,$order);
        //echo '<pre>';print_r($lead_status_list);exit;
        if ($this->input->post('file_type') != "" && !empty($lead_status_list)) {
            $this->load->model("Audittrail_model");
            $audittrail_model = new Audittrail_model();
            $audittrail_model->log("download", "tm", "Lead Status", $searchBy);
            // Increase memory size so we don't error out trying to export days/weeks worth of data
            ini_set('memory_limit','1024M');
            $lead_status_export = $this->Leads_model->getLeadsStatusList($searchBy,"","",$sortField,$order,1);
            $ls_export = $this->_assemble_lead_status_export($lead_status_export);
            $this->export_data($this->input->post('file_type'), "Lead Status Report", $ls_export);
        }
        $data['num_recs'] = $tot_records;
        $leadStatus = getLeadStatusValues(); // get lead status with the help of helper file
        unset($leadStatus['In Progress']);
        $data['leadStatus'] = $leadStatus;
        $data['leadsStatusList'] = $lead_status_list;
        $data['filterBy'] = !empty($searchBy['filter_by']) ? $searchBy['filter_by'] : 'updated_at';
        // set base url for blade file & pagination 
        $config['base_url'] =  '/dialer/Leads/index/';
        $data['base_url'] = $config['base_url'];
        // intialize pagination vairables
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
        // get agents /  QA / Campaigns from lead_histroy table
        $data['telemarketerList']= $this->Leads_model->get_telemarketer();
        $data['qaList'] = $this->Leads_model->getQAList();
        $data['allCampaignList'] = $this->Leads_model->getAllCampaignList();
        if ($this->session->userdata('user_type') == 'manager') {
            $officeLists = $this->session->userdata('sub_telemarketing_offices');
            array_unshift($officeLists, $this->session->userdata('telemarketing_offices'));
            foreach ($officeLists as $office) {
                $officeList[$office] = $office;
            }
        } else {
            $officeList = format_array($officesModel->get_all('is_active = 1'),'name','name');
        }
        $data['getEGWebsitesList'] = $officeList;
        $data['user_id'] = $this->session->userdata('uid');
        if($this->session->userdata('user_type') == 'dataresearch_user' || $this->session->userdata('user_type') == 'agent'){
            $data['crumbs'] = $this->crumbs . ' > QA';
        } else {
        $data['crumbs'] = $this->crumbs . ' > QA > Lead Status';
        }    
        $data['meta_title'] = 'Lead Status';
        $data['title'] = 'Lead Status';
        $data['main'] = 'dialer/leads/index';
        $this->load->vars($data);
        $this->load->view('layout');
    }

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

    // export lead records using Excell format 
    function _export_excel($report_title, $rows) {
        ini_set('memory_limit','1024M');
        // set report header 
        if($report_title == 'Lead Status Report_Report') {
            $report_header = array('Site', 'Campaign ID','Campaign Name','Campaign Type','First Name', 'Last Name', 'Company','Email','Notes','Job Title','Agent','QA','Time Submitted', 'Last Updated','Status','Phone','Rejection Reason','Follow-Up Reason');
        } else {
            $report_header = array_keys($rows[0]);
        }
        // set lead data to export report
        $i = 0;
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $report_values[$i][] = $value;
            }
            $i++;
        }
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".xls"; // The file name you want any resulting file to be called.
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel($filename);
        $excel_obj->setHeadersAndValues($report_header, $report_values);
        //now generate the excel file with the data and headers set
        $excel_obj->GenerateExcelFile();
        exit;
    }

    // export lead records using Csv format
    function _export_csv($report_title, $rows) {
        ini_set('memory_limit','1024M');
        // set report header 
        if($report_title == 'Lead Status Report_Report') {
            $report_header = array('Site', 'Campaign ID','Campaign Name','Campaign Type','First Name','Last Name','Company','Email','Notes','Job Title','Agent','QA','Time Submitted','Last Updated','Status','Phone', 'Rejection Reason','Follow-Up Reason');
        } else {
            $report_header = array_keys($rows[0]);
        }
        // set lead data to export report
        $i = 0;
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $report_values[$i][] = $value;
            }
            $i++;
        }
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".csv"; // The file name you want any resulting file to be called.
        //create the instance of the exportexcel format
        $csv_obj = new ExportCSV($filename);
        $csv_obj->setHeadersAndValues($report_header, $report_values);
        //now generate the csv file with the data and headers set
        $csv_obj->GenerateCSVFile();
        exit;
    }
    
    /**
    * This function takes a raw array and assembles a finished array for export
    * @param $lead_status_export Array The array to be processed
    * @param $report_values Array The output array
    */
    function _assemble_lead_status_export($lead_status_export) {
        $i = 0;
        foreach($lead_status_export as &$export_row) {
            if($export_row['rejection_reasons'] == 'Reason' || $export_row['followup_reasons'] == 'Reason') {
                $reject_reasons = '';
                $follow_up_reasons = '';
                $this->load->model('Leads_model');
                $reasons = $this->Leads_model->getReasonsByLeadId($export_row['lead_id']);
                foreach($reasons as $reason) {
                    if ($reason['status'] == 'Reject') {
                        $reject_reasons .= 'Reason: ' . $reason['reason'] . "\r\n" . $reason['reason_text'] . "\r\n";
                    } else if ($reason['status'] == 'Follow-up') {
                        //get recent follow-up reason only
                        $follow_up_reasons = 'Reason: ' . $reason['reason'] . "\r\n" . $reason['reason_text'] . "\r\n";
                    }
                }
                $export_row['rejection_reasons'] = rtrim($reject_reasons, "\r\n");
                $export_row['followup_reasons'] = rtrim($follow_up_reasons, "\r\n");
            }
            
            foreach($export_row as $key => $value) {
                switch ($key) {
                    case 'telemarketing_offices':
                        $report_values[$i][0] = $value != '' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : ' ';
                        break;
                    case 'eg_campaign_id':
                        $report_values[$i][1] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'campaign_name':
                        $report_values[$i][2] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'campaign_Type':
                        $report_values[$i][3] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'first_name':
                        $report_values[$i][4] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'last_name':
                        $report_values[$i][5] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'company':
                        $report_values[$i][6] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'contact_email':
                        $report_values[$i][7] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'notes':
                        $report_values[$i][8] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'job_title':
                        $report_values[$i][9] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'agent_name':
                        $report_values[$i][10] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'qa_name':
                        $report_values[$i][11] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'Time_Submitted':
                        if( $value != '' ){
                            $report_values[$i][12] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", php_datetimeformat($value));
                        }else{
                            $report_values[$i][12] = '';
                        }
                        break;
                    case 'Last_Updated':
                        if( $value != '' ){
                            $report_values[$i][13] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", php_datetimeformat($value));
                        }else{
                            $report_values[$i][13] = '';
                        }
                        break;
                    case 'Status':
                        $report_values[$i][14] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    case 'phone':
                        $report_values[$i][15] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                        break;
                    
                    case 'rejection_reasons':
                        $report_values[$i][16] = ($value != '-' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : NULL);
                        break;
                    case 'followup_reasons':
                        $report_values[$i][17] = ($value != '-' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : NULL);
                        break;
                }
            }
            ksort($report_values[$i]);
            $i++;
        }
        return $report_values;
    }
 
    // Fetch Follow-up / Regected Reason(s) 
    function getReasons(){
         if (!empty($_POST['leadID'])){
            $this->load->model('Leads_model');
            $res = $this->Leads_model->getReasonsByLeadId($_POST['leadID']);           
            
            $data['status'] = true;
            $data['data']= $res;                
         }else{
            $data['message'] = "Sorry, an error has occurred.";
            $data['status'] = false;
         }
        echo json_encode($data);
        exit();   
    }
}
?>
