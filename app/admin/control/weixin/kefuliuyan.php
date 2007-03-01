<?php
$params = array(
		 'page' => $_GET['paged']
		,'size' => $_GET['size']
		,'tab'  => empty($_GET['tab'])?'0':$_GET['tab']
	);

$results = Api::httpToApi('Message/getMessageToWeb',$params);
$pages = numfpage($results['extraInfo']['tab_'.$params['tab']],$results['extraInfo']['size']);
// echo "<pre />";
// print_r($results);exit;

$extraInfo = $results['extraInfo'];
$results   = $results['results'];
tpl();