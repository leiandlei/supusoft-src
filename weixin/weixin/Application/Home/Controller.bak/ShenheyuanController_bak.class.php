<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
use Org\Lin    as lin;
class ShenheyuanController extends CommonController {

	/**签到**/
	public function qiandao(){
        //微信授权
        global $arrOptions;
        $weAuto = new wx\TPWechat($arrOptions);
        $auth = $weAuto->checkAuth();
        if(empty($_SESSION['wxuser']['JsTicket'])||(time()-$_SESSION['wxuser']['JsTicketTime'])>7100 ){
          $js_ticket = $weAuto->getJsTicket();
          $_SESSION['wxuser']['JsTicket']=$js_ticket;
          $_SESSION['wxuser']['JsTicketTime']=time();
        }else{
          $js_ticket=$_SESSION['wxuser']['JsTicket'];
        }
        if (!$js_ticket) {
            echo "获取js_ticket失败！<br>";
            echo '错误码：'.$weAuto->errCode;
            exit;
        }
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->weAutoInfo = $weAuto->getJsSign($url);

        $results          = $this->httpToApi('Task/getAuditTaskList',array('is_finish'=>'0'));
        $this -> assign('shenherenwu',$results['results']);

        $tid = $ct_id = array();
        foreach ($results['results'] as $shenherenwu)
        {
            $tid[]   = $shenherenwu['tid'];
            $ct_id[] = $shenherenwu['ct_id'];
        }

        if( !empty($tid)&&!empty($ct_id) )
        {
            $results = $this->httpToApi('Auditor/taskqdList',array('tid'=>implode(',', $tid),'ct_id'=>implode(',',$ct_id)));
            $this -> assign('qiandaodlist',$results['results']);
        }

        $this -> assign('username',$_SESSION[SANDC_KEY]['userInfo']['name']);
        $this -> display();
    }

    /**审核员通知**/
    public function shenheyuantongzhi(){
        $results = $this->httpToApi('Notice/getNotice',array('type'=>2,'page'=>$_GET['page'],'size'=>$_GET['size']));
        $page    = new lin\Page($results['extraInfo']);
        
        $this->page   = $page->getPage();
        $this->results = $results['results'];
        $this -> display();
    }

    /**审核员通知详情**/
    public function shenheyuantongzhidetail(){
        if(empty(I('get.id')))$this->redirect('Shenheyuan/shenheyuantongzhi');
        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        if( $results['errorCode']!='0'||(count($results['results'])<1) )$this->redirect('Shenheyuan/shenheyuantongzhi');
        $this -> assign($results);
        $this -> display();
    }
	
	/**审核员通知详情**/
    public function shenheyuantongzhicontent(){
        if(empty(I('get.id')))$this->redirect('Shenheyuan/shenheyuantongzhi');
        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        if( $results['errorCode']!='0'||(count($results['results'])<1) )$this->redirect('Shenheyuan/shenheyuantongzhi');
        $this -> assign($results);
        $this -> display();
    }

    /**任务查询**/
    public function renwuchaxun(){
        $params = array(
                     'is_finish' => empty($_GET['tab'])?'0':$_GET['tab']
                    ,'tab'       => empty($_GET['tab'])?'0':$_GET['tab']
                    ,'page'      => $_GET['page']
                    ,'size'      => $_GET['size']
                );
    	$results = $this->httpToApi('Task/getAuditTaskList',$params);
        $page    = new lin\Page($results['extraInfo']);
        
        $this->page       = $page->getPage();
        $this->extraInfo  = $results['extraInfo'];
        $this->results    = $results['results'];
        $this->audit_type = array('1001'=>'初审','1002'=>'一阶段','1003'=>'二阶段','1004'=>'监一','1005'=>'监二','1006'=>'监三','1007'=>'再认证','1008'=>'专项审核','1009'=>'特殊监督','1101'=>'变更','99'=>'其他');
        $this->role       = array('01'=>'组长','02'=>'组员');
        $this->display();
    }

}