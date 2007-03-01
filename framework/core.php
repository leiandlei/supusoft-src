<?php
//环境配置
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
function_exists('date_default_timezone_set') && date_default_timezone_set('PRC');
header("Content-Type: text/html; charset=UTF-8");
//==========初始化系统============
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
define('MODEL_DIR', CORE_DIR . '/models/'); //模型目录 
define('CTL_DIR', APP_DIR . 'admin/control/'); //控制器路径 
define('VIEW_DIR', APP_DIR . 'admin/view/'); //模板路径
define('API_DIR', ROOT . 'api/'); //模板路径
//判断是否开启系统提示错误
// @zbzytech 屏蔽未声明变量提示：方法1：修改php.ini 让它不提示；目前这个为方法2：
$DEBUG=isset($_GET['DEBUG'])?$_GET['DEBUG']:NULL;
//@zbzytech 1则关闭DEBUG 0为开启debug
if ( DEBUG or $DEBUG==1) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    //var_dump("123");
    ini_set("display_errors", "Off");
} 

// 加载核心文件
require_once CORE_DIR . 'import.php'; //公共函数-不能修改-分页-字符串处理code 时间处理 date 
require_once CORE_DIR . 'function.php'; //系统函数 
require_once CORE_DIR . 'cache.fun.php';
require_once CORE_DIR . 'page.fun.php'; //分页函数 
require_once CORE_DIR . 'error.class.php';
require_once CORE_DIR . 'model.class.php';

//初始化
$db = load('db.mysql');
$db->connect(get_option('db.db_host'), get_option('db.db_user'), get_option('db.db_pwd'), get_option('db.db_name'));
//p($db);
//========================实现功能===============================  以fun为后缀
require_once CORE_DIR . 'einfo.fun.php'; //公共信息函数 
/* 判断是否登录 已登录则正常显示 未登录则显示登录页 */
//路由分发
$m = isset($_GET['m']) ? ($_GET['m']) : 'admin'; //模块 moudle
$c = (isset($_GET['c'])) ? ($_GET['c']) : 'index'; //控制 controller
$a = (isset($_GET['a'])) ? ($_GET['a']) : 'index'; //方法 action
if( getgp('c')!='doc' || getgp('downs')!=1 ){
    if( !in_array(getgp('c'),array('output')) ){
       if (!load('login')->isLoggedin()) {
        require_once CTL_DIR . 'login.php';
        require_once( CTL_DIR. 'customer.php' );
        exit;
        } 
    }
    
}
/* 计划任务 */
run_cron();
// 权限控制
// 只对ma都存在的情况进行验证
// m或a不同时存在的按标识处理
//左侧导航菜单与权限
require_once CONF . 'main_menu.php';

if (!auth()) {
    showmsg('noauth', 'error');
    exit;
}
try {
    //控制文件
    $ctl_path = APP_DIR . $m . '/control/' . $c . '.php';
    if (file_exists($ctl_path)) {
        // echo $ctl_path;exit;
        require_once $ctl_path;
    } else {
        //直接加载方法文件
        $action = APP_DIR . $m . '/control/' . $c . '/' . $a . '.php';
        if (file_exists($action)) {
            require_once $action;
        } else {
            exit('控制器文件不存在:' . $action);
        }
    }
}
catch (error $e) {
    $e->get_message();
}
?>