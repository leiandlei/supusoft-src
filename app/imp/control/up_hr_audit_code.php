<?php
/*
* 给人员加代码
*/
$arr=array("徐雁","陈喜群","张号","张子健","许航","周自胜","刘忠文","黄小武","王玫","黄盛仁","赵戍征","乐树林","肖自谷","郑劲松","周京萍","雷建忠","李爱平","戴晖毅");
$code="E-19-04-03";
$iso="A02";
foreach($arr as $name){
	$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$name'");
	$qua=$db->get_row("SELECT id,qua_type FROM `sp_hr_qualification` WHERE `uid` = '$uid' AND `iso` = '$iso' ORDER BY `e_date` DESC");
	$hac_id=$db->get_var("SELECT id FROM `sp_hr_audit_code` WHERE `use_code` = '$code' AND `uid` = '$uid' AND `deleted` = '0'");
	if(!$hac_id){
			$db->insert("hr_audit_code",array("uid"=>$uid,"use_code"=>$code,"iso"=>$iso,"qua_id"=>$qua[id],"qua_type"=>$qua[qua_type],"ctfrom"=>"01000000","hr_is_hire"=>'1',"hqa_status"=>"1")) && $j++;
		}else{
			
			echo $name."<br/>";
		}
		
}
echo $j;
	

