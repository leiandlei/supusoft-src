<?php
require_once( ROOT . '/data/cache/audit_type.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' );
require_once( ROOT . '/data/cache/risk_level.cache.php' );
require_once( ROOT . '/data/cache/iso.cache.php' );

 
$step = (int)getgp('step');

$et = load('enterprise');
$ct = load('contract');
$cti = load('contract.item');
$audit = load('audit');
$task = load('task'); 
//合同来源
$ctfrom_select = f_ctfrom_select(); 
$province_select = f_province_select();//省分下拉 (搜索用)

//认证体系
$iso_select = f_select('iso');
 
 
$audit_type_select=f_select("audit_type");

//认证标志
$mark_add_checkbox = $mark_checkbox = '';
if( $mark_array ){
	foreach( $mark_array as $code => $item ){
		$mark_checkbox .= "<label><input type=\"checkbox\" name=\"marks[]\" class=\"mark-item\"  value=\"$code\"/>$item[name]</label> &nbsp; ";
		$mark_add_checkbox .= "<label><input type=\"checkbox\" name=\"add[mk][]\" class=\"mark-item\" value=\"$code\" />$item[name]</label> &nbsp; ";
	}
} 
//体系版本
$audit_ver_select = f_select('audit_ver');

//风险等级
$risk_level_select = '';
if( $risk_level_array ){
	foreach( $risk_level_array as $item ){
		$risk_level_select .= "<option value=\"$item[code]\">$item[code] | $item[name]</option>";
	}
}

unset( $code, $item );
//引入模块控制下的方法
$action=CTL_DIR.$c.'/'.$a.'.php'; 
if(file_exists($action)){
	include_once($action); 
}else{
	echo '该方法不存在，请检查对应程序';
	echo '<br />方法名称：'.$a;	
}
?>