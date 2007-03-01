<?php
// 【评定管理】【资料回收】
require_once( ROOT . '/data/cache/audit_type.cache.php' );

$step = getgp('step');
$upload_done_date=getgp( 'comment_finish_date' );

$et = load('enterprise');
$ct = load('contract');
$cti = load('contract.item');
$audit = load('audit');
//合同来源
$ctfrom_select = f_ctfrom_select();
$province_select = f_province_select();//省分下拉 (搜索用)

$iso_select=f_select('iso');//认证体系
$audit_ver_select = f_select('audit_ver');//体系版本
//审核类型
if( $audit_type_array ){
	foreach( $audit_type_array as $code => $item ){
		if( in_array( $code, array( '1002', '1003','1004','1005', '1006','1007' ) ) )
		$audit_type_select .= "<option value=\"$code\">$item[name]</option>";
	}
}
unset( $code, $item );
$status_0_V = '未安排';
$status_1_V = '已安排';
$status_2_V = '已派人';
$status_3_V = '已审批';
if($a=='add')
	$a='edit';
//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
} 