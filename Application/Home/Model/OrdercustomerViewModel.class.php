<?php 
namespace Home\Model;
use Think\Model\ViewModel;
class OrdercustomerViewModel extends ViewModel {
	public $viewFields = array(
		'ordercustomer' => array('id','order' => 'ordernum','name','idcard','passport','mobile','email','address','province','city','area','birth','age','sex','startflight','starttime','endflight','endtime','_type'=>'LEFT'),
		'area' => array('name' => 'areaname', '_on' => 'ordercustomer.area=area.id','_type'=>'LEFT'),
		'province' => array('name' => 'provincename', '_on' => 'ordercustomer.province=province.id','_type'=>'LEFT'),
		'city' => array('name' => 'cityname', '_on' => 'ordercustomer.city=city.id')
	);
}