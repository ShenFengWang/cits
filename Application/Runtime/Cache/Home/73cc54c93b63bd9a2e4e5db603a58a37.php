<?php if (!defined('THINK_PATH')) exit();?><div id="myoperation_table"></div>
<script>
$('#myoperation_table').datagrid({
	url:'/webforcits/log/myoperationJson',
	method:'get',
	striped:true,
	singleSelect:true,
	width:document.body.clientWidth - 227,
	height:document.body.clientHeight - 128,
	pagination:true,
	pageList:[10,20,30,50,70,100],
	pageSize:20,
	columns:[[
		{field:'action',title:'操作',width:120},
		{field:'logtime',title:'时间',width:150},
		{field:'target',title:'详情',width:1000}
	]]
});
</script>