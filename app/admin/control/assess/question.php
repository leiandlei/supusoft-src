<?php
//评定问题列表
extract( $_GET, EXTR_SKIP );
$fields = $join = $where = '';

//搜索条件
	//上传时间 起
	if( $upload_date_start )
		$where .=" AND p.upload_done_date >= '$upload_date_start 00:00:00'";
	//上传时间 止
	if( $upload_date_end )
		$where .=" AND p.upload_done_date <= '$upload_date_end 24:00:00'";


	//企业名称
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',trim($ep_name))."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND p.eid IN (".implode(',',$_eids).")";
		}
	}

//合同编号
if( $ct_code=trim($ct_code) ){
   $where .= " AND p.ct_code = $ct_code";
	
}

//合同项目编号
if( $cti_code=trim($cti_code) ){
	$where .= " AND p.cti_code like '%$cti_code%'";
}


	//要获取的字段
	$fields .= "a.*,p.ct_code,p.cti_code,p.eid,t.tb_date,t.te_date,p.assess_date,p.sp_date";
	//$fields .= "a.*,p.ct_code,cti_code,p.eid,tt.name";
	//要关联的表
	$join .= " LEFT JOIN sp_task t ON a.tid = t.id";
	$join .= " INNER JOIN sp_project p ON p.tid = t.id";
	

	$where .= " AND a.deleted = '0'";


	if( !$export ){
		
		$total=$db->get_var("SELECT COUNT(*) total FROM `sp_assess_notes` a $join WHERE 1 $where ");
		
		
		$pages = numfpage( $total );
	}

	$resdb = array();
    $sql = "SELECT $fields FROM `sp_assess_notes` a $join where 1 $where ORDER BY a.id DESC $pages[limit]";

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt[ep_name]=$db->get_var("SELECT ep_name FROM `sp_enterprises` WHERE `eid` = '$rt[eid]'");
		$rt[name]=$db->get_var("SELECT name FROM sp_task_audit_team WHERE `role` = '01' and tid='{$rt[tid]}' and deleted=0");
		//$rt[tb_date] = $db->get_var("SELECT name FROM sp_task_audit_team WHERE `role` = '01' and pid='{$rt[pid]}'");
		//$rt[name]=$db->get_var("SELECT name FROM sp_task_audit_team WHERE `role` = '01' and pid='{$rt[pid]}'");
		
		
		$resdb[$rt['id']] = $rt;
		
	}
//echo "<pre>";print_r($resdb);exit;
	if( !$export ){
		tpl();
	} else { //导出excel表格
		ob_start();
		tpl( 'xls/list_question' );
		$data = ob_get_contents();
		ob_end_clean();

		export_xls( '评定问题列表', $data );
	}