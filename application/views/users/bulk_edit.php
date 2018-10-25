<?php  if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good') $class = "class= 'error-msg good'"; else $class = "class='error-msg bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo(' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        }?>
<section class="section-content-main-area">
    <div class="content-main-area">
        
        <link rel="stylesheet" type="text/css" href="<?=$this->config->item('static_url')?>/css/bootstrap.css<?=$this->cache_buster?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->config->item('static_url')?>/css/bootstrap-duallistbox.css<?=$this->cache_buster?>">
        <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/dialer/assigncampaigns/jquery.bootstrap-duallistbox.js<?=$this->cache_buster?>"></script>
        <?php

        if (validation_errors() != '') { ?>
            <div id="divErrorMsg" class="alert alert-warning server-validation-msg">
                <p><strong>Please fix the following input errors:</strong></p><?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php
        $attributes = array('class' => 'popup-form account-detail-dialog assign-form', 'id' => 'form', 'name' => 'form', 'autocomplete' => 'off', 'novalidate' => 'novalidate','enctype' => 'multipart/form-data');
        echo form_open('/users/bulkupdate/', $attributes);
        ?>
        <div class="form-section-title">
            <p>Bulk Edit</p>
            <span></span>
        </div>
        <div class="form-row">
            <div class="dialog-form alignleft clear" id="tm_offices_section">
                <label>Site (From):</label>

                <div class="styled select-dropdown">
                    <select name="tmOfficeFrom" id="tmOfficeFrom" onchange="assignAgentOfficeTl(this);">
                        <?php
                        if (!empty($tmOffices)) {
                            foreach ($tmOffices as $key => $sitesList) {
                                echo '<option value="' . $key . '">' . $sitesList . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form alignleft clear" id="tm_offices_section">
                <label>Site (To):</label>
                <div class="styled select-dropdown">
                    <select name="tmOfficeTo" id="tmOfficeTo">
                        <?php
                        $tmOfficesTo = $tmOffices;
                        if (!empty($tmOfficesTo)) {
                            unset($tmOfficesTo['no_site']);
                            foreach ($tmOfficesTo as $key => $sitesList) {
                                echo '<option value="' . $key . '">' . $sitesList . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form alignright"  id="teamlead_details">
                <label>Team Leader:</label>
                <div class="styled select-dropdown">
                    <select  name="teamlead" id="teamlead" required="required">
                    <option role="option" value=""> ---SELECT ONE--- </option>

                </select>

                </div>
            </div>
                       
        </div>
        <div class="form-row">
            <div class="dialog-form alignleft">
                <div id="selectdiv" class="dialog-form alignleft"></div>
                <input type="hidden" id="oldselectedagents"  name ="oldselectedagents" values ="0">
                <input type="hidden" id="newselectedagents"  name ="newselectedagents" values ="0">
            </div>
        </div>
        <div class="popup-btn-group" id="submit_group" style="display: none;">
            <ul style="text-align: left;">
                <li>
                    <button type="submit" class="general-btn" id="moveAgents">Save</button>
                </li>
                <li>
                    <button  type="button" class="general-btn" id="btnCancel"  onclick="window.location.href='/users/'">Cancel</button>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
        </form>
    </div>
</section>
<script>

</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/users/bulk_edit.js<?=$this->cache_buster?>"></script>
