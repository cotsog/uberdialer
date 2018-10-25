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

function seeMoreNotes(lead_history_ids){
    var notesUrl = 'dialer/reports/get_more_notes/';
    var postData = "lead_history_ids="+ lead_history_ids ;
    AjaxCall(notesUrl, postData, "post", "json").done(function (response) {
        if (response.status) {

            var data =  '<div style="padding: 30px !important;">';
            data += '<table class="model table table-bordered vertical-tbl sort-th" style="width: 100%;table-layout: fixed;"> \n\
                        <tr><th>User Type</th><th>User Name</th><th>Notes</th>';
            dataarray= new Array();
            dataarray = response.data;
            for (i = 0; i < dataarray.length; i++) {
                data += "<tr><td>"+dataarray[i].user_type + "</td><td>"+dataarray[i].first_name + "</td><td>"+dataarray[i].note + "</td></tr>";
            }
            data += "</table></div>";
            dialog.dialog("open");
            $('#dialog-form').html(data);
        }else{
            ShowAlertMessage(response.message);
        }
    });
}

function showHide(shID) {

    if (document.getElementById(shID)) {
        if (document.getElementById(shID+'-show').style.display != 'none') {
            document.getElementById(shID+'-show').style.display = 'none';
            document.getElementById(shID).style.display = 'block';
        }
        else {
            document.getElementById(shID+'-show').style.display = 'inline';
            document.getElementById(shID).style.display = 'none';
        }
    }
}

var dialog = $("#dialog-form").dialog({
    autoOpen: false,
    height: 400,
    width: 650,
    modal: true,
    resizable: true,
    dialogClass: 'popup-title'
});

$("#btnCancel").click(function () {
    dialog.dialog("close");
    $('#dialog-form').html('');
});

$("#report_item").addClass("active open");
$("#qa_escalation").addClass("active");