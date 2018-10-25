<?php if ($this->session->flashdata('msg') != '') { ?>
<div style="margin-top: 0px !important;" id="divErrorMsg" class="error-msg bad ">
        <p><span><i class="fa fa-times-circle"></i></span>  <?php echo $this->session->flashdata('msg'); ?></p>
</div>
<?php } ?>

<section class="section-content-main-area">
    <div class="content-main-area">
        <?php

        if (validation_errors() != '') { ?>
            <div id="divErrorMsg" class="alert alert-warning server-validation-msg">
                <p><strong>Please fix the following input errors:</strong></p><?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php

        //------------------------------>
        // Fill Edit form
        //------------------------------>
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'campaign_form', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate');
        echo form_open('/dialer/campaigns/edit/' . $campaign->id, $attributes);
        ?>
        <div class="form-section-title">
            <p>CAMPAIGN DEFINITION</p>
            <span></span>
        </div>

        <div class="form-row">
            <div class="dialog-form">
                <label><span class="alert-required">*</span>Admin Campaign ID:</label>

				 <div class="form-view">
                    <label class="view-text-field"><?php echo $campaign->eg_campaign_id; ?><input type="hidden"
                                                                                                  id="eg_campaign_id"
                                                                                                  name="eg_campaign_id"
                                                                                                  value="<?php echo $campaign->eg_campaign_id; ?>"/></label>
                    </div>
            </div>
            <div class="dialog-form">
                <label>Campaign Name:</label>

                <div class="form-view"><label
                        class="view-text-field"><?php echo htmlspecialchars($campaign->name); ?></label></div>
            </div>

            
            <div class="dialog-form ">
                <label>Type:</label>

 					<div class="form-view"><label class="view-text-field"><?php echo $campaign->type; ?></label></div>

            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Campaign CPL:</label>

                <div class="form-view"><label class="view-text-field"><?php echo $campaign->cpl; ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>Lead Goal:</label>

                <div class="form-input">
                    <input type="text" id="lead_goal" name="lead_goal" maxlength="5"
                           value="<?php echo $campaign->lead_goal; ?>"/>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Start Date:</label>

                <div class="form-view"><label class="view-text-field"><?php echo $campaign->start_date; ?></label></div>
            </div>
            <div class="dialog-form ">
                <label>End Date:</label>

                <div class="form-view"><label class="view-text-field"><?php echo $campaign->end_date; ?></label></div>
            </div>
            <?php if(!empty($campaign->site_name)){?>
			<div class="dialog-form ">
                <label>TM Brand:</label>

                <div class="form-view"><label class="view-text-field"><?php echo $campaign->site_name; ?></label></div>
                <input type="hidden" id="site_name" name="site_name"
                       value="<?php echo $campaign->site_name; ?>">
            </div>
            <?php } ?>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Status:</label>

                <div class="styled select-dropdown">
                    <select name="status" id="status">
                        <option value="pending" <?= $campaign->status == 'pending' ? 'selected="selected"' : '' ?> >
                            Pending
                        </option>
                        <option value="active" <?= $campaign->status == 'active' ? 'selected="selected"' : '' ?>>
                            Active
                        </option>
                        <option value="paused" <?= $campaign->status == 'paused' ? 'selected="selected"' : '' ?> >Paused
                        </option>
                        <option value="completed" <?= $campaign->status == 'completed' ? 'selected="selected"' : '' ?>>
                            Completed
                        </option>
                    </select>
                </div>
            </div>                        
            <div class="dialog-form radio-alignment-popup">
                <div class=" popup-radio-group">
                    <label> <span class="alert-required">*</span>Custom Question/s:</label>
                    <ul>
                        <li>
                            <input tabindex="8" type="radio" id="custom_questions_yes" name="custom_questions"
                                   value="1" <?= $campaign->custom_questions == 1 ? 'checked="checked"' : '' ?>
                                   onclick="yesNoCheck()"/>
                            <label for="custom_questions_yes">Yes</label>
                        </li>

                        <li>
                            <input tabindex="9" type="radio" id="custom_questions_no" value="0"
                                   name="custom_questions" <?= $campaign->custom_questions == 0 ? 'checked="checked"' : '' ?>
                                   onclick="yesNoCheck()"/>
                            <label for="custom_questions_no">No</label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="dialog-form" id="custom_question_value_div" style="display: none;">
                <label class="vertical-top"><span class="alert-required">*</span>Question Text:</label>

                <div class="form-input">
                    <input type="text" id="custom_question_value" name="custom_question_value" required="required"
                           maxlength="200"
                           placeholder="Custom Question"
                           value="<?php
                        if (!form_error("custom_question_value")) {
                            echo htmlspecialchars($campaign->custom_question_value);
                        } else {
                            echo $this->input->post('custom_question_value');
                        }
                           ?>"/>
                </div>
            </div>

            <div class="dialog-form ">
                <label>Call File Request Date:</label>

                <div class="form-input date-picker"><input type="text" id="call_filerequest_date" name="call_filerequest_date"
                                                           placeholder="Date Sent Call File Request" maxlength="10"
                                                           value="<?php
                                                           if (!form_error("call_filerequest_date")) {
                                                               echo $campaign->call_filerequest_date;
                                                           } else {
                                                               echo $this->input->post('call_filerequest_date');
                                                           }
                                                           ?>"/></div>
        </div>
        <div class="dialog-form ">
            <label>Materials Sent to TM Ops (Asset, CF,  TM Kick Off Email, etc):</label>

            <div class="form-input date-picker"><input type="text" id="materials_sent_to_tm_Date" name="materials_sent_to_tm_Date"
                                                       placeholder="Materials Sent to TM Ops Date" maxlength="10"
                                                       value="<?php
                                                       if (!form_error("materials_sent_to_tm_Date")) {
                                                           echo $campaign->materials_sent_to_tm_Date;
                                                       } else {
                                                           echo $this->input->post('materials_sent_to_tm_Date');
                                                       }
                                                       ?>"/></div>
        </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Telemarketing Offices:</label>

                <div class="styled select-dropdown">
				<?php $selected_offices = array();
                    if (!empty($tm_offices))?>
                    <select <?php if($this->session->userdata('user_type') == 'manager'){echo 'disabled'; } ?> name="telemarketing_offices[]" id="telemarketing_offices" multiple="multiple">
                        <?php
                        if (!empty($getEGWebsitesList)) {
                            foreach ($getEGWebsitesList as $key => $sitesList) {
                                if (in_array($key, $tm_offices))
                                    $selected = "selected";
                                else
                                    $selected = "";

                                echo '<option value="' . $key . '" ' . $selected . '>' . $sitesList . '</option>';

                            }
                        }
                      
                        ?>

                    </select>
                    
        </div>
        <?php
        if ($this->config->item('auto_dialer_toggle')) { ?>


            <!-- Start HP UAD-43 configure campaign ---> 
            <!-- Start HP UAD-3 configure campaign --->           
            <div class="dialog-form radio-alignment-popup">
                <div class="popup-radio-group">
                    <label><span class="alert-required">*</span>Auto Dial:</label>
                    <ul>
                        <li>
                            <input type="radio" id="autodial_yes" name="autodial"
                                   value="1" <?= $campaign->auto_dial == '1' ? 'checked="checked"' : '' ?>
                                   onclick="showHideAutoDial()" class="autodial_option" />
                            <label for="autodial_yes">Yes</label>
                        </li>

                        <li>
                            <input type="radio" id="autodial_no" name="autodial" 
                                    value="0" <?= $campaign->auto_dial == '0' ? 'checked="checked"' : '' ?>
                                   onclick="showHideAutoDial()" class="autodial_option" />
                            <label for="autodial_no">No</label>
                        </li>
                    </ul>
                </div>  
            </div>

            <div class="" id="autodial_options" style="display:<?= $campaign->auto_dial == '1' ? 'block' : 'none' ?>">
                <!-- Start HP UAD-18 configure campaign ---> 
                <div class="dialog-form ">
                    <!-- Start HP UAD-39 configure campaign ---> 
                    <label><span class="alert-required">*</span>Minimum Hopper Level:</label>
                    <!-- End HP UAD-39 configure campaign ---> 

                    <div class="styled select-dropdown">
                        <select name="minimum_hopper_level" id="minimum_hopper_level">
                            <option value="1" <?= $campaign->auto_hopper_level == 1 ? 'selected="selected"' : '' ?>>1</option>
                            <option value="10" <?= $campaign->auto_hopper_level == 10 ? 'selected="selected"' : '' ?>>10</option>
                            <option value="20" <?= $campaign->auto_hopper_level == 20 ? 'selected="selected"' : '' ?>>20</option>
                            <option value="30" <?= $campaign->auto_hopper_level == 30 ? 'selected="selected"' : '' ?>>30</option>
                            <option value="40" <?= $campaign->auto_hopper_level == 40 ? 'selected="selected"' : '' ?>>40</option>
                            <option value="50" <?= $campaign->auto_hopper_level == 50 ? 'selected="selected"' : '' ?>>50</option>
                            <option value="60" <?= $campaign->auto_hopper_level == 60 ? 'selected="selected"' : '' ?>>60</option>
                            <option value="70" <?= $campaign->auto_hopper_level == 70 ? 'selected="selected"' : '' ?>>70</option>
                            <option value="80" <?= $campaign->auto_hopper_level == 80 ? 'selected="selected"' : '' ?>>80</option>
                            <option value="90" <?= $campaign->auto_hopper_level == 90 ? 'selected="selected"' : '' ?>>90</option>
                            <option value="100" <?= $campaign->auto_hopper_level == 100 ? 'selected="selected"' : '' ?>>100</option>
                        </select>
                    </div>
                </div>
                <!-- End HP UAD-18 configure campaign ---> 

                <!-- Start RP UAD-8 : add abandon rate, threshold time and message -->
                    <div class="dialog-form ">
                        <label><span class="alert-required">*</span>Abandon Calls Rate:</label>
                        <div class="form-input">
                            <input type="text" id="auto_abandoned_rate" name="auto_abandoned_rate" placeholder="Enter value in percentage" value="<?php echo (isset($campaign->auto_abandoned_rate))?$campaign->auto_abandoned_rate:ABANDON_CALLS_RATE; ?>"/>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label><span class="alert-required">*</span>Wait Time Threshold1:</label>
                        <div class="styled select-dropdown">
                            <select name="auto_time_threshold_one" id="auto_time_threshold_one">
                                <?php for($i=1; $i<=60; $i++) { ?>
                                    <option value="<?php echo $i;?>" <?= $campaign->auto_time_threshold_one != $i ? ((! isset($campaign->auto_time_threshold_one)) && ($i == WAIT_TIME_THRESHOLD_1))?'selected="selected"':'' : 'selected="selected"'; ?>><?php echo $i; ?> Sec.</option>
                                <?php } ?>    
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form  clear">
                        <label class="vertical-top"><span class="alert-required">*</span>Recorded Message1:</label>
                        <div class="form-input"><textarea rows="8" minlength="5" cols="10" id="auto_recorded_message_one" name="auto_recorded_message_one" placeholder="Recorded message for first threshold..." required="required"
                                                class="textarea-script span9"><?php
                                if (! form_error("auto_recorded_message_one")) {
                                    //echo trim($campaign->auto_recorded_msg_one);  
                                    echo (isset($campaign->auto_recorded_msg_one))?trim($campaign->auto_recorded_msg_one):RECORDED_MESSAGE_1;
                                } else {
                                    echo trim($this->input->post('auto_recorded_message_one'));
                                }
                                ?></textarea></div>
                    </div>
                    <div class="dialog-form ">
                        <label><span class="alert-required">*</span>Wait Time Threshold2:</label>
                        <div class="styled select-dropdown">
                            <select name="auto_time_threshold_two" id="auto_time_threshold_two">
                                <?php for($i=1; $i<=60; $i++) { ?>
                                    <option value="<?php echo $i;?>" <?= $campaign->auto_time_threshold_two != $i ? ((! isset($campaign->auto_time_threshold_two)) && ($i == WAIT_TIME_THRESHOLD_2))?'selected="selected"':'' : 'selected="selected"'; ?>><?php echo $i; ?> Sec.</option>
                                <?php } ?>    
                            </select>
                        </div>
                    </div>
                    <div class="dialog-form ">
                        <label class="vertical-top"><span class="alert-required">*</span>Recorded Message2:</label>
                        <div class="form-input"><textarea rows="8" minlength="5" cols="10" id="auto_recorded_message_two" name="auto_recorded_message_two" placeholder="Recorded message for second threshold..." required="required"
                                                class="textarea-script span9"><?php
                                if (! form_error("auto_recorded_message_two")) {
                                    //echo trim($campaign->auto_recorded_msg_two);
                                    echo (isset($campaign->auto_recorded_msg_two))?trim($campaign->auto_recorded_msg_two):RECORDED_MESSAGE_2;
                                } else {
                                    echo trim($this->input->post('auto_recorded_message_two'));
                                }
                                ?></textarea></div>
                    </div>                
                <!-- End RP UAD-8 : add abandon rate, threshold time and message --> 
            </div>
            <!-- End HP UAD-3 configure campaign--->
            <!-- End HP UAD-43 configure campaign--->
    <?php
        }  ?>
            </div>

        </div>

        <div class="form-section-title">
            <p>JOB FILTERS</p>
            <span></span>
        </div>
        <div class="form-row mar-b-0">

            <div class="dialog-form ">
                <label>Job Function:</label>

               <div class="form-view"><label class="view-text-field">
                        <?php
						if($campaign->job_function!="")
						{
							$job_function=str_replace("|","<br>",$campaign->job_function);
							echo $job_function;
                         }else{echo ' - ';} ?>

                </label></div>
                </div>
            <div class="dialog-form ">
                <label>Job Level:</label>
				<div class="form-view"><label class="view-text-field">
                        <?php
						if($campaign->job_level!="")
						{
							$job_level=str_replace("|","<br>",$campaign->job_level);
							echo $job_level;
                         }else{echo ' - ';} ?>

                </label></div>

                </div>

            <div class="dialog-form ">
                <label>Company Size:</label>
				<div class="form-view"><label class="view-text-field">
                        <?php
						if($campaign->company_size!="")
						{
							$company_size=str_replace("|","<br>",$campaign->company_size);
                            if($company_size == '1-9'){
                                $company_size = '1 to 9';
                            }
                            if($company_size == '10-24'){
                                $company_size = '10 to 24';
                            }

                            echo $company_size;
                         }else{echo ' - ';} ?>

               </label> </div>

            </div>

            <div class="dialog-form ">
                <label>Industries:</label>
                <div class="form-view">
                    <label class="view-text-field">
                    <?php
                        if($campaign->industries!="")
                        {
                                $industries=str_replace("|","<br>",$campaign->industries);
                                echo $industries;
                        }else{echo ' - ';}
                    ?>
                    </label>
                </div>
            </div>

			<div class="dialog-form ">
                <label>Country:</label>
				<div class="form-view"><label class="view-text-field">
				<?php
						if($campaign->country!="")
						{
							$country=str_replace("|","<br>",$campaign->country);
							echo $country;
                         }else{echo ' - ';} ?>

                </label></div>
                
            </div>
            <div class="dialog-form  clear">
                <label class="vertical-top"><span class="alert-required">*</span>Script Main:</label>

                <div class="form-input"><textarea rows="8" minlength="5" cols="10" id="script_main"
                                                   name="script_main" placeholder="Script Main..." required="required"
                                                   class="textarea-script span9"><?php
                        if (!form_error("script_main")) {
                            echo $campaign->script_main;
                        } else {
                            echo $this->input->post('script_main');
                        }
                        ?></textarea></div>
            </div>
            <div class="dialog-form ">
                <label class="vertical-top">Script Alter:</label>

                <div class="form-input"><textarea rows="8" cols="10"id="script_alt" placeholder="Script Alter..." name="script_alt"
                                                   class="textarea-script span9"><?php
                        if (!form_error("script_alt")) {
                            echo $campaign->script_alt;
                        } else {
                            echo $this->input->post('script_alt');
                        }
                        ?></textarea></div>
            </div>
            <div class="dialog-form ">
                <label>Assign Team:</label>

                <div class="styled select-dropdown">
                    <select name="assign_team_id[]" id="assign_team_id" multiple="multiple">
                        <?php
                        if (!empty($teamMemberUserList)) {
                            foreach ($teamMemberUserList as $teamMember) {
                                if (!empty($selectedTL) && in_array($teamMember->id, $selectedTL))
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

        </div>

        <div class="popup-btn-group">
            <ul>
                <li>
                    
                    <input type="hidden" id="company_name" name="company_name" value="<?php echo $campaign->company_name; ?>">
                    <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                </li>
                <li>
                    <button type="button" class="general-btn" id="btnCancel"
                            onclick="window.location.href='/dialer/campaigns/'">Cancel
                    </button>
                </li>
            </ul>
        </div>
        <!--<input type="hidden" id="Hidden_Campaign_ID" name="Campaign_ID" value="<?php //echo $campaign->id; ?>"/>-->
        <input type="hidden" id="campaign_previous_status" name="campaign_previous_status" value="<?php echo $campaign->status; ?>"/>
        <div class="clearfix"></div>

        <?php echo form_close(); ?>
    </div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript">
    var eGCampaignList = '';
</script>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/campaigns/campaign.js"></script>
