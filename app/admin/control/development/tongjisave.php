<?php
	require_once ROOT . '/framework/models/feiyong.class.php';
	$results = feiyong::hezuofangtongjisave($_POST);
	if($results['errorCode']='1'){
		$REQUEST_URI = "?c=development&a=tongji";
		showmsg('success', 'success', $REQUEST_URI);
	}
