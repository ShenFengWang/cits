<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class TicketViewModel extends ViewModel {
	public $viewFields = array(
		'ticket' => array('id','sender','worth','remain','firsttime','lasttime','usedtimes','usedpeople','_type'=>'LEFT'),
		'sender' => array('name' => 'sendername', '_on' => 'ticket.sender=sender.id')
	);
}