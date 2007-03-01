<?php
$userid      = $_SESSION['userinfo']['id'];
$username    = $_SESSION['userinfo']['name'];
$data_for    = getgp('data_for');
$tatid       = getgp('tatid');
$del         = getgp('del');
/**生成页面数据**/
if ($tatid) {
	//获取需要编辑的信息
	$sql=" SELECT auditor.*,hr.name FROM sp_task_audit_team auditor LEFT JOIN sp_hr hr ON hr.id=auditor.uid WHERE auditor.id=$tatid ";
	$auditorInfo=$db->get_row($sql);
	$hrInfo['uid']         =$auditorInfo['uid']; 
	$hrInfo['name']        =$auditorInfo['name']; 
	
    $hrInfo['taskBeginDate']=$auditorInfo['taskBeginDate']; 
    $hrInfo['taskEndDate']  =$auditorInfo['taskEndDate']; 

    $yjdst         = substr($hrInfo['taskBeginDate'],11,2);
	$taskBeginDate = substr($hrInfo['taskBeginDate'],0,10);

	switch ($yjdst) {
		case '08':
			$yjdst_bm_8  = "selected";
			break;
		case '13':
			$yjdst_bm_13 = "selected";
			break;
		default:
			break;
	} 

	$yjded         = substr($hrInfo['taskEndDate'],11,2);
	$taskEndDate   = substr($hrInfo['taskEndDate'],0,10);
	switch ($yjded) {
		case '12':
			$yjded_bm_12 = "selected";
			break;
		case '17':
			$yjded_bm_17 = "selected";
			break;
		default:
			break;
	}

	switch ($auditorInfo['data_for']) {
		case '2':
			$data_for_2 = "selected";
			break;
		case '5':
			$data_for_5 = "selected";
			break;
		case '6':
			$data_for_6 = "selected";
			break;
		default:
			break;
	}
}
/**生成页面数据**/

/**存储变更数据**/
if($_POST){
	if ($tatid) {
		$name_hr  =$db->get_row( "select id from  sp_hr where name = '".$_POST['name']."' and deleted = 0");
		$yjdst_time = getgp('yjdst_time');
		$yjded_time = getgp('yjded_time');
		$params ['uid']           = $userid;
		$params ['name']          = $username;
		$params ['data_for']      = $data_for;
		$tb_date                  = getgp('taskBeginDate') . ' ' .$yjdst_time;
		$te_date                  = getgp('taskEndDate') . ' ' .$yjded_time;
		$params ['taskBeginDate'] = $tb_date;
		$params ['taskEndDate']   = $te_date;
		$params ['note']          = getgp('note');
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=$userid AND id<>$tatid AND taskBeginDate<='$te_date' AND taskBeginDate>='$tb_date' AND deleted='0'";
		$if_exist_b=$db->get_var($sql);
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=$userid AND id<>$tatid AND taskEndDate<='$te_date' AND taskEndDate>='$tb_date' AND deleted='0'";
		$if_exist_e=$db->get_var($sql);
		if ($auditorInfo['create_uid']==$userid) {
			if ($if_exist_b=='0'&&$if_exist_e=='0') {
				if ($auditorInfo['create_uid']==$userid) {
					$db->update('task_audit_team',$params, array('id' => $tatid));
					dialog_showmsg( 'success', 'success', $REQUEST_URI );
				}
			}else{
				dialog_showmsg( '操作失败，与已录入日程冲突', 'error', $REQUEST_URI );
			}
		}else{
			$sql="SELECT name FROM sp_hr WHERE id=".$auditorInfo['create_uid']." AND deleted='0'";
			$name=$db->get_var($sql);
			dialog_showmsg( '日程只能由创建人更改，请联系'.$name.'更改', 'error', $REQUEST_URI);
		}
	}else{
		$name_hr  =$db->get_row( "select id from  sp_hr where name = '".$_POST['name']."' and deleted = 0");
		$yjdst_time = getgp('yjdst_time');
		$yjded_time = getgp('yjded_time');
		$params ['uid']           = $userid;
		$params ['name']          = $username;
		$params ['data_for']      = $data_for;
		$tb_date                  = getgp('taskBeginDate') . ' ' .$yjdst_time;
		$te_date                  = getgp('taskEndDate') . ' ' .$yjded_time;
		$params ['taskBeginDate'] = $tb_date;
		$params ['taskEndDate']   = $te_date;
		$params ['note']          = getgp('note');
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=$userid AND taskBeginDate<='$te_date' AND taskBeginDate>='$tb_date' AND deleted='0'";
		$if_exist_b=$db->get_var($sql);
		$sql="SELECT count(*) FROM sp_task_audit_team WHERE uid=$userid AND taskEndDate<='$te_date' AND taskEndDate>='$tb_date' AND deleted='0'";
		$if_exist_e=$db->get_var($sql);
		if ($if_exist_b=='0'&&$if_exist_e=='0') {
			$db->insert('task_audit_team',$params);
			dialog_showmsg( 'success', 'success', $REQUEST_URI );
		}else{
			dialog_showmsg( '操作失败，与已录入日程冲突', 'error', $REQUEST_URI );
		}
	}
}
/**存储变更数据**/

/**删除行程**/
if ($del=='1') {
	if ($tatid) {
		if ($auditorInfo['create_uid']==$userid) {
				$params ['deleted'] ='1';
				$db->update('task_audit_team',$params, array('id' => $tatid));
				dialog_showmsg( 'success', 'success', $REQUEST_URI);
		}else{
			$sql="SELECT name FROM sp_hr WHERE id=".$auditorInfo['create_uid']." AND deleted='0'";
			$name=$db->get_var($sql);
			dialog_showmsg( '日程只能由创建人更改，请联系'.$name.'更改', 'error', $REQUEST_URI);
		}
	}else{
		dialog_showmsg( '行程未录入，请点击右上角关闭按钮取消录入', 'error', $REQUEST_URI);
	}
}
/**删除行程**/
tpl();