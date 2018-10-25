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
                    <p>Call File Status</p>
                </div>
                <form action="/dialer/reports/call_file_status" method="post"  onsubmit='return true;'>
                    <input type="hidden" name="file_type" value="excel" />
                    <input type="hidden" name="campaignId" value="<?=($this->input->post('campaignId'))?$this->input->post('campaignId'):''?>" />
                    <input type="hidden" name="start_date" value="<?=($this->input->post('start_date'))?$this->input->post('start_date'):''?>" />
                    <input type="hidden" name="end_date" value="<?=($this->input->post('end_date'))?$this->input->post('end_date'):''?>" />
                    <input style="top: 110px;position: absolute;right: 90px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                </form>

                <form action="/dialer/reports/call_file_status" method="post"  onsubmit='return true;'>
                    <input type="hidden" name="file_type" value="csv" />
                    <input type="hidden" name="campaignId" value="<?=($this->input->post('campaignId'))?$this->input->post('campaignId'):''?>" />
                    <input type="hidden" name="start_date" value="<?=($this->input->post('start_date'))?$this->input->post('start_date'):''?>" />
                    <input type="hidden" name="end_date" value="<?=($this->input->post('end_date'))?$this->input->post('end_date'):''?>" />
                    <input style="top: 110px;position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                </form>
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
                            <div class="dialog-form ">
                        <label style="width: 70px;">From:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="start_date" name="start_date" placeholder="Start date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('start_date'); ?>"/>
                        </div>
                    </div>
                     <div class="dialog-form ">
                        <label style="width: 70px;">To:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="end_date" name="end_date" placeholder="End date" readonly
                                    maxlength="10"
                                   value="<?php echo set_value('end_date'); ?>"/>
                        </div>
                    </div>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                </form>
            </div>
            
            <!-- Workable Dispo -->
            <div class="pad-15-t pad-15-lr ">
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th class="blue_label" style="alignment:left"colspan="<?=count( $reportData['workable']['Agent'] ) + 1 ?>">Workable Dispositions</th>
                        </tr>
                    <?php 
                    
                    $ct = 0;
                    $total_display = 0;
                    if(count($reportData['workable'])>0){
                    foreach ( $reportData['workable'] as $agents => $dispo ) {
                        if( $ct == 0 ){
                            $ct++;?>
                             <tr style="background: #f4f4f4;">
                                <th class="blue_label"><?=$agents?></th>
                                <?php foreach( $dispo as $value ){ ?>
                                <th class="blue_label"><?=$value?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php } else{
                                //insert total rows before owner rows
                                if( ( $agents == 'PUREB2B' || $agents == '3rd Party' ) && !$total_display ){
                                    $total_display = 1;
                                    echo '<tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                                    echo '<th>TOTALS</th>';

                                    foreach ( $reportData['counts']['workable'] as $total ) {
                                        echo '<th>' . $total . '</th>';
                                    }
                                    echo '</tr>';
                                }
                            ?>
                                <tr>
                                <td><?=$agents?></td>
                                <?php foreach( $dispo as $value ){
                                ?>
                                    <td align="center"><?=$value?></td>
                                <?php
                                }
                                echo "</tr>";
                            }
                        }
                    }
                    
                ?>
                    </tbody>
                </table>
            </div >
            <!-- Non Workable Dispo -->
            <div class="pad-15-t pad-15-lr ">
                <table id="call_file_analysis_report" class="call_file_analysis_report  table table-bordered row vertical-tbl sort-th"
                   style="width: 100%;">
                     <thead>
                        <tr style="background: #f4f4f4;">
                            <th class="blue_label" style="alignment:left"colspan="<?=count( $reportData['non_workable']['Agent'] ) + 1 ?>">Non Workable Dispositions</th>
                        </tr>
                    <?php 
                    $ct = 0;
                    $total_display = 0;
                    if(count($reportData['non_workable'])>0){
                        foreach ( $reportData['non_workable'] as $agents => $dispo ) {
                            if( $ct == 0 ){
                                $ct++;
                            ?>
                            <thead>
                                <tr style="background: #f4f4f4;">
                                    <th class="blue_label"><?=$agents?></th>
                                    <?php foreach( $dispo as $value ){ ?>
                                        <th class="blue_label"><?=$value?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php } else{ 
                                //insert total rows before owner rows
                                if( ( $agents == 'PUREB2B' || $agents == '3rd Party' ) && !$total_display ){
                                        $total_display = 1;
                                        echo '<tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">';
                                        echo '<th>TOTAL</th>';

                                        foreach ( $reportData['counts']['non_workable'] as $total ) {
                                            echo '<th>' . $total . '</th>';
                                        }
                                        echo '</tr>';
                                }?>    
                                <tr>
                                <td><?=$agents?></td>
                                <?php foreach( $dispo as $value ){
                                ?>
                                    <td align="center"><?=$value?></td>
                                <?php
                                }
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div >
        </div>
    </div>
     <div class="clearfix"></div>
</section>
<script>
$(document).ready(function(){
    $("table#call_analysis_report tr:even").css("background-color", "#FFFFFF");
    $("table#call_analysis_report tr:odd").css("background-color", "#F4F4F8");
    
    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#to_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            startDate.setDate(startDate.getDate() + 30);
            //sets dt2 maxDate to the last day of 30 days window
            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
        }
    });
    $("#to_date").datepicker({showAnim: 'slideDown'}); 
    
    $("#start_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var date = $(this).datepicker('getDate');
            date.setDate(date.getDate() + 6);
            $('#end_date').datepicker('option', 'maxDate', date); // Reset minimum date
             // Add 7 days
            //$('#to_date').datepicker('setDate', date); // Set as default
        },
        onClose: function () {
            $("#end_date").datepicker(
                "change", {
                minDate: new Date($('#start_date').val())
            });
            
        }
    });

    if($("#start_date").val() != "" && $("#end_date").val() != ""){
        var date = new Date($('#start_date').val());
            date.setDate(date.getDate() + 6);
        $("#end_date").datepicker({
                showAnim: 'slideDown',
                minDate: new Date($('#start_date').val()),
                maxDate: date
        });
    }else{
        $("#end_date").datepicker({showAnim: 'slideDown'});
    }
    
});

$("#report_item").addClass("active open");
$("#call_file_status").addClass("active");

$('#form').validate({
    rules: {
        campaign_name: "required",
        weekNo: "required"
    },
    messages: {
        campaign_name: "",
        weekNo:  ""
    }    
});
</script>

<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>