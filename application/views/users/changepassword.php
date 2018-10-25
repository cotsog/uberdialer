<section class="content-area">
    <section class="section-content-main-area">
        <div class="content-main-area">
            <div class="container">
                <div class="change-pass-form">
                    <div class="change-pass-heading">
                        <h3>Change Password</h3>
                    </div>
                      <?php if (validation_errors()) { 
                          if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
                            echo('<div id="divErrorMsg" '.$class.'>');    ?>                           
                            <span><i class="fa fa-times-circle"></i></span><?php echo validation_errors(); ?>
                        </div>
                    <?php } else if($this->session->flashdata('msg') != ''){ ?>
                    <?php     if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
                            echo('<div id="divErrorMsg" '.$class.'>');    ?>        
                            <p><span><i class="fa fa-times-circle"></i></span><?php echo $this->session->flashdata('msg'); ?></p>
                        </div>
                    <?php } ?>
                    <fieldset>
                        <?php
                        $attributes = array('class' => 'popup-form', 'id' => 'form', 'name' => 'form','autocomplete' => 'off');
                        echo form_open_multipart('/users/changepassword', $attributes);
                        ?>

                        <div class="dialog-form col-12 ">
                            <label class="col-12"><span class="alert-required">*</span>Old Password:</label>
                            <div class="form-input"><input type="password" id="old_password" maxlength="15" name="old_password" placeholder="Old Password" value="" autocomplete="off"/></div>
                        </div>
                        <div class="dialog-form col-12 ">
                             <label class="col-12"><span class="alert-required">*</span>New Password:</label>
                             <div class="form-input"><input type="password" id="password" name="password" placeholder="New Password" required="required" maxlength="15" value="<?php echo set_value('new_password'); ?>" /></div>

                        </div>
                        <div class="dialog-form col-12 ">
                             <label class="col-12"><span class="alert-required">*</span>Confirm Password:</label>
                             <div class="form-input"><input type="password" id="passconf" name="passconf"  placeholder="Confirm Password" required="required" data-equals="password" maxlength="15" value="<?php echo set_value('confirm_password'); ?>" /></div>
                        </div>
                        <footer class="clearfix">
                            <input type="submit" id="btnLogin" value="Save"/>                                      
                        </footer>
                        <?php echo form_close(); ?>

                    </fieldset>

                </div>
            </div>

        </div>

    </section>
</section>
   <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery-1.11.1.validate.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/users/changepassword.js<?=$this->cache_buster?>"></script>
