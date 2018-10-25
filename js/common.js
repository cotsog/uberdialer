var hideLoading = true;
var serverError = "Oops! Something went wrong on server, suspected error would be ";
var set_heart_beat_interval = 120000; // check user active or not since last two minutes //120000 // 60000
var set_inactivity_interval = 900000; // check user active or not since last 15 minutes for specific page. //900000 //180000
var set_inactivity_time_in_minutes = 15; // This minutes should be convert as per 'set_inactivity_interval' variable value; //3

$.widget("ui.dialog", $.ui.dialog, {
    options: {
        clickOutside: false, // Determine if clicking outside the dialog shall close it
        clickOutsideTrigger: "" // Element (id or class) that triggers the dialog opening 
    },

    open: function () {

        var clickOutsideTriggerEl = $(this.options.clickOutsideTrigger);
        var that = this;

        if (this.options.clickOutside) {
            // Add document wide click handler for the current dialog namespace
            $(document).on("click.ui.dialogClickOutside" + that.eventNamespace, function (event) {
                if ($(event.target).closest($(clickOutsideTriggerEl)).length == 0 && $(event.target).closest($(that.uiDialog)).length == 0) {
                    that.close();
                }
            });
        }

        this._super(); // Invoke parent open method
    },

    close: function () {
        var that = this;

        // Remove document wide click handler for the current dialog
        $(document).off("click.ui.dialogClickOutside" + that.eventNamespace);

        this._super(); // Invoke parent close method 
    }

});

$(document).ready(function () {

    //set left navigation height
    setLeftNavigationHeight();

    //checkbox style
    h = $(document).height() - 50;
    $('#chkbox').css({'height': h + 'px', 'background-color': '#272b35'});

    //menu expand/collase buton click event
    $('.menu').click(function () {
        var container = $(this);
        var width = parseInt(container.outerWidth());
        if (!container.hasClass('hidden')) {
            container.css({'margin-left': 0});
            container.css({'left': 57});
            container.addClass('hidden');
        } else {
            container.removeClass('hidden');
            container.css({'left': 200});
        }
    }), 0;
});

//get current datetime: format dd-M-yyyy hh:mm
function getCurrentDateTime() {
//    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var months = ["Jan", "Feb", "March", "April", "May", "June", "July", "Aug", "Sep", "Oct", "Nov", "Dec"];

    var s = function (p) {
        return ('' + p).length < 2 ? '0' + p : '' + p;
    };

    var current_date = new Date();
    month = current_date.getMonth();
    day = s(current_date.getDate());
    year = current_date.getFullYear();
    hour = s(current_date.getHours());
    minute = s(current_date.getMinutes());

    return months[month].toUpperCase()+ "-"+ day + "-" + year + " " + hour + ":" + minute;
}


//get left navigation height
function setLeftNavigationHeight() {
    var h = $(document).height() - 50;
    $('.left-panel').css({'height': h});
}



function TextOnlyAllowed(InputText){
    $("#"+ InputText).keypress(function(event){
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if(!(inputValue >= 65 && inputValue <= 120) && inputValue != 32 && inputValue != 0 && inputValue != 8) { 
            event.preventDefault(); 
        }
    });
}

function NumericTextOnlyAllowed(InputTextID) {
    $("#" + InputTextID).keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
            (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) ||
                // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

function isNumberKey(evt)
{
    if( navigator.userAgent.toLowerCase().indexOf('firefox') > 1 ){
        var e = evt || window.event; // for trans-browser compatibility
        var charCode = e.which || e.keyCode;
        if(charCode == 118) return true;
        
        if (charCode > 31 && (charCode < 47 || charCode > 57))
            return false;
        if (e.shiftKey) return false;
            return true;
        
    }else{
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)){
            return false;
        }
        return true;
   }
   
}

//style="padding: 10px; max-width: 500px; word-wrap: break-word;"
function ShowConfirm(dialogText, okFunc, cancelFunc, dialogTitle,firstButtonName) {
    if(!firstButtonName)
        firstButtonName = 'Delete';

    $('.remove-user').dialog('close');
    $('<div id="dialog-confirm" class="remove-user" ><p>' + dialogText + '</p></div>').dialog({
        draggable: false,
        modal: true,
        resizable: false,
        width: 475,
        title: dialogTitle || 'Confirm',
        minHeight: 75,
        buttons: [{
                text: firstButtonName,
                click : function() {
                if (typeof (okFunc) == 'function') {
                    setTimeout(okFunc, 50);
                }
                $(this).dialog('destroy');
                    // submit form
                }
            }, {
                text: "Cancel",
                click: function() {
                if (typeof (cancelFunc) == 'function') {

                    setTimeout(cancelFunc, 50);
                }
                $(this).dialog('destroy');
                    // don't submit form
            }
        }],
        create: function () {
            $('.remove-user').parent('.ui-dialog').addClass('white-bg');
            $(this).closest(".ui-dialog").find(".ui-button").eq(0).find("span").addClass("delete_cancel_button");
            $(this).closest(".ui-dialog").find(".ui-button").eq(1).find("span").addClass("general-btn");
            $(this).closest(".ui-dialog").find(".ui-button").eq(2).find("span").addClass("general-btn").attr("id", "delete_cancel_button");
            $(this).closest('div.ui-dialog')
                .find('.ui-dialog-titlebar-close')
                .click(function(e) {
                    $(":checkbox:checked").prop('checked', false);
                    $("#list").jqGrid('resetSelection');
                    e.preventDefault();
                });
        }
    });
}

function ShowAlertMessage(output_msg, title_msg) {
    if (!title_msg)
        title_msg = 'Oops! Something went wrong';

    if (!output_msg)
        output_msg = 'Sorry, an error has occurred';

    $('.remove-user').dialog('close');
    $("<div class='remove-user'></div>").html('<p class="output_alert_msg">' + output_msg + '</p>').dialog({
        title: title_msg,
        resizable: false,
        modal: true,
        width: 475,
        buttons: {
            "Ok": function () {
                $(this).dialog("close");
            }
        },
        create: function () {
            $('.remove-user').parent('.ui-dialog').addClass('white-bg');
            $(this).closest(".ui-dialog").find(".ui-button").eq(1).find("span").addClass("general-btn");

        }
    });
}

function AjaxCall(url, postData, httpmethod, calldatatype, contentType, showLoading, hideLoadingParam, isAsync) {
    if (hideLoadingParam != undefined && !hideLoadingParam)
        hideLoading = hideLoadingParam;
    if (contentType == undefined)
        contentType = "application/x-www-form-urlencoded;charset=UTF-8";

    if (showLoading == undefined)
        showLoading = true;

    if (showLoading == false || showLoading.toString().toLowerCase() == "false")
        showLoading = false;
    else
        showLoading = true;


    if (isAsync == undefined)
        isAsync = true;

    return jQuery.ajax({
        type: httpmethod,
        url: baseUrl+url,
        data: postData,
        global: showLoading,
        dataType: calldatatype,
        contentType: contentType,
        async: isAsync,
        processData: false,
        beforeSend: function () { }, //$.blockUI();},//beforeSend: function() { if (showLoading) myApp.showPleaseWait(); },$('body').addClass("loading");
        error: function(xhr, textStatus, errorThrown) {

            if (!userAborted(xhr)) {
                if (xhr.status == 403) {
                    var isJson = false;
                    try {
                        var response = $.parseJSON(xhr.responseText);
                        isJson = true;
                    }
                    catch (e) { }
                    if (isJson && response != null && response.Type == "NotAuthorized" && response.Link != undefined)
                        window.location = baseUrl +response.Link;
                    else
                        window.location = window.baseUrl;
                }
                else {
                    var alertText = "";
                    switch (xhr.status){
                        case 404:
                            alertText =  serverError +  "'Method " + xhr.statusText + "'";
                            break;

                        case 200:
                            alertText = "";
                            break;

                        default :
                            alertText =  serverError + "'" + xhr.statusText + "'";
                            break;
                    }
                    alert(alertText);
                }
            }
        }
    });
}

function BlockUI() {
    $('body').addClass("body_loading");
}

function UnBlockUI() {
    $('body').removeClass("body_loading");
}

$(document).ajaxStop(function (jqXHR, settings) {
    if (hideLoading) {
        UnBlockUI();
        $("#main").show();
    }
});

function userAborted(xhr) {
    return !xhr.getAllResponseHeaders();
}

$(document).ready(function() {
    if(logged_user_type != "qa" && app_module_type == "tm") {
        callbackReminder();
    }

    tId = setInterval(heartBeatAjaxCall, set_heart_beat_interval); //300000ms = 5 minutes == 300000

    // heartbeat call for user session
    //setInterval(userHeartBeatAjaxCall, 300000); //5 minutes == 300000
    
});

function callbackReminder()
{
    $.ajax({
        type:"post",
        dataType: "json",
        url:"/dialer/calls/callbackReminder",
        success:function(data){
            if(data.status == 1){
                 
               var response = data.details;

                html = '';

                $.each(response, function(i, item) {

                    html = html + '<p style="word-break: break-all">';
                    html = html + '<label>Prospect:</label><span> ' + item.prospect + '</span>';
                    html = html + '<br><label>When:</label><span> ' + item.call_disposition_update_date + '</span>';
                    html = html + '<br><label>Company:</label><span> ' + item.company + '</span>';
                    html = html + '<br><label>Phone Number:</label><span> ' + item.phone + '</span>';
                    html = html + '</p>';
                });

                 $('#callback-details').html(html);
                 $('.callout').show();
            }    
        }
    });
}

function userHeartBeatAjaxCall() {

    $.ajax({
        type: 'POST',
        url:baseUrl+'users/updateuserlastactivity',
         dataType: 'json'
    }).success(function(response) {

    }).error(function(response) {

    });
}

function heartBeatAjaxCall() {

    $.ajax({
        type: 'POST',
        url:baseUrl+'users/heartbeatcall',
		 dataType: 'json'
    }).success(function(response) {
        //CampaignSignOutPopupDialogOpen(response);
        //LogoutPopupDialogOpen(response);
    }).error(function(response) {

    });

    userHeartBeatAjaxCall();
}

function setCookie(key,value){
    $.cookie(key, value, { expires: 7, path: '/' });
}

function getCookie(key){
    return $.cookie(key);
}

setIdleTimeout(set_heart_beat_interval); //3000;       // 30 seconds //120000
setAwayTimeout(set_inactivity_interval); //600000;      // 10 minutes //900000
document.onIdle=function(){
    clearInterval(tId);
};
document.onAway=function(){
    var onAwayStatus = {};
    onAwayStatus.status = false;
    var getUnExpiredCookie = getCookie('event_idle_time_stamp');//parseInt(document.cookie.split('=')[2]);
    var difference = new Date().getTime() - parseInt(getUnExpiredCookie); // This will give difference in milliseconds
    var resultInMinutes = Math.round(difference / 60000);

    // check campaign sign in with agent user type
    if(sign_in_user_type != undefined && sign_in_user_type == 'agent' && resultInMinutes >= set_inactivity_time_in_minutes){
        $.ajax({
            type: 'POST',
            url:baseUrl+'dialer/campaigns/check_agent_session',
            dataType: 'json'
        }).success(function(response) {
            if(response.data != undefined && response.data.campaign_id != undefined){
                $.ajax({
                    type: 'POST',
                    url:baseUrl+'dialer/contacts/index/'+response.data.campaign_id+'/0/'+'out',
                    dataType: 'json'
                }).success(function(response) {
                    CampaignSignOutPopupDialogOpen(response);
                }).error(function(response) {
                    window.location = baseUrl+'dialer/campaigns';
                });
            }
        }).error(function(response) {
            window.location = baseUrl+'dialer/campaigns';
        });
        }

};

function CampaignSignOutPopupDialogOpen(response){
    if(response.status == false || !response.status){
        //delete confirmation dialog
        $("#dialog-campaign-signout-confirm-redirect").dialog({
            closeOnEscape: false,
            height: 200,
            width: 350,
            draggable:false,
            modal: true,
            dialogClass: 'popup-title',
            open: function () {
                // scrollbar fix for IE
                $('body').css('overflow', 'hidden');
                $('.ui-widget-overlay').css('z-index', '1000002');
            },
            close: function () {
                // reset overflow
                $('body').css('overflow', 'auto');
                $(this).remove();
                $('.ui-widget-overlay').css('z-index', '100');
            }
        });
        $('#dialog-campaign-signout-confirm-redirect').prev().find('button').remove();
        $("#dialog-campaign-signout-confirm-redirect").dialog("open");
    }
}
/* Lock - unlock && on close tab/reload page disconnected call */

var validNavigation = false;
var lead_lock = 0;

function wireUpEvents() {
    function goodbye(e) {

        var last_call_history_id= $('#last_call_history_id').val();
        var call_end_datetime = $('#call_end_datetime').val();

        if(last_call_history_id != undefined && last_call_history_id > 0 && call_end_datetime != undefined && (call_end_datetime == '' || call_end_datetime == null)){
            return "Warning: A call is in progress. please hang up before navigating away from this page.";
        }

    }

    window.onbeforeunload=goodbye;
    
    // Attach the event keypress to exclude the F5 refresh
    $(document).bind('keypress', function(e) {

        if (e.keyCode == 116){
           validNavigation = false;
        }
    });

    // Attach the event click for all links in the page
    $("a").bind("click", function() {
        validNavigation = false;
    });

    // Attach the event submit for all forms in the page
    $("form").bind("submit", function() {
        validNavigation = true;
    });

    // Attach the event click for all inputs in the page
    $("input[type=submit]").bind("click", function() {
        validNavigation = true;
    });
    //*/
}

// Wire up the events as soon as the DOM tree is ready
$(document).ready(function() {
    wireUpEvents();
	if(logged_user_type != "qa" && app_module_type=="appt"){
    calendar_reminder();
	}
});

if(logged_user_type != "qa" && app_module_type == "appt"){
var calendar_reminder = function() {
    $.ajax({
        type:"post",
        dataType: "json",
        url:"/appt/appointmentmanager/agentReminder",
        success:function(response){
            if(response.status == "success"){
                ShowAlertMessage(response.message,'Appointment Reminder Alert');
            }    
        }
    });
};
var callback_reminder = function() {
    $.ajax({
        type:"post",
        dataType: "json",
        url:"/appt/calls/callbackReminder",
        success:function(response){
            if(response.status == "success"){
                ShowAlertMessage(response.message,'Callback Reminder Alert');
            }    
        }
    });
};
var interval = 1000 * 60 * 5; // where X is your every X minutes
setInterval(calendar_reminder, interval);
setInterval(callback_reminder, interval);
}

///trim all data before submit to search
function fnBeforeSearch(theGrid) {
var i, l, rules, rule, $grid = theGrid,
postData = $grid.jqGrid('getGridParam', 'postData'),
filters = $.parseJSON(postData.filters);

if (filters && typeof filters.rules !== 'undefined' && filters.rules.length > 0) {
rules = filters.rules;
for (i = 0; i < rules.length; i++) {
rule = rules[i];
rule.data = rule.data.trim();
}
postData.filters = JSON.stringify(filters);
}
}