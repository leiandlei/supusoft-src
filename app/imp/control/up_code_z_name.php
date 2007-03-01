<?php
$report=excel_read(CONF."imp/code_eg6.12.xls");
foreach($report as $s=>$_r){
	foreach($_r as $k=>$_val){
	$_val['B']=trim($_val['B']);
	$_val['A']=trim($_val['A']);
	if($k<2 or !$_val['B']) continue;
	if($_val[E]=='二级' || $_val[E]=='二')
		$risk_level="02";
	elseif($_val[E]=='一级' || $_val[E]=='一')
		$risk_level="01";
	elseif($_val[E]=='三级' || $_val[E]=='三')
		$risk_level="03";
	else
		$risk_level="03";
	$mark="";
	if($_val[F]=='是')
		$mark="01";
	if(strpos("Q",$_val[A])!==false)
		$iso="A01";
	elseif(strpos("E",$_val[A])!==false)
		$iso="A02";
	else
		$iso='A03';
	$res=explode(".",$_val[C]);
	$new_code=$temp=array("code"=>$_val[A],
						"shangbao"=>$_val[C],
						"iso"=>$iso,
						"risk_level"=>$risk_level,
						"industry"=>$_val[B],
						"msg"=>$_val[D],
						"mark"=>$mark,
						"dalei"=>$res[0],
						"zhonglei"=>$res[1],
						"xiaolei"=>$res[0],
						);
	$c_id=$db->get_var("SELECT id FROM `sp_settings_audit_code` WHERE `shangbao` = '$_val[C]' and iso='$iso'");
	if($c_id)
		$db->update("settings_audit_code",$new_code,array("id"=>$c_id));
	else
		$db->insert("settings_audit_code",$new_code) && $i++;
	$auditors=explode("、",$_val[G]);
	foreach($auditors as $v){
		$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$v'");
		$qua=$db->get_row("SELECT * FROM `sp_hr_qualification` WHERE `uid` = '$uid' AND `iso` = '$iso'");
		$hac=$db->get_row("SELECT * FROM `sp_hr_audit_code` WHERE `uid` = '$uid' AND `use_code` = '$_val[A]'");
		$new_hac=array(	"uid"		=>$uid,
						"iso"		=>$iso,
						"qua_id"	=>$qua[id],
						"qua_type"	=>$qua[qua_type],
						"use_code"	=>$_val[A],
						"ctfrom"	=>"01000000",
						);
		if($hac[id])
			$db->update("hr_audit_code",$new_hac,array("id"=>$hac[id]));
		else
			$db->insert("hr_audit_code",$new_hac) && $j++;
		
	}
	$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$_val[H]'");
	$s_id=$db->get_var("SELECT id FROM `sp_stff` WHERE `code` = '$_val[A]'");
	if($s_id)
		$db->update("stff",array("zy_uid"=>$uid,"zy_name"=>$_val['H']),array("id"=>$s_id));
	else	
		$db->insert("stff",array("code"=>$_val['A'],"zy_uid"=>$uid,"zy_name"=>$_val['H'])) && $k++;




	}
}
echo "settings_audit_code $i   <br/>"; 
echo "hr_audit_code $j   <br/>"; 
echo "stff $k   <br/>"; 
?>