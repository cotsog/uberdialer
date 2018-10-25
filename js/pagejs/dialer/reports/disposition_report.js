$(document).ready(function()
{
    $("table#disposition_history tr:even").css("background-color", "#FFFFFF");
    $("table#disposition_history tr:odd").css("background-color", "#F4F4F8");


            
});

// call this function for Date range datepicker
$(function () {
    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var date = $(this).datepicker('getDate');
            date.setDate(date.getDate() + 6);
            $('#to_date').datepicker('option', 'maxDate', date); // Reset minimum date
             // Add 7 days
            //$('#to_date').datepicker('setDate', date); // Set as default
        },
        onClose: function () {
            $("#to_date").datepicker(
                "change", {
                minDate: new Date($('#from_date').val())
            });
            
        }
    });

    if($("#from_date").val() != "" && $("#to_date").val() != ""){
        $("#to_date").datepicker({
                showAnim: 'slideDown',
                maxDate: new Date($('#to_date').val())
        });
    }else{
        $("#to_date").datepicker({showAnim: 'slideDown',
            
        });
    }
    

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
$("#disposition_history").addClass("active");