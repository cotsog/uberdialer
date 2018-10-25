<?php
if (!empty($pass_reset)) {
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/login.css<?=$this->cache_buster?>" />
        <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/font-awesome.css<?=$this->cache_buster?>" />        
    </head>
    <body>
    <div class="container">
        <div id="login-form">
            <div class="banner-img">
                <img src="/images/logo-2x.png" />
            </div>
            <?php
            if (validation_errors() != '') {
                echo('<div id="divErrorMsg" class="error-msg bad"><p><strong>Please fix the following input errors:</strong></p>');
                //echo '<span><i class="fa fa-times-circle"></i></span>';
                echo(validation_errors());
                echo('</div>');
            }
            ?>
            <!--        <div id="divErrorMsg" class="error-msg bad" style="display: none">-->
            <!--            <p><i class="fa fa-times-circle"></i></span>Invalid username password. Please try again.</p>-->
            <!--        </div>-->
            <h3>LOGIN</h3>
            <fieldset>
                <?php
                $attributes = array('class' => 'form-horizontal', 'id' => 'passwordform');
                echo form_open('/passwordreset/' . $token, $attributes);
                echo form_hidden('m_id', $pass_reset->user_id);
                echo form_hidden('r_id', $pass_reset->id);
               ?>
                <!--<form novalidate>-->
                <div class="dialog-form form-element">
                    <label>New Password:</label>
                  
                    <input type="password" id="password" name="password" required="required" maxlength="15" value="<?php echo set_value('new_password'); ?>"/>
                   
                    <?php //echo form_error("password"); ?>
                </div>
                <div class=" dialog-form form-element" style="margin-top: 20px;">
                    <label>Confirm New Password:</label>
                    <input type="password" id="passconf" name="passconf" required="required" data-equals="password" maxlength="15" value="<?php echo set_value('confirm_password'); ?>" />
                    <?php //echo form_error("passconf"); ?>
                </div>

                <footer class="clearfix">
                    <input type="submit" class="general-btn" id="btn-resetpassword" value="SAVE"/>
                    <input type="button" class="general-btn" onclick="window.location = '/login';" value="CANCEL"/><br>
                </footer>
                <input type="hidden" name="access_on_ids" id="access_on_ids" value=""/>
                <div class="clearfix"></div>
                <?php form_close(); ?>
            </fieldset>
            <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.min.js"></script>
        </div>
    </div>
    <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery-1.11.1.validate.min.js<?=$this->cache_buster?>"></script>
    <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/additional-methods.min.js"></script>
    <script type="text/javascript">
        $('#passwordform').validate({
            rules: {
                password: {
                    minlength: 5
                },
                passconf: {
                    minlength: 5,
                    equalTo: "#password"
                }
            },
            messages: {
                password: {
                    required: "",
                    minlength: "Your password must be at least 5 characters long"
                },
                passconf: {
                    required: "",
                    minlength: "Your password must be at least 5 characters long",
                    equalTo: "Please enter the same password as above"
                }
            }
        });    
        $(function () {
            $('.error').css({float:"none"});
           // $('#passwordform').validate_popover({onsubmit: false, popoverPosition: 'top'});

//            $("#btn-resetpassword").click(function (ev) {
//                var valid = $('#passwordform').validate().form();
//
//                if (valid == false) {
//                    ev.preventDefault();
//                    return false;
//                }
//            });

            $(window).resize(function () {
                $.validator.reposition();
            });
        });
    </script>
    <style>
        #login-form input[type="password"] { border-top: 1px solid #efefee; }
    </style>
    </body>
    </html>

<?php
}
?>