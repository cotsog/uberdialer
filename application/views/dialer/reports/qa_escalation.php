<section class="section-content-main-area">
    <div class="content-main-area">
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>QA Escalation</p>
                </div>
                <input style="top: 75px;position: absolute;right: 90px;" type="image" onclick="export_report('excel')" src="/images/file-extension-xls-biff-icon.png" width="32" height="32"/>
                <input style="top: 75px;position: absolute;right: 46px;" type="image" onclick="export_report('csv')" src="/images/file-extension-csv-icon.png" width="32" height="32"/>
            </div>

            <div class="pad-15-t pad-15-lr ">

                <div class="pad-15-l row-left-pad call-row-title">
                    <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">

                        <div class="dialog-form ">
                            <label style="width: 70px;"> From Date:</label>

                            <div class="form-input date-picker">
                                <input type="text" id="from_date" name="from_date" placeholder="From date" readonly
                                       maxlength="10"
                                       value="<?php echo $this->input->post('from_date'); ?>"/>
                            </div>
                        </div>
                        <div class="dialog-form ">
                            <label style="width: 60px;"> To Date:</label>

                            <div class="form-input date-picker">
                                <input type="text" id="to_date" name="to_date" placeholder="To date" readonly
                                       maxlength="10"
                                       value="<?php echo $this->input->post('to_date'); ?>"/>
                            </div>
                        </div>
                        <input type="hidden" name="filter_status" id="filter_status" value="">
                        <input type="hidden" id="file_type" name="file_type" value="" />
                        <div class="dialog-form ">
                            <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                        </div>
                        
                    </form>

                </div>

                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <div style="float:left; margin: 5px 0px 05px 20px;"><?php if(isset($num_recs)){echo $num_recs;} ?> Record(s) found</div>
                </div>

                    <table class="table table-bordered row vertical-tbl sort-th table-forty" >
                        <thead>
                            <?php if(!empty($get_total_team_array)){
                                echo '<tr style="background: #0093e7;"><th colspan="2" class="team_total_header">Team Totals</th></tr>';
                                foreach($get_total_team_array as $key=>$team_value){ ?>
                                    <tr>
                                       <td><?php echo $team_value['first_name']; ?></td>
                                       <td><?php if(isset($team_value['team_count_value'])){echo $team_value['team_count_value'];}else{echo '0';} ?></td>
                                    </tr>
                               <?php }
                            } ?>
                        </thead>
                    </table>

                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">Campaign Name</th>
                        <th class="aligncenter">Company</th>
                        <th class="aligncenter">Prospect's Name</th>
                        <th class="aligncenter">Call Disposition</a></th>
                        <th class="aligncenter">QA Notes/ Agent Infraction</a></th>
                        <th class="aligncenter">Agent's Name</a></th>
                        <th class="aligncenter">Team</th>
                        <th class="aligncenter">Date</th>
                        <th class="aligncenter">QA</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($qa_escalation)){

                        
                        foreach($qa_escalation as $key => $qa_escalation_value){
                            $content_notes = limit_words($qa_escalation_value['notes'], 10);
                            ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $qa_escalation_value['campaign_name']; ?></td>
                                <td class="text_align_left"><?php echo $qa_escalation_value['company_name']; ?></td>
                                <td class="text_align_left"><?php echo $qa_escalation_value['prospect_name']; ?></td>
                                <td><?php echo $qa_escalation_value['calldisposition_name']; ?></td>
                                <td class="break-all-word"><p><?php echo $content_notes['start']; ?></p>
                                    <?php if ($content_notes['end']!=""): ?>
                                        <a href="javascript:void(0)"
                                           onclick="seeMoreNotes('<?php echo $qa_escalation_value['lead_history_ids']; ?>');return false;">Read more...</a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $qa_escalation_value['agent_name']; ?></td>
                                <td><?php echo $qa_escalation_value['team_leader_name']; ?></td>
                                <td><?php echo $qa_escalation_value['notes_created_date']; ?></td>
                                <td><?php echo $qa_escalation_value['qa_name']; ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="9"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>
        </div>
    </div>
    <div id="dialog-form" title="Notes" class="account-detail-dialog" style="display:none; margin-top: 50px;">

    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/qa_escalation.js<?=$this->cache_buster?>"></script>
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