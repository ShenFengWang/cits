<?php if (!defined('THINK_PATH')) exit();?><div style="text-align:center;">
<form id="themesChange" action="/webforcits/home/set/themes" method="post" style="margin:50px auto;">
<div style="margin-bottom:10px;">
<select id="themes" name="themes">
<?php if(is_array($themes)): foreach($themes as $key=>$tm): ?><option value="<?php echo ($tm); ?>"><?php echo ($tm); ?></option><?php endforeach; endif; ?>
</select>
</div>
<div style="margin-bottom:10px;">
<input type="hidden" name="identify">
<input type="submit" class="easyui-linkbutton" value="提交" style="padding:5px 10px;">
</div>
</form>
</div>
<script>
//初始化选中项
$('#themes').val('<?php echo ($themesSelected); ?>');

//初始化识别码
$('input[name="identify"]').val(identify);

//主题改变后预览
$('#themes').change(function(){
	var themesName = $(this).val();
	$('#themesLoad').attr('href','/webforcits/css/themes/' + themesName + '/easyui.css');
});

//提交修改
$('#themesChange').form({
	url:'/webforcits/home/set/themes',
	onSubmit:function(){
		
	},
	success:function(callback){
		if(callback == '1'){
			$.messager.show({'title':'提示','msg':'主题修改成功'});
		}else{
			$.messager.show({'title':'提示','msg':callback});
		}
	}
});
</script>