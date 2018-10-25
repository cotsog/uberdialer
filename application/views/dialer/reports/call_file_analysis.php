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
                    <p>Call File Analysis</p>
                </div>
                <input style="top: 75px;position: absolute;right: 90px;" type="image" onclick="export_report('excel')" src="/images/file-extension-xls-biff-icon.png" width="32" height="32"/>
                <input style="top: 75px;position: absolute;right: 46px;" type="image" onclick="export_report('csv')" src="/images/file-extension-csv-icon.png" width="32" height="32"/>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">
                    <div class="dialog-form">
                        <label style="width: 70px;">Campaign:</label>
                        <div class="styled select-dropdown">
                            <select class="team_leader_combo_report" name="campaignId" id="campaignId">
                                <?php
                                $campaignId =($this->input->post('campaignId'))?$this->input->post('campaignId'):'';
                                foreach ($campaigns as $campaigns) {
                                    if($campaignId){
                                     if ($campaigns->id == $campaignId)
                                        $selected = "selected";
                                    else
                                        $selected = "";
                                    }else{
                                        $selected = "";
                                    }
                                    echo '<option role="option" value="' . $campaigns->id . '" ' . $selected . '>' . $campaigns->name . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
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
                    <input type="hidden" name="filter_status" id="filter_status" value="<?php echo $this->input->post('filter_status'); ?>">
                    <input type="hidden" id="file_type" name="file_type" value="" />
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <?php if (!empty($reportData)) {
                foreach ($reportData as $key => $report_data_value) { ?>
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                    <thead>
                        <tr class="border_Style_none">
                            <td colspan="5" class="date_format_position">
                                <?php echo date('m/d/Y', strtotime($key)); ?>
                            </td>
                        </tr>
                        <tr style="background: #f4f4f4;">
                            <th class="text_align_left blue_label">Time Zone</th>
                            <th class="blue_label">Dials</th>
                            <th class="blue_label">Human Answer</th>
                            <th class="blue_label">Contact Rate</th>
                            <th class="blue_label">Lead Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php 
                $TimeZoneArray = array('EST','CST','MST','PST');
                    foreach ($report_data_value as $timezone_key => $date_wise_data_value) {
                        if(in_array($timezone_key,$TimeZoneArray)){
                            if(count($date_wise_data_value)>0){
                                echo '<tr class ="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                                echo "<td class='text_align_left'>".$timezone_key."</td>";?>                                
                                <td><?php echo $date_wise_data_value[0]['dials']?$date_wise_data_value[0]['dials']:0?></td>
                                <td><?php echo $date_wise_data_value[0]['human_answer']?$date_wise_data_value[0]['human_answer']:0?></td>
                                <td><?php echo $date_wise_data_value[0]['contact_rate']?$date_wise_data_value[0]['contact_rate']:0?></td>
                                <td><?php echo $date_wise_data_value[0]['lead_conversion']?$date_wise_data_value[0]['lead_conversion']:0?></td>
                                <?php echo "</tr>";                            
                            }else{
                                echo '<tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                                echo "<td class ='text_align_left'>".$timezone_key."</td>";
                                echo "<td>0</td>";
                                echo "<td>0</td>";
                                echo "<td>0</td>";
                                echo "<td>0</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    echo '<tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                    echo '<th class="text_align_left" style="background-color: #f9ebea;">Team Total</th>';
                    echo '<th>'.$report_data_value['TDials'].'</th>';
                    echo '<th>'.$report_data_value['THumanAnswer'].'</th>';
                    echo '<th>'.$report_data_value['TContactRate'].'</th>';
                    echo '<th>'.$report_data_value['TLeadConversion'].'</th>';
                    echo '</tr>';
                    echo '<tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                    echo '<th class="text_align_left" style="background-color: #f2d7d5 ">Percentage</th>';
                    echo '<th></th>';
                    echo '<th>'.$report_data_value['PHumanAnswer'].'%</th>';
                    echo '<th>'.$report_data_value['PContactRate'].'%</th>';
                    echo '<th>'.$report_data_value['PLeadConversion'].'%</th>';
                    echo '</tr>';
                ?>
                    </tbody>
                </table>
                <?php 
                }}
                else { ?>
                <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                    <div class="dashboard_no_records">No record(s) found</div>
                </div>
                <?php }?>
            </div >
            <div class="pad-15-t pad-15-lr ">
                <div>
                    <h1 style="color: red;">Time Zone Wise Gross Total </h1> 
                </div><br/>                
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th class="text_align_left  font-bold report-callfile-total-th" >Time Zone</th>
                            <th class=" font-bold report-callfile-total-th" >Total Dials</th>
                            <th class=" font-bold report-callfile-total-th">Total Human Answer</th>
                            <th class=" font-bold report-callfile-total-th" >Total Contact Rate</th>
                            <th class=" font-bold report-callfile-total-th" >Total Lead Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php  
                    $Array = array('0','1','2','3');
                    foreach ($totalArray as $key => $value) {if(in_array($key,$Array)){?>
                        <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">                            
                            <th class=" text_align_left"><?php echo $value['Time_zone'];?></th>
                            <th class=""><?php echo $value['dials'];?></th>
                            <th class=""><?php echo $value['human_answer'];?></th>
                            <th class=""><?php echo $value['contact_rate'];?></th>
                            <th class=""><?php echo $value['lead_conversion'];?></th>
                        </tr>
                    <?php }}?>
                        <tr>
                            <th class="text_align_left report-callfile-gross-total">Gross Total</th>
                            <th class="report-callfile-gross-total"><?php echo $totalArray['total_dials'];?></th>
                            <th class="report-callfile-gross-total" ><?php echo $totalArray['total_human_answer'];?></th>
                            <th class="report-callfile-gross-total"><?php echo $totalArray['total_contact_rate'];?></th>
                            <th class="report-callfile-gross-total" ><?php echo $totalArray['total_lead_conversion'];?></th>
                        <tr>                    
                    </tbody>
                </table>                   
            </div>
        </div>
    </div>
     <div class="clearfix"></div>
</section>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/call_file_analysis.js<?=$this->cache_buster?>"></script>
<script type="text/javascript">
$(document).ready(function()  {
    $('#file_type').val('');
});

function export_report(fileType) {
    if(fileType != '') {
        $('#file_type').val(fileType);
        $('#leads_btnSave').trigger('click');
        $('#file_type').val('');
    }
}
</script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>