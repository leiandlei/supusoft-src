<?php
/*
 *	函数名：load
 *	说  明：加载类 并创建实例: 工厂模式：实例化对象
 *	参  数：$class 类名
 *	返回值：类的实例： 自动加载
 */
function load($mdl_name,$options=array(), $is_load = false) //是否强制加载
{
    if (empty($mdl_name))
        return false;
    static $objs = array();
    //global $objs;
    $mdl_path = '';
    //var_dump($mdl_name);
    if (strpos($mdl_name, '.')) 
    {
        $arr      = explode('.', $mdl_name);
        $mdl_path = $arr[0] . '/';
        $mdl_name = str_replace('.', '_', $mdl_name);

    }
	 
    if (isset($objs[$mdl_name]) and $is_load == false) {
        return $objs[$mdl_name];
    } else {
        $model_file = CORE_DIR . 'models/' . "{$mdl_path}" . "$mdl_name" . '.class.php';
	  //LY 加载framework~models下的相应数据模型
        if (file_exists($model_file)) {
            require_once $model_file;
            if(empty($options)){
                $objs[$mdl_name] = new $mdl_name();
            }else{
                $objs[$mdl_name] = new $mdl_name($options);
            }
        } else {
            echo '<br>';
            echo '模型不存在' . $model_file;
            debug();
        }
    }
    
    //var_dump($objs[$mdl_name]);
    return $objs[$mdl_name];
}

/*
 *	函数名：tpl
 *	说  明：加载模板
 *	参  数：模板目录下，模板文件的路径
 *	返回值：无; 模板嵌套 :widget
 
 */
function tpl($template = '')
{
    extract($GLOBALS, EXTR_SKIP);
    $default_tpl_file = $template ? $template : $a;
    $located          = APP_DIR . $m . '/view/' . $c . '/' . $default_tpl_file . ".htm"; //模板后缀定死了为htm
    if (!file_exists($located)) {
        $located = APP_DIR . $m . '/view/' . $default_tpl_file . ".htm";
        if (!file_exists($located)) {
            debug($located . '模板不存在');
        }
    }
    require_once($located);
    //扩展系统调试功能
    if ($c != 'index' and $c!='export')
        require_once(APP_DIR. 'admin/view/header.htm');
}


//=============================================合同来源=================================================
//===========================系统框架函数=======================================
/*
 *	函数名：get_option
 *	说  明：获取系统选项
 *	参  数：$option 选项名
 *	返回值：成功返回 选英 失败返回 false
 */
function get_option($name)
{
    static $config = array();
    if (!strstr($name, '.')) {
        if (isset($config[$name])) {
            return $config[$name];
        }
        $temp          = require CONF . 'config.php';
        $config[$name] = $temp[$name];
        return $temp[$name];
    } else {
        $name_arr = explode('.', $name);
        if (isset($config[$name_arr[0]][$name_arr[1]])) {
            return $config[$name_arr[0]][$name_arr[1]];
        }
        $temp                               = require CONF . $name_arr[0] . '_config.php';
        $config[$name_arr[0]][$name_arr[1]] = $temp[$name_arr[1]];
        return $config[$name_arr[0]][$name_arr[1]];
    }
}

/*
 *	函数名：showmsg
 *	说  明：加载模板
 *	参  数：无
 *	返回值：无
 */
function showmsg($msg, $notice_style = 'success', $jumpurl = '', $T = 0.1)
{
    $GLOBALS['message']      = lang_info('msg', $msg);
    $GLOBALS['notice_style'] = $notice_style;
    $GLOBALS['jump']         = ($jumpurl) ? "<meta http-equiv=\"Refresh\" content=\"$T; url=$jumpurl\">" : '';
    $GLOBALS['jumpurl']      = $jumpurl;
    extract($GLOBALS);
    tpl('showmsg');
    exit;
}
 
/*
 *  函数名：dialog_showmsg
 *  说  明：dialog窗口提示信息，无返回按钮
 *  参  数：无
 *  返回值：无
 */
function dialog_showmsg($msg, $notice_style = 'success', $jumpurl = '', $T = 0.1)
{
    $GLOBALS['message']      = lang_info('msg', $msg);
    $GLOBALS['notice_style'] = $notice_style;
    $GLOBALS['jump']         = ($jumpurl) ? "<meta http-equiv=\"Refresh\" content=\"$T; url=$jumpurl\">" : '';
    $GLOBALS['jumpurl']      = $jumpurl;
    extract($GLOBALS);
    tpl('dialog_showmsg');
    exit;
}