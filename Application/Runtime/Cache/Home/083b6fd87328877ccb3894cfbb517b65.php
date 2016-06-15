<?php if (!defined('THINK_PATH')) exit();?><div id="myorder_unconfirm_customer">
	<div id="myorder_unconfirm_customer_toolbar">
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onClick="myorder_unconfirm_customer_remove()">删除</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onClick="myorder_unconfirm_customer_edit()">编辑</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onClick="myorder_unconfirm_customer_save()">保存</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onClick="myorder_unconfirm_customer_cancel()">取消</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="myorder_unconfirm_customer_reload()">刷新</a>
	</div>
</div>
<script>
$('#myorder_unconfirm_customer').datagrid({
	url:'/webforcits/home/operation/myorderJson/unconfirmcustomer/<?php echo ($orderid); ?>',
	method:'get',
	fit:true,
	striped:true,
	singleSelect:true,
	toolbar:'#myorder_unconfirm_customer_toolbar',
	columns:[[
		{field:'name',title:'姓名',width:80,rowspan:2},
		{field:'idcard',title:'身份证号码',width:120,rowspan:2},
		{title:'身份证信息',colspan:6},
		{field:'passport',title:'护照',width:100,rowspan:2,editor:{type:'textbox',options:{validType:'checkPassport'}}},
		{field:'mobile',title:'手机号码',width:100,rowspan:2,editor:{type:'textbox',options:{validType:'checkMobile'}}},
		{field:'email',title:'邮箱',width:150,rowspan:2,editor:{type:'textbox',options:{validType:'email',invalidMessage:'邮箱格式错误'}}},
		{field:'address',title:'地址',width:200,rowspan:2,editor:{type:'textbox'}},
		{title:'航班信息',colspan:4}
	],[
	   {field:'provincename',title:'省份',width:80},
	   {field:'cityname',title:'城市',width:100},
	   {field:'areaname',title:'地区',width:120},
	   {field:'birth',title:'出生日期',width:80},
	   {field:'age',title:'年龄',width:80},
	   {field:'sex',title:'性别',width:80},
	   {field:'startflight',title:'去程-航班号',width:100,editor:{type:'textbox',options:{validType:'checkFlight'}}},
	   {field:'starttime',title:'去程-时间',width:150,editor:{type:'datetimebox',options:{editable:false,formatter:function(date){
			var y = date.getFullYear();
			var m = date.getMonth()+1;
			var d = date.getDate();
			var h = date.getHours();
			var i = date.getMinutes();
			var s = date.getSeconds();
			return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d)+' '+(h<10?('0'+h):h)+':'+(i<10?('0'+i):i)+':'+(s<10?('0'+s):s);
		},parser:function(s){
			if (!s) return new Date();
			var ss = (s.split(' '));
			var day = (ss[0].split('-'));
			var hours = (ss[1].split(':'));
			var y = parseInt(day[0],10);
			var m = parseInt(day[1],10);
			var d = parseInt(day[2],10);
			var h = parseInt(hours[0],10);
			var i = parseInt(hours[1],10);
			var s = parseInt(hours[2],10);
			if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
				return new Date(y,m-1,d,h,i,s);
			} else {
				return new Date();
			}
		}
		}}},
	   {field:'endflight',title:'返程-航班号',width:100,editor:{type:'textbox',options:{validType:'checkFlight'}}},
	   {field:'endtime',title:'返程-时间',width:150,editor:{type:'datetimebox',options:{editable:false,formatter:function(date){
			var y = date.getFullYear();
			var m = date.getMonth()+1;
			var d = date.getDate();
			var h = date.getHours();
			var i = date.getMinutes();
			var s = date.getSeconds();
			return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d)+' '+(h<10?('0'+h):h)+':'+(i<10?('0'+i):i)+':'+(s<10?('0'+s):s);
		},parser:function(s){
			if (!s) return new Date();
			var ss = (s.split(' '));
			var day = (ss[0].split('-'));
			var hours = (ss[1].split(':'));
			var y = parseInt(day[0],10);
			var m = parseInt(day[1],10);
			var d = parseInt(day[2],10);
			var h = parseInt(hours[0],10);
			var i = parseInt(hours[1],10);
			var s = parseInt(hours[2],10);
			if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
				return new Date(y,m-1,d,h,i,s);
			} else {
				return new Date();
			}
		}
		}}}
	]],
	onClickRow: function(rowIndex, rowData){
	if(myorder_unconfirm_customer_editIndex != rowIndex){
			if(myorder_unconfirm_customer_endEdit()){
				$('#myorder_unconfirm_customer').datagrid('cancelEdit',myorder_unconfirm_customer_editIndex);
				myorder_unconfirm_customer_editIndex = rowIndex;
			}else{
				$('#myorder_unconfirm_customer').datagrid('selectRow', myorder_unconfirm_customer_editIndex);
			}
		}
	},
	onAfterEdit: function(rowIndex, rowData, changes){
		myorder_unconfirm_customer_ajax($('#myorder_unconfirm_customer').datagrid('getSelected').id,rowData,'edit');
	}
});

//初始化编辑状态
var myorder_unconfirm_customer_editIndex = undefined;
function myorder_unconfirm_customer_endEdit(){
	if(myorder_unconfirm_customer_editIndex == undefined){return true;}
	if($('#myorder_unconfirm_customer').datagrid('validateRow',myorder_unconfirm_customer_editIndex)){
		return true;
	}else{
		return false;
	}
}

//删除
function myorder_unconfirm_customer_remove(){
	if(myorder_unconfirm_customer_endEdit()){
		var selectedItem = $('#myorder_unconfirm_customer').datagrid('getSelected');
		if(selectedItem){
			$.messager.confirm({title:'提示',msg:'是否删除该游客？',fn:function(callback){
				if(callback){
					myorder_unconfirm_customer_ajax(selectedItem.id,selectedItem,'remove');
				}
			}});
		}
	}
}

//编辑
function myorder_unconfirm_customer_edit(){
	if(myorder_unconfirm_customer_endEdit() && $('#myorder_unconfirm_customer').datagrid('getSelected')){
		$('#myorder_unconfirm_customer').datagrid('selectRow',myorder_unconfirm_customer_editIndex);
		$('#myorder_unconfirm_customer').datagrid('beginEdit',myorder_unconfirm_customer_editIndex);
	}
}

//保存
function myorder_unconfirm_customer_save(){
	if(myorder_unconfirm_customer_editIndex != undefined){
		if(myorder_unconfirm_customer_endEdit()){
			$('#myorder_unconfirm_customer').datagrid('acceptChanges');
			myorder_unconfirm_customer_editIndex = undefined;
		}
	}
}

//取消
function myorder_unconfirm_customer_cancel(){
	$('#myorder_unconfirm_customer').datagrid('rejectChanges');
	myorder_unconfirm_customer_editIndex = undefined;
}

//提交数据
function myorder_unconfirm_customer_ajax(id,data,action){
	$.post('/webforcits/home/operation/myordercustomer/unconfirm',{id:id,data:data,action:action,identify:identify})
	.success(function(callback){
		if(callback != '1'){
			$.messager.show({title:'提示',msg:callback});
		}
		$('#myorder_unconfirm_customer').datagrid('reload');
	})
	.error(function(){
		$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
	});
}


function myorder_unconfirm_customer_reload(){
	$('#myorder_unconfirm_customer').datagrid('reload');
	myorder_unconfirm_customer_editIndex = undefined;
}
</script>