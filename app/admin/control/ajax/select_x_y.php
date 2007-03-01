<?php
$eid=getgp("eid");
if($_POST){
	$xvalue=getgp("xvalue");
	$yvalue=getgp("yvalue");
	$db->update("enterprises",array("xvalue"=>$xvalue,"yvalue"=>$yvalue),array("eid"=>$eid));
}
$ep_name = $db->get_var("SELECT ep_name FROM `sp_enterprises` WHERE `eid` = '$eid'");
$add=getgp("add");
!$ep_name && $ep_name= "北京";
!$add && $add= $ep_name;

tpl();