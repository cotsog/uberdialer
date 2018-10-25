<?php
if ($this->session->flashdata('msg') != '') { ?>
    <div id="divErrorMsg" class="error-msg <?php echo $this->session->flashdata('class'); ?>">
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
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'create_contact', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate');
        echo form_open('/dialer/contacts/create/' . $campaign_id, $attributes);
        ?>
        <div class="form-section-title">
            <p>CONTACT DEFINITION</p>
            <span></span>
        </div>
        <div class="form-row">
<!--            <div class="dialog-form ">
                <label><span class="alert-required">*</span>MPG Contact ID:</label>

                <div class="form-input">
                    <input type="text" id="eg_contact_id" name="id" required="required" maxlength="20" placeholder="contact Id">
                </div>
            </div>-->
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Email:</label>

                <div class="form-input">
                    <input type="email" id="email" name="email" required="required" maxlength="100"
                           placeholder="Email"/>
                </div>
            </div>
            <div class="dialog-form">
                <label><span class="alert-required">*</span>First Name:</label>

                <div class="form-input">
                    <input type="text" id="first_name" name="first_name" required="required" maxlength="100"
                           placeholder="First Name">
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Last Name:</label>

                <div class="form-input">
                    <input type="text" id="last_name" name="last_name" required="required" maxlength="100"
                           placeholder="Last Name">
                </div>
            </div>
            
            <div class="dialog-form ">
                <label>Job Title:</label>

                <div class="form-input">
                    <input type="text" id="job_title" name="job_title" maxlength="255" placeholder="Job Title">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Job Level:</label>
                <div class="styled select-dropdown">
                    <select name="job_level" id="job_level">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($jobLevelValues)) {
                            foreach ($jobLevelValues as $jobLevel) {
                                echo '<option value="' . $jobLevel . '">' . $jobLevel . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Company Name:</label>

                <div class="form-input"><input type="text" id="company" name="company" maxlength="100"
                                               placeholder="Company Name"></div>
            </div>
            <div class="dialog-form ">
                <label>Street Address:</label>
                <div class="form-input">
                    <textarea rows="8" cols="10" maxlength="100" placeholder="Address..."id="address1" name="address1" class="textarea-script span9"></textarea>
                </div>
            </div>
            
            <div class="dialog-form ">
                <label>Street Address 2:</label>
                <div class="form-input">
                    <textarea rows="8" cols="10" maxlength="100" placeholder="Address..."id="address2" name="address2" class="textarea-script span9"></textarea>
                </div>
            </div>
             <div class="dialog-form ">
                <label>City:</label>

                <div class="form-input"><input type="text" id="city" name="city" maxlength="25" placeholder="City">
                </div>
            </div>
            <div class="dialog-form ">
                <label>State:</label>
                    <div class="styled select-dropdown">
                        <select name="state" id="state">
                            <option selected="selected" value="">--SELECT--</option>
                            <?php
                            if (!empty($state)) {
                                foreach ($state as $stateValue) {
                                    echo '<option value="'.  trim($stateValue). '">' . $stateValue . '</option>';
                                }
                            }
                            ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form">
                <span class="alert-required">*</span><label>Country:</label>
                <div class="styled select-dropdown">
                    <select name="country" id="country">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($countries)) {
                            foreach ($countries as $country) {
                                echo '<option value="'.  strtoupper(trim($country->country_code)). '">' . $country->country . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Zip:</label>
                <div class="form-input"><input type="text" id="zip" name="zip" maxlength="20" placeholder="Postal Code">
                </div>
            </div>            
            <div class="dialog-form ">
                <label>Time Zone:</label>
                <div class="form-input"><input type="text" id="time_zone" style='text-transform:uppercase' name="time_zone" maxlength="100" placeholder="Time Zone">
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Phone Number:</label>

                <div class="form-input">
                    <input type="text" id="phone" name="phone" required="required" maxlength="20" placeholder="Phone Number">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Phone Number (Hospital):</label>

                <div class="form-input">
                    <input type="text" id="alternate_no" name="alternate_no"  maxlength="20" placeholder="Hospital Phone Number">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Bed Size Range:</label>
                    <div class="styled select-dropdown">
                        <select name="bed_size" id="bed_size">
                            <option selected="selected" value="">--SELECT--</option>
                            <?php
                            if (!empty($bedSizeOptions)) {
                                foreach ($bedSizeOptions as $bedSizeRange) {
                                    echo '<option value="'.  trim($bedSizeRange). '">' . $bedSizeRange . '</option>';
                                }
                            }
                            ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Employee size:</label>

                <div class="styled select-dropdown">
                    <select name="company_size" id="company_size">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($companySizeValues)) {
                            foreach ($companySizeValues as $companySize) {
                                echo '<option value="' . $companySize . '">' . $companySize . '</option>';
                            }
                        }
                        ?>

                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Priority:</label>
                <div class="form-input">
                    <input type="text" id="priority" name="priority" maxlength="3" placeholder="Priority">
                </div>
            </div>
    </div>
        
        
        <div class="popup-btn-group">
            <ul>
                <li>
                    <button type="submit" class="general-btn" id="create_contact_btnSave">Save</button>
                </li>
                <li>
                    <a class="general-btn" id="btnCancel" href="/dialer/contacts/index/<?=$campaign_id?>">Cancel</a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
        </form>
    </div>
</section>
<script type="text/javascript" src="<?php echo base_url($this->path.'/js/pagejs/contacts/create.js') ?>"></script>
