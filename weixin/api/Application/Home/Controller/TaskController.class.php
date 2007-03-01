<?php
namespace Home\Controller;
use Think\Controller;
class TaskController extends ApiController {

    /**
     * 审核任务
     */
    public function getAllTaskList(){
        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        switch ($this->auto){//获取用户类型
            case 'admin'  ://管理员
            case 'visitor'://游客
                $userID = I('post.userID');
            case 'normal'://正常用户
                if($this->auto=='normal')
                {
                    $userID = $this->userID;
                }

                if(!empty($userID))
                {
                    $where['tat.uid'] = array('EQ',$userID);
                }

                $model  = M('task_audit_team tat');
                $field .= 'tat.*';
                $where['tat.deleted']       = array('EQ',0);
                $date                       = I('post.date')?I('post.date'):date('Y-m-d');
                $where['tat.taskBeginDate'] = array('EGT',substr($date,0,7).'-01 00:00:00');
                $where['tat.taskEndDate']   = array('ELT',substr($date,0,7).'-31 23:59:59');
                
                //关联sp_unionlogin表
                $join   = array_merge($join,array('LEFT JOIN sp_unionlogin un ON un.userID = tat.uid '));
                $field .= ',un.unionToken';
                $where['un.status'] = array('EQ',1);
                
                $list  = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->select();
                // echo $model->getLastSql();exit;
                $results = self::getArrayForResults( 0,'',$list,null );

                
                break;
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        /**---程序开始---**/
        exit(json_encode($results));
    }
    public function getAuditTaskList(){
        /**---是否有数据没有提交---**/
        //$unsetKey = self::getUnsetRequest('type');
        //if ( $unsetKey !== null){
        //    $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        //  exit(json_encode($results));
        //}
        /**---是否有数据没有提交---**/

        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        switch ($this->auto){//获取用户类型
            case 'admin'  ://管理员
            case 'visitor'://游客
                $userID = I('post.userID');
            case 'normal'://正常用户
                if($this->auto=='normal')
                {
                    $userID = $this->userID;
                }

                if(!empty($userID))
                {
                    $where['tat.uid'] = array('EQ',$userID);
                }

                $is_finish = empty(I('post.is_finish'))?0:I('post.is_finish');

                $model  = M('task_audit_team tat');
                $field .= 'tat.*';
                $where['tat.deleted']       = array('EQ',0);
                $where['tat.role']          = array('NEQ','');
                $date                       = I('post.date')?I('post.date'):date('Y-m-d');
                //往后推半个月的项目信息列表
                if (I('post.data_renwu')=='1')
                {
                	//1已完成 0未完成
                	if($is_finish=='1')
                	{
                		$date2  = date("Y-m-d",strtotime("-15 day",strtotime($date)));
	                    $where['tat.taskEndDate'] = array('EGT',substr($date2,0,10).' 00:00:00');
                	}else {
                		$date2  = date("Y-m-d",strtotime("+15 day",strtotime($date)));
	                    $where['tat.taskBeginDate']   = array('ELT',substr($date2,0,10).' 23:59:59');
						$where['tat.taskEndDate']     = array('EGT',substr($date,0,10).' 00:00:00');
                	}
					
                }else{
                    $where['tat.taskBeginDate'] = array('ELT',substr($date,0,10).' 23:59:59');
                    $where['tat.taskEndDate']   = array('EGT',substr($date,0,10).' 00:00:00');
                }
                $order  = 'tat.taskBeginDate';

                
                //关联sp_project表
                $join   = array_merge($join,array('sp_project p ON p.id = tat.pid'));
                $field .= ',p.is_finish,p.rect_finish,p.audit_type,p.cti_code,p.ct_code,p.ct_id,p.id,p.comment_pass_date,p.comment_pass,p.sp_date,p.sv_note';
                $where['p.deleted'] = array('EQ',0);
                

                //关联sp_task表
                $join   = array_merge($join,array('LEFT JOIN sp_task t ON t.id = tat.tid'));
                $field .= ',t.jh_sp_date,t.jh_sp_note,t.jh_sp_name,t.bufuhe,t.if_push,t.upload_file_date,t.jh_sp_status';
                $where['t.deleted'] = array('EQ',0);
                // $where['t.status'] = array('EQ',3);
                //关联sp_contract
                $join   = array_merge($join,array('LEFT JOIN sp_contract c ON c.eid = t.eid'));
                $field .= ',c.is_site';
                $where['c.deleted'] = array('EQ',0);
                //关联sp_enterprises表
                $join   = array_merge($join,array('LEFT JOIN sp_enterprises e ON e.eid = t.eid '));
                $field .= ',e.ep_name,e.areaaddr';
                $where['e.deleted'] = array('EQ',0);

                //关联sp_unionlogin表
                $join   = array_merge($join,array('LEFT JOIN sp_unionlogin un ON un.userID = tat.uid '));
                $field .= ',un.unionToken';
                $where['un.status'] = array('EQ',1);
                // print_r($join);exit;
                // $seach = self::getSeach();
                // foreach ($seach as $key => $value) {
                //     switch ($key) {
                //         default:
                //             break;
                //     }
                // }
                if($is_finish!='all')
                {
                	$where['p.is_finish'] = array('EQ',$is_finish);
                }
				$list  = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->group('tat.tid,tat.uid')->order($order)->select();
//				echo $model->getLastSql();exit;
				$page  = self::getPageInfo(count($list));//获取分页
				if( !empty($page['limit']) )
			    {
			    	$list    = array_slice($list,$page['limit']['start'],$page['limit']['end']);
			    }
				$results = self::getArrayForResults( 0,'',$list );
                break;
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        /**---程序开始---**/
        exit(json_encode($results));
    }
}