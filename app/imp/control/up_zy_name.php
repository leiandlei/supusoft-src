<?php
//维护专业管理人员
$report=excel_read(CONF."imp/zy_name.xlsx");
foreach($report as $s=>$_r){
foreach($_r as $k=>$_val){
$_val['B']=trim($_val['B']);
if($k<2 or !$_val['B']) continue;
$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$_val[B]'");
if(!$uid)
$uid=$db->insert("hr",array("name"=>$_val['B'],"ctfrom"=>"01000000","is_hire"=>'1',"sex"=>'1'));
$db->insert("stff",array("code"=>trim($_val['A']),"zy_uid"=>$uid,"zy_name"=>$_val['B'])) && $i++;




}
}
echo $i; 
?>