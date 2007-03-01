<?php
set_time_limit(0);

/*
 *---------------------------------------------------------------
 * 导出59项月报
 * 附加说明
 *---------------------------------------------------------------
 */
$A = array();
$a58 = $begindate = $A['58'] = getgp('s_date');//58.取数开始日期
$a59 = $enddate = $A['59'] = getgp('e_date');//59.取数截止日期
$enddate.=" 23:59:59";
//$data_arr = array();
$pids = array();
$tids = array();

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
function get_code($code){
	$code=str_replace(array(";",",","，"," "),"；",$code);
	$codes=explode("；",$code);
	foreach($codes as $k=>$v){
		$codes[$k]=substr($v,0,8);

	}
	$codes=array_unique($codes);
	return join("；",$codes);
}
$arr['a1'] = $arr['a2']="CNCA-R-2002-022";







//变更信息
$sql = "SELECT z.status,z.s_date,z.e_date,z.iso,z.ct_id,z.pid,z.cert_scope,z.main_certno ,z.first_date ,z.change_date,z.certno,z.cti_id,z.cert_addr,z.mark,z.audit_code,z.audit_ver,z.is_change,z.old_certno,z.old_cert_name,z.change_type,z.ct_id,z.cert_name_e,z.audit_type,z.ct_id,
c.cg_type_report,c.pass_date,c.cgs_date,c.cge_date,c.cg_reason,c.cg_pid,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry
FROM `sp_certificate_change` c LEFT JOIN sp_certificate z on z.id=c.zsid LEFT JOIN sp_enterprises e on e.eid=z.eid
WHERE c.deleted='0' and c.pass_date >= '$begindate' and c.pass_date <= '$enddate'    ORDER BY c.id DESC ";

//and c.cg_type_report IN ('04','05','06','97')
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	//$pids[]=$row[pid];
	$cti_info = $db->get_row("select total,renum from sp_contract_item where cti_id='$row[cti_id]' ");
	$p_info = $db->get_row("select st_num,id,cti_id,ct_id,tid,audit_type from sp_project where id='$row[cg_pid]'");
	$pid = $p_info['id'];
	
	$task_info = $db->get_row("select tb_date,te_date from sp_task t  where t.id='$p_info[tid]' ");
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $row['cert_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $row['mark'];			//7.认可标志
	$arr['a8'] = rtrim($row['industry'],"；");	//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $row['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
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
	if($row['main_certno']){
		$arr['a23'] = '1'; 				//23.是否子证书
		$arr['a24'] = $row['main_certno']; 	//24.主证书
	}
	$arr['a25'] = '0'; 					//25.是否多现场
	$arr['a26'] = $row['audit_ver'];	//26.认证项目代码

	$arr['a27'] = '';					//27.其它时认证代码说明
	$arr['a28'] = get_code($row['audit_code']); 	//28.业务范围代码
	$arr['a29'] = $row['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = '04';						//30.因为都是变更数据 所以认证活动代码为04
	$arr['a31'] = '97';					//31.变更类型代码
	$arr['a32'] = get_audit_type($p_info['audit_type']);	//32.变更时所处的审核活动期间
	$arr['a33'] = $row[is_change];					//33.shi否换证
	$arr['a34'] = $row[change_type]; 					//34.换证原因
	$arr['a35'] = $row[old_certno]; 					//35.原证书注册号
	$arr['a36'] = $row[old_cert_name]; 					//36.原证书颁发机构
	if($arr['a33']=="1")
		$arr['a37'] = ($row[change_date]=="0000-00-00")?"":$row[change_date]; 					//37.换证日期-
	else
		$arr['a37']="";
	$arr['a38'] = $cti_info[renum]; 					//38.复评次数
	$arr['a39'] = "";
	if($arr['a30']=='0301'||$arr['a30']=='0302'){
		$arr['a39'] = get_audit_num($p_info);
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $p_info['st_num'];		//42.审核人日
	$sql = "select count(id) from sp_project where tid='$p_info[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//43.是否结合审核
	if($num>1){
		$arr['a43'] = '1';
	}

	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_project WHERE id = '$pid'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	//$arr['a44'] = implode('；',$hr_arr);		//评定人员
	$p_info[audit_type]=='1007' && $p_info[audit_type]="1003";
	$arr['a45'] =$db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$p_info[audit_type]' and deleted=0 and iso='$p_info[iso]'");	//收费金额
	$arr['a45']?$arr['a45']:$arr['a45']=0;
	$arr['a46'] = "01";		//收费币种
	$ct_code=$db->get_var("SELECT ct_code FROM `sp_contract` WHERE `ct_id` = '$row[ct_id]'");
	$cw_info = $db->get_row("select invoice from sp_contract_cost_detail where pid='$row[pid]' and invoice<>'' and deleted=0 ");
	$arr['a47'] = $cw_info['invoice']?$cw_info['invoice']:$ct_code;		//收费发票号
	$sql = "select risk_level from sp_contract_item where cti_id='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $row['s_date'];		//证书发证日期
	$arr['a50'] = $row['e_date'];		//证书到期日期
	$arr['a51'] = $row['status'];	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	if($arr['a51']=='02'){
		$arr['a52'] = $row['cg_reason'];//暂停原因
		$arr['a53'] = ($row['pass_date']=="0000-00-00 00:00:00")?"":substr($row['pass_date'],0,10);	//暂停开始时间
		$arr['a54'] = ($row['cge_date']=="0000-00-00 00:00:00")?"":$row['cge_date'];	//暂停结束时间
	}else if($arr['a51']=='03'){
		$arr['a55'] = $row['cg_reason'];//撤销原因
		$arr['a56'] = ($row['pass_date']=="0000-00-00 00:00:00")?"":substr($row['pass_date'],0,10);	//撤销日期
	}
	$arr['a57'] = $row['cgs_date'];		//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$arr['a60'] = "变更";	
	$arr['a45']=0;
	$arr['a47']=$ct_code;
	$data_arr[] = $arr;
}




//评定 （评定通过，监督类型,非换证(又取消 认监委的换证指的是证书号码变化)）
//and a.if_cert>'y'



$sql = "SELECT e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,
p.tid,p.ct_id,p.cti_id,p.audit_type,p.sp_date,
p.id as pid ,p.tid,p.st_num,p.ct_id,p.iso,p.ifchangecert
FROM sp_project p LEFT JOIN sp_enterprises e on e.eid=p.eid  WHERE p.deleted='0' and p.pd_type='1' and p.audit_type NOT IN ('1001','1002','1003','1007','1010','2001','2002','3001') and p.sp_date >= '$begindate' and p.sp_date <= '$enddate'    ORDER BY p.sp_date DESC";

$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$sql = "select * from sp_certificate where cti_id='$row[cti_id]' and is_check='y' and status IN ('01','02','03','04') order by status asc limit 1";
	$zs_info = $db->get_row($sql);

	$cti_info = $db->get_row("select total,renum from sp_contract_item where cti_id='$row[cti_id]' ");
	//echo "select id from sp_project where ct_id='$row[ct_id]' and audit_type='$row[audit_type]' and iso='$row[iso]' <br>";
	$pid = $row['pid'];
	$pids[] = $pid;
	$tids[] = $row['tid'];
	//echo "select * from sp_task t left join sp_task_vice tv on tv.tid=t.id where t.id='$row[tid]' and tv.pid='$pid' <br>";
	$task_info = $db->get_row("select tb_date,te_date from sp_task where id='$row[tid]' ");
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $zs_info['cert_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $zs_info['mark'];		//7.认可标志
	$arr['a8'] = trim($row['industry'],"；");//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $zs_info['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr['a15'] = $row['delegate']; 	//15.组织法定代表人
	$arr['a16'] = $row['nature']; 		//16.组织性质代码
	$arr['a17'] = $row['capital']; 		//17.组织注册资本
	$arr['a18'] = $row['currency'];		//18.组织注册资本币种
	$arr['a19'] = $row['ep_amount']; 	//19.组织组织人数
	$arr['a20'] = $cti_info['total']; 	//20.体系人数
	$arr['a21'] = ($row['first_date'] == '0000-00-00')?'':$row['first_date']; 	//21.初次获证日期
	if(!$arr['a21'] )$arr['a21']= $zs_info['s_date'];
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
	$arr['a28'] = get_code($zs_info['audit_code']); 	//28.业务范围代码
	$arr['a29'] = $zs_info['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = get_audit_type($row['audit_type']);	//30.活动代码
	$arr['a31'] = '';					//31.变更类型代码(评定)
	$arr['a32'] = '';					//32.变更时所处的审核活动期间
	$arr['a33'] = '0';					//33.书否换证
	$arr['a34'] = ''; 					//34.换证原因
	$arr['a35'] = ''; 					//35.原证书注册号
	$arr['a36'] = ''; 					//36.原证书颁发机构
	if($arr['a33']=="1")
		$arr['a37'] = ($row[change_date]=="0000-00-00")?"":$row[change_date]; 					//37.换证日期-根据新月报规则 不可能有换这个日期，所以以下处理
	else
		$arr['a37']="";
	$arr['a38'] = $cti_info[renum]; 					//38.复评次数
	$arr['a39'] = '';
	if($arr['a30']=='0301'||$arr['a30']=='0302'){
		$arr['a39'] = get_audit_num($p_info);
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $row['st_num'];		//42.审核人日
	$sql = "select count(id) from sp_project where tid='$row[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//43.是否结合审核
	if($num>1){
		$arr['a43'] = '1';
	}
	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_project WHERE id = '$row[pid]'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	/*
	if($row[audit_type]=='1002' || $row[audit_type]=='1003')
		$cost_type='1003';
	elseif($row[audit_type]=='1004')
		$cost_type='1004';
	elseif($row[audit_type]=='1005')
		$cost_type='1005';
	elseif($row[audit_type]=='1007')
		$cost_type='1008';
	elseif($row[audit_type]=='1008' || $row[audit_type]=='1009'||$row[audit_type]=='1010')
		$cost_type='1006';
	else
		$cost_type='1007';
	*/
	$row[audit_type]=='1007' && $row[audit_type]="1003";
	$arr['a45'] =$db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso='$row[iso]'");	//收费金额
	$arr['a45']?$arr['a45']:$arr['a45']=0;
	
	$arr['a46'] = "01";		//收费币种
	$cw_info = $db->get_row("select invoice from sp_contract_cost_detail where pid='$row[pid]' and invoice<>'' and deleted=0 ");
	$ct_code=$db->get_var("SELECT ct_code FROM `sp_contract` WHERE `ct_id` = '$row[ct_id]'");
	$arr['a47'] = $cw_info['invoice']?$cw_info['invoice']:$ct_code;		//收费发票号


	$sql = "select risk_level from sp_contract_item where cti_id='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $zs_info['s_date'];		//证书发证日期
	$arr['a50'] = $zs_info['e_date'];		//证书到期日期
	$arr['a51'] = sprintf('%02d',$zs_info['status']);	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	$arr['a57'] ='';
	if($arr['a30'] =='0301' || $arr['a30'] =='0302' || $arr['a30'] =='04')
		$arr['a57'] = $row[sp_date];					//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$arr['a60'] = "评定";
	$arr['a45']=0;
	$arr['a47']=$ct_code;
	$data_arr[] = $arr;
}
//exit;
// 新发证书 （）
$sql="select z.status,z.s_date,z.e_date,z.iso,z.ct_id,z.cert_scope,z.main_certno ,z.first_date,z.cert_name_e ,z.certno,z.cti_id,z.cert_addr,z.mark,z.audit_code,z.pid,z.audit_ver,z.s_date,z.e_date,z.change_date,z.audit_type,z.ct_id,z.tid,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry
from sp_certificate z LEFT JOIN sp_enterprises e on z.eid=e.eid
where z.is_check='y' and z.s_date >='".$begindate."' and z.s_date <='".$enddate."' and z.status ='01' and z.deleted=0  AND z.main_certno='' order by z.s_date desc";


$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$cti_info = $db->get_row("select total,renum from sp_contract_item where cti_id='$row[cti_id]' ");
	$pid = $row['pid'];
	$tids[] = $row['tid'];
	$pids[] = $pid;

	$p_info = $db->get_row("select st_num,id,cti_id,ct_id,tid from sp_project where id='$row[pid]'  ");
	$task_info = $db->get_row("select tb_date,te_date from sp_task where id='$p_info[tid]' ");
	$arr['a3'] = $row['ep_name']; 		//3.组织机构名称
	$arr['a4'] = $row['cert_name_e']; 	//4.组织机构英文名称
	$arr['a5'] = $row['ep_oldname'];	//5.组织机构原名称
	$arr['a6'] = $row['work_code'];		//6.组织机构代码
	$arr['a7'] = $row['mark'];		//7.认可标志
	$arr['a8'] = trim($row['industry'],"；");//8.所属行业
	$arr['a9'] = $row['statecode'];	//10.组织机构所在地方代码
	$arr['a10'] = $row['areacode'];		//9.组织机构所在地区代码
	$arr['a11'] = $row['cert_addr'];	//11.证书地址
	$arr['a12'] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr['a13'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr['a14'] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr['a15'] = $row['delegate']; 	//15.组织法定代表人
	$arr['a16'] = $row['nature']; 		//16.组织性质代码
	$arr['a17'] = $row['capital']; 		//17.组织注册资本
	$arr['a18'] = $row['currency'];		//18.组织注册资本币种
	$arr['a19'] = $row['ep_amount']; 	//19.组织组织人数
	$arr['a20'] = $cti_info['total']; 	//20.体系人数
	$arr['a21'] = ($row['first_date'] == '0000-00-00')?'':$row['first_date']; 	//21.初次获证日期
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
	$arr['a28'] =get_code($row['audit_code']); 	//28.业务范围代码
	$arr['a29'] = $row['cert_scope'];	//29.认证覆盖范围
	$arr['a30'] = get_audit_type($row['audit_type']);	//30.活动代码
	$arr['a31'] = '';					//31.变更类型代码(评定)
	$arr['a32'] = '';					//32.变更时所处的审核活动期间
	$arr['a33'] = '0';					//33.是否换证
	$arr['a34'] = ''; 					//34.换证原因
	$arr['a35'] = ''; 					//35.原证书注册号
	$arr['a36'] = ''; 					//36.原证书颁发机构
	if($arr['a33']=="1")
		$arr['a37'] = ($row[change_date]=="0000-00-00")?"":$row[change_date]; 					//37.换证日期-根据新月报规则 不可能有换这个日期，所以以下处理
	else
		$arr['a37']="";
	$arr['a38'] = $cti_info[renum]; 					//38.复评次数
	$arr['a39'] = '';
	if($arr['a30']=='0301'||$arr['a30']=='0302'){
		$arr['a39'] = get_audit_num($p_info);
	}
	$arr['a40'] = substr($task_info['tb_date'],0,10); 	//40.审核开始日期
	$arr['a41'] = substr($task_info['te_date'],0,10);	//41.审核结束日期
	$arr['a42'] = $p_info['st_num'];		//42.审核人日
	$sql = "select count(id) from sp_project where tid='$p_info[tid]' ";
	$num = $db->get_var($sql);
	$arr['a43'] = '0';					//43.是否结合审核
	if($num>1){
		$arr['a43'] = '1';
	}
	$pd_row = $db->get_row("SELECT comment_a_name,comment_b_name FROM sp_project WHERE id = '$row[pid]'");
	$arr['a44'] = "$pd_row[comment_a_name]；$pd_row[comment_b_name]";		//评定人员
	$cw_info = $db->get_row("select invoice from sp_contract_cost_detail where pid='$row[pid]' and invoice<>'' and deleted=0 ");
	$ct_code=$db->get_var("SELECT ct_code FROM `sp_contract` WHERE `ct_id` = '$row[ct_id]'");
	/*if($row[audit_type]=='1002' || $row[audit_type]=='1003')
		$cost_type='1003';
	elseif($row[audit_type]=='1004')
		$cost_type='1004';
	elseif($row[audit_type]=='1005')
		$cost_type='1005';
	elseif($row[audit_type]=='1007')
		$cost_type='1008';
	elseif($row[audit_type]=='1008' || $row[audit_type]=='1009'||$row[audit_type]=='1010')
		$cost_type='1006';
	else
		$cost_type='1007';
		*/
	$row[audit_type]=='1007' && $row[audit_type]="1003";
	$arr['a45'] =$db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso='$row[iso]'");	//收费金额
	$arr['a45']?$arr['a45']:$arr['a45']=0;
	$arr['a46'] = "01";		//收费币种
	$arr['a47'] = $cw_info['invoice']?$cw_info['invoice']:$ct_code;		//收费发票号

	//echo $arr['a45']."-".$db->sql."<br/>";
	$sql = "select risk_level from sp_contract_item where cti_id='$row[cti_id]' ";
	$arr['a48'] = $db->get_var($sql);	//风险系数
	$arr['a49'] = $row['s_date'];		//证书发证日期
	$arr['a50'] = $row['e_date'];		//证书到期日期
	$arr['a51'] = sprintf('%02d',$zs_info['status']);	//证书状态
	$arr['a52'] = '';					//暂停原因
	$arr['a53'] = '';					//暂停开始时间
	$arr['a54'] = '';					//暂停结束时间
	$arr['a55'] = '';					//撤销原因
	$arr['a56'] = '';					//撤销日期
	$arr['a57'] ='';
	if($arr['a30'] =='0301' || $arr['a30'] =='0302' || $arr['a30'] =='04')
		$arr['a57'] = $db->get_var("SELECT sp_date FROM sp_project WHERE `id` = '$row[pid]'");					//变更日期
	$arr['a58'] = $a58;					//取数开始时间
	$arr['a59'] = $a59;					//取数结束时间
	$arr['a60'] = "新发证";
	$arr['a45']=0;
	$arr['a47']=$ct_code;
	$data_arr[] = $arr;
}



//审核组信息
//p(count($pids));

$pids = array_unique( $pids );
$auditors = array();
$fields = "tat.iso,tat.pid,tat.uid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.taskBeginDate,tat.taskEndDate,tat.name,";
$fields .= "p.cti_id,";
//$fields .= "ta.taskBeginDate,ta.taskEndDate,ta.name,";
$fields .= "hr.card_type,hr.card_no,hr.audit_job";
//$fields .= "hqa.qua_no";


$join .= " LEFT JOIN sp_hr hr ON hr.id = tat.uid";
$join .= " LEFT JOIN sp_project p ON p.id = tat.pid";

if(sizeof($pids)==0) {
	$pids = array('-1');
}
if(sizeof($tids)==0) {
	$tids = array('-1');
}

$where = " AND tat.pid IN (".implode(',',$pids).")    AND tat.deleted = 0";
//
$audit_types = array( '1002', '1003', '1004', '1005', '1007', '1008' );
$audit_type_pattrens = array( '01', '02', '0301', '0301', '04', '0302' );


$sql = "SELECT $fields FROM sp_task_audit_team tat $join WHERE 1 $where";
//$sql = "SELECT COUNT(*) FROM sp_task_audit_team tat $join WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	if($rt['audit_type']=='1002')
			$a4='01';
		elseif($rt['audit_type']=='1003')
			$a4='02';
		elseif($rt['audit_type']=='1007')
			$a4='04';
		elseif($rt['audit_type']=='1004' || $rt['audit_type']=='1005')
			$a4='0301';
		elseif($rt['audit_type']=='1008' || $rt['audit_type']=='1009' || $rt['audit_type']=='1010')
			$a4='0302';
		else
			$a4='99';
	$auditor = array(
		'a1'	=> $arr['a1'],
		'a2'	=> $db->get_var("SELECT certno FROM sp_certificate WHERE cti_id = $rt[cti_id] AND status = 1 and deleted=0 LIMIT 1"),
		'a3'	=> $rt['taskBeginDate'],
		'a4'	=> $a4,
		'a5'	=> $rt['taskEndDate'],
		'a6'	=> $rt['name'],
		'a7'	=> $rt['card_type'],
		'a8'	=> $rt['card_no'] ,
		'a9'	=> str_replace( '1001', '01', $rt['qua_type'] ),
		'a10'	=> $db->get_var("SELECT qua_no FROM `sp_hr_qualification` WHERE `uid` = '$rt[uid]' AND `iso` = '$rt[iso]' AND status=1"),
		'a11'	=> substr($rt[role],-2),
		'a12'	=> ( '1002' == $rt['role'] ? 1 : 0 ) ,
		'a13'	=> ( 1 == $rt['audit_job'] ? 1 : 0),
		'a14'	=> ( 1 == $rt['witness'] ? '01' : '02' )
	);
	if(!$rt['witness'])
		$auditor[a14]='';
	if($rt['role']=='1004')
		$auditor[a11]='';
	if($auditor[a10])
		$auditors[] = $auditor;
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

//输出Execl文件
/**/




 $filename = "获证组织基本信息表_".mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

 //header("Content-Type: application/vnd.ms-excel");
// header("Content-Disposition: attachment; filename=" . $filename );
// header("Pragma: no-cache");
// header("Expires: 0");
ob_start();
tpl( 'xls/59' );
$data = ob_get_contents();
ob_end_clean();

export_xls( $filename, $data );
exit;
}
?>
