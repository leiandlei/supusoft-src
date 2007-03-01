<?php
/*
*计算基础人日
*/
$cti_id=getgp("cti_id");
$level=getgp("level");
if($cti_id && $level){
//查询体系人数,体系
	$cti_info=$db->get_row("SELECT total,iso FROM `sp_contract_item` where cti_id='$cti_id'");
	extract($cti_info);
	$filed="num_m";
	if($level=="01")
		$filed="num_h";
	elseif($level=="02")
		$filed="num_m";
	else
		$filed="num_l";
	if($iso=='A01'){
		$base_num=$db->get_var("SELECT num_l FROM `sp_enterprises_base` WHERE `ep_amount` >= '$total' and iso ='1' limit 1");
	}else{
		$base_num=$db->get_var("SELECT $filed FROM `sp_enterprises_base` WHERE `ep_amount` >= '$total' and iso ='2' limit 1");
	}
	echo $base_num;
	exit;
}
