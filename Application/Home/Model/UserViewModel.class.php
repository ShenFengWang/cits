<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class UserViewModel extends ViewModel {
	public $viewFields = array(
		'user' => array('id','username','realname','ingroup','bankcard','bankname','idcardnumber','loginstatus','mobile','authority','_type'=>'LEFT'),
		'group_list' => array('name' => 'ingroup_name', '_on' => 'user.ingroup=group_list.id','_type'=>'LEFT'),
		'authority' => array('name' => 'authority_name', '_on' => 'user.authority=authority.id','_type'=>'LEFT'),
		'actor' => array('name' => 'actor_name', '_on' => 'user.loginstatus=actor.id')
	);
}