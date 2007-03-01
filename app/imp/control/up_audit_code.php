<?php 
/*
*首先将文件名改成中文 用excel_read读取数据
*/
set_time_limit(0);
$report=excel_read(CONF."imp/code.xls");
$temp=$temp_auditor=array();
foreach($report as $s=>$_r){
	if($s=='Sheet1')
		$iso='A01';
	if($s=='Sheet2')
		$iso='A02';
	if($s=='Sheet3')
		$iso='A03';
	$i=0;
foreach($_r as $k=>$_val){
	if($k<2) continue;
	// foreach($item as $_k=>$_v){
		// if(in_array($_k,array("V","AL","AO","AP","AX","AY","BB","BC","BE","BF")))
			// $item[$_k]=f_date($_v);
	
	// }
	if($_val[D]=='二级' || $_val[D]=='二')
		$risk_level="02";
	elseif($_val[D]=='一级' || $_val[D]=='一')
		$risk_level="01";
	elseif($_val[D]=='三级' || $_val[D]=='三')
		$risk_level="03";
	else
		$risk_level="03";
	$mark="";
	if($_val[E]=='是')
		$mark="01";
	$_val[C]=str_replace("．",".",$_val[C]);
	$res=array();
	preg_match("/[\d|\.]+/",$_val[C],$res);
	$audit_code=$res[0];
	if(!$audit_code or strlen($audit_code)<8)continue;
	$msg=trim(str_replace($audit_code,"",$_val['C']));
	if($_val[A]){
		$new_code=$temp=array("code"=>$_val[A],
					"risk_level"=>$risk_level,
					"industry"=>$_val[B],
					"mark"=>$mark
					);
	}else
		$new_code=$temp;
	$where=array('shangbao'=>$audit_code,'iso'=>$iso);
	$f=$db->find_one("settings_audit_code",$where);
	if(!$f){
		$new_code=array_merge($new_code,$where);
		$new_code['msg']=$msg;
		$res=explode(".",$audit_code);
		$new_code['dalei']=$res[0];
		$new_code['xiaolei']=$res[2];
		$new_code['zhonglei']=$res[1];
		$db->insert("settings_audit_code",$new_code) && $i++;
		file_put_contents("data/log/set_log.log",$new_code[code]."--".$audit_code."\r\n",FILE_APPEND);
	}
	if($_val['F']){
		$_val['F']=str_replace(" ","、",$_val['F']);
		$temp_auditor=$auditor_array=explode("、",$_val['F']);
	}
	else{
		$auditor_array=$temp_auditor;
	}
	foreach($auditor_array as $name){
		$name=trim($name);
		$uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$name'");
		$hac_id=$db->get_var("SELECT id FROM `sp_hr_audit_code` WHERE `use_code` = '$new_code[code]' AND `uid` = '$uid' AND `deleted` = '0'");
		if(!$uid){
			$str="ERRER--$name \r\n";
			file_put_contents("data/log/error.log",$str,FILE_APPEND);
			continue;
		}
		$qua=$db->get_row("SELECT id,qua_type FROM `sp_hr_qualification` WHERE `uid` = '$uid' AND `iso` = '$iso' ORDER BY `e_date` DESC");
		if(!$hac_id){
			$db->insert("hr_audit_code",array("uid"=>$uid,"use_code"=>$new_code[code],"iso"=>$iso,"qua_id"=>$qua[id],"qua_type"=>$qua[qua_type],"ctfrom"=>"01000000","hr_is_hire"=>'1',"hqa_status"=>"1")) && $j++;
			$str=$name."---".$new_code[code]."---".$audit_code."\r\n";
			file_put_contents("data/log/log.log",$str,FILE_APPEND);
		}
	}
}
echo $iso."set".$i."--hr_set_code".$j."<br/>";
}
ECHO "SUCCESS";
?>