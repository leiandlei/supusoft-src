<?php
//审核员公告
$notices = array();
$total=$db->get_var("SELECT COUNT(*) FROM sp_notice  WHERE status=1 and type=2");
$pages = numfpage( $total, 20);
$query = $db->query( "SELECT n.*,hr.name author FROM sp_notice n INNER JOIN sp_hr hr ON hr.id = n.create_uid WHERE n.status=1 and n.type=2 ORDER BY id DESC $pages[limit]" );
while( $rt = $db->fetch_array( $query ) ){
	$rt['filename'] = substr($rt['filename'],strlen($rt['id'].'_') );
	//$rt['filename']
	$notices[] = $rt;
}
tpl();
?>
