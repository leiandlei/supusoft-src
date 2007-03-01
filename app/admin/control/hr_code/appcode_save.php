<?php
//业务代码申请保存
	//将请求信息变成变量
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	$value = array(
		'status' => $status,  //更改申请状态-通过-不通过
		'note2' => $note2,
		'note' => $note,
		'evaluater' => $evaluater,
		'note2_date' => date("Y-m-d H:i:s"),
		'pass_date' => $pass_date,
		'source'=>implode("；",getgp('skill_source')),
	);


	$acaclass = load('auditcodeapp');
	if($acaid){
		$acaclass->edit($acaid,$value);
	}
	$app_info=$acaclass->get($acaid);
	$hr_info=$db->get_row("SELECT * FROM `sp_hr` WHERE `id` = '$app_info[uid]'");
	$qua_info=$db->get_row("SELECT * FROM `sp_hr_qualification` WHERE `id` = '$app_info[qua_id]'");
	if($app_info[status]=='3'){
		
		$value2 = array(
			'uid'			=> $app_info[uid],
			'qua_id'		=> $app_info[qua_id],
			'qua_type'		=> $qua_info[qua_type],
			'ctfrom' 		=>$hr_info[ctfrom],
			'areacode' 		=>$hr_info[areacode],
			'iso'			=> $app_info[iso],
			'evaluater' 	=> $evaluater,
			'use_code'		=> $app_info[app_use_code],
			'source'		=> implode("；",getgp('skill_source')),
			'pass_date'		=> $pass_date,
			//'status'		=> '1',
		);
		$code_info=$db->get_row("SELECT id FROM `sp_hr_audit_code` WHERE `uid` = '$app_info[uid]' AND `iso` = '$app_info[iso]' AND `use_code` = '$app_info[app_use_code]' AND `deleted` = '0'");
		$auditcode = load('auditcode');
		if($code_info[id])
			$auditcode->edit($code_info[id],$value2);
		else
			$auditcode->add( $value2 ); //申请小类，审批后，添加到人员小类表里
	}

	$REQUEST_URI='?c=hr_code&a=clist';
	showmsg( 'success', 'success', $REQUEST_URI );

?>
