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
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'site_form', 'name' => 'form', 'autocomplete' => 'off');
        echo form_open('/utilities/editsite/'.$id, $attributes);
        ?>
        <div class="form-section-title">
            <p>Edit TM Site</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form">
                <label>Name:</label>

                <div class="form-input"><input type="text" id="name" name="name" placeholder="Name" required value="<?=$tm_office[0]['name']?>"/>

                    <p id="empty-message"></p>
                </div>
            </div>
            <div class="dialog-form">
                <label>Select Parent:</label>
                    <div class="form-input">
                        <select name="siteId" id="siteId">
                            <option selected="selected" value="">--SELECT--</option>
                            <?php
                            if (!empty($siteIds)) {
                                foreach ($siteIds as $site) {
                                    $siteId = trim($site['id']);
                                    $siteName = trim($site['name']);?>
                                    <option role="option" value="<?= $siteId;?>" <?= $siteId == $tm_office[0]['parent_id'] ? 'selected="selected"' : '' ?>><?= $siteName;?></option>
                                <?php }
                            }
                            ?>
                        </select>
                    </div>
            </div>

        </div>

        <div class="popup-btn-group">
            <ul>
                <li>
                    <button type="submit" class="general-btn" id="campaign_btnSave">Save</button>
                </li>
                <li>
                    <button type="button" class="general-btn" id="btnCancel"
                            onclick="window.location.href='/utilities/sites/'">Cancel
                    </button>
                </li>
            </ul>
        </div>

        </form>
    </div>
    <div class="clearfix"></div>
</section>