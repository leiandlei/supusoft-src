<?php
/*
*证书变更查询
*/
$change_item_select=f_select('certchange');
$fields = $join = $where = '';
$export		= getgp( 'export' );
$ep_name	= getgp( 'ep_name' );
$ct_code	= getgp( 'ct_code' );
$cti_code	= getgp( 'cti_code' );
$ctfrom		= getgp( 'ctfrom' );
$change_item= getgp('change_item');
$certno		= getgp( 'certno' );
$change_date_start = getgp('change_date_start');
$change_date_end = getgp('change_date_end');
$pass_date_start = getgp('pass_date_start');
$pass_date_end = getgp('pass_date_end');
$status	= (int)getgp( 'status' );

!isset($status) && $status='0';
${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';

$ctfrom_select = f_ctfrom_select();

$ep_ids = $ct_ids = $cti_ids = array();
//企业名称
if( $ep_name ){
	$eids = array();
	$query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%$ep_name%'");
	while( $rt = $db->fetch_array( $query ) ){
		$eids[] = $rt['eid'];
	}
	if( $eids ){
		$query = $db->query("SELECT id FROM sp_certificate WHERE eid IN (".implode(',',$eids).")");
		while( $rt = $db->fetch_array( $query ) ){
			$ep_ids[] = $rt['id'];
		}
		if( $ep_ids ){
			$where .= " AND cc.zsid IN (".implode(',',$ep_ids).")";
		} else {
			$where .= " AND cc.id < -1";
		}
	} else {
		$where .= " AND cc.id < -1";
	}
}


//省份
if( $areacode=getgp("areacode") ){
	$pcode = substr($areacode,0,2) . '0000';
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$zs_ids=array(-1);
	$query = $db->query("SELECT id FROM sp_certificate WHERE eid IN (".implode(',',$_eids).")");
	while( $rt = $db->fetch_array( $query ) ){
		$zs_ids[] = $rt['id'];
	}

	$where .= " AND cc.zsid IN (".implode(',',$zs_ids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


//合同编号
if( $ct_code ){
	$zsid = $db->get_var("SELECT id FROM sp_certificate WHERE ct_code='$ct_code'");
	if( $zsid ){
		$where .= " AND cc.zsid = $zsid";
	} else {
		$where .= " AND cc.id < -1";
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
$where .= " AND c.ctfrom >= '$ctfrom' AND c.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

//项目编号
if( $cti_code ){
	$zs_ids=array(-1);
	$query = $db->query("SELECT zs.id FROM sp_contract_item cti left join sp_certificate zs on zs.cti_id=cti.cti_id WHERE cti.cti_code like '%$cti_code%' and cti.deleted=0 and zs.deleted=0");
	while($rt=$db->fetch_array($query)){
		$zs_ids[]=$rt[id];
		}
	$where .= " AND cc.zsid in (".implode(",",$zs_ids).")";
	
}

//证书编号
if( $certno ){
	$zsid = $db->get_var("SELECT id FROM sp_certificate WHERE certno = '$certno'");
	if( $zsid ){
		$where .= " AND cc.zsid = $zsid";
	} else {
		$where .= " AND cc.id < -1";
	}
}

if($change_item){
	$where .= " and cc.cg_type='$change_item'  ";
	$change_item_select = str_replace( "value=\"$change_item\">", "value=\"$change_item\" selected='selected'>" , $change_item_select );

}

//变更时间
if($change_date_start){
	$where .= " AND cc.cgs_date >= '$change_date_start' ";
}
if($change_date_end){
	$where .= " AND cc.cgs_date <= '$change_date_end' ";
}

//上报时间
if($pass_date_start){
	$where .= " AND cc.pass_date >= '$pass_date_start' ";
}
if($pass_date_end){
	$where .= " AND cc.pass_date <= '$pass_date_end' ";
}


$join .= " LEFT JOIN sp_certificate c ON c.id = cc.zsid ";
$join .= " LEFT JOIN sp_enterprises e ON e.eid = c.eid";

$where .= " and cc.deleted='0' ";
$status_count1=$db->get_var("SELECT COUNT(*) total FROM sp_certificate_change cc $join WHERE 1 $where and cc.status='1' ");
if( !$export ){
	$status_count = array(
	'0'=>$db->get_var("SELECT COUNT(*) total FROM sp_certificate_change cc $join WHERE 1 $where and cc.status='0' "),
	'1'=>$db->get_var("SELECT COUNT(*) total FROM sp_certificate_change cc $join WHERE 1 $where and cc.status='1' "),);
	$pages = numfpage( $status_count[$status] );
}
$where .= "  AND cc.status = '$status'  ";
$sql = "SELECT cc.*,c.iso,c.audit_ver,c.cti_id,c.ct_id,c.eid,c.certno,e.ep_name FROM sp_certificate_change cc $join WHERE 1 $where ORDER BY cc.id DESC $pages[limit]";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){

	$rt['cti_code'] = $db->get_var("select cti_code from sp_contract_item where cti_id='$rt[cti_id]' ");
	$rt['ctfrom'] = f_ctfrom( $rt['ctfrom'] );
	$rt['audit_ver'] = $audit_ver_array[$rt['audit_ver']]['msg'];
	$rt['eid'] = $db->get_var("select ep_name from sp_enterprises where eid='$rt[eid]' ");
	$rt['cg_type_V'] = $changeitem_array[$rt['cg_type']]['name'];
	$rt['note'] = $rt['note'];
	$rt['cg_xj']="变更前：".read_cache("certstate",$rt['cg_af']).$rt['cg_af'];
	$rt['cg_xj'].="<br/>变更后：".read_cache("certstate",$rt['cg_bf']).$rt['cg_bf'];
	if($rt['cg_type']=="97_01")
		$rt[action]="c_zt";
	elseif($rt['cg_type']=="97_03"){
		$rt[action]="c_cx";
	}
	elseif($rt['cg_type']=="97_02"){
		$rt[action]="c_hf";
	}
	else
		$rt[action]="";
	$datas[$rt[id]] = chk_arr($rt);

}


if( !$export ){
		tpl();
	} else {//导出证书变更列表
		ob_start();
		tpl( 'xls/list_change_list' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '证书变更查询', $data );
	}