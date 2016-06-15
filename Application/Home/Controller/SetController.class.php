<?php
namespace Home\Controller;
use Think\Controller;
class SetController extends Controller {
	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}
	
	//密码修改
	public function password_change(){
		if($this->base->login && $this->base->authorityCheck('password_change')){//验证登录状态及权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					if(preg_match('/[A-Za-z0-9_]{10,30}/',I('post.oldpassword')) && preg_match('/[A-Za-z0-9_]{10,30}/',I('post.newpassword')) &&
					preg_match('/[A-Za-z0-9_]{10,30}/',I('post.confirmpassword')) && (I('post.newpassword') == I('post.newpassword'))){//验证提交信息
						$user_M = M('user');
						$loginInfo = $this->base->loginInfo();
						$userWhere['id'] = $loginInfo['id'];
						if(sha1(I('post.oldpassword')) == $user_M->where($userWhere)->getField('password')){//验证原始密码
							if($user_M->where($userWhere)->setField('password',sha1(I('post.newpassword')))){//修改密码
								$this->base->writeLog(session('username'),'password_change');
								exit('修改成功');
							}else{
								$this->base->errorMessage(session('username'),'修改密码','数据库更新失败或未更新信息');
								exit($this->base->changeCallback_error);
							}
						}else{
							exit('原始密码错误');
						}
					}else{
						exit('提交信息错误');
					}
				}else{
					exit('识别码错误');
				}
			}else{
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//主题设置
	public function themes(){
		if($this->base->login && $this->base->authorityCheck('themes')){//验证登录状态及权限
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					$themesList = $this->base->themes;
					if(in_array(I('post.themes'),$themesList)){//验证提交信息
						$user_M = M('user');
						$userWhere['id'] = $loginInfo['id'];
						if($user_M->where($userWhere)->setField('themes',I('post.themes'))){//修改主题
							$this->base->writeLog(session('username'),'themes');
							exit('1');
						}else{
							$this->base->errorMessage(session('username'),'修改主题','数据库更新失败或未更新信息');
							exit($this->base->changeCallback_error);
						}
					}else{
						exit('提交错误');
					}
				}else{
					exit('识别码错误');
				}
			}else{
				$user_M = M('user');
				$userWhere['id'] = $loginInfo['id'];
				$themesSelected = $user_M->where($userWhere)->getField('themes');
				$this->assign('themesSelected',$themesSelected);
				$this->assign('themes',$this->base->themes);
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//个人信息查看
	public function personalinfo($abc = false,$getJson = false){//get首位信息自动生成，采用第二信息
		if($this->base->login && $this->base->authorityCheck('personalinfo')){//验证登录状态及权限
			if($getJson){//是否读取json
				$loginInfo = $this->base->loginInfo();
				$user_M = M('user');
				$userWhere['id'] = $loginInfo['id'];
				$info = $user_M->field('username,realname,bankcard,bankname,idcardnumber,mobile,loginstatus,authority,ingroup')->where($userWhere)->find();
				if($info){
					$info['idcardnumber'] = $this->base->idcardChange($info['idcardnumber']);
					if($info['loginstatus'] == 4){
						$info['loginstatus'] = $info['authority'] = $info['ingroup'] = '超级管理员';
					}else if(($info['loginstatus'] > 0) && ($info['loginstatus'] < 4)){
						$actor_M = M('actor');
						$authority_M = M('authority');
						$group_list_M = M('group_list');
						$actorWhere['id'] = $info['loginstatus'];
						$authorityWhere['id'] = $info['authority'];
						$group_listWhere['id'] = $info['ingroup'];
						if($actorInfo = $actor_M->field('name')->where($actorWhere)->find()){
							$info['loginstatus'] = $actorInfo['name'];
						}else{
							$this->base->errorMessage(session('username'),'查询个人信息','无法读取角色信息');
							exit('角色信息异常');
						}
						if($authorityInfo = $authority_M->field('name')->where($authorityWhere)->find()){
							$info['authority'] = $authorityInfo['name'];
						}else{
							$this->base->errorMessage(session('username'),'查询个人信息','无法读取基础权限信息');
							exit('基础权限信息异常');
						}
						if($group_listInfo = $group_list_M->field('name,parent')->where($group_listWhere)->find()){
							$info['ingroup'] = $group_listInfo['name'];
						}else{
							$this->base->errorMessage(session('username'),'查询个人信息','无法读取组信息');
							exit('组信息异常');
						}
					}else{
						$this->base->errorMessage(session('username'),'查询个人信息','登录状态异常');
						exit('登录状态异常');
					}
					foreach($info as $infoKey => $infoValue){
						switch ($infoKey){
						case 'username':
							$infoKey = '用户名';
							break;
						case 'realname':
							$infoKey = '真实姓名';
							break;
						case 'bankcard':
							$infoKey = '银行卡号码';
							break;
						case 'bankname':
							$infoKey = '开户行信息';
							break;
						case 'idcardnumber':
							$infoKey = '身份证号码';
							break;
						case 'mobile':
							$infoKey = '手机号码';
							break;
						case 'ingroup':
							$infoKey = ($loginInfo['loginstatus'] == 3) ? '所在区域' : '所在组';
							break;
						case 'loginstatus':
							$infoKey = '角色';
							break;
						case 'authority':
							$infoKey = '基础权限';
							break;
						default:
							$infoKey = '未定义';
							break;
						}
						$personalinfo[] = array('itemid' => $infoKey,'personalinfo' => $infoValue);
					}
					if($loginInfo['loginstatus'] < 3 && $loginInfo['loginstatus'] > 0){
						$group_listWhere['id'] = $group_listInfo['parent'];
						$areaInfo = $group_list_M->field('name')->where($group_listWhere)->find();
						$personalinfo[] = array('itemid' => '所在区域', 'personalinfo' => $areaInfo['name']);
					}
					$this->ajaxReturn($personalinfo);
				}else{
					$this->base->errorMessage(session('username'),'查询个人信息','无法读取数据');
					exit('查询个人信息失败');
				}
			}else{
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//特殊操作
	public function extraset(){
		if($this->base->login && $this->base->authorityCheck('extraset')){//验证登录和权限
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				//验证特殊操作权限和提交数据
				if($loginInfo['loginstatus'] >= $this->base->extraSet('extraset') && preg_match('/^[1-9]+[0-9]*$/',I('post.id')) && 
				I('post.action') == 'edit' && $this->base->loginInfo(I('post.identify')) && $postData = I('post.data')){
					if(preg_match('/^[0-9]+$/',$postData['value'])){//验证修改值
						$extraset_M = M('extraset');
						$extrasetWhere['id'] = I('post.id');
						$extrasetInfo = $extraset_M->field('name,field,vstart,vend,value')->where($extrasetWhere)->find();
						if($extrasetInfo && $extrasetInfo['name'] == $postData['name']){
							if($extrasetInfo['value'] == $postData['value'])exit('未修改值');
							if($postData['value'] >= $extrasetInfo['vstart'] && $postData['value'] <= $extrasetInfo['vend']){//验证值范围
								if($this->base->extraSet($extrasetInfo['field'],$postData['value'])){
									exit('1');
								}else{
									exit('数据修改失败，请重试');
								}
							}else{
								exit('设定值错误');
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
			}else{
				$this->assign('loginstatus',$loginInfo['loginstatus']);
				$this->assign('EXTRA_extraset',$this->base->extraSet('extraset'));
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//特殊操作->Json数据
	public function extrasetJson(){
		if($this->base->login && $this->base->authorityCheck('extraset')){
			$extraset_M = M('extraset');
			$extrasetInfo = $extraset_M->field('id,name,tip,value')->select();
			$this->ajaxReturn($extrasetInfo);
		}else{
			exit($this->base->changeCallback_error);
		}
	}
}