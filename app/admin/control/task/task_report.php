<?php
// 审核工作汇总表
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
set_time_limit(0);
if($year=trim(getgp("year"))){
	$iso_arr=array("A01"=>"Q","A02"=>'E',"A03"=>'S');
	$s_date=$year."-01-01 00:00:00";
	$e_date=++$year."-01-01 00:00:00";
	$tids=$db->get_col("SELECT id FROM `sp_task` WHERE `deleted` = '0'  AND `tb_date` >='$s_date' AND `te_date` < '$e_date' ");
	if(!$tids){
		echo "所选年份没有数据，请选择其他年份。<br/>";
		$year=date("Y");
		echo "<ul>";
		for($i=$year-3;$i<=$year;$i++)
		echo "<li><a href='?c=task&a=task_report&year=".$i."'>$i 年</li>";
		echo "</li>";
		exit;
	}
		
	$query=$db->query("SELECT p.*,e.ep_name,t.tb_date,t.te_date,t.save_date,t.rect_date FROM `sp_project` p LEFT JOIN sp_enterprises e ON e.eid=p.eid LEFT JOIN sp_task t ON t.id=p.tid   WHERE p.`deleted` = '0' AND `tid` IN (".join(",",$tids).") order by t.te_date");
	$data=array();
	while($rt=$db->fetch_array($query)){
		$arr=array();
		$arr[1]=$rt['ep_name'];
		$arr[2]="";
		$arr[3]=$arr[4]=$arr[5]="";
		if($rt[audit_type]=='1002' or $rt[audit_type]=='1003')
			$arr[3]=f_audit_type($rt[audit_type]);
		if($rt[audit_type]=='1004' or $rt[audit_type]=='1005')
			$arr[4]=f_audit_type($rt[audit_type]);
		if($rt[audit_type]=='1007')
			$arr[5]="√";
		$arr[6]=$iso_arr[$rt['iso']];
		$arr[7]=$db->get_var("SELECT name FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND `deleted` = '0' AND `role` = '01'");
		$auditor=$db->get_col("SELECT name FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND `deleted` = '0' AND `role` <> '01'");
		$arr[8]=join(",",$auditor);
		$arr[9]=mkdate($rt['tb_date'],$rt['te_date']);
		$arr[10]=mysql2date( 'Y-n-j',$rt['tb_date']);
		$arr[11]=mysql2date( 'Y-n-j',$rt['te_date']);
		$arr[12]=substr($rt['redata_date'],0,10);
		$arr[13]=$rt['comment_a_name'];
		$arr[14]=substr($rt['bao_date'],0,10);
		$arr[15]=$db->get_var("SELECT sms_date FROM `sp_sms` WHERE `temp_id` = '$rt[tid]' AND `deleted` = '0' AND `flag` = '4' AND `is_sms` = '2' ");
		$arr[16]=$rt['save_date'];
		$arr[17]=$rt['rect_date'];
		ksort($arr);
		// p($arr);
		// exit;
		$data[]=chk_arr($arr);


	}
	do_excel($data,"审核工作汇总表");
}else{
	$year=date("Y");
	echo "<ul>";
	for($i=$year-3;$i<=$year;$i++)
	echo "<li><a href='?c=task&a=task_report&year=".$i."'>$i 年</li>";
	echo "</li>";

}
function do_excel($data,$title){
	require_once ROOT.'/theme/Excel/PHPExcel.php';
	require_once ROOT.'/theme/Excel/PhpExcel/Writer/Excel2007.php';
	require_once ROOT.'/theme/Excel/PhpExcel/Writer/Excel5.php';
	include_once ROOT.'/theme/Excel/PhpExcel/IOFactory.php'; 
	//$objExcel = new PHPExcel(); 
	$objReader = new PHPExcel_Reader_Excel5;
	$objExcel = $objReader->load(CONF."task.xls");
	$objExcel->setActiveSheetIndex(0);  
	// 设置工作薄名称
	$objActSheet = $objExcel->getActiveSheet();
	$objActSheet->setCellValue("A1",date("Y",strtotime($data[0][11])).$title);
	$i=5;
	// $j=1;
	// $f=false;
	foreach($data as $_val){
		// if(!$f and date("m",strtotime($_val[11]))>$j){
			// $objActSheet->setCellValue("A".$i,date("Y",strtotime($_val[11]))."-".$j);
			// $j++;
			// $i++;
			// $f=true;
		// }
		$k="A";
		foreach($_val as $val){
			$objActSheet->setCellValue($k.$i,$val);
			$k++;
			}
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