<?php
//财务明细删除
$ccd_id=getgp('ccd_id');
	$ct_id=getgp('ct_id');
	$pid=$db->get_var("SELECT pid FROM `sp_contract_cost_detail` WHERE `id` = '$ccd_id'");
	$ctcf->del($ccd_id);
	$audit->edit($pid,array('is_finance'=>0));
	$bf=$ctcf->get($ccd_id);
	log_add($p_res[eid], current_user("uid"), "财务收费删除", NULL, serialize($bf));
	$REQUEST_URI='?c=finance&a=dlist';
	showmsg( 'success', 'success', $REQUEST_URI );