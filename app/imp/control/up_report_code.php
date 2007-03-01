<?php
// echo date("M.d,Y");
// echo date("F d,Y");
$db->query("DELETE FROM sp_settings WHERE type in ('certrecall','certpasue') AND length(code)=4");
$db->query("UPDATE sp_settings SET is_stop=1 WHERE type in ('certrecall','certpasue') AND length(code)=2");
$db->del("settings",array("type"=>"certpasue"));
$res=excel_read("data/imp/report.xls");
foreach ($res as $sheet=>$v){
	if($sheet=='Sheet1'){
		$type="certrecall";
		
	}
	if($sheet=='Sheet2'){
		$type="certpasue";
		
	}
	
	foreach( $v as $item){
		$vieworder=substr($item['A'],-2);
		$db->query("INSERT INTO sp_settings SET type = '$type' , code = '{$item['A']}' , name = '{$item['B']}' , vieworder = '$vieworder'") && $i++;
	}
	
}
echo $i;





