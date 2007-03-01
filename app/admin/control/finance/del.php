<?php
//财务明细删除
    $ccd_id=getgp('ccd_id');
	$ct_id=getgp('ct_id');
	$cost_id=$db->get_var("SELECT cost_id FROM `sp_contract_cost_detail` WHERE `ct_id` = '$ct_id'");
	$sql = "update `sp_contract_cost_detail` set `deleted`=1 where `id`=$ccd_id";
	$db->query( $sql );
	$sql = "update `sp_contract_cost` set `status`=0 where `id`=$cost_id";
	$db->query( $sql );
	log_add($p_res[eid], current_user("uid"), "财务收费删除", NULL, serialize($bf));
	$REQUEST_URI='?c=finance&a=dlist';
	showmsg( 'success', 'success', $REQUEST_URI );
	