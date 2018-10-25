$(function () {
    initpophover();
    var griddata = campaigndata;
    var theGrid = jQuery("#list");
    var newGrid;
    var newGridName = "#list";
                newPager = 'list';
    var get_selected_tab_segment = document.URL.substr(document.URL.lastIndexOf('/') + 1);
    if(get_selected_tab_segment == 'campaigns#list'){
        newGridName = "#list";
    }else if(get_selected_tab_segment == 'campaigns#gird2'){
        $("#completed_type_search").click();
    }
    callGrid(griddata, theGrid, newGridName, newPager);
    EditDeleteData(newGridName);
    $("#campaign_item").addClass("active open");
    $("#campaign_lists").addClass("active");
    $(".fa-times-circle").css('cursor', 'pointer');
    $(".divErrorMsg").css('cursor', 'pointer');
    $(".fa-times-circle").click(function () {
        $("#divErrorMsg").hide();
    });
    $("#divErrorMsg").click(function () {
        $("#divErrorMsg").hide();
    });
});
   
$("#main_type_search").click(function () {
    $("#gbox_gird2").remove();
                
    $("#dvGqgrid").append(' <table id="list" class="jqglabel"></table>  <div id="pager" class="jqgrid-footer"></div> ');
    var newGrid = jQuery("#list");
    newGridName = '#list';
                newPager = 'list';
    $('#completed_type_search').removeClass('active');
    $("#main_type_search").addClass('active');
    var griddata = campaigndata;

    callGrid(griddata, newGrid, newGridName, newPager);
    EditDeleteData(newGridName);
    displayGridToolbar('main');
});

$("#completed_type_search").click(function () {

    $("[id$='list']").remove();
    $("[id$='pager']").remove();

    $('#main_type_search').removeClass('active');
    $("#completed_type_search").addClass('active');
                $("#gbox_list").remove();
    $("#dvGqgrid").append(' <table id="gird2" class="jqglabel"></table> <div id="pager" class="jqgrid-footer"></div>  ');
    var griddata = completedcampaigndata;
    var newGrid = jQuery("#gird2");
    newGridName = '#gird2';
    newPager = 'grid2';
    callGrid(griddata, newGrid, newGridName, newPager);
    EditDeleteData(newGridName);
    displayGridToolbar('completed');
});

function EditDeleteData(newGridName) {

    $(".fa-edit").click(function () {
        var theCheckboxes = $("input[type='checkbox']").not('#cb_list,#cb_gird2,#select_all');
        if (theCheckboxes.filter(":checked").length < 1) {
            ShowAlertMessage("Please select at least one campaign for edit.");
            return false;
        }
        else if (theCheckboxes.filter(":checked").length > 1) {
            ShowAlertMessage(" Please select only one campaign at a time for editing.");
            return false;
        }
        else {
            var checkedIds = $(":checkbox:checked").map(function () {
                var campaign_id = this.id;
                if (newGridName == '#list')
                campaign_id = campaign_id.replace('jqg_list_', '');
                if (newGridName == '#gird2')
                    campaign_id = campaign_id.replace('jqg_gird2_', '');
                window.location = "/dialer/campaigns/edit/" + campaign_id;

            });
        }
    });
    $("#delete_campaign").click(function () {

        var theCheckboxes = $("input[type='checkbox']");
        if (theCheckboxes.filter(":checked").length == 0) {
            $(this).removeAttr("checked");
            ShowAlertMessage("Please select at least one campaign for delete.");
            return false;
        }
        ShowConfirm('Do you want to delete this campaign(s) ?', function () {
                var cID;
                var idArray = $('input:checkbox:checked').map(function () {
                    if (newGridName == '#list')
                         cID = this.id.replace('jqg_list_', '');
                    if (newGridName == '#gird2')
                        cID = this.id.replace('jqg_gird2_', '');
                    return cID;
                });

                idArray = jQuery.grep(idArray, function (value) {
                    return value != 'cb_list';
                });

                idArray = jQuery.grep(idArray, function (value) {
                    return value != 'cb_gird2';
                });
                var selected_campaign_id = ($.makeArray(idArray).join(','));
                var campaign_id = selected_campaign_id.replace('jqg_list_', '');
                var deleteCampaignURL = "dialer/campaigns/delete";
                postData = "campaignID=" + campaign_id
               
                AjaxCall(deleteCampaignURL, postData, "post", "json").done(function (response) {
                    if (response.status) {

                        $("#divErrorMsg").hide();
                        $('.hiddendiv').show();
						window.location="campaigns/index";
                        /*if (newGridName == '#list') {
                            var url = '/dialer/campaigns/getCampaignsList';
						}
                        if (newGridName == '#gird2') {
                            var url = '/dialer/campaigns/getCampaignsListCompleted';
						}

                        $(newGridName).jqGrid('setGridParam', {url: url, datatype: 'json'}).trigger('reloadGrid');
                        window.location.hash = newGridName;
                        window.location.reload(true);*/
                        //return [true, "", ''];
                    }
                    else {
                        ShowAlertMessage(response.message);
                        $(":checkbox:checked").prop('checked', false);
                        $(newGridName).jqGrid('resetSelection');
                        return false;
                    }
                    
                    
                });
            }
            , function () {
                $(":checkbox:checked").prop('checked', false);
                $(newGridName).jqGrid('resetSelection');
                return false;
            },
            'Remove Campaign'
        );
    });
}

function callGrid(griddata, theGrid, GridName, PagerName) {
    //initpophover();
    var max_height_content = $(".ui-jqgrid-bdiv").height();
    $('#dvGqgrid').css('max-height', max_height_content);
    $('#dvGqgrid').css('height', max_height_content);

	var oldGrid;

    var LogInUserType = '';
    if (logged_user_type != undefined) {
        var LogInUserType = logged_user_type;
    }

    var hiddenColumn = true;
    var hiddenMemberAccessColumns = true; // Only Member Can see CPL Column
    var hiddenAgentAccessColumns = false; // Only Agent Can not see
    var showOnlyAgentAccessColumn = true;
    var hiddenTMOfficeColumn = false;
    // var hiddenTeamLeaderAccessColumns = false; // Only Team Leader Can not see
    var IsSelect = true;
    if (LogInUserType == 'admin') {
        hiddenMemberAccessColumns = false;
    }
    if (LogInUserType == 'manager') {
        hiddenMemberAccessColumns = false;
        hiddenTMOfficeColumn = true;
    }
    if (LogInUserType == 'agent') {
        hiddenAgentAccessColumns = true;
        showOnlyAgentAccessColumn = false;
        hiddenColumn = false;
        IsSelect = false;
        hiddenTMOfficeColumn = true;
    }
    if (LogInUserType == 'qa') {
        IsSelect = false;
        hiddenColumn = false;
        hiddenTMOfficeColumn = true;
    }
    if (LogInUserType == 'team_leader') {
       showOnlyAgentAccessColumn = false;
        hiddenTMOfficeColumn = true;
       IsSelect = false;
       hiddenColumn = false;
    }

    var campaignTypeList = $.parseJSON(campaign_type_list);
    var eGCampaignList = $.parseJSON(tm_offices);

    var campaignType = campaignTypeList;
    if (GridName == '#list') {
        if (LogInUserType == 'admin' || LogInUserType == 'manager') {
	var statusval = ":All;pending:Pending;active:Active;paused:Paused";
        }else{
            var statusval = ":All;pending:Pending;active:Active";
        }
    } else {
        var statusval = "completed:Completed";
    }
    //var campaignType = ":All;HQL:HQL;Blended:Blended;Telemarketing:Telemarketing";
//    var theGrid = jQuery("#list");

     var reload = true;
    theGrid.jqGrid({
        /*url: url,
        datatype: "json",*/
		 datatype: 'local',
          data: griddata,
        mtype: "post",
        cache: false,
        colNames: ["", "ID", "Contacts", "Campaign Name","Telemarketing Offices", "Type", "Start", "End", "Ordered", "TM Leads Today", "QA Approved", "Rejected", "CPL/CPA", "Report", "Status"],
        colModel: [
            {width: '10px', search: false, hidden: true, name: 'id', index: 'campaign_id'},
            {name: "eg_campaign_id", index: 'eg_campaign_id', width: '80px'},
            {
                name: "AgentSignInOut", index: 'AgentSignInOut', width: '100px', search: false,
                formatter: function (cellvalue, options, rowObject) {
                    var newURL = '';
                    if (rowObject.AgentSignInOut == 'Sign Out') {
                        newURL = '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/' + rowObject.campaign_id + '/' + rowObject.AgentSignInOutValue + '">' + rowObject.AgentSignInOut + '</a> / <a class="define_ellipsis_text" href="lists/index/' + rowObject.campaign_id + '">Back to call list</a>';
                    } else {
                        newURL = '<a class="define_ellipsis_text" href="/dialer/contacts/campaign_sign_in_out/' + rowObject.campaign_id + '/' + rowObject.AgentSignInOutValue + '">' + rowObject.AgentSignInOut + '</a>';
                    }
                    return newURL;
                },
                hidden: showOnlyAgentAccessColumn,
                editable: showOnlyAgentAccessColumn,
                editrules: {edithidden: showOnlyAgentAccessColumn}
            },
            {
                name: "name", index: 'name', fixed: true, width: '300px',
                formatter: function (cellvalue, options, rowObject) {
                    return '<a class="define_ellipsis_text" href="/dialer/campaigns/view/' + rowObject.campaign_id + '">' + rowObject.name + '</a>';
                }
            },
            {
                name: "telemarketing_offices",
                index: 'telemarketing_offices',
                width: '100px',
                stype: 'select',
                edittype: 'select',
                editoptions: {value: eGCampaignList},
                searchoptions: {value: eGCampaignList, defaultValue: ""},
                hidden: hiddenTMOfficeColumn,
                editable: hiddenTMOfficeColumn,
                editrules: {edithidden: hiddenTMOfficeColumn}
            },
            {
                name: "type",
                index: 'type',
                width: '100px',
                formatter: 'select',
                stype: 'select',
                edittype: 'select',
                editoptions: {value: campaignType},
                searchoptions: {sopt: ['eq'], value: campaignType},
                hidden: hiddenMemberAccessColumns,
                editable: hiddenMemberAccessColumns,
                editrules: {edithidden: hiddenMemberAccessColumns}
            },
            {
                name: "start_date",
                index: 'start_date',
                width: '90px',
                formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                formatoptions: {srcformat: 'Y-m-d', newformat: 'm/d/Y'},
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns}
            },
            {
                name: "end_date", index: 'end_date', width: '90px', formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                formatoptions: {srcformat: 'Y-m-d', newformat: 'm/d/Y'}
            },
            {
                name: "lead_goal",
                index: 'lead_goal',
                width: '87px',
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns}
            },
            {
                name: "total_Leads",
                index: 'total_Leads',
                width: '80px',
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns}
              
            },
            {
                name: "aprroved_leads",
                index: 'aprroved_leads',
                width: '80px',
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns}
            },
            {
                name: "rejected_leads",
                index: 'rejected_leads',
                width: '80px',
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns}
            },
            {
                name: "cpl",
                index: 'cpl',
                width: '80px',
                hidden: hiddenMemberAccessColumns,
                editable: hiddenMemberAccessColumns,
                editrules: {edithidden: hiddenMemberAccessColumns}
            },
            {
                name: "report",
                index: 'report',
                width: '80px',
                search: false,
                hidden: hiddenAgentAccessColumns,
                editable: hiddenAgentAccessColumns,
                editrules: {edithidden: hiddenAgentAccessColumns},
                formatter: function (cellvalue, options, rowObject) {
                    return '<a href="/dialer/reports/qualified_leads/' + rowObject.id + '">' + 'Report' + '</a>';
                }
            },
            {
                name: "status",
                index: 'status',
                width: '80px',
                stype: 'select',
                edittype: 'select',
                editoptions: {value: statusval},
                searchoptions: {value: statusval, defaultValue: ""}

            }
        ],
		 localReader: {repeatitems: true},
        sortname: 'id',
        sortorder: 'desc',
        pager: "#pager",
        edit: true,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: 10,
        rownumbers: false,
        height: 'auto',
        width: '100%',
        loadonce: true,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: IsSelect,
        multiselectWidth: 30,
        multipleSearch: true,
        viewrecords: true,

//        onInitGrid: get_filters,
        emptyrecords: "No records available.",
		
        gridComplete: function () {
            setSearchDate(theGrid, "start_date", 'm/d/yy');
            setSearchDate(theGrid, "end_date", 'm/d/yy');
            theGrid.jqGrid('filterToolbar', {
                beforeSearch: function () {
					fnBeforeSearch(theGrid);  
                    if (oldGrid != "") {
                        $(GridName + ' tbody').html(oldGrid);
			}
                }, stringResult: true, searchOnEnter: false, defaultSearch: "cn"
            });
        },
        loadComplete: function () {

            applyCustomPaging(theGrid);
            var myGrid = $(GridName);
            if (myGrid.getGridParam('reccount') === 0) {
                oldGrid = $(GridName + ' tbody').html();
                $(GridName + ' tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                // $('#dvGqgrid tr.ui-search-toolbar').hide();
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');

                $('#jqgh_list_cb').hide();
                $('#jqgh_gird2_cb').hide();
                $('#gird2_cb').hide();
                $('#gsh_gird2_cb').hide();
                $('#edit_c').hide();
                $('#del_c').hide();
            } else {
                $('#jqgh_list_cb').show();
                $('#jqgh_gird2_cb').show();
                $('#gird2_cb').show();
                $('#gsh_gird2_cb').show();
                $('#edit_c').show();
                $('#del_c').show();
                // $('#dvGqgrid tr.ui-search-toolbar').show();
            }

            var max_height_content = $(".ui-jqgrid-bdiv").height();
            $('#dvGqgrid').css('max-height', max_height_content);
            $('#dvGqgrid').css('height', max_height_content);

            $('.content-main-area #dvGqgrid a').each(function () {
                var href = $(this).attr('href');
                $(this).attr('href', 'javascript:void(0);');
                $(this).attr('jshref', href);
                $(this).addClass('jhrefclick');
                $('.clearsearchclass').removeAttr('href');
                $('.clearsearchclass').removeAttr('jshref');
                $('.clearsearchclass').removeClass('jhrefclick');
            });

                $('.content-main-area #dvGqgrid a.jhrefclick').bind('click', function (e) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    e.stopPropagation();
                    var href = $(this).attr('jshref');
                    if (!e.metaKey && e.ctrlKey)
                        e.metaKey = e.ctrlKey;
                    if (!e.metaKey && href) {
                        location.href = href;
                    }
                    return false;
                });

            $('.define_ellipsis_text').parent().addClass('default_ellipsis_text');
        }
    });
}

function initpophover() {
    //bind tooltip of filter panel: copy query button hover
    listSettings = {
        content: '<p>Copy</p>',
        title: '',
        style: 'inverse',
        padding: false,
        width: 115,
        height: 10,
        trigger: 'hover',
        placement: 'bottom-left',
        delay: {show: 0, hide: 200}
    };
    $('.fa-copy').webuiPopover('destroy').webuiPopover(listSettings);
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
    //bind tooltip of filter panel: delete button hover
    listSettings = {
        content: '<p>Delete</p>',
        title: '',
        style: 'inverse',
        padding: false,
        width: 115,
        height: 10,
        trigger: 'hover',
        placement: 'bottom-left',
        delay: {show: 0, hide: 200}
    };
    $('.fa-trash-o').webuiPopover('destroy').webuiPopover(listSettings);
    
}
$("#globalSearchText").keyup(function (e) {        
    themainGrid = $("#list");
    thecompleteGrid = $("#gird2");
    mainGrid = jQuery('#list').jqGrid('getGridParam', 'reccount');
    completeGrid = jQuery('#gird2').jqGrid('getGridParam', 'reccount');
    if (mainGrid != 0) {
      keywordSearch(themainGrid, $("#globalSearchText").val());
        }
    if (typeof(completeGrid) != "undefined") {
      keywordSearch(thecompleteGrid, $("#globalSearchText").val());
    }
});

function displayGridToolbar(tabType) {
    if (tabType == 'main') {
        $('#list').css('display', 'block');
        $('#gbox_list').css('display', 'block');
        $('#gview_gird2').css('display', 'block');
        $('#gbox_gird2').css('display', 'none');
    } else if (tabType == 'completed') {
        $('#list').css('display', 'none');
        $('#gbox_list').css('display', 'none');
        $('#gview_gird2').css('display', 'block');
        $('#gbox_gird2').css('display', 'block');
    }
}
