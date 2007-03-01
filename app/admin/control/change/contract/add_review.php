<?php
//变更评审列表 audit 
$status_arr=array("未安排","已安排","待审批","已审批","","监督维护","退回");
	/******************************
	 #			搜   索			  #
	 ******************************/


	$fields = $join = $where = $page_str = '';


	$ep_name		= getgp( 'ep_name' ); //企业名称
	$ctfrom			= getgp( 'ctfrom' ); //合同来源
	$audit_type		= getgp( 'audit_type' ); //审核类型
	$ct_code		= trim(getgp( 'ct_code' )); //合同编号
	$cti_code		= trim(getgp( 'cti_code' )); //合同项目编号
	$pre_date_start	= getgp('pre_date_start'); //计划时间
	$pre_date_end	= getgp('pre_date_end'); //计划时间
	$final_date_start	= getgp( 'final_date_start' ); //最后监察时间 起
	$final_date_end	= getgp( 'final_date_end' ); //最后监察时间 止

	 $flag	= getgp( 'status' );
	$export			= getgp( 'export' );
	 !$flag && $flag=1;
 
 
	${'status_'.$flag.'_tab'} = ' ui-tabs-active ui-state-active';

	//企业名称
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',trim($ep_name))."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND p.eid IN (".implode(',',$_eids).")";
		} else {
			$where .= " AND p.id < -1";
		}
		unset( $_eids, $_query, $rt );
	}
	

	//合同编号
	if( $ct_code ){
	   $where .= " AND p.ct_code = '$ct_code'";
		
	}

	//合同项目编号
	if( $cti_code ){
		$where .= " AND p.cti_code like '%$cti_code%'";
	}

	
	

	//合同来源限制
	$len = get_ctfrom_level( current_user( 'ctfrom' ) );

	if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
		$_len = get_ctfrom_level( $ctfrom );
		$len = $_len;
	} else {
		$ctfrom = current_user( 'ctfrom' );
	}
	switch( $len ){
		case 2	: $add = 1000000; break;
		case 4	: $add = 10000; break;
		case 6	: $add = 100; break;
		case 8	: $add = 1; break;
	}
	$ctfrom_e = sprintf("%08d",$ctfrom+$add);
	$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

	//计划开始时间 开始
	if( $pre_date_start ){
		$where .= " AND p.pre_date >= '$pre_date_start'";
	}
	//计划开始时间 结束
	if( $pre_date_end ){
		$where .= " AND p.pre_date <= '$pre_date_end'";
	}
	//最后审核日期 开始
	if( $final_date_start ){
		$where .= " AND p.final_date >= '$final_date_start'";
	}
	//最后审核日期 结束
	if( $final_date_end ){
		$where .= " AND p.final_date <= '$final_date_end'";
	}




	//要获取的字段
	$fields .= "p.*,e.ep_name,e.ctfrom";
	$type_allow=array("1003","1008","1009","1010","3001","2001","2002","2003");
	//要关联的表
	$join .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
	//$where .= " AND p.deleted = '0' AND p.audit_type IN ('".implode("','",$type_allow)."')";
	$where .= "  AND p.deleted = '0'";
	if( !$export ){
		$total[1] = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where  AND p.flag = '1'");
		$total[2] = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where  AND p.flag = '2'");
		$total[3] = $db->get_var("SELECT COUNT(*) FROM sp_project p WHERE 1 $where ");
		$pages = numfpage($total[$flag]);
	}
	 if($flag!='3'){
	 
	 $where .= " AND p.flag='$flag'";
	 }
	$sql = "SELECT $fields FROM sp_project p $join WHERE 1 $where ORDER BY p.id DESC $pages[limit]";
	
	$projects = array();
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
		$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
		$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
		$rt['final_date'] == '0000-00-00' && $rt['final_date'] = '';
		$rt['status_V'] = $status_arr[$rt['status']];
		$ct_info=$db->get_row("SELECT mark_require,audit_require,finance_require FROM `sp_contract` WHERE `ct_id` = '$rt[ct_id]' ");
		if($ct_info[mark_require] || $ct_info[audit_require] || $ct_info[finance_require])
			$rt[style]='style="background-color:#f00;"';
		$projects[$rt['id']] = chk_arr($rt);
	}
	
	
	if( !$export ){
		tpl();
	} else {
		ob_start();
		tpl('xls/list_wait_arrange');
		$data = ob_get_contents();
		ob_end_clean();
		export_xls('审核项目列表',$data);
	}
?>