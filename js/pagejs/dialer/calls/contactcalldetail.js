/*Call-back date & time calendar*/

$(function () {
    if ($("#call_disposition_update_date").val() == "0000-00-00 00:00:00" || $("#call_disposition_update_date").val() == "" || $("#call_disposition_update_date").val() == null) {
        var currentDateTime = new Date();

        var CurrentDate = currentDateTime.getDate();
        var Month = currentDateTime.getMonth() + 1;
        var Year = currentDateTime.getFullYear();

        var hours = currentDateTime.getHours();
        var minutes = currentDateTime.getMinutes();
        var seconds = currentDateTime.getSeconds();

        var dateNow = Year + '-' + Month + '-' + CurrentDate + ' ' + hours + ':' + minutes + ':' + seconds;
        $("#call_disposition_update_date").val(dateNow);
    } else {
        var dateNow = $("#call_disposition_update_date").val();
    }
    $("#call_disposition_update_date").datetimepicker({step: 30, minDate: 0, format: 'Y-m-d H:i'});
    $('#call_disposition_update_date').datetimepicker({defaultValue: dateNow});

});


/*Call-back date & time calendar*/

/* Notes keyup & limit words validation */

/*$('#notes').keyup(function (e) {
    var cs = $(this).val().length;
    var cs_rem = (500 - cs);
    if (cs_rem <= 50) {
        $("#char_count").addClass('char_alert');
    } else {
        $("#char_count").removeClass('char_alert');
    }
    if (cs_rem == 0) {
        e.preventDefault();
    } else if (cs_rem < 0) {
        cs_rem = 0;
        this.value = this.value.substring(0, 500);
    }
    $('#char_count').html(cs_rem);
});*/


var campaignType = $('#campaign_type').val();


function show_other(f,fo,dropdown=true)
{
    var sel_box = '#'+f;
    var other_box = '#'+fo;
    var selectedOther = $(sel_box).val().toLowerCase().split(':');
    var selectedOtherLabel = selectedOther[0].substring(0, 5);
    if((selectedOtherLabel == 'other') && (dropdown==true || $(sel_box).is(':checked'))  )
    {
        setTimeout(function() {
            $(other_box).focus();
        }, 1000);
        $(other_box).show();            
    }    
    else
    {
        $(other_box).hide();
        if($(sel_box).attr('value')!='' && ($(sel_box+' option:last').text().toLowerCase()=='others' || $(sel_box+' option:last').text()=='other')) {
            $(sel_box+' option:last').val('Other');
        }
    }
}

function showOtherSR(otherID) {
    $('#'+otherID).toggle();
}

function append_other(f,fo)
{
    sel_box = '#'+f;
    other_box = '#'+fo;
    if($(other_box).val() != '' && ($(sel_box+' option:last').text().toLowerCase()=='others' || $(sel_box+' option:last').text()=='other'))
    {
        $(sel_box+" option:last").val('Other:'+$(other_box).val());
    }

}

function append_trigger(f,fo,trigger)
{
    sel_box = '#'+f;
    other_box = '#'+fo;        
    if($(other_box).val() != '')
    {
        $(sel_box+" option:eq(1)").val(trigger+':'+$(other_box).val());            
    }
}

/* Notes keyup & limit words validation */

function agentSubmission(e){
    var hasNoCall = callRequiredPerDisposition(e);
    if(!hasNoCall){
        var success = confirm("Are you sure?");
        if (success) {
            if(validateSurveyResponses()){
                AddCallDetail("SubmitByAgent");
            }else{
                e.preventDefault();
            }

        } else {
            e.preventDefault();
        }
    }else{
        e.preventDefault();
    }
}

/* All submit button action */
$("#call_history_btnSave").click(function (e) {
    agentSubmission(e);
});

$("#submit_go_next_btnSave").click(function (e) {
    agentSubmission(e);
});

$("#add_as_diff_person_btnSave").click(function (e) {
    agentSubmission(e);
});
$("#change_call_dispo").click(function (e) {
    var success = confirm("Are you sure?");
    if (success) {
        ChangeDisposition();
        $(this).addClass('btn-is-disabled');
    } else {
        e.preventDefault();
    }
});

$("#btnBack").click(function (e){
    
    var campaign_contact_id = $('#campaign_contact_id').val();
    var postAgentStartCallData ="campaign_contact_id=" + campaign_contact_id;

    $.ajax({
            type: 'POST',
            async: false,
            url: '/dialer/calls/checkDisposOfCall',
            dataType: 'json',
            data: postAgentStartCallData
        }).success(function (response) {
            if(!response.status){
                ShowAlertMessage(response.message);
                e.preventDefault();
            }
        }).error(function (response) {
            ShowAlertMessage(response.message);
            e.preventDefault();
        });
});

function callRequiredPerDisposition(){
    var hasNoCall = false;
    if($('#post_last_call_history_id').val() == '' && !IsAddPage && $('#isLifted').val() != '1'){
        var campaignContactId = $('#campaign_contact_id').val();
        var postData ="campaignContactId=" + campaignContactId;
        $.ajax({
                type: 'POST',
                async: false,
                url: '/dialer/calls/checkIfDispoHasCall',
                dataType: 'json',
                data: postData
            }).success(function (response) {
                if(!response.status){
                    ShowAlertMessage(response.message);
                    hasNoCall = true;
                }
            }).error(function (response) {
                ShowAlertMessage(response.message);
            });
    }
    return hasNoCall;
}

/**
 * validations should only be applied on these dispositions:
 * Lead for Qualification,Busy,
 * Redirected,
 * Not In Directory
 * @param {type} qa
 * @returns {Boolean}
 */
function validateSurveyResponses(qa){
    if(qa){
        $('#approve').removeClass('btn-is-disabled');
        $('#follow-up').removeClass('btn-is-disabled');
        $('#reject').removeClass('btn-is-disabled');
        $('#duplicate_lead').removeClass('btn-is-disabled');
    }
    var errorCount = 0;
    if( $('#questions').val() != '' && jQuery.inArray($('#campaign_type').val(),['pureresearch','puremql','smartleads']) >=0 && 
            (jQuery.inArray($('#call_disposition').val(),['1','13','20']) >=0 || qa == true)){
        
        $.each(surveyQuestions, function( index, value ) {
            if($('input[name=qid_'+value+']').is(":radio") && $("input[name=qid_"+value+"]:checked").length == 0){
                errorCount++;
            }else if($("input[name='qid_"+value+"[]']").is(":checkbox") && $("input[name='qid_"+value+"[]']:checked").length == 0){
                errorCount++;
            }else if(!$('input[name=qid_'+value+']').is(":radio") && !$("input[name='qid_"+value+"[]']").is(":checkbox") && $.trim($("#qid_"+value).val()).length == 0){
                errorCount++;
            }
                
          });

    }
    if(errorCount > 0){
        $("#survey_questions_id").addClass('error');
        $("#survey_error").html("*missing answers");
        $('html, body').animate({
        scrollTop: $("#survey_questions_id").offset().top
    }, 700);
        return false;
    }else{
        $("#survey_questions_id").removeClass('error');
        $("#survey_error").html("");
        if(qa){
            $('#approve').addClass('btn-is-disabled');
            $('#follow-up').addClass('btn-is-disabled');
            $('#reject').addClass('btn-is-disabled');
            $('#duplicate_lead').addClass('btn-is-disabled');
        }
        return true;
    }
}
/* All submit button action */

/*Send Email to selected resource*/

function sendEmailResource() {

    var resource_name;
    var resource_id = $('#resource_id').val();
    resource_name = $('#resource_name').val();
    if (resource_name == undefined || resource_name == '') {
        resource_name = $('#campaign_resource').val();
    }
    if (resource_id == undefined || resource_id == '' || resource_id == '0') {
        resource_id = $('#resource_id_select').val();
    }
    action_qa = $('#action_qa').val();

    var to_email_add;
    
    if($("input[name=emailChange]:checked").length == 0) { 
        to_email_add = $("#email").val();
    } else {
        to_email_add = $("#newemail").val();
    }


    var emailPostData = "email=" + to_email_add + '&name=' + $('#first_name').val() + '&campaign=' + $('#eg_campaign_id').val() + '&resource=' + resource_id + '&campaign_id=' + $('#hidden_Campaign_ID').val() + '&contact_id=' + $('#contact_id').val() + '&lead_id=' + $('#lead_id').val() + "&action_qa=" + action_qa + '&resource_name=' + resource_name + '&campaign_contact_id=' + $('#campaign_contact_id').val();
    var sendEmailResources = 'dialer/calls/send_resource';
    AjaxCall(sendEmailResources, emailPostData, "post", "json").done(function (response) {
        if (response.status) {
            $('#email_resource').html(response.message);
            if (response.message == 'Email has been sent to:'. $to_email_add) {
                $('#email_resource').attr('disabled', 'disabled');
                $('#approve').removeClass('btn-is-disabled');
            }
        }
        else {
            if (response.data == 'locked') {
                ShowConfirm(response.message, function () {
                        $.ajax({
                            type: "POST",
                            url: "/dialer/calls/update_multiple_lock_contact/"
                        }).success(function (result) {
                            var success_result = JSON.parse(result);
                            if (success_result.status && success_result.data == 'reload') {
                                window.location.reload();
                            }
                        });
                    },
                    function () {
                        return false;
                    },
                    'Unlock Contact', 'Ok'
                );
            }
            if (response.data != 'locked') {
                ShowAlertMessage(response.message);
                return false;
            }
        }
    });
}

/*Send Email to selected resource*/

function AddCallDetail(statusValue) {
    var call_disposition_value, check_call_disposition_value, call_disposition_update_date_value, postData, emailPostData;

    function CheckCallDispositionValueCallBack() {

        call_disposition_value = $('#call_disposition').val();
        call_disposition_update_date_value = $('#call_disposition_update_date').val();
        if (call_disposition_value == '2' && call_disposition_update_date_value == '') {
            return true;
        } else {
            return false;
        }
    }

    check_call_disposition_value = CheckCallDispositionValueCallBack();

     $('#call_detail_form').validate({
        submitHandler: function (form) {
            if($('#call_disposition').val() != ''){
                    $("#call_history_btnSave").addClass('btn-is-disabled');
                    $("#submit_go_next_btnSave").addClass('btn-is-disabled');
                    $("#add_as_diff_person_btnSave").addClass('btn-is-disabled');
                }
            return form.submit();
        }
    });

    /*Contact call detail form validation rules*/

    $("#first_name").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#last_name").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#phone-number").rules("add", {
        maxlength: 20,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#email").rules("add", {
        maxlength: 100,
        required: true,
        email: true,
        messages: {
            required: ""
        }
    });
    
    $("#industry").rules("add", {
        maxlength: 255,
        required: true,
        messages: {
            required: ""
        }
    });
    
    $("#company_size").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    
    var customErrors = [];
    var customMaxErrors = [];
    $('.cqValidation').each(function(i, val) {
            var thisValue = $(this).val();
            var stringArray = thisValue.split('|');
            var required = false;
            var maxLen = "";
            var cqid = stringArray[0];

            $.each(stringArray, function( index, value ) {

                var getMax = value.split(':');
                if(value == "required"){
                    required = true;
                }else if(getMax[0] == "maxlength"){
                    maxLen = getMax[1];
                }

              });
            
            if(maxLen != ""){
                $("#"+cqid).rules("add", {
                    maxlength: maxLen,
                    required: required,
                    messages: {
                        required: ""
                    }
                });   
            }else{
                $("#"+cqid).rules("add", {
                    required: required,
                    messages: {
                        required: ""
                    }
                });
            }
            
            
            var label = $("[for="+cqid+"]").text();
            if(required &&  $("#"+cqid).val() == ''){
                customErrors[i] = label;
            }else if(maxLen != "" && $("#"+cqid).val().length > maxLen){
                customMaxErrors[i] = "Exceeded max lenght for question: " + label; 
            }
    });
    
    if(customErrors.length > 0){
        $("#customQuestions").addClass("error");
        $("#cq_error").html("*The following custom questions are required:<br>" +customErrors.join('<br>'));
    }else if(customMaxErrors.length > 0){
        $("#customQuestions").addClass("error");
        $("#cq_error").html("*Please fix the following errors:<br>" +customMaxErrors.join('<br>'));
    }else{
        $("#customQuestions").removeClass("error");
        $("#cq_error").html("");
    }
    
    if ($("#industry").val() == '') {
        $("#industry_validation").addClass("error");
    }else{
        $("#industry_validation").removeClass("error");
        EnableQaButtons();
    }
    
    if ($("#company_size").val() == '') {
        $("#company_size_validation").addClass("error");
    }else{
        $("#company_size_validation").removeClass("error");
        EnableQaButtons();
    }
    
    if($('#gdprRequired').val() == 1){
        if($('#call_disposition').val() == '1'){
            $("#pureb2bConsent").rules("add", {
                maxlength: 3,
                required: true,
                messages: {
                    required: ""
                }
            });

            $("#clientConsent").rules("add", {
                maxlength: 3,
                required: true,
                messages: {
                    required: ""
                }
            });
            if($("#pureb2bConsent").val() == '' || $("#clientConsent").val() == ''){
                $("#gdpr_questions_id").addClass('error');
                    $("#gdpr_error").html("*missing response");
                    $('html, body').animate({
                    scrollTop: $("#gdpr_questions_id").offset().top
                }, 700);
            }else{
                $("#gdpr_error").html("");
                $("#gdpr_questions_id").removeClass('error');
            }
        }else{
            if ( $( "#pureb2bConsent" ).length && $( "#pureb2bConsent" ).length ) {
                $("#pureb2bConsent").rules("add", {
                    maxlength: 3,
                    required: false,
                    messages: {
                        required: ""
                    }
                });
            
                $("#clientConsent").rules("add", {
                    maxlength: 3,
                    required: false,
                    messages: {
                        required: ""
                    }
                });
                $("#gdpr_error").html("");
                $("#gdpr_questions_id").removeClass('error');
            }
        }
    }
    
    if(isManualCreate){
        
        $("#company").rules("add", {
            maxlength: 100,
            required: true,
            messages: {
                required: ""
            }
        });
    
        $("#job_title").rules("add", {
            maxlength: 255,
            required: true,
            messages: {
                required: ""
            }
        });
        
        $("#address").rules("add", {
            maxlength: 100,
            required: true,
            messages: {
                required: ""
            }
        });
        
        $("#city").rules("add", {
            maxlength: 45,
            required: true,
            messages: {
                required: ""
            }
        });
        
        $("#country").rules("add", {
            maxlength: 25,
            required: true,
            messages: {
                required: ""
            }
        });
        
        if ($("#country").val() == '') {
            $("#country_validation").addClass("error");
        }else{
            $("#country_validation").removeClass("error");
        }
        
        if ( ($("#country").val() == 'us' || $("#country").val() == 'ca') && $("#state").val() == '') {
            $("#state").rules("add", {
                maxlength: 25,
                required: true,
                messages: {
                    required: ""
                }
            });
        }else{
            $("#state").rules("add", {
                maxlength: 25,
                required: false,
                messages: {
                    required: ""
                }
            });
        }
        
        if (($("#country").val() == 'us' || $("#country").val() == 'ca') && $("#zip").val() == '') {
            $("#zip").rules("add", {
                maxlength: 20,
                required: true,
                messages: {
                    required: ""
                }
            });
        }else{
            $("#zip").rules("add", {
                maxlength: 20,
                required: false,
                messages: {
                    required: ""
                }
            });
        }
    }
    /*Contact call detail form validation rules*/

    /* Question Response Validation Rules */

    var emailChange = $('#emailChange').val();
    if (emailChange != undefined && emailChange == '1') {
        $("#newemail").rules("add", {
            required: true,
            email: true,
            maxlength: 100,
            messages: {
                required: ""
            }
        });
    }
    $('#resource_error').hide();
    var campaign_type = $('#campaign_type').val();
    var resource_id_select = $('#resource_id_select').val();
    if (logged_user_type != undefined && logged_user_type != 'qa') {
        
        var call_disposition = $('#call_disposition').val();
        if (call_disposition != undefined) {
            
            $("#call_disposition").rules("add", {
                required: true,
                maxlength: 100,
                messages: {
                    required: ""
                }
            });
        }
        $("#call_disposition_update_date").rules("add", {
            required: check_call_disposition_value,
            messages: {
                required: ""
            }
        });

        
        
        if (campaign_type == 'hql' && resource_id_select != undefined) { //campaign_type == 'hql' &&
            
            $("#resource_id_select").rules("add", {
                required: true,
                maxlength: 50,
                messages: {
                    required: ""
                }
            });
        }

        if (campaign_type != 'hql' && resource_id_select != undefined) { //campaign_type == 'hql' &&
            $("#resource_id_select").rules("add", {
                required: false,
                maxlength: 50,
                messages: {
                    required: ""
                }
            });
        }
    }  
    if (campaign_type != 'pureresearch' && campaign_type != 'puremql' && campaign_type != 'smartleads' && (resource_id_select == '' || resource_id_select == undefined) && (statusValue == "Approve" || call_disposition == 1)) { //campaign_type == 'hql' &&
        $("#resource_id_select").rules("add", {
            required: true,
            messages: {
                required: ""
            }
        });
        $('#resource_error').show();
        if (statusValue == "Approve") {
            $('#approve').removeClass('btn-is-disabled');
        }
    }else{
        if(resource_id_select != undefined){
            $("#resource_id_select").rules("add", {
                required: false,
                messages: {
                    required: ""
                }
            });
            $("#resource_id_select").removeClass("error");
            $('#resource_error').hide();
        }
    }
    

    /* Question Response Validation Rules */
}

function ChangeDisposition() {
    $( "#call_detail_form" ).submit();
}

$("#approve").click(function (e) {
    hideRejectReason();
    hideFollowUpReason();
    var success = confirm("Are you sure?");
    if (success) {
        if(validateSurveyResponses(true)){
            AddCallDetail('Approve');
            if($('#call_detail_form').valid()){
                $('#approve').addClass('btn-is-disabled');
                $('#follow-up').addClass('btn-is-disabled');
                $('#reject').addClass('btn-is-disabled');
                $('#duplicate_lead').addClass('btn-is-disabled');
            }
        }else{
            e.preventDefault();
        }
    } else {
        e.preventDefault();
        $('#approve').removeClass('btn-is-disabled');
        $('#follow-up').removeClass('btn-is-disabled');
        $('#reject').removeClass('btn-is-disabled');
        $('#duplicate_lead').removeClass('btn-is-disabled');
    }
});

function showRejectReason() {
    $('#input_fields_wrap').show();
    if ($('#reject_reason_1').css('display') == 'none') {
        $('#reject_reason_1').css('display', 'block');
        return false;
    }
    else {
        var returnVariableValue;

        $('select.main_reason_combo').each(function () {
            var get_id_reject_Reason = $(this).attr('id');
            var reject_Reason = $('#' + get_id_reject_Reason).val();

            if (reject_Reason != undefined && (reject_Reason == null || reject_Reason == "" || reject_Reason <= 0)) {
                ShowAlertMessage("Please select added all reason.");
                returnVariableValue = false;
                return false;
            } else {
                returnVariableValue = true;
                return true;
            }
        });
        return returnVariableValue;
    }
}

function showFollowUpReason() {

    $('#follow_up_input_fields_wrap').show();
    if ($('#follow_up_reason_1').css('display') == 'none') {
        $('#follow_up_reason_1').css('display', 'block');
        return false;
    }
    else {
        var returnVariableValue;

        $('select.main_follow_up_reason_combo').each(function () {
            var get_id_follow_up_Reason = $(this).attr('id');
            var follow_upReason = $('#' + get_id_follow_up_Reason).val();

            if (follow_upReason != undefined && (follow_upReason == null || follow_upReason == "" || follow_upReason <= 0)) {
                ShowAlertMessage("Please select added all reason.");
                returnVariableValue = false;
                return false;
            } else {
                returnVariableValue = true;
                return true;
            }
        });
        return returnVariableValue;
    }
}

function hideRejectReason() {
    $('#reject_reason_1').css('display', 'none');
    $('#reason_text_div_1').css('display', 'none');
    $('#input_fields_wrap').css('display', 'none');
    EnabledFollowUpButton();
}

function hideFollowUpReason() {
    $('#follow_up_reason_1').css('display', 'none');
    $('#follow_up_text_div_1').css('display', 'none');
    $('#follow_up_input_fields_wrap').css('display', 'none');
    EnabledRejectButton();
}

$("#reject").on("click", function (event) {
    hideFollowUpReason();
    var rejectReason = showRejectReason();

    if (rejectReason) {
        AddCallDetail('Reject');
    }
    else {
        return false;
    }
});

$("#follow-up").on("click", function (event) {
    hideRejectReason();
    var follow_upReason = showFollowUpReason();

    if (follow_upReason) {
        AddCallDetail('Follow-up');
    }
    else {
        return false;
    }

});

$("#update_and_submit").click(function () {
    hideRejectReason();
    hideFollowUpReason();
    $('#reject').addClass('btn-is-disabled');
    AddCallDetail('Update and Submit');
});

$("#email_resource").click(function () {
    sendEmailResource();
    $('#email_resource').addClass('btn-is-disabled');
});

function getDropDownList(name, id, optionList) {
    var combo = $("<select></select>").attr("id", id).attr("name", name);
    $.each(optionList, function (i, el) {
        combo.append("<option value='" + el + "'>" + el + "</option>");
    });

    return combo;
}

/* Follow Up Reason Region */

function getMainFollowUpReasonDropDownList(name, id, optionList) {
    var combo = $("<select class='main_follow_up_reason_combo' onchange='FollowUpReason(this);'></select>").attr("id", id).attr("name", name);
    combo.append("<option value=''>" + '--Select Follow-up Reason--' + "</option>");
    $.each(optionList, function (i, el) {
        combo.append("<option value='" + el + "'>" + el + "</option>");
    });

    return combo;
}

function FollowUpReason(FollowUpReason) {
    var FollowUpReasonValue = FollowUpReason.value;

    var id = FollowUpReason.id;
    var follow_up_status = $('#follow-up-status').val();
    if (id == 'follow_up_reason_1' && FollowUpReasonValue != '' && follow_up_status != 'yes') {

        var success = confirm("Are you sure you want to select follow-up reason, after that you can't change status?");
        if (success) {
            $('#follow-up-status').val('yes');
            hideRejectReason();
        }
        else {
            FollowUpReason.value = "";
            return false;
        }
    }

    var stringArray = id.split('_');
    var lastChar = stringArray[3];

    $('#sub_follow_up_combo_' + lastChar).css('display', 'none');
    $('#inaccurate_data' + lastChar).remove();
    $('#failure_verify' + lastChar).remove();
    if (FollowUpReasonValue == 'Inaccurate Data Entry') {
        $('#sub_follow_up_combo_' + lastChar).css('display', 'block');
        var Inaccurate_data_entryArray = ['Job Title', 'Employee Size', 'Industry Type', 'Address', 'Custom Question Answer', 'Email Address', 'Phone number'];
        var Inaccurate_data_entryCombo = getDropDownList('sub_drop_down_value_follow_up_' + lastChar, 'inaccurate_data' + lastChar, Inaccurate_data_entryArray);
        $('#' + id).parents('.select-dropdown').find('#sub_follow_up_combo_' + lastChar).append(Inaccurate_data_entryCombo);
    }
    else if (FollowUpReasonValue == 'Failure to Ask or Verify Pertinent Information') {
        $('#sub_follow_up_combo_' + lastChar).css('display', 'block');
        var failure_verify_data_entryArray = ['Job Title', 'Employee Size', 'Industry Type', 'Address', 'Custom Question Answer', 'Email Address'];
        var failure_verify_Combo = getDropDownList('sub_drop_down_value_follow_up_' + lastChar, 'failure_verify' + lastChar, failure_verify_data_entryArray);
        $('#' + id).parents('.select-dropdown').find('#sub_follow_up_combo_' + lastChar).append(failure_verify_Combo);
    }
    if (FollowUpReasonValue == 'Others') {
        $('#follow_up_text_div_' + lastChar).css('display', 'block');
    }
    else {
        $('#follow_up_text_div_' + lastChar).css('display', 'none');
    }
}

/* Follow Up Reason Region */

/* Reject Reason Region */

function getMainRejectReasonDropDownList(name, id, optionList) {
    var combo = $("<select class='main_reason_combo' onchange='RejectedReason(this);'></select>").attr("id", id).attr("name", name);
    combo.append("<option value=''>" + '--Select Rejection Reason--' + "</option>");
    $.each(optionList, function (i, el) {
        combo.append("<option value='" + el + "'>" + el + "</option>");
    });

    return combo;
}

function RejectedReason(RejectedReason) {
    var RejectedReasonValue = RejectedReason.value;

    var id = RejectedReason.id;

    var reject_status = $('#reject-status').val();
    if (id == 'reject_reason_1' && RejectedReasonValue != '' && reject_status != 'yes') {

        var success = confirm("Are you sure you want to select reject reason, after select you can't change status?");
        if (success) {
            $('#reject-status').val('yes');
            hideFollowUpReason();
        }
        else {
            RejectedReason.value = "";
            return false;
        }
    }

    var stringArray = id.split('_');
    var lastChar = stringArray[2];
    $('#sub_combo_' + lastChar).css('display', 'none');
    $('#campaign_filter' + lastChar).remove();
    $('#Unprofessionalism' + lastChar).remove();
    if (RejectedReasonValue == 'Campaign Filter') {
        $('#sub_combo_' + lastChar).css('display', 'block');
        var campaign_filterArray = ['Unqualified Job Title', 'Unqualified Employee Size', 'Unqualified Industry Type', 'Unqualified Custom Question Answer'];
        var campaignFilterCombo = getDropDownList('sub_drop_down_value_' + lastChar, 'campaign_filter' + lastChar, campaign_filterArray);
        $('#' + id).parents('.select-dropdown').find('#sub_combo_' + lastChar).append(campaignFilterCombo);
    }
    else if (RejectedReasonValue == 'Unprofessionalism/Call handling') {
        $('#sub_combo_' + lastChar).css('display', 'block');
        var UnprofessionalismArray = ['Agent being too pushy despite prospect being adamant about not being interested', 'Misleading prospects to get the desired response and/or providing inaccurate or false information'];
        var UnprofessionalismCombo = getDropDownList('sub_drop_down_value_' + lastChar, 'Unprofessionalism' + lastChar, UnprofessionalismArray);
        $('#' + id).parents('.select-dropdown').find('#sub_combo_' + lastChar).append(UnprofessionalismCombo);
    }

    if (RejectedReasonValue == 'Others') {
        $('#reason_text_div_' + lastChar).css('display', 'block');
    }
    else {
        $('#reason_text_div_' + lastChar).css('display', 'none');
    }
}

function remove() {
    $(this).parents(".clonedInput").remove();
}

/* Reject Reason Region */

function CallDispositionCallBack(call_disposition_value, selected) {

    var disposition_value = call_disposition_value.value;
    if (disposition_value == undefined && selected == 'selected') {
        disposition_value = 2;
    }
    if (disposition_value == 2) {
        $('#call_disposition_datepicker').css('display', 'inline-block');
    } else {
        $('#call_disposition_datepicker').css('display', 'none');
    }
}

$("#resource_id_select").change(function () {
    var resource = $(this).val();
    $("#resource_id").val($(this).val());
    if (resource) {
        $("#resource_name").val(resource_id_select.options[resource_id_select.selectedIndex].text);
    } else {
        $("#resource_name").val("");
    }
});

function FormCastEditMode(IsCallDial, is_conference) {

    if (IsCallDial && logged_user_type != undefined && logged_user_type != 'qa') {
        agentStartCallDial(is_conference);
    }
}

/* Direct/Conference call start*/

$('#phone-number').on('input', function (e) {
    enable_all_dialler();
});

$('#alternate_no').on('input', function (e) {
    enable_all_dialler();
});

var internal_conf_make_call_btn = 0;
$("#internalConfMakeCallBtn").click(function () {
    dial($.trim($("#phone-number").val()));
});

$("#editContactCallAccess").click(function () {
    dial($.trim($("#phone-number").val()));
});

$("#editAltContactCallAccess").click(function () {
    dial($.trim($("#alternate_no").val()));
});

$("#internalAltConfMakeCallBtn").click(function () {
    dial($.trim($("#alternate_no").val()));
});

function dial(phone_number) {
    
    //validate if the last call made does not have disposition. If there is no dispo, user cannot call
    var enableCall = callDispositionRequiredPerCall();
    
    if(enableCall){
        $("#display-call-time").timer('remove');
        var direct_phone_number = phone_number;
        $("#connectedno").val(direct_phone_number);
        internal_conf_make_call_btn = 0;
        FormCastEditMode(true, internal_conf_make_call_btn);
    }
}

function agentStartCallDial(is_conference) {
    make_call(is_conference);
    display_contact_info_dialbar();
    $('#phone-status-text').addClass('display-block-i');
}

/* Direct/Conference call end*/

function showHide(shID) {

    if (document.getElementById(shID)) {
        if (document.getElementById(shID + '-show').style.display != 'none') {
            document.getElementById(shID + '-show').style.display = 'none';
            document.getElementById(shID).style.display = 'block';
        }
        else {
            document.getElementById(shID + '-show').style.display = 'inline';
            document.getElementById(shID).style.display = 'none';
        }
    }
}

function save_contact() {
    $('#call_detail_form').validate({
        submitHandler: function (form) {
            form.submit();
        }
    });

    /*Contact call detail form validation rules*/

    $("#first_name").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#last_name").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#phone-number").rules("add", {
        maxlength: 20,
        required: true,
        messages: {
            required: ""
        }
    });
    $("#email").rules("add", {
        maxlength: 100,
        required: true,
        email: true,
        messages: {
            required: ""
        }
    });
    
    $("#industry").rules("add", {
        maxlength: 255,
        required: true,
        messages: {
            required: ""
        }
    });
    
    $("#company_size").rules("add", {
        maxlength: 100,
        required: true,
        messages: {
            required: ""
        }
    });
    
    if ($("#industry").val() == '') {
        $("#industry_validation").addClass("error");
    }else{
        $("#industry_validation").removeClass("error");
        EnableQaButtons();
    }
    
    if ($("#company_size").val() == '') {
        $("#company_size_validation").addClass("error");
    }else{
        $("#company_size_validation").removeClass("error");
        EnableQaButtons();
    }
    /*Contact call detail form validation rules*/
}

$('#save_contact').click(function () {
    save_contact();
});

$('#emailChange').click(function (e) {
    if (logged_user_type != undefined && logged_user_type != 'agent') {
        if ($(this).is(':checked')) {
            $('#new_email_content_box').removeAttr('style');
        }
        else {
           $('#new_email_content_box').css('display', 'none');
        }
    } else {
        $('#new_email_content_box').css('display', 'none');
    }
    $("#newemail_error").html("");
    $("#newemail").val("");
    $("#newemail").removeClass("error");
});

//new email focus out process
            $("#newemail").focusout(function () {
                $("#newemail_error").html("");
                $("#newemail").removeClass("error");
                if($('#emailChange').is(":checked") && $("#newemail").val() != ''){
                var newEmailPostData = "newemail=" + $("#newemail").val() +"&currentemail="+ $("#email").val();

                $.ajax({
                    type: "POST",
                    data: newEmailPostData,
                    url: "/dialer/calls/check_email_member_exist"
                }).success(function (result) {
                    var response = JSON.parse(result);
                    if (response.status) {
                        if (response.data != undefined && response.data != '') {
                            var email = $('#member_id').val();
                            if (email != response.data) {
                                var conf = confirm(response.message);
                                if (conf == true) {
                                    var postData, contact_id, campaign_contact_id;
                                    contact_id = $('#contact_id').val();
                                    
                                    var lockUnlockContactURL = 'dialer/calls/update_member_id_contact';
                                    postData = "contact_id=" + contact_id + "&email=" + response.data + "&prev_email=" + response.prev_email;

                                    AjaxCall(lockUnlockContactURL, postData, "post", "json").done(function (response) {
                                        if (response.status) {
                                            window.location.reload(true);
                                        }
                                        else {
                                            if (response.message != undefined) {
                                                ShowAlertMessage(response.message, response.title_message);
                                            } else if (response.span_message != undefined) {
                                                var top_of_screen = $(window).scrollTop();
                                                var top_of_email = $("#email").offset().top
                                                if (top_of_screen > top_of_email) {
                                                    $('html, body').animate({
                                                        scrollTop: $("#newemail").offset().top
                                                    }, 500);
                                                }
                                                $("#newemail_error").html(response.span_message);
                                                $("#newemail").addClass("error");
                                            }
                                        }
                                    });
                                }
                            }
                        }
                        }else {
                            if (response.message != undefined) {
                                ShowAlertMessage(response.message, response.title_message);
                            } else if (response.span_message != undefined) {
                                var top_of_screen = $(window).scrollTop();
                                var top_of_email = $("#email").offset().top
                                if (top_of_screen > top_of_email) {
                                    $('html, body').animate({
                                       scrollTop: top_of_email
                                    }, 500);
                                }
                                $("#newemail_error").html(response.span_message);
                                $("#newemail").addClass("error");
                            }
                        }
                });
            } else {
                var top_of_screen = $(window).scrollTop();
                var top_of_email = $("#email").offset().top
                if (top_of_screen > top_of_email) {
                    $('html, body').animate({
                       scrollTop: top_of_email
                    }, 500);
                }
                $("#newemail_error").html("Please enter new email.");
                $("#newemail").addClass("error");
            }
});

// Add as a diff. person check email on focus out
$("#email").focusout(function () {

    if(IsAddPage && !isManualCreate){
        var newEmailPostData = "email="+$("#email").val()+"&campaign_id="+$("#contact_campaign_id").val()+"&list_id="+list_id;
        var original_call_history_id = $("#original_call_history_id").val();
        if(original_call_history_id > 0){
            newEmailPostData += "&original_call_history_id="+original_call_history_id;
        }
        var campaign_contact_id = $("#campaign_contact_id").val();
        if(campaign_contact_id > 0){
            newEmailPostData += "&from_campaign_contact_id="+campaign_contact_id;
        }
        $('#email_loader').html('<img class="loader" alt="Processing.." src="https://s3.amazonaws.com/enterprise-guide/images/loadingbar.gif">');
        $.ajax({
            type: "POST",
            data: newEmailPostData,
            url: "/dialer/calls/check_add_email_contact_exist"
        }).success(function (result) {
            var response = JSON.parse(result);
            if(response.cid){
                window.location = "/dialer/calls/index/"+response.cid+"/"+list_id+"?add_diff=true";
            }else{
            if (response.status) {
                if (response.message != undefined && response.message != '') {
                     ShowAlertMessage(response.message);
                     enable_top_phone_no();
                     enable_top_alternate_no();
                }else if(response.is_eg_member){
                    console.log("record loaded from eg.members table");
                    //console.log(response.member_details);
                    setEgMemberDetails(response.member_details);
                }else{
                    enable_top_phone_no();
                    enable_top_alternate_no();
                }
            }else{
                ShowAlertMessage(response.message);
                enable_top_phone_no();
                enable_top_alternate_no();
            }
            }
             $('#email_loader').html('');
        });
    }else if(IsAddPage && isManualCreate){
        var newEmailPostData = "email="+$("#email").val()+"&campaign_id="+$("#contact_campaign_id").val()+"&list_id="+list_id+"&manual=true";
        var original_call_history_id = $("#original_call_history_id").val();
        if(original_call_history_id > 0){
            newEmailPostData += "&original_call_history_id="+original_call_history_id;
        }
        $('#email_loader').html('<img class="loader" alt="Processing.." src="https://s3.amazonaws.com/enterprise-guide/images/loadingbar.gif">');
        $.ajax({
            type: "POST",
            data: newEmailPostData,
            url: "/dialer/calls/check_add_email_contact_exist"
        }).success(function (result) {
            var response = JSON.parse(result);
            if(response.cid){
                //window.location = "/dialer/calls/index/"+response.cid+"/"+list_id+"?add_diff=true";
		window.location = "/dialer/calls/index/"+response.cid+"/"+list_id+"?manual_create=true";
            }else{
            if (response.status) {
                if (response.message != undefined && response.message != '') {
                    ShowAlertMessage(response.message);
		    $("#email").val("");
                    disable_top_phone_no();
                    disable_top_alternate_no();
                }else if(response.is_eg_member){
                    console.log("record loaded from eg.members table");
                    //console.log(response.member_details);
                    setEgMemberDetails(response.member_details)
                }
            }else{
                ShowAlertMessage(response.message);
		$("#email").val("");
                disable_top_phone_no();
                disable_top_alternate_no();
            }
            }
             $('#email_loader').html('');
        });
    }
});
//set GDPR combination logic
if($('#gdprRequired').val()==1){
consentLogic();
    $('#pureb2bConsent').change(function(){
        consentLogic(true);
    });
    
    $('#clientConsent').change(function(){
        consentLogic(true);
    });
    
    function consentLogic(dropdownChanged=false){
        var dropdown=$('#call_disposition');
        if($('#pureb2bConsent').val() != "" && $('#clientConsent').val() != ""){
            if($('#pureb2bConsent').val() == "no" && $('#clientConsent').val() == "no"){
                dropdown.empty();  
                dropdown.append(
                    $('<option>', {
                        value: '18',
                        text: dispoValues['18']
                    }, '</option>'));
            }else if($('#pureb2bConsent').val() == "yes" && $('#clientConsent').val() == "no"){
                dropdown.empty();  
                dropdown.append(
                    $('<option>', {
                        value: '19',
                        text: dispoValues['19']
                    }, '</option>'));
                    if(!dropdownChanged){
                        $('#pureb2bConsent').attr("disabled", true); 
                        $('#clientConsent').attr("disabled", true); 
                        $('#editContactCallAccess').hide();
                        $('#internalConfMakeCallBtn').hide();
                        $('#editAltContactCallAccess').hide();
                        $('#internalAltConfMakeCallBtn').hide();
                        //$('#dial-call-pad').hide();
                        
                    }
            }else if($('#pureb2bConsent').val() == "no" && $('#clientConsent').val() == "yes"){
                dropdown.empty();  
                dropdown.append(
                    $('<option>', {
                        value: '1',
                        text: dispoValues['1']
                    }, '</option>'));
            }else{
                if(dropdownChanged){
                    dropdown.empty();
                    $.each(dispoOptions, function (index, value) {
                        dropdown.append(value);
                    });
                }
            }
        }
    }
}

if($('#gdprClientNo').val()==1){
var dropdown=$('#call_disposition');
    dropdown.empty();  
    dropdown.append(
        $('<option>', {
            value: '19',
            text: dispoValues['19']
        }, '</option>'));
        $('#pureb2bConsent').attr("disabled", true); 
        $('#clientConsent').attr("disabled", true); 
        $('#editContactCallAccess').hide();
        $('#internalConfMakeCallBtn').hide();
        $('#editAltContactCallAccess').hide();
        $('#internalAltConfMakeCallBtn').hide();
        //$('#dial-call-pad').hide();
}

function setEgMemberDetails(member_details){
    document.getElementById("member_id").value =member_details.id;
    $("#first_name").val(member_details.first_name);
    $("#last_name").val(member_details.last_name);
    $("#company").val(member_details.company_name);
    $("#job_title").val(member_details.job_title);
    $("#phone-number").val(member_details.phone);
    $("#ext").val(member_details.ext);
    $("#address").val(member_details.address1);
    $("#city").val(member_details.city);
    $("#state").val(member_details.state);
    $("#zip").val(member_details.zip);
    $("#country select").val(member_details.country);
    $("#industry").val(member_details.industry);
    $("#company_size").val(member_details.company_size);
    $("#company_revenue").val(member_details.company_revenue);
    $("#country").val(member_details.country);
    document.getElementById("original_owner").value =member_details.original_owner;
}

function EnabledRejectButton() {
    $('#reject').removeClass('btn-is-disabled');
    $('#follow-up').addClass('btn-is-disabled');
    $('#approve').addClass('btn-is-disabled');
    $('#duplicate_lead').addClass('btn-is-disabled');
}

function EnabledFollowUpButton() {
    $('#follow-up').removeClass('btn-is-disabled');
    $('#reject').addClass('btn-is-disabled');
    $('#approve').addClass('btn-is-disabled');
    $('#duplicate_lead').addClass('btn-is-disabled');
}

function EnableQaButtons() {
    $('#approve').removeClass('btn-is-disabled');
    $('#follow-up').removeClass('btn-is-disabled');
    $('#reject').removeClass('btn-is-disabled');
    $('#duplicate_lead').removeClass('btn-is-disabled');
}

$(document).ready(function () {
    NumericTextOnlyAllowed('call_new_number');
    NumericTextOnlyAllowed('alternate_no');
    $("#dial-call-pad").click(function(){
        if($('#pureb2bConsent').val() == "yes" && $('#clientConsent').val() == "no"){
           $('#make-call-btn').hide();
        }
        if($('#gdprClientNo').val()==1){
            $('#make-call-btn').hide();
        }
    });
    //is_add_page ==true
    //is_manual_create=="false"
    if($('#is_add_page').val() == 1 && $('#is_manual_create').val() == "false"){
        enable_top_phone_no();
        enable_top_alternate_no();
    }
    
    wireUpEvents();
    /* Reject Reason Region */
    var max_reject_fields = 5; //maximum input boxes allowed
    var wrapper = $(".input_fields_wrap"); //Fields wrapper
    var add_button = $("#add_field_button"); //Add button ID
    var x = 1; //initial text box count
    $(add_button).click(function (e) { //on add input button click

        var reject_reason_1 = $('#reject_reason_1').val();
        if (reject_reason_1 != undefined && reject_reason_1 == '') {
            ShowAlertMessage("Please select Reject reason first.");
            return false;
        }
        e.preventDefault();
        if ($('.clonedInput').length < max_reject_fields) { //max input box allowed

            x++; //text box increment

            var RejectReasonArray = ['Campaign Filter', 'Unprofessionalism/Call handling', 'Duplicate Lead in Admin', 'Prospect expressing signs of Sarcasm, being agitated, being Irate, and hanging-up without proper closing', 'Others'];

            var createdCombo = getMainRejectReasonDropDownList('reject_reason[]', 'reject_reason_' + x, RejectReasonArray);
            var FinalCreatedCombo = $('<div/>', {'class': 'clonedInput'}).append(
                $('<div/>', {'class': 'styled select-dropdown table-dropdown'}).append(
                    createdCombo
                ).append($('<div class="styled select-dropdown table-dropdown sub_combo" id="sub_combo_' + x + '">' + '</div>'))
                    .append(
                    $('<div class="dialog-form others_label_input_text" style="display: none;" id="reason_text_div_' + x + '"><div class="form-input">' +
                    '<input type="text" id="reason_text_' + x + '" name="reason_text[]" maxlength="100" placeholder="Rejection Reason"/></div></div>')
                ).append(
                    $('<a href="javascript:void(0);" id="remove_rejected_field" class="remove_added_button_field">Remove</a>')
                )
            );

            $(wrapper).append(FinalCreatedCombo); //add input box
        }
        else {
            ShowAlertMessage('You can add maximum 5 reason.');
        }
    });

    $(wrapper).on("click", "#remove_rejected_field", function (e) { //user click on remove text
        e.preventDefault();
        $(this).parent('div').parent('.clonedInput').remove();
    });

    /* Reject Reason Region */

    /* FollowUp Reason Region */
    var y = 1; //initial text box count
    var max_follow_fields = 5; //maximum input boxes allowed
    var followUpWrapper = $(".follow_up_input_fields_wrap"); //Fields wrapper
    var followUp_add_button = $("#follow_up_add_field_button"); //Add button ID
    $(followUp_add_button).click(function (e) { //on add input button click
        var follow_up_reason_1 = $('#follow_up_reason_1').val();
        if (follow_up_reason_1 != undefined && follow_up_reason_1 == '') {
            ShowAlertMessage("Please select Follow-up reason first.");
            return false;
        }

        e.preventDefault();
        if ($('.followUpClonedInput').length < max_follow_fields) { //max input box allowed
            y++; //text box increment

            var FollowUpReasonArray = ['Inaccurate Data Entry', 'Failure to Ask or Verify Pertinent Information', 'Improper Branding', 'Failure to state the purpose of the call and what we are trying to promote', 'Others'];

            var createdCombo = getMainFollowUpReasonDropDownList('follow_up_reason[]', 'follow_up_reason_' + y, FollowUpReasonArray);
            var FinalCreatedCombo = $('<div/>', {'class': 'followUpClonedInput'}).append(
                $('<div/>', {'class': 'styled select-dropdown table-dropdown'}).append(
                    createdCombo
                ).append($('<div class="styled select-dropdown table-dropdown sub_combo" id="sub_follow_up_combo_' + y + '">' + '</div>'))
                    .append(
                    $('<div class="dialog-form others_label_input_text" style="display: none;" id="follow_up_text_div_' + y + '"><div class="form-input">' +
                    '<input type="text" id="follow_up_text_' + y + '" name="follow_up_text[]" maxlength="100" placeholder="Follow-Up Reason"/></div></div>')
                ).append(
                    $('<a href="javascript:void(0);" id="remove_follow_up_field" class="remove_added_button_field">Remove</a>')
                )
            );

            $(followUpWrapper).append(FinalCreatedCombo); //add input box
        } else {
            ShowAlertMessage('You can add maximum 5 reason.');
        }
    });

    $(followUpWrapper).on("click", "#remove_follow_up_field", function (e) { //user click on remove text
        $(this).parent('div').parent('.followUpClonedInput').remove();
    });

    /* FollowUp Reason Region */
});

$(document).ready(function () {
    if (IsCallbackSelected != undefined && IsCallbackSelected == '2' && (logged_user_type != 'qa')) {
        CallDispositionCallBack(2, 'selected');
    }

    $('#view_notes').click(function(){

        html = '';
        
        html = html + '<img src="https://s3.amazonaws.com/enterprise-guide/images/loadingbar.gif" alt="Loading.." />';
        html = html + '<br/><br/>';
        $('#contact-notes-cont').html(html);

                        
        var member_id = $('#member_id').val();

        if(member_id != '') {
            $.ajax({
                type: "POST",
                data: {
                    member_id:member_id,
                },
                url: "/dialer/calls/getnotesbymemberid/"
            }).success(function (data) {
                var response = JSON.parse(data);

                html = '';

                $.each(response, function(i, item) {

                    html = html + '<p class="pre-populated-note" style="word-break: break-all">';
                    html = html + item.note;
                    html = html + '<span class="user_detail"> - ' + item.first_name + ' ' + item.last_name + '</span>';
                    html = html + '<span class="date_format"> Campaign ' + item.eg_campaign_id + '</span>';
                    html = html + '<span class="date_format"> - ' + item.created_at + '</span>';
                    html = html + '</p>';
                });
                $('#contact-notes-cont').html(html);
            });
        }
    });


    $('#view_other_contacts').click(function(){

        html = '';
        
        html = html + '<img src="https://s3.amazonaws.com/enterprise-guide/images/loadingbar.gif" alt="Loading.." />';
        html = html + '<br/><br/>';
        $('#other-contacts').html(html);

                        
        var listId = $('#hidden_list_id').val();
        var campaignId = $('#hidden_Campaign_ID').val();
        var phone = $('#hidden_phone').val();
        var contactId = $('#contact_id').val();

        $.ajax({
            type: "GET",
            url: "/dialer/contacts/getCampaignContactsByPhone/"+phone+"/"+campaignId+"/"+listId+"/"+contactId,
            dataType: 'json'
        }).success(function (data) {
            var response = data;
            var otherContactsLength = data.length;
            html = '';
            var counter = 1;
            $.each(response, function(i, item) {

                html = html + '<p class="pre-populated-note" style="word-break: break-all">';
                html = html + '<span class="prospect">Name of Prospect: ' + item.prospect + '</span><br>';
                html = html + '<span class="job_title"> Job Title: ' + item.job_title + '</span><br>';
                html = html + '<span class="prospect_link">URL to contact details: ' + item.url + '</span>';
                html = html + '</p>';
                
                if(counter==2){
                    return false;
                }
                counter++;
            });
            if(otherContactsLength > 2){
                html = html + '<p class="pre-populated-note" style="word-break: break-all">';
                html = html + '<span><a href="/dialer/contacts/viewAllOtherContacts/'+ phone +'/'+ campaignId + '/'+listId + '/'+contactId + '" target="_blank">See more</a></span>';
                html = html + '</p>';
            }
            $('#other-contacts').html(html);
        }).error(function (data) {
            $('#other-contacts').html("No results found.");
        });
        
    });
});

function formatAMPM() {
    date = new Date();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var dd = ( '0' + (date.getDate()) ).slice(-2); //yields day
    var MM = date.getMonth(); //yields month
    var yyyy = date.getFullYear().toString().substr(2, 2); //yields year
    var xxx = ( MM + 1) + "/" + dd + "/" + yyyy;
    var strTime = xxx;
    strTime += ' ' + hours + ':' + minutes + ' ' + ampm;
    return strTime;
}

function addCallHistoryTr(call_history_id) {

    var date = formatAMPM();
    $("#call_history_no_record_div").remove();

    if ($.trim($("#call_history_tab").html()) == '') {
        var call_history_html = '<table id="call-history-tbl" class="table table-bordered row call-history-tbl mar-b-0">';
        call_history_html += "<thead><tr><th style='width:12%;'>Last call made</th>";
        call_history_html += "<th style='width:10%;'>ID</th>";
        call_history_html += "<th style='width:14%;'>campaign</th><th style='width:12%;'>Result/ Status</th><th style='width:12%;'>Agent</th><th style='width:8%;'>Rec. link</th><th style='width:8%;'>Sec</th><th style='width:12%;'>TM Brand</th><th style='width:12%;'>Notes</th></tr></thead>";
        call_history_html += "<tbody>";
        call_history_html += '</tbody></table>';
        $("#call_history_tab").html(call_history_html);
    }
    var row = "<tr>";
    row += "<td>" + date + "</td>";
    row += "<td></td>"; 
    row += "<td>" + call_campaign_name + "</td>";
    row += "<td></td>";
    row += "<td>" + logged_username + "</td>";
    row += "<td id ='id_" + call_history_id + "'></td>";
    row += "<td></td>";
    row += "<td></td>";
    row += "<td></td>";
    row += "</tr>";
    $('#call-history-tbl tbody').append(row);
}
