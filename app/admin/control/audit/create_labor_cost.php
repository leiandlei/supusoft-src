<?php
set_time_limit(0);
/*
*导出劳务费计算表--审核安排
*/
if($step=getgp("step")){
if(!($e_date=getgp("e_date") && $s_date=getgp("s_date"))){
	$e_date=date("Y-m")."-01";
	$s_date=get_addday($e_date,-1);
}else{
	$e_date=getgp("e_date");
	$s_date=getgp("s_date");

}
$where =" AND taskBeginDate>='$s_date' AND taskEndDate<'$e_date'";
//******************excel

$data=array(array(
			"A"	=>"1",
			"B"	=>"姓名",
			"C"	=>"审核日期",//
			"D"	=>"审核企业",//
			"E"	=>"QMS",
			"F"	=>"EMS",
			"G"	=>"OHSMS",//
			"H"	=>"EnMS",
			"I"	=>"组长天数",//
			"J"	=>"组员天数",//
			"K"	=>"配组",
			"L"	=>"文审",
			"M"	=>"有配组",
			
			));


//********************
$auditors = array();
$query = $db->query( "SELECT ta.*,e.ep_name FROM sp_tat_temp ta LEFT JOIN sp_enterprises e ON e.eid = ta.eid  where 1 $where  ORDER BY ta.uid" );
$uid="";
$_num=$_num1=$_num2=$_num3=0.0;
$f=1;
while( $rt = $db->fetch_array( $query ) ){
	$rt[num]=mkdate($rt[taskBeginDate],$rt[taskEndDate]);
	$rt[num]<0 && $rt[num]='';
	
	$rt['taskBeginDate'] = mysql2date( 'Y/m/d H', $rt['taskBeginDate'] );
	$rt['taskEndDate'] = mysql2date( 'Y/m/d H', $rt['taskEndDate'] );
	$rt[peizu_person]=$rt[you_person]="";
	$peizu_person= $db->get_var("select peizu_person from sp_task where id='{$rt[tid]}'");
	if(strpos($peizu_person,$rt[name])!==false)
		$rt[peizu_person]=1;
	if($rt['role']=='01'){
		$rt[role_num]=$rt[num];
		if($peizu_person && strpos($peizu_person,$rt[name])===false)
			$rt[you_person]=1;
		}
	
	$_res=explode("|",$rt[iso]);
	$_res1=explode("|",$rt[qua_type]);
	$_res2=array();
	foreach($_res as $k=>$val){
		$_res2[$val]=f_qua_type( $_res1[$k] );
	
	}
	$wenshen_person = $db->get_var("select wenshen_person from sp_task where id='{$rt[tid]}'");
	
	$wen=array();
	if($wenshen_person && strpos($wenshen_person,$rt[name])!==false){
		if(strpos($wenshen_person,"Q"))
			$wen[q]=1;
		if(strpos($wenshen_person,"E"))
			$wen[e]=1;
		if(strpos($wenshen_person,"O"))
			$wen[o]=1;
		if(strpos($wenshen_person,"N"))
			$wen[n]=1;
		$rt[wenshen_person]=count($wen);
		}
		
		
	if(!$uid){
		$uid=$rt[uid];
		$_num+=$rt[num];
		$_num1+=$rt[role_num];
		$_num2+=$rt[peizu_person];
		$_num3+=$rt[wenshen_person];
		$_num4+=$rt[you_person];
	}	
	else{
		if($uid!=$rt[uid]){
			$uid=$rt[uid];
			$f++;
			$data[]=array(
			"A"	=>"",
			"B"	=>"",
			"C"	=>"",//
			"D"	=>"",//
			"E"	=>"",
			"F"	=>"",
			"G"	=>"",//
			"H"	=>"",
			"I"	=>$_num1,//
			"J"	=>$_num,//
			"K"	=>$_num2,
			"L"	=>$_num3,
			"M"	=>$_num4,
			
			);
			$data[]=array(
			"A"	=>"",
			"B"	=>"",
			"C"	=>"",//
			"D"	=>"",//
			"E"	=>"",
			"F"	=>"",
			"G"	=>"",//
			"H"	=>"",
			"I"	=>"",//
			"J"	=>"",//
			"K"	=>"",
			"L"	=>"",
			"M"	=>"",
			
			);
			$data[]=array(
			"A"	=>$f,
			"B"	=>"姓名",
			"C"	=>"审核日期",//
			"D"	=>"审核企业",//
			"E"	=>"QMS",
			"F"	=>"EMS",
			"G"	=>"OHSMS",//
			"H"	=>"EnMS",
			"I"	=>"组长天数",//
			"J"	=>"组员天数",//
			"K"	=>"配组",
			"L"	=>"文审",
			"M"	=>"有配组",
			
			);
		
		$_num=$_num1=$_num2=$_num3=$_num4=0.0;
		$_num+=$rt[num];
		$_num1+=$rt[role_num];
		$_num2+=$rt[peizu_person];
		$_num3+=$rt[wenshen_person];
		$_num4+=$rt[you_person];
		}else{
			$_num+=$rt[num];
			$_num1+=$rt[role_num];
			$_num2+=$rt[peizu_person];
			$_num3+=$rt[wenshen_person];
			$_num4+=$rt[you_person];
		}
	
	}
	
	$data[]=array(
			"A"	=>"",
			"B"	=>$rt[name],
			"C"	=>$rt[taskBeginDate]."-".$rt['taskEndDate'],//
			"D"	=>$rt[ep_name],//
			"E"	=>$_res2[A01],
			"F"	=>$_res2[A02],
			"G"	=>$_res2[A03],//
			"H"	=>$_res2[A12],
			"I"	=>$rt[role_num],//
			"J"	=>$rt[num],//
			"K"	=>$rt[peizu_person],
			"L"	=>$rt[wenshen_person],
			"M"	=>$rt[you_person],
			
			);
		$auditors[]=$rt;

}
require_once ROOT.'/include/Excel/PHPExcel.php';
require_once ROOT.'/include/Excel/PhpExcel/Writer/Excel2007.php';
require_once ROOT.'/include/Excel/PhpExcel/Writer/Excel5.php';
include_once ROOT.'/include/Excel/PhpExcel/IOFactory.php'; 
$objExcel = new PHPExcel(); 
//$objWriter = new PHPExcel_Writer_Excel2003($objExcel); // 用于 2007 格式  
$objExcel->setActiveSheetIndex(0);  
$objActSheet = $objExcel->getActiveSheet();
foreach($data as $i=>$_val){
	$j=$i+1;
	foreach($_val as $k=>$val)
		$objActSheet->setCellValue($k.$j,$val);
}
$filename = date("Y-m-d").'-劳务费信息.xls';
header("Content-Type: application/force-download");  
header("Content-Type: application/octet-stream");  
header("Content-Type: application/download");  
header('Content-Disposition:inline;filename="'.iconv( 'UTF-8', 'gbk', $filename ).'"');  
header("Content-Transfer-Encoding: binary");  
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
header("Pragma: no-cache");  
$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
$objWriter->save('php://output');  
 
}else{

	tpl();

}