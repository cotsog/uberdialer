<section class="section-content-main-area">
    <div class="content-main-area">
        <?php

        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        } ?>
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
                <div class="column-header" style="padding: 5px 0px; margin-bottom: 5px;">
                    <p style="float:left;"><?php echo $fullName; ?></p>
                </div>
                <div class="column-header">
                    <form method="post" name="filter_form" id="filter_form" class="dashboard_filter">
                    <p style="float:left;">
                            <?php echo date('M d, Y', time()); ?>
                            <input type="hidden" value="" name="filter_status" id="btn_filter_status">
                            <input type="hidden" value="" name="team_leader_id" id="btn_team_leader_id">
                            <input type="hidden" value="" name="from_date" id="btn_from_date">
                            <input type="hidden" value="" name="to_date" id="btn_to_date">
                            <input type="hidden" value="" name="btn_type" id="btn_type">
                    </p>
                    </form>
                    
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">
                    <?php if($this->session->userdata('user_type') == 'manager' || in_array($this->session->userdata('user_type'), $upperManagement)){?>
                    <div class="dialog-form">
                        <label style="width: 100px;">Team Leader:</label>

                        <div class="styled select-dropdown">
                            <select class="team_leader_combo" name="team_leader_id" id="team_leader_id">
                                <?php if(in_array($this->session->userdata('user_type'), $upperManagement)) { ?>
                                    <optgroup label="<?php echo $userTypes[$this->session->userdata('user_type')] ?>"></optgroup>
                                    <option value="<?php echo $this->session->userdata('uid') ; ?>"><?php echo $manager_name; ?></option>
                                <optgroup label="Manager"></optgroup>
                                    <?php
                                    if (!empty($get_all_manager_list)) {
                                        foreach ($get_all_manager_list as $members) {
                                            if ($members->id == $this->input->post('team_leader_id'))
                                                $selected = "selected";
                                            else
                                                $selected = "";

                                            echo '<option role="option" value="' . $members->id . '" ' . $selected . '>' . $members->first_name . '</option>';
                                        }
                                    }
                                    ?>
                                <?php } else if($this->session->userdata('user_type') == 'manager') {?>
                                <optgroup label="Manager"></optgroup>
                                <option value="<?php echo $this->session->userdata('uid') ; ?>"><?php echo $manager_name; ?></option>
                                <?php } ?>
                                <optgroup label="Team Leader">
                                <?php
                                if (!empty($teamMemberUserList)) {
                                    foreach ($teamMemberUserList as $teamMember) {
                                        if ($teamMember->id == $this->input->post('team_leader_id'))
                                            $selected = "selected";
                                        else
                                            $selected = "";

                                        echo '<option role="option" value="' . $teamMember->id . '" ' . $selected . '>' . $teamMember->first_name . '</option>';
                                    }
                                }
                                ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

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

            </div>
            <?php if (!empty($team_leaders_report_data)) {
                $showExpand = false;
                if($this->session->userdata('uid') == 273806){
                    $showExpand = true;
                }
            $expand_count = 0;
            foreach ($team_leaders_report_data as $key => $tl_report_data_value) { ?>

            <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th"
                   style="width: 98%;margin-left: 15px;">
                <thead>
                <tr class="border_Style_none">
                    <td colspan="5" class="date_format_position">
                        <?php echo date('M d, Y', strtotime($key)); ?>
                    </td>
                </tr>
                <tr style="background: #f4f4f4;">
                    <th class="main_label">Agent Name</th>
                    <th class="main_label">Total Dials</a></th>
                    <?php if($this->session->userdata('user_type') == 'admin'){ echo '<th class="main_label">Total Disposed</a></th>'; } ?>
                    <th class="main_label">Submitted Leads</a></th>
                    <th class="main_label">Approved Leads</a></th>
                    <?php if($showExpand){ ?><th class="main_label">Expand</th> <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tl_report_data_value as $date_key => $date_wise_data_value) { ?>
                    <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                        <td class="text_align_left"
                            title="<?php echo $date_wise_data_value['full_name']; ?>"><?php echo $date_wise_data_value['full_name']; ?></td>
                        <td class="text_align_left"><?php echo $date_wise_data_value['today_dials_count']; ?></td>
                        <?php if($this->session->userdata('user_type') == 'admin'){ echo '<td class="text_align_left">'. $date_wise_data_value['total_dials_dispo_count'] .'</td>'; } ?>
                        <td class="text_align_left"><?php echo $date_wise_data_value['today_leads_count']; ?></td>
                        <td class="text_align_left"><?php if(isset($date_wise_data_value['today_approve_leads'])){ echo $date_wise_data_value['today_approve_leads'];}else{echo '0';} ?></td>
                        <?php if($showExpand){ ?><td><a id="expand" class="toggle expand" href="#" data-cont="campaigngrid_expand_<?=$expand_count?>" data-todaydate="<?=$date_wise_data_value['today_date']?>" data-agentid="<?=$date_wise_data_value['agent_id']?>">[Expand]</a></td> <?php } ?>
                    </tr>
                    <tr class="text_display_area campaigngrid_expand_<?=$expand_count?>" id="campaigngrid" style="display:none">
                        <td colspan="5">
                            <table id="DataTables_Table_0"
                                       class="table table-striped table-bordered bootstrap-datatable datatable dataTable internalgrid"
                                       aria-describedby="DataTables_Table_0_info" align="center" style="display:none;">
                                    <tr>
                                        <th align="left" class="expand_report_title" nowrap>EG Campaign ID</th>
                                        <th align="left" class="expand_report_title" nowrap>Campaign</th>
                                        <th align="left" class="expand_report_title" nowrap>Today Dials</th>
                                        <th align="left" class="expand_report_title" nowrap>Submitted Leads</th>
                                        <th align="left" class="expand_report_title" nowrap>Approved Leads</th>
                                        <th align="left" class="expand_report_title" nowrap>Campaign Type</th>
                                    </tr>
                                </table>
                        </td>
                    </tr>
                <?php 
                        $expand_count++;
                        }
                   }
                ?>
                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th"
                       style="width: 98%;margin-left: 15px;">
                    <thead>
                    <tr class="total_area">
                        <td>Total</td>
                        <td>Total Dials: <?php echo $total_dials; ?></td>
                        <td>Submitted Leads: <?php echo $total_leads; ?></td>
                        <td>Approved Leads: <?php echo $approve_leads; ?></td>
                    </tr>

                    </thead>
                    <tbody>
                </table>
                <?php
                }
                else { ?>
                    <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                        <div class="dashboard_no_records">No record(s) found</div>
                    </div>
                <?php } ?>

                </tbody>
            </table>

        </div>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/dashboards/team_leader.js"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>

<script>
$(document).ready(function(){

    $('.expand').click(function(){

        today_date = $(this).data('todaydate');
        agent_id = $(this).data('agentid');
        cont = $(this).data('cont');
        logged_user_id = "<?=$loggedUserID?>";

        if(!$('.'+cont+' .text_display_area').length){


            $.ajax({
              url: '/dialer/dashboards/getUsersDialsPerCampaign/',
              type: 'POST',
              data: { 
                    today_date:today_date,
                    agent_id:agent_id,
                    logged_user_id:logged_user_id
                    },
              dataType: 'json',
              async: false,
              success: function(data) {

                html = "";

                $.each(data, function(i, item) {
                    if(item.today_dial_count == null){
                        item.today_dial_count = 0;
                    }
                    html = html + "<tr valign='top' class='text_display_area'>";
                    html = html + "<td align='text_align_left'>" + item.eg_campaign_id + "</td>";
                    html = html + "<td align='left'>" + item.name + "</td>";
                    html = html + "<td class='text_align_left'>" + item.today_dial_count +"</td>";
                    html = html + "<td class='text_align_left'>" + item.today_lead_count +"</td>";
                    html = html + "<td class='text_align_left'>" + item.today_approve_lead_count +"</td>";
                    html = html + "<td align='left'>" + item.type + "</td>";
                    html = html + "</tr>";
                })

                $('.'+cont).show();
               $('.'+cont+' tr').after(html);
              },
              error: function(e) {
                //called when there is an error
                //console.log(e.message);
              }
            });


        }
    });
$("#dialer_dashboard_item").addClass("active open");
    $("#dialer_dashboard").addClass("active");
});
</script>