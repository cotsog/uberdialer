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
    echo form_open('/users/create/', $attributes);
    ?>
    <div class="form-section-title">
        <p>USER DEFINITION</p>
        <span></span>
    </div>
    <div class="form-row">
        <div class="dialog-form alignleft">
            <label><span class="alert-required">*</span>First Name:</label>
            <div class="form-input"><input tabindex="1" type="text" id="first_name" name="first_name" required="required" maxlength="100"
                   value="<?php echo set_value('first_name'); ?>"/>
            </div>
        </div>
        <div class="dialog-form alignright">
            <label><span class="alert-required">*</span>Last Name:</label>
            <div class="form-input"><input tabindex="2" type="text" id="last_name" name="last_name" required="required" maxlength="100"
                   value="<?php echo set_value('last_name'); ?>"/>
            </div>
        </div>
        <div class="dialog-form alignleft">
            <label><span class="alert-required">*</span>Password:</label>
            <div class="form-input"><input tabindex="3" type="password" id="password" name="password" required="required" maxlength="15"
                   value="<?php echo set_value('password'); ?>"/>
            </div>
        </div>
        <div class="dialog-form alignright">
            <label><span class="alert-required">*</span>Confirm Password:</label>
            <div class="form-input"><input tabindex="4" type="password" id="password_confirm" name="password_confirm" required="required" maxlength="15"
                   value="<?php echo set_value('password_confirm'); ?>"/>
            </div>
        </div>
        <div class="dialog-form alignleft">
            <label>Type:</label>
           
            <div class="styled select-dropdown">
               <select name="user_type" id="user_type1">
                <?php  if($this->session->userdata['user_type'] =='admin' || $this->session->userdata['user_type'] =='manager'){?>
                    <option role="option" value="agent">Agent</option>
                    <option role="option" value="dataresearch_user">Data Research User</option>
                    <option role="option" value="qa">QA</option>
                    <option role="option" value="team_leader">Team Leader</option>
                    <option role="option" value="manager">Manager</option>
                    <option role="option" value="admin">TM Admin</option>
                <?php }else if($this->session->userdata['user_type'] =='team_leader'){?>
                    <option role="option" value="agent">Agent</option>
                <?php }?>    
                </select>
            </div>
        </div>
        <div class="dialog-form alignleft clear" id="tm_offices_section">
            <label> <span class="alert-required">*</span>Telemarketing Offices:</label>
         
            <div class="styled select-dropdown">
                <select <?php if($this->session->userdata('user_type') == 'team_leader'){echo 'disabled'; } ?> name="telemarketing_offices" id="telemarketing_offices">
                    <?php
                    if (!empty($getEGWebsitesList)) {
                        foreach ($getEGWebsitesList as $key => $sitesList) {
                            if ($key == $this->session->userdata('telemarketing_offices'))
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
        <div class="dialog-form radio-alignment-popup" id="module_section">
            <label class="alignleft"><span class="alert-required">*</span>Module:</label>
            <div class="styled select-dropdown">
                <select <?php if($this->session->userdata('user_type') != 'admin'){echo 'disabled'; } ?> name="module[]" id="module" multiple>
                    <?php if(!empty($getModuleTypeValues)){
                        foreach($getModuleTypeValues as $key=>$module_value){
                        if (!empty($module_value) && in_array($key, $this->session->userdata('module')))
                            $selected = "selected";
                        else
                            $selected = "";

                        echo '<option role="option" value="' . $key . '" ' . $selected . '>' . $module_value . '</option>';
                        ?>

                    <?php } } ?>
                </select>
            </div>
        </div>
        <div class="dialog-form alignright" id="teamleads_details">
            <label><span class="alert-required">*</span>Group/Team:</label>

            <div class="styled select-dropdown">
                <select name="teamleads" id="teamleads" required="required">
                    <option role="option" value=""> ---SELECT ONE---</option>
                    <?php
                    foreach ($members_list as $group_member) {
                        if($group_member->id == $this->session->userdata['uid']){
                            echo '<option role="option" value="' . $this->session->userdata['uid'] . '" selected>' . $group_member->member_name . '</option>';
                        }else{
                            echo '<option role="option" value="' . $group_member->id . '">' . $group_member->member_name . '</option>';
                        }
                        
                    }
                    ?>
                </select>

            </div>
        </div>
        <div class="dialog-form alignleft clear">
            <label><span class="alert-required">*</span>Email Address:</label>
            <div class="form-input"><input tabindex="5" type="email" id="email" name="email" required="required" maxlength="100"
                   value="<?php echo set_value('email'); ?>"/>
            </div>
        </div>
        <div class="dialog-form alignleft clear">
            <label>Hired Date:</label>

            <div class="form-input date-picker">
                <input type="text" id="hired_date" name="hired_date" placeholder="Hired date" value="<?php echo set_value('hired_date'); ?>"/>
            </div>
        </div>

        <div class="dialog-form alignleft" id="tier_section">
            <label><span class="alert-required">*</span>Tier:</label>          
            <div class="styled select-dropdown">
               <select name="tier" id="tier">
                    <option role="option" value="1">1</option>
                    <option role="option" value="2">2</option>
                    <option role="option" value="3">3</option>        
                </select>
            </div>
        </div>
        <div class="dialog-form alignleft" id="project_section">
            <label>Project:</label>          
            <div class="styled select-dropdown" id="project_selector">
               <select name="project" id="project">
                    <option role="option" value=""> ---SELECT ONE---</option>
                   <?php if(count($this->session->userdata('module')) == '1' && in_array('tm',$this->session->userdata('module'))){?>
                       <option role="option" value="LG">LeadGen</option>
                       <option role="option" value="MQL">MQL</option>
                       <option role="option" value="MDG">MDG</option>
                       <option role="option" value="CSTC">CSTC</option>
                   <?php } ?>
                   <?php if(count($this->session->userdata('module')) > 1){?>
                       <option role="option" value="LG">LeadGen</option>
                       <option role="option" value="MQL">MQL</option>
                       <option role="option" value="MDG">MDG</option>
                       <option role="option" value="CSTC">CSTC</option>
                   <?php }?>
                </select>
            </div>
        </div>
        
        <div class="dialog-form alignleft">
            <label>Schedule:</label>          
            <div class="styled select-dropdown">
               <select name="schedule" id="schedule">
                    <option role="option" value=""> ---SELECT ONE---</option>
                    <option role="option" value="8am-5pm EST">8am-5pm EST</option>
                    <option role="option" value="9am-6pm EST">9am-6pm EST</option>
                    <option role="option" value="10am-7pm EST">10am-7pm EST</option>        
                </select>
            </div>
        </div>
        <div class="dialog-form radio-alignment-popup">
            <label class="alignleft"><span class="alert-required">*</span>Status:</label>
            <div class="styled select-dropdown">
                <select name="status" id="status">
                    <option role="option" value="Active">Active</option>
                    <option role="option" value="InActive">InActive</option>
                    <option role="option" value="Released">Released</option>
                    <option role="option" value="Resigned">Resigned</option>                   
                </select>
            </div>    
        </div>

        <div class="dialog-form alignright">
            <label>Read-Only:</label>
            <div class="form-input">
                <input type="checkbox" tabindex="6" id="is_readonly" class="css-checkbox" name="is_readonly" maxlength="100" value="0" />
                <label class="css-label checkbox-label radGroup1 cst-export-lbl" for="is_readonly"></label>
            </div>
        </div>
        
    </div>
    <div class="popup-btn-group">
        <ul>
            <li>
                <button type="submit" tabindex="9" class="general-btn" id="btnSave">Save</button>
            </li>
            <li>
                <button  type="button"  class="general-btn" tabindex="10" id="btnCancel" onclick="window.location.href='/users/'" >Cancel</button>
            </li>
            
        </ul>
    </div>
    <input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>

  
    </form>
    </div>
      <div class="clearfix"></div>
</section>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/users/create.js"></script>
