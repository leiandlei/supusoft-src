<?php
switch (PHP_OS) {
	case 'Linux':
		$autoload = __DIR__ . '/linux/Autoloader.php';
		break;
	case 'WINNT':
		$autoload = __DIR__ . '/windos/Autoloader.php';
		break;
    default:
    	echo '不支持系统';exit;
        break;
}
require_once $autoload;
// 设置类自动加载回调函数
spl_autoload_register('\Workerman\Autoloader::loadByNamespace');