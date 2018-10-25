
$(function () {
    // Load DatePicker 
    $("#hired_date").datepicker({showAnim: 'slideDown'});
    
    // Show/Hide Group Field as per User Type 
    selected_usertype = $("#user_type1 option:selected").text().toLowerCase();
    if (selected_usertype == 'agent') {
        $("#teamleads_details").show();
    } else {
        $("#teamleads_details").hide();
    }
    if ($.inArray( $('#user_type1').val(), upperManagementTypes ) >= 0) {
        $("#tm_offices_section").hide();
        $('#module_section').hide();
    } else {
        $("#tm_offices_section").show();
    }
});

// Set Jquery Validation 
$('#form').validate({
    rules: {
        first_name: "required",
        last_name: "required",
        password: {
            minlength: 5
        },
        password_confirm: {
            minlength: 5,
            equalTo: "#password"
        },
        email: {
            required: true,
            email: true
        }
    },
    messages: {
        first_name: "",
        last_name: "",
//        password: {
//            required: false,
//            minlength: "Your password must be at least 5 characters long"
//        },
//        password_confirm: {
//            required: "",
//            minlength: "Your password must be at least 5 characters long",
//            equalTo: "Please enter the same password as above"
//        },
        email: {
            required: "",
            email: "Please enter a valid email address"
        },
        teamleads: ""
    }
});

$('#telemarketing_offices').focus(function () {
    prev_val = $(this).val();
}).change(function () {
    $(this).blur();
    var success = confirm('Are you sure you want to change the TM Office?');
    if (success)
    {
        var module_value = [];
        $.each($("#module option:selected"), function () {
            module_value.push($(this).val());
        });

        usertype = $("#user_type1 option:selected").text().toLowerCase();
        if (usertype == 'agent') {
            var tm_offices = [];
            $.each($("#telemarketing_offices option:selected"), function () {
                tm_offices.push($(this).val());
            });

            var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
            var postData = "tm_offices=" + tm_offices + "&module_value=" + module_value;
            AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
                if (response.status == false) {
                    $(this).val(prev_val);
                    ShowAlertMessage(response.message);
                } else {
                    if(response.data != undefined){
                    var j = response.data;
                    var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
                    }
                    $("#teamleads").html(options);
                    }else{
                        $("#teamleads").html('');
                }
                }
            });

        }else{

            var check_tl_with_agent_url = 'users/check_tl_with_agent/';
            var post_userData =  "user_id=" + $('#user_id').val() + "&module_value=" + module_value;
            AjaxCall(check_tl_with_agent_url, post_userData, "post", "json").done(function (response) {
                if (response.status == false) {
                    jQuery("select#telemarketing_offices").val(prev_val);
                    ShowAlertMessage(response.message);
                }
            });
        }
    }
    else
    {
        $(this).val(prev_val);
        return false;
    }
});

function getDropDownList(name, id, optionList) {
    var combo = $("<select></select>").attr("id", id).attr("name", name);
    combo.append("<option value=''>---SELECT ONE---</option>");
    $.each(optionList, function (i, el) {
        combo.append("<option value="+ i +">" + el + "</option>");
    });
    return combo;
}

$('#module').focus(function () {
    prev_val = $(this).val();
}).change(function () {
    $(this).blur();
    var success = confirm('Are you sure you want to change the Module?');
    if (success)
    {
        var module_value = [];
        $.each($("#module option:selected"), function () {
            module_value.push($(this).val());
        });

        usertype = $("#user_type1 option:selected").text().toLowerCase();
        var module = $('#module').val();
        var project_array = {LG: 'LeadGen', MQL: 'MQL', "MDG": 'MDG', "CSTC":'CSTC'};
        if(module.length != undefined){
            if(module.length == 1 && jQuery.inArray("appt", module) != -1){
                $('#tier_section').hide();
                $('#tier').attr('disabled',true);
                $('#project')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value="APPT_SETTINGS">Appointment Settings</option>')
                    .val('APPT_SETTINGS')
                ;
            }else if(module.length == 1 && jQuery.inArray("tm", module) != -1){
                $('#tier_section').show();
                $('#tier').attr('disabled',false);
                $('#project').remove();
                var combo = getDropDownList('project','project',project_array);
                $("#project_selector").append(combo);
            }else if(module.length == 2){
                $('#tier_section').show();
                $('#tier').attr('disabled',false);
                $('#project').remove();
                var combo =  getDropDownList('project','project',project_array);
                $("#project_selector").append(combo);
                $('#project')
                    .find('option')
                    .end()
                    .append('<option value="APPT_SETTINGS">Appointment Settings</option>');
            }
        }

        if (usertype == 'agent') {
            var tm_offices = [];
            $.each($("#telemarketing_offices option:selected"), function () {
                tm_offices.push($(this).val());
            });

            var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
            var postData = "tm_offices=" + tm_offices + "&module_value=" + module_value;
            AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
                if (response.status == false) {
                    $(this).val(prev_val);
                    ShowAlertMessage(response.message);
                } else {
                    if(response.data != undefined){
                        var j = response.data;
                        var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
                        }
                        $("#teamleads").html(options);
                    }else{
                        $("#teamleads").html('');
                    }

                }
            });

        }else{
            if(usertype == 'team leader'){
            var check_tl_with_agent_url = 'users/check_tl_with_agent/';
            var post_userData =  "user_id=" + $('#user_id').val() + "&module_value=" + module_value;;
            AjaxCall(check_tl_with_agent_url, post_userData, "post", "json").done(function (response) {
                if (response.status == false) {
                    jQuery("select#module").val(prev_val);
                    ShowAlertMessage(response.message);
                }
            });
        }
    }
    }
    else
    {
        $(this).val(prev_val);
        return false;
    }
});

// Change User Type
$('#user_type1').focus(function () {
    prev_val = $(this).val();
}).change(function () {
    $(this).blur();
    var success = confirm('Are you sure you want to change the user type?');
    if (success)
    {
        usertype = $("#user_type1 option:selected").val().toLowerCase();
        if (usertype == 'agent') {
            $("#teamleads_details").show();
        } else {
            $("#teamleads_details").hide();
        }
        if ($.inArray( usertype, upperManagementTypes ) >= 0) {
            $("#tm_offices_section").hide();
            $('#module_section').hide();
        } else {
            $("#tm_offices_section").show();
            $('#module_section').show();
    }
    }
    else
    {
        $(this).val(prev_val);
        return false;
    }
});

// Password Validation Function
function checkPasswordMatch() {
    var password = $("#password").val();
    var confirmPassword = $("#password_confirm").val();

    if (password != confirmPassword)
        $(".error").html("Passwords do not match!");
    else
        $(".error").html("Passwords match.");
}

// Check Campaign Assign To the user ON Status Change
$('#status').focus(function () {
   prev_status_val = $(this).val();
   old_status_val = $('#temp_status').val();
   
}).change(function () {
    $(this).blur();
    
    usertype = $("#user_type1 option:selected").text().toLowerCase();
    new_status_val = $("#status option:selected").text();
     
    if(usertype == "team leader"){
        
        var url = '/users/isAssignCampaignToAgent/';
        var postData = "teamID="+$('#user_id').val();
        
        AjaxCall(url, postData, "post", "json").done(function (response) { 
            if (response.status == false) {
               $('#status').val(prev_status_val);                  
               ShowAlertMessage(response.message);
            }else{
               if(old_status_val == 'Active' && new_status_val != 'Active'){
                    var success = confirm('some agents would be associated with this user. Do you still want to update his status?');//Are you sure you want to change the Status?
                }else{
                   var success = confirm('Are you sure you want to change the Status?');
                } 
                if (!success)
                {
                    $('#status').val(prev_status_val);
                    return false;
                } 
            } 
        });

        
    }
});

// Password Validation; call checkPasswordMatch() function
$(document).ready(function () {
   $("#txtConfirmPassword").keyup(checkPasswordMatch);
});

// Set css For Menu
$("#user_lists").addClass("active open");
