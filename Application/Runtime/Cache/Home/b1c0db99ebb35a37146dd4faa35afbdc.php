<?php if (!defined('THINK_PATH')) exit();?><div style="text-align:center;margin-top:50px;">
<form id="Set_password_change" method="post" style="margin:auto;">
<div style="margin-bottom:10px;">原始密码：<input type="password" name="oldpassword" class="easyui-textbox" data-options="required:true,validType:'checkPassword',missingMessage:'此项必填'"></div>
<div style="margin-bottom:10px;">新设密码：<input type="password" name="newpassword" class="easyui-textbox" data-options="required:true,validType:'checkPassword',missingMessage:'此项必填'"></div>
<div style="margin-bottom:10px;">再次确认：<input type="password" name="confirmpassword" class="easyui-textbox" data-options="required:true,validType:'isSame',missingMessage:'此项必填'"></div>
<div><input type="hidden" name="identify"><input type="submit" class="easyui-linkbutton" value="提交" style="margin-right:20px;padding:5px 10px;"></div>
</form>
</div>
<script>
//验证并提交
$('#Set_password_change').form({
    url:'/webforcits/home/set/password_change',
    onSubmit: function(){
    	return $(this).form('enableValidation').form('validate');
    },
    success:function(data){
    	$.messager.show({'title':'提示','msg':data});
        $('#Set_password_change').form('clear');
    }
});

//识别码填写
$('input[name="identify"]').val(identify);
</script>