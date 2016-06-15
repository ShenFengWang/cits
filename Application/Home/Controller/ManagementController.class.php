<?php
namespace Home\Controller;
use Think\Controller;
class ManagementController extends Controller {
	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}
	
	//区域和组
	public function groupadmin(){
		if($this->base->login && $this->base->authorityCheck('groupadmin')){//验证登录和权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					$group_list_M = M('group_list');
					$group_listWhere['id'] = preg_match('/[1-9][0-9]*/',I('post.groupid')) ? I('post.groupid') : exit('提交数据错误');
					switch(I('post.action')){//区分动作
					
					//保存组名称
					case 'save':
						$newGroupName = I('post.groupname') ? I('post.groupname') : exit('提交数据错误');
						$oldGroupName = $group_list_M->field('name')->where($group_listWhere)->find();
						if($oldGroupName){//验证是否存在组
							if($oldGroupName['name'] != $newGroupName){//验证名称是否相同
								if($group_list_M->where($group_listWhere)->setField('name',$newGroupName)){//修改组名
									$this->base->writeLog(session('username'),'changegroupname',$oldGroupName['name'].' => '.$newGroupName);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'修改区域或组名称',$oldGroupName['name'].' => '.$newGroupName.' 信息更改失败');
									exit($this->base->changeCallback_error);
								}
							}else{
								exit('信息未修改');
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//新增组
					case 'add':
						$group_listWhere['type'] = 1;
						$group_listWhere['name'] = I('post.groupname') ? I('post.groupname') : exit('提交数据错误');
						if($group_list_M->where($group_listWhere)->find()){//验证是否存在组
							$group_listData = array('name' => '新增用户组', 'parent' => I('post.groupid'));
							if($group_list_M->data($group_listData)->add()){//新增组
								$this->base->writeLog(session('username'),'addgroup','父区域名称：'.I('post.groupname'));
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'新增用户组失败',I('post.groupname').' => 新增失败');
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//创建区域
					case 'create':
						if(I('post.groupid') == I('post.identify')){//验证相同
							$group_listData = array('name' => '新建区域', 'type' => 1);
							if($group_list_M->data($group_listData)->add()){//创建区域
								$this->base->writeLog(session('username'),'creategroup','创建区域');
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'创建失败','数据库新增失败');
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//删除区域/组
					case 'del':
						$group_listWhere['name'] = I('post.groupname') ? I('post.groupname') : exit('提交数据错误');
						$groupType = $group_list_M->field('type')->where($group_listWhere)->find();
						if($groupType){//验证组是否存在
							$user_M = M('user');
							$userConfirm['ingroup'] = I('post.groupid');
							if($groupType['type']){//根据组类型验证该组下是否有成员或组
								$group_listConfirm['parent'] = I('post.groupid');
								if($group_list_M->where($group_listConfirm)->find() || $user_M->where($userConfirm)->find()){
									exit($this->base->changeCallback_error);
								}
							}else{
								if($user_M->where($userConfirm)->find())exit($this->base->changeCallback_error);
							}
							if($group_list_M->where($group_listWhere)->delete()){//删除组
								$this->base->writeLog(session('username'),'deletegroup','区域/组名称：'.I('post.groupname'));
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'删除区域/组失败','区域/组名称：'.I('post.groupname'));
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					default:
						exit('提交数据错误');
						break;
					}
				}else{
					exit('识别码验证失败');
				}
			}else{
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//区域和组->json数据
	public function groupadminJson(){
		if($this->base->login && $this->base->authorityCheck('groupadmin')){//验证登录和权限
			$group_list_M = M('group_list');
			$grouplistInfo = $group_list_M->select();
			if($grouplistInfo){//是否有组信息
				$Model = new \Think\Model();
				//查询组内成员数量
				$userGroupInfo = $Model->query('select `ingroup`, count(*) as `num` from `cits_user` where `loginstatus` > 0 and `loginstatus` < 4 group by `ingroup`');
				foreach($userGroupInfo as $ug){//grouplistInfo成员数量循环赋值
					foreach($grouplistInfo as $ulKey => $ulValue){
						if($ug['ingroup'] == $ulValue['id']){
							$grouplistInfo[$ulKey]['size'] = $ug['num'];
						}
						if(!isset($grouplistInfo[$ulKey]['size']))$grouplistInfo[$ulKey]['size'] = 0;
					}
				}
				$ajaxData = array();//初始化ajax数据
				foreach($grouplistInfo as $alKey => $alValue){//循环查询区域类型的数据
					$area_cache = array();//查询得出的当前区域数据
					if($alValue['type']){
						//初始化当前区域数据
						$area_cache = array('id' => $alValue['id'], 'name' => $alValue['name'], 'type' => '区域', 'size' => $alValue['size'], 'children' => array());
						unset($grouplistInfo[$alKey]);
						foreach($grouplistInfo as $glKey => $glValue){//循环查询该区域下组的数据
							$group_cache = array();//查询得出的当前组数据
							if($glValue['parent'] == $alValue['id']){
								//初始化当前组数据
								$group_cache = array('id' => $glValue['id'], 'name' => $glValue['name'], 'type'=> '组', 'size' => $glValue['size'], 'children' => array());
								array_push($area_cache['children'],$group_cache);//将当前组数据入栈给当前区域
								unset($grouplistInfo[$glKey]);
							}
						}
						array_push($ajaxData,$area_cache);//将当前区域数据入栈给ajax数据
					}
				}
				$this->ajaxReturn($ajaxData);
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//密码重置
	public function password_reset(){
		if($this->base->login && $this->base->authorityCheck('password_reset')){//验证登录状态和权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					if(preg_match('/[A-Za-z0-9_]{10,20}/',I('post.username'))){//验证提交信息
						$memberName = $this->base->memberInfo('name');//可操作成员用户名
						if(in_array(I('post.username'),$memberName)){//验证是否可操作
							$user_M = M('user');
							$userWhere['username'] = I('post.username');
							if($user_M->where($userWhere)->setField('password',sha1('a1b2c3d4e5'))){//重置密码
								$this->base->writeLog(session('username'),'password_reset','用户名：'.I('post.username'));
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'重置密码','重置对象：'.I('post.username').'，数据库更新失败');
								exit($this->base->changeCallback_error);
							}
						}else{
							exit('用户名错误或无权重置该用户！');
						}
					}else{
						exit($this->base->changeCallback_error);
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//角色权限
	public function actoradmin($menu = false){
		if($this->base->login && $this->base->authorityCheck('actoradmin')){//验证登录状态和权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					//验证提交数据
					if(preg_match('/[1-3]{1}/',I('post.actorid')) && preg_match('/[a-z0-9_]+/',I('post.col')) && ((I('post.action') == 'totrue') || (I('post.action') == 'tofalse'))){
						$target_url_M = M('target_url');
						$target_urlWhere['authority_name'] = I('post.col');
						$target_urlInfo = $target_url_M->field('name,type')->where($target_urlWhere)->find();
						if($target_urlInfo){//判断权限名存在
							$typeCheck = (I('post.actorid') == 1) ? 1 : 2;
							if($target_urlInfo['type'] == $typeCheck){//判断类型相同
								$actor_M = M('actor');
								$actorWhere['id'] = I('post.actorid');
								$actorInfo = $actor_M->field('name,'.I('post.col'))->where($actorWhere)->find();
								$actionCheck = (I('post.action') == 'totrue') ? 1 : 0;
								if($actorInfo){//角色表权限存在
									if($actorInfo[I('post.col')] != $actionCheck){//数据库和提交不同
										if($actor_M->where($actorWhere)->setField(I('post.col'),$actionCheck)){//修改权限
											$actorFrom = (I('post.action') == 'totrue') ? '禁用' : '启用';
											$actorTo = $actionCheck ? '启用' : '禁用';
											$this->base->writeLog(session('username'),'actoradmin','角色：'.$actorInfo['name'].' 权限：'.$target_urlInfo['name'].' 动作：'.$actorFrom.' => '.$actorTo);
											$this->reFlashActorAndAuthority('actor',I('post.actorid'));
											exit('1');
										}else{
											$this->base->errorMessage(session('username'),'角色权限修改','更新数据库失败');
											exit('修改失败，请重试');
										}
									}else{
										exit('无需修改');
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
						exit($this->base->changeCallback_error);
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				if($menu == 'menu'){
					$actor_M = M('actor');
					$getActor = $actor_M->field('id,name as text')->select();
					$this->ajaxReturn($getActor);
				}else{
					$this->display();
				}
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//角色权限->json数据
	public function actoradminJson($actor = false){
		if($this->base->login && $this->base->authorityCheck('actoradmin') && preg_match('/[1-3]{1}/',$actor)){//验证登录状态、权限和请求 
			$target_url_M = M('target_url');
			$target_urlWhere['type'] = ($actor == 1) ? 1 : 2;//判断类型
			$target_urlInfo = $target_url_M->field('name,authority_name')->where($target_urlWhere)->select();
			$actor_M = M('actor');
			$actorWhere['id'] = $actor;
			$actorInfo = $actor_M->where($actorWhere)->find();
			foreach($target_urlInfo as $tgKey => $tgValue){
				$target_urlInfo[$tgKey]['id'] = $tgValue['authority_name'];
				$target_urlInfo[$tgKey]['value'] = $actorInfo[$tgValue['authority_name']] ? '是' : '否';
				unset($target_urlInfo[$tgKey]['authority_name']);
			}
			$this->ajaxReturn($target_urlInfo);
		}else{
			$this->base->noLogin();
		}
	}
	
	//基础权限
	public function authorityadmin($menu = false){
		if($this->base->login && $this->base->authorityCheck('authorityadmin')){//判断登录和权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					if(preg_match('/[0-9]+/',I('post.menuid')) && I('post.name') && I('post.action')){//验证提交数据
						$authority_M = M('authority');
						switch (I('post.action')){//判断动作
						case 'add'://新增
							$authorityData['name'] = I('post.name');
							if($authority_M->data($authorityData)->add()){//新增权限
								$this->base->writeLog(session('username'),'authorityadd','新基础权限名称：'.I('post.name'));
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'新增基础权限','数据写入失败');
								exit('新增失败，请重试！');
							}
							break;
							
						case 'del'://删除
							$user_M = M('user');
							$userWhere['authority'] = I('post.menuid');
							if(!$user_M->where($userWhere)->find()){//是否有用户使用该权限
								$authorityWhere = array('id' => I('post.menuid'), 'name' => I('post.name'));
								if($authority_M->where($authorityWhere)->find()){//权限ID是否存在
									if($authority_M->where($authorityWhere)->delete()){//删除权限
										$this->base->writeLog(session('username'),'authoritydel','权限名称：'.I('post.name'));
										exit('1');
									}else{
										$this->base->errorMessage(session('username'),'删除基础权限','权限名称：'.I('post.name'));
										exit('删除失败，请重试！');
									}
								}else{
									exit($this->base->changeCallback_error);
								}
							}else{
								exit($this->base->changeCallback_error);
							}
							break;
							
						case 'edit'://修改名称
							$authorityWhere['id'] = I('post.menuid');
							$authorityInfo = $authority_M->field('name')->where($authorityWhere)->find();
							if(I('post.name') != $authorityInfo['name']){//提交名称与数据库对比
								if($authority_M->where($authorityWhere)->setField('name',I('post.name'))){
									$this->base->writeLog(session('username'),'authoritychange','原名称：'.$authorityInfo['name'].'，修改为：'.I('post.name'));
									exit('1');
								}else{
									$this->errorMessage(session('username'),'修改基础权限名称','原名称：'.$authorityInfo['name'].'，修改为：'.I('post.name'));
									exit('修改失败，请重试！');
								}
							}else{
								exit($this->base->changeCallback_error);
							}
							break;
							
						case 'totrue'://启用
							$authorityWhere['id'] = I('post.menuid');
							$authorityInfo = $authority_M->field('name,'.I('post.name'))->where($authorityWhere)->find();
							if($authorityInfo && !$authorityInfo[I('post.name')]){//权限存在且具体权限未启用
								$target_url_M = M('target_url');
								$target_urlWhere = array('authority_name' => I('post.name'), 'type' => 1);
								if($target_urlInfo = $target_url_M->field('name')->where($target_urlWhere)->find()){//权限信息且符合类型
									if($authority_M->where($authorityWhere)->setField(I('post.name'),1)){//启用权限
										$this->base->writeLog(session('username'),'authorityseton','权限名称：'.$authorityInfo['name'].'，具体权限：'.$target_urlInfo['name']);
										$this->reFlashActorAndAuthority('authority',I('post.menuid'));
										exit('2');
									}else{
										$this->base->errorMessage(session('username'),'基础权限启用失败','权限名称：'.$authorityInfo['name'].'，具体权限：'.$target_urlInfo['name']);
										exit($this->base->changeCallback_error);
									}
								}else{
									exit($this->base->changeCallback_error);
								}
							}else{
								exit($this->base->changeCallback_error);
							}
							break;
							
						case 'tofalse'://禁用
							$authorityWhere['id'] = I('post.menuid');
							$authorityInfo = $authority_M->field('name,'.I('post.name'))->where($authorityWhere)->find();
							if($authorityInfo && $authorityInfo[I('post.name')]){
								$target_url_M = M('target_url');
								$target_urlWhere = array('authority_name' => I('post.name'), 'type' => 1);
								if($target_urlInfo = $target_url_M->field('name')->where($target_urlWhere)->find()){
									if($authority_M->where($authorityWhere)->setField(I('post.name'),0)){
										$this->base->writeLog(session('username'),'authoritysetoff','权限名称：'.$authorityInfo['name'].'，具体权限：'.$target_urlInfo['name']);
										$this->reFlashActorAndAuthority('authority',I('post.menuid'));
										exit('2');
									}else{
										$this->base->errorMessage(session('username'),'基础权限禁用失败','权限名称：'.$authorityInfo['name'].'，具体权限：'.$target_urlInfo['name']);
										exit($this->base->changeCallback_error);
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
					exit($this->base->changeCallback_error);
				}
			}else{
				if($menu == 'menu'){//ajax菜单数据
					$authority_M = M('authority');
					$authorityInfo = $authority_M->field('id,name as text')->select();
					$Model = new \Think\Model();
					//按权限名称统计各名称下用户数量
					$userInfo = $Model->query('select `authority`,count(*) as num from `cits_user` where `authority` > 0 group by `authority`');
					foreach($userInfo as $userInfoValue){
						$authorityNum[$userInfoValue['authority']] = $userInfoValue['num'];
					}
					//格式化json数据
					foreach($authorityInfo as $aiKey => $aiValue){
						if(isset($authorityNum[$aiValue['id']])){
							$authorityInfo[$aiKey]['attributes'] = array('num' => $authorityNum[$aiValue['id']], 'name' => $authorityInfo[$aiKey]['text']);
							$authorityInfo[$aiKey]['text'] .= ' ('.$authorityNum[$aiValue['id']].')';
						}else{
							$authorityInfo[$aiKey]['attributes'] = array('num' => 0, 'name' => $authorityInfo[$aiKey]['text']);
							$authorityInfo[$aiKey]['text'] .= '(0)';
						}
					}
					$this->ajaxReturn($authorityInfo);
				}else{
					$this->display();
				}
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//基础权限->Json数据
	public function authorityadminJson($num = false){
		if($this->base->login && $this->base->authorityCheck('authorityadmin') && preg_match('/[1-9]+[0-9]*/',$num)){
			$authority_M = M('authority');
			$authorityWhere['id'] = $num;
			if($authorityInfo = $authority_M->where($authorityWhere)->find()){
				$target_url_M = M('target_url');
				$target_urlWhere['type']  = 1;
				$target_urlInfo = $target_url_M->field('name,authority_name')->where($target_urlWhere)->select();
				foreach($target_urlInfo as $tuKey => $tuValue){
					$target_urlInfo[$tuKey]['id'] = $target_urlInfo[$tuKey]['authority_name'];
					$target_urlInfo[$tuKey]['value'] = $authorityInfo[$tuValue['authority_name']] ? '是' : '否';
					unset($target_urlInfo[$tuKey]['authority_name']);
				}
				$this->ajaxReturn($target_urlInfo);
			}else{
				exit($this->base->changeCallback_error);
			}
		}else{
			$this->base->noLogin();
		}	
	}
	
	//更改权限后刷新五大类
	private function reFlashActorAndAuthority($table = false,$id = false){
		if($table && $id){
			$target_url_M = M('target_url');
			$target_urlInfo = $target_url_M->field('parent,authority_name')->select();//权限详情
			$table_M = M($table);
			$tableWhere['id'] = $id;
			if($tableInfo = $table_M->where($tableWhere)->find()){//角色或基础权限信息
				foreach($this->base->targetFormat as $targetFormatValue){//初始化五大类状态
					$tableData[$targetFormatValue] = 0;
				}
				foreach($target_urlInfo as $tuKey => $tuValue){
					//类目下有权限启用则赋值
					if(isset($tableInfo[$tuValue['authority_name']]) && $tableInfo[$tuValue['authority_name']] && isset($tableData[$tuValue['parent']])){
						$tableData[$tuValue['parent']] = 1;
					}
				}
				//更新错误记录日志
				if($table_M->where($tableWhere)->save($tableData) === false){
					$this->base->errorMessage(session('username'),'更新权限五大类型','表名：'.$table.' ID：'.$id);
				}
			}
			return false;
		}else{
			return false;
		}
	}
	
	//注册凭证
	public function reg_cert(){
		if($this->base->login && $this->base->authorityCheck('reg_cert')){//验证登录、权限
			if(IS_POST){
				if(preg_match('/^[0-9]+$/',I('post.id')) && $this->base->loginInfo(I('post.identify'))){//验证id和识别码
					switch (I('post.action')){//根据action判断
					//添加随机码
					case 'addrandom':
						if(I('post.id') == 0){
							$reg_cert_M = M('reg_cert');
							//查询随机码是否存在
							do{
								$reg_certData['randomnumber'] = rand(100000,999999);
								$reg_certCheck = $reg_cert_M->where($reg_certData)->find();
								if($reg_certCheck === false){
									$this->base->errorMessage(session('username'),'注册凭证表查询失败','无法读取数据库');
									exit('查询失败，请重试');
								}
							}while($reg_certCheck !== null);
							if($reg_cert_M->data($reg_certData)->add()){//添加随机码
								$this->base->writeLog(session('username'),'reg_cert_random','新增注册凭证，类型：随机码，号码：'.$reg_certData['randomnumber']);
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'注册凭证新增失败','类型：随机码，号码：'.$reg_certData['randomnumber']);
								exit('数据库更新失败，请重试');
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//添加或修改身份
					case 'edit':
						$reg_certConfirm = I('post.data');
						//验证提交信息
						if(preg_match('/^[0-9Xx]{18}$/',$reg_certConfirm['idcardnumber']) && preg_match('/[1-3]{1}/',$reg_certConfirm['loginstatus_name'])
						 && preg_match('/[1-9]{1}[0-9]*/',$reg_certConfirm['authority_name']) && preg_match('/[1-9]{1}[0-9]*/',$reg_certConfirm['ingroup_name'])){
							$group_list_M = M('group_list');
							$authority_M = M('authority');
							$actor_M = M('actor');
							$group_listWhere['id'] = $reg_certConfirm['ingroup_name'];
							$group_listWhere['type'] = ($reg_certConfirm['loginstatus_name'] == 3) ? 1 : 0;
							$authorityWhere['id'] = $reg_certConfirm['authority_name'];
							$actorWhere['id'] = $reg_certConfirm['loginstatus_name'];
							//验证基础、角色、组是否存在
							if($group_list_M->where($group_listWhere)->find() && $authority_M->where($authorityWhere)->find() && $actor_M->where($actorWhere)->find()){
								//如果有提交个人信息则验证
								if($reg_certConfirm['realname']){
									if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$reg_certConfirm['realname']))exit($this->base->changeCallback_error);
								}
								if($reg_certConfirm['bankcard']){
									if(!preg_match('/^[0-9]{16,19}$/',$reg_certConfirm['bankcard']))exit($this->base->changeCallback_error);
								}
								if($reg_certConfirm['bankname']){
									if(!preg_match('/.{4,30}/',$reg_certConfirm['bankname']))exit($this->base->changeCallback_error);
								}
								if($reg_certConfirm['mobile']){
									if(!preg_match('/^[1][0-9]{10}$/',$reg_certConfirm['mobile']))exit($this->base->changeCallback_error);
								}
								$reg_cert_M = M('reg_cert');
								//根据提交id验证身份证号码
								if(I('post.id') == 0){
									$reg_certWhere['idcardnumber'] = $this->base->idcardChange($reg_certConfirm['idcardnumber']);
									if($reg_cert_M->where($reg_certWhere)->find())exit('身份证号码重复');
								}else{
									$reg_certWhere['id'] = I('post.id');
									$reg_certWhere['randomnumber'] = 0;
									$reg_certCheck = $reg_cert_M->where($reg_certWhere)->find();
									if($reg_certCheck === null || $reg_certCheck === false)exit($this->base->changeCallback_error);
									$reg_certWhere = array('id' => array('neq',I('post.id')), 'idcardnumber' => $this->base->idcardChange($reg_certConfirm['idcardnumber']));
									if($reg_cert_M->where($reg_certWhere)->find())exit('身份证号码重复');
								}
								//格式化更新/写入数据
								$reg_certData = array('idcardnumber' => $this->base->idcardChange($reg_certConfirm['idcardnumber']), 'realname' => $reg_certConfirm['realname'] ? $reg_certConfirm['realname'] : '',
								'ingroup' => $reg_certConfirm['ingroup_name'], 'bankcard' => $reg_certConfirm['bankcard'] ? $reg_certConfirm['bankcard'] : 0,
								'bankname' => $reg_certConfirm['bankname'] ? $reg_certConfirm['bankname'] : '', 'loginstatus' => $reg_certConfirm['loginstatus_name'],
								'mobile' => $reg_certConfirm['mobile'] ? $reg_certConfirm['mobile'] : 0, 'authority' => $reg_certConfirm['authority_name']
								);
								//根据提交id更新/写入
								if(I('post.id') != 0){
									$reg_certDataWhere['id'] = I('post.id');
									$reg_certInfo = $reg_cert_M->where($reg_certDataWhere)->data($reg_certData)->save();
								}else{
									$reg_certInfo = $reg_cert_M->data($reg_certData)->add();
								}
								if($reg_certInfo !== false){
									$this->base->writeLog(session('username'),'reg_cert_idcard','新增/更新注册凭证，类型：身份证，号码：'.$reg_certConfirm['idcardnumber']);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'新增/更新注册凭证失败','类型：身份证，号码'.$reg_certConfirm['idcardnumber']);
									exit('更新数据库失败，请重试');
								}
							}else{
								exit($this->base->changeCallback_error);
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					case 'remove':
						$reg_cert_M = M('reg_cert');
						$reg_certWhere['id'] = I('post.id');
						$reg_certInfo = $reg_cert_M->where($reg_certWhere)->find();
						//待删除id不存在则退出
						if($reg_certInfo === false || $reg_certInfo === null)exit($this->base->changeCallback_error);
						if($reg_cert_M->where($reg_certWhere)->delete()){//删除id
							$this->base->writeLog(session('username'),'reg_cert_remove','ID:'.I('post.id'));
							exit('1');
						}else{
							$this->base->errorMessage(session('username'),'删除注册凭证失败','ID:'.I('post.id'));
							exit('删除失败，请重试');
						}
						break;
						
					default:
						exit($this->base->changeCallback_error);
						break;
					}
				}
			}else{
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//注册凭证->Json数据
	public function reg_certJson($action = 'list',$actor = false){
		if($this->base->login && $this->base->authorityCheck('reg_cert')){//验证登录和权限
			switch ($action){
			//凭证列表
			case 'list':
				$reg_cert_D = D('RegCertView');
				if($reg_certInfo = $reg_cert_D->select()){
					foreach($reg_certInfo as $rcKey => $rcValue){
						$reg_certInfo[$rcKey]['idcardnumber'] = $rcValue['idcardnumber'] ? $this->base->idcardChange($rcValue['idcardnumber']) : '';
						$reg_certInfo[$rcKey]['ingroup_name'] = $rcValue['ingroup'] ? $rcValue['group_name'] : '';
						$reg_certInfo[$rcKey]['authority_name'] = $rcValue['authority'] ? $rcValue['authority_name'] : '';
						$reg_certInfo[$rcKey]['loginstatus_name'] = $rcValue['loginstatus'] ? $rcValue['actor_name'] : '';
						foreach($rcValue as $personalKey => $personalValue){
							if(!$personalValue)$reg_certInfo[$rcKey][$personalKey] = '';
						}
					}
					$this->ajaxReturn($reg_certInfo);
				}
				break;
				
			//基础权限列表
			case 'authority':
				$authority_M = M('authority');
				if($authorityInfo = $authority_M->field('id,name')->select()){
					$this->ajaxReturn($authorityInfo);
				}
				break;
				
			//角色权限列表
			case 'actor':
				$actor_M = M('actor');
				if($actorInfo = $actor_M->field('id,name')->select()){
					$this->ajaxReturn($actorInfo);
				}
				break;
				
			//根据get数据选择组列表
			case 'ingroup':
				if(preg_match('/[1-3]{1}/',$actor)){
					$group_list_M = M('group_list');
					$group_listWhere['type'] = ($actor == 3) ? 1 : 0;
					if($group_listInfo = $group_list_M->field('id,name')->where($group_listWhere)->select()){
						$this->ajaxReturn($group_listInfo);
					}
				}
				break;
				
			default:
				exit;
				break;
			}
		}
	}
	
	//成员管理
	public function memberadmin(){
		if($this->base->login && $this->base->authorityCheck('memberadmin')){//验证登录和权限
			if(IS_POST){
				if($this->base->loginInfo(I('post.identify'))){//验证识别码
					if(!preg_match('/[1-9]+[0-9]*/',I('post.id')))exit($this->base->changeCallback_error);//验证提交ID
					$loginInfo = $this->base->loginInfo();
					switch (I('post.target')){
					//正式成员
					case 'formal':
						if(!in_array(I('post.id'),$this->base->memberInfo('id')))exit($this->base->changeCallback_error);//验证是否可操作
						$user_M = M('user');
						switch (I('post.action')){
						//编辑
						case 'edit':
							if(!$postData = I('post.data'))exit($this->base->changeCallback_error);//是否有data数据提交
							//验证可操作组
							if($loginInfo['loginstatus'] == 2){
								if($postData['ingroup_name'] != $loginInfo['ingroup'])exit($this->base->changeCallback_error);
							}else{
								if(!in_array($postData['ingroup_name'],$this->base->groupInfo('id')))exit($this->base->changeCallback_error);
							}
							//验证基础权限是否存在
							$authority_M = M('authority');
							$authorityWhere['id'] = $postData['authority_name'];
							$authorityInfo = $authority_M->field('name')->where($authorityWhere)->find();
							//验证数据合法性
							if($postData['actor_name'] < $loginInfo['loginstatus'] && $authorityInfo && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$postData['realname']) &&
							preg_match('/^[0-9Xx]{18}$/',$postData['idcardnumber']) && preg_match('/^[0-9]{16,19}$/',$postData['bankcard']) &&
							preg_match('/.{4,30}/',$postData['bankname']) && preg_match('/^[1][0-9]{10}$/',$postData['mobile'])){
								//验证身份证是否重复
								$userWhere = array('id' => array('neq',I('post.id')), 'idcardnumber' => $this->base->idcardChange($postData['idcardnumber']));
								if($user_M->where($userWhere)->find()){
									exit('身份证号码重复');
								}
								//初始化入库数据
								$userData = array('realname' => $postData['realname'], 'bankcard' => $postData['bankcard'], 'bankname' => $postData['bankname'],
								'idcardnumber' => $this->base->idcardChange($postData['idcardnumber']), 'mobile' => $postData['mobile'],
								'authority' => $postData['authority_name'], 'loginstatus' => $postData['actor_name'], 'ingroup' => $postData['ingroup_name']);
								$actor_M = M('actor');
								$actorWhere['id'] = $postData['actor_name'];
								$actorInfo = $actor_M->field('name')->where($actorWhere)->find();
								$group_list_M = M('group_list');
								$group_listWhere['id'] = $postData['ingroup_name'];
								$group_listInfo = $group_list_M->field('name')->where($group_listWhere)->find();
								if(!$actorInfo || !$group_listInfo)exit($this->base->changeCallback_error);
								//初始化日志信息
								$logMessage = '用户名：'.$postData['username'].'，真实姓名：'.$postData['realname'].'，银行卡号码：'.$postData['bankcard'].'，开户行信息：'.
								$postData['bankname'].'，身份证号码：'.$postData['idcardnumber'].'，手机号码：'.$postData['mobile'].'，基础权限：'.$authorityInfo['name'].
								'，角色：'.$actorInfo['name'].'所在区域/组：'.$group_listInfo['name'];
								$userWhere = array('id' => I('post.id'));
								$userInfo = $user_M->where($userWhere)->save($userData);//更新用户
								if($userInfo){
									$this->base->writeLog(session('username'),'member_edit',$logMessage);
									exit('1');
								}else if($userInfo === false){
									$this->base->errorMessage(session('username'),'修改正式成员信息',$logMessage);
									exit('信息修改失败，请重试');
								}else{
									exit('未修改任何信息');
								}
							}else{
								exit($this->base->changeCallback_error);
							}
							break;
							
						//删除
						case 'remove':
							$EXTRA_memberadmin = $this->base->extraSet('memberadmin');//额外角色权限
							if($loginInfo['loginstatus'] < $EXTRA_memberadmin)exit($this->base->changeCallback_error);
							$userWhere['id'] = I('post.id');
							$userData = array('ingroup' => -1, 'authority' => -1, 'loginstatus' => -1);
							$userInfo = $user_M->field('id,username,realname')->where($userWhere)->find();//非验证(选择动作前已进行可操作验证)，日志信息
							if($user_M->where($userWhere)->save($userData)){//删除用户
								$this->base->writeLog(session('username'),'member_remove','编号：'.$userInfo['id'].'，用户名：'.$userInfo['username'].'，真实姓名：'.$userInfo['realname']);
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'删除正式成员失败','ID：'.$userInfo['id'].'，用户名：'.$userInfo['username'].'，真实姓名：'.$userInfo['realname']);
								exit('删除失败，请重试');
							}
							break;
							
						default:
							exit($this->base->changeCallback_error);
							break;
						}
						break;
						
					//未授权成员
					case 'unsign':
						$EXTRA_memberadmin = $this->base->extraSet('memberadmin');//额外角色权限
						if($loginInfo['loginstatus'] < $EXTRA_memberadmin)exit($this->base->changeCallback_error);
						$user_M = M('user');
						$userWhere = array('authority' => 0, 'loginstatus' => 0, 'ingroup' => 0, 'id' => I('post.id'));
						if($userInfo = $user_M->where($userWhere)->find()){
							switch (I('post.action')){
							case 'edit':
								if(!$postData = I('post.data'))exit($this->base->changeCallback_error);//是否有data数据提交
								//验证可操作组
								if($loginInfo['loginstatus'] == 2){
									if($postData['ingroup'] != $loginInfo['ingroup'])exit($this->base->changeCallback_error);
								}else{
									if(!in_array($postData['ingroup'],$this->base->groupInfo('id')))exit($this->base->changeCallback_error);
								}
								//验证基础权限是否存在
								$authority_M = M('authority');
								$authorityWhere['id'] = $postData['authority'];
								$authorityInfo = $authority_M->field('name')->where($authorityWhere)->find();
								//验证角色
								if($postData['actor'] < $loginInfo['loginstatus'] && $authorityInfo){
									$actor_M = M('actor');
									$actorWhere['id'] = $postData['actor'];
									$actorInfo = $actor_M->field('name')->where($actorWhere)->find();
									$group_list_M = M('group_list');
									$group_listWhere['id'] = $postData['ingroup'];
									$group_listInfo = $group_list_M->field('name')->where($group_listWhere)->find();
									//验证角色和组存在
									if(!$actorInfo || !$group_listInfo)exit($this->base->changeCallback_error);
									//初始化更新数据
									$userData = array('authority' => $postData['authority'], 'loginstatus' => $postData['actor'], 'ingroup' => $postData['ingroup']);
									//初始化日志数据
									$logMessage = 'ID:'.I('post.id').'，用户名：'.$userInfo['username'].'，真实姓名：'.$userInfo['realname'].'，基础权限：'.
									$authorityInfo['name'].'，角色：'.$actorInfo['name'].'，所在区域/组：'.$group_listInfo['name'];
									if($user_M->where($userWhere)->save($userData)){//更新
										$this->base->writeLog(session('username'),'memberadmin_unsign_edit',$logMessage);
										exit('1');
									}else{
										$this->base->errorMessage(session('username'),'编辑未授权成员失败',$logMessage);
										exit('编辑未授权成员失败，请重试！');
									}
								}
								break;
								
							case 'remove':
								//初始化日志数据
								$logMessage = 'ID:'.I('post.id').'，用户名：'.$userInfo['username'].'，真实姓名：'.$userInfo['realname'];
								if($user_M->where($userWhere)->delete()){//删除
									$this->base->writeLog(session('username'),'member_unsign_remove',$logMessage);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'删除未授权成员失败',$logMessage);
									exit('删除未授权成员失败，请重试');
								}
								break;
								
							default:
								exit($this->base->changeCallback_error);
								break;
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
				$loginInfo = $this->base->loginInfo();
				$this->assign('loginstatus',$loginInfo['loginstatus']);//赋值角色信息
				
				$EXTRA_memberadmin = $this->base->extraSet('memberadmin');
				$this->assign('EXTRA_memberadmin',$EXTRA_memberadmin);//赋值额外角色权限信息
				
				if($loginInfo['loginstatus'] >= $EXTRA_memberadmin){
					$user_M = M('user');
					$userWhere['loginstatus'] = 0;
					$userInfo = $user_M->where($userWhere)->find();
					$unsign = $userInfo ? 'selected = "true"' : '';
					$this->assign('unsign',$unsign);//赋值是否先打开未授权列表
				}
				
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//成员管理->Json数据
	public function memberadminJson($action = false, $group = false){
		if($this->base->login && $this->base->authorityCheck('memberadmin') && $action){//验证登录、权限和请求类型
			$loginInfo = $this->base->loginInfo();
			switch($action){
			//左侧组列表
			case 'menu':
				$this->ajaxReturn($this->base->groupInfo('json'));
				break;
				
			//正式成员信息
			case 'formal':
				$user_D = D('UserView');
				if($loginInfo['loginstatus'] > 2 && preg_match('/[1-9]+[0-9]*/',$group)){//验证角色和提交数据、查询用户列表
					$userloginstatus = $loginInfo['loginstatus'] == 4 ? '1,2,3' : '1,2';
					$userWhere = array('ingroup' => $group, 'authority' => array('gt',0), 'loginstatus' => array('in',$userloginstatus));
					$userInfo = $user_D->where($userWhere)->select();
				}else if($loginInfo['loginstatus'] == 2){
					$userWhere = array('ingroup' => $loginInfo['ingroup'], 'authority' => array('gt',0), 'loginstatus' => 1);
					$userInfo = $user_D->where($userWhere)->select();
				}
				if($userInfo){
					foreach($userInfo as $userKey => $userValue){//格式化身份证
						$userInfo[$userKey]['idcardnumber'] = $this->base->idcardchange($userValue['idcardnumber']);
					}
				}
				$this->ajaxReturn($userInfo);
				break;
				
			//下拉选项->基础权限
			case 'authority':
				$authority_M = M('authority');
				$authorityInfo = $authority_M->field('id,name')->select();
				$this->ajaxReturn($authorityInfo);
				break;

			//下拉选项->角色
			case 'actor':
				$actor_M = M('actor');
				$actorWhere['id'] = array('lt',$loginInfo['loginstatus']);
				$actorInfo = $actor_M->field('id,name')->where($actorWhere)->select();
				$this->ajaxReturn($actorInfo);
				break;
				
			//下拉选项->区域/组
			case 'ingroup':
				$loginstatus = $group;
				if(preg_match('/[1-3]{1}/',$loginstatus) && $loginInfo['loginstatus'] > $loginstatus){//验证角色
					$group_list_M = M('group_list');
					//根据角色生成查询条件
					if($loginstatus == 3 && $loginInfo['loginstatus'] == 4){
						$group_listWhere = array('type' => 1, 'parent' => 0);
					}else{
						if($loginInfo['loginstatus'] == 4){
							$group_listWhere = array('type' => 0);
						}else if($loginInfo['loginstatus'] == 3){
							$group_listWhere = array('type' => 0, 'parent' => $loginInfo['ingroup']);
						}else if($loginInfo['loginstatus'] == 2){
							$group_listWhere = array('id' => $loginInfo['ingroup']);
						}else{
							exit($this->base->changeCallback_error);
						}
					}
					$group_listInfo = $group_list_M->field('id,name')->where($group_listWhere)->select();
					$this->ajaxReturn($group_listInfo);
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			//未授权列表
			case 'unsign':
				$EXTRA_memberadmin = $this->base->extraSet('memberadmin');
				if($loginInfo['loginstatus'] >= $EXTRA_memberadmin){//验证额外设置
					$user_M = M('user');
					$userWhere = array('ingroup' => 0, 'loginstatus' => 0, 'authority' => 0);
					$userInfo = $user_M->field('id,username,realname,idcardnumber,mobile,bankcard,bankname')->where($userWhere)->select();
					if($userInfo){
						foreach($userInfo as $userKey => $userValue){
							$userInfo[$userKey]['idcardnumber'] = $this->base->idcardChange($userValue['idcardnumber']);
							$userInfo[$userKey]['authority'] = '';
							$userInfo[$userKey]['actor'] = '';
							$userInfo[$userKey]['ingroup'] = '';
						}
					}
					$this->ajaxReturn($userInfo);
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
				
			default:
				break;
			}
			exit($this->base->changeCallback_error);
		}else{
			exit($this->base->changeCallback_error);
		}
	}
	
	//现金券
	public function ticketadmin($action = false){
		if($this->base->login && $this->base->authorityCheck('ticketadmin')){//验证登录、权限
			$EXTRA_ticketadmin = $this->base->extraSet('ticketadmin');//特殊权限
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				if($loginInfo['loginstatus'] < $EXTRA_ticketadmin)exit($this->base->changeCallback_error);
				if($this->base->loginInfo(I('post.identify')) && I('post.action')){//验证识别码、动作
					if(I('post.action') == 'edit' || I('post.action') == 'remove'){//编辑和删除操作
						$ticket_D = D('TicketView');
						//验证现金券编号
						$ticketWhere['id'] = preg_match('/^[1-9]+[0-9]*$/',I('post.ticketid')) ? I('post.ticketid') : exit($this->base->changeCallback_error);
						$ticketInfo = $ticket_D->where($ticketWhere)->find();
						if($ticketInfo){//验证现金券存在
							$ticket_M = M('ticket');
							switch(I('post.action')){
							//删除
							case 'remove':
								//日志文字
								$logMessage = '现金券编号：'.I('post.ticketid').'，发券人：'.$ticketInfo['sendername'].'，价值：'.$ticketInfo['worth'].'，余额：'.$ticketInfo['remain'];
								if($ticket_M->where($ticketWhere)->delete()){//删除现金券
									$this->base->writeLog(session('username'),'ticketadmin_remove',$logMessage);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'删除现金券',$logMessage);
									exit('删除失败，请重试');
								}
								break;
								
							//编辑
							case 'edit':
								//数据是否存在
								$postData = I('post.data') ? I('post.data') : exit($this->base->changeCallback_error);
								//数据未修改
								if($ticketInfo['sender'] == $postData['sendername'] && $ticketInfo['worth'] == $postData['worth'] && $ticketInfo['remain'] == $postData['remain']){
									exit('现金券信息未修改');
								}else{
									//验证提交数据
									if(preg_match('/^[1-9]+[0-9]*$/',$postData['sendername']) && preg_match('/^[1-9]+[0-9]*$/',$postData['worth']) && 
									preg_match('/^[1-9]+[0-9]*$/',$postData['remain'])){
										//验证价值和余额大小
										if($postData['worth'] < $postData['remain'])exit('现金券余额不能大于价值');
										$sender_M = M('sender');
										$senderWhere['id'] = $postData['sendername'];
										$senderInfo = $sender_M->where($senderWhere)->find();
										//发券人ID是否存在
										if($senderInfo){
											//初始化修改数据
											$ticketData = array('id' => I('post.ticketid'), 'sender' => $postData['sendername'], 'worth' => $postData['worth'], 'remain' => $postData['remain']);
											//日志文字
											$logMessage = '现金券编号：'.I('post.ticketid').'。原信息 => 发券人：'.$ticketInfo['sendername'].'，价值：'.$ticketInfo['worth'].
											'，余额：'.$ticketInfo['remain'].'。修改为 => 发券人：'.$senderInfo['name'].'，价值：'.$postData['worth'].'，余额：'.$postData['remain'];
											if($ticket_M->data($ticketData)->save()){//编辑
												$this->base->writeLog(session('username'),'ticketadmin_edit',$logMessage);
												exit('1');
											}else{
												$this->base->errorMessage(session('username'),'编辑现金券信息',$logMessage);
												exit('现金券信息编辑失败，请重试');
											}
										}else{
											exit($this->base->changeCallback_error);
										}
									}else{
										exit($this->base->changeCallback_error);
									}
								}
								break;
								
							default :
								exit($this->base->changeCallback_error);
								break;
							}
						}else{
							exit($this->base->changeCallback_error);
						}
					}
					//有发券人和价值就验证
					if(I('post.sender')){
						if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',I('post.sender')))exit('发券人提交错误');
					}
					if(I('post.worth')){
						if(!preg_match('/^[1-9]+[0-9]*$/',I('post.worth')))exit('价值提交错误');
					}
					if(!I('post.data'))exit('无列表数据');
					$dataArray = explode(',',I('post.data'));//字符串转数组
					$dataLength = count($dataArray);
					if(I('post.sender'))$dataLength++;
					if(I('post.worth'))$dataLength++;
					if($dataLength > 4)exit('列表数据长度异常，请修改后提交');//验证提交数量
					//发券人赋值
					$sender = I('post.sender') ? I('post.sender') : $dataArray[0];
					//价值赋值
					if(I('post.worth')){
						$worth = I('post.worth');
					}else{
						if(I('post.sender')){
							$worth = $dataArray[0];
						}else{
							$worth = $dataArray[1];
						}
					}
					//起始ID和终止ID赋值
					if(I('post.sender') && I('post.worth')){
						$startid = $dataArray[0];
						$endid = isset($dataArray[1]) ? $dataArray[1] : $startid;
					}else if(!I('post.sender') && !I('post.worth')){
						$startid = $dataArray[2];
						$endid = isset($dataArray[3]) ? $dataArray[3] : $startid;
					}else{
						$startid = $dataArray[1];
						$endid = isset($dataArray[2]) ? $dataArray[2] : $startid;
					}
					//验证发券人、价值、起始终止ID
					if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$sender) && preg_match('/^[1-9]+[0-9]*$/',$worth) && preg_match('/^[1-9]+[0-9]*$/',$startid) &&
					preg_match('/^[1-9]+[0-9]*$/',$endid) && $startid <= $endid){
						//初始化发券人列表
						$senderList = F('senderList');
						if(!$senderList){
							$sender_M = M('sender');
							$senderInfo = $sender_M->select();
							if($senderInfo){
								foreach($senderInfo as $siValue){
									$senderList[$siValue['id']] = $siValue['name'];
								}
							}
							$senderList = $senderInfo;
						}
						//查询发券人是否在列表内
						$senderExist = in_array($sender,$senderList);
						switch (I('post.action')){
						//增加现金券
						case 'addticket':
							if(!$senderExist)exit('2');//发券人不存在则退出
							//初始化发券人id
							$senderId_array = array_keys($senderList,$sender);
							if(count($senderId_array) == 1){
								$senderId = $senderId_array[0];
							}else{
								$this->base->errorMessage(session('username'),'发券人的快存数据获取失败','发券人：'.$sender);
								if(!isset($sender_M))$sender_M = M('sender');
								$senderWhere['name'] = $sender;
								$senderInfo = $sender_M->field('id')->where($senderWhere)->find();
								if($senderInfo){
									$senderId = $senderInfo['id'];
								}else{
									exit('发券人名称获取失败，请重试');
								}
							}
							$ticket_M = M('ticket');
							//验证现金券编号是否存在
							$ticketWhere['id'] = array('between',$startid.','.$endid);
							$ticketIdConfirm = $ticket_M->where($ticketWhere)->find();
							if($ticketIdConfirm)exit('现金券编号：('.$startid.' - '.$endid.')已经存在，请修改后提交');
							//初始化新增数据，新增
							if($startid == $endid){
								$ticketData = array('id' => $startid, 'sender' => $senderId, 'worth' => $worth, 'remain' => $worth);
								$ticketInfo = $ticket_M->data($ticketData)->add();
							}else{
								for($i = $startid;$i <= $endid;++$i){
									$ticketData[] = array('id' => $i, 'sender' => $senderId, 'worth' => $worth, 'remain' => $worth);
								}
								$ticketInfo = $ticket_M->addAll($ticketData);
							}
							//写入日志
							$logMessage = '发券人：'.$sender.'，价值：'.$worth.'，起始ID：'.$startid.'，终止ID：'.$endid;
							if($ticketInfo){
								$this->base->writeLog(session('username'),'ticket_addticket',$logMessage);
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'新增现金券失败',$logMessage);
								exit('新增现金券失败，请重试');
							}
							break;
							
						//增加发券人
						case 'addsender':
							if($senderExist){
								//若发券人存在，写入错误日志（正常情况不可能发生）
								$this->base->errorMessage(session('username'),'新增发券人','发券人已存在');
								exit('异常！发券人已存在，请联系管理员');
							}else{
								if(!isset($sender_M))$sender_M = M('sender');
								$senderData['name'] = $sender;
								//新增发券人
								if($sender_M->data($senderData)->add()){
									$senderInfo = $sender_M->select();
									foreach($senderInfo as $siValue){
										$senderList[$siValue['id']] = $siValue['name'];
									}
									//刷新快速储存
									F('senderList',$senderList);
									$this->base->writeLog(session('username'),'ticket_addsender','发券人名称：'.$sender);
									exit('1');
								}else{
									$this->base->errorMessage(session('username'),'新增发券人','添加失败；发券人名称：'.$sender);
									exit('发券人添加失败，请重试');
								}
							}
							break;
							
						default:
							exit($this->base->changeCallback_error);
							break;
						}
					}else{
						exit('数据填写错误，请修改后提交');
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}else{
				if($action){
					switch ($action){
					case 'add':
						//新增现金券页面，验证
						if($loginInfo['loginstatus'] < $EXTRA_ticketadmin)exit($this->base->changeCallback_error);
						$this->display('addticket');
						break;
						
					default:
						$this->assign('loginstatus',$loginInfo['loginstatus']);
						$this->assign('EXTRA_ticketadmin',$EXTRA_ticketadmin);
						$this->display();
						break;
					}
				}else{
					exit($this->base->changeCallback_error);
				}
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//现金券->Json数据
	public function ticketadminJson($action = false,$id = false){
		if($this->base->login && $this->base->authorityCheck('ticketadmin') && $action){//验证登录、权限、动作
			switch ($action){
			//菜单
			case 'menu':
				$sender_M = M('sender');
				$senderInfo = $sender_M->field('id,name as text')->select();
				$menuInfo = array(array('id' => 0, 'text' => '全部', 'children' => $senderInfo));
				$this->ajaxReturn($menuInfo);
				break;
				
			//列表
			case 'main':
				//验证发券人ID、当前页数、每页数量
				if(preg_match('/^[0-9]+$/',$id) && preg_match('/^[1-9]*[0-9]+$/',I('get.page') && preg_match('/^[1-9]*[0-9]+$/',I('get.rows')))){
					$ticket_D = D('TicketView');
					if(I('get.ticketid') && preg_match('/[1-9]+[0-9]*/',I('get.ticketid'))){//是否查询状态
						$ticketWhere['id'] = I('get.ticketid');
						$ticketInfo = $ticket_D->where($ticketWhere)->select();
						$total = $ticketInfo ? 1 : 0;
					}else{
						$page = I('get.page');
						$rows = I('get.rows');
						if($id == 0){
							$total = $ticket_D->count();
						}else{
							$senderInfo = F('senderList');
							if(isset($senderInfo[$id])){
								$ticketWhere = array('sender' => $id);
								$total = $ticket_D->where($ticketWhere)->count();
							}else{
								exit($this->base->changeCallback_error);
							}
						}
						if($total){
							$limit = ($page - 1) * $rows;
							if($id == 0){
								$ticketInfo = $ticket_D->limit($limit.','.$rows)->order('`id` desc')->select();
							}else{
								$ticketInfo = $ticket_D->where($ticketWhere)->limit($limit.','.$rows)->order('`id` desc')->select();
							}
						}else{
							exit('无数据');
						}
					}
					foreach($ticketInfo as $tiKey => $tiValue){
						if($tiValue['firsttime'])$ticketInfo[$tiKey]['firsttime'] = date("Y-m-d H:i:s",$tiValue['firsttime']);
						if($tiValue['lasttime'])$ticketInfo[$tiKey]['lasttime'] = date("Y-m-d H:i:s",$tiValue['lasttime']);
					}
					$ticketInfoAjax = array('total' => $total, 'rows' => $ticketInfo);
					$this->ajaxReturn($ticketInfoAjax);
						
				}else{
					exit($this->base->changeCallback_error);
				}
				break;
			
			//发券人列表
			case 'sender':
				$loginInfo = $this->base->loginInfo();
				if($loginInfo['loginstatus'] < $this->base->extraSet('ticketadmin'))exit($this->base->changeCallback_error);
				$sender_M = M('sender');
				$senderInfo = $sender_M->select();
				$this->ajaxReturn($senderInfo);
				break;
				
			default:
				exit($this->base->changeCallback_error);
				break;
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//旅游线路
	public function route(){
		if($this->base->login && $this->base->authorityCheck('route')){//验证登录、权限
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				//验证提交数据
				if(preg_match('/^[0-9]+$/',I('post.id')) && I('post.data') && I('post.action') && $this->base->loginInfo(I('post.identify')) &&
				$loginInfo['loginstatus'] >= $this->base->extraSet('route')){
					$route_M = M('route');
					switch(I('post.action')){
					//增加和编辑
					case 'edit':
						//编辑 则确认线路存在
						if(I('post.id') != 0){
							$routeWhere = array('id' => I('post.id'));
							$routeConfirm = $route_M->where($routeWhere)->find();
							if(!$routeConfirm)exit($this->base->changeCallback_error);
						}
						$postData = I('post.data');
						//验证data数据
						if(strlen($postData['name']) > 0 && preg_match('/^[1-9]+[0-9]*$/',$postData['price']) && preg_match('/^[1-9]+[0-9]*$/',$postData['agemin']) &&
						preg_match('/^[1-9]+[0-9]*$/',$postData['agemax']) && $postData['agemax'] >= $postData['agemin'] &&
						preg_match('/^[0-9]{6}[0-9,]*$/',$postData['provincename']) && preg_match('/^[0-1]{1}$/',$postData['workname'])){
							$province_M = M('province');
							//确认省份
							if(strlen($postData['provincename']) == 6 && preg_match('/^[1-9]+[0-9]*$/',$postData['provincename'])){
								$provinceWhere = array('id' => $postData['provincename']);
								$provinceInfo = $province_M->where($provinceWhere)->select();
								if(!$provinceInfo)exit($this->base->changeCallback_error);
							}else{
								$provinceList = explode(',',$postData['provincename']);
								$provinceWhere = array('id' => array('in',$postData['provincename']));
								$provinceInfo = $province_M->where($provinceWhere)->select();
								if(count($provinceList) != count($provinceInfo))exit($this->base->changeCallback_error);
							}
							$provincenameList = array();
							foreach($provinceInfo as $piValue){
								array_push($provincenameList,$piValue['name']);
							}
							//省份字符串
							$provincenameString = implode(',',$provincenameList);
							//格式化入库数据
							$routeData = array('name' => $postData['name'], 'price' => $postData['price'], 'agemin' => $postData['agemin'], 'agemax' => $postData['agemax'],
							'province' => $postData['provincename'], 'provincename' => $provincenameString, 'work' => $postData['workname']);
							//格式化日志文字
							$logMessage = '线路名称：'.$postData['name'].'，价格：'.$postData['price'].'，最小年龄：'.$postData['agemin'].'，最大年龄：'.$postData['agemax'].
							'，省份：'.$provincenameString.'，启用：'.($postData['workname'] ? '是' : '否');
							if(I('post.id') == 0){
								$routeResult = $route_M->data($routeData)->add();
								$actionType = 'route_add';
							}else{
								$routeData['id'] = I('post.id');
								$routeResult = $route_M->data($routeData)->save();
								$actionType = 'route_edit';
								//编辑 则再补全日志文字
								$logMessage = '原数据 => '.'线路名称：'.$routeConfirm['name'].'，价格：'.$routeConfirm['price'].'，最小年龄：'.$routeConfirm['agemin'].
								'，最大年龄：'.$routeConfirm['agemax'].'，省份：'.$routeConfirm['provincename'].'，启用：'.($routeConfirm['work'] ? '是' : '否')
								.'<br>修改为 => '.$logMessage;
							}
							if($routeResult === false){
								$this->base->errorMessage(session('username'),'编辑旅游线路失败',$logMessage);
								exit('编辑失败，请重试');
							}else{
								if($routeResult){
									$this->base->writeLog(session('username'),$actionType,$logMessage);
									exit('1');
								}else{
									exit('未修改信息');
								}
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//删除
					case 'remove':
						//验证线路
						$routeWhere = array('id' => I('post.id'), 'name' => I('post.data'), 'work' => array('egt',0));
						$routeConfirm = $route_M->field('name')->where($routeWhere)->find();
						if($routeConfirm){
							$routeData = array('id' => I('post.id'), 'work' => '-1');
							if($route_M->data($routeData)->save()){//删除线路(修改线路work值)
								$this->base->writeLog(session('username'),'route_remove','线路名称：'.$routeConfirm['name']);
								exit('1');
							}else{
								$this->base->errorMessage(session('username'),'删除旅游线路失败','旅游线路：'.$routeConfirm['name']);
								exit('删除失败，请重试');
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
				$this->assign('loginstatus',$loginInfo['loginstatus']);
				$this->assign('EXTRA_route',$this->base->extraSet('route'));
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//旅游线路->Json数据
	public function routeJson($action = false){
		if($this->base->login && $this->base->authorityCheck('route')){
			switch ($action){
			//旅游线路列表
			case 'travel':
				$route_M = M('route');
				$routeWhere = array('work' => array('egt',0));
				$routeInfo = $route_M->where($routeWhere)->select();
				if($routeInfo){
					foreach($routeInfo as $riKey => $riValue){
						$routeInfo[$riKey]['workname'] = $riValue['work'] ? '是' : '否';
					}
				}
				$this->ajaxReturn($routeInfo);
				break;
				
			//省份
			case 'province':
				$province_M = M('province');
				$provinceInfo = $province_M->select();
				$this->ajaxReturn($provinceInfo);
				break;
				
			default:
				exit($this->base->changeCallback_error);
				break;
			}
		}else{
			exit($this->base->changeCallback_error);
		}
	}
	
	//发券人提成
	public function sendercut(){
		if($this->base->login && $this->base->authorityCheck('sendercut')){//验证登录、权限
			$loginInfo = $this->base->loginInfo();
			if(IS_POST){
				//验证提交数据
				if(preg_match('/^[0-9]+$/',I('post.id')) && preg_match('/^[1-9]+[0-9]*$/',I('post.sender')) && I('post.action') && I('post.data') &&
				$this->base->loginInfo(I('post.identify')) && $loginInfo['loginstatus'] >= $this->base->extraSet('senderout')){
					//验证发券人
					$sender_M = M('sender');
					$senderWhere = array('id' => I('post.sender'));
					if(!$senderConfirm = $sender_M->field('name')->where($senderWhere)->find())exit($this->base->changeCallback_error);
					$sendercut_M = M('sendercut');
					switch(I('post.action')){
					//新增和编辑
					case 'edit':
						$postData = I('post.data');
						//验证data数据
						if(preg_match('/^[1-9]+[0-9]*$/',$postData['routename']) && preg_match('/^[1-9]+[0-9]*$/',$postData['cut'])){
							//验证线路
							$route_M = M('route');
							$routeWhere = array('id' => $postData['routename']);
							if(!$routeConfirm = $route_M->field('name')->where($routeWhere)->find())exit($this->base->changeCallback_error);
							//验证提成规则是否重复
							$sendercutWhere = array('route' => $postData['routename'], 'sender' => I('post.sender'));
							if(I('post.id') != 0)$sendercutWhere['id'] = array('id' => array('neq',I('post.id')));
							if($sendercut_M->where($sendercutWhere)->find())exit('该线路已有提成规则，不能重复添加');
							//编辑 则获取原数据
							if(I('post.id') != 0){
								$sendercut_D = D('SendercutView');
								$sendercutWhere = array('id' => I('post.id'), 'sender' => I('post.sender'));
								if(!$sendercutOld = $sendercut_D->where($sendercutWhere)->find())exit($this->base->changeCallback_error);
							}
							//格式化入库数据
							$sendercutData = array('route' => $postData['routename'], 'sender' => I('post.sender'), 'cut' => $postData['cut']);
							//格式化日志文字
							$logMessage = '发券人：'.$senderConfirm['name'].'，旅游线路：'.$routeConfirm['name'].'，提成金额：'.$postData['cut'];
							if(I('post.id') == 0){
								$actionType = 'sendercut_add';
								$sendercutResult = $sendercut_M->data($sendercutData)->add();
							}else{
								$actionType = 'sendercut_edit';
								$sendercutData['id'] = I('post.id');
								//补全日志文字
								$logMessage = '原数据 => 发券人：'.$senderConfirm['name'].'，旅游线路：'.$sendercutOld['routename'].'，提成金额：'.$sendercutOld['cut'].
								'<br>修改为 => '.$logMessage;
								$sendercutResult = $sendercut_M->data($sendercutData)->save();
							}
							if($sendercutResult === false){
								$this->base->errorMessage(session('username'),'编辑发券人提成失败',$logMessage);
								exit('编辑失败，请重试');
							}else{
								if($sendercutResult){
									$this->base->writeLog(session('username'),$actionType,$logMessage);
									exit('1');
								}else{
									exit('未修改信息');
								}
							}
						}else{
							exit($this->base->changeCallback_error);
						}
						break;
						
					//删除
					case 'remove':
						//验证规则是否存在
						$sendercutWhere = array('id' => I('post.id'), 'route' => I('post.data'), 'sender' => I('post.sender'));
						if(!$sendercutConfirm = $sendercut_M->where($sendercutWhere)->find())exit($this->base->changeCallback_error);
						//获取线路信息
						$route_M = M('route');
						$routeWhere['id'] = I('post.data');
						if(!$routeInfo = $route_M->field('name')->where($routeWhere)->find())exit($this->base->changeCallback_error);
						//格式化日志文字
						$logMessage = '发券人：'.$senderConfirm['name'].'，旅游线路：'.$routeInfo['name'];
						if($sendercut_M->where($sendercutWhere)->delete()){//删除提成规则
							$this->base->writeLog(session('username'),'sendercut_remove',$logMessage);
							exit('1');
						}else{
							$this->base->errorMessage(session('username'),'删除发券人提成规则失败',$logMessage);
							exit('删除失败，请重试');
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
				$this->assign('loginstatus',$loginInfo['loginstatus']);
				$this->assign('EXTRA_sendercut',$this->base->extraSet('sendercut'));
				$this->display();
			}
		}else{
			$this->base->noLogin();
		}
	}
	
	//发券人提成->Json数据
	public function sendercutJson($action = false,$id = false){
		if($this->base->login && $this->base->authorityCheck('sendercut') && $action){
			switch ($action){
			case 'menu':
				$sender_M = M('sender');
				$senderInfo = $sender_M->field('id,name as text')->select();
				$this->ajaxReturn($senderInfo);
				break;
				
			case 'route':
				$route_M = M('route');
				$routeWhere = array('work' => 1);
				$routeInfo = $route_M->field('id,name')->where($routeWhere)->select();
				$this->ajaxReturn($routeInfo);
				break;
				
			case 'main':
				if(preg_match('/[1-9]+[0-9]*/',$id)){
					$sender_M = M('sender');
					$senderWhere = array('id' => $id);
					$senderInfo = $sender_M->where($senderWhere)->find();
					if($senderInfo){
						$sendercut_D = D('SendercutView');
						$sendercutWhere = array('sender' => $id);
						$sendercutInfo = $sendercut_D->where($sendercutWhere)->select();
						$this->ajaxReturn($sendercutInfo);
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
}