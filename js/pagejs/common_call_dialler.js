$('#call_new_number').on('input',function(e){

    var call_new_number = $('#call_new_number').val();
	send_dtmf_values(call_new_number,"manual");
});

function display_contact_info_dialbar() {
    var connectedno = $.trim($("#connectedno").val());
    var old_phn_no = $.trim($('#phone-number').val());
    var alternate_phn_no = $.trim($('#alternate_no').val());
    if (connectedno == old_phn_no || connectedno == alternate_phn_no) {
        $('#phone-status-text').removeClass('display-block-i');
        $('#phone-status-text').addClass('display-none-i');
        $('#contact_calling_info').removeClass('display-none-i');
        $('#contact_calling_info').addClass('display-block-i');
    }
    return {connectedno: connectedno, old_phn_no: old_phn_no};
}
function on_call_answered_Start(phone_status_text) {
    var postAgentStartCallData, contact_id,campaign_id,number_dialed,call_start_datetime,addpage,conid,last_call_history_id;

    if (phone_status_text != undefined && phone_status_text == 'on Call Answered.') { // on Call Answered.

        var callStart = 'start';

        switch (callStart) {
            case "start":
                $("#display-call-time").timer({
                    action: 'start',
                    seconds: 0
                });
                break;
        }

        var __ret =  display_contact_info_dialbar();
        var connectedno = __ret.connectedno;
        var old_phn_no = __ret.old_phn_no;
        var callStartTime = get_current_end_call_time();
        if (connectedno == old_phn_no) {
            $('#prefix_call_text').html('Connected : ');
        }
        else
        {
            $('#phone-status-text').removeClass('display-none-i');
            $('#phone-status-text').addClass('display-block-i');
        }
        $('#call_start_datetime').val(callStartTime);
        $('#dial_pad_call_start_datetime').val(callStartTime);
        $('#pause-call-btn').prop('disabled', false);
    }
}

function is_make_call_btn() {
    $("#display-call-time").timer('remove');

    var text = $('#phone-status-text').text();
    var connectedno =   $.trim($("#connectedno").val());
    var old_phn_no = $.trim($('#phone-number').val());
    var alternate_phn_no = $.trim($('#alternate_no').val());
    if(connectedno == old_phn_no || connectedno == alternate_phn_no){

        $('#prefix_call_text').html('Dialling: ');
        $('#contact_calling_info').removeClass('display-none-i');
        $('#contact_calling_info').addClass('display-block-i');
    }
    else if (text) {

        $('#phone-status-text').removeClass('display-none-i');
        $('#phone-status-text').addClass('display-block-i');
        $('#contact_calling_info').removeClass('display-block-i');
        $('#contact_calling_info').addClass('display-none-i');
    }

    $('#add-new-number').addClass('display-none-i');

}
$('#make-call-btn').click(function () {
    var connectedno = $.trim($("#connectedno").val());
    if(connectedno == undefined || connectedno == ''){
        ShowAlertMessage('please enter number to dial the call.');
        return false;
    }
    is_make_call_btn();
    //$('#conf-make-call-btn').prop('disabled',true);

});

$('#conf-make-call-btn').click(function () {
    var connectedno = $.trim($("#connectedno").val());
    if(connectedno == undefined || connectedno == ''){
        ShowAlertMessage('please enter number to dial the call.');
        return false;
    }
    is_make_call_btn();
    //$('#make-call-btn').prop('disabled',true);

});
function convertToServerTimeZone(){
    var offset,clientDate,serverDate,utc;
    //EST
    offset = -5.0;
    clientDate = new Date();
    utc = clientDate.getTime() + (clientDate.getTimezoneOffset() * 60000);
    serverDate = new Date(utc + (3600000*offset));
    return serverDate;
}

function get_current_end_call_time() {
    var currentDateTime = convertToServerTimeZone();
    //var currentDateTime = new Date();
    var CurrentDate = currentDateTime.getDate();
    var Month = currentDateTime.getMonth() + 1;
    var Year = currentDateTime.getFullYear();

    var hours = currentDateTime.getHours();
    var minutes = currentDateTime.getMinutes();
    var seconds = currentDateTime.getSeconds();

    var callEndTime = Year + '-' + Month + '-' + CurrentDate + ' ' + hours + ':' + minutes + ':' + seconds;
    return callEndTime;
}
$("#drop-call-btn").click(function () {
   // drop_call();

    var CallDialerOpen = 'div#call_popup_start';
    $("#display-call-time").timer('pause');
    var callEndTime = get_current_end_call_time();
    $('#call_end_datetime').val(callEndTime);
    $('#dial_pad_call_end_datetime').val(callEndTime);
    $('#call_new_number').val('');
    on_terminated_btn_style();
    var CallDialerOpen = 'div#call_popup_start';

    $('#pause-call-btn').prop('disabled', true);

	$('#add-new-number').removeClass('display-block-i');
	$('#add-new-number').addClass('display-none-i');


});
$("#dial-call-pad").click(function () {
	$('#add-new-number').toggle("slow");
    $('#add-new-number').removeClass('display-none-i');
    $('#call_new_number').val('');

});

/* Common enable disable code */

function enable_top_phone_no() {
    $('#editContactCallAccess').prop('disabled', false);
    $('#internalConfMakeCallBtn').prop('disabled', false);
    $('#editContactCallAccess').removeClass('disable-btn-make-call');
    $('#internalConfMakeCallBtn').removeClass('disable-btn-make-call');
    $('#editContactCallAccess').addClass('btn-make-call');
    $('#internalConfMakeCallBtn').addClass('btn-make-call');
}
function enable_top_alternate_no() {
    $('#editAltContactCallAccess').prop('disabled', false);
    $('#internalAltConfMakeCallBtn').prop('disabled', false);
    $('#editAltContactCallAccess').removeClass('disable-btn-make-call');
    $('#internalAltConfMakeCallBtn').removeClass('disable-btn-make-call');
    $('#editAltContactCallAccess').addClass('btn-make-call');
    $('#internalAltConfMakeCallBtn').addClass('btn-make-call');
}
function enable_all_dialler(){

    // enable or disable phone no dialer
    var phone_number = $.trim($('#phone-number').val());
    var calling_status = $('#calling_status').val();
    var email_address = $('#email').val();
    var is_manual_create = $("#is_manual_create").val();
    
    if (phone_number != undefined && phone_number != '' && calling_status != undefined && calling_status == '') {
        if (is_manual_create == 'false') {
            enable_top_phone_no();
        } else if(email_address != undefined && email_address != '') {
            enable_top_phone_no();
        }
    } else {
        disable_top_phone_no();
    }

    // enable or disable alternate no dialer
    var alternate_no = $('#alternate_no').val();

    if (alternate_no != undefined && alternate_no != '' && calling_status != undefined && calling_status == '') {
        if (is_manual_create == 'false') {
            enable_top_alternate_no();
        } else if(email_address != undefined && email_address != '') {
            enable_top_alternate_no();
        }
    } else {
        disable_top_alternate_no();
    }

    // enable or disable bottom dialler
    var call_new_number = $('#call_new_number').val();
    var campaign_contact_id = $('#campaign_contact_id').val();
    if (campaign_contact_id != undefined && campaign_contact_id != '' && call_new_number != undefined && call_new_number != '' && calling_status != undefined && calling_status == '') {
        if(isTodayExceedCallDial != undefined && isTodayExceedCallDial != ""){
            disable_bottom_dialbar();
        }else{
            enable_bottom_dialbar();
        }
        call_new_number    }
    else if(campaign_contact_id == undefined && calling_status == undefined && call_new_number != undefined && call_new_number != ''){
        enable_bottom_dialbar();
    }
    else {
        disable_bottom_dialbar();
    }
}

function disable_top_phone_no() {
    $('#editContactCallAccess').prop('disabled', true);
    $('#internalConfMakeCallBtn').prop('disabled', true);
    $('#editContactCallAccess').addClass('disable-btn-make-call');
    $('#internalConfMakeCallBtn').addClass('disable-btn-make-call');
    $('#editContactCallAccess').removeClass('btn-make-call');
    $('#internalConfMakeCallBtn').removeClass('btn-make-call');
}
function disable_top_alternate_no() {
    $('#editAltContactCallAccess').prop('disabled', true);
    $('#internalAltConfMakeCallBtn').prop('disabled', true);
    $('#editAltContactCallAccess').addClass('disable-btn-make-call');
    $('#internalAltConfMakeCallBtn').addClass('disable-btn-make-call');
    $('#editAltContactCallAccess').removeClass('btn-make-call');
    $('#internalAltConfMakeCallBtn').removeClass('btn-make-call');
}
function disable_bottom_dialbar() {
    $('#make-call-btn').removeClass('btn-make-call');
    $('#make-call-btn').addClass('disable-btn-make-call');
    $('#conf-make-call-btn').removeClass('btn-make-call');
    $('#conf-make-call-btn').addClass('disable-btn-make-call');
}

function enable_bottom_dialbar(){
    $('#make-call-btn').removeClass('disable-btn-make-call');
    $('#make-call-btn').addClass('btn-make-call');
    $('#conf-make-call-btn').removeClass('disable-btn-make-call');
    $('#conf-make-call-btn').addClass('btn-make-call');
}

function disable_all_dialler(){

    var calling_status = $('#calling_status').val('calling');

    disable_top_phone_no();

    disable_top_alternate_no();

    disable_bottom_dialbar();
}