$(document).ready(function() {
						   
    $("#nav>li").click(function () {
        $(this).siblings('li').removeClass('current');
        $(this).addClass('current');  //��һ��Ԫ����ʾ
        $(this).siblings('li').removeClass('current');

        var url = $(this).children(".J_menuItem").attr('href');
        $("#J_iframe").attr('src', url);
        return false; //��ֹĬ���¼�

    });

});