<?php
//财务发票邮寄

	$fields = $join = $where =  '';
	extract( $_GET, EXTR_SKIP );
	$status_0_tab = $status_1_tab = $status_2_tab = '';

	!$is_sms && $is_sms=0;
	${'status_'.$is_sms.'_tab'} = ' ui-tabs-active ui-state-active';

	//企业名称
	$ep_name = trim($ep_name);
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',trim($ep_name))."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND s.eid IN (".implode(',',$_eids).")";
		} else {
			$where .= " AND s.id < -1";
		}
		unset( $_eids, $_query, $rt, $_eids );
	}

	//企业编号
	if( $ep_code ){
		$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
		if( $eid ){
			$where .= " AND s.eid = '$eid'";
		} else {
			$where .= " AND s.id < -1";
		}
	}
	if($invoice=trim($invoice)){
		$id = $db->get_var("SELECT id FROM `sp_contract_cost_detail` WHERE `invoice` = '$invoice' and deleted =0");
		if( $id ){
			$where .= " AND s.temp_id = '$id'";
		} else {
			$where .= " AND s.id < -1";
		}
	
	}

if( $s_dates ){ // 起
		$where .= " AND s.sms_date >= '$s_dates'";
	}
	if( $s_datee ){ // 止
		$where .= " AND s.sms_date <= '$s_datee'";
	}
	if( $ct_code=trim($ct_code) ){ //合同
		$_pids = array();
		$_query = $db->query("SELECT id FROM sp_project WHERE ct_code = '$ct_code' and deleted=0");
		while( $rt = $db->fetch_array( $_query ) ){
			$_pids[] = $rt['id'];
		}
		if( $_pids ){
			$where .= " AND s.pid IN (".implode(',',$_pids).")";
		} else {
			$where .= " AND s.id < -1";
		}
		unset( $_eids, $_query, $rt, $_eids );
		
	}

	if( $cti_code=trim($cti_code) ){ //合同项目
		$_pids = array();
		$_query = $db->query("SELECT id FROM sp_project WHERE cti_code like '%$cti_code%' and deleted=0");
		while( $rt = $db->fetch_array( $_query ) ){
			$_pids[] = $rt['id'];
		}
		if( $_pids ){
			$where .= " AND s.pid IN (".implode(',',$_pids).")";
		} else {
			$where .= " AND s.id < -1";
		}
		unset( $_eids, $_query, $rt, $_eids );
	}
	/* 联表 */
	$join .= " LEFT JOIN sp_enterprises e ON e.eid = s.eid ";
	// $join .= " LEFT JOIN sp_project p ON p.eid = s.eid";
	$join .= " LEFT JOIN sp_contract c ON c.eid=s.eid";
	$join .= " LEFT JOIN sp_contract_item ci ON ci.eid=s.eid";
	$join .= " LEFT JOIN sp_contract_cost_detail ccd ON ccd.id=s.temp_id";
	// $join .= " LEFT JOIN sp_contract_cost_detail ccd ON ccd.eid = s.eid and ccd.status='2'";
    $where .= " AND ccd.status = '2'";
	$where .= " AND s.flag = '3'";
	$where .= " AND s.deleted = 0";
	$sms_total = array(0,0,0);
	if(!$export){
		$query=$db->query("SELECT s.is_sms,COUNT(*) total FROM sp_sms s  $join WHERE 1 $where GROUP BY s.is_sms" );
		while( $rt = $db->fetch_array( $query ) ){
			$sms_total[$rt['is_sms']] = $rt['total'];
		}
	}
	$where .= " and s.is_sms='$is_sms'";
	$pages = numfpage( $sms_total[$is_sms] );
	$sql = "SELECT s.*,ccd.status,c.ct_code,ci.cti_code,e.ep_name FROM sp_sms s $join WHERE 1 $where ORDER BY s.id DESC $pages[limit]";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt[invoice_cost]=$db->get_var("SELECT invoice_cost FROM `sp_contract_cost_detail` WHERE id = '$rt[temp_id]' and deleted=0");
		$datas[] = $rt;
	}
	if( !$export ){
		tpl();
	} else {
	ob_start();
		tpl( 'xls/list_finance_elist' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '未邮寄证书表', $data );
	}