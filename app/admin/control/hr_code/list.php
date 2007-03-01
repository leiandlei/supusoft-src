<?php
require_once('data/cache/ctfrom.cache.php');

/*
*人员业务代码查询
*/
// $shuruname =getgp('name');
// if (empty($shuruname)){

$code = '';
$fields = $join = $where = $hr_where = $str_r = '';
$url_param = '?';
$qualification_select=f_select('qualification');

extract( $_GET, EXTR_SKIP );

foreach( $_GET as $k => $v ){
	if( 'paged' == $k ) continue;
	$url_param .= "$k=$v&";
}
$url_param = substr( $url_param, 0, -1 );
$name      = trim($name);
$easycode  = trim($easycode);
$h_code    = trim($h_code);
$qua_no    = trim($qua_no);
$banben    = trim($banben);
// if( $name ){
// 	$uids = array();
// 	$query = $db->query( "SELECT id FROM sp_hr WHERE 1 AND name like '%$name%'");
// 	while( $rt = $db->fetch_array( $query ) ){
// 		$uids[] = $rt['id'];
// 	}
// 	if( $uids ){
// 		$where .= " AND hac.uid IN (".implode(',',$uids).")";
// 	} else {
// 		$where .= " AND hac.id < -1";
// 	}
// }
// echo "<pre />";
// print_r($uids);exit;
if( $easycode ){
	$uids = array();
	$query = $db->query("SELECT id FROM sp_hr WHERE 1 AND easycode like '%$easycode%'");
	while( $rt = $db->fetch_array( $query ) ){
		$uids[] = $rt['id'];
	}
	if( $uids ){
		$where .= " AND hac.uid IN (".implode(',',$uids).")";
	} else {
		$where .= " AND hac.id < -1";
	}
}
$qualification=getgp('qualification');
if($qualification){
	$where .= " AND hq.qua_type = '$qualification' ";
	$qualification_select = str_replace( "value=\"$qualification\">", "value=\"$qualification\" selected>" , $qualification_select );
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
if( '01000000' != $ctfrom ){
	$where .= " AND hac.ctfrom >= '$ctfrom' AND hac.ctfrom < '$ctfrom_e'";
}
$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

if($iso){
	$where .= " AND hac.iso = '$iso' ";
	$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
}
if($audit_code && !empty($banben))
{
	$codeid =$db->getAll("select * from sp_settings_audit_code where shangbao like '$audit_code%' and deleted=0 and is_stop=0 ");
	foreach($codeid as $code)
	{
		$codes[]   = $code['id'];
	}
	$audit_codeids = implode(',', $codes);
//	echo $audit_codeids;exit;
	if(!empty($audit_codeids))
	{
		if($banben=='1')
		{
			$where .= " AND hac.audit_code_2017 in ($audit_codeids)";
		}elseif($banben=='2')
		{
			$where .= " AND hac.audit_code in ($audit_codeids) ";
		}
	}else{
        $where .="AND 0";
    }
}
//只搜索代码
if(!empty($audit_code) && empty($banben) )
{
	$codeid =$db->getAll("select * from sp_settings_audit_code where shangbao like '$audit_code%' and deleted=0 and is_stop=0 ");
	foreach($codeid as $code)
	{
		$codes[]   = $code['id'];
	}
	$audit_codeids = implode(',', $codes);
	if(!empty($audit_codeids))
	{
		$where .= " AND (hac.audit_code_2017 in ($audit_codeids) or hac.audit_code in ($audit_codeids))";
	}else{
        $where .="AND 0";
    }

}



if($use_code && !empty($banben))
{
	if($banben=='1')
	{
		$where .= " AND hac.use_code_2017 like '$use_code%'";
	}elseif($banben=='2')
	{
		$where .= " AND hac.use_code like '$use_code%'";
		
	}
}
if(!empty($banben))
{
	if($banben=='1')
	{
		$hrcodeList  = $db->getAll("Select * from sp_settings_audit_code where banben=1 and is_stop=0");
		foreach($hrcodeList as $hrcode)
		{
			$hrcodeid[] = $hrcode['id'];
		}

		$hrcodeid   = implode(',',array_filter(array_unique($hrcodeid))); 
	
		$where .= " AND hac.audit_code_2017 in ($hrcodeid) ";
		
	}elseif($banben=='2')
	{
		$hrcodeList  = $db->getAll("Select * from sp_settings_audit_code where banben=2 and is_stop=0");
		foreach($hrcodeList as $hrcode)
		{
			$hrcodeid[] = $hrcode['id'];
		}
		$hrcodeid   = implode(',',array_filter(array_unique($hrcodeid))); 
		$where .= " AND hac.audit_code in ($hrcodeid)"; 
		
	}
}

if($pass_date_s){
	$where .= " AND hac.pass_date >= '$pass_date_s' ";
}
if($pass_date_e){
	$where .= " AND hac.pass_date <= '$pass_date_e' ";
}

if(!empty($name)){
	$where .= " AND hr.name like '$name%' ";
	// $where .= " AND hac.uid = '$uids%' ";
}

//人员在职 且 注册资格有效
$is_hire=getgp('is_hire');

if($is_hire){ //搜索用
	 $where.=" AND hr.is_hire=$is_hire";
	$f_is_hire = str_replace( "value=$is_hire>", "value=$is_hire selected>" , $f_is_hire );
}else{
	$where.=" AND (hr.is_hire = 1 or hr.is_hire = 3)";
}
$where .= "  AND hr.deleted = 0  AND hac.deleted = 0";

$join .= " LEFT JOIN sp_hr_qualification hq ON hq.id = hac.qua_id";
$join .= " LEFT JOIN sp_hr hr ON hr.id = hac.uid";

$sql = "SELECT COUNT(*) FROM sp_hr_audit_code hac $join WHERE 1 $where";
//echo $sql;exit;
if( !$export){
	$total = $db->get_var($sql);
	$pages = numfpage( $total, 20, $url_param );
}

$hacs = array();
$sql = "SELECT hac.*,hr.code,hr.name,hr.ctfrom,hr.audit_job,hr.is_hire FROM sp_hr_audit_code hac $join WHERE 1 $where order by hac.use_code asc $pages[limit]";

$query = $db->query($sql);
$data_codes=array();
while( $rt = $db->fetch_array( $query ) ){
	$rt['qua_type_V'] = f_qua_type( $rt['qua_type'] );
	$rt['iso_V'] = f_iso( $rt['iso'] );
	$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
	$rt['source'] = f_source( $rt['source'] );
	$rt['audit_job'] = f_audit_job( $rt['audit_job'] );
	$rt['status_V'] = $status_array[$rt['status']];
	$rt['is_assess_V'] = ($rt['is_assess'])?'是':'否';
	$rt['is_hire_V']=$hr_is_hire[$rt['is_hire']];
	$fenzu=$db->get_var("SELECT fenzu FROM `sp_settings_audit_code` WHERE `iso` = '$rt[iso]' and shangbao='$rt[audit_code]' and deleted=0 and is_stop=0 ");
	switch ($fenzu) {
		case '1':
			$rt['fenzu']="E00-1";
			break;
		case '2':
			$rt['fenzu']="E00-2";
			break;
		case '3':
			$rt['fenzu']="E00-3";
			break;
		case '4':
			$rt['fenzu']="S00-1";
			break;
		case '5':
			$rt['fenzu']="S00-2";
			break;
		case '6':
			$rt['fenzu']="S00-3";
			break;
		case '7':
			$rt['fenzu']="S00-4";
			break;
		default:
			break;
	}
	// if($export){
	// 	$temp=$db->get_col("SELECT shangbao FROM `sp_settings_audit_code` WHERE `iso` = '$rt[iso]' and code='$rt[use_code]'");
	// 	foreach($temp as $k=>$v){
	// 		$temp[$k]=substr($v,0,8);
			
	// 	}
	// 	$rt[audit_code]=join(";",$temp);
	// }
//	echo "<pre />";
//	print_r($rt);exit;
	if(is_numeric($rt['audit_code'] ))
	{
		if(!empty($rt['audit_code']))$rt['audit_code']     = $db->get_var("select shangbao from sp_settings_audit_code where id=".$rt['audit_code']);
	}
	if(is_numeric($rt['audit_code_2017'] ))
	{
		if(!empty($rt['audit_code_2017']))$rt['audit_code_2017'] = $db->get_var("select shangbao from sp_settings_audit_code where id=".$rt['audit_code_2017']);	
	}else{
		$rt['audit_code_2017']='';
	}
	$hacs[$rt['id']] = chk_arr($rt);
}
if( !$export ){
	tpl('hr/hr_code_list');
} else {
	ob_start();
	tpl( 'xls/list_hr_code' );
	$data = ob_get_contents();
	ob_end_clean();
	export_xls( '人员专业代码列表', $data );
}
?>