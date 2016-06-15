<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class SendercutViewModel extends ViewModel {
	public $viewFields = array(
		'sendercut' => array('id','route','cut','_type'=>'LEFT'),
		'route' => array('name' => 'routename', '_on' => 'sendercut.route=route.id')
	);
}