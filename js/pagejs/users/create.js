$(function () {
    // Load DatePicker 
    $("#hired_date").datepicker({showAnim: 'slideDown'});
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
        },
        status: "required"
    },
    messages: {
        first_name: "",
        last_name: "",
        password: {
            required: "",
            minlength: "Your password must be at least 5 characters long"
        },
        password_confirm: {
            required: "",
            minlength: "Your password must be at least 5 characters long",
            equalTo: "Please enter the same password as above"
        },
        email: {
            required: "",
            email: "Please enter a valid email address"
        },
        status:"",
        teamleads: ""
    }
});

$('#telemarketing_offices').focus(function () {
}).change(function () {
    usertype = $("#user_type1 option:selected").text().toLowerCase();
    if (usertype == 'agent') {
        var tm_offices = [];
        $.each($("#telemarketing_offices option:selected"), function () {
            tm_offices.push($(this).val());
        });
        var module_value = [];
        $.each($("#module option:selected"), function () {
            module_value.push($(this).val());
        });
        var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
        var postData = "tm_offices=" + tm_offices + "&module_value=" + module_value;
        AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
            if (response.status == false) {
                ShowAlertMessage(response.message);
            } else {
                if(response.data != undefined) {
                    var j = response.data;
                    var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
                    }
                    $("#teamleads").html(options);
                }else{
                    $("#teamleads").html("");
                }
            }
        });
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
}).change(function () {
   //var module_value = $('#module').val();
    var usertype = $("#user_type1 option:selected").text().toLowerCase();
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
        var module_value = [];
        $.each($("#module option:selected"), function () {
            module_value.push($(this).val());
        });
        var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
        var postData = "tm_offices=" + tm_offices + "&module_value=" + module_value;
        AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
            if (response.status == false) {
                ShowAlertMessage(response.message);
            } else {
                if(response.data != undefined) {
                var j = response.data;
                var options = '<option role="option" value=""> ---SELECT ONE---</option>';
                for (var i = 0; i < j.length; i++) {
                    options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
                }
                $("#teamleads").html(options);
                }else{
                    $("#teamleads").html("");
            }
            }
        });
    }
});

// Show/Hide Group Field as per User Type 
$("#user_type1").change(function () {
    usertype = $("#user_type1 option:selected").text().toLowerCase();
    if (usertype == 'agent') {
        $("#teamleads_details").show();
    } else {
        $("#teamleads_details").hide();
    }

    if (usertype == 'tm admin') {
        $("#tm_offices_section").hide();
        $('#module_section').hide();
    } else {
        $("#tm_offices_section").show();
        $('#module_section').show();
    }
});

// Set css For Menu  
$("#user_lists").addClass("active open");
$("#user_create").addClass("active");
