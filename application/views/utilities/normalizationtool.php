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
        $attributes = array('method' => 'post', 'class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'site_form', 'name' => 'form', 'autocomplete' => 'off');
        echo form_open('/utilities/normalizationtool/', $attributes);
        ?>
            <div class="form-section-title">
                <p>Normalization Tool</p>
                <span></span>
            </div>
            <div class="form-row">
                <div class="dialog-form">
                    <label>Job Title:</label>

                    <div class="form-input"><input type="text" id="job_title" name="job_title" placeholder="Job Title" required value=""/>

                        <p id="empty-message"></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($normalize)) { ?>

            <div class="form-row">
                <div class="dialog-form">
                    <label><b>Job Level: <?=$normalize['job_level']?></b></label>
                </div>
            </div>

            <div class="form-row">
                <div class="dialog-form">
                    <label><b>Job Silo:  <?=$normalize['silo']?></b></label>
                </div>
            </div>

            <?php } ?>

            <div class="popup-btn-group">
                <ul>
                    <li>
                        <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                    </li>
                </ul>
            </div>

        </form>
    </div>
    <div class="clearfix"></div>
</section>