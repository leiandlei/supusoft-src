<?php
/*
*子证书范围保存
*/
//子证书范围
require CONF.'/cache/iso.cache.php';
$pd_id = ( int ) getgp ( 'pd_id' );
$tid = ( int ) getgp ( 'tid' );
$ct_id = ( int ) getgp ( 'ct_id' );
if($scope=getgp("scope"))
foreach($scope as $id=>$val){
	$db->update("contract_num",array("scope"=>$val),array("id"=>$id));
}
showmsg ( 'success', 'success', "?c=assess&a=edit&pd_id=$pd_id&ct_id=$ct_id&tid=$tid#tab-edit" );
