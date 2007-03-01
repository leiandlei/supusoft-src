<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
use Org\Lin    as lin;


class ShenheyuanController extends CommonController {
	
	/**审核员通知**/
	public function gettongzhi_list()
	{
		$results = $this->httpToApi('Notice/getNotice',array('type'=>2,'page'=>I('get.page'),'size'=>10));
		exit(json_encode($results));
	}
	public function tongzhi_detail()
	{
		if(empty(I('get.id')))$this->redirect('Shenheyuan/tongzhi');
        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        if( $results['errorCode']!='0'||(count($results['results'])<1) )$this->redirect('Shenheyuan/tongzhi');
        $this -> assign($results);
        $this -> display();
	}
	
	/**任务查询**/
    public function getrenwuchaxun(){
        $params = array(
                     'is_finish' => I('get.is_finish')
                    ,'page'      => $_GET['page']
                    ,'size'      => $_GET['size']
                    ,'data_renwu'=> 1
                );
//      echo '<pre />';
//		print_r($params);exit;
    	$results = $this->httpToApi('Task/getAuditTaskList',$params);
		$results['audit_type'] = array('1001'=>'初审','1002'=>'一阶段','1003'=>'二阶段','1004'=>'监一','1005'=>'监二','1006'=>'监三','1007'=>'再认证','1008'=>'专项审核','1009'=>'特殊监督','1101'=>'变更','99'=>'其他');
        $results['role']       = array('01'=>'组长','02'=>'组员');
        exit(json_encode($results));
    }

	/**签到**/
	public function qiandao(){
        //微信授权
        global $arrOptions;
        $weAuto = new wx\TPWechat($arrOptions);
        $auth = $weAuto->checkAuth();
        if(empty($_SESSION['wxuser']['JsTicket'])||(time()-$_SESSION['wxuser']['JsTicketTime'])>7100 )
        {
          	$js_ticket = $weAuto->getJsTicket();
          	$_SESSION['wxuser']['JsTicket']=$js_ticket;
          	$_SESSION['wxuser']['JsTicketTime']=time();
        }else
        {
          $js_ticket=$_SESSION['wxuser']['JsTicket'];
        }

        if (!$js_ticket) {
            echo "获取js_ticket失败！<br>";
            echo '错误码：'.$weAuto->errCode;
            exit;
        }
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->weAutoInfo = $weAuto->getJsSign($url);

        $results          = $this->httpToApi('Task/getAuditTaskList',array('is_finish'=>'all'));
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
		$this -> display();
    }

	/**进度查询**/
	public function jindu()
	{
        
        $results = $this->httpToApi('Task/getAllTaskList',$params);

        $data    = array();
        foreach ($results['results'] as $value)
        {

           $timeinfo   = lin\W2Time::getTimesArrayBetweenDateTime($value['taskbegindate'],$value['taskenddate']);
         
           $begintime  = substr($value['taskbegindate'],10,9);
           $endtime    = substr($value['taskenddate'],10,9);
           if( count($timeinfo)>1 )
           {
                for($i=0,$k=count($timeinfo);$i<$k;$i++)
                {
                    $tmp = array();
                    $tmp['data']   = $timeinfo[$i];
                    switch ($value['data_for']) {
                        case '2':
                            $tmp['color']  = 'green';
                            break;
                        case '6':
                            $tmp['color']  = 'red';
                            break;
                        case '5':
                            $tmp['color']  = 'blue';
                            break;
                        default:
                            $tmp['color']  = 'yellow';
                            break;
                    }
                    if($i==0){
                        $tmp['starttime'] = $begintime;
                        $tmp['endttime']  = '18:00:00';
                    }elseif(($k-$i)=='1'){
                        $tmp['starttime'] = '08:00:00';
                        $tmp['endttime']  = $endtime;
                    }else{
                        $tmp['starttime'] = '08:00:00';
                        $tmp['endttime']  = '18:00:00';
                    }
                    $data[] = $tmp;
                }
           }
        }
        $this->assign("data",json_encode($data));
		$this->display();
	}
    
    public function changearray($arr)
    { 
        $newarray=array();   
        foreach($arr as $key=>$val){  
            if(is_array($val)){  
                slef::changearray($val);  
            }else{  
                global $newarray;  
                $newarray[$key]=$val;  
            }  
        }  
    } 
}