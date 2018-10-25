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
        var date = new Date($('#from_date').val());
            date.setDate(date.getDate() + 6);
        $("#to_date").datepicker({
                showAnim: 'slideDown',
                minDate: new Date($('#from_date').val()),
                maxDate: date
        });
    }else{
        $("#to_date").datepicker({showAnim: 'slideDown'});
    }
});
$("#report_item").addClass("active open");
$("#agent_status").addClass("active");
