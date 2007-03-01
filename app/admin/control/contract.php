<?php
/*
 *合同管理 控制文件
 *
 *
 */
require_once( ROOT . '/data/cache/nature.cache.php' );
 
require_once( ROOT . '/data/cache/statecode.cache.php' );
require_once( ROOT . '/data/cache/industry.cache.php' );
require_once( ROOT . '/data/cache/currency.cache.php' );
require_once( ROOT . '/data/cache/region.cache.php' );
require_once( ROOT . '/data/cache/iso.cache.php' );
require_once( ROOT . '/data/cache/audit_type.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' );
require_once( ROOT . '/data/cache/audit_ver.cache.php' );
require_once( ROOT . '/data/cache/risk_level.cache.php' ); 
 
$step	= getgp('step'); 
$et		= load( 'enterprise' );
$ct		= load( 'contract' );
$cti	= load( 'contract.item' ); 

$ctfrom_select = f_ctfrom_select();//合同来源
$province_select = f_province_select();//省分下拉 (搜索用)

//审核类型
$audit_type_select = '';
if( $audit_type_array ){
	foreach( $audit_type_array as $code => $item ){
		if( in_array( $code, array( '1001', '1004','1005' ,'1007') ) )
		$audit_type_select .= "<option value=\"$code\">$item[name]</option>";
	}
}

//认证标志
$mark_add_checkbox = $mark_checkbox = $signe_select='';
if( $mark_array ){
	foreach( $mark_array as $code => $item ){
		if($item['is_stop']==0) {
			$mark_checkbox .= "<label><input type=\"checkbox\" name=\"marks[]\" class=\"mark-item\"  value=\"$code\"/>$item[name]</label> &nbsp; ";
			$mark_add_checkbox .= "<label><input type=\"checkbox\" name=\"add[mark][]\" class=\"mark-item\" value=\"$code\" />$item[name]</label> &nbsp; ";
		}
	}
}
$iso_select=f_select('iso');//体系
$audit_ver_select = f_select('audit_ver');//体系版本

$risk_level_select =f_select('risk_level');//风险等级

unset( $code, $item );
$signe_names=$db->get_results("SELECT name,id FROM `sp_hr` WHERE `signe_name` = '1'");
foreach($signe_names as $val){
	$signe_select.="<option value=\"$val[name]\">$val[name]</option>"; 
}
//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
} 
 
?>