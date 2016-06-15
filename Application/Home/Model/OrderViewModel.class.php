<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class OrderViewModel extends ViewModel {
	public $viewFields = array(
		'order' => array('id','createid','ticket','sender','route','starttime','province','city','customernum','leadername','mobile','email','address','createtime','confirmtime','_as' => 'ordertable','_type'=>'LEFT'),
		'route' => array('name' => 'routename', '_on' => 'ordertable.route=route.id','_type'=>'LEFT'),
		'province' => array('name' => 'provincename', '_on' => 'ordertable.province=province.id','_type'=>'LEFT'),
		'city' => array('name' => 'cityname', '_on' => 'ordertable.city=city.id','_type'=>'LEFT'),
		'user' => array('realname' => 'createname', '_on' => 'ordertable.createid=user.id')
	);
}