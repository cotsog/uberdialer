<head><script>
    var validNavigation = false;
function wireUpEvents() { 

  window.onbeforeunload=goodbye;
 
  // Attach the event keypress to exclude the F5 refresh
  $(document).bind('keypress', function(e) {
    if (e.keyCode == 116){
      validNavigation = false;
    }
  });
 
  // Attach the event click for all links in the page
  $("a").bind("click", function() {
    validNavigation = false;
  });
 
  // Attach the event submit for all forms in the page
  $("form").bind("submit", function() {
    validNavigation = true;
  });
 
  // Attach the event click for all inputs in the page
  $("input[type=submit]").bind("click", function() {
    validNavigation = true;
  });
}
 
// Wire up the events as soon as the DOM tree is ready
$(document).ready(function() {
  wireUpEvents();
});</script></head>

<?php if ($this->session->flashdata('msg') != '') { ?>
    <div id="divErrorMsg" class="error-msg bad">
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
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'editform', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate');
        echo form_open('/dialer/contacts/edit/'.$contactDetail->id.'/'.$contactDetail->campaign_id.'/'.$contactDetail->list_id, $attributes);
        ?>
        <input type="hidden" id="member_id" name="member_id" value="<?php echo isset($contactDetail->member_id)?$contactDetail->member_id:set_value('member_id'); ?>">
        <input type="hidden" id="locked_by" name="locked_by" value="<?php echo isset($contactDetail->locked_by)?$contactDetail->locked_by:set_value('locked_by'); ?>">
        <div class="form-section-title">
            <p>CONTACT DEFINITION</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Email:</label>

                <div class="form-input">
                    <input type="email" id="email" name="email" required="required" maxlength="100" placeholder="Email" value="<?php echo isset($contactDetail->email)?$contactDetail->email:set_value('email'); ?>" readonly />
                </div>
            </div>
            
            <div class="dialog-form">
                <label><span class="alert-required">*</span>First Name:</label>
                <div class="form-input">
                    <input type="text" id="first_name" name="first_name" required="required" maxlength="100" placeholder="First Name" value="<?php echo isset($contactDetail->first_name)?$contactDetail->first_name:set_value('first_name'); ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Last Name:</label>

                <div class="form-input">
                    <input type="text" id="last_name" name="last_name" required="required" maxlength="100" placeholder="Last Name" value="<?php echo isset($contactDetail->last_name)?$contactDetail->last_name:set_value('last_name'); ?>">
                </div>
            </div>

            <div class="dialog-form ">
                <label>Job Title:</label>

                <div class="form-input">
                    <input type="text" id="job_title" name="job_title"  maxlength="255" placeholder="Job Title" value="<?php echo isset($contactDetail->job_title)?$contactDetail->job_title:set_value('job_title'); ?>">
                </div>
            </div>

            <div class="dialog-form ">
                <label>Job Level:</label>

                <div class="styled select-dropdown">
                    <select name="job_level" id="job_level">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($jobLevelValues)) {
                            foreach ($jobLevelValues as $jobLevel) { ?>
                                <option role="option" value="<?= $jobLevel;?>" <?= $contactDetail->job_level == $jobLevel ? 'selected="selected"' : '' ?>><?= $jobLevel;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Job Function:</label>

                <div class="styled select-dropdown">
                    <select name="job_function" id="job_function">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($jobFunctionValues)) {
                            foreach ($jobFunctionValues as $jobFunction) { ?>
                                <option role="option" value="<?= $jobFunction;?>" <?= $contactDetail->job_function == $jobFunction ? 'selected="selected"' : '' ?>><?= $jobFunction;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Company Name:</label>

                <div class="form-input"><input type="text" id="company" name="company" maxlength="100" placeholder="Company Name" value="<?php echo isset($contactDetail->company)?$contactDetail->company:set_value('company'); ?>"></div>
            </div>

            <div class="dialog-form ">
                <label>Company size:</label>

                <div class="styled select-dropdown">
                    <select name="company_size" id="company_size">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($companySizeValues)) {
                            foreach ($companySizeValues as $companySize) {
                                if($companySize == '1-9'){
                                    $companySize = '1 to 9';
                                }
                                if($companySize == '10-24'){
                                    $companySize = '10 to 24';
                                }
                                ?>
                                <option role="option" value="<?= $companySize;?>" <?= $contactDetail->company_size == $companySize ? 'selected="selected"' : '' ?>><?= $companySize;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="dialog-form ">
                <label>Industry:</label>

                <div class="styled select-dropdown">
                    <select name="industry" id="industry">
                        <option selected="selected" value="">--SELECT--</option>
                        <?php
                        if (!empty($industriesValues)) {
                            foreach ($industriesValues as $industries) { ?>
                                <option role="option"
                                        value="<?= $industries; ?>" <?= $contactDetail->industry == $industries ? 'selected="selected"' : '' ?>><?= $industries; ?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Phone Number:</label>

                <div class="form-input"> <input type="text" id="phone" name="phone" required="required" maxlength="20" placeholder="Phone Number" value="<?php echo isset($contactDetail->phone)?$contactDetail->phone:set_value('phone'); ?>"></div>
            </div>
            <div class="dialog-form ">
                <label>Alternate Number:</label>

                <div class="form-input"> <input type="text" id="alternate_no" name="alternate_no" maxlength="20" placeholder="Alternate Number" value="<?php echo isset($contactDetail->alternate_no)?$contactDetail->alternate_no:set_value('alternate_no'); ?>"></div>
            </div>
            <div class="dialog-form ">
                <label>Time Zone:</label>

                <div class="form-input">  <input type="text" id="time_zone" name="time_zone" style='text-transform:uppercase'  maxlength="100" placeholder="Time Zone" value="<?php echo isset($contactDetail->time_zone)?$contactDetail->time_zone:set_value('time_zone'); ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>City:</label>

                <div class="form-input">  <input type="text" id="city" name="city" maxlength="100" placeholder="City" value="<?php echo isset($contactDetail->city)?$contactDetail->city:set_value('city'); ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>Postal Code:</label>

                <div class="form-input"> <input type="text" id="zip" name="zip"  maxlength="20" placeholder="Postal Code" value="<?php echo isset($contactDetail->zip)?$contactDetail->zip:set_value('zip'); ?>">
                </div>
            </div>
            <div class="dialog-form ">
                <label>State:</label>

                <div class="form-input"><input type="text" id="state" name="state" maxlength="100" placeholder="State" value="<?php echo isset($contactDetail->state)?$contactDetail->state:set_value('state'); ?>">
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
                                $countryCode = trim($country->country_code);
                                $country = trim($country->country);?>
                                <option role="option" value="<?= $countryCode;?>" <?= $contactDetail->country == $countryCode ? 'selected="selected"' : '' ?>><?= $country;?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>Street Address:</label>

                <div class="form-input">
                    <textarea rows="8" cols="10" maxlength="300" placeholder="Address..." id="address" name="address" class="textarea-script span9"><?php echo isset($contactDetail->address)?$contactDetail->address:set_value('address'); ?></textarea>
                </div>
            </div>
            <div class="dialog-form ">
                <label>priority:</label>
                <div class="form-input">
                    <input type="text" id="priority" name="priority" maxlength="3" placeholder="Priority" value="<?php echo isset($contactDetail->priority)?$contactDetail->priority:set_value('priority'); ?>">
                </div>
            </div>
           
            <input type="hidden" id="id" name="id"  value="<?php echo isset($contactDetail->id)?$contactDetail->id:set_value('id'); ?>">
            <input type="hidden" id="campaign_id" name="campaign_id"  value="<?php echo isset($contactDetail->campaign_id)?$contactDetail->campaign_id:set_value('campaign_id'); ?>">
            <input type="hidden" id="list_id" name="list_id"  value="<?php echo isset($contactDetail->list_id)?$contactDetail->list_id:set_value('list_id'); ?>">
        </div>

        <div class="popup-btn-group">
            <ul>
                <li>
                    <button type="submit" class="general-btn" id="edit_contact_btnSave">Save</button>
                </li>
                <li>
                    <button type="button" class="general-btn btnCancel-view-list" id="btnCancel" name="btncancel" >Cancel</button>
                </li>
            </ul>
        </div>
        
        </form>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript">
    var contact_id = <?php echo isset($contactDetail->id) ? $contactDetail->id:''; ?>;
    var campaign_id = <?php echo isset($contactDetail->campaign_id) ? $contactDetail->campaign_id:''; ?>;
    var list_id = <?php echo isset($contactDetail->list_id) ? $contactDetail->list_id:''; ?>;
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/contacts/edit.js<?=$this->cache_buster?>"></script>
