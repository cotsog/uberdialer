$("#leads_btnSave").click(function () {
    $('#leadstatus_searchform').validate({

        submitHandler: function (form) {
            form.submit();
        }
    });

    $.validator.addMethod("greaterThan",

        function (value, element, param) {

            if (value != undefined && value != '' && value != null) {
                var $min = $(param);
                if($min.val() != "") {
                    return Date.parse(value) >= Date.parse($min.val());
                }
                else
                    return true;
            } else {
                return true;
            }
        }, "You cannot enter a From Date that is greater than a To Date.");

    $.validator.addMethod("lessThan",

        function (value, element, param) {

            if (value != undefined && value != '' && value != null) {
                var $min = $(param);
                if($min.val() != "") {
                    return Date.parse(value) <= Date.parse($min.val());
                }
                else
                    return true;
            } else {
                return true;
            }
        }, "You cannot enter a From Date that is greater than a To Date.");

    $("#from_date").rules("add", {
        required: true,
        lessThan: "#to_date",
        messages: {
            required: "",
            lessThan: "You cannot enter a From Date that is greater than a To Date."
        }
    });
    $("#to_date").rules("add", {
        required: true,
        greaterThan: "#from_date",
        messages: {
            required: "",
            greaterThan: "You cannot enter a To Date that is Less than a From Date."
        }
    });
    if(loggedInUserType != undefined && loggedInUserType == 'manager'){
        $("#team_leader_id").rules("add", {
            required: true,
            messages: {
                required: ""
            }
        });
    }
});

function RangeDatePicker(filter_status){
    $(function () {
        $('#from_date').datepicker('destroy');
        $('#to_date').datepicker('destroy');
        if(filter_status == 'weekly'){

        $("#from_date").datepicker({
            showAnim: 'slideDown',
            onSelect: function (date) {
                var dt2 = $('#to_date');
                var startDate = $(this).datepicker('getDate');
                dt2.datepicker('option', 'minDate', startDate);
                    startDate.setDate(startDate.getDate() + 6);
                    dt2.datepicker('setDate', startDate);
            }
        });
        }else{
            $("#from_date").datepicker({
                showAnim: 'slideDown',
                onSelect: function (date) {
                    var dt2 = $('#to_date');
                    var startDate = $(this).datepicker('getDate');
                    dt2.datepicker('option', 'minDate', startDate);
                }
            });
        }
        if(filter_status == 'weekly'){

            $("#to_date").datepicker({
                showAnim: 'slideDown',
                onSelect: function (date) {
                    var dt1 = $('#from_date');
                    var toDate = $(this).datepicker('getDate');
                    dt1.datepicker('option', 'maxDate', toDate);
                    toDate.setDate(toDate.getDate() - 6);
                    dt1.datepicker('setDate', toDate);
                }
            });
        }else{
        $("#to_date").datepicker({showAnim: 'slideDown',
            onSelect: function (date) {
                var dt1 = $('#from_date');
                var endDate = $(this).datepicker('getDate');
                dt1.datepicker('option', 'maxDate', endDate);
            }
        });
        }


    });
}

function filter_by(filter_status) {
    if (filter_status == 'daily') {
        $('#daily').removeClass('dashboard-weekly');
        $('#daily').addClass('dashboard-daily');
        $('#weekly').removeClass('dashboard-daily');
        $('#weekly').addClass('dashboard-weekly');
    } else if (filter_status == 'weekly') {
        $('#weekly').removeClass('dashboard-weekly');
        $('#weekly').addClass('dashboard-daily');
        $('#daily').removeClass('dashboard-daily');
        $('#daily').addClass('dashboard-weekly');
    }
}
$('.dashboard-btn-default').click(function () {
    var filter_status = $(this).html().toLowerCase();
    RangeDatePicker(filter_status);

    filter_by(filter_status);

    $('#filter_status').val(filter_status);
    $('#btn_filter_status').val($('#filter_status').val());
    $('#btn_team_leader_id').val($('#team_leader_id').val());
    $('#btn_from_date').val($('#from_date').val());
    $('#btn_to_date').val($('#to_date').val());
    $('#btn_type').val(filter_status);

    $('#filter_form').validate({
        submitHandler: function (form) {
            form.submit();
        }
});
});

//** filter by daily and weekly

/*$(".dashboard-btn-default").click(function () {


});*/

//** filter by daily and weekly *//

$(document).ready(function () {

    

    $("table#DataTables_Table_0 tr:even").css("background-color", "#F4F4F8");
    $("table#DataTables_Table_0 tr:odd").css("background-color", "#FFFFFF");
    var filter_status = $('#filter_status').val();
    if(filter_status != undefined && filter_status != ''){
        RangeDatePicker(filter_status);
        filter_by(filter_status);
    }else{
       RangeDatePicker('daily');
        filter_by('daily');
    }

    $(".toggle").click(function()
    {
        $(this).parents('tr').next('tr').find('#DataTables_Table_0').toggle();
        return false;
    });
});