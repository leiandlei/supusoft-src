<?php
//ajax异步传输使用
function print_json($array)
{
    exit(json_encode($array));
}
 
 
//===========================字符串处理=======================================
/*
 *	函数名：getgp
 *	说  明：获取 $_GET 或 $_POST 内的变量
 *	参  数：$key 数组的下标 $method 为 G 则取 $_GET 数组 为 P 则取 $_POST 数组
 *	返回值：如果存在则返回元素，不存在刚返回 null
 */
function getgp($key, $method = null)
{
    if ($method == 'G' || $method != 'P' && isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $_POST[$key];
}

//过去搜索条件
function getSeach(){
    $seach = array();
    $where = '';
    foreach ($_REQUEST as $key => $value) {
        if( strstr($key,'seach_') ){
            if( !empty($value) ){
                $seach[substr($key,6)]=$value;
            }   
        }
    }
    return $seach;
}


/*
 * 函数名：xml_str
 * 说  明：把字符转义为实体字符
 * 参  数：$str
 * 返回值：返回转义后的字符串
 应用：导出word中编辑特殊字符
 */
function xml_str($str){
	$arr_search = array('<','>','&','\'','"');
	$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
	$str = str_ireplace($arr_search,$arr_replace,$str);
	return $str;
}


//===========================时间函数=======================================
/*
 *	函数名：current_time
 *	说  明：获取当前时间
 *	参  数：$type 类型 mysql = yyyy-mm-dd hh:ii:ss 形式 timestamp 则为 时间戳形式
 *			$gmt 是否格林威治时间
 *	返回值：正整数
 */
function current_time($type)
{
    //@HBJ 2013年9月11日 17:24:29 解决时间不正确的问题
    /* @wangp 不需要的代码 2013-09-28 9:13
    date_default_timezone_set('PRC');
    ini_set('date.timezone','Asia/Shanghai');
    $gmt_offset = 0;*/
    switch ($type) {
        case 'mysql':
            return date('Y-m-d H:i:s');
            break;
        case 'timestamp':
            return time();
            break;
        default:
            return date("Y-m-d");
            break;
    }
}
//===========================文件处理函数=======================================
/*
 * 函数名：writeover
 * 功  能：写文件
 * 参  数：$filename 文件名 $data 文件内容 $iflock 是否锁定文件 $check 是否校验 $chmod 是否用 chmod 设置权限
 * 返回值：无
 */
function writeover($filename, $data, $method = "rb+", $iflock = 1, $check = 1, $chmod = 1)
{
    $check && strpos($filename, '..') !== false && exit('Forbidden');
    touch($filename);
    $handle = fopen($filename, $method);
    if ($iflock) {
        flock($handle, LOCK_EX);
    }
    fwrite($handle, $data);
    if ($method == "rb+")
        ftruncate($handle, strlen($data));
    fclose($handle);
    $chmod && @chmod($filename, 0777);
}
//数组处理
function deal_arr($arr, $require)
{
    $tmp = array();
    foreach ($arr[$require] as $k => $v) {
        if (!$v)
            continue;
        $keys = array_keys($arr);
        foreach ($keys as $key) {
            if (!$arr[$key][$k])
                continue;
            $tmp[$k][$key] = $arr[$key][$k];
        }
    }
    return $tmp;
}
//调试类
function debug($msg)
{
    throw new error($msg);
}


/**
 * 将字符串按照其中的分隔符换行显示
 * @param string $str 需要处理的字符串
 * @param array $options 字符串中的分隔符
 * @return string
 */
function LongToBr($str, $options = array())
{
    $separator = '';
    foreach ($options as $option) {
        $str = str_replace($option, $options[0], $str);
    }
    $str_array = explode($options[0], $str);
    $string    = '';
    foreach ($str_array as $key => $str) {
        $string .= $str;
        if ($key < count($str_array) - 1) {
            $string .= '<br />';
        }
    }
    return $string;
}
/*
 *	函数名：p
 *	说  明：调试函数：开发时输出错误信息，应用控制器层：错误登记一般
 *	参  数：
 *	返回值：
 */
function p()
{
    $args = func_get_args();
    echo '<pre>';
    //多个参数循环输出
    foreach ($args as $arg) {
        if (is_array($arg)) {
            print_r($arg);
            echo '<br>';
        } else if (is_string($arg)) {
            echo $arg . '<br>';
        } else {
            var_dump($arg);
            echo '<br>';
        }
    }
    echo '</pre>';
}
/*
 *	函数名：print_const
 *	说  明：打印 自定义常量
 *	参  数：
 *	返回值：
 */

function print_const(){
	$const=get_defined_constants(TRUE);
	p($const[user]);
}
//数据库错误调试
function halt($msg = '', $sql = '')
{
    $c_file = '控制器文件：' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    p($c_file);
    $action = $_GET['a'];
    p('控制器方法：' . $action);
    p($_POST);
    echo 'Query Error:<br />' . $msg;
    if ($msg)
        echo '<br/>';
    echo $err_str = mysql_error();
    $output      = current_time('mysql') . "<br />" . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . " $msg\r\n$err_str \r\n\r\n";
    $server_date = date("Y-m-d");
    $filename    = $server_date . "_SQL.htm";
    //if(DEBUG)
    // writeover( LOG_DIR.$filename, $output, 'ab+' ); //暂时不生成错误记--录
    exit();
}
 

/*
 *	函数名：sp_var_export
 *	说  明：输出数组为代码格式
 *	参  数：$input 要输出的数据 $indent 数组结果分隔符
 *	返回值：代码格式的 数组
 */
function sp_var_export($input, $indent = '')
{
    switch (gettype($input)) {
        case 'string':
            return "'" . str_replace(array(
                "\\",
                "'"
            ), array(
                "\\\\",
                "\'"
            ), $input) . "'";
        case 'array':
            $output = "array(\r\n";
            foreach ($input as $key => $value) {
                $output .= $indent . "\t" . sp_var_export($key, $indent . "\t") . ' => ' . sp_var_export($value, $indent . "\t");
                $output .= ",\r\n";
            }
            $output .= $indent . ')';
            return $output;
        case 'boolean':
            return $input ? 'true' : 'false';
        case 'NULL':
            return 'NULL';
        case 'integer':
        case 'double':
        case 'float':
            return "'" . (string) $input . "'";
    }
    return 'NULL';
}
/**********************************************
 *											  *
 *				运行计划任务				  *
 *											  *
 **********************************************/
function run_cron(){
	global $db;

	$query = $db->query( "SELECT * FROM sp_cron WHERE is_open = '1' AND next_time <= '".current_time('mysql')."'" );
	while( $_d = $db->fetch_array( $query ) ){
		$next_time = '';
		$_next_time=$_d['next_time'];
		$_d['next_time']=date("y-m-d ").mysql2date(" H:i:s",$_d['next_time']);
		switch( $_d['loop_type'] ){
			case 'month':
				$next_time = thedate_add( $_d['next_time'], 1, 'month' );
				break;
			case 'week'	:
				$next_time = thedate_add( $_d['next_time'], 1, 'week' );
				break;
			case 'day'	:
				$next_time = thedate_add( $_d['next_time'], 1, 'day' );
				break;
			case 'hour'	:
				$next_time = thedate_add( $_d['next_time'], 1, 'hour' );
				break;
			case 'now'	:
			default		:
				list( $now_day, $now_hour, $now_minute ) = explode( '-', $_d['loop_time'] );
				$now_type = 'minute';

				if( $now_day ){
					$now_type = 'day';
					$now_time = $now_day;
				} elseif( $now_hour ){
					$now_type = 'hour';
					$now_time = $now_hour;
				} else {
					$now_type = 'minute';
					$now_time = $now_minute;
				}
				$next_time = thedate_add( current_time('mysql'), $now_time, $now_type );
				break;
		} // end switch(.....
		if( file_exists( APP_DIR ."/cron/{$_d['run_script']}.php") && current_time('mysql') >= $_next_time ){
			$db->update( 'cron',
					array( 'modifed_time' => current_time('mysql'), 'next_time' => $next_time ),
					array( 'cron_id' => $_d['cron_id'] ) );
			require_once( APP_DIR ."/cron/{$_d['run_script']}.php" );
			
		}

	} // end while( $rt = $db->fetch_array(.....

}
//输出Execl文件
function export_xls($filename, $data)
{
    $filename = iconv('UTF-8', 'gbk', $filename) . '_' . mysql2date("Y-m-d", current_time('mysql')) . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $data;
}
/**
 * 输出本地word文件
 * @param string $filename 文件名
 * @param string $data 数据
 * @param string $ct_id 合同id
 */
function export_word($filename, $data, $ct_id)
{
    $temp = uniqid() . strrchr($filename, '.');
    global $db;
    $ct_info = $db->get_row("SELECT eid,ctfrom FROM `sp_contract` WHERE `ct_id` = '$ct_id'");
    if (!$ct_info)
        return false;
    $out_filename = get_option('upload_ep_dir') . date('Ymd') . '/';
    !is_dir($out_filename) && mkdir($out_filename);
    $out_filename .= $temp;
    if (file_put_contents($out_filename, $data)) {
        $new_attach = array(
            'eid' => $ct_info[eid],
            'ct_id' => $ct_id,
            'name' => $filename,
            'ctfrom' => $ct_info['ctfrom'],
            'ext' => 'doc',
            'size' => filesize($out_filename),
            'filename' => date('Ymd') . '/' . $temp,
            'ftype' => '9000',
            'description' => '系统自动保存'
        );
        load('attachment')->add($new_attach);
    }
}


/**
 * 日志记录
 * @param integer $eid 企业ID(没有则传入0)
 * @param integer $uid 用户ID(没有则传入0)
 * @param string(200) $content 日志内容
 * @param string(65535) $af_str 改前内容
 * @param string(65535) $bf_str 改后内容
 * @return integer 新日志主键id
 */
function log_add($eid='', $uid='', $content, $af_str='', $bf_str='')
{
    global $db;
    $data = array(
        'eid' => $eid,
        'uid' => $uid,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'content' => $content,
        'af_str' => $af_str,
        'bf_str' => $bf_str,
      //  'update_uid' => current_user('uid'),
      //  'update_date' => date('Y-m-d H:i:s', time())
    );
    return $db->insert('log', magic_gpc($data, 1));
}
/*
 *	函数名：parse_args
 *	说  明：解析参数(可以是 字符串|数组|对象）返回数组
 *	参  数：$args 要解析的字符串，数据或对象 $default 可选 有默认值的返回值
 *	返回值：数组
 */
function parse_args($args, $defaults = '')
{
    if (is_object($args))
        $r = get_object_vars($args);
    elseif (is_array($args))
        $r =& $args;
    else
        parse_str($args, $r);
    if (is_array($defaults))
        $r = array_merge($defaults, $r);

    $arr=array();
    foreach ($r as $key => $value) {
        $arr[$key] = htmlspecialchars($value);
    }

    return $arr;
}
/**
 * 修正tab切换连接
 * @param boolean $echo 是否输出
 * @return string
 */
function gettourl($echo = true, $type = '')
{
    $str = '';
    foreach ($_GET as $key => $value) {
        if (!in_array($key, array(
            'm',
            'a',
            'status',
            'svStatus',
            'pd_type',
            'is_sms',
            'redata_status',
            'is_hire',
            'hr_exp',
            'is_check',
            'audit_finish',
            'is_bao',
            'type',
            'tab',
            'sp_type'
        ))) {
            $str .= "&" . $key . "=" . $value;
        }
    }
    if ($echo) {
        echo $str;
    } else {
        return $str;
    }
}
 
 /*
 * 函数名：sysinfo
 * 功  能：输出系统配置信息
 * 参  数：要显示的选项键名 $show
 * 返回值：无
 */
function sysinfo($show)
{
    if (empty($show))
        return false;
    switch ($show) {
        case 'sysurl':
        case 'home_url':
        case 'url': //系统路径
        case 'siteurl':
            $output = sys_url();
            break;
        case 'template_directory':
        case 'template_url':
            $output = get_template_directory_uri();
            break;
        case 'stylesheet_directory':
            $output = get_stylesheet_directory_uri();
            break;
        case 'stylesheet_url':
            $output = get_stylesheet_uri();
            break;
        case 'charset':
            $output = get_option('charset');
            break;
        case 'regname':
            $output = get_option('regname');
            break;
        case 'softname':
            $output = get_option('softname');
            break;
        default:
            $output = '';
            break;
    }
    echo $output;
}

//===========================地址函数=======================================
/*
 *	函数名：sys_url
 *	说  明：获取程序运行的URL
 *	参  数：$page 指定路径
 *	返回值：返回程序运行的URL路径
 */
function sys_url($path = '')
{
    $url = "http://$_SERVER[HTTP_HOST]" . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
    if (!empty($path) && is_string($path) && strpos($path, '..') === false)
        $url .= '/' . ltrim($path, '/');
    return $url;
}

 
 
/////////////////////////////时间处理函数////////////////////////////////////////////////////
/*
 *	函数名：mysql2date
 *	说  明：将mysql日期格式转为指定的日期格式
 *	参  数：$format 目标日期格式
 *			$date 日期时间
 *	返回值：正整数
 */
function mysql2date($format, $date)
{
    if (empty($date))
        return false;
    $i = strtotime($date);
    return date($format, $i);
}
/*
 *	函数名：thedate_add
 *	说  明：在指定的日期上增另指定的时间
 *	参  数：$date 日期
 *			$int 整数
 *			$r 类型：year = 年 month = 月 day = 天 week = 周 hour = 时 minute = 分 second = 秒
 *	返回值：日期
 */
function thedate_add($date, $int, $r)
{
    $rs = array(
        'year',
        'month',
        'day',
        'week',
        'hour',
        'minute',
        'second'
    );
    if (empty($date) || empty($int) || !in_array($r, $rs))
        return false;
    return date('Y-m-d H:i:s', strtotime($date . " $int $r"));
}
/*
 * 函数名：get_addday
 * 功  能：日期 + $month 月 -$day 天
 * 参  数：$nowtiem 当前时间 $month 要加/减的月数 $day 要加/减的天数
 * 返回值：加/减后的 日期
 */
function get_addday($nowtime, $month, $day = 0)
{
    $nowtime = explode('-', $nowtime);
    $mktime  = mktime(0, 0, 0, $nowtime['1'] + $month, $nowtime['2'] + $day, $nowtime['0']);
    $time    = date("Y-m-d", $mktime);
    return $time;
}
/*
 * 函数名：mkdate
 * 功  能：计算任务时间天数（任务结束-任务开始）
 * 参  数：$s_date 开始时间 $e_date 结束时间 （2014-03-8 13:00）
 * 返回值：天数 3.5
 
function mkdate($s_date, $e_date)
{
    $s_date_arr = explode(" ", $s_date);
    $e_date_arr = explode(" ", $e_date);
    $s_date     = strtotime($s_date_arr[0]);
    $e_date     = strtotime($e_date_arr[0]);
    $time       = $e_date - $s_date;
    $time       = $time / (3600 * 24);
    if ($s_date_arr[1] != "08:00" && $s_date_arr[1] != "08:00:00")
        $time -= 0.5;
    if ($e_date_arr[1] != "17:00" && $e_date_arr[1] != "17:00:00")
        $time -= 0.5;
    return $time + 1;
}
*/
function mkdate($s_date, $e_date)
{
   $s_date     = strtotime($s_date);
    $e_date     = strtotime($e_date);
    $time       = $e_date - $s_date;
	$time       = $time / (3600 * 24);
	$t=$time-(int)$time;
	if($t==0)
		$res=1;
	elseif($t<0.3)
		$res=0.5;
	elseif($t>0.3)
		$res=1;
	
	return (int)$time+$res;
}

/*
 * 函数名：format_date
 * 功  能：格式化时间
 * 参  数：$date  （2014-03-8 13:00）
 * 返回值：天数 2014年03月8日 下午
 */
function format_date($date)
{
    $str = explode(' ', $date);
    $arr = explode('-', $str[0]);
    $res = $arr[0] . "年" . $arr[1] . "月" . $arr[2] . "日";
    if ($str[1])
        if (strtotime($str[1]) < strtotime("13:00:00"))
            $res .= " 上午";
        else
            $res .= " 下午";
    return $res;
}
 
/*
 *	函数名：magic_gpc
 *	说  明：对字符串或数组进行转义
 *	参  数：$string 要转义的字符串
 *			$force 是否强制转义
 *			$strip 是否删除由 addslashes() 函数添加的反斜杠
 *	返回值：如果存在则返回元素，不存在刚返回 null
 */
function magic_gpc($string, $force = 0, $strip = false)
{
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = magic_gpc($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}
/*
 *	函数名：lang_info
 *	说  明：语言包信息
 *	参  数：$template 模板名 $msg 要使用的信息
 *	返回值：所使用的语言的 字符串
 */
function lang_info($template, $msg)
{
    global $lang;
    static $langs = array();
    if (!isset($langs[$template])) {
        require LANG . $template . '.lang.php';
        $langs[$template] = true;
    }
    if (!empty($lang[$msg])) {
        return $lang[$msg];
    } else {
        return $msg;
    }
}
/*
 *	函数名：curent_user
 *	说  明：获取当前用户信息
 *	参  数：$field 要获取的字段名
 *	返回值：$field 不为空时获取对应的字段值 为空时返回全部
 */
function current_user($field = '')
{
    if ('uid' == $field)
        $field = 'id';
    return isset($_SESSION['userinfo'][$field]) ? $_SESSION['userinfo'][$field] : '';
}
/*
 * 函数名：get_ctfrom_level
 * 功  能：合同来源 树形深度
 * 参  数：$code
 * 返回值： 层数
 */
function get_ctfrom_level($code = '00000000')
{
    if ('00' == substr($code, 0, 2)) {
        $len = 0;
    } elseif ('000000' == substr($code, 2, 6)) { //顶级
        $len = 2;
    } elseif ('0000' == substr($code, 4, 4)) { //二级
        $len = 4;
    } elseif ('00' == substr($code, 6, 2)) { //三级
        $len = 6;
    } else {
        $len = 8;
    }
    return $len;
}
/*
 * 函数名：create_dir
 * 功  能：创建目录
 * 参  数：$path 要创建的目录 支持多层
 * 返回值：无
 */
function create_dir($path)
{
    $path = dirname($path);
    if (!is_dir($path)) {
        create_dir($path);
        @mkdir($path);
        @chmod($path, 0777);
        @fclose(@fopen($path . '/index.html', 'w'));
        @chmod($path . '/index.html', 0777);
    }
}
/*
 * 函数名：readover
 * 功  能：读文件
 * 参  数：$filename 文件名 $method 打开模式
 * 返回值：文件的内容
 */
function readover($filename, $method = 'rb')
{
    strpos($filename, '..') !== false && exit('Forbidden');
    $filedata = '';
    if (($handle = @fopen($filename, $method)) && file_exists($filename)) {
        flock($handle, LOCK_SH);
        $filedata = @fread($handle, filesize($filename));
        fclose($handle);
    }
    return $filedata;
}
 
/**
 * 将?m=enterprise&a=add等网址转化成sp_hr中sys字段需要的格式
 * @param string $str 需处理的字符
 * @param string $ma 返回单独的m值或a值
 * @return string
 */
function urltoauth111($str, $ma = 'ma')
{
    if (strpos($str, 'm=') !== false and strpos($str, 'a=') !== false) {
        preg_match('/m=([0-9a-zA_Z]*)/', $str, $m);
        preg_match('/a=([0-9a-zA_Z]*)/', $str, $a);
        switch ($ma) {
            case 'm':
                return $m[1];
            case 'a':
                return $a[1];
                break;
            default:
                return $m[1] . ':' . $a[1];
        }
    }
    return $str;
}
function urltoauth($str, $ma = 'ma')
{
    if (strpos($str, 'c=') !== false and strpos($str, 'a=') !== false) {
        preg_match('/c=([0-9a-zA_Z]*)/', $str, $c);
        preg_match('/a=([0-9a-zA_Z]*)/', $str, $a);
        switch ($ma) {
            case 'c':
                return $c[1];
            case 'a':
                return $a[1];
                break;
            default:
                return $c[1] . ':' . $a[1];
        }
    }
    return $str;
}

/**
 * 是否具有权限
 * @return boolean
 */
function auth()
{
    global $left_nav;
    $sysControlArray = array();
    foreach ($left_nav as $left_nav_array) {
        foreach ($left_nav_array as $left_nav_array_nav) {
            //@zbzytech php5.4 对于数组没有的数据项是不能直接命名使用的（warning） 因此判一个是否存在
            if(is_array($left_nav_array_nav) AND array_key_exists('options',$left_nav_array_nav)){
                if (is_array($left_nav_array_nav['options'])){
                    foreach ($left_nav_array_nav['options'] as $key => $options_array) {
                        $sysControlArray[] = urltoauth($options_array[1]);
                        if (isset($options_array[3])) {
                            $explode = explode('|', $options_array[3]);
                            foreach ($explode as $explode) {
                                $sysControlArray[] = urltoauth($explode);
                            }
                        }
                    }
                }
            }    
        }
    }

    // 不在权限设置范围之内是有权限的
    if (!in_array(urltoauth($_SERVER['REQUEST_URI']), $sysControlArray)) {
        return true;
    }
    // admin永远有权限
    if ($_SESSION['userinfo']['username'] == 'admin') {
        return true;
    }

    // m或a不同时存在的按标识处理
    if (empty($_GET['c']) or empty($_GET['a'])) {
        return true;
    }
    // 特殊m不检查权限
    $c = array(
        'login',
        'home',
        'plugin',
        'ajax'
    );
    $a = array('task_batch_approval','task_batch_unapproval');
    if ( in_array($_GET['c'], $c)&&!in_array($_GET['a'],$a) ) {
        return true;
    }
    if (strpos($_SESSION['userinfo']['sys'], urltoauth($_SERVER['REQUEST_URI'])) !== false) {
        return true;
    }
    if( strpos('audit:progress|audit:list_hr_plan|attachment:down|attachment:batdown|sys:resetpw',urltoauth($_SERVER['REQUEST_URI'])) !== false ){
        return true;
    }
    return false;
}
 
/*
 * 函数名：chk_arr
 * 功  能：判断数组中有array("0000-00-00","0000-00-00 00:00:00") 返回空
 * 参  数：$arr 数组
 * 返回值：$arr 数组
 */
function chk_arr($arr)
{
    if (!is_array($arr))
        return false;
    foreach ($arr as $k => $val) {
        if (is_array($val))
            chk_arr($val);
        else {
            if (in_array($val, array(
                "0000-00-00",
                "0000-00-00 00:00:00",
                "1970-01-01"
            )))
                $arr[$k] = "";
        }
    }
    return $arr;
}


/*
 * 函数名：getOrgInfo
* 功  能：通过组织机构代码，获取组织信息
* 参  数：$orgCode 字符串 727536586
* 返回值： 对象
*/
function getOrgInfo($orgCode){
    require_once ROOT . '/framework/nusoap/nusoap.php';
    $ws     =   "http://codeplat.cnca.cn/codecheckws/CodeCheckServicePort?wsdl";
    // $ws     =   "http://211.103.228.162/codecheckws/CodeCheckServicePort?wsdl";
    $client = new soapclient($ws,true);
    $client->soap_defencoding = 'UTF-8';
    $client->decode_utf8 = false;

    $err = $client->getError();
    if ($err)return $client->getError();
    $orgUser    =get_option("orgUser");     //账号
    $orgPasd    =get_option('orgPasd'); //密码
    $orgToken   =get_option('orgToken');    //固定密钥

    $dt_psd     =   file_get_contents(DATA_DIR."orgcode.log");  //获取动态密钥

    $jm_dt_psd  =   ecryptdString($orgPasd,$dt_psd);        //加密密码(动态密钥)
    $param2     =   array('arg0' => array('systemCode'=>$orgUser,'password'=>$jm_dt_psd,'orgCode'=>$orgCode));
    $result     =   $client->call('searchDMInfo',$param2);

    if (empty($result))return $client->getError();
    if ($result['return']['message']!='success'){

        $jm_gd_psd  =   ecryptdString($orgPasd,$orgToken);  //加密密码(固定密钥)
        $param1     =   array('arg0' => array('systemCode'=>$orgUser,'password'=>$jm_gd_psd));
        $result     =   $client->call('searchKEY',$param1);//echo $result->return; 获取动态密钥

        $open=fopen(DATA_DIR."orgcode.log","w" );
        fwrite( $open,$result['return'] );
        fclose($open);

        $jm_dt_psd  =   ecryptdString($orgPasd,$result['return']);//加密密码(动态密钥)
        $param2     =   array('arg0' => array('systemCode'=>$orgUser,'password'=>$jm_dt_psd,'orgCode'=>$orgCode));
        $result     =   $client->call('searchDMInfo',$param2);
        return $result['return'];
    }
    return $result['return'];
}


/* * 实现AES加密 * $text : 要加密的字符串 */ 
function ecryptdString($text,$mykey){
	$key =pack("H*", $mykey); 
	$pad = 16 - (strlen($text) % 16); 
	$text .=str_repeat(chr($pad), $pad); 
	return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB)); 
} 
	
/* * 实现AES解密 此函数用不到 */ 
function decryptString($crypttext){ 
	$key =pack("H*", $crypttext); 
	$crypttext=pack("H*",$crypttext); 
	$text =mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypttext, MCRYPT_MODE_ECB); 
	$pad = 16 - (strlen($text) % 16); $text .=str_repeat(chr($pad), $pad); return $text; 
}


/*
 * 函数名：excel_read
* 功  能：通过组织机构代码，获取组织信息
* 参  数：$filePath excel 文件
* 返回值： 数组
*/

function excel_read($filePath){
	//首先导入PHPExcel
	require_once ROOT.'/theme/Excel/PHPExcel.php'; 
	//建立reader对象
	$PHPReader = new PHPExcel_Reader_Excel2007();
	if(!$PHPReader->canRead($filePath)){
		$PHPReader = new PHPExcel_Reader_Excel5();
		if(!$PHPReader->canRead($filePath)){
			echo 'no Excel';
			return ;
		}
	}

	//建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
	$PHPExcel = $PHPReader->load($filePath);
	//获取工作表的数目
	$sheetCount = $PHPExcel->getSheetCount();
	$data=array();
	for($i=0;$i<$sheetCount;$i++){
		$j=$i+1;
		/**读取excel文件中的第一个工作表*/
		$currentSheet = $PHPExcel->getSheet($i);
		/**取得最大的列号*/
		$allColumn = $currentSheet->getHighestColumn();
		if($allColumn!='A') $allColumn++;
		/**取得一共有多少行*/
		$allRow = $currentSheet->getHighestRow();
		//循环读取每个单元格的内容。注意行从1开始，列从A开始
		for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){
			for($colIndex='A';$colIndex!=$allColumn;$colIndex++){
				$addr = $colIndex.$rowIndex;
				$cell = $currentSheet->getCell($addr)->getValue();
				if($cell instanceof PHPExcel_RichText)     //富文本转换字符串
					$cell = $cell->__toString();
				
				$data["Sheet".$j][$rowIndex][$colIndex]=$cell;
			}

		}
		
	}
	return $data;
}
/**对excel里的日期进行格式转化*/ 
function GetData($val){ 
$jd = GregorianToJD(1, 1, 1970); 
$gregorian = JDToGregorian($jd+intval($val)-25569);
$gregorian=date("Y-m-d",strtotime($gregorian)); 
return $gregorian;/**显示格式为 “年-月-日” */ 
} 

//ajax返回
function ajaxReturn($errorCode = 0,$errorStr = '',$data = array()){
    $results = array(
             'errorCode' => $errorCode
            ,'errorStr'  => $errorStr
            ,'data'      => $data
        );
    return json_encode($results,JSON_UNESCAPED_UNICODE);
}

function getUrlContent($url){
    $ch = curl_init(); //初始化CURL句柄 
    curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// 跟踪重定向
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
    $results = curl_exec($ch);//执行预定义的CURL
    curl_close($ch);
    return $results;
}

/**
 * 获取上个月月份
 * @param  string $date [description]
 * @return [type]       [description]
 */
function getlastMonth($date='')
{
    $date      = empty($date)?date('Y-m-d H:i:s'):$date;
    $timestamp = strtotime($date);
    $day       = date('Y-m',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1)));
    return $day;
}

?>