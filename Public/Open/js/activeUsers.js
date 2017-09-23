var activeUsersChart = function() {
	var itemStyle = {normal:{areaStyle:{type:'default'}}};
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_old_gamers_and_new_gamers}, {name : lan_sa_active_gamers}]);
	EgretChart.setData('bar', lan_sa_old_gamers, dataJson.oldUsers, true, '', itemStyle);
	EgretChart.setData('bar', lan_sa_new_gamers, dataJson.newUsers, true, '', itemStyle);
	EgretChart.setData('line', lan_sa_active_gamers, dataJson.activeUsers, false, 1);
	EgretChart.show('activeUsersChart');
};

var hourDataChart = function() {
	EgretChart.init();
		EgretChart.setData('line', lan_sa_average_active, dataJson.activeUsersHour);

	EgretChart.show('hourDataChart');
};

var hourDataChartAll = function() {
	EgretChart.init();
	for(m in dataJson.activeUsersHourAll) {
		EgretChart.setData('line', m, dataJson.activeUsersHourAll[m]);
	}

	EgretChart.show('hourDataChart');
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

var loginTimesChart = function() {
	if (dataJson.loginTimes.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setData('bar', lan_sa_number_of_people, dataJson.loginTimes);
	EgretChart.show('loginTimesChart');
	
	EgretPieChart.init('', '', true);
	EgretPieChart.setData(dataJson['loginTimes'], '55%', ['50%']);
	EgretPieChart.show('loginTimesPieChart');
};

var allLevelChart = function() {
	if (dataJson.levelAll.length == 0) return true;
	EgretChart.init();
	EgretChart.setData('bar', lan_sa_all_gamers, dataJson.levelAll);

	EgretChart.show('allLevelChart');
};

var payLevelChart = function() {
	if (dataJson.levelPay.length == 0) return true;
	EgretChart.init();
	EgretChart.setData('bar', lan_sa_pay_gamers, dataJson.levelPay);

	EgretChart.show('payLevelChart');
};


var chanDataChart = function() {
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
					formatter : "{d}%",
					position:'inner'
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


// var genderChart = function() {
// 	var itemStyle = {normal:{areaStyle:{type:'default'}}};
// 	EgretChart.init('', '', {"x2":0});
// 	for (m in dataJson.activeUsersGender) {
// 		EgretChart.setData('bar', m, dataJson.activeUsersGender[m], true, '', itemStyle);
// 	}
//
// 	var chart = EgretChart.show('genderChart');
//
// 	var itemStyle = {
// 			normal: {
// 			  	label : {
// 				show : false,
// 				formatter : "{d}%",
// 				position:'inner'
// 				},
// 				labelLine : {
// 					show : false
// 				}
// 			}
// 		};
// 		EgretPieChart.init('', '', true);
// 		EgretPieChart.setData(dataJson.activeUsersGenderAll, '70%', ['50%'], itemStyle);
// 		var chart1 = EgretPieChart.show('genderChartPie');
//
// 		chart.connect(chart1);
// 		chart1.connect(chart);
// };


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

    if (value != 'payPlayer') {
    	if (value == "hourData") {
    		$("#hourDataDiv").show();
    		var fun = "showHourData";
    	} else {
    		$("#hourDataDiv").hide();
    		var fun = value + "Chart";
    	}
	    doCallback(eval(fun));
    } else {
        $("#hourDataDiv").hide();
    	$("#payPlayerTab").tablesorter();
    	tablePage("payPlayerTab", "dataContent", "Pagination", [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    }
    event.preventDefault();
});

$("#levelTabs a").click(function(event){
    $("#levelTabsTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#levelTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();

    var fun = value + "Chart";
    doCallback(eval(fun));
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
		if (data == 'activeUsers') {
			titles = [lan_date, lan_sa_active_gamers, lan_sa_old_gamers, lan_sa_new_gamers];
			labels = ['activeUsers', 'oldUsers', 'newUsers'];
			tds = [1, 2, 3];
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
