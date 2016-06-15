<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-layout" style="width:100%;height:100%">
	<div region="west" style="width:200px;">
		<ul id="memberoperation_menu"></ul>
	</div>
	<div region="center">
		<div id="memberoperation_main"></div>
	</div>
</div>
<script>
$('#memberoperation_menu').tree({
	url:'/webforcits/home/log/memberoperationJson/menu',
	method:'get',
	animate:true,
	onClick:function(node){
		if(node.id == 0)return false;
		$('#memberoperation_main').datagrid({
			url:'/webforcits/log/memberoperationJson/main/' + node.id,
			method:'get',
			striped:true,
			singleSelect:true,
			width:document.body.clientWidth - 428,
			height:document.body.clientHeight - 129,
			pagination:true,
			pageList:[10,20,30,50,70,100],
			pageSize:20,
			columns:[[
				{field:'action',title:'操作',width:120},
				{field:'logtime',title:'时间',width:150},
				{field:'target',title:'详情',width:1000}
			]]
		});
	}
});
</script>