$(function () {
    var oldGrid;

    //initpophover();
    var categoriesStr = ":All;agent:Agent;qa:QA;team_leader:Team Leader;manager:Manager";
    var statusStr = ":All;Active:Active;InActive:InActive;Released:Released;Resigned:Resigned";
   var theGrid = jQuery("#list");
    theGrid.jqGrid({
        url: '/dialer/calls/get_inbound_calls',
        datatype: "json",
        mtype: "post",
        colNames: ["PureB2B No","contact_id","campaign_id","Caller ID", "Timestamp","Record link","duration"],
        colModel: [
            {name: "channel", index: 'channel', width: '150px',classes: 'break-word word-wrap'},
            {name:"contact_id",index:"contact_id",hidden:true,width:"0px",search:false },
            {name:"campaign_id",index:"campaign_id",hidden:true,width:"0px", search:false },
            {name: "target", index: 'target', width: '200px' ,classes: 'break-word word-wrap',formatter: function (cellvalue, options, rowObject) {
                if( rowObject.contact_id == "" || rowObject.contact_id == null ){
                    return rowObject.target;
                }else{
                    return '<a href="/dialer/contacts/edit/'+ rowObject.contact_id +'/'+ rowObject.campaign_id+'"> '+rowObject.target+' </a>';
                }
            }},
            {name: "created_at", index: 'created_at', width: '200px' ,classes: 'break-word word-wrap',formatter: 'date',
                sorttype: 'date', datefmt: 'Y-m-d H:i:s', formatoptions: {srcformat: 'Y-m-d H:i:s', newformat: 'm/d/Y H:i:s'}},
            {name: "recording_url", index: 'recording_url', width: '200px',sortable:false,search:false ,classes: 'break-word word-wrap',formatter: function (cellvalue, options, rowObject) {
                if( rowObject.recording_url == "" || rowObject.recording_url == null ){
                    return '-';
                }else{
                    return '<a target="_blank" href="'+ rowObject.recording_url +'"> Record Link </a>';
                }
            }
                    
            },
            {name:"duration",index:"duration",width: '150px',classes: 'break-word word-wrap'},
        ],
        //sortname: 'id',
        //sortorder: 'desc',
        pager: "#pager",
        edit: false,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: 50,
        rownumbers: false,
        height: 'auto',
        width: 'auto',
        loadonce: true,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: false,
        multiselectWidth: 50,
        multipleSearch: true,
        viewrecords: true,
        emptyrecords: "No records available.",
        //viewrecords: true,
        gridComplete: function () {
            setSearchDate(theGrid, "created_at", 'm/d/yy');
           // setSearchDate(theGrid, "created_at", 'yy-mm-dd');
            theGrid.jqGrid('filterToolbar', {stringResult: true, searchOnEnter: false, defaultSearch: "cn"});
        },
        loadComplete: function () {
            applyCustomPaging(theGrid);
            if ($('#list').getGridParam('records') === 0) {
                oldGrid = $('#list tbody').html();
                $('#list tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');
                
            }
            else{
                oldGrid = "";
                
            } 
        }
    });
    
    $("#qa_item").addClass("active open");
    $("#inbound_calls").addClass("active");   
});



