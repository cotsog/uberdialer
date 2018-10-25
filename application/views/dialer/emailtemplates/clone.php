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
        echo form_open('/dialer/emailtemplates/templateclone/', $attributes);
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
                            if ($campaign->id == $template->campaign_id){
                                $selected = "selected";
                            }else{
                                $selected = "";
                            }  
                            echo '<option value="' . $campaign->id . '" ' . $selected . '>' . htmlspecialchars($campaign->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form ">
                <label>TM Brand:</label>
                <div class="form-input">
                    <input type="text" id="siteName" readonly placeholder="Site Name" value="<?php  echo htmlspecialchars($template->site_name)?>"/>
                </div>
            </div>
            <div class="dialog-form ">  
                <label><span class="alert-required">*</span>Resource</label>               
                <div class="styled select-dropdown ">
                    <select name="resource_id" id="resource_id" class="resourcedropdown" required="required">
                        <?php
                        if (!empty($resources)) {
                            foreach ($resources as $resource) {
                                if ($resource['id'] == $template->resource_id){
                                    $tempvar = 1;
                                    $selected = "selected";
                                }else{
                                    $tempvar = 0;
                                    $selected = "";
                                }    
                                echo '<option value="' . $resource['id'] . '" ' . $selected . '>' . $resource['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" id="resource_name" name="resource_name" 
                            value="<?php if (!form_error("resource_name")) { echo $template->resource_name;
                                } else {
                                    echo $this->input->post('resource_name');
                                }
                                ?>"/>
                </div>                                    
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Subject Line:</label>
                <div class="form-input ">
                    <input type="text" id="subject_line" name="subject_line" placeholder="Suject Line" 
                    value="<?php if (!form_error("subject_line")) { echo htmlspecialchars($template->subject_line);
                    } else {
                        echo $this->input->post('subject_line');
                    }
                    ?>"/>
                </div>
            </div>
            <div class="dialog-form ">
                <label><span class="alert-required">*</span>Body</label>
                <div class="form-input form-input-width-50">
                    <textarea rows="8" cols="10" placeholder="Body" id="body" name="body" class="textarea-script span9"><?php if (!form_error("body")) { echo $template->body;
                        } else {
                            echo $this->input->post('body');
                        }?></textarea>
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
                    <input type="text" id="signature_line" name="signature_line" placeholder="Signature Line" 
                            value="<?php if (!form_error("signature_line")) { echo htmlspecialchars($template->signature_line);} else {
                                echo $this->input->post('subject_line');
                            }
                    ?>"/>
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
        </div>
    </form>
    </div>
    <div class="clearfix"></div>
</section>

<script type="text/javascript">
var resource_array = [];
resource_array = <?php echo json_encode($resources);?>
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/ckeditor/ckeditor.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/emailtemplates/clone.js<?=$this->cache_buster?>"></script>

            