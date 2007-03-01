<?php
/**
 * 开发代号 : supusoft
 * 软件作者 : 连雷雷(leiandlei@gmail.com)
 * 发布时间 : 2007年03月01日
 */

define('ROOT', dirname(__FILE__)); //系统根目录
define('CONF', ROOT . '/data/'); //配置目录
define('DEBUG', 0); //是否开启调试模式

/** 接口常量 **/
define('API_URL', 'http://cams.lll.cn/weixin/api/index.php/Home/'); //接口地址
define('SECRET_BROWSER', 'f4aba6e9f29f7d1a3a12f022f9dd8ef7');//浏览器设备混淆码
define("SECRET_PASSWORD", '8fc2e71c67eab47388eed910c5c313c3');//密码混淆码
define("SANDC_KEY", 'userinfo');//session和cookie的key
$arrHeader = array
(
  'Userid' => 0       //当前用户ID
, 'Requesttime' => null    //请求时间
, 'Logintime' => null    //最后登录时间
, 'Clientversion' => 1.0     //版本号
, 'Devicetype' => 1       //类型 1:浏览器设备 2:PC 3:安卓 4:iOS 5:其他 默认浏览器设备
, 'Checkcode' => null    //用户和登陆时间组成加密字符
);

/** 接口常量 **/
$arrOptions = array(
  'appid' => 'wx148327f7228aeb76'
, 'appsecret' => 'a41a800bc08de7518fe65d9dd0f9c9ac'
, 'encodingaeskey' => 'De4fh545Gf912shdsdsDs433h4HWb3238fyd74hGs2Y'
, 'token' => 'lll'
, 'debug' => '1'
);
require_once ROOT . '/data/arr_config.php'; //框架引导文件
require_once ROOT . '/framework/Api.class.php';//接口文件
require_once ROOT . '/framework/core.php'; //框架引导文件
