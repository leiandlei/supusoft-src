<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	$results  = feiyong::shenheyuansave($_POST);
	if($results['errorCode']='1'){
		$REQUEST_URI = "?c=development&a=shenheyuan";
		showmsg('success', 'success', $REQUEST_URI);
	}