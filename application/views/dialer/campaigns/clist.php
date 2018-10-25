<section class="section-content-main-area">
    <?php
        $page_num = (int)$this->uri->segment(5);
        if($page_num==0)$page_num=1;
        $order_seg = $this->uri->segment(7,'asc');
       
        if($order_seg=='asc')$order = 'desc';else $order = 'asc';
    ?>
    <div class="content-main-area">
        <div style="display:none;" >
            <?php //echo "<pre>"; print_r($_SESSION); echo "</pre>";?>
        </div> 
        <?php
            //echo "<pre>"; print_r($_POST); echo "</pre>";
            
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div id="divErrorMsg" class="error-msg good hiddendiv" style="display:none;" >
            <p><span><i class="fa fa-times-circle"></i></span> Campaign deleted successfully!
        </div>   
        <div id="ajax-content-container"></div>
        <div class="column-header query-list">
            <div class="alignleft">
                <span class="column-title">Campaigns</span>
            </div>
            <div class="icons">
                <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager'){ ?>
                    <a href="#" id="edit_c"><i class="fa fa-edit list-edit-font"></i></a>
                    <?php if($logged_user_type == 'admin'){?>
                        <a href="/dialer/campaigns/create" class="add-icon"><i class="fa add-tooltip"></i></a>
                <?php } ?>
                <?php } ?>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">  
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" action="/dialer/Campaigns/index/<?php echo $page_for;?>/">
                    <div class="dialog-form ">
                        <label>Eg. ID :</label>
                        <div class="form-input">
                            <input type="text" id="campaign_id" name="campaign_id"  maxlength="5" placeholder="Eg. Campaign ID" value="<?php echo $this->input->post('campaign_id'); ?>" onkeypress="return isNumberKey(event)">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Camp. Name :</label>
                        <div class="form-input">
                            <input type="text" id="campaign_name" name="campaign_name"  maxlength="200" placeholder="Campaign Name" value="<?php echo $this->input->post('campaign_name'); ?>">
                        </div>
                    </div>
                    <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager'){ ?> 
                    <div class="dialog-form ">
                        <label>Telemarketing Office:</label>
                        <div class="styled select-dropdown">
                            <select name="telemarketing_office" id="telemarketing_office" style ="width:150px !important;max-width:150px !important;">
                                <option role="option" value="">All</option>
                                <?php foreach ($tm_offices as $key => $office) { ?>
                                <option role="option" <?php if($this->input->post('telemarketing_office') == $office){ echo "selected"; } ?> value="<?=$key?>"><?=$office?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Type:</label>
                        <div class="styled select-dropdown">
                            <select name="campaign_type" id="campaign_type" style ="width:150px !important;max-width:150px !important;">
                                <option role="option" value="">All</option>
                                <option role="option" value="leadgen" <?php if($this->input->post('campaign_type') == "leadgen"){ echo "selected"; } ?>>Lead Gen</option>
                                <option role="option" value="cat_leads" <?php if($this->input->post('campaign_type') == "cat_leads"){ echo "selected"; } ?>>Category Leads</option>
                                <option role="option" value="iq_center" <?php if($this->input->post('campaign_type') == "iq_center"){ echo "selected"; } ?>>IQ Center</option>
                                <option role="option" value="dual_cpl" <?php if($this->input->post('campaign_type') == "dual_cpl"){ echo "selected"; } ?>>Dual CPL</option>
                                <option role="option" value="hql" <?php if($this->input->post('campaign_type') == "hql"){ echo "selected"; } ?>>HQL</option>
                                <option role="option" value="partner" <?php if($this->input->post('campaign_type') == "partner"){ echo "selected"; } ?>>Partner</option>
                                <option role="option" value="blended" <?php if($this->input->post('campaign_type') == "blended"){ echo "selected"; } ?>>Blended</option>
                                <option role="option" value="telemarketing" <?php if($this->input->post('campaign_type') == "telemarketing"){ echo "selected"; } ?>>Telemarketing</option>
                                <option role="option" value="mql" <?php if($this->input->post('campaign_type') == "mql"){ echo "selected"; } ?>>MQL</option>
                                <option role="option" value="puremql" <?php if($this->input->post('campaign_type') == "puremql"){ echo "selected"; } ?>>PureMQL</option>
                                <option role="option" value="pureresearch" <?php if($this->input->post('campaign_type') == "pureresearch"){ echo "selected"; } ?>>PureResearch</option>
                                <option role="option" value="smartleads" <?php if($this->input->post('campaign_type') == "smartleads"){ echo "selected"; } ?>>Smart Leads</option>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                    <div class="dialog-form ">
                        <label> Start Date:<br/></label>
                        <div class="form-input date-picker">
                            <input type="text" id="start_date" name="start_date" placeholder="Start date" readonly maxlength="10" value="<?php echo $this->input->post('start_date'); ?>"/>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="dialog-form ">
                        <label> End Date:</label>
                        <div class="form-input date-picker">
                            <input type="text" id="end_date" name="end_date" placeholder="End date" readonly maxlength="10" value="<?php echo set_value('end_date'); ?>"/>
                        </div>
                    </div>
                    <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                    <div class="dialog-form ">
                        <label>Ordered :</label>
                        <div class="form-input">
                            <input type="text" id="ordered" name="ordered"  maxlength="6" placeholder="Ordered" value="<?php echo $this->input->post('ordered'); ?>">
                        </div>
                    </div>
                    <?php if($page_for != 'completed'){?>
                    <div class="dialog-form ">
                        <label>TM Lead Today :</label>
                        <div class="form-input">
                            <input type="text" id="lead_today" name="lead_today"  maxlength="6" placeholder="Tm Lead Today" value="<?php echo $this->input->post('lead_today'); ?>">
                        </div>
                    </div>
                    <?php } ?>
                    <!--<div class="dialog-form ">
                        <label>QA Approved :</label>
                        <div class="form-input">
                            <input type="text" id="qa_approved" name="qa_approved"  maxlength="6" placeholder="Qa Approved" value="<?php //echo $this->input->post('qa_approved'); ?>">
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label>Rejected :</label>
                        <div class="form-input">
                            <input type="text" id="rejected" name="rejected"  maxlength="6" placeholder="Rejected" value="<?php //echo $this->input->post('rejected'); ?>">
                        </div>
                    </div>-->
                    <?php } ?>
                    <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
                    <div class="dialog-form ">
                        <label>CPL/CPA :</label>
                        <div class="form-input">
                            <input type="text" id="cpl_cpa" name="cpl_cpa"  maxlength="6" placeholder="CPL/CPA" value="<?php echo $this->input->post('cpl_cpa'); ?>">
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($page_for == 'active'){?>
                    <div class="dialog-form ">
                        <label>Status:</label>
                        <div class="styled select-dropdown">
                            <select name="status" id="status" style ="width:150px !important;max-width:150px !important;">
                                <option value="">ALL</option>
                                <option value="Active" <?php if($this->input->post('status') == "Active"){ echo "selected"; } ?>>Active</option>
                                <option value="Pending" <?php if($this->input->post('status') == "Pending"){ echo "selected"; } ?>>Pending</option>
                                <option value="Paused" <?php if($this->input->post('status') == "Paused"){ echo "selected"; } ?>>Paused</option>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                    <br>
                    
                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                    <div class="dialog-form ">
                        <button type="button" class="general-btn" id="leads_btnClear" onclick="location.href='/dialer/Campaigns/index/<?php echo $page_for;?>/'">Clear</button>
                    </div>
                </form>
            </div>
            <?php //echo $num_recs."---<pre>"; print_r($campaigns); echo "</pre>";?>
            <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'  && $logged_user_type != 'qa'){?>
            <div class="form-row clear">
                <div class="filter-type-menu jq-tab-container">
                    <ul>
                        <li class="all_type_search <?php if($page_for == 'active'){echo 'active';}?>" id="main_type_search">MAIN</li>
                        <li class="all_type_search <?php if($page_for == 'completed'){echo 'active';}?>" id="completed_type_search">COMPLETED</li>
                       
                    </ul>
                </div>
            </div>
             <?php }?>
            <div class="pad-15-t pad-15-lr ">
                <table class="table table-bordered row vertical-tbl sort-th lead_table" style="width: 100%;table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="aligncenter" style="width:4%;">&nbsp;&nbsp;<?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager'){ ?><input type="checkbox" id="select_all" name="select_all" style="display: block; margin-left: 10px;"><?php } ?></th>
                            <th class="aligncenter" style="width:5%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/ID/<?=$order?>">EG. ID</th>
                            <?php if($logged_user_type == 'team_leader' || $logged_user_type == 'agent'){ ?>
                            <th class="aligncenter" style="width:5%;">Contact</th>
                            <?php } ?>
                            <th class="aligncenter" style="width:30%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/Name/<?=$order?>">Campaign Name</th>
                            <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
                            <th class="aligncenter" style="width:10%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/Office/<?=$order?>">Telemarketing Offices</th>
                            <th class="aligncenter" style="width:11%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/Type/<?=$order?>">Type</th>
                            <?php } ?>
                            <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                            <th class="aligncenter" style="width:9%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/Start/<?=$order?>">Start</th>
                            <?php } ?>
                            <th class="aligncenter" style="width:9%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/End/<?=$order?>">End</th>
                            <th class="aligncenter" style="width:9%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/Completion/<?=$order?>">TM End Date</th>
                            <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                            <th class="aligncenter" style="width:5%;" class="aligncenter">Ordered</th>
                            <th class="aligncenter" style="width:5%;" class="aligncenter">TM Lead Today</th>
                            <th class="aligncenter" style="width:5%;" class="aligncenter">QA Approved</th>
                            <th class="aligncenter" style="width:5%;" class="aligncenter">Rejected</th>
                            <?php } ?>
                            <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
                            <th class="aligncenter" style="width:10%;" class="aligncenter" id="sort_column"><a href="<?= $base_url.$page_num?>/CPL/<?=$order?>">CPL/CPA</th>
                            <?php } ?>
                            <th class="aligncenter" style="width:8%;" class="aligncenter" id="sort_column">
                                <?php if($page_for == 'active'){?> <a href="<?= $base_url.$page_num?>/Status/<?=$order?>">Status</a> <?php }else{?>
                                    Status
                                <?php }?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //echo "<pre>"; print_r($campaigns); echo "</pre>";
                        if(!empty($campaigns)){
                            foreach($campaigns as $campaign){ ?>
                                <tr align="center" style="word-break: break-all">
                                    <td><?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager'){ ?><input type="checkbox" class="checkbox" name="cmp_chk" value="<?php echo $campaign->id; ?>" style="display:block;"><?php } ?></td>
                                    <td><?php echo $campaign->eg_campaign_id; ?></td>
                                    <?php if($logged_user_type == 'team_leader' || $logged_user_type == 'agent'){ ?>
                                    <td><?php
                                        if ($campaign->AgentSignInOut == 'Sign Out') {
                                            if ($campaign->auto_dial == 1 && $this->config->item('auto_dialer_toggle')) {
                                                echo '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/'.$campaign->campaign_id.'/'.$campaign->AgentSignInOutValue.'">'.$campaign->AgentSignInOut.'</a> / <a class="define_ellipsis_text" href="/dialer/contacts/index/'.$campaign->campaign_id.'/auto">Back to landing page</a>';
                                            } else {
                                                echo '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/'.$campaign->campaign_id.'/'.$campaign->AgentSignInOutValue.'">'.$campaign->AgentSignInOut.'</a> / <a class="define_ellipsis_text" href="/dialer/lists/index/'.$campaign->campaign_id.'">Back to call list</a>';
                                            }
                                        } else {
                                            echo '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/'.$campaign->campaign_id.'/'.$campaign->AgentSignInOutValue.'">'.$campaign->AgentSignInOut.'</a>';
                                        }
                                        ?>    
                                    </td>
                                    <?php }?>
                                    <td><?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'qa'){ echo '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/'.$campaign->id.'/">'.$campaign->name.'</a>'; }else{ echo $campaign->name;} ?></td>
                                    <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
                                    <td><?php echo $campaign->telemarketing_offices; ?></td>
                                    <td><?php echo $campaign->type; ?></td>
                                    <?php }?>
                                    <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                                    <td><?php echo $campaign->start_date; ?></td>
                                    <?php }?>
                                    <td><?php echo $campaign->end_date; ?></td>
                                    <td><?php echo $campaign->completion_date; ?></td>
                                    <?php if(in_array($logged_user_type, $upperManagement) || $logged_user_type == 'manager' || $logged_user_type == 'team_leader'){ ?>
                                    <td><?php echo $campaign->lead_goal; ?></td>
                                    <td><?php echo $campaign->total_Leads; ?></td>
                                    <td><?php echo $campaign->aprroved_leads; ?></td>
                                    <td><?php echo $campaign->rejected_leads; ?></td>
                                    <?php } ?>
                                    <?php if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){ ?>
                                    <td><?php echo $campaign->cpl; ?></td>
                                    <?php } ?>
                                    <td><?php echo $campaign->status; ?></td>
                                </tr>
                        <?php } }else{ ?>
                            <tr>
                            <td colspan= <?php if(!empty($campaigns)){ echo "14"; }else{ echo "12";} ?>><div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table><br/><br/>
                <?php if(!empty($campaigns)){ if (isset($this->pagination)) {?>
               <div>
                    <div>
                        <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $offset + 1; ?> to <?php echo $offset + count($campaigns); ?> of <?php echo $num_recs; ?> entries</div>
                    </div>
                   <div class="pagination" style="margin: 40px 0;">
                        <?php echo $page_links; ?>
                    </div>
                </div>
                <?php } } ?>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    //var campaign_type_list = '<?php //echo $campaignTypeList;?>';
    //var logged_user_type = '<?php //echo $logged_user_type;?>';
	//var campaigndata = <?php //echo $campaigns;?>;
	 <?php //if($logged_user_type != 'team_leader' && $logged_user_type != 'agent'){?>
	//var completedcampaigndata = <?php //echo $completed_campaigns;?>;
	<?php //}?>
	//var totalCampaignRecord = <?php //echo $totalCampaignRecord;?>;
</script>

<script type="text/javascript">

$(function () {
    isNumberKey('campaign_id');
    NumericTextOnlyAllowed('ordered');
    NumericTextOnlyAllowed('lead_today');
    NumericTextOnlyAllowed('qa_approved');
    NumericTextOnlyAllowed('rejected');
    $("#start_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#end_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
        dt2.datepicker('option', 'minDate', startDate);
        }
    });
    
    $("#end_date").datepicker({showAnim: 'slideDown',
        onSelect: function (date) {
        var dt1 = $('#start_date');
        var endDate = $(this).datepicker('getDate');
        dt1.datepicker('option', 'maxDate', endDate);
        }
   });
           
    $(".fa-edit").click(function () {
        var theCheckboxes = $("input[type='checkbox']").not('#cb_list,#cb_gird2,#select_all');
        if (theCheckboxes.filter(":checked").length < 1) {
            ShowAlertMessage("Please select at least one campaign for edit.");
            return false;
        }
        else if (theCheckboxes.filter(":checked").length > 1) {
            ShowAlertMessage(" Please select only one campaign at a time for editing.");
            return false;
        }
        else {
            var campaign_id = $.map($('input[name="cmp_chk"]:checked'), function(c){return c.value; });
            console.log(campaign_id);
            var camp_id = campaign_id[0];
            window.location = "/dialer/campaigns/edit/" + camp_id;
        }
    });
    
    $("#delete_campaign").click(function (){
        var theCheckboxes = $("input[type='checkbox']");
        if (theCheckboxes.filter(":checked").length == 0) {
            $(this).removeAttr("checked");
            ShowAlertMessage("Please select at least one campaign for delete.");
            return false;
        }
        ShowConfirm('Do you want to delete this campaign(s) ?', function () {
                var cID;
                var campaign_id = $.map($('input[name="cmp_chk"]:checked'), function(c){return c.value; });
                console.log(campaign_id);
                var deleteCampaignURL = "dialer/campaigns/delete";
                postData = "campaignID=" + campaign_id
                AjaxCall(deleteCampaignURL, postData, "post", "json").done(function (response) {
                    if (response.status) {
                        $("#divErrorMsg").hide();
                        $('.hiddendiv').show();
                        window.location="campaigns/index";
                    }
                    else {
                        ShowAlertMessage(response.message);
                        $(":checkbox:checked").prop('checked', false);
                        $(newGridName).jqGrid('resetSelection');
                        return false;
                    }
                });
            }
            , function () {
                $(":checkbox:checked").prop('checked', false);
                $(newGridName).jqGrid('resetSelection');
                return false;
            },
            'Remove Campaign'
        );
    });
    
    // Select all checkbox
    $("#select_all").change(function(){  //"select all" change 
        $(".checkbox").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
    });

    //".checkbox" change 
    $('.checkbox').change(function(){ 
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if(false == $(this).prop("checked")){ //if this item is unchecked
            $("#select_all").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.checkbox:checked').length == $('.checkbox').length ){
            $("#select_all").prop('checked', true);
        }
    });

});

$("#campaign_item").addClass("active open");
$("#campaign_lists").addClass("active");


$('.pagination li a').click(function() {
    $('#leadstatus_searchform').attr('action', $(this).attr('href'));
    $('#leadstatus_searchform').submit();
    return false;
});

$('#sort_column a').click(function() {
$('#leadstatus_searchform').attr('action', $(this).attr('href'));
$('#leadstatus_searchform').submit();
     return false;
});

$('.tm-report-uploader a').click(function() {
    $('#leadstatus_searchform').attr('action', $(this).attr('href'));
    $('#leadstatus_searchform').submit();
    return false;
});

$("#main_type_search").click(function (){
    window.location.href = "/dialer/campaigns/index/active";
});

$("#completed_type_search").click(function (){
    window.location = "/dialer/campaigns/index/completed";
});

</script>
<style>
   td {
    word-break: break-word;
    word-wrap: break-word;
}
</style>
