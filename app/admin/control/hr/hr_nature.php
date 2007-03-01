<?php
	require_once ROOT.'/theme/Excel/myexcel.php';
	$tmplate = CONF.'hr_nature.xls';
	if(!is_dir(ROOT.'/app/excel/temp'))@mkdir(ROOT.'/app/excel/temp');
	$tmpName = 'hr'.'('.getgp('date').time().')'.'.xls';
	$tmpPath = 'data/report_data/';
	$excel   = new Myexcel($tmplate);
	$excel->setIndex(0);
	$rowNumber  = 3;
	//有效证书
	/**===========1人员专业能力评价=======**/
	$sql = "SELECT name from sp_hr where job_type like '%1001%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);

	/**===========1人员专业能力评价=======**/

    /**===========2合同评审=======**/
	$excel->setIndex(1);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1003%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
    /**===========2合同评审=======**/
    
    /**===========3审核员=======**/
	$excel->setIndex(2);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1004%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
    /**===========3审核员=======**/

   /**===========4评定人员=======**/
   $excel->setIndex(3);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1006%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
   /**===========4评定人员=======**/

   /**===========5技术专家=======**/
   $excel->setIndex(4);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1007%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
    /**===========5技术专家=======**/
    
    /**===========6审核方案管理人员=======**/
    $excel->setIndex(5);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1010%'  and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
    /**===========6审核方案管理人员=======**/

    /**===========7培训教师=======**/
    $excel->setIndex(6);
	$rowNumber   = 3;
	$sql = "SELECT name from sp_hr where job_type like '%1011%' and deleted =0";
	$row=$db->getAll($sql);
	foreach ($row as $v) {
		$excel->addRow($rowNumber,1);
		$data['A'] = $v['name'];
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
    /**===========7培训教师=======**/

	$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
	echo $saveName['oldName'];
	exit;