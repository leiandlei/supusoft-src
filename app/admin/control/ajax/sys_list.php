<?php
/*
 *冻结、解冻帐号---权限管理=>系统管理
 */
$uids= getgp('uid');
$type=getgp("type");
if($type=="1"){
	//冻结
	$db->query("UPDATE sp_hr SET is_stop=0 WHERE id in(".join(",",$uids).")");

}else{
	$db->query("UPDATE sp_hr SET is_stop=1 WHERE id in(".join(",",$uids).")");
}
showmsg("success","success","?c=sys&a=list");