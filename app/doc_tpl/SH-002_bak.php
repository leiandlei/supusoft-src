<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
// $br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';
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
// 项目编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);


//审核成员信息
$sql_sh    = "select hr.name,hr.sex,hrq.qua_type,stat.role from sp_task_audit_team stat left join sp_hr_qualification hrq on stat.uid=hrq.uid left join sp_hr hr on stat.uid=hr.id where stat.tid=".$tid;
$query_sh  = $db->query($sql_sh);
$xmlStr    = '';
$xmlString = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:left w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:bottom w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:right w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideH w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideV w:val="single" w:color="auto" w:sz="4" w:space="0"/></w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:trHeight w:val="390" w:hRule="atLeast"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="1692" w:type="dxa"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:spacing w:line="320" w:lineRule="exact"/><w:jc w:val="center"/></w:pPr><w:r><w:t>组员</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1843" w:type="dxa"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:spacing w:line="320" w:lineRule="exact"/><w:jc w:val="center"/><w:rPr><w:highlight w:val="none"/></w:rPr></w:pPr><w:r><w:rPr><w:highlight w:val="none"/></w:rPr><w:t>{name}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3320" w:type="dxa"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:spacing w:line="320" w:lineRule="exact"/><w:jc w:val="center"/><w:rPr><w:highlight w:val="none"/></w:rPr></w:pPr><w:r><w:rPr><w:highlight w:val="none"/></w:rPr><w:t xml:space="preserve">{zige} </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1358" w:type="dxa"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:spacing w:line="320" w:lineRule="exact"/><w:jc w:val="center"/><w:rPr><w:highlight w:val="none"/></w:rPr></w:pPr><w:r><w:rPr><w:highlight w:val="none"/></w:rPr><w:t>{sex}</w:t></w:r></w:p></w:tc></w:tr>';
while( $rt = $db->fetch_array( $query_sh ) ){
	switch ($rt['qua_type']) {
			case '02'://审核员
				$zige = '审核员';
				break;
			case '03'://实习审核员
				$zige = '实习审核员';
				break;
			case '04'://技术专家
				$zige = '技术专家';
				break;
			case '1001'://验证
				$zige = '验证';
				break;
		}
	switch ( $rt['role'] ) {
		case '1001':
				$name_zh = $rt['name'];
				$zige_zh = $zige;
				$sex_zh  = ($rt['sex']==1)?'男':'女';
			break;
		
		default:
			$xmlStr .= str_replace( '{name}', $rt['name'], $xmlString );
			$xmlStr = str_replace( '{zige}', $zige, $xmlStr );
			$xmlStr = str_replace( '{sex}', ($rt['sex']==1)?'男':'女', $xmlStr );
			break;
	}
}
// $audit_type="";
// $zhuanjia=$isos=$audit_vers=array();
// while( $rt = $db->fetch_array( $query ) ){
// 	$audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
// 	$zhuanjia[]=$rt['zy_name'];
// 	$isos[$rt[cti_id]]=f_iso($rt[iso]);
// 	$audit_vers[$rt[cti_id]]=$audit_ver_array[$rt[audit_ver]][audit_basis];
// 	if($rt[audit_type]=='1002' or $rt[audit_type]=='1003')
// 		$audit_1002=$checked;
// 	if($rt[audit_type]=="1007"){
// 		$audit_1007=$checked;
// 		$rnum=$db->get_var("SELECT renum FROM `sp_contract_item` WHERE `cti_id` = '$rt[cti_id]'");
// 		}
// }
// $zhuanjia=array_unique($zhuanjia);

// $audit_date=mysql2date( 'Y年n月',$tb_date);

$filename = $ep_name .'审核方案策划和实施记录及有效期内体系运行情况.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-002.xml' );
// var_dump($audit_date);
// E();

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{name_zh}', $name_zh, $output );
$output = str_replace( '{ct_code}', $ct_code, $output );
//echo $ct_code;exit;
$output = str_replace( '{zige_zh}', $zige_zh, $output );
$output = str_replace( '{sex_zh}', $sex_zh, $output );
$output = str_replace( '{xmlStr}', $xmlStr, $output );
$output = str_replace( '{audit_date}', $audit_date, $output );
$output = str_replace( '{app_date}', date("Y年n月j日"), $output );
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
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