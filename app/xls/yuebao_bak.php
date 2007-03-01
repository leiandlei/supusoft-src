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

$arr_data_zsInfo = array();
$arr_data_shInfo = array();
$pids = array();
$tids = array();


/**===========证书=======**/
//sql初始化
$join = $select = '';$where = ' where 1';

//证书表
$sql    = "select %s from `sp_certificate` c";
$select.= "c.*";
$where .= " and c.`status`='01' and c.deleted=0 and c.s_date>='$begindate' and c.s_date<='$enddate'";

//审核项目表
$select.= ",p.id as pid,p.audit_code,p.ct_code,p.audit_type,p.comment_b_name,p.sp_date,p.tid";
$join  .= " left join sp_project p ON c.cti_id=p.cti_id";
$where .= " and p.deleted = '0'";

//审核计划表
$select.= ",t.te_date,t.id as tid";
$join  .= " left join sp_task t ON t.id=p.tid";
$where .= " and t.deleted = '0'";

//企业表
$select.= ",e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.cta_addrcode,e.xvalue,e.yvalue,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,e.person_tel,e.ep_phone,e.ep_fax";
$join  .= " left join sp_enterprises e ON e.eid=c.eid ";
$where .= " and e.deleted = 0";

//合同项目表
$select.= ",cti.base_num";
$join  .= " left join sp_contract_item cti ON p.cti_id = cti.cti_id";
$where .= " and cti.deleted = '0'";



//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where." ORDER BY t.tb_date DESC";
$query = $db->query($sql);
while($row=$db->fetch_array($query)){
// echo "<pre />";	
// print_r($row);exit;
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
	$pids[]   = $row["pid"];
	if($row['audit_type']==1002)continue;

	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' and `deleted`=0");
	//子证书
	$zicertno = $db->get_row("select eid from sp_enterprises_site where eid='$row[eid]' and `deleted`=0");


	$cti_ids  = $db->get_col("select cti_id from `sp_project` where `tid`='$row[tid]' and `deleted`=0");

	$arr     = array();
	$arr[2]  = get_option("zdep_id");
	$arr[3]  = "中标华信（北京）认证中心有限公司";
	$arr[4]  = $row['cert_name'];
	$arr[5]  = '';
	$arr[6]  = $row['ep_oldname'];
	$arr[7]  = (is_numeric(substr($row['work_code'],-1,1)))?$row['work_code']:(substr($row['work_code'],0,strlen($row['work_code'])-1).'-'.substr($row['work_code'],strlen($row['work_code'])-1));
	$arr[8]  = rtrim($row['industry'],"；");
	$arr[9]  = $row['statecode'];
	$arr[10] = $row['areacode'];
	$arr[11] = $row['ep_addr'];
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
	$arr[24] = get_code($row['audit_code']);
	$arr[25] = $audit_ver_array[$row['audit_ver']]['audit_basis'];
	$arr[26] = $row['union_count']>0?"1":"0";
	$arr[27] = $row['prod_addr'];
	$arr[28] = $row['cert_scope'];
	$arr[29] = ($row['audit_ver']=="A010201")?$row['cert_scope']:'';
	$arr[30] = '';
	$arr[31] = get_audit_type($row['audit_type']);
	$arr[32] = $cti_info['renum'];
	$arr[33] = get_audit_num($row[audit_type]);
	$arr[34] = substr($results['tb_date'],0,10);
	$arr[35] = substr($row['te_date'],0,10);
	$arr[36] = $tk_num;
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
	$arr[43] = "";
	$arr[44] = "";
	$arr[45] = "";
	$arr[46] = "";

	$arr[48] = empty($zicertno)?'0':'1';
	$arr[49] = '';
	if($row['main_certno']){
		$arr[48] = '1';
		$arr[49] = $row['main_certno'];
	}

	$arr[52] = $row['is_change'];
	$arr[53] = $row['change_date'];
	$arr[54] = $row['change_type'];
	$arr[55] = $row['old_cert_name'];
	$arr[56] = $row['old_certno'];
	if(!$row['is_change']){
		$arr[52]='0';
		$arr[53]=$arr[54]=$arr[55]=$arr[56]='';
	}

	$arr[57] = '';
	$arr[59] = '';

	$arr[60] = "";
	$arr[61] = "01";
	// print_r($row['ct_code']);exit;
	$arr[62] = $row['ct_code'];
	$invoice_query = $db->query("SELECT * FROM `sp_contract_cost_detail` WHERE `ct_id` = '$row[ct_id]' AND `deleted` = '0' AND `iso` LIKE '%$row[iso]%' AND audit_type LIKE '%$row[audit_type]%' AND `invoice` <> '' ");
	if($invoice_query){
		$invoice     = "";
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

	$arr[62] = "";
	$arr[63] = "";
	$arr[65] = ($row[is_change])?"02":"01";
	$arr[72] = $row['base_num'];
	$arr[73] = substr($begindate,0,10);
	$arr[74] = substr($enddate,0,10);

	$arr_data_zsInfo[] = $arr;
}
// echo '<pre />';
// print_r($arr_data_zsInfo);exit;
/**===========证书=======**/


/**===========证书审核组=============**/
$pids[] = -1;
$pids = array_filter(array_unique($pids));

//sql初始化
$join = $select = '';$where = ' where 1';

//审核计划派人明细表
$sql    = "select %s from `sp_task_audit_team` tat";
$select.= "tat.iso,tat.pid,tat.uid,tat.tid,tat.audit_type,tat.qua_type,tat.role,tat.witness,tat.taskBeginDate,tat.taskEndDate,tat.name,tat.use_code";
$where .= " and tat.pid IN (".implode(',',$pids).") and tat.deleted = 0";

//人员表
$select.= ",hr.card_type,hr.card_no,hr.audit_job";
$join  .= " left join sp_hr hr ON hr.id = tat.uid";
$where .= " and hr.deleted = '0'";

//审核项木表
$select.= ",p.cti_id,p.eid";
$join  .= " left join sp_project p ON p.id = tat.pid";
$where .= " and p.deleted = '0'";

//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
$query = $db->query( $sql );
while( $row = $db->fetch_array( $query ) ){
	$a13=0;$a15="";$arr = array();
	if($row['qua_type']!='03' and $row['use_code'])$a13=1;
	if($row['witness'])$a14="01";

	$_query = $db->query("select witness_person FROM `sp_task_audit_team` where `tid` =".$row['tid']." and `witness_person` IS NOT NULL and `witness_person`<>'' AND deleted=0");
	while($r=$db->fetch_array( $_query )){
		if(strpos($r['witness_person'],$row['name']))$a14="02";
	}

	$cert_query=$db->query("select certno from sp_certificate where cti_id = ".$row['cti_id']." and  eid='$row[eid]' and deleted=0");
	while($_r1=$db->fetch_array( $cert_query )){
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

		$arr[11] = $row['qua_type'];
		$arr[12] = $db->get_var("select qua_no from `sp_hr_qualification` where `uid`=".$row['uid']." and `iso` = '".$row['iso']."' and status=1");
		$arr[13] = $a13;
		$arr[14] = ($row['audit_job']==1)?1:0;
		$arr[15] = $a15;	

		if($arr[12])$arr_data_shInfo[] = $arr;
	}
}
// echo '<pre />';
// print_r($arr_data_shInfo);exit;
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