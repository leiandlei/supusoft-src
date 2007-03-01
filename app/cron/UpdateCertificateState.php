<?php
/*
 *	将过期的证书设置为失效
 *
 *
 */
$date=date("Y-m-d");

$query=$db->query( "SELECT * FROM `sp_certificate` WHERE `e_date` < '$date'  AND status IN ('01','02') AND is_check='y'" );
while($rt=$db->fetch_array($query)){
	$pid=$db->get_var("SELECT p.id FROM sp_project p LEFT JOIN sp_task t ON t.id=p.tid WHERE cti_id='$rt[cti_id]' AND p.deleted=0 AND p.tid<>0 ORDER BY t.te_date DESC");
	$new = array(
			'zsid'			=> $rt[id],	//证书id
			'cg_pid'		=> $pid,	//变更关联项目id
			'iso'			=> $rt['iso'],	//体系
			'audit_type'	=> $rt['audit_type'],	//体系
			'audit_ver'		=> $rt['audit_ver'],	//标准版本
			'ctfrom'		=> $rt['ctfrom'],	//合同来源
			'cg_type'		=> "97_05",	//变更类型
			'cg_type_report'=> "97",	//上报类型
			'cg_meta'		=> "97_05",	//变更字段
			'cg_af'			=> $rt[status],	//变更前
			'cg_bf'			=> "05",	//变更后
			'cgs_date'		=> $rt[e_date],	//变更日期
			'pass_date'		=> $rt[e_date],	//
			'status'		=> "1",	//状态
			'note'		=> "系统生成",	//状态
		);
	$cg_id=$db->get_var("SELECT id FROM `sp_certificate_change` WHERE `zsid` = '$rt[id]' AND `deleted` = '0' AND `cg_type` = '97_05'");
	if(!$cg_id)
		$db->insert("certificate_change",$new);
	$db->update("certificate",array("status"=>'05'),array("id"=>$rt[id]));


}

?>