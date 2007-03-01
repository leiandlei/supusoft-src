<?php
	if( !getgp('date') ){
		exit( json_encode(array('errorCode'=>1,'errorStr'=>'缺少时间')) );
	}
	$nextyear  = getgp('date');
	$nouDate   = $nextyear.'-12-31';//入职时间小余
	$nextDate  = $nextyear.'-01-01';//离职时间大
	$ctfrom    = " AND hr.ctfrom >= '01000000' AND hr.ctfrom < '02000000'";
	//======================**************//总数筛选条件
	$personall    = $db->getAll("select ID from sp_metas_hr where deleted=0");
	$userids      = array();
	foreach($personall as $items)
	{
		$peosonList   = $db->getAll("select * from sp_metas_hr where deleted=0  and (meta_name='in_date' or meta_name='out_date') and  ID=".$items['ID']);
		//===================================================判断sheet3 总数 剔除不符合条件的人员**********************//
		$zaizhi       = true;
		foreach($peosonList as $peoson)
		{
			if($peoson['meta_name']=='in_date')
			{
				if( $peoson['meta_value']>$nouDate)
				{
					$zaizhi        = false;break;
				} 
			}else if($peoson['meta_name']=='out_date'){
				if(!empty($peoson['meta_value']) && $peoson['meta_value']< $nouDate ){
					$zaizhi        = false;break;
				}
			}
		}
		if($zaizhi)$userids[]  = $items['ID'];
		//===================================================**********************//
		//===================================================判断sheet1 人员列表 剔除不符合条件的人员**********************//
		
		$renyuanlist  = true;
		
		foreach($peosonList as $peoson)
		{
			
			if($peoson['meta_name']=='in_date')
			{
				if($peoson['meta_value']>$nouDate || empty($peoson['meta_value']) || $peoson['meta_value']=='0000-00-00'){
					$renyuanlist   = false;break;
				}
			}else if($peoson['meta_name']=='out_date'){
				if(!empty($peoson['meta_value']) && $peoson['meta_value'] <= $nouDate ){
					$renyuanlist   = false;break;
				}
			}
			
		}
		
		if($renyuanlist)$renyuanlistids[]= $items['ID'];
	
		//===================================================**********************//
		
	}
	$userids   = implode(array_filter(array_unique($userids)),',');
	if(!empty($userids))
	{
		$wheres = ' and hr.id in('.$userids.')';
	}else{
		$wheres = '';
	}
	$renyuanlistids   = implode(array_filter(array_unique($renyuanlistids)),',');
	

	if(!empty($renyuanlistids))
	{
		$where5 = ' and hr.id in('.$renyuanlistids.')';
	}else{
		$where5 = '';
	}
	//======================**************//总数筛选条件结束=================**************//注册认证人员筛选条件 结束

	require_once ROOT.'/theme/Excel/myexcel.php';
	$tmplate = ROOT.'/app/excel/nianbao.xls';
	if(!is_dir(ROOT.'/app/excel/temp'))@mkdir(ROOT.'/app/excel/temp');
	$tmpName = "temp/".getgp('date').time().'.xls';
	$tmpPath = 'app/excel/';
	$excel   = new Myexcel($tmplate);
	$excel->setIndex(0);
	$excel->setBackgroundColor('A','N');
	$rowNumber = 11;
	$total      = $db->get_var("select count(hr.id) from sp_hr hr where hr.deleted='0' ".$where5." ".$ctfrom."");
	//	print_r($total);exit;              //人员总数
	$registered = $db->get_var("select count(hr.id) from sp_hr hr where hr.deleted='0' and (hr.job_type like '%1004%') ".$where5." ".$ctfrom."");
	//	print_r($registered);exit;         //注册认证人员
	$fulltime   = $db->get_var("select count(hr.id) from sp_hr hr where hr.deleted='0' and (hr.job_type like '%1004%' or hr.job_type like '%1007%') and hr.audit_job='1' ".$where5." ".$ctfrom."");
//		print_r($fulltime);exit;           //专业认证人员
	$data['D'] = $total;
	$data['G'] = $registered;
	$data['K'] = $fulltime;
	$excel->addOneRowFromArray($data,$rowNumber);

	$excel->setIndex(1);
	$excel->setBackgroundColor('A','N');
	$arr_iso  = $db->getAll("select iso,SUBSTRING(audit_ver,1,5) as audit_ver from sp_settings_audit_vers where is_stop='0' and is_shangbao='1' and deleted='0' GROUP BY SUBSTRING(audit_ver,1,5) order by audit_ver asc");
	
	$rowNumber = 7;
	foreach ($arr_iso as $v) {
		$excel->addRow($rowNumber,1);
		$data = array(
					 'B' =>substr($v['audit_ver'],0,5)
					,'E' =>$db->get_var("select count(id) from sp_certificate where deleted='0' and audit_ver like '".substr($v['audit_ver'],0,5)."%' and DATE_FORMAT(s_date,'%Y')='".getgp('date')."'")
					,'F' =>$db->get_var("select count(id) from sp_project where deleted='0' and status='3'  and audit_ver like '".substr($v['audit_ver'],0,5)."%' and (audit_type='1004' or audit_type='1005') and DATE_FORMAT(sp_date,'%Y')='".getgp('date')."'")
					,'G' =>$db->get_var("select count(id) from sp_certificate_change where deleted='0' and audit_ver like '".substr($v['audit_ver'],0,5)."%' and cg_meta='97_01' and DATE_FORMAT(cgs_date,'%Y')='".getgp('date')."'")
					,'H' =>$db->get_var("select count(id) from sp_certificate_change where deleted='0' and audit_ver like '".substr($v['audit_ver'],0,5)."%' and cg_meta='97_03' and DATE_FORMAT(cgs_date,'%Y')='".getgp('date')."'")
					,'I' =>0
				);
		switch ($data['B']) {
			case 'A0101':
				$data['C'] = '质量管理体系认证（ISO9000）';
				break;
			case 'A0102':
				$data['C'] = '建设施工行业质量管理体系认证';
				break;
			case 'A0201':
				$data['C'] = '环境管理体系认证';
				break;
			case 'A0301':
				$data['C'] = '中国职业健康安全管理体系认证';
				break;
			case 'C0299':
				$data['C'] = '所有未列明的其他服务认证';
				break;
			
			default:
				$data['C'] = '';
				break;
		}
		$i=0;
		$valid = $db->getALL("select id from sp_certificate where deleted='0' and audit_ver like '".substr($v['audit_ver'],0,5)."%' and DATE_FORMAT(s_date,'%Y')<='".getgp('date')."' and DATE_FORMAT(e_date,'%Y')>'".getgp('date')."'");
		foreach ($valid as $v) {
			$sql = "select * from sp_certificate_change where zsid='".$v['id']."' and deleted='0' and status='1' and DATE_FORMAT(cgs_date,'%Y')<='".getgp('date')."' order by id desc";
			$results = $db->getAll($sql);
			if (!empty($results)) {
				if ($results['0']['cg_type']=='97_01'||$results['0']['cg_type']=='97_03') {
					continue;
				}
			}
			$i++;
		}
		$data['D'] = $i;
		$excel->addOneRowFromArray($data,$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);


	$excel->setIndex(2);
	$excel->setBackgroundColor('A','N');

	/** 认证机构专职认证人员基本信息表 **/
	$join = $select = '';$where =' where 1';
	////***********人员基本信息表列表开始********************////
	////***********人员基本信息表列表结束********************////
	
	$sql     = "select 
				hq.id,hq.uid,hq.iso,hq.qua_no,hq.qua_type,hq.e_date,hr.name,hr.sex,hr.card_no,hr.tel
				from `sp_hr` hr 
				left join sp_hr_qualification hq on hr.id=hq.uid 
				where hr.audit_job='1' and hq.deleted='0' and hr.deleted='0' and hr.id !=1  and (hr.job_type like '%1004%' or hr.job_type like '%1007%') ".$wheres."";

	$results = $db->getAll($sql);
	
	$i = 0;$rowNumber = 22;
	foreach ($results as $v) 
	{
		$data = array(
					 'B' =>$i
					,'C' =>substr($v['iso'],0,3)
					,'E' =>$v['name']
					,'F' =>($v['sex']=='1')?'男':'女'
					,'G' =>$v['card_no']
					,'I' =>$v['tel']
					,'K' =>$v['qua_no']
					,'L' =>!empty($v['qua_no'])?$v['e_date']:''
				);
		switch ($data['C']) {
			case 'A01':
				$data['D'] = '质量管理体系认证';
				break;
			case 'A02':
				$data['D'] = '环境管理体系认证';
				break;
			case 'A03':
				$data['D'] = '职业健康安全管理体系认证';
				break;
			case 'C02':
				$data['D'] = '一般服务认证';
				break;
			
			default:
				$data['D'] = '';
				break;
		}

		switch ($v['qua_type']) {
			case '01':
			case '02':
				$data['J'] = '审核员';
				break;
			case '03':
				$data['J'] = '实习审核员';
				break;
			case '04':
				$data['J'] = '技术专家';
				break;
			case '06':
				$data['J'] = '审查员';
				break;
			// case '99':
			// 	$data['J'] = '其他';
			// 	break;
			// case '1000':
			// 	$data['J'] = '认证评定';
			// 	break;
			// case '1001':
			// 	$data['J'] = '验证';
			// 	break;
			default:
				$data['J'] = '';
				break;
		}

		if ($v['qua_type']=='04') {
			$sql = "select count(id) from sp_hr_qualification where deleted='0' and uid='".$v['uid']."' and qua_type!='04' and iso='".$v['iso']."'";
			$abc = $db->get_var($sql);
			if ($abc!=0) {
				continue;
			}
		}
		$sql = "select count(id) from sp_hr_qualification where deleted='0' and id!='".$v['id']."' and uid='".$v['uid']."' and qua_type='04' and iso='".$v['iso']."'";
		$cba = $db->get_var($sql);
		if ($cba!=0) {
			$data['J'] = $data['J'];
		}

		$excel->addRow($rowNumber,1);
		$i++;
		$excel->addOneRowFromArray($data,$rowNumber);
		$excel->mergeCells('G'.$rowNumber,'H'.$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);
	/** 认证机构专职认证人员基本信息表 **/
	//判断专兼职 剔除不符合条件的人员
	$personall_list    = $db->getAll("select ID from sp_metas_hr where deleted=0 ");
	$userids_list      = array();
	$innerthis         = array();
	$outthisid         = array();
	foreach($personall_list as $items_list)
	{
		$peosonLists   = $db->getAll("select * from sp_metas_hr where deleted=0  and (meta_name='in_date' or meta_name='out_date') and  ID=".$items_list['ID']);
		$zhuanjian     = false;
		$innerlist     = false;
		$outlist       = false;
		foreach($peosonLists as $peosons)
		{
			if($peosons['meta_name']=='in_date') //人员为 登记时间 登记时间小余12-31
			{
				if( $peosons['meta_value']<$nouDate )
				{
					$zhuanjian     = true;
				}
				if( $nextDate < $peosons['meta_value'] && $peosons['meta_value'] < $nouDate)//新加入人员
				{
					$innerlist     = true;
				}
			}
			if($peosons['meta_name']=='out_date') //离职人员
			{
				if( $nextDate < $peosons['meta_value'] && $peosons['meta_value'] < $nouDate && !empty($peosons['meta_value']) )
				{
					$outlist       = true;
					
				}
			}
			if($zhuanjian)$userids_list[]  = $items_list['ID'];
			if($innerlist)$innerthis[]     = $items_list['ID'];
			if($outlist)$outlistthis[]     = $items_list['ID'];
		}
	}
	
	$userids_list   = implode(array_filter(array_unique($userids_list)),',');
	if(!empty($userids_list))
	{
		$where1 = ' and id in('.$userids_list.')';
	}else{
		$where1 = '';

	}
	$innerthis   = implode(array_filter(array_unique($innerthis)),',');
	if(!empty($innerthis))
	{
		$where2 = ' and hr.id in('.$innerthis.')';
	}else{
		$where2 = '';
	}
	$outlistthis   = implode(array_filter(array_unique($outlistthis)),',');
	if(!empty($outlistthis))
	{
		$where3 = ' and hr.id in('.$outlistthis.')';
	}else{
		$where3 = '';
	}
	
	/**认证机构认证人员统计表**/
	
	$arr_user = array();
	$user = $db->getAll("select id from sp_hr where audit_job='0' and is_hire='1' ".$where1." and deleted='0'");
	foreach ($user as $v) {
		$arr_user['jian'][] = $v['id'];
	}
	$user = $db->getAll("select id from sp_hr where audit_job='1' and is_hire='1' ".$where1." and deleted='0'");
	foreach ($user as $v) {
		$arr_user['zhuan'][] = $v['id'];
	}
	$rowNumber = 14;
	$arr_iso   = $db->getAll("select SUBSTRING(iso,1,3) as iso from sp_settings_audit_vers where is_stop='0' and is_shangbao='1' and deleted='0' GROUP BY SUBSTRING(iso,1,3) order by iso asc");
	

	foreach ($arr_iso as $v) {
		
		/** 新加入人数 **/
		$excel->addRow($rowNumber,1);
		//=================//
		$zhuan = $db->get_var("select count(hq.id) from sp_hr_qualification hq left join sp_hr hr on hq.uid=hr.id where hq.status='1' and hq.deleted='0' and hq.uid in(".implode(',', $arr_user['zhuan']).")   and hq.qua_type!='04' and hq.iso='".$v['iso']."' ");
		
		$jian  = $db->get_var("select count(hq.id) from sp_hr_qualification hq left join sp_hr hr on hq.uid=hr.id where hq.status='1' and hq.deleted='0' and hq.uid in(".implode(',', $arr_user['jian']).")    and hq.qua_type!='04' and hq.iso='".$v['iso']."' ");
	
		$sql   = "select count(hq.id) from sp_hr_qualification hq left join sp_hr hr on hq.uid=hr.id where    hq.deleted='0' ".$where3."  and hq.qua_type!='04' and hq.iso='".$v['iso']."'";
		$quit  = $db->get_var($sql);
		$sql   = "select count(hq.id) from sp_hr_qualification hq left join sp_hr hr on hq.uid=hr.id where    hq.status='1'  ".$where2."  and hq.deleted='0'  and hq.qua_type!='04' and hq.iso='".$v['iso']."'";

		$join1 = $db->get_var($sql);
		
		
		$data = array(
					 'B' =>substr($v['iso'],0,3)
					,'H' =>$zhuan
					,'I' =>$jian
					,'J' =>$quit
					,'K' =>$join1
				);
		switch ($data['B']) {
			case 'A01':
				$data['D'] = '质量管理体系认证';
				break;
			case 'A02':
				$data['D'] = '环境管理体系认证';
				break;
			case 'A03':
				$data['D'] = '职业健康安全管理体系认证';
				break;
			case 'C02':
				$data['D'] = '一般服务认证';
				break;
			
			default:
				$data['D'] = '';
				break;
		}

		$excel->addOneRowFromArray($data,$rowNumber);
		$excel->mergeCells('B'.$rowNumber,'C'.$rowNumber);
		$excel->mergeCells('D'.$rowNumber,'G'.$rowNumber);
	}
	$excel->removeRow($rowNumber-1,1);

	/**认证机构认证人员统计表**/

	$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
	echo $saveName['oldName'];
	exit;