<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$check="□";
$checked="■";

$tid = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );
$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

$audit_1002=$audit_1007=$check;
$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type="";
$zhuanjia=$isos=$audit_vers=array();
while( $rt = $db->fetch_array( $query ) ){
	$audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
	$zhuanjia[]=$rt['zy_name'];
	$isos[$rt[cti_id]]=f_iso($rt[iso]);
	$audit_vers[$rt[cti_id]]=$audit_ver_array[$rt[audit_ver]][audit_basis];
	if($rt[audit_type]=='1002' or $rt[audit_type]=='1003')
		$audit_1002=$checked;
	if($rt[audit_type]=="1007"){
		$audit_1007=$checked;
		$rnum=$db->get_var("SELECT renum FROM `sp_contract_item` WHERE `cti_id` = '$rt[cti_id]'");
		}
}
$zhuanjia=array_unique($zhuanjia);
//项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);


//审核依据
$shenheyiju=$db->getAll("select ssav.audit_basis from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_shenheyiju='';
foreach( $shenheyiju as $v)
{
	$str_shenheyiju .= $v['audit_basis'].',';
}
$str_shenheyiju = substr($str_shenheyiju,0,strlen($str_shenheyiju)-1);

//认证标准
$ct=$db->get_row("SELECT ct_code,iso,cti_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

//组长
$leader=$db->get_var("SELECT name FROM `sp_task_audit_team`  WHERE `tid` = '$tid' and `role`='01' and `deleted`='0'");
extract( $leader, EXTR_SKIP );

 extract( $ct, EXTR_SKIP );
//体系 
$a=$db->getAll("select sp.iso from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
$str_iso='';
// echo "<pre />";
// print_r($a);exit;
foreach( $a as $v)
{
	switch ($v['iso'])
   {
		case 'A10':
			$iso='知识产权管理体系';
			break;
		case 'A01':
			$iso='QMS';
			break;
		case 'A02':
			$iso='EMS';
			break;
		case 'A03':
			$iso='OHSMS';
			break;
	}
	$str_iso.= $iso.',';
}
$str_iso= substr($str_iso,0,strlen($str_iso)-1);



$query_t=$db->query("SELECT audit_type FROM `sp_project` where `eid`='$eid'");
while ($rt = $db->fetch_array($query_t)) {
	switch ($rt['audit_type']) {
		case '1002':
			$rt['audit_type']='一阶段';
			break;
		case '1003':
			$rt['audit_type']='二阶段';
			break;
	}
}

//附加信息
$sql = 'select * from `sp_metas_ep` where ID='.$eid.' and deleted=0';
$extra = $db->getAll($sql);
foreach ($extra as $value) {
	$extra_ep[$value['meta_name']] = $value['meta_value'];
}


//审核组信息
$l = $auditors = array();
$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";
$query = $db->query( $sql);

while( $rt = $db->fetch_array( $query ) ){
	if( $rt['role']=="1001" ){
		$l=$rt[name];
	} else {
		$auditors[$rt[uid]]=$rt['name'];
	} 
	
}
$audit_date=mysql2date( 'Y年n月j日 H:i',$tb_date)." 至 ".mysql2date( 'Y年n月j日 H:i',$te_date);

$filename = '审表003 管理体系文件审查报告('.$ep_name.').doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-003.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
$output = str_replace( '{audit_basis}', $str_shenheyiju , $output );
//组长
$output = str_replace( '{leader}', $leader, $output );
//邮编
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
$output = str_replace( '{app_date}',date("Y年m月d日"), $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $extra_ep['person_mail'], $output );
$output = str_replace( '{iso}', $str_iso, $output );
$output = str_replace( '{audit_type}', $audit_type, $output );
$output = str_replace( '{audit_ver}', $audit_ver, $output );
$output = str_replace( '{cti_code}', $cti_code, $output );
if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	if(!empty(getgp('dates'))){
		$filePath = CONF.'downs'.'/'.getgp('dates');
	}else{
		$filePath = CONF.'downs';
	}
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
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );;
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>