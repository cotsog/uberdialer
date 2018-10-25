<style>
    .agents {
        float:left; margin-bottom:1%; margin-right: 4%;
        margin-left: 1%;
    }   
    .agent_label{
        font-size: 11px;
    }
    .agent_data{
        font-size: 20px;
        font-weight: bold;
    }
</style>
<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Real-time Monitoring Report</p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" action="" name="realtime_monitoring_report_form" id="realtime_monitoring_report_form" class="dashboard_filter">
                    <div class="dialog-form ">
                        <label style="width: 70px;"> From Date:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="from_date" name="from_date" placeholder="From date" readonly maxlength="10" value="<?=$this->input->post('from_date')?>"/>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label style="width: 60px;"> To Date:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="to_date" name="to_date" placeholder="To date" readonly maxlength="10" value="<?=$this->input->post('to_date')?>"/>
                        </div>
                    </div>               
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                <?php if(!empty($agents_calls_by_date)){ ?>
                    <input style="top: 110px;position: absolute;right: 90px;" type="image" name="submit" src="https://s3.amazonaws.com/uberdialer/images/file-extension-xls-biff-icon.png" onclick="export_rtmr('excel')" width="32" height="32" alt="Submit"/>
                    <input style="top: 110px;position: absolute;right: 46px;" type="image" name="submit" src="https://s3.amazonaws.com/uberdialer/images/file-extension-csv-icon.png" onclick="export_rtmr('csv')" width="32" height="32" alt="Submit"/>
                    <input type="hidden" name="file_type" id="file_type" />
                </form>
                <?php } ?>
            </div>
            <div>
                <?php if(!empty($agents_calls_by_date)){ ?>
                <span class="pad-15-t pad-15-l">Daily Report Totals</span>
                <div class="pad-15-t pad-15-lr ">
                    <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                       style="width: 100%;">
                        <thead>
                            <tr style="background: #f4f4f4;">
                                <?php if($user_type == 'admin'){?><th class="blue_label">Office </th><?php } ?>
                                <th class="blue_label">Call Date</th>
                                <th class="blue_label">Agent Name</th>
                                <th class="blue_label">MM:SS</th>
                                <th class="blue_label">Campaign ID</th>
                                <th class="blue_label">Campaign Name</th>
                                <th class="blue_label">Total number of Calls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($agents_calls_by_date as $call_detail){
                                    $separate_data = array();
                                    if(!empty($call_detail['call_details'])){
                                        $separate_data = explode("|", $call_detail['call_details']);
                                    }
                                    echo('<tr>');
                                    if($user_type == 'admin'){ echo('<td>'.$call_detail['office'].'</td>');}
                                    echo('<td>'.$call_detail['call_date'].'</td>');
                                    echo('<td>'.$call_detail['agent'].'</td>');
                                    echo('<td>'.$call_detail['total_call_duration'].'</td>');
                                    echo('<td>'.$call_detail['campaign_id'].'</td>');
                                    echo('<td>'.$call_detail['campaign_name'].'</td>');
                                    echo('<td>'.$call_detail['total_count_calls'].'</td>');
                                    echo('<tr>');
                                }
                            ?>
                        </tbody>
                    </table>    
                </div>
                <?php }
                if(isset($agents_weekly_calls_by_date) && !empty($agents_weekly_calls_by_date)) { ?>
                <span class="pad-15-t pad-15-l">Weekly Report Totals</span>
                <div class="pad-15-t pad-15-lr ">
                    <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                       style="width: 100%;">
                        <thead>
                            <tr style="background: #f4f4f4;">
                                <?php if($user_type == 'admin'){?><th class="blue_label">Office </th><?php } ?>
                                <th class="blue_label">Week Number</th>
                                <th class="blue_label">Agent Name</th>
                                <th class="blue_label">MM:SS</th>
                                <th class="blue_label">Campaign ID</th>
                                <th class="blue_label">Campaign Name</th>
                                <th class="blue_label">Total number of Calls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if(!empty($agents_weekly_calls_by_date)){
                                foreach ($agents_weekly_calls_by_date as $weekly_call_detail){
                                    $separate_data = array();
                                    if(!empty($weekly_call_detail['call_details'])){
                                        $separate_data = explode("|", $weekly_call_detail['call_details']);
                                    }
                                    echo('<tr>');
                                    if($user_type == 'admin'){ echo('<td>'.$weekly_call_detail['office'].'</td>');}
                                    echo('<td>'.$weekly_call_detail['week_of_year'].'</td>');
                                    echo('<td>'.$weekly_call_detail['agent'].'</td>');
                                    echo('<td>'.$weekly_call_detail['total_call_duration'].'</td>');
                                    echo('<td>'.$weekly_call_detail['campaign_id'].'</td>');
                                    echo('<td>'.$weekly_call_detail['campaign_name'].'</td>');
                                    echo('<td>'.$weekly_call_detail['total_count_calls'].'</td>');
                                    echo('<tr>');
                                }
                            }
                            ?>
                        </tbody>
                    </table>    
                </div>
                <?php } 
                if(isset($agents_monthly_calls_by_date) && !empty($agents_monthly_calls_by_date)) { ?>
                <span class="pad-15-t pad-15-l">Monthly Report Totals</span>
                <div class="pad-15-t pad-15-lr ">
                    <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                       style="width: 100%;">
                        <thead>
                            <tr style="background: #f4f4f4;">
                                <?php if($user_type == 'admin'){?><th class="blue_label">Office </th><?php } ?>
                                <th class="blue_label">Call Month</th>
                                <th class="blue_label">Agent Name</th>
                                <th class="blue_label">MM:SS</th>
                                <th class="blue_label">Campaign ID</th>
                                <th class="blue_label">Campaign Name</th>
                                <th class="blue_label">Total number of Calls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if(!empty($agents_monthly_calls_by_date)){
                                foreach ($agents_monthly_calls_by_date as $monthly_call_detail){
                                    $separate_data = array();
                                    if(!empty($monthly_call_detail['call_details'])){
                                        $separate_data = explode("|", $monthly_call_detail['call_details']);
                                    }
                                    echo('<tr>');
                                    if($user_type == 'admin'){ echo('<td>'.$monthly_call_detail['office'].'</td>');}
                                    echo('<td>'.$monthly_call_detail['call_month'].'</td>');
                                    echo('<td>'.$monthly_call_detail['agent'].'</td>');
                                    echo('<td>'.$monthly_call_detail['total_call_duration'].'</td>');
                                    echo('<td>'.$monthly_call_detail['campaign_id'].'</td>');
                                    echo('<td>'.$monthly_call_detail['campaign_name'].'</td>');
                                    echo('<td>'.$monthly_call_detail['total_count_calls'].'</td>');
                                    echo('<tr>');
                                }
                            }
                            ?>
                        </tbody>
                    </table>    
                </div>
                <?php } ?>
            </div>
        </div>        
    </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/realtime_monitoring_report.js<?=$this->cache_buster?>"></script>