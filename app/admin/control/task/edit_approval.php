<?php

$tid = (int)getgp( 'tid' );

//取任务信息
$sql = "SELECT t.*,e.ep_name FROM sp_task t INNER JOIN sp_enterprises e ON e.eid = t.eid WHERE t.id = '$tid'";
$task = $db->get_row( $sql );

//是否可审批
//如果ID不正确也不能使用
$disabled = ( $task['status'] != 2 ) ? ' disabled' : '';

//是否可撤销审批
$disabled_unapproval = ( $task['status'] == 3 ) ? '' : ' disabled';


$curr_date = mysql2date( 'Y-m-d', current_time( 'mysql' ) );

$approval_date = ($task['approval_date'])?$task['approval_date']:$curr_date;
$approval_note = ($task['approval_note'])?$task['approval_note']:'';


$datas=array();
$query=$db->query("SELECT id,ct_id,audit_type,iso,ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$ct_ids=array();
while($rt=$db->fetch_array($query)){
	$rt[audit_type]=="1002" && $rt[audit_type]="1003";
	$r=$db->get_row("SELECT * FROM `sp_contract_cost` WHERE `iso` like '%$rt[iso]%' AND `cost_type` like '%$rt[audit_type]%' AND `ct_id` = '$rt[ct_id]'");
	$rt['cost_type']=read_cache("cost_type",$rt['audit_type']);
	$rt[iso]=f_iso($rt[iso]);
	$rt[id]=$r[id];
	$datas[]=$rt;
	$ct_ids[$rt[ct_id]]=$rt[ct_id];
}
sort($ct_ids);
$ct_id=array_pop($ct_ids);
/*
//合同中需要收费的信息
$sql = "select * from sp_contract_cost where ct_id IN (".join(",",array_unique($ct_ids)).") AND cost > 0 and cost_type IN ('".join("','",$cost_type)."') and deleted = 0 order by id asc";
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	$iso_arr = explode('|', $row['iso']);
	foreach($iso_arr as $key=>$value){
		$iso_arr[$key] = read_cache('iso',$value);
	}
	if(strlen($row['iso'])==3 && !in_array($row['iso'],array_keys($cost_type)))
		continue;
	$row['iso'] = implode('<br>',$iso_arr);
	$row['cost_type'] = read_cache('cost_type',$row['cost_type']);
	$datas[] = $row;
}
*/
tpl( 'task/edit_approval' );



 


?>