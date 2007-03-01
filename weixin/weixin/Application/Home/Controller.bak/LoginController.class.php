<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class LoginController extends ApiController {
    public function index(){
        global $arrOptions;
        $Auth = new wx\Auth($arrOptions);
        $Auth = $Auth->wxuser;
        $this->wxuser=$Auth;
        // print_r($Auth);exit;
        if( $Auth['return']['errorCode']==1 ){
            if( empty($Auth['return']['results']) ){
                header("Location:http://cams.lll.cn/weixin/weixin/index.php/Home/");exit;
            }
            switch ($Auth['return']['results']['key']) {
                case 'authUrl':
                    header("Location:".$Auth['return']['results']['value']);exit;
                    break;
            }
        }else{
            $results = $this->httpToApi('Login/login_unionlogin',array('unionToken'=>$Auth['open_id'],'unionType'=>4));
            $actionName = empty(session('actionName'))?cookie('actionName'):session('actionName');
            $actionName = empty($actionName)?'Index/index':$actionName;
            // print_r($results);exit;
            if( $results['errorCode']==0 ){
                //  cookie(SANDC_KEY,null);
                // session(SANDC_KEY,null);
                $_SESSION[SANDC_KEY]=$results['results'];
                $_SESSION[SANDC_KEY]['extraInfo']=$results['extraInfo'];
                // print_r($_SESSION);exit;
                cookie(SANDC_KEY,$results['results']);
                cookie(SANDC_KEY.'.extraInfo',$results['extraInfo']);
                $this->redirect($actionName);exit;
            }else{
                // print_r($this->wxuser);exit;
                if( in_array($actionName,array('Enterprises/shenqingrenzheng')) ){
                    //  cookie(SANDC_KEY,null);
                    // session(SANDC_KEY,null);
                     cookie('wxuser',$Auth);
                    session('wxuser',$Auth);
                    $this->redirect($actionName);exit;
                }else{
                    $this->display('Login/index');exit;
                }
                
            }
            
        }
        
    }
}
