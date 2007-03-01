<?php
	if( !getgp('sdate') ){
		exit( json_encode(array('errorCode'=>1,'errorStr'=>'缺少时间')) );
	}
	$sdate      = getgp('sdate');
	require_once ROOT.'/theme/Excel/myexcel.php';
	$tmplate = ROOT.'/app/excel/certificate.xls';
	if(!is_dir(ROOT.'/app/excel/temp'))@mkdir(ROOT.'/app/excel/temp');
	$tmpName = "temp/"."获取组织信息分类报表".getgp('sdate').'.xls';
	$tmpPath = 'app/excel/';
	$excel   = new Myexcel($tmplate);
	//汇总统计 开始
	$excel->setIndex(0);
	$excel->setBackgroundColor('A','N');
	//GB/T 19001-2008/ISO9001:2008
	$rowNumber  = 7;
	//有效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010101' and s_date <'".$sdate."' and mark='01' and status='01'";
	$effective  = $db->get_var($sql);
	//暂停证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010101' and s_date <'".$sdate."' and mark='01' and status='02'";
	
	$suspend    = $db->get_var($sql);
	//撤销证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010101' and s_date <'".$sdate."' and mark='01' and status='03'";
	$revoke     = $db->get_var($sql);
	//过期失效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010101' and s_date <'".$sdate."' and mark='01' and status='05'";
	$over       = $db->get_var($sql);
	$data1['C'] = $effective;
	$data1['G'] = $suspend;
	$data1['H'] = $revoke;
	$data1['I'] = $over;
	$excel->addOneRowFromArray($data1,$rowNumber);
    //ISO9001:2015	
    $rowNumber  = 8;
	//有效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010103' or audit_ver='A010102' and s_date <'".$sdate."' and mark='01' and status='01'";
	$effective  = $db->get_var($sql);
	//暂停证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010103' or audit_ver='A010102' and s_date <'".$sdate."' and mark='01' and status='02'";
	
	$suspend    = $db->get_var($sql);
	//撤销证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010103' or audit_ver='A010102' and s_date <'".$sdate."' and mark='01' and status='03'";
	$revoke     = $db->get_var($sql);
	//过期失效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A010103' or audit_ver='A010102' and s_date <'".$sdate."' and mark='01'  and status='05'";
	$over       = $db->get_var($sql);
	$data2['C'] = $effective;
	$data2['G'] = $suspend;
	$data2['H'] = $revoke;
	$data2['I'] = $over;
	$excel->addOneRowFromArray($data2,$rowNumber);
	//GB/T 24001-2004/ISO14001:2004
	$rowNumber  = 13;
	//有效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020101' and s_date <'".$sdate."' and mark='01' and status='01'";
	$effective  = $db->get_var($sql);
	
	//暂停证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020101' and s_date <'".$sdate."' and mark='01'  and status='02'";
	
	$suspend    = $db->get_var($sql);
	//撤销证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020101' and s_date <'".$sdate."' and mark='01' and status='03'";
	$revoke     = $db->get_var($sql);
	//过期失效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020101' and s_date <'".$sdate."' and mark='01' and status='05'";
	$over       = $db->get_var($sql);
	$data3['C'] = $effective;
	$data3['G'] = $suspend;
	$data3['H'] = $revoke;
	$data3['I'] = $over;
	$excel->addOneRowFromArray($data3,$rowNumber);
	//GB/T 24001-2016/ISO14001:2015
	$rowNumber  = 14;
	//有效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020102' and s_date <'".$sdate."' and mark='01' and status='01'";
	$effective  = $db->get_var($sql);
	// echo "<pre />";
	// print_r($effective);exit;
	//暂停证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020102' and s_date <'".$sdate."' and mark='01' and status='02'";
	
	$suspend    = $db->get_var($sql);
	//撤销证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020102' and s_date <'".$sdate."' and mark='01' and status='03'";
	$revoke     = $db->get_var($sql);
	//过期失效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A020102' and s_date <'".$sdate."' and mark='01'  and status='05'";
	$over       = $db->get_var($sql);
	$data4['C'] = $effective;
	$data4['G'] = $suspend;
	$data4['H'] = $revoke;
	$data4['I'] = $over;
	$excel->addOneRowFromArray($data4,$rowNumber);
	//GB/T 28001-2011
	$rowNumber = 16;
	//有效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A030102' and s_date <'".$sdate."' and mark='01' and status='01'";
	$effective  = $db->get_var($sql);
	//暂停证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A030102' and s_date <'".$sdate."' and mark='01' and status='02'";
	$suspend    = $db->get_var($sql);
	//撤销证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A030102' and s_date <'".$sdate."' and mark='01' and status='03'";
	$revoke     = $db->get_var($sql);
	//过期失效证书
	$sql        = "select count(id) from sp_certificate where deleted='0' and audit_ver='A030102' and s_date <'".$sdate."' and mark='01' and status='05'";
	$over       = $db->get_var($sql);
	$data5['C'] = $effective;
	$data5['G'] = $suspend;
	$data5['H'] = $revoke;
	$data5['I'] = $over;
	$excel->addOneRowFromArray($data5,$rowNumber);
	///汇总统计 结束
	///业务范围统计  开始
	$excel->setIndex(1);
	$i=1;
	while ( $i<= 39) 
	{
		if ($i<=9) {
			$i='0'.$i;
		}
		$qms         = $db->get_var("select count(cert.id) from sp_certificate cert  LEFT JOIN sp_project p ON p.ct_id = cert.ct_id and p.iso = cert.iso  where  p.audit_type=1003   and cert.s_date <'".$sdate."' and p.audit_code LIKE '%$i".".%'"." AND p.audit_code NOT LIKE "."'%."."$i".".%'"."  and cert.status='01' and cert.iso='A01' and cert.mark='01' and cert.deleted='0' ");
		$ems         = $db->get_var("select count(cert.id) from sp_certificate cert  LEFT JOIN sp_project p ON p.ct_id = cert.ct_id and p.iso = cert.iso  where  p.audit_type=1003   and cert.s_date <'".$sdate."' and p.audit_code LIKE '%$i".".%'"." AND p.audit_code NOT LIKE "."'%."."$i".".%'"."  and cert.status='01' and cert.iso='A02' and cert.mark='01' and cert.deleted='0'");
        $ohsms       = $db->get_var("select count(cert.id) from sp_certificate cert  LEFT JOIN sp_project p ON p.ct_id = cert.ct_id and p.iso = cert.iso  where  p.audit_type=1003   and cert.s_date <'".$sdate."' and p.audit_code LIKE '%$i".".%'"." AND p.audit_code NOT LIKE "."'%."."$i".".%'"."  and cert.status='01' and cert.iso='A03' and cert.mark='01' and cert.deleted='0'");
		$data6['B']  = $qms;
		$data6['C']  = $ems;
		$data6['D']  = $ohsms;
		$rowNumber   = $i+2;
		$excel->addOneRowFromArray($data6,$rowNumber);
        $i++;
	}
	
	//业务范围统计  结束
	//地域统计开始
	$excel->setIndex(2);
	//区划代码
	$rowNumber  = 4;
	$codes  = array('110000','120000','130000','140000','150000','210000','220000','230000','310000'
				,'320000','330000','340000','350000','360000','370000','410000','420000','430000'
				,'440000','450000','460000','500000','510000','520000','530000','540000','610000'
				,'620000','630000','640000','650000','710000','810000','820000');
	$i=4;
	foreach ($codes as $value) 
	{
		$min_code    = $value;
		$max_code    = $value+9999;
		$qms_code    = $db->get_var("select count(cert.id) from sp_certificate cert LEFT JOIN sp_enterprises ep on cert.eid=ep.eid where cert.deleted='0'  and cert.s_date <'".$sdate."' and cert.iso='A01' and cert.status='01' and ep.areacode >= '".$min_code."' and ep.areacode <= '".$max_code."' and cert.mark='01' and cert.deleted='0'");
		$ems_code    = $db->get_var("select count(cert.id) from sp_certificate cert LEFT JOIN sp_enterprises ep on cert.eid=ep.eid where cert.deleted='0'  and cert.s_date <'".$sdate."' and cert.iso='A02' and cert.status='01' and ep.areacode >= '".$min_code."' and ep.areacode <= '".$max_code."' and cert.mark='01' and cert.deleted='0'");
		$ohsms_code  = $db->get_var("select count(cert.id) from sp_certificate cert LEFT JOIN sp_enterprises ep on cert.eid=ep.eid where cert.deleted='0'  and cert.s_date <'".$sdate."' and cert.iso='A03' and cert.status='01' and ep.areacode >= '".$min_code."' and ep.areacode <= '".$max_code."' and cert.mark='01' and cert.deleted='0'");
		$data7['C']  = $qms_code;
		$data7['D']  = $ems_code;
		$data7['E']  = $ohsms_code;
		$rowNumber   = $i;
		$excel->addOneRowFromArray($data7,$rowNumber);
		$i++;
	}
	/**认证机构认证人员统计表**/

	$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
	echo $saveName['oldName'];
	exit;