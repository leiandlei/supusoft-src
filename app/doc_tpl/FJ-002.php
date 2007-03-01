<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );

$tid = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );
$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );



$a=substr($tb_date,0,10);
$b=substr($te_date,0,10);

$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type="";
$zhuanjia=array();
while( $rt = $db->fetch_array( $query ) ){
	$audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
	$zhuanjia[]=$rt['zy_name'];
}
$zhuanjia=array_unique($zhuanjia);
 
//结合度
$jhd=$db->get_row("SELECT * FROM `sp_contract` WHERE `ct_id` = '$ctid'");
// echo "<pre />";
// print_r($jhd);exit;
extract( $jhd, EXTR_SKIP );

//审核依据
$shenheyiju=$db->getAll("select ssav.audit_basis from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
$str_shenheyiju='';
foreach( $shenheyiju as $v)
{
	$str_shenheyiju .= $v['audit_basis'].', ';
}
$str_shenheyiju = substr($str_shenheyiju,0,strlen($str_shenheyiju)-1);

 //合同编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
//print_r($ct);exit;
//extract( $ct, EXTR_SKIP );
$ct_code=$ct['ct_code'];

//范围
$fw=$db->getAll("SELECT scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");
// echo "<pre>";
// print_r($fw);exit;
foreach ($fw as $key => $value) {
	switch ($value['iso']) {
		case 'A01':	
			$scope.='Q:'.$value[scope].'/'.'  ';
			break;
		case 'A02':
			$scope.='E:'.$value[scope].'/'.'  ';
			break;
		case 'A03':
			$scope.='S:'.$value[scope];
			break;
		default:
			break;
	}
}


//行业
$hy=$db->get_row("SELECT industry FROM `sp_enterprises` WHERE `eid` = '$eid'");
extract( $hy, EXTR_SKIP );

$sql = "SELECT name FROM `sp_settings` WHERE `type` = 'industry' and code in(".substr(str_replace('；',',',$hy['industry']),0,strlen(str_replace('；',',',$hy['industry']))-1).")";
$query = $db->query($sql);
$industry = '';
while ( $rts = $db->fetch_array($query)){
	$industry .= $rts['name'].',';
}
$industry = substr($industry,0,strlen($industry)-1);
//print_r($hy);exit;
//多现场
$guding=$db->get_var("SELECT count(es_type) FROM `sp_enterprises_site` WHERE `eid` = '$eid' and es_type=1000");
extract( $guding, EXTR_SKIP );
//临时
$linshi =$db->get_var("SELECT count(es_type) FROM `sp_enterprises_site` WHERE `eid` = '$eid' and es_type=1001");
extract( $linshi, EXTR_SKIP );
//组长
$leader=$db->get_var("SELECT name FROM `sp_task_audit_team`  WHERE `eid` = '$eid' and role=01");
extract( $leader, EXTR_SKIP );
//项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);


while( $rt = $db->fetch_array( $query ) ){
	if( $rt['role']=="1001" ){
		$leader=$rt['name'];
	} else {
		$auditors[$rt['uid']]=$rt['name'];
	} 
	
}
$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);

$filename = 'CCAA审核经历记录表.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/FJ-002.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
 $output = str_replace( '{ep_name}', $ep_name, $tpldata );
 $output = str_replace( '{leader}', $leader, $output );
 $output = str_replace( '{ct_code}', $ct_code, $output );
 $output = str_replace( '{ep_addr}', $ep_addr, $output );
 $output = str_replace( '{person}', $person, $output );
 $output = str_replace( '{person_tel}', $person_tel, $output );
 $output = str_replace( '{person_email}', $person_email, $output );
 $output = str_replace( '{ep_fax}', $ep_fax, $output );
 // print_r($scope);exit;
 $output = str_replace( '{scope}', $scope, $output );
 $output = str_replace( '{guding}', $guding, $output );
 $output = str_replace( '{linshi}', $linshi, $output );
//结合度
// PRINT_R($xtgc_jhd);EXIT;
 $output = str_replace( '{gltx_jhd}', $gltx_jhd, $output );
 $output = str_replace( '{xtgc_jhd}', $xtgc_jhd, $output );
 $output = str_replace( '{glps_jhd}', $glps_jhd, $output );
 $output = str_replace( '{ns_jhd}', $ns_jhd, $output );
 $output = str_replace( '{fzhmb_jhd}', $fzhmb_jhd, $output );
 $output = str_replace( '{gjjz_jhd}', $gjjz_jhd, $output );
 $output = str_replace( '{glzz_jhd}', $glzz_jhd, $output );
 $output = str_replace( '{sum_jhd}', $sum_jhd, $output );
 //项目编号
 $output = str_replace( '{cti_code}', $str_xiangmu , $output );
 $output = str_replace( '{audit_basis}', $str_shenheyiju , $output );
 ////邮编
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
 $output = str_replace( '{tb_date}', $a, $output );
 $output = str_replace( '{te_date}', $b, $output );
 $output = str_replace( '{ep_amount}', $ep_amount, $output );
 $output = str_replace( '{industry}', $industry, $output );
 //echo $industry;exit;
if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath = CONF.'downs';
	//没有目录创建目录
	if(!is_dir($filePath)) {
	    mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filename) ){
		@unlink ($filePath.'/'.$filename); 
	}

	file_put_contents($filePath.'/'.$filename,$output);
	
	if( file_exists($filePath.'/'.$filename) ){
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>