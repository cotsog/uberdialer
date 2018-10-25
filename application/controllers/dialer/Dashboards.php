<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboards extends MY_Controller
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
        $this->load->helper(array('url', 'html', 'form', 'utils'));
        $this->load->model('Dashboards_model');
    }

    public function index()
    {
        // call User Type wise dashboard Function
        if ($this->session->userdata('user_type') == 'agent') {
            $this->agentDashboard();
        }
        if ($this->session->userdata('user_type') != 'agent' && $this->session->userdata('user_type') != 'qa') {
            $this->teamLeaderDashboard($this->session->userdata('user_type'));
        }
        if ($this->session->userdata('user_type') == 'qa') {
            redirect('/dialer/leads');
        }
    }

    // Agent Dashboard
    public function agentDashboard()
    {
        $logedInUser = $this->session->userdata('uid');
        $this->load->model('Dashboards_model');
        $main_array = array();

        // Get agent wise campaigns
        $campaigns = $this->Dashboards_model->get_assign_campaign_by_agent($logedInUser);

        // set all records camaogin Wise  into Main Array
        if(count($campaigns)>0){
            foreach ($campaigns as $campaignsData) {
                $main_array[$campaignsData['name']] = $this->getAgentData($campaignsData['id']);
            }
        }
        $data['agentData'] = $main_array;
        $data['meta_title'] = 'Dashboard';
        $data['title'] = 'Dashboard';
        $data['crumbs'] = $this->crumbs.' > Agent ';
        $data['main'] = 'dialer/dashboards/agentdashboard';
        $this->load->vars($data);
        $this->load->view('layout');

    }
    public function getAgentData($campaignID){
        $logedInUser = $this->session->userdata('uid');
        $data = array();
        // Get logged in agent's Shift & set Shift time on hourly based
        $schedule = $this->session->userdata('schedule');

        if (!empty($schedule)) {
            list($start, $end) = explode('-', $schedule);
            $end = str_replace("EST", "", $end);

            $time = strtotime($start);
            $timeStop = strtotime($end);
        } else {
            $time = strtotime('9:00am');
            $timeStop = strtotime('6:00pm');
        }

        // Fetch Agent total leads / dials / Approved Leads / Rejected Leads & Follow-up Leads
        $dials = $this->Dashboards_model->getAgentDials($logedInUser,$campaignID);

        $leads = $this->Dashboards_model->get_agent_leads($logedInUser,$campaignID,'updated_at','Pending');
        $rejectedLeads = $this->Dashboards_model->get_agent_leads($logedInUser,$campaignID,'updated_at','Reject');
        $ApprovedLeads = $this->Dashboards_model->get_agent_leads($logedInUser,$campaignID,'updated_at','Approve');
        $totalFollowup = $this->Dashboards_model->get_agent_followupleads($logedInUser,$campaignID);

        $i = $totaldials = $totalLeads = $totalRejleads = $totalAprleads =  0;

        while ($time < $timeStop) {
            $d = $l = $rl = $al = 0;
            $t1 = date('g A', $time);
            $time = strtotime('+1 hour ', $time);
            $t2 = date('g A', $time);
            $data[$i]['time'] = $t1 . '-' . $t2;

            //
            /*
             *  set records according to shift time wise
             */

            // set dials array
            if (count($dials) > 0) {
                foreach ($dials as $dialsData) {
                    if ($dialsData['dial_hour'] == $t1) {
                        $d = $dialsData['TotalDials'];
                    }
                }
                $data[$i]['tDials'] = isset($d) ? $d : 0;
            } else {
                $data[$i]['tDials'] = 0;
            }

            // set Leads array
            if (count($leads) > 0) {
                foreach ($leads as $leadsData) {
                    if ($leadsData['leads_hour'] == $t1) {
                        $l = $leadsData['TotalLeads'];
                    }
                }
                $data[$i]['tLeads'] = isset($l) ? $l : 0;
            } else {
                $data[$i]['tLeads'] = 0;
            }

            // set Rejected Leads array
            if (count($rejectedLeads) > 0) {
                foreach ($rejectedLeads as $rejectedLeadsData) {
                    if ($rejectedLeadsData['leads_hour'] == $t1) {
                        $rl = $rejectedLeadsData['TotalLeads'];
                    }
                }
                $data[$i]['tRejLeads'] = isset($rl) ? $rl : 0;
            } else {
                $data[$i]['tRejLeads'] = 0;
            }

            // set Approved Leads array
            if (count($ApprovedLeads) > 0) {
                foreach ($ApprovedLeads as $approvedLeadsData) {
                    if ($approvedLeadsData['leads_hour'] == $t1) {
                        $al = $approvedLeadsData['TotalLeads'];
                    }
                }
                $data[$i]['tApLeads'] = isset($al) ? $al : 0;
            } else {
                $data[$i]['tApLeads'] = 0;
            }

            $totaldials += $data[$i]['tDials'];
            $totalLeads += $data[$i]['tLeads'];
            $totalRejleads += $data[$i]['tRejLeads'];
            $totalAprleads += $data[$i]['tApLeads'];
            $i++;
        }
        $data['Total']['TotalDials'] = $totaldials;
        $data['Total']['TotalLeads'] = $totalLeads;
        $data['Total']['TotalRejleads'] = $totalRejleads;
        $data['Total']['TotalAprleads'] = $totalAprleads;
        $data['Total']['totalFollowup'] = $totalFollowup->TotalFollowUp;

        return $data;
    }

    public function teamLeaderDashboard($user_type)
    {
        $this->load->helper('common');
            $team_leader_id = $this->input->post('team_leader_id');
            if ($team_leader_id > 0)
                $loggedUserID = $team_leader_id;
            else
                $loggedUserID = $this->session->userdata('uid');

        $isAuthorized = IsTLManagerUpperManagementAuthorized($user_type);
        if (!$isAuthorized) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'You are unauthorized person for access this page!');
            redirect('/dialer/campaigns');
        }
        $filter_status = $this->input->post('filter_status');

        if ($filter_status == 'weekly') {
            $this->WeeklyTeamLeaderData($loggedUserID, $user_type);
        } else {
            $this->DailyTeamLeaderData($loggedUserID, $user_type);
        }

    }

    function getUserDetailByID($loggedUserID)
    {
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $userDetail = $users_model->get_by_id($loggedUserID);
        $fullName = strtoupper($userDetail->first_name) . ' ' . strtoupper($userDetail->last_name);
        return $fullName;
    }

    function WeeklyTeamLeaderData($loggedUserID, $user_type)
    {
        $dashboards_model = new Dashboards_model();
        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');

        // if from date/to date empty should be today Date
        if (isset($_POST['btn_type']) && $_POST['btn_type'] == 'weekly') {
            $from_date = date('Y-m-d', strtotime("-6 day", strtotime(date('Y-m-d'))));
            $_POST['from_date'] = date('m/d/Y', strtotime($from_date));
            $to_date = date('Y-m-d', strtotime(date('Y-m-d'))); //strtotime("+1 day", time()));
            $_POST['to_date'] = date('m/d/Y', time());
        } else {
            $_POST['from_date'] = date('m/d/Y', strtotime($pass_from_date));
            $from_date = date('Y-m-d', strtotime($pass_from_date));
            $to_date = date('Y-m-d', strtotime($pass_to_date));//strtotime("+1 day",strtotime($pass_to_date)));
            $_POST['to_date'] = date('m/d/Y', strtotime($pass_to_date));
        }

        // Loop between timestamps, 24 hours at a time
        $week_date_array = array();

        for ($i = strtotime($from_date); $i <= strtotime($to_date); $i = $i + 86400) {
            $day = new DateTime(date('Y-m-d', $i));
            $current_day = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
            if($current_day < 6){
                $week_date_array[] = $day->format('Y-m-d');
            }
        }

        if (count($week_date_array) != 5) {
            $this->session->set_flashdata('class', 'bad');
            $this->session->set_flashdata('msg', 'Please select weekly date!');
            redirect('/dialer/dashboards');
        }

        $main_array = array();

        /* Get List of data by agent name */

        $dials_by_day_data = $dashboards_model->get_campaign_dials_by_day($from_date, $to_date, $loggedUserID,$user_type);

        $total_date_count_array = array();
        for ($j = 0; $j < count($week_date_array); $j++) {
            $date = $week_date_array[$j];

            $total_date_count_array[$date] = array_values(array_filter($dials_by_day_data, function ($elem) use ($date) {
                if (isset($elem['today_date'])) {
                    return $date == $elem['today_date'];
                }
            }));
        }

        $date_wise_dials_leads_sumArray = array();
        foreach ($total_date_count_array as $date_wise_key => $date_wise_value) {
            if (empty($date_wise_value)) {
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_dials'] = 0;
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_leads'] = 0;
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_approved_leads'] = 0;
            } else {
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_dials'] = array_sum(array_column($date_wise_value, 'today_dials_count'));
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_leads'] = array_sum(array_column($date_wise_value, 'today_leads_count'));
                $date_wise_dials_leads_sumArray[$date_wise_key]['date_wise_approved_leads'] = array_sum(array_column($date_wise_value, 'today_approve_leads'));
            }
        }
        $data['date_wise_dials_leads_sumArray'] = $date_wise_dials_leads_sumArray;

        if (!empty($dials_by_day_data)) {

            $date_unique_array = array_values(array_unique(array_column($dials_by_day_data, 'full_name')));

            if ($date_unique_array) {
                for ($i = 0; $i < count($date_unique_array); $i++) {
                    $name = $date_unique_array[$i];
                    $main_array[$name] = array_values(array_filter($dials_by_day_data, function ($elem) use ($name) {
                        return $name == $elem['full_name'];
                    }));
                }
            }

            $total_agent_count_array = array();
            foreach ($main_array as $key => $value) {

                $total_agent_dials = array_sum(array_column($main_array[$key], 'today_dials_count'));
                $total_agent_leads = array_sum(array_column($main_array[$key], 'today_leads_count'));
                $total_agent_approved_leads = array_sum(array_column($main_array[$key], 'today_approve_leads'));

                $total_agent_count_array[$key]['today_dials_count'] = $total_agent_dials;
                $total_agent_count_array[$key]['today_leads_count'] = $total_agent_leads;
                $total_agent_count_array[$key]['total_agent_approved_leads'] = $total_agent_approved_leads;
                for ($j = 0; $j < count($week_date_array); $j++) {
                    for ($n = 0; $n < count($main_array); $n++) {
                        $date = $week_date_array[$j];
                        $main_array[$key][$date] = array_values(array_filter($main_array[$key], function ($elem) use ($date) {
                            if (isset($elem['today_date'])) {
                                return $date == $elem['today_date'];
                            }
                        }));
                    }
                }
            }

            $data['total_agent_count_array'] = $total_agent_count_array;

            if (!empty($main_array)) {
                foreach ($main_array as $key => $dials_date_array) {
                    for ($j = 0; $j < count($main_array[$key]); $j++) {
                        if (isset($main_array[$key][$j])) {
                            unset($main_array[$key][$j]);
                        }
                    }
                }
            }
        }

        /* Get List of data by Campaign name */

        $agent_unique_array = array_values(array_unique(array_column($dials_by_day_data, 'agent_id')));
        $sub_campaign_main_array = array();

        if (!empty($agent_unique_array)) {

            $this->load->helper('common');
            $getCSV_campaign_string = getCSVFromArrayElement($agent_unique_array);
            if (!empty($getCSV_campaign_string)) {
                $campaign_agent_list = $dashboards_model->get_campaign_by_agent($from_date, $to_date, $getCSV_campaign_string,$loggedUserID,$user_type);
            }

            $campaign_agent_lead_count_list = $dashboards_model->get_agent_detail_by_campaign($from_date, $to_date, $getCSV_campaign_string,$loggedUserID,$user_type);

            if (!empty($campaign_agent_lead_count_list)) {
                if ($date_unique_array) {
                    for ($i = 0; $i < count($date_unique_array); $i++) {
                        $name = $date_unique_array[$i];
                        $sub_campaign_main_array[$name] = array_values(array_filter($campaign_agent_lead_count_list, function ($elem) use ($name) {
                            return $name == $elem['full_name'];
                        }));
                    }
                }
            }

            $campaign_id_unique_array = array_values(array_unique(array_column($campaign_agent_list, 'name')));

            if (!empty($campaign_id_unique_array)) {
                foreach ($campaign_id_unique_array as $key => $value) {
                    foreach ($sub_campaign_main_array as $sub_key => $sub_dials_date_array) {
                        $sub_campaign_main_array[$sub_key][$value] = array_values(array_filter($sub_campaign_main_array[$sub_key], function ($elem) use ($value) {
                            if (isset($elem['name'])) {
                                return $elem['name'] == $value;
                            }
                        }));
                    }
                }

                foreach ($sub_campaign_main_array as $sub_key => $sub_dials_date_array) {
                    foreach ($sub_campaign_main_array[$sub_key] as $unset_key => $unset_value) {
                        if (is_int($unset_key)) {
                            unset($sub_campaign_main_array[$sub_key][$unset_key]);
                        }
                    }
                }
            }

            if (!empty($sub_campaign_main_array)) {
                foreach ($sub_campaign_main_array as $key => $sub_dials_date_array) {
                    foreach ($sub_dials_date_array as $campaign_key => $campaign_value) {

                        if (!empty($campaign_value)) {
                            for ($j = 0; $j < count($week_date_array); $j++) {
                                $date = $week_date_array[$j];
                                $sub_campaign_main_array[$key][$campaign_key][$date] = array_values(array_filter($campaign_value, function ($elem) use ($date) {
                                    if (isset($elem['today_date'])) {
                                        return $date == $elem['today_date']; //&& ($elem['name'] == $key);
                                    }
                                }));
                            }
                          }else{
                            for ($j = 0; $j < count($week_date_array); $j++) {
                                $date = $week_date_array[$j];
                                $sub_campaign_main_array[$key][$campaign_key][$date] = array();
                            }
                        }
                    }
                }
                }

            if (!empty($campaign_id_unique_array)) {
                    foreach ($sub_campaign_main_array as $sub_key => $sub_campaign_dials_date_array) {
                        foreach ($sub_campaign_dials_date_array as $campaign_key => $value) {
                        foreach ($value as $unset_campaign_key => $date_value) {
                            if (is_int($unset_campaign_key)) {
                                unset($sub_campaign_main_array[$sub_key][$campaign_key][$unset_campaign_key]);
                            }
                        }
                    }
                }
            }
        }
            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        if ($user_type == 'manager') {

            $this->load->helper('common');
            $csv_tm_ofc = getCSVFromArrayElement((array)$logged_tm_office);
            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true,$csv_tm_ofc);
            $breadCrumb = 'Manager';
            $data['manager_name'] = $this->getUserDetailByID($this->session->userdata('uid'));
        }else  if ($user_type == 'admin') {
            $breadCrumb = 'TM Admin';
            $data['get_all_manager_list'] = $dashboards_model->getAllManagerList();
            $data['manager_name'] = $this->getUserDetailByID($this->session->userdata('uid'));
            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true);
        }else{
            $breadCrumb = 'Team Leader';
        }

        $data['fullName'] = $this->getUserDetailByID($loggedUserID);
        $data['team_leaders_report_data'] = $main_array;
        $data['sub_campaign_main_array'] = $sub_campaign_main_array;
        $data['WeekDateArray'] = $week_date_array;
        $data['crumbs'] = $this->crumbs . ' > '.$breadCrumb.'';
        $data['meta_title'] = $breadCrumb;
        $data['title'] = $breadCrumb;
        $data['main'] = 'dialer/dashboards/team_dashboard_weekly';
        $this->load->vars($data);
        $this->load->view('layout');
    }

    function DailyTeamLeaderData($loggedUserID, $user_type)
    {
        $dashboards_model = new Dashboards_model();
        $pass_from_date = $this->input->post('from_date');
        $pass_to_date = $this->input->post('to_date');

        if ((isset($_POST['btn_type']) && $_POST['btn_type'] == 'daily') || empty($_POST)) {
            $from_date = date('Y-m-d', time());
            $_POST['from_date'] = date('m/d/Y', time());
            $to_date = date('Y-m-d', time());
            $_POST['to_date'] = date('m/d/Y', time());
        } else {
            $from_date = date('Y-m-d', strtotime($pass_from_date));
            $to_date = date('Y-m-d', strtotime($pass_to_date));
        }
        $main_array = array();

        $total_dials = 0;
        $total_leads = 0;
        $approve_leads = 0;

        $dials_by_day_data = $dashboards_model->getCampaignDialsByDay($from_date, $to_date, $loggedUserID,$user_type);

        if (!empty($dials_by_day_data)) {

            $total_dials = array_sum(array_column($dials_by_day_data, 'today_dials_count'));
            $total_leads = array_sum(array_column($dials_by_day_data, 'today_leads_count'));
            $approve_leads = array_sum(array_column($dials_by_day_data, 'today_approve_leads'));
        }

        $data['total_dials'] = $total_dials;
        $data['total_leads'] = $total_leads;
        $data['approve_leads'] = $approve_leads;

        if (!empty($dials_by_day_data)) {
            $date_unique_array = array_values(array_unique(array_column($dials_by_day_data, 'today_date')));
            if ($date_unique_array) {
                for ($i = 0; $i < count($date_unique_array); $i++) {
                    $date = $date_unique_array[$i];

                    $main_array[$date] = array_values(array_filter($dials_by_day_data, function ($elem) use ($date) {
                        return $date == $elem['today_date'];
                    }));
                }
            }

        }

            $this->load->model('Campaigns_model');
            $campaignsModel = new Campaigns_model();
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $upperManagement = $this->config->item('upper_management_types');
        if ($user_type == 'manager') {
            $breadCrumb = 'Manager';
            $this->load->helper('common');
            $subOffices = $this->session->userdata('sub_telemarketing_offices');
            $tmOffices = array($logged_tm_office);
            if(!empty($subOffices)){
                $tmOffices = array_merge($tmOffices,$subOffices);
            }
            $csv_tm_ofc = getCSVFromArrayElement($tmOffices);
            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true,$csv_tm_ofc);
            $data['manager_name'] = $this->getUserDetailByID($this->session->userdata('uid'));
        }else  if (in_array($user_type, $upperManagement)) {
            $breadCrumb = 'TM Admin';
            $data['manager_name'] = $this->getUserDetailByID($this->session->userdata('uid'));
            $data['get_all_manager_list'] = $dashboards_model->getAllManagerList();
            $data['teamMemberUserList'] = $campaignsModel->getTeamLeaderUsersList(true);
        }else{
            $breadCrumb = 'Team Leader';
        }
        $data['userTypes'] =  $this->config->item('user_types');
        $data['upperManagement'] = $upperManagement;
        $data['loggedUserID'] = $loggedUserID;
        $data['fullName'] = $this->getUserDetailByID($loggedUserID);
        $data['team_leaders_report_data'] = $main_array;

        $data['crumbs'] = $this->crumbs . ' > '.$breadCrumb.'';
        $data['meta_title'] = 'Dashboard';
        $data['title'] = 'Dashboard';
        $data['main'] = 'dialer/dashboards/team_dashboard';
        $this->load->vars($data);
        $this->load->view('layout');
    }


    function getUsersDialsPerCampaign() {

        $dashboards_model = new Dashboards_model();

        $user_type = $this->session->userdata('user_type');

        $loggedUserID = $this->input->post('logged_user_id');
        $today_date = $this->input->post('today_date');
        $agent_id = $this->input->post('agent_id');

        $main_array = $dashboards_model->get_dials_campaign_by_user2($today_date, $agent_id,$loggedUserID,$user_type);

        echo json_encode($main_array);
    }

    function realTimeMonitoring(){
        $dashboards_model = new Dashboards_model();
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $this->load->model('Offices_model');
        $officesModel = new Offices_model();
        $breadCrumb = 'Real-time Monitoring';
        $loggedInUserType = $this->session->userdata('user_type');
        $logged_tm_office = $this->session->userdata('telemarketing_offices');
        $get_agent_counts = $get_agents_calls_today = array();
        $data['selected_site'] =  '';
        $data['selected_managers'] = $data['selected_team_leaders'] = '';
        if($this->input->post()) {
            $data['selected_site'] = $this->input->post('tm_site');
            $data['selected_managers'] = $this->input->post('tm_managers');
            $data['selected_team_leaders'] = $this->input->post('tm_team_leaders');

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $this->session->set_rtm_site = $data['selected_site'];
            $this->session->set_rtm_managers = $data['selected_managers'];
            $this->session->set_rtm_tl = $data['selected_team_leaders'];

            session_write_close(); 
        }else if( $this->session->set_rtm_site || $this->session->set_rtm_managers || $this->session->set_rtm_tl ){
            $data['selected_site'] = $this->session->set_rtm_site;
            $data['selected_managers'] = $this->session->set_rtm_managers;
            $data['selected_team_leaders'] = $this->session->set_rtm_tl;
        }
        $upperManagement = $this->config->item('upper_management_types');
        if(in_array($loggedInUserType, $upperManagement)) {
            $data['tm_offices'] = $officesModel->get_all('is_active = 1','name AS `office`');
            if($this->input->post() || $this->session->set_rtm_site || $this->session->set_rtm_managers || $this->session->set_rtm_tl ) {
                $get_agent_counts = $dashboards_model->get_agent_counts($loggedInUserType, $data['selected_site'], implode(',', $data['selected_team_leaders']));
                $get_agents_calls_today = $dashboards_model->get_agent_calls_today($loggedInUserType, $data['selected_site'], implode(',', $data['selected_team_leaders']));
                //re-arrange list according to higlighted rows
                if(!empty($get_agents_calls_today)){
                    $idle_bg_color = "background-color:#FF0000;color:#FFFFFF";
                    $incall_bg_color = "background-color:#FFFF00;color:#000000";
                    $highlighted_idle_agent = array();
                    $highlighted_incall_agent = array();
                    $regular_agent = array();
                    foreach ($get_agents_calls_today as $call_detail){
                        $get_agent_state_and_id = $call_detail->agent_state;
                        $get_agent_state_and_id = explode("_", $get_agent_state_and_id);
                        $call_detail->agent_state = (isset($get_agent_state_and_id[0])) ?  $get_agent_state_and_id[0] : '';
                        $call_detail->call_id = (isset($get_agent_state_and_id[1])) ?  $get_agent_state_and_id[1] : '';
                        $latest_call_duration_to_arr = explode( ":", $call_detail->latest_call_duration );
                        $latest_call_duration_to_m = isset( $latest_call_duration_to_arr[0] ) ? sprintf( '%02d', $latest_call_duration_to_arr[0] ) : '00';
                        $latest_call_duration_to_s = isset( $latest_call_duration_to_arr[1] ) ? sprintf( '%02d', $latest_call_duration_to_arr[1] ) : '00';
                        $latest_call_duration_to_num = $latest_call_duration_to_m . $latest_call_duration_to_s;
                        if( strtolower( $call_detail->agent_state ) == 'idle' ){
                            if( $latest_call_duration_to_num > 200 ){
                                $call_detail->bg_color = $idle_bg_color;
                                $highlighted_idle_agent[] = $call_detail;
                            }else{
                                $call_detail->bg_color = '';
                                $regular_agent[] = $call_detail;
                            }
                        }else{
                             if( $latest_call_duration_to_num > 500 ){
                                $call_detail->bg_color = $incall_bg_color;
                                $highlighted_incall_agent[] = $call_detail;
                            }else{
                                $call_detail->bg_color = '';
                                $regular_agent[] = $call_detail;
                            }
                        }
                    }
                    $get_agents_calls_today = array_merge( $highlighted_idle_agent, $highlighted_incall_agent, $regular_agent );
                }
            }
        } elseif($loggedInUserType == 'manager') {
            $data['tm_team_leaders'] = $users_model->get_by_site($logged_tm_office,'manager');
            if($this->input->post() || !empty( $this->session->set_rtm_site ) || !empty( $this->session->set_rtm_managers ) || !empty( $this->session->set_rtm_tl )) {
                $data['selected_team_leaders'] = $data['selected_team_leaders'];
                $get_agent_counts = $dashboards_model->get_agent_counts($loggedInUserType, $logged_tm_office, implode(',', $data['selected_team_leaders']));
                $get_agents_calls_today = $dashboards_model->get_agent_calls_today($loggedInUserType, $logged_tm_office, implode(',', $data['selected_team_leaders']));
                //re-arrange list according to higlighted rows
                if(!empty($get_agents_calls_today)){
                    $idle_bg_color = "background-color:#FF0000;color:#FFFFFF";
                    $incall_bg_color = "background-color:#FFFF00;color:#000000";
                    $highlighted_idle_agent = array();
                    $highlighted_incall_agent = array();
                    $regular_agent = array();
                    foreach ($get_agents_calls_today as $call_detail){
                        $get_agent_state_and_id = $call_detail->agent_state;
                        $get_agent_state_and_id = explode("_", $get_agent_state_and_id);
                        $call_detail->agent_state = (isset($get_agent_state_and_id[0])) ?  $get_agent_state_and_id[0] : '';
                        $call_detail->call_id = (isset($get_agent_state_and_id[1])) ?  $get_agent_state_and_id[1] : '';
                        $latest_call_duration_to_arr = explode( ":", $call_detail->latest_call_duration );
                        $latest_call_duration_to_m = isset( $latest_call_duration_to_arr[0] ) ? sprintf( '%02d', $latest_call_duration_to_arr[0] ) : '00';
                        $latest_call_duration_to_s = isset( $latest_call_duration_to_arr[1] ) ? sprintf( '%02d', $latest_call_duration_to_arr[1] ) : '00';
                        $latest_call_duration_to_num = $latest_call_duration_to_m . $latest_call_duration_to_s;
                        if( strtolower( $call_detail->agent_state ) == 'idle' ){
                            if( $latest_call_duration_to_num > 200 ){
                                $call_detail->bg_color = $idle_bg_color;
                                $highlighted_idle_agent[] = $call_detail;
                            }else{
                                $call_detail->bg_color = '';
                                $regular_agent[] = $call_detail;
                            }
                        }else{
                             if( $latest_call_duration_to_num > 500 ){
                                $call_detail->bg_color = $incall_bg_color;
                                $highlighted_incall_agent[] = $call_detail;
                            }else{
                                $call_detail->bg_color = '';
                                $regular_agent[] = $call_detail;
                            }
                        }
                    }
                    $get_agents_calls_today = array_merge( $highlighted_idle_agent, $highlighted_incall_agent, $regular_agent );
                }
            }
        } elseif($loggedInUserType == 'team_leader') {
            $get_agent_counts = $dashboards_model->get_agent_counts($loggedInUserType, $logged_tm_office, $this->session->userdata('uid'));
            $get_agents_calls_today = $dashboards_model->get_agent_calls_today($loggedInUserType, $logged_tm_office, $this->session->userdata('uid'));
            //re-arrange list according to higlighted rows
            if(!empty($get_agents_calls_today)){
                $idle_bg_color = "background-color:#FF0000;color:#FFFFFF";
                $incall_bg_color = "background-color:#FFFF00;color:#000000";
                $highlighted_idle_agent = array();
                $highlighted_incall_agent = array();
                $regular_agent = array();
                foreach ($get_agents_calls_today as $call_detail){
                    $get_agent_state_and_id = $call_detail->agent_state;
                    $get_agent_state_and_id = explode("_", $get_agent_state_and_id);
                    $call_detail->agent_state = (isset($get_agent_state_and_id[0])) ?  $get_agent_state_and_id[0] : '';
                        $call_detail->call_id = (isset($get_agent_state_and_id[1])) ?  $get_agent_state_and_id[1] : '';
                    $latest_call_duration_to_arr = explode( ":", $call_detail->latest_call_duration );
                    $latest_call_duration_to_m = isset( $latest_call_duration_to_arr[0] ) ? sprintf( '%02d', $latest_call_duration_to_arr[0] ) : '00';
                    $latest_call_duration_to_s = isset( $latest_call_duration_to_arr[1] ) ? sprintf( '%02d', $latest_call_duration_to_arr[1] ) : '00';
                    $latest_call_duration_to_num = $latest_call_duration_to_m . $latest_call_duration_to_s;
                    if( strtolower( $call_detail->agent_state ) == 'idle' ){
                        if( $latest_call_duration_to_num > 200 ){
                            $call_detail->bg_color = $idle_bg_color;
                            $highlighted_idle_agent[] = $call_detail;
                        }else{
                            $call_detail->bg_color = '';
                            $regular_agent[] = $call_detail;
                        }
                    }else{
                         if( $latest_call_duration_to_num > 500 ){
                            $call_detail->bg_color = $incall_bg_color;
                            $highlighted_incall_agent[] = $call_detail;
                        }else{
                            $call_detail->bg_color = '';
                            $regular_agent[] = $call_detail;
                        }
                    }
                }
                $get_agents_calls_today = array_merge( $highlighted_idle_agent, $highlighted_incall_agent, $regular_agent );
            }
        }
        $data['upperManagement'] = $upperManagement;
        $data['agents_counts'] = $get_agent_counts;
        $data['agents_calls_today'] = $get_agents_calls_today;
        $data['user_type'] = $loggedInUserType;
        $data['crumbs'] = $this->crumbs . ' > '.$breadCrumb.'';
        $data['meta_title'] = $breadCrumb;
        $data['title'] = $breadCrumb;
        $data['main'] = 'dialer/dashboards/real_time_monitoring';
        $this->load->vars($data);
        $this->load->view('layout');

    }

    function get_mgr_tl() {
        $this->load->model('Users_model');
        $users_model = new Users_model();
        $tm_site = $this->input->post('site_name');
        $all = 0;
        if( $this->session->set_rtm_site ){
            if( $tm_site != $this->session->set_rtm_site ){
                $all = 1;
            }
        }
        $managers_tls = array('manager' => '', 'team_leader' => '');
        $mgr_tl = $users_model->get_by_site($tm_site);

        foreach($mgr_tl as $user) {
            //set all option to selected when site has been changed
            if( $all ){
                $managers_tls[$user->user_type] .= '<option selected="selected" value="'.$user->id.'">'.$user->name.'</option>';
                continue;
            }
            
            //set selected team leader and managers based on session
            if( $user->user_type == 'team_leader' ){
                if( $this->session->set_rtm_tl ){
                    if( $user->user_type == 'team_leader' && in_array( $user->id, $this->session->set_rtm_tl ) ){
                        $managers_tls[$user->user_type] .= '<option selected="selected" value="'.$user->id.'">'.$user->name.'</option>';
                    }else{
                        $managers_tls[$user->user_type] .= '<option value="'.$user->id.'">'.$user->name.'</option>';
                    }
                }else{
                    $managers_tls[$user->user_type] .= '<option selected="selected" value="'.$user->id.'">'.$user->name.'</option>';
                }
            }else{    
                if( $this->session->set_rtm_managers ){
                    if( $user->user_type == 'manager' && in_array( $user->id, $this->session->set_rtm_managers ) ){
                        $managers_tls[$user->user_type] .= '<option selected="selected" value="'.$user->id.'">'.$user->name.'</option>';
                    }else{
                        $managers_tls[$user->user_type] .= '<option value="'.$user->id.'">'.$user->name.'</option>';
                    }
                }else{
                    $managers_tls[$user->user_type] .= '<option selected="selected" value="'.$user->id.'">'.$user->name.'</option>';
                }
            }
        }
        
        echo json_encode($managers_tls);
    }

}
