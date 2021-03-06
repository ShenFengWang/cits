<?php if (!defined('THINK_PATH')) exit();?><div id="myorder_accordion">
	<div title="未确认">
		<div id="myorder_unconfirm">
			<div id="myorder_unconfirm_toolbar" style="display:none;">
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-man',plain:true" onClick="myorder_unconfirm_view()">查看游客信息</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onClick="myorder_remove()">删除</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onClick="myorder_edit()">编辑</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onClick="myorder_save()">保存</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onClick="myorder_cancel()">取消</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="myorder_unconfirm_reload()">刷新</a>
			</div>
		</div>
	</div>
	<div title="已确认">
		<div id="myorder_confirmed">
			<div id="myorder_confirmed_toolbar"  style="display:none;">
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-man',plain:true" onClick="myorder_confirmed_view()">查看游客信息</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="myorder_confirmed_reload()">刷新</a>
			</div>
		</div>
	</div>
</div>
<div id="myorder_view"></div>
<script>
$('#myorder_accordion').accordion({
	width:document.body.clientWidth - 227,
	height:document.body.clientHeight - 128
});

$('#myorder_unconfirm').datagrid({
	url:'/webforcits/home/operation/myorderJson/unconfirm',
	method:'get',
	fit:true,
	striped:true,
	singleSelect:true,
	toolbar:'#myorder_unconfirm_toolbar',
	columns:[[
	    {field:'id',title:'订单号',width:100},
		{field:'ticket',title:'现金券编号',width:100},
		{field:'sender',title:'发券人姓名',width:100},
		{field:'routename',title:'旅游线路',width:150},
		{field:'starttime',title:'出发时间',width:100,editor:{type:'datebox',options:{required:true,missingMessage:'此项必选',editable:false,formatter:function(date){
			var y = date.getFullYear();
			var m = date.getMonth()+1;
			var d = date.getDate();
			return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
		},parser:function(s){
			if (!s) return new Date();
			var ss = (s.split('-'));
			var y = parseInt(ss[0],10);
			var m = parseInt(ss[1],10);
			var d = parseInt(ss[2],10);
			if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
				return new Date(y,m-1,d);
			} else {
				return new Date();
			}
		}
		}}},
		{field:'provincename',title:'出发省份',width:100},
		{field:'cityname',title:'出发城市',width:100},
		{field:'leadername',title:'联系人姓名',width:80,editor:{type:'textbox',options:{required:true,missingMessage:'此项必填',validType:'checkRealname'}}},
		{field:'mobile',title:'手机号码',width:100,editor:{type:'textbox',options:{validType:'checkMobile'}}},
		{field:'email',title:'邮箱',width:150,editor:{type:'textbox',options:{validType:'email',invalidMessage:'邮箱格式错误'}}},
		{field:'address',title:'地址',width:200,editor:{type:'textbox'}},
		{field:'createtime',title:'创建时间',width:150}
	]],
	pagination:true,
	pageList:[10,20,30,50,70,100],
	pageSize:20,
	queryParams:{},
	onClickRow: function(rowIndex, rowData){
		if(myorder_editIndex != rowIndex){
			if(myorder_endEdit()){
				$('#myorder_unconfirm').datagrid('cancelEdit',myorder_editIndex);
				myorder_editIndex = rowIndex;
			}else{
				$('#myorder_unconfirm').datagrid('selectRow', myorder_editIndex);
			}
		}
	},
	onAfterEdit: function(rowIndex, rowData, changes){
		myorder_ajax($('#myorder_unconfirm').datagrid('getSelected').id,rowData,'edit');
	}
});

function myorder_unconfirm_view(){
	if(myorder_endEdit() && $('#myorder_unconfirm').datagrid('getSelected')){
		var selectedItem = $('#myorder_unconfirm').datagrid('getSelected');
		$('#myorder_view').window({
			title:'订单号:' + selectedItem.id + ' - 线路:' + selectedItem.routename,
			width:1000,
			height:500,
			maximizable:true,
			closable:true,
			href:'/webforcits/home/operation/myorder/unconfirm/' + selectedItem.id,
			closed:false
		});
	}
}

//初始化编辑状态
var myorder_editIndex = undefined;
function myorder_endEdit(){
	if(myorder_editIndex == undefined){return true;}
	if($('#myorder_unconfirm').datagrid('validateRow',myorder_editIndex)){
		return true;
	}else{
		return false;
	}
}

//删除
function myorder_remove(){
	if(myorder_endEdit()){
		var selectedItem = $('#myorder_unconfirm').datagrid('getSelected');
		if(selectedItem){
			$.messager.confirm({title:'提示',msg:'是否删除该订单？',fn:function(callback){
				if(callback){
					myorder_ajax(selectedItem.id,selectedItem.createid,'remove');
				}
			}});
		}
	}
}

//编辑
function myorder_edit(){
	if(myorder_endEdit() && $('#myorder_unconfirm').datagrid('getSelected')){
		$('#myorder_unconfirm').datagrid('selectRow',myorder_editIndex);
		$('#myorder_unconfirm').datagrid('beginEdit',myorder_editIndex);
	}
}

//保存
function myorder_save(){
	if(myorder_editIndex != undefined){
		if(myorder_endEdit()){
			$('#myorder_unconfirm').datagrid('acceptChanges');
			myorder_editIndex = undefined;
		}
	}
}

//取消
function myorder_cancel(){
	$('#myorder_unconfirm').datagrid('rejectChanges');
	myorder_editIndex = undefined;
}

//提交数据
function myorder_ajax(id,data,action){
	$.post('/webforcits/home/operation/myorder',{id:id,data:data,action:action,identify:identify})
	.success(function(callback){
		if(callback != '1'){
			$.messager.show({title:'提示',msg:callback});
		}
		$('#myorder_unconfirm').datagrid('reload');
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
	});
}

//刷新
function myorder_unconfirm_reload(){
	$('#myorder_unconfirm').datagrid('reload');
	myorder_editIndex = undefined;
}


$('#myorder_confirmed').datagrid({
	url:'/webforcits/home/operation/myorderJson/confirmed',
	method:'get',
	fit:true,
	striped:true,
	singleSelect:true,
	toolbar:'#myorder_confirmed_toolbar',
	columns:[[
	    {field:'id',title:'订单号',width:100},
		{field:'ticket',title:'现金券编号',width:100},
		{field:'sender',title:'发券人姓名',width:100},
		{field:'routename',title:'旅游线路',width:150},
		{field:'customernum',title:'人数',width:80},
		{field:'starttime',title:'出发时间',width:100},
		{field:'provincename',title:'出发省份',width:100},
		{field:'cityname',title:'出发城市',width:100},
		{field:'leadername',title:'联系人姓名',width:80},
		{field:'mobile',title:'手机号码',width:100},
		{field:'email',title:'邮箱',width:150},
		{field:'address',title:'地址',width:200},
		{field:'confirmtime',title:'确认时间',width:150}
	]],
	pagination:true,
	pageList:[10,20,30,50,70,100],
	pageSize:20,
	queryParams:{}
});

//刷新
function myorder_confirmed_reload(){
	$('#myorder_confirmed').datagrid('reload');
}

function myorder_confirmed_view(){
	if($('#myorder_confirmed').datagrid('getSelected')){
		var selectedItem = $('#myorder_confirmed').datagrid('getSelected');
		$('#myorder_view').window({
			title:'订单号:' + selectedItem.id + ' - 线路:' + selectedItem.routename,
			width:1000,
			height:500,
			maximizable:true,
			closable:true,
			href:'/webforcits/home/operation/myorder/confirmed/' + selectedItem.id,
			closed:false
		});
	}
}
</script>