<section class="section-content-main-area">
    <div class="content-main-area">
        <div class="pad-15-b">
            <div class="pad-15-t pad-15-l  call-row-title">
               <div class="column-header">
                    <p>Consolidated Lead Tracker</p>
                </div>
            </div>
            <div class="pad-15-b">
                <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                    <form method="post" name="lead_track_form" id="lead_track_form" class="dashboard_filter">
                        <?php if($this->session->userdata('user_type') == 'manager'){?>
                            <div class="dialog-form">
                                <label style="width: 100px;">Team Leader:</label>

                                <div class="styled select-dropdown">
                                    <select class="team_leader_combo" name="team_leader_id" id="team_leader_id">
                                        <option value="">--Select Team Leader--</option>
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
                            <button type="submit" class="general-btn" id="lead_track_filterBtn">Filter</button>
                        </div>
                    </form>

                </div>

                <?php if (!empty($lead_tracker_array)) {
                    foreach($lead_tracker_array as $lead_key=>$lead_value){
                    ?>
                    <div class="pad-15-t pad-15-l row-left-pad call-row-title">
                        <table id="staffing_attrition_report" class="table table-bordered row vertical-tbl sort-th"
                               style="width: 99%;">
                            <thead>
                                <tr style="background: #f4f4f4;">
                                    <th class="main_label">DATE: <?php echo date("M d (l)", strtotime($lead_key)); ?></th>
                                    <?php if (!empty($lead_value)) {
                                        foreach ($lead_value as $hour_key=>$lead_hour_value) { ?>
                                            <th class="main_label"><?php echo $hour_key; ?></a></th>
                                        <?php }
                                    } ?>
                                    <th class="main_label">Total</th>
                                    <th class="main_label">Goal</th>
                                    <th class="main_label">% to Goal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left"><?php echo $this->session->userdata('user_fname'); ?></td>
                            <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                            <?php } } ?>
                            </tr>

                            <!-- Running Lead -->

                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left">RUNNING</td>
                                <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                                <?php } } ?>
                            </tr>

                            <!-- HOURLY SITE GOAL -->

                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left hourly_site_goal">HOURLY SITE GOAL</td>
                                <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                                <?php } } ?>
                            </tr>

                            <!-- RUNNING TOTAL -->

                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left running_total">RUNNING TOTAL</td>
                                <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                                <?php } } ?>
                            </tr>

                            <!-- RUNNING SITE GOAL -->

                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left running_site_goal">RUNNING SITE GOAL</td>
                                <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                                <?php } } ?>
                            </tr>

                            <!-- PERCENTAGE TO GOAL -->
                            <tr class="text_display_area dashboard_name_title" align="center" style="word-break: break-all">
                                <td class="text_align_left pr_to_goal">PERCENTAGE TO GOAL</td>
                                <?php if(!empty($lead_value)){ foreach ($lead_value as $date_hour_key => $hour_wise_data_value) { ?>

                                    <td class="text_align_left"><?php echo $hour_wise_data_value['lead_per_hour']; ?></td>

                                <?php } } ?>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                <?php } }?>

            </div>

        </div>
    </div>
</section>
<div class="clearfix"></div>
<script type="text/javascript"> var loggedInUserType = '<?php echo $this->session->userdata('user_type'); ?>'; </script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/reports/consolidated_lead_track.js"></script>
<style>
    td {
        word-break: break-word;
        word-wrap: break-word;
    }
</style>