<?php
//define( 'DB_FROM',  'cmiqc_f');


require_once( ROOT . '/data/cache/job_type.cache.php' );		//人员性质 多项
require_once( ROOT . '/data/cache/ct_type.cache.php' );		//人员 合同类型

$user = load('user');
$step = getgp('step');
$ctfrom_select=f_select('ctfrom');//合同来源(登记用 搜索用)
$province_select =f_province_select();//省份下拉(登记用 搜索用)
$department_select =f_select('department');//部门
$political_select =f_select('political');//政治面貌
$card_type_select =f_select('card_type');//证件类型
$choose_type_select =f_select('choose_type');//选用类型
$insurance_select =f_select('insurance');//社保登记
$audit_job_select =f_select('audit_job');//是否专职

//人员性质
$job_type_checkbox ='';
if( $job_type_array ){
	foreach( $job_type_array as $code => $item ){
		$job_type_checkbox .= "<input type='checkbox' name='job_type[$code]' value=\"$item[code]\">".$item[name].'&nbsp;';
	}
}

//合同类型
$ct_type_checkbox ='';
if( $ct_type_array ){
	foreach( $ct_type_array as $code => $item ){
		$ct_type_checkbox .= "<input type='checkbox' name='meta[ct_type][$code]' value=\"$item[code]\">".$item[name].'&nbsp;&nbsp;';
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