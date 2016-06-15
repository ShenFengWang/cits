<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-layout" style="height:100%;width:100%;">
	<div region="west" style="padding:10px;width:200px;">
		<ul id="ticketadmin_menu"></ul>
	</div>
	<div id="ticketadmin_main" region="center">
		<div id="ticketadmin_main_toolbar" style="display:none;">
<?php if($loginstatus >= $EXTRA_ticketadmin): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onClick="ticketadmin_add()">增加</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onClick="ticketadmin_remove()">删除</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onClick="ticketadmin_edit()">编辑</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onClick="ticketadmin_save()">保存</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onClick="ticketadmin_cancel()">取消</a><?php endif; ?>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="ticketadmin_reload()">刷新</a>
			<input id="ticketadmin_main_search">
		</div>
	</div>
</div>
<div id="ticketadmin_addticket"></div>
<script>
//初始化界面
var ticketadmin_main_height = document.body.clientHeight - 128;
var ticketadmin_main_width = document.body.clientWidth - 426;

$('#ticketadmin_menu').tree({
	url:'/webforcits/home/management/ticketadminJson/menu',
	method:'get',
	animate:true,
	onClick:function(node){
		$('#ticketadmin_main').datagrid({
			url:'/webforcits/home/management/ticketadminJson/main/' + node.id,
			method:'get',
			striped:true,
			singleSelect:true,
			toolbar:'#ticketadmin_main_toolbar',
			columns:[[
				{field:'id',title:'编号',width:120},
				{field:'sendername',title:'发券人',width:120,editor:{type:'combobox',options:{
					valueField:'id',textField:'name',method:'get',
					url:'/webforcits/home/management/ticketadminJson/sender',required:true,missingMessage:'此项必选',editable:false,
					onLoadSuccess:function(){
						$(this).combobox('setValue',$('#ticketadmin_main').datagrid('getSelected').sender);
					}
				}}},
				{field:'worth',title:'价值',width:120,editor:{type:'textbox',options:{required:true,missingMessage:'此项必填',validType:'checkNum'}}},
				{field:'remain',title:'余额',width:120,editor:{type:'textbox',options:{required:true,missingMessage:'此项必填',validType:'checkNum'}}},
				{field:'firsttime',title:'首次使用时间',width:150},
				{field:'lasttime',title:'最近使用时间',width:150},
				{field:'usedtimes',title:'使用次数',width:120},
				{field:'usedpeople',title:'使用人数',width:120}
			]],
			height:ticketadmin_main_height,
			width:ticketadmin_main_width,
			pagination:true,
			pageList:[10,20,30,50,70,100],
			pageSize:20,
			queryParams:{},
			onClickRow: function(rowIndex, rowData){
				if(ticketadmin_editIndex != rowIndex){
					if(ticketadmin_endEdit()){
						$('#ticketadmin_main').datagrid('cancelEdit',ticketadmin_editIndex);
						ticketadmin_editIndex = rowIndex;
					}else{
						$('#ticketadmin_main').datagrid('selectRow', ticketadmin_editIndex);
					}
				}
			},
			onAfterEdit: function(rowIndex, rowData, changes){
				ticketadmin_ajax($('#ticketadmin_main').datagrid('getSelected').id,'edit',rowData);
			}
		});
		$('#ticketadmin_main_search').searchbox({
			value:'',
			prompt:'现金券编号',
			searcher:function(value,name){
				if(value == ''){
					$('#ticketadmin_main').datagrid('options').queryParams = {};
					$('#ticketadmin_main').datagrid('reload');
				}else{
					var check = /^[1-9]+[0-9]*$/;
					if(check.test(value)){
						$('#ticketadmin_main').datagrid('options').queryParams.ticketid = value;
						$('#ticketadmin_main').datagrid('reload');
					}
				}
			}
		});
	}
});

<?php if($loginstatus >= $EXTRA_ticketadmin): ?>//添加现金券界面
$('#ticketadmin_addticket').window({
	title:'新增现金券',
	collapsible:false,
	minimizable:false,
	maximizable:true,
	closable:true,
	closed:true,
	href:'/webforcits/home/management/ticketadmin/add',
	resizable:false,
	width:500,
	height:800,
	modal:true
});

//添加现金券按钮
function ticketadmin_add(){
	$('#ticketadmin_addticket').window('open');
}

//初始化编辑状态
var ticketadmin_editIndex = undefined;
function ticketadmin_endEdit(){
	if(ticketadmin_editIndex == undefined){return true;}
	if($('#ticketadmin_main').datagrid('validateRow',ticketadmin_editIndex)){
		return true;
	}else{
		return false;
	}
}

//删除
function ticketadmin_remove(){
	if(ticketadmin_endEdit()){
		var selectedItem = $('#ticketadmin_main').datagrid('getSelected');
		if(selectedItem){
			$.messager.confirm({title:'提示',msg:'是否删除该现金券？',fn:function(callback){
				if(callback){
					ticketadmin_ajax(selectedItem.id,'remove');
				}
			}});
		}
	}
}

//编辑
function ticketadmin_edit(){
	if(ticketadmin_endEdit() && $('#ticketadmin_main').datagrid('getSelected')){
		$('#ticketadmin_main').datagrid('selectRow',ticketadmin_editIndex);
		$('#ticketadmin_main').datagrid('beginEdit',ticketadmin_editIndex);
	}
}

//保存
function ticketadmin_save(){
	if(ticketadmin_editIndex != undefined){
		if(ticketadmin_endEdit()){
			$('#ticketadmin_main').datagrid('acceptChanges');
			ticketadmin_editIndex = undefined;
		}
	}
}

//取消
function ticketadmin_cancel(){
	$('#ticketadmin_main').datagrid('rejectChanges');
	ticketadmin_editIndex = undefined;
}

//提交数据
function ticketadmin_ajax(id,action,data){
	$.post('/webforcits/home/management/ticketadmin',{ticketid:id,action:action,data:data,identify:identify})
	.success(function(callback){
		if(callback != '1'){
			$.messager.show({title:'提示',msg:callback});
		}
		$('#ticketadmin_main').datagrid('reload');
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
	});
}<?php endif; ?>

function ticketadmin_reload(){
	$('#ticketadmin_menu').tree('reload');
	$('#ticketadmin_main').datagrid('reload');
}
</script>