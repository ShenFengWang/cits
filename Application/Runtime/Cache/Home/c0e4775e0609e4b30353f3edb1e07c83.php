<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-layout" style="width:100%;height:100%">
	<div region="west" style="width:200px;">
		<ul id="memberloginfo_menu"></ul>
	</div>
	<div region="center">
		<div id="memberloginfo_main"></div>
	</div>
</div>
<script>
$('#memberloginfo_menu').tree({
	url:'/webforcits/home/log/memberloginfoJson/menu',
	method:'get',
	animate:true,
	onClick:function(node){
		if(node.id == 0)return false;
		$('#memberloginfo_main').datagrid({
			url:'/webforcits/log/memberloginfoJson/main/' + node.id,
			method:'get',
			striped:true,
			singleSelect:true,
			width:document.body.clientWidth - 428,
			height:document.body.clientHeight - 129,
			pagination:true,
			pageList:[10,20,30,50,70,100],
			pageSize:20,
			columns:[[
				{field:'username',title:'用户名',width:120},
				{field:'user_id',title:'用户ID',width:120},
				{field:'ip',title:'登录IP',width:120},
				{field:'log_time',title:'登录时间',width:150}
			]]
		});
	}
});
</script>