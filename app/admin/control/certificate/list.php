<?php
/*
*证书查询列表
*/
$audit_type       = getgp('audit_type');

$audit_ver        = getgp('audit_ver');
$mark_select      = f_select('mark');
$iso_select       = f_select('iso'); //认证体系
$audit_ver_select = f_select('audit_ver');//体系版本
$audit_type_select= f_select('audit_type','',array('1003','1004','1005','1006','1007'));
$certstate_select =f_select('certstate');
$guimo_select='<option value="S">--S--</option>
            <option value="M">--M--</option>
            <option value="L">--L--</option>';
$chushen_select='<option value="1">初审</option>
            <option value="2">复评</option>';
$iso_arr=array("A01"=>'质量管理体系（ISO9001）',"A02"=>'环境管理体系（ISO14001）',"A03"=>'职业健康安全管理体系（OHSAS18001）');
$fields = $join = $where = '';
extract( $_GET, EXTR_SKIP );
$iso = $_GET['iso'];
$certstatus = ($certstatus)?1:$certstatus;
//合同来源
$ctfrom_select = f_ctfrom_select();

//企业名称
$ep_name = trim($ep_name);
if( $ep_name ){
	$where .=" AND cert.cert_name like '%$ep_name%'";
}
//省份
if( $areacode=getgp("areacode") ){
	$pcode = substr($areacode,0,2) . '0000';
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where .= " AND cert.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


//企业编号
if( $ep_code =trim($ep_code)){
	$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
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



if( $s_dates ){ //注册时间 起
	$where .= " AND cert.s_date >= '$s_dates'";
}
/*
if( $s_dates ){ //注册时间 止
	$where .= " AND cert.s_date <= '$s_datee'";
}
*/
//@wangp 证书到期时间是 s_datee 不是 s_dates
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

if( $iso ){ //认证体系
	$where .= " AND cert.iso = '$iso'";
	if($item['is_stop']==0) {
		$iso_select = str_replace("value=\"$iso\">","value=\"$iso\" selected>",$iso_select);
	}
}

$audit_ver_select = str_replace( "value=\"A010103\">", "value=\"A010102,A010103\">", $audit_ver_select);
if($audit_ver) { //标准版本
	$_audit_ver = explode(',', $audit_ver);
	if(count($_audit_ver)>1)
	{
		$or = array();
		foreach($_audit_ver as $item)
		{
			$or[] = "cert.audit_ver ='".$item."'";
		}
		$where .= " AND (".implode(' or ', $or).")";
	}else{
		$where .= " AND cert.audit_ver = '$audit_ver'";
	}

	$audit_ver_select=str_replace("value=\"$audit_ver\">","value=\"$audit_ver\" selected>",$audit_ver_select);
}
$scope = trim($scope);
if( $scope ){
	$where .= " AND cert_scope LIKE '%$scope%'";
}

//专业代码
$audit_code = trim($audit_code);

if( $audit_code ){
	$where .= " AND cti.audit_code LIKE '%$audit_code%'";
}
//大类搜索
$audit_code_max = trim($audit_code_max);
  
if( $audit_code_max ){
    $join  .= " LEFT JOIN sp_project p ON p.ct_id = cert.ct_id and p.iso = cert.iso";
	$where .= " AND p.deleted=0 AND p.audit_type=1003" ;
    $where .= " AND cti.audit_code LIKE '%$audit_code_max".".%'"." AND cti.audit_code NOT LIKE "."'%."."$audit_code_max".".%'"." AND p.deleted=0 AND p.audit_type=1003" ;
}

//认可标志
if( $mark ){
	$where .= " AND cert.mark = '$mark'";
	$mark_select = str_replace("value=\"$mark\">","value=\"$mark\" selected>",$mark_select);
}

if($s_datee)
{
	
	if(!empty($mark))
	{
		$bgbszhengshu  = $db->getAll("select * from sp_certificate_change where cg_type='104' and cg_bf ='$mark' and cgs_date > '$s_datee' and status =1");
	}else{
		$bgbszhengshu  = $db->getAll("select * from sp_certificate_change where cg_type='104' and cgs_date > '$s_datee' and status =1");
		
	}
	foreach($bgbszhengshu as $zhengshuid)
	{
		$zhengshuids[] = $zhengshuid['zsid'];
	}
	$zhengshuids       =  implode(',', $zhengshuids);

	if(!empty($zhengshuids))$where .=" AND cert.id not in (".$zhengshuids.")";
}



if( $certno=trim($certno) ){
	$where .= " AND cert.certno = '$certno'";
}

if( $certstate ){
	$where .= " AND cert.status = '$certstate'";
}
if( $guimo){
	$guimo_select=str_replace("value=\"$guimo\"","value=\"$guimo\" selected",$guimo_select);
	$where .= " AND RIGHT(cert.certno,1)='$guimo'";

}
if( $chushen){
	$chushen_select=str_replace("value=\"$chushen\"","value=\"$chushen\" selected",$chushen_select);
	if($chushen=='1')
		$where .= " AND LEFT(RIGHT(cert.certno,3),1)='0'";
	else
		$where .= " AND LEFT(RIGHT(cert.certno,3),1)>0";

}

//@wangp 加条件不显示 deleted = 1 的记录
$where .= " AND cert.deleted = 0";
$where .= " AND cert.status <> '' and cert.is_check='y' ";
$certstate_select = str_replace("value=\"$certstate\">","value=\"$certstate\" selected>",$certstate_select);

$join .= " LEFT JOIN sp_enterprises  e ON e.eid = cert.eid ";
// $join .= " LEFT JOIN sp_contract ct ON ct.ct_id = cert.ct_id";
$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = cert.cti_id";
  


$sql = "SELECT cert.*  FROM sp_certificate cert $join  WHERE 1 $where ORDER BY cert.id DESC ";

$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){

	$rt['audit_ver'] = f_audit_ver($rt['audit_ver']);
	$rt['mark'] = f_mark($rt['mark']);
	$rt['status'] = f_certstate($rt['status']);
	$rt['ctfrom'] = f_ctfrom( $rt['ctfrom'] );
	// if($audit_type){
	$rt['type'] = $db->get_var( "SELECT audit_type FROM sp_project WHERE cti_id = '".$rt['cti_id']."' AND ct_id = '".$rt['ct_id']."' and sp_type=1 and deleted=0 order by audit_type desc" );

    if(!empty($audit_type)&&$rt['type']!=$audit_type)
    {
		continue;
    }
// }
	// 暂停时间
	$rt['time1'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_01' AND status=1 and deleted=0 ORDER BY id DESC " );
	// 暂停到期
	$rt['time2'] = $db->get_var( "SELECT cge_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_01' AND status=1 and deleted=0 ORDER BY id DESC " );
	// 撤销时间
	$rt['time3'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_03' AND status=1 and deleted=0 ORDER BY id DESC " );
	// 恢复时间
	$rt['time4'] = $db->get_var( "SELECT cgs_date FROM sp_certificate_change WHERE zsid = '{$rt['id']}' AND cg_type = '97_02' AND status=1 and deleted=0 ORDER BY id DESC " );
	if($export1){
		$rt[iso]=$iso_arr[$rt[iso]];
		$rt[date]=date("Y年m月d日",strtotime($rt[s_date]))."-".date("Y年m月d日",strtotime($rt[e_date]));
	}
//	新增联系人电话邮箱
    $rs=$db->get_row("SELECT person,person_tel,person_email FROM sp_enterprises WHERE eid =".$rt['eid']);
    $enterprise = load( 'enterprise' );
    $metas          = $enterprise->meta($rt['eid']);
    $rt['person']=$rs['person'];
    $rt['person_tel']=$rs['person_tel'];
    $rt['person_email']=$metas['person_mail'];
	$datas[] = $rt;

}
//分页
	if (!$export and !$export1) {
         $total = COUNT($datas);
	     $pages = numfpage($total);
	    // echo "<pre />";print_r($pages['limit']);exit;
	    if( empty($pages['limit']) ){
	    	$datas = $datas;
	    }else{
	    	$limits  = explode(',',substr($pages['limit'],7));
	    	$datas = array_slice($datas,$limits[0],$limits[1]);
	    }
	}
//分页

if( !$export and !$export1){
	tpl();
}
if($export) {
	ob_start();
	tpl( 'xls/list_certificate' );
	$data = ob_get_contents();
	ob_end_clean();

	export_xls( '证书列表', $data );
}
if($export1) {
	ob_start();
	tpl( 'xls/list_cert' );
	$data = ob_get_contents();
	ob_end_clean();

	export_xls( '证书列表', $data );
}