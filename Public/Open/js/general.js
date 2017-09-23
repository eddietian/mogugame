var activeUsersChart = function() {
	var itemStyle = {normal:{areaStyle:{type:'default'}}};
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_old_gamers_and_new_gamers}, {name : lan_da_income}]);
	EgretChart.setData('bar', lan_sa_old_gamers, generalDataJson.oldUsers, true, '', itemStyle);
	EgretChart.setData('bar', lan_sa_new_gamers, generalDataJson.newUsers, true, '', itemStyle);
	/*EgretChart.setData('line', lan_sa_active_gamers, generalDataJson.activeUsers, false, 1);*/
	EgretChart.setData('line', lan_da_income, generalDataJson.usersIncome, false, 1);
	EgretChart.show('activeUsersChart');
};

var newUsersChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_new_gamers}, {name : lan_sa_retention, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', lan_sa_new_gamers, generalDataJson.newUsers);
	EgretChart.setData('line', lan_sa_retention_one, generalDataJson["1RatentionRate"], false, 1);
	EgretChart.setData('line', lan_sa_retention_three, generalDataJson["3RatentionRate"], false, 1);
	EgretChart.setData('line', lan_sa_retention_seven, generalDataJson["7RatentionRate"], false, 1);
	EgretChart.show('newUsersChart');
};

var keepPayChart = function() {
	if (generalDataJson.payTimes.length == 0) return true;
	EgretChart.init('', '', {"x2":20});
	EgretChart.setYAxis([{name : '', axisLabel:{formatter: '{value} (' + lan_sa_single_people + ')'}}]);
	EgretChart.setData('bar', lan_sa_number_of_people, generalDataJson.payTimes);
	var chart = EgretChart.show('keepPayChart');
	
	EgretPieChart.init('', '', true);
	EgretPieChart.setData(generalDataJson.payTimes, '55%', ['50%']);
	var chart1 = EgretPieChart.show("keepPayChartPie");
};

var usersIncomeChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_da_income}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_da_income, generalDataJson.usersIncome);
	EgretChart.setData('line', lan_sa_active, generalDataJson.activeUsers, false, 1);
	EgretChart.show('usersIncomeChart');
};

var usersArpuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_active}, {name : 'ARPU'}]);
	EgretChart.setData('bar', lan_sa_active, generalDataJson.activeUsers);
	EgretChart.setData('line', 'ARPU', generalDataJson.usersArpu, false, 1);
	EgretChart.show('usersArpuChart');
};

var usersArppuChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : 'ARPPU'}, {name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', 'ARPPU', generalDataJson.usersArppu);
	EgretChart.setData('line', lan_sa_pay_rate, generalDataJson.usersPayRate, false, 1);
	EgretChart.show('usersArppuChart');
};

var usersPayRateChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_active}, {name : lan_sa_pay_rate, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('bar', lan_sa_active, generalDataJson.activeUsers);
	EgretChart.setData('line', lan_sa_pay_rate, generalDataJson.usersPayRate, false, 1);
	EgretChart.show('usersPayRateChart');
};

var payUsersChart = function() {
	var itemStyle = {normal:{areaStyle:{type:'default'}}};
	EgretChart.init('');
	/*EgretChart.setYAxis([{name : '老付费玩家,新付费玩家'}, {name : '付费玩家'}]);*/
	EgretChart.setYAxis([{name : lan_sa_old_pay_gamers_and_new_pay_gamers}, {name : lan_sa_active}]);
	EgretChart.setData('bar', lan_sa_old_pay_gamers, generalDataJson.oldPayUsers, true, '', itemStyle);
	EgretChart.setData('bar', lan_sa_new_pay_gamers, generalDataJson.newPayUsers, true, '', itemStyle);
	EgretChart.setData('line', lan_sa_active, generalDataJson.activeUsers, false, 1);
	EgretChart.show('payUsersChart');
}

var ratentionRateChart = function() {
	EgretChart.init('');
	EgretChart.setYAxis([{name : lan_sa_retention, axisLabel:{formatter: '{value} %'}}]);
	EgretChart.setData('line', lan_sa_retention_one, generalDataJson["1RatentionRate"]);
	EgretChart.setData('line', lan_sa_retention_three, generalDataJson["3RatentionRate"]);
	EgretChart.setData('line', lan_sa_retention_seven, generalDataJson["7RatentionRate"]);
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
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    
    $("#detailType").val(value);

    $("#channelContrastDiv").hide();
    var dataType = $("#chanDataType").val();
    if (value == "chanData") {
    	showChanData(gameId, dataType, startDate, endDate);
    } else if (value == "channelContrast") {
    	 $("#channelContrastDiv").show();
    	 var label = $("#channelContrastType").val();
    	 showChanContrastData(gameId, label, dataType, startDate, endDate);
    }
    
    if (value == "chanData") {
    	$("#chanDataUnit").show();
    } else {
    	$("#chanDataUnit").hide();
    }
    event.preventDefault();
});


$("#chanDataId").change(function(){
	var dataType = $("#chanDataType").val();
	showChanData(gameId, dataType, startDate, endDate);
});

$("#chanDataType").change(function() {
	var dataType = this.value;
	var detailType = $("#detailType").val();
	if (detailType == "detail") {
		showGameDetail(dataType);
	} else if (detailType == "chanData"){
		showChanData(gameId, dataType, startDate, endDate);
	} else if (detailType == "channelContrast"){
		var label = $("#channelContrastType").val();
		showChanContrastData(gameId, label, dataType, startDate, endDate);
	}
});

var showGameDetail = function(dataType) {
	var dataTypes = ['day', 'week', 'month'];
	for(i in dataTypes) {
		$("#" + dataTypes[i] + "Detail").hide();
	}
	
	$("#" + dataType + "Detail").show();
}

$("#channelContrastType").change(function() {
	var label = this.value;
	var dataType = $("#chanDataType").val();
	showChanContrastData(gameId, label, dataType, startDate, endDate);
});

// 显示渠道数据
var showChanData = function(gameId, dataType, startDate, endDate) {
	var cId = $("#chanDataId").val();
	var url = "/Member/Developer/Game/Data.getChanGeneral?gameId=" + gameId + "&dataType=" + dataType + "&startDate=" + startDate + "&endDate=" + endDate + "&cId=" + cId;
	var sign = getUrlParam('sign');
	if(sign){
		url +="&sign="+sign;
	}
	$.get(url, function(data){
	    $("#chanDataTable").html(data);
	    if (data.indexOf("暂无数据") == -1) {
	    	if (cId == 0) {
		    	var headers = {9:{sorter:false}};
			    $("#chanDataTab").tablesorter({headers:headers, sortList:[[8, 1]]});
	    	
			    tablePage("chanDataTab", "dataContent", "Pagination", [1, 2, 3, 4, 7, 8], true ,{}, "detailChanData", [dataType]);
	    	} else {
	    		detailChanData(dataType);
	    	}
	    }
	});
}

var detailChanData = function(dataType) {
	$("a[href='#detailChanData']").click(function() {
    	var chanId = $(this).attr("data");
    	var chanName = $(this).attr("chanName");
    	var url = "/Member/Developer/Game/Data.getChanGeneralOne?gameId=" + gameId + "&cId=" + chanId + "&dataType=" + dataType + "&startDate=" + startDate + "&endDate=" + endDate;
		var sign = getUrlParam('sign');
		if(sign){
			url +="&sign="+sign;
		}
    	$.get(url, function(data) {
    		var titles = {"day":lan_day, "week":lan_week, "month":lan_month};
    		$('#modalDetail').modal('show');
			var title = chanName + " " + titles[dataType] + lan_data;
			$("#title").html(title);
			$("#modalDetailData").html(data);
			
			$("#chanDataOneTable").tablesorter({headers:{}, sortList:[[0,1]]});
			if (dataType == 'day') {
				tablePage('chanDataOneTable', 'dataContentChanDataOne', 'PaginationChanDataOne', [1, 2 ,3 , 5, 6, 7], true, headers);
			} else {
				tablePage('chanDataOneTable', 'dataContentChanDataOne', 'PaginationChanDataOne', [1,2 ,3 , 4, 5, 6], true, headers);
			}
    	});
    });
};



// 显示渠道对比
var showChanContrastData = function(gameId, label, dataType, startDate, endDate) {
	var url = "/Member/Developer/Game/Data.getChanContrastGeneral?gameId=" + gameId + "&label=" + label + "&startDate=" + startDate + "&endDate=" + endDate + "&dataType=" + dataType;
	var sign = getUrlParam('sign');
	if(sign){
		url +="&sign="+sign;
	}
	$.get(url, function(data){
	    $("#channelContrastTable").html(data);
	    if (data.indexOf(lan_no_data) == -1) {
		    $("#channelContrastTab").tablesorter();
		    tablePage("channelContrastTab", "dataContent1", "Pagination1", [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
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
			titles = [lan_date, lan_sa_new_gamers, lan_sa_old_gamers, lan_da_income];
			labels = ['newUsers', 'oldUsers', 'usersIncome'];
			tds = [1, 2, 3];
		} else if(data == 'newUsers') {
			titles = [lan_date, lan_da_new_gamers, lan_sa_retention_one];
			labels = ['newUsers', '1RatentionRate'];
			tds = [1];
		} else if(data == 'payUsers') {
			titles = [lan_date, lan_sa_active, lan_sa_new_pay_gamers, lan_sa_old_pay_gamers];
			labels = ['activeUsers', 'newPayUsers', 'oldPayUsers'];
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
		
		showTable(data, titles, labels, generalDataJson, dateKeys);
		var dataContentTable = data + "ContentTable";
		var dataContent = data + "Content";
		var Pagination = data + "Pagination";
		tablePage(dataContentTable, dataContent, Pagination, tds);
	} else {
		var fun = data + "Chart";
	    doCallback(eval(fun));
	}
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
});