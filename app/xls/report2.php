<?php

$a58 = $begindate = getgp('s_date');//58.取数开始日期
$a59 = $enddate = getgp('e_date');//59.取数截止日期
$get_data_arr = $data_arr = array();

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
$arr['a1'] = 'KC-001';

//print_rr($data_arr);
//评定 （评定通过，监督类型,非换证(又取消 认监委的换证指的是证书号码变化)）
//and a.if_cert>'y'
$sql = "SELECT p.id,a.ct_id,a.cti_id
FROM sp_assess a inner join sp_enterprises e on e.eid=a.eid left join sp_project p on p.id=a.pid WHERE a.deleted='0' and a.pd_type='1' and (a.audit_type='1004' or a.audit_type='1005' or a.audit_type='1006') and a.assess_date >= '$begindate' and a.assess_date <= '$enddate' group by a.id ORDER BY a.assess_date DESC";
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	if(!in_array($row['id'], $get_data_arr)){
		$sql = "select certno from sp_certificate where ct_id='$row[ct_id]' and cti_id='$row[cti_id]' and is_check='y' and (status='1' or status='2' or status='4' or status='5') order by id desc limit 1";
		$row['certno'] = $db->get_var($sql);
		$row['pid'] = $row['id'];
		$get_data_arr[] = $row;
	}
}

// 新发证书 （）
$sql="select z.pid,z.certno from sp_certificate z inner join sp_enterprises e on z.eid=e.eid
where z.is_check='y' and z.report_date >='".$begindate."' and z.report_date <='".$enddate."' and (z.status='1' or z.status='2' or z.status='4' or z.status='5') order by z.report_date desc";
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	if(!in_array($row['pid'], $get_data_arr)){
		$get_data_arr[] = $row;
	}
}



$tpl_db = DATA_DIR . '/SUPU201208.mdb';

$target_db = DATA_DIR . 'access/' . mysql2date( 'Y-m-d', current_time( 'mysql') ) . '.mdb';

if( file_exists( $target_db ) ){
	@unlink( $target_db );
}
$is_copyed = copy( $tpl_db, $target_db );
if( !$is_copyed ){
	writeover( $target_db, readover( $tpl_db ) );
}
 


foreach($get_data_arr as $key=>$value){
	$sql = "select h.audit_job,tat.role,tat.audit_code,tat.qua_type,h.card_type,h.card_no, p.audit_type,t.tb_date,t.te_date,ta.name,ta.uid from sp_task t left join sp_task_audit_team tv on tv.tid=t.id left join sp_task_auditor ta on ta.tid=t.id left join sp_task_audit_team tat on tat.auditor_id=ta.id left join sp_project p on tv.pid=p.id left join sp_hr h on h.id=ta.uid where tv.pid='$value[pid]' group by ta.uid ";
	$res = $db->query($sql);
	while($row = $db->fetch_array($res)){
		$sql = "select qua_no from sp_hr_qualification  where qua_type='".$row['qua_type']."' and uid='".$row['uid']."' and status='1' ";
		$qua_no = $db->get_var($sql);
		if($row['witness']==1){
			$witness='01';
		}else if($row['witness']=='0'){
			$witness='02';
		}else{
			$witness='';
		}
		if($row['audit_code']){
			$zhuanye = 1;
		}else{
			$zhuanye = 0;
		}
		if($row['audit_job']){
			$audit_job = 1;
		}else{
			$audit_job = 0;
		}
$ins_arr = array(
	'ZDEP_ID'		=> 'KC-001',
	'ZOSCID'		=> $value['certno'],
	'ZAUDSTADATE'	=> $row['tb_date'],
	'ZAUDENDDATE'	=> $row['te_date'],
	'ZAUDSTATUS'	=> get_audit_type($row['audit_type']),
	'ZGroMemName'	=> $row['name'],
	'ZIDType'		=> $row['card_type'],
	'ZID_NUMBER'	=> $row['card_no'],
	// 此处随便代替
	'ZQuaID'		=> '01',
	'ZMemCertCode'	=> (empty($qua_no)) ? ('1') : ($qua_no),
	// 此处随便代替
	'ZMEMROLE'		=> '01',
	'ZISCERT'		=> $zhuanye,
	'ZISSPECIAL'	=> $audit_job,
	'ZEVIMARK'		=> $witness,
);
		foreach( $ins_arr as $k => $v ){
			$ins_arr[$k] = iconv( 'UTF-8', 'GB2312', $v );
		}
		$ins_arr = access_magic( $ins_arr );
		$sql = "INSERT INTO ZBVERIFYMEM ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";
		//echo "$key | $sql <br/><br/>";
		load('report')->insert( $sql, $target_db );
	}
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
echo 'done';

exit;

//输出Execl文件
/* */
 $filename = iconv( 'UTF-8', 'GB2312', '审核组信息表_').mysql2date( "Y-m-d", current_time( 'mysql' ) ).".xls";

 header("Content-Type: application/vnd.ms-excel");
 header("Content-Disposition: attachment; filename=" . $filename );
 header("Pragma: no-cache");
 header("Expires: 0");


?>
<meta
	http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
TD {
	font-size: 12px;
	vertical-align: middle;
	vnd .ms-excel.numberformat: @;
}
</style>
<table border="1">
	<tr>
		<td>1.认证机构批准号</td>
		<td>2.证书编号</td>
		<td>3.审核阶段</td>
		<td>4.审核开始时间</td>
		<td>5.审核截止日期</td>
		<td>6.审核员姓名</td>
		<td>7.证件类型</td>
		<td>8.证件号码</td>
		<td>9.资格类型</td>
		<td>10.资格证书编号</td>
		<td>11.审核员身份角色</td>
		<td>12.是否专业审核员</td>
		<td>13.是否专职</td>
		<td>14.见证人/被见证人</td>
	</tr>
	<?php
	foreach($get_data_arr as $key=>$value){
		$sql = "select h.audit_job,tat.role,tat.audit_code,tat.qua_type,h.card_type,h.card_no, p.audit_type,t.tb_date,t.te_date,ta.name,ta.uid from sp_task t left join sp_task_audit_team tv on tv.tid=t.id left join sp_task_auditor ta on ta.tid=t.id left join sp_task_audit_team tat on tat.auditor_id=ta.id left join sp_project p on tv.pid=p.id left join sp_hr h on h.id=ta.uid where tv.pid='$value[pid]' group by ta.uid ";
		$res = $db->query($sql);
		while($row = $db->fetch_array($res)){
			$sql = "select qua_no from sp_hr_qualification  where qua_type='".$row['qua_type']."' and uid='".$row['uid']."' and status='1' ";
			$qua_no = $db->get_var($sql);
			if($row['witness']==1){
				$witness='01';
			}else if($row['witness']=='0'){
				$witness='02';
			}else{
				$witness='';
			}
			if($row['audit_code']){
				$zhuanye = 1;
			}else{
				$zhuanye = 0;
			}
			if($row['audit_job']){
				$audit_job = 1;
			}else{
				$audit_job = 0;
			}
			echo "<tr>";
			echo "<td>".'KC-001'."</td>";
			echo "<td>".$value['certno']."</td>";
			echo "<td>".get_audit_type($row['audit_type'])."</td>";
			echo "<td>".$row['tb_date']."</td>";
			echo "<td>".$row['te_date']."</td>";
			echo "<td>".$row['name']."</td>";
			echo "<td>".$row['card_type']."</td>";
			echo "<td>".$row['card_no']."</td>";
			echo "<td>".$row['qua_type']."</td>";
			echo "<td>".$qua_no."</td>";
			echo "<td>".$row['role']."</td>";
			echo "<td>".$zhuanye."</td>";
			echo "<td>".$audit_job."</td>";
			echo "<td>".$witness."</td>";
			echo "</tr>";
		}
	}

	?>
</table>

