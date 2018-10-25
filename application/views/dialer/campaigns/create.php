<?php if ($this->session->flashdata('msg') != '') { ?>
    <div id="divErrorMsg" class="error-msg">
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
        // Fill Add form
        //------------------------------>
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'campaign_form', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate');
        echo form_open('/dialer/campaigns/create/', $attributes);
        ?>
        <div class="form-section-title">
            <p>CAMPAIGN DEFINITION</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form">
                <label><span class="alert-required">*</span>Admin Campaign ID:</label>

                <div class="form-input"><input type="text" id="eg_campaign_id" name="eg_campaign_id" maxlength="7"
                                               placeholder="Admin Campaign ID"
                                               value="<?php echo set_value('eg_campaign_id'); ?>"/>

                    <p id="empty-message"></p>
                </div>
            </div>
            <div id="campaign_fields" <?php if (set_value('name') == ""){ ?>style="display:none" <?php } ?>>
                <div class="dialog-form">
                <label>Campaign Name:</label>

                    <div class="form-view"><label class="view-text-field"><span
                                id="dis_name"><?php echo set_value('name'); ?></span><input type="hidden" id="name"
                                                                                            name="name"
                                                                                            value="<?php echo set_value('name'); ?>"/>
                </label>
                    </div>
                </div>


            <div class="dialog-form ">
                <label> <span class="alert-required">*</span>Type:</label>

                <div class="form-view"><label class="view-text-field">
                    <span id="dis_type"><?php echo set_value('type'); ?></span>
                            <input type="hidden" id="type" name="type" maxlength="100"
                                   value="<?php echo set_value('type'); ?>"/>
                </label></div>
                </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Campaign CPL:</label>

                    <div class="form-input"><span id="dis_cpl"><?php echo set_value('cpl'); ?></span><input
                            type="hidden" id="cpl" name="cpl" value="<?php echo set_value('cpl'); ?>"></div>
            </div>

            <div class="dialog-form ">
                <label>Lead Goal:</label>

                <div class="form-view">
                        <input type="text" id="lead_goal" name="lead_goal" maxlength="5"
                               value="<?php echo set_value('lead_goal'); ?>"/>
                </div>
            </div>
            <div class="dialog-form ">
                <label> Start Date:</label>

                    <div class="form-view"><label class="view-text-field"><span
                                id="dis_start_date"><?php echo set_value('start_date'); ?></span>
                            <input type="hidden" id="start_date" name="start_date"
                                   value="<?php echo set_value('start_date'); ?>"/>
                </label></div>
                </div>
            <div class="dialog-form ">
                <label>End Date:</label>

                    <div class="form-view"><label class="view-text-field"><span
                                id="dis_end_date"><?php echo set_value('end_date'); ?></span><input type="hidden"
                                                                                                    id="end_date"
                                                                                                    name="end_date"
                                                                                                    value="<?php echo set_value('end_date'); ?>"/></label>
            </div>
			</div>
                <div class="dialog-form" id="site_tm_brand_name">
                    <label> TM Brand:</label>

                    <div class="form-view"><label class="view-text-field">
                            <span id="dis_site_name"><?php echo set_value('site_name'); ?></span>
                            <input type="hidden" id="site_name" name="site_name" maxlength="100"
                                   value="<?php echo set_value('site_name'); ?>"/>
                        </label></div>
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Status:</label>

                <div class="styled select-dropdown">
                    <select name="status" id="status">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                    </select>
            </div>
            </div>
            <div id="custom_questions_div" class="dialog-form radio-alignment-popup">
                <div class="popup-radio-group">
                    <label> <span class="alert-required">*</span>Custom Question/s:</label>
                    <ul>
                        <li>
                            <input tabindex="8" type="radio" id="custom_questions_yes" name="custom_questions"
                                   onclick="yesNoCheck()"
                                   value="1"/>
                            <label for="custom_questions_yes">Yes</label>
                        </li>

                        <li>
                            <input tabindex="9" type="radio" id="custom_questions_no" value="0"
                                   name="custom_questions" checked="checked" onclick="yesNoCheck()"/>
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
                           value="<?php echo set_value('custom_question_value'); ?>"/>
                </div>
            </div>

            <div class="dialog-form ">
                <label>Call File Request Date:</label>

                <div class="form-input date-picker"><input type="text" id="call_filerequest_date"
                                                           name="call_filerequest_date"
                                                           placeholder="Call File Request Date" maxlength="10"
                                                           value="<?php echo set_value('call_filerequest_date'); ?>"/>
                </div>
             </div>
            <div class="dialog-form ">
                <label>Materials Sent to TM Ops (Asset, CF, TM Kick Off Email, etc):</label>

                <div class="form-input date-picker"><input type="text" id="materials_sent_to_tm_Date"
                                                           name="materials_sent_to_tm_Date"
                                                           placeholder="Materials Sent to TM Ops Date" maxlength="10"
                                                           value="<?php echo set_value('materials_sent_to_tm_Date'); ?>"/>
                </div>
            </div>
            <div class="dialog-form ">
                <label> <span class="alert-required">*</span>Telemarketing Offices:</label>

                <div class="styled select-dropdown">
                    <select name="telemarketing_offices[]" id="telemarketing_offices" multiple="multiple">

                        <?php $i = 0;
                        $selected = "";
                        if (!empty($getEGWebsitesList)) {
                            foreach ($getEGWebsitesList as $key => $sitesList) {
                                if ($i == 0) {
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
        </div>
        <div class="form-section-title">
            <p>JOB FILTERS</p>
            <span></span>
        </div>
        <div class="form-row mar-b-0">
            <?php if($this->app == 'mpg'){ ?>
                    <div class="dialog-form ">
                        <label>Filters:</label>

                        <div class="form-view">
                            <label class="view-text-field large_text_area"><span id="dis_filters"></span></label></div>
                    </div>
                <?php }else{?>
            <div class="dialog-form ">
                <label>Job Function:</label>
				<div class="form-view"><label class="view-text-field"><span id="dis_job_function"><?php echo set_value('job_function'); ?></span><input type="hidden" id="job_function" name="job_function" value="<?php echo set_value('job_function'); ?>"/></label></div>

                </div>
            <div class="dialog-form ">
                <label>Job Level:</label>
				<div class="form-view"><label class="view-text-field"><span id="dis_job_level"><?php echo set_value('job_level'); ?></span><input type="hidden" id="job_level" name="job_level" value="<?php echo set_value('job_level'); ?>"/></label></div>

                </div>

            <div class="dialog-form ">
                <label>Company Size:</label>
				<div class="form-view"><label class="view-text-field"><span id="dis_company_size"><?php echo set_value('company_size'); ?></span><input type="hidden" id="company_size" name="company_size" value="<?php echo set_value('company_size'); ?>"/></label></div>

                </div>

            <div class="dialog-form ">
                <label>Industries:</label>
				<div class="form-view"><label class="view-text-field"><span id="dis_industries"><?php echo set_value('industries'); ?></span><input type="hidden" id="industries" name="industries" value="<?php echo set_value('industries'); ?>"/></label></div>

                </div>

			 <div class="dialog-form ">
                <label>Country:</label>
				<div class="form-view"><label class="view-text-field"><span id="dis_country"><?php echo set_value('country'); ?></span><input type="hidden" id="country" name="country" value="<?php echo set_value('country'); ?>"/></label></div>

            </div>
             <?php }?>


            <div class="dialog-form  clear">
                <label class="vertical-top"><span class="alert-required">*</span>Script Main:</label>

                <div class="form-input">
                    <textarea rows="8" cols="10" minlength="5" placeholder="Script Main..." id="script_main"
                              name="script_main" required="required"
                                                  class="textarea-script span9"><?php echo set_value('script_main'); ?></textarea>
                </div>
            </div>

            <div class="dialog-form ">
                <label class="vertical-top">Script Alter:</label>

                <div class="form-input">
                    <textarea rows="8" cols="10" placeholder="Script Alter..." id="script_alt" name="script_alt"
                                                  class="textarea-script span9"><?php echo set_value('script_alt'); ?></textarea>
                </div>
            </div>

            <div class="dialog-form ">
                <label>Assign Team:</label>

                <div class="styled select-dropdown">
                    <select name="assign_team_id[]" id="assign_team_id" multiple="multiple">
                        <?php
                        if (!empty($teamMemberUserList)) {
                            foreach ($teamMemberUserList as $teamMember) {
                                echo '<option value="' . $teamMember->id . '">' . $teamMember->first_name . '</option>';
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
                    <input type="hidden" id="existedEGCampaignIDFilterValues" name="existedEGCampaignIDFilterValues"
                           value="<?php echo $existedEGCampaignIDStringValues; ?>">
                    <input type="hidden" id="company_name" name="company_name" value="">
                    <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                </li>
                <li>
                    <button type="button" class="general-btn" id="btnCancel"
                            onclick="window.location.href='/dialer/campaigns/'">Cancel
                    </button>
                </li>
            </ul>
        </div>

        </form>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript">
    var eGCampaignList = '<?php echo $this->db->escape_str($eGCampaignList); ?>';
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/campaigns/campaign.js"></script>
