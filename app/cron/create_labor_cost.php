<?php
set_time_limit(0);
/*
*为计算劳务费，人日准备数据
*/
$year=date("Y");
$e_date=date("Y-m-d H:i:s");
$s_date=thedate_add($e_date,-5,"month");
$db->query("DELETE FROM sp_tat_temp where taskBeginDate>='$s_date' AND taskEndDate<='$e_date'");
$where =" AND taskBeginDate>='$s_date' AND taskEndDate<='$e_date' and `qua_type` != '03'";
//$where ="  and `qua_type` != '03'";
$query=$db->query("SELECT * FROM `sp_task_audit_team`  where deleted=0 $where");
while($rt=$db->fetch_array($query)){
	$_row=$db->get_row("select id,iso,qua_type from sp_tat_temp where uid='$rt[uid]' and taskBeginDate='$rt[taskBeginDate]' and taskEndDate='$rt[taskEndDate]'");
	if($_row[id]){
		$db->update("tat_temp",array("iso"=>$_row[iso]."|".$rt[iso],'qua_type'=>$_row[qua_type]."|".$rt[qua_type]),array("id"=>$_row[id]));
		$rt[role]=="1001" && $db->update("tat_temp",array("role"=>"1001"),array("id"=>$_row[id]));
	}
	else{
		$rt[id]="";
		$db->insert("tat_temp",$rt);
	
	}
	$_row="";

}
$db->query("OPTIMIZE TABLE sp_tat_temp");
$db->query("REPAIR TABLE `sp_tat_temp` ");