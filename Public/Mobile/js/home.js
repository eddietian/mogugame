/**
* ÂÛÌ³Ê×Ò³ÇÐ»»
* @dependencies ÒÀÀµjquery
*/
$(document).ready(function(){
	var $newCode=$(".gift-con .newCode");/*°ñµ¥ÇÐ»»*/
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

