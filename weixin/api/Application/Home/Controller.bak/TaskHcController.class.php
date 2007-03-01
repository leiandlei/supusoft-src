<?php
namespace Home\Controller;
use Think\Controller;
class TaskHcController extends ApiController {

	/**
	 * 审核员回传信息
	 */
	public function getTaskHcEList(){
        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        $tab  = empty(I('post.tab'))?'0':I('post.tab');
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
                if($this->auto=='normal'){
                    $where['th.userID']=$this->userID;
                }
            	$model  = M('task_hc th');
            	$where['th.deleted'] = array('EQ',0);

                $tab_0   = count($model->join($join)->where(array_merge($where,array('th.status'=>array('EQ',0))))->group('th.ct_id')->select());
                $tab_1   = count($model->join($join)->where(array_merge($where,array('th.status'=>array('EQ',1))))->group('th.ct_id')->select());
                $arr_tab = array(
                		 'tab_0' => $tab_0
                        ,'tab_1' => $tab_1
                        ,'tab_class_'.$tab => 'active'
                	);
               
                /**分组统计总数**/
                $page  = self::getPageInfo($arr_tab['tab_'.$tab]);//获取分页
                
                $where['th.status'] = array('EQ',$tab);
                $list  = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->group('th.eid')->limit($page['limit']['start'],$page['limit']['end'])->select();
                
                $data = array();
                $nullModel = new \Think\Model();
                foreach ($list as $value) {
                	if( !empty($value['eid']) ){
                		$sql ="select * from sp_enterprises where eid=".$value['eid'];
                		$enterprises = $nullModel->query($sql);
                		$value['enterprises'] = $enterprises[0];
                	}

                    $sql ="select * from sp_unionlogin where genre=1 and userID=".$value['userid'];
                    $unionlogin = $nullModel->query($sql);
                    if( !empty($unionlogin) ){
                        $value['unionlogin'] = $unionlogin[0];
                    }

                	$data[]=$value;
                }

                //echo $model->getLastSql();exit;
                $results = self::getArrayForResults( 0,'',$data,array_merge($page,$arr_tab) );
            	break;
            case 'visitor'://游客
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

	/**
	 * 审核员回复信息列表
	 */
	public function getTaskHcListByEid(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid');
        if ( $unsetKey !== null){
           $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/

        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
            	$model  = M('task_hc th');
            	$where['th.deleted'] = array('EQ',0);
            	$where['th.eid']     = array('EQ',I('post.eid'));
               
                /**分组统计总数**/
                $count = $model ->join($join)->where($where)->count();
                $page  = self::getPageInfo($count);//获取分页
                $list  = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->limit($page['limit']['start'],$page['limit']['end'])->order('th.`status` asc')->select();
                
                $data = array();
                $nullModel = new \Think\Model();
                foreach ($list as $value) {
                	if( !empty($value['eid']) ){
                		$sql ="select * from sp_enterprises where eid=".$value['eid'];
                		$enterprises = $nullModel->query($sql);
                		$value['enterprises'] = $enterprises[0];
                	}
                	if( !empty($value['userid']) ){
                		$sql ="select * from sp_hr where id=".$value['userid'];
                		$hr  = $nullModel->query($sql);
                		$value['hr'] = $hr[0];
                	}

                    $sql ="select * from sp_unionlogin where genre=1 and userID=".$value['userid'];
                    $unionlogin = $nullModel->query($sql);
                    if( !empty($unionlogin) ){
                        $value['unionlogin'] = $unionlogin[0];
                    }

                	$data[]=$value;
                }
                $results = self::getArrayForResults( 0,'',$data,$page );
            	break;
            case 'visitor'://游客
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

	/**
	 * 修改信息
	 */
	public function update(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('id');
        if ( $unsetKey !== null){
           $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/

        /**---程序开始---**/
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
            	$model  = M('task_hc th');
            	$id     = I('post.id');
            	$params = I('post.');unset($params['id']);

            	$bool   = $model->where( 'id='.$id )->save($params);
            	if($bool){
            		$results = self::getArrayForResults( 0,'成功' );
            	}else{
            		$results = self::getArrayForResults( 1,'失败' );
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
        /**---程序结束---**/
        exit(json_encode($results));
	}
}