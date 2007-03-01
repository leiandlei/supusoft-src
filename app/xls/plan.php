<?php
set_time_limit(0);
date_default_timezone_set('PRC');
/*
 *---------------------------------------------------------------
 * 导出59项月报
 * 附加说明
 *---------------------------------------------------------------
 */
$data = array();
$code   = get_option("zdep_id");
$s_date = getgp('s_date');//取数开始日期
$e_date = getgp('e_date');//取数截止日期
$s_date.= ' 00:00:00';
$e_date.= " 23:59:59";

//sql初始化
$join = $select = '';$where = ' where 1';

//审核项目表
$sql    = "select %s from `sp_project` p";
$select.= "p.ct_code,p.cti_id,p.cti_code,p.bao_date,p.bao_uid,p.id pid,p.iso,p.audit_type,p.audit_ver";
$where .= " and p.audit_type!='1002' and p.tid != '0' and p.deleted=0";

//审核计划表
$select.= ",t.eid,t.ctfrom,t.id,t.tb_date,t.te_date";
$join  .= " left join sp_task t ON p.tid = t.id";
$where .= " and t.deleted = '0' and t.status = '3' ";

//企业表
$select.= ",e.ep_name,e.person,e.person_tel,e.ep_fax,e.person_tel,e.bg_addr,e.prod_addr,e.work_code,e.areacode,e.statecode";
$join  .= " left join sp_enterprises e ON e.eid = p.eid";
$where .= " and e.deleted = '0'";

//合同项目表
// $select.= ",cti.cti_code";
// $join  .= " left join sp_contract_item cti ON cti.cti_id=p.cti_id";
// $where .= " and cti.deleted = '0'";

//其他条件
if(!empty($s_date)&&!empty($e_date))$where .= " AND t.tb_date>='$s_date' AND t.te_date<'$e_date'";

//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where." ORDER BY t.tb_date DESC";

//获取数据并进行处理
$query = $db->query( $sql);
while( $rt = $db->fetch_array( $query ) ){
	$ep_name = $rt['ep_name'];
	$type="";
	switch( $rt['audit_type'] ){
		case '1003' : $type = '0102'; break;
		case '1004' : 
		case '1005' : $type = '03'; break;
		case '1007' : $type = '0202'; break;
		case '1008' : $type = '03'; break;
		default : $type = ''; break;
	}
	
	$tat_query=$db->query("SELECT tat.uid,hr.name,hr.tel,hr.card_type,hr.card_no,tat.iso,tat.role,tat.qua_type FROM sp_task_audit_team tat LEFT JOIN sp_hr hr ON hr.id = tat.uid  WHERE 1 AND hr.deleted = 0 AND tat.deleted = 0 and tat.pid='$rt[pid]' and tat.role<>'' ORDER BY role");
	$s=$z=array();
	while($_r=$db->fetch_array($tat_query)){
		$quano=$db->get_var("SELECT qua_no FROM `sp_hr_qualification` WHERE `uid` = '$_r[uid]' AND `iso` = '$_r[iso]' AND `qua_type` in ('01','02','03') AND `status` = '1'");
		
		if($_r['qua_type']=='04'){
			$str = $_r['name'];
			//证件类型
			$str .= "，".$_r['card_type'];
			//证件号码
			$str .= "，".$_r['card_no'];
			//联系电话
			$str .= "，".$_r['tel'];
			// $str .= "(".f_iso($rt['iso']).":专家)";
			$z[] = $str;
		} else {
			$str = $quano;
			$str .= '，';
			$str .= $_r['name']; 
			$str .= '，';
			$str .= $_r['role']; 
			$str .= '，';
			$str .= $_r['tel'];
			$s[] = $str;
		}
	}
		// echo "<pre />";
		// print_r($_r);exit;
	$rt['certno']=$db->get_var("SELECT certno FROM `sp_certificate` WHERE `cti_id` = '$rt[cti_id]' AND `deleted` = '0' and status in('01','02') ORDER BY `e_date`");
	$rt['work_code']=str_replace("-",'',$rt['work_code']);
	$a14=$rt['cti_code'];
	if(!in_array($type,array("0101","0102")))
		$a14="";
	$data[] = array(
					 2  => $rt['cti_code']
					,3  => $type
					,4  => $rt['tb_date']
					,5  => $rt['te_date']
					,6  => join("；",$s)
					,7  => join("；",$z)
					,8  => $rt['statecode']
					,9  => $rt['areacode']
					,10 => $rt['work_code']
					,11 => $rt['ep_name']
					,12 => $rt['person']
					,13 => $rt['person_tel']
					,14 => $rt['bg_addr']
					,15 => ($rt['audit_type']=='1004')?'':(($rt['audit_type']=='1005')?'':$rt['ct_code'])
					,16 => ($type=='0102')?'':$rt['certno']
					,17 => $rt['audit_ver']
					,18 => $code
				);
}
// echo '<pre />';
// print_r(CONF);
// echo '<br />';
//输出文件
require_once ROOT.'/theme/Excel/myexcel.php';
$tmplate = CONF.'plan.xls';
$tmpName = $ep_name.getgp('s_date').'-'.getgp('e_date').'计划上报.xls';
$tmpPath = 'data/report_data/';
$excel   = new Myexcel($tmplate);
$excel  -> addDataFromArray($data,8);
$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
echo $saveName['oldName'];
exit;
?>
