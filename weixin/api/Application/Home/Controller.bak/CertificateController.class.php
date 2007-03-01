<?php
namespace Home\Controller;
use Think\Controller;
class CertificateController extends ApiController {
	public function getCertificateList(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('unionToken,unionType');
        if ( $unsetKey !== null){
            $results = self::getArrayForResults(1,'部分数据未提交，请检查。'.$unsetKey);
        	exit(json_encode($results));
        }
        /**---是否有数据没有提交---**/

        $unloginModel = M('unionlogin');
        $unloginModel = $unloginModel->where( "unionToken='%s' and unionType=%s and status=1",array(I('post.unionToken'),I('post.unionType')) )->find();
        if( empty($unloginModel) ){
        	$results = self::getArrayForResults( 1,'您没有权限执行该操作' );
        	exit(json_encode($results));
        }

        /**---程序开始---**/
        $join = $where = array();$order = $field='';

        $model  = M('enterprises e');
        $where['e.deleted'] = array('EQ',0);
        switch ( $unloginModel['genre'] ){//获取用户类型
            case '1'://机构内
                exit(json_encode(self::getArrayForResults( 1,'您没有权限执行该操作' )));
                break;
            case '2'://合作方
            	$where['e.ctfrom'] = array('EQ',$unloginModel['userid']);
            	break;
            case '3'://客户
            	$where['e.eid'] = array('EQ',$unloginModel['userid']);
            	break;
            default:break;
        }

        //关联证书表
        $join   = array_merge($join,array('LEFT JOIN sp_certificate c ON c.eid = e.eid'));
		$where['c.deleted'] = array('EQ',0);

		$order = 'c.id desc';

		$count = $model->join($join)->where($where)->count();//总数
        $page  = self::getPageInfo($count);//获取分页
        $list  = $model->join($join)->where($where)->order($order)->field($field)->limit($page['limit']['start'],$page['limit']['end'])->select();
        
        $results = self::getArrayForResults( 0,'',$list,$page );
        /**---程序结束---**/
        exit(json_encode($results));
        
	}
}