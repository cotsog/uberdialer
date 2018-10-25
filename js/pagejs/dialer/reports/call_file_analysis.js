$(document).ready(function()
{
    $("table#call_analysis_report tr:even").css("background-color", "#FFFFFF");
    $("table#call_analysis_report tr:odd").css("background-color", "#F4F4F8");
});
$("#report_item").addClass("active open");
$("#call_file_analysis").addClass("active");


$('#form').validate({
    rules: {
        campaign_name: "required",
        weekNo: "required"
    },
    messages: {
        campaign_name: "",
        weekNo:  ""
    }    
});

$("#report_item").addClass("active open");
$("#call_file_analysis").addClass("active");

$(function () {
    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#to_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            startDate.setDate(startDate.getDate() + 30);
            //sets dt2 maxDate to the last day of 30 days window
            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
        }
    });
    $("#to_date").datepicker({showAnim: 'slideDown'});    
});

 $(function () {
    $("#start_date").datepicker({
        format: "Y-m-d",
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#end_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
        dt2.datepicker('option', 'minDate', startDate);
        }
    });
    $("#end_date").datepicker({showAnim: 'slideDown',
        onSelect: function (date) {
        var dt1 = $('#start_date');
        var endDate = $(this).datepicker('getDate');
        dt1.datepicker('option', 'maxDate', endDate);
        }
    });

    });
