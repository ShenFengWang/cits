<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Working System</title>
    <meta name="keywords" content="">
    <meta name="description" content="">

    <link id="themesLoad" rel="stylesheet" href="/webforcits/css/themes/<?php echo ($themes); ?>/easyui.css">
    <link rel="stylesheet" href="/webforcits/css/themes/icon.css">
    <link rel="stylesheet" href="/webforcits/css/themes/color.css">
    
    <!--[if lt IE 9]>
      <script src="/webforcits/js/html5shiv.min.js"></script>
      <script src="/webforcits/js/respond.min.js"></script>
    <![endif]-->

  </head>
  <body class="easyui-layout">
    <div data-options="region:'north'" style="height:100px;padding:10px;">
      <a href="/webforcits/home/main/logout" class="easyui-linkbutton" data-options="iconCls:'icon-no'" style="position:absolute;right:30px;top:28px;padding:10px 15px;">安全退出</a>
    </div>

    <div id="west" data-options="region:'west',split:true,title:'主菜单',href:'/webforcits/home/main/index'" style="width:200px;"></div>

    <div id="east" data-options="region:'east',split:true,collapsed:true,title:'个人菜单'" style="width:200px;padding:10px;"></div>

    <div id="center" class="easyui-tabs" data-options="region:'center',fit:true,border:false"></div>

    <!-- Javascript -->
    <script src="/webforcits/js/jquery-1.11.3.min.js"></script>
    <script src="/webforcits/js/jquery.easyui.min.js"></script>

    <script>
    $(document).ready(function(){
    	//初始化验证规则
    	$.extend($.fn.validatebox.defaults.rules, {
    	    isSame: {
    	        validator: function(){
    	            return $('input[name="newpassword"]').val() == $('input[name="confirmpassword"]').val();
    	        },
    			message: '两次密码不一致'
    	    },
    	    checkPassword: {
    	        validator: function(value){
    	        	var check = /^[a-zA-Z0-9_]{10,30}$/;
    	        	return check.test(value);
    	        },
    	        message: '10-30字符，英文、数字或下划线'
    	    },
    	    checkUsername: {
    	    	validator: function(value){
    	    		var check = /^[a-zA-Z0-9_]{10,20}$/;
    	    		return check.test(value);
    	    	},
    	    	message: '10-20字符，英文、数字或下划线'
        	},
        	checkIdcard: {
            	validator: function(value){
					var check = /^[0-9]{17}[0-9Xx]{1}$/;
					return check.test(value);
            	},
            	message: '18字符，末尾X大小写不限'
        	},
        	checkRealname: {
            	validator: function(value){
            		var check = /^[\u4e00-\u9fa5]{2,4}$/g;
            		return check.test(value);
        		},
        		message: '2-4字符，中文'
        	},
        	checkMobile: {
            	validator: function(value){
            		var check = /^[1][0-9]{10}$/;
            		return check.test(value);
        		},
        		message: '11位数'
        	},
        	checkBankcard: {
            	validator: function(value){
            		var check = /^[0-9]{16,19}$/;
            		return check.test(value);
        		},
        		message: '16-19位数'
        	},
        	checkBankname: {
            	validator: function(value){
            		var len = value.length;
            		if(len > 3 && len < 31){return true;}else{return false;}
        		},
        		message: '4-30字符'
        	},
        	checkNum: {
            	validator: function(value){
            		var check = /^[1-9]+[0-9]*$/;
            		return check.test(value);
        		},
        		message: '正整数'
        	},
        	checkNature: {
            	validator: function(value){
            		var check = /^[0-9]+$/;
            		return check.test(value);
        		},
        		message: '自然数'
        	},
        	checkTicket: {
            	validator: function(value){
            		var check = /^[0-9,]+$/;
            		return check.test(value);
        		},
        		message: '数字和逗号(,)'
        	},
        	checkPassport: {
            	validator: function(value){
            		var check = /^[a-zA-Z0-9]+$/;
            		return check.test(value);
        		},
        		message: '英文和数字'
        	},
        	checkFlight: {
            	validator: function(value){
            		var check = /^[A-Za-z]{2}[0-9]{4}$/;
            		return check.test(value);
        		},
        		message: '2英文+4数字'
        	}
    	});
    });
    </script>
  </body>
</html>