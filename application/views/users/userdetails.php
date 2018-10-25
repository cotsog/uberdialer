<div class="account-detail-popup"> 
    <?php
    $id = $this->session->userdata('uid');
    $attributes = array('class' => 'popup-form', 'id' => 'userdetail_form', 'name' => 'userdetail_form', 'novalidate' => 'novalidate');
    echo form_open('/users/updateProfile/' . $this->session->userdata('uid'), $attributes);
    ?>

    <div class="alignleft form-left">
        <div class="dialog-form <?php if($this->config->item('sso_is_enabled')) { echo 'background-none'; }?>">
            <label>First Name:</label>
            <input type="text" id="userfname" name="first_name" <?php if($this->config->item('sso_is_enabled')) { echo 'disabled'; }?> required="required" value="<?php echo $this->session->userdata('user_fname'); ?> "/>
        </div>                                              
    </div>
    <div class="alignleft form-right">
        <div class="dialog-form <?php if($this->config->item('sso_is_enabled')) { echo 'background-none'; }?>">
            <label>Last Name:</label>
            <input type="text" id="userlname" name="last_name" <?php if($this->config->item('sso_is_enabled')) { echo 'disabled'; }?> required="required" value="<?php echo $this->session->userdata('user_lname');  ?> "/>
        </div>
    </div>
    <div class="alignleft form-left">
        <div class="dialog-form background-none">
            <label>Type:</label>
            <?php
            if($this->session->userdata('user_type')){
                if($this->session->userdata('user_type') == 'agent'){
                    $type = "Agent";
                }else if ($this->session->userdata('user_type') == 'qa'){
                    $type = "QA";
                }else if ($this->session->userdata('user_type') == 'team_leader'){
                    $type = "Team Leader";
                }else if ($this->session->userdata('user_type') == 'dataresearch_user'){
                    $type = "Data Research User";
                }else if ($this->session->userdata('user_type') == 'admin'){
                    $type = "TM Admin";
                }
                else{
                    $type = "Manager";
                }
            }else{
                $type = $this->session->userdata('user_type');
            }?>
            <input type="text" id="user_type" name="user_type" disabled required="required" value="<?= isset($type)?$type:$this->session->userdata('user_type');?>" />
        </div>
    </div>
    <div class="alignleft form-right">
        <div class="dialog-form background-none">
            <label>E-mail:</label>
            <input type="text" id="uemail" readonly  required="required" <?php if($this->config->item('sso_is_enabled')) { echo 'disabled'; }?> name="email" value="<?php echo $this->session->userdata('user_email'); ?>"/>
        </div>
     </div>
                    <?php
    $group = $this->session->userdata('group');
    if(!empty($group)){?>
        <div class="dialog-form alignleft custom-details background-none"  id="teamleads_details">
            <label><span class="alert-required">*</span>Group/Team:</label>
            <input type="text" id="usergroup" name="Group" disabled  value="<?php echo $group;?>" />
        </div>
   <?php  }?>
    <?php if(!$this->config->item('sso_is_enabled')) { ?>
    <div class="popup-btn-group user-notify-btn">
        <ul>
            <li><button type="submit" class="general-btn" id="btnSaveUser">Save</button></li>
            <li><a id="btnCancelUser" class="general-btn">Cancel</a></li>
        </ul>
    </div><?php } ?>
            
    <?php echo form_close(); ?>
    <!--</form>-->
</div>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery-1.11.1.validate.min.js"></script>
<script>
    $(function () {
        selected_usertype = $("#user_type option:selected").text().toLowerCase();
    });
    $('#btnCancelUser').click(function () {
        $("#dialog").dialog("close");
    });
    $("#user_type").change(function () {
        usertype = $("#user_type option:selected").text().toLowerCase();
        if (usertype == "qa" || usertype == 'agent') {
            $("#teamleads_details").show();
        } else {
            $("#teamleads_details").hide();
        }
    });
    $('#user_type').focus(function () {
        prev_val = $(this).val();
    }).change(function () {
        $(this).blur();
        var success = confirm('Are you sure you want to change the user type?');
        if (success)
        {
            usertype = $("#user_type option:selected").text().toLowerCase();
            if (usertype == "qa" || usertype == 'agent') {
                $("#teamleads_details").show();

            } else {
                $("#teamleads_details").hide();
            }
        }
        else
        {
            $(this).val(prev_val);
            return false;
        }
    });
    $('#userdetail_form').validate({
        rules: {
            first_name: "required",
            last_name: "required"
        },
        messages: {
            first_name: "",
            last_name: ""
        }
    });

</script>