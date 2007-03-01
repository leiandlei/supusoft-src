<?php
set_time_limit(0);
$a58 = $begindate = getgp('s_date');//58.取数开始日期
$a59 = $enddate = getgp('e_date');//59.取数截止日期
$data_arr = array();
$pids = array();

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
$arr['a1'] = get_option('zdep_id');
$arr['a2'] = get_option('zdep_id');




/*


//变更信息
$sql = "SELECT z.status,z.s_date,z.e_date,z.iso,z.ct_id,z.cert_scope,z.main_certno ,z.first_date ,z.certno,z.cti_id,z.cert_addr,z.mark,z.audit_code,
c.cg_type_report,c.audit_type,c.cgs_date,c.cge_date,c.cg_reason,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate  
FROM `sp_certificate_change` c LEFT JOIN sp_certificate z on z.id=c.zsid LEFT JOIN sp_enterprises e on e.eid=z.eid
WHERE c.deleted='0' and c.cgs_date >= '$begindate' and c.cgs_date <= '$enddate' and c.cg_type_report IN ('04','05','06','97')  ORDER BY c.id DESC ";


$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$cti_info = $db->get_row("select total from sp_contract_item where cti_id='$row[cti_id]' ");
	$p_info = $db->get_row("select st_num,id,cti_id,ct_id,tid from sp_project where ct_id='$row[ct_id]' and audit_type='$row[audit_type]' and iso='$row[iso]' ");
	$pid = $p_info['id'];
	$task_info = $db->get_row("select tb_date,te_date from sp_task t  where t.id='$p_info[tid]' ");


	$arr['a2'] = f_ctfrom($row['ctfrom']);
	$arr['a2']=='' && $arr['a2'] = $arr['a1'];
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $row['ep_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $row['mark'];			//7.认可标志
	$arr['a8'] = $row['audit_code'];	//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $row['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr['a15'] = $row['delegate']; 	//15.组织法定代表人
	$arr['a16'] = $row['nature']; 		//16.组织性质代码
	$arr['a17'] = $row['capital']; 		//17.组织注册资本
	$arr['a18'] = $row['currency'];		//18.组织注册资本币种
	$arr['a19'] = $row['ep_amount']; 	//19.组织组织人数
	$arr['a20'] = $cti_info['total']; 	//20.体系人数
	$arr['a21'] = ($row['first_date'] == '0000-00-00')?'':$row['first_date']; 	//21.初次获证日期
	$arr['a22'] = $row['certno']; 		//22.证书号码
	$arr['a23'] = '0'; 					//23.是否子证书
	$arr['a24'] = ''; 					//24.主证书
	if($arr['main_certno']){
		$arr['a23'] = '1'; 				//23.是否子证书
		$arr['a24'] = $arr['main_certno']; 	//24.主证书
	}
	$arr['a25'] = '0'; 					//25.是否多现场
	$arr['a26'] = $row['audit_ver'];	//26.认证项目代码

	$arr['a27'] = '';					//27.其它时认证代码说明
	$arr['a28'] = $row['audit_code']; 	//28.业务范围代码
	$arr['a29'] = $row['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = '04';						//30.因为都是变更数据 所以认证活动代码为04
	$arr['a31'] = '97';					//31.变更类型代码
	$arr['a32'] = get_audit_type($row['audit_type']);	//32.变更时所处的审核活动期间
	$arr['a33'] = '';					//33.书否换证
	$arr['a34'] = ''; 					//34.换证原因
	$arr['a35'] = ''; 					//35.原证书注册号
	$arr['a36'] = ''; 					//36.原证书颁发机构
	$arr['a37'] = ''; 					//37.换证日期-根据新月报规则 不可能有换这个日期，所以以下处理
	$arr['a38'] = ''; 					//38.复评次数
	$arr['a39'] = '';
	if($arr['a32']=='0301'||$arr['a32']=='0302'){
		if($row['audit_type']=='1004'){
			$arr['a39'] = '1';			//39.监督次数
		}elseif($row['audit_type']=='1005'){
			$arr['a39'] = '2';			//39.监督次数
		}elseif($row['audit_type']=='1006'){
			$arr['a39'] = '3';			//39.监督次数
		}
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $p_info['st_num'];		//42.审核人日
	$sql = "select iso from sp_task where id='$p_info[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//42.是否结合审核
	if(strstr($num, ',')){
		$arr['a43'] = '1';
	}

	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_assess WHERE pid = '$task_info[pid]'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	//$arr['a44'] = implode('；',$hr_arr);		//评定人员
	$arr['a45'] = '';					//收费金额
	$arr['a46'] = '';					//收费币种
	$arr['a47'] = '';					//收费发票号
	$sql = "select meta_value from sp_metas_ot where used = 'contract_item' and meta_name = 'risk_level' and ID='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $row['s_date'];		//证书发证日期
	$arr['a50'] = $row['e_date'];		//证书到期日期
	$arr['a51'] = sprintf('%02d',$row['status']);	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	if($arr['a51']=='02'){
		$row['a52'] = $row['cg_reason'];//暂停原因
		$arr['a53'] = $row['cgs_date'];	//暂停开始时间
		$arr['a54'] = $row['cge_date'];	//暂停结束时间
	}else if($arr['a51']=='03'){
		$arr['a55'] = $row['cg_reason'];//撤销原因
		$arr['a56'] = $row['cgs_date'];	//撤销日期
	}
	$arr['a57'] = $row['cgs_date'];		//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$data_arr[] = $arr;
}



*/

//评定 （评定通过，监督类型,非换证(又取消 认监委的换证指的是证书号码变化)）
//and a.if_cert>'y'



$sql = "SELECT e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,
a.pid,a.tid,a.ct_id,a.cti_id,a.audit_type,
p.ccd_id ,p.tid,p.st_num 
FROM sp_assess a LEFT JOIN sp_enterprises e on e.eid=a.eid LEFT JOIN sp_project p on p.id=a.pid WHERE a.deleted='0' and a.pd_type='1' and a.audit_type IN ('1004','1005','1006') and a.assess_date >= '$begindate' and a.assess_date <= '$enddate' group by a.id ORDER BY a.assess_date DESC";

$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$sql = "select * from sp_certificate where cti_id='$row[cti_id]' and is_check='y' and status IN (1,2,3,4) order by id desc limit 1";
	$zs_info = $db->get_row($sql);

	$cti_info = $db->get_row("select total from sp_contract_item where cti_id='$row[cti_id]' ");
	//echo "select id from sp_project where ct_id='$row[ct_id]' and audit_type='$row[audit_type]' and iso='$row[iso]' <br>";
	$pid = $row['pid'];
	$pids[] = $pid;
	//echo "select * from sp_task t left join sp_task_vice tv on tv.tid=t.id where t.id='$row[tid]' and tv.pid='$pid' <br>";
	$task_info = $db->get_row("select tb_date,te_date from sp_task where id='$row[tid]' ");
	//tb_date,te_date
	//print_rr($task_info);
	//$arr['a2']=='' && $arr['a2'] = $arr['a1'];
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $row['ep_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $zs_info['mark'];		//7.认可标志
	$arr['a8'] = $zs_info['audit_code'];//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $zs_info['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr['a15'] = $row['delegate']; 	//15.组织法定代表人
	$arr['a16'] = $row['nature']; 		//16.组织性质代码
	$arr['a17'] = $row['capital']; 		//17.组织注册资本
	$arr['a18'] = $row['currency'];		//18.组织注册资本币种
	$arr['a19'] = $row['ep_amount']; 	//19.组织组织人数
	$arr['a20'] = $cti_info['total']; 	//20.体系人数
	$arr['a21'] = $zs_info['first_date']; 	//21.初次获证日期
	if(!$arr['a21'])$arr['a21']= $zs_info['s_date'];
	$arr['a22'] = $zs_info['certno']; 		//22.证书号码
	$arr['a23'] = '0'; 					//23.是否子证书
	$arr['a24'] = ''; 					//24.主证书
	if($zs_info['main_certno']){
		$arr['a23'] = '1'; 				//23.是否子证书
		$arr['a24'] = $zs_info['main_certno']; 	//24.主证书
	}
	$arr['a25'] = '0'; 					//25.是否多现场
	$arr['a26'] = $zs_info['audit_ver'];	//26.认证项目代码
	$arr['a27'] = '';					//27.其它时认证代码说明
	$arr['a28'] = $zs_info['audit_code']; 	//28.业务范围代码
	$arr['a29'] = $zs_info['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = get_audit_type($row['audit_type']);	//30.活动代码
	$arr['a31'] = '';					//31.变更类型代码(评定)
	$arr['a32'] = '';					//32.变更时所处的审核活动期间
	$arr['a33'] = '';					//33.书否换证
	$arr['a34'] = ''; 					//34.换证原因
	$arr['a35'] = ''; 					//35.原证书注册号
	$arr['a36'] = ''; 					//36.原证书颁发机构
	$arr['a37'] = ''; 					//37.换证日期-根据新月报规则 不可能有换这个日期，所以以下处理
	$arr['a38'] = ''; 					//38.复评次数
	$arr['a39'] = '';
	if($arr['a32']=='0301'||$arr['a32']=='0302'){
		if($row['audit_type']=='1004'){
			$arr['a39'] = '1';			//39.监督次数
		}elseif($row['audit_type']=='1005'){
			$arr['a39'] = '2';			//39.监督次数
		}elseif($row['audit_type']=='1006'){
			$arr['a39'] = '3';			//39.监督次数
		}
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $row['st_num'];		//42.审核人日
	$sql = "select iso from sp_task where id='$row[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//42.是否结合审核
	if(strstr($num,'.')>1){
		$arr['a43'] = '1';
	}
	/*
	$sql = "select name from sp_assess a left join sp_assess_person ap on ap.pd_id=a.id left join sp_hr h on ap.pd_uid=h.id where a.pid='$row[pid]' ";
	$in_res = $db->query($sql);
	$hr_arr = array();
	while($in_row = $db->fetch_array($in_res)){
		$hr_arr[] = $in_row['name'];
	}
	*/
	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_assess WHERE pid = '$row[pid]'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	//$arr['a44'] = implode('；',$hr_arr);		//评定人员
	$sql = "select invoice,invoice_cost ,currency from sp_contract_cost_detail where id='$row[ccd_id]' ";
	$cw_info = $db->get_row($sql);
	$arr['a45'] = $cw_info['invoice_cost'];	//收费金额
	$arr['a46'] = $cw_info['currency'];		//收费币种
	$arr['a47'] = $cw_info['invoice'];		//收费发票号
	
	
	$sql = "select meta_value from sp_metas_ot where used = 'contract_item' and meta_name = 'risk_level' and ID='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $zs_info['s_date'];		//证书发证日期
	$arr['a50'] = $zs_info['e_date'];		//证书到期日期
	$arr['a51'] = sprintf('%02d',$zs_info['status']);	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	$arr['a57'] = '';					//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$data_arr[] = $arr;
}

// 新发证书 （）
$sql="select z.status,z.s_date,z.e_date,z.iso,z.ct_id,z.cert_scope,z.main_certno ,z.first_date ,z.certno,z.cti_id,z.cert_addr,z.mark,z.audit_code,z.pid,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate
from sp_certificate z LEFT JOIN sp_enterprises e on z.eid=e.eid
where z.is_check='y' and z.report_date >='".$begindate."' and z.report_date <='".$enddate."' and (z.status='1' or z.status='2' or z.status='4' or z.status='5') order by z.report_date desc";


$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$cti_info = $db->get_row("select total from sp_contract_item where cti_id='$row[cti_id]' ");
	//echo "select id from sp_project where ct_id='$row[ct_id]' and audit_type='$row[audit_type]' and iso='$row[iso]' <br>";
	$pid = $row['pid'];
	$pids[] = $pid;
	$p_info = $db->get_row("select st_num,id,cti_id,ct_id,tid from sp_project where id='$row[pid]'  ");
	//echo "select * from sp_task t left join sp_task_vice tv on tv.tid=t.id where t.id='$row[tid]' and tv.pid='$pid' <br>";
	$task_info = $db->get_row("select tb_date,te_date from sp_task where id='$p_info[tid]' ");
	//tb_date,te_date
	//print_rr($task_info);
	//$arr['a2'] = f_ctfrom($row['ctfrom']);
	//$arr['a2']=='' && $arr['a2'] = $arr['a1'];
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $row['ep_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $row['mark'];		//7.认可标志
	$arr['a8'] = $row['audit_code'];//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $row['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_name from sp_metas_ot where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr['a15'] = $row['delegate']; 	//15.组织法定代表人
	$arr['a16'] = $row['nature']; 		//16.组织性质代码
	$arr['a17'] = $row['capital']; 		//17.组织注册资本
	$arr['a18'] = $row['currency'];		//18.组织注册资本币种
	$arr['a19'] = $row['ep_amount']; 	//19.组织组织人数
	$arr['a20'] = $cti_info['total']; 	//20.体系人数
	$arr['a21'] = $row['first_date']; 	//21.初次获证日期
	if(!$arr['a21'])$arr['a21']= $row['s_date'];
	$arr['a22'] = $row['certno']; 		//22.证书号码
	$arr['a23'] = '0'; 					//23.是否子证书
	$arr['a24'] = ''; 					//24.主证书
	if($row['main_certno']){
		$arr['a23'] = '1'; 				//23.是否子证书
		$arr['a24'] = $row['main_certno']; 	//24.主证书
	}
	$arr['a25'] = '0'; 					//25.是否多现场
	$arr['a26'] = $row['audit_ver'];	//26.认证项目代码
	$arr['a27'] = '';					//27.其它时认证代码说明
	$arr['a28'] = $row['audit_code']; 	//28.业务范围代码
	$arr['a29'] = $row['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = get_audit_type($row['audit_type']);	//30.活动代码
	$arr['a31'] = '';					//31.变更类型代码(评定)
	$arr['a32'] = '';					//32.变更时所处的审核活动期间
	$arr['a33'] = '';					//33.书否换证
	$arr['a34'] = ''; 					//34.换证原因
	$arr['a35'] = ''; 					//35.原证书注册号
	$arr['a36'] = ''; 					//36.原证书颁发机构
	$arr['a37'] = ''; 					//37.换证日期-根据新月报规则 不可能有换这个日期，所以以下处理
	$arr['a38'] = ''; 					//38.复评次数
	$arr['a39'] = '';
	if($arr['a32']=='0301'||$arr['a32']=='0302'){
		if($row['audit_type']=='1004'){
			$arr['a39'] = '1';			//39.监督次数
		}elseif($row['audit_type']=='1005'){
			$arr['a39'] = '2';			//39.监督次数
		}elseif($row['audit_type']=='1006'){
			$arr['a39'] = '3';			//39.监督次数
		}
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $p_info['st_num'];		//42.审核人日
	$sql = "select iso from sp_task where id='$p_info[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//42.是否结合审核
	if(strstr($num,'.')){
		$arr['a43'] = '1';
	}
	/*
	$sql = "select name from sp_assess a left join sp_assess_person ap on ap.pd_id=a.id left join sp_hr h on ap.pd_uid=h.id where a.pid='$row[pid]' ";
	$in_res = $db->query($sql);
	$hr_arr = array();
	while($in_row = $db->fetch_array($in_res)){
		$hr_arr[] = $in_row['name'];
	}
	*/
	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_assess WHERE pid = '$row[pid]'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	$sql = "select invoice,invoice_cost ,currency from sp_contract_cost_detail where id='$row[ccd_id]' ";
	$cw_info = $db->get_row($sql);
	$arr['a45'] = $cw_info['invoice_cost'];	//收费金额
	$arr['a46'] = $cw_info['currency'];		//收费币种
	$arr['a47'] = $cw_info['invoice'];		//收费发票号
	
	
	$sql = "select meta_value from sp_metas_ot where used = 'contract_item' and meta_name = 'risk_level' and ID='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $zs_info['s_date'];		//证书发证日期
	$arr['a50'] = $zs_info['e_date'];		//证书到期日期
	$arr['a51'] = sprintf('%02d',$zs_info['status']);	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	$arr['a57'] = '';					//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$data_arr[] = $arr;
}



//审核组信息
$pids = array_unique( $pids );
$auditors = array();

$fields = "tat.iso,tat.pid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.is_leader,";
$fields .= "p.cti_id,";
$fields .= "ta.taskBeginDate,ta.taskEndDate,ta.name,";
$fields .= "hr.card_type,hr.card_no,hr.audit_job,";
$fields .= "hqa.qua_no";


$join = " LEFT JOIN sp_task_auditor ta ON ta.id = tat.auditor_id";
$join .= " LEFT JOIN sp_hr hr ON hr.id = ta.uid";
$join .= " LEFT JOIN sp_project p ON p.id = tat.pid";
$join .= " LEFT JOIN sp_hr_qualification hqa ON (hqa.uid = ta.uid AND hqa.iso = tat.iso AND hqa.qua_type = tat.qua_type)";

if(sizeof($pids)==0) {
	$pids = array('-1');
}
$where = " AND tat.pid IN (".implode(',',$pids).") AND tat.deleted = 0";

$audit_types = array( '1002', '1003', '1004', '1005', '1007', '1008' );
$audit_type_pattrens = array( '01', '02', '0301', '0301', '04', '0302' );


$sql = "SELECT $fields FROM sp_task_audit_team tat $join WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$auditor = array(
		'a1'	=> $arr['a1'],
		'a2'	=> $db->get_var("SELECT certno FROM sp_certificate WHERE cti_id = $rt[cti_id] AND status = 1 LIMIT 1"),
		'a3'	=> $rt['taskBeginDate'],
		'a4'	=> ( ( $at = str_replace( $audit_types, $audit_type_pattrens, $rt['audit_type'] ) ) == $rt['audit_type'] ? '00' : $at ),
		'a5'	=> $rt['taskEndDate'],
		'a6'	=> $rt['name'],
		'a7'	=> $rt['card_type'],
		'a8'	=> $rt['card_no'],
		'a9'	=> str_replace( '1001', '01', $rt['qua_type'] ),
		'a10'	=> $rt['qua_no'],
		'a11'	=> ( $rt['is_leader'] ? '01' : ( '1004' == $rt['role'] ? '03' : '02' ) ),
		'a12'	=> ( '1002' == $rt['role'] ? 1 : 0 ) ,
		'a13'	=> ( 1 == $rt['audit_job'] ? 1 : 0),
		'a14'	=> ( 1 == $rt['witness'] ? '01' : '02' )
	);
	$auditors[] = $auditor;
}



//输出Access

$cert_arr = array(
	'ZDEP_ID'		=> 'a1',
	'ZSUBAUDORG'	=> 'a2',
	'ZORGNAME'		=> 'a3',
	'ZORGNAMEENG'	=> 'a4',
	'ZORGOLDNAME'	=> 'a5',
	'ZORGID'		=> 'a6',
	'ZAUDFLAGS'		=> 'a7',
	'ZTRADES'		=> 'a8',
	'ZCouCode'		=> 'a9',
	'ZAreaCode'		=> 'a10',
	'ZORGADDR'		=> 'a11',
	'ZORGZIP'		=> 'a12',
	'ZORGTEL'		=> 'a13',
	'ZORGFAX'		=> 'a14',
	'ZORGLEADER'	=> 'a15',
	'ZORGTYPEID'	=> 'a16',
	'ZORGREGCAP'	=> 'a17',
	'ZCAPMONTYPE'	=> 'a18',
	'ZORGSIZE'		=> 'a19',
	'ZORGSYSSIZE'	=> 'a20',
	'ZFAACDATE'		=> 'a21',
	'ZOSCID'		=> 'a22',
	'ZSUBCERT'		=> 'a23',
	'ZMOSCID'		=> 'a24',
	'ZMULTIPLACE'	=> 'a25',
	'ZProCode'		=> 'a26',
	'ZProCodeDES'	=> 'a27',
	'ZSPESORTS'		=> 'a28',
	'ZREGIRANGES'	=> 'a29',
	'ZAUDCODE'		=> 'a30',
	'ZTYPECODES'	=> 'a31',
	'ZAUDCHGDUR'	=> 'a32',
	'ZCHGGROUP'		=> 'a33',
	'ZCERTSTATUS'	=> 'a34',
	'ZOLDREGIID'	=> 'a35',
	'ZOLDORGNAME'	=> 'a36',
	'ZNEWREGDATE'	=> 'a37',
	'ZAGAINTIMES'	=> 'a38',
	'ZSURTIMES'		=> 'a39',
	'ZAUDSTADATE'	=> 'a40',
	'ZAUDENDDATE'	=> 'a41',
	'ZAUDDAYSS'		=> 'a42',
	'ZISUNIT'		=> 'a43',
	'ZASSMANLIST'	=> 'a44',
	'ZAUDMONEY'		=> 'a45',
	'ZGETMONTYPE'	=> 'a46',
	'ZINVOICENO'	=> 'a47',
	'ZRISKCOEF'		=> 'a48',
	'ZREGIDATE'		=> 'a49',
	'ZREGITODATE'	=> 'a50',
	'ZCHANGEID'		=> 'a51',
	'ZPCID'			=> 'a52',
	'ZSUSSTADATE'	=> 'a53',
	'ZSUSENDDATE'	=> 'a54',
	'ZRCID'			=> 'a55',
	'ZRCDATE'		=> 'a56',
	'ZCHANGEDATE'	=> 'a57',
	'ZBEGINDATE'	=> 'a58',
	'ZENDDATE'		=> 'a59',
);

$auditor_map = array(
	'ZDEP_ID'		=> 'a1',
	'ZOSCID'		=> 'a2',
	'ZAUDSTADATE'	=> 'a3',
	'ZAUDSTATUS'	=> 'a4',
	'ZAUDENDDATE'	=> 'a5',
	'ZGroMemName'	=> 'a6',
	'ZIDType'		=> 'a7',
	'ZID_NUMBER'	=> 'a8',
	'ZQuaID'		=> 'a9',
	'ZMemCertCode'	=> 'a10',
	'ZMEMROLE'		=> 'a11',
	'ZISCERT'		=> 'a12',
	'ZISSPECIAL'	=> 'a13',
	'ZEVIMARK'		=> 'a14',
);





$tpl_db = DATA_DIR . 'SUPU201208.mdb';

$target_db = DATA_DIR . 'access/' . mysql2date( 'Y-m-d', current_time( 'mysql') ) . '.mdb';



if( file_exists( $target_db ) ){
	@unlink( $target_db );
}
$is_copyed = copy( $tpl_db, $target_db );
if( !$is_copyed ){
	writeover( $target_db, readover( $tpl_db ) );
}

 


foreach( $data_arr as $key => $item ){
	$ins_arr = array();
	foreach( $cert_arr as $k => $v ){
		$ins_arr[$k] = iconv( 'UTF-8', 'GBK', $item[$v] );
	}
	$ins_arr = access_magic( $ins_arr );

	$sql = "INSERT INTO ZBCERT_GET ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";

	load('report')->insert( $sql, $target_db );
}

foreach( $auditors as $key => $item ){
	$ins_arr = array();
	foreach( $auditor_map as $k => $v ){
		$ins_arr[$k] = iconv( 'UTF-8', 'GBK', $item[$v] );
	}
	$ins_arr = access_magic( $ins_arr );

	$sql = "INSERT INTO ZBVERIFYMEM ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";
	//echo "$key | $sql <br/><br/>";
	//echo "$sql <br/>";
	load('report')->insert( $sql, $target_db );
}


function access_magic( $string ){
	if( is_array( $string ) ){
		foreach( $string as $key => $val ) {
			$string[$key] = str_replace( "'", "''", $val);
		}
	} else {
		$string = str_replace( "'", "''", $string);
	}
	return $string;
}
echo sysinfo('url') . '/' . strstr($target_db, 'data');

exit;

//输出Execl文件
/**/




 $filename = iconv( 'UTF-8', 'GB2312', '获证组织基本信息表_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

 header("Content-Type: application/vnd.ms-excel");
 header("Content-Disposition: attachment; filename=" . $filename );
 header("Pragma: no-cache");
 header("Expires: 0");
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
TD{
    font-size: 12px;
    vertical-align: middle;
	vnd.ms-excel.numberformat:@;
}
</style>
<table>
	<thead>
		<table border="1">
    <tr>
		<td>1.认证机构批准号</td>
		<td>2.实施审核的关键场所名称</td>
		<td>3.组织名称</td>
		<td>4.组织名称（英文）</td>
		<td>5.组织原名</td>
		<td>6.组织机构代码</td>
		<td>7.认可标志</td>
		<td>8.所属行业</td>
		<td>9.组织认证地址所在国家和地区代码</td>
		<td>10.组织认证地址所在行政区划代码</td>
		<td>11.组织通讯/注册地址</td>
		<td>12.组织通讯邮编</td>
		<td>13.组织联系电话</td>
		<td>14.组织联系传真</td>
		<td>15.法定代表人</td>
		<td>16.组织性质代码</td>
		<td>17.组织注册资本</td>
		<td>18.注册资本币种</td>
		<td>19.组织员工数</td>
		<td>20.体系相关的员工数</td>
		<td>21.初次获证日期</td>
		<td>22.认证证书号</td>
		<td>23.是否是子证书</td>
		<td>24.主认证证书号</td>
		<td>25.是否有多现场</td>
		<td>26.认证项目代码</td>
		<td>27.认证项目代码说明</td>
		<td>28.业务范围代码</td>
		<td>29.证书覆盖范围</td>
		<td>30.认证审核活动代码</td>
		<td>31.证书变更类别代码</td>
		<td>32.证书变更时所处的审核活动期间</td>
		<td>33.是否换证</td>
		<td>34.换证原因</td>
		<td>35.原（已）获认证的认证证书号</td>
		<td>36.原（已）颁证机构名称</td>
		<td>37.换证日期</td>
		<td>38.再认证次数</td>
		<td>39.监督次数</td>
		<td>40.审核开始日期</td>
		<td>41.审核截至日期</td>
		<td>42.审核人日数</td>
		<td>43.是否结合</td>
		<td>44.评定人员名单</td>
		<td>45.实收的认证费用</td>
		<td>46.实收的认证费用币种</td>
		<td>47.发票号码</td>
		<td>48.风险系数</td>
		<td>49.颁证日期</td>
		<td>50.证书截止日期</td>
		<td>51.证书状态</td>
		<td>52.暂停原因</td>
		<td>53.暂停开始时间</td>
		<td>54.暂停截至时间</td>
		<td>55.撤销原因</td>
		<td>56.撤销时间</td>
		<td>57.变更日期</td>
		<td>58.取数开始日期</td>
		<td>59.取数截止日期</td>
    </tr>
	</thead>
	<tbody>
<?php 
	foreach($data_arr as $value){
		echo "<tr>";
		foreach($value as $v){
			echo "<td>".$v."</td>";
		}
		echo "</tr>";
	}
?>
	</tbody>
</table>