<?php if (!defined('THINK_PATH')) exit();?><div style="text-align:center;padding-top:50px;">
<div style="margin-bottom:15px;"><input id="neworder_ticket_input"></div>
<div style="margin-bottom:15px;"><input id="neworder_sender_input"></div>
<div><a href="javascript:void(0)" class="easyui-linkbutton" onClick="neworder_submit()">开始制单</a></div>
</div>
<script>
//初始化
var newordername = $('#center').tabs('getSelected').panel('options').title;

$('#neworder_ticket_input').textbox({
	type:'text',
	prompt:'现金券编号',
<?php if($EXTRA_order_ticket == 0): ?>validType:'checkNature',
<?php else: ?>
	validType:'checkTicket',<?php endif; ?>
<?php if($EXTRA_order_none == 0): ?>required:true,
	missingMessage:'该项必填',<?php endif; ?>
	onChange:function(){
		alert('11');
	}
});

$('#neworder_sender_input').textbox({
	type:'text',
	prompt:'发券人姓名',
<?php if(($EXTRA_order_sender == 0) and ($EXTRA_order_none == 0)): ?>required:true,
	missingMessage:'该项必填'<?php endif; ?>
});

//提交
function neworder_submit(){
	if($('#neworder_ticket_input').textbox('isValid') && $('#neworder_sender_input').textbox('isValid')){
		var ticket = $('#neworder_ticket_input').textbox('getValue');
		var sender = $('#neworder_sender_input').textbox('getValue');
		$('#neworder_ticket_input').textbox('disable');
		$('#neworder_sender_input').textbox('disable');
		$.post('/webforcits/home/operation/neworder',{ticket:ticket,sender:sender,action:'first',identify:identify})
		.success(function(callback){
			if(callback == '1'){
				$('#center').tabs('getTab',newordername).panel('refresh','/webforcits/home/operation/neworder_second');
			}else{
				$.messager.show({title:'提示',msg:callback});
				$('#neworder_ticket_input').textbox('enable');
				$('#neworder_sender_input').textbox('enable');
			}
		})
		.error(function(){
			$.messager.show({title:'提示',msg:'无法提交数据，请重试'});
			$('#neworder_ticket_input').textbox('enable');
			$('#neworder_sender_input').textbox('enable');
		});
	}
}
</script>