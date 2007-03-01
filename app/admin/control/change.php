<?php
require_once( ROOT . '/data/cache/certreplace.cache.php' );
require_once( ROOT . '/data/cache/certchange.cache.php' );
require_once( ROOT . '/data/cache/nature.cache.php' );
require_once( ROOT . '/data/cache/audit_ver.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' );
require_once( ROOT . '/data/cache/certpasue.cache.php' );
require_once( ROOT . '/data/cache/certrecall.cache.php' );

$certificate = load('certificate'); //加载证书模型
$enterprise = load('enterprise'); //加载企业模型
$contract = load('contract');
$change = load('change');

$zsid = getgp('zsid');
$cgid = getgp('cgid');
$step = getgp('step');

$change_item_select=f_select('certchange'); //变更类型
$province_select = f_province_select();//省分下拉 (搜索用)


//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
}
 