<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        
        $page_num = (int)$this->uri->segment(3);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(5,'asc');
        if($order_seg=='asc')$order = 'desc';else $order = 'asc';
        ?>
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>QA Approved Leads Report</p>
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
                        <input type="hidden" name="campaign_id" id="campaign_id" value="">
                        <input type="hidden" id="file_type" name="file_type" value="" />
                        <div class="dialog-form ">
                            <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                        </div>
                    </form>
                    <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                        <span class="tm-filter-msg"></span>
                        <?php if(!empty($admin_qualified_leads)){ ?>
                            <input style="top: 75px;position: absolute;right: 90px; z-index: 2;" type="image" onclick="export_report('excel')" src="/images/file-extension-xls-biff-icon.png" width="32" height="32"/>
                            <input style="top: 75px;position: absolute;right: 46px; z-index: 2;" type="image" onclick="export_report('csv')" src="/images/file-extension-csv-icon.png" width="32" height="32"/>
                            <!--<span class="tm-report-uploader" style="float: right;">
                            <a href="/dialer/reports/qualified_leads/<?php /*echo $campaign_id; */?>?file_type=excel&from_date=<?php /*echo $this->input->post('from_date'); */?>&end_date=<?php /*echo $this->input->post('to_date'); */?>">
                                <img src="/images/file-extension-xls-biff-icon.png" width="32" height="32"></a>
                            <a href="/dialer/reports/qualified_leads/<?php /*echo $campaign_id; */?>?file_type=csv&from_date=<?php /*echo $this->input->post('from_date'); */?>&end_date=<?php /*echo $this->input->post('to_date'); */?>">
                                <img src="/images/file-extension-csv-icon.png" width="32" height="32"></a>
                        </span>-->
                        <?php } ?>

                </div>
                </div>

                <div class="span12 pad-15-t" style="margin:0 0 10px 0;">
                    <div style="float:left; margin: 5px 0px 05px 20px;"><?php if(isset($num_recs)){echo $num_recs;} ?> Record(s) found</div>
                </div>

                <table id="admin_qualified_leads_report" class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                    <tr style="background: #f4f4f4;">
                        <th class="aligncenter">Unqualified Reason</th>
                        <th class="aligncenter"><a href="<?php echo $base_url ?>first_name/<?php echo $order ?>?from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">First Name</a></th>
                        <th class="aligncenter"><a href="<?php echo $base_url?>last_name/<?php echo $order?>?from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">Last Name</a></th>
                        <th class="aligncenter"><a href="<?php echo $base_url?>email/<?php echo $order?>?from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">Email</a></th>
                        <th class="aligncenter"><a href="<?php echo $base_url?>company/<?php echo $order?>?from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">Company Name</a></th>
                        <th class="aligncenter">Address</th>
                        <th class="aligncenter">Phone</th>
                        <th class="aligncenter">Job Function</th>
                        <th class="aligncenter">Job Title</th>
                        <th class="aligncenter"><a href="<?php echo $base_url?>company_size/<?php echo $order?>?from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">Company Size</a></th>
                        <th class="aligncenter">Industry</th>
                        <th class="aligncenter">Silo</th>
                        <th class="aligncenter">Resource ID</th>
                        <th class="aligncenter"><a href="<?php echo $base_url?>qualified_status/<?php echo $order?>?&from_date=<?php echo $this->input->post('from_date'); ?>&end_date=<?php echo $this->input->post('to_date'); ?>">Qualified Yes/No</a></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($admin_qualified_leads)){
                        foreach($admin_qualified_leads as $admin_qualified_leads_value){?>
                            <tr class="text_display_area" align="center" style="word-break: break-word">
                                <td><?php if($admin_qualified_leads_value['status'] == 'Reject'){?>
                                        <a style="cursor: pointer;" class= "reason_link" >
                                            <?php echo "Reason(s)" ;?>
                                        </a>
                                        <span style="display:none;" class ="reason_lead_id"><?php echo $admin_qualified_leads_value['lead_id'] ?></span>
                                    <?php }else { echo ' --- '; }?>
                                </td>

                                <td class="text_align_left"><?php echo $admin_qualified_leads_value['first_name']; ?></td>
                                <td class="text_align_left"><?php echo $admin_qualified_leads_value['last_name']; ?></td>
                                <td><?php echo $admin_qualified_leads_value['email']; ?></td>
                                <td><?php echo $admin_qualified_leads_value['company']; ?></td>
                                <td><?php echo $admin_qualified_leads_value['address']; ?></td>
                                <td><?php echo $admin_qualified_leads_value['phone']; ?></td>
                                <td><?php echo !empty($admin_qualified_leads_value['job_function']) ? $admin_qualified_leads_value['job_function'] : '---'; ?></td>
                                <td><?php echo $admin_qualified_leads_value['job_title']; ?></td>
                                <td><?php if($admin_qualified_leads_value['company_size'] == '1-9'){
                                        $admin_qualified_leads_value['company_size'] = '1 to 9';
                                    }
                                    if($admin_qualified_leads_value['company_size'] == '10-24'){
                                        $admin_qualified_leads_value['company_size'] = '10 to 24';
                                    }
                                    echo $admin_qualified_leads_value['company_size']; ?></td>
                                <td><?php echo !empty($admin_qualified_leads_value['industry']) ? $admin_qualified_leads_value['industry'] : '---'; ?></td>
                                <td><?php echo $admin_qualified_leads_value['silo']; ?></td>
                                 <td><?php if($admin_qualified_leads_value['resource_name']){?>
                                        <a style="cursor: pointer;" onClick="displayResource('<?php echo $admin_qualified_leads_value['resource_name'];?>')" >
                                            <?php  echo $admin_qualified_leads_value['resource_id'];?>
                                        </a>                                       
                                    <?php }else{ echo $admin_qualified_leads_value['resource_id'];}?>
                                </td>
                                <td><?php echo $admin_qualified_leads_value['qualified_status']; ?></td>
                            </tr>
                        <?php }  }
                    else{?>
                        <tr>
                            <td colspan="14"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table><br/><br/>

            </div>

        </div>
    <div id="dialog-form" title="" class="account-detail-dialog" style="display:none;">

    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/admin_qualified_leads.js<?=$this->cache_buster?>"></script>
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