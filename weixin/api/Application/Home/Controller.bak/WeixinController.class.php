<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class WeixinController extends ApiController {
	Public function _initialize(){
		$unsetKey = self::getUnsetRequest('unionToken,unionType');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        $model = M('unionlogin');
        $where['unionToken'] = array('EQ',I('post.unionToken'));
        $where['unionType']  = array('EQ',I('post.unionType'));
        $where['status']     = array('EQ',1);
        
        $this->user = $user = $model->where($where)->find();
        $this->auto = empty($user)?self::getAuto():self::getAuto($user['userid']);
	}

    /**
     * 根据用户openID获取用户信息
     * @return [type] [description]
     */
    public function getUserInfoByOpenID(){
        $results = self::getArrayForResults( 0,'',$this->userInfo );
        exit(json_encode($results));
    }

    /**
     * 回传
     */
    public function huichuan(){

        /**---------程序开始------**/
        switch ($this->auto) {
            case 'admin'://管理员
            case 'normal'://正常用户
                $date   = date('Y-m-d H:i:s');
                $userID = $this->userID;
                /**---查询当前审核任务---**/
                $join_find = array(
                         'LEFT JOIN sp_project p ON p.id = tat.pid'
                        ,'LEFT JOIN sp_task t ON t.id = tat.tid'
                        ,'LEFT JOIN sp_enterprises e ON e.eid = t.eid'
                    );
                $where_find['tat.uid']           = array('EQ',$userID);
                $where_find['tat.taskBeginDate'] = array('ELT',$date);
                $where_find['tat.taskEndDate']   = array('EGT',$date);
                $where_find['tat.role']          = array('NEQ','');
                $where_find['tat.deleted']       = array('EQ','0');
                $where_find['t.status']          = array('EQ','3');
                $where_find['t.deleted']         = array('EQ','0');
                $where_find['p.is_finish']       = array('EQ','0');
                $where_find['p.deleted']         = array('EQ','0');
                $where_find['e.deleted']         = array('EQ','0');

                $arr_rw = M('task_audit_team tat')->join($join_find)->where($where_find)->order('tat.taskBeginDate')->field('p.id as pid,tat.eid,tat.tid,p.ct_id')->find();
                if( empty($arr_rw) ){
                    $arr_rw = array(
                                 'pid'   => 0
                                ,'eid'   => 0
                                ,'tid'   => 0
                                ,'ct_id' => 0
                              );
                }
                /**---查询当前审核任务---**/

                //获取照片
                $image = parent::get_photo(I('post.url'));
                /**---存入照片---**/
                $params = array();
                $params['userID']         = $userID;
                $params['pid']            = $arr_rw['pid'];
                $params['eid']            = $arr_rw['eid'];
                $params['tid']            = $arr_rw['tid'];
                $params['ct_id']          = $arr_rw['ct_id'];
                $params['image']          = 'http://'.$_SERVER["HTTP_HOST"].$image;
                $params['content']        = I('post.content');

                $params['createTime']     = date('Y-m-d H:i:s');
                $params['createUserID']   = $this->userID;
                $params['createUserName'] = $this->userInfo['username'];
                if( M('task_hc')->add($params) ){
                    $results = self::getArrayForResults( 0 );
                    break;
                }
                $results = self::getArrayForResults( 1,'回传失败，请重新上传' );
                /**---存入照片---**/
                break;
            case 'visitor'://游客
                $results = self::getArrayForResults( 2,'您还没有绑定账号' );
                break;
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        /**---------程序开始------**/
        exit(json_encode($results));
    }

}