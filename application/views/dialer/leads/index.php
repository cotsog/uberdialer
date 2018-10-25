<section class="section-content-main-area">
    <?php
        $page_num = (int)$this->uri->segment(3);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(6,'asc');
       
        if($order_seg=='asc')$order = 'desc';else $order = 'asc';
        
        $status_revert = array('Reject','Duplicate Lead');
        $only_admin_revert = array('Approve','Follow-up');
    ?>
    <div class="content-main-area">
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div style="z-index: 1; position: absolute; width: 100%;" id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b" >
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header">
                    <p>Leads List</p>
                </div>
                <?php if(!empty($leadsStatusList)){ ?>
                <input style="top: 75px;position: absolute;right: 90px; z-index: 2;" type="image" onclick="export_report('excel')" src="/images/file-extension-xls-biff-icon.png" width="32" height="32"/>
                <input style="top: 75px;position: absolute;right: 46px; z-index: 2;" type="image" onclick="export_report('csv')" src="/images/file-extension-csv-icon.png" width="32" height="32"/>
                <?php } ?>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">  
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" action="/dialer/Leads/">
                    <div class="dialog-form ">
                        <label>Status:</label>
                        <div class="styled select-dropdown">
                            <select name="status[]" id="status" autocomplete="off" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                if (!empty($leadStatus)) {
                                    $postStatus =($this->input->post('status'))?$this->input->post('status'):array('Pending');
                                    foreach ($leadStatus as $key=>$lead_status) {
                                        if($this->session->userdata('user_type') == 'qa' && $lead_status == 'In Progress')
                                            continue;
                                        
                                            if (in_array($key, $postStatus))
                                                $selected = 'selected';
                                            else
                                                $selected = "";
                                            echo '<option value="'.$key.'"' . $selected. '>' .$lead_status . '</option>';
                                    }                                    
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label> Start Date:<br/></label>
                        <div class="form-input date-picker">
                            <input type="text" id="start_date" name="start_date" placeholder="Start date" readonly
                                   maxlength="10"
                                   value="<?php echo $this->input->post('start_date'); ?>"/>
                        </div>
                    </div>
                     <div class="dialog-form ">
                        <label> End Date:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="end_date" name="end_date" placeholder="End date" readonly
                                    maxlength="10"
                                   value="<?php echo set_value('end_date'); ?>"/>
                        </div>
                    </div>
                    <div class="dialog-form">
                        <label> Filter by:</label>
                        <div class="form-input">
                            <input type="radio" name="filter_by" value="created_at" <?php echo $filterBy == "created_at" ? 'checked' : '' ?> style="display:inline-block;margin-left:0"> Time Submitted
                            <br />
                            <input type="radio" name="filter_by" value="updated_at" <?php echo $filterBy == "updated_at" ? 'checked' : '' ?> style="display:inline-block;margin-left:0"> Last Updated
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Telemarketer:</label>
                        <div class="styled select-dropdown">
                            <select name="telemarketer[]" id="telemarketer" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                foreach($telemarketerList as $tm){
                                    
                                        if ($this->input->post('telemarketer') && in_array($tm->id, $this->input->post('telemarketer')))
                                            $selected = "selected";
                                        else
                                            $selected = "";
                                    echo '<option value="'.$tm->id.'" '.$selected.'>'.$tm->first_name.' '.$tm->last_name.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Name:</label>

                        <div class="form-input">
                            <input type="text" id="contact_name" name="contact_name"  maxlength="20" placeholder="User Name" value="<?php echo $this->input->post('contact_name'); ?>">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Company:</label>
                        <div class="form-input">
                            <input type="text" id="company" name="company"  maxlength="100" placeholder="Company" value="<?php echo $this->input->post('company'); ?>">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Email:</label>
                        <div class="form-input">
                            <input type="text" id="email" name="email"  maxlength="100" placeholder="Email" value="<?php echo $this->input->post('email'); ?>">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Campaign ID:</label>
                        <div class="form-input">
                            <input type="text" id="eg_campaign_id" name="eg_campaign_id" placeholder="Campaign ID" value="<?php echo $this->input->post('eg_campaign_id'); ?>">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Campaign:</label>
                        <div class="styled select-dropdown">
                            <select name="campaign[]" id="campaign" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                if (!empty($allCampaignList)) {
                                    foreach ($allCampaignList as $campaign) {
                                        if ($this->input->post('campaign') && in_array($campaign->id, $this->input->post('campaign'))) {
                                            $selected = "selected";
                                        } else {
                                            $selected = "";
                                        }
                                        echo '<option value="' . $campaign->id . '" ' . $selected . '>' . htmlspecialchars($campaign->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>QA:</label>
                        <div class="styled select-dropdown">
                            <select name="qa[]" id="qa" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php
                                if (!empty($qaList)) {
                                    foreach ($qaList as $qa) {
                                        if ($this->input->post('qa') && in_array($qa->id, $this->input->post('qa'))) {
                                            $selected = "selected";
                                        } else {
                                            $selected = "";
                                        }
                                        echo '<option value="' . $qa->id . '" ' . $selected . '>' .$qa->first_name.' '.$qa->last_name. '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Site:</label>
                        <div class="styled select-dropdown">
                            <select name="sites[]" id="sites" multiple="multiple" style ="width:350px !important;max-width:350px !important;">
                                <?php $i = 0;
                                $selected = "";
                                if (!empty($getEGWebsitesList)) {
                                    foreach ($getEGWebsitesList as $key => $sitesList) {
                                        if ($this->input->post('sites') && in_array($key, $this->input->post('sites'))) {
                                            $selected = "selected";
                                        } else {
                                            $selected = "";
                                        }
                                        echo '<option value="' . $key . '" ' . $selected . '>' . $sitesList . '</option>';
                                        $i++;
                                    }
                                }

                                ?>
                            </select>
                        </div>
                    </div>                    
                    <?php if($this->session->userdata('user_type') == 'admin' || $this->session->userdata('user_type') == 'team_leader' || $this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'qa'){?>
                    <div class="dialog-form">
                        <label>Non-active campaigns:</label>
                        <div class="form-input">
                            <input type="checkbox" id="show_non_active" class="css-checkbox" name="show_non_active" value="1" <?php if($this->input->post('show_non_active')){echo "checked";} ?>/>  
                            <label class="css-label checkbox-label radGroup1 cst-export-lbl" for="show_non_active"></label>
                        </div>    
                    </div>
                    <?php }?>
                    <input type="hidden" id="file_type" name="file_type" value="" />
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="leads_btnClear" onclick="location.href='/dialer/Leads/'">Clear</button>
                    </div>
                </form>
            </div>
            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th lead_table" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="aligncenter" style="width:5%;"><a href="<?= $base_url.$page_num?>/Site/<?=$order?>">Site</a></th>
                            <th class="aligncenter" style="width:10%;">Campaign ID</th>
                            <th class="aligncenter" style="width:10%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Name/<?=$order?>">Campaign Name</a></th>
                            <th class="aligncenter" style="width:10%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Type/<?=$order?>">Campaign Type</a></th>
                            <th class="aligncenter" style="width:8%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/FirstName/<?=$order?>">First Name</a></th>
                            <th class="aligncenter" style="width:8%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/LastName/<?=$order?>">Last Name</a></th>
                            <th class="aligncenter" style="width:9%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Company/<?=$order?>">Company</a></th>
                            <th class="aligncenter" style="width:10%;">Email</th>
                            <th class="aligncenter" style="width:9%;">Notes</th>
                            <th class="aligncenter" style="width:5%;">Job Title</th>
                            <th class="aligncenter" style="width:6%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Agent/<?=$order?>">Agent</a></th>
                            <th class="aligncenter" style="width:5%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Qa/<?=$order?>">QA</a></th>
                            <th class="aligncenter" style="width:10%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/TimeSubmitted/<?=$order?>">Time Submitted</a></th>
                            <th class="aligncenter" style="width:9%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/LastUpdated/<?=$order?>">Last Updated</a></th>
                            <th class="aligncenter" style="width:6%;"  id="sort_column"><a href="<?= $base_url.$page_num?>/Status/<?=$order?>">Status</a></th>
                            <th class="aligncenter" style="width:10%;">Phone</th>
                            <th class="aligncenter" style="width:10%;">Rejection Reason</th>
                            <th class="aligncenter" style="width:10%;">Follow-Up Reason</th>
                            
                            <?php if((in_array($this->input->post('status'), $status_revert) && $this->session->userdata('user_type') != 'agent') || (in_array($this->input->post('status'), $only_admin_revert) && $this->session->userdata('user_type') == 'admin')){ ?>
                                <th class="vertical-middle" style="width:8%;">Revert to Pending</th>
                            <?php } ?>
                            <?php if(!empty($leadsStatusList)){?>
                            <th class="aligncenter" style="width:5%;"></th>
                            <th class="aligncenter" style="width:5%;"></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($leadsStatusList)){
                            foreach($leadsStatusList as $leadsStatus){
                                $last_updated_arr = explode( " ", $leadsStatus->Last_Updated );
                                  $content_notes = limit_words($leadsStatus->notes, 5); ?>
                                <tr align="center" style="word-break: break-all">
                                    <td><?php echo $leadsStatus->telemarketing_offices; ?></td>
                                    <td><?php echo $leadsStatus->eg_campaign_id; ?></td>
                                    <td><?php echo $leadsStatus->campaign_name; ?></td>
                                    <td><?php echo $leadsStatus->campaign_Type; ?></td>
                                    <td><?php echo $leadsStatus->first_name; ?></td>
                                    <td><?php echo $leadsStatus->last_name; ?></td>                                    
                                    <td><?php echo $leadsStatus->company; ?></td>
                                    <td><?php echo $leadsStatus->contact_email; ?></td>
                                    
                                    <td><p><?php echo $content_notes['start']; ?></p>
                                        <?php if ($content_notes['end']!=""): ?>
                                            <a href="javascript:void(0)"
                                               onclick="seeMoreNotes('<?php echo $leadsStatus->campaign_contact_id; ?>');return false;">Read more...</a>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td><?php echo $leadsStatus->job_title; ?></td>
                                    <td><?php echo $leadsStatus->agent_name; ?></td>
                                    <!-- QA Name -->
                                    <?php 
                                    if (($leadsStatus->Status == '' || $leadsStatus->Status == 'Pending') && (($leadsStatus->Time_Submitted == $leadsStatus->Last_Updated) || empty($leadsStatus->Time_Submitted)) && !$leadsStatus->is_qa_in_progress) {
                                        $leadsStatus->qa_name = '';
                                    }?>
                                    <td><?php echo $leadsStatus->qa_name; ?></td>
                                    
                                    <td><?php echo $leadsStatus->Time_Submitted; ?></td>
                                    <td><?php echo $leadsStatus->Last_Updated; ?></td>
                                    <!-- status -->
                                    <?php if ($leadsStatus->Status == '') {
                                        $lead_status = 'Pending';
                                    } else if ($leadsStatus->Status == 'Approve') {
                                        $lead_status = 'Approved';
                                    }
                                    else if ($leadsStatus->Status == 'Reject') {
                                        $lead_status = 'Rejected';
                                    }else{
                                        $lead_status = $leadsStatus->Status;
                                    }
                                    ?>
                                    <td ><?php echo $lead_status; ?></td>
                                    <td><?php echo $leadsStatus->phone; ?></td>
                                    
                                    <!-- Rejection Reason -->
                                    <td><?php 
                                    if($leadsStatus->rejection_reasons == 'Reason'){?>
                                        <a style="cursor: pointer;" class= "thelink" >
                                            <?= $leadsStatus->rejection_reasons."(s)";?>
                                        </a>  
                                        <span style="display:none;" class ="leadid"><?=$leadsStatus->lead_id ?></span>
                                    <?php }?>
                                    </td>
                                    
                                    <!-- Follow-Up Reason -->
                                    <td><?php 
                                    if($leadsStatus->followup_reasons == 'Reason'){?>
                                        <a style="cursor: pointer;" class= "thelink" >
                                            <?= $leadsStatus->rejection_reasons."(s)";?>
                                        </a>  
                                        <span style="display:none;" class ="leadid"><?=$leadsStatus->lead_id ?></span>
                                    <?php }?>
                                    </td>
                                    
                                    <!-- View Links -->
                                    <?php if((in_array($this->input->post('status'), $status_revert) && ($this->session->userdata('user_type') == 'qa' || $this->session->userdata('user_type') == 'admin')) || (in_array($this->input->post('status'), $only_admin_revert) && $this->session->userdata('user_type') == 'admin')){
                                        echo '<td class="vertical-middle"><a href="/dialer/calls/revertToPending/'.$leadsStatus->lead_id.'" onclick="return confirm(\'Are you sure you want to revert this lead?\')">[Revert]</a></td>';
                                        } ?>
                                    <td class="vertical-middle"><a href="/dialer/calls/index/<?php echo $leadsStatus->campaign_contact_id;?>/<?php echo $leadsStatus->list_id;?>/qa?action=view">View</a></td>
                                    <!-- QA Links -->
                                    <?php 
                                    $status = $this->input->post('status');
                                    if($status!='Duplicate Lead' && $status!='Approve' && $status !='Follow-up' &&  $this->session->userdata('user_type') != 'agent') {
                                       if ($leadsStatus->Status == 'Approve') {
                                            echo '<td class="line-through"></td>';
                                       }else if (($leadsStatus->qa == $user_id ) || (empty($leadsStatus->qa) && !$leadsStatus->is_qa_in_progress)) {?>
                                    <td class="qa_accepted vertical-middle"><a href="/dialer/calls/index/<?php echo $leadsStatus->campaign_contact_id;?>/<?php echo $leadsStatus->list_id;?>/qa?action=qa">QA</a></td>
                                        <?php } else {
                                            echo '<td class="line-through">[QA]</td>';
                                        }
                                    }else if($this->session->userdata('user_type') =='qa' && !$leadsStatus->edit_lead_status && $status == 'Follow-up' && ((strtotime($leadsStatus->Last_Updated) < strtotime('-7 day')) || (($leadsStatus->qa == $user_id ) || (empty($leadsStatus->qa) || !$leadsStatus->is_qa_in_progress)))){?>
                                        <td class="qa_accepted vertical-middle"><a href="/dialer/calls/index/<?php echo $leadsStatus->campaign_contact_id;?>/<?php echo $leadsStatus->list_id;?>/qa?action=qa">QA</a></td>
                                    <?php }else {
                                        echo '<td class="line-through">[QA]</td>';
                                    } ?>
                                </tr>
                            <?php }  }
                            else{?>
                            <tr>
                                <td colspan= <?php if(!empty($leadsStatusList)){ echo "14"; }else{ echo "12";} ?>><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                            </tr>
                            <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($leadsStatusList)){ if (isset($this->pagination)) {?>
               <div>
                    <div>
                        <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($leadsStatusList); ?> of <?php echo $num_recs; ?> entries</div>
                    </div>
                    <div class="pagination">
                        <?php echo $page_links; ?>
                    </div>
                </div>
                <?php } } ?>
            </div>
        </div>
    </div>
    <div id="dialog-form" title="FOLLOWUP/REJECTION REASONS" class="account-detail-dialog" style="display:none;"></div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/leads/index.js<?=$this->cache_buster?>"></script>
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
