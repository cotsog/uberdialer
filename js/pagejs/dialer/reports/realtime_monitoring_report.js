$(function () {
    $("#report_item").addClass("active open");
    $("#realtime_monitoring_report").addClass("active");

    $("#from_date").datepicker({
        showAnim: 'slideDown',
        onSelect: function (date) {
            var dt2 = $('#to_date');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            startDate.setDate(startDate.getDate() + 90);
            //sets dt2 maxDate to the last day of 30 days window
            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
        }
    });
    $("#to_date").datepicker({showAnim: 'slideDown'});    
});
function export_rtmr(type) {
    $('#file_type').val(type);
    $('#realtime_monitoring_report_form').submit();
}