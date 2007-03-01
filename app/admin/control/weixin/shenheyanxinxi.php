<?php
$params = array(
		 'page' => $_GET['paged']
		,'size' => $_GET['size']
		,'tab'  => empty($_GET['tab'])?'0':$_GET['tab']
	);

$results = Api::httpToApi('TaskHc/getTaskHcEList',$params);
// echo '<pre />';
// print_r($results);exit;
$pages = numfpage($results['extraInfo']['tab_'.$params['tab']],$results['extraInfo']['size']);

$extraInfo = $results['extraInfo'];
$results   = $results['results'];
tpl();