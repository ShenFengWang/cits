<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	//首页
    public function index(){
	    if(IS_POST){//登录
	    	//验证输入
	    	if(!preg_match('/^[A-Za-z0-9_]{10,20}$/',I('post.username')) || !preg_match('/^[a-z0-9]{40}$/',I('post.password'))){
	    		session('post_error_times',session('post_error_times') + 1);
	    		$this->assign('login_check',0);
	    		$this->display();
	    		exit;
	    	}
	    	$user_M = M('user');
	    	$user_Where = array('username' => I('post.username'),'password' => I('post.password'),'loginstatus' => array('gt',0),'authority' => array('gt',0));
	    	$userInfo = $user_M->field('id,themes')->where($user_Where)->find();
	    	if($userInfo){
	    		$login_ip_M = M('login_ip');//记录登录信息
	    		$login_username_M = M('login_'.I('post.username'));
	    		$logip = $this->getip() ? $this->getip() : 'unknown';
	    		$loginipData = array('username' => I('post.username'), 'ip' => $logip, 'log_time' => time(), 'user_id' => $userInfo['id']);
	    		if($login_ip_M->data($loginipData)->add() && $login_username_M->data($loginipData)->add()){
		    		session('username',I('post.username'));
		    		session('password',I('post.password'));
		    		session('post_error_times',0);
		    		$this->assign('themes',$userInfo['themes']);
		    		$this->display('main');
	    		}else{
	    			$this->assign('login_check',1);
	    			$this->display();
	    		}
	    	}else{
	    		session('post_error_times',session('post_error_times') + 1);
	    		$this->assign('login_check',0);
	    		$this->display();
	    	}
	    }else{//仅打开
	    	if(session('?username') && session('?password') && preg_match('/^[A-Za-z0-9_]{10,20}$/',session('username')) && preg_match('/^[a-z0-9]{40}$/',session('password'))){
	    		$user_M = M('user');
	    		$user_Where = array('username' => session('username'),'password' => session('password'),'loginstatus' => array('gt',0),'authority' => array('gt',0));
	    		$userInfo = $user_M->field('id,themes')->where($user_Where)->find();
	    		if($userInfo){
	    			$this->assign('themes',$userInfo['themes']);
	    			$this->display('main');
	    		}else{
	    			$this->assign('login_check',1);
	    			$this->display();
	    		}
	    	}else{
	    		$this->assign('login_check',1);
	    		$this->display();
	    	}
	    }
    }
    
    //注册码或身份证检验
    public function proof(){
    	$this->_before_index();
    	if(IS_POST){
    		$proofid = I('post.proofid');
    		$callback['status'] = 0;
    		$reg_cert_M = M('reg_cert');
    		
    		//验证post
    		if(preg_match('/^[0-9]{6}$/',$proofid)){
    			$reg_cert_Where['randomnumber'] = $proofid;
    		}else if(preg_match('/^[0-9Xx]{18}$/',$proofid)){
    			$reg_cert_Where['idcardnumber'] = str_replace(array('X','x'),array('99','99'),$proofid);
    		}else{
    			session('post_error_times',session('post_error_times') + 1);
    			$callback['error'] = session('post_error_times');
    			$this->ajaxReturn($callback);
    			exit;
    		}
    		
    		//确认注册信息
    		$regInfo = $reg_cert_M->field('id')->where($reg_cert_Where)->find();
    		if($regInfo){
    			//更新注册验证+时间
    			$regInfo_Where['id'] = $regInfo['id'];
    			$regInfoCertUpdate['cert_time'] = time();
    			$regInfoCertUpdate['cert'] = sha1(time());
    			if($reg_cert_M->where($regInfo_Where)->setField($regInfoCertUpdate)){
    				$regInfo['cert'] = $regInfoCertUpdate['cert'];
    				$callback = $regInfo;
    				$callback['status'] = 1;
    			}
    		}else{//增加错误次数
    			session('post_error_times',session('post_error_times') + 1);
    			$callback['error'] = session('post_error_times');
    			if(session('post_error_times') > 9)$this->_before_index();
    		}
    		$this->ajaxReturn($callback);
    	}else{
    		exit;
    	}
    }
    
    //注册页面
    public function reg($id = false,$cert = false){
    	$this->_before_index();
    	//验证请求
    	if(!preg_match('/^[0-9]+$/',$id) || !preg_match('/^[a-z0-9]{40}$/',$cert)){
    		session('post_error_times',session('post_error_times') + 1);
    		exit;
    	}
    	
    	//确认并读取注册信息
    	$reg_cert_M = M('reg_cert');
    	$reg_cert_Where = array('id' => $id,'cert' => $cert);
    	$regInfo = $reg_cert_M->field('idcardnumber,realname,bankcard,bankname,ingroup,loginstatus,cert_time,mobile,authority')->where($reg_cert_Where)->find();
    	
    	if(IS_POST){//注册
    		if($regInfo){
    			$callback['status'] = 0;
    			if($regInfo['cert_time'] + 1800 < time()){//判断超时
    				$callback['message'] = '原因：注册超时！';
    				$this->ajaxReturn($callback);
    				exit;
    			}else{
    				//正则验证post
    				if(preg_match('/^[A-Za-z0-9_]{10,20}$/',I('post.username')) && preg_match('/^[A-Za-z0-9_]{10,30}$/',I('post.password')) && 
    				(I('post.password') == I('post.passwordconfirm')) && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',I('post.realname')) && 
    				preg_match('/^[0-9Xx]{18}$/',I('post.idcardnumber')) && preg_match('/^[0-9]{16,19}$/',I('post.bankcard')) && 
    				preg_match('/.{4,30}/',I('post.bankname')) && preg_match('/^[1][0-9]{10}$/',I('post.mobile'))){
    					$user_M = M('user');
    					$user_Where['username'] = I('post.username');
    					$usernameCheck = $user_M->field('id')->where($user_Where)->find();
    					if($usernameCheck){//判断是否有相同注册名
    						$callback['status'] = -1;
    						$callback['message'] = '该用户名已被注册！';
    					}else{
    						//初始化数据
	    					$user_Data = array('username' => I('post.username'),'password' => sha1(I('post.password')),'realname' => I('post.realname'),
	    					'ingroup' => $regInfo['ingroup'],'bankcard' => I('post.bankcard'),'bankname' => I('post.bankname'),'mobile' => I('post.mobile'),
	    					'idcardnumber' => str_replace(array('x','X'),array('99','99'),I('post.idcardnumber')),'loginstatus' => $regInfo['loginstatus'],
	    					'authority' => $regInfo['authority']);
	    					$Model = new \Think\Model();
	    					$logTable = 'cits_log_'.I('post.username');
	    					$loginTable = 'cits_login_'.I('post.username');
	    					if($user_M->data($user_Data)->add()){
	    						if($reg_cert_M->where($reg_cert_Where)->delete()){
	    							//注册成功，返回信息
/*	    							$newLogConfirm = $Model->query("create table if not exists `$logTable` (`id` int not null auto_increment primary key,`action` varchar(1000) not null,`logtime` int not null,`target` text not null)");
	    							if(!$newLogConfirm){
	    								$error_message_M = M('error_message');
	    								$error_message_Data['message'] = '无法新增日志表：'.I('post.username');
	    								$error_message_M->data($error_message_Data)->add();
	    							}*/
	    							$sql = mysqli_connect('localhost','root','','cits');
	    							if(mysqli_query($sql,"create table if not exists `$logTable` (`id` int not null auto_increment primary key,`action` varchar(1000) not null,`logtime` int not null,`target` text not null)") == false){
	    								$error_message_M = M('error_message');
	    								$error_message_Data['message'] = '无法新增日志表：'.I('post.username');
	    								$error_message_M->data($error_message_Data)->add();
	    							}
	    							if(mysqli_query($sql,"create table if not exists `$loginTable` (`id` int not null auto_increment primary key,`username` varchar(20) not null,`ip` varchar(15) not null,`log_time` int not null,`user_id` int not null)") == false){
	    								$error_message_M = M('error_message');
	    								$error_message_Data['message'] = '无法新增登录表：'.I('post.username');
	    								$error_message_M->data($error_message_Data)->add();
	    							}
	    							mysqli_close($sql);
	    							$callback['status'] = 1;
	    							$callback['message'] = $regInfo['loginstatus'] ? '现在，你可以登录系统了！' : '请联系管理员为您分配权限。<br><span style = "color:red;">警告：在未分配权限的情况下登录系统仍将提示用户名或密码错误。<br>请勿尝试登录，否则可能被封禁IP！</span>';
	    						}else{
	    							//注册失败，删除数据
	    							$user_Delete['username'] = I('post.username');
	    							if(!$user_M->where($user_Delete)->delete()){
	    								//数据库操作异常
	    								$error_message_M = M('error_message');
	    								$error_message_Data['message'] = '无法删除数据表(user)信息：username = '.I('post.username');
	    								$error_message_M->data($error_message_Data)->add();
	    								$callback['message'] = '原因：未知，请联系管理员！';
	    							}else{
	    								$callback['message'] = '原因：新增用户失败！';
	    							}
	    						}
	    					}else{
	    						$callback['message'] = '原因：新增用户失败！';
	    					}
    					}
    				}else{
    					$callback['message'] = '原因：数据提交错误！';
    				}
    				$this->ajaxReturn($callback);
    			}
    		}
    	}else{//读取页面
	    	if($regInfo && ($regInfo['cert_time'] + 1800 > time())){
	    		unset($regInfo['cert_time']);
	    		unset($regInfo['ingroup']);
	    		unset($regInfo['loginstatus']);
	    		foreach($regInfo as $regInfoKey => $regInfoValue){
	    			if(!$regInfoValue)$regInfo[$regInfoKey] = '';
	    		}
	    		$regInfo['idcardnumber'] = (strlen($regInfo['idcardnumber']) == 19) ? preg_replace('/[99]{2}$/','X',$regInfo['idcardnumber']) : $regInfo['idcardnumber'];
	    		$this->assign('regInfo',$regInfo);
	    		$this->display();
	    	}else{
	    		exit('注册超时');
	    	}
    	}
    }
    
    //前置操作
    public function _before_index(){
    	//初始化时间时区
    	date_default_timezone_set('Asia/Shanghai');
    
    	//查询ip是否被禁止
    	$timeNow = time();
    	if($userIp = $this->getip()){
	    	$forbidden_ip_M = M('forbidden_ip');
	    	$forbidden_ip_Where['ip'] = $userIp;
	    	$isForbidden = $forbidden_ip_M->field('f_time')->where($forbidden_ip_Where)->find();
	    	if($isForbidden){
	    		if($isForbidden['f_time'] + 604800 > $timeNow){
	    			exit;//一星期内则继续禁止
	    		}else{
	    			$forbidden_ip_M->where($forbidden_ip_Where)->delete();//超过一星期则删除
	    		}
	    	}
    	}
    	
    	//初始化post错误数，超过9次则禁止
        if(session('?post_error_times')){
        	if(session('post_error_times') > 9){
        		if($userIp){
	        		$forbidden_ip_Data['f_time'] = $timeNow;
	        		$forbidden_ip_Data['ip'] = $userIp;
	        		$forbidden_ip_M->data($forbidden_ip_Data)->add();
        		}
        		exit;
        	}
        }else{
        	session('post_error_times',0);
        }
    }
    
    //获取IP
    private function getip() {
//	if($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]){ 
//		$ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
//	}elseif($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]){
//		$ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
//	}elseif($HTTP_SERVER_VARS["REMOTE_ADDR"]) {
//		$ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
//	}else
	if(getenv("HTTP_X_FORWARDED_FOR")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	}elseif(getenv("HTTP_CLIENT_IP")) {
		$ip = getenv("HTTP_CLIENT_IP");
	}elseif(getenv("REMOTE_ADDR")){
		$ip = getenv("REMOTE_ADDR");
	}else{
		$ip = false;
	}
	return $ip;
	}
}