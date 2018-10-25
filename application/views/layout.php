<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo $meta_title; ?></title>
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
    <script>
        var logged_user_type = '<?php echo $this->session->userdata("user_type");?>';
        var app_module_type = '<?php echo $this->app_module_type; ?>';
	</script>
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/font-awesome.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery-ui.min.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery.navgoco.min.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/jquery.webui-popover.min.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/ui.jqgrid.min.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/custom.css<?=$this->cache_buster?>" />
    <link rel="stylesheet" href="<?=$this->config->item('static_url')?>/css/magnific-popup.css<?=$this->cache_buster?>" />
    <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.min.js<?=$this->cache_buster?>"></script>
    <script type="text/javascript" src="https://s3.amazonaws.com/enterprise-guide/js/bootstrap.min.js<?=$this->cache_buster?>"></script>
    <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery-ui.min.js<?=$this->cache_buster?>"></script>
    <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.nicescroll.min.js<?=$this->cache_buster?>"></script>
    <script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.magnific-popup.js<?=$this->cache_buster?>"></script>
     

</head>
<body>
    
<div class="main-wrapper clearfix ">
    <section class="header-section">
        <div class="logo-div"></div>
                <div class="user-div theme-blue <?='user-bg'.$this->app ?>">
                    <img class="admin-logo" src="https://s3.amazonaws.com/enterprise-guide/logos/admin/uber_dialer_trans_65x65.png" />
            <ul id="nav">
                <li>
                    <p>UBER DIALER</p>
                    <p><?php echo $this->app_logo; ?></p><p class="char_alert<?=$this->fileAppend ?>">Note: Please make sure that you dial the number with correct country code.</p>
                    <!--<img src="<?php // echo base_url('/images/pb2b.png'); ?>" />-->
                </li>
                <li>
                    <!--                            <a href="#" class="pop-shuffle-popup"></a>-->
                    <!--                            <a href="#" class="pop-notification-popup"><span>2</span></a>-->
                    <ul class="user-profile-cont">
                                <li class="dropdown user-dropdown">
                                    <a href="#" class="dropdown-toggle user-name" data-toggle="dropdown">
                                        <i class="user-profile-icon"></i><span>Hi <?=$this->session->userdata('user_fname')?></span><i class="fa fa-angle-down"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="/users/profile"><i class="fa fa-fw fa-user"></i>Profile</a>
                                        </li>
                                        <li>
                                            <a href="<?=$this->config->item('sso_url').'users/changepassword'?>"><i class="fa fa-fw fa-lock"></i>Change Password</a>
                                        </li>
                                        <?php if(!empty($this->app_access_list)){foreach ($this->app_access_list as $app_access) { ?>
                                            <li>
                                                <a href="<?=$app_access->url?>"><i class="fa fa-fw fa-level-up"></i><?=$app_access->name?></a>
                                            </li>
                                        <?php }} ?>
                                    </ul>
                                </li>
                            </ul>
                            <!--<a href="<?php echo $this->uri_route; ?>?switch=true" class="pop-shuffle-popup" target="_blank"></a>-->
                    <a href="/logout" class="pop-logout-popup"></a>
                </li>
            </ul>
        </div>

        <div id="dialog" class="user-detail-dialog-top userdetail" style="display: none" >
            <div class="user-detail">
                <div id="tabs">

                    <div class="popup-wraper">
                        <?php  $this->load->view('users/userdetails'); ?>
                    </div>
                </div>
            </div>
    </section>
    <?php $this->load->view('menu'); ?>
    <section class="content-area">
        <a href="#"><i class="fa menu fa-navicon"></i></a>

        <div class="path-area">
            <ul>
                <?php
                if (isset($crumbs)) {
                    echo($crumbs);
                } else {
                    echo('<li id="crumbs">&nbsp;</li>');
                }
                ?>
                <!--<li>Dashboard <i class="fa fa-angle-right"></i></li>
                <li>Campaigns</li>-->
            </ul>
        </div>
        <?php $this->load->view($main); ?>
    </section>
</div>
<div class="callout">
  <div class="callout-header">Callback Reminder</div>
  <span class="closebtn" onclick="this.parentElement.style.display='none';">×</span>
  <div class="callout-container" id="callback-details">  </div>
</div>
<?php
$this->load->view('auto_session');
$server_uri = $_SERVER['REQUEST_URI'];
$callsIndex = "dialer/calls/index";
$callsCreate = "dialer/calls/create";
$datateam = "dialer/datateam";
if(strpos($server_uri, "changepassword") == false && (strpos($server_uri, $callsIndex) == true || strpos($server_uri, $callsCreate) == true || strpos($server_uri, $datateam) == true)){
    $this->load->view('call_dialler');
}
// End RP UAD-11 : display call dialbar in list page to change below condition
?>
<div id="dialog-confirm-redirect" title="Logout" class="dialog-confirm-redirect" style="display: none;">
    <p>You are logged-out by administrator. You will be redirected to login page.</p>
    <ul>
    </ul>
    <div class="popup-btn-group">
        <ul>
            <li><a class="general-btn" href="/logout">OK</a></li>
        </ul>
    </div>
</div>
<div id="dialog-campaign-signout-confirm-redirect" title="Logout" class="dialog-confirm-redirect" style="display: none;">
    <p>You are logged-out by administrator. You will be redirected to Campaigns page.</p>
    <ul>
    </ul>
    <div class="popup-btn-group">
        <ul>
            <li><a class="general-btn" href="/<?php echo $this->app_module_name; ?>/campaigns">OK</a></li>
        </ul>
    </div>
</div>
<script>window.baseUrl = "/"</script>

<script type="text/javascript"src="<?=$this->config->item('static_url')?>/js/idle.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/common.js<?=$this->cache_buster?>"></script>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.webui-popover.min.js<?=$this->cache_buster?>"></script>
<!--custom scroll js-->
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/jquery.cookie.min.js<?=$this->cache_buster?>"></script>
<!--left nav js-->

<!--left menu collpase expand js-->
<script type="text/javascript"src="<?=$this->config->item('static_url')?>/js/menu-custom.min.js<?=$this->cache_buster?>"></script>

<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/JQGrid/grid.locale-en.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/JQGrid/jquery.jqGrid.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/JQGrid/jqgrid_common.js<?=$this->cache_buster?>"></script>

<!--Top Layout js-->
<script type="text/javascript">

    $(function () {

        //bind tooltip of top navigation: shuffle
        initPopover();
        function initPopover() {

                    //bind tooltip of top navigation: user detail
        var listSettings = {
                        content: '<p>Change Password</p>',
            title: '',
            style: 'inverse',
            padding: false,
            placement: 'bottom',
            animation: 'pop',
            trigger: 'hover',
                        width: 150,
            height: 10,
            delay: {show: 0, hide: 200}
        };
                    $('.pop-user-popup').webuiPopover('destroy').webuiPopover(listSettings);
        
                    //bind tooltip of top navigation: logout
        var listSettings = {
                        content: '<p>Logout</p>',
            title: '',
            style: 'inverse',
            padding: false,
                        placement: 'bottom-left',
            animation: 'pop',
            trigger: 'hover',
                        width: 70,
            height: 10,
            delay: {show: 0, hide: 200}
        };
                    $('.pop-logout-popup').webuiPopover('destroy').webuiPopover(listSettings);

        //bind tooltip of top navigation: logout
        var listSettings = {
                        content: '<p>Notifications</p>',
            title: '',
            style: 'inverse',
            padding: false,
            placement: 'bottom-left',
            animation: 'pop',
            trigger: 'hover',
                        width: 100,
            height: 10,
            delay: {show: 0, hide: 200}
        };
                    $('.pop-notification-popup').webuiPopover('destroy').webuiPopover(listSettings);
                }

                $("#notification-dialog").dialog({
                    position: { my: "right+10 top+10", at: "left bottom", of: $('.pop-logout-popup') },
                    closeOnEscape: true,
                    draggable: false,
                    height: 100,
                    resizable: false,
                    autoOpen: false,
                    minWidth: 400,
                    dialogClass: 'noclose user-detail-dialog dialog-corner-all ui-fixed-dialog',
                    clickOutside: true,
                    clickOutsideTrigger: ".pop-notification-popup",
                    show: {
                        effect: "blind",
                        duration: 100
                    },
                    hide: {
                        effect: "blind",
                        duration: 100
                    },
                    open: function (event, ui) {
                        //close the user popup if it is open.
                        if ($("#dialog").dialog("isOpen") == true) {
                            $("#dialog").dialog("close");
                        }
                        destroyPopover();
                    },
                    close: function (event, ui) {
                        initPopover();
                        //bind click event of user detail popup
                        $('.pop-notification-popup').click(function () {
                            openNotificationPopup();
                        });
                    }
                });

                $('.pop-notification-popup').click(function () {
                    openNotificationPopup();
                });

                $("#notification-dialog").dialog('widget').find('.ui-dialog-titlebar').html("<div class='field-group-title '><span></span><a></a>Notifications<div class='alignright icon-arrow'></div></div>");

                function openNotificationPopup() {

                    var isOpen = $("#notification-dialog").dialog("isOpen");
                    console.log(isOpen);
                    if (isOpen) {
                        $("#notification-dialog").dialog("close");
                    }
                    else {

                        $("#notification-dialog").dialog("open");
                    }
                }

                //destroy popover when popup is open
                function destroyPopover() {
                    $('.pop-user-popup').webuiPopover('destroy');
                    $('.pop-notification-popup').webuiPopover('destroy');
                    $('.pop-shuffle-popup').webuiPopover('destroy');
                    $('.pop-logout-popup').webuiPopover('destroy');
                }

        //bind current date time to top navigation
        $("#currentdatetime").text(getCurrentDateTime());

        //user detail dialog box
        $("#dialog").dialog({
            position: {my: "right+50 top+10", at: "left bottom", of: $('.pop-user-popup')},
            closeOnEscape: false,
            draggable: false,
            resizable: false,
            autoOpen: false,
            minWidth: 660,
            dialogClass: 'noclose user-detail-dialog',
            show: {
                effect: "blind",
                duration: 100
            },
            hide: {
                effect: "blind",
                duration: 100
            }
        });

        //toggle popup user
        $('.pop-user-popup').click(function () {
            var isOpen = $("#dialog").dialog("isOpen");
            if (isOpen)
                $("#dialog").dialog("close");
            else
                $("#dialog").dialog("open");

            //close the notification if it is open.
            if ($("#notification-dialog").dialog("isOpen") == true) {
                $("#notification-dialog").dialog("close");
            }

        });

        //add title and upper site arrow
        $("#dialog").dialog('widget').find('.ui-dialog-titlebar').html("<div class='field-group-title'>USER DETAILS<div class='alignright icon-arrow'></div></div>");

    });
    var sign_in_user_type = '<?php echo $this->session->userdata('user_type'); ?>';

    $(document).ready(function () {
        if(window.location.pathname.indexOf("dialer") > -1 && window.location.pathname.indexOf("datateam") == -1){
            $('#user_lists').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#utilities_lists').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#dialer').addClass('active open module_segment');
            $("#dialer_nav").slideToggle('slow');

        }else if(window.location.pathname.indexOf("appt") > -1){
            $('#user_lists').removeClass('active open module_segment');
            $('#dialer').removeClass('active open module_segment');
            $('#utilities_lists').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#appointment').addClass('active open module_segment');
            $("#appointment_nav").slideToggle('slow');
        }else if(window.location.pathname.indexOf("users") > -1){
            $('#dialer').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#utilities_lists').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#user_lists').addClass('active open module_segment');
            $("#user_nav").slideToggle('slow');
        }else if(window.location.pathname.indexOf("utilities") > -1){
            $('#dialer').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#user_lists').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#utilities_lists').addClass('active open module_segment');
            $("#utilities_nav").slideToggle('slow');
        }else if(window.location.pathname.indexOf("datateam") > -1){
            $('#dialer').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#user_lists').removeClass('active open module_segment');
            $('#utilities_lists').removeClass('active open module_segment');
            $('#data_research_team').addClass('active open module_segment');
        }
        $("#dialer").click(function () {
            $("#appointment_nav").css('display','none');
            $("#user_nav").css('display','none');
            $('#user_lists').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#dialer').addClass('active open module_segment');
            $("#dialer_nav").slideToggle();
        });
        $("#appointment").click(function () {
            $("#dialer_nav").css('display','none');
            $("#user_nav").css('display','none');
            $('#dialer').removeClass('active open module_segment');
            $('#user_lists').removeClass('active open module_segment');
            $('#appointment').addClass('active open module_segment');
            $("#appointment_nav").slideToggle();
        });
        $("#user_lists").click(function () {
            $("#dialer_nav").css('display','none');
            $("#appointment_nav").css('display','none');
            $('#dialer').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#user_lists').addClass('active open module_segment');
            $("#user_nav").slideToggle();
        });
        $("#utilities_lists").click(function () {
            $("#dialer_nav").css('display','none');
            $("#appointment_nav").css('display','none');
            $("#user_nav").css('display','none');
            $('#dialer').removeClass('active open module_segment');
            $('#appointment').removeClass('active open module_segment');
            $('#user_lists').removeClass('active open module_segment');
            $('#data_research_team').removeClass('active open module_segment');
            $('#utilities_lists').addClass('active open module_segment');
            $("#utilities_nav").slideToggle();
        });
    });

</script>
<div id="feedback-launcher" >Feedback</div>
    <div class="feedback-form">
    <?php
    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    ?>
        <iframe id = "feedback-frame" class = "feedback-frame" src=""></iframe>
    </div>
    <script type="text/javascript">
        $(document).ready(function(){
        $('#feedback-launcher').click(function(){
            document.getElementById("feedback-frame").src="<?=$this->config->item('eg_admin')?>/feedback/?url=<?=urlencode($actual_link); ?>&email=<?=urlencode($this->session->userdata('user_email')); ?>";
            $('.feedback-form').toggle('slow');    
            var text = $('#feedback-launcher').text();
            $('#feedback-launcher').text(
            text == "Feedback" ? "Close" : "Feedback");
        });
        })
    </script>
<style>

<?php
    if(strpos($server_uri, "changepassword") == false && (strpos($server_uri, $callsIndex) == true || strpos($server_uri, $callsCreate) == true || strpos($server_uri, $datateam) == true)){
?>
    .session-start-stop {
      bottom: 50px !important;
    }
<?php
    }
?>
/*Call Popup css starts*/
.session-start-stop {
  background: #f4f4f4 none repeat scroll 0 0;
  border-top: 1px solid #eee;
  bottom: 0px;
  -webkit-box-shadow:0 0 3px #a6a6a6;
  -moz-box-shadow:0 0 3px #a6a6a6;
  box-shadow:0 0 3px #a6a6a6;
  /*padding: 0 20px;*/
  position: fixed;
  width:100%;
  z-index: 99;
  height: 50px;
}

.btn-start-session{
    background-color: #489F48;
    background-image: none;
    color: #ffffff !important;
    cursor: pointer;
    float: right;
    margin: 3px 25px 0px 0px;
    margin-left: 45%;
    margin-right: 45%;
    /*display: none;*/
}

.btn-stop-session{
    background-color: #CA3C38;
    background-image: none;
    color: #ffffff !important;
    cursor: pointer;
    float: right;
    margin: 3px 25px 0px 0px;
    display: none;
    margin-left: 45%;
    margin-right: 45%;
}
#feedback-launcher{
    position: fixed!important;
    bottom: 60px;
    left: 50px;
    z-index: 999999999;
    padding:15px;
    background: #20B2BB !important;
    color: #ffffff;
    border-radius:100px;
    cursor: pointer;
    }
.feedback-form{
    position: fixed!important;
    left :20px;
    z-index: 999999999;
    /* padding: 15px; */
    width: 350px;
    height: calc(75% - 20px - 75px - 20px)!important;
    bottom: calc(50px  + 75px)!important;
    display:none
}
.feedback-frame{
    z-index:9999999999;
    border: 1px #20B2BB solid;
    border-radius: 15px;
    width: 100%!important;
    height: 100%!important;;
}


.callout {
  display: none;
  position: fixed;
  bottom: 70%;
  right: 20px;
  margin-left: 20px;
  max-width: 350px;
  border: #96928d solid 1px;
}

.callout-header {
  padding: 3% 3%;
  background: #0093e7;
  font-size: 16px;
  color: white;
}

.callout-container {
  padding: 6%;
  background-color: #fff;
  line-height: 1.3;
  font-size: 14px;
}

.closebtn {
  position: absolute;
  top: 5%;
  right: 15px;
  color: white;
  font-size: 20px;
  cursor: pointer;
}

.closebtn:hover {
  color: lightgrey;
}
</style>
</body>
</html>
