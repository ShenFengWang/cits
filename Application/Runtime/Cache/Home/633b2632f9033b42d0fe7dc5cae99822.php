<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-layout" style="height:100%;width:100%;">
	<div region="west" style="padding:10px;width:200px;">
		<ul id="sendercut_menu"></ul>
	</div>
	<div id="sendercut_main" region="center">
		<div id="sendercut_main_toolbar" style="display:none;">
<?php if($loginstatus >= $EXTRA_sendercut): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onClick="sendercut_add()">增加</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onClick="sendercut_remove()">删除</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onClick="sendercut_edit()">编辑</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onClick="sendercut_save()">保存</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onClick="sendercut_cancel()">取消</a><?php endif; ?>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="sendercut_reload()">刷新</a>
		</div>
	</div>
</div>
<script>
//初始化发券人ID
var sendercut_sender = undefined;

$('#sendercut_menu').tree({
	url:'/webforcits/home/management/sendercutJson/menu',
	method:'get',
	animate:true,
	onClick:function(node){
		sendercut_sender = node.id;
		$('#sendercut_main').datagrid({
			url:'/webforcits/home/management/sendercutJson/main/' + node.id,
			method:'get',
			striped:true,
			singleSelect:true,
			width:document.body.clientWidth - 426,
			height:document.body.clientHeight - 128,
			toolbar:'#sendercut_main_toolbar',
			columns:[[
				{field:'routename',title:'线路名称',width:400,editor:{type:'combobox',options:{required:true,missingMessage:'此项必选',editable:false,
				valueField:'id',textField:'name',url:'/webforcits/home/management/sendercutJson/route',method:'get',onLoadSuccess:function(){
					$(this).combobox('setValue',$('#sendercut_main').datagrid('getSelected').route);
				}}}},
				{field:'cut',title:'提成金额',width:200,editor:{type:'textbox',options:{required:true,missingMessage:'此项必填',validType:'checkNum'}}}
			]],
			onClickRow: function(rowIndex, rowData){
				if(sendercut_editIndex != rowIndex){
					if(sendercut_endEdit()){
						$('#sendercut_main').datagrid('cancelEdit',sendercut_editIndex);
						sendercut_editIndex = rowIndex;
					}else{
						$('#sendercut_main').datagrid('selectRow', sendercut_editIndex);
					}
				}
			},
			onAfterEdit: function(rowIndex, rowData, changes){
				if(sendercut_action == 'add'){
					var id = 0;
				}else{
					var id = $('#sendercut_main').datagrid('getSelected').id;
				}
				if(sendercut_sender != undefined){
					sendercut_ajax(id,rowData,'edit');
				}
			}
		});
	}
});

//初始化编辑
var sendercut_editIndex = undefined;
var sendercut_action = undefined;

function sendercut_endEdit(){
	if(sendercut_editIndex == undefined){return true;}
	if($('#sendercut_main').datagrid('validateRow',sendercut_editIndex)){
		return true;
	}else{
		return false;
	}
}

<?php if($loginstatus >= $EXTRA_sendercut): ?>//增加
function sendercut_add(){
	if(sendercut_endEdit()){
		$('#sendercut_main').datagrid('cancelEdit',sendercut_editIndex);
		$('#sendercut_main').datagrid('appendRow',{});
		sendercut_editIndex = $('#sendercut_main').datagrid('getRows').length - 1;
		sendercut_action = 'add';
		$('#sendercut_main').datagrid('selectRow', sendercut_editIndex).datagrid('beginEdit', sendercut_editIndex);
	}
}

//删除
function sendercut_remove(){
	if(sendercut_endEdit()){
		var selectedItem = $('#sendercut_main').datagrid('getSelected');
		if(selectedItem){
			$.messager.confirm({title:'提示',msg:'是否删除该提成规则？',fn:function(callback){
				if(callback){
					sendercut_ajax(selectedItem.id,selectedItem.route,'remove');
				}
			}});
		}
	}
}

//编辑
function sendercut_edit(){
	if(sendercut_endEdit() && $('#sendercut_main').datagrid('getSelected')){
		$('#sendercut_main').datagrid('selectRow',sendercut_editIndex);
		$('#sendercut_main').datagrid('beginEdit',sendercut_editIndex);
		sendercut_action = 'edit';
	}
}

//保存
function sendercut_save(){
	if(sendercut_editIndex != undefined){
		if(sendercut_endEdit()){
			$('#sendercut_main').datagrid('acceptChanges');
			sendercut_editIndex = undefined;
			sendercut_action = undefined;
		}
	}
}

//取消
function sendercut_cancel(){
	$('#sendercut_main').datagrid('rejectChanges');
	sendercut_editIndex = undefined;
	sendercut_action = undefined;
}

//提交数据
function sendercut_ajax(id,data,action){
	$.post('/webforcits/home/management/sendercut',{id:id,data:data,action:action,sender:sendercut_sender,identify:identify})
	.success(function(callback){
		if(callback != '1'){
			$.messager.show({title:'提示',msg:callback});
		}
		sendercut_reload();
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
	});
}<?php endif; ?>

//刷新
function sendercut_reload(){
	$('#sendercut_main').datagrid('reload');
	sendercut_editIndex = undefined;
	sendercut_action = undefined;
}

</script>