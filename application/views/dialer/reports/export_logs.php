<section class="section-content-main-area">
    <div class="content-main-area">

            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Export Logs</p>
                </div>
            </div>

            <div class="pad-15-t pad-15-lr ">

                <div class="pad-15-l row-left-pad call-row-title">
                    <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter" style="float: left;">

                        <div class="dialog-form ">
                            <label style="width: 90px;"> From Date:</label>

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
                </div>

                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <div class="no_of_record_area"><?php if(isset($num_recs)){echo $num_recs;} ?> Record(s) found</div>
                </div>

                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">User</th>
                        <th class="aligncenter">Report</th>
                        <th class="aligncenter">Date and Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($logs)) {
                        foreach ($logs as $log) {?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $log['first_name'] . ' ' . $log['last_name']; ?></td>
                                <td class="text_align_left"><?php echo $log['sub_module'] . "<br/><a href='#filters" . $log['id'] . "'><i>View Filters</i></span><span id='filters" . $log['id'] . "' class='toggle'><br/>" . $log['qualifiers']; ?></span></td>
                                <td class="text_align_left"><?php echo date("m/d/Y H:i:s", strtotime($log['log_date'])); ?></td>
                            </tr>
                        <?php }  
                    } else { ?>
                        <tr>
                            <td colspan="3"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
                <br/><br/>
            </div>
        </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/export_logs.js<?=$this->cache_buster?>"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
    .toggle        { display: none; }
    .toggle:target { display: table-row; }
</style>