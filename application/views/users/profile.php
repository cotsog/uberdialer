<section class="section-content-main-area">
    <div class="content-main-area">
        <?php
        $attributes = array('class' => 'popup-form account-detail-dialog campaign-form', 'id' => 'form', 'name' => 'form', 'novalidate' => 'novalidate');
        echo form_open('/users/edit/' . $user->id, $attributes);
        ?>
        <div class="form-section-title">
            <p>USER PROFILE</p>
            <span></span>
        </div>


        <div class="form-row">
            <div class="dialog-form alignleft background-none">
                <label>First Name:</label>
                <div class="form-input">
                <?php 
                    echo htmlspecialchars($user->first_name);
                ?>
                </div>
            </div>
            <div class="dialog-form alignleft background-none">
                <label>Last Name:</label>
                <div class="form-input">
                <?php 
                    echo htmlspecialchars($user->last_name);
                ?>
                </div>
            </div>
           
            <div class="dialog-form alignleft background-none">
                <label>Email Address:</label>
                <div class="form-input">
                <?php 
                    echo htmlspecialchars($user->email);
                ?>
                </div>
            </div>
            

            <div class="dialog-form alignleft">
                <label>Type:</label>
                <div class="styled select-dropdown">
                        <?php
                            echo $userTypes[$user->user_type];
                        ?>                    
                </div>
            </div>
            <div class="dialog-form alignright"  id="teamleads_details">
                <label>Group/Team:</label>
                <div class="styled select-dropdown">
                   <?php
                    foreach ($members_list as $group_member) {
                        if ($group_member->id != $user->id) {
                            if ($group_member->id == $user->parent_id){
                                echo htmlspecialchars($group_member->member_name);
                            }
                        }
                    }
                    ?>
                </div>
            </div>

        </div>

<div class="popup-btn-group">

</div>
<input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>
<input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>
<input type="hidden" name="selected_user_type" value="<?php echo $user->user_type; ?>"/>
        <?php echo form_close(); ?>
</div>
    <div class="clearfix"></div>
</section>
