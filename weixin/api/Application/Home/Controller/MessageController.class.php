<?php
namespace Home\Controller;
use Think\Controller;
class MessageController extends ApiController {

	public function addMessage(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('unionToken,unionType');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetVal[$unsetKey]);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/

		$params = I('post.');
		$params['createTime'] = date('Y-m-d H:i:s');

		$model = M('message');
		$id = $model -> add($params);

		$data = $model->where('id='.$id)->find();
		$results = self::getArrayForResults(0,'成功',$data);
		exit(json_encode($results));
	}

	public function getMessageToWeb(){
 		/**---程序开始---**/
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
            	$status = empty(I('post.tab'))?'0':I('post.tab');

            	$model  = M('message');
                $where['deleted'] = array('EQ',0);

                /**分组统计总数**/
                $tab_0 = $model->where(array_merge($where,array('status'=>array('EQ',0))))->count();
                $tab_1 = $model->where(array_merge($where,array('status'=>array('EQ',1))))->count();
                $tab = array(
                		 'tab_0' => $tab_0
                        ,'tab_1' => $tab_1
                        ,'tab_class_'.I('post.tab') => 'ui-state-active'
                	);
                /**分组统计总数**/
                $page  = self::getPageInfo($tab['tab_'.I('post.tab')]);//获取分页
                $where['status'] = array('EQ',$status);
                $list    = $model->where($where)->limit($page['limit']['start'],$page['limit']['end'])->select();

                $results = self::getArrayForResults( 0,'',$list,array_merge($page,$tab) );
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

    public function updateMessage(){
        /**---是否有数据没有提交---**/
        $unsetKey = self::getUnsetRequest('id,status');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
            exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/


        /**---程序开始---**/
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
                $model = M('message');
                $bool  = $model->where('id='.I('post.id'))->save(array('status'=>I('post.status')));
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
        /**---程序开始---**/
        exit(json_encode($results));
    }
}