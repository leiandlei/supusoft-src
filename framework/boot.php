<?php
//环境配置
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
function_exists('date_default_timezone_set') && date_default_timezone_set('PRC');
header("Content-Type: text/html; charset=UTF-8");
//配置信息  

//=========================系统主要路径设置=============================
//框架配置
//MVC设计模式  框架路径
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc()); //是否自动转义
define('STYLESHEET_DIR', 'include/'); //ui主题目录
define('APP_DIR', 'app/'); //应用目录
define('DATA_DIR', 'data/'); //数据与日志目录
define('LOG_DIR', ROOT . '/data/error_log/'); //错误日志路径
define('DOCTPL_PATH', APP_DIR . 'doc_tpl/'); //导出word模板路径
define('CORE_DIR', ROOT . '/framework/'); //系统框架目录
define('LANG', ROOT . '/lang/'); //语言目录 
define('UPLOAD_PATH', ROOT . '/uploads/'); //附件保存物理路径
define('CACHE_PATH', DATA_DIR . '/cache/'); //缓存目录－配置缓存
define('SYS_CACHE_DIR', DATA_DIR . '/sys_cache/'); //系统缓存目录
define('MODEL_DIR', DATA_DIR . '/model/'); //模型目录 
define('CTL_DIR', APP_DIR . 'admin/control/'); //控制器路径 
define('VIEW_DIR', APP_DIR . 'admin/view/'); //模板路径
//判断是否开启系统提示错误
if (DEBUG) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    ini_set("display_errors", "Off");
}
////////////////////////////////////左侧导航/////////////////////////////
require_once CONF . 'main_menu.php';
////////////////////////////////////系统引用的函数////////////////////////////////
require_once CORE_DIR . 'core.php'; //公共函数-不能修改-分页-字符串处理code 时间处理 date 
require_once CORE_DIR . 'function.php'; //系统函数  
require_once CORE_DIR . 'cache.fun.php'; 
require_once CORE_DIR . 'page.fun.php'; //分页函数


//加载系统核心文件
//require_once CORE_DIR . 'mysql.class.php';
require_once CORE_DIR . 'error.class.php';
require_once CORE_DIR . 'model.class.php';

//初始化
$db = load('db.mysql'); 
 
$db->connect(get_option('db.db_host'), get_option('db.db_user'), get_option('db.db_pwd'), get_option('db.db_name'));
 



//p($db);
 
//========================实现功能===============================  以fun为后缀
require_once CORE_DIR . 'einfo.fun.php'; //公共信息函数 
?>