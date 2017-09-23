var newUsersChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_new_gamers}, {name : lan_sa_retention, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', lan_sa_new_gamers, dataJson.newUsers);
	EgretChart.setData('line', lan_sa_retention_one, dataJson["1RatentionRate"], false, 1);
	EgretChart.setData('line', lan_sa_retention_three, dataJson["3RatentionRate"], false, 1);
	EgretChart.setData('line', lan_sa_retention_seven, dataJson["7RatentionRate"], false, 1);
	EgretChart.show('newUsersChart');
};

var hourDataChart = function() {
	EgretChart.init();
	EgretChart.setData('line', lan_sa_average_new, dataJson.newUsersHour);

	EgretChart.show('hourDataChart');
};

var hourDataChartAll = function() {
	EgretChart.init();
	for(m in dataJson.newUsersHourAll) {
		EgretChart.setData('line', m, dataJson.newUsersHourAll[m]);
	}

	EgretChart.show('hourDataChart');
};

var firstLevelChart = function() {
	if (dataJson.oneLevel == undefined) return true;
	EgretChart.init('', '', {"x2":0});
	EgretChart.setData('bar', lan_sa_first_day_grade_distribution, dataJson.oneLevel);

	var chart = EgretChart.show('firstLevelChart');
	
	var itemStyle = {
		normal: {
		  	label : {
			show : false,
			formatter : "{d}%",
			position:'inner'
			},
			labelLine : {
				show : false
			}
		}
	};
	
	EgretPieChart.init('', '', true);
	EgretPieChart.setData(dataJson.oneLevel, '70%', ['50%'], itemStyle);
	var chart1 = EgretPieChart.show('firstLevelChartPie');
	
	chart.connect(chart1);
	chart1.connect(chart);
};


var chanDataChart = function() {

	var itemStyle = {
		normal: {
		  	label : {
				show : false,
				formatter : "{d}%",
				position:'inner'
			},
			labelLine : {
				show : false
			}
		}
	};

	if (chanDataJson.chanDataTotal.length == 0) return true;
	EgretChart.init('', '', {"x2":20});
	for (m in chanDataJson.chanData) {
		EgretChart.setData('line', m, chanDataJson.chanData[m]);
	}

	var chart = EgretChart.show('chanDataChart');

	var itemStyle = {
		normal: {
		  	label : {
				show : false,
			},
			labelLine : {
				show : false
			}
		}
	};
	EgretPieChart.init('', '', true);
	EgretPieChart.setData(chanDataJson.chanDataTotal, '55%', ['50%']);
	var chart1 = EgretPieChart.show('chanDataChartPie');

	chart.connect(chart1);
	chart1.connect(chart);

};

$("#hourDataType").change(function(){
	showHourData();
});

var showHourData = function() {
	var value = $("#hourDataType").val();
	if (value == "day") {
		hourDataChartAll();
	} else {
		hourDataChart();
	}
};

$("#generalTabs a").click(function(event){
    $("#generalTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#generalTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    if (value != 'ltv') {
    	if (value == "hourData") {
    		$("#hourDataDiv").show();
    		var fun = "showHourData";
    	} else {
    		$("#hourDataDiv").hide();
    		var fun = value + "Chart";
    	}
	    doCallback(eval(fun));
    } else {
    	ltvPage();
    }
    event.preventDefault();
});

// ltv 分页
var ltvPage = function () {
	var num_entries = $("#ltvTab tr[name='rows']").length;
    $("#Pagination").pagination(num_entries, {
        num_edge_entries: 1, //边缘页数
        num_display_entries: 4, //主体页数
        prev_text: lan_pre_page,
        next_text: lan_next_page,
        callback: pageselectCallback,
        items_per_page: 10 //每页显示10项
    });

    function pageselectCallback(page_index, jq){
	 	$("#dataContent").empty();
    	var items_per_page = 10;
		var max_elem = Math.min((page_index+1) * items_per_page, num_entries);
		if ($("#dataContent thead").length < 1) {
			$("#dataContent").append($("#ltvTab thead").clone());
		}

		if ($("#dataContent tbody").length < 1) {
			$("#dataContent").append("<tbody></tbody>");
		} else {
			$("#dataContent tbody").empty();
		}

		for(var i=page_index*items_per_page;i<max_elem;i++){
			var tr = $("#ltvTab tr[name='rows']:eq("+i+")").clone();
			$("#dataContent tbody").append(tr);
		}
        return false;
    }
}

$("#levelTabs a").click(function(event){
    $("#levelTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#levelTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    if (value != 'keyLevel') {
	    var fun = value + "Chart";
	    doCallback(eval(fun));
    }
    event.preventDefault();
});


$("a[href='#chartTable']").click(function() {
	var data = $(this).attr("data");
	var type = $(this).attr("type");

	var labels = $("#" + data + "Tab a");
	labels.each(function() {
		var label = $(this).attr("data")
		var a = $(this).attr("type")
		$("#" + label + a).hide();
		$(this).removeClass("hover");
	});
	$(this).addClass("hover");
	$("#" + data + type).show();

	var tds = titles = labels = [];
	if (type == 'Table') {
		if (data == 'newUsers') {
			titles = ['日期', '新增玩家', '1日留存'];
			labels = ['newUsers', '1RatentionRate'];
			tds = [1];
		} else if(data == 'payUsers') {
			titles = ['日期', '新增玩家', '1日留存'];
			labels = ['newUsers', '1RatentionRate'];
			tds = [1];
		}

		showTable(data, titles, labels, dataJson, dateKeys);
		var dataContentTable = data + "ContentTable";
		var dataContent = data + "Content";
		var Pagination = data + "Pagination";
		tablePage(dataContentTable, dataContent, Pagination, tds);
	} else {
		var fun = data + "Chart";
	    doCallback(eval(fun));
	}
});
