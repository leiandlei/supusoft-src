<?php


require_once( ROOT . '/data/cache/region.cache.php' );

	//合同来源
	$ctfrom_select = f_ctfrom_select();

	//省分下拉 (搜索用)
	$province_select = f_province_select();


	$fields = $join = $where = $item_join = $item_where = $page_str = '';
	$status = (int)getgp( 'status' );
	$where .= " AND ct.deleted = '0'";

	$status_0_tab = $status_1_tab = $status_2_tab = $status_3_tab = '';
	${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';

	$fields .= ",e.ep_name,e.ctfrom";

	$join = " LEFT JOIN sp_enterprises e ON e.eid = ct.eid";

	//搜索开始
	$a = getgp( 'a' );
	$ep_name = getgp( 'ep_name' );
	$code = getgp('code');
	$ct_code = getgp( 'ct_code' );
	$cti_code = getgp( 'cti_code' );
	$ctfrom	= $ctfrom_select= str_replace( 'value="'.$ctfrom.'"', 'value="'.$ctfrom.'" selected ', $ctfrom_select );
	$areacode = getgp( 'areacode' );
	$status	= getgp( 'status' );
	$export=getgp('export');
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND ct.eid IN (".implode(',',$_eids).")";
		}
		$page_str .= '&ep_name='.$ep_name;
	}

	if( $code ){		//企业编号
		$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
		$where .= " AND ct.eid = '$eid'";
	}


	if( $ct_code ){ //合同编码
		$where .= " AND ct.ct_code = '$ct_code'";
	}

	if( $cti_code ){ //合同项目编码
		$ct_ids=array(-1);
	$query = $db->query("SELECT ct_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$ct_ids[]=$rt[ct_id];
		}
	$where .= " AND ct.ct_id in (".implode(",",$ct_ids).")";
	}


	//合同来源限制
	$len = get_ctfrom_level( current_user( 'ctfrom' ) );

	if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
		$_len = get_ctfrom_level( $ctfrom );
		$len = $_len;
	} else {
		$ctfrom = current_user( 'ctfrom' );
	}
	$last = substr($ctfrom,$len - 1,1);
	$ctfrom_e = substr( $ctfrom, 0, $len -1 ).($last+1);
	$_i = 8 - $len;
	for( $i = 0; $i < $_i; $i++ ){
		$ctfrom_e .= '0';
	}
	$where .= " AND ct.ctfrom >= '$ctfrom' AND ct.ctfrom < '$ctfrom_e'";



	if( $areacode ){
		$pcode = substr($areacode,0,2) . '0000';
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND ct.eid IN (".implode(',',$_eids).")";
		}

		$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
	}


	$status_arry = array('0'=>'未登记完','1'=>'已登记','2'=>'已评审','3'=>'已审批');
	$datas = array();
	/*列表*/
	if (!$export) {
		$total = $db->get_var("SELECT COUNT(*) FROM sp_contract ct WHERE 1 $where ");
		$pages = numfpage( $total);
		}


	$sql = "SELECT ct.* $fields FROM sp_contract ct $join WHERE 1 $where ORDER BY ct.ct_id DESC $pages[limit]";
	$res = $db->query($sql);
	while( $rt = $db->fetch_array( $res ) ){
		$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
		$rt['status'] = $status_arry[$rt['status']];
		$query=$db->query("SELECT cti_code FROM `sp_contract_item` WHERE `ct_id` = '$rt[ct_id]' AND `deleted` = '0' ");
		while( $r = $db->fetch_array( $query ) ){
			$rt['cti_code'][]=$r[cti_code];
		}
		unset($query,$r);
		$query=$db->query("SELECT * FROM `sp_contract_cost` WHERE `ct_id` = '$rt[ct_id]' AND `deleted` = '0' ");
		while( $r = $db->fetch_array( $query ) ){
			$r[iso]=explode("|",$r[iso]);
			$_iso=array();
			foreach($r[iso] as $iso)
				$_iso[]=f_iso($iso);
			$r[iso]=join(",",$_iso);
			$rt['cost'][]=$r[iso].":".read_cache("cost_type",$r[cost_type]).$r['cost'];
		}
		unset($query,$r);
		$datas[] = $rt;
	}

	if( !$export ){
		tpl( 'contract/cost_add_list' );
	} else {//导出合同费用列表
		ob_start();
		tpl( 'xls/list_contract_cost_add' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '合同费用列表', $data );
	}