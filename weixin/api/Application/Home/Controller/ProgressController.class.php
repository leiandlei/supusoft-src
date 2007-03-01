<?php
namespace Home\Controller;
use Think\Controller;
class ProgressController extends ApiController {
	/**
	 * 审核进度
	 */
	public function shenhejinduList(){
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
        $p_states  = array('未安排','待派人','待审批','已审批','','维护','维护（退）');
		$ct_states = array('未登记完','待评审','待审批','已审批');
		$iso_array = array(
						'C02' => array(
							'code' => 'C02',
							'name' => '一般服务认证',
							'type' => 'iso',
							'is_stop' => '0',
						),
						'D01' => array(
							'code' => 'D01',
							'name' => '品牌认证',
							'type' => 'iso',
							'is_stop' => '1',
						),
						'A01' => array(
							'code' => 'A01',
							'name' => 'QMS',
							'type' => 'iso',
							'is_stop' => '0',
						),
						'A02' => array(
							'code' => 'A02',
							'name' => 'EMS',
							'type' => 'iso',
							'is_stop' => '0',
						),
						'A04' => array(
							'code' => 'A04',
							'name' => 'FSMS',
							'type' => 'iso',
							'is_stop' => '1',
						),
						'A03' => array(
							'code' => 'A03',
							'name' => 'OHSMS',
							'type' => 'iso',
							'is_stop' => '0',
						),
						'C01' => array(
							'code' => 'C01',
							'name' => 'ESP体育场所服务认证',
							'type' => 'iso',
							'is_stop' => '1',
						),
						'A10' => array(
							'code' => 'A10',
							'name' => '知识产权管理体系',
							'type' => 'iso',
							'is_stop' => '1',
						),
						'OTHER' => array(
							'code' => 'OTHER',
							'name' => '其它',
							'type' => 'iso',
							'is_stop' => '1',
						),
					);
		$audit_type_array = array(
						'1001' => array(
							'code' => '1001',
							'name' => '初审',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1002' => array(
							'code' => '1002',
							'name' => '一阶段',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1003' => array(
							'code' => '1003',
							'name' => '二阶段',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1004' => array(
							'code' => '1004',
							'name' => '监一',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1005' => array(
							'code' => '1005',
							'name' => '监二',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1007' => array(
							'code' => '1007',
							'name' => '再认证',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1006' => array(
							'code' => '1006',
							'name' => '监三',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1008' => array(
							'code' => '1008',
							'name' => '专项审核',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1009' => array(
							'code' => '1009',
							'name' => '特殊监督',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'1101' => array(
							'code' => '1101',
							'name' => '变更',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
						'99' => array(
							'code' => '99',
							'name' => '其他',
							'type' => 'audit_type',
							'is_stop' => '0',
						),
					);
		$certstate_array  = array(
								'01' => array(
									'code' => '01',
									'name' => '有效',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'02' => array(
									'code' => '02',
									'name' => '暂停',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'03' => array(
									'code' => '03',
									'name' => '撤销',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'05' => array(
									'code' => '05',
									'name' => '过期失效',
									'type' => 'certstate',
									'is_stop' => '0',
								),
							);
		$certstate_array  = array(
								'01' => array(
									'code' => '01',
									'name' => '有效',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'02' => array(
									'code' => '02',
									'name' => '暂停',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'03' => array(
									'code' => '03',
									'name' => '撤销',
									'type' => 'certstate',
									'is_stop' => '0',
								),
								'05' => array(
									'code' => '05',
									'name' => '过期失效',
									'type' => 'certstate',
									'is_stop' => '0',
								),
							);

        $join = $where = array();$order = $field='';

        $model  = M('project p');
        $field .= 'p.id,p.iso,p.audit_type,p.final_date,p.redata_date,p.status AS state,p.comment_date,p.comment_pass_date,p.sp_date,p.eid,p.cti_id';
        $where['p.deleted'] = array('EQ',0);

        //关联sp_contract表
        $join   = array_merge($join,array('LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id'));
        $field .= ',ct.create_date AS ct_create_date,ct.review_date AS ct_review_date,ct.approval_date AS ct_approval_date,ct.status AS ct_state';
        $where['ct.deleted'] = array('EQ',0);

        //关联sp_contract_item表
        $join   = array_merge($join,array('LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id'));
        $field .= ',cti.cti_code';
        $where['cti.deleted'] = array('EQ',0);

        //关联sp_task表
        $join   = array_merge($join,array('LEFT JOIN sp_task t ON t.id = p.tid'));
        $field .= ',t.id as tid,t.tb_date,t.te_date,t.save_date,t.rect_date,t.bufuhe';
        $where['t.deleted'] = array('EQ',0);

        //关联sp_enterprises表 如果是客户应该是 sp_enterprises.eid=unlogin.userid 如果是合作方应该是ctfrom
        $join   = array_merge($join,array('LEFT JOIN sp_enterprises e ON e.eid = p.eid'));
        $field .= ',e.ep_name';
        $where['e.deleted'] = array('EQ',0);
        switch ( $unloginModel['genre'] ){//获取用户类型
        	case '1'://机构内
        		//关联sp_task_audit_team表
		        $join   = array_merge($join,array('LEFT JOIN sp_task_audit_team tat ON tat.tid = t.id and tat.pid=p.id'));
		        $where['tat.uid']     = array('EQ',$unloginModel['userid']);
		        $where['tat.deleted'] = array('EQ',0);
        		break;
            case '2'://合作方
            	$where['e.ctfrom'] = array('EQ',$unloginModel['userid']);
            	break;
            case '3'://客户
            	$where['e.eid'] = array('EQ',$unloginModel['userid']);
            	break;
            default:break;
        }
        

        $order = 't.te_date desc';

        $seach = self::getSeach();
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

        $list    = $model->join($join)->where($where)->order($order)->field($field)->select();
    	$data = array();
    	foreach ($list as $value) {
			$Model     = new \Think\Model();

			if(!empty($value['tid'])&&!empty($value['iso'])){
				$zuzhang   = $Model->query("select name from `sp_task_audit_team` where tid=".$value['tid']." and iso='".$value['iso']."' and role='01' and deleted=0");
				if($zuzhang){
					$value['zuzhang'] = $zuzhang[0]['name'];
				}
			}
			
			if( !empty($value['cti_id'])&&!empty($value['eid']) ){
				$cert_info = $Model->query("SELECT id as zsid,certno,s_date as cert_start,e_date as cert_end,status cert_state FROM `sp_certificate` WHERE `cti_id` = ".$value['cti_id']." AND `eid` = ".$value['eid']." AND `deleted` = '0' ORDER BY `e_date` DESC");
	    		if($cert_info){
					$value=array_merge($value,$cert_info[0]);
					$value['cert_state'] = $certstate_array[$value['cert_state']]['name'];

					$send_date = $Model->query("SELECT sms_date FROM `sp_sms` WHERE `temp_id` = ".$value['zsid']." AND `deleted` = '0' and flag=1");
					if($send_date){
						$value['send_date'] = $send_date[0]['sms_date'];
					}
				}
			}

			$value['iso']        = $iso_array[$value['iso']]['name'];
			$value['audit_type'] = $audit_type_array[$value['audit_type']]['name'];
			$value['state']      = $p_states[$value['state']];
			$value['ct_state']   = $ct_states[$value['ct_state']];
			$value['bufuhe']?$value['bufuhe']="是":$value['bufuhe']="否";

			$data[]=$value;
    	}

    	$page    = self::getPageInfo(count($data));//获取分页
        $data    = array_slice($data,$page['limit']['start'],$page['size']);
    	$results = self::getArrayForResults( 0,'',$data,$page );
        /**---程序开始---**/
        exit(json_encode($results));
	}
}

?>
