//jqgrid show selected records
function showSelectedRecords(theGrid, columnname) {
    alert('SelectedRecords');
    //theGrid[0].clearToolbar();
    var postData = theGrid.jqGrid("getGridParam", "postData"),
        colModel = theGrid.jqGrid("getGridParam", "colModel"),
        rules = [],
        searchText = "Yes";
    rules.push({
        field: columnname,
        op: "eq",
        data: searchText
    });
    postData.filters = JSON.stringify({
        groupOp: "OR",
        rules: rules
    });

    alert(postData.filters);
    theGrid.jqGrid("setGridParam", { search: true });
    theGrid.trigger("reloadGrid", [{ page: 1, current: true }]);

    return false;
}

//keyword search
function keywordSearch(theGrid, value) {
    if (value === '') {
        $('.fa-search').show();
        $('a.clear-keyword-search').hide();
    }
    else {
        $('.fa-search').hide();
        $('a.clear-keyword-search').show();
    }
    var postData = theGrid.jqGrid("getGridParam", "postData"),
        colModel = theGrid.jqGrid("getGridParam", "colModel"),
        rules = [],
        searchText = value,
        l = colModel.length,
        separator = ' ',
        searchTextParts = $.trim(searchText).split(separator),
        cnParts = searchTextParts.length,
        i,
        iPart,
        cm;
    for (i = 0; i < l; i++) {
        cm = colModel[i];
        if (cm.search !== false && (cm.stype === undefined || cm.stype === "text" || cm.stype === "select")) {
            for (iPart = 0; iPart < cnParts; iPart++) {
                rules.push({
                    field: cm.name,
                    op: "cn",
                    data: searchTextParts[iPart]
                });
            }
        }
    }
    postData.filters = JSON.stringify({
        groupOp: "OR",
        rules: rules
    });
    theGrid.jqGrid('resetSelection');
    theGrid.jqGrid("setGridParam", { search: true });
    theGrid.trigger("reloadGrid", [{ page: 1, current: true }]);
    return false;
}

//inline search: type - select, date
var getUniqueNames = function (theGrid, columnName) {
    var idToDataIndex = theGrid.jqGrid('getGridParam', '_index');
    var allRowData = theGrid.jqGrid('getGridParam', 'data');
    var uniqueTexts = [], textsLength = allRowData.length, text, textsMap = {};
    if (textsLength > 0) {
        for (id in idToDataIndex) {
            if (idToDataIndex.hasOwnProperty(id)) {
                data = allRowData[idToDataIndex[id]];
                text = data[columnName];
                if (text !== undefined && text !== "" && textsMap[text] === undefined) {
                    textsMap[text] = true;
                    uniqueTexts.push(text);
                }
            }
        }
    }
    return uniqueTexts;
},
buildSearchSelect = function (uniqueNames) {
    var values = ":All";
    $.each(uniqueNames, function () {
        values += ";" + this + ":" + this;
    });
    return values;
};

//inline search: search type - select
var setSearchSelect = function (theGrid, columnName) {
    theGrid.jqGrid('setColProp', columnName,
    {
        stype: 'select',
        searchoptions: {
            value: buildSearchSelect(getUniqueNames(theGrid, columnName)),
            sopt: ['eq'],
            clearSearch: false
        }
    }
    );
};

//inline search: search type - date
var setSearchDate = function (theGrid, columnName, dateformat) {
    theGrid.jqGrid('setColProp', columnName,
    {
        searchoptions: {
            sopt: ['cn'],
            dataInit: function (elem) {
                $(elem).datepicker({
                    dateFormat: dateformat,
                    //changeYear: true,
                    //changeMonth: true,
                    //showButtonPanel: true,
                    onSelect: function () {
                        if ($(this).val() === '') {
                            $(this).parent().next('td').hide();
                            $(this).parent().addClass('custom-search');
                        }
                        else {
                            $(this).parent().next('td').find('a').css('padding', '0');
                            $(this).parent().next('td').show();
                            $(this).parent().removeClass('custom-search');
                        }
                        $(this).keydown();
                    }
                });
            }
        }
    }
    );
},
selectedRow = null,
oldFrom = $.jgrid.from, lastSelected, i, n, $ids, id;
$.jgrid.from = function (source, initalQuery) {
    var result = oldFrom.call(this, source, initalQuery),
            old_select = result.select;
    result.select = function (f) {
        lastSelected = old_select.call(this, f);
        return lastSelected;
    };
    return result;
};



//AlphabeticalSearch search
function alphabeticalSearch(theGrid, value) {
	
    var postData = theGrid.jqGrid("getGridParam", "postData"),
        colModel = theGrid.jqGrid("getGridParam", "colModel"),
        rules = [],
        searchText = value,
        l = colModel.length,
        separator = ' ',
        searchTextParts = $.trim(searchText).split(separator),
        cnParts = searchTextParts.length,
        i,
        iPart,
        cm;
    for (i = 0; i < l; i++) {
        cm = colModel[i];
        if (cm.search !== false && (cm.stype === undefined || cm.stype === "text")) {
            for (iPart = 0; iPart < cnParts; iPart++) {
                rules.push({
                    field: cm.name,
                    op: "bw",
                    data: searchTextParts[iPart]
                });
            }
        }
    }
    postData.filters = JSON.stringify({
        groupOp: "OR",
        rules: rules
    });


    theGrid.jqGrid("setGridParam", { search: true });
    theGrid.trigger("reloadGrid", [{ page: 1, current: true }]);

    return false;
}

//apply custom paging to the listing pages
function applyCustomPaging(theGrid,rowcountval="records",pageurl="") {
	
    var MAX_PAGERS = theGrid.getGridParam("rowNum");
    var rowCount = theGrid.jqGrid('getGridParam', rowcountval);
	if(rowCount!=theGrid[0].p.records)
	{
			$('.ui-paging-info').html("View 1 - "+MAX_PAGERS+" of "+rowCount);
	}
	if(pageurl!="")
	{
			var totalpages = theGrid.getGridParam("totalpages");
	  		var ajaxurl = pageurl;
	}
	else
	{
		var totalpages = theGrid[0].p.lastpage;
		var ajaxurl = "#";
	}
    if (rowCount > MAX_PAGERS) {
        var i, myPageRefresh = function (e) {
            var newPage = $(e.target).text();
			if(pageurl!="")
			{
				 var newUrl = e.target.href;
			 	theGrid.jqGrid("clearGridData");
			 	theGrid.jqGrid('setGridParam', {url:newUrl,page: newPage,datatype: 'json',mtype: "post"}).trigger('reloadGrid'); 
			}
			else
            	theGrid.trigger("reloadGrid", [{ page: newPage }]);
            e.preventDefault();
        };

        $(theGrid[0].p.pager + '_left td.myPager').remove();
        var pagerPrevTD = $('<td>', { class: "myPager" }), prevPagesIncluded = 0,
            pagerNextTD = $('<td>', { class: "myPager" }), nextPagesIncluded = 0,
            totalStyle = theGrid[0].p.pginput === false,
            startIndex = totalStyle ? theGrid[0].p.page - MAX_PAGERS * 2 : theGrid[0].p.page - MAX_PAGERS;
        for (i = startIndex; i <= totalpages && (totalStyle ? (prevPagesIncluded + nextPagesIncluded < MAX_PAGERS * 2) : (nextPagesIncluded < MAX_PAGERS)) ; i++) {
            if (i <= 0 || i === theGrid[0].p.page) { continue; }

            var link = $('<a>', { href: ajaxurl, click: myPageRefresh });
            link.text(String(i));
            if (i < theGrid[0].p.page || totalStyle) {
                if (prevPagesIncluded > 0 && prevPagesIncluded < 1 ) { pagerPrevTD.append('<span>&nbsp;</span>'); }
				if (prevPagesIncluded < 1){pagerPrevTD.append(link);}
                prevPagesIncluded++;
            } else {
                if ((nextPagesIncluded > 0 && nextPagesIncluded < 1 ) || (totalStyle && prevPagesIncluded > 0)) { pagerNextTD.append('<span>&nbsp;</span>'); }
                if(nextPagesIncluded < 1) {pagerNextTD.append(link);}
                nextPagesIncluded++;
            }
        }
        if (prevPagesIncluded > 0) {
            $(theGrid[0].p.pager + '_left td[id^="prev"]').after(pagerPrevTD);
        }
        if (nextPagesIncluded > 0) {
            $(theGrid[0].p.pager + '_left td[id^="next"]').before(pagerNextTD);
        }
        $('.jqgrid-footer').show();
		$('#pg_pager').show();
    }
    else {
        $('.jqgrid-footer').hide();
		$('#pg_pager').hide();
    }

}


function applyCustomPaging_dialog(theGrid) {
    var MAX_PAGERS = theGrid.getGridParam("rowNum");
    var rowCount = theGrid.jqGrid('getGridParam', 'records');
    if (rowCount > MAX_PAGERS) {
        var i, myPageRefresh = function (e) {
            var newPage = $(e.target).text();
            theGrid.trigger("reloadGrid", [{ page: newPage }]);
            e.preventDefault();
        };

        $(theGrid[0].p.pager + '_left td.myPager').remove();
        var pagerPrevTD = $('<td>', { class: "myPager" }), prevPagesIncluded = 0,
            pagerNextTD = $('<td>', { class: "myPager" }), nextPagesIncluded = 0,
            totalStyle = theGrid[0].p.pginput === false,
            startIndex = totalStyle ? theGrid[0].p.page - MAX_PAGERS * 2 : theGrid[0].p.page - MAX_PAGERS;
        for (i = startIndex; i <= theGrid[0].p.lastpage && (totalStyle ? (prevPagesIncluded + nextPagesIncluded < MAX_PAGERS * 2) : (nextPagesIncluded < MAX_PAGERS)) ; i++) {
            if (i <= 0 || i === theGrid[0].p.page) { continue; }

            var link = $('<a>', { href: '#', click: myPageRefresh });
            link.text(String(i));
            if (i < theGrid[0].p.page || totalStyle) {
                if (prevPagesIncluded > 0) { pagerPrevTD.append('<span>&nbsp;</span>'); }
                pagerPrevTD.append(link);
                prevPagesIncluded++;
            } else {
                if (nextPagesIncluded > 0 || (totalStyle && prevPagesIncluded > 0)) { pagerNextTD.append('<span>&nbsp;</span>'); }
                pagerNextTD.append(link);
                nextPagesIncluded++;
            }
        }
        if (prevPagesIncluded > 0) {
            $(theGrid[0].p.pager + '_left td[id^="prev"]').after(pagerPrevTD);
        }
        if (nextPagesIncluded > 0) {
            $(theGrid[0].p.pager + '_left td[id^="next"]').before(pagerNextTD);
        }
        $('.jqgrid-footer td:first').show();
    }
    else {
        $('.jqgrid-footer td:first').hide();
    }

}

$(function () {
    //keyup event of inline serach input
    $('body').on('keyup', '.ui-search-input input', function () {
        if ($(this).val() === '') {
            $(this).parent().next('td').hide();
            $(this).parent().addClass('custom-search');
        }
        else {
            $(this).parent().next('td').find('a').css('padding', '0');
            $(this).parent().next('td').show();
            $(this).parent().removeClass('custom-search');
        }
    });
    //click event of clear search button
    $('body').on('click', '.clearsearchclass', function () {
        $(this).parent().hide();
        $(this).closest('tr').find('.ui-search-input').addClass('custom-search');
    });

    //This is important function for jqgrid width resizing
    //Mutation Observer function to check the any mutation with window
    (function ($) {
        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

        $.fn.attrchange = function (callback) {
            if (MutationObserver) {
                var options = {
                    subtree: false,
                    attributes: true
                };

                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (e) {
                        callback.call(e.target, e.attributeName);
                    });
                });

                return this.each(function () {
                    observer.observe(this, options);
                });

            }
        }
    })(jQuery);

    //event listener for aside css change
    $('aside').attrchange(function (attrName) {
        if (attrName == 'class') {
            $("#list").setGridWidth($("#dvGqgrid").width());
            $("#tblJQGrid").setGridWidth($("#dvGqgrid").width());
            $("#listStdFields").setGridWidth($("#tab").width());
            $("#list").setGridWidth($("#tab").width());
        }
    });
    //function for adjust width of jqgrid on window resizing
    $(window).resize(setwidth);
    //setwidth function for jqgrid 
    function setwidth() {
        $("#list").setGridWidth($("#dvGqgrid").width());
        $("#listStdFields").setGridWidth($("#tab").width());
    }

})

