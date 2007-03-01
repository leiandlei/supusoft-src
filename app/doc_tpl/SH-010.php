<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$tid     = (int)getgp( 'tid' );
$ctid    = (int)getgp( 'ct_id' );
$t_info  = $db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info = load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );


$query     = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type="";
$zhuanjia  =array();
while( $rt = $db->fetch_array( $query ) ){
	$audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
	$zhuanjia[]=$rt['zy_name'];
}
$zhuanjia=array_unique($zhuanjia);

$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);

$tb_year = substr($tb_date, 0,4);
$tb_mon  = substr($tb_date, 5,2);
$tb_mon  = ltrim($tb_mon,"0");
$tb_day  = substr($tb_date, 8,2);
$tb_day  = ltrim($tb_day,"0");
$tb_date = $tb_year."年".$tb_mon."月".$tb_day."日";

// 项目编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

$leader = $auditors = array();
$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";
$query = $db->query( $sql);

while( $rt = $db->fetch_array( $query ) ){
	if( $rt['role']=="1001" ){
		$leader=$rt[name];
	} else {
		$auditors[$rt[uid]]=$rt['name'];
	} 
	
}
$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);

$filename = '审表010 首次会议签到表('.$ep_name.').doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-010.xml' );

//企业信息部分
$arr_search  = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name     = str_ireplace($arr_search,$arr_replace,$ep_name);

$output      = str_replace( '{ep_name}', $ep_name, $tpldata );
$output      = str_replace( '{audit_type}', $audit_type, $output );
$output      = str_replace( '{zhuanjia}', join(",",$zhuanjia), $output );
$output      = str_replace( '{leader}', $leader, $output );
$output      = str_replace( '{auditor}', join(",",$auditors), $output );
$output      = str_replace( '{audit_date}', $audit_date, $output );
$output      = str_replace( '{ct_code}', $ct_code, $output );
$output      = str_replace( '{cti_code}', $str_xiangmu , $output );
$output      = str_replace( '{tb_date}', $tb_date , $output );
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
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>