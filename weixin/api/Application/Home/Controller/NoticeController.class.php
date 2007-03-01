<?php
namespace Home\Controller;
use Think\Controller;
class NoticeController extends ApiController {
	
	/**
	 * 获取公告
	 */
	public function getNotice(){
	   /**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('type,unionToken');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/

        /**---程序开始---**/
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
            case 'visitor'://游客
                $model  = M('unionlogin');
				
                $where['unionToken'] = array('EQ',I('post.unionToken'));
                $where['status'] = array('EQ',1);
                $info = $model->where($where)->find();
				
                if( !empty($info) ){
                    $where  = array();
                    $model  = M('notice');
                    $where['status'] = array('EQ',1);
					$type            = I('post.type');
                    switch ($info['genre']) {
                        case '1'://机构用户
                        	if( !empty($type)&&in_array($type,array('2','11')) )
                        	{
                        		$where['type']   = array('IN','2,11');
                        	}else{
                        		$where['type']   = $type;
                        	}
                            break;
                        case '2'://合作方
                        case '3'://客户
                        	if( !empty($type) )
                        	{
                        		$where['type']   = array('IN',$type.',11');
                        	}
                            break;
                    }
					
					if($type=='11' || $type=='2')
					{
						$where[]         = '(ISNULL(receiveuser) or FIND_IN_SET('.$this->userID.',receiveuser))';
					}

                    $count   = $model->where($where)->count();//总数
                    $page    = self::getPageInfo($count);//获取分页
                    $list    = $model->where($where)->order('id DESC')->limit($page['limit']['start'],$page['limit']['end'])->select();
                    $results = self::getArrayForResults( 0,'',$list,$page );
                }else{
                    $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                }
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

    /**
     * 公告详情
     * @return [type] [description]
     */
    public function getNoticeDetail(){
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
            case 'visitor'://游客
                $model  = M('notice');
                $where['status'] = array('EQ',1);
                $where['id']   = array('EQ',I('post.id'));

                $list    = $model->where($where)->find();
				if( !empty($list) )
				{
					$viewuser   = explode(',', $list['viewuser']);
					$viewuser[] = $this->userID;
					$viewuser   = array_unique(array_filter($viewuser));
					$viewuser   = implode(',', $viewuser);
                	M('notice')->where('id='.I('post.id'))->save(array('viewuser'=>$viewuser));
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

    /**
     * 更新公告
     * @return [type] [description]
     */
    public function updateNotice(){
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
                $params = I('post.');unset($params['id']);
                $id     = M('notice')->where('id='.I('post.id'))->save($params);
                if($id){
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
        /**---程序开始---**/
        exit(json_encode($results));
    }
}