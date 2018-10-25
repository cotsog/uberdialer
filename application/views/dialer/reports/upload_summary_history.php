<section class="section-content-main-area">
    <?php
    $page_num = (int)$this->uri->segment(3);
    if($page_num==0)$page_num=1;
    $order_seg = $this->uri->segment(6,'asc');
    if($order_seg=='asc')$order = 'desc';else $order = 'asc';
    ?>
    <div class="content-main-area">
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>Upload Summary Report</p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform">
                    <div class="dialog-form ">
                        <label> From Date:</label>

                        <div class="form-input date-picker">
                            <input type="text" id="from_date" name="from_date" placeholder="From date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('from_date'); ?>"/>
                        </div>
                    </div>

                    <div class="dialog-form ">
                        <label> To Date:</label>

                        <div class="form-input date-picker">
                            <input type="text" id="to_date" name="to_date" placeholder="To date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('to_date'); ?>"/>
                        </div>
                    </div>
                    
                    <div class="dialog-form ">
                        <label>Campaign:</label>

                        <div class="styled select-dropdown">
                            <select name="campaign[]" id="campaign" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                if (!empty($allCampaignList)) {
                                    foreach ($allCampaignList as $campaign) {
                                        if (in_array($campaign->id, $this->input->post('campaign')))
                                            $selected = "selected";
                                        else
                                            $selected = "";

                                        echo '<option value="' . $campaign->id . '" ' . $selected . '>' . htmlspecialchars($campaign->name) . '</option>';
                                    }
                                }
                                ?>

                            </select>
                        </div>
                    </div>

                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="leads_btnSave" onclick="location.href='/dialer/reports/upload_summary_report'">Clear</button>
                    </div>

                    <div class="span12" style="margin:0 0 10px 0;float: right">
                        <br>
                        <input style="top: 150px;position: absolute;right: 90px;" type="image" name="xls" value="xls" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                        <input style="top: 150px;position: absolute;right: 46px;" type="image" name="csv" value="csv" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                    </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table id="uploadsummary_history" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/created_at/<?=$order?>">Date</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/cid/<?=$order?>">Campaign ID</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/list_name/<?=$order?>">List</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/ct_uploaded/<?=$order?>">No. of Uploaded</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/ct_dupes/<?=$order?>">No. of Dupes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($campaign_lists)){

                        foreach($campaign_lists as $campaign_list){
                            $dupes_lnk = 0;
                            if( isset( $campaign_list->ct_dupes ) && $campaign_list->ct_dupes > 0 ){
                                $dupes_lnk = '<a href="/dialer/reports/dupes_report?list_history_id=' . $campaign_list->chid . '" title="View Dupes" target="blank">' . $campaign_list->ct_dupes . '</a>';
                            }
                            ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td><?php echo isset($campaign_list->created_at)?$campaign_list->created_at:''; ?></td>
                                <td><?php echo isset($campaign_list->cid)?$campaign_list->cid:''; ?></td>
                                <td><?php echo isset($campaign_list->list_name)?$campaign_list->list_name:''; ?></td>
                                <td><?php echo isset($campaign_list->ct_uploaded)?$campaign_list->ct_uploaded:''; ?></td>
                                <td><?php echo $dupes_lnk ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="5"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($campaign_lists)){ if (isset($this->pagination)) {?>
                    <div>
                        <div>
                            <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($campaign_list); ?> of <?php echo $num_recs; ?> entries</div>
                        </div>
                        <div class="pagination">
                            <?php echo $page_links; ?>
                        </div>
                    </div>
                <?php } } ?>
            </div>
        </div>
    </div>
    <div id="dialog-form" title="REJECTION REASONS" class="account-detail-dialog" style="display:none;"></div>
    <!--    <div id="dialog-form dialog-notes" title="NOTES" class="account-detail-dialog" style="display:none;"></div>-->
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/uploadsummary_report.js<?=$this->cache_buster?>"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>