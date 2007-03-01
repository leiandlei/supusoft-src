<?php
/*
 *功能描述：修改发放通知时间-应用场景：证书变更查询
 */
$id= getgp('id');
$tongzhi_date=getgp("tongzhi_date");
if($id && $tongzhi_date){
	$db->update("certificate_change",array("tongzhi_date"=>$tongzhi_date),array("id"=>$id));
}
