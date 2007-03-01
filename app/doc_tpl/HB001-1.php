<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
require( ROOT . '/data/cache/add_basis.cache.php' );
require( ROOT . '/data/cache/exc_basis.cache.php' );
//$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';
$ctid = (int)getgp( 'ct_id' );
$eid = (int)getgp( 'eid' );
$add_basis_check=$exc_basis_check="";
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
//增减人日
$where .= " AND ct_id = '$ctid' AND iso='A01'";
$sql = "SELECT * FROM sp_contract_item  WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$add_basis=unserialize($rt['add_basis']);
	foreach($add_basis_array as $k=>$item){
		if($item['is_stop']) continue;
		if(in_array($item['code'],$add_basis))
		$add_basis_str .= $item['name'];
	}
	$exc_basis=unserialize($rt['exc_basis']);
	foreach($exc_basis_array as $k=>$item){
		if($item['is_stop']) continue;
		if(in_array($item['code'],$exc_basis))
		$exc_basis_str .= $item['name'];
	}
}

// 合同编号
$ct=$db->get_row("SELECT ct_code,review_date,pre_date,wb_db,approval_user,approval_date,zizhi_require,sum_jhd FROM `sp_contract` WHERE `eid` = '$eid' AND `deleted` = '0'");
//结合度
$sum_jhd    = (empty($sum_jhd))   ? '' : $sum_jhd."%" ;
// print_r($ct);exit;
extract( $ct, EXTR_SKIP );
//合同评审人
$ps=$db->get_row("SELECT hr.name FROM `sp_hr` hr left join `sp_contract` sc on hr.`id`=sc.`review_uid` where sc.`ct_id`='$ctid'");

extract( $ps, EXTR_SKIP );
//项目编号,申请范围
$xm=$db->get_row("SELECT cti_code,audit_code,audit_code_2017,scope,total,base_num,add_num,exc_num,xc_num,fxc_num,total_num,exc_clauses,mark FROM `sp_contract_item` WHERE `eid` = '$eid' 
AND iso='A01' AND `deleted` = '0'");
switch ($xm['mark']) {
	case '01':
			$xm['mark']='CNAS';
		break;	
	default:
		    $xm['mark']='LLL';
		break;
}
extract( $xm, EXTR_SKIP );

//风险等级
$fx=$db->get_row("SELECT `risk_level` FROM `sp_contract_item` WHERE ct_id= '$ctid' AND  iso='A01'");
// print_r($fx);exit;
switch ($fx['risk_level']) {
	case '01':
		$level='高风险';
		break;
	case '02':
		$level='中风险';
		break;
	default:
		$level='低风险';
		break;
}
//cnas费用
$a=$db->get_row("SELECT cc.cost FROM `sp_contract_cost` cc left join  `sp_contract_item` ci on cc.`ct_id`=ci.`ct_id` WHERE cc.`eid`= '$eid' AND cc.`cost_type`='1003'AND ci.`iso`='A01' AND ci.`mark`='01'");
// print_r($a);exit;

//其他费用
$b=$db->get_row("SELECT cc.cost FROM `sp_contract_cost` cc left join  `sp_contract_item` ci on cc.`ct_id`=ci.`ct_id` WHERE cc.`eid`= '$eid' AND cc.`cost_type`='1003' AND ci.`iso`='A01' AND ci.`mark`='99'");
// print_r($b);exit;
//认证标准
$basis=$db->get_var("SELECT audit_basis FROM `sp_contract_item` ci left join `sp_settings_audit_vers` av on av.`audit_ver`=ci.`audit_ver` WHERE av.`deleted` = '0' and ci.ct_id='$ctid' and ci.iso='A01'");
extract($basis, EXTR_SKIP );



//简单临时场所数、
$es =$db->getAll("SELECT es_name FROM sp_enterprises_site WHERE eid = '$eid' AND es_type='1001' AND deleted = 0 ");
$escount=count($es);
// print_r($escount);exit;
//固定
$ese =$db->getAll("SELECT es_name FROM sp_enterprises_site WHERE eid = '$eid' AND es_type='1000' AND deleted = 0 ");
$escounte=count($ese);

$filename = ' 合表001-1 QMS认证申请评审表及组织审核通知单('.$ep_name.').doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/HB001-1.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{name}', $ps['name'], $output );
$output = str_replace( '{cti_code}', $cti_code, $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{ep_phone}', $ep_phone, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
// renri
$output = str_replace( '{add_basis}', $add_basis_str, $output);
$output = str_replace( '{exc_basis}', $exc_basis_str, $output);
if(getgp('banben')=='1')//旧版本专业代码
{
	if(!empty($xm['audit_code_2017']))
	{
		$codeList  = array_filter(explode('；', $xm['audit_code_2017']));
		$codeims   = '';
		foreach($codeList as $code)
		{
			if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		}
		$xm['audit_code_2017'] = $codeims;
	}
	$output = str_replace( '{audit_code}', $xm['audit_code_2017'], $output );
}else{//新版本专业代码
	if(!empty($xm['audit_code']))
	{
		$codeList  = array_filter(explode('；', $xm['audit_code']));
		$codeims   = '';
		foreach($codeList as $code)
		{
			if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		}
		$xm['audit_code'] = $codeims;
	}
	$output = str_replace( '{audit_code}', $xm['audit_code'], $output );

}

$output = str_replace( '{scope}', $xm['scope'], $output );
$output = str_replace( '{wb_db}', $wb_db, $output );
$output = str_replace( '{review_date}', $review_date, $output );
$output = str_replace( '{base_num}', $xm['base_num'], $output );
$output = str_replace( '{add_num}', $xm['add_num'], $output );
$output = str_replace( '{exc_num}', $xm['exc_num'], $output );
$output = str_replace( '{xc_num}', $xm['xc_num'], $output );
$output = str_replace( '{fxc_num}', $xm['fxc_num'], $output );
$output = str_replace( '{total_num}', $xm['total_num'], $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{total}', $xm['total'], $output );
$output = str_replace( '{industry}', $industry, $output );
$output = str_replace( '{pre_date}', $pre_date, $output );
$output = str_replace( '{site_count}', $site_count, $output );
$output = str_replace( '{pre_date}', $pre_date, $output );
$output = str_replace( '{approval_user}', $approval_user, $output );
$output = str_replace( '{approval_date}', $approval_date, $output );
$output = str_replace( '{zizhi_require}', $zizhi_require, $output );
$output = str_replace( '{exc_clauses}', $exc_clauses, $output );
$output = str_replace( '{sum_jhd}', $sum_jhd, $output );
$output = str_replace( '{escount}', $escount, $output );
$output = str_replace( '{escounte}', $escounte, $output );
$output = str_replace( '{audit_basis}',$basis, $output );
//证书类型
// print_r($xm['mark']);exit;
$output = str_replace( '{zs_type}', $xm['mark'], $output );
//风险等级
// print_r($level);exit;
$output = str_replace( '{level}', $level, $output );
// cnas费用
$output = str_replace( '{acost}', $a['cost'], $output );
//其他费用
$output = str_replace( '{bcost}', $b['cost'], $output );
$output = str_replace( '{add_basis_check}', $add_basis_str, $output );
$output = str_replace( '{exc_basis_check}', $exc_basis_str, $output );
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
