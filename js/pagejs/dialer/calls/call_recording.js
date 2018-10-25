function RangeDatePicker(lead_status){
    $(function () {
            $("#start_date").datepicker({
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
}
$('#status').change(function(){
    var lead_status = $('#status').val();
    RangeDatePicker(lead_status);
});
$(document).ready(function () {
    var lead_status = $('#status').val();
    RangeDatePicker(lead_status);
});
$("#qa_item").addClass("active open");
$("#call_history").addClass("active");

$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});


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

$('.thelink').click(function(){ 
    var leadID = $(this).next('.leadid').text();//$(".leadid").text();
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
//            $('#dialog-form').css('max-height','340px !important');
//            $('#dialog-form').css('overflow','auto !important');
            
        }else{
            ShowAlertMessage(response.message);
        } 
    });
});

function seeMoreNotes(campaign_contact_ids){
    var notesUrl = 'dialer/calls/get_more_notes_by_call';
    var postData = "campaign_contact_ids="+ campaign_contact_ids ;
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
            dialog.dialog("option", "title", "Notes");
            $('#dialog-form').html(data); 
        }else{
            ShowAlertMessage(response.message);
        }
    });
}

$('.pagination li a').click(function() {
    //console.log($(this).attr('href'));
    $('#leadstatus_searchform').attr('action', $(this).attr('href'));
    $('#leadstatus_searchform').submit();

     return false;
});

