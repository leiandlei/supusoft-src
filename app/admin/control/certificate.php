<?php
require_once( ROOT . '/data/cache/certreplace.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' );
require_once( ROOT . '/data/cache/audit_type.cache.php' );
require_once( ROOT . '/data/cache/audit_ver.cache.php' );
require_once( ROOT . '/data/cache/ctfrom.cache.php' );

$certificate =load('certificate');

$zsid = getgp('zsid');
$step = getgp('step');
$audit_ver = getgp('audit_ver');
$iso = (int)getgp( 'iso' );

$iso_select =f_select('iso');//体系下拉
$audit_ver_select = f_select('audit_ver');
$province_select = f_province_select();//省分下拉 (搜索用)
// p($certreplace_array);
if( $certreplace_array ){
	foreach( $certreplace_array as $code => $item ){
		$certreplace_select .= "<option value=\"$code\">$item[code]-$item[name]</option>";
	}
}


//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
}else
	echo "ERROR";