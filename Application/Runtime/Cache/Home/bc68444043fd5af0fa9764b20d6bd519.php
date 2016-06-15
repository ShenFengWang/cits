<?php if (!defined('THINK_PATH')) exit();?><div id="myloginfo_table"></div>
<script>
$('#myloginfo_table').datagrid({
	url:'/webforcits/log/myloginfoJson',
	method:'get',
	striped:true,
	singleSelect:true,
	width:document.body.clientWidth - 227,
	height:document.body.clientHeight - 128,
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
</script>