// 消费金额
var diamondBuyPChart = function() {
	if (dataJson.diamondBuyP.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_sa_single_people}}]);
	EgretChart.setData('bar', lan_sa_number_of_people, dataJson.diamondBuyP);
	var chart = EgretChart.show('diamondBuyPChart');
}

// 充值次数
var diamondBuyTimesChart = function() {
	if (dataJson.diamondBuyTimes.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_times}}]);
	EgretChart.setData('bar', lan_sa_times, dataJson.diamondBuyTimes);
	var chart = EgretChart.show('diamondBuyTimesChart');
}

//充值间隔
var paySpaceChart = function() {
	if (dataJson.paySpace.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_times}}]);
	EgretChart.setData('bar', lan_sa_times, dataJson.paySpace);
	var chart = EgretChart.show('paySpaceChart');
}

//首付周期
var firstPaySpaceChart = function() {
	if (dataJson.firstPaySpace.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_sa_single_people}}]);
	EgretChart.setData('bar', lan_sa_number_of_people, dataJson.firstPaySpace);
	EgretChart.show('firstPaySpaceChart');
};

// 首付等级
var firstPayLevelChart = function() {
	if (dataJson.firstPayLevel.length == 0) return true;
	EgretChart.init('', '', '', false);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_sa_single_people}}]);
	EgretChart.setData('bar', lan_sa_number_of_people, dataJson.firstPayLevel);
	EgretChart.show('firstPayLevelChart');
};

// 首付金额
var firstPayNumChart = function() {
	if (dataJson.firstPayNum.length == 0) return true;
	EgretChart.init('');
	EgretChart.setData('bar', lan_sa_first_pay_amount, dataJson.firstPayNum);
	EgretChart.show('firstPayNumChart');
};

// 首付充值包
var firstPayItemChart = function() {
	if (dataJson.firstPayItem.length == 0) return true;
	EgretChart.init('', '', '', true);
	EgretChart.setYAxis([{type : 'value',axisLabel:{formatter: '{value} ' + lan_sa_single_people}}]);
	EgretChart.setData('bar', lan_sa_number_of_people, dataJson.firstPayItem);
	EgretChart.show('firstPayItemChart');
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

$("#firstPayTabs a").click(function(event){
    $("#firstPayTabs a").removeClass("active");
    $(this).addClass("active");
    var value = $(this).attr("data");

    var labels = $("#firstPayTabs a");

    labels.each(function(){
    	var label = $(this).attr("data")
    	$("#" + label).hide();
    });

    $("#" + value).show();
    var fun = value + "Chart";
    doCallback(eval(fun));
    event.preventDefault();
});