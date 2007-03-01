<?php
//人员登记模块
require_once (ROOT . '/data/cache/ctfrom.cache.php'); //证件类型
require_once (ROOT . '/data/cache/card_type.cache.php'); //证件类型
require_once (ROOT . '/data/cache/political.cache.php'); //政治面貌
require_once (ROOT . '/data/cache/job_type.cache.php'); //人员性质 多项
require_once (ROOT . '/data/cache/audit_job.cache.php'); //审核性质
require_once (ROOT . '/data/cache/ct_type.cache.php'); //人员 合同类型
require_once (ROOT . '/data/cache/technical.cache.php'); //人员职称
require_once (ROOT . '/data/cache/choose_type.cache.php'); //选用类型
require_once (ROOT . '/data/cache/insurance.cache.php'); //社保登记
require_once (ROOT . '/data/cache/region.cache.php'); //省份
require_once (ROOT . '/data/cache/department.cache.php'); //部门
require_once (ROOT . '/data/cache/attachtype.cache.php'); //附件类型
require_once (ROOT . '/data/cache/employment_nature.cache.php'); //聘用性质
require_once (ROOT . '/data/cache/department.cache.php'); //所在部门
require_once (ROOT . '/data/cache/post.cache.php'); //所在岗位
require_once (ROOT . '/data/cache/business.cache.php'); //业务类别
require_once (ROOT . '/data/cache/functions.cache.php'); //业务职能
require_once (ROOT . '/data/cache/employment_methods.cache.php'); //业务职能
require_once( ROOT . '/data/cache/education.cache.php' ); //教育经历

$user=load('user'); //加载类
$step = getgp('step');
$exp=load('experience');

//合同来源
$ctfrom_select = f_ctfrom_select();
//省份下拉(登记用 搜索用)
$province_select = f_province_select();
//聘用性质
$employment_nature_select = f_select('employment_nature');
//政治面貌
$political_select =f_select('political');
//证件类型
$card_type_select = f_select('card_type');
$audit_job_select = f_select('audit_job');//是否专职
$technical_select = f_select('technical');  //是否专职
$choose_type_select =f_select('choose_type');//选用类型
$insurance_select =f_select('insurance');//社保登记
$xueli_select=f_select('education'); //人员学历
$attachtype_select = f_select('attachtype');//附件类型
//部门设置
$department_checkbox = '';
if ($department_array) {
    foreach ($department_array as $code => $item) {
        $department_checkbox.= "<input type='checkbox' name='department[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//岗位
$post_checkbox = '';
if ($post_array) {
    foreach ($post_array as $code => $item) {
        $post_checkbox.= "<input type='checkbox' name='post[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//业务类别
$business_checkbox = '';
if ($business_array) {
    foreach ($business_array as $code => $item) {
        $business_checkbox.= "<input type='checkbox' name='business[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//业务职能
$functions_checkbox = '';
if ($functions_array) {
    foreach ($functions_array as $code => $item) {
        $functions_checkbox.= "<input type='checkbox' name='functions[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//人员性质
$job_type_checkbox = '';
if ($job_type_array) {
    foreach ($job_type_array as $code => $item) {
        $job_type_checkbox.= "<input type='checkbox' name='job_type[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
 
 


//用工方式
$employment_methods_checkbox = '';
if ($employment_methods_array) {
    foreach ($employment_methods_array as $code => $item) {
        $employment_methods_checkbox.= "<input type='checkbox' name='employment_methods[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
 


$ct_type_checkbox = '';//合同类型
if ($ct_type_array) {
    foreach ($ct_type_array as $code => $item) {
        $ct_type_checkbox.= "<input type='checkbox' name='meta[ct_type][$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;&nbsp;';
    }
}


unset($code, $item);
if($a=='add')
	$a='edit';
//引入模块控制下的方法
$action=CTL_DIR.$c.'/'.$a.'.php';
//var_dump($action);
if(file_exists($action)){
	include_once($action);
	exit;
}


?>
