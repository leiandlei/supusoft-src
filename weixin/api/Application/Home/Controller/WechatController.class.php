<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class WechatController extends ApiController {
	public $weObj;
	
	Public function _initialize(){
		global $arrOptions;
		$this->weObj = new wx\TPWechat($arrOptions);
   	}

   	public function sendTemplateMessage(){
   		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('data');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/
        $data = json_decode( htmlspecialchars_decode(I('post.data')),ture );
        $data = $this->weObj->sendTemplateMessage($data);
        $results = self::getArrayForResults($data['errcode'],$data['errmsg']);
        exit(json_encode($results));
   	}
	
}