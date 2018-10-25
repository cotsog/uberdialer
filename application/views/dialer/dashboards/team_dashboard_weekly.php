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
                            <button type="submit" id="daily" class="dashboard-daily dashboard-btn-default">DAILY</button>
                            <button type="submit" id="weekly" class="dashboard-weekly dashboard-btn-default">WEEKLY</button>
                            <input type="hidden" value="" name="filter_status" id="btn_filter_status">
                            <input type="hidden" value="" name="team_leader_id" id="btn_team_leader_id">
                            <input type="hidden" value="" name="from_date" id="btn_from_date">
                            <input type="hidden" value="" name="to_date" id="btn_to_date">
                            <input type="hidden" value="" name="btn_type" id="btn_type">
                    </p>
                    </form>
                    <div class="today_date_position"><?php echo date("M d, Y", strtotime($this->input->post('from_date'))); ?> - <?php echo date("M d, Y", strtotime($this->input->post('to_date'))); ?></div>
                </div>
            </div>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <form method="post" name="leadstatus_searchform" id="leadstatus_searchform" class="dashboard_filter">

                    <?php if($this->session->userdata('user_type') == 'manager' || $this->session->userdata('user_type') == 'admin'){?>
                        <div class="dialog-form">
                            <label style="width: 100px;">Team Leader:</label>

                            <div class="styled select-dropdown">
                                <select class="team_leader_combo" name="team_leader_id" id="team_leader_id">
                                    <?php if($this->session->userdata('user_type') == 'admin'){ ?>
                                        <optgroup label="TM Admin"></optgroup>
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
                    <input type="hidden" name="filter_status" id="filter_status"
                           value="<?php echo $this->input->post('filter_status'); ?>">

                    <div class="dialog-form ">
                        <button type="submit" class="general-btn" id="leads_btnSave">Filter</button>
                    </div>
                </form>

            </div>
            <?php if (!empty($team_leaders_report_data)) { ?>
            <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th"
                       style="width: 99%;">
                    <thead>

                    <tr style="background: #f4f4f4;">
                        <th class="main_label">Agent Name</th>
                        <?php if (!empty($WeekDateArray)) {
                            foreach ($WeekDateArray as $week_day_value) { ?>
                                <th colspan="3"
                                    class="main_label"><?php echo date("l", strtotime($week_day_value)); ?></a></th>
                            <?php }
                        } ?>
                        <th  colspan="3" class="main_label">Total</th>
                        <th class="main_label">Expand</th>
                    </tr>
                    <tr>
                        <td rowspan="1"></td>
                        <?php if (!empty($WeekDateArray)) {
                            foreach ($WeekDateArray as $week_day_value) { ?>
                                <td class="dials_leads_bg" scope="col"># Dials</td>
                                <td class="dials_leads_bg" scope="col"># S Leads</td>
                                <td class="dials_leads_bg" scope="col"># A Leads</td>
                            <?php }
                        } ?>
                        <td class="dials_leads_bg" scope="col"># Dials</td>
                        <td class="dials_leads_bg" scope="col"># S Leads</td>
                        <td class="dials_leads_bg" scope="col"># A Leads</td>
                        <td rowspan="1"></td>

                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($team_leaders_report_data as $key => $tl_report_data_value) { ?>
                        <tr id="weekly_main_div" class="text_display_area dashboard_name_title" align="center"
                            style="word-break: break-all">
                            <td class="text_align_left" style="width: 13%;"
                                title="<?php echo $key; ?>"><?php echo $key; ?></td>
                            <?php
                            foreach ($WeekDateArray as $week_day_value) {

                                if (empty($tl_report_data_value[$week_day_value])) {

                                    echo '<td class="text_align_left" >0</td>';
                                    echo '<td class="text_align_left" >0</td>';
                                    echo '<td class="text_align_left" >0</td>';
                                } else {
                                    ?>
                                    <td class="text_align_left"><?php if(isset($tl_report_data_value[$week_day_value][0]['today_dials_count'])){ echo $tl_report_data_value[$week_day_value][0]['today_dials_count'];}else{echo'0';} ?></td>
                                    <td class="text_align_left"><?php if(isset($tl_report_data_value[$week_day_value][0]['today_leads_count'])){ echo $tl_report_data_value[$week_day_value][0]['today_leads_count'];}else{echo'0';} ?></td>
                                    <td class="text_align_left"><?php if(isset($tl_report_data_value[$week_day_value][0]['today_approve_leads'])){ echo $tl_report_data_value[$week_day_value][0]['today_approve_leads'];}else{echo'0';} ?></td>
                                <?php }
                            } ?>
                            <?php if(!empty($total_agent_count_array)){
                                foreach($total_agent_count_array as $agent_count_key=>$agent_count_value){
                                    if($agent_count_key == $key){
                                    ?>
                                    <td class="dials_leads_bg"><?php echo $agent_count_value['today_dials_count']; ?></td>
                                    <td class="dials_leads_bg"><?php echo $agent_count_value['today_leads_count']; ?></td>
                                    <td class="dials_leads_bg"><?php echo $agent_count_value['total_agent_approved_leads']; ?></td>
                            <?php }}
                            }
                            ?>
                            <td><a class="toggle" href="#">[Expand]</a></td>

                        </tr>
                        <!-- Expand Agent detail -->
                        <?php if (!empty($sub_campaign_main_array)) { ?>
                            <tr class="text_display_area" id="campaigngrid">
                                <td colspan="24">
                                    <table id="DataTables_Table_0"
                                           class="table table-striped table-bordered bootstrap-datatable datatable dataTable internalgrid"
                                           aria-describedby="DataTables_Table_0_info" align="center"
                                           style="display:none;">
                                        <?php foreach ($sub_campaign_main_array as $agent_key => $agent_value) {
                                            if ($agent_key == $key) { ?>
                                                <?php foreach ($agent_value as $campaign_key => $campaign_agent_value) { ?>
                                                    <tr id="sub_campaign_Detail" valign="top" class="text_display_area">
                                                        <td style="width: 13% !important;"
                                                            align="left"><?php echo $campaign_key; ?></td>
                                                        <?php
                                                    foreach ($WeekDateArray as $week_day_value) {
                                                        foreach ($campaign_agent_value as $campaign_date_key => $date_campaign_agent_value) { ?>
                                                            <?php
                                                            if($week_day_value == $campaign_date_key){
                                                            if (empty($date_campaign_agent_value)) { ?>
                                                                <td class="text_align_left">0</td>
                                                                <td class="text_align_left">0</td>
                                                                <td class="text_align_left">0</td>
                                                            <?php } else { ?>
                                                                <td class="text_align_left"><?php if(isset($date_campaign_agent_value[0]['today_dial_count'])){echo $date_campaign_agent_value[0]['today_dial_count'];}else{echo '0';} ?></td>
                                                                <td class="text_align_left"><?php if(isset($date_campaign_agent_value[0]['today_lead_count'])){echo $date_campaign_agent_value[0]['today_lead_count'];}else{echo '0';} ?></td>
                                                                <td class="text_align_left"><?php if(isset($date_campaign_agent_value[0]['today_agent_approve_lead_count'])){echo $date_campaign_agent_value[0]['today_agent_approve_lead_count'];}else{echo '0';} ?></td>
                                                            <?php } }
                                                        } } ?> </tr>
                                                <?php } ?>
                                            <?php }
                                        } ?>
                                    </table>
                                </td>
                                <!--<td rowspan="1"></td>-->
                            </tr>
                        <?php } ?>
                        <!-- Expand Agent detail -->
                    <?php } ?>
                    <tr class="total_area">
                        <td>Total</td>
                        <?php foreach($date_wise_dials_leads_sumArray as $date_key=>$date_total_value){ ?>
                            <td><?php echo $date_total_value['date_wise_dials'] ?></td>
                            <td><?php echo $date_total_value['date_wise_leads'] ?></td>
                            <td><?php echo $date_total_value['date_wise_approved_leads'] ?></td>
                       <?php } ?>
                    </tr>
                    <?php }
                    else { ?>
                        <div class="pad-15-t pad-15-l row-left-pad call-row-title pad-15-b" style="margin-top: 10px;">
                            <div class="dashboard_no_records">No record(s) found</div>
                        </div>
                    <?php } ?>

                    </tbody>
                </table>
            </div>
            <br/><br/>
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