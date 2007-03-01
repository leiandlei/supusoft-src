<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class IndexController extends CommonController {
    public function index(){
        $this -> display();
    }
	public function getindex_list()
	{
		$params  = array();
		$params['type'] = 1;
		$params['page'] = I('get.page')?I('get.page'):'1';
		$params['size'] = 10;
		$results = $this->httpToApi('Notice/getNotice',$params);
		exit(json_encode($results));
	}

	public function index_detail()
	{
        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        $this -> assign($results);
        $this -> display();
	}
}