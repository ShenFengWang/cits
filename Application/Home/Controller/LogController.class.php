<?php
namespace Home\Controller;
use Think\Controller;
class LogController extends Controller {

	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}
	
	//我的登录
	public function myloginfo(){
		if($this->base->login && $this->base->authorityCheck('myloginfo')){
			$this->display();
		}else{
			$this->base->noLogin();
		}
	}
	
	//我的登录->Json数据
	public function myloginfoJson(){
		if($this->base->login && $this->base->authorityCheck('myloginfo')){
			if(preg_match('/^[1-9]+[0-9]*$/',I('get.page')) && preg_match('/^[1-9]+[0-9]*/',I('get.rows'))){
				$login_username_M = M('login_'.session('username'));
				$total = $login_username_M->count();
				if(!$total)return false;
				$login_usernameInfo = $login_username_M->field('username,ip,log_time,user_id')->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select();
				foreach($login_usernameInfo as $luKey => $luValue){
					$login_usernameInfo[$luKey]['log_time'] = date("Y-m-d H:i:s",$luValue['log_time']);
				}
				$login_usernameInfoAjax = array('total' => $total, 'rows' => $login_usernameInfo);
				$this->ajaxReturn($login_usernameInfoAjax);
			}else{
				exit($this->base->changeCallback_error);
			}
		}else{
			exit($this->base->changeCallback_error);
		}
	}
	
	//我的操作
	public function myoperation(){
		if($this->base->login && $this->base->authorityCheck('myoperation')){
			$this->display();
		}else{
			$this->base->noLogin();
		}
	}
	
	//我的操作->Json数据
	public function myoperationJson(){
		if($this->base->login && $this->base->authorityCheck('myoperation')){
			if(preg_match('/^[1-9]+[0-9]*$/',I('get.page')) && preg_match('/^[1-9]+[0-9]*/',I('get.rows'))){
				$log_username_M = M('log_'.session('username'));
				$total = $log_username_M->count();
				if(!$total)return false;
				$log_usernameInfo = $log_username_M->field('action,logtime,target')->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select();
				foreach($log_usernameInfo as $luKey => $luValue){
					$log_usernameInfo[$luKey]['logtime'] = date("Y-m-d H:i:s",$luValue['logtime']);
				}
				$log_usernameInfoAjax = array('total' => $total, 'rows' => $log_usernameInfo);
				$this->ajaxReturn($log_usernameInfoAjax);
			}else{
				exit($this->base->changeCallback_error);
			}
		}else{
			exit($this->base->changeCallback_error);
		}
	}
	
	//成员登录
	public function memberloginfo(){
		if($this->base->login && $this->base->authorityCheck('memberloginfo')){
			$this->display();
		}else{
			$this->base->noLogin();
		}
	}
	
	//成员登录->Json数据
	public function memberloginfoJson($action = false,$userid = false){
		if($this->base->login && $this->base->authorityCheck('memberloginfo') && $action){
			switch ($action){
			case 'menu':
				$this->ajaxReturn($this->base->memberInfo('Json'));
				break;
				
			case 'main':
				if(preg_match('/^[1-9]+[0-9]*$/',$userid) && preg_match('/^[1-9]+[0-9]*$/',I('get.page') && preg_match('/^[1-9]+[0-9]*$/',I('get.rows')))){
					$user_M = M('user');
					$userWhere['id'] = $userid;
					$userInfo = $user_M->field('username')->where($userWhere)->find();
					if($userInfo){
						$login_username_M = M('login_'.$userInfo['username']);
						$total = $login_username_M->count();
						if(!$total)return false;
						$login_usernameInfo = $login_username_M->field('username,ip,log_time,user_id')->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select();
						foreach($login_usernameInfo as $luKey => $luValue){
							$login_usernameInfo[$luKey]['log_time'] = $luValue['log_time'] ? date("Y-m-d H:i:s",$luValue['log_time']) : '';
						}
						$login_usernameInfoAjax = array('total' => $total, 'rows' => $login_usernameInfo);
						$this->ajaxReturn($login_usernameInfoAjax);
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
	}
	
	//成员操作
	public function memberoperation(){
		if($this->base->login && $this->base->authorityCheck('memberoperation')){
			$this->display();
		}else{
			$this->base->noLogin();
		}
	}
	
	//成员操作->Json数据
	public function memberoperationJson($action = false,$userid = false){
			if($this->base->login && $this->base->authorityCheck('memberoperation') && $action){
			switch ($action){
			case 'menu':
				$this->ajaxReturn($this->base->memberInfo('Json'));
				break;
				
			case 'main':
				if(preg_match('/^[1-9]+[0-9]*$/',$userid) && preg_match('/^[1-9]+[0-9]*$/',I('get.page') && preg_match('/^[1-9]+[0-9]*$/',I('get.rows')))){
					$user_M = M('user');
					$userWhere['id'] = $userid;
					$userInfo = $user_M->field('username')->where($userWhere)->find();
					if($userInfo){
						$log_username_M = M('log_'.$userInfo['username']);
						$total = $log_username_M->count();
						if(!$total)return false;
						$log_usernameInfo = $log_username_M->field('action,logtime,target')->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select();
						foreach($log_usernameInfo as $luKey => $luValue){
							$log_usernameInfo[$luKey]['logtime'] = $luValue['logtime'] ? date("Y-m-d H:i:s",$luValue['logtime']) : '';
						}
						$log_usernameInfoAjax = array('total' => $total, 'rows' => $log_usernameInfo);
						$this->ajaxReturn($login_usernameInfoAjax);
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
	}
	
	//系统错误
	public function errormessage(){
		if($this->base->login && $this->base->authorityCheck('errormessage')){
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				if(I('post.action') == 'delall' && $this->base->loginInfo(I('post.identify')) && $loginInfo['loginstatus'] >= $this->base->extraSet('errormessage')){
					$error_message_M = M('error_message');
					$error_messageInfo = $error_message_M->where('1')->delete();
					if($error_messageInfo !== false){
						$this->base->writeLog(session('username'),'errormessage_delall','清空系统错误，清除数量：'.$error_messageInfo);
						exit('1');
					}else{
						$this->base->erroeMessage(session('username'),'清空系统错误失败');
						exit('删除失败，请重试');
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				$this->assign('loginstatus',$loginInfo['loginstatus']);
				$this->assign('EXTRA_errormessage',$this->base->extraSet('errormessage'));
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//系统错误->Json数据
	public function errormessageJson(){
		if($this->base->login && $this->base->authorityCheck('errormessage') && preg_match('/^[1-9]+[0-9]*$/',I('get.page')) && preg_match('/^[1-9]+[0-9]*$/',I('get.rows'))){
			$error_message_M = M('error_message');
			$total = $error_message_M->count();
			if(!$total)return false;
			$error_messageInfo = $error_message_M->limit($this->base->queryLimit(I('get.page'),I('get.rows')))->order('`id` desc')->select();
			foreach($error_messageInfo as $emKey => $emValue){
				$error_messageInfo[$emKey]['logtime'] = $emValue['logtime'] ? date("Y-m-d H:i:s",$emValue['logtime']) : '';
			}
			$error_messageInfoAjax = array('total' => $total, 'rows' => $error_messageInfo);
			$this->ajaxReturn($error_messageInfoAjax);
		}else{
			exit($this->base->changeCallback_error);
		}
	}
}