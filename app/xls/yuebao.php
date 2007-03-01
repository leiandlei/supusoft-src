<?php
set_time_limit(0);
date_default_timezone_set('PRC');
/*
 *---------------------------------------------------------------
 *月报
 *---------------------------------------------------------------
 */
$begindate = getgp('s_date');//取数开始日期
$begindate.= " 00:00:00";
$enddate   = getgp('e_date');//取数截止日期
$enddate  .= " 23:59:59";
//版本
$banben    = getgp('banben');
$arr_data_zsInfo = array();
$arr_data_shInfo = array();
$pids = array();
$tids = array();

/**===========证书=======**/
//sql初始化
$join = $select = '';
$where = ' where 1';

//证书表
$sql    = "select %s from `sp_certificate` c";
$select.= "c.*";
$where .= " and c.`status`='01' and c.deleted=0";

//判断变更关联项目id
$cg_pid = $db->getALL("select ctc.cg_pid from sp_certificate_change ctc where ctc.cgs_date>='$begindate' and ctc.cgs_date<='$enddate'");

if (!empty($cg_pid)) {
	foreach ($cg_pid as $v) {
		$arr_cg_pid .= "'".$v['cg_pid']."'".",";
	}
	$arr_cg_pid = substr($arr_cg_pid,0,-1);
	$where .= " and p.deleted = '0' and (p.sp_date between '$begindate' and '$enddate' or p.id in ($arr_cg_pid)) and p.audit_type not in('1001','1002') ";
}else{
	$where .= " and p.deleted = '0' and p.sp_date between '$begindate' and '$enddate'";
}
//审核项目表

$select.= ",p.id as pid,p.audit_code,p.pd_audit_code,p.audit_code_2017,p.pd_audit_code_2017,p.ct_code,p.audit_type,p.comment_b_name,p.sp_date,p.tid,p.pd_mark,p.mark as p_mark";
$join  .= " left join sp_project p ON c.cti_id=p.cti_id";
$where .= " and p.deleted = '0' and p.sp_date between '$begindate' and '$enddate'";
//审核计划表
$select.= ",t.te_date,t.id as tid,tb_date";
$join  .= " left join sp_task t ON t.id=p.tid";
$where .= " and t.deleted = '0'";

//企业表
$select.= ",e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.person_tel,e.ep_phone,e.ep_fax,e.cta_addr as ctaaddr";
$join  .= " left join sp_enterprises e ON e.eid=c.eid ";
$where .= " and e.deleted = 0";

//合同项目表
$select.= ",cti.base_num,cti.risk_level";
$join  .= " left join sp_contract_item cti ON p.cti_id = cti.cti_id";
$where .= " and cti.deleted = '0'";

//拼装sql

$sql  = sprintf($sql,($select=='')?'*':$select).$join.$where." ORDER BY t.tb_date DESC";
$data = $db->getAll($sql);
//筛取变更证书id
$biangengzhengshus = $db->getAll("select * from sp_certificate_change scc  where  scc.cgs_date between '$begindate' and '$enddate' and scc.audit_type='1101' and scc.status=1 and scc.deleted=0");
foreach ($biangengzhengshus as  $biangengzhengshu)$zsid[] = $biangengzhengshu['zsid'];
$zhengshuid   =  implode(',',array_unique($zsid));
//筛取证书id的信息
if(!empty($zhengshuid))
{
	$tmp  = array();
	foreach ($data as $value)$tmp[$value['id']] = $value;
	$data = $tmp;
	$biangeng     = $db->getAll("select t1.*,t2.work_code,t2.industry,t2.statecode,t2.areacode,t2.cta_addrcode,t2.person_tel,t2.ep_phone,t2.ep_fax,t2.delegate,t2.nature,t2.capital,t2.currency,t2.ep_amount
							from sp_certificate  t1  left join  sp_enterprises  t2 on t1.eid=t2.eid  where  t1.id in($zhengshuid) and t1.deleted=0 and t2.deleted=0"); 
	
	foreach ($biangeng as $val)
	{
		//=========================证书变更的信息
		$bgjoin = $bgselect = '';
		$bgwhere = ' where 1';
		//证书表
		$bgsql    = "select %s from `sp_certificate` c";
		$bgselect.= "c.*";
		$bgwhere .= " and c.`id`='".$val['id']."'  and c.deleted=0";
		//===========//		
		$bgselect.= ",p.id as pid,p.audit_code,p.pd_audit_code,p.audit_code_2017,p.pd_audit_code_2017,p.ct_code,p.audit_type,p.comment_b_name,p.sp_date,p.tid,p.pd_mark,p.mark as p_mark";
		$bgjoin  .= " left join sp_project p ON c.cti_id=p.cti_id";
		$bgwhere .= " and p.deleted = '0' ";
		//审核计划表
		$bgselect.= ",t.te_date,t.id as tid,tb_date";
		$bgjoin  .= " left join sp_task t ON t.id=p.tid";
		$bgwhere .= " and t.deleted = '0'";
		
		//企业表
		$bgselect.= ",e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.person_tel,e.ep_phone,e.ep_fax,e.cta_addr";
		$bgjoin  .= " left join sp_enterprises e ON e.eid=c.eid ";
		$bgwhere .= " and e.deleted = 0";
		//合同项目表
		$bgselect.= ",cti.base_num,cti.risk_level";
		$bgjoin  .= " left join sp_contract_item cti ON p.cti_id = cti.cti_id";
		$bgwhere .= " and cti.deleted = '0'";
		//拼装		
		$bgsql            = sprintf($bgsql,($bgselect=='')?'*':$bgselect).$bgjoin.$bgwhere." ORDER BY t.tb_date DESC";
		$biangengControct = $db->getOne($bgsql);
		$val['risk_level']           = $biangengControct['risk_level'];
		$val['base_num']             = $biangengControct['base_num'];
		$val['tid']             = $biangengControct['tid'];
		
		$val['audit_code_2017']      = $biangengControct['audit_code_2017']; //审核阶段
		$val['pd_audit_code_2017']   = $biangengControct['pd_audit_code_2017']; //审核阶段
		$val['audit_code']           = $biangengControct['audit_code']; //审核阶段
		$val['pd_audit_code']        = $biangengControct['pd_audit_code']; //审核阶段
		$val['comment_b_name']       = "孙伶民";//37评定人员
		$val['sp_date']              = $db->get_var("select cgs_date from sp_certificate_change where zsid='".$val['id']."' and audit_type='1101' and status=1 and deleted=0");
		$val['pd_mark']              = $val['mark'];
		$val['bg_type']              = '1';//证书为变更
		$data[$val['id']] = $val;
	}
}

foreach($data as $row)
{
	$rows = $db->getAll("select *  from `sp_task` where eid='$row[eid]' and deleted=0");
	switch ($row['audit_type']) {
		case '1002': //一阶段
		case '1003': //二阶段
			$tk_num = $rows[0]['tk_num']+$rows[1]['tk_num'];
			break;
		case '1004': //监督
			$tk_num= ($rows[0]['tk_num']>$rows[1]['tk_num'])?$rows[0]['tk_num']:$rows[1]['tk_num'];
			break;
		default:
			break;
	}
   
	//审核开始日期
	$results = $db->get_row("select t.tb_date  from `sp_task` t left join `sp_project` p on t.id=p.tid where t.eid='$row[eid]' AND p.audit_type='1002' AND p.deleted=0 AND t.deleted=0 group by t.tb_date");
	$ep_name  = $row['cert_name'];

	// $pids[]   = $row["pid"];

	if($row['audit_type']==1002)continue;

	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' and `deleted`=0");
	//子证书
	
	
	$cti_ids  = $db->get_col("select cti_id from `sp_project` where `tid`='$row[tid]' and `deleted`=0");
	$arr     = array();
	$arr[2]  = get_option("zdep_id");
	$arr[3]  = "中标华信（北京）认证中心有限公司";
	$arr[4]  = $row['cert_name'];
	$arr[5]  = '';
	$arr[6]  = $row['ep_oldname'];
	$arr[7]  = (is_numeric(substr($row['work_code'],-1,1)))?$row['work_code']:(substr($row['work_code'],0,strlen($row['work_code'])-1).substr($row['work_code'],strlen($row['work_code'])-1));
	$arr[8]  = rtrim($row['industry'],"；");
	$arr[9]  = $row['statecode'];
	$arr[10] = $row['areacode'];
	if ($row['ep_addr']==$row['ctaaddr']&&$row['ep_addr']==$row['bg_addr']) {
		$arr[11] = $row['ep_addr'];
	}else{
		$arr[11] = $row['ep_addr']."；".$row['ctaaddr']."；".$row['bg_addr'];
	}
	
	$arr[12] = $row['cta_addrcode'];
	$arr[13] = $row['person_tel'].'；'.$row['ep_phone'];
	$arr[14] = $row['ep_fax'];
	$arr[15] = $row['delegate'];
	$arr[16] = $row['nature'];
	$arr[17] = $row['capital'];
	$arr[18] = $row['currency'];
	$arr[19] = $row['ep_amount'];
	$arr[20] = $cti_info['total'];
	$arr[21] = (!$row['first_date'] || $row['first_date'] == '0000-00-00')?$row['s_date']:$row['first_date'];//21.初次获证日期;
	$arr[22] = $row['certno'];
	$arr[23] = $row['audit_ver'];
	
	//***********************//
	if($banben=='1') //旧版本
	{
		empty($row['pd_audit_code_2017'])?($row['audit_code_2017']=$row['audit_code_2017']):($row['audit_code_2017']=$row['pd_audit_code_2017']);
		if(!empty($row['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $row['audit_code_2017']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$row['audit_code'] = $codeims;
		}
	}else if($banben=='2') //新版本
	{
		empty($row['pd_audit_code'])?($row['audit_code']=$row['audit_code']):($row['audit_code']=$row['pd_audit_code']);
		if(!empty($row['audit_code']))
		{
			$codeList  = array_filter(explode('；', $row['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$row['audit_code'] = $codeims;
		}	
	}
	
	//***********************//
	$arr[24] = get_code($row['audit_code']);
	$arr[25] = $audit_ver_array[$row['audit_ver']]['audit_basis'];
	//多场所
	$duochangsuo  = $db->getAll("select * from sp_enterprises_site  es where eid =".$row['eid']." and deleted = '0' ");
	$gudingduochangsuo = '0';
	$duochangsuoAddr   = '';
	foreach($duochangsuo as $key=> $temp)
	{
		if($temp['es_type']=='1000')
		{
			$gudingduochangsuo = '1';
		}else{
			$gudingduochangsuo = '0';
		}
		$duochangsuoAddr  .=$temp['es_addr'].'；';
	}
//	$arr[26] = $row['union_count']>0?"1":"0";
    $arr[26] = $gudingduochangsuo;
	$arr[27] = $duochangsuoAddr;
	$arr[28] = $row['cert_scope'];
	$arr[29] = ($row['audit_ver']=="A010201")?$row['cert_scope']:'';
	$arr[30] = '';
	$shenhecode = '';
	$zhengshubiangeng = $db->get_row("select * from sp_certificate_change where zsid =".$row['id']." and audit_type ='1101' and deleted =0");
//	if($row['bg_type']=='1')
//	{
//		$row['audit_type']  = $db->get_var("select audit_type from sp_project where ct_id='".$row['ct_id']."' and deleted=0 group by id  desc");
//	}
		switch ($row['audit_type']) {
		case '1002': //一阶段
		case '1003': //二阶段
			 $shenhecode = '01';
			break;
		case '1007': //再认证
			 $shenhecode = '02';
			break;
		case '1004':  //监一 监二
		case '1005':
			 $shenhecode = '03';
			break;
		case '1009':  //特殊审核
			 $shenhecode = '04';
			break;
		default:
		if(!empty($zhengshubiangeng))
			$shenhecode = '05';	 
		break;
	}
	$arr[31] = $shenhecode;
	$arr[32] = $cti_info['renum'];
	$arr[33] = get_audit_num($row[audit_type]);

	if($row['audit_type']=='1003')
	{
		$arr[34] = substr($results['tb_date'],0,10);
	}else{
		
		$arr[34] = substr($row['tb_date'],0,10);
	}
	$arr[35] = substr($row['te_date'],0,10);
	//判断是否为证书变更，若为证书变更 则默认为空 否则照常
	
	if($row['bg_type']=='1'){
		$arr[34] = '';
		$arr[35] = '';
		$arr[36] = '';
	}else{
		$arr[36] = $tk_num;
	}
	
	//判断结束
	$arr[37] = "01";
	$cti_ids=$db->get_col("SELECT cti_id FROM `sp_project` WHERE `tid` = '$row[tid]' AND `deleted` = '0'");
	if(count(array_unique($cti_ids))==2)$arr[37] ="02";
	if(count(array_unique($cti_ids))==3)$arr[37] ="03";
	if(count(array_unique($cti_ids))>3)$arr[37] ="04";
	$arr[38] = $row['comment_b_name'];
	$arr[39] = $row['sp_date'];
	$arr[40] = $row['s_date'];
	$arr[41] = $row['e_date'];
	$arr[42] = $row['status'];
	//======================证书暂停类型*****************//
	//判断此证书处于 暂停期 输出暂停时间
	$gq  = $db->getAll("select cg_type from sp_certificate_change where zsid = ".$row['id']."  and cgs_date<='$enddate' and deleted=0");
	$arr1= $arr2 ='0';
	foreach ($gq as $a) 
	{
		if ($a['cg_type'] == '97_01')$arr1++;
		if ($a['cg_type'] == '97_02')$arr2++;
	}
    if ($arr1>$arr2)
    {
		$zantingyuanyin = $db->get_row("select * from sp_certificate_change where zsid =".$row['id']." and cg_type ='97_01' and deleted =0 group by id desc");
   		if(!empty($zantingyuanyin))
		{
			$arr[43] = $zantingyuanyin['cg_reason'];//暂停原因 
			$arr[44] = $zantingyuanyin['cgs_date'];//暂停开始时间
			$arr[45] = $zantingyuanyin['cge_date'];//暂停结束时间
		}
    }else{
		$arr[43] = '';
		$arr[44] = '';
		$arr[45] = '';
	}
	//======================证书暂停类型结束*****************//
	//======================证书撤销类型开始*****************//
	$chexiaoyuanyin =  $db->get_row("select * from sp_certificate_change where zsid =".$row['id']." and cg_type ='97_03' and deleted =0 group by id desc");
	if(!empty($chexiaoyuanyin))
	{
		$arr[46]        =  $chexiaoyuanyin['cg_reason']; //撤销原因
		$arr[47]        =  $chexiaoyuanyin['cgs_date'];  //撤销时间
	}else{
		$arr[46]        =  '';
		$arr[47]        =  '';
	}
	$arr[48]            =  '0';
	//======================证书撤销类型结束*****************//
	//变更时间 变更代码*****************// 
    $biangengdate       =  $db->get_row("select * from sp_certificate_change where zsid =".$row['id']."  and deleted =0 group by id desc");
    if(!empty($biangengdate))
    {
		$arr[50]        =  $biangengdate['cgs_date'];
    	$daimaarr       =  $db->getAll("select * from sp_certificate_change where zsid =".$row['id']."  and deleted =0");	
	    $countdaimaarr  = array('0101','0102','0104','0105','0106','0107','0108','0109','0110','0196','0103');
	  	$daima          = array();
	    foreach ($daimaarr as  $daimaarrs) 
	    {
	    	switch ($daimaarrs['cg_type']) {
	    		case '103':
	    		case '104':
	    		case '105':
	    		case '106':
	    		case '107':
	    		case '108':
	    		case '109':
	    		case '110':
	    		case '196':
	    		case '199':
	    			$daimaarrs['cg_type'] = '0'.$daimaarrs['cg_type'];
	    			break;
				case '97_01':
				case '97_02':
				case '97_03':
					$daimaarrs['cg_type'] = '0197';
	    		default:break;
	    	}
	    	$daima []= $daimaarrs['cg_type']; 
	    }
    	$arr[51]      =  implode('；',array_filter(array_unique($daima)));
	    //判断本身的代码是否在总的代码里 存在返回1 不存在返回0
	   if(!empty($daima))
	   {
		   	foreach($daima as $item)
		    {
		   		if(in_array($item, $countdaimaarr))
		   		{
		   			 $bgdaima ='1';break;
		   		}else{
					 $bgdaima ='0';break;
		   		}
		    }
	   }
	   $arr[52]  = $bgdaima;
    }else{
    	$arr[50] = ''; //变更时间
    	$arr[51] = ''; //变更类型代码
    	$arr[52] = '0'; //是否换证
    }

    

    	
    
	//变更时间 变更代码*************结束****// 
	// $arr[48] = ($row['union_count']==0)?'0':'1';
	// $arr[49] = ($row['union_count']==0)?'':$row['main_certno'];
	// if($row['main_certno']){
	// 	$arr[48] = '1';
	// 	$arr[49] = $row['main_certno'];
	// }

	// $arr[52] = $row['is_change'];
	$arr[53] = ($row['change_date']=='0000-00-00')?$row['change_date']='':$row['change_date']=$row['change_date'];
	$arr[54] = $row['change_type'];
	$arr[55] = $row['old_cert_name'];
	$arr[56] = $row['old_certno'];
	// if($row['is_change']==0){
	// 	$arr[53]=$arr[54]=$arr[55]=$arr[56]='';
	// }



	$arr[57] = ($row['pd_mark']==01)?'CNAS C182-M':'';
	$arr[58] = ($row['pd_mark']==01)?'01':'';
	$arr[59] = $row['risk_level'];

	$arr[60] = "";
	$arr[61] = "01";
	$arr[62] = $row['ct_code'];
	$invoice_query = $db->query("SELECT * FROM `sp_contract_cost_detail` WHERE `ct_id` = '$row[ct_id]' AND `deleted` = '0' AND `iso` LIKE '%$row[iso]%' AND audit_type LIKE '%$row[audit_type]%' AND `invoice` <> '' ");
	if($invoice_query){
		$invoice      = "";
		$invoice_cost = 0.00;
		while($_r=$db->fetch_array($invoice_query)){
			$invoice      .= $_r['invoice']."；";
			$invoice_cost += $_r['invoice_cost'];	
		}
		$arr[60] = $invoice_cost;
		$arr[62] = trim($invoice,'；');
	}
	!$arr[60] && $arr[60] = $db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso like '%$row[iso]%' AND cost_type like '%$row[audit_type]%'");//收费金额
	!$arr[62] && $arr[62] = $row['ct_code'];//收费发票号
	$arr[60]  = sprintf("%.2f", $arr[60]);

	
	$arr[63] = "";

	if($row['bg_type']=='1'){
		$arr[60] = "";//判断是否为变更证书 变更证书默认为0
		$arr[61] = "";//判断是否为变更证书 变更证书默认为0
		$arr[62] = "";
		$arr[67] = "0";//判断是否为变更证书 变更证书默认为0
	}else{
		$arr[62] = $row['ct_code'];
		if ($row['pd_mark']==01) 
		{
			$arr[67] = "0";
			$ct_id = $db->get_var("select ct_id from `sp_project` where `tid`='$row[tid]' and `deleted`=0");
			$comment_date = $db->get_var("SELECT comment_date FROM `sp_project` where `ct_id`='$ct_id' and `iso`='$row[iso]' and `deleted`=0 limit 1,1;");
			$date=date_create($comment_date);
			date_add($date,date_interval_create_from_date_string("1 year"));
			$point_date = date_format($date,"Y-m-d");
			if ($point_date<substr($row['te_date'],0,10)) {
				$arr[67] = "1";
			}
		}
	}
	
    //=== 上报类型 =**********************//
		$shangbaotype=0;
    	switch ($row['audit_type']) 
    	{
	    	case '1001':
	    	case '1002':
	    	case '1003':
	    		$shangbaotype =  '01';         //初次发证
	    		break;
	    	default:
	    	$temarr1  = array('0101','0102','0103','0104','0105','0106','0107','0108','0109','0110','0196'); //换发证书（监督换证、再认证换证、转机构换证、证书信息变更换证）
	    	$temarr2  = array('0199'); //证书信息变更
	    	$temarr3  = array('0197'); //证书状态变化
	    	$fivearr  = array_unique(explode('；', $arr[51]));    //数据库所存在值
            if(empty($arr[51]))
            {
            	$shangbaotype =  '03';                   // 报送监督检查信息
            }else{
            	foreach( $fivearr as $item)
	            {
	            	if(in_array($item, $temarr1))
	            	{
					     $shangbaotype =  '02';break; // 报送监督检查信息
	            	}else if(in_array($item, $temarr2))
	            	{
	            		 $shangbaotype =  '04';break; // 证书信息变更
	            	}else if(in_array($item, $temarr3))
	            	{
	            		 $shangbaotype =  '05';break; // 证书状态变化
	            	}
	            }
            }
			break;
	    }
	$arr[65] = $shangbaotype;
	//=== 上报类型结束 =**********************//
	$arr[72] = $row['base_num'];
	$arr[73] = substr($begindate,0,10);
	$arr[74] = substr($enddate,0,10);

	$arr_data_zsInfo[] = $arr;
    
}
/**===========证书=======**/


/**===========证书审核组=============**/
//===================//
if (!empty($arr_cg_pid)) {
	$where1 = " and p.deleted = '0' and (p.sp_date between '$begindate' and '$enddate' or p.id in ($arr_cg_pid))";
}else{
	$where1 = " and p.deleted = '0' and p.sp_date between '$begindate' and '$enddate'";
}
$projectList   = $db->getAll("select p.id as pid
			from `sp_certificate` c left join sp_project p ON c.cti_id=p.cti_id 
			left join sp_task t ON t.id=p.tid 
			left join sp_enterprises e ON e.eid=c.eid  
			left join sp_contract_item cti ON p.cti_id = cti.cti_id 
			where 1 
			and c.`status`='01' 
			and c.deleted=0 
			and p.audit_type not in('1001')  
			and t.deleted = '0' 
			and e.deleted = 0 
			and cti.deleted = '0' 
			$where1
			ORDER BY t.tb_date DESC");
foreach ($projectList as $project) 
{
	$pids[]  = $project['pid'];
}
//=========以上为取Pid==========//
$pids[] = -1;
$pids = array_filter(array_unique($pids));

//sql初始化
$join = $select = '';$where = ' where 1';

//审核计划派人明细表
$sql    = "select %s from `sp_task_audit_team` tat";
$select.= "tat.iso,tat.pid,tat.uid,tat.tid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.taskBeginDate,tat.taskEndDate,tat.name,tat.use_code,tat.audit_code,tat.audit_code_2017";
$where .= " and tat.pid IN (".implode(',',$pids).") and tat.deleted = 0";

//人员表
$select.= ",hr.card_type,hr.card_no,hr.audit_job,hr.id as uid";
$join  .= " left join sp_hr hr ON hr.id = tat.uid";
$where .= " and hr.deleted = '0'";

//审核项木表
$select.= ",p.cti_id,p.eid";
$join  .= " left join sp_project p ON p.id = tat.pid";
$where .= " and p.deleted = '0'";  //and ct.is_site ='1'
//合同表
$select .= ",ct.is_site";
$join  .= " left join sp_contract ct ON ct.ct_id = p.ct_id";
// $where .= " and  ct.is_site='1' and ct.deleted = '0'";
$where .= " and ct.deleted = '0'";
//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;

$query = $db->query( $sql );
while( $row = $db->fetch_array( $query ) )
{
	if($row['audit_type']=='1002'&&$row['is_site']=='0')continue; //屏蔽一阶段的 不实施现场审核的项目
	
	$a13=0;$a15="";$arr = array();
//	if($row['qua_type']!='03' and $row['use_code'])$a13=1;
	if($row['witness'])$a14="01";

	$_query = $db->query("select witness_person FROM `sp_task_audit_team` where `tid` =".$row['tid']." and `witness_person` IS NOT NULL and `witness_person`<>'' AND deleted=0");
	while($r=$db->fetch_array( $_query )){
		if(strpos($r['witness_person'],$row['name']))$a14="02";
	}
	$cert_query=$db->query("select id,certno from sp_certificate where cti_id = ".$row['cti_id']." and  eid='$row[eid]' and deleted=0");
	while($_r1=$db->fetch_array( $cert_query ))
	{	
		$gq=$db->getAll("select cg_type from sp_certificate_change where zsid = ".$_r1['id']."  and cgs_date<='$enddate' and deleted=0");

		$arr1='0';
		$arr2='0';
		foreach ($gq as $a) 
		{
			if ($a['cg_type'] == '97_01') {
				$arr1++;
			}
			if ($a['cg_type'] == '97_02') {
				$arr2++;
			}
			
		}
		if ($arr1>$arr2)continue;
			
		
			
		$arr[2] = get_option("zdep_id");
		$arr[3] = $_r1['certno'];
		$arr[4] = get_audit_type_auditor($row['audit_type']);
		$arr[5] = $row['taskBeginDate'];
		$arr[6] = $row['taskEndDate'];
		$arr[7] = $row['name'];
		$arr[8] = $row['card_type'];
		$arr[9] = $row['card_no'];

		$arr[10] = substr($row['role'],-2);
		if($row['role']=='1004')$arr[10]='';
		if($row['qua_type']=='04')$arr[10]='03';
		
		$arr[11] = $row['qua_type'];
		$zige  = $db->get_row("select * from sp_task_audit_team where tid =".$row['tid']." and  `uid`=".$row['uid']." and `iso` = '".$row['iso']."' and deleted=0");

		if($zige['qua_type']=='04'){
			$code1 = $db->get_var("select code from `sp_hr` where `id`=".$row['uid']." and deleted=0");
		}else{
			$code1 = $db->get_var("select qua_no from `sp_hr_qualification` where `uid`=".$row['uid']." and `iso` = '".$row['iso']."' and qua_type =".$zige['qua_type']." and  status=1");
		}
		$arr[12] = $code1;
		//04专业码结束
		if($banben=='1'&&!empty($row['audit_code_2017']))//（旧版本）
		{
			$arr[13] = '1';	
		}elseif ($banben=='2' && !empty($row['audit_code'])){
			$arr[13] = '1';	
		}else{
			$arr[13] = '0';	
		}
		
		$arr[14] = ($row['audit_job']==1)?1:0;
		//判断是否见证
		$arr_jz=array(
			'0'=>"1",
			'1'=>"5",
			'2'=>"7",
			'3'=>"8"
			);

		$sql="select witness_person,iso from sp_task_audit_team where tid=".$row['tid']." and iso='".$row['iso']."' and deleted=0";
		$witness_person=$db->getAll($sql);
		foreach ($witness_person as $key => $val) {
			if ($val['witness_person']!='0') {
				$witness_person_id[$key]=$val['witness_person'];
				$iso[$key]=$val['iso'];
			}
		}
		if (in_array($row['witness'],$arr_jz)) {
			$a15='01';
		}elseif (in_array($row['uid'],$witness_person_id)&&in_array($row['iso'],$iso)) {
			$a15='02';
		}else{
			$a15='';
		}
		// echo "<pre />";
		// print_r($witness_person_id);exit;
		$arr[15] = $a15;
		//判断是否见证结束
		$arr_data_shInfo[] = $arr;
	}
}
/**===========证书审核组=============**/


/**=============导出xls==============**/
require_once ROOT.'/theme/Excel/myexcel.php';
$tmplate = CONF.'templ.xls';
$tmpName = $ep_name.getgp('s_date').'-'.getgp('e_date').'月报.xls';
$tmpPath = 'data/report_data/';
$excel   = new Myexcel($tmplate);
$excel  -> addDataFromArray($arr_data_zsInfo,8);
$excel  -> setIndex(1);
$excel  -> addDataFromArray($arr_data_shInfo,8);
$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
echo $saveName['oldName'];
exit;
/**=============导出xls==============**/


/**===========用到的函数=============**/
function get_code($code){
	$code=str_replace(array(";",",","，"," "),"；",$code);
	$codes=explode("；",$code);
	foreach($codes as $k=>$v){
		$codes[$k]=substr($v,0,8);

	}
	$codes=array_unique($codes);
	return join("；",$codes);
}
function get_audit_type($audit_type){
	if($audit_type=='1001'||$audit_type=='1002'||$audit_type=='1003'){
		return '01';
	}else if($audit_type=='1004'||$audit_type=='1005'||$audit_type=='1006'){
		return '0301';
	}else if($audit_type=='1007'){
		return '02';
	}else if($audit_type=='1008'||$audit_type=='1009'||$audit_type=='1010'){
		return '0302';
	}else{
		return '0302';
	}
}
function get_audit_num($audit_type){
	$num=0;
	switch($audit_type){
		case '1004':$num=1;break;
		case '1005':$num=2;break;
		case '1006':$num=3;break;
		case '1011':$num=4;break;
		case '1012':$num=5;break;
		case '1013':$num=6;break;
		case '1014':$num=7;break;
		case '1015':$num=8;break;
		case '1016':$num=9;break;
		case '1017':$num=10;break;
		case '1019':$num=11;break;
		case '1020':$num=12;break;
		case '1021':$num=13;break;
		case '1022':$num=14;break;
		default :$num=0;break;
	
	
	}
	return $num;
}
//注：还有一个类型是05变更，需要在程序里判断
function get_audit_type_auditor($audit_type){
	if($audit_type=='1002'){
		return '0101';//初审一阶段
	}else if($audit_type=='1003'){
		return '0102';
	}else if($audit_type=='1007'){
		return '0202';
	}else if($audit_type=='1004' || $audit_type=='1005'){
		return '03';
	}else{
		return '04';//变更在特殊监管  04
	}
}
/**===========用到的函数=============**/
?>
