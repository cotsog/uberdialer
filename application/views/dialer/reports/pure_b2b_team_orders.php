<section class="section-content-main-area">
    <div class="content-main-area">
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>PureB2B TM Orders</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">
                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <span class="tm-filter-msg"></span>

                    <div class="no_of_record_area"><?php if (isset($num_recs)) {
                            echo $num_recs;
                        } ?> Record(s) found
                    </div>
                    <form action="/dialer/reports/pure_b2b_team_orders" method="post"  onsubmit='return true;'>
                        <input type="hidden" name="file_type" value="excel" />
                        <input style="top: 110px;position: absolute;right: 90px;" type="image" name="submit" src="https://s3.amazonaws.com/uberdialer/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                    </form>
                    <form action="/dialer/reports/pure_b2b_team_orders" method="post"  onsubmit='return true;'>
                        <input type="hidden" name="file_type" value="csv" />
                        <input style="top: 110px;position: absolute;right: 46px;" type="image" name="submit" src="https://s3.amazonaws.com/uberdialer/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                    </form>
                </div>
                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th"
                       style="width: 100%;table-layout: fixed;">
                    <thead>

                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">Campaign ID</th>
                        <th class="aligncenter">Campaign</a></th>
                        <th class="aligncenter">Amount of Leads Ordered</a></th>
                        <th class="aligncenter">Date Sent Call File Requested</th>
                        <th class="aligncenter">Deadline</a></th>
                        <th class="aligncenter">Materials Sent to TM Ops (Asset, CF, TM Kick Off Email, etc)</th>
                        <th class="aligncenter">Date Launch TM</a></th>
                        <th class="aligncenter">Completion Date</a></th>
                        <th class="aligncenter">QA Approved Leads</a></th>
                        <th class="aligncenter">Qualified Leads - Campaign Tab</a></th>
                    </tr>

                    </thead>
                    <tbody>
                    <?php
                    if (!empty($campaignTypeWiseArray)) {
                        foreach ($campaignTypeWiseArray as $campaign_type => $report_value) {
                            if (!empty($report_value)) { ?>
                                <tr class="border_Style_none">
                                    <td colspan="10" class="campaign_type_format_position">
                                        <?php echo $campaign_type; ?>
                                    </td>
                                </tr>
                                <?php foreach ($report_value as $pure_b2b_team_orders_value) { ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $pure_b2b_team_orders_value['eg_campaign_id']; ?></td>
                                <td><?php echo $pure_b2b_team_orders_value['name']; ?></td>
                                <td class="text_align_left"><?php echo $pure_b2b_team_orders_value['lead_goal']; ?></td>
                                <td><?php echo $pure_b2b_team_orders_value['call_filerequest_date']; ?></td>
                                <td><?php echo $pure_b2b_team_orders_value['end_date']; ?></td>
                                        <td><?php echo $pure_b2b_team_orders_value['materials_sent_to_tm_Date']; ?></td>
                                <td><?php echo $pure_b2b_team_orders_value['tm_launch_date']; ?></td>
                                <td><?php if($pure_b2b_team_orders_value['completion_date'] != '00/00/0000') echo $pure_b2b_team_orders_value['completion_date']; ?></td>
                                        <td><?php echo $pure_b2b_team_orders_value['qa_approve_leads']; ?></td>
                                <td></td>
                            </tr>
                                <?php }
                            }
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="11">
                                <div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/pure_b2b_team_orders.js"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>