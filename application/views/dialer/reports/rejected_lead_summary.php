<section class="section-content-main-area">
    <div class="content-main-area">

            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Rejected Lead Summary</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">

                <div class="pad-15-l row-left-pad call-row-title">
                    <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter" style="float: left;">

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

                        <div class="dialog-form ">
                            <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                        </div>
                    </form>
                    <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                        <span class="tm-filter-msg"></span>
                        <?php if(!empty($rejected_lead_summary)){ ?>
                            <form action="/dialer/reports/rejected_lead_summary" method="post"  onsubmit='return true;'>
                                <input type="hidden" name="file_type" value="excel" />
                                <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                                <input type="hidden" name="end_date" value="<?php echo $this->input->post('to_date'); ?>" />
                                <input style="top: 140px;position: absolute;right: 90px;" type="image" name="submit" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                            </form>

                            <form action="/dialer/reports/rejected_lead_summary" method="post"  onsubmit='return true;'>
                                <input type="hidden" name="file_type" value="csv" />
                                <input type="hidden" name="from_date" value="<?php echo $this->input->post('from_date'); ?>" />
                                <input type="hidden" name="end_date" value="<?php echo $this->input->post('to_date'); ?>" />
                                <input style="top: 140px;position: absolute;right: 46px;" type="image" name="submit" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                            </form>
                        <?php } ?>
                </div>
                </div>

                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <div class="no_of_record_area"><?php if(isset($num_recs)){echo $num_recs;} ?> Record(s) found</div>
                </div>

                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">Date</th>
                        <th class="aligncenter">Campaign</th>
                        <th class="aligncenter">Prospect's Name</th>
                        <th class="aligncenter">Company Name</a></th>
                        <th class="aligncenter">Reason</a></th>
                        <th class="aligncenter">Job Title</a></th>
                        <th class="aligncenter">Agent's Name</th>
                        <th class="aligncenter">QA's Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($rejected_lead_summary)){
                        foreach($rejected_lead_summary as $rejected_lead_summary_value){?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $rejected_lead_summary_value['Last_Updated']; ?></td>
                                <td class="text_align_left"><?php echo $rejected_lead_summary_value['campaign_name']; ?></td>
                                <td class="text_align_left"><?php echo $rejected_lead_summary_value['prospect_name']; ?></td>
                                <td><?php echo $rejected_lead_summary_value['company_name']; ?></td>
                                <td><?php                                     if($rejected_lead_summary_value['status'] == 'Reject'){?>
                                        <a style="cursor: pointer;" class= "reason_link" >
                                            <?php echo "Reason(s)" ;?>
                                        </a>
                                        <span style="display:none;" class ="reason_lead_id"><?php echo $rejected_lead_summary_value['lead_id'] ?></span>
                                    <?php }?>
                                </td>
                                <td><?php echo $rejected_lead_summary_value['job_title']; ?></td>
                                <td><?php echo $rejected_lead_summary_value['agent_name']; ?></td>
                                <td><?php echo $rejected_lead_summary_value['qa_name']; ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="8"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>

        </div>
    <div id="dialog-form" title="REJECTION REASONS" class="account-detail-dialog" style="display:none;">

    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/rejected_lead_summary.js<?=$this->cache_buster?>"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>