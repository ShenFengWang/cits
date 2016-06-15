<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
	//登录确认
	public $login;
	
	//修改回执
	public $changeCallback_success = '数据处理成功';
	public $changeCallback_error = '数据处理失败';
	
	//大类
	public $targetFormat = array('operation', 'management', 'report', 'log', 'set');
	
	//大类及中文名
	public $TargetNameFormat = array('operation' => '操作', 'management' => '管理','report' => '报表', 'log' => '日志', 'set' => '设置');
	
	//主题
	public $themes = array('default','black','bootstrap','gray','metro');
	
	//登录信息
	public function loginInfo($identify = false){
		if($this->login){
			$info = F('authority_'.session('username'));
			if($identify){
				return $identify == $info['identify'];
			}else{
				return $info;
			}
		}else{
			return false;
		}
	}
	
	//验证权限
	public function authorityCheck($authorityName = false){
		if($this->login && $authorityName){
			$authority = F('authority_'.session('username'));
			return $authority[$authorityName];
		}else{
			return false;
		}
	}
	
	//记录错误信息
	public function errorMessage($username = null,$action = null,$message = null){
		$error_message_M = M('error_message');
		$error_messageData['message'] = '操作人：['.$username.']<br>动作：['.$action.']<br>信息：['.$message.']';
		$error_messageData['logtime'] = time();
		$error_message_M->data($error_messageData)->add();
	}
	
	//写入个人日志
	public function writeLog($username = false,$action = false,$target = ''){
		if($username && $action){
			$log_M = M('log_'.$username);//确认数据库表
			if(!$target)$target = '自己';
			$logData = array('logtime' => time(), 'target' => $target);
			switch($action){//动作
			case 'password_change':
				$logData['action'] = '修改密码';
				break;
				
			case 'themes':
				$logData['action'] = '修改主题';
				break;
				
			case 'changegroupname':
				$logData['action'] = '修改区域或组名称';
				break;
				
			case 'addgroup':
				$logData['action'] = '新增用户组';
				break;
				
			case 'creategroup':
				$logData['action'] = '创建区域';
				break;
				
			case 'deletegroup':
				$logData['action'] = '删除区域/组';
				break;
				
			case 'password_reset':
				$logData['action'] = '重置密码';
				break;
				
			case 'actoradmin':
				$logData['action'] = '角色权限更改';
				break;
				
			case 'authorityadd':
				$logData['action'] = '新增基础权限';
				break;
				
			case 'authoritydel':
				$logData['action'] = '删除基础权限';
				break;
				
			case 'authorityseton':
				$logData['action'] = '基础权限启用';
				break;
				
			case 'authoritysetoff':
				$logData['action'] = '基础权限禁用';
				break;
				
			case 'reg_cert_random':
				$logData['action'] = '新增注册随机码';
				break;
				
			case 'reg_cert_idcard':
				$logData['action'] = '新增/更新注册身份证';
				break;
				
			case 'reg_cert_remove':
				$logData['action'] = '删除注册凭证';
				break;
				
			case 'member_remove':
				$logData['action'] = '删除正式成员';
				break;
				
			case 'member_edit':
				$logData['action'] = '修改正式成员信息';
				break;
				
			case 'member_unsign_remove':
				$logData['action'] = '删除未授权成员';
				break;
				
			case 'member_unsign_edit':
				$logData['action'] = '编辑未授权成员';
				break;
				
			case 'ticket_addsender':
				$logData['action'] = '新增发券人';
				break;
				
			case 'ticket_addticket':
				$logData['action'] = '新增现金券';
				break;
				
			case 'ticketadmin_remove':
				$logData['action'] = '删除现金券';
				break;
				
			case 'ticketadmin_edit':
				$logData['action'] = '编辑现金券';
				break;
				
			case 'errormessage_delall':
				$logData['action'] = '清空系统错误';
				break;
				
			case 'extraset_edit':
				$logData['action'] = '修改特殊操作';
				break;
				
			case 'route_add':
				$logData['action'] = '新增旅游线路';
				break;
				
			case 'route_edit':
				$logData['action'] = '编辑旅游线路';
				break;
				
			case 'sendercut_add':
				$logData['action'] = '新增发券人提成规则';
				break;
				
			case 'sendercut_edit':
				$logData['action'] = '编辑发券人提成规则';
				break;
				
			case 'sendercut_remove':
				$logData['action'] = '删除发券人提成规则';
				break;
				
			case 'neworder':
				$logData['action'] = '创建新订单';
				break;
				
			case 'myorder_edit':
				$logData['action'] = '编辑订单';
				break;
				
			case 'myorder_remove':
				$logData['action'] = '删除订单';
				break;
				
			case 'unconfirm_customer_edit':
				$logData['action'] = '编辑游客信息';
				break;
				
			case 'unconfirm_customer_remove':
				$logData['action'] = '删除游客信息';
				break;
				
			case 'orderconfirm':
				$logData['action'] = '确认订单';
				break;
				
			default :
				$logData = false;
				break;
			}
			if($logData){
				if(!$log_M->data($logData)->add()){
					$this->errorMessage(session('username'),'写入日志','写日志失败');
				}else{
					$this->flashSession();
				}
			}
		}
	}
	
	//刷新session
	private function flashSession(){
		if(session('?username') && session('?password')){
			session('username',session('username'));
			session('password',session('password'));
		}
	}
	
	//身份证末位X转换
	public function idcardChange($idcard = false){
		if($idcard && preg_match('/[0-9Xx]{18,19}/',$idcard)){
			$length = strlen($idcard);
			if($length == 18){
				$idcard = str_replace(array('X','x'),array(99,99),$idcard);
			}else if($length == 19){
				$idcard = preg_replace('/[9]{2}$/','X',$idcard);
			}
			return $idcard;
		}else{
			return 123;
		}
	}
	
	//返回首页
	public function noLogin(){
		exit('未登录状态');
	}
	
	//可操作成员
	public function memberInfo($action){
		if($this->login){
			$loginInfo = $this->loginInfo();
			$user_M = M('user');
			switch ($loginInfo['loginstatus']){
			case '2':
				$userWhere = array('ingroup' => $loginInfo['ingroup'], 'loginstatus' => 1);
				break;
				
			case '3':
				$group_list_M = M('group_list');
				$group_listWhere['parent'] = $loginInfo['ingroup'];
				$childGroup = $group_list_M->field('id')->where($group_listWhere)->select();//区域下的组信息
				$memberGroup = array($loginInfo['ingroup']);
				if($childGroup){
					foreach($childGroup as $childGroupValue){
						array_push($memberGroup,$childGroupValue['id']);
					}
				}
				$userWhere = array('ingroup' => array('in',implode(',',$memberGroup)), 'loginstatus' => array('in','1,2'));
				break;
				
			case '4':
				$userWhere = array('loginstatus' => array('in','1,2,3'));
				break;
				
			default:
				return false;
				break;
			}
			$userList = $user_M->where($userWhere)->select();
			if($action == 'list'){
				return $userList;
			}else if($action == 'name'){
				$userName = array();
				foreach($userList as $userListValue){
					array_push($userName,$userListValue['username']);
				}
				return $userName;
			}else if($action == 'id'){
				$userId = array();
				foreach($userList as $userListValue){
					array_push($userId,$userListValue['id']);
				}
				return $userId;
			}else if($action == 'Json'){
				$userJson = array(array('id' => 0, 'text' => '普通', 'children' => array()));
				if($loginInfo['loginstatus'] > 2)array_push($userJson,array('id' => 0, 'text' => '组管理', 'children' => array()));
				if($loginInfo['loginstatus'] > 3)array_push($userJson,array('id' => 0, 'text' => '区域管理', 'children' => array()));
				foreach($userList as $userListValue){
					switch($userListValue['loginstatus']){
					case '1':
						array_push($userJson[0]['children'],array('id' => $userListValue['id'], 'text' => $userListValue['realname']));
						break;
					case '2':
						array_push($userJson[1]['children'],array('id' => $userListValue['id'], 'text' => $userListValue['realname']));
						break;
					case '3':
						array_push($userJson[2]['children'],array('id' => $userListValue['id'], 'text' => $userListValue['realname']));
						break;
					default:
						break;
					}
				}
				return $userJson;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//可操作组
	public function groupInfo($action = false){
		if($this->login){
			$loginInfo = $this->loginInfo();
			$group_list_M = M('group_list');
			switch ($loginInfo['loginstatus']){
			case '3':
				$group_listWhere = array('parent' => $loginInfo['ingroup'], 'type' => 0);
				break;
				
			case '4':
				$group_listWhere = array('parent' => 0, 'type' => 1);
				break;
				
			default:
				return false;
				break;
			}
			if($action == 'json'){
				$group_listInfo = $group_list_M->field('id,name as text')->where($group_listWhere)->select();
				if($group_listInfo && $loginInfo['loginstatus'] == 4){
					foreach($group_listInfo as $glKey => $glValue){
						$group_listInfo[$glKey]['children'] = array();
					}
				}
				if($loginInfo['loginstatus'] == 4){
					$group_listWhere = array('type' => 0);
					if($group_list_groupInfo = $group_list_M->field('id,name,parent')->where($group_listWhere)->select()){
						foreach($group_list_groupInfo as $glgKey => $glgValue){
							foreach($group_listInfo as $glKey => $glValue){
								if($group_listInfo[$glKey]['id'] == $glgValue['parent']){
									$group_listInfo[$glKey]['children'][] = array('id' => $glgValue['id'], 'text' => $glgValue['name']);
								}
							}
						}
					}
				}
				return $group_listInfo;
			}else if($action == 'id'){
				$idlist = array();
				if($loginInfo['loginstatus'] == 4){
					$group_listInfo = $group_list_M->field('id')->select();
				}else{
					$group_listInfo = $group_list_M->field('id')->where($group_listWhere)->select();
				}
				foreach($group_listInfo as $glValue){
					array_push($idlist,$glValue['id']);
				}
				return $idlist;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//额外设置
	function extraSet($field = false, $val = false){
		if($field){
			if($val === false){
				$extra = F('EXTRA_'.$field);
				if($extra === false){
					$extraset_M = M('extraset');
					$extrasetWhere['field'] = $field;
					$extrasetInfo = $extraset_M->field('value')->where($extrasetWhere)->find();
					if($extrasetInfo){
						F('EXTRA_'.$field,$extrasetInfo['value']);
						$extra = $extrasetInfo['value'];
					}else{
						return false;
					}
				}
				return $extra;
			}else{
				if(preg_match('/^[0-9]+$/',$val)){
					$extraset_M = M('extraset');
					$extrasetWhere['field'] = $field;
					$extrasetInfo = $extraset_M->field('name,vstart,vend,value')->where($extrasetWhere)->find();
					if($extrasetInfo){
						if($extrasetInfo['value'] == $val)return true;
						if($val >= $extrasetInfo['vstart'] && $val <= $extrasetInfo['vend']){
							if($extraset_M->where($extrasetWhere)->setField('value',$val)){
								F('EXTRA_'.$field,$val);
								$this->writeLog(session('username'),'extraset_edit','操作名：'.$extrasetInfo['name'].'，值：'.$extrasetInfo['value'].' => '.$val);
								return true;
							}else{
								$this->errorMessage(session('username'),'修改特殊操作失败',$extrasetInfo['name'].'：'.$extrasetInfo['value'].' => '.$val);
								return false;
							}
						}else{
							return false;
						}
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}
	
	//查询limit
	function queryLimit($page,$rows){
		if(preg_match('/^[1-9]+[0-9]*$/',$page) && preg_match('/^[1-9]+[0-9]*$/',$rows)){
			$limit = ($page - 1) * $rows;
			$callback = $limit.','.$rows;
			return $callback;
		}else{
			return false;
		}
	}
	
	//初始化
	function __construct(){
		parent::__construct();
		$this->login = (session('?username') && session('?password')) ? true : false;
		date_default_timezone_set('Asia/Shanghai');
		$this->flashSession();
	}
}