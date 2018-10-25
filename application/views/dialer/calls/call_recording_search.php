<section class="section-content-main-area">
    <?php
        $page_num = (int)$this->uri->segment(3);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(5,'asc');
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
                    <p>Call History List</p>
                </div>
                <?php if(!empty($call_recording_list)) { ?>
                <!-- <input style="top: 75px;position: absolute;right: 90px;" type="image" onclick="export_report('excel')" src="/images/file-extension-xls-biff-icon.png" width="32" height="32"/> -->
                <input style="top: 75px;position: absolute;right: 46px;" type="image" onclick="export_report('csv')" src="/images/file-extension-csv-icon.png" width="32" height="32"/>
                <?php } ?>
            </div>
            
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">  
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" action="/dialer/calls/call_history">
                    <div class="dialog-form ">
                        <label>Agent Status:</label>

                        <div class="styled select-dropdown">
                            <select name="calldisposition_name" id="calldisposition_name" style ="width:280px !important;max-width:280px !important;">
                                <?php
                                if (!empty($call_dispositions)) {
                                    $agentStatus =($this->input->post('calldisposition_name'))?$this->input->post('calldisposition_name'):'';
                                     $selected_all = "";
                                    if($agentStatus == "ALL") {
                                        $selected_all = 'selected';
                                    }
                                    echo '<option value=""> ---Select One---</option>';
                                    echo '<option value="ALL" ' . $selected_all. '>ALL</option>';
                                    foreach ($call_dispositions as $calldisposition) {
                                        if ($this->input->post('calldisposition_name') == $calldisposition->id)
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
                        <label>QA Status:</label>
                        <div class="styled select-dropdown">
                            <select name="status" id="status" autocomplete="off">
                                <?php
                                if (!empty($leadStatus)) {
                                    $postStatus =($this->input->post('status'))?$this->input->post('status'):'';
                                     $selected_all = "";
                                    if($postStatus == "ALL"){
                                             $selected_all = 'selected';
                                        }
                                        echo '<option value=""> ---Select One---</option>';
                                        echo '<option value="ALL" ' . $selected_all. '>ALL</option>';

                                    foreach ($leadStatus as $key=>$lead_status) {
                                        if($this->session->userdata('user_type') == 'qa' && $lead_status == 'In Progress')
                                            continue;
                                        
                                            if (isset($postStatus) && ($postStatus == $key))
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
                        <label> Start Date:<br/><span style="font-size: 10px">(filter by <b>Last Updated</b>)</span></label>

                        <div class="form-input date-picker">
                            <input type="text" id="start_date" name="start_date" placeholder="Start date" readonly
                                   maxlength="10" value="<?php 
                                    if ( (!empty($agentStatus) || !empty($postStatus) ) && (empty($this->input->post('start_date')) || strtotime(date('m/d/Y')) == strtotime($this->input->post('start_date')) )){
                                                echo  date('m/d/Y');
                                        }else{
                                            echo $this->input->post('start_date');
                                        } ?>"/>
                        </div>
                    </div>
                     <div class="dialog-form ">
                        <label> End Date:</label>

                        <div class="form-input date-picker">
                            <input type="text" id="end_date" name="end_date" placeholder="End date" readonly
                                    maxlength="10"
                                   value="<?php 
                                    if ( (!empty($agentStatus) || !empty($postStatus) ) && (empty($this->input->post('end_date')) || strtotime(date('m/d/Y')) == strtotime($this->input->post('end_date')) )){
                                                echo  date('m/d/Y');
                                        }else{
                                            echo $this->input->post('end_date');
                                        } ?>"/>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Telemarketer:</label>

                        <div class="styled select-dropdown">
                            <select name="telemarketer" id="telemarketer">
                                <option value=''>--Select One--</option>
                                <?php
                                foreach($telemarketerList as $tm){
                                    if ($tm->id == $this->input->post('telemarketer'))
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
                        <label>Full Name:</label>

                        <div class="form-input"><input type="text" id="contact_name" name="contact_name"  maxlength="20"
                                                       placeholder="User Name" value="<?php echo $this->input->post('contact_name'); ?>"></div>
                    </div>

                    <div class="dialog-form ">
                        <label>Company:</label>

                        <div class="form-input"><input type="text" id="company" name="company"  maxlength="20"
                                                       placeholder="Company" value="<?php echo $this->input->post('company'); ?>"></div>
                    </div>

                    <div class="dialog-form ">
                        <label>Email:</label>

                        <div class="form-input"><input type="text" id="email" name="email"  maxlength="50"
                                                       placeholder="Email" value="<?php echo $this->input->post('email'); ?>"></div>
                    </div>
                    <div class="dialog-form ">
                        <label>Campaign:</label>

                        <div class="styled select-dropdown">
                            <select name="campaign" id="campaign">
                                <option value=""> ---Select One---</option>
                                <?php
                                if (!empty($allCampaignList)) {
                                    foreach ($allCampaignList as $campaign) {
                                        if ($campaign->id == $this->input->post('campaign'))
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
                        <label>QA:</label>
                        <div class="styled select-dropdown">
                            <select name="qa" id="qa">
                                <option value=""> ---Select One---</option>

                                <?php
                                if (!empty($qaList)) {
                                    foreach ($qaList as $qa) {
                                        if ($qa->id == $this->input->post('qa'))
                                            $selected = "selected";
                                        else
                                            $selected = "";

                                        echo '<option value="' . $qa->id . '" ' . $selected . '>' .$qa->first_name.' '.$qa->last_name. '</option>';
                                    }
                                }
                                ?>

                            </select>
                        </div>
                    </div>                    
                    <?php if($this->session->userdata('user_type') == 'team_leader' || $this->session->userdata('user_type') == 'manager'){?>
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
                        <button type="button" class="general-btn" id="leads_btnClear" onclick="location.href='/dialer/calls/call_history'">Clear</button>
                    </div>
                    <div class="span12" style="margin:0 0 10px 0;float: right"></div>
                </form>
            </div>

            <?php if(!empty(array_count_values($_POST)) || !empty($this->input->get()) || !empty($this->input->post()) ) { ?>

            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="aligncenter">Campaign ID</th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Name/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">Campaign Name</a></th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Type/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">Type</a></th>
                            <th class="aligncenter">Full Name</th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Company/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">Company</a></th>
                            <th class="aligncenter">Email</th>
                            <th class="aligncenter">Dialed No.</th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Time/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">Date & Time</a></th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Agent/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">Agent</a></th>
                            <th class="aligncenter">Agent Status</th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Qa/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">QA</a></th>
                            <th class="aligncenter"><a href="<?= $base_url.$page_num?>/Status/<?=$order."?status=".$this->input->post('status')."&start_date=".$this->input->post('start_date')."&end_date=".$this->input->post('end_date')."&telemarketer=".$this->input->post('telemarketer')."&contact_name=".$this->input->post('contact_name')."&company=".$this->input->post('company')."&email=".$this->input->post('email')."&campaign=".$this->input->post('campaign')."&qa=".$this->input->post('qa');?>">QA Status</a></th>
                            <th class="aligncenter">Notes</th>
                            <th class="aligncenter">Rec. link</th>
                            <th class="aligncenter">Retrieve Recording</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($call_recording_list)){
                           
                            foreach($call_recording_list as $leadsStatus){

                                  $content_notes = limit_words($leadsStatus->notes, 5);
                                  ?>
                            <tr align="center" style="word-break: break-all">
                                <td><?php echo isset($leadsStatus->eg_campaign_id)?$leadsStatus->eg_campaign_id:''; ?></td>
                                <td><?php echo isset($leadsStatus->campaign_name)?$leadsStatus->campaign_name:''; ?></td>
                                <td><?php echo isset($leadsStatus->campaign_type)?$leadsStatus->campaign_type:''; ?></td>
                                <td><?php echo isset($leadsStatus->full_name)?$leadsStatus->full_name:'';?></td>
                                <td><?php echo isset($leadsStatus->company)?$leadsStatus->company:'';?></td>
                                <td><?php echo isset($leadsStatus->contact_email)?$leadsStatus->contact_email:'';?></td>
                                <td><?php echo isset($leadsStatus->phone)?$leadsStatus->phone:'';?></td>
                                <td><?php echo isset($leadsStatus->call_created_at)?php_datetimeformat($leadsStatus->call_created_at):'';?></td>
                                <td><?php echo isset($leadsStatus->agent_name)?$leadsStatus->agent_name:'';?></td>
                                <td><?php echo isset($leadsStatus->call_disposition)?$leadsStatus->call_disposition:'';?></td>
                                <td><?php echo isset($leadsStatus->qa_name)?$leadsStatus->qa_name:'';?></td>
                                <td><?php echo isset($leadsStatus->Status)?$leadsStatus->Status:''; ?></td>
                                <td>
                                   <p><?php echo $content_notes['start']; ?></p>
                                    <?php if ($content_notes['end']!=""): ?>
                                        <a href="javascript:void(0)"
                                           onclick="seeMoreNotes('<?php echo isset($leadsStatus->campaign_contact_id)?$leadsStatus->campaign_contact_id:''; ?>');return false;">Read more...</a>
                                    <?php endif; ?>
                                </td>
                                <td id="rec_link_<?php echo $leadsStatus->plivo_id; ?>">
                                    <?php if(!empty($leadsStatus->recording_url)){ ?>
                                        <a href="<?php echo $leadsStatus->recording_url; ?>" target="_blank">Rec</a>
                                        <br/><span><?php echo $leadsStatus->duration; ?></span>
                                    <?php }else{?>
                                        <span id = "recording_url_<?php echo $leadsStatus->plivo_id;?>">-</span>
                                        <br/><span></span>
                                    <?php }?>
                                </td>
                                <td>
                                    <?php if(empty($leadsStatus->recording_url)){ ?>
                                        <a href="javascript:" onclick="retrieve_recording('<?php echo $leadsStatus->sid; ?>','<?php echo $leadsStatus->plivo_id; ?>','<?php echo $leadsStatus->conf_sid; ?>')">Retrieve</a>
                                        <div id="message_<?php echo $leadsStatus->plivo_id; ?>"></div>
                                    <?php }?>
                                </td>
                            </tr>
                            <?php }  }
                            else{?>
                            <tr>
                                <td colspan="13"><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                            </tr>
                            <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($call_recording_list)){ if (isset($this->pagination)) {?>
               <div>
                    <div>
                        <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($call_recording_list); ?> of <?php echo $num_recs; ?> entries</div>
                    </div>
                    <div class="pagination">
                        <?php echo $page_links; ?>
                    </div>
                </div>
                <?php } } ?>
            </div>
            <?php } else {  echo '<p style="background: #d8d8d8; width: 97%;display: inline-block;
    padding: 10px; margin: 15px 15px 0 15px; font-size: 14px; font-weight: normal; "> Please select filter/s. </p>'; }?>
        </div>
    </div>
    <div id="dialog-form" title="REJECTION REASONS" class="account-detail-dialog" style="display:none;"></div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/calls/call_recording.js<?=$this->cache_buster?>"></script>
<script type="text/javascript">
$(document).ready(function()  {
    $('#file_type').val('');
});

function export_report(fileType) {
    if(fileType != '') {
        $('#file_type').val(fileType);
        $('#leadstatus_searchform').submit();
        $('#file_type').val('');
    }
}

function retrieve_recording(call_uuid, plivo_id, confSid){
    if(call_uuid != "" && plivo_id != ""){
        $.ajax({ url: "/utilities/retrieve_call_recording/"+call_uuid+"/"+plivo_id+"/"+confSid,
                data: {},
                dataType: "json",
                type: "POST",
                success: function(data)
                {
                    console.log(" == "+ call_uuid +" == "+ plivo_id);
                    if(data.recording)
                    {
                        $("#recording_url_"+plivo_id).html('<a href="' + data.recording_url + '" target="_blank">Rec</a>');
                        $("#message_"+plivo_id).html('');
                    }
                    else
                    {
                        $("#message_"+plivo_id).html('No recording found.');
                    }
                    
                },
                error: function(error){
                    console.log(error);
                },
                beforeSend:function()
                {
                    $("#message_"+plivo_id).html('<img src="https://s3.amazonaws.com/enterprise-guide/images/ajax-loader.gif" alt="Processing.." />');
                }
            });
    }else{
        alert('No recording found.');
    }
}
</script>
<style>
   td {
    word-break: break-word;
    word-wrap: break-word;
}
</style>