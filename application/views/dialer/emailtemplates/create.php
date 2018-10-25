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
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'form', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate','enctype' => 'multipart/form-data');
        echo form_open('/dialer/emailtemplates/create/', $attributes);
        ?>
        <div class="form-section-title">
            <p>TEMPLATE DEFINITION</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form">
                <label><span class="alert-required">*</span>Campaign</label>

                <div class="styled select-dropdown">
                    <select name="campaign_id" id="campaign_id" required="required">
                        <option role="option" value=""> ---SELECT ONE---</option>
                        <?php
                        foreach ($campaigns as $campaign) {
                            echo '<option role="option" value="' . $campaign->id . '">' . $campaign->name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
             
            <div class="dialog-form ">
                <label>TM Brand:</label>
                <div class="form-input">
                    <input type="text" id="siteName" readonly placeholder="Site Name" />
                </div>
            </div>
            <div class="dialog-form ">  
               <label><span class="alert-required">*</span>Resource</label>
                <div class="styled select-dropdown ">
                    <select name="resource_id" id="resource_id" class="resourcedropdown" required="required">
                    </select>
                    <input type="hidden" id="resource_name" name="resource_name">
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Subject Line:</label>
                <div class="form-input ">
                    <input type="text" id="subject_line" name="subject_line" placeholder="Subject Line"/>
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Body</label>
                <div class="form-input form-input-width-50">
                    <textarea rows="8" cols="10" placeholder="Body" id="body" name="body" class="textarea-script span9"></textarea>
                </div>
                <div form-input align="right" style="width: 32%;float: right;">
                    <table class="table table-bordered row vertical-tbl sort-th" style="width: 100%;table-layout: fixed; ">
                        <tr><th>Token</th><th>Token Value</th></tr>
                        <tr><td>#DATETIME#</td><td>Date & time</td></tr>
                        <tr><td>#AGENTNAME#</td><td>Agent first name & last name </td></tr>
                    </table>
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Signature Line:</label>
                <div class="form-input">
                    <input type="text" id="signature_line" name="signature_line" placeholder="Signature Line"/>
                </div>
            </div>
        </div>
        <div class="popup-btn-group">
            <ul>
                <li>
                    <button type="submit" tabindex="9" class="general-btn" id="btnSave">Save</button>
                </li>
                <li>
                    <button  type="button"  class="general-btn" tabindex="10" id="btnCancel" onclick="window.location.href='/dialer/emailtemplates/'" >Cancel</button>
                </li>
            </ul>
        </div>
    </form>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript" src="https://s3.amazonaws.com/uberdialer/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/emailtemplates/create.js"></script>

            