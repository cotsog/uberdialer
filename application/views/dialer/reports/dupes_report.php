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
                    <p><?=$list_name?>Dupes Report</p>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform">
                    <div class="span12" style="margin:0 0 10px 0;float: right">
                        <br>
                        <input style="top: 110px;position: absolute;right: 90px;" type="image" name="xls" value="xls" src="/images/file-extension-xls-biff-icon.png" width="32" height="32" alt="Submit"/>
                        <input style="top: 110px;position: absolute;right: 46px;" type="image" name="csv" value="csv" src="/images/file-extension-csv-icon.png" width="32" height="32" alt="Submit"/>
                    </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table id="uploadsummary_history" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/email/<?=$order?>/<?=$list_history_id?>">Email</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/first_name/<?=$order?>/<?=$list_history_id?>">First Name</a></th>
                        <th class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/last_name/<?=$order?>/<?=$list_history_id?>">Last Name</a></th>
                        <th class="aligncenter" id="sort_column">List</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($campaign_lists)){

                        foreach($campaign_lists as $campaign_list){

                            ?>
                            <tr class="text_display_area" align="center" style="word-break: break-all">
                                <td><?php echo isset($campaign_list->email)?$campaign_list->email:''; ?></td>
                                <td><?php echo isset($campaign_list->first_name)?$campaign_list->first_name:''; ?></td>
                                <td><?php echo isset($campaign_list->last_name)?$campaign_list->last_name:''; ?></td>
                                <td><?php echo isset($campaign_list->dupes_list_name)?$campaign_list->dupes_list_name:''; ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="6"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
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