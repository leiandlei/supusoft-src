<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class IndexController extends CommonController {
    public function index(){
        $this -> display();
    }
}