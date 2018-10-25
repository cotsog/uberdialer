/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



$(function () {
    var oldGrid;
    $(".fa-times-circle").css('cursor', 'pointer');
    $(".divErrorMsg").css('cursor', 'pointer');
    initpophover();
    var theGrid = jQuery("#list");
    theGrid.jqGrid({
        url: '/dialer/emailtemplates/get_templates',
        datatype: "json",
        mtype: "post",
        colNames: ["EmailID", "Campaign Name", "Campaign Status","Resource", "Created Date","View/Edit","Clone"],
        colModel: [
//            {name:"",index:"",search:false, width:"10px" },
            {name: "id", index: 'id', width: '58px',classes: 'break-word word-wrap'},
            {name: "name", index: 'name', width: '200px' ,classes: 'break-word word-wrap'},
            {name: "status", index: 'status', width: '70px'},
            {name: "resource_name", index: 'resource_name', width: '350px' ,classes: 'break-word word-wrap'},
            {
                name: "created_at", index: 'created_at', width: '80px', formatter: 'date',
                sorttype: 'date',
                datefmt: 'Y-m-d',
                formatoptions: {srcformat: 'Y-m-d', newformat: 'm/d/Y'}
            },{name: "", index: '',width: '60px', search:false,
                formatter: function (cellvalue, options, rowObject) {
                    var newURL = '';
                    newURL = '<a class="define_ellipsis_text" href="/dialer/emailtemplates/view/' + rowObject.id + '">View</a>/<a class="define_ellipsis_text" href="/dialer/emailtemplates/edit/' + rowObject.id + '">Edit</a>';
                    return newURL;
                }
            },
            {name: "",index: '', width: '50px',search:false, 
                formatter: function (cellvalue, options, rowObject) {
                //return '<a class="btn btn-success" href="/emailtemplates/clone/'+ rowObject.id +'>Clone</a>';}
                 var newURL = '';
                    newURL = '<a class="define_ellipsis_text" href="/dialer/emailtemplates/templateclone/' + rowObject.id + '">Clone</a>';
                    return newURL;
                }    
            }
        ],
        sortname: 'created_at',
        sortorder: 'desc',
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
            theGrid.jqGrid('filterToolbar', {beforeSearch: function() { 
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
            }
            else{
                oldGrid = "";
                $('#jqgh_list_cb').show();
            } 
        }
    });
    $(".list-trash-font").click(function () {
        var theCheckboxes = $("input[type='checkbox']");
        if (theCheckboxes.filter(":checked").length < 1) {
            ShowAlertMessage("Please select at least one template for delete.");
            return false;
        }
         var idArray = $('input:checkbox:checked').map(function () {
            return this.id.replace('jqg_list_', '');
        });

        ShowConfirm('Do you want to delete this template(s) ?', function () {
            selected_temp_id = ($.makeArray(idArray).join(','));
            temp_id = selected_temp_id.replace('jqg_list_', '');
            postData = "templateID="+temp_id
            var deleteURL = "dialer/emailtemplates/delete/";
            AjaxCall(deleteURL,postData, "post", "json").done(function (response) {
                if (response.status) {
                    $("#divErrorMsg").hide();
                    $('.hiddendiv').show();
                    $("#list").jqGrid('setGridParam', {datatype: 'json'}).trigger('reloadGrid');
                    return [true, "", ''];
                }
                else {
                    ShowAlertMessage(response.message);
                    $(":checkbox:checked").prop('checked', false);
                    $("#list").jqGrid('resetSelection');
                    return false;
                }
                });
            }
            , function () {
                $(":checkbox:checked").prop('checked',false);
                $("#list").jqGrid('resetSelection');

                return false;
            },
            'Remove Template'
        );
    });
    
    
    $("#globalSearchText").keyup(function (e) {
        if(oldGrid!=""){
            $("#list tbody").html(oldGrid);
        }
        keywordSearch(theGrid, $("#globalSearchText").val());
        
    });
    $("#template_lists").addClass("active open");
    $("#templates").addClass("active");
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
$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});
