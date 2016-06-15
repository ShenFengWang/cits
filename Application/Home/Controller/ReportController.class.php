<?php
namespace Home\Controller;
use Think\Controller;
class ReportController extends Controller {

	//加载全局控制器
	private $base;
	
	function __construct(){
		parent::__construct();
		$this->base = new BaseController();
	}


}