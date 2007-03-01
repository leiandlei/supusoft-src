<?php


$id=getgp('id');
	$ct_id = getgp('ct_id');
	$af=$ctc->get($id);
	$ctc->del($id);
	log_add($af[eid], current_user("uid"), "[合同收费]合同收费修改", serialize($af), serialize($bf));
	$REQUEST_URI='?c=cost&a=edit&ct_id='.$ct_id;
	showmsg( 'success', 'success', $REQUEST_URI );