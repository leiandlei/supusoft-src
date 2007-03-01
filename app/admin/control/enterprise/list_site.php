<?php
//分场所查询
$eid = (int)getgp( 'eid' );

$e_sites = array();
$sql = "SELECT es.*,e.ep_name,e.ctfrom FROM sp_enterprises_site es INNER JOIN sp_enterprises e ON e.eid = es.eid WHERE es.eid = '$eid'";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$rt['es_type_V'] = f_es_type( $rt['es_type'] );
	$e_sites[$rt['es_id']] = $rt;
}

tpl( 'enterprise/list_site' );
?>