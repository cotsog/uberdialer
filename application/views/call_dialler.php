<!--</div>-->
<div>
    <input type="hidden" id="web_rtc_agent_id" name="web_rtc_agent_id"
           value="<?php echo $this->session->userdata('uid'); ?>">
    <div class="add-new-number dialog-form" id="add-new-number" style="display: none;">
        <div style="width:205px">
  <ul id="dialpad_items"> 
      <li class="dialpad_list" onClick="send_dtmf_values('1');"><a class="btn btn-info" href="javascript:void(0)">1</a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('2');"><a class="btn btn-info" href="javascript:void(0)" >2 <span>ABC</span> </a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('3');"><a class="btn btn-info" href="javascript:void(0)" >3 <span>DEF</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('4');"><a class="btn btn-info" href="javascript:void(0)" >4 <span>GHI</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('5');"><a class="btn btn-info" href="javascript:void(0)" >5 <span>JKL</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('6');"><a class="btn btn-info" href="javascript:void(0)">6 <span>MNO</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('7');"><a class="btn btn-info" href="javascript:void(0)">7 <span>PQRS</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('8');"><a class="btn btn-info" href="javascript:void(0)">8 <span>TUV</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('9');"><a class="btn btn-info" href="javascript:void(0)" >9 <span>WXYZ</span></a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('*');"><a class="btn btn-info" href="javascript:void(0)" >*</a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('0');"><a class="btn btn-info" href="javascript:void(0)" >0</a></li>
      <li class="dialpad_list" onClick="send_dtmf_values('#');"><a class="btn btn-info" href="javascript:void(0)" >#</a></li>
  </ul>
    </div>
        <input type="text" id="call_new_number" name="call_new_number" maxlength="15" onkeypress='return isNumberKey(event)'
               value="<?php //if (!empty($contactCallDetail->phone)) {  echo $contactCallDetail->phone; }  ?>"/>
    </div>
</div>

<div class="call-popup" id="call_popup_start">
    <div class="col-lg-6 alignleft">
        <div class="call-contact-info">

            <h2 id="phone-status-text" class="text-center">WebRTC initializing...</h2><span id="dialer_text" style="display: none;"></span>
            <h3 id="contact_calling_info" style="display: none;">
                <span id="prefix_call_text">Dialing : </span>
                    <span><?php if (!empty($contactCallDetail->first_name)) { echo $contactCallDetail->first_name;}
                        if (!empty($contactCallDetail->last_name)) { echo " " . $contactCallDetail->last_name; } ?></span>
<i class="fa fa-sort-up"></i>
</h3>
</div>
</div>
    <!--Start RP UAD-11 : for show/hide start and stop button in call dial bar --> 
    <?php 
        $userType = $this->session->userdata('user_type');
        $autoDialEnable = $this->session->userdata('AutoDialEnable');
        $agentSessionStatus = $this->session->userdata('AgentAutoSessionStatus');
        $campaignId = $this->session->userdata('AgentSessionCampaignID');
        $buttonValue = ($agentSessionStatus == 1) ? "stop" : "start";
        if(isset($autoDialEnable) && $autoDialEnable != 0 && ($userType == 'agent' || $userType == 'team_leader')) {
    ?>
            <div class="col-lg-3 alignleft">
                <button id="start-session-btn" type="button" class="web-rtc-btn btn-start-session" onclick="start_stop_session('start')">
                    <a><b> Start Session</b></a>
                </button>
                <button id="stop-session-btn" type="button" class="web-rtc-btn btn-stop-session" onclick="start_stop_session('stop')">
                    <a><b>Stop Session</b></a>
                </button>
            </div>
    <?php } ?>
    <!--End RP UAD-11 : for show/hide start and stop button in call dial bar --> 
<div class="col-lg-3 alignright">
        <div class="call-navigation">
            <ul style="padding: 5px 20px;">
                <li style="padding: 10px 12px;" id="dial-call-pad"><a><i class="fa fa-th"></i></a></li>

                <!-- <li id="dial-call-pad"><a><i class="fa fa-th"></i></a></li>-->
            <li class="end-call-pointer">
                <button id="pause-call-btn" type="button" class="web-rtc-btn btn-pause-call" disabled="true" onclick="pause_call();">
                    <a><i id="mute-state" class="fa fa-microphone"></i></a>
                </button>
            </li>
            
             <!--<li onclick="pause_call()"><a><i class="fa fa-microphone-slash"></i></a></li>-->

            <li class="end-call-pointer">
                 <?php if($this->isConference && (empty($this->isConferenceOffices) || in_array($this->session->userdata('telemarketing_offices'), $this->isConferenceOffices) || $this->session->userdata('user_type') == 'admin')){
                    $makeCall = "make_call(1);";
                 }else{
                    $makeCall = "make_call(0);";
                 }
                ?>
                <button id="make-call-btn" type="button" class="web-rtc-btn disable-btn-make-call" onclick="<?php echo $makeCall;  ?>">
                    <a>Direct  <i class="fa fa-phone"></i></a>
                </button>
            </li>
            <li class="end-call-pointer">
                <button id="drop-call-btn" type="button" class="web-rtc-btn btn-drop-call" disabled="true" onclick="drop_call();">
                    <a><i class="fa fa-phone"></i></a>
                </button>
            </li>

            <input type="hidden" id="dial_pad_call_start_datetime" name="dial_pad_call_start_datetime"
                   value="">
            <input type="hidden" id="dial_pad_call_end_datetime" name="dial_pad_call_end_datetime"
                   value="">
            <input type="hidden" id="last_call_history_id" name="plivo_last_call_history_id"
                   value="<?php echo set_value('plivo_last_call_history_id'); ?>">
        </ul>
        <h4 class="call-timer" id="display-call-time"></h4>

        <input type="hidden" value="<?php if (!empty($contactCallDetail->phone)) {
            if(isset($contactCallDetail->dial_code)) {
                echo $contactCallDetail->dial_code;
            }
            echo $contactCallDetail->phone;} ?>" name="connectedno" id="connectedno">

    </div>
</div>
</div>
<script type="text/javascript">
    window.plivo_endpoint_username = "<?php echo $this->session->userdata('plivo_endpoint_username'); ?>";
    window.plivo_endpoint_password = "<?php echo $this->session->userdata('plivo_endpoint_password'); ?>";
    window.call_campaign_name = "<?php echo isset($contactCallDetail->name)?$contactCallDetail->name:"";?>";
    window.app_name = "<?php echo isset($this->app)?$this->app:"";?>";
    window.module_type = "<?php echo $this->app_module_type; ?>";
    // now include plivo lib & handlers
</script>

<script src="<?=$this->config->item('static_url')?>/js/pagejs/common_call_dialler.js<?=$this->cache_buster?>" type="text/javascript"></script>
<script src="<?=$this->config->item('static_url')?>/js/timer.jquery.min.js<?=$this->cache_buster?>" type="text/javascript"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/plivo/plivo.min.js<?=$this->cache_buster?>"></script>
<script type="text/javascript" src="<?=$this->config->item('static_url')?>/js/plivo/plivo-handler.js<?=$this->cache_buster?>"></script>

<!--// ###################### Start Autodialer Code ############################//-->
<script type="text/javascript">
   $(document).ready(function () {
       var purpose = "<?php echo $buttonValue;?>";
       if(purpose == "start") {
           $("#stop-session-btn").css('display','none');
           $("#start-session-btn").css('display','block');
       } else if (purpose == "stop") {
           $("#start-session-btn").css('display','none');
           $("#stop-session-btn").css('display','block');
       }
   }); 
   function start_stop_session(purpose) 
   {
       var campaign_id = "<?php echo $campaignId;?>";
       $.ajax({
            type: 'POST',
            url:baseUrl+'users/updateAutoAgentStatus',
            data:'campaign_id='+campaign_id+'&session_value='+purpose,
            dataType: 'json'
        }).success(function(response) {
            var status = response['status'];
            if(status == true){
                if(purpose == "start") {
                    $("#stop-session-btn").css('display','block');
                    $("#start-session-btn").css('display','none');
                }else if (purpose == "stop") {
                    $("#start-session-btn").css('display','block');
                    $("#stop-session-btn").css('display','none');
                }
            } 
        }).error(function(response) {

        });
   }
</script>
<!--// ###################### End Autodialer Code ############################//-->
