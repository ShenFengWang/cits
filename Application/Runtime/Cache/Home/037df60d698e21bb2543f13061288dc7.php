<?php if (!defined('THINK_PATH')) exit();?><div id="orderconfirm_table">
	<div id="orderconfirm_toolbar">
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-lock'" onClick="orderconfirm_confirm()">确认成单</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-man',plain:true" onClick="orderconfirm_view()">查看游客信息</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="orderconfirm_reload()">刷新</a>
	</div>
</div>
<div id="orderconfirm_window"></div>
<script>
$('#orderconfirm_table').datagrid({
	url:'/webforcits/home/operation/orderconfirmJson',
	method:'get',
	striped:true,
	singleSelect:true,
	width:document.body.clientWidth - 226,
	height:document.body.clientHeight - 128,
	toolbar:'#orderconfirm_toolbar',
	columns:[[
	    {field:'id',title:'订单号',width:80},
		{field:'createname',title:'制单人',width:100},
		{field:'ticket',title:'现金券编号',width:120},
		{field:'sender',title:'发券人姓名',width:120},
		{field:'routename',title:'旅游线路',width:150},
		{field:'starttime',title:'出发时间',width:150},
		{field:'provincename',title:'出发省份',width:80},
		{field:'cityname',title:'出发城市',width:100},
		{field:'leadername',title:'联系人姓名',width:80},
		{field:'mobile',title:'手机号码',width:120},
		{field:'email',title:'邮箱',width:150},
		{field:'address',title:'地址',width:200},
		{field:'createtime',title:'创建时间',width:150}
	]]
});

function orderconfirm_reload(){
	$('#orderconfirm_table').datagrid('reload');
}

function orderconfirm_view(){
	if($('#orderconfirm_table').datagrid('getSelected')){
		var selectedItem = $('#orderconfirm_table').datagrid('getSelected');
		$('#orderconfirm_window').window({
			title:'订单号：' + selectedItem.id + ' - 旅游线路：' + selectedItem.routename,
			href:'/webforcits/home/operation/orderconfirm/customer/' + selectedItem.id,
			width:1000,
			height:500
		});
	}
}

function orderconfirm_confirm(){
	if($('#orderconfirm_table').datagrid('getSelected')){
		$.messager.confirm({title:'请确认',msg:'是否确认该订单？',fn:function(callback){
			if(callback){
				var selectedItem = $('#orderconfirm_table').datagrid('getSelected');
				$.post('/webforcits/home/operation/orderconfirm',{id:selectedItem.id,identify:identify})
				.success(function(callback){
					if(callback != '1'){
						$.messager.show({title:'提示',msg:callback});
					}
					orderconfirm_reload();
				})
				.error(function(){
					$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
				});
			}
		}});
	}
}
</script>