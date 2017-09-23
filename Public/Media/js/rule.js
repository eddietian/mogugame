var noticetext = {
        t: "提示信息",
        a: "不能为空！",
        n: "请填写数字！",
        s: "不能输入特殊字符！",
        p: "请填写邮政编码！",
        m: "请填写手机号码！",
        e: "邮箱地址格式不对！",
        u: "请填写网址！",
        r: "请填写正确信息！",
        w: "datatype未定义！",
        f: "两次输入的内容不一致！",
        c: "正在检测信息…",        
        v: "所填信息没有经过验证，请稍后…",
        g: "正在提交数据…"
    },
    
regular = {
    a: /^[\w|&|\^|\.|\$|@|%|!|\(|\)|\*|\?|#|,|:|;|~|\\|\/|\[|\]|\-|\+|\||\{|\}|=|\u4E00-\u9FA5\uf900-\ufa2d]+$/,//验证字符
    x: /^[\u4E00-\u9FA5\uf900-\ufa2d\w\.\s\S]+$/,//验证字符中英文
    w: /^\w+$/,//数字、字母(其他字母俄)、下划线字符
    n: /^\d+$/,//验证数字
    s: /^[\u4E00-\u9FA5\uf900-\ufa2d\w\.\s]+$/,
    h:/^[a-zA-Z0-9_]+$/,//验证数字，英文字母、下划线

    //验证中文字符
    b: /^[\u3400-\u4DB5\u4E00-\u9FA5\u9FA6-\u9FBB\uF900-\uFA2D\uFA30-\uFA6A\uFA70-\uFAD9\uFF00-\uFFEF\u2E80-\u2EFF\u3000-\u303F\u31C0-\u31EF\w\.\s%\}\{\*\+\-\(\)“”‘’《》（）\[\]\(\)\<\>]+$/,
    
    //验证英文大小写
    y: /^(a-z|A-Z)+$/,
    //验证中文
    z: /^[\u4E00-\u9FA5\uf900-\ufa2d]+$/,
    //验证两位小数
    d: /^\d{1,}(\.\d{2})?$/,
    //验证图片后缀
    p: /^(.+)(\.(jpg|JPG)$|\.(jpeg|JPEG)$|\.(gif|GIF)$|\.(png|PNG)$)/,
    //验证网址
    u: /^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+/,
    //验证手机号码
    // m: /^13[0-9]{9}|14[57]{1}[0-9]{8}|15[012356789]{1}[0-9]{8}|170[0-9]{8}|18[0-9]{9}|17[678]{1}[0-9]{8}$/,
    m: /^1[34578]\d{9}$/,
    //验证电话号码
    g: /^((\d{3,4})|(\d{3,4}-))?\d{8}$/,
    //验证email
    e: /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,
    //验证qq
    q: /^[1-9][0-9]{4,9}$/,
    //验证身份证号
    ic:function (gets){

        var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];// 加权因子;
        var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ];// 身份证验证位值，10代表X;

        if (gets.length == 15) {
            return isValidityBrithBy15IdCard(gets);
        }else if (gets.length == 18){
            var a_idCard = gets.split("");// 得到身份证数组
            if (isValidityBrithBy18IdCard(gets)&&isTrueValidateCodeBy18IdCard(a_idCard)) {
                return true;
            }
            return false;
        }
        return false;

        function isTrueValidateCodeBy18IdCard(a_idCard) {
            var sum = 0; // 声明加权求和变量
            if (a_idCard[17].toLowerCase() == 'x') {
                a_idCard[17] = 10;// 将最后位为x的验证码替换为10方便后续操作
            }
            for ( var i = 0; i < 17; i++) {
                sum += Wi[i] * a_idCard[i];// 加权求和
            }
            valCodePosition = sum % 11;// 得到验证码所位置
            if (a_idCard[17] == ValideCode[valCodePosition]) {
                return true;
            }
            return false;
        }

        function isValidityBrithBy18IdCard(idCard18){
            var year = idCard18.substring(6,10);
            var month = idCard18.substring(10,12);
            var day = idCard18.substring(12,14);
            var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));
            // 这里用getFullYear()获取年份，避免千年虫问题
            if(temp_date.getFullYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){
                return false;
            }
            return true;
        }

        function isValidityBrithBy15IdCard(idCard15){
            var year =  idCard15.substring(6,8);
            var month = idCard15.substring(8,10);
            var day = idCard15.substring(10,12);
            var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));
            // 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法
            if(temp_date.getYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){
                return false;
            }
            return true;
        }
    },
}

/* 倒计时 */
function clock(e,t) {
    var t = t?t:60,s = $(e);
    s.addClass('disabled');
    var a = setInterval(function() {
        t--;
        s.text(t + '秒后重发'),
        0 == t && (s.removeClass('disabled').text('获取验证码'), clearInterval(a))
    },1e3)   					
}

/* 验证码刷新 */
function flushCode(that) {
    var url = that.src.replace(/((\.html)?|(\/t\/.+)?)$/g,'');
    that.src = url+'/t/'+(new Date()).getTime(); 
}

function ajaxsm(url,data,callback,error,before) {
    $.ajax({
        type:'post',
        dataType:'json',
        data:data,
        url:url,
        time:1000000,
        beforeSend:before,
        success:callback,
        error:error
    });
}

/* 输入检查 */
function check(t) {
    if (!t.is('[datatype]')) {return '';}
    var l = t.attr('type'),
        v=$.trim(t.val()),
        s = t.closest('div').siblings('.o_check_tip'),
        d = t.attr('datatype'),
        f = t.attr('recheck'),p='';
    if (s.length < 1) {s = t.closest('.o_cw').find('.o_check_tip');}
    if (v && v == $.trim(t.attr('placeholder'))) {v = '';}
    if (f) {p=t.closest('form').find('input[name='+f+']');}
    if (d.indexOf('-')>-1) {dr = d.slice(1),dr=dr.split('-'),ds=d.charAt(0),zh=regular[ds]+'';zh=zh.slice(2,-3);r=new RegExp('^'+zh+'{' + dr[0]+ ',' +dr[1]+ '}$');} else {r = regular[d]}   
    if (s.hasClass('o_check_wrong')) {s.removeClass('o_check_wrong').text('');}
    if (l == 'checkbox') {
        if (t.is('[nullmsg]') && !t.is(':checked')) {s.removeClass('o_check_right').addClass('o_check_wrong').text(t.attr('nullmsg'));return false;} 
    } else {
        if (t.is('[nullmsg]') && (/^[a|w|n|s|y|z|d|u|m|e|q|p|g|b|h|(ic)]/.test(d)) && ( v == '')) {s.removeClass('o_check_right').addClass('o_check_wrong').text(t.attr('nullmsg'));return false;}    
        if (typeof r == 'function') {
            if(t.is('[errormsg]') && v && !r(v)) {
                s.removeClass('o_check_right').addClass('o_check_wrong').text(t.attr('errormsg'));return false;
            }
        } else {
            if (t.is('[errormsg]') && (v) && !r.test(v)) {s.removeClass('o_check_right').addClass('o_check_wrong').text(t.attr('errormsg'));return false;}
        }
        
        if (t.is('[errormsg]') && p && p.val() != t.val()) {s.removeClass('o_check_right').addClass('o_check_wrong').text(t.attr('errormsg'));return false;}      
    }
    console.log(v,t.is('[ajaxurl]'),t.attr('ajaxurl'));
    if (v && t.is('[ajaxurl]') && t.attr('ajaxurl')) {
        ajaxdata=t.attr('ajaxdata')+'='+v;
        $.ajax({
            async: false,type:'post',dataType:'json',url:t.attr('ajaxurl'),data:ajaxdata,success:function(data){
                if(data.status==0){
                    s.removeClass('o_check_right').addClass('o_check_wrong').text('*'+data.msg);return false;   
                }
            },error:function(){

            }});
    }
    return true;
}