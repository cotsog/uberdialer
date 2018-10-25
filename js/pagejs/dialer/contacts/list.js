$(function () {
    var i = 0;
    var oldGrid;
    $(".fa-times-circle").css('cursor', 'pointer');
    $(".divErrorMsg").css('cursor', 'pointer');
    var rowNum = 50;
    $('.myPager').hide();
    var theGrid = jQuery("#list");

    theGrid.jqGrid({
				 
	datatype: 'local',
        data: contactsdata.rows,
        colNames: ["","Full Name","Phone Number","email","Company Name","Date of Last Activity","Time Zone","Link"],
        colModel: [
            {name:"sid",index:"sid",width:5, key:true , search:false,sortable:false},                           
            {name: "full_name", index: 'full_name', width: '180px'},
            {name: "phone", classes:"break-word word-wrap",index: 'phone', width: '100px'},
            {name: "email", index: 'email', width: '200px'},
            {name: "company", index: 'company', width: '250px'},
            {name: "updated_at", index: 'updated_at', width: '200px' ,classes: 'break-word word-wrap',formatter: 'date',
                sorttype: 'date', datefmt: 'Y-m-d H:i:s', formatoptions: {srcformat: 'Y-m-d H:i:s', newformat: 'm/d/Y H:i:s'}},
            {name: "time_zone", index: 'time_zone', width:'70px'},
            {name:"contact_list_id", index:'contact_list_id',width:"60px",formatter: function (cellvalue, options, rowObject) {
                return '<a class="btn btn-success" href="/dialer/calls/index/'+ rowObject.contact_list_id +'/'+ rowObject.list_id +'/qa?action=view">View</a>'; }},
        ],
        
        localReader: {repeatitems: true},
        sortname: "FIELD (c.priority,'1','2','3','4','5','6','7','8',''),FIELD (c.time_zone,'EST','CST','MST','PST'),FIELD(cl.source,'api','file') ASC,c.id",
        sortorder: 'asc',
        pager: "#pager",
        edit: true,
        pgtext: "{0}",
        pagerpos: 'left',
        rowNum: rowNum,
        total:contactsdata.records,
	    totalpages:contactsdata.total,
	    newurl:'/dialer/contacts/getContacts/'+campaign_id+'/1/0/1/'+listid, // getContacts($campaign id ,$ajax,$contactFilter ,$isContactListPage)
        rownumbers: false,
        height: 'auto',
        width: 'auto',
        loadonce: false,
        gridview: true,
        autowidth: true,
        shrinkToFit: true,
        multiselect: false,
        multiselectWidth: 30,
        multipleSearch: true,
        emptyrecords: "No records available.",
        viewrecords: true,
        gridComplete: function () {
            theGrid.jqGrid('filterToolbar', {beforeSearch: function() { 
			fnBeforeSearch(theGrid);  
                if(oldGrid!=""){$('#list tbody').html(oldGrid);}
            },stringResult: true, searchOnEnter: false, defaultSearch: "cn"});
            if( $("#list").getGridParam('datatype') == 'local'){
                (contactsdata.records + "").replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
                $('.ui-paging-info').html('View 1 - ' + rowNum + ' of ' + contactsdata.records);
            }


            //$('.myPager').hide(); 
        },
        loadComplete: function () {
            totalrecord=$('#list').getGridParam('records');
            if(theGrid.jqGrid('getGridParam', 'datatype')=="json")
            {
                applyCustomPaging(theGrid);
                totalrecord=$('#list').getGridParam('records');
            }
            else
            {
                applyCustomPaging(theGrid,"total","/dialer/contacts/getContacts/"+campaign_id+'/1/0/1/'+listid);
                totalrecord=$('#list').getGridParam('total');
            }
            theGrid.jqGrid('setGridParam', { url:'/dialer/contacts/getContacts/'+campaign_id+'/1/0/1/'+listid,datatype: 'json',mtype: "post" });
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

            if (totalrecord === 0) {
                oldGrid = $('#list tbody').html();
                $('#list tbody').html("<div style='padding:6px;font-size: 14px; background:#D8D8D8'>No record(s) found</div>");
                $('.ui-jqgrid-sortable span').remove();
                $(".ui-jqgrid-htable tr div").removeClass('ui-jqgrid-sortable');
               
            }
            else{
                oldGrid = "";
            } 
            $("#pager").after("<div class='clearfix'></div>");
            //$("#pager").hide();
        }
    });

});

$(".fa-times-circle").click(function () {
    $("#divErrorMsg").hide();
});
$("#divErrorMsg").click(function () {
    $("#divErrorMsg").hide();
});

$("#campaign_item").addClass("active open");
$("#list_view").addClass("active");
setInterval(function () {
    $('.jq_grid_assign_list').parent().addClass('jq_grid_assign_list_content');
    $('.jq_grid_assign_list').parent().parent().addClass('jq_grid_assign_list_title');
}, 100);


   