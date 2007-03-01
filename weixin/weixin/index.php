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
session_start();
set_time_limit(0);
header("Content-Type: text/html; charset=utf-8");
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',0);
 
// 定义应用目录
define('APP_PATH','./Application/');

/** 接口常量 **/
define('API_URL','http://cams.lll.cn/weixin/api/index.php/Home/'); //接口地址
define('SECRET_BROWSER'  , 'ksdhbfiuyh98182y379812hi9');//浏览器设备混淆码
define("SECRET_PASSWORD" , 'f983r2ewioeoiwaeefadsafew');//密码混淆码
define("SANDC_KEY"       , 'userInfo');//session和cookie的key
$arrOptions = array(
				 'appid'          => 'wx767442a77474c183'
				,'appsecret'      => '89250f4d7afaa84fd0cd030f08589146'
				,'encodingaeskey' => 'Ph2KYWNqmUP5lEk8jVfGCm3W4H4idqHqi9fkUWEMJka'
				,'token'          => 'lll'
				,'debug'          => '1'
		);

$arrHeader = array
			(
				 'Userid'        => 0              //当前用户ID
				,'Requesttime'   => null           //请求时间
				,'Logintime'     => null           //最后登录时间
				,'Clientversion' => 1.0            //版本号
				,'Devicetype'    => 1              //类型 1:浏览器设备 2:PC 3:安卓 4:iOS 5:其他 默认浏览器设备
				,'Checkcode'     => null           //用户和登陆时间组成加密字符
			);
/** 接口常量 **/
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';