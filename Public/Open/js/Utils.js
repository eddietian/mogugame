/**
 * Created by guojie on 2015/1/24.
 * 静态工具类
 */

var Utils = {};

//处理字符串
Utils.String = {
    //修剪两端空白字符和换行符
    trim:function (s){
        return s.replace(/(^\s*)|(\s*$)|(\n)/g, "");
    },
    //修剪左端空白字符和换行符
    leftTrim:function (s){
        return s.replace(/(^\s*)|(^\n)/g, "");
    },
    //修剪右端空白字符和换行符
    rightTrim:function (s){
        return s.replace(/(\s*$)|(\n$)/g, "");
    },
    // 是否是URL
    isUrl: function(s) {
        //var p = /^((https|http|ftp|rtsp|mms)?:\/\/)(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]$)|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,6})?((\/?)|(\/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+\/?)$/;
        var p = /^((https|http|ftp|rtsp|mms)?:\/\/)[A-Za-z0-9-]+\.[A-Za-z0-9-]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;
        s = s.toLowerCase();
        return p.test(s);
    },
    //格式化数字
    numberFormat:function (s,l){
        if (!l || l < 1)l = 3;
        s=String(s).split(".");
        s[0]=s[0].replace(new RegExp('(\\d)(?=(\\d{'+l+'})+$)','ig'),"$1,");
        return s.join(".");
    },
    //字母类型检测
    isString:function (s){
        var patrn=/^([a-z]|[A-Z])+$/;
        return patrn.exec(s);
    },
    //浮点数类型检测
    isNumber:function (s){
        var patrn=/^\d+\.\d+$/;
        return patrn.exec(s);
    },
    //整数类型检测
    isInt:function (s){
        var patrn=/^-?\d+$/;
        return patrn.exec(s);
    },
    //正整数类型检测
    isUint:function (s){
        var patrn=/^\d+$/;
        return patrn.exec(s);
    },
    //星号字节
    asteriskByte:function(s,start,end)
    {
        var startStr = start?s.substr(0,start):"";
        var endStr = end?s.substr(end+1):"";
        var star = "",l;
        l = !start && !end?s.length:(start && !end?s.length - start:end);
        while(star.length < l)star += "*";
        return startStr + star + endStr;
    },
    //四舍五入保留n位小数(默认保留两位小数)
    twoDecimalPlaces:function(s,l)
    {
        if (isNaN(parseFloat(s))||s==0) return "0.00";
        var bit = !l?100:Math.pow(10,l);
        var str = String(Math.round(s * bit) / bit);
        while (str.indexOf(".") != -1 && str.length <= str.indexOf(".") + l)str += '0';
        return str;
    }
};

//获取地址栏参数
Utils.getQueryString = function(attr)
{
    var parameter = window.location.search;
    var reg = new RegExp(attr+"=([^&]*)")
    var result = parameter.substr(1).match(reg);
    return (result!=null&&result.length>1)?result[1]:null;
}

//滚动到页面底部
Utils.isScrollBottom = function()
{
    var scrollTop = document.documentElement.scrollTop||document.body.scrollTop;
    var clientHeight = document.documentElement.clientHeight;
    if(scrollTop + clientHeight >= document.body.scrollHeight) return true;
    return false;
}


//设置cookie
Utils.setCookie = function(name,value,iDay)
{
    //当前时间和过期时间相加
    var date = new Date();
    date.setDate(date.getDate()+iDay);
    //给cookie赋值
    document.cookie = name+"="+value+";expires="+date;
}
//获取cookie
Utils.getCookie = function(name)
{
    //cookie转换成数组
    var arr = document.cookie.split("; ");
    for(var i = 0; i<arr.length;i++)
    {
        //每个数组元素再进行分割，第一个是名字第二个是值
        var arr2 = arr[i].split("=");
        //只需判断名字存在就把值反出去
        if(name == arr2[0])return arr2[1];
    }
    //当前搜索的cookie不存在返回空值
    return null;
}
//删除cookie
Utils.removeCookie = function(name)
{
    //在设置的时候让他的时间过期，预览器将自动清除过期的cookie
    setCookie(name,"",-1);
}
//获取json数据长度
Utils.getJsonLength = function(json)
{
    var len = 0,key;
    for(key in json)len++;
    return len;
}

//小于ie9以下的古老ie
Utils.antiIE = function () {
    var ua = window.navigator.userAgent;
    this.browser = {};
    /* jshint ignore:start */
    var reg = new RegExp('MSIE ([0-9]{1,}[\.0-9]{0,})');
    /* jshint ignore:end */
    var IEVersion;
    if (ua.match(reg)) {
        this.browser.msie = true;
        IEVersion = parseFloat(ua.match(reg)[1]);
        if (IEVersion < 9) {
            alert('您还在使用老旧的IE' + IEVersion +'浏览器，部分功能将不被支持，建议升级浏览器试试吧~');
        }
    } else {
        this.browser.msie = false;
    }
};