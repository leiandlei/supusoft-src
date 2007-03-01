<?php
$eid = getgp('eid');
$params = array(
		 'page' => $_GET['paged']
		,'size' => $_GET['size']
		,'eid'  => $eid
	);

$results = Api::httpToApi('TaskHc/getTaskHcListByEid',$params);
// echo '<pre />';
// print_r($results);exit;
$pages = numfpage($results['extraInfo']['countTotal'],$results['extraInfo']['size']);
$extraInfo = $results['extraInfo'];
$results   = $results['results'];
tpl();
?>
