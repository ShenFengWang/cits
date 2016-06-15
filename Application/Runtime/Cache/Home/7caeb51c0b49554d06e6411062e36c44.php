<?php if (!defined('THINK_PATH')) exit();?>	<div class="easyui-accordion" data-options="fit:true,border:false">
<?php if(is_array($mainmenu)): foreach($mainmenu as $key=>$menu): ?><div title="<?php echo ($key); ?>" data-options="iconCls:'icon-ok'" style="padding:10px;">
<?php if(is_array($menu)): foreach($menu as $key=>$list): if($list['switch']): ?><div><a href="/webforcits/<?php echo ($list["url"]); ?>" class="easyui-linkbutton" style="width:173px;margin-bottom:10px;"><?php echo ($list["name"]); ?></a></div><?php endif; endforeach; endif; ?>
		</div><?php endforeach; endif; ?>
	</div>
<script>
//识别码
var identify = '<?php echo ($identify); ?>';

//菜单点击
$('#west a').click(function(){
	var center = $('#center');
	if(center.tabs('exists',$(this).text())){
		center.tabs('select',$(this).text());
	}else{
		center.tabs('add',{
			title: $(this).text(),
			href: $(this).attr('href'),
			method:'get',
			closable: true,
			fit:true
		});
	}
	return false;
});
</script>