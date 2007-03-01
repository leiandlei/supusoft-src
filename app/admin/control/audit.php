<?php
//与审核相关的控制器 
require_once( ROOT . '/data/cache/audit_type.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' ); 
$step = getgp('step');
$et = load('enterprise');
$ct = load('contract');
$cti = load('contract.item');
$audit = load('audit');
$task = load('task'); 

//合同来源
$ctfrom_select = f_ctfrom_select(); 
$province_select = f_province_select();//省分下拉 (搜索用)

//认证体系
$iso_select = f_select('iso');

//审核类型
if( $audit_type_array ){
	foreach( $audit_type_array as $code => $item ){
		if( in_array( $code, array( '1002', '1003','1004','1005', '1006','1007' ) ) )
		$audit_type_select .= "<option value=\"$code\">$item[name]</option>";
	}
}
//审核类型2 适用于特殊审核
if( $audit_type_array ){
	foreach( $audit_type_array as $code => $item ){
		if( !in_array( $code, array( '1001' ) ) )
		$audit_type_select2 .= "<option value=\"$code\">$item[name]</option>";
	}
}
//认证标志
$mark_add_checkbox = $mark_checkbox = '';
if( $mark_array ){
	foreach( $mark_array as $code => $item ){
		$mark_checkbox .= "<label><input type=\"checkbox\" name=\"marks[]\" class=\"mark-item\"  value=\"$code\"/>$item[name]</label> &nbsp; ";
		$mark_add_checkbox .= "<label><input type=\"checkbox\" name=\"add[mk][]\" class=\"mark-item\" value=\"$code\" />$item[name]</label> &nbsp; ";
	}
}  
$audit_ver_select = f_select('audit_ver');//体系版本
 
$risk_level_select = f_select('risk_level');//风险等级
 
unset( $code, $item );

//引入模块控制下的方法
$action=CTL_DIR.$c.'/'.$a.'.php';
if(file_exists($action)){
	include_once($action); 
}else{
	echo '该方法不存在，请检查对应程序';
	echo '<br />方法名称：'.$a;	
}
function do_excel($data,$title){
	require_once ROOT.'/theme/Excel/phpexcel.php';
	require_once ROOT.'/theme/Excel/PHPExcel/Writer/Excel2007.php';
	require_once ROOT.'/theme/Excel/PHPExcel/Writer/Excel5.php';
	include_once ROOT.'/theme/Excel/PHPExcel/IOFactory.php'; 
	//$objExcel = new PHPExcel(); 
	$objReader = new PHPExcel_Reader_Excel5;
	$objExcel = $objReader->load(CONF."task_pre.xls");
	$objExcel->setActiveSheetIndex(0);  
	// 设置工作薄名称
	$objActSheet = $objExcel->getActiveSheet();
	$objActSheet->setCellValue("A3",date("Y",strtotime(substr($data[0][7],0,10))).$title);
	$i=5;
	//$j=1;
	// $f=false;
	foreach($data as $_val){
		// if(!$f and date("m",strtotime($_val[11]))>$j){
			// $objActSheet->setCellValue("A".$i,date("Y",strtotime($_val[11]))."-".$j);
			// $j++;
			// $i++;
			// $f=true;
		// }
		$k="B";
		foreach($_val as $val){
			$objActSheet->setCellValue($k.$i,$val);
			$k++;
			}
		$objActSheet->setCellValue("A".$i,$i-4);
		$i++;

	}
	$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
	
	$filename = date("Y-m-d").'-'.$title.'.xls';
	ob_end_clean();//清除缓冲区,避免乱码
	header("Content-Type: application/force-download");  
	header("Content-Type: application/octet-stream");  
	header("Content-Type: application/download");  
	header('Content-Disposition:inline;filename="'.iconv( 'UTF-8', 'gbk', $filename ).'"');  
	header("Content-Transfer-Encoding: binary");  
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
	header("Pragma: no-cache");  
	$objWriter->save('php://output');   
	// $savedir="data/report_data/".date("Y-m-d").".xls";
	// if(file_exists($savedir))
		// @unlink($savedir);
	// $objWriter->save($savedir);
	// return $savedir;
}
?>