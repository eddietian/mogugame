/**
* ��̳��ҳ�л�
* @dependencies ����jquery
*/
$(document).ready(function(){
	var $newCode=$(".gift-con .newCode");/*���л�*/
	var $newtGift=$(".gift-con .newtGift");
	$newCode.click(function(){
		$(this).addClass("on");
		$newtGift.removeClass("on");
		$("#newCodeList").show();
		$("#newGiftList").hide();
		
	});
	$newtGift.click(function(){
		$(this).addClass("on");
		$newCode.removeClass("on");
		$("#newCodeList").hide();
		$("#newGiftList").show();
	});
});

