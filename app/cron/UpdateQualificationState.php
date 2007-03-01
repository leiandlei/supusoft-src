<?php
/*
 *	将过期的注册资格设置为失效
 *
 *
 */
set_time_limit(0);
$query = $db->query("SELECT uid,iso FROM sp_hr_qualification WHERE e_date <  '".mysql2date('Y-m-d',current_time('mysql'))."' AND status = '1'");
while( $rt = $db->fetch_array( $query ) ){
	$db->query("UPDATE sp_hr_audit_code SET hqa_status = 0 WHERE uid = $rt[uid] AND iso = '$rt[iso]'");
}

$db->query( "UPDATE sp_hr_qualification SET status = '0' WHERE e_date < '".mysql2date('Y-m-d',current_time('mysql'))."' AND status = '1'" );


?>