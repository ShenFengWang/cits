<?php if (!defined('THINK_PATH')) exit();?><div id="extraset_table">
	<div id="extraset_toolbar" style="display:none;">
<?php if($loginstatus >= $EXTRA_extraset): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-edit'" onClick="extraset_edit()">编辑</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-save'" onClick="extraset_save()">保存</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-cancel'" onClick="extraset_cancel()">取消</a><?php endif; ?>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-reload'" onClick="extraset_reload()">刷新</a>
	</div>
</div>

<script>
//初始化编辑对象
var extraset_editIndex = undefined;

//初始化表格
$('#extraset_table').datagrid({
	url:'/webforcits/home/set/extrasetJson',
	method:'get',
	striped:true,
	singleSelect:true,
	toolbar:'#extraset_toolbar',
	height:document.body.clientHeight - 128,
	width:document.body.clientWidth - 228,
	columns:[[
		{field:'name',title:'名称',width:120},
		{field:'tip',title:'说明',width:400},
		{field:'value',title:'值',width:120,editor:{type:'textbox',options:{required:true,missingMessage:'此项必填',validType:'checkNature'}}}
	]],
	onClickRow: function(rowIndex, rowData){
		if(extraset_editIndex != rowIndex){
			if(extraset_endEdit()){
				$('#extraset_table').datagrid('cancelEdit',extraset_editIndex);
				extraset_editIndex = rowIndex;
			}else{
				$('#extraset_table').datagrid('selectRow', extraset_editIndex);
			}
		}
	},
	onAfterEdit: function(rowIndex, rowData, changes){
		extraset_ajax($('#extraset_table').datagrid('getSelected').id,rowData,'edit');
	}
});

//编辑状态
function extraset_endEdit(){
	if(extraset_editIndex == undefined)return true;
	if($('#extraset_table').datagrid('validateRow',extraset_editIndex)){
		return true;
	}else{
		return false;
	}
}

//刷新
function extraset_reload(){
	$('#extraset_table').datagrid('reload');
	extraset_editIndex = undefined;
}

<?php if($loginstatus >= $EXTRA_extraset): ?>//编辑
function extraset_edit(){
	if(extraset_endEdit() && $('#extraset_table').datagrid('getSelected')){
		$('#extraset_table').datagrid('selectRow',extraset_editIndex);
		$('#extraset_table').datagrid('beginEdit',extraset_editIndex);
	}
}

//保存
function extraset_save(){
	if(extraset_editIndex != undefined){
		if(extraset_endEdit()){
			$('#extraset_table').datagrid('acceptChanges');
			extraset_editIndex = undefined;
		}
	}
}

//取消
function extraset_cancel(){
	$('#extraset_table').datagrid('rejectChanges');
	extraset_editIndex = undefined;
}

//提交数据
function extraset_ajax(id,data,action){
	$.post('/webforcits/home/set/extraset',{id:id,data:data,action:action,identify:identify})
	.success(function(callback){
		if(callback != '1'){
			$.messager.show({title:'提示',msg:callback});
		}
		extraset_reload();
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
	});
}<?php endif; ?>
</script>