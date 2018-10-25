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

    $('.reason_link').click(function(){
        var leadID = $(this).next('.reason_lead_id').text();
        var url = 'dialer/leads/getReasons/';
        var postData = "leadID="+leadID;
        AjaxCall(url, postData, "post", "json").done(function (response) {
            if (response.status) {
                var data =  '<div style="padding: 30px !important;">';
                data += '<table class="model table table-bordered vertical-tbl sort-th" style="width: 100%;table-layout: fixed;"> \n\
                        <tr><th>Reason</th><th>Reason Text</th>';
                dataarray= new Array();
                dataarray = response.data;
                for (i = 0; i < dataarray.length; i++) {
                    data += "<tr><td>"+dataarray[i].reason + "</td><td>"+dataarray[i].reason_text + "</td></tr>";
                }
                data += "</table></div>";
                dialog.dialog("open");
                dialog.dialog("option", "title", "REJECTION REASONS");
                $('#dialog-form').html(data);
            }else{
                ShowAlertMessage(response.message);
            }
        });
    });

});
function displayResource(name){
    var data =  '<div style="padding: 30px !important;">';
                data += '<table class="model table table-bordered vertical-tbl sort-th" style="width: 100%;table-layout: fixed;"> \n\
                        <tr><th class="text_align_center">Resource Name</th></tr><tr><td>'+name+'</td></tr><table>';
    data += "</div>";
    dialog.dialog("open");
    dialog.dialog("option", "title", "RESOURCE");
    $('#dialog-form').html(data);
}
var dialog = $("#dialog-form").dialog({
    autoOpen: false,
    height: 340,
    width: 650,
    modal: true,
    resizable: true,
    dialogClass: 'popup-title'
});

$("#btnCancel").click(function () {
    dialog.dialog("close");
    $('#dialog-form').html('');
});

