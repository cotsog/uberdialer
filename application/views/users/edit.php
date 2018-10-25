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
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'form', 'name' => 'form', 'novalidate' => 'novalidate');
        echo form_open('/users/edit/' . $members->id, $attributes);
        ?>
        <div class="form-section-title">
            <p>USER DEFINITION</p>
            <span></span>
        </div>


        <div class="form-row">
            <input type ="hidden" id="user_id" value="<?=$members->id?>"/>
            <div class="dialog-form alignleft <?php if($this->config->item('sso_is_enabled')) { echo 'background-none'; }?>">
                <label><span class="alert-required">*</span>First Name:</label>
                <div class="form-input"><input type="text" id="first_name" name="first_name" required="required" <?php if($this->config->item('sso_is_enabled')) { echo 'readonly="readonly"'; }?> maxlength="100" value="<?php
                if (!form_error("first_name")) {
                    echo htmlspecialchars($members->first_name);
                } else {
                    echo htmlspecialchars($this->input->post('first_name'));
                }
                ?>"/>
                </div>
            </div>
            <div class="dialog-form alignright <?php if($this->config->item('sso_is_enabled')) { echo 'background-none'; }?>">
                <label><span class="alert-required">*</span>Last Name:</label>
                <div class="form-input"><input type="text" id="last_name" name="last_name" required="required" <?php if($this->config->item('sso_is_enabled')) { echo 'readonly="readonly"'; }?> maxlength="255" value="<?php
                if (!form_error("last_name")) {
                    echo htmlspecialchars($members->last_name);
                } else {
                    echo htmlspecialchars($this->input->post('last_name'));
                }
                ?>"/>
                </div>
            </div>
            <?php if(!$this->config->item('sso_is_enabled')) { ?>
            <div class="dialog-form alignleft">
                <label>Password:</label>
                <div class="form-input">
                    <input type="password" id="password" name="password" placeholder="New Password" maxlength="15" value="<?php echo set_value('new_password'); ?>" />
                </div>
            </div>
            <div class="dialog-form alignright">
            <label>Confirm Password:</label>
            <div class="form-input"><input type="password" id="password_confirm" name="password_confirm"  placeholder="Confirm Password" data-equals="password" maxlength="15" value="<?php echo set_value('confirm_password'); ?>" /></div>
            </div>
            <?php } ?>
            <div class="dialog-form alignleft <?php if($this->config->item('sso_is_enabled')) { echo 'background-none'; }?> ">
                <label><span class="alert-required">*</span>Email Address:</label>
                <div class="form-input">
                    <input type="text" id="email" name="email" <?php if($this->config->item('sso_is_enabled')) { echo 'readonly="readonly"'; }?> required="required" maxlength="100" value="<?php
                        if (!form_error("email")) {
                            echo $members->email;
                        } else {
                            echo $this->input->post('email');
                        }
                        ?>" />
                </div>
            </div>


            <?php 
            $userTypeDisable = true;
            if(!in_array($this->session->userdata['user_type'], $upperManagementTypes) || $this->session->userdata['user_type'] == 'admin') {
                $userTypeDisable = false;
            } 
            ?>
            <div class="dialog-form alignleft" id="user_type_section">
                <label>Type:</label>
                <div class="styled select-dropdown">
                    <select name="user_type" id="user_type1" <?php echo $userTypeDisable ? 'disabled="disabled"' : ''; ?>>
                        <?php 
                        
                            foreach($allUserTypes as $userType => $label){
                                $selected = $members->user_type == $userType ? 'selected="selected"' : '' ;
                                if (($this->session->userdata['user_type'] =='team_leader' && $userType == 'agent')
                                        || ($this->session->userdata['user_type'] =='manager' && !in_array($userType, $upperManagementTypes))
                                        || in_array($this->session->userdata['user_type'], $upperManagementTypes)
                                        ) {
                                    echo "<option role='option' value='{$userType}' {$selected}>{$label}</option>";
                                }
                            }
                        ?>                       
                    </select>
                </div>
            </div>
            <div class="dialog-form alignleft clear" id="tm_offices_section">
                <label> <span class="alert-required">*</span>Telemarketing Offices:</label>

                <div class="styled select-dropdown">
                    <select <?php if((!in_array($this->session->userdata('user_type'), $upperManagementTypes) && $this->session->userdata('user_type') != 'manager') || count($getEGWebsitesList) == 1){echo 'disabled'; } ?> name="telemarketing_offices" id="telemarketing_offices">
                        <?php
                        if (!empty($getEGWebsitesList)) {
                            foreach ($getEGWebsitesList as $key => $sitesList) {
                                if ($key == $members->telemarketing_offices)
                                    $selected = "selected";
                                else
                                    $selected = "";

                                echo '<option value="' . $key . '" ' . $selected . '>' . $sitesList . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="dialog-form radio-alignment-popup" id="module_section" style="<?php if($members->user_type=='admin'){echo "display:none";}?>" >
                <label class="alignleft"><span class="alert-required">*</span>Module:</label>

                <div class="styled select-dropdown">
                    <select <?php if(!in_array($this->session->userdata('user_type'), $upperManagementTypes)) { echo 'disabled'; } ?> name="module[]" id="module" multiple>
                        <?php if(!empty($getModuleTypeValues)){ foreach($getModuleTypeValues as $key_module => $module_value){
                            if (!empty($module_value) && in_array($key_module, $members->module))
                                $selected = "selected";
                            else
                                $selected = "";

                            echo '<option role="option" value="' . $key_module . '" ' . $selected . '>' . $module_value . '</option>';
                        } ?>

                        <?php } ?>
                    </select>
                </div>
            </div>
            <input type="hidden" value="<?php echo $members->parent_id ? $members->parent_id : "" ?>" name="previous_parent_id">
            <div class="dialog-form alignright"  id="teamleads_details">
                <label><span class="alert-required">*</span>Group/Team:</label>
                <div class="styled select-dropdown">
                    <select  name="teamleads" id="teamleads" required="required">
                    <option role="option" value=""> ---SELECT ONE--- </option>
                    <?php
                    foreach ($members_list as $group_member) {
                        //if ($group_member->id != $members->id) {
                            if ($group_member->id == $members->parent_id)
                                $selected = "selected";
                            else
                                $selected = "";
                            echo '<option role="option" value="' . $group_member->id . '" ' . $selected . '>' . htmlspecialchars($group_member->member_name) . '</option>';
                        //}
                        }
                    ?>
                </select>

                </div>
            </div>
            
            <div class="dialog-form alignleft clear">
                <label>Hired Date:</label>

                <div class="form-input date-picker">
                    <input type="text" id="hired_date" name="hired_date" placeholder="Hired date" 
                           value="<?php if (!form_error("hired_date")) { 
                                    echo $members->hired_date;
                                    } else {
                                        echo $this->input->post('hired_date');
                                    }
                                ?>"/>
                </div>
            </div>

            <div class="dialog-form alignleft" id="tier_section" style=
                <?php  echo 'display:block;'?>>
                <label>Tier:</label>          
                <div class="styled select-dropdown">
                   <select name="tier" id="tier">
                        <option role="option" value="1" <?= $members->tier == '1' ? 'selected="selected"' : '' ?>>1</option>
                        <option role="option" value="2" <?= $members->tier == '2' ? 'selected="selected"' : '' ?>>2</option>
                        <option role="option" value="3" <?= $members->tier == '3' ? 'selected="selected"' : '' ?>>3</option>        
                    </select>
                </div>
            </div>
            <div class="dialog-form alignleft" id="project_section">
                <label>Project:</label>          
                <div class="styled select-dropdown" id="project_selector">
                   <select name="project" id="project">
                        <option role="option" value=""> ---SELECT ONE---</option>
                       <?php if(!empty($members->module[0]) && $members->module[0] == 'tm'){ ?>
                        <option role="option" value="LG" <?= $members->project == 'LG' ? 'selected="selected"' : '' ?>>LeadGen</option>
                        <option role="option" value="MQL" <?= $members->project == 'MQL' ? 'selected="selected"' : '' ?>>MQL</option>
                        <option role="option" value="MDG" <?= $members->project == 'MDG' ? 'selected="selected"' : '' ?>>MDG</option>   
                        <option role="option" value="CSTC" <?= $members->project == 'CSTC' ? 'selected="selected"' : '' ?>>CSTC</option> 
                       <?php }else{ ?>
                           <option role="option" value="LG" <?= $members->project == 'LG' ? 'selected="selected"' : '' ?>>LeadGen</option>
                           <option role="option" value="MQL" <?= $members->project == 'MQL' ? 'selected="selected"' : '' ?>>MQL</option>
                           <option role="option" value="MDG" <?= $members->project == 'MDG' ? 'selected="selected"' : '' ?>>MDG</option>
                           <option role="option" value="CSTC" <?= $members->project == 'CSTC' ? 'selected="selected"' : '' ?>>CSTC</option>
                       <?php } ?>
                    </select>
                </div>
            </div>

            <div class="dialog-form alignleft">
                <label>Schedule:</label>          
                <div class="styled select-dropdown">
                   <select name="schedule" id="schedule">
                        <option role="option" value=""> ---SELECT ONE---</option>
                        <option role="option" value="8am-5pm EST" <?= $members->schedule == '8am-5pm EST' ? 'selected="selected"' : '' ?>>8am-5pm EST</option>
                        <option role="option" value="9am-6pm EST" <?= $members->schedule == '9am-6pm EST' ? 'selected="selected"' : '' ?>>9am-6pm EST</option>
                        <option role="option" value="10am-7pm EST" <?= $members->schedule == '10am-7pm EST' ? 'selected="selected"' : '' ?>>10am-7pm EST</option>        
                    </select>
                </div>
            </div>
            <div class="dialog-form radio-alignment-popup">
                <label class="alignleft"><span class="alert-required">*</span>Status:</label>
                <input type="hidden" id= "temp_status" name = "temp_status" value ="<?= $members->status?>"/>
                <div class="styled select-dropdown">
                    <select name="status" id="status">
                        <option role="option" value="Active" <?= $members->status == 'Active' ? 'selected="selected"' : '' ?>>Active</option>
                        <option role="option" value="InActive" <?= $members->status == 'InActive' ? 'selected="selected"' : '' ?>>InActive</option>
                        <option role="option" value="Released" <?= $members->status == 'Released' ? 'selected="selected"' : '' ?>>Released</option>
                        <option role="option" value="Resigned" <?= $members->status == 'Resigned' ? 'selected="selected"' : '' ?>>Resigned</option>                   
                    </select>
                </div>    
            </div>            
        </div>

<div class="popup-btn-group">
    <ul>            
        <li><button type="submit" class="general-btn" id="btnSave" >Save</button></li>         
        <li><button type="button" class="general-btn" id="btnCancel" onclick="window.location.href='/users/'">Cancel</button></li>
           
    </ul>
</div>
<input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>
<input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>
<input type="hidden" name="selected_user_type" value="<?php echo $members->user_type; ?>"/>
        <?php echo form_close(); ?>
</div>
    <div class="clearfix"></div>
</section>
<script type="text/javascript">
var upperManagementTypes = ["<?php echo implode('","', $upperManagementTypes); ?>"];
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/users/edit_user.js<?=$this->cache_buster?>"></script>
