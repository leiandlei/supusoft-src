<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	$results  = feiyong::feiyongsave($_POST);
	if($results['errorCode']='1'){
		$REQUEST_URI = "?c=development&a=feiyong";
		showmsg('success', 'success', $REQUEST_URI);
	}
	