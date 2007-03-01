<?php


$loop_type_array = array(
	'month'	=> '每月',
	'week'	=> '每周',
	'day'	=> '每天',
	'hour'	=> '每小时',
	'now'	=> '每隔'
);

$week_day_array = array('天', '一', '二', '三', '四', '五', '六' );

$out_format = array(
	'month_day'	=> "%d日%d时%02d分",
	'week_day'	=> "周%s%d时%02d分",
	'day'		=> "%d点%02d分",
	'hour'		=> "%d分",
	'now'		=> "%d%s"
);

$now_type_array = array(
	'day'	=> '天',
	'hour'	=> '小时',
	'minute'=> '分钟',
);


if($a=='add')
	$a='edit';
//引入模块控制下的方法
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
}

?>