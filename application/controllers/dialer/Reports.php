<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Reports extends MY_Controller
{
    public $crumbs = '<a href="/dialer/dashboards/">Dashboard</a>';

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('uid')) {
            //Admin User Not logged in
            $this->session->set_flashdata('prev_action', 'loginfail');
            redirect('/login');
        }
        $this->load->helper(array('url', 'html', 'form','utils'));
        $this->load->model('Reports_model');
    }

    /* Dev_NV region Start */

    function export_data($file_type, $report_name, $data)
    {
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

    function _export_excel($report_title, $rows)
    {
        ini_set('memory_limit', '1024M');
        if(isset($rows[0])) {
            $report_header = array_map('ucwords', str_replace('_', ' ', array_keys($rows[0])));
            $i = 0;
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    $report_values[$i][] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                }
                $i++;
            }
        } else {
            if(isset($rows['Lead Gen'])) {
                $report_header = array('Campaign ID','Campaign','Amount of Leads Ordered','Date Sent Call File Requested','Deadline','Materials Sent to TM Ops (Asset, CF, TM Kick Off Email, etc)','Date Launch TM','Completion Date','QA Approved Leads','Qualified Leads - Campaign Tab');
                $num_cols = count($report_header);
                $report_values = $this->_assemble_tm_orders_export($rows);
            } else if(isset($rows['workable'])) {
                $report_header = array();
                $report_values = $this->_assemble_call_file_status_export($rows);
            } else {
                $report_header = array();
                $report_values = $this->_assemble_call_file_export($rows);
            }
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

    function _export_csv($report_title, $rows)
    {
        ini_set('memory_limit', '1024M');
        if(isset($rows[0])) {
            $report_header = array_map('ucwords', str_replace('_', ' ', array_keys($rows[0])));
            $i = 0;
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    $report_values[$i][] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                }
                $i++;
            }
        } else {
            if(isset($rows['Lead Gen'])) {
                $report_header = array('Campaign ID','Campaign','Amount of Leads Ordered','Date Sent Call File Requested','Deadline','Materials Sent to TM Ops (Asset/CF/TM Kick Off Email/etc)','Date Launch TM','Completion Date','QA Approved Leads','Qualified Leads - Campaign Tab');
                $num_cols = count($report_header);
                $report_values = $this->_assemble_tm_orders_export($rows);
            } else if(isset($rows['workable'])) {
                $report_header = array();
                $report_values = $this->_assemble_call_file_status_export($rows);
            } else {
                $report_header = array();
                $report_values = $this->_assemble_call_file_export($rows);
            }
        }
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".csv"; // The file name you want any resulting file to be called.
        //create the instance of the exportexcel format
        $csv_obj = new ExportCSV($filename);
        $csv_obj->setHeadersAndValues($report_header, $report_values);
        //now generate the excel file with the data and headers set
        $csv_obj->GenerateCSVFile();
        exit;
    }

    /* Staffing and Attrition Report */

    /* PureB2B Team Orders Report */

    function pure_b2b_team_orders()
    {
        if($this->session->userdata('user_type') == 'agent'){
            redirect('/dialer/campaigns');
        }else if($this->session->userdata('user_type') == 'qa'){
            redirect('/dialer/leads');
        }else{
            redirect('/users/profile');
        }exit;
        $this->load->helper('common');
        $isAuthorized = IsAdminManagerAuthorized($this->session->userdata('user_type'));
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $reports_model = new Reports_model();
        $pure_b2b_team_orders = $reports_model->pure_b2b_team_orders();
        $this->load->helper('campaignjobdetail');
        $func = 'getCampaignTypeValues'.ucfirst($this->app);
        $campaignTypeList = $func();
        $campaignTypeWiseArray = array();

        foreach($campaignTypeList as $key=>$value){
            $campaignTypeWiseArray[$value] = array_values(array_filter($pure_b2b_team_orders, function($pure_b2b_value) use($key){
                    return $pure_b2b_value['type'] == $key;
            }));
        }
        if ($this->input->post('file_type') != "" && !empty($campaignTypeWiseArray)) {
            $this->export_data($this->input->post('file_type'), "PureB2B Team Orders", $campaignTypeWiseArray);
        } else {
            $data['num_recs'] = count($pure_b2b_team_orders);
            $data['pure_b2b_team_orders'] = $pure_b2b_team_orders;
            $data['campaignTypeWiseArray'] = $campaignTypeWiseArray;
            $data['crumbs'] = $this->crumbs . ' > Reports > PureB2B TM Orders';
            $data['meta_title'] = 'PureB2B TM Orders';
            $data['title'] = 'PureB2B TM Orders';
            $data['main'] = 'dialer/reports/pure_b2b_team_orders';
            $this->load->vars($data);
            $this->load->view('layout');
        }
    }
    
    function _assemble_tm_orders_export($rows) {
        $i = 0;
        foreach ($rows as $campaign_type => $campaigns) {
            if(!empty($campaigns)) {
                $report_values[$i][] = $campaign_type;
                for($x=0;$x<9;$x++) {
                    $report_values[$i][] = '';
                }
                $i++;
                foreach ($campaigns as $v) {
                    unset($v['id']);
                    unset($v['type']);
                    unset($v['status']);
                    foreach($v as $key => $value) {
                        switch ($key) {
                            case 'eg_campaign_id':
                                $report_values[$i][0] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'name':
                                $report_values[$i][1] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'lead_goal':
                                $report_values[$i][2] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'call_filerequest_date':
                                $report_values[$i][3] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'end_date':
                                $report_values[$i][4] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'materials_sent_to_tm_Date':
                                $report_values[$i][5] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'tm_launch_date':
                                $report_values[$i][6] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            case 'completion_date':
                                $report_values[$i][7] = $value != '00/00/0000' ? iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : NULL;
                                break;
                            case 'qa_approve_leads':
                                $report_values[$i][8] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                            default:
                                $report_values[$i][] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
                                break;
                        }
                    }
                    ksort($report_values[$i]);
                    $i++;
                }
            }
        }
        return $report_values;
    }
    
    function _assemble_call_file_export($rows) {
        $time_zones = array('EST','CST','MST','PST');
        $i = 0;
        foreach ($rows as $call_date => $call_data) {
            if($call_date != 'totals') {
                if(!empty($call_data)) {
                    $report_values[$i][] = $call_date;
                    for($x=0;$x<5;$x++) {
                        $report_values[$i][] = '';
                    }
                    $i++;
                    $report_values[$i][] = 'Time Zone';
                    $report_values[$i][] = 'Dials';
                    $report_values[$i][] = 'Human Answer';
                    $report_values[$i][] = 'Contact Rate';
                    $report_values[$i][] = 'Lead Conversion';
                    foreach ($call_data as $time_zone => $values) {
                        if(in_array($time_zone, $time_zones)) {
                            $i++;
                            if(!empty($values[0])) {
                                foreach($values[0] as $k => $v) {
                                    switch ($k) {
                                        case 'dials':
                                            $report_values[$i][1] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v);
                                            break;
                                        case 'human_answer':
                                            $report_values[$i][2] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v);
                                            break;
                                        case 'contact_rate':
                                            $report_values[$i][3] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v);
                                            break;
                                        case 'lead_conversion':
                                            $report_values[$i][4] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v);
                                            break;
                                        default:
                                            $report_values[$i][0] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v);
                                            break;
                                    }
                                }
                                ksort($report_values[$i]);
                                $i++;
                            } else {
                                $report_values[$i][0] = $time_zone;
                                $report_values[$i][1] = '0';
                                $report_values[$i][2] = '0';
                                $report_values[$i][3] = '0';
                                $report_values[$i][4] = '0';
                                $i++;
                            }
                        }
                    }
                    $report_values[$i][0] = 'Team Total';
                    $report_values[$i][1] = isset($call_data['TDials']) ? $call_data['TDials'] : "";
                    $report_values[$i][2] = isset($call_data['THumanAnswer']) ? $call_data['THumanAnswer'] : "";
                    $report_values[$i][3] = isset($call_data['TContactRate']) ? $call_data['TContactRate'] : "";
                    $report_values[$i][4] = isset($call_data['TLeadConversion']) ? $call_data['TLeadConversion'] : "";
                    $i++;
                    $report_values[$i][0] = 'Percentage';
                    $report_values[$i][1] = '';
                    $report_values[$i][2] = isset($call_data['PHumanAnswer']) ? $call_data['PHumanAnswer'] : "" . '%';
                    $report_values[$i][3] = isset($call_data['PContactRate']) ? $call_data['PContactRate'] : "" . '%';
                    $report_values[$i][4] = isset($call_data['PLeadConversion']) ? $call_data['PLeadConversion'] : "" . '%';
                    $i++;
                    // Add a blank row after each date's section. This is just a formatting
                    // choice, to break up the sheet when there are a lot of days selected
                    $report_values[$i][0] = ' ';
                    $report_values[$i][1] = '';
                    $report_values[$i][2] = '';
                    $report_values[$i][3] = '';
                    $report_values[$i][4] = '';
                    $i++;
                }
            }
        }
        foreach($rows as $call_date => $call_data) {
            if($call_date == 'totals') {
                $report_values[$i][] = 'Time Zone Wise Gross Total';
                $report_values[$i][] = '';
                $report_values[$i][] = '';
                $report_values[$i][] = '';
                $report_values[$i][] = '';
                $i++;
                $report_values[$i][] = 'Time Zone';
                $report_values[$i][] = 'Total Dials';
                $report_values[$i][] = 'Total Human Answer';
                $report_values[$i][] = 'Total Contact Rate';
                $report_values[$i][] = 'Total Lead Conversion';
                $i++;
                foreach ($call_data as $k => $values) {
                    if(is_array($values)) {
                        foreach($values as $key => $val) {
                            switch ($key) {
                                case 'dials':
                                    $report_values[$i][1] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val);
                                    break;
                                case 'human_answer':
                                    $report_values[$i][2] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val);
                                    break;
                                case 'contact_rate':
                                    $report_values[$i][3] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val);
                                    break;
                                case 'lead_conversion':
                                    $report_values[$i][4] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val);
                                    break;
                                default:
                                    $report_values[$i][] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $val);
                                    break;
                            }
                        }
                        ksort($report_values[$i]);
                        $i++;
                    }
                }
                $report_values[$i][0] = 'Gross Total';
                $report_values[$i][1] = $call_data['total_dials'];
                $report_values[$i][2] = $call_data['total_human_answer'];
                $report_values[$i][3] = $call_data['total_contact_rate'];
                $report_values[$i][4] = $call_data['total_lead_conversion'];
                $i++;
            }
        }
        $report_values = array_values($report_values);
        return $report_values;
    }

    function _assemble_call_file_status_export($rows) {
        $i = 0;
        
        $report_values[$i][] = 'Workable Dispositions';
     
        $total_count = 0;
        $total_display = 0;
        foreach ($rows['workable'] as $agents => $dispo) {
            $i++;
            //insert total rows before owner rows
            if( ( $agents == 'PUREB2B' || $agents == '3rd Party' ) && !$total_display ){
                $total_display = 1;
                $report_values[$i][] = 'TOTAL';
                foreach ( $rows['counts']['workable'] as $total ) {
                   $report_values[$i][] = $total;
                }
                $i++;
            }
            $report_values[$i][] = $agents;
            foreach( $dispo as $value ){
                $report_values[$i][] = $value;
            }
        }
        $i++;
        
        $report_values[$i][] = '';
        $i++;
        $report_values[$i][] = 'Non Workable Dispositions';
        $total_display = 0;
        $total_count = 0;
        foreach ($rows['non_workable'] as $agents => $dispo) {
            $i++;
            //insert total rows before owner rows
            if( ( $agents == 'PUREB2B' || $agents == '3rd Party' ) && !$total_display ){
                $total_display = 1;
                $report_values[$i][] = 'TOTAL';
                foreach ( $rows['counts']['non_workable'] as $total ) {
                   $report_values[$i][] = $total;
                }
                $i++;
            }
            $report_values[$i][] = $agents;
            foreach( $dispo as $value ){
                $report_values[$i][] = $value;
            }
        }
        $i++;
        
        return $report_values;
    }
    
    function _assemble_qa_escalation($team_array, $qa_escalation_array) {
        $i = 0;
        $report_values[$i]['Team Totals'] = '';
        $i++;
        foreach($team_array as $team_esc) {
            $report_values[$i][] = $team_esc['first_name'];
            $report_values[$i][] = isset($team_esc['team_count_value']) ? $team_esc['team_count_value'] : '0';
            $i++;
        }
        $report_values[$i][] = ' ';
        $i++;
        $report_values[$i][] = "Campaign Name";
        $report_values[$i][] = "Company";
        $report_values[$i][] = "Prospect's Name";
        $report_values[$i][] = "Call Disposition";
        $report_values[$i][] = "QA Notes/ Agent Infraction";
        $report_values[$i][] = "Agent's Name";
        $report_values[$i][] = "Team";
        $report_values[$i][] = "Date";
        $report_values[$i][] = "QA";
        $i++;
        foreach($qa_escalation_array as $qa_esc) {
            $report_values[$i][] = $qa_esc['campaign_name'];
            $report_values[$i][] = $qa_esc['company_name'];
            $report_values[$i][] = $qa_esc['prospect_name'];
            $report_values[$i][] = $qa_esc['calldisposition_name'];
            $report_values[$i][] = str_replace('<br>', "\r\n", $qa_esc['notes']);
            $report_values[$i][] = $qa_esc['agent_name'];
            $report_values[$i][] = $qa_esc['team_leader_name'];
            $report_values[$i][] = $qa_esc['notes_created_date'];
            $report_values[$i][] = $qa_esc['qa_name'];
            $i++;
        }
        return $report_values;
    }

    /* Start Rejected Lead Summary */

    function rejected_lead_summary()
    {
         $user_type = $this->session->userdata('user_type');
         $loggedUserID = $this->session->userdata('uid');
         if ($user_type == 'agent') {
             $this->session->set_flashdata('class', 'bad');
             $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
             redirect('/dialer/campaigns');
         }

         $pass_from_date = $this->input->post('from_date');
         $pass_to_date = $this->input->post('to_date');

         // if from date/to date empty should be today Date
         if (empty($pass_to_date) && empty($pass_from_date)) {
             $from_date = date('Y-m-d', time());
             $_POST['from_date'] = date('m/d/Y', time());
             $to_date = date('Y-m-d', time());
             $_POST['to_date'] = date('m/d/Y', time());
         } else {
             $from_date = date('Y-m-d', strtotime($pass_from_date));
             $to_date = date('Y-m-d', strtotime($pass_to_date));
         }
         if($this->input->post('file_type')!=""){
             $pass_from_date = $this->input->post('from_date');
             $pass_to_date = $this->input->post('end_date');
             $from_date = date('Y-m-d', strtotime($pass_from_date));
             $to_date = date('Y-m-d', strtotime($pass_to_date));
         }

         $reports_model = new Reports_model();
         $rejected_lead_summary = $reports_model->rejected_lead_summary($from_date, $to_date, $loggedUserID);

        if ($this->input->post('file_type') != "" && !empty($rejected_lead_summary)) {
            $searchBy = array("from_date" => $from_date, "to_date" => $to_date, 'File Type' => $this->input->post('file_type'));
            $this->load->model("Audittrail_model");
            $audittrail_model = new Audittrail_model();
            $audittrail_model->log("download", "tm", "Rejected Lead Summary", $searchBy);
            $this->export_data($this->input->post('file_type'), "Rejected Lead Summary", $rejected_lead_summary);
        } else {
         $data['num_recs'] = count($rejected_lead_summary);
         $data['rejected_lead_summary'] = $rejected_lead_summary;
         $data['crumbs'] = $this->crumbs . ' > Reports > Rejected Lead Summary';
         $data['meta_title'] = 'Rejected Lead Summary';
         $data['title'] = 'Rejected Lead Summary';
         $data['main'] = 'dialer/reports/rejected_lead_summary';
         $this->load->vars($data);
         $this->load->view('layout');
         }
     }

    /* End Rejected Lead Summary */

    function get_more_notes()
    {
        if (!empty($_POST)) {

            if (isset($_POST['lead_history_ids']))
                $lead_history_ids = $_POST['lead_history_ids'];

            if (!empty($lead_history_ids)) {
                $reports_model = new Reports_model();
                $get_all_notes = $reports_model->get_more_notes($lead_history_ids);
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

    /* End QA Escalation */


    /* End Admin Qualified Leads Report */

    /**
     * @param $pass_to_date
     * @param $pass_from_date
     * @return array
     */
    public function get_date_range_data($pass_to_date, $pass_from_date)
    {
        $from_date = $pass_from_date ? date('Y-m-d', strtotime($pass_from_date)) : date('Y-m-d');
        $to_date = $pass_to_date ? date('Y-m-d', strtotime($pass_to_date)) : date('Y-m-d');
        $_POST['from_date'] = date('m/d/Y', strtotime($from_date));
        $_POST['to_date'] = date('m/d/Y', strtotime($to_date));
        return array($from_date, $to_date);
    }

    /* Disposition history report */

    public function disposition_report($page_num = 1, $sortField = 'date', $order = 'asc'){

        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');

        // check logged user have permission for access this report
        $isAuthorized = IsTLManagerUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $this->load->helper('campaignjobdetail');
        $this->load->library('pagination');

        $reports_model = new Reports_model();
        
        
        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');
        
        // if from date/to date empty should be today Date
        $this->get_date_range_data($pass_to_date, $pass_from_date);

        $searchBy = $this->input->post();

        // run this script while user filter any field values
        if (empty($searchBy) && count($this->input->get()) > 0) {
            $searchBy = $this->input->get();
            $_POST['email'] = $this->input->get('email');
            $_POST['calldisposition_name'] = $this->input->get('calldisposition_name');
            $_POST['phone'] = $this->input->get('phone');
            $_POST['first_name'] = $this->input->get('first_name');
            $_POST['last_name'] = $this->input->get('last_name');
            $_POST['company'] = $this->input->get('company');
            $_POST['campaign_id'] = $this->input->get('campaign_id');
            $_POST['campaign'] = $this->input->get('campaign');
            $_POST['date'] = $this->input->get('date');
            $_POST['dialer'] = $this->input->get('dialer');
            if($user_type == 'admin'){
                $_POST['site'] = $this->input->get('site');
            }
        }
        $noCampaignSelection = 0;
        //Get campaign list if campaign filter is empty
        /*if (empty($searchBy['campaign'])) {
	    $noCampaignSelection = 1;	
            $searchBy['campaign'] = $reports_model->getCampaignsWDispo($searchBy);
        }*/
        if (!empty($this->input->post('csv_y')) || !empty($this->input->post('xls_y'))) {
            $file_type = !empty($this->input->post('csv_x')) ? 'csv' : (!empty($this->input->post('xls_x')) ? 'excel' : '');
	    //ini_set('memory_limit', '1024M');
            $limit_per_loop = 50000;
            $offset = 0;
            $report = array();
	    $tot_records = $reports_model->dispositionReportCounts($searchBy);
	    if ($tot_records > 0) {
		$no_of_loop = ceil($tot_records / 50000);
		for ($i = 1;$i <= $no_of_loop; $i++) {
					
		    $reports = $reports_model->dispositionReport($searchBy, $limit_per_loop, $offset, $sortField, $order, true);
		    if (count($reports) > 0) {
		    	$report = array_merge($report, $reports);
		    }
		    if ($i == $no_of_loop) {
			$offset = $tot_records - $limit_per_loop;
		    } else {
			    $offset += $limit_per_loop;
		    }
		} 
	    }
	    if (empty($report)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'No Records found.');
                redirect('/dialer/reports/disposition_report');
            }else{
                $this->load->model("Audittrail_model");
                $audittrail_model = new Audittrail_model();
                unset($searchBy['xls_x']);
                unset($searchBy['xls_y']);
                unset($searchBy['csv_x']);
                unset($searchBy['csv_y']);
                unset($searchBy['csv']);
                unset($searchBy['xls']);
                $searchBy['file_type'] = $file_type;
		if ($noCampaignSelection) {
			unset($searchBy['campaign']);
		}
                $audittrail_model->log("download", "tm", "Disposition History", $searchBy);
                $this->export_data($file_type, "Disposition", $report);
            }
        } else {
            //validate if selected range is within 1 week
            $startDateTime = strtotime($pass_from_date);
            $correctEndDateTime = strtotime("+6 day", strtotime($pass_from_date));
            $inputtedEndDateTime = strtotime($pass_to_date);

            if(!empty($this->input->post('to_date'))){
                if($inputtedEndDateTime > $correctEndDateTime){
                    $this->session->set_flashdata('class', 'bad');
                    $this->session->set_flashdata('msg', 'Please select date range not more than 7 days.');
                    redirect('/dialer/reports/disposition_report');
                    exit;
                }
            }
        
            $user_model = new Users_model();
            $this->load->model('Calls_model');
            $calls_model = new Calls_model();
            
            if($user_type == 'admin'){
                $this->load->model('Offices_model');
                $officesModel = new Offices_model();
                
                $tm_offices = $officesModel->get_all('is_active = 1','name AS `office`');
                $data['tm_offices'] = $tm_offices;
            }
            $recs_per_page = 100;
            // $offset = 0;

            $page_number = (int)$this->input->get('per_page', TRUE);

            if (empty($page_number)) $page_number = 1;
            //$offset = ($page_number-1)*$recs_per_page;
            $offset = (int)$this->input->get('per_page', TRUE);
            //Get campaign list if campaign filter is empty
            $tot_records = $reports_model->dispositionReportCounts($searchBy);

            if ($tot_records > 0) {
                // get DNC disposition data list
                $data['dnc_disposition_list'] = $reports_model->dispositionReport($searchBy, $recs_per_page, $offset, $sortField, $order);
            } else{
                $data['dnc_disposition_list'] = array();
            }
            
            $data['num_recs'] = $tot_records;

            //Load pagination and configure
            $this->load->library('pagination');

            $config['base_url'] = '/dialer/reports/disposition_report/';
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

            $this->load->model('Campaigns_model');
            $loggedUserID = $this->session->userdata('uid');
            $data['allCampaignList'] = $this->Campaigns_model->get_campaign_by_userLogin($user_type,$loggedUserID,1);
            $data['user_id'] = $loggedUserID;
            $data['user_type'] = $user_type;
            $data['call_dispositions'] = $calls_model->getCallDispositionsByModule("tm","","id,calldisposition_name as name");

            $data['crumbs'] = $this->crumbs . ' > Reports > Disposition History';
            $data['meta_title'] = 'Disposition Report';
            $data['title'] = 'Disposition Report';
            $data['main'] = '/dialer/reports/disposition_history';

            $this->load->vars($data);
            $this->load->view('layout');
        }
    }
    /* Dev_NV region End */

    /* Dev_KR region Start */

    /* agent_status Report */
    
    public function agent_status(){

        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        if ($user_type == 'qa') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        
        $main_array = array();
        $loginLogoutTimestamp_array = array();
        
        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');
        
        // if from date/to date empty should be today Date
        if (empty($pass_to_date) && empty($pass_from_date)) {
            $from_date = date('Y-m-d', time());
            $_POST['from_date'] = date('m/d/Y', time());
            $to_date = date('Y-m-d', time());
            $_POST['to_date'] = date('m/d/Y', time());
        } else {
            $from_date = date('Y-m-d', strtotime($pass_from_date));
            $to_date = date('Y-m-d', strtotime($pass_to_date));
        }
        
        $upperManagement =  $this->config->item('upper_management_types');
        
        if($user_type == 'agent'){
            $loggedInUser = $this->session->userdata('uid');
        }else{
            $team_leader_id = $this->input->post('team_leader_id');
            if ($team_leader_id > 0  || ($user_type == 'manager') || (in_array($user_type, $upperManagement))){
                $loggedInUser = $team_leader_id;
            }
            else{
                $loggedInUser = $this->session->userdata('uid');
                $_POST['team_leader_id'] = $loggedInUser;
            }
        }
        $this->load->model('Users_model');
            $users = $this->Users_model->getusers();
            if(empty($loggedInUser)){
                $user_ids_array = array_column($users, 'id');
                $this->load->helper('common');
                $loggedInUser = getCSVFromArrayElement($user_ids_array);
            }

            if($user_type == 'manager' || in_array($user_type, $upperManagement)){
                array_unshift($users, array('id' => '0','member_name'=>'Select'));
            }
            $data['users'] = $users;
            
        $main_array = array();
        if(!empty($_POST['team_leader_id']) || (empty($_POST['team_leader_id']) && !empty($loggedInUser) && $user_type == 'agent')){

            $_POST['team_leader_id'] = $this->input->post('team_leader_id');

            $main_array = $this->Reports_model->get_agent_status($from_date, $to_date,$loggedInUser);

            if($this->input->post('status_export_type')!="" && !empty($main_array))
            {
                     $filename = " Agent Status ";
                //$this->export_data($this->input->post('status_export_type'),$filename,$main_array);
                     $this->exportAgentStatus($filename, $main_array, $this->input->post('status_export_type'));
                }

            /* Log in/out table for TL/Agent Only */

            if($user_type != 'manager' || !in_array($user_type, $upperManagement)){
                $loginLogoutTimestamp_array = $this->Reports_model->get_loginout_sesion_recods($from_date, $to_date, $loggedInUser);

                if($this->input->post('login_export_type') != "" && !empty($main_array))
                {
                         $filename = " Login history ";
                    //$filename = "Login history Status ".$from_date." To ".$to_date;     
                    $this->export_data($this->input->post('login_export_type'),$filename,$loginLogoutTimestamp_array);
                }
            }
        }
        $data['upperManagement'] =  $upperManagement;
        $data['reportData'] = $main_array;
        $data['logedInUser'] = $loggedInUser;
        $data['logInOutData']=$loginLogoutTimestamp_array;
        if($user_type != 'agent'){
            $data['crumbs'] = $this->crumbs . ' > Reports > Agent Status';
        } else {
            $data['crumbs'] = $this->crumbs . ' > Agent Status';
        }    
        $data['meta_title'] = 'Agent Status';
        $data['title'] = 'Agent Status';
        $data['main'] = 'dialer/reports/agent_status';

        $this->load->vars($data);
        $this->load->view('layout');
        
    }

    function exportAgentStatus($report_title, $data, $file_type){
        ini_set('memory_limit', '1024M');
        $report_header = array('Call Disposition','Total Count','Total Spent Time');
        $i = 0;
        $total = 0;
        foreach ($data as $row) {
            $total += $row['TotalDials'];
            foreach ($row as $key => $value) {
                $report_values[$i][] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
            }
            $i++;
        }
        $report_values[$i][0] = "Total Count:";
        $report_values[$i][1] = $total;
        
        $filename = url_title($report_title, '_', TRUE);
        $this->load->helper("export_excel");
        
        switch (trim($file_type)) {
            case 'excel':
                $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".xls"; // The file name you want any resulting file to be called.
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel($filename);
                $excel_obj->setHeadersAndValues($report_header, $report_values);
                //now generate the excel file with the data and headers set
                $excel_obj->GenerateExcelFile();
                break;
            case 'csv':
                $filename = $filename . "_" . date("Y_m_d_H:i:s") . ".csv"; // The file name you want any resulting file to be called.
                //create the instance of the exportexcel format
                $csv_obj = new ExportCSV($filename);
                $csv_obj->setHeadersAndValues($report_header, $report_values);
                //now generate the excel file with the data and headers set
                $csv_obj->GenerateCSVFile();
                break;
            default:
                echo "file type not specified";
                break;
        }
        exit;
    }
    
      /* QA Product Summary Report */
     public function qa_product_summary(){
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');
    
        if ($user_type == 'agent') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $pass_from_date = $this->input->post('from_date');
        
        // if from date/to date empty should be today Date
        if (empty($pass_from_date)) {
            $from_date = date('Y-m-d', time());
            $_POST['from_date'] = date('m/d/Y', time());
        } else {
            $from_date = date('Y-m-d', strtotime($pass_from_date));           
        }
        
        $lead_data = $this->Reports_model->get_qa_leads($from_date);
        $users = array_column($lead_data, 'first_name');
        $lead_array = array();
        $campaign_array = array();
        $approveRow = $rejectRow = $followupRow =0;
            foreach($lead_data as $value){
            if(in_array($value['first_name'],$users)){
                $lead_array['# of Leads approved'][$value['first_name']] = $value['approved_leads'];
                $lead_array['# of Leads rejected'][$value['first_name']] = $value['Reject_leads'];
                $lead_array['# of Leads tagged as follow up'][$value['first_name']] = $value['Followup_leads'];
                $lead_array['# of Leads duplicate lead'][$value['first_name']] = $value['Duplicate_leads'];
                }
            }
        $lead_array['# of Leads approved']['QA_Team_Total'] = array_sum(array_column($lead_data, 'approved_leads'));
        $lead_array['# of Leads rejected']['QA_Team_Total'] = array_sum(array_column($lead_data, 'Reject_leads'));
        $lead_array['# of Leads tagged as follow up']['QA_Team_Total'] = array_sum(array_column($lead_data, 'Followup_leads'));
         $lead_array['# of Leads duplicate lead']['QA_Team_Total'] = array_sum(array_column($lead_data, 'Duplicate_leads'));
         $totalLeads = $lead_array['# of Leads approved']['QA_Team_Total'] + $lead_array['# of Leads rejected']['QA_Team_Total'] + $lead_array['# of Leads tagged as follow up']['QA_Team_Total'] + $lead_array['# of Leads duplicate lead']['QA_Team_Total'];
        $campaign_lead_data = $this->Reports_model->get_campaign_qa_leads($from_date);
        
        $campaign_type =  array_unique(array_column($campaign_lead_data, 'type'));
        foreach($campaign_lead_data as $data){           
            $campaign_array[$data['name']]['type']= $data['type'];
            $campaign_array[$data['name']][$data['first_name']] = $data['leads'];            
        }
//        echo "<pre/>";
//        print_r($campaign_array);exit;
        $data['Campaign_Type'] = $campaign_type;
        $data['Qa']= $users;
        $data['QA_Leads_Data']=$lead_array;
        $data['Campaign_Data'] = $campaign_array;
        $data['totalLeads'] = $totalLeads;
        $data['crumbs'] = $this->crumbs . ' > Reports > QA Production Summary';
        $data['meta_title'] = 'QA Production Summary';
        $data['title'] = 'QA Production Summary';
        $data['main'] = 'dialer/reports/qa_product_summary';

        $this->load->vars($data);
        $this->load->view('layout');   
    }

    public function export_qa()
    {
        $var = $_POST['data'];   
        $filename ='QA_production_summary_'.date('Y-m-d',strtotime($_POST['from_date'])).".xls"; 
        header("Content-type: application/octet-stream"); //A MIME attachment with the content type "application/octet-stream" is a binary file.
        
        //Typically, it will be an application or a document that must be opened in an application, such as a spreadsheet or word processor. 
        header("Content-Disposition: attachment; filename=$filename"); //with this extension of file name you tell what kind of file it is.
        header("Pragma: no-cache"); //Prevent Caching
        header("Expires: 0"); //Expires and 0 mean that the browser will not cache the page on your hard drive
        echo "<htm><body>".$var."</body></html>";
                
    }
    /* Dev_KR region End */
    
    /**
    * Real-time Monitoring Dashboard Report
    */
    function realtime_monitoring_report() {
        $this->load->helper('common');
        $loggedInUserType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $isAuthorized = IsTLManagerUpperManagementAuthorized($loggedInUserType);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are not authorized to access this page');
            redirect('/dialer/campaigns');
        }
        $reports_model = new Reports_model();
        $from_date = date('Y-m-d', strtotime($this->input->post('from_date')));
        $to_date = date('Y-m-d', strtotime($this->input->post('to_date')));
        $get_agent_counts = $get_agents_calls_by_date = array();
        if($from_date != '') {
            $get_agents_calls_by_date = $reports_model->get_agent_calls_by_date($loggedInUserType, $logged_tm_office,$from_date,$to_date,'ORDER BY mr.user_id,mr.call_date,mr.eg_campaign_id');
            $get_agents_weekly_calls_by_date = $reports_model->get_agent_calls_by_date($loggedInUserType, $logged_tm_office,$from_date,$to_date,'GROUP BY mr.user_id,WEEKOFYEAR(mr.call_date),mr.eg_campaign_id ORDER BY mr.user_id,mr.call_date,mr.eg_campaign_id','weekly');
            $get_agents_monthly_calls_by_date = $reports_model->get_agent_calls_by_date($loggedInUserType, $logged_tm_office,$from_date,$to_date,'GROUP BY mr.user_id,DATE_FORMAT(mr.call_date, "%Y-%m"),mr.eg_campaign_id ORDER BY mr.user_id,mr.call_date,mr.eg_campaign_id','monthly');
        }
        if ($this->input->post('file_type') != "" && !empty($get_agents_calls_by_date)) {
            $export_array[] = array('Daily Report Totals' => '');
            if($loggedInUserType == 'admin') {
                $export_array[] = array('Office' => 'Office','Call Date' => 'Call Date','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            } else {
                $export_array[] = array('Call Date' => 'Call Date','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            }
            foreach($get_agents_calls_by_date as $dv) {
                unset($dv['user_id']);
                $export_array[] = $dv;
            }
            $export_array[] = array('Weekly Report Totals' => '');
            $export_array[] = array('Weekly Report Totals');
            if($loggedInUserType == 'admin') {
                $export_array[] = array('Office' => 'Office','Call Date' => 'Call Date','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            } else {
                $export_array[] = array('Week of Year' => 'Week of Year','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            }
            foreach($get_agents_weekly_calls_by_date as $wv) {
                unset($wv['user_id']);
                $export_array[] = $wv;
            }
            $export_array[] = array('Monthly Report Totals' => '');
            $export_array[] = array('Monthly Report Totals');
            if($loggedInUserType == 'admin') {
                $export_array[] = array('Office' => 'Office','Call Date' => 'Call Date','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            } else {
                $export_array[] = array('Call Month' => 'Call Month','Agent Name' => 'Agent Name','MM:SS' => 'MM:SS','Campaign ID' => 'Campaign ID','Campaign Name' => 'Campaign Name','Total number of Calls' => 'Total number of Calls');
            }
            foreach($get_agents_monthly_calls_by_date as $mv) {
                unset($mv['user_id']);
                $export_array[] = $mv;
            }
            $this->export_data($this->input->post('file_type'), "Realtime Monitoring Report", $export_array);
        } else {
            $data['agents_calls_by_date'] = $get_agents_calls_by_date;
            $data['agents_weekly_calls_by_date'] = $get_agents_weekly_calls_by_date;
            $data['agents_monthly_calls_by_date'] = $get_agents_monthly_calls_by_date;
            $data['user_type'] = $loggedInUserType;
            $data['crumbs'] = $this->crumbs . ' > Reports > Real-time Monitoring';
            $data['meta_title'] = 'Real-time Monitoring Report';
            $data['title'] = 'Real-time Monitoring Report';
            $data['main'] = 'dialer/reports/real_time_monitoring_report';
            $this->load->vars($data);
            $this->load->view('layout');
        }
    }


    /* call file status Report */
    
    public function call_file_status()
    {
        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');
        $loggedUserID = $this->session->userdata('uid');

        $isAuthorized = IsTLManagerUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $this->load->model('Campaigns_model');
        $this->load->model('Calls_model');
        $calls_model = new Calls_model();

        $data['campaigns'] = $this->Campaigns_model->get_campaign_by_userLogin($user_type,$loggedUserID,1);

        if(count($data['campaigns'])==0){
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Campaign(s) not found!');
            redirect('/dialer/campaigns');
        }

        $campaignId = $this->input->post('campaignId');

        if(empty($campaignId)){
            $campaignId = $data['campaigns'][0]->id;
            $_POST['campaignId'] = $data['campaigns'][0]->id;
        }

        $startDate = $this->input->post('start_date');
        if(empty($startDate)){
            $startDate = date('m/d/Y');
            $_POST['start_date'] = $startDate;
        }

        $endDate = $this->input->post('end_date');
        if(empty($endDate)){
            $endDate = date('m/d/Y');
            $_POST['end_date'] = $endDate;
        }

        $startDates = explode("/",$startDate);
        $startDate = $startDates[2] . "-" . $startDates[0] . "-" . $startDates[1];

        $endDates = explode("/",$endDate);
        $endDate = $endDates[2] . "-" . $endDates[0] . "-" . $endDates[1];

        $main_array = $reportData = array();
        $main_array = $this->Reports_model->get_call_file_status_per_agent($campaignId,$startDate,$endDate);

        //get pureb2b and third party counts per disposition 
        $counts_per_owner = $this->Reports_model->get_counts_per_owner($campaignId,$startDate,$endDate);

        $counts_per_owner_per_dispo = array();
        $calldispositions = array();

        $call_dispositions_lists = $calls_model->getCallDispositionsByModule("tm","");

        foreach ($call_dispositions_lists as $call_dispositions_list) {
            $calldispositions[$call_dispositions_list->id] = $call_dispositions_list->calldisposition_name;
            //# of Records default value for pureb2b and third party
            $counts_per_owner_per_dispo[$call_dispositions_list->is_workable]['PUREB2B'][$call_dispositions_list->id]['total'] = 0;
            $counts_per_owner_per_dispo[$call_dispositions_list->is_workable]['3rd Party'][$call_dispositions_list->id]['total'] = 0;
           
            //set report headers
            if($call_dispositions_list->is_workable) {
                $callDispositions['workable'][] = $call_dispositions_list->calldisposition_name;
                $reportData['workable']['Agent'][$call_dispositions_list->calldisposition_name] = $call_dispositions_list->calldisposition_name;
                $recordCount['workable'][$call_dispositions_list->calldisposition_name] = 0;
            } else {
                $callDispositions['non_workable'][] = $call_dispositions_list->calldisposition_name;
                $reportData['non_workable']['Agent'][$call_dispositions_list->calldisposition_name] = $call_dispositions_list->calldisposition_name;
                $recordCount['non_workable'][$call_dispositions_list->calldisposition_name] = 0;
            }
        }
        
        //Added # of Records in report headers
        $reportData['workable']['Agent']['# Of Records'] = "# Of Records";
        $reportData['non_workable']['Agent']['# Of Records'] = "# Of Records";
        
        $callDispositions['workable'][] = "# Of Records";
        $callDispositions['non_workable'][] = "# Of Records";
        
        //compute total per owner
        foreach( $counts_per_owner as $items ){
            $calldispositions[$items['call_disposition_id']] = $items['calldisposition_name'];
            if( strtolower( $items['name'] ) == 'pureb2b' ){
                $counts_per_owner_per_dispo[$items['is_workable']]['PUREB2B'][$items['call_disposition_id']]['total'] += $items['count'];
            }else{
                $counts_per_owner_per_dispo[$items['is_workable']]['3rd Party'][$items['call_disposition_id']]['total'] += $items['count'];
            }
        }

        foreach( $counts_per_owner_per_dispo as $is_workable => $items ){
            foreach( $items as $type => $item ){
                foreach( $item as $call_disposition_id => $value ){
                    $main_array[] = array("agent" => $type,
                                          "user_id" => $type,
                                          "call_disposition_id" => $call_disposition_id,
                                          "name" => $calldispositions[$call_disposition_id],
                                          "count" => $value['total'],
                                          "is_workable" => $is_workable
                                        );
                }
            }
        }

        $call_files = array();
        $call_files[0] = $call_files[1] = array();
        
        foreach( $main_array as $items ){
            $call_files[$items['is_workable']][$items['agent']][$items['name']] = $items['count'];
        }

        //set default value for # Of Records
        $recordCount['workable']['# Of Records'] = 0;
        $recordCount['non_workable']['# Of Records'] = 0;

        if( count($call_files) > 0 ){
            //consolidate workable dispo per agent
            foreach( $call_files[1] as $key => $items ) {
                foreach ($callDispositions['workable'] as $callDisposition) {
                    $reportData['workable'][$key][$callDisposition] = isset( $items[$callDisposition] ) ? $items[$callDisposition] : 0;
                    if( $key == 'PUREB2B' ||  $key == '3rd Party' ){
                    }else{
                        $recordCount['workable'][$callDisposition] += isset( $items[$callDisposition] ) ? $items[$callDisposition] : 0;
                    }
                }
                $reportData['workable'][$key]['# Of Records'] = array_sum($items);
                if( $key == 'PUREB2B' ||  $key == '3rd Party' ){ 
                }else{
                    $recordCount['workable']['# Of Records'] += $reportData['workable'][$key]['# Of Records'];
                }
            }
            //consolidate non workable dispo per agent
            foreach( $call_files[0] as $key => $items ) {
                foreach ($callDispositions['non_workable'] as $callDisposition) {
                    $reportData['non_workable'][$key][$callDisposition] = isset( $items[$callDisposition] ) ? $items[$callDisposition] : 0;
                    if( $key == 'PUREB2B' ||  $key == '3rd Party' ){
                    }else{
                        $recordCount['non_workable'][$callDisposition] += isset( $items[$callDisposition] ) ? $items[$callDisposition] : 0;
                    }
                }
                $reportData['non_workable'][$key]['# Of Records'] = array_sum($items);
                if( $key == 'PUREB2B' ||  $key == '3rd Party' ){
                }else{
                    $recordCount['non_workable']['# Of Records'] += $reportData['non_workable'][$key]['# Of Records'];
                }    
            }
        }
        
        $reportData['counts'] = $recordCount;
        
        if($this->input->post('file_type')!="" && !empty($reportData))
        {
            $campaign = $this->Campaigns_model->get_one($campaignId);
            $filename = $campaign->name . " - Call File Status ";
            $this->export_data($this->input->post('file_type'),$filename,$reportData);
        }
        
        $data['reportData'] = $reportData;
        $data['crumbs'] = $this->crumbs . ' > Reports > Call File Status';
        $data['meta_title'] = 'Call File Status';
        $data['title'] = 'Call File Status';
        $data['main'] = 'dialer/reports/call_file_status';

        $this->load->vars($data);
        $this->load->view('layout');

    }
      
    public function manually_added_contacts(){
        if($this->session->userdata('user_type') == 'agent'){
            redirect('/dialer/campaigns');
        }else if($this->session->userdata('user_type') == 'qa'){
            redirect('/dialer/leads');
        }else{
            redirect('/users/profile');
        }exit;
        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');
        $isAuthorized = IsAdminAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        $report_data = $this->Reports_model->get_created_contacts();
        $data['report'] = $report_data;
        $data['crumbs'] = $this->crumbs . ' > Reports > Manually Added Contacts';
        $data['meta_title'] = 'Manually Added Contacts Report';
        $data['title'] = 'Manually Added Contacts Report';
        $data['main'] = 'dialer/reports/manually_added_contacts';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function manually_added_contacts_campaign($campaign_id){
        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');
        $isAuthorized = IsAdminAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        $report_data = $this->Reports_model->get_created_contacts_by_campaign($campaign_id);
        $data['report'] = $report_data;
        $data['campaign_id'] = $campaign_id;
        $data['crumbs'] = $this->crumbs . ' > Reports > Manually added contacts of Campaign ' . $campaign_id;
        $data['meta_title'] = 'Manually added contacts of Campaign ' . $campaign_id;
        $data['title'] = 'Manually added contacts of Campaign ' . $campaign_id;
        $data['main'] = 'dialer/reports/manually_added_contacts_campaign';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    public function export_manually_added_contacts(){
        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');
        $isAuthorized = IsAdminAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        $file_type = !empty($this->input->post('csv_x')) ? 'csv' : (!empty($this->input->post('xls_x')) ? 'excel' : '');
        if(!empty($this->input->post('campaign_id'))){
            $campaign_id = $this->input->post('campaign_id');
            $report_data = $this->Reports_model->get_created_contacts_by_campaign($campaign_id);
        }else{
            $report_data = $this->Reports_model->get_created_contacts();
        }
        if (empty($report_data)) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'No Records found.');
            redirect('/dialer/reports/manually_added_contacts');
        }else{
            $this->export_data($file_type, "Manually Added Contacts", $report_data);
        }
    }
    
    public function upload_summary_report($page_num = 1, $sortField = 'date', $order = 'asc'){

        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');

        // check logged user have permission for access this report
        $isAuthorized = IsAdminTLManagerAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $this->load->library('pagination');

        $reports_model = new Reports_model();
        
        
        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');

        // if from date/to date empty should be today Date
        $this->get_date_range_data($pass_to_date, $pass_from_date);

        $searchBy = $this->input->post();

        // run this script while user filter any field values
        if (empty($searchBy) && count($this->input->get()) > 0) {
            $searchBy = $this->input->get();
            $_POST['campaign'] = $this->input->get('campaign');
        }
        
       if (!empty($this->input->post('csv_y')) || !empty($this->input->post('xls_y'))) {
            $file_type = !empty($this->input->post('csv_x')) ? 'csv' : (!empty($this->input->post('xls_x')) ? 'excel' : '');
            $report = $reports_model->uploadsummary_report("", $searchBy,"","","","","",true);	
            if (empty($report)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'No Records found.');
                redirect('/dialer/reports/upload_summary_report');
            }else{
                $this->export_data($file_type, "Upload Summary", $report);
            }
        } else {
            $recs_per_page = 100;
           
            $page_number = (int)$this->input->get('per_page', TRUE);

            if (empty($page_number)) $page_number = 1;
            //$offset = ($page_number-1)*$recs_per_page;
            $offset = (int)$this->input->get('per_page', TRUE);
            $tot_records = $reports_model->uploadsummary_report($IsNumRecord = 1, $searchBy);

            // get DNC disposition data list
            $data['campaign_lists'] = $reports_model->uploadsummary_report("", $searchBy, $recs_per_page, $offset, $sortField, $order);
            
            $data['num_recs'] = $tot_records;

            //Load pagination and configure
            $this->load->library('pagination');

            $config['base_url'] = '/dialer/reports/upload_summary_report/';
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

            $this->load->model('Campaigns_model');
            $loggedUserID = $this->session->userdata('uid');
            $data['allCampaignList'] = $this->Campaigns_model->get_egcampaign_by_userLogin($user_type,$loggedUserID,1);
            $data['user_id'] = $loggedUserID;
            $data['user_type'] = $user_type;
            
            $data['crumbs'] = $this->crumbs . ' > Reports > Upload Summary';
            $data['meta_title'] = 'Upload Summary Report';
            $data['title'] = 'Upload Summary Report';
            $data['main'] = '/dialer/reports/upload_summary_history';

            $this->load->vars($data);
            $this->load->view('layout');
        }
    }
    
    public function dupes_report($page_num = 1, $sortField = 'date', $order = 'asc', $list_history_id = ''){

        $this->load->helper('common');
        $user_type = $this->session->userdata('user_type');

        // check logged user have permission for access this report
        $isAuthorized = IsAdminTLManagerAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $this->load->library('pagination');

        $reports_model = new Reports_model();
        
        $searchBy['list_history_id'] = $this->input->get('list_history_id') !== null ? $this->input->get('list_history_id') : $list_history_id;
        
        if (!empty($this->input->post('csv_y')) || !empty($this->input->post('xls_y'))) {
            $file_type = !empty($this->input->post('csv_x')) ? 'csv' : (!empty($this->input->post('xls_x')) ? 'excel' : '');
            $report = $reports_model->dupes_report("", $searchBy,"","","","","",true);	
            if (empty($report)) {
                $this->session->set_flashdata('class', 'bad');
                $this->session->set_flashdata('msg', 'No Records found.');
                redirect('/dialer/reports/dupes_report');
            }else{
                $this->export_data($file_type, "Dupes Report", $report);
            }
        } else {
            $recs_per_page = 100;
           
            $page_number = (int)$this->input->get('per_page', TRUE);

            if (empty($page_number)) $page_number = 1;
            //$offset = ($page_number-1)*$recs_per_page;
            $offset = (int)$this->input->get('per_page', TRUE);
            $tot_records = $reports_model->dupes_report($IsNumRecord = 1, $searchBy);

            // get DNC disposition data list
            $data['campaign_lists'] = $reports_model->dupes_report("", $searchBy, $recs_per_page, $offset, $sortField, $order);
            $data['list_name'] = isset($data['campaign_lists'][0]->list_name) ? $data['campaign_lists'][0]->list_name . " " : "";
            $data['num_recs'] = $tot_records;

            //Load pagination and configure
            $this->load->library('pagination');

            $config['base_url'] = '/dialer/reports/dupes_report/';
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
            $data['list_history_id'] = $searchBy['list_history_id'];
            $this->load->model('Campaigns_model');
            $loggedUserID = $this->session->userdata('uid');
            $data['allCampaignList'] = $this->Campaigns_model->get_campaign_by_userLogin($user_type,$loggedUserID,1);
            $data['user_id'] = $loggedUserID;
            $data['user_type'] = $user_type;
            
            $data['crumbs'] = $this->crumbs . ' > Reports > Dupes Report';
            $data['meta_title'] = 'Dupes Report';
            $data['title'] = 'Dupes Report';
            $data['main'] = '/dialer/reports/dupes_report';

            $this->load->vars($data);
            $this->load->view('layout');
        }
    }
    
    function export_logs()
    {
        $user_type = $this->session->userdata('user_type');
        if ($user_type != 'admin') {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }

        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');

        if (empty($pass_to_date) && empty($pass_from_date)) {
            $from_date = date('Y-m-d', time());
            $_POST['from_date'] = date('m/d/Y', time());
            $to_date = date('Y-m-d', time());
            $_POST['to_date'] = date('m/d/Y', time());
        } else {
            $from_date = date('Y-m-d', strtotime($pass_from_date));
            $to_date = date('Y-m-d', strtotime($pass_to_date));
        } 
        
        $this->load->model("Calls_model");
        $calls_model = new Calls_model();
        //extract call disposition name from calldisposition table
        $calldispositions = $calls_model->get_call_dispositions();
        foreach ($calldispositions as $calldisposition) {
            $calldisposition_arr[$calldisposition->id] = $calldisposition->name;
        }
        
        $this->load->model("Audittrail_model");
        $audittrail_model = new Audittrail_model();
        $logs = $audittrail_model->getLogs($from_date, $to_date, "tm", "download", "a.id,u.first_name, u.last_name, a.sub_module, a.qualifiers, a.log_date");
        
        //extract logs in audit_trail table
        $data['num_recs'] = count($logs);
        foreach ($logs as &$log) {
            //make filters readable
            $filters = json_decode($log['qualifiers']);
            $filtersTxt = '';
            foreach ($filters as $key => $filter) {
                if (is_array($filter)) {
                    $filtersTxt .= "<b>" . ucwords(str_replace("_", " ", $key)) . "</b>: "; 
                    foreach ($filter as $value) {
                        if ($key == "calldisposition_name") {
                            $value = $calldisposition_arr[$value];
                        }
                        $filtersTxt .= $value . "<br/>";
                    }
                } else {
                    if ($key == "calldisposition_name") {
                        $filter = isset($calldisposition_arr[$filter]) ? $calldisposition_arr[$filter] : 'ALL';
                    }
                    if ($filter != '') {
                        $filtersTxt .= "<b>" . ucwords(str_replace("_", " ", $key)) . "</b>: " . $filter . "<br/>";
                    }
                }
            }
            $log['qualifiers'] = $filtersTxt;
        }
        
        $data['logs'] = $logs;
        $data['crumbs'] = $this->crumbs . ' > Reports > Export Logs';
        $data['meta_title'] = 'Export Logs';
        $data['title'] = 'Export Logs';
        $data['main'] = 'dialer/reports/export_logs';
        $this->load->vars($data);
        $this->load->view('layout');
    }

}?>
