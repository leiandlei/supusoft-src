<?php
//证书登记列表
$fields = $join = $where = '';
	 
	extract($_GET);
	$iso_select=f_select('iso');
  	//合同来源
	$ctfrom_select = f_ctfrom_select();
	if($ctfrom){
		$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
	}
	if( $audit_type_array ){
		foreach( $audit_type_array as $code => $item ){
			$audit_type_select .= "<option value=\"$code\">$item[name]</option>";
		}
		if($audit_type){
			$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>" , $audit_type_select );
		}
	}
	if( $audit_ver_array ){
		foreach( $audit_ver_array as $code => $item ){
			if($item['is_stop']==0) {
				$audit_ver_select .= "<option value=\"$code\">$item[msg]</option>";
			}
		}
		if($iso){
			$audit_ver_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $audit_ver_select );
		}
	}
	!$is_check && $is_check='1';
	$status_n_tab = $status_e_tab = '';
	${'status_'.$is_check.'_tab'} = ' ui-tabs-active ui-state-active';
	
$ep_name = trim($ep_name);
if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',trim($ep_name))."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}

	if( $_eids ){
		$where .= " AND p.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND p.id < -1";
	}
	unset( $_eids, $_query, $rt, $_eids );
}
//省份
if( $areacode=getgp("areacode") ){
	$pcode = substr($areacode,0,2) . '0000';
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where .= " AND p.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


//企业编号
if( $ep_code =trim($ep_code)){
	$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
	if( $eid ){
		$where .= " AND p.eid = '$eid'";
	} else {
		$where .= " AND p.id < -1";
	}
}	//合同编号
if( $ct_code=trim($ct_code) ){ //合同编码
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code' and deleted=0");
	if( $ct_id ){
		$where .= " AND p.ct_id = '$ct_id'";
	} else {
		$where .= " AND p.id < -1";
	}
}

if( $cti_code=trim($cti_code) ){ //合同项目编码
	$cti_ids=array(-1);
	$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$cti_ids[]=$rt[cti_id];
		}
	$where .= " AND p.cti_id in (".implode(",",$cti_ids).")";
}
	if($sp_date_s){
		$where .= " and p.sp_date >= '$sp_date_s' ";
	}
	if($sp_date_e){
		$where .= " and p.sp_date <= '$sp_date_e' ";
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
	$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";


	if($audit_type){
		$where .= " and p.audit_type = '$audit_type' ";
	}
	if($audit_ver){
		$where .= " and p.audit_ver = '$audit_ver' ";
	}
	if($iso){
		$where .= " and p.iso = '$iso' ";
	}
	$join .= " left JOIN sp_enterprises e ON e.eid = p.eid ";
	//$join .= " left JOIN sp_project p ON p.id = p.pid";
	//@HBJ 2013-09-12
	//显示未删除
	$where .= " and p.deleted = 0 ";

	$where .= " AND p.sp_type = '1'";

	$ne_total = array(
	'1' => $db->get_var("SELECT COUNT(*) total FROM sp_project p $join WHERE 1 $where AND p.ifchangecert = '1' and p.audit_type ='1003' "),
	'2' => $db->get_var("SELECT COUNT(*) total FROM sp_project p $join WHERE 1 $where AND p.ifchangecert = '2' and p.audit_type ='1003' ")
	);
	//驳回
	$is_check3 =2;
	$join_is_check3 = " left join sp_certificate c on p.cti_id = c.cti_id ";
	$where_is_check3= " and c.is_check='e' ";
	$ne_total[3] = $db->get_var("SELECT COUNT(*) total FROM sp_project p $join $join_is_check3 WHERE 1 $where $where_is_check3 AND p.ifchangecert = '2' ");
	
	if( $is_check==3 ){
		$is_check =2;
		$join .= " left join sp_certificate c on p.cti_id = c.cti_id ";
		$where.= " and c.is_check='e' ";
	}



	
	$pages = numfpage( $ne_total[$is_check] );
	$where .= "  and p.ifchangecert ='$is_check'";
	
	$sql = "SELECT p.*,e.ep_name FROM sp_project p $join WHERE 1 $where and p.audit_type !='1002' AND p.pd_type = '1'  ORDER BY p.id DESC $pages[limit]";
// 	echo "<pre />";
// print_r($sql);exit;
	$query = $db->query( $sql );
	
	while( $rt = $db->fetch_array( $query ) ){
		$rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
		$rt['audit_ver'] = f_audit_ver($rt['audit_ver']);
		$rt['audit_type'] = f_audit_type($rt['audit_type']);
		$rt['status'] = f_certstate($rt['status']);
		$datas[] = $rt;
	}

	if($export) {
		ob_start();
		tpl( 'xls/list_certificate' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '证书列表', $data );exit;
	}
	tpl();