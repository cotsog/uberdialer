$(document).ready(function()
{
    $("table#staffing_attrition_report tr:even").css("background-color", "#F4F4F8");
    $("table#staffing_attrition_report tr:odd").css("background-color", "#FFFFFF");

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

});

$("#report_item").addClass("active open");
$("#export_logs").addClass("active");