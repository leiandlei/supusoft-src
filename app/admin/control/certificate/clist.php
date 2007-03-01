<?php
/*
*证书变更
*/



	$mark_select=f_select('mark');
 	$certstate_select=f_select('certstate');
	$audit_ver_select = f_select('audit_ver');//体系版本
	$ctfrom_select = f_ctfrom_select();//合同来源
	$audit_type_select=f_select('audit_type','',array('1003','1004','1005','1006','1007'));
	$fields = $join = $where ='';
 	extract( $_GET, EXTR_SKIP );
	$certstatus = ($certstatus)?1:$certstatus;

	//企业名称
	$ep_name = trim($ep_name);
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}

		if( $_eids ){
			$where .= " AND cert.eid IN (".implode(',',$_eids).")";
		} else {
			$where .= " AND cert.id < -1";
		}
		unset( $_eids, $_query, $rt, $_eids );
	}

	//企业编号
	if( $ep_code=trim($ep_code) ){
		$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code' and deleted=0");
		if( $eid ){
			$where .= " AND cert.eid = '$eid'";
		} else {
			$where .= " AND cert.id < -1";
		}
	}

	//合同来源限制
	$len = get_ctfrom_level( current_user( 'ctfrom' ) );

	if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
		$_len = get_ctfrom_level( $ctfrom );
		$len = $_len;
	} else {
		$ctfrom = current_user( 'ctfrom' );
	}
	switch( $len ){
		case 2	: $add = 1000000; break;
		case 4	: $add = 10000; break;
		case 6	: $add = 100; break;
		case 8	: $add = 1; break;
	}
	$ctfrom_e = sprintf("%08d",$ctfrom+$add);
	$where .= " AND cert.ctfrom >= '$ctfrom' AND cert.ctfrom < '$ctfrom_e'";
	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
	unset( $len, $_len );


	if( $audit_type ){ //审核类型
		$where .= " AND cert.audit_type = '$audit_type'";
		$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>" , $audit_type_select );
	}

	if( $s_dates ){ //注册时间 起
		$where .= " AND cert.s_date >= '$s_dates'";
	}
	if( $s_datee ){ //注册时间 止
		$where .= " AND cert.s_date <= '$s_datee'";
	}

	if( $e_dates ){ //到期时间 起
		$where .= " AND cert.e_date >= '$e_dates'";
	}
	if( $e_datee ){ //到期时间 止
		$where .= " AND cert.e_date <= '$e_datee'";
	}

	if( $ct_code=trim($ct_code) ){ //合同编码
		$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code' and deleted=0");
		if( $ct_id ){
			$where .= " AND cert.ct_id = '$ct_id'";
		} else {
			$where .= " AND cert.id < -1";
		}
	}

	if( $cti_code=trim($cti_code) ){ //合同项目编码
		$cti_ids=array(-1);
	$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$cti_ids[]=$rt[cti_id];
		}
	$where .= " AND cert.cti_id in (".implode(",",$cti_ids).")";
	}

	//@wangp 直接在GET中取ISO
	$iso = $_GET['iso'];

	if( $iso ){ //认证体系
		$where .= " AND cert.iso = '$iso'";
		$iso_select = str_replace("value=\"$iso\">","value=\"$iso\" selected>",$iso_select);
	}

	//专业代码
	$audit_code = trim($audit_code);
	if( $audit_code ){
		$where .= " AND cert.audit_code LIKE '%$audit_code%'";
	}
	//认可标志
	if( $mark ){
		$where .= " AND cert.mark = '$mark'";
		$mark_select = str_replace("value=\"$mark\">","value=\"$mark\" selected>",$mark_select);
	}
	//标准版本
	if($audit_ver){
		$where.="AND cti.audit_ver='$audit_ver'";

	}
	if( $certno=trim($certno) ){
		$where .= " AND cert.certno = '$certno'";
	}

	if( !$certstate ){
		$where .= " AND cert.status > 0 and cert.is_check='y' ";
	} else {
		$where .= " AND cert.status = '$certstate'";
	}
	$certstate_select = str_replace("value=\"$certstate\">","value=\"$certstate\" selected>",$certstate_select);

	//@wangp 限制不显示 已删除的记录
	$where .= " AND cert.deleted = 0";

	$join .= " LEFT JOIN sp_enterprises e ON e.eid = cert.eid ";
	$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = cert.ct_id";
	$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = cert.cti_id";
	//$join .= " LEFT JOIN sp_assess a ON a.id = cert.pd_id";

	$where .= " AND cert.status != '0' and cert.status !='-1' ";
	//撤销的也不能出现
if( !$export ){
	$total = $db->get_var("SELECT COUNT(*) FROM sp_certificate  cert $join WHERE 1 $where");
	$pages = numfpage( $total, 20, $url_param );
}
	$sql = "SELECT cert.*,e.ep_name,ct.ct_code,cti.cti_code FROM sp_certificate cert $join WHERE 1 $where ORDER BY cert.id DESC $pages[limit]";

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['audit_ver'] = f_audit_ver($rt['audit_ver']);
		$rt['audit_type'] = f_audit_type($rt['audit_type']);
		$rt['mark'] = f_mark($rt['mark']);
		$rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
		$rt['status'] = f_certstate($rt['status']);
		$rt['stop_date'] = ('0000-00-00' == $rt['stop_date'])? '' : $rt['stop_date'];
		$res=$db->find_one("metas_ot"," AND ID='$rt[eid]' AND used='enterprise' AND meta_name='ep_phone'","meta_value");
	$rt['ep_phone']=$res['meta_value'];
	$res=$db->find_one("metas_ot"," AND ID='$rt[eid]' AND used='enterprise' AND meta_name='ep_fax'","meta_value");
	$rt['ep_fax']=$res['meta_value'];
	// 暂停时间
	$rt['time1'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_01' AND status=1 ORDER BY id DESC " );
	// 暂停到期
	$rt['time2'] = $db->get_var( "SELECT cge_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_01' AND status=1 ORDER BY id DESC " );
	// 撤销时间
	$rt['time3'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_03' AND status=1 ORDER BY id DESC " );
	// 恢复时间
	$rt['time4'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_02' AND status=1 ORDER BY id DESC " );
		$datas[] = $rt;
	}

	if( !$export ){
tpl('certificate/list');
} else {//应撤销证书列表
ob_start();
tpl( 'xls/list_certificate' );
$data = ob_get_contents();
ob_end_clean();
export_xls( '应撤销证书列表', $data );
}