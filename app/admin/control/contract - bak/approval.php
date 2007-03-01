<?php
/*
 * 合同评审审批取信息
 */
$ct_id = (int)getgp( 'ct_id' );

	//合同信息
	$sql = "SELECT ct.*,e.ep_name FROM sp_contract ct INNER JOIN sp_enterprises e ON e.eid = ct.eid WHERE ct_id = '$ct_id'";
	$contract = $db->get_row( $sql );
	$contract['audit_type_V'] = f_audit_type( $contract['audit_type'] );

	$approval_date = ($contract['approval_date'] == '0000-00-00') ? '' :$contract['approval_date'];
 	//是否可审批
	$approval_disabled = ( 2 != $contract['status'] ) ? 'disabled' : '';

	//是否可撤销审批
	$projects = $db->get_results("SELECT * FROM sp_project WHERE ct_id = '$ct_id' AND deleted = 0", 'id');
	if( $projects ){
		$disabled = false;
		foreach( $projects as $project ){
			if( 0 != $project['status'] ){
				$disabled = true;
				break;
			}
		}
	}
	$unapproval_disabled = ( $disabled || 3 != $contract['status'] ) ? 'disabled' : '';

	$allow_types = array('1001','1002');
	$ct_archives = array();
	$archive_join = " LEFT JOIN sp_hr hr ON hr.id = a.create_uid";
	$sql = "SELECT a.*,hr.name author FROM sp_attachments a $archive_join WHERE a.ct_id = '$ct_id' AND a.ftype IN ('".implode("','",$allow_types)."') ORDER BY a.id ASC";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['ftype_V'] = f_arctype( $rt['ftype'] );
		$ct_archives[$rt['id']] = $rt;
	}
	tpl( 'contract/approval' );