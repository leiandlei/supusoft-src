<?php
/*
*检查合同号是否存在
*/
$ct_code = getgp('ct_code');
if($ct_code){
	$res=$db->get_row("SELECT ct_id FROM `sp_contract` WHERE `ct_code`='$ct_code'");
	if($res)
		echo "ok";
	exit;

}elseif($cti_id=getgp("cti_id")){
	
	if($db->update("contract_item",array("deleted"=>1),array("cti_id"=>$cti_id)))
		echo "ok";
	exit;
	
}

