<?php


//@HBJ 2013年9月12日 09:29:13 checkform()检测是否资格重复
	if(isset($_REQUEST['ajax'])) {

		//如果编辑成为无效资格，允许
		if(strtotime(getgp('e_date'))<strtotime(date('Y-m-d'))) {
			print json_encode( array( 'state' => 'ok' ) );exit;
		}

		switch($_REQUEST['qua_type']) {
			case '01':
				$qua_type = array("'01'", "'02'", "'03'");
				break;
			case '02':
				$qua_type = array("'01'", "'02'", "'03'");
				break;
			case '03':
				$qua_type = array("'01'", "'02'", "'03'");
				break;
			default:
				$qua_type = array("'{$_REQUEST['qua_type']}'");
		}
		$date = date('Y-m-d');
		if($_REQUEST['id']) {
			//编辑
			$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification hqa WHERE hqa.uid={$_REQUEST['uid']} AND hqa.iso='{$_REQUEST['iso']}' AND hqa.qua_type IN (".implode(',',$qua_type).") AND hqa.status=1 AND hqa.id!='{$_REQUEST['id']}'");
		}
		else {
			//新加
			$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification hqa WHERE hqa.uid={$_REQUEST['uid']} AND hqa.iso='{$_REQUEST['iso']}' AND hqa.qua_type IN (".implode(',',$qua_type).") AND hqa.status=1");
		}

		if($total) {
			print json_encode( array( 'state' => $total ) );
		}else{
			print json_encode( array( 'state' => 'ok' ) );
		}
		exit;
	}

	$iso=$_POST['iso'];
	if(is_array($audit_ver_array)) {
		foreach($audit_ver_array as $value){
			if($value['iso']==getgp('iso')){
				$iso=$value['iso'];
				break;
			}
		}

	}
	$id= getgp('id');

	if(strtotime(getgp('e_date'))<strtotime(date('Y-m-d'))){
		$status = '0';
	}else{
		$status = '1';
	}
	$uid = (int)getgp('uid');
	$hr = $db->get_row("SELECT ctfrom FROM sp_hr WHERE id = '$uid'");
	$default = array(
		'uid'		=> $uid,	//用户id
		'ctfrom'	=> $hr['ctfrom'],
		'iso'		=> $iso,			//体系
		'qua_type'  => getgp('qua_type'),  //注册资格
		'qua_no'	=> getgp('qua_no'),	//注册资格号码
		's_date'	=> getgp('s_date'),	//资格开始时间
		'e_date'	=> getgp('e_date'),	//资格结束时间
		'note'		=> getgp('note'),	//备注
		'is_leader'	=> getgp('is_leader'),	//组长
		'pd_date'	=> getgp('pd_date'),	//备注
		'status'	=> $status,	//状态 -1为删除状态
	);
	$db->update('hr_audit_code',array('hqa_status'=>$status), array('uid'=>$uid,'iso'=>$iso) );


	if( $id ){
		//之前日志
		$bf_str=serialize($qualification->get($id));

		$qualification->edit( $id, $default );
		 //日志
 		log_add(0, $uid, "修改注册资格",$bf_str, serialize($qualification->get($id)));
	} else {
		$id = $qualification->add( $default );
		//日志
	 	$info=$qualification->get($id);
		$bf_str = serialize($qualification->get($id));
		log_add('',$info['uid'],'增加注册资格','',$bf_str);
	}
	$REQUEST_URI='?c=hr_qualification&a=edit&uid='.getgp('uid');
	showmsg( 'success', 'success', $REQUEST_URI );