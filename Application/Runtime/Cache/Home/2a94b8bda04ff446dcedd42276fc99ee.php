<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Working System</title>
    <meta name="keywords" content="">
    <meta name="description" content="">

    <link rel="stylesheet" href="/webforcits/css/reset.css">
    <link rel="stylesheet" href="/webforcits/css/supersized.css">
    <link rel="stylesheet" href="/webforcits/css/style.css">
    <link rel="stylesheet" href="/webforcits/css/flavr.css">
    <link rel="stylesheet" href="/webforcits/css/animate.css">
    <link rel="stylesheet" href="/webforcits/css/classie.css">
    
    <!--[if lt IE 9]>
      <script src="/webforcits/js/html5shiv.min.js"></script>
      <script src="/webforcits/js/respond.min.js"></script>
    <![endif]-->

  </head>
  <body oncontextmenu="return false">

     <div class="page-container">
       <h1>系统登陆</h1>
       <form action="" method="post">
         <div>
            <input type="text" name="username" placeholder="Username" autocomplete="off">
         </div>
         <div>
            <input type="password" name="password" placeholder="Password" onpaste="return false">
         </div>
         <button id="submit" type="submit">登　　陆</button>
         <div style="margin-top:25px;">
            <a href="#" id="create">没有账号？</a>
         </div>
       </form>
     </div>
     <div style="position:fixed;bottom:30px;text-align:center;width:100%;">Powered by shenfeng.Wang</div>

    <!-- Javascript -->
    <script src="/webforcits/js/jquery-1.11.3.min.js"></script>
    <script src="/webforcits/js/supersized.3.2.7.min.js"></script>
    <script src="/webforcits/js/supersized-init.js"></script>
    <script src="/webforcits/js/flavr.min.js"></script>
    <script src="/webforcits/js/sha1.js"></script>
    <script src="/webforcits/js/classie.js"></script>
    <script>
    $(document).ready(function(){
        //初始设置
        var substatus = 'proof',reg_id,reg_cert;
        
    	//没有账号->点击
    	$('#create').click(function(){
            new $.flavr({ 
                title : '请输入',
                content: '<div class="extra-message" style="color:red;"></div>',
    			dialog : 'prompt',
                prompt : { placeholder: '注册码或身份证' }, 
                animateEntrance : 'bounceInDown',
                animateClosing : 'pulse',
                buttons : {
                	confirm : {text: '提交', style : 'danger', action : function($container,$prompt){
                		var flavr = this;
                		if(substatus == 'proof'){
	                		if($prompt.val() == ''){
	                    		$container.find('.extra-message').text('输入为空，请正确填写！');
	                    		return false;
	                		}
	                		$.ajax({
	                    		url : '/webforcits/home/index/proof',
	                    		type : 'post',
	                    		async : false,
	                    		data : {proofid : $prompt.val()},
	                    		success : function(callback){
	                        		if(callback['status'] == 0){
		                        		if(callback['error'] > 9){window.location.replace("about:blank");}
	                            		flavr.title('');
	                            		flavr.content('<div class="extra-message" style="color:red;">输入错误！<br />您还有 ' + (10 - callback['error']) + ' 次机会！</div>');
	                            		$(".flavr-button[rel='btn-confirm']").remove();
	                            		$(".flavr-button[rel='btn-cancel']").text('确定');
	                            		return false;
	                        		}else if(callback['status'] == 1){
	                        			flavr.fullscreen();
	                        			flavr.title('请完善信息');
	                        			reg_id = callback['id'];
	                        			reg_cert = callback['cert'];
	                        			$('.flavr-message').load('/webforcits/home/index/reg/' + reg_id + '/' + reg_cert + '/' , function(){substatus = 'reg';});
	                        		}
	                        	},
	                    		error : function(){
	                        		flavr.title('');
	                        		flavr.content('无法提交信息，请重试！');
	                        		$(".flavr-button[rel='btn-confirm']").remove();
	                        		$(".flavr-button[rel='btn-cancel']").text('确定');
	                        		return false;
	                        	}
	                    	});
                		}else if(substatus == 'reg'){
							var regInfo = {
							username:$('#reg_username').val(),password:$('#reg_password').val(),passwordconfirm:$('#reg_passwordconfirm').val(),
							realname:$('#reg_realname').val(),idcardnumber:$('#reg_idcardnumber').val(),bankcard:$('#reg_bankcard').val(),
							bankname:$('#reg_bankname').val(),mobile:$('#reg_mobile').val()
							};
							var regInfo_check = 1;
							$.each(regInfo,function(k,v){
								if(v == ''){
									$('.extra-message').text('信息未填写完整！');
									regInfo_check = 0;
									return false;
								}
							});
							if(regInfo_check == 0)return false;
							var reg_check = /^[a-zA-Z0-9_]+$/;
							if(regInfo['username'].length < 10 || regInfo['username'].length > 20 || !reg_check.test(regInfo['username'])){
								$('.extra-message').text('用户名填写错误！请注意填写要求！');
								return false;
							}
							if(regInfo['password'].length < 10 || regInfo['password'].length > 30 || !reg_check.test(regInfo['password'])){
								$('.extra-message').text('密码填写错误！请注意填写要求！');
								return false;
							}
							if(regInfo['password'] != regInfo['passwordconfirm']){
								$('.extra-message').text('两次密码输入不同！');
								return false;
							}
							var reg_check_cn = /^[\u4e00-\u9fa5]+$/g;
							if(regInfo['realname'].length < 2 || regInfo['realname'].length > 4 || !reg_check_cn.test(regInfo['realname'])){
								$('.extra-message').text('真实姓名输入错误！请注意填写要求！');
								return false;
							}
							var reg_check_mobile = /^[1][0-9]{10}$/;
							if(!reg_check_mobile.test(regInfo['mobile'])){
								$('.extra-message').text('手机号码输入错误！');
								return false;
							}
							var reg_check_idcard = /^[0-9Xx]{18}$/;
							if(!reg_check_idcard.test(regInfo['idcardnumber'])){
								$('.extra-message').text('身份证号码输入错误！');
								return false;
							}
							var reg_check_bankcard = /^[0-9]{16,19}$/;
							if(!reg_check_bankcard.test(regInfo['bankcard'])){
								$('.extra-message').text('银行卡号码异常，请确认！');
								return false;
							}
							if(regInfo['bankname'].length < 4 || regInfo['bankname'].length > 30){
								$('.extra-message').text('开户行信息长度异常，请输入4-30个字符。');
								return false;
							}
							$.post('/webforcits/home/index/reg/' + reg_id + '/' + reg_cert + '/' , regInfo)
							.success(function(callback){
								if(callback['status'] == 1){
									flavr.title('恭喜，注册成功！');
									flavr.content(callback['message']);
								}else if(callback['status'] == 0){
									flavr.title('抱歉，注册失败');
									flavr.content(callback['message']);
								}else if(callback['status'] == -1){
									$('.extra-message').text(callback['message']);
									return false;
								}
								$(".flavr-button[rel='btn-confirm']").remove();
								$(".flavr-button[rel='btn-cancel']").text('确定');
								flavr.revert();
							})
							.error(function(){
								flavr.title('抱歉，注册失败！');
								flavr.content('原因：无法提交数据。');
								$(".flavr-button[rel='btn-confirm']").remove();
								$(".flavr-button[rel='btn-cancel']").text('确定');
								flavr.revert();
							});
                		}
                    	return false;
                	}},
                	cancel : {text : '取消', style : 'primary', action : function($container,$prompt){substatus = 'proof';}}
                },
           });
           return false;
    	});
    	
    	//没有账号->hover
        $("#create").hover(
            function(){
            	$(this).text('那就创建！');
        	},
        	function(){
            	$(this).text('没有账号？');
        	}
        );

        //登陆提交前处理
        $('#submit').click(function(){
            var username = $('input[name="username"]').val();
			var password = $('input[name="password"]').val();
			if(username.length == 0 || password.length == 0){
				new $.flavr({
					content : '用户名和密码不能为空！',
					animateEntrance : 'flash',
					animateClosing : 'pulse'
				});
				return false;
			}
			$('input[name="password"]').val(hex_sha1(password));
        });

<?php if(($login_check == 0)): ?>//用户名或密码错误
		new $.flavr({
			content : '用户名或密码错误！',
			animateEntrance : 'flash',
			animateClosing : 'pulse'
		});<?php endif; ?>
    });
    </script>
    <!--[if lt IE 9]>
      <script type="text/javascript">
      	document.getElementById("submit").style.display = "none";
      	document.getElementById("create").style.display = "none";
      	alert("检测到您正在使用低版本IE浏览器，本系统不支持IE 8 及以下版本登录，请更换浏览器！");
      </script>
    <![endif]-->
  </body>
</html>