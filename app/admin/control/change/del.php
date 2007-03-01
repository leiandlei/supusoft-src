<?php


$cgid = getgp('cgid');
	$cg_info = $change->get($cgid);
	$eid = $db->get_var("select eid from sp_certificate where id='$cg_info[zsid]' ");
	$zs_info = $certificate->get($cg_info['zsid']);
	$zsid=$zs_info['id'];

	if($cg_info['status']=='0'){
		$change->del($cgid);
		//Log::write($eid,'删除变更-未审批',$af_str,$bf_str,'add'); @wangp 日志函数被重写了 2013-09-27 11:20
	}else{
		switch($cg_info['cg_type']){
			case '01':
				$sql = "update sp_enterprises set ep_name = '$cg_info[cg_af]' where eid='$eid' ";
				$db->query($sql);
				$sql = "update sp_certificate set cert_name = '$cg_info[cg_af]' where id='$cg_info[zsid]' ";
				$db->query($sql);
				break;
			case '02':
				$sql = "update sp_certificate set cert_addr='$cg_info[cg_af]' where id='$cg_info[zsid]' ";
				$db->query($sql);
				break;
			case '03':
				$sql = "update sp_certificate set total='$cg_info[cg_af]' where id='$cg_info[zsid]'";
				$db->query($sql);
				break;
			case '04':
				$sql = "update sp_certificate set mark='$cg_info[cg_af]' where id='$cg_info[zsid]' ";
				$db->query($sql);
				// $sql = "update sp_project set mark='$cg_info[cg_af]' where ct_id='$zs_info[ct_id]' and iso='$zs_info[iso]' and audit_type='$cg_info[audit_type]' ";
				// $db->query($sql);
				break;
			case '05':
			case '06':
				$sql = "update sp_certificate set cert_scope='$cg_info[cg_af]' where id='$zsid' ";
				$db->query($sql);
				break;
			case '08':
				$sql = "update sp_certificate set audit_ver='$cg_info[cg_af]' where id='$zsid' ";
				$db->query($sql);
				break;
			case '97_01':
			case '97_02':
			case '97_03':
			case '97_04':
				$sql = "update sp_certificate set status='$cg_info[cg_af]' where id='$zsid' ";
				$db->query($sql);
		}
		//Log::write($eid,'删除变更-已审批',$af_str,$bf_str,'add'); @wangp 日志函数被重写了
		$change->del($cgid);
	}
	$REQUEST_URI='?c=change&a=list&status='.$cg_info['status'];
	showmsg( 'success', 'success',$REQUEST_URI );