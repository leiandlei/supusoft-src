<?php
	$zsid = getgp('zsid');
	if(empty($zsid)){
		echo '缺少参数';exit;
	}
	$sql = "select * from `sp_certificate` ce left join `sp_enterprises` en on en.eid=ce.eid where 1 and ce.deleted=0 and en.deleted=0 and ce.id=".$zsid;
	$results = $db->getOne($sql);
	$ct_id = $results['ct_id'];
	$cti_id = $results['cti_id'];
	extract($results,EXTR_OVERWRITE);
	
	//证书信息
    switch ($status) {
		case '01':
			$str_status = '有效';
			break;
		case '02':
			$str_status = '暂停';
			break;
		case '03':
			$str_status = '撤销';
			break;
        case '04':
			$str_status = '过期失效';
			break;
		default:
			break;
	}
	switch ($audit_ver) {
		case 'A010101':
			$str_audit_ver = 'GB/T 19001-2008/ISO9001:2008';
            $name_audit_ver='质量管理体系';
			break;
		case 'A010102':
		case 'A010103':
			$str_audit_ver = 'GB/T 19001-2016/ISO9001:2015';
        $name_audit_ver='质量管理体系';
			break;
		case 'A011001':
			$str_audit_ver = 'IECQ-HSPM QC080000:2012';
            $name_audit_ver='QC080000';
			break;
		case 'A020101':
			$str_audit_ver = 'GB/T 24001-2004/ISO14001:2004';
            $name_audit_ver='环境管理体系';
			break;
		case 'A020102':
			$str_audit_ver = 'GB/T 24001-2016/ISO14001:2015';
            $name_audit_ver='环境管理体系';
			break;
		case 'A030102':
			$str_audit_ver = 'GB/T 28001-2011';
            $name_audit_ver='职业健康安全管理体系';
			break;
        case 'A010202':
            $str_audit_ver = 'GB/T 19001-2016/ISO9001:2015;GB/T 50430-2007';
            $name_audit_ver='建设施工行业质量管理体系认证';
            break;
		default:
			break;
	}

	switch ($mark) {
		case '01':
			$str_mark = 'CNAS';
			break;
		case '02':
			$str_mark = 'UKAS';
			break;
		case '99':
			$str_mark = '其他';
			break;
		default:
			break;
	}

	if(strlen($work_code)==9){
	$startstr = substr($work_code, 0, 8);
	$endstr   = substr($work_code, 8, 1);
	$work_code_1=$startstr.'-'.$endstr;
	}else{
		$work_code_1=$work_code;
	}

	if (strlen($work_code)==9) {
		$arr_work_code="组织机构代码";
	}else{
		$arr_work_code="统一社会信用代码";
	}
	
	//监督、再认证次数
	$count_zairenzheng = $db->get_var("SELECT COUNT(*) FROM sp_project WHERE `eid`='$eid' AND `audit_type`='1007' AND `deleted`='0'");
	// $count_jiandu      = $db->get_var("SELECT COUNT(DISTINCT audit_type) AS a FROM sp_project WHERE `ct_id`='$ct_id' AND `audit_type`='1004' OR `audit_type`='1005' and pd_type='1' AND `deleted`='0'");
	$count_jiandu      = $db->get_var("SELECT COUNT(DISTINCT sp_type) AS a FROM sp_project WHERE ct_id='$ct_id' AND cti_id='$cti_id' AND (audit_type='1004' OR audit_type='1005') AND (sp_type='2' OR sp_type='1') AND deleted='0'");
	

	//多场所
	$site_count = $db->get_var("select site_count from sp_enterprises where eid='$eid' AND `deleted`='0'");

	if($site_count=="0") {
		$multi_site = "否";
	}else{
		$multi_site = "是";
	}
	
	
	$lishi = array();
	//porject
	$sql = "select * from sp_project WHERE `eid`='".$eid."' and cti_id='".$cti_id."' and audit_type in('1003','1004','1005') and deleted=0";
	$project = $db->getAll($sql);
	if(!empty($project))
	{
		require_once(DATA_DIR.'cache/audit_type.cache.php');

		foreach($project as $item)
		{
			if(empty($audit_type_array[$item['audit_type']]['name']))continue;
			if($item['audit_type']!='1003')continue;
			$lishi[$item['create_date']]['type'] = $item['audit_type']=='1003'?'初次审核':$audit_type_array[$item['audit_type']]['name'];
			
//			$sql = "select * from sp_certificate_change WHERE `zsid`='".$zsid."' and deleted=0 ORDER BY id desc";
//			$change = $db->getAll($sql);
//			$lishi[$item['create_date']]['date']  = $change[0]['cgs_date'];
		}
	}

//	
	//porject
	$sql = "select * from sp_certificate_change WHERE `zsid`='".$zsid."' and deleted=0 ORDER BY id desc";
	$change = $db->getAll($sql);

	
	if(!empty($project))
	{
		require_once(DATA_DIR.'cache/certchange.cache.php');
		
		foreach($change as $item)
		{
			$lishi[$item['create_date']]['date']    = $change[0]['cgs_date'];
			if(empty($certchange_array[$item['cg_type']]['name']))continue;
		
			if($item['audit_type']=='1004'||$item['audit_type']=='1005'){
				$lishi[$item['create_date']]['type']    = '监督审核';
			}
//			$lishi[$item['create_date']]['date']    = $item['cgs_date'];
			$lishi[$item['create_date']]['content'] = $certchange_array[$item['cg_type']]['name'];
			switch($item['cg_type'])
			{
				case '104'://认可标识转换
					require_once(DATA_DIR.'cache/mark.cache.php');//认可标识
					$lishi[$item['create_date']]['content'] = $lishi[$item['create_date']]['content'].'('.$mark_array[$item['cg_af']]['name'].'变更为'.$mark_array[$item['cg_bf']]['name'].')';
					break;
				case '108'://审核类型转换
					require_once(DATA_DIR.'cache/audit_ver.cache.php');//审核类型
					$lishi[$item['create_date']]['content'] = $lishi[$item['create_date']]['content'].'('.$audit_ver_array[$item['cg_af']]['msg'].'变更为'.$audit_ver_array[$item['cg_bf']]['msg'].')';
					break;
				case '97_01'://证书变更
				case '97_02'://证书变更
				case '97_03'://证书变更
				case '97_05'://证书变更
					$lishi[$item['create_date']]['content'] = $lishi[$item['create_date']]['content'].'('.$certchange_array['97_'.$item['cg_af']]['name'].'变更为'.$certchange_array['97_'.$item['cg_bf']]['name'].')';
					break;
				default:
					$lishi[$item['create_date']]['content'] = $lishi[$item['create_date']]['content'].'('.$item['cg_af'].'变更为'.$item['cg_bf'].')';
					break;
			}
			
			
		}
	}
	$cgs_date = $db->get_var("select cgs_date from sp_certificate_change WHERE `zsid`='".$zsid."' and deleted=0 ORDER BY id desc");

	rsort($lishi);
	tpl('output/certificateInfo');
?>
