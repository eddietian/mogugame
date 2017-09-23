var activeUsersChart = function() {
	var itemStyle = {normal:{areaStyle:{type:'default'}}};
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_old_gamers_and_new_gamers}, {name : lan_da_income}]);
	EgretChart.setData('bar', lan_sa_old_gamers, dataJson.oldUsers, true, '', itemStyle);
	EgretChart.setData('bar', lan_sa_new_gamers, dataJson.newUsers, true, '', itemStyle);
	EgretChart.setData('line', lan_da_income, dataJson.usersIncome, false, 1);
	EgretChart.show('activeUsersChart');
};

var newUsersChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_new_gamers}, {name : lan_sa_retention_one_three_seven, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', lan_sa_new_gamers, dataJson.newUsers);
	EgretChart.setData('line', lan_sa_retention_one, dataJson["1RatentionRate"], false, 1);
    EgretChart.setData('line', lan_sa_retention_three, dataJson["3RatentionRate"], false, 1);
    EgretChart.setData('line', lan_sa_retention_seven, dataJson["7RatentionRate"], false, 1);
	EgretChart.show('newUsersChart');
};

var usersIncomeChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_da_income}, {name : lan_sa_active}]);
	EgretChart.setData('line', lan_da_income, dataJson.usersIncome);
	EgretChart.setData('line', lan_sa_active, dataJson.activeUsers, false, 1);
	EgretChart.show('usersIncomeChart');
};

var usersArpuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : 'ARPU'}, {name : lan_sa_active}]);
    EgretChart.setData('bar', lan_sa_active, dataJson.activeUsers, false, 1);
	EgretChart.setData('line', 'ARPU', dataJson.usersArpu);
	EgretChart.show('usersArpuChart');
};

var usersArppuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : 'ARPPU'}, {name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', 'ARPPU', dataJson.usersArppu);
	EgretChart.setData('line', lan_sa_pay_rate, dataJson.usersPayRate, false, 1);
	EgretChart.show('usersArppuChart');
};

var usersPayRateChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_sa_active, dataJson.activeUsers, false, 1);
    EgretChart.setData('line', lan_sa_pay_rate, dataJson.usersPayRate);
	EgretChart.show('usersPayRateChart');
};

var payUsersChart = function() {
	var itemStyle = {normal:{areaStyle:{type:'default'}}};
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_old_pay_gamers_and_new_pay_gamers}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_sa_old_pay_gamers, dataJson.oldPayUsers, true, '', itemStyle);
	EgretChart.setData('bar', lan_sa_new_pay_gamers, dataJson.newPayUsers, true, '', itemStyle);
	EgretChart.setData('line', lan_sa_active, dataJson.activeUsers, false, 1);
	EgretChart.show('payUsersChart');
}

var ratentionRateChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_retention, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('line', lan_sa_retention_one, dataJson["1RatentionRate"]);
	EgretChart.setData('line', lan_sa_retention_three, dataJson["3RatentionRate"]);
	EgretChart.setData('line', lan_sa_retention_seven, dataJson["7RatentionRate"]);
	EgretChart.show('ratentionRateChart');
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
    var fun = value + "Chart";
    doCallback(eval(fun));
    event.preventDefault();
});

$("#detailTabs a").click(function(event){
    $("#detailTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#detailTabs a");

    labels.each(function(){
    	var label = $(this).attr("data");
    	$("#" + label).hide();
    });

    $("#" + value).show();
    $("#detailType").val(value);

    $("#gameContrastDiv").hide();
    var dataType = $("#gameDataType").val();
    if (value == "gameData") {
    	showGameData(chanId, dataType, startDate, endDate);
    } else if (value == "gameContrast") {
    	 $("#gameContrastDiv").show();
    	 var label = $("#gameContrastType").val();
    	 showGameContrastData(chanId, label, dataType, startDate, endDate);
    }

    event.preventDefault();
});

$("#gameDataType").change(function() {
	var detailType = $("#detailType").val();
	var dataType = this.value;
	if (detailType == "detail") {
		showChanDetail(dataType);
	} else if (detailType == "gameData"){
		showGameData(chanId, dataType, startDate, endDate);
	} else if (detailType == "gameContrast"){
		var label = $("#gameContrastType").val();
		showGameContrastData(chanId, label, dataType, startDate, endDate);
	}
});

var showChanDetail = function(dataType) {
	var dataTypes = ['day', 'week', 'month'];
	for(i in dataTypes) {
		$("#" + dataTypes[i] + "Detail").hide();
	}

	$("#" + dataType + "Detail").show();
}

$("#gameContrastType").change(function() {
	var label = this.value;
	var dataType = $("#gameDataType").val();
	showGameContrastData(chanId, label, dataType, startDate, endDate);
});

// 显示游戏数据
var showGameData = function(chanId, dataType, startDate, endDate) {
	var url = "/Member/ChannelOperators/Channel/Stat.getGameGeneral?chanId=" + chanId + "&dataType=" + dataType + "&startDate=" + startDate + "&endDate=" + endDate;
	var sign = getUrlParam('sign');
	if(sign){
		url +="&sign="+sign;
	}
	$.get(url, function(data){
	    $("#gameDataTable").html(data);
	    if (data.indexOf(lan_no_data) == -1) {
	    	var headers = {0:{sorter:false}};
		    $("#gameDataTab").tablesorter({sortList:[[8, 1]], headers:headers});
		    tablePage("gameDataTab", "dataContent", "Pagination", [2, 3, 4, 7, 8], true, headers);
	    }
	});
}

// 显示游戏对比
var showGameContrastData = function(chanId, label, dataType, startDate, endDate) {

	var url = "/Member/ChannelOperators/Channel/Stat.getGameContrastGeneral?chanId=" + chanId + "&label=" + label + "&startDate=" + startDate + "&endDate=" + endDate + "&dataType=" + dataType;
	var sign = getUrlParam('sign');
	if(sign){
		url +="&sign="+sign;
	}
	$.get(url, function(data){
	    $("#gameContrastTable").html(data);
	    if (data.indexOf(lan_no_data) == -1) {
		    $("#gameContrastTab").tablesorter();
		    tablePage("gameContrastTab", "dataContent1", "Pagination1", [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
	    }
	});
}

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
			titles = [lan_date, lan_sa_active_gamers, lan_sa_new_gamers, lan_sa_old_gamers];
			labels = ['activeUsers', 'oldUsers', 'newUsers'];
			tds = [1, 2, 3];
		} else if(data == 'newUsers') {
			titles = [lan_date, lan_da_new_gamers, lan_sa_retention_one];
			labels = ['newUsers', '1RatentionRate'];
			tds = [1];
		} else if(data == 'payUsers') {
			titles = [lan_date, lan_sa_pay_gamers, lan_sa_new_pay_gamers, lan_sa_old_pay_gamers];
			labels = ['payUsers', 'newPayUsers', 'oldPayUsers'];
			tds = [1, 2, 3];
		} else if(data == 'usersIncome') {
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
		} else if(data == 'ratentionRate') {
			titles = [lan_date, lan_sa_retention_one, lan_sa_retention_three, lan_sa_retention_seven];
			labels = ['1RatentionRate', '3RatentionRate', '7RatentionRate'];
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
/* $.datepicker.setDefaults(lan_datepicker_default_format);
$("#startDate").datepicker({
    //dayNamesMin: [ "日","一", "二", "三", "四", "五", "六"],
    //monthNamesShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    //dateFormat: "yy-mm-dd",
    defaultDate:'{$startDate}',
    changeMonth: true,
    maxDate: 0,
    changeYear:true,
    numberOfMonths: 1,
});

$("#endDate").datepicker({
    //dayNamesMin: [ "日","一", "二", "三", "四", "五", "六"],
    //monthNamesShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    //dateFormat: "yy-mm-dd",
    defaultDate:'{$endDate}',
    changeMonth: true,
    maxDate: 0,
    changeYear:true,
    numberOfMonths: 1,
});

$("#endDate,#startDate").change(function(){
	var endDate = $("#endDate").val();
	var startDate = $("#startDate").val();
	var nowDate = getDate();

	var nowD = new Date(nowDate);
	var nowTime = parseInt(nowD.getTime() / 1000);
	
	var eD = new Date(endDate);
	var endTime = parseInt(eD.getTime() / 1000);
	
	var sD = new Date(startDate);
	var startTime = parseInt(sD.getTime() / 1000);
	
	if (endTime > nowTime) {
		endTime = nowTime;
		$("#endDate").val(nowDate);
	}
	var maxDay = 90;
	var num = (endTime - startTime) / 86400;
	if (num > maxDay) {
		startTime = endTime - 86400 * 14;
		startDate = getDate(startTime * 1000);
		$("#startDate").val(startDate);
		alert(lan_message1 + maxDay + lan_message2);
	}
});



$("#startCalendar,#endCalendar").click(function(){
	$(this).prev("input").focus();
}); */