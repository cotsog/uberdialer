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
                    <p>Disposition Report</p>
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
                        <label>Disposition:</label>

                        <div class="styled select-dropdown">
                            <select name="calldisposition_name[]" id="calldisposition_name" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                if (!empty($call_dispositions)) {
                                    foreach ($call_dispositions as $calldisposition) {
                                        if (in_array($calldisposition->id, $this->input->post('calldisposition_name')))
                                            $selected = "selected";
                                        else
                                            $selected = "";

                                        echo '<option value="' . $calldisposition->id . '" ' . $selected . '>' . htmlspecialchars($calldisposition->name) . '</option>';
                                    }
                                }
                                ?>

                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Telemarketer:</label>

                        <div class="form-input"><input type="text" id="dialer" name="dialer"  maxlength="20"
                                                       placeholder="Telemarketer" value="<?php echo $this->input->post('dialer'); ?>"></div>
                    </div>

                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="leads_btnSave" onclick="location.href='/dialer/reports/disposition_report'">Clear</button>
                    </div>

                    <div class="span12" style="margin:0 0 10px 0;float: right">
                        <br>
                        <input style="top: 150px;position: absolute;right: 90px;" type="image" name="xls" value="xls" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                        <input style="top: 150px;position: absolute;right: 46px;" type="image" name="csv" value="csv" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                    </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table id="disposition_history" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/calldisposition_name/<?=$order?>">Disposition</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/campaign_id/<?=$order?>">ID</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/campaign/<?=$order?>">Campaign</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/date/<?=$order?>">Date</a></th>
                        <th class="aligncenter">Telemarketer</a></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($dnc_disposition_list)){

                        foreach($dnc_disposition_list as $dnc_disposition){
                            ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td><?php echo isset($dnc_disposition->calldisposition_name)?$dnc_disposition->calldisposition_name:''; ?></td>
                                <td><?php echo isset($dnc_disposition->campaign_id)?$dnc_disposition->campaign_id:'';?></td>
                                <td><?php echo isset($dnc_disposition->campaign_name)?$dnc_disposition->campaign_name:'';?></td>
                                <td><?php echo isset($dnc_disposition->created_at)?php_datetimeformat($dnc_disposition->created_at):'';?></td>
                                <td><?php echo isset($dnc_disposition->dialer)?$dnc_disposition->dialer:'';?></td>

                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="5"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($dnc_disposition_list)){ if (isset($this->pagination)) {?>
                    <div>
                        <div>
                            <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($dnc_disposition_list); ?> of <?php echo $num_recs; ?> entries</div>
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

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/disposition_report.js<?=$this->cache_buster?>"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>