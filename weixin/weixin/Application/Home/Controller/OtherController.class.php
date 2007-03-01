<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class OtherController extends ApiController {

	/**客服留言**/
    public function kefuliuyan(){
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
                 'unionToken' => $_SESSION['wxuser']['open_id']
                ,'unionType'  => 4
                ,'nickName'   => $_SESSION['wxuser']['nickname']
                ,'sex'        => $_SESSION['wxuser']['sex']
                ,'location'   => $_SESSION['wxuser']['location']
                ,'avatar'     => $_SESSION['wxuser']['avatar']
            );
            $params = array_merge($params,I('post.'));
            // print_r($params);exit;
            unset($params['sub']);
            $results = $this->httpToApi('Message/addMessage',$params);
            if($results['errorCode']=='0'){
            	$this->success('留言成功','kefuliuyan');
            }else{
            	$this->error('留言失败，请重新留言。谢谢合作','kefuliuyan');
                // $this->error($results['errorStr'],'kefuliuyan');
            }
        }else{
        	$this -> display();
        }
    }
}