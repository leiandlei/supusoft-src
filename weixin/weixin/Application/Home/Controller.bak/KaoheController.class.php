<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
use Org\Lin    as lin;
class KaoheController extends CommonController 
{
	public function index()
	{
		$this->redirect('Kaohe/examine_user');
	}
	
	public function examine_user()
	{
		$this->display();	
	}
	
	public function examine_user_main()
	{
		$this->display();	
	}
	
	public function examine_user_info()
	{
		$this->display();	
	}
	
	public function examine_user_info_main()
	{
		$this->display();	
	}
	
	public function getexamine_user()
	{
		$params['page']   = I('post.page');
		$results = $this->httpToApi('Kaohe/examine_user',$params);
		exit(json_encode($results));
	}
	public function getexamine_user_info()
	{
		$params['exu_id'] = I('get.exuid');
		$params['page']   = I('get.page');
		$results = $this->httpToApi('Kaohe/examine_user_info',$params);
		exit(json_encode($results));
	}
}