
$( document ).ready(function() {
	console.log( "HTML ready!" );
	initPhone();
	login(window.plivo_endpoint_username, window.plivo_endpoint_password);
});	


function date(){
	return (new Date()).toISOString().substring(0, 10)+" "+Date().split(" ")[4];
}

function login(username, password) {
		if(username && password){
			//start UI load spinner
			plivoWebSdk.client.login(username, password);
			
		}else{
			console.error('username/password missing!')
		}
}
function audioDeviceChange(e){
	console.log('audioDeviceChange',e);
	if(e.change){
		if(e.change == "added"){
			customAlert(e.change,e.device.kind +" - "+e.device.label,'info');		
		}else{
			customAlert(e.change,e.device.kind +" - "+e.device.label,'warn');
		}
	}else{
		customAlert('info','There is an audioDeviceChange but mediaPermission is not allowed yet');
	}
}
function onWebrtcNotSupported() {
	console.warn('no webRTC support');
	alert('Webrtc is not supported in this broswer, Please use latest version of chrome/firefox/opera/IE Edge');
}
function mediaMetrics(obj){
	/**
	* Set a trigger for Quality FB popup when there is an warning druing call using sessionStorage
	* During `onCallTerminated` event check for `triggerFB` flag
	*/
	sessionStorage.setItem('triggerFB',true);
	console.table([obj]);
	var classExist = document.querySelector('.-'+obj.type);
	var message = obj.type;
	/**
	* If there is a same audio level for 3 samples then we will get a trigger
	* If audio level is greater than 30 then it could be some continuous echo or user is not speaking
	* Set message "same level" for audio greater than 30. Less than 30 could be a possible mute  	
	*/
	if(obj.type.match('audio') && obj.value > 30){
		message = "same level";
	}
	if(obj.active){
		classExist? classExist.remove() : null; 
	$(".alertmsg").prepend(
	  '<div class="metrics -'+obj.type+'">' +
	  '<span style="margin-left:20px;">'+obj.level+' | </span>' +
	  '<span style="margin-left:20px;">'+obj.group+' | </span>' +
	  '<span style="margin-left:20px;">'+message+' - '+obj.value+' : </span><span >'+obj.desc+'</span>'+
	  '<span aria-hidden="true" onclick="closeMetrics(this)" style="margin-left:25px;cursor:pointer;">X</span>' +
	  '</div>'
	);
	}
	if(!obj.active && classExist){
		document.querySelector('.-'+obj.type).remove();
	}
	// Handle no mic input even after mic access
	if(obj.desc == "no access to your microphone"){
		phone_status('Your browser has some issues in accessing your microphone in the hardware. Please restart or close and open back your browser to fix it.');
	}
}

function onReady(){
	 phone_status("Logging in...");
	console.info('Ready');
}
function onLogin(){
	phone_status("Phone Ready.");
	if (window.tap_communication_id) {
            // with these loaded, join the call
            setTimeout(function () {
                tap_communication(window.tap_communication_id);
            }, 1500);
        }
	console.info('Logged in');
}
function onLoginFailed(reason){
	phone_status("Authentication to the VOIP service has failed.");
	console.info('onLoginFailed ',reason);
	customAlert('Login failure :',reason);	
}
function onLogout(){
	phone_status("on Logout.");
	console.info('onLogout');
}
function onCalling(){
	phone_status("on Calling.");
	console.info('onCalling');
}
function onCallRemoteRinging(){
	phone_status("on Remote Ringing.");
	console.info('onCallRemoteRinging');
}
function onCallAnswered(){
	console.info('onCallAnswered');
	phone_status("on Call Answered.");
	
}
function onCallTerminated(evt){
	 phone_status("on Call Terminated.");
	console.info('onCallTerminated');
}
function onCallFailed(reason){
	phone_status("on call failed.");
	console.info('onCallFailed',reason);
	if(reason && /Denied Media/i.test(reason)){
		phone_status("Audio device access is blocked Please allow!");
	};
	
}
function onMediaPermission(evt){
	console.info('onMediaPermission',evt);
	if(evt.error){
		customAlert('Media permission error',evt.error);
		phone_status("Audio device access is blocked Please allow!");           
	}
}


function callOff(reason){
	if(typeof reason == "object"){
		customAlert('Hangup',JSON.stringify(reason) );
	}else if(typeof reason == "string"){
		customAlert('Hangup',reason);
	}
	
	phone_status('Idle');
	
}

String.prototype.calltimer = function () {
    var sec_num = parseInt(this, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
}
function closeMetrics(e){
	e.parentElement.remove();
}

function customAlert(header,alertMessage,type){
	var typeClass="";
	if(type == "info"){
		typeClass = "alertinfo";
	}else if(type == "warn"){
		typeClass = "alertwarn";
	}
	$(".alertmsg").prepend(
	  '<div class="customAlert'+' '+typeClass+'">' +
	  '<span style="margin-left:20px;">'+header+' | </span>' +
	  '<span style="margin-left:20px;">'+alertMessage+' </span>'+
	  '<span aria-hidden="true" onclick="closeMetrics(this)" style="margin-left:25px;cursor:pointer;">X</span>' +
	  '</div>'
	);
}


function trimSpace(e){
	 e.value = e.value.replace(/[- ()]/g,'');
}

/** 
* Hangup calls on page reload / close
* This is will prevent the other end still listening for dead call
*/
window.onbeforeunload = function () {
    plivoWebSdk.client && plivoWebSdk.client.logout();
};

/*
	Capture UI onclick triggers 
*/
// variables to declare 

var plivoWebSdk; // this will be retrived from settings in UI

function initPhone(username, password){
	var options = {
    "debug":"DEBUG",
    "permOnClick":true,
    "audioConstraints":{"optional":[{"googAutoGainControl":false},{"googEchoCancellation":false}]},
    "enableTracking":true
};
	plivoWebSdk = new window.Plivo(options);
	plivoWebSdk.client.on('onWebrtcNotSupported', onWebrtcNotSupported);
	plivoWebSdk.client.on('onLogin', onLogin);
	plivoWebSdk.client.on('onLogout', onLogout);
	plivoWebSdk.client.on('onLoginFailed', onLoginFailed);
	plivoWebSdk.client.on('onCallRemoteRinging', onCallRemoteRinging);
	//plivoWebSdk.client.on('onIncomingCallCanceled', onIncomingCallCanceled);
	plivoWebSdk.client.on('onCallFailed', onCallFailed);
	plivoWebSdk.client.on('onCallAnswered', onCallAnswered);
	plivoWebSdk.client.on('onCallTerminated', onCallTerminated);
	plivoWebSdk.client.on('onCalling', onCalling);
	//plivoWebSdk.client.on('onIncomingCall', onIncomingCall);
	plivoWebSdk.client.on('onMediaPermission', onMediaPermission);
	plivoWebSdk.client.on('mediaMetrics',mediaMetrics);
	plivoWebSdk.client.on('audioDeviceChange',audioDeviceChange);
	plivoWebSdk.client.setRingTone(true);
	plivoWebSdk.client.setRingToneBack(true);
	// plivoWebSdk.client.setConnectTone(false);
	/** Handle browser issues
	* Sound devices won't work in firefox
	*/
	console.log('initPhone ready!')
}
function tap_communication(communication_id) {

    var web_rtc_qa_id = $('#web_rtc_qa_id').val();

    plivoWebSdk.client.call(communication_id, {
        'X-PH-Tap': web_rtc_qa_id
        // @todo qa_id here
    });
    // change btns
    $('#drop-call-btn').prop('disabled', false);
    // log it
    phone_status('Joining comm: ' + communication_id);
}

function phone_status(text) {

	$('#dialer_text').text(text);
    var connectedno = $.trim($("#connectedno").val());
    if (text == 'on Calling.' || text == 'on Remote Ringing.') {

    } else {
        if (text == 'on Call Terminated.')
            $('#phone-status-text').text('Call Ended : ' + connectedno);
        else if (text == 'on Call Answered.')
            $('#phone-status-text').text('Connected : ' + connectedno);
		else
        $('#phone-status-text').text(text);
    }

    //$('#phone-status-text').hide();
    if (text == 'on Call Answered.') { //on Call Answered.
        $("#display-call-time").timer('start');
        on_call_answered_Start(text);
    }
    if (text == 'on Call Terminated.' || text == 'on call failed.' || text == 'on Logout.') {

        on_terminated_btn_style();
        $('#calling_status').val('');
        $('#call_new_number').val('');
        enable_all_dialler();
        // - server side call -  Should be ajax call for end of call history table

        var old_phn_no = $.trim($('#phone-number').val());
        var alternate_phn_no = $.trim($('#alternate_no').val());
        if ((connectedno == old_phn_no || connectedno == alternate_phn_no) && (text == 'on Call Terminated.')) {
            $('#prefix_call_text').html('Call Ended : ');
        }
        else {
            $('#contact_calling_info').removeClass('display-block-i');
             $('#contact_calling_info').addClass('display-none-i');
            $('#phone-status-text').removeClass('display-none-i');
            $('#phone-status-text').addClass('display-block-i');
        }
        var last_call_history_id = $('#last_call_history_id').val();
        if (last_call_history_id != undefined && last_call_history_id > 0) { //connectedno == old_phn_no &&
            var postAgentEndCallData, contact_id, campaign_id, number_dialed, call_start_datetime, addpage, conid, call_end_datetime,campaign_contact_id, controller_path;

            var callEndTime = get_current_end_call_time();

            $('#call_end_datetime').val(callEndTime);
            $('#dial_pad_call_end_datetime').val(callEndTime);

            var all_call_end_datetime = $('#all_call_end_datetime').val();
            if (all_call_end_datetime != undefined && all_call_end_datetime != ''){
                $('#all_call_end_datetime').val(all_call_end_datetime+","+callEndTime);
            }else{
                $('#all_call_end_datetime').val(callEndTime);
            }

            contact_id = $('#contact_id').val();
            campaign_contact_id = $("#campaign_contact_id").val();
            call_start_datetime = $('#dial_pad_call_start_datetime').val();
            call_end_datetime = $('#dial_pad_call_end_datetime').val();

            if(campaign_contact_id == undefined){
                campaign_contact_id =0;
            }

            postAgentEndCallData = "call_start_datetime=" + call_start_datetime + "&call_end_datetime=" + call_end_datetime + "&last_call_history_id=" + last_call_history_id+"&campaign_contact_id="+campaign_contact_id+"&module_type="+window.module_type;
            controller_path = (window.module_type == "tm") ? "dialer" : "appt";
            $.ajax({
                type: 'POST',
                url: baseUrl + controller_path+'/calls/agentEndCallDial',
                dataType: 'json',
                data: postAgentEndCallData,
                beforeSend: function() {
                    $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").prop('disabled', true); // disable button
                    $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").val('Please Wait...');
                }
            }).success(function (response) {
                if(response.recLink != undefined && response.recLink !='' && response.recLink != null){
                     var td_id =  "#id_" + last_call_history_id;
                    if((td_id).length > 0) {
                        var html = "<a href='"+response.recLink+"' target='_blank'>Rec</a>";
                        $(td_id).html(html);
                    }  
                }                
                $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").prop('disabled', false); // enable button
                $("#call_history_btnSave").val('Submit');
                $("#submit_go_next_btnSave").val('Submit and go to next Contact');
                $("#add_as_diff_person_btnSave").val('Add as a different person');
                return true;
            }).error(function (response) {
                ShowAlertMessage(response.message);
            });
        }
    }
    if (text == 'Phone Ready.') {
        enable_all_dialler();
        $('#check-phone-ready').removeClass('btn-is-disabled');

        // remove restriction for alternate no
        $('#check-alt-phone-ready').removeClass('btn-is-disabled');
    }

    console.log(text);
}
/**
 * Take a call and bring it to the browser.
 */
function take_call() {

    // accept the incoming connection and start two-way audio
    plivoWebSdk.client.answer();
}

/**
 * Drop an active call (hangup)
 */
function drop_call() {
	console.info('Hangup');
	if(plivoWebSdk.client.callSession){
		plivoWebSdk.client.hangup();
	}else{
		callOff();
	}
    
}
	
/**
 * SEND DTMF DIGIT
 */
function dtmf(digit) {
    console.log("send dtmf=" + digit);
	plivoWebSdk.client.sendDtmf(digit);
}

function callDispositionRequiredPerCall(){
    var egCampaignId = $('#eg_campaign_id').val();
    var lastDispoEgCampaignId = $("#lastDispo").attr("class");
    var getLastCallHistoryId = $("#post_last_call_history_id").val();
    var getLastDispo = $("#lastDispo").html();
    var enableCall = true;
    var manualcreate = $('#is_manual_create').val();
    if(manualcreate == "false"){
        var callTimer = $("#display-call-time").html();
        var getCallTimeSecs = callTimer.split(' ');
        if(getLastCallHistoryId != "" && (getCallTimeSecs[0] == ''  || (getCallTimeSecs[0] != '' && getCallTimeSecs[0] > 15))){
            //alert("Disposition required");
            ShowAlertMessage("Disposition required");
            enableCall = false;
        }
    }
    return enableCall;
}

/**
 * Make a call through Plivo
 * @param  {[type]} sdet [description]
 * @return {[type]}     [description]
 */
function make_call(is_conference_call) {

    var dest = $.trim($("#connectedno").val());
    if (dest == undefined || dest == '') {
        ShowAlertMessage('please enter number to dial the call.');
        return false;
    }
    
    //check if the call has been lifted. if lifted, then require only to resubmit the contact with new disposition and prevent call
    if($('#isLifted').val() == '1'){
        ShowAlertMessage('This contact has been lifted. Please re-submit correct disposition before calling.');
        enableCall = false;
        return false;
    }
    
    //validate if the last call made does not have disposition. If there is no dispo, user cannot call
    var enableCall = callDispositionRequiredPerCall();
    
    if(enableCall){
        disable_all_dialler();

        // get the phone number from UI
        //var dest = $.trim($("#connectedno").val());//'+919687332487';//'+1 818-629-0902'//$('#phone-number').val(); //19789679066 $("#connectedno").html();
        if (dest != undefined && dest == '') {
            var dest = '18186290902';
        }

        var postAgentStartCallData, contact_id, campaign_id, campaign_contact_id, campaign_list_id, number_dialed, addpage, conid, controller_path;

        contact_id = $('#contact_id').val();
        campaign_contact_id = $('#campaign_contact_id').val();
        campaign_id = $('#contact_campaign_id').val();
        campaign_list_id = $('#hidden_list_id').val();
        number_dialed = dest;
        addpage = $('#is_add_page').val();
        manualcreate = $('#is_manual_create').val();
        var callDispositionId = $("#call_disposition").val();
        var previousCallDispositionId = $("#previous_call_disposition_id").val();

        if (addpage) {
            conid = "contact_id=0";
        } else {
            conid = "contact_id=" + contact_id;
        }

        postAgentStartCallData = conid + "&campaign_id=" + campaign_id + "&campaign_contact_id=" + campaign_contact_id + "&list_id=" + campaign_list_id + 
                                "&phone=" + number_dialed+"&module_type="+window.module_type+
                                "&is_add_page="+addpage+"&is_manual_create="+manualcreate+"&call_disposition="+callDispositionId+
                                "&previous_call_disposition_id="+previousCallDispositionId;
        controller_path = (window.module_type == "tm") ? "dialer" : "appt";


        $.ajax({
            type: 'POST',
            url: baseUrl + controller_path +'/calls/agentStartCallDial',
            dataType: 'json',
            data: postAgentStartCallData,
            beforeSend: function() {
                $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").prop('disabled', true); // disable button
                $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").val('Please Wait...');
            }
        }).success(function (response) {

            if(!response.makecall) {
                window.location = baseUrl+'dialer/contacts/index/'+campaign_id+'/'+campaign_list_id;
            } else if(!response.status && response.nodispo==true){
                ShowAlertMessage(response.message);
                phone_status('--');
                $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").prop('disabled', false); // enable button
                $("#call_history_btnSave").val('Submit');
                $("#submit_go_next_btnSave").val('Submit and go to next Contact');
                $("#add_as_diff_person_btnSave").val('Add as a different person');
            }else if(!response.status && response.reachedLimitCall==true){
                enable_top_phone_no();
                enable_top_alternate_no();
                enable_bottom_dialbar();
                ShowAlertMessage(response.message);
            }else {

                var callStartTime = get_current_end_call_time();
                $('#last_call_history_id').val(response.data);
                $('#post_last_call_history_id').val(response.data);
                var all_call_history_id, all_call_start_datetime;
                var all_call_history_id = $('#all_call_history_id').val();
                var all_call_start_datetime = $('#all_call_start_datetime').val();
                if (all_call_history_id != undefined && all_call_history_id != '') {
                    $('#all_call_history_id').val(all_call_history_id+","+response.data);
                    $('#all_call_start_datetime').val(all_call_start_datetime+","+callStartTime);
                }else{
                    $('#all_call_history_id').val(response.data);
                    $('#all_call_start_datetime').val(callStartTime);
                }


                $('#call_start_datetime').val(callStartTime);
                $('#dial_pad_call_start_datetime').val(callStartTime);
                if(call_campaign_name != undefined && call_campaign_name !=''){
                    addCallHistoryTr(response.data);
                }

                var web_rtc_agent_id, last_call_history_id, campaign_contact_id;
                 web_rtc_agent_id = $('#web_rtc_agent_id').val();
                 last_call_history_id = $('#last_call_history_id').val();
                campaign_contact_id = $('#campaign_contact_id').val();
                if (campaign_contact_id == undefined || campaign_contact_id == null) {
                    campaign_contact_id = 0;
                }
                // make a call?
                if (dest) {
                    if(!plivoWebSdk.client.isLoggedIn){alert('You\'re not Logged in!')}
                    // ask plivo to connect it
                    plivoWebSdk.client.call(dest, {
                        'X-PH-Webrtc': web_rtc_agent_id,
                        'X-PH-Conf': is_conference_call,
                        'X-PH-CallHistoryID': last_call_history_id,
                        'X-PH-CampaignContactId': campaign_contact_id,
                        'X-PH-Appname': window.app_name
                    }); // @todo agent_id to go here  '1' // // @todo check dial call is conference call or not
                    // change btns

                    $('#drop-call-btn').prop('disabled', false);

                    // log it
                    var connectedno = $.trim($("#connectedno").val());
                    var old_phn_no = $.trim($('#phone-number').val());

                    phone_status('Dialing: ' + dest);
                    $("#call_history_btnSave, #submit_go_next_btnSave, #add_as_diff_person_btnSave").prop('disabled', false); // enable button
                    $("#call_history_btnSave").val('Submit');
                    $("#submit_go_next_btnSave").val('Submit and go to next Contact');
                    $("#add_as_diff_person_btnSave").val('Add as a different person');
                }
            }
        }).error(function (response) {
            ShowAlertMessage(response.message);
        });
    
    }

}

// Change the status of the UI

/**
 * Mute or Unmute a call.
 */
function pause_call() {
    // use the conn's muted property and mute / unmute
    if (window.isMuted) {
      plivoWebSdk.client.unmute();
        $('#mute-state').removeClass('fa fa-microphone-slash');
        $('#mute-state').addClass('fa fa-microphone');
      //  $('#mute-state').text('Call unmute.');
        window.isMuted = !window.isMuted;
    } else {
		plivoWebSdk.client.mute();
        //$('#mute-state').text('Call mute.');
        $('#mute-state').removeClass('fa fa-microphone');
        $('#mute-state').addClass('fa fa-microphone-slash');
        window.isMuted = !window.isMuted;
    }
}
function on_terminated_btn_style() {
    $("#display-call-time").timer('pause');
    $('#dial-call-pad').removeClass('display-none-i');
    $('#dial-call-pad').addClass('display-inline-block-i');

}
function send_dtmf_values(n, input) {

 var text = $('#dialer_text').text();
    if (input != undefined && input == 'manual') {
        input = "manual";
    } else {
        input = "dialpad";
    }
    if (text == 'on Call Answered.') {
        if (input == "dialpad") {
	 		$('#call_new_number').val($('#call_new_number').val() + n);
		}
	 dtmf(n);
 }
    else {
        if (input == "dialpad") {
	 		$('#call_new_number').val($('#call_new_number').val() + n);
		}
		
			 var call_new_number = $('#call_new_number').val();
        var calling_status = $('#calling_status').val();
		//console.log(call_new_number);
        enable_all_dialler();
	 //console.log(call_new_number);
    $("#connectedno").val(call_new_number);
 }
 
}
