<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
header("Content-Type: text/html; charset=utf-8");
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',1);
// 定义应用目录
$Clientversion = @$_SERVER['HTTP_CLIENTVERSION'];
if(empty($Clientversion)){
	$Clientversion = '1.0';
	//echo json_encode(array('errorCode'=>1,'errorStr'=>'版本错误','results'=>''));exit;
}

switch ($Clientversion){//根据版本号选择应用目录

	case '1.0':
		$APP_PATH='./Application/';
		break;

	default:
		$APP_PATH='./Application/';
		break;

}

if(!is_dir($APP_PATH)){echo json_encode(array('errorCode'=>1,'errorStr'=>'版本不存在','results'=>''));exit;}
clearstatcache();

define('APP_PATH',$APP_PATH);
/** 接口常量 **/
define('HEADERS'   ,null);//浏览器设备混淆码
define('USEERTABLE','hr');//用户表表明
define('SECRET_BROWSER'  ,'ksdhbfiuyh98182y379812hi9');//浏览器设备混淆码
define('SECRET_PC'       ,'ksdhbfiuyh98182y379812hi9');//PC混淆码
define('SECRET_ANDROID'  ,'ksdhbfiuyh98182y379812hi9');//安卓混淆码
define('SECRET_IOS'      ,'ksdhbfiuyh98182y379812hi9');//IOS混淆码
define('SECRET_OTHER'    ,'ksdhbfiuyh98182y379812hi9');//其他混淆码
define("SECRET_PASSWORD" ,'f983r2ewioeoiwaeefadsafew');//密码混淆码
define("USER_RANDCODE"  , 'f9823r2ioeoiwaeefadsafeww');//用户信息混淆码

/** 接口常量 **/
$arrOptions = array(
				 'appid'          => 'wx767442a77474c183'
				,'appsecret'      => '89250f4d7afaa84fd0cd030f08589146'
				,'encodingaeskey' => 'Ph2KYWNqmUP5lEk8jVfGCm3W4H4idqHqi9fkUWEMJka'
				,'token'          => 'lll'
				,'debug'          => '1'
		);


// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';