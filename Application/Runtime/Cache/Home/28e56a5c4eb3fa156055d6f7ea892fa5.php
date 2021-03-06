<?php if (!defined('THINK_PATH')) exit();?><div id="orderconfirm_customer">
	<div id="orderconfirm_customer_toolbar">
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onClick="orderconfirm_customer_reload()">刷新</a>
	</div>
</div>
<script>
$('#orderconfirm_customer').datagrid({
	url:'/webforcits/home/operation/orderconfirmJson/customer/<?php echo ($orderid); ?>',
	method:'get',
	fit:true,
	striped:true,
	singleSelect:true,
	toolbar:'#orderconfirm_customer_toolbar',
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
	]]
});


function orderconfirm_customer_reload(){
	$('#orderconfirm_customer').datagrid('reload');
}
</script>