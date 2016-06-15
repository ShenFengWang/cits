<?php if (!defined('THINK_PATH')) exit();?><div style="text-align:center;padding:50px;">
<form id="Management_password_reset" class="easyui-form" method="post" style="margin:auto;">
	<div style="margin-bottom:10px;">成员用户名</div>
	<div style="margin-bottom:10px;"><input type="text" class="easyui-textbox" name="username" data-options="required:true,validType:'checkUsername',missingMessage:'此项必填'"></div>
	<div style="margin-bottom:10px;"><input type="hidden" name="identify"><input type="submit" class="easyui-linkbutton" value="提交" style="padding:5px 10px;"></div>
	<div style="margin-bottom:10px;"><span id="password_reset_callback" style="color:red;"></span></div>
</form>
</div>
<script>
//验证并提交
$('#Management_password_reset').form({
    url:'/webforcits/home/management/password_reset',
    onSubmit: function(){
    	return $(this).form('enableValidation').form('validate');
    },
    success:function(callback){
        if(callback == 1){
        	$('#password_reset_callback').text('重置成功！新密码为：a1b2c3d4e5，请通知组员尽快修改密码！');
        	$('#Management_password_reset').form('clear');
        }else{
        	$.messager.show({'title':'提示','msg':callback});
        	$('password_reset_callback').text();
        }
    }
});

//识别码填写
$('input[name="identify"]').val(identify);
</script>