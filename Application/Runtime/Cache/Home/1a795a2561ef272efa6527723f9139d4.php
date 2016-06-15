<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-panel" style="width:200px;height:100%;padding:10px;position:fixed;">
	<div style="text-align:center;margin-bottom:10px;">
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add'" title="新增权限" onclick="authorityadminmenu_add();" style="margin-right:10px;"></a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove'" title="删除权限" onclick="authorityadminmenu_remove();" style="margin-right:10px;"></a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit'" title="重命名" onclick="authorityadminmenu_edit();" style="margin-right:10px;"></a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload'" title="刷新" onclick="authorityadminmenu_reload();"></a>
	</div>
	<ul id="authorityadminmenu"></ul>
</div>
<div style="margin-left:200px;">
	<table id="authorityadminmain"></table>
	<div id="authorityadminmaintoolbar" style="display:none;">
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" onclick="authorityadminmain_ok_clear('ok');">启用</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-clear',plain:true" onclick="authorityadminmain_ok_clear('clear');">禁用</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick="authorityadminmain_reload();">刷新</a>
	</div>
</div>

<script>
//初始化参数
var authorityadmin_authorityid = false;

//权限列表读取
$('#authorityadminmenu').tree({
	url:'/webforcits/home/management/authorityadmin/menu',
	method:'get',
	onClick: function(node){
		authorityadmin_authorityid = node.id;
		$('#authorityadminmain').datagrid({
			url: '/webforcits/management/authorityadminJson/' + node.id,
			method: 'get',
			singleSelect:true,
			striped:true,
			height:document.body.clientHeight - 128,
			width:document.body.clientWidth - 428,
			toolbar:'#authorityadminmaintoolbar',
			columns:[[{field:'name',title:'权限名称',width:'200'},{field:'value',title:'启用',width:'100'}]]
		});
	}
});

//权限列表操作
//新增权限
function authorityadminmenu_add(){
	$.messager.prompt({title:'请输入',msg:'新的权限名称为：',fn:function(val){
		if(val != '' && val != undefined)authorityadmin_ajax('0',val,'add');
	}});
}

//删除权限
function authorityadminmenu_remove(){
	var menuSelected = $('#authorityadminmenu').tree('getSelected');
	if(menuSelected){
		if(menuSelected.attributes.num == '0'){
			$.messager.confirm({title:'请确认',msg:'是否删除权限[' + menuSelected.attributes.name + ']？',fn:function(isDel){
				if(isDel){
					authorityadmin_ajax(menuSelected.id,menuSelected.attributes.name,'del');
				}
			}});
		}else{
			$.messager.show({title:'提示',msg:'使用该权限的成员数量必须为零才能删除'});
		}
	}
}

//重命名
function authorityadminmenu_edit(){
	var menuSelected = $('#authorityadminmenu').tree('getSelected');
	if(menuSelected){
		$.messager.prompt({title:'请输入',msg:'权限重命名为：',fn:function(val){
			if(val != '' && val != menuSelected.attributes.name && val != undefined){
				authorityadmin_ajax(menuSelected.id,val,'edit');
			}
		}});
	}
}

//刷新
function authorityadminmenu_reload(){
	$('#authorityadminmenu').tree('reload');
}

//公共：提交数据
function authorityadmin_ajax(menuid,name,action){
	//alert(menuid + ' ' + name + ' ' + action);return false;
	$.post('/webforcits/home/management/authorityadmin',{menuid:menuid,name:name,action:action,identify:identify})
	.success(function(callback){
		if(callback == '1'){
			authorityadminmenu_reload();
		}else if(callback == '2'){
			authorityadminmain_reload();
		}else{
			$.messager.show({title:'提示',msg:callback});
		}
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'数据无法提交，请重试！'});
	});
}

//权限主体
//刷新
function authorityadminmain_reload(){
	$('#authorityadminmain').datagrid('reload');
}

//启用和禁用
function authorityadminmain_ok_clear(action){
	var mainSelected = $('#authorityadminmain').datagrid('getSelected');
	if(mainSelected && authorityadmin_authorityid){
		if(action == 'ok'){
			if(mainSelected.value == '否')authorityadmin_ajax(authorityadmin_authorityid,mainSelected.id,'totrue');
		}else if(action == 'clear'){
			if(mainSelected.value == '是')authorityadmin_ajax(authorityadmin_authorityid,mainSelected.id,'tofalse');
		}
	}
}
</script>