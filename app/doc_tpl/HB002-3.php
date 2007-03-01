<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
require( ROOT . '/data/cache/add_basis.cache.php' );
require( ROOT . '/data/cache/exc_basis.cache.php' );

$ctid = (int)getgp( 'ct_id' );
$eid = (int)getgp( 'eid' );

$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

// 合同编号
$ct=$db->get_row("SELECT ct_code,review_date,pre_date,approval_user,approval_date,zizhi_require,sum_jhd FROM `sp_contract` WHERE `eid` = '$eid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//项目编号,申请范围
$xm=$db->get_row("SELECT cti_code,audit_code,scope,base_num,add_num,exc_num,xc_num,fxc_num,total_num,exc_clauses,mark FROM `sp_contract_item` WHERE `eid` = '$eid' AND iso='A03' AND `deleted` = '0'");
switch ($xm['mark']) {
	case '01':
			$xm['mark']='CNAS';
		break;	
	default:
		    $xm['mark']='LLL';
		break;
}
extract( $xm, EXTR_OVERWRITE );

//cnas费用//其他费用
$a=$db->get_var("SELECT cc.cost FROM `sp_contract_cost` cc left join  `sp_contract_item` ci on cc.`ct_id`=ci.`ct_id` WHERE cc.`eid`= '$eid' AND cc.`cost_type`='1003' AND ci.`iso`='A03' AND ci.`mark`='01'");
$b=$db->get_var("SELECT cc.cost FROM `sp_contract_cost` cc left join  `sp_contract_item` ci on cc.`ct_id`=ci.`ct_id` WHERE cc.`eid`= '$eid' AND cc.`cost_type`='1003' AND ci.`iso`='A03' AND ci.`mark`='99'");

/**标准信息**/
$sql_auditVers_Info    = "select * from sp_settings_audit_vers where iso='A03'";
$result_auditVers_Info = $db->getOne($sql_auditVers_Info);
/**标准信息**/

$filename = ' 合表002-3 OHSMS认证申请通知单('.$ep_name.').doc';
//读入模板文件 
$output = readover( DOCTPL_PATH . 'doc/HB002-3.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{cti_code}', $cti_code, $output );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{ep_name}', $ep_name, $output );
$output = str_replace( '{sq_type}', $mark,    $output );
$output = str_replace( '{zongfeiyong}', $a+$b,    $output );
$output = str_replace( '{biaozhun}', $result_auditVers_Info['audit_basis'],    $output );
$output = str_replace( '{scope}', $scope,    $output );
$output = str_replace( '{chuburenzhengshijian}', substr($pre_date,0,7),    $output );
$output = str_replace( '{date}', $approval_date,    $output );


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
