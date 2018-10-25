<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Admin - Login</title>
        <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/login.css<?=$this->cache_buster?>" />
        <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/font-awesome.css<?=$this->cache_buster?>" />
        <?php $this->load->view('_metaname.php'); ?>            
    </head>
<body>
<div class="container">
    <div id="login-form">
        <div class="banner-img">
            <img src="/images/logo-2x.png" />
        </div>
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('msg') != '') {
            if ($this->session->flashdata('class') == 'good')
                $class = "class= 'error-msg good'";
            else
                $class = "class='error-msg  bad'";
            echo('<div id="divErrorMsg" ' . $class . '>');
            echo (' <p><span><i class="fa fa-times-circle"></i></span>');
            echo $this->session->flashdata('msg');
            echo('</div>');
        }
        ?>
        <?php
        if (validation_errors() != '') {
            echo('<input type="hidden" id="checkvalidation" name="checkvalidation" value="no" />');
            if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
            echo('<div id="divErrorMsg" '.$class.'>');     
            echo (' <span><i class="fa fa-times-circle"></i></span>');
            echo(validation_errors());
            
            echo('</div>');
        }
        ?>
        <?php
        if ((isset($msg) && $msg != '') || $this->session->flashdata('prev_action') != '') {
            if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
            echo('<div id="divErrorMsg" '.$class.'>');            
            echo (' <p><span><i class="fa fa-times-circle"></i></span>');
            if ($this->session->flashdata('prev_action') == 'logout') {
                echo 'You have been logged out';
            } elseif ($this->session->flashdata('prev_action') == 'nosession') {
                echo 'Please log in';
            } elseif ($this->session->flashdata('prev_action') == 'loginfail') {
                echo 'Invalid Credentials. Please try again<input type="hidden" id="checkvalidation" name="checkvalidation" value="no" /></p>';
            } elseif ($this->session->flashdata('prev_action') == 'inactiveuser') {
                echo 'Sorry, Your account is an inactive, please contact to administrator.<input type="hidden" id="checkvalidation" name="checkvalidation" value="no" /></p>';
            }elseif (isset($msg)) {
                echo '' . $msg . '';
            }
            echo('</div>');
        }
        if ($this->session->flashdata('forgotpass') != '') {
              
            if ($this->session->flashdata('forgotpass') == 'passwordsent') {            
            echo('<div id="divErrorMsg" class="error-msg good">');   
                echo ('<p><span><i class="fa fa-times-circle"></i></span><strong>Well done! </strong>');
                echo 'Password Reset instructions have been e-mailed to you.</p>';
            }
            if ($this->session->flashdata('forgotpass') == 'doesntexist') {                
            echo('<div id="divErrorMsg" class="error-msg bad">');   
                echo ('<p><span><i class="fa fa-times-circle"></i></span><strong>Oh snap! </strong>');
                echo 'Email does not exist.</p>';
            }
            echo('</div>');
        }
        if ($this->session->flashdata('resetpass') != '') {

            if ($this->session->flashdata('resetpass') == 'resetsuccess') {
                if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
            echo('<div id="divErrorMsg" class="error-msg good">');
                echo ('<p><span><i class="fa fa-times-circle"></i></span><strong>Well done! </strong>');
                echo 'Your password has been reset</p>';
                echo('</div>');
            } elseif ($this->session->flashdata('resetpass') == 'tokenexpired') {
                if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
            echo('<div id="divErrorMsg" '.$class.'>');     
                echo ('<strong>Oh snap! </strong>');
                echo 'Your reset token was not found or may have expired. You will have to request a new password reset token <a href="/password">here</a>.';
                echo('</div>');
            } elseif ($this->session->flashdata('resetpass') == 'resetfailed') {
                if($this->session->flashdata('class')=='good') $class="class= 'error-msg good'" ; else $class="class='error-msg  bad'";           
            echo('<div id="divErrorMsg" '.$class.'>');     
                echo ('<p><span><i class="fa fa-times-circle"></i></span>Oh snap! </strong>');
                echo 'Failed to reset password. You will have to request a new password reset token <a href="/password">here</a></p>.';
                echo('</div>');
            }
        }
        ?>
        <div id="divErrorMsg" class="error-msg bad" style="display: none">
            <p><span><i class="fa fa-times-circle"></i></span>Invalid username password. Please try again.</p>
        </div>
        <h3>LOGIN</h3>
        <fieldset>
            <?php
            $attributes = array('class' => 'form-horizontal', 'id' => 'form' ,'autocomplete' => 'off',);
            echo form_open('/login', $attributes);
            ?>
            <div class="dialog-form  form-element">                 
                <input  type="email" id="email" name="email" required="required" maxlength="100" value="<?php echo set_value('email'); ?>"   /> 
                <span><i class="fa fa-user"></i></span>
                <?php echo form_error('email'); ?>                 
            </div>
            <div class=" dialog-form  form-element">                            
                <input type="password" id="password" name="password" required="required" maxlength="15" value="<?php echo set_value('password'); ?>"   /> 

                <span class="no-b-border"><i class="fa fa-lock"></i></span>
                <?php //echo form_error('password'); ?>
            </div>

            <div class="form-element alignment">
                <div>
                    <input type="checkbox" value="true" name="RememberMe" id="chkRemember" data-val-required="The RememberMe field is required." data-val="true" class="css-checkbox">
                        <label class="css-label radGroup1" for="chkRemember">Remember Me?</label>
                        <span class="hidden"><input type="hidden" value="false" name="remember_me"></span>
                </div>
                <div>
                    <a class="forgot-password-popup" href="#">Forgot Password?</a>
                </div>
                <footer class="clearfix">
                    <input type="submit" value="SIGN IN" name="btnsubmit" id="btnlogin"/>
                </footer>
            </div>
            <?php echo form_close(); ?>
            <div class="form-element alignment">
                <?php
                $attributes = array('class' => 'form-horizontal', 'id' => 'form1');
                echo form_open('/password', $attributes);
                ?>
                <div style="display: none" class="form-element" id="forgotPassword">
                    <input type="email" value="" placeholder="Email" name="email" id="email" data-val-required="Email is required" data-val-regex-pattern="^[A-Za-z0-9_\+-]+(\.[A-Za-z0-9_\+-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.([A-Za-z]{2,4})$" data-val-regex="Invalid Email Format" data-val-length-max="255" data-val-length="The field Email must be a string with a maximum length of 255." data-val="true"/>
                        <span>
                            <button type="submit" class="btn btn-primary">                                        
                                <i class="fa fa-arrow-circle-right"></i>
                            </button>
                        </span>
                        <div>
                            <span data-valmsg-replace="true" data-valmsg-for="Email" class="field-validation-valid text-danger"></span>
                        </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </fieldset>       
    </div> 
</div>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery-1.11.1.validate.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/login.js<?=$this->cache_buster?>"></script>
</body>
</html>