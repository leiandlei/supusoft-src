<?php
$report=excel_read(CONF."imp/zj_name.xlsx");
foreach($report as $s=>$_r){
foreach($_r as $k=>$_val){
$_val['B']=trim($_val['B']);
$_val['A']=trim($_val['A']);
if($k<2 or !$_val['B']) continue;
$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$_val[B]'");
if(!$uid)
$uid=$db->insert("hr",array("name"=>$_val['B'],"ctfrom"=>"01000000","is_hire"=>'1',"sex"=>'1'));
$f=$db->get_var("SELECT id FROM `sp_stff` WHERE `code` = '$_val[A]'");
if($f)
	$f=$db->update("stff",array("zj_uid"=>$uid,"zj_name"=>$_val['B']),array("code"=>trim($_val['A'])));
else	
	$db->insert("stff",array("code"=>$_val['A'],"zj_uid"=>$uid,"zj_name"=>$_val['B'])) && $i++;




}
}
echo $i; 
?>