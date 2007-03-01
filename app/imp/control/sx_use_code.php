<?php

$query = $db->query("SELECT audit_code,cti_id,iso FROM `sp_contract_item`");
while( $val = $db->fetch_array( $query ) ){
	if(substr($val[audit_code],2,1)=='.'){
		$r="";
		$audit_code=str_replace(array(";","｜","|"),"；",$val[audit_code]);
		$r=explode("；",$audit_code);
		$use_code="";
		foreach($r as $_val){
			$c=$db->get_var("SELECT code FROM `sp_settings_audit_code` WHERE `shangbao` = '$_val' and iso='$val[iso]'");
			$use_code.=$c."；";
			unset($c);
		}
		$use_code=rtrim($use_code,'；');
		$db->update("contract_item",array("audit_code"=>$audit_code,"use_code"=>$use_code),array("cti_id"=>$val[cti_id]));
	}else{

		$r="";
		$use_code=str_replace(array(";","｜","|"),"；",$val[audit_code]);
		$r=explode("；",$use_code);
		$audit_code="";
		foreach($r as $_val){
			$c=$db->get_var("SELECT code FROM `sp_settings_audit_code` WHERE `code` = '$_val' and iso='$val[iso]'");
			$audit_code.=$c."；";
			unset($c);
		}
		$audit_code=rtrim($audit_code,'；');
		$db->update("contract_item",array("audit_code"=>$audit_code,"use_code"=>$use_code),array("cti_id"=>$val[cti_id]));
	
	
	}

}
file_put_contents(DATA_DIR."imp/".date("Y-m-d").".log",$n++."、contract_item|".date("Y-m-d H:i:s")."\r\n",FILE_APPEND);

$tables=array("project","task_audit_team","certificate");
foreach($tables as $table){
$query = $db->query("SELECT audit_code,id,iso FROM sp_".$table);
while( $val = $db->fetch_array( $query ) ){
	if(substr($val[audit_code],2,1)=='.'){
		$r="";
		$audit_code=str_replace(array(";","｜","|"),"；",$val[audit_code]);
		$r=explode("；",$audit_code);
		$use_code="";
		foreach($r as $_val){
			$c=$db->get_var("SELECT code FROM `sp_settings_audit_code` WHERE `shangbao` = '$_val' and iso='$val[iso]'");
			$use_code.=$c."；";
			unset($c);
		}
		$use_code=rtrim($use_code,'；');
		$db->update($table,array("audit_code"=>$audit_code,"use_code"=>$use_code),array("id"=>$val[id]));
	}else{

		$r="";
		$use_code=str_replace(array(";","｜","|"),"；",$val[audit_code]);
		$r=explode("；",$use_code);
		$audit_code="";
		foreach($r as $_val){
			$c=$db->get_var("SELECT code FROM `sp_settings_audit_code` WHERE `code` = '$_val' and iso='$val[iso]'");
			$audit_code.=$c."；";
			unset($c);
		}
		$audit_code=rtrim($audit_code,'；');
		$db->update($table,array("audit_code"=>$audit_code,"use_code"=>$use_code),array("id"=>$val[id]));
	
	
	}

}
file_put_contents(DATA_DIR."imp/".date("Y-m-d").".log",$n++."、".$table."|".date("Y-m-d H:i:s")."\r\n",FILE_APPEND);

}
echo "SUCCESS";