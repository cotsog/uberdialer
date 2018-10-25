$(document).ready(function()
{
    $("table#uploadsummary_history tr:even").css("background-color", "#FFFFFF");
    $("table#uploadsummary_history tr:odd").css("background-color", "#F4F4F8");
});

// call this function for Date range datepicker
$(function () {
    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#to_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            dt2.datepicker('option', 'minDate', startDate);
        }
    });
    $("#to_date").datepicker({showAnim: 'slideDown',
        onSelect: function (date) {
            var dt1 = $('#from_date');
            var endDate = $(this).datepicker('getDate');
            dt1.datepicker('option', 'maxDate', endDate);
        }
});
});

// post form with pagination search value
$('.pagination li a').click(function() {
    $('#leadstatus_searchform').attr('action', $(this).attr('href'));
    $('#leadstatus_searchform').submit();

    return false;
});

// post form with sorting field
$('#sort_column a').click(function() {
    $('#leadstatus_searchform').attr('action', $(this).attr('href'));
    $('#leadstatus_searchform').submit();

    return false;
});

// Active current page on left navigation slide bar
$("#report_item").addClass("active open");
$("#upload_summay").addClass("active");
