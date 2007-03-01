<?php
/*
*
*/
$id = getgp('id');
$ajax=getgp('ajax');
$is_sms=getgp("is_sms");
if($ajax){
	if($step=getgp("step")){
		$is_sms==1?$is_sms=0:$is_sms=5;
		$cert=$db->query("update sp_sms set is_sms=$is_sms where id='$id'");
	}
	else
		$cert=$db->query("update sp_sms set is_sms=1 where id='$id'");
	if($cert)
		echo "ok";
	exit;
}
