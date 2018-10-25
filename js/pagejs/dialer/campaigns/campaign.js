$("#btnCancel").click(function () {
    dialog.dialog("close");
});

function yesNoCheck() {
    if (document.getElementById('custom_questions_yes').checked) {
        document.getElementById('custom_question_value_div').style.display = 'block';
    }
    else document.getElementById('custom_question_value_div').style.display = 'none';

}

// Start HP UAD-3 configure campaign
function showHideAutoDial() {
    if (document.getElementById('autodial_yes').checked) {
        document.getElementById('autodial_options').style.display = 'block';
    }
    else document.getElementById('autodial_options').style.display = 'none';
}
// End HP UAD-3 configure campaign

//Campaign detail dialog - Save button event
$("#campaign_btnSave").click(function () {
    var statusValue, checkStatusValue, customQuestionsValue, checkcustomQuestionsValue, autoDialValue, checkAutoDialValue;

    function CheckStatusValueActive() {
        statusValue = $('#status').val();
        if (statusValue == 'active') {
            return true;
        } else {
            return false;
        }
    }

    function CheckCustomValueYes() {
        customQuestionsValue = $('input[name="custom_questions"]:checked').val();

        if (customQuestionsValue == 1) {
            return true;
        } else {
            return false;
        }
    }

    // Start RP UAD-8 : check auto dial value yes or no
    function CheckAutoDialValueYes() {
        autoDialValue = $('input[name="autodial"]:checked').val();
        if (autoDialValue == 1) {
            return true;
        } else {
            return false;
        }
    }

    
    checkcustomQuestionsValue = CheckCustomValueYes();

    checkStatusValue = CheckStatusValueActive();

    checkAutoDialValue = CheckAutoDialValueYes();
    
    // End RP UAD-8 : check auto dial value yes or no

    $.validator.addMethod("greaterThan",

        function (value, element, param) {

            checkStatusValue = CheckStatusValueActive();

            if (checkStatusValue || (value != undefined && value != '' && value != null)) {
                var $min = $(param);
                if ($min.val() != "") {
                return Date.parse(value) > Date.parse($min.val());
                }
                else
                 return true;
            } else {
                return true;
            }
        }, "Start date must be less than end date");

    $.validator.addMethod("lessThan",

        function (value, element, param) {

            checkStatusValue = CheckStatusValueActive();

            if (checkStatusValue || (value != undefined && value != '' && value != null)) {
                var $min = $(param);
                if ($min.val() != "") {
                return Date.parse(value) < Date.parse($min.val());
                }
                else
                  return true;
            } else {
                return true;
            }
        }, "End date must be greater than start date");

    $('#campaign_form').validate({

        submitHandler: function (form) {
            form.submit();
        }
    });

    $("#eg_campaign_id").rules("add", {
        min: 1,
        required: true,
        maxlength: 10,
        number: true,
        messages: {
            required: ""
        }
    });
    $("#lead_goal").rules("add", {
        required: checkStatusValue,
        digits: true,
        max: 30000,
        maxlength: 5,
        messages: {
            required: "Please enter only digits"
        }
    });

    $("#custom_question_value").rules("add", {
        required: checkcustomQuestionsValue,
        maxlength: 200,
        messages: {
            required: ""
        }
    });

    $("#script_main").rules("add", {
        required: checkStatusValue,
        minlength: 5,
        messages: {
            required: ""
        }
    });
    
    // Start RP UAD-8 : add validation 
    $("#auto_abandoned_rate").rules("add", {
        required: checkAutoDialValue,
        minlength: 1,
        number: true,
        messages: {
            required: ""
        }
});

    $("#auto_recorded_message_one").rules("add", {
        required: checkAutoDialValue,
        minlength: 10,
        messages: {
            required: ""
        }
    });
    
    $("#auto_recorded_message_two").rules("add", {
        required: checkAutoDialValue,
        minlength: 10,
        messages: {
            required: ""
        }
    });
    // End RP UAD-8 : add validation 
});

function edit_tm_offices() {
    var tm_offices = [];
    $.each($("#telemarketing_offices option:selected"), function () {
        tm_offices.push($(this).val());
    });

    var get_tl_office_url = 'dialer/campaigns/get_tl_user_list/';
    var postData = "tm_offices=" + tm_offices + "&module_value=" + 'tm';
    AjaxCall(get_tl_office_url, postData, "post", "json").done(function (response) {
        if (response.status == false) {
            ShowAlertMessage(response.message);
            $("#assign_team_id").html("");
        } else {
            if(response.data != undefined){
            var j = response.data;
            var options = '<option role="option" value=""> ---SELECT ONE---</option>';
            for (var i = 0; i < j.length; i++) {
                options += '<option role="option"  value="' + j[i].id + '">' + j[i].first_name + '</option>';
            }
            $("#assign_team_id").html(options);
            }else{
                $("#assign_team_id").html("");
        }

        }
    });
}
$('#telemarketing_offices').focus(function () {
    prev_val = $(this).val();
}).change(function () {
    var create_segment = document.URL.substr(document.URL.lastIndexOf('/') + 1);
    if(create_segment == 'create' || create_segment == 'create#'){
        edit_tm_offices();
    }else{
        var success = confirm('Please make sure that no user is working on this campaign or stop/pause calling before make any changes?');
        if (success)
        {
            edit_tm_offices();
        }
        else
        {
            $(this).val(prev_val);
            return false;
        }
    }

});

$(document).ready(function () {
    NumericTextOnlyAllowed('eg_campaign_id');
    NumericTextOnlyAllowed('auto_abandoned_rate');
    NumericTextOnlyAllowed('lead_goal');
    $("#call_filerequest_date").datepicker();
    $("#materials_sent_to_tm_Date").datepicker();
    var create_segment = document.URL.substr(document.URL.lastIndexOf('/') + 1);
    if(create_segment == 'create' || create_segment == 'create#'){
        $("#telemarketing_offices").change();
    }
    });

$(function () {
    $("#campaign_item").addClass("active open");
    var Hidden_Campaign_ID = $('#Hidden_Campaign_ID').val();
    if (Hidden_Campaign_ID <= 0) {
        $("#campaign_add").addClass("active");
    } else {
        if (document.getElementById('custom_questions_yes').checked) {
            document.getElementById('custom_question_value_div').style.display = 'block';
        }
        else document.getElementById('custom_question_value_div').style.display = 'none';
    }    

});
var campaignId = $('#Hidden_Campaign_ID').val();
var assign_team_id = $('#assign_team_id').val();

(function ($) {

    $.widget("app.autocomplete", $.ui.autocomplete, {

        // Which class get's applied to matched text in the menu items.
        options: {
            highlightClass: "ui-state-highlight"
        },

        _renderItem: function (ul, item) {

            // Replace the matched text with a custom span. This
            // span uses the class found in the "highlightClass" option.
            var re = new RegExp("(" + this.term + ")", "gi"),
                cls = this.options.highlightClass,
                template = "<span class='" + cls + "'>$1</span>",
                label = item.label.replace(re, template),
                $li = $("<li/>").appendTo(ul);

            // Create and return the custom menu item content.
            $("<a/>").attr("href", "#")
                .html(label)
                .appendTo($li);

            return $li;

        }

    });
    if (eGCampaignList != undefined && eGCampaignList != '') {
        var availableEGCampaigns = $.parseJSON(eGCampaignList);

        $("#eg_campaign_id").autocomplete({
            source: availableEGCampaigns,
            highlightClass: "auto-complete-bold-text",
            response: function (event, ui) {
                if (ui.content.length === 0) {
                    $("#empty-message").text("No results found");
                } else {
                    $("#empty-message").empty();
                }
            },
            select: function (e, u) {

                if (u.item.value == -1) {
                    return false;
                }
                if (parseInt(u.item.value) > 0) {
                    var getEGCampaignDataByIDURL = 'dialer/campaigns/getEGCampaignDataByID';
                    var postData = "egCampaignID=" + u.item.value;
                    AjaxCall(getEGCampaignDataByIDURL, postData, "post", "json").done(function (response) {
                        if (response.status) {

                            $('#campaign_fields').css("display", "block");
                            $('#dis_name').text(response.data.name);
                            $('#dis_type').text(response.data.type);
                            $('#dis_cpl').text(response.data.cpl);
                            $('#dis_lead_goal').text(response.data.leads_ordered);
                            $('#dis_start_date').text(response.data.start_date);
                            if (response.data.program_end_date == null) {
                                $('#dis_end_date').text('-');
                            }
                            else {
                                $('#dis_end_date').text(response.data.program_end_date);
                            }
                            if (response.data.tm_brand != undefined && response.data.tm_brand != null && response.data.tm_brand != '') {
                                $('#site_tm_brand_name').show();
                                $('#dis_site_name').text(response.data.tm_brand);
                                $('#site_name').val(response.data.tm_brand);
                            }else{
                                $('#site_tm_brand_name').show();
                                $('#dis_site_name').text("Enterprise Guide");
                               // $('#site_tm_brand_name').hide();
                                $('#site_name').val("Enterprise Guide");
                            }
                            $('#name').val(response.data.name);
                            $('#status').val(response.data.status);
                            $('#type').val(response.data.type);
                            $('#cpl').val(response.data.cpl);
                            $('#lead_goal').val(response.data.leads_ordered);
                            $('#start_date').val(response.data.start_date);
                            //$('#end_date').val(response.data.end_date);
                            $('#end_date').val(response.data.program_end_date);
                            $('#company_name').val(response.data.company);
                            $('#dis_job_function').html(response.data.silo_filter);
                            $('#job_function').val(response.data.silo_filter_db);
                            $('#dis_job_level').html(response.data.job_level);
                            $('#job_level').val(response.data.job_level_db);
                            $('#dis_company_size').html(response.data.company_size);
                            $('#company_size').val(response.data.company_size_db);
                            $('#dis_industries').html(response.data.industry);
                            $('#industries').val(response.data.industry_db);
                            $('#dis_country').html(response.data.country);
                            $('#country').val(response.data.country_db);
                            $('#dis_filters').html(response.data.filters);
                            if (response.data.type == 'puremql' || response.data.type == 'pureresearch' || response.data.type == 'smartleads') {
                                $('#custom_questions_div').hide();
                                $('#custom_question_value').removeAttr('required');
                            } else {
                                $('#custom_questions_div').show();
                                $('#custom_question_value').attr('required', 'required');
                            }
                            return true;
                        }
                        else {
                            ShowAlertMessage(response.message);
                        }
                    });
                }
            }
        });

        $( "#eg_campaign_id" ).autocomplete('widget').addClass("auto-complete-fixedHeight");
    }

}(jQuery));
