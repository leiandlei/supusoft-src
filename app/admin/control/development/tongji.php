<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	feiyong::$month = getgp('month');

	$results     = feiyong::hezuofangtongji();
	$results     = $results['results'];

	$month       = feiyong::$month;
	$contSetting = feiyong::$contSetting;
	tpl();