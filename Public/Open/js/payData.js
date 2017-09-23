var usersArppuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : 'ARPPU'}, {name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', 'ARPPU', dataJson.usersArppu);
	EgretChart.setData('line', lan_sa_pay_rate, dataJson.usersPayRate, false, 1);
	EgretChart.show('usersArppuChart');
};

var usersArpuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_active}, {name : 'ARPU'}]);
	EgretChart.setData('bar', lan_sa_active, dataJson.activeUsers);
	EgretChart.setData('line', 'ARPU', dataJson.usersArpu, false, 1);
	EgretChart.show('usersArpuChart');
};

var usersPayRateChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_active}, {name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', lan_sa_active, dataJson.activeUsers);
	EgretChart.setData('line', lan_sa_pay_rate, dataJson.usersPayRate, false, 1);
	EgretChart.show('usersPayRateChart');
};


var payUsersChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_pay_gamers}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_sa_old_pay_gamers, dataJson.oldPayUsers, true);
	EgretChart.setData('bar', lan_sa_new_pay_gamers, dataJson.newPayUsers, true);
	EgretChart.setData('line', lan_sa_active, dataJson.activeUsers, false, 1);
	EgretChart.show('payUsersChart');
};


var payTimesChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_recharge_times}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_sa_recharge_times, dataJson.payTimes, true);
	EgretChart.setData('line', lan_sa_active, dataJson.activeUsers, false, 1);
	EgretChart.show('payTimesChart');
};

var usersIncomeChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_active}, {name : lan_da_income}]);
	
	EgretChart.setData('bar', lan_sa_active, dataJson.activeUsers);
	EgretChart.setData('line', lan_da_income, dataJson.usersIncome, false, 1);
	EgretChart.show('usersIncomeChart');
};

var usersIncomeHourChart = function() {
	EgretChart.init();
	EgretChart.setData('line', lan_da_income, dataJson.usersIncomeHour);

	EgretChart.show('usersIncomeHourChart');
};

var payUsersHourChart = function() {
	EgretChart.init();
	EgretChart.setData('line', lan_sa_pay_peoples, dataJson.payUsersHour);

	EgretChart.show('payUsersHourChart');
};

var payTimesHourChart = function() {
	EgretChart.init();
	EgretChart.setData('line', lan_sa_pay_times, dataJson.payTimesHour);

	EgretChart.show('payTimesHourChart');
};

var payTimesHourChartAll = function() {
	EgretChart.init();
	for(m in dataJson.payTimesHourAll) {
		EgretChart.setData('line', m, dataJson.payTimesHourAll[m]);
	}

	EgretChart.show('payTimesHourChart');
};

var payUsersHourChartAll = function() {
	EgretChart.init();
	for(m in dataJson.payUsersHourAll) {
		EgretChart.setData('line', m, dataJson.payUsersHourAll[m]);
	}

	EgretChart.show('payUsersHourChart');
};

var usersIncomeHourChartAll = function() {
	EgretChart.init();
	for(m in dataJson.usersIncomeHourAll) {
		EgretChart.setData('line', m, dataJson.usersIncomeHourAll[m]);
	}

	EgretChart.show('usersIncomeHourChart');
};

$("#hourDataType").change(function(){
	showHourData();
});

var showHourData = function() {
	var value = $("#hourDataType").val();
	var chartType = $("#hourDataChart").val();
	if (value == "day") {
		var fun = chartType + "All";
	} else {
		var fun = chartType;
	}
	console.log(fun);
	doCallback(eval(fun));
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
}

var levelNumChart = function() {
	if (dataJson.levelNum.length == 0) return true;
	EgretChart.init('');
	EgretChart.setYAxis([{name : '', axisLabel:{formatter: lan_currency_sign + ' {value}'}}]);
	EgretChart.setData('line', lan_sa_view_income_by_grade, dataJson.levelNum);
	EgretChart.show('levelNumChart');
};

var levelTimesChart = function() {
	if (dataJson.levelNum.length == 0) return true;
	EgretChart.init('');
	EgretChart.setYAxis([{name : '', axisLabel:{formatter: '{value} ' + lan_times}}]);
	EgretChart.setData('line', lan_sa_view_pay_peoples_by_grade, dataJson.levelTimes);
	EgretChart.show('levelTimesChart');
};

$("#payLevelTabs a").click(function(event){
    $("#payLevelTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#payLevelTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    var fun = value + "Chart";
    doCallback(eval(fun));
    event.preventDefault();
});

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
    var fun = value + "Chart";
    doCallback(eval(fun));
    event.preventDefault();
});

$("#hourPayTabs a").click(function(event){
    $("#hourPayTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#hourPayTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    var args = value + "Chart";
    $("#hourDataChart").val(args);
    var fun = "showHourData";
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
		if(data == 'usersIncome') {
			titles = [lan_date, lan_da_income, lan_sa_active_gamers];
			labels = ['usersIncome', 'activeUsers'];
			tds = [1, 2];
		} else if(data == 'usersPayRate') {
			titles = [lan_date, lan_sa_pay_rate, lan_sa_active_gamers];
			labels = ['usersPayRate', 'activeUsers'];
			tds = [2];
		} else if(data == 'usersArpu') {
			titles = [lan_date, 'ARPU', lan_sa_active_gamers];
			labels = ['usersArpu', 'activeUsers'];
			tds = [2];
		} else if(data == 'usersArppu') {
			titles = [lan_date, 'ARPPU', lan_sa_active_gamers];
			labels = ['usersArppu', 'activeUsers'];
			tds = [2];
		} else if(data == 'payUsers') {
			titles = [lan_date, lan_sa_new_pay_gamers, lan_sa_old_pay_gamers, lan_sa_pay_gamers, lan_sa_active_gamers];
			labels = ['newPayUsers', 'oldPayUsers', 'payUsers', 'activeUsers'];
			tds = [1, 2, 3, 4];
		} else if(data == 'payTimes') {
			titles = [lan_date, lan_sa_recharge_times, lan_sa_active_gamers];
			labels = ['payTimes', 'activeUsers'];
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