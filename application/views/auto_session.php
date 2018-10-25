<!--</div>-->
 <?php 
        $loggedUserID = $this->session->userdata('uid');
        $userType = $this->session->userdata('user_type');
        $autoDialEnable = $this->cache->memcached->get($loggedUserID.'_AutoDialEnable');
        $agentSessionStatus = $this->cache->memcached->get($loggedUserID.'_AgentAutoSessionStatus');
        $campaignId = $this->session->userdata('AgentSessionCampaignID');
        $buttonValue = ($agentSessionStatus == 1) ? "stop" : "start";
        if(isset($autoDialEnable) && $autoDialEnable != 0 && $this->config->item('auto_dialer_toggle') && ($userType == 'agent' || $userType == 'team_leader')) {
    ?>
<div>
<div class="session-start-stop" id="call_popup_start">
   
    <!--Start RP UAD-11 : for show/hide start and stop button in call dial bar --> 
   
            <div class="col-lg-12 aligncenter">
                <button id="start-session-btn" type="button" class="web-rtc-btn btn-start-session" onclick="startStopSession('start')">
                    <a><b> Start Session</b></a>
                </button>
                <button id="stop-session-btn" type="button" class="web-rtc-btn btn-stop-session" onclick="startStopSession('stop')">
                    <a><b>Stop Session</b></a>
                </button>
            </div>
   
    <!--End RP UAD-11 : for show/hide start and stop button in call dial bar --> 

</div>
</div>
<?php } ?>
<!--// ###################### Start Autodialer Code ############################//-->
<script type="text/javascript">
   $(document).ready(function () {
       var purpose = "<?php echo $buttonValue;?>";
       if(purpose == "start") {
           $("#start-session-btn").css('display','block');
           $("#stop-session-btn").css('display','none');
           $(".has-submenu").css({'pointer-events' : ''});
           $(".dropdown-menu").css({'pointer-events' : ''});
           $(".pop-logout-popup").css({'pointer-events' : ''});
           $(".path-area").css({'pointer-events' : ''});
       } else if (purpose == "stop") {
           $("#start-session-btn").css('display','none');
           $("#stop-session-btn").css('display','block');
           $(".has-submenu").css('pointer-events','none');
           $(".dropdown-menu").css('pointer-events','none');
           $(".pop-logout-popup").css('pointer-events','none');
           $(".path-area").css('pointer-events','none');
       }
   }); 
   function startStopSession(purpose) 
   {
       var campaignId = "<?php echo $campaignId;?>";
       var url = window.location.pathname;
       var redirectUrl = baseUrl+'dialer/calls/autoCalls/'+campaignId;
       $.ajax({
            type: 'POST',
            url:baseUrl+'users/updateAutoAgentStatus',
            data:'campaignId='+campaignId+'&sessionValue='+purpose,
            dataType: 'json'
        }).success(function(response) {
            var status = response['status'];
            if(status == true){
                if(purpose == "start") {
                    $("#stop-session-btn").css('display','none');
                    $("#start-session-btn").css('display','block');
                    $(".has-submenu").css('pointer-events','none');
                    $(".dropdown-menu").css('pointer-events','none');
                    $(".pop-logout-popup").css('pointer-events','none');
                    $(".path-area").css('pointer-events','none');
                    if (url !== redirectUrl) {
                        $(location).attr('href',window.location.origin+redirectUrl);
                    }
                }else if (purpose == "stop") {
                    $("#start-session-btn").css('display','block');
                    $("#stop-session-btn").css('display','none');
                    $(".has-submenu").css({'pointer-events' : ''});
                    $(".dropdown-menu").css({'pointer-events' : ''});
                    $(".pop-logout-popup").css({'pointer-events' : ''});
                    $(".path-area").css({'pointer-events' : ''});
                }
            } 
        }).error(function(response) {

        });
   }
</script>
<!--// ###################### End Autodialer Code ############################//-->
