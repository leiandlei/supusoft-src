<?php
	//合作方列表
	$user = $_SESSION['extraInfo']['userType'];
	$code = $_SESSION['userinfo']['code'];
	$URL  = substr($_SERVER['HTTP_REFERER'],0,strrpos($_SERVER['HTTP_REFERER'],"/"));
	if ($user=="hezuofang") {
		$sql = "select * from sp_partner where code = '".$code."' and deleted=0 ";
		header("Location: ".$URL."/?c=concert_sheet&a=xtd_list&ctfrom=".$code);
		exit;
	}
	if ($user=="stuff"){
		$sql = "select * from sp_partner where code <> '01000000' and deleted=0 ";
		
	}
	$results = $db->getAll($sql);

	tpl();
?>