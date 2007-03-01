<?php


//小类编辑

	$acaclass = load('auditcodeapp');
	$quaclass = load('qualification');
	$userclass = load('user');
	$acaid=getgp('acaid');
	$iso = getgp('iso');
	$uid=getgp('uid');
	$aca_info = $acaclass->get($acaid);
	$source_info=$aca_info['source']; //能力来源

	$qua_info = $quaclass->get($qid);
	$user_info = $userclass->get($uid);


	$status_arr = array('3'=>'通过','2'=>'不通过');

	$status_select = '';
	foreach( $status_arr as $code => $item ){
		$status_select .= "<option value=\"$code\">$item</option>";
	}
	$skll_array = explode('；', $source_info);
    foreach ($skll_array as $value) {
        $skill_source_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $skill_source_checkbox);
    }
    //人员专业能力评价
    $res = $db->query("select id,name from sp_hr where job_type like '%1001%' and is_hire='1' ");
    while($row = $db->fetch_array($res)){
    	$evaluater_select  .= "<option value='$row[name]'>".$row['name']."</option>";
    }
    if($aca_info[evaluater]){
    	$evaluater_select = str_replace("<option value='$aca_info[evaluater]'>", "<option value='$aca_info[evaluater]' selected>", $evaluater_select);
    }

	if($aca_info['status']=='2'||$aca_info['status']=='3'){
		$str = $aca_info['status'];
		$status_select = str_replace( "value=\"$str\">", "value=\"$str\" selected>" , $status_select );
	}
	$iso_v = f_iso($iso);
	tpl('hr/appcode_edit');

?>
