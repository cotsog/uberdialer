$("#lead_track_filterBtn").click(function () {
    $('#lead_track_form').validate({

        submitHandler: function (form) {
            form.submit();
        }
    });

    $.validator.addMethod("greaterThan",

        function (value, element, param) {

            if (value != undefined && value != '' && value != null) {
                var $min = $(param);
                if($min.val() != "") {
                    return Date.parse(value) >= Date.parse($min.val());
                }
                else
                    return true;
            } else {
                return true;
            }
        }, "You cannot enter a From Date that is greater than a To Date.");

    $.validator.addMethod("lessThan",

        function (value, element, param) {

            if (value != undefined && value != '' && value != null) {
                var $min = $(param);
                if($min.val() != "") {
                    return Date.parse(value) <= Date.parse($min.val());
                }
                else
                    return true;
            } else {
                return true;
            }
        }, "You cannot enter a From Date that is greater than a To Date.");

    $("#from_date").rules("add", {
        required: true,
        lessThan: "#to_date",
        messages: {
            required: "",
            lessThan: "You cannot enter a From Date that is greater than a To Date."
        }
    });
    $("#to_date").rules("add", {
        required: true,
        greaterThan: "#from_date",
        messages: {
            required: "",
            greaterThan: "You cannot enter a To Date that is Less than a From Date."
        }
    });
    if(loggedInUserType != undefined && loggedInUserType == 'manager'){
        $("#team_leader_id").rules("add", {
            required: true,
            messages: {
                required: ""
            }
        });
    }
});

$(document).ready(function()
{
    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#to_date');
            var startDate = $(this).datepicker('getDate');
            dt2.datepicker('option', 'minDate', startDate);
            startDate.setDate(startDate.getDate() + 6);
            dt2.datepicker('setDate', startDate);
        }
    });

    $("#to_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt1 = $('#from_date');
            var toDate = $(this).datepicker('getDate');
            dt1.datepicker('option', 'maxDate', toDate);
            toDate.setDate(toDate.getDate() - 6);
            dt1.datepicker('setDate', toDate);
        }
    });


    $("table#staffing_attrition_report tr:even").css("background-color", "#FFFFFF");
    $("table#staffing_attrition_report tr:odd").css("background-color", "#F4F4F8");
});
$("#report_item").addClass("active open");
$("#consolidated_lead_track").addClass("active");