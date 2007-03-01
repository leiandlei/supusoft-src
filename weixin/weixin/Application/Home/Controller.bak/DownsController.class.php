<?php
namespace Home\Controller;
use Think\Controller;
use Org\Lin as l;
class DownsController extends CommonController {
	Public function _initialize(){
       	// 初始化的时候检查用户权限
      	if( !$this->checkLogin() ){
          	$this->redirect('Login/index');exit;
      	}
      	//$this -> Menu = sprintf('<ul class="nav nav-list">%s</ul>',$this->getMenu($this -> getMenuInfo()));
    }

	/**
	 * 下载公告文件
	 * @return [type] [description]
	 */
	public function downsNotice(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('id');
        if ( $unsetKey !== null){
            echo '数据不完整';exit;
        }
        /**---是否有数据没有提交---**/

        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        if( empty($results['results']) ){
        	echo '没有通告';exit;
        }

        switch (session(SANDC_KEY.'.genre')) {
        	case '1'://机构人员
        		$arr_viewuser = explode(',',$results['results']['viewuser']);
        		if( !in_array(session(SANDC_KEY.'.userid'), $arr_viewuser) ){
        			$viewuser = empty($results['results']['viewuser'])?session(SANDC_KEY.'.userid'):$results['results']['viewuser'].','.session(SANDC_KEY.'.userid');
        			$params  = array(
        					     'id'       => I('get.id')
        					    ,'viewuser' => $viewuser
                                ,'aaa'      => 1
        				   );
        			$this->httpToApi('Notice/updateNotice',$params);
        		}
        		break;
        	
        	default:break;
        }
        $filePath = 'http://'.$_SERVER['HTTP_HOST'].dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))).'/uploads/notice/'.$results['results']['filename'];
      	if( @file_get_contents($filePath,null,null,-1,1) ? true : false ){
            // $mimeType = 'audio/x-matroska'; 
            // $range = isset($_SERVER['HTTP_RANGE'])?$_SERVER['HTTP_RANGE']:null; 
            // set_time_limit(0);
            // $transfer = new l\Transfer($filePath,$mimeType,$range); 
            // $transfer -> send();
            Header("Location:$filePath"); 
        }else{
            echo '文件不存在';
        }
        exit;
	}
}