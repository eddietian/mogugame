function setFooter() {
    var copy = $("a:contains('粤网文')");
    if (copy.length == 0) {
        copy = $("p:contains('粤网文')")
    }
    var color = copy.css("color");
    var fontSize = copy.css("fontSize") || "14px";
    copy.before('<div ><a style="color:' + color + ';display:block;" target="_blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44011602000107" style="display:inline-block;text-decoration:none;height:20px;line-height:20px;"><img src="http://img3.65.com/www/images/beian.png" style="display:inline-block;vertical-align: middle;position: relative;top: -2px;"/><p style="display:inline-block;height:20px;line-height:20px;margin: 0px 0px 0px 5px;">粤公网安备 44011602000107号</p></a></div>');
    var icp = $("a:contains('粤ICP备')");
    if (icp.length == 0) {
        icp = $("span:contains('粤ICP备')");
        if (icp.length > 0) {
            icp.wrap("<a href='http://www.miibeian.gov.cn' ></a>");
            icp = $("a:contains('粤ICP备')");
        } else {
            icp = $("p:contains('粤ICP备')");
        }

    }
    if ($("p:contains('粤ICP备')").length > 0) {
        icp.append('<a target="_blank" href="http://www.miibeian.gov.cn/" style="color:' + color + ';margin-left:10px;font-size:' + fontSize + '" >经营许可证编号：粤B2-20160304</a>');
    } else {
        icp.after('<a target="_blank" href="http://www.miibeian.gov.cn/" style="color:' + color + ';margin-left:10px;font-size:' + fontSize + '" >经营许可证编号：粤B2-20160304</a>');
    }
}
if (window.location.href.indexOf("gcd") > -1) {
    $(function() {
        setFooter();
    })
} else {
    window.onload = function() {
        setFooter();
    };
}
