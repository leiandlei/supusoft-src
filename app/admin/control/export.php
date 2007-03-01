<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
require( DATA_DIR . 'cache/ctfrom.cache.php' );
//统计功能
if( 'contract' == $a ){

	$sql = "SELECT SUBSTRING(accept_date,1,4) 'Year',
SUM(IF(SUBSTRING(accept_date,6,2)='01',1,0)) AS '01',
SUM(IF(SUBSTRING(accept_date,6,2)='02',1,0)) AS '02',
SUM(IF(SUBSTRING(accept_date,6,2)='03',1,0)) AS '03',
SUM(IF(SUBSTRING(accept_date,6,2)='04',1,0)) AS '04',
SUM(IF(SUBSTRING(accept_date,6,2)='05',1,0)) AS '05',
SUM(IF(SUBSTRING(accept_date,6,2)='06',1,0)) AS '06',
SUM(IF(SUBSTRING(accept_date,6,2)='07',1,0)) AS '07',
SUM(IF(SUBSTRING(accept_date,6,2)='08',1,0)) AS '08',
SUM(IF(SUBSTRING(accept_date,6,2)='09',1,0)) AS '09',
SUM(IF(SUBSTRING(accept_date,6,2)='10',1,0)) AS '10',
SUM(IF(SUBSTRING(accept_date,6,2)='11',1,0)) AS '11',
SUM(IF(SUBSTRING(accept_date,6,2)='12',1,0)) AS '12'
FROM sp_contract where accept_date != '0000-00-00' group by SUBSTRING(accept_date,1,4); ";

	$data_year = array();
	for($i = 0; $i < 5; $i++) {
		$data_year[] = date("Y") - $i;
	}

	$res = $db->query($sql);
	while($row=$db->fetch_array($res)){
		 
		if(in_array($row['Year'], $data_year)){
			$data[$row['Year']]=$row;
		}
	}
	//@zbzytech krsort() 函数要求数组型参数 由于查空数据带来的BUG
	if(!is_array($data)) $data = array();
	// 表格部分
	do {
		krsort($data);
		$records = '';
		$recid = 1;
		$total = 0;
		foreach($data as $key=>$value) {
			$records .="		{ recid: $recid, ";
			foreach($value as $month=>$score) {
				if($month!='Year') {
					$total += $score;
					$records .="m$month: $score,  ";
				}else{
					$records .="year: $score,  ";
				}
			}

			$records .="total: $total,  ";
			$total = 0;

			$records .="},\n";
			$recid++;
		}
	}while(false);
 
	ksort($data);

	$datasets = '';
	foreach($data as $key=>$value) {
		$data_data = 'NULL';
		foreach($value as $month=>$score) {
			if($month!='Year') {
				$data_data .= ", ['$month', $score]";
			}
		}
		$data_data = str_replace('NULL,', '', $data_data);

		$datasets .= <<<EOT
			"$key": {
				label: "$key",
				data: [$data_data]
			},\n
EOT;
	}

	tpl();
}  elseif( 'certificate' == $a ){

	$sql = "SELECT SUBSTRING(s_date,1,4) 'Year',
SUM(IF(SUBSTRING(s_date,6,2)='01',1,0)) AS '01',
SUM(IF(SUBSTRING(s_date,6,2)='02',1,0)) AS '02',
SUM(IF(SUBSTRING(s_date,6,2)='03',1,0)) AS '03',
SUM(IF(SUBSTRING(s_date,6,2)='04',1,0)) AS '04',
SUM(IF(SUBSTRING(s_date,6,2)='05',1,0)) AS '05',
SUM(IF(SUBSTRING(s_date,6,2)='06',1,0)) AS '06',
SUM(IF(SUBSTRING(s_date,6,2)='07',1,0)) AS '07',
SUM(IF(SUBSTRING(s_date,6,2)='08',1,0)) AS '08',
SUM(IF(SUBSTRING(s_date,6,2)='09',1,0)) AS '09',
SUM(IF(SUBSTRING(s_date,6,2)='10',1,0)) AS '10',
SUM(IF(SUBSTRING(s_date,6,2)='11',1,0)) AS '11',
SUM(IF(SUBSTRING(s_date,6,2)='12',1,0)) AS '12'
FROM sp_certificate where s_date != '0000-00-00' group by SUBSTRING(s_date,1,4); ";

	$data_year = array();
	for($i = 0; $i < 5; $i++) {
		$data_year[] = date("Y") - $i;
	}

	$res = $db->query($sql);
	while($row=$db->fetch_array($res)){
		if(in_array($row['Year'], $data_year)){
			$data[$row['Year']]=$row;
		}
	}
	//@zbzytech krsort() 函数要求数组型参数 由于查空数据带来的BUG
	if(!is_array($data)) $data = array();
	// 表格部分
	do {
		krsort($data);
		$records = '';
		$recid = 1;
		$total = 0;
		foreach($data as $key=>$value) {
			$records .="		{ recid: $recid, ";
			foreach($value as $month=>$score) {
				if($month!='Year') {
					$total += $score;
					$records .="m$month: $score,  ";
				}else{
					$records .="year: $score,  ";
				}
			}

			$records .="total: $total,  ";
			$total = 0;

			$records .="},\n";
			$recid++;
		}
	}while(false);

	ksort($data);

	$datasets = '';
	foreach($data as $key=>$value) {
		$data_data = 'NULL';
		foreach($value as $month=>$score) {
			if($month!='Year') {
				$data_data .= ", ['$month', $score]";
			}
		}
		$data_data = str_replace('NULL,', '', $data_data);

		$datasets .= <<<EOT
			"$key": {
				label: "$key",
				data: [$data_data]
			},\n
EOT;
	}
	 
	 
	tpl();
	 
	 
} else if($a=='report'){
	$banben = '1';
	$s_date = getgp('s_date');
	$e_date = getgp('e_date');
	tpl( 'export/report' );
} 
else if($a=='mdir'){
	$s_date = getgp('s_date');
	$e_date = getgp('e_date');
	tpl( 'export/mdir' );
} else if($a=='plan_report'){
	tpl( 'export/plan' );
} else if($a=='year_report'){
	$date_e    = getgp('date_e');
	$s_zizheng = getgp('s_zizheng');
	$mark      = getgp('mark');
	$banben    = empty(getgp('banben'))?'1':getgp('banben');

	$s_kind = getgp('s_kind');
	if($date_e){
		$where =" AND z.s_date <= '$date_e' and z.deleted=0";
	}
	if($mark){
		$where .=" AND z.mark='$mark'";
	}
	//屏蔽证书表中未变更的
	if($date_e || $mark)
	{
		if(!empty($mark))
		{
			$bgbszhengshu  = $db->getAll("select * from sp_certificate_change where cg_type='104' and cg_bf ='$mark' and cgs_date > '$date_e' and status =1");
		}else{
			$bgbszhengshu  = $db->getAll("select * from sp_certificate_change where cg_type='104' and cgs_date > '$date_e' and status =1");
			
		}
		foreach($bgbszhengshu as $zhengshuid)
		{
			$zhengshuids[] = $zhengshuid['zsid'];
		}
		$zhengshuids       =  implode(',', $zhengshuids);
		if(!empty($zhengshuids))$where .=" AND z.id not in (".$zhengshuids.")";
	}
	$sql = "select z.id,z.cert_name,z.status,e.areacode ,z.audit_ver,z.iso,z.cti_id,cti.audit_code,cti.audit_code_2017 from sp_certificate z left join sp_enterprises e on e.eid=z.eid left join sp_contract_item cti on cti.cti_id=z.cti_id where z.status in('01','02','03','05') and z.deleted=0 and cti.deleted=0 $where";
	$res = $db->getAll($sql);
	$tempArr  = array();
	
	foreach($res as $row)
	{
		// if($row['status']!='01') continue;
		//匹配有效证书中的审核类型
		if($row['status']=='01')
		{
			$audit_type  = $db->get_var("select audit_type from  sp_project where cti_id =".$row['cti_id']." and sp_type=1  and audit_type not in (1008,1009,1101,99) and deleted =0 group by id desc ");
			// $sql = "INSERT INTO sp_fff (ep_name, audit_type, audit_ver) VALUES ('".$row['cert_name']."', '".$audit_type."', '".$row['audit_ver']."')";
			// $a = $db->query($sql);
			switch ($audit_type) 
			{
				case '1001':
				case '1002':
				case '1003':
					$datas[$row['audit_ver']][1001]++;		
					break;
				case '1004':
				case '1005':
				case '1006':
					$datas[$row['audit_ver']][1004]++;		
					break;
				case '1007':
					$datas[$row['audit_ver']][1007]++;
					break;
			}
		}
		$datas[$row['audit_ver']][(int)$row['status']]++;
		if ($row['status']=='02') {
			$bg = $db->get_row("select * from sp_certificate_change where cg_type='97_01' and cgs_date > '$date_e' and status =1 and zsid=".$row['id']." and deleted=0 group by id desc");
			// print_r($bg);exit;
			if (!empty($bg)) {
				$datas[$row['audit_ver']]['01']++;
			}
		}
		$datas[substr($row['areacode'],0,2).'0000'][$row['iso']]++;	
		//2017版本专业代码
		if(!empty($row['audit_code_2017'])&& $banben=='1')
		{
			$codeList  = array_filter(explode('；', $row['audit_code_2017']));
			$codeims   = '';
			foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			$arr              = array_filter(explode('；',str_replace(';','；',$codeims)));
			$temp             = array();
			foreach($arr as $value)
			{
				$_dalei = substr($value, 0,2);
				if( !in_array($_dalei, $temp) )
				{
					$datas[substr($value, 0,2)][$row['iso']]++;
					$temp[] = $_dalei;
				}
			}
		}
		//新版本专业代码
		if(!empty($row['audit_code'])&& $banben=='2')
		{

			$codeList  = array_filter(explode('；', $row['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			$arr              = array_filter(explode('；',str_replace(';','；',$codeims)));
			$temp             = array();
			//29.12.00；
			//34.01.00；
			//29.11.07
			foreach($arr as $value)
			{
				$_dalei = substr($value, 0,2);
				if( !in_array($_dalei, $temp) )
				{
					$datas[substr($value, 0,2)][$row['iso']]++;
					$temp[] = $_dalei;
				}
			}
		}
		
	}

	tpl( 'export/year_report' );
} else if($a=='auditor_report'){
	$s_date = getgp('s_date');
	$e_date = getgp('e_date');
	if(!$s_date){
		$s_date = date("Y").'-01-01';
	}
	if(!$e_date){
		$e_date = date("Y").'-12-31';
	}
	tpl( 'export/auditor_report' );
}else if($a=='xmqd')
{
	// echo "<pre />";
	// print_r($s_date);exit;
	include_once CONF.'arr_config.php';
	$ctfrom_select = f_ctfrom_select();

	$where   = 'where 1';$in_eid = array();
	$seach   = getSeach();

	foreach($seach as $key=>$item)
	{
		switch($key)
		{
			case 'ctfrom':
				$where .= " and e.ctfrom='".$item."'"; 
				$ctfrom_select = str_replace( 'value="'.$item.'"', 'value="'.$item.'" selected ', $ctfrom_select );
				break;
			case 'approval_date_start':
				$where .= " and ct.approval_date>='".$item."'"; 
				break;
			case 'approval_date_end':
				$where .= " and ct.approval_date<='".$item."'"; 
				break;
			default:break;
		}
	}

	$sql     = 'SELECT e.eid,e.ctfrom,e.ep_name,e.areaaddr,e.person,e.person_tel,ct.approval_date from sp_enterprises e left join sp_contract ct on ct.eid=e.eid '.$where;

	$results = $db->getAll($sql);
	if(!empty($results))
	{

		foreach($results as $key=>$value)
		{

			$sql = "SELECT meta_value from sp_metas_ep where meta_name='person_mail' and ID=".$results[$key]['eid'];
			$results[$key]['person_mail'] = $db->get_var($sql);
			$sql      = "select ct_id from sp_contract where eid=".$value['eid']." and deleted=0 order by review_date desc";
			$results[$key]['ct_id'] = $db->get_var($sql);

			$results[$key]['cti_cf'] = array();
			if(!empty($results[$key]['ct_id']))
			{
				$sql   = "select cti.cti_id,cti.cti_code,cti.total,cti.iso,cti.audit_ver,cti.audit_code,cf.mark,cf.first_date,cf.mark,cf.cert_scope from sp_contract_item cti left join sp_certificate cf on cti.cti_id=cf.cti_id where cti.ct_id=".$results[$key]['ct_id'];

				$items = $db->getAll($sql);
				foreach($items as $item)
				{

					$item['te_date'] = '';
					if(!empty($item["cti_id"]))
					{
						$sql = 'SELECT t.te_date,t.audit_type,p.final_date,p.sp_date,t.tb_date,tat.role,tat.name from sp_project p LEFT JOIN sp_task t on p.tid=t.id LEFT JOIN sp_task_audit_team tat on tat.tid=t.id where p.eid='.$value["eid"].' and cti_id='.$item["cti_id"].' and tat.role=01 and p.tid >0 ORDER BY t.te_date desc LIMIT 0,1';

						$tmp = $db->getOne($sql);
						$item['audit_type'] = $tmp['audit_type'];
                        $item['zjname'] = $tmp['name'];
						$item['te_date']    = $tmp['te_date'];
						if($tmp['audit_type']==1003)
						{
							
							if($tmp['sp_date']!='')
							{
							$b =  date('Y-m-d', strtotime("+1 years",strtotime($tmp['sp_date'])));
						    $item['final_date'] = $b;
							}else{$item['final_date'] ='';}
								
						}else{
							if($tmp['tb_date']!='')
							{
							$_c =  date('Y-m-d', strtotime("+1 years",strtotime($tmp['tb_date'])));
						    $item['final_date'] = $_c;
						    }else{$item['final_date'] ='';}
						}
					}
					$results[$key]['cti_cf'][] = $item;

				}		
			}
		}
	}
	
	if(!empty($seach['audit_type']))
	{
		$tmp = array();
		foreach($results as $value)
		{
			if( empty($value['cti_cf'][0]['audit_type'])||$value['cti_cf'][0]['audit_type']!=$seach['audit_type'])continue;
			$tmp[] = $value;
		}
		$results = $tmp;
	}

	$mark_select      = f_select('mark');
// echo "<pre />";
// 	print_r($results['cti_cf']);exit;	
	if(!empty($seach['mark']))
	{

		$mark_select = str_replace("value=\"$mark\">","value=\"".$seach['mark']."\" selected>",$mark_select);

		$tmp = array();
		foreach($results as $value)
		{
			if( empty($value['cti_cf'][0]['mark'])||$value['cti_cf'][0]['mark']!=$seach['mark'])continue;
			$tmp[] = $value;
		}
		$results = $tmp;

	}

	if(!empty($_REQUEST['export']))
	{
		ob_start();
		tpl( 'xls/list_export_xmqd' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls('项目清单',$data);
	}else{
		tpl( 'export/xmqd' );
	}
}

?>