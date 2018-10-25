// initialise the grid view listing
$(function () {
    initpophover();
    var griddata = contactsdata;
    var theGrid = jQuery("#list");
    var newGrid;
    var newGridName = "#list";
    newPager = 'list';
    callGrid(griddata, theGrid, newGridName, newPager);
    EditData(newGridName);
    NumericTextOnlyAllowed('gs_phone');
   
//   if(logged_user_type == 'agent'){
//    document.onmousedown=disableclick;
//        status="Right Click Disabled";
//        function disableclick(e)
//        {
//          if(event.button==2)
//           {
//             alert(status);
//             return false;  
//           }
//        }
//    }
});


//populate search filters
function populateFilterRules( filterRules ){
    
// If we've got any existing filters, we want to populate the appropriate fields with the filter/search data
    if(filterRules['full_name'] !== undefined) {
        $('#gs_full_name').val(filterRules['full_name']);
    }
    if(filterRules['phone'] !== undefined) {
        $('#gs_phone').val(filterRules['phone']);
    }
    if(filterRules['company'] !== undefined) {
        $('#gs_company').val(filterRules['company']);
    }
    if(filterRules['agent_name'] !== undefined) {
        $('#gs_agent_name').val(filterRules['agent_name']);
    }
    if(filterRules['status'] !== undefined) {
        $('#gs_status').val(filterRules['status']);
    }
    if(filterRules['call_disposition_id'] !== undefined) {   
        $('#gs_call_disposition_id').val(filterRules['call_disposition_id']);
    }
    if(filterRules['callback_date'] !== undefined) {
        $('#gs_callback_date').val(filterRules['callback_date']);
    }
    if(filterRules['time_zone'] !== undefined) {
        $('#gs_time_zone').val(filterRules['time_zone']);
    }
}
// Edit contact for specific contact pass id
function EditData(newGridName) {

    $(".fa-edit").click(function () {
        var theCheckboxes = $("input[type='checkbox']").not('#cb_list,#cb_gird2');
        if (theCheckboxes.filter(":checked").length < 1) {
            ShowAlertMessage("Please select at least one contact for edit.");
            return false;
        }
        else if (theCheckboxes.filter(":checked").length > 1) {
            ShowAlertMessage(" Please select only one contact at a time for editing.");
            return false;
        }
        else {
             var checkedIds = $(":checkbox:checked").map(function () {
                var call_list_id = this.id;
                if (newGridName == '#list')
                    call_list_id = call_list_id.replace('jqg_list_', '');
                if (newGridName == '#gird2')
                    call_list_id = call_list_id.replace('jqg_gird2_', '');

                //window.location = "/dialer/contacts/edit/" + call_list_id +"/"+campaign_id+ "/" + listid;
                //window.location = "/dialer/calls/create/"+campaign_id+ "/" + listid + "/?cid=" + call_list_id;
            });
        }
    });
}

// common function for workable and non-workable grid view
function callGrid(griddata, theGrid, GridName, PagerName) {
    //initpophover();
    //alert(JSON.stringify(griddata.rows[0].cell));
    var max_height_content = $(".ui-jqgrid-bdiv").height();
    $('#dvGqgrid').css('max-height', max_height_content);
    $('#dvGqgrid').css('height', max_height_content);
    var i = 0;
    var oldGrid;
    
        var categoriesStr = $.parseJSON(jsonDispo);
	categoriesStr[''] = ""; 
        var callback_display = true;
   
    
    var statusStr = $.parseJSON(leadStatusDataJsonData);

   
        new_url = '/dialer/contacts/getContacts/'+campaign_id+'/1/0/0/'+listid;
        row_num = 20;
   

    theGrid.jqGrid({
        datatype: 'local',
        data: griddata.rows,
        mtype: "post",
        cache: false,
        colNames: ["","","","Full Name","Phone Number","Company Name","Agent","Last QA Result","Call Disposition","CallBack Date","Time Zone","Notes",""],
        colModel: [
            {name:"id",index:"id",hidden:true,search: false,width:"10px"},
            {name:"contact_list_id", index:'contact_list_id',hidden:true,width:"10px"},
            {name:"edit_lead_status", index:'edit_lead_status',width: '20px',search:false,cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}, formatter: SuppressionIdFormatter},
            {name: "full_name", index: 'full_name', width: '180px',formatter: function (cellvalue, options, rowObject) {
                return '<a class="btn btn-success" href="/dialer/calls/index/'+ rowObject.contact_list_id +'/'+rowObject.list_id +'">'+ rowObject.full_name +'</a>';},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "phone", classes:"break-word word-wrap",index: 'phone', width: '150px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name: "company", index: 'company', width: '250px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name:"agent_name", index:"agent_name",width:"100px",cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name:"status", index:"status",width:"100px" ,formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value: statusStr},
                searchoptions: {sopt: ['eq'], value: statusStr},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },           
            {name:"call_disposition_id",index:'call_disposition_id',width:'172px',formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value:categoriesStr},
                searchoptions: {sopt: ['eq'], value:categoriesStr},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name:"callback_date",index:'callback_date',width:'140px',  formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                search:false,
                hidden:callback_display,
                formatoptions: {srcformat: 'ISO8601Long', newformat: 'm/d/Y H:i A'},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "time_zone", index: 'time_zone', width: '70px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name: "note", index: 'note',title: false,search:false, width: '180px',
                formatter: function (cellvalue, options, rowObject) {
                   if (rowObject.note == undefined || rowObject.note == 'NULL') {
                        rowObject.note = '';
                     } else {
                        rowObject.note = rowObject.note;
                    }
                    var separateArray = [];
                    var test = [];
                    var myStr = rowObject.note;
                    var maxLength = 10;
                    var totalWords = $.trim(myStr).split(' ').length;
                    if(totalWords > maxLength){
                        newStr =  myStr.split(/\s+/).slice(0,5).join(" ");
                        var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                    }					
                    if(removedStr != undefined && (removedStr != '' || removedStr != null)){
                        separateArray.push('<p class="show-pop btn btn-danger btn-lg" data-animation="pop" data-content="<p class=\'jq_grid_assign_list\'>' + myStr + '</p>" data-placement="top-left" data-target="webuiPopover257">' + newStr + '</p>' ) ;
                        i++;
                    }else{
                        separateArray.push('<p class="assign_list_tooltip" data-placement="top-left" title="' + myStr + '">' + myStr + '</p>') ;
                    }
                    return separateArray;
                },
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "locked_by", index: 'locked_by', hidden: true }
        ],
        localReader: {repeatitems: true},
        sortname: "cl.updated_at ASC,FIELD (cl.filter_status,'1') DESC ,tlh.status,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST') , ISNULL(cd.calldisposition_name), cd.calldisposition_name ,FIELD(cl.source,'api','call_file')",
        sortorder: 'asc',
        pager: "#pager",
        edit: true,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: row_num,
        total:griddata.records,
        totalpages:griddata.total,
        newurl:new_url,
        rownumbers: false,
        height: 'auto',
        width: 'auto',
        loadonce: false,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: true,
        multiselectWidth: 30,
        multipleSearch: true,
        emptyrecords: "No records available.",
        viewrecords: true,
        gridComplete: function () {
            setSearchDate(theGrid, "callback_date", 'm/d/yy');
            theGrid.jqGrid('filterToolbar', {beforeSearch: function() { 
				fnBeforeSearch(theGrid);  
                if(oldGrid!=""){$(GridName + ' tbody').html(oldGrid);}
            },stringResult: true, searchOnEnter: false, defaultSearch: "cn"});
            $('[aria-describedby="list_full_name"]').each(function() {
                if($(this).attr('class') == 'api') {
                    $(this).siblings(":first").addClass('api');
                }
            });
        },
        loadComplete: function () {
            var myGrid = $(GridName);
            //applyCustomPaging(theGrid,"total");

            $(GridName+" tr.jqgrow td:nth(3)").css('width','19px');

            // get data as per selected grid view tab
            var new_url_non_ajax = '';
            //if(GridName != undefined && GridName == '#list'){
                new_url_non_ajax = '/dialer/contacts/getContacts/'+campaign_id+'/1/0/0/'+listid;
            
			applyCustomPaging(theGrid,"total",new_url_non_ajax);
            theGrid.jqGrid('setGridParam', { url:new_url_non_ajax,datatype: 'json',mtype: "post"}); // ,datatype: 'json',mtype: "post"
            var filter_data = theGrid.getGridParam("postData").filters;
            if(filter_data != undefined && GridName == '#list'){
                var new_filete_data = {};
                var fjson = $.parseJSON(filter_data);
                $(fjson).each(function(i,val){
                    $.each(val,function(k,v){
                        if(k = 'rules'){
                            $(v).each(function(i,va){
                                new_filete_data[va.field] = va.data;
                            });
                        }
                    });
                });
                filterRules = new_filete_data;
            }
            var settings1 = {
            trigger:'hover',
            title:'Notes',
            style: 'inverse',
            padding: false,
            width: '300',
            height: '75',
            placement: 'top-left',
            animation:'pop',
            delay: {show: 0, hide: 200}
            };
            $('p.show-pop').webuiPopover('destroy').webuiPopover(settings1);

            var totalrecord = myGrid.getGridParam('reccount');
            if (totalrecord === 0) {
                oldGrid =  $(GridName + ' tbody').html();
                $(GridName +' tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                //$('#dvGqgrid tr.ui-search-toolbar').hide();
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');
               
                $('#jqgh_list_cb').hide();
                $('#jqgh_gird2_cb').hide();
                $('#gird2_cb').hide();
                $('#gsh_gird2_cb').hide();
                $('#edit_c').hide();
                $('#del_c').hide();
            }
            else{
                $('#jqgh_list_cb').show();
                $('#jqgh_gird2_cb').show();
                $('#gird2_cb').show();
                $('#gsh_gird2_cb').show();
                $('#edit_c').show();
                $('#del_c').show();
            } 

            var max_height_content = $(".ui-jqgrid-bdiv").height();
            $('#dvGqgrid').css('max-height', max_height_content);
            $('#dvGqgrid').css('height', max_height_content);
            $("#pager").after("<div class='clearfix'></div>");
            $("#pager").show();
        }
    });
}
function callGridnon(griddata, theGrid, GridName, PagerName) {
    //initpophover();
    //alert(JSON.stringify(griddata.rows[0].cell));
    var max_height_content = $(".ui-jqgrid-bdiv").height();
    $('#dvGqgrid').css('max-height', max_height_content);
    $('#dvGqgrid').css('height', max_height_content);
    var i = 0;
    var oldGrid;

    if(GridName != undefined && GridName == '#list'){
        var categoriesStr = $.parseJSON(jsonWorkableDispo);
	categoriesStr[''] = ""; 
    }else if(GridName != undefined && GridName == '#gird2'){
        var categoriesStr = $.parseJSON(jsonNonWorkableDispo);
	categoriesStr[''] = ""; 
    } 

    var statusStr = $.parseJSON(leadStatusDataJsonData);

    
   
	new_url = '/dialer/contacts/get_non_workable_contacts/'+campaign_id+'/1/0/'+listid;
	row_num = 15;
    

    theGrid.jqGrid({
        datatype: 'json',
        url: new_url,
        mtype: "post",
        cache: false,
        colNames: ["","","","Full Name","Phone Number","Company Name","Agent","Last QA Result","Call Disposition","CallBack Date","Time Zone","Notes",""],
        colModel: [
            {name:"id",index:"id",hidden:true,search: false,width:"10px"},
            {name:"contact_list_id", index:'contact_list_id',hidden:true,width:"10px"},
            {name:"edit_lead_status", index:'edit_lead_status',width: '20px',search:false,cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}, formatter: SuppressionIdFormatter},
            {name: "full_name", index: 'full_name', width: '180px',formatter: function (cellvalue, options, rowObject) {
                return '<a class="btn btn-success" href="/dialer/calls/index/' +'/'+ rowObject.contact_list_id +'/'+listid +'">'+ rowObject.full_name +'</a>';},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "phone", classes:"break-word word-wrap",index: 'phone', width: '150px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name: "company", index: 'company', width: '250px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name:"agent_name", index:"agent_name",width:"100px",cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name:"status", index:"status",width:"100px" ,formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value: statusStr},
                searchoptions: {sopt: ['eq'], value: statusStr},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },           
            {name:"call_disposition_id",index:'call_disposition_id',width:'172px',formatter: 'select',
                stype: 'select', edittype: 'select', editoptions: {value:categoriesStr},
                searchoptions: {sopt: ['eq'], value:categoriesStr},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name:"callback_date",index:'callback_date',width:'140px',  formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                search:false,
                formatoptions: {srcformat: 'ISO8601Long', newformat: 'm/d/Y H:i A'},
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "time_zone", index: 'time_zone', width: '70px',cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}},
            {name: "note", index: 'note',title: false,search:false, width: '180px',
                formatter: function (cellvalue, options, rowObject) {
                   if (rowObject.note == undefined || rowObject.note == 'NULL') {
                        rowObject.note = '';
                     } else {
                        rowObject.note = rowObject.note;
                    }
                    var separateArray = [];
                    var test = [];
                    var myStr = rowObject.note;
                    var maxLength = 10;
                    var totalWords = $.trim(myStr).split(' ').length;
                    if(totalWords > maxLength){
                        newStr =  myStr.split(/\s+/).slice(0,5).join(" ");
                        var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                    }					
                    if(removedStr != undefined && (removedStr != '' || removedStr != null)){
                        separateArray.push('<p class="show-pop btn btn-danger btn-lg" data-animation="pop" data-content="<p class=\'jq_grid_assign_list\'>' + myStr + '</p>" data-placement="top-left" data-target="webuiPopover257">' + newStr + '</p>' ) ;
                        i++;
                    }else{
                        separateArray.push('<p class="assign_list_tooltip" data-placement="top-left" title="' + myStr + '">' + myStr + '</p>') ;
                    }
                    return separateArray;
                },
                cellattr: function(rowId, val, rawObject, cm, rdata) { return (rawObject.source == 'api') ? ' class="api"' : '';}
            },
            {name: "locked_by", index: 'locked_by', hidden: true }
        ],
        sortname: "cl.updated_at ASC,FIELD (cl.filter_status,'1') DESC ,tlh.status,FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST') , ISNULL(cd.calldisposition_name), cd.calldisposition_name ,FIELD(cl.source,'api','call_file')",
        sortorder: 'asc',
        pager: "#pager",
        edit: true,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: row_num,
        rownumbers: false,
        height: 'auto',
        width: 'auto',
        loadonce: false,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: true,
        multiselectWidth: 30,
        multipleSearch: true,
        emptyrecords: "No records available.",
        viewrecords: true,
        gridComplete: function () {
            setSearchDate(theGrid, "callback_date", 'm/d/yy');
            theGrid.jqGrid('filterToolbar', {beforeSearch: function() { 
			fnBeforeSearch(theGrid); 
                if(oldGrid!=""){$(GridName + ' tbody').html(oldGrid);}
            },stringResult: true, searchOnEnter: false, defaultSearch: "cn"});
            $('[aria-describedby="list_full_name"]').each(function() {
                if($(this).attr('class') == 'api') {
                    $(this).siblings(":first").addClass('api');
                }
            });
        },
        loadComplete: function () {
            var myGrid = $(GridName);
            applyCustomPaging(theGrid);

            $(GridName+" tr.jqgrow td:nth(3)").css('width','19px');

            // get data as per selected grid view tab
           

           // theGrid.jqGrid('setGridParam', { url:new_url_non_ajax,datatype: 'json',mtype: "post"}); // ,datatype: 'json',mtype: "post"

            var settings1 = {
            trigger:'hover',
            title:'Notes',
            style: 'inverse',
            padding: false,
            width: '300',
            height: '75',
            placement: 'top-left',
            animation:'pop',
            delay: {show: 0, hide: 200}
            };
            $('p.show-pop').webuiPopover('destroy').webuiPopover(settings1);

            var totalrecord = myGrid.getGridParam('reccount');
            if (totalrecord === 0) {
                oldGrid =  $(GridName + ' tbody').html();
                $(GridName +' tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                //$('#dvGqgrid tr.ui-search-toolbar').hide();
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');
               
                $('#jqgh_list_cb').hide();
                $('#jqgh_gird2_cb').hide();
                $('#gird2_cb').hide();
                $('#gsh_gird2_cb').hide();
                $('#edit_c').hide();
                $('#del_c').hide();
            }
            else{
                $('#jqgh_list_cb').show();
                $('#jqgh_gird2_cb').show();
                $('#gird2_cb').show();
                $('#gsh_gird2_cb').show();
                $('#edit_c').show();
                $('#del_c').show();
            } 

            var max_height_content = $(".ui-jqgrid-bdiv").height();
            $('#dvGqgrid').css('max-height', max_height_content);
            $('#dvGqgrid').css('height', max_height_content);
            $("#pager").after("<div class='clearfix'></div>");
            $("#pager").show();
        }
    });
}
// hide another grid as per selected grid view tab
function displayGridToolbar(tabType) {
    if (tabType == 'workable') {
        $('#list').css('display', 'block');
        $('#gbox_list').css('display', 'block');
        $('#gview_gird2').css('display', 'none');
        $('#gview_gird3').css('display', 'none');
        $('#gbox_gird3').css('display', 'none');
        $('#gbox_gird2').css('display', 'none');
    } else if (tabType == 'non_workable') {
        $('#list').css('display', 'none');
        $('#gbox_list').css('display', 'none');
        $('#gview_gird3').css('display', 'none');
        $('#gbox_gird3').css('display', 'none');
        $('#gview_gird2').css('display', 'block');
        $('#gbox_gird2').css('display', 'block');
    } else if (tabType == 'callback') {
        $('#list').css('display', 'none');
        $('#gbox_list').css('display', 'none');
        $('#gview_gird2').css('display', 'none');
        $('#gbox_gird2').css('display', 'none');
        $('#gview_gird3').css('display', 'block');
        $('#gbox_gird3').css('display', 'block');
    }
}

//open detail dialog while click on read more or any other dialog box
var dialog = $("#dialog-form").dialog({
    autoOpen: false,
    height: 'auto',
    width: 650,
    modal: true,
    resizable: false,
    dialogClass: 'popup-title'
});

//display Add,Edit,contact list icon
function initpophover() {
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
    
    //bind tooltip of filter panel: Contact List button hover
    listSettings = {
        content: '<p>Contact List</p>',
        title: '',
        style: 'inverse',
        padding: false,
        width: 115,
        height: 10,
        trigger: 'hover',
        placement: 'bottom-left',
        delay: {show: 0, hide: 200}
    };
    $('.fa-list-alt').webuiPopover('destroy').webuiPopover(listSettings);
    
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
}

  function unlockLead(CampaignContactID) {
      var postData = "campaignContactId="+CampaignContactID;
        $.ajax({
            type: 'POST',
            data: postData,
            url: '/dialer/calls/update_multiple_lock_contact/1',
            async: false,
            dataType: 'json',
            success: function (response) {
                if(response.campaign_contact_id != undefined && response.campaign_contact_id !=''){
                    window.location = "/dialer/calls/index/"+response.campaign_contact_id+"/"+response.list_id+"";
                }else if(response.error != undefined && response.error !=''){
                    ShowAlertMessage(response.error);
                }else{
                    $('.contactLocked-'+CampaignContactID).hide();             
                }
            },
            error: function (x, e) {
                if (x.status == 401) {
                    window.location.href = '/Login';
                }
            }
        });
    }

    //display Lock-unlock icon
    function SuppressionIdFormatter(cellvalue, options, rowObject) {
        if (cellvalue == '1') {
            if (((logged_user_type == 'manager' || rowObject.locked_by == logged_user_id) && rowObject.user_type != 'admin') || logged_user_type == 'admin') {
                return '<a href="javascript:void(0)" onclick="unlockLead(' + rowObject.contact_list_id + ');" class="desclinkcss"><span class="fa fa-lock locked-by-user-icon contactLocked-'+rowObject.contact_list_id+'" title="Locked (Click to unlock)"></span></a>';
            }
            else {
                return '<span class="fa fa-lock locked-by-user-icon" title="Locked"></span>';
            }
        }else{
            return '&nbsp;';
        }
    }

/*$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});*/

// Active current page on left navigation slide bar
$("#campaign_item").addClass("active open");
$("#list_view").addClass("active");

// large notes open as a popup animation notes on hover that text
setInterval(function () {
    $('.jq_grid_assign_list').parent().addClass('jq_grid_assign_list_content');
    $('.jq_grid_assign_list').parent().parent().addClass('jq_grid_assign_list_title');
}, 100);
$(document).ready(function() {
    // If we've got any existing filters, we want to populate the appropriate fields with the filter/search data
    if(filterRules['full_name'] !== undefined) {
        $('#gs_full_name').val(filterRules['full_name']);
    }
    if(filterRules['phone'] !== undefined) {
        $('#gs_phone').val(filterRules['phone']);
    }
    if(filterRules['company'] !== undefined) {
        $('#gs_company').val(filterRules['company']);
    }
    if(filterRules['agent_name'] !== undefined) {
        $('#gs_agent_name').val(filterRules['agent_name']);
    }
    if(filterRules['status'] !== undefined) {
        $('#gs_status').val(filterRules['status']);
    }
    if(filterRules['call_disposition_id'] !== undefined) {
        $('#gs_call_disposition_id').val(filterRules['call_disposition_id']);
    }
    if(filterRules['callback_date'] !== undefined) {
        $('#gs_callback_date').val(filterRules['callback_date']);
    }
    if(filterRules['time_zone'] !== undefined) {
        $('#gs_time_zone').val(filterRules['time_zone']);
    }
});
