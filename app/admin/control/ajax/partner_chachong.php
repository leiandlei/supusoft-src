<?php
//合作方查重
$name = getgp('name');
$if_exist = 0;
$sql = "select * from sp_partner where deleted=0 and name='".$name."'";
$arr = $db->get_row($sql);
if (!empty($arr)) {
	$if_exist = 1;
}else{
	$if_exist = 2;
}
echo json_encode($if_exist);
?>