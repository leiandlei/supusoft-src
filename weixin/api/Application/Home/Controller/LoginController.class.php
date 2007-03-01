<?php
namespace Home\Controller;
use Think\Controller;
use Org\Lin as l;
class LoginController extends ApiController {
    public function login(){
        switch ($this->auto) {
            case 'admin'://管理员
            case 'normal'://正常用户
                $results = self::getArrayForResults( 1,'您已经登录' );
                break;
            case 'visitor'://游客
                $user = M('hr')->where( "username='%s' and password='%s' and deleted=0",I('post.user'),I('post.password') )->find();
                if( empty($user) ){
                    $results = self::getArrayForResults( 1,'登录失败' );
                }else{
                    $results = self::getArrayForResults( 0,'登录成功',$user,self::getHeaderAuthInfoForUserID($user['id']),$this->getAuto($user['id']) );
                }
                break;
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        echo json_encode($results);exit;
    }

    public function login_unionlogin(){
        if( empty(I('post.unionToken') ))exit( json_encode(self::getArrayForResults( 1,'请刷新页面重试' )) );
        $this->auto = !empty($this->auto)?$this->auto:'visitor';
        switch ($this->auto) {
            case 'admin'://管理员
            case 'normal'://正常用户
                $results = self::getArrayForResults( 0,'您已经登录' );
                break;
            case 'visitor'://游客
                $wxuser = M('unionlogin')->where( "unionToken='%s' and unionType='%s' and status=1",I('post.unionToken'),I('post.unionType') )->find();
                if( empty(I('post.user'))||empty(I('post.password')) ){
                    if( empty($wxuser) ){
                        $results = self::getArrayForResults( 1,'登录失败,请绑定账号' );
                    }else{
                        switch ($wxuser['genre']) {
                            case '1'://机构
                                $wxuser['userInfo'] = M('hr')->where('id='.$wxuser['userid'])->find();
                                break;
                            case '2'://合作方
                                $wxuser['userInfo'] = M('partner')->where('eid='.$wxuser['userid'])->find();
                                break;
                            case '1'://企业
                                $wxuser['userInfo'] = M('enterprises')->where('pt_id='.$wxuser['userid'])->find();
                                break;
                            
                            default:
                                $wxuser['userInfo'] = array();
                                break;
                        }
                        $results = self::getArrayForResults( 0,'登录成功',$wxuser,self::getHeaderAuthInfoForUserID($wxuser['userID']),$this->getAuto($wxuser['userID']) );
                        //M('hr')->where('id='.$wxuser['userID'])->save(array('lastLoginTime'=>date('Y-m-d H:i:s'),'modifyTime'=>date('Y-m-d H:i:s')));
                    }
                }else{
                    if( empty($wxuser) ){
                        //默认查是否是本机构
                        $wxuser = M('hr')->where( "username='%s' and password='%s' and is_stop=1",I('post.user'),I('post.password') )->field('id as id')->find();
                        $genre  = isset($wxuser)?1:'';

                        if( empty($wxuser) ){
                            $wxuser = M('partner')->where( "username='%s' and password='%s' and deleted=0",I('post.user'),I('post.password') )->field('pt_id as id')->find();
                            $genre  = isset($wxuser)?2:'';
                        }

                        if( empty($wxuser) ){
                            $wxuser = M('enterprises')->where( "username='%s' and password='%s' and deleted=0",I('post.user'),I('post.password') )->field('eid as id')->find();
                            $genre  = isset($wxuser)?3:'';
                        }

                        if( empty($wxuser) ){
                            $results = self::getArrayForResults( 1,'登录失败,请检查账号密码' );
                        }else{
                            $params = array(
                                     "userID"     => $wxuser['id']
                                    ,"unionToken" => I('post.unionToken')
                                    ,"unionType"  => I('post.unionType')
                                    ,"genre"      => $genre
                                    ,"createTime" => date("Y-m-d")
                                    ,"modifyTime" => date("Y-m-d")
                                );
                            if( !is_string($params['unionToken'])||strlen($params['unionToken'])< 17 ||empty($params['unionToken']) ){
                                exit(json_encode(self::getArrayForResults( 1,'请刷新页面重试' )));
                            }else{
                                $id = M('unionlogin')->add($params);
                                if($id){
                                    $wxuser  = M('unionlogin')->where( "id='%s' and status=1",$id )->find();
                                    $results = self::getArrayForResults( 0,'登录成功',$wxuser,self::getHeaderAuthInfoForUserID($wxuser['userID']),$this->getAuto($wxuser['userID']) );
                                    //M('hr')->where('id='.$wxuser['userID'])->save(array('lastLoginTime'=>date('Y-m-d H:i:s'),'modifyTime'=>date('Y-m-d H:i:s')));
                                }else{
                                    $results = self::getArrayForResults( 1,'登录失败,请重新登录' );
                                }
                            } 
                        }
                    }else{
                        $results = self::getArrayForResults( 0,'登录成功',$wxuser,self::getHeaderAuthInfoForUserID($wxuser['userID']),$this->getAuto($wxuser['userID']) );
                        //M('hr')->where('id='.$wxuser['userID'])->save(array('lastLoginTime'=>date('Y-m-d H:i:s'),'modifyTime'=>date('Y-m-d H:i:s')));
                    }
                }
                break;
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        echo json_encode($results);
        exit;
    }

}
