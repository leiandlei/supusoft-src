<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
use Org\Lin    as lin;
class EnterprisesController extends CommonController {
    Public function _initialize(){}
	
	/**申请认证**/
    public function shenqingrenzheng(){
        if( empty($_SESSION['wxuser']['open_id']) ){
            global $arrOptions;
            $Auth = new wx\Auth($arrOptions);
            $Auth = $Auth->wxuser;
            if( $Auth['return']['errorCode']==1 ){
                switch ($Auth['return']['results']['key']) {
                    case 'authUrl':
                        header("Location:".$Auth['return']['results']['value']);exit;
                        break;
                }
            }
            $_SESSION['wxuser']=$Auth;
        }
        if( I('post.sub') ){
            $params = array(
                 'unionToken' => session('wxuser.open_id')
                ,'unionType'  => 4
                ,'nickName'   => session('wxuser.nickname')
                ,'sex'        => session('wxuser.sex')
                ,'location'   => session('wxuser.location')
                ,'avatar'     => session('wxuser.avatar')
            );
            $params = array_merge($params,I('post.'));
            unset($params['sub']);
            $results = $this->httpToApi('Renzhengapply/apply',$params);
        }else{
            $params = array(
                 'unionToken' => session('wxuser.open_id')
                ,'unionType'  => 4
            );
            $results = $this->httpToApi('Renzhengapply/getApply',$params);
        }
        $this->results = $results['results'];
        $this -> display();
    }

    /**审核进度**/
    public function shenhejindu(){
        // session(SANDC_KEY,null);
        // cookie(SANDC_KEY,null);
        // unset($_SESSION);
        // var_dump($this->checkLogin());exit;
        // 初始化的时候检查用户权限
        if( !$this->checkLogin() ){
            session('actionName',null);
             cookie('actionName',null);
            session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
             cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
            $this->redirect('Login/index');exit;
        }

        $params  = array(
                         'page'=>$_GET['page']
                        ,'size'=>$_GET['size']
                        ,'unionToken'=> session(SANDC_KEY.'.uniontoken')
                        ,'unionType' => 4
                    );
        $results = $this->httpToApi('Progress/shenhejinduList',$params);
        $page    = new lin\Page($results['extraInfo']);
        $this->page   = $page->getPage();
        $this->results = $results['results'];
    	$this -> display();
    }

    /**通知公告**/
    public function tongzhigonggao(){
        // 初始化的时候检查用户权限
        if( !$this->checkLogin() ){
            session('actionName',null);
             cookie('actionName',null);
            session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
             cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
            $this->redirect('Login/index');exit;
        }
    	$results = $this->httpToApi('Notice/getNotice',array('type'=>1,'page'=>$_GET['page'],'size'=>$_GET['size']));
        $page    = new lin\Page($results['extraInfo']);
        
        $this->page   = $page->getPage();
        $this->results = $results['results'];
        $this -> display();
    }

    /**客户公告**/
    public function kehugonggao(){
        // 初始化的时候检查用户权限
        if( !$this->checkLogin() ){
            session('actionName',null);
             cookie('actionName',null);
            session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
             cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
            $this->redirect('Login/index');exit;
        }
        $results = $this->httpToApi('Notice/getNotice',array('type'=>3,'page'=>$_GET['page'],'size'=>$_GET['size']));
        $page    = new lin\Page($results['extraInfo']);
        
        $this->page   = $page->getPage();
        $this->results = $results['results'];
        $this -> display();
    }

    /**通知公告详情**/
    public function tongzhigonggaoDetail(){
        // 初始化的时候检查用户权限
        if( !$this->checkLogin() ){
            session('actionName',null);
             cookie('actionName',null);
            session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
             cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
            $this->redirect('Login/index');exit;
        }
        if(empty(I('get.id')))$this->redirect('Notice/getNoticeDetail');
        $results = $this->httpToApi('Notice/getNoticeDetail',array('id'=>I('get.id')));
        if( $results['errorCode']!='0'||(count($results['results'])<1) )$this->redirect('Shenheyuan/shenheyuantongzhi');
        $this -> assign($results);
        $this -> display();
    }

    /**证书状态**/
    public function zhengshuzhuangtai(){
        // 初始化的时候检查用户权限
        if( !$this->checkLogin() ){
            session('actionName',null);
             cookie('actionName',null);
            session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
             cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
            $this->redirect('Login/index');exit;
        }
        $params  = array(
                         'page'=>$_GET['page']
                        ,'size'=>$_GET['size']
                        ,'unionToken'=> session(SANDC_KEY.'.uniontoken')
                        ,'unionType' => 4
                    );
        $results = $this->httpToApi('Certificate/getCertificateList',$params);
        $page    = new lin\Page($results['extraInfo']);
        
        $this->status  = array('01'=>'有效','02'=>'失效');
        $this->iso     = array('A01'=>'质量','A02'=>'环境','A03'=>'职业健康安全管理体系');
        $this->page    = $page->getPage();
        $this->results = $results['results'];
        $this -> display();
    }
}