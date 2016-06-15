<?php
namespace Home\Controller;
use Think\Controller;
class OperationController extends Controller {

	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}

	//制单 第一步 + 数据处理
	public function neworder(){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){
					switch (I('post.action')){
					case 'first':
						if($this->orderCheckFirst(I('post.ticket'),I('post.sender'))){
							if(I('post.ticket') == '' && I('post.sender') == ''){
								$saveData = array('ticket_post' => '', 'sender_post' => '', 'worth' => 0, 'remain' => 0, 'ticket' => '', 'sender' => '', 'identify' => sha1(time()));
								F('order_cache_first_'.session('username'),$saveData);
								F('order_cache_second_'.session('username'),null);
								exit('1');
							}
							$ticketArr = explode(',',I('post.ticket'));
							$senderArr = explode(',',I('post.sender'));
							if(count($senderArr) == 1 && count($ticketArr) != 1){
								for($i = 1;$i < count($ticketArr); ++$i){
									$senderArr[$i] = $senderArr[0];
								}
							}
							$ticket_D = D('TicketView');
							$worth = 0;
							$remain = 0;
							$senderNameArr = array();
							foreach($ticketArr as $taKey => $taValue){
								$ticketWhere = array('id' => $taValue);
								if(!$ticketInfo = $ticket_D->where($ticketWhere)->find())exit('编号['.$taValue.']现金券不存在');
								if(!$this->base->extraSet('order_sender')){
									if($ticketInfo['sendername'] != $senderArr[$taKey])exit('现金券号码与发券人姓名不匹配');
								}
								array_push($senderNameArr,$ticketInfo['sendername']);
								$worth += $ticketInfo['worth'];
								$remain += $ticketInfo['remain'];
							}
							$senderName = implode(',',$senderNameArr);
							$saveData = array('ticket_post' => I('post.ticket'),'sender_post' => $senderName, 'worth' => $worth, 'remain' => $remain,
							'ticket' => $ticketArr, 'sender' => $senderArr, 'identify' => sha1(time()));
							F('order_cache_first_'.session('username'),$saveData);
							F('order_cache_second_'.session('username'),null);
							exit('1');
						}else{
							exit('数据格式错误，请修正后提交');
						}
						break;
						
					case 'second':
						if($order_first = F('order_cache_first_'.session('username'))){
							if($order_first['identify'] == I('post.order_identify')){
								$postRoute = I('post.route');
								$postStarttime = I('post.starttime');
								$postProvince = preg_match('/^[1-9]+[0-9]*$/',I('post.province')) ? I('post.province') : exit($this->base->changeCallback_error);
								$postCity = preg_match('/^[1-9]+[0-9]*$/',I('post.city')) ? I('post.city') : exit($this->base->changeCallback_error);
								if($postRoute && $postStarttime){
									$route_M = M('route');
									$routeWhere = array('name' => $postRoute,'work' => 1);
									if(!$routeInfo = $route_M->field('id,price,agemin,agemax,province')->where($routeWhere)->find())exit($this->base->changeCallback_error);
									if(!$starttimeUnix = strtotime($postStarttime))exit($this->base->changeCallback_error);
									if($starttimeUnix < time())exit('选择的时间不能在当前日期之前');
									$city_M = M('city');
									$cityWhere = array('id' => $postCity, 'province' => $postProvince);
									if(!$cityInfo = $city_M->where($cityWhere)->find())exit($this->base->changeCallback_error);
									$saveData = array('route' => $routeInfo, 'starttime' => $starttimeUnix, 'route_post' => I('post.route'), 'starttime_post' => I('post.starttime'),
									'province_post' => I('post.province'), 'city_post' => I('post.city'), 'startcity' => $cityInfo, 'identify' => $order_first['identify']);
									F('order_cache_second_'.session('username'),$saveData);
									F('order_cache_third_'.session('username'),null);
									exit('1');
								}else{
									exit($this->base->changeCallback_error);
								}
							}else{
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					case 'third':
						if(preg_match('/^[0-9]+$/',I('post.id')) && I('post.data')){
							$order_first = F('order_cache_first_'.session('username'));
							$order_second = F('order_cache_second_'.session('username'));
							$order_third = F('order_cache_third_'.session('username'));
							$postData = I('post.data');
							if($order_first && $order_second && $order_third && $order_first['identify'] == $order_second['identify'] && $order_first['identify'] == $order_third['identify']){
								switch(I('post.mode')){
								case 'add':
									if(I('post.id') == 0 && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$postData['name']) && preg_match('/^[0-9]{17}[0-9Xx]{1}$/',$postData['idcard'])){
										$postData['id'] = $order_third['id'];
										$order_third['id'] += 1;
										$order_third['customernum'] += 1;
										$order_third['list'][$postData['id']] = $postData;
										F('order_cache_third_'.session('username'),$order_third);
										exit('1');
									}
									exit($this->base->changeCallback_error);
									break;
									
								case 'edit':
									if(isset($order_third['list'][I('post.id')]) && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$postData['name']) && preg_match('/^[0-9]{17}[0-9Xx]{1}$/',$postData['idcard'])){
										$order_third['list'][I('post.id')] = $postData;
										F('order_cache_third_'.session('username'),$order_third);
										exit('1');
									}
									exit($this->base->changeCallback_error);
									break;
									
								case 'remove':
									if(isset($order_third['list'][I('post.id')])){
										unset($order_third['list'][I('post.id')]);
										$order_third['customernum'] -= 1;
										F('order_cache_third_'.session('username'),$order_third);
										exit('1');
									}
									exit($this->base->changeCallback_error);
									break;
									
								default:
									exit($this->base->changeCallback_error);
									break;
								}
							}else{
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					case 'confirm':
						if(!$order_first = F('order_cache_first_'.session('username')))exit('错误，无现金券信息');
						if(!$order_second = F('order_cache_second_'.session('username')))exit('错误，无旅游线路信息');
						if(!$order_third = F('order_cache_third_'.session('username')))exit('错误，无游客信息');
						if($order_first['identify'] == $order_second['identify'] && $order_first['identify'] == $order_third['identify'] && 
						$order_first['identify'] == I('post.order_identify')){
							$loginInfo = $this->base->loginInfo();
							$orderLeadername = '';
							$orderMobile = 0;
							$orderEmail = '';
							$orderAddress = '';
							foreach($order_third['list'] as $otValue){
								if($otValue['name'] != '' && $otValue['mobile'] != ''){
									if($orderLeadername == '' || $orderMobile == 0){
										$orderLeadername = $otValue['name'];
										$orderMobile = $otValue['mobile'];
										$orderEmail = $otValue['email'];
										$orderAddress = $otValue['address'];
									}
								}
								if($otValue['leader'] == '是'){
									if($otValue['name'] != '' && $otValue['mobile'] != ''){
										$orderLeadername = $otValue['name'];
										$orderMobile = $otValue['mobile'];
										$orderEmail = $otValue['email'];
										$orderAddress = $otValue['address'];
										break;
									}
								}
							}
							if($orderMobile == ''){
								$orderLeadername = $order_third['list'][1]['name'];
								$orderMobile = $order_third['list'][1]['mobile'];
								$orderEmail = $order_third['list'][1]['email'];
								$orderAddress = $order_third['list'][1]['address'];
							}
							$order_M = M('order');
							$orderData = array('createid' => $loginInfo['id'], 'ticket' => $order_first['ticket_post'], 'sender' => $order_first['sender_post'],
							'route' => $order_second['route']['id'], 'starttime' => $order_second['starttime'], 'province' => $order_second['startcity']['province'],
							'city' => $order_second['startcity']['id'], 'leadername' => $orderLeadername, 'mobile' => $orderMobile, 'email' => $orderEmail,
							'address' => $orderAddress, 'createtime' => time());
							$orderId = $order_M->data($orderData)->add();
							if(!$orderId)exit('创建订单错误，请重试');
							$ordercustomer_M = M('ordercustomer');
							foreach($order_third['list'] as $otValue){
								if(!$customerInfo = $this->customerInfo($otValue['idcard']))continue;
								$ordercustomerData[] = array('order' => $orderId, 'name' => $otValue['name'], 'idcard' => $otValue['idcard'], 'passport' => $otValue['passport'],
								'mobile' => $otValue['mobile'], 'email' => $otValue['email'], 'address' => $otValue['address'], 'province' => $customerInfo['province'],
								'city' => $customerInfo['city'], 'area' => $customerInfo['area'], 'birth' => $customerInfo['birth'], 'sex' => $customerInfo['sex'],
								'age' => $customerInfo['age']);
							}
							if($ordercustomerData && $ordercustomer_M->addAll($ordercustomerData)){
								$logMessage = '现金券编号：'.$order_first['ticket_post'].'，发券人姓名：'.$order_first['sender_post'].'<br>'.
								'旅游线路：'.$order_second['route_post'].'，出发时间：'.$order_second['starttime_post'].'，出发城市：'.$order_second['startcity']['name'].'<br>'.
								'主联系人姓名 ：'.$orderData['leadername'].'，手机号码：'.$orderData['mobile'].'，邮箱：'.$orderData['email'].'，地址：'.$orderData['address'].'<br>'.
								'游客资料：<br>';
								foreach($ordercustomerData as $odValue){
									$info = '姓名：'.$odValue['name'].'，身份证：'.$odValue['idcard'].'，护照：'.$odValue['passport'].'，手机：'.$odValue['mobile'].
									'，邮箱：'.$odValue['email'].'，地址：'.$odValue['address'].'<br>';
									$logMessage .= $info;
								}
								$this->base->writeLog(session('username'),'neworder',$logMessage);
								F('order_cache_first_'.session('username'),null);
								F('order_cache_second_'.session('username'),null);
								F('order_cache_third_'.session('username'),null);
								exit('1');
							}else{
								$orderDelete = array('id' => $orderId);
								$order_M->where($orderDelete)->delete();
								$this->base->errorMessage(session('username'),'创建订单失败！','无法创建游客资料');
								exit('创建订单失败，请重试');
							}
						}else{
							exit('信息验证错误，请关闭重新填写！');
						}
						break;
						
					default:
						exit($this->base->changeCallback_error);
						break;
					}
					
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				$this->assign('EXTRA_order_none',$this->base->extraSet('order_none'));
				$this->assign('EXTRA_order_sender',$this->base->extraSet('order_sender'));
				$this->assign('EXTRA_order_ticket',$this->base->extraSet('order_ticket'));
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//验证现金券和发券人格式
	private function orderCheckFirst($ticket,$sender){
		if($this->base->extraSet('order_none')){
			if($ticket == '' && $sender == '')return true;
		}
		if($this->base->extraSet('order_ticket')){
			if(!preg_match('/^[0-9,]+$/',$ticket))return false;
			if($this->base->extraSet('order_sender') == '0'){
				if($sender == '')return false;
			}
		}else{
			if(!preg_match('/^[0-9]+$/',$ticket))return false;
			if($this->base->extraSet('order_sender') == '0'){
				if($sender == '')return false;
			}
		}
		$ticketnum = count(explode(',',$ticket));
		$sendernum = count(explode(',',$sender));
		if($this->base->extraSet('order_ticket')){
			if($ticketnum < 1 || ($ticketnum != $sendernum && $sendernum != 1))return false;
		}else{
			if($ticketnum != 1 || $sendernum != 1)return false;
		}
		return true;
	}
	
	//游客身份证信息扩展
	private function customerInfo($idcard){
		if($idcard && preg_match('/^[0-9]{17}[0-9Xx]{1}$/',$idcard)){
			$info['province'] = substr($idcard,0,2).'0000';
			$info['city'] = substr($idcard,0,4).'00';
			$info['area'] = substr($idcard,0,6);
			$info['birth'] = substr($idcard,6,8);
			$age = substr($idcard,6,4);
			$year = date("Y",time());
			$info['age'] = $year - $age;
			$sexnum = substr($idcard,16,1);
			if($sexnum % 2 == 0){
				$info['sex'] = 2;
			}else{
				$info['sex'] = 1;
			}
			return $info;
		}else{
			return false;
		}
	}
	
	//制单 第二步
	public function neworder_second(){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			if($order_first = F('order_cache_first_'.session('username'))){
				$this->assign('ticket',$order_first['ticket_post']);
				$this->assign('sender',$order_first['sender_post']);
				$this->assign('worth',$order_first['worth']);
				$this->assign('remain',$order_first['remain']);
				$this->assign('EXTRA_order_overdraft',$this->base->extraSet('order_overdraft'));
				$this->assign('EXTRA_order_none',$this->base->extraSet('order_none'));
				$this->assign('identify',$order_first['identify']);
				$this->display();
			}else{
				header('location: '.__APP__.'/home/operation/neworder');
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//制单 第二步->Json数据
	public function neworder_secondJson($action = false,$id = false){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			switch ($action){
			case 'routelist':
				$route_M = M('route');
				$routeWhere = array('work' => 1);
				$routeInfo = $route_M->field('price,name')->where($routeWhere)->select();
				$this->ajaxReturn($routeInfo);
				break;
				
			case 'province':
				$province_M = M('province');
				$provinceInfo = $province_M->select();
				$this->ajaxReturn($provinceInfo);
				break;
				
			case 'city':
				if(!$id)return false;
				if(!preg_match('/^[1-9]+[0-9]*$/',$id))return false;
				$city_M = M('city');
				$cityWhere = array('province' => $id);
				$cityInfo = $city_M->field('id,name')->where($cityWhere)->select();
				$this->ajaxReturn($cityInfo);
				break;
				
			default:
				exit($this->base->changeCallback_error);
				break;
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//制单 第三步
	public function neworder_third(){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			$order_first = F('order_cache_first_'.session('username'));
			$order_second = F('order_cache_second_'.session('username'));
			if($order_first && $order_second && $order_first['identify'] == $order_second['identify']){
				$order_third = F('order_cache_third_'.session('username'));
				if($order_third){
					$this->assign('customernum',$order_third['customernum']);
				}else{
					$saveData = array('id' => 1, 'customernum' => 0 ,'list' => array(),'identify' => $order_first['identify']);
					F('order_cache_third_'.session('username'),$saveData);
					$this->assign('customernum',0);
				}
				$this->assign('price',$order_second['route']['price']);
				$this->assign('remain',$order_first['remain']);
				$this->assign('EXTRA_order_overdraft',$this->base->extraSet('order_overdraft'));
				$this->assign('identify',$order_first['identify']);
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//制单 第三步->Json数据
	public function neworder_thirdJson(){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			$order_first = F('order_cache_first_'.session('username'));
			$order_second = F('order_cache_second_'.session('username'));
			$order_third = F('order_cache_third_'.session('username'));
			if($order_first && $order_second && $order_third && $order_first['identify'] == $order_second['identify'] && $order_first['identify'] == $order_third['identify']){
				$list = array();
				foreach($order_third['list'] as $value){
					array_push($list,$value);
				}
				$this->ajaxReturn($list);
			}else{
				exit($this->base->changeCallback_error);
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//制单 生成订单
	public function neworder_confirmed(){
		if($this->base->login && $this->base->authorityCheck('neworder')){
			$this->display();
		}else{
			$this->base->noLogin();
		}
	}
	
	//我的订单
	public function myorder($target = false,$orderid = false){
		if($this->base->login && $this->base->authorityCheck('myorder')){
			if(IS_POST){
				if(preg_match('/^[1-9]+[0-9]*$/',I('post.id')) && I('post.data') && $this->base->loginInfo(I('post.identify'))){
					$loginInfo = $this->base->loginInfo();
					switch (I('post.action')){
					case 'edit':
						$postData = I('post.data');
						if($loginInfo['id'] != $postData['createid'])exit($this->base->changeCallback_error);
						$order_M = M('order');
						$orderWhere = array('id' => I('post.id'), 'createid' => $loginInfo['id'], 'status' => 0);
						if(!$orderInfo = $order_M->where($orderWhere)->find())exit($this->base->changeCallback_error);
						if($postData['starttime'] == date("Y-m-d",$orderInfo['starttime']) && $postData['leadername'] == $orderInfo['leadername'] &&
						($postData['mobile'] ? $postData['mobile'] : 0) == $orderInfo['mobile'] && $postData['email'] == $orderInfo['email'] && 
						$postData['address'] == $orderInfo['address']){
							exit('未修改信息');
						}
						if($postData['leadername'] == '')exit($this->base->changeCallback_error);
						if(!$starttime = strtotime($postData['starttime']))exit($this->base->changeCallback_error);
						if($postData['mobile']){
							if(!preg_match('/^[1]{1}[0-9]{10}$/',$postData['mobile']))exit($this->base->changeCallback_error);
						}
						$orderData = array('id' => I('post.id'), 'starttime' => $starttime, 'leadername' => $postData['leadername'], 
						'mobile' => ($postData['mobile'] ? $postData['mobile'] : 0), 'email' => $postData['email'], 'address' => $postData['address']);
						$logMessage = '订单号：'.$postData['id'].'<br>原信息 => 出发时间：'.date("Y-m-d",$orderInfo['starttime']).'，联系人：'.$orderInfo['leadername'].
						'，手机：'.($orderInfo['mobile'] ? $orderInfo['mobile'] : '').'，邮箱：'.$orderInfo['email'].'，地址：'.$orderInfo['address'].'<br>'.
						'修改为 => 出发时间：'.$postData['starttime'].'，联系人：'.$postData['leadername'].'，手机：'.$postData['mobile'].'，邮箱：'.$postData['email'].
						'，地址：'.$postData['address'];
						if($order_M->data($orderData)->save()){
							$this->base->writeLog(session('username'),'myorder_edit',$logMessage);
							exit('1');
						}else{
							$this->base->errorMessage(session('username'),'编辑订单资料失败',$logMessage);
							exit('编辑失败，请重试');
						}
						break;
						
					case 'remove':
						if($loginInfo['id'] == I('post.data')){
							$order_M = M('order');
							$orderWhere = array('id' => I('post.id'), 'createid' => I('post.data'), 'status' => 0);
							if($orderInfo = $order_M->field('id')->where($orderWhere)->find()){
								$logMessage = '订单号：'.$orderInfo['id'];
								if($order_M->data($orderWhere)->delete()){
									$ordercustomer_M = M('ordercustomer');
									$ordercustomerWhere = array('order' => I('post.id'));
									$ordercustomer_M->data($ordercustomerWhere)->delete();
									$this->base->writeLog(session('username'),'myorder_remove',$logMessage);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'删除订单失败',$logMessage);
									exit('删除失败，请重试');
								}
							}else{
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					default:
						exit($this->base->changeCallback_error);
						break;
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				switch ($target){
				case 'unconfirm':
					if(preg_match('/^[1-9]+[0-9]*$/',$orderid)){
						$loginInfo = $this->base->loginInfo();
						$order_M = M('order');
						$orderWhere = array('id' => $orderid, 'createid' => $loginInfo['id'], 'status' => 0);
						if(!$orderConfirm = $order_M->field('id')->where($orderWhere)->find())exit($this->base->changeCallback_error);
						$this->assign('orderid',$orderid);
						$this->display('myorder_unconfirm_customer');
					}else{
						exit($this->base->changeCallback_error);
					}
					break;
					
				case 'confirmed':
					if(preg_match('/^[1-9]+[0-9]*$/',$orderid)){
						$loginInfo = $this->base->loginInfo();
						$order_M = M('order');
						$orderWhere = array('id' => $orderid, 'createid' => $loginInfo['id'], 'status' => 1);
						if(!$orderConfirm = $order_M->field('id')->where($orderWhere)->find())exit($this->base->changeCallback_error);
						$this->assign('orderid',$orderid);
						$this->display('myorder_confirmed_customer');
					}else{
						exit($this->base->changeCallback_error);
					}
					break;
					
				default:
					$this->display();
					break;
				}
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//编辑游客信息
	public function myordercustomer($target = false){
		if(IS_POST){
			if($target && $this->base->loginInfo(I('post.identify'))){
				if(preg_match('/^[1-9]+[0-9]*$/',I('post.id')) && I('post.data')){
					$loginInfo = $this->base->loginInfo();
					switch ($target){
					case 'unconfirm':
						$postData = I('post.data');
						$order_M = M('order');
						$orderWhere = array('id' => $postData['ordernum'], 'createid' => $loginInfo['id'], 'status' => 0);
						if(!$orderConfirm = $order_M->field('id')->where($orderWhere)->find())exit($this->base->changeCallback_error);
						$ordercustomer_M = M('ordercustomer');
						$ordercustomerWhere = array('id' => I('post.id'));
						if(!$ordercustomerInfo = $ordercustomer_M->where($ordercustomerWhere)->find())exit($this->base->changeCallback_error);
						switch (I('post.action')){
						case 'edit':
							if($postData['mobile']){
								if(!preg_match('/^[1]{1}[0-9]{10}$/',$postData['mobile']))return false;
							}
							if($postData['startflight']){
								if(!preg_match('/^[A-Za-z]{2}[0-9]{4}$/',$postData['startflight']))return false;
							}
							if($postData['starttime']){
								if(!$starttime = strtotime($postData['starttime']))return false;
							}else{
								$starttime = false;
							}
							if($postData['endflight']){
								if(!preg_match('/^[A-Za-z]{2}[0-9]{4}$/',$postData['endflight']))return false;
							}
							if($postData['endtime']){
								if(!$endtime = strtotime($postData['endtime']))return false;
							}else{
								$endtime = false;
							}
							$ordercustomerData = array('id' => I('post.id'), 'passport' => $postData['passport'], 'mobile' => $postData['mobile'], 'email' => $postData['email'],
							'address' => $postData['address'], 'startflight' => strtoupper($postData['startflight']), 'starttime' => ($starttime ? $starttime : 0),
							'endflight' => strtoupper($postData['endflight']), 'endtime' => ($endtime ? $endtime: 0));
							$ordercustomerResult = $ordercustomer_M->data($ordercustomerData)->save();
							$logMessage = '订单号：'.$orderConfirm['id'].'，游客姓名：'.$ordercustomerInfo['name'].'<br>'.
							'原信息 => 护照：'.$ordercustomerInfo['passport'].'，手机：'.($ordercustomerInfo['mobile'] ? $ordercustomerInfo['mobile'] : '').
							'邮箱：'.$ordercustomerInfo['email'].'，地址：'.$ordercustomerInfo['address'].'，去程-航班号：'.$ordercustomerInfo['startflight'].
							'，去程-时间：'.($ordercustomerInfo['starttime'] ? date("Y-m-d H:i:s",$ordercustomerInfo['starttime']) : '').
							'，返程-航班号：'.$ordercustomerInfo['endflight'].'，返程-时间：'.($ordercustomerInfo['endtime'] ? date("Y-m-d H:i:s",$ordercustomerInfo['endtime']) : '').
							'<br> 修改为 => 护照：'.$postData['passport'].'，手机：'.$postData['mobile'].'，邮箱：'.$postData['email'].'，地址：'.$postData['address'].
							'，去程-航班号：'.$postData['startflight'].'，去程-时间：'.$postData['starttime'].'，返程-航班号：'.$postData['endflight'].
							'，返程-时间：'.$postData['endtime'];
							if($ordercustomerResult !== false){
								if($ordercustomerResult){
									$this->base->writeLog(session('username'),'unconfirm_customer_edit',$logMessage);
									exit('1');
								}else{
									exit('未修改信息');
								}
							}else{
								$this->base->errorMessage(session('username'),'编辑游客信息失败',$logMessage);
								exit('编辑失败，请重试');
							}
							break;
							
						case 'remove':
							$logMessage = '订单号：'.$orderConfirm['id'].'，游客姓名：'.$ordercustomerInfo['name'];
							if($ordercustomer_M->where($ordercustomerWhere)->delete()){
								$this->base->writeLog(session('username'),'unconfirm_customer_remove',$logMessage);
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'删除游客信息失败',$logMessage);
								exit('删除失败，请重试');
							}
							break;
							
						default:
							exit($this->base->changeCallback_error);
							break;
						}
						break;
					
					default:
						exit($this->base->changeCallback_error);
						break;
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				exit($this->base->changeCallback_error);
			}
		}else{
			exit($this->base->changeCallback_error);
		}
	}
	
	public function myorderJson($action = false,$orderid = false){
		if($this->base->login && $this->base->authorityCheck('myorder')){
			$loginInfo = $this->base->loginInfo();
			switch ($action){
			case 'unconfirm':
				if(preg_match('/^[1-9]+[0-9]*$/',I('get.page')) && preg_match('/^[1-9]+[0-9]*$/',I('get.rows'))){
					$order_D = D('OrderView');
					if(!$total = $order_D->count())return false;
					$orderWhere = array('createid' => $loginInfo['id'], 'status' => 0);
					if($orderInfo = $order_D->where($orderWhere)->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select()){
						foreach($orderInfo as $oiKey => $oiValue){
							$orderInfo[$oiKey]['starttime'] = $oiValue['starttime'] ? date("Y-m-d",$oiValue['starttime']) : '';
							$orderInfo[$oiKey]['createtime'] = $oiValue['createtime'] ? date("Y-m-d H:i:s",$oiValue['createtime']) : '';
							$orderInfo[$oiKey]['mobile'] = $oiValue['mobile'] ? $oiValue['mobile'] : '';
						}
					}
					$orderInfoAjax = array('total' => $total, 'rows' => $orderInfo);
					$this->ajaxReturn($orderInfoAjax);
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			case 'confirmed':
				if(preg_match('/^[1-9]+[0-9]*$/',I('get.page')) && preg_match('/^[1-9]+[0-9]*$/',I('get.rows'))){
					$order_D = D('OrderView');
					if(!$total = $order_D->count())return false;
					$orderWhere = array('createid' => $loginInfo['id'], 'status' => 1);
					if($orderInfo = $order_D->where($orderWhere)->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select()){
						foreach($orderInfo as $oiKey => $oiValue){
							$orderInfo[$oiKey]['starttime'] = $oiValue['starttime'] ? date("Y-m-d",$oiValue['starttime']) : '';
							$orderInfo[$oiKey]['confirmtime'] = $oiValue['confirmtime'] ? date("Y-m-d H:i:s",$oiValue['confirmtime']) : '';
							$orderInfo[$oiKey]['mobile'] = $oiValue['mobile'] ? $oiValue['mobile'] : '';
						}
					}
					$orderInfoAjax = array('total' => $total, 'rows' => $orderInfo);
					$this->ajaxReturn($orderInfoAjax);
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			case 'unconfirmcustomer':
				if(preg_match('/^[1-9]+[0-9]*$/',$orderid)){
					$ordercustomer_D = D('OrdercustomerView');
					$ordercustomerWhere = array('order' => $orderid);
					if($ordercustomerInfo = $ordercustomer_D->where($ordercustomerWhere)->select()){
						foreach($ordercustomerInfo as $oiKey => $oiValue){
							$ordercustomerInfo[$oiKey]['mobile'] = $oiValue['mobile'] ? $oiValue['mobile'] : '';
							$ordercustomerInfo[$oiKey]['sex'] = ($oiValue['sex'] == 1) ? '男' : '女';
							$ordercustomerInfo[$oiKey]['starttime'] = $oiValue['starttime'] ? date("Y-m-d H:i:s",$oiValue['starttime']) : '';
							$ordercustomerInfo[$oiKey]['endtime'] = $oiValue['endtime'] ? date("Y-m-d H:i:s",$oiValue['endtime']) : '';
						}
						$this->ajaxReturn($ordercustomerInfo);
					}else{
						exit($this->base->changeCallback_error);
					}
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			case 'confirmedcustomer':
				if(preg_match('/^[1-9]+[0-9]*$/',$orderid)){
					$ordercustomer_D = D('OrdercustomerView');
					$ordercustomerWhere = array('order' => $orderid);
					if($ordercustomerInfo = $ordercustomer_D->where($ordercustomerWhere)->select()){
						foreach($ordercustomerInfo as $oiKey => $oiValue){
							$ordercustomerInfo[$oiKey]['mobile'] = $oiValue['mobile'] ? $oiValue['mobile'] : '';
							$ordercustomerInfo[$oiKey]['sex'] = ($oiValue['sex'] == 1) ? '男' : '女';
							$ordercustomerInfo[$oiKey]['starttime'] = $oiValue['starttime'] ? date("Y-m-d H:i:s",$oiValue['starttime']) : '';
							$ordercustomerInfo[$oiKey]['endtime'] = $oiValue['endtime'] ? date("Y-m-d H:i:s",$oiValue['endtime']) : '';
						}
						$this->ajaxReturn($ordercustomerInfo);
					}else{
						exit($this->base->changeCallback_error);
					}
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			default:
				exit($this->base->changeCallback_error);
				break;
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	public function orderconfirm($target = false, $orderid = false){
		if($this->base->login && $this->base->authorityCheck('orderconfirm')){
			if(IS_POST){
				if(preg_match('/^[1-9]+[0-9]*$/',I('post.id')) && $this->base->loginInfo(I('post.identify'))){
					exit('功能未完成');
					$order_M = M('order');
					$orderWhere = array('id' => I('post.id'), 'status' => 0);
					if(!$orderInfo = $order_M->where($orderWhere)->find())return false;
					$ordercustomer_M = M('ordercustomer');
					$ordercustomerWhere = array('order' => I('post.id'));
					if(!$ordercustomerInfo = $ordercustomer_M->where($ordercustomerWhere)->select())return false;
					$customernum = 0;
					$bookingnum = 0;
					$bookingPrice = $this->base->extraSet('customerservice');
					foreach($ordercustomerInfo as $oiValue){
						$customernum += 1;
						if($oiValue['startflight'] && $oiValue['starttime'])$bookingnum += 0.5;
						if($oiValue['endflight'] && $oiValue['endtime'])$bookingnum += 0.5;
					}
					$bookingCut = $bookingPrice * $bookingnum;
					$bookingcut_M = M('bookingcut');
					$bookingData = array('orderid' => $orderInfo['id'], 'userid' => $orderInfo['createid'], 'customernum' => $customernum, 
					'bookingnum' => $bookingnum, 'price' => $bookingPrice, 'cut' => $bookingCut);
					$bookingResult = $bookingcut_M->data($bookingData)->add();
					if(!$bookingResult){
						$this->base->errorMessage(session('username'),'确认订单 => 订票提成 未生成','订单号：'.I('post.id'));
					}
					if($orderInfo['ticket'] != ''){
						$ticketArr = explode(',',$orderInfo['ticket']);
						if(count($ticketArr) >= 1){
							
						}else{
							$bookingcut_M->where(array('id' => $bookingResult))->delete();
							$this->base->errorMessage(session('username'),'订单现金券有异常','订单号：'.I('post.id').'，现金券：'.$orderInfo['ticket']);
							exit('未知原因，请联系管理员');
						}
					}
					$orderData = array('id' => I('post.id'), 'customernum' => $customernum, 'status' => 1);
					$orderResult = $order_M->data($orderData)->save();
					if($orderResult === false){
						$this->base->errorMessage(session('username'),'确认订单失败','订单号：'.I('post.id'));
						exit('无法确认订单，请重试');
					}else{
						$this->base->writeLog(session('username'),'orderconfirm','订单号：'.I('post.id'));
						exit('1');
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				switch($target){
				case 'customer':
					if(!preg_match('/^[1-9]+[0-9]*$/',$orderid))return false;
					$this->assign('orderid',$orderid);
					$this->display('orderconfirm_customer');
					break;
					
				default:
					$this->display();
					break;
				}
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	private function orderconfirm_ticketInfo($ticket){
		if(!is_array($ticket) || !$ticket)return false;
		$ticket_M = M('ticket');
		$sender = array();
		foreach($ticket as $key => $value){
			$ticketWhere = array('id' => $value);
			if(!$ticketInfo = $ticket_M->field('sender,remain')->where($ticketWhere)->find())return false;
			$sender[$ticketInfo['sender']] = $ticketInfo['sender'];
		}
		return $sender;
	}
	
	public function orderconfirmJson($target = false,$orderid = false){
		if($this->base->login && $this->base->authorityCheck('orderconfirm')){
			if($target == 'customer' && preg_match('/^[1-9]+[0-9]*$/',$orderid)){
				$ordercustomer_D = D('OrdercustomerView');
				$ordercustomerWhere = array('order' => $orderid);
				if(!$ordercustomerInfo = $ordercustomer_D->where($ordercustomerWhere)->select())return false;
				foreach($ordercustomerInfo as $oiKey => $oiValue){
					$ordercustomerInfo[$oiKey]['mobile'] = $oiValue['mobile'] ? $oiValue['mobile'] : '';
					$ordercustomerInfo[$oiKey]['sex'] = ($oiValue['sex'] == 1) ? '男' : '女';
					$ordercustomerInfo[$oiKey]['starttime'] = $oiValue['starttime'] ? date("Y-m-d H:i:s",$oiValue['starttime']) : '';
					$ordercustomerInfo[$oiKey]['endtime'] = $oiValue['endtime'] ? date("Y-m-d H:i:s",$oiValue['endtime']) : '';
				}
				$this->ajaxReturn($ordercustomerInfo);
			}else{
				if(!$memberInfo = $this->base->memberInfo('id'))return false;
				$queryIn = implode(',',$memberInfo);
				$queryIn = '1,2,3';//for test
				$order_D = D('OrderView');
				$orderWhere = array('createid' => array('in',$queryIn), 'status' => 0);
				if(!$orderInfo = $order_D->where($orderWhere)->select())return false;
				foreach($orderInfo as $oiKey => $oivalue){
					$orderInfo[$oiKey]['starttime'] = $oivalue['starttime'] ? date("Y-m-d",$oivalue['starttime']) : '';
					$orderInfo[$oiKey]['createtime'] = $oivalue['endtime'] ? date("Y-m-d H:i:s",$oivalue['endtime']) : '';
					$orderInfo[$oiKey]['mobile'] = $oivalue['mobile'] ? $oivalue['mobile'] : '';
				}
				$this->ajaxReturn($orderInfo);
			}
		}else{
			exit($this->base->changeCallback_error);
		}
	}
}