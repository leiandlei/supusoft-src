<?php
namespace Home\Controller;
use Think\Controller;
class AjaxController extends ApiController {
	public function login(){
      if( !empty($_POST['password']) ){
          $_POST['password'] = md5($_POST['password']);
      }
  		$results = $this->httpToApi('Login/login_unionlogin',$_POST);
  		if( $results['errorCode']==0 ){
           cookie(SANDC_KEY,$results['results']);
          session(SANDC_KEY,$results['results']);

           cookie(SANDC_KEY.'.extraInfo',$results['extraInfo']);
          session(SANDC_KEY.'.extraInfo',$results['extraInfo']);
          exit(json_encode($results));
      }else{
      	  session(null); // 清空当前的session
          cookie(null); // 清空当前设定前缀的所有cookie值
          exit($this->getArrayForResults(1,$results['errorStr']));
      }
	}

    public function qiandao(){
      $params = array(
                   'pid'  => $_POST['params']['pid']
                  ,'tid'  => $_POST['params']['tid']
                  ,'eid'  => $_POST['params']['eid']
                  ,'lat'  => $_POST['location']['latitude']+0.002
                  ,'lat'  => ($_POST['location']['latitude']+0.002)-0.0006
                  ,'lng'  => $_POST['location']['longitude']+0.006
                );
      $results = $this->httpToApi('Auditor/taskqd',$params);
      exit(json_encode($results));
    }
}