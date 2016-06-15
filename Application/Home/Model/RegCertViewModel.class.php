<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class RegCertViewModel extends ViewModel {
	public $viewFields = array(
		'reg_cert' => array('id','randomnumber','idcardnumber','realname','ingroup','bankcard','bankname','loginstatus','mobile','authority','_type'=>'LEFT'),
		'group_list' => array('name' => 'group_name', '_on' => 'reg_cert.ingroup=group_list.id','_type'=>'LEFT'),
		'authority' => array('name' => 'authority_name', '_on' => 'reg_cert.authority=authority.id','_type'=>'LEFT'),
		'actor' => array('name' => 'actor_name', '_on' => 'reg_cert.loginstatus=actor.id')
	);
}