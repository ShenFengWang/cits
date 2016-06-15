<?php if (!defined('THINK_PATH')) exit();?><table id="reg_cert_table"></table>
<div id="reg_cert_toolbar" style="display:none;">
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-man',plain:true" onclick="reg_cert_idcard();">新增身份</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="reg_cert_random();">新增随机</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="reg_cert_remove();">删除</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="reg_cert_edit();">编辑</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="reg_cert_save();">保存</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onclick="reg_cert_cancel();">取消</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick="reg_cert_reload();">刷新</a>
</div>
<script>
//初始化编辑ID
var reg_cert_editIndex = undefined;

//初始化动作
var reg_cert_action = undefined;

//初始化注册凭证信息
$('#reg_cert_table').datagrid({
	url:'/webforcits/home/management/reg_certJson',
	method:'get',
	striped:true,
	singleSelect:true,
	height:document.body.clientHeight - 128,
	width:document.body.clientWidth - 228,
	toolbar:'#reg_cert_toolbar',
	columns:[[
		{field:'randomnumber',title:'随机码',width:'100',sortable:true},
		{field:'idcardnumber',title:'身份证',width:'200',sortable:true,editor:{type:'validatebox',options:{required:true,missingMessage:'此项必填',validType:'checkIdcard'}}},
		{field:'realname',title:'真实姓名',width:'100',editor:{type:'validatebox',options:{validType:'checkRealname'}}},
		{field:'bankcard',title:'银行卡号码',width:'200',editor:{type:'validatebox',options:{validType:'checkBankcard'}}},
		{field:'bankname',title:'开户行信息',width:'200',editor:{type:'validatebox',options:{validType:'checkBankname'}}},
		{field:'mobile',title:'手机号码',width:'150',editor:{type:'validatebox',options:{validType:'checkMobile'}}},
		{field:'authority_name',title:'基础权限',width:'100',editor:{type:'combobox',options:{valueField:'id',textField:'name',method:'get',url:'/webforcits/home/management/reg_certJson/authority',required:true,missingMessage:'此项必选',editable:false,onLoadSuccess:function(){
			$(this).combobox('setValue',$('#reg_cert_table').datagrid('getSelected').authority);
		}}}},
		{field:'loginstatus_name',title:'角色',width:'100',editor:{type:'combobox',options:{valueField:'id',textField:'name',method:'get',url:'/webforcits/home/management/reg_certJson/actor',required:true,missingMessage:'此项必选',editable:false,onChange:function(value){
			if(value != undefined){
				var ingroup = $('#reg_cert_table').datagrid('getEditor',{index:reg_cert_editIndex,field:'ingroup_name'}).target;
				ingroup.combobox({valueField:'id',textField:'name',url:'/webforcits/home/management/reg_certJson/ingroup/' + value,method:'get',editable:false,onLoadSuccess:function(){
					if(value != $('#reg_cert_table').datagrid('getSelected').loginstatus){$(this).combobox('setValue','');}else{$(this).combobox('setValue',$('#reg_cert_table').datagrid('getSelected').ingroup);}
				}});
			}
		},onLoadSuccess:function(){
			$(this).combobox('setValue',$('#reg_cert_table').datagrid('getSelected').loginstatus);
		}}}},
		{field:'ingroup_name',title:'所在区域/组',width:'138',editor:{type:'combobox',options:{required:true,missingMessage:'此项必选',editable:false}}}
	]],
	onAfterEdit: function(rowIndex, rowData, changes){
		if(reg_cert_editIndex != undefined && reg_cert_action != undefined){
			if(reg_cert_action == 'add'){
				reg_cert_ajax('0','edit',rowData);
			}
			if(reg_cert_action == 'edit'){
				reg_cert_ajax($('#reg_cert_table').datagrid('getSelected').id,'edit',rowData);
			}
		}
	},
	onClickRow: function(rowIndex, rowData){
		if(reg_cert_editIndex != rowIndex){
			if(reg_cert_endEdit()){
				$('#reg_cert_table').datagrid('cancelEdit',reg_cert_editIndex);
				reg_cert_editIndex = rowIndex;
			}else{
				$('#reg_cert_table').datagrid('selectRow', reg_cert_editIndex);
			}
		}
	}
});

//获取编辑状态
function reg_cert_endEdit(){
	if(reg_cert_editIndex == undefined){return true;}
	if($('#reg_cert_table').datagrid('validateRow',reg_cert_editIndex)){
		return true;
	}else{
		return false;
	}
}

//提交数据
function reg_cert_ajax(id,action,data){
	//alert('id:' + id +', action:' + action + ', data:' + data + ', identify:' + identify);
	//$.each(data,function(k,v){alert(k + ' : ' + v);});
	$.post('/webforcits/management/reg_cert',{id:id,action:action,data:data,identify:identify})
	.success(function(callback){
		if(callback == 1){
			reg_cert_reload();
		}else{
			$.messager.show({title:'提示',msg:callback});
			reg_cert_reload();
			document.write(callback);
		}
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
		reg_cert_reload();
	});
}

//刷新
function reg_cert_reload(){
	$('#reg_cert_table').datagrid('reload');
	reg_cert_action = undefined;
	reg_cert_editIndex = undefined;
}

//新增随机
function reg_cert_random(){
	$.messager.confirm({title:'请确认',msg:'是否新增一个随机码？',fn:function(callback){
		if(callback){
			reg_cert_ajax('0','addrandom','');
		}
	}});
}

//新增身份
function reg_cert_idcard(){
	if(reg_cert_endEdit()){
		$('#reg_cert_table').datagrid('appendRow',{});
		reg_cert_editIndex = $('#reg_cert_table').datagrid('getRows').length - 1;
		reg_cert_action = 'add';
		$('#reg_cert_table').datagrid('selectRow', reg_cert_editIndex).datagrid('beginEdit', reg_cert_editIndex);
	}
}

//编辑身份
function reg_cert_edit(){
	if(reg_cert_endEdit() && $('#reg_cert_table').datagrid('getSelected')){
		var SelectedItem = $('#reg_cert_table').datagrid('getSelected');
		if(SelectedItem.randomnumber != '')return false;
		reg_cert_action = 'edit';
		$('#reg_cert_table').datagrid('selectRow',reg_cert_editIndex);
		$('#reg_cert_table').datagrid('beginEdit',reg_cert_editIndex);
	}
}

//保存
function reg_cert_save(){
	if(reg_cert_editIndex != undefined){
		if(reg_cert_endEdit()){
			$('#reg_cert_table').datagrid('acceptChanges');
			reg_cert_action = undefined;
			reg_cert_editIndex = undefined;
		}
	}
}

//删除
function reg_cert_remove(){
	if(reg_cert_editIndex == undefined){return false;}
	var selectedItem = $('#reg_cert_table').datagrid('getSelected');
	if(selectedItem && selectedItem.id != undefined){
		$.messager.confirm({title:'请确认',msg:'是否删除当前凭证？',fn:function(callback){
			if(callback){reg_cert_ajax(selectedItem.id,'remove','');}
		}});
	}
}

//取消
function reg_cert_cancel(){
	$('#reg_cert_table').datagrid('rejectChanges');
	reg_cert_editIndex = undefined;
	reg_cert_action = undefined;
}
</script>