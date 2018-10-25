<style>
    .check_email{
        display:inline;
    }
</style>
<?php
if ($this->session->flashdata('msg') != '') { ?>
<div id="divErrorMsg" style="margin-top:15px;" class="error-msg <?php echo $this->session->flashdata('class'); ?>">
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
        $filter = !empty($contact_details->id) ? "?cid={$contact_details->id}" : "";
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'create_contact', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate');
        echo form_open('/dialer/contacts/create/' . $campaign_id.'/'.$list_id . $filter , $attributes);
        ?>
        <div class="form-section-title">
            <p>CONTACT DEFINITION</p>
            <span></span>
        </div>
        <div class="form-row">
<!--            <div class="dialog-form ">
                <label><span class="alert-required">*</span>EG Contact ID:</label>

                <div class="form-input">
                    <input type="text" id="eg_contact_id" name="id" required="required" maxlength="20" placeholder="contact Id">
                </div>
            </div>-->
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Email:</label>

                <div class="form-input">
                    <input type="email" id="email" name="email" required="required" maxlength="100" value="<?php echo isset($contact_details->email) ? $contact_details->email : ""; ?>" <?php echo isset($contact_details->email) ? "readonly" : ""; ?>
                           placeholder="Email"/>
                </div><div id='check_email' class='check_email'></div>
            </div>
            <div class="dialog-form">
                <label><span class="alert-required">*</span>First Name:</label>

                <div class="form-input">
                    <input type="text" id="first_name" name="first_name" required="required" maxlength="100" value="<?php echo isset($contact_details->first_name) ? $contact_details->first_name : ""; ?>"
                           placeholder="First Name">
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Last Name:</label>

                <div class="form-input">
                    <input type="text" id="last_name" name="last_name" required="required" maxlength="100" value="<?php echo !empty($contact_details->last_name) ? $contact_details->last_name : ""; ?>"
                           placeholder="Last Name">
                </div>
            </div>

            

            <div class="dialog-form ">
                <label>Job Title:</label>

                <div class="form-input">
                    <input type="text" id="job_title" name="job_title" maxlength="255" placeholder="Job Title" value="<?php echo !empty($contact_details->job_title) ? $contact_details->job_title : ""; ?>">
                </div>
            </div>

            <div class="dialog-form ">
                <label>Job Level:</label>

                <div class="styled select-dropdown">
                    <select name="job_level" id="job_level">
                        <option <?php echo !empty($contact_details->job_level) ? '' : 'selected="selected"'; ?> value="">--SELECT--</option>
                        <?php
                        if (!empty($jobLevelValues)) {
                            $selected = "";
                            foreach ($jobLevelValues as $jobLevel) {
                                if(!empty($contact_details->job_level) && $contact_details->job_level == $jobLevel){
                                    $selected = 'selected="selected"';
                                }else{
                                    $selected = "";
                                }
                                echo '<option value="' . $jobLevel . '" '. $selected .' ">' . $jobLevel . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Job Function:</label>

                <div class="styled select-dropdown">
                    <select name="job_function" id="job_function">
                        <option <?php echo !empty($contact_details->job_function) ? '' : 'selected="selected"'; ?> value="">--SELECT--</option>
                        <?php
                        if (!empty($jobFunctionValues)) {
                            $selected = "";
                            foreach ($jobFunctionValues as $jobFunction) {
                                if(!empty($contact_details->job_function) && $contact_details->job_function == $jobFunction){
                                    $selected = 'selected="selected"';
                                }else{
                                    $selected = "";
                                }
                                echo '<option value="' . $jobFunction . '" '. $selected .' >' . $jobFunction . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Company Name:</label>

                <div class="form-input"><input type="text" id="company" name="company" maxlength="100" value="<?php echo !empty($contact_details->company) ? $contact_details->company : ""; ?>"
                                               placeholder="Company Name"></div>
            </div>

            <div class="dialog-form ">
                <label>Company size:</label>

                <div class="styled select-dropdown">
                    <select name="company_size" id="company_size">
                        <option <?php echo !empty($contact_details->company_size) ? '' : 'selected="selected"'; ?> value="">--SELECT--</option>
                        <?php
                        if (!empty($companySizeValues)) {
                            $selected = "";
                            foreach ($companySizeValues as $companySize) {
                                if(!empty($contact_details->company_size) && $contact_details->company_size == $companySize){
                                    $selected = 'selected="selected"';
                                }else{
                                    $selected = "";
                                }
                                if($companySize == '1-9'){
                                    $companySize = '1 to 9';
                                }
                                if($companySize == '10-24'){
                                    $companySize = '10 to 24';
                                }
                                echo '<option value="' . $companySize . '" '. $selected .' >' . $companySize . '</option>';
                            }
                        }
                        ?>

                    </select>
                </div>
            </div>

            <div class="dialog-form ">
                <label>Industry:</label>

                <div class="styled select-dropdown">
                    <select name="industry" id="industry">
                        <option <?php echo !empty($contact_details->industry) ? '' : 'selected="selected"'; ?> value="">--SELECT--</option>
                        <?php
                        if (!empty($industriesValues)) {
                            $selected = "";
                            foreach ($industriesValues as $industry) {
                                if(!empty($contact_details->industry) && $contact_details->industry == $industry){
                                    $selected = 'selected="selected"';
                                }else{
                                    $selected = "";
                                }
                                echo '<option value="' . $industry . '" '. $selected .'>' . $industry . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Phone Number:</label>

                <div class="form-input">
                    <input type="text" id="phone" name="phone" required="required" maxlength="20" placeholder="Phone Number" value="<?php echo !empty($contact_details->phone) ? $contact_details->phone : ""; ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Alternate Number:</label>

                <div class="form-input">
                    <input type="text" id="alternate_no" name="alternate_no" maxlength="20" placeholder="Alternate Number" value="<?php echo !empty($contact_details->alternate_no) ? $contact_details->alternate_no : ""; ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Time Zone:</label>
                <div class="form-input"><input type="text" id="time_zone" style='text-transform:uppercase' name="time_zone" maxlength="100" placeholder="Time Zone" value="<?php echo !empty($contact_details->time_zone) ? $contact_details->time_zone : ""; ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>City:</label>

                <div class="form-input"><input type="text" id="city" name="city" maxlength="25" placeholder="City" value="<?php echo !empty($contact_details->city) ? $contact_details->city : ""; ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Postal Code:</label>
                <div class="form-input"><input type="text" id="zip" name="zip" maxlength="20" placeholder="Postal Code" value="<?php echo !empty($contact_details->zip) ? $contact_details->zip : ""; ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>State:</label>

                <div class="form-input"><input type="text" id="state" name="state" maxlength="20" placeholder="State" value="<?php echo !empty($contact_details->state) ? $contact_details->state : ""; ?>">
                </div>
            </div>
            <div class="dialog-form">
                <span class="alert-required">*</span><label>Country:</label>
<!--                <div class="form-input">
                    <input type="text" id="country" name="country" maxlength="100" placeholder="Country">
                </div>-->
                <div class="styled select-dropdown">
                    <select name="country" id="country">
                        <option <?php echo !empty($contact_details->country) ? '' : 'selected="selected"'; ?> value="">--SELECT--</option>
                        <?php
                        if (!empty($countries)) {
                            $selected = "";
                            foreach ($countries as $country) {
                                if(!empty($contact_details->country) && strtoupper($contact_details->country) == strtoupper($country->country_code)){
                                    $selected = 'selected="selected"';
                                }else{
                                    $selected = "";
                                }
                                echo '<option value="'.trim($country->country_code). '" '. $selected .'>' . $country->country . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Street Address:</label>
                <div class="form-input">
                    <textarea rows="8" cols="10" maxlength="100" placeholder="Address..."id="address" name="address" class="textarea-script span9"><?php echo !empty($contact_details->address) ? $contact_details->address : ""; ?></textarea>
                </div>
            </div>

            <div class="dialog-form ">
                <label>priority:</label>
                <div class="form-input">
                    <input type="text" id="priority" name="priority" maxlength="3" placeholder="Priority" value="<?php echo !empty($contact_details->priority) ? $contact_details->priority : ""; ?>">
                </div>
            </div>
        </div>

        <div class="popup-btn-group">
            <ul>
                <li>
                    <button type="submit" class="general-btn" id="create_contact_btnSave">Save</button>
                </li>
                <li>
                    <a class="general-btn" id="btnCancel" href="/dialer/contacts/index/<?=$campaign_id?>/<?=$list_id?>">Cancel</a>
                </li>
            </ul>
        </div>
        <input type="hidden" id="contact_campaign_id" name="contact_campaign_id" value="<?=$campaign_id?>" >
        <input type="hidden" id="list_id" name="list_id" value="<?=$list_id?>" >
        <div class="clearfix"></div>
        </form>
    </div>
</section>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/contacts/create.js<?=$this->cache_buster?>"></script>
