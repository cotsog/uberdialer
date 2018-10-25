$(function () {
    var oldGrid;

    $(".fa-times-circle").css('cursor', 'pointer');
    $(".divErrorMsg").css('cursor', 'pointer');
    initpophover();
    // var eGCampaignList = ":All;Davao:Davao TM;Oceana:Oceana TM;Virtual:Pampanga VTM;Davao VTM:Davao VTM;Cebu TM:Cebu TM;Cebu VTM:Cebu VTM";
    var eGCampaignList = $.parseJSON(tm_offices);
    var categoriesStr = $.parseJSON(allUserTypes);
    var statusStr = ":All;Active:Active;InActive:InActive;Released:Released;Resigned:Resigned";
    var moduleStr = ":All;Telemarketing:Telemarketing;Appointment:Appointment";
    var theGrid = jQuery("#list");
    var LogInUserType = '';

    if (logged_user_type != undefined) {
        LogInUserType = logged_user_type;
    }
    var hiddenTMOfficeColumn = true;
     if ($.inArray( LogInUserType, upperManagementTypes ) >= 0 || LogInUserType == 'manager') {
         hiddenTMOfficeColumn = false;
    }
    var hiddenParentColumn = false;
    if (LogInUserType == 'team_leader') {
         hiddenParentColumn = true;
    }
    theGrid.jqGrid({
    datatype: 'local',
        data: usersdata,
        colNames: ["Name", "Email", "User Type","Group/Team","Telemarketing Offices","Module","Status", "Created Date"],
        colModel: [
            {name: "full_name", index: 'full_name', width: '150px',classes: 'break-word word-wrap'},
            {name: "email", index: 'email', width: '200px' ,classes: 'break-word word-wrap'},
            {name: "user_type", index: 'user_type', width: '200px', formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value: categoriesStr},
                searchoptions: {sopt: ['eq'], value: categoriesStr}
            },
            {name: "parent", index: 'parent', width: '150px',classes: 'break-word word-wrap',
                hidden: hiddenParentColumn,
                editable: hiddenParentColumn,
                editrules: {edithidden: hiddenParentColumn}
            },
            {name: "telemarketing_offices", index: 'telemarketing_offices', width: '150px',classes: 'break-word word-wrap', stype: 'select',
                edittype: 'select',
                editoptions: {value: eGCampaignList},
                searchoptions: {value: eGCampaignList, defaultValue: ""},
                hidden: hiddenTMOfficeColumn,
                editable: hiddenTMOfficeColumn,
                editrules: {edithidden: hiddenTMOfficeColumn}},
            {
                name: "module",
                index: 'module',
                width: '120px',
                stype: 'select',
                edittype: 'select',
                editoptions: {value: moduleStr},
                searchoptions: {value: moduleStr, defaultValue: ""}
            },
            {name: "status", index: 'status', width: '200px', formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value: statusStr},
                searchoptions: {sopt: ['eq'], value: statusStr}
            },

            {
                name: "created_at", index: 'created_at', width: '150px', formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                formatoptions: {srcformat: 'Y-m-d', newformat: 'm/d/Y'}
            }
        ],
        sortname: 'full_name',
        sortorder: 'asc',
        pager: "#pager",
        edit: true,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: 10,
        rownumbers: false,
        height: 'auto',
        width: 'auto',
        loadonce: true,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: true,
        multiselectWidth: 50,
        emptyrecords: "No records available.",
        //viewrecords: true,
        gridComplete: function () {
            setSearchDate(theGrid, "created_at", 'm/d/yy');
           // setSearchDate(theGrid, "created_at", 'yy-mm-dd');
            theGrid.jqGrid('filterToolbar', {beforeSearch: function() { 
            fnBeforeSearch(theGrid); 
             if(oldGrid!=""){$('#list tbody').html(oldGrid);}
           },stringResult: true, searchOnEnter: false, defaultSearch: "cn"});
        },
        loadComplete: function () {
            applyCustomPaging(theGrid);
            if ($('#list').getGridParam('records') === 0) {
                oldGrid = $('#list tbody').html();
                $('#list tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');
                $('#jqgh_list_cb').hide();
                $('#edit-u').hide();
//                $('#del-u').hide();
            }
            else{
                oldGrid = "";
                $('#jqgh_list_cb').hide();
                $('#edit-u').show();
//                $('#del-u').show();
            } 
        }
    });
    $("#globalSearchText").keyup(function (e) {
        if(oldGrid!=""){
            $("#list tbody").html(oldGrid);
        }
        keywordSearch(theGrid, $("#globalSearchText").val());
        
    });
    $(".fa-edit").click(function () {
        var theCheckboxes = $("input[type='checkbox']");
        if (theCheckboxes.filter(":checked").length < 1) {
            ShowAlertMessage("Please select at least one user for edit.");
            return false;
        }
        else if (theCheckboxes.filter(":checked").length > 1) {
          
            ShowAlertMessage("Please select only one user at a time for editing.");
            return false;
        } else {
            var checkedIds = $(":checkbox:checked").map(function () {
                if (this.id != 'is_readonly') {
                    users_id = this.id;
                } else {
                    $(":checkbox:checked").prop('checked', false);
                }
                user_id = users_id.replace('jqg_list_', '');
                window.location = "/users/edit/" + user_id;
              
            });
        }
    });

// Set css For Menu  
    $("#user_lists").addClass("active open");
    $("#user").addClass("active");
});

$('#dialog-form').on('dialogclose', function (event) {
    $(":checkbox:checked").prop('checked', false);
    $("#list").jqGrid('resetSelection');
});

//status formatter for Status column of query listing
function statusformatter(cellvalue, options, rowObject) {

    var classname = 'status';
    if (cellvalue.toLowerCase() == 'incomplete') {
        classname += ' incomplete';
    }
    else if (cellvalue.toLowerCase() == 'processing') {
        classname += ' processing';
    }
    else if (cellvalue.toLowerCase() == 'queued') {
        classname += ' queued';
    }
    else if (cellvalue.toLowerCase() == 'error') {
        classname += ' error';
    }
    else {
        classname += ' completed';
        cellvalue = rowObject['Count'];
    }
    return '<span class="' + classname + '">' + cellvalue + '</span>';
}

//Account detail dialog
var dialog = $("#dialog-form").dialog({
    autoOpen: false,
    height: 640,
    width: 650,
    modal: true,
    resizable: false,
    dialogClass: 'popup-title'
});

$("#btnCancel").click(function () {
    dialog.dialog("close");
});

function initpophover() {
    //bind tooltip of filter panel: add new part button hover
    listSettings = {
        content: '<p>Add</p>',
        title: '',
        style: 'inverse',
        padding: false,
        width: 115,
        height: 10,
        trigger: 'hover',
        placement: 'bottom-left',
        delay: {show: 0, hide: 200}
    };
    $('.add-icon').webuiPopover('destroy').webuiPopover(listSettings);
    //bind tooltip of filter panel: edit button hover
    listSettings = {
        content: '<p>Edit</p>',
        title: '',
        style: 'inverse',
        padding: false,
        width: 115,
        height: 10,
        trigger: 'hover',
        placement: 'bottom-left',
        delay: {show: 0, hide: 200}
    };
    $('.fa-edit').webuiPopover('destroy').webuiPopover(listSettings);
}
$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});