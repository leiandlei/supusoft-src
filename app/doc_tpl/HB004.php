<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );
//$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';
$pid = getgp( 'pid' );
$t_num = getgp( 't_num' );     //总人日
$tk_num = getgp( 'tk_num' );    //结合人日

$ct=$db->get_row("SELECT ep_name,ep_amount,sp.eid FROM `sp_project` sp  left join  `sp_enterprises` en on sp.eid= en.eid where sp.`id` in($pid) AND sp.`deleted` = 0 And en.`deleted` = 0");
extract( $ct, EXTR_SKIP );
//总人日
$ct=$db->get_row("SELECT ep_name,ep_amount,sp.eid,audit_type FROM `sp_project` sp  left join  `sp_enterprises` en on sp.eid= en.eid where sp.`id` in($pid) AND sp.`deleted` = 0 And en.`deleted` = 0");
extract( $ct, EXTR_SKIP );
//审核类型
switch ($audit_type) {
	case '1001':
				$audit_types='初审';
				break;
			case '1002':
				$audit_types='一阶段';
				break;
			case '1003':
				$audit_types='二阶段';
				break;
			case '1004':
				$audit_types='监一';
				break;
			case '1005':
				$audit_types='监二';
				break;
			case '1006':
				$audit_types='监三';
				break;
			case '1007':
				$audit_types='再认证';
				break;
			case '1008':
				$audit_types='专项审核';
				break;
			case '1009':
				$audit_types='特殊监督';
				break;
			case '1101':
				$audit_types='变更';
				break;
			case '99':
				$audit_types='其他';
				break;
}
//合同编号
$ct=$db->get_row("SELECT ct_code,sum_jhd FROM `sp_contract` WHERE `eid` = '$eid' AND `deleted` = '0'");

extract( $ct, EXTR_SKIP );

//
$filename = $ep_name .' 合表004 审核人日调整确认单.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/HB004.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace( '{ep_name}', $ep_name, $tpldata );
//企业eid
$output = str_replace( '{audit_type}',$audit_types, $output );
$output = str_replace( '{ep_name}',$ep_name, $output );
$output = str_replace( '{ep_amount}',$ep_amount, $output );
$output = str_replace( '{ct_code}',$ct_code, $output );

$output = str_replace( '{num}',$t_num-$tk_num, $output );
$output = str_replace( '{tk_num}',$tk_num, $output );
$output = str_replace( '{sum_jhd}',$sum_jhd, $output );

/**审核人日调整**/
$ct=$db->getAll("SELECT * FROM `sp_project` sp left join `sp_contract_item` cti on sp.cti_id=cti.cti_id WHERE sp.`id` in($pid) AND sp.`deleted` = 0 and cti.`deleted`=0");
foreach($ct as $value)
{
	switch ($value['iso']) {
		case 'A01':
			$replace_xc  = '{q_xc_num}';
			$replace_fxc = '{q_fxc_num}';
			break;
		case 'A02':
			$replace_xc  = '{e_fxc_num}';
			$replace_fxc = '{e_xc_num}';
			break;
		case 'A03':
			$replace_xc  = '{s_fxc_num}';
			$replace_fxc = '{s_xc_num}';
			break;
		
	}
	$output = str_replace( $replace_xc,$value['xc_num'], $output );
	$output = str_replace( $replace_fxc,$value['fxc_num'], $output );
}

    $output = preg_replace("/\{.[^-]+?\}/", "", $output);
/**审核人日调整**/

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
?><?php
/* 
* @Author: anchen
* @Date:   2016-05-23 10:21:52
* @Last Modified by:   anchen
* @Last Modified time: 2018-04-23 18:06:19
*/
?>

