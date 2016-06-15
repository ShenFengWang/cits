<?php
namespace Home\Controller;
use Think\Controller;
class MainController extends Controller {

	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}

	//初始化界面
	public function index(){
		if(session('?username') && session('?password')){
			$user_M = M('user');//验证登录
			$userWhere = array('username' => session('username'), 'password' => session('password'), 'loginstatus' => array('gt',0), 'authority' => array('gt',0));
			$userInfo = $user_M->field('id,realname,ingroup,loginstatus,authority')->where($userWhere)->find();//读取用户数据
			if($userInfo){
				if($userInfo['loginstatus'] == 4){//权限判断，超管
					$Model = new \Think\Model();
					$authoritySql = $Model->query('show columns from `cits_authority`');
					foreach($authoritySql as $authorityKey => $authorityValue){
						$authorityInfo[$authorityValue['field']] = 1;
					}
				}else{//权限判断，普通
					$authority_M = M('authority');
					$authorityWhere['id'] = $userInfo['authority'];
					$authorityInfo = $authority_M->where($authorityWhere)->find();
					if(!$authorityInfo)exit('无权限信息');
					$actor_M = M('actor');
					$actorWhere['id'] = $userInfo['loginstatus'];
					$actorInfo = $actor_M->where($actorWhere)->find();
					if(!$actorInfo)exit('无角色信息');
					foreach($authorityInfo as $aiKey => $aiValue){
						$authorityInfo[$aiKey] = ($aiValue || $actorInfo[$aiKey]) ? 1 : 0;
					}
				}
				unset($authorityInfo['id']);
				unset($authorityInfo['name']);
				$authorityInfo['id'] = $userInfo['id'];
				$authorityInfo['identify'] = md5(time());//识别码
				$authorityInfo['ingroup'] = $userInfo['ingroup'];
				$authorityInfo['loginstatus'] = $userInfo['loginstatus'];
				F('authority_'.session('username'),$authorityInfo);//储存权限和本次登录识别码信息
				if(F('authority_'.session('username'))){
					//操作目标初始化
					$targetFormat = array('operation' => $authorityInfo['operation'], 'management' => $authorityInfo['management'],
					'report' => $authorityInfo['report'], 'log' => $authorityInfo['log'], 'set' => $authorityInfo['set']);
					//操作目标中文名称
					$targetnameFormat = $this->base->TargetNameFormat;//array('operation' => '操作', 'management' => '管理','report' => '报表', 'log' => '日志', 'set' => '设置');
					$target_url_M = M('target_url');
					//操作目标循环处理
					foreach($targetFormat as $targetKey => $targetValue){
						if($targetValue){
							$target_urlWhere['parent'] = $targetKey;
							//读取可操作大类下具体操作
							$target_urlInfo = $target_url_M->field('name,url,authority_name')->where($target_urlWhere)->select();
							if($target_urlInfo){
								foreach($target_urlInfo as $target_url_Key => $target_url_Value){
									$target_urlInfo[$target_url_Key]['switch'] = $authorityInfo[$target_url_Value['authority_name']];//是否可操作
									unset($target_urlInfo[$target_url_Key]['authority_name']);
								}
								$MainMenu[$targetnameFormat[$targetKey]] = $target_urlInfo;//页面输出赋值
							}else{
								//exit('动作读取失败');
							}
						}
					}
					$this->assign('identify',$authorityInfo['identify']);
					$this->assign('mainmenu',$MainMenu);
					$this->display();
				}else{
					exit('权限初始化失败');
				}
			}
		}
	}
	
	//退出登录
	public function logout(){
		F('authority_'.session('username'),null);
		session(null);
		session('[destroy]');
		header('location: /');
		exit;
	}
}
