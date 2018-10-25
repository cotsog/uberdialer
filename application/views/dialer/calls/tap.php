<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PureB2B WebRTC Template</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
          integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]>
    <script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <p class="lead text-center">Call Tapper, Operating on Call <?php echo $communication->id; ?>!</p>
            <h2 id="phone-status-text" class="text-center">WebRTC initializing...</h2>

            <div class="btn-group col-md-offset-4" role="group">
                <button id="drop-call-btn" type="button" class="btn btn-danger" disabled="true" onClick="drop_call();">
                    <span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span>
                </button>
            </div>
            <div class="col-md-offset-4 col-md-4 text-center">
                <h3 id="mute-state">Shhhh...</h3>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <p>SIP Host: phone.plivo.com</p>
            <p>SIP Username: <?php if(isset($endpoint->username)) {echo $endpoint->username;} ?></p>
            <p>SIP Password: <?php if(isset($endpoint->password)) { echo $endpoint->password;} ?></p>
        </div>
    </div>
    <input type="hidden" id="web_rtc_qa_id" name="web_rtc_qa_id"
           value="<?php echo $this->session->userdata('uid'); ?>">
</div> <!-- /container -->

<!-- Latest compiled and minified JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
        integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
        crossorigin="anonymous"></script>
<script>
    window.plivo_endpoint_username = "<?php if(isset($endpoint->username)) {echo $endpoint->username;} ?>";
    window.plivo_endpoint_password = "<?php if(isset($endpoint->password)) { echo $endpoint->password;} ?>";
    // join the call
    window.tap_communication_id = "<?php echo $communication->id; ?>";
    window.isMute = true;
    // now include plivo lib & handlers
</script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/pagejs/common_call_dialler.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/timer.jquery.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/plivo/plivo.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/plivo/plivo-handler.js<?=$this->cache_buster?>"></script>
<script>
// see the handler on how to join conversations.
</script>
</body>
</html>
