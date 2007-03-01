<?php
/* 
* @Author: mantou
* @Date:   2017-11-20 17:35:46
* @Last Modified by:   mantou
* @Last Modified time: 2017-11-22 14:41:23
*/
require_once ROOT . '/framework/models/feiyong.class.php';

	$results  = feiyong::jiesuansave($_POST);
	$status   = $_POST['status'];
	$month    = $_POST['months'];
	if($results['errorCode']='1'){
		$REQUEST_URI = "?c=development&a=jiesuansheet&status=$status&month=$month";
		showmsg('success', 'success', $REQUEST_URI);
	}
?>
