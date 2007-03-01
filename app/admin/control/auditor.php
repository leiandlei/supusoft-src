<?php
require_once (ROOT . '/data/cache/ct_type.cache.php'); //人员 合同类型
require_once (ROOT . '/data/cache/job_type.cache.php'); //工作类型 多项

$user = load('user');
$step = getgp('step');
//省份下拉(登记用 搜索用)
$province_select = f_province_select();//
$department_select =f_select('department');//政治面貌
$political_select = f_select('political');//证件类型
$card_type_select = f_select('card_type');//人员性质
$audit_job_select = f_select('audit_job');//是否专职
$choose_type_select = f_select('choose_type');//选用类型
$insurance_select =f_select('insurance');//社保登记
$qualification_select=f_select('qualification');//资格
$iso_select=f_select('iso');//体系
$attachtype_select = f_select('attachtype');//附件类型

$job_type_checkbox = '';
if ($job_type_array) {
    foreach ($job_type_array as $code => $item) {
        $job_type_checkbox.= "<input type='checkbox' name='job_type[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//合同类型
$ct_type_checkbox = '';
if ($ct_type_array) {
    foreach ($ct_type_array as $code => $item) {
        $ct_type_checkbox.= "<input type='checkbox' name='meta[ct_type][$code]' value=\"$item[code]\" disabled='disabled'>" . $item[name] . '&nbsp;&nbsp;';
    }
}
unset($code, $item);
$uid = current_user('uid');
//测试复制
if (!uid) {
    echo '访问无效';
    exit;
}
//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
}
//===========================非单独控制的方法=========================

?>