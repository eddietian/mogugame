
var gtFailbackFrontInitial = function(result) {
						var s = document.createElement('script');
						s.id = 'gt_lib';
						s.src = 'http://static.geetest.com/static/js/geetest.0.0.0.js';
						s.charset = 'UTF-8';
						s.type = 'text/javascript';
						document.getElementsByTagName('head')[0].appendChild(s);
						var loaded = false;
						s.onload = s.onreadystatechange = function() {
							if (!loaded && (!this.readyState|| this.readyState === 'loaded' || this.readyState === 'complete')) {
								loadGeetest(result);
								loaded = true;
							}
						};
					}
					var loadGeetest = function(config) {

						//1. use geetest capthca
						window.gt_captcha_obj = new window.Geetest({
							gt : config.gt,
							challenge : config.challenge,
							product : 'float',
							offline : !config.success
						});

						gt_captcha_obj.appendTo("#div_id_embed");
					}

					s = document.createElement('script');
					s.src = 'http://api.geetest.com/get.php?callback=gtcallback';
					$("#div_geetest_lib").append(s);
					
					var gtcallback =( function() {
						var status = 0, result, apiFail;
						return function(r) {
							status += 1;
							if (r) {
								result = r;
								setTimeout(function() {
									if (!window.Geetest) {
										apiFail = true;
										gtFailbackFrontInitial(result)
									}
								}, 1000)
							}
							else if(apiFail) {
								return
							}
							if (status == 2) {
								loadGeetest(result);
							}
						}
					})()
					
				/*	$.ajax({
								url : "jyyz/web/StartCaptchaServlet.php?rand="+Math.round(Math.random()*100),
								type : "get",
								dataType : 'JSON',
								success : function(result) {
									console.log(result);
									gtcallback(result)
								}
							})*/
							
					$.ajax({
								url : "http://www.fpwap.com/eckf/jyyz/web/StartCaptchaServlet.php?rand="+Math.round(Math.random()*100),
								type : "GET",
								 data:{},
								 dataType: 'jsonp',
		                         jsonp: "callbackparam",
		                         jsonpCallback:"returnJsonpjyz",
		                         timeout: 10000,
								success : function(result) {
									console.log(result);
									gtcallback(result)
								}
							})
	
							
							
var zhubq=0;
function ztcontentbq(){
	 //var atopp=$('#ztcontent').offset().top;	
	//var iHeightt=$('.comment-expression-popu2').height();
	//$('.comment-expression-popu2').css({'top':atopp+10})
	var comiconlist=showiconlist('bqone','2');
	$('.firsticonlist').append(comiconlist);
	$(".comment-expression-popu2").show();	
	
	$("#bqone li img").click(function () {
	var kk = $(this).attr("title");
	kk="[/表情"+kk+"]";
	var newtextcontent=$("#ztcontent").val();
	$("#ztcontent").val(newtextcontent+kk);
});
	
}
				
(function(){
	//点击空白处关闭表情框；
	 $(document).bind("click",function(e){ 
		 var target = $(e.target); 
		 if(target.closest(".comment-expression-popu2").length == 0 && target.closest(".dw-comment-expression-btn").length == 0 && target.closest(".comment-expression-popu").length == 0){
			 $(".comment-expression-popu2").hide(); 
			 $(".comment-expression-popu").hide(); 
		 } 
		 })  
	
   
	
		var oStar=$('#star');
		var aLi = $(".startlist li");
		var oUl = $(".startlist");
		var i = iScore = iStar = 0;
		
		
		for (i = 1; i <= aLi.length; i++){
			aLi[i - 1].index = i;
			aLi[i - 1].onmouseover = function (){
				fnPoint(this.index);
			};
			aLi[i - 1].onmouseout = function (){
				fnPoint();
			};
	
			aLi[i - 1].onclick = function (){
				iStar = this.index;
			}
		}
	
		function fnPoint(iArg){
			iScore = iArg || iStar;
			for (i = 0; i < aLi.length; i++) aLi[i].className = i < iScore ? "on" : "";	
		}
			
	})();
	




//进入自动加载吐槽；
jztc();
//进入刷新列表；

var dzlist=[];
var norep=0;
var tcallcount=0;
var morrep=[];
var repztpage=2;
//判断最新和热门
var biaojicomm=1;
var allpl=0;

//主评论赞和踩；
function zczancai(val,type,num){
	$.ajax({
		 url:"http://www.fpwap.com/eckf/comments.php?type=8&rand="+Math.round(Math.random()*100),
		 type: 'GET',
		 data:{'id':val,'type':type},
		 dataType: 'jsonp',
		 jsonp: "callbackparam",
		 jsonpCallback:"returnJsonp",
		 timeout: 10000,
		 success: function(data){
			 if(data.status){
				 if(type==1){
					 $(".ztc"+val).html('('+(num+1)+')');//踩
					$('.cainum'+val).animate({"opacity":"1",top:"-40px","right":"-10px"},function(){
					  $('.cainum'+val).css({"opacity":"0"})	 
					 })
				 }else{
					 $(".ztd"+val).html('('+(num+1)+')');//赞
					 $('.dingnum'+val).animate({"opacity":"1",top:"-40px","right":"-10px"},function(){
					  $('.dingnum'+val).css({"opacity":"0"})	 
					 })
					 
			     }
				  
				 }else{
					 //alert(data.info);
					 $('.ztd'+val).css({"color":"#ccc"})
					 }
			 }
		 });
}
//用户回复查看更多
function ctmorpl(val){
	ztdjchange();
	if(morrep[val]){
		 morrep[val]++;
		 }else{
			 morrep[val]=1;
			 }
	
	$.ajax({
		 url:"http://www.fpwap.com/eckf/comments.php?type=9&rand="+Math.round(Math.random()*100),
		 type: 'GET',
		 data:{'id':val,'page':morrep[val]},
		 dataType: 'jsonp',
		 jsonp: "callbackparam",
		 jsonpCallback:"returnJsonp",
		 timeout: 10000,
		 success: function(data){
		 if(data.status){
			 if(morrep[val]==1){
				 $(".repnew"+val).empty();
				 }
			    var addreplist='';
			    if(data.data.count<5){
			    	$("#showlook"+val).remove();
				    }
                if(data.data.count>0){
                	    $.each(data.data.list,function(k,repreplist){
                	    	addreplist+= '<li class="replay_list_item" data-cmt-cid="0f156b67d31bc1c7d77faf0b5bef48e8"> '+
  		                '<div class="reply_content">'+
  		                  '<p class="reply_info"><a href="#" target="_blank">'+repreplist.phone1+'</a><font class="palr5">对</font><a href="javascript:;">'+repreplist.phone2+'</a><font class="palr5">说</font></p>'+
  		                  '<p class="reply_text"> '+repreplist.content+'<span class="reply_time">('+repreplist.time+')</span> </p>'+
  		                  '<div class="reply_action"> <a href="javascript:void(1);" onclick="zthf('+repreplist.id*-1+')" class="comment-reply">回复</a> </div>'+
  		                  '<div id="pccct'+repreplist.id*-1+'"></div>'
  		                '</div>'+
  		              '</li>'
                    	  });
                      }
                $(".repnew"+val).append(addreplist);
			 }
		 }
		 
	})
}
function rmcomment(){
	alert('热门评论');
}
//主评论加载更多；
function ztmore(val){
	if(val){
		norep=0;
		ztdjchange();
		repztpage=1;
		biaojicomm=val;
		 $(".dw-comment-comment_list").empty(pllisthp); 
	}
	//alert(repztpage);
	var pllisthp='';
	$.ajax({
		 url:"http://www.fpwap.com/eckf/comments.php?type=66&rand="+Math.round(Math.random()*100),
		 type: 'GET',
		 data:{'classid':classid,'contentid':contentid,'page':repztpage,'biaoji':biaojicomm},
		 dataType: 'jsonp',
		 jsonp: "callbackparam",
		 jsonpCallback:"returnJsonp",
		 timeout: 10000,
		 success: function(data){
  		 if(data.status){
          if(data.data.counta){
        	  if(val){
      			 $(".dw-comment-comment_list").empty(pllisthp); 
      		    }
        	  $.each(data.data.lista,function(i,n){
            	 
        			 pllisthp+='<li class="comment-list_item" data-cmt-cid="855062a800522fb07e0717a5aad4742c">'+
      		        '<div class="comment_content">'+
      		          '<p class="comment_info"><a href="#" target="_blank" class="author"><img src="http://www.fpwap.com/eckf/images//fwicon.png" alt="" onerror="this.onerror=null">'+n.phone+'</a></p>'+
      		          '<p class="comment_text">'+n.content+'</p>'+
      		          '<div class="comment_action" style="position: relative;">'+
      		            '<div class="post_time">'+n.time+'</div>'+
      		            '<a href="javascript:void(1);" onclick="zczancai('+n.id+',0,'+n.praise+')" data-num="0" data-cmt-oper="agree" class="view_up" style="position: relative;" title="赞"><div class="dingnum'+n.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>赞<em data-num="3" class="ztd'+n.id+'">('+n.praise+')</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zczancai('+n.id+',1,'+n.cai+')" data-num="0" data-cmt-oper="disagree" class="view_down" title="踩"><div class="cainum'+n.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>踩<em data-num="3" class="ztc'+n.id+'">('+n.cai+')</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zthf('+n.id+')" id="zcomm'+n.id+'" class="comment-reply">回复<em data-num="3">('+n.comments+')</em></a> </div>'+
      		            '<div id="zcomma'+n.id+'"></div>'+
      		            '<div class="rep'+n.id+'">'
                  
                  if(n.replist!=null && n.replist!=''){
                	  pllisthp+= '<div class="comment_reply_ctn">'+
      		          ' <i class="comment_reply_arrow"></i>'+
  		            '<ul class="dw-comment-replay_list repnew'+n.id+'">';

  		            
                	    $.each(n.replist,function(k,repreplist){
                		  pllisthp+= '<li class="replay_list_item" data-cmt-cid="0f156b67d31bc1c7d77faf0b5bef48e8"> '+
  		                '<div class="reply_content">'+
		                  '<p class="reply_info"><a href="#" target="_blank">'+repreplist.phone1+'</a><font class="palr5">对</font><a href="#">'+repreplist.phone2+'</a><font class="palr5">说</font></p>'+
		                  '<p class="reply_text"> '+repreplist.content+'<span class="reply_time">('+repreplist.time+')</span> </p>'+
		                  '<div class="reply_action"> <a href="javascript:void(1);" onclick="zthf('+repreplist.id*-1+')" class="comment-reply">回复</a> </div>'+
		                  '<div id="pccct'+repreplist.id*-1+'"></div>'
		                '</div>'+
		              '</li>'
                    	  });
                	    pllisthp+='</ul>'+
      		          '</div>'
      		          if(n.comments>3){
        		        	pllisthp+='<div id="showlook'+n.id+'"><a href="javascript:void(1);" onclick="ctmorpl('+n.id+')" class="dw-comment-more_comment"><span>查看更多评论</span></a></div>'
          		          }
      		          
    		          pllisthp+='</div>';
                      } 
        			 pllisthp+=    '</div>'+
      		          '</li>' 
                	    
	  	     });
  	  	     if(data.data.counta<10){
  	  	  	     $("#morztpl").html("已经到底部了哦");
  	  	  	     }
        	  $(".dw-comment-comment_list").append(pllisthp); 
            }else{
                $("#morztpl").html("已经到底部了哦");
                 //alert("没有值");
            }
		 }else{
			 $(".tucBotL").append('还没人吐槽，来一发吧！');
			 }
		 }
		 }); 
	repztpage++;
}

//进入自动加载；
function jztc(){//
	var addtulist='';
	var pllisthp='';
	$.ajax({
		 url:"http://www.fpwap.com/eckf/comments.php?type=6&rand="+Math.round(Math.random()*100),
		 type: 'GET',
		 data:{'classid':classid,'contentid':contentid},
		 dataType: 'jsonp',
		 jsonp: "callbackparam",
		 jsonpCallback:"returnJsonp",
		 timeout: 10000,
		 success: function(data){
			  islogin=data.data.userhtml;
			 $('.user-info').append(islogin);
			 sessinid=data.sessinid;
			 //if(data.data.userlist.id){
				//  sessinid=data.data.userlist.id;
				// }else{
				//	 sessinid=0;
				//	 }
			
			 
			 
  		 if(data.status){
  	  		 if(data.data.count){
  	  	  		 tcallcount=data.data.count;
  	  	  		 $.each(data.data.list,function(i,n){
  	  	  			 if(i<4){
  	  	  	  			 var colortc="newtca";
  	  	  	  		}else if(i>=4 && i<=8){
  	  	  	  	  		var colortc="newtcb";
  	  	  	  	  	}else if(i>8 && i<=12){
  	  	  	  	  	    var colortc="newtcc";
  	  	  	  	  	  	}else{
  	  	    	  	  	  	var colortc="";
  	  	  	  	  	  	  	}
  	   				addtulist+='<span><a href="javascript:void(1);" onclick="djtc('+n.id+','+n.praise+')" id="idtc'+n.id+'" class="element '+colortc+'" data-toggle="tooltip" title="'+n.content+'已有'+n.praise+'人赞同，点击投票">'+n.content+'</a></span>'
  	  	  			 });
  	  			 $(".tucaoTop").append(addtulist);
  	  			 $(".tucBotL").append('已有<font class="red">'+data.data.count+'</font>人吐槽/表赞');
  	  	  }else{
   	  		 $(".tucBotL").append('还没人吐槽，来一发吧！');
  	  	  }
          if(data.data.counta){
        	  $.each(data.data.lista,function(i,n){
            	 
        			 pllisthp+='<li class="comment-list_item" data-cmt-cid="855062a800522fb07e0717a5aad4742c">'+
      		        '<div class="comment_content">'+
      		          '<p class="comment_info"><a href="#" target="_blank" class="author"><img src="http://www.fpwap.com/eckf/images//fwicon.png" alt="" onerror="this.onerror=null">'+n.phone+'</a></p>'+
      		          '<p class="comment_text">'+n.content+'</p>'+
      		          '<div class="comment_action" style="position: relative;">'+
      		            '<div class="post_time">'+n.time+'</div>'+
      		            '<a href="javascript:void(1);" onclick="zczancai('+n.id+',0,'+n.praise+')" data-num="0" data-cmt-oper="agree" class="view_up" style="position: relative;" title="赞"><div class="dingnum'+n.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>赞<em data-num="3" class="ztd'+n.id+'">('+n.praise+')</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zczancai('+n.id+',1,'+n.cai+')" data-num="0" data-cmt-oper="disagree" class="view_down" title="踩"><div class="cainum'+n.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>踩<em data-num="3" class="ztc'+n.id+'">('+n.cai+')</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zthf('+n.id+')" id="zcomm'+n.id+'" class="comment-reply">回复<em data-num="3">('+n.comments+')</em></a> </div>'+
      		            '<div id="zcomma'+n.id+'"></div>'+
      		            '<div class="rep'+n.id+'">'
                  
        		          if(n.replist!=null && n.replist!=''){
                	  pllisthp+= '<div class="comment_reply_ctn">'+
      		          ' <i class="comment_reply_arrow"></i>'+
  		            '<ul class="dw-comment-replay_list repnew'+n.id+'">';
                	    $.each(n.replist,function(k,repreplist){
                		  pllisthp+= '<li class="replay_list_item" data-cmt-cid="0f156b67d31bc1c7d77faf0b5bef48e8"> '+
  		                '<div class="reply_content">'+
		                  '<p class="reply_info"><a href="#" target="_blank">'+repreplist.phone1+'</a><font class="palr5">对</font><a href="javascript:;">'+repreplist.phone2+'</a><font class="palr5">说</font></p>'+
		                  '<p class="reply_text"> '+repreplist.content+'<span class="reply_time">('+repreplist.time+')</span> </p>'+
		                  '<div class="reply_action"> <a href="javascript:void(1);" onclick="zthf('+repreplist.id*-1+')" class="comment-reply">回复</a> </div>'+
		                  '<div id="pccct'+repreplist.id*-1+'"></div>'
		                '</div>'+
		              '</li>'
                    	  });


    		          pllisthp+='</ul>'+
      		          '</div>'
      		          if(n.comments>3){
        		        	pllisthp+='<div id="showlook'+n.id+'"><a href="javascript:void(1);" onclick="ctmorpl('+n.id+')" class="dw-comment-more_comment"><span>查看更多评论</span></a></div>'
          		          }
      		          
    		          pllisthp+='</div>';
                      } 
        			 pllisthp+=    '</div>'+
      		          '</li>'       
	  	     });
        	  $(".dw-comment-comment_list").prepend(pllisthp); 
        	  $("#allpl").html(data.data.numlist);
        	  allpl=data.data.numlist;
            }else{
				 $('#morztpl span').text('还没有评论，快来抢沙发吧！');
            }

	  	  	  
		 }
		 }
		 }); 
}
function zthf(val){
//	window.gt_captcha_obj.onRefresh(function(){
//	    pass = true;
//	})
	if(norep==val){
		return;
		}
   var comiconlist=showiconlist('bqtwo',' ');	
   var repcmm= '<!-- 回复 -->'+
   '<div class="comment_reply_reply_ctn">'+
   '<div class="dw-comment-reply_post-ctn">'+
     '<div class="textarea_ctn">'+
       '<textarea class="dw-comment-reply_post newtext" id="rephfone"></textarea>'+
     '</div>'+
     '<div class="dw-comment-reply_post-foot">'+
       '<p class="user-info">'+
       islogin+
       '</p>'+
       ' <!--  验证码代码在这里-->'+
       '<div id="yzmz">'+
		'</div>'+
       '<!--  yanzm代码在这里-->'+
	   '<div class="errortips" id="errortipsbot"></div>'+
      ' <div class="dw-comment-reply_post-action">'+
       ' <div class="comment-expression"> <span class="note" style="display: none;">超出<em></em>字</span> <a href="javascript:void(1);" onclick="xianshibq()" class="dw-comment-expression-btn"><img src="http://www.fpwap.com/eckf/images/fptp.png" alt="表情"></a> </div>'+
        ' <input class="submit not_login" type="submit" onclick="repuser('+val+')" value="">'+
       '</div>'+
     '</div>'+
   '</div>'+comiconlist+
 '</div>'+
  '<!--回复-->'	

 
    if(val<0){
 	   $('#pccct'+val).append(repcmm);
  	  $("#yzmz").replaceWith($("#yzmzt"));
	    $('#pccct'+norep).empty();
	    if(norep>0){
	       	 $('#zcomma'+norep).empty();}
     }else{
     	   $('#zcomma'+val).append(repcmm);
      	  $("#yzmz").replaceWith($("#yzmzt"));
      	  $('#zcomma'+norep).empty();
       	 if(norep<0){
        		$('#pccct'+norep).empty();}
     }
   
    norep=val;
	$("#bqtwo li img").click(function () {
	var kk = $(this).attr("title");
	kk="[/表情"+kk+"]";
	var newtextcontent=$("#rephfone").val();
	$("#rephfone").val(newtextcontent+kk);
});
}
function xianshibq(){
	$(".comment-expression-popu").show();
}

//点击吐槽；
function djtc(val,val1){
	val1++;
	   $.ajax({
	  		 url:"http://www.fpwap.com/eckf/comments.php?type=7&rand="+Math.round(Math.random()*100),
	  		 type: 'GET',
	   		 data:{'tcid':val},
	  		 dataType: 'jsonp',
	  		 jsonp: "callbackparam",
	  		 jsonpCallback:"returnJsonp",
	  		 timeout: 10000,
	  		 success: function(data){
	  			 tcallcount++;
    			 $(".tucBotL").empty();
    			 $(".tucBotL").append('已有<font class="red">'+tcallcount+'</font>人吐槽/表赞');
    			 $("#idtc"+val).attr('title','已有'+val1+'人赞同, 您已投票'); 
	  		 }
	  		 });  
}


$('#tucaolist').click(function(){
    var tuchaoone=$.trim($('#text2').val());
    if(tuchaoone==null || tuchaoone==''){
        alert('输入能为空哦!');
        }else{
            $.ajax({
        		 url:"http://www.fpwap.com/eckf/comments.php?type=5&rand="+Math.round(Math.random()*100),
        		 type: 'GET',
         		 data:{'content':tuchaoone,'classid':classid,'contentid':contentid,'sessinid':sessinid},
        		 dataType: 'jsonp',
        		 jsonp: "callbackparam",
        		 jsonpCallback:"returnJsonp",
        		 timeout: 10000,
        		 success: function(data){
            		 if(data.status){
            			 $(".tucaoTop").prepend('<span><a href="javascript:void(1);" class="element newtc" data-toggle="tooltip" title="'+data.data+'已有1人赞同，点击投票">'+data.data+'</a></span>');
            			 $('#text2').val(' ');
            			 tcallcount++;
            			 $(".tucBotL").empty();
            			 $(".tucBotL").append('已有<font class="red">'+tcallcount+'</font>人吐槽/表赞');
                		 }else{
                    		 alert(data.info);
                    		}
        		 }
        		 }); 
        }
});
function repuser(val){
	 var rephfone=$.trim($('#rephfone').val());
	 var repliston='';
	 var geetest_challenge=$(".geetest_challenge").val();
     var geetest_validate=$(".geetest_validate").val();
     var geetest_seccode=$(".geetest_seccode").val();
	  if(rephfone.replace(/^\s+|\s+$/g,"")==null || rephfone.replace(/^\s+|\s+$/g,"")==''){
	        $('#errortipsbot').text('输入内容不能为空！');
					   setTimeout(function(){
							$('#errortipsbot').text(' ');
						 }, 1500);
	        }else{
	            $.ajax({
	        		 url:"http://www.fpwap.com/eckf/comments.php?type=4&rand="+Math.round(Math.random()*100),
	        		 type: 'GET',
	         		 data:{'id':val,'uid':sessinid,'content':rephfone,'geetest_challenge':geetest_challenge,'geetest_validate':geetest_validate,'geetest_seccode':geetest_seccode},
	        		 dataType: 'jsonp',
	        		 jsonp: "callbackparam",
	        		 jsonpCallback:"returnJsonp",
	        		 timeout: 10000,
	        		 success: function(data){
		        		 if(data.status){
		        			 window.gt_captcha_obj.refresh();
		            		 repliston= '<li class="replay_list_item" data-cmt-cid="0f156b67d31bc1c7d77faf0b5bef48e8"> '+
		   		                '<div class="reply_content">'+
		   		                  '<p class="reply_info"><a href="#" target="_blank">'+data.data.phone1+'</a><font class="palr5">对</font><a href="javascript:;">'+data.data.phone2+'</a><font class="palr5">说</font></p>'+
		   		                  '<p class="reply_text"> '+data.data.content+'<span class="reply_time">('+data.data.time+')</span> </p>'+
		   		                  '<div class="reply_action"> <a href="javascript:void(1);" onclick="zthf('+data.data.id*-1+')" class="comment-reply">回复</a> </div>'+
		   		                  '<div id="pccct'+data.data.id*-1+'"></div>'
		   		                '</div>'+
		   		              '</li>'
		   			           	if($('.rep'+val).html()==''||$('.rep'+val).html()==null){
		   		                	  var addkj= '<div class="comment_reply_ctn">'+
		   		      		          ' <i class="comment_reply_arrow"></i>'+
		   		  		            '<ul class="dw-comment-replay_list repnew'+val+'">'+
		   		    		        '</ul>'+
		   		      		          '</div>'+
		   		      		        '</div>';
		   			        	} 
		   			           $('.rep'+val).append(addkj);     
		                 $(".repnew"+data.data.ztid).prepend(repliston);
		        		 $('#rephfone').val(' ');
	        		 }else{
		 			        $('#errortipsbot').text(data.info);
					   setTimeout(function(){
							$('#errortipsbot').text(' ');
						 }, 1500);
					   }
			       }
	        		 });
		        }
}
$("#ztcontent").click(function(){
	ztdjchange();
});
function ztdjchange(){
	$("#ztyzm").append("<div id='zztyzm'></div>");
	 $("#zztyzm").replaceWith($("#yzmzt"));

		    if(norep>0){
		       	 $('#zcomma'+norep).empty();}
	       	 if(norep<0){
	        	$('#pccct'+norep).empty();}
		       	norep=0;
}



$('#pinglunzt').click(function(){
	      var geetest_challenge=$(".geetest_challenge").val();
	      var geetest_validate=$(".geetest_validate").val();
	      var geetest_seccode=$(".geetest_seccode").val();
           var content=$("#ztcontent").val();
		   if(content.replace(/^\s+|\s+$/g,"")==null || content.replace(/^\s+|\s+$/g,"")==''){
			   $('#errortips').text('输入内容不能为空！');
					   setTimeout(function(){
							$('#errortips').text(' ');
						 }, 1500);
			   return;
			   }
           $.ajax({
      		 url:"http://www.fpwap.com/eckf/comments.php?type=3&rand="+Math.round(Math.random()*100),
      		 type: 'GET',
       		 data:{'content':content,'classid':classid,'contentid':contentid,'uid':sessinid,'geetest_challenge':geetest_challenge,'geetest_validate':geetest_validate,'geetest_seccode':geetest_seccode},
      		 dataType: 'jsonp',
      		 jsonp: "callbackparam",
      		 jsonpCallback:"returnJsonp",
      		 timeout: 10000,
      		 success: function(data){
          		 if(data.status){
           			window.gt_captcha_obj.refresh();
              		 //alert(data.info);
          			 $('#ztcontent').val(' ');
          			 var userplone='<li class="comment-list_item" data-cmt-cid="855062a800522fb07e0717a5aad4742c">'+
            		        '<div class="comment_content">'+
            		          '<p class="comment_info"><a href="#" target="_blank" class="author"><img src="http://www.fpwap.com/eckf/images//fwicon.png" alt="" onerror="this.onerror=null">'+data.data.phone+'</a></p>'+
            		          '<p class="comment_text">'+data.data.content+'</p>'+
            		          '<div class="comment_action">'+
            		            '<div class="post_time">'+data.data.time+'</div>'+
            		            '<a href="javascript:void(1);" onclick="zczancai('+data.data.id+',0,0)" data-num="0" data-cmt-oper="agree" class="view_up" title="赞"><div class="dingnum'+data.data.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>赞<em data-num="3" class="ztd'+data.data.id+'">(0)</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zczancai('+data.data.id+',1,0)" data-num="0" data-cmt-oper="disagree" class="view_down" title="踩"><div class="cainum'+data.data.id+'" style="position: absolute; color:#f60;font-size:16px; right:0px;top:-10px;opacity:0;">+1</div>踩<em data-num="3" class="ztc'+data.data.id+'">(0)</em></a>&nbsp;&nbsp;<a href="javascript:void(1);" onclick="zthf('+data.data.id+')" id="zcomm'+data.data.id+'" class="comment-reply">回复<em data-num="3">(0)</em></a> </div>'+
            		            '<div id="zcomma'+data.data.id+'"></div>'+
            		            '<div class="rep'+data.data.id+'"></div></li>';
          		      $(".dw-comment-comment_list").prepend(userplone); 
            		    allpl++
            		    $("#allpl").html(allpl);
                    	  
                   }else{
                	   $('#errortips').text(data.info);
					   setTimeout(function(){
							$('#errortips').text(' ');
						 }, 1500);

                  }
      		 }
      		 }); 
})
function taglist(val){
	 var val2=$("#ztcontent").val();
	 $('#ztcontent').val(val2+val);
}
function dengru(){
	var url=window.location.href;
    window.location.href="http://fahao.fpwap.com/dl.html?location="+url;   
}
function showiconlist(valid,keys){
var comiconlist='<div class="comment-expression-popu'+keys+'"><span class="arrow-down"></span><div class="content">'+
  '<ul class="rl_exp_main clearfix rl_selected" id="'+valid+'" style="display: block;"><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/0.gif" alt="1" title="1"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/1.gif" alt="2" title="2"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/2.gif" alt="3" title="3"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/3.gif" alt="4" title="4"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/4.gif" alt="5" title="5"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/5.gif" alt="6" title="6"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/6.gif" alt="7" title="7"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/7.gif" alt="8" title="8"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/8.gif" alt="9" title="9"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/9.gif" alt="10" title="10"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/10.gif" alt="11" title="11"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/11.gif" alt="12" title="12"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/12.gif" alt="13" title="13"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/13.gif" alt="14" title="14"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/14.gif" alt="15" title="15"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/15.gif" alt="16" title="16"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/16.gif" alt="17" title="17"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/17.gif" alt="18" title="18"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/18.gif" alt="19" title="19"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/19.gif" alt="20" title="20"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/20.gif" alt="21" title="21"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/21.gif" alt="22" title="22"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/22.gif" alt="23" title="23"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/23.gif" alt="24" title="24"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/24.gif" alt="25" title="25"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/25.gif" alt="26" title="26"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/26.gif" alt="27" title="27"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/27.gif" alt="28" title="28"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/28.gif" alt="29" title="29"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/29.gif" alt="30" title="30"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/30.gif" alt="31" title="31"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/31.gif" alt="32" title="32"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/32.gif" alt="33" title="33"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/33.gif" alt="34" title="34"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/34.gif" alt="35" title="35"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/35.gif" alt="36" title="36"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/36.gif" alt="37" title="37"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/37.gif" alt="38" title="38"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/38.gif" alt="39" title="39"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/39.gif" alt="40" title="40"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/40.gif" alt="41" title="41"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/41.gif" alt="42" title="42"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/42.gif" alt="43" title="43"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/43.gif" alt="44" title="44"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/44.gif" alt="45" title="45"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/45.gif" alt="46" title="46"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/46.gif" alt="47" title="47"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/47.gif" alt="48" title="48"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/48.gif" alt="49" title="49"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/49.gif" alt="50" title="50"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/50.gif" alt="51" title="51"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/51.gif" alt="52" title="52"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/52.gif" alt="53" title="53"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/53.gif" alt="54" title="54"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/54.gif" alt="55" title="55"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/55.gif" alt="56" title="56"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/56.gif" alt="57" title="57"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/57.gif" alt="58" title="58"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/58.gif" alt="59" title="59"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/59.gif" alt="60" title="60"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/60.gif" alt="61" title="61"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/61.gif" alt="62" title="62"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/62.gif" alt="63" title="63"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/63.gif" alt="64" title="64"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/64.gif" alt="65" title="65"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/65.gif" alt="66" title="66"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/66.gif" alt="67" title="67"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/67.gif" alt="68" title="68"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/68.gif" alt="69" title="69"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/69.gif" alt="70" title="70"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/70.gif" alt="71" title="71"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/71.gif" alt="72" title="72"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/72.gif" alt="73" title="73"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/73.gif" alt="74" title="74"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/74.gif" alt="75" title="75"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/75.gif" alt="76" title="76"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/76.gif" alt="77" title="77"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/77.gif" alt="78" title="78"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/78.gif" alt="79" title="79"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/79.gif" alt="80" title="80"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/80.gif" alt="81" title="81"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/81.gif" alt="82" title="82"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/82.gif" alt="83" title="83"></li><li class="rl_exp_item"><img src="http://www.fpwap.com/eckf/images/mr/83.gif" alt="84" title="84"></li></ul>'+
'</div></div>';
return comiconlist;
}
