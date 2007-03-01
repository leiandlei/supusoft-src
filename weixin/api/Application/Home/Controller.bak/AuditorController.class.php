<?php
namespace Home\Controller;
use Think\Controller;
class AuditorController extends ApiController {
	public function taskqd(){//签到
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('tid,pid,eid,lat,lng');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }

        /**---是否有数据没有提交---**/
		switch ($this->auto) {
            case 'admin'://管理员
            	$date   = I('post.date');
            	$userID = I('post.userID');
            case 'normal'://正常用户
            	if($this->auto=='normal'){
            		$date   = date('Y-m-d H:i:s');
            		$userID = $this->userID;
            	}

            	/**---查询当前审核任务---**/
            	$join_find = array(
            			 'LEFT JOIN sp_project p ON p.id = tat.pid'
            			,'LEFT JOIN sp_task t ON t.id = tat.tid'
            			,'LEFT JOIN sp_enterprises e ON e.eid = t.eid'
            		);
                $where_find['tat.uid']           = array('EQ',$userID);
                $where_find['tat.taskBeginDate'] = array('ELT',date("Y-m-d H:i:s",strtotime("$date +2 hour")));
                $where_find['tat.taskEndDate']   = array('EGT',date("Y-m-d H:i:s",strtotime("$date -2 hour")));
                $where_find['tat.role']          = array('NEQ','');
                $where_find['tat.deleted']       = array('EQ','0');

                $where_find['t.id']              = array('EQ',I('post.tid'));
                $where_find['t.status']          = array('EQ','3');
                $where_find['t.deleted']         = array('EQ','0');
                
                $where_find['p.id']              = array('EQ',I('post.pid'));
                $where_find['p.is_finish']       = array('EQ','0');
                $where_find['p.deleted']         = array('EQ','0');
                
                $where_find['e.eid']             = array('EQ',I('post.eid'));
                $where_find['e.deleted']         = array('EQ','0');

                $arr_rw = M('task_audit_team tat')->join($join_find)->where($where_find)->order('tat.taskBeginDate')->field('p.id as pid,tat.eid,tat.tid,p.ct_id')->find();
                if( empty($arr_rw) ){
                	$results = self::getArrayForResults( 1,'您暂时还没有审核任务' );
                	break;
                }
                /**---查询当前审核任务---**/

                /**---查询今天是否有签到---**/
                // $where_qd_find['userID']  = array('EQ',$userID);
                // $where_qd_find['eid']     = array('EQ',$arr_rw['eid']);
                // $where_qd_find['tid']     = array('EQ',$arr_rw['tid']);
                // $where_qd_find['ct_id']   = array('EQ',$arr_rw['ct_id']);
                // $where_qd_find['qd_date'] = array('EQ',substr($date,0,10));
                // $where_qd_find['qd_type'] = array('EQ',I('post.type'));
                // $where_qd_find['status']  = array('EQ',1);
                // $arr_qd = M('task_qd')->where($where_qd_find)->find();
                // if( !empty($arr_qd) ){
                // 	$results = self::getArrayForResults( 1,'您今天已经签到' );
                // 	break;
                // }
                /**---查询今天是否有签到---**/

                /**---插入签到信息---**/
                $gaodeApi = "http://restapi.amap.com/v3/geocode/regeo?output=xml&location=".I('post.lng').",".I('post.lat')."&key=f6995bd5740b1a7a55795c9accc3c5c8&radius=1000&extensions=base";
                $str_xml  = file_get_contents($gaodeApi);
                $arr_xml  = self::xmlToArray($str_xml);
                if($arr_xml['info']=='OK'){
                    $add_task_qd['qd_addr']    = $arr_xml['regeocode']['formatted_address'];
                }

                $add_task_qd['userID']         = $userID;
                $add_task_qd['pid']            = $arr_rw['pid'];
                $add_task_qd['eid']            = $arr_rw['eid'];
                $add_task_qd['tid']            = $arr_rw['tid'];
                $add_task_qd['ct_id']          = $arr_rw['ct_id'];
                $add_task_qd['qd_date']        = substr($date,0,10);
                $add_task_qd['qd_dateTime']    = date('Y-m-d H:i:s');
                $add_task_qd['qd_lat']         = I('post.lat');
                $add_task_qd['qd_lng']         = I('post.lng');
                
                $add_task_qd['createTime']     = date('Y-m-d H:i:s');
                $add_task_qd['createUserID']   = $this->userID;
                $add_task_qd['createUserName'] = $this->userInfo['username'];
                if( M('task_qd')->add($add_task_qd) ){
                	$results = self::getArrayForResults( 0,'签到成功' );
                	break;
                }
                $results = self::getArrayForResults( 1,'签到失败' );
                /**---插入签到信息---**/

                break;
            case 'visitor'://游客
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        exit(json_encode($results));
	}

    public function tasknoqdlist(){
        switch ($this->auto) {
            case 'admin'://管理员
                $date   = I('post.date');
                $userID = I('post.userID');
            case 'normal'://正常用户
                if($this->auto=='normal'){
                    $date   = date('Y-m-d');
                    $userID = $this->userID;
                }

                /**---查询当前审核任务---**/
                $join_find = array(
                         'LEFT JOIN sp_project p ON p.id = tat.pid'
                        ,'LEFT JOIN sp_task t ON t.id = tat.tid'
                        ,'LEFT JOIN sp_enterprises e ON e.eid = t.eid'
                        ,'LEFT JOIN sp_unionlogin un ON tat.uid = un.userID'
                    );
                // !empty($userID)&&$where_find['tat.uid'] = array('EQ',$userID);
                $where_find['tat.uid'] = array('IN','101,154');

                $where_find['tat.taskBeginDate'] = array('ELT',substr($date,0,10).' 23:59:59');
                $where_find['tat.taskEndDate']   = array('EGT',substr($date,0,10).' 00:00:00');
                $where_find['tat.audit_type']    = array('NEQ','1002');
                $where_find['tat.role']          = array('NEQ','');
                $where_find['tat.deleted']       = array('EQ','0');
                $where_find['t.status']          = array('EQ','3');
                $where_find['t.deleted']         = array('EQ','0');
                $where_find['p.is_finish']       = array('EQ','0');
                $where_find['p.deleted']         = array('EQ','0');
                $where_find['e.deleted']         = array('EQ','0');
                $where_find['un.status']         = array('EQ','1');

                $field  = 'e.ep_name,e.areaaddr,p.id as pid,p.ct_code,tat.eid,tat.tid,p.ct_id,tat.taskBeginDate,tat.taskEndDate,tat.name,tat.uid as userid,un.unionToken as openid';
                $arr_rw = M('task_audit_team tat')->join($join_find)->where($where_find)->order('tat.taskBeginDate')->field($field)->group('name,taskBeginDate,taskEndDate')->select();
                if( empty($arr_rw) ){
                    $results = self::getArrayForResults( 1,'暂时还没有审核任务' );
                }else{
                    $results = self::getArrayForResults( 0,'',$arr_rw );
                }
                break;
            case 'visitor'://游客
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        exit(json_encode($results));
    }

    //签到列表
    public function taskqdList(){
        /**---------程序开始------**/
        switch ($this->auto) {
            case 'admin'://管理员
                $userID = I('post.userID');
            case 'normal'://正常用户
                if($this->auto=='normal')
                {
                    $userID = $this->userID;
                }
                $join = $where = array();$order = $field='';

                if( !empty($userID) )$where['tq.userID'] = array('EQ',$userID);

                $model  = M('task_qd tq');
                $field .= '';
                
                if(I('post.tid'))$where['tq.tid']     = array('IN',I('post.tid'));
                if(I('post.ct_id'))$where['tq.ct_id'] = array('IN',I('post.ct_id'));

                $where['tq.status'] = array('EQ',1);

                //关联HR表
                $join   = array_merge($join,array('LEFT JOIN sp_hr hr ON tq.userID = hr.id and tq.userID=hr.id'));
                $field .= '';
                $where['hr.deleted'] = array('EQ',0);

                //关联HR表
                $join   = array_merge($join,array('LEFT JOIN sp_enterprises e ON e.eid = tq.eid'));
                $field .= '';
                $where['e.deleted'] = array('EQ',0);

                $seach = self::getSeach();
                $seach['qd_date'] = !empty($seach['qd_date'])?$seach['qd_date']:date('Y-m-d');
                foreach ($seach as $key => $value) {
                    switch ($key) {
                        case 'qd_date'://签到日期
                            $where['tq.qd_date'] = array('EQ',$value);
                            break;
                        case 'qd_type'://签到类型
                            $where['tq.qd_type'] = array('EQ',$value);
                            break;
                        default:
                            break;
                    }
                }

                $count = $model->join($join)->where($where)->count();//总数
                $page  = self::getPageInfo($count);//获取分页
                $list  = $model -> join($join)->where($where)->order($order)->field(empty($field)?'*':$field)->limit($page['limit']['start'],$page['limit']['end'])->select();
                $results = self::getArrayForResults( 0,'',$list,$page );
                break;
            case 'visitor'://游客
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