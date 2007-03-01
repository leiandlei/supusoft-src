<?php
set_time_limit(0);

/*
 *---------------------------------------------------------------
 * 导出59项月报
 * 附加说明
 *---------------------------------------------------------------
 */
require_once ROOT.'/data/cache/audit_ver.cache.php';
$new_data = array();
$a71 = $begindate  = getgp('s_date');//58.取数开始日期
$a72 = $enddate = getgp('e_date');//59.取数截止日期
$enddate.=" 23:59:59";
//$data_arr = array();
$pids = array();
$tids = array();

function get_audit_type($audit_type){
	if($audit_type=='1001'||$audit_type=='1002'||$audit_type=='1003'){
		return '01';
	}else if(in_array($audit_type,array('1004', '1005'))){
		return '03';
	}else if($audit_type=='1007'){
		return '02';
	}else if($audit_type=='1101'){
		return '05';
	}else{
		return '04';
	}
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
function get_code($code){
	$code=str_replace(array(";",",","，"," "),"；",$code);
	$codes=explode("；",$code);
	foreach($codes as $k=>$v){
		$codes[$k]=substr($v,0,8);

	}
	$codes=array_unique($codes);
	return join("；",$codes);
}









//变更信息
$sql = "SELECT z.cert_name,z.status,z.s_date,z.e_date,z.cert_scope,z.main_certno ,z.first_date ,z.change_date,z.certno,z.cert_addr,z.mark,z.is_change,z.old_certno,z.old_cert_name,z.change_type,z.cert_name_e,
c.cg_type_report,c.pass_date,c.cgs_date,c.cge_date,c.cg_reason,c.cg_pid as pid,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.ep_phone,e.ep_fax,
p.iso,p.audit_type,p.audit_ver,p.audit_code,p.ct_id,p.cti_id,p.ct_code,p.st_num,p.tid,p.comment_a_name,p.comment_b_name,p.sp_date
FROM `sp_certificate_change` c LEFT JOIN sp_certificate z on z.id=c.zsid LEFT JOIN sp_enterprises e on e.eid=z.eid left join sp_project p on p.id=c.cg_pid
WHERE c.deleted='0' and c.cgs_date >= '$begindate' and c.cgs_date <= '$enddate'    ORDER BY c.id DESC ";

//and c.cg_type_report IN ('04','05','06','97')
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	// $pids[]=$row[pid];
	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' ");
	$task_info = $db->get_row("select tb_date,te_date from sp_task t  where t.id='$row[tid]' and deleted=0");
	// $arr[2] = $row['ep_name']; 		//组织机构名称
	$arr=array();
	$arr[1]=$arr[2]=get_option("zdep_id");
	$arr[3] = $row['cert_name']; 		//
	$arr[4] = $row['cert_name_e']; 	//组织机构英文名称
	$arr[5] = $row['ep_oldname'];	//组织机构原名称
	$arr[6] = $row['work_code'];		//6.组织机构代码
	$arr[7] = trim($row['industry'],"；");	//8.所属行业
	$arr[8] = $row['statecode'];	//10.组织机构所在地方代码
	$arr[9] = $row['areacode'];		//9.组织机构所在地区代码
	$arr[10] = $row['cert_addr'];	//11.证书地址
	$arr[11] = $row['cta_addrcode'];	//12.组织邮政编码
	$arr[12] = $row['ep_phone'];			//13.组织联系电话
	$arr[13] = $row["ep_fax"]; 			//14.组织联系传真
	$arr[14] = $row['delegate']; 	//15.组织法定代表人
	$arr[15] = $row['nature']; 		//16.组织性质代码
	$arr[16] = $row['capital']; 		//17.组织注册资本
	$arr[17] = $row['currency'];		//18.组织注册资本币种
	$arr[18] = $row['ep_amount']; 	//19.组织组织人数
	$arr[19] = $cti_info['total']; 	//20.体系人数
	$arr[20] = (!$row['first_date'] || $row['first_date'] == '0000-00-00')?$row[s_date]:$row['first_date']; 	//21.初次获证日期
	$arr[21] = $row['certno']; 		//22.证书号码
	$arr[22] = $row['audit_ver'];	//26.认证项目代码
	$arr[23] = get_code($row['audit_code']);			
	$arr[24] = $audit_ver_array[$row[audit_ver]][audit_basis];//24、认证依据
	$arr[25] = $row[union_count]>0?"1":"0"; 					//25.是否多现场
	$arr[26] = $row[prod_addr]; //25.多现场名称地址多个用全角分号分隔。
	/*	
	if($arr[25]=='1'){
		$site_query=$db->query("SELECT * FROM `sp_enterprises` WHERE `parent_id` = '$row[eid]' AND `deleted` = '0'");
		while($r=$db->fetch_array($site_query)){
			$arr[26].=$r[ep_name]
		
		}
	
	
	}
	*/
	$arr[27] = $row['cert_scope'];	//27.认证覆盖范围
	$arr[28] = ''; 					//28、EC9000证书对应的QMS覆盖范围
	if($row[audit_ver]=="A010201")
		$arr[28] = $row['cert_scope'];
	$arr[29] = ''; 					//29、获证组织能源管理体系边界
	$arr[30] = '05'; 					//30、认证审核活动代码 变更 04
	$arr[31] = $cti_info[renum]; 			//31.再认证次数
	$arr[32] = get_audit_num($row[audit_type]); 					//32、监督次数
	$arr[33] = substr($task_info['tb_date'],0,10); 	//33.审核开始日期
	$arr[34] = substr($task_info['te_date'],0,10);	//34.审核结束日期
	$arr[35] = $row[st_num];//35、审核人日数
	$arr[36] ="01";//36、结合审核类型
	$cti_ids=$db->get_col("SELECT cti_id FROM `sp_project` WHERE `tid` = '$row[tid]' AND `deleted` = '0'");
	if(count(array_unique($cti_ids))==2)
		$arr[36] ="02";
	elseif(count(array_unique($cti_ids))==3)
		$arr[36] ="03";
	elseif(count(array_unique($cti_ids))>3)
		$arr[36] ="04";
		
	$arr[37] = $row[comment_a_name]."；".$row[comment_b_name];		//评定人员
	$arr[38] = $row[sp_date];//38、认证决定日期
	$arr[39] = $row['s_date'];		//证书发证日期
	$arr[40] = $row['e_date'];		//证书到期日期
	$arr[41] = $row['status'];	//证书状态
	$arr[42] = '';					//暂停原因
	$arr[43] = '';					//暂停开始时间
	$arr[44] = '';					//暂停结束时间
	$arr[45] = '';					//撤销原因
	$arr[46] = '';					//撤销日期
	if($row['status']=='02'){
		$arr[42]=$row[cg_reason];
		$arr[43]=$row[cgs_date];
		$arr[44]=$row[cge_date];
	}
	if($row['status']=='03'){
		$arr[45]=$row[cg_reason];
		$arr[46]=$row[cgs_date];
	}
	$arr[47] = '0';					//47、是否是子证书
	$arr[48] = '';					//48、主认证证书号
	if($row[main_certno]){
		$arr[47] = '1';
		$arr[48] = $row[main_certno];
	}
	$arr[49] = $row['cgs_date'];		//变更日期
	$arr[50] = $row[cg_type_report];					//31.变更类型代码
	$arr[51] = $row[is_change];					//33.shi否换证
	$arr[52] = ($row[change_date]=="0000-00-00")?"":$row[change_date]; 
	$arr[53] = $row[change_type]; 					//34.换证原因
	$arr[54] = $row[old_certno]; 					//35.原证书注册号
	$arr[55] = $row[old_cert_name]; 					//36.原证书颁发机构
	$arr[56] = ""; 					//56、证书使用的认可号
	$arr[57] = $row[mark]; 					//57、证书使用的认可标志代码
	$arr[58] = $cti_info[risk_level];	//风险系数
	$invoice_query=$db->query("SELECT * FROM `sp_contract_cost_detail` WHERE `ct_id` = '$row[ct_id]' AND `deleted` = '0' AND `iso` LIKE '%$row[iso]%' AND audit_type LIKE '%$row[audit_type]%' AND `invoice` <> '' ");
	if($invoice_query){
		$invoice="";
		$invoice_cost=0.00;
		while($_r=$db->fetch_array($invoice_query)){
			$invoice.=$_r[invoice]."；";
			$invoice_cost+=$_r[invoice_cost];	
		}
		$arr[59]=$invoice_cost;
		$arr[60]="01";
		$arr[61]=trim($invoice,'；');
	}
	!$arr[59] && $arr[59] = $db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso like '%$row[iso]%' AND cost_type like '%$row[audit_type]%'");	//收费金额
	$arr[60] = "01";		//收费币种
	!$arr[61] && $arr[61] = $row[ct_code];		//收费发票号

	
	
	$arr[59]=sprintf("%.2f", $arr[59]);
	$arr[62] ="";//62、认证证书附件文件名
	$arr[63] ="";//63、信息记录是否可公开
	$arr[64] ="04";//64、上报类型
	if($row[cg_type_report]=='0197')
		$arr[64] ="02";
	$arr[65] ="";//65、备注
	// $arr[66] ="";//66、审核结论
	// $arr[67] ="";//67、审核不符合项
	// $arr[68] ="";//68、本次认证活动的认证决定
	// $arr[69] ="";//69、上报类型
	// $arr[70] ="";//70、备注
	$arr[71] = $a71;					//取数开始时间
	$arr[72] = $a72;					//取数结束时间
	$data_arr[] = $arr;
}




//评定 （评定通过，监督类型,非换证(又取消 认监委的换证指的是证书号码变化)）
//and a.if_cert>'y'AND ifchangecert='0'



$sql = "SELECT 
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.ep_phone,e.ep_fax,
p.iso,p.audit_type,p.audit_ver,p.ct_id,p.cti_id,p.ct_code,p.st_num,p.tid,p.comment_a_name,p.comment_b_name,p.sp_date,p.audit_code,p.scope,p.id as pid
FROM sp_project p LEFT JOIN sp_enterprises e on e.eid=p.eid  WHERE p.deleted='0' and p.tid !='0' and p.pd_type='1' and p.audit_type  IN ('1004','1005')   and p.sp_date >= '$begindate' and p.sp_date <= '$enddate'    ORDER BY p.sp_date DESC";
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$pids[] = $row['pid'];
	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' ");
	$task_info = $db->get_row("select tb_date,te_date from sp_task t  where t.id='$row[tid]' and deleted=0");
	$cert_info=$db->get_row("SELECT * FROM `sp_certificate` WHERE `cti_id` = '$row[cti_id]' AND `eid` = '$row[eid]' AND `deleted` = '0' and status in('01','02') order by e_date desc");
	// $arr[2] = $row['ep_name']; 		//组织机构名称
	if(!$cert_info) continue;
	$arr=array();
	$arr[1]=$arr[2]=get_option("zdep_id");
	$arr[3] = $cert_info['cert_name']; 		//
	$arr[4] = $cert_info['cert_name_e']; 	//组织机构英文名称
	$arr[5] = $row['ep_oldname'];	//组织机构原名称
	$arr[6] = $row['work_code'];		//6.组织机构代码
	$arr[7] = rtrim($row['industry'],"；");	//8.所属行业
	$arr[8] = $row['statecode'];	//10.组织机构所在地方代码
	$arr[9] = $row['areacode'];		//9.组织机构所在地区代码
	$arr[10] = $cert_info['cert_addr'];	//11.证书地址
	$arr[11] = $row['cta_addrcode'];	//12.组织邮政编码
	$arr[12] = $row['ep_phone'];			//13.组织联系电话
	$arr[13] = $row['ep_fax']; 			//14.组织联系传真
	$arr[14] = $row['delegate']; 	//15.组织法定代表人
	$arr[15] = $row['nature']; 		//16.组织性质代码
	$arr[16] = $row['capital']; 		//17.组织注册资本
	$arr[17] = $row['currency'];		//18.组织注册资本币种
	$arr[18] = $row['ep_amount']; 	//19.组织组织人数
	$arr[19] = $cti_info['total']; 	//20.体系人数
	$arr[20] = (!$cert_info['first_date'] || $cert_info['first_date'] == '0000-00-00')?$cert_info[s_date]:$cert_info['first_date']; 	//21.初次获证日期
	$arr[21] = $cert_info['certno']; 		//22.证书号码
	$arr[22] = $row['audit_ver'];	//26.认证项目代码
	$arr[23] = get_code($row['audit_code']);			
	$arr[24] = $audit_ver_array[$row[audit_ver]][audit_basis];//24、认证依据
	$arr[25] = $row[union_count]>0?"1":"0"; 					//25.是否多现场
	$arr[26] = $row[prod_addr]; //25.多现场名称地址多个用全角分号分隔。
	/*	
	if($arr[25]=='1'){
		$site_query=$db->query("SELECT * FROM `sp_enterprises` WHERE `parent_id` = '$row[eid]' AND `deleted` = '0'");
		while($r=$db->fetch_array($site_query)){
			$arr[26].=$r[ep_name]
		
		}
	
	
	}
	*/
	$arr[27] = $row['scope'];	//27.认证覆盖范围
	$arr[28] = ''; 					//28、EC9000证书对应的QMS覆盖范围
	if($row[audit_ver]=="A010201")
		$arr[28] = $row['scope'];
	$arr[29] = ''; 					//29、获证组织能源管理体系边界
	$arr[30] = get_audit_type($row['audit_type']); 					//30、认证审核活动代码 变更 04
	$arr[31] = $cti_info[renum]; 			//31.再认证次数
	$arr[32] = get_audit_num($row[audit_type]); 					//32、监督次数
	$arr[33] = substr($task_info['tb_date'],0,10); 	//33.审核开始日期
	$arr[34] = substr($task_info['te_date'],0,10);	//34.审核结束日期
	$arr[35] = $row[st_num];//35、审核人日数
	$arr[36] ="01";//36、结合审核类型
	$cti_ids=$db->get_col("SELECT cti_id FROM `sp_project` WHERE `tid` = '$row[tid]' AND `deleted` = '0'");
	if(count(array_unique($cti_ids))==2)
		$arr[36] ="02";
	if(count(array_unique($cti_ids))==3)
		$arr[36] ="03";
	if(count(array_unique($cti_ids))>3)
		$arr[36] ="04";
		
	$arr[37] = $row[comment_a_name]."；".$row[comment_b_name];		//评定人员
	$arr[38] =$row[sp_date];//38、认证决定日期
	$arr[39] = $cert_info['s_date'];		//
	$arr[40] = $cert_info['e_date'];		//证书到期日期
	$arr[41] = $cert_info['status'];	//证书状态
	$arr[42] = '';					//暂停原因
	$arr[43] = '';					//暂停开始时间
	$arr[44] = '';					//暂停结束时间
	$arr[45] = '';					//撤销原因
	$arr[46] = '';					//撤销日期
	$arr[47] = '0';					//47、是否是子证书
	$arr[48] = '';					//48、主认证证书号
	if($cert_info[main_certno]){
		$arr[47] = '1';
		$arr[48] = $cert_info[main_certno];
	}
	$arr[49] = "";		//变更日期
	$arr[50] = "";					//31.变更类型代码
	$arr[51] = "0";					//33.shi否换证
	$arr[52] = ""; 
	$arr[53] = ""; 					//34.换证原因
	$arr[54] = ""; 					//35.原证书注册号
	$arr[55] = ""; 					//36.原证书颁发机构
	$arr[56] = ""; 					//56、证书使用的认可号
	$arr[57] = $cert_info[mark]; 					//57、证书使用的认可标志代码
	$arr[58] = $cti_info[risk_level];	//风险系数
	$invoice_query=$db->query("SELECT * FROM `sp_contract_cost_detail` WHERE `ct_id` = '$row[ct_id]' AND `deleted` = '0' AND `iso` LIKE '%$row[iso]%' AND audit_type LIKE '%$row[audit_type]%' AND `invoice` <> '' ");
	if($invoice_query){
		$invoice="";
		$invoice_cost=0.00;
		while($_r=$db->fetch_array($invoice_query)){
			$invoice.=$_r[invoice]."；";
			$invoice_cost+=$_r[invoice_cost];	
		}
		$arr[59]=$invoice_cost;
		$arr[60]="01";
		$arr[61]=trim($invoice,'；');
	}
	!$arr[59] && $arr[59] = $db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso like '%$row[iso]%' AND cost_type like '%$row[audit_type]%'");	//收费金额
	$arr[60] = "01";		//收费币种
	!$arr[61] && $arr[61] = $row[ct_code];		//收费发票号
	$arr[59]=sprintf("%.2f", $arr[59]);
	$arr[62] ="";//62、认证证书附件文件名
	$arr[63] ="";//63、服务认证所属领域代码
	$arr[64] ="04";//64、上报类型
	if($cert_info[is_change])
		$arr[64] ="02";
	$arr[65] ="";//65、备注
	// $arr[66] ="";//66、审核结论
	// $arr[67] ="";//67、审核不符合项
	// $arr[68] ="";//68、本次认证活动的认证决定
	// $arr[69] ="";//69、上报类型
	// $arr[70] ="";//70、备注
	$arr[71] = $a71;					//取数开始时间
	$arr[72] = $a72;					//取数结束时间
	$data_arr[] = $arr;
}

//审核组信息
$_pids = array_unique( $pids );
$pids=array(-1);
foreach($_pids as $v){
	if($v)
		$pids[]=$v;
}
$auditors = array();
$fields = "tat.iso,tat.pid,tat.uid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.taskBeginDate,tat.taskEndDate,tat.name,tat.use_code,";
$fields .= "p.cti_id,p.eid,";
//$fields .= "ta.taskBeginDate,ta.taskEndDate,ta.name,";
$fields .= "hr.card_type,hr.card_no,hr.audit_job";
//$fields .= "hqa.qua_no";


$join = " LEFT JOIN sp_hr hr ON hr.id = tat.uid";
$join .= " LEFT JOIN sp_project p ON p.id = tat.pid";

$where = " AND tat.pid IN (".implode(',',$pids).")    AND tat.deleted = 0";
//
$audit_types = array( '1002', '1003', '1004', '1005', '1007', '1008' );
$audit_type_pattrens = array( '01', '02', '0301', '0301', '04', '0302' );


$sql = "SELECT $fields FROM sp_task_audit_team tat $join WHERE 1 $where";
//$sql = "SELECT COUNT(*) FROM sp_task_audit_team tat $join WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$a12=0;
	if($rt[qua_type]!='03' and $rt[use_code])
		$a12=1;
	$a14="";
	if($rt[witness])
		$a14="01";
	$_query=$db->query("SELECT witness_person FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND `witness_person` IS NOT NULL AND `witness_person` <> '' AND deleted=0");
	while($r=$db->fetch_array( $_query )){
		if(strpos($r[witness_person],$rt[name]))
			$a14="02";
	}
	unset($_query,$r);
	$cert_query=$db->query("SELECT certno FROM sp_certificate WHERE cti_id = $rt[cti_id] AND  eid='$rt[eid]' and deleted=0");
	while($_r1=$db->fetch_array( $cert_query )){
		$auditor = array(
			'1'	=> $arr['1'],
			'2'	=> $_r1[certno],
			'3'	=> get_audit_type_auditor($rt[audit_type]),
			'4'	=> $rt['taskBeginDate'],
			'5'	=> $rt['taskEndDate'],
			'6'	=> $rt['name'],
			'7'	=> $rt['card_type'],
			'8'	=> $rt['card_no'] ,
			'9'	=> substr($rt[role],-2),
			'10'	=> $rt[qua_type],
			'11'	=> $db->get_var("SELECT qua_no FROM `sp_hr_qualification` WHERE `uid` = '$rt[uid]' AND `iso` = '$rt[iso]' AND status=1"),
			'12'	=> $a12 ,
			'13'	=> ( 1 == $rt['audit_job'] ? 1 : 0),
			'14'	=> $a14
		);
		if($rt['role']=='1004')
			$auditor[9]='';
		if($auditor[11])
			$auditors[] = $auditor;
	}
	unset($cert_query,$_r1);

	
}


$pids=array();
//exit;
// 新发证书 （）
/* $sql="select z.*,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.ep_phone,e.ep_fax
from sp_certificate z LEFT JOIN sp_enterprises e on z.eid=e.eid 
where z.is_check='y' and z.s_date >='".$begindate."' and z.s_date <='".$enddate."' and z.status='01' and z.deleted=0 and main_certno=''  order by z.s_date desc";
 */
$sql="SELECT c.*,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.ep_phone,e.ep_fax
,p.id as pid,p.audit_code,p.audit_type,p.st_num,p.comment_a_name,p.comment_b_name,p.sp_date,p.tid,
t.tb_date,t.te_date
 FROM sp_certificate c LEFT JOIN sp_project p ON (c.cti_id=p.cti_id AND c.`status`='01') LEFT JOIN sp_task t ON t.id=p.tid LEFT JOIN sp_enterprises e ON e.eid=c.eid  WHERE c.s_date>='$begindate' AND c.s_date<='$enddate' AND  p.deleted=0";//p.ifchangecert='2' AND
$res = $db->query($sql);
while($row=$db->fetch_array($res)){

	$pids[]=$row[pid];
	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' ");	
	// $arr[2] = $row['ep_name']; 		//组织机构名称
	$arr=array();
	$arr[1]=$arr[2]=get_option("zdep_id");
	$arr[3] = $row['cert_name']; 		//
	$arr[4] = $row['cert_name_e']; 	//组织机构英文名称
	$arr[5] = $row['ep_oldname'];	//组织机构原名称
	$arr[6] = $row['work_code'];		//6.组织机构代码
	$arr[7] = rtrim($row['industry'],"；");	//8.所属行业
	$arr[8] = $row['statecode'];	//10.组织机构所在地方代码
	$arr[9] = $row['areacode'];		//9.组织机构所在地区代码
	$arr[10] = $row['cert_addr'];	//11.证书地址
	$arr[11] = $row['cta_addrcode'];	//12.组织邮政编码
	$arr[12] = $row['ep_phone'];			//13.组织联系电话
	$arr[13] = $row['ep_fax']; 			//14.组织联系传真
	$arr[14] = $row['delegate']; 	//15.组织法定代表人
	$arr[15] = $row['nature']; 		//16.组织性质代码
	$arr[16] = $row['capital']; 		//17.组织注册资本
	$arr[17] = $row['currency'];		//18.组织注册资本币种
	$arr[18] = $row['ep_amount']; 	//19.组织组织人数
	$arr[19] = $cti_info['total']; 	//20.体系人数
	$arr[20] = (!$row['first_date'] || $row['first_date'] == '0000-00-00')?$row[s_date]:$row['first_date']; 	//21.初次获证日期
	$arr[21] = $row['certno']; 		//22.证书号码
	$arr[22] = $row['audit_ver'];	//26.认证项目代码
	$arr[23] = get_code($row['audit_code']);			
	$arr[24] = $audit_ver_array[$row[audit_ver]][audit_basis];//24、认证依据
	$arr[25] = $row[union_count]>0?"1":"0"; 					//25.是否多现场
	$arr[26] = $row[prod_addr]; //25.多现场名称地址多个用全角分号分隔。
	/*	
	if($arr[25]=='1'){
		$site_query=$db->query("SELECT * FROM `sp_enterprises` WHERE `parent_id` = '$row[eid]' AND `deleted` = '0'");
		while($r=$db->fetch_array($site_query)){
			$arr[26].=$r[ep_name]
		
		}
	
	
	}
	*/
	$arr[27] = $row['cert_scope'];	//27.认证覆盖范围
	$arr[28] = ''; 					//28、EC9000证书对应的QMS覆盖范围
	if($row[audit_ver]=="A010201")
		$arr[28] = $row['cert_scope'];
	$arr[29] = ''; 					//29、获证组织能源管理体系边界
	$arr[30] = get_audit_type($row['audit_type']); 					//30、认证审核活动代码 变更 04
	$arr[31] = $cti_info[renum]; 			//31.再认证次数
	$arr[32] = get_audit_num($row[audit_type]); 					//32、监督次数
	$arr[33] = substr($row[tb_date],0,10); 	//33.审核开始日期
	$arr[34] = substr($row[te_date],0,10);	//34.审核结束日期
	$arr[35] = $row[st_num];//35、审核人日数
	$arr[36] ="01";//36、结合审核类型
	$cti_ids=$db->get_col("SELECT cti_id FROM `sp_project` WHERE `tid` = '$row[tid]' AND `deleted` = '0'");
	if(count(array_unique($cti_ids))==2)
		$arr[36] ="02";
	if(count(array_unique($cti_ids))==3)
		$arr[36] ="03";
	if(count(array_unique($cti_ids))>3)
		$arr[36] ="04";
		
	$arr[37] = $row[comment_a_name]."；".$row[comment_b_name];		//评定人员
	$arr[38] = $row[sp_date];//38、认证决定日期
	$arr[39] = $row['s_date'];		//证书发证日期
	$arr[40] = $row['e_date'];		//证书到期日期
	$arr[41] = $row['status'];	//证书状态
	$arr[42] = '';					//暂停原因
	$arr[43] = '';					//暂停开始时间
	$arr[44] = '';					//暂停结束时间
	$arr[45] = '';					//撤销原因
	$arr[46] = '';					//撤销日期
	
	$arr[47] = '0';					//47、是否是子证书
	$arr[48] = '';					//48、主认证证书号
	if($row[main_certno]){
		$arr[47] = '1';
		$arr[48] = $row[main_certno];
	}
	$arr[49] = "";		//变更日期
	$arr[50] = "";					//31.变更类型代码
	$arr[51] = $row[is_change];					//33.shi否换证
	$arr[52] = $row[change_date]; 
	$arr[53] = $row[change_type]; 					//34.换证原因
	$arr[54] = $row[old_cert_name]; 					//35.原证书注册号
	$arr[55] = $row[old_certno]; 					//36.原证书颁发机构
	if(!$row[is_change]){
	$arr[51]='0';
	$arr[52]=$arr[53]=$arr[54]=$arr[55]='';
	}
	$arr[56] = ""; 					//56、证书使用的认可号
	$arr[57] = $row[mark]; 					//57、证书使用的认可标志代码
	$arr[58] = $cti_info[risk_level];	//风险系数
	$invoice_query=$db->query("SELECT * FROM `sp_contract_cost_detail` WHERE `ct_id` = '$row[ct_id]' AND `deleted` = '0' AND `iso` LIKE '%$row[iso]%' AND audit_type LIKE '%$row[audit_type]%' AND `invoice` <> '' ");
	if($invoice_query){
		$invoice="";
		$invoice_cost=0.00;
		while($_r=$db->fetch_array($invoice_query)){
			$invoice.=$_r[invoice]."；";
			$invoice_cost+=$_r[invoice_cost];	
		}
		$arr[59]=$invoice_cost;
		$arr[60]="01";
		$arr[61]=trim($invoice,'；');
	}
	!$arr[59] && $arr[59] = $db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso like '%$row[iso]%' AND cost_type like '%$row[audit_type]%'");	//收费金额
	$arr[60] = "01";		//收费币种
	!$arr[61] && $arr[61] = $row[ct_code];		//收费发票号
	$arr[59]=sprintf("%.2f", $arr[59]);
	$arr[62] ="";//62、认证证书附件文件名
	$arr[63] ="";//63、信息记录是否可公开
	$arr[64] ="01";//64、上报类型
	if($row[is_change])
		$arr[64] ="02";
	$arr[65] ="";//65、备注
	// $arr[66] ="";//66、审核结论
	// $arr[67] ="";//67、审核不符合项
	// $arr[68] ="";//68、本次认证活动的认证决定
	// $arr[69] ="";//69、上报类型
	// $arr[70] ="";//70、备注
	$arr[71] = $a71;					//取数开始时间
	$arr[72] = $a72;					//取数结束时间
	$data_arr[] = $arr;
	$p_info=$p_info1=$p_info2=$tb_date=$te_date=$st_num=$tid="";
}
// do_excel($data_arr,"","1234");
// p($data_arr);
// exit;


//审核组信息
$_pids = array_unique( $pids );
$pids=array(-1);
foreach($_pids as $v){
	if($v)
		$pids[]=$v;
}
$fields = "tat.iso,tat.pid,tat.uid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.taskBeginDate,tat.taskEndDate,tat.name,tat.use_code,";
$fields .= "p.cti_id,p.eid,";
//$fields .= "ta.taskBeginDate,ta.taskEndDate,ta.name,";
$fields .= "hr.card_type,hr.card_no,hr.audit_job";
//$fields .= "hqa.qua_no";


$join = " LEFT JOIN sp_hr hr ON hr.id = tat.uid";
$join .= " LEFT JOIN sp_project p ON p.id = tat.pid";

$where = " AND tat.pid IN (".implode(',',$pids).")    AND tat.deleted = 0";
//
$audit_types = array( '1002', '1003', '1004', '1005', '1007', '1008' );
$audit_type_pattrens = array( '01', '02', '0301', '0301', '04', '0302' );


$sql = "SELECT $fields FROM sp_task_audit_team tat $join WHERE 1 $where";
//$sql = "SELECT COUNT(*) FROM sp_task_audit_team tat $join WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$a12=0;
	if($rt[qua_type]!='03' and $rt[use_code])
		$a12=1;
	$a14="";
	if($rt[witness])
		$a14="01";
	$_query=$db->query("SELECT witness_person FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND `witness_person` IS NOT NULL AND `witness_person` <> '' AND deleted=0");
	while($r=$db->fetch_array( $_query )){
		if(strpos($r[witness_person],$rt[name]))
			$a14="02";
	}
	unset($_query,$r);
	$cert_query=$db->query("SELECT certno FROM sp_certificate WHERE cti_id = $rt[cti_id] AND  eid='$rt[eid]' and deleted=0");
	while($_r1=$db->fetch_array( $cert_query )){
		$auditor = array(
			'1'	=> $arr['1'],
			'2'	=> $_r1[certno],
			'3'	=> get_audit_type_auditor($rt[audit_type]),
			'4'	=> $rt['taskBeginDate'],
			'5'	=> $rt['taskEndDate'],
			'6'	=> $rt['name'],
			'7'	=> $rt['card_type'],
			'8'	=> $rt['card_no'] ,
			'9'	=> substr($rt[role],-2),
			'10'	=> $rt[qua_type],
			'11'	=> $db->get_var("SELECT qua_no FROM `sp_hr_qualification` WHERE `uid` = '$rt[uid]' AND `iso` = '$rt[iso]' AND status=1"),
			'12'	=> $a12 ,
			'13'	=> ( 1 == $rt['audit_job'] ? 1 : 0),
			'14'	=> $a14
		);
		if($rt['role']=='1004')
			$auditor[9]='';
		if($auditor[11])
			$auditors[] = $auditor;
	}
	unset($cert_query,$_r1);

	
}




//输出Access
$type=getgp("type");
if($type=="access"){
    
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
foreach( $data_arr as $key => $item ){
	$ins_arr = array();
	foreach( $cert_arr as $k => $v ){
		$ins_arr[$k] = iconv( 'UTF-8', 'GBK', $item[$v] );
	}
	$ins_arr = access_magic( $ins_arr );
	if(!$item[$v]) continue;
	$sql = "INSERT INTO ZBCERT_GET ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";
	//load('report')->query( $sql, "/".$target_db );
	load('report')->query( $sql, $target_db );
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
	//load('report')->query( $sql, "/".$target_db );
	load('report')->query( $sql,$target_db );
}



echo sysinfo('url') . '/' . strstr($target_db, 'data');

exit;
}else{

exit;
}
?>
