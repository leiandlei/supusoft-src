<?php
namespace Home\Controller;
use Think\Controller;
class OutApiController extends ApiController {
    Public function _initialize(){
        $this->auto = self::getAuto();
    }
	
	/**
	 * 用户新增
	 * @version 2016年7月1日16:48:44
	 */
	public function userAdd(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('username,password');
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
                $model = M('customer');

            	$customer = $model->where( "username='%s'",I('post.username') )->find();
            	if( !empty($customer) ){
            		$results = self::getArrayForResults( 1,'用户名已存在' );break;
            	}

            	$is_ok = $model->add(I('post.'));
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'用户添加失败,请重新添加' );break;
            	}
                $list = $model->where("cu_id=%s and deleted=0",$is_ok)->find();
            	$results = self::getArrayForResults( 0,'添加成功',$list );
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
	 * 用户修改
	 * @version 2016年7月1日16:48:33
	 */
	public function userEdit(){
		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('cu_id');
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
                $model = M('customer');

            	$customer = $model->where( "cu_id='%s' and deleted=0",I('post.cu_id') )->find();
            	if( empty($customer) ){
            		$results = self::getArrayForResults( 1,'用户不存在' );break;
            	}

            	$params = I('post.');unset($params['cu_id']);
            	$is_ok  = $model->where('cu_id=%s and deleted=0',I('post.cu_id'))->save($params);
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'修改失败,请重新修改' );break;
            	}

                $list    = $model->where("cu_id=%s and deleted=0",I('post.cu_id'))->find();
            	$results = self::getArrayForResults( 0,'修改成功',$list );
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
	 * 企业新增
	 * @version 2016年7月1日17:04:23
	 */
	public function enterprisesAdd(){
		//$results = self::getArrayForResults( 1,'开发中。。。' );
		//exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('cu_id,work_code,ctfrom,ep_name,nature,statecode,industry,delegate,ep_amount,manager_daibiao,phone_daibiao,capital,currency,ep_phone,ep_fax,areaaddr,areacode,ep_addr,ep_addrcode,cta_addr,cta_addrcode,gb_addr');
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
                $model = M('enterprises');

                $params = I('post.');
                if( !empty($params['prod_check']) ){
                    $prod_check = explode(',',$params['prod_check']);
                    $params['prod_check'] = unserialize($prod_check);
                }
            	$is_ok  = $model->add($params);
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'用户添加失败,请重新添加' );break;
            	}
                $list    = $model->where("eid=%s and deleted=0",$is_ok )->find();
            	$results = self::getArrayForResults( 0,'添加成功',$list );
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
	 * 企业修改
	 * @version 2016年7月1日17:04:23
	 */
	public function enterprisesEdit(){
		//$results = self::getArrayForResults( 1,'开发中。。。' );
		//exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid');
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
                $model       = M('enterprises');
                $enterprises =$model->where( "eid='%s' and deleted=0",I('post.eid') )->find();
                if( empty($enterprises) ){
                    $results = self::getArrayForResults( 1,'企业不存在' );break;
                }

                $params = I('post.');unset($params['eid']);
                if( !empty($params['prod_check']) ){
                    $prod_check = explode(',',$params['prod_check']);
                    $params['prod_check'] = unserialize($prod_check);
                }

                $is_ok  = $model->where('eid=%s and deleted=0',I('post.eid'))->save($params);
                if( !$is_ok ){
                    $results = self::getArrayForResults( 1,'修改失败,请重新修改' );break;
                }

                $list    = $model->where("eid=%s and deleted=0",I('post.eid') )->find();
                $results = self::getArrayForResults( 0,'修改成功',$list );
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
	 * 财务新增
	 * @version 2016年7月1日17:04:23
	 */
	public function metasEdit(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid');
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
            	$enterprises = M('enterprises')->where( "eid='%s' and deleted=%s",I('post.eid'),0 )->find();
                if( empty($enterprises) ){
                    $results = self::getArrayForResults( 1,'合同不存在' );break;
                }

                $paramsNew = $paramsOld = array();
                $model  = M('metas_ep');
                $where['ID']      = array('EQ',I('post.eid'));
                $where['deleted'] = array('EQ',0);
                $list = $model->where($where)->select();
                if( count($list)>0 ){
                    foreach ($list as $value) {
                        if( !empty(I("post.".$value['meta_name'])) )
                            $paramsOld[$value['meta_name']]=$value['meta_value'];
                    }
                }

                foreach (I('post.') as $key => $value) {
                    if( !array_key_exists($key,$paramsNew)&&$key!='eid' ){
                        $paramsNew[$key]=I('post.'.$key);
                    }
                }

                foreach ($paramsNew as $key => $value) {
                    if( array_key_exists($key,$paramsOld) ){
                        $data = array(
                                  'meta_value'  => $value
                                 ,'update_date' => date('Y-m-d H:i:s')
                            );
                        $model->where("ID=%s and meta_name='%s' and deleted=0",I('post.eid'),$key)->save($data);
                    }else{
                        $data = array(
                                     'ID'          => I('post.eid')
                                    ,'meta_name'   => $key
                                    ,'meta_value'  => $value
                                    ,'create_date' => date('Y-m-d H:i:s')
                                );
                        $model->add($data);
                    }
                }

                $list = $model->where($where)->select();
            	$results = self::getArrayForResults( 0,'添加成功',$list );
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
	 * 财务信息删除
	 * @version 2016年7月1日17:04:23
	 */
	public function metasDel(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('meta_id');
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
            	$metas_ep = M('metas_ep')->where( "meta_id='%s' and deleted=0",I('post.meta_id') )->find();
            	if( !empty($metas_ep) ){
            		$results = self::getArrayForResults( 1,'信息不存在' );break;
            	}


            	$is_ok = M('metas_ep')->where("meta_id='%s'",I('post.meta_id'))->save(array('deleted'=>1));
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'删除失败' );break;
            	}
                $where['ID']      = array('EQ',I('post.eid'));
                $where['deleted'] = array('EQ',0);
                $list    = M('metas_ep')->where($where)->find();
            	$results = self::getArrayForResults( 0,'删除成功',$list );
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
	 * 分场所新增
	 * @version 2016年7月1日17:04:23
	 */
	public function enterprisesSiteAdd(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid,es_type,es_name,es_addr');
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
            	$enterprises = M('enterprises')->where( "eid='%s' and deleted=%s",I('post.eid'),0 )->find();
                if( empty($enterprises) ){
                    $results = self::getArrayForResults( 1,'企业不存在' );break;
                }

            	$is_ok = M('enterprises_site')->add(I('post.'));
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'企业添加失败,请重新添加' );break;
            	}
                $enterprises_site = M('enterprises_site')->where( "es_id=",$is_ok )->find();
            	$results = self::getArrayForResults( 0,'添加成功',$enterprises_site );
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
	 * 分场所修改
	 * @version 2016年7月1日17:04:23
	 */
	public function enterprisesSiteEdit(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('es_id');
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
            	$enterprises_site = M('enterprises_site')->where( "es_id='%s' and deleted=0",I('post.es_id') )->find();
            	if( empty($enterprises_site) ){
            		$results = self::getArrayForResults( 1,'分厂所不存在' );break;
            	}

                $params = I('post.');unset($params['es_id']);
            	$is_ok = M('enterprises_site')->where("es_id='%s' and deleted=0",I('post.es_id'))->save($params);
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'分厂所修改失败,请重新添加' );break;
            	}
                $enterprises_site = M('enterprises_site')->where( "es_id=",I('post.es_id') )->find();
            	$results = self::getArrayForResults( 0,'修改成功',$enterprises_site );
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
	 * 合同新增
	 * @version 2016年7月1日17:04:23
	 */
	public function contractAdd(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid,ct_code,is_first,pre_date,zxfgznbms');
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
            	$enterprises = M('enterprises')->where( "eid='%s' and deleted=0",I('post.eid') )->find();
                if( empty($enterprises) ){
                    $results = self::getArrayForResults( 1,'企业不存在' );break;
                }

                $contract = M('contract')->where( "eid='%s' and ct_code='%s' and deleted=0",I('post.eid'),I('post.ct_code') )->find();
                if( !empty($contract) ){
                    $results = self::getArrayForResults( 1,'合同已存在' );break;
                }

            	$is_ok = M('contract')->add(I('post.'));
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'合同添加失败,请重新添加' );break;
            	}
                $list = M('contract')->where('ct_id='.$is_ok)->find();
            	$results = self::getArrayForResults( 0,'添加成功',$list );
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
	 * 合同修改
	 * @version 2016年7月1日17:04:23
	 */
	public function contractEdit(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('ct_id');
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
                $model    = M('contract');

            	$contract = $model->where( "ct_id=%s and deleted=0",I('post.ct_id') )->find();
            	if( empty($contract) ){
            		$results = self::getArrayForResults( 1,'合同不存在' );break;
            	}

                $params = I('post.');unset($params['ct_id']);
            	$is_ok = $model->where("ct_id=%s and deleted=0",I('post.ct_id'))->save( $params );
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'合同修改失败,请重新添加' );break;
            	}
                $list    = $model->where('ct_id='.I('post.ct_id'))->find();
            	$results = self::getArrayForResults( 0,'修改成功',$list );
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
	 * 体系新增
	 * @version 2016年7月1日17:04:23
	 */
	public function contractItemAdd(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('eid,ct_id,audit_ver,audit_type,cti_code,total,renum,is_turn,scope');
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
                $model    = M('contract_item');

            	$contract_item = $model->where( "eid=%s and ct_id=%s and audit_ver='%s'",I('post.eid'),I('post.ct_id'),I('post.audit_ver') )->find();
            	if( !empty($contract_item) ){
            		$results = self::getArrayForResults( 1,'版本已存在' );break;
            	}

                $params               = I('post.');
                $params['iso']        = substr(I('post.audit_ver'),0,3);
                $params['audit_type'] = '1001';
            	$is_ok = $model->add($params);
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'体系添加失败,请重新添加' );break;
            	}
                $list = $model->where('cti_id='.$is_ok)->find();
            	$results = self::getArrayForResults( 0,'添加成功',$list );
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
	 * 体系修改
	 * @version 2016年7月1日17:04:23
	 */
	public function contractItemEdit(){
		// $results = self::getArrayForResults( 1,'开发中。。。' );
		// exit(json_encode($results));

		/**---是否有数据没有提交---**/
		$unsetKey = self::getUnsetRequest('cti_id');
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
                $model         = M('contract_item');
            	$contract_item = $model ->where( "cti_id=%s and deleted=0",I('post.cti_id') )->find();
            	if( empty($contract_item) ){
            		$results = self::getArrayForResults( 1,'体系不存在' );break;
            	}

                $params = I('post.');unset($params['cti_id']);
            	$is_ok = $model->where("cti_id=%s and deleted=0",I('post.cti_id'))->add( $params );
            	if( !$is_ok ){
            		$results = self::getArrayForResults( 1,'体系修改失败,请重新添加' );break;
            	}
                $list    = $model->where('cti_id='.I('post.cti_id'))->find();
            	$results = self::getArrayForResults( 0,'修改成功',$list );
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