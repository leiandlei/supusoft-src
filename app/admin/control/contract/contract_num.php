<?php 
function do_exp($cs,$item,$type,$ct_id){
global $db;
	if(!is_array($cs)) return false;
	foreach($cs as $eid=>$_val){
		if($_val===false) continue;
		$new_num=array(	"eid"=>$eid,
						"ct_id"=>$ct_id,
						$item=>$_val,
						"type"=>$type,
						);
		$num_info=$db->get_row("SELECT id FROM `sp_contract_num` WHERE eid='$eid' AND ct_id='$ct_id' and type='$type'");
		if($num_info[id])
			$db->update("contract_num",$new_num,array("id"=>$num_info[id]));
		else{
			// $new_num[create_uid]=current_user("uid");
			$db->insert("contract_num",$new_num);
			}
		


	}
}
if($_POST){
// $cs=getgp("cs");
// $jy=getgp("jy");
// $je=getgp("je");
// $js=getgp("js");
foreach($_POST as $k=>$v){
	do_exp($v,$k,$_POST[type],$_POST[ct_id]);
}


}
showmsg("success","success","?c=contract&a=review&ct_id=$_POST[ct_id]&eid=$_POST[eid]");	