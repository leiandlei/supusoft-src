<?php


require_once( ROOT . '/data/cache/region.cache.php' );
	//合同来源
	$ctfrom_select = f_ctfrom_select();

	//省分下拉 (搜索用)
	$province_select = f_province_select();
	//收费项目
    $cost_type_select    =  f_select('cost_type');
	if ($_SESSION['extraInfo']['ctfrom']=='01000000') {
	    $ctf='';
	}else{
	    $ctf = $_SESSION['extraInfo']['ctfrom'];
	    $hezuofang = 1;
	}
	$ctfrom = getgp( 'ctfrom' )?getgp( 'ctfrom' ):$ctf;
	$fields = $join = $where = $item_join = $item_where = $page_str = '';
	$status = (int)getgp( 'status' );
	$where .= " AND cc.deleted = '0'";

	$status_0_tab = $status_1_tab = $status_2_tab = $status_3_tab = '';
	${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';

	

	//搜索开始
	$a = getgp( 'a' );
	$ep_name = trim(getgp( 'ep_name' ));
	$code = trim(getgp('code'));
	$ct_code = trim(getgp( 'ct_code' ));
	$cti_code = trim(getgp( 'cti_code' ));
	$ctfrom_select= str_replace( 'value="'.$ctfrom.'"', 'value="'.$ctfrom.'" selected ', $ctfrom_select );
	$areacode = getgp( 'areacode' );
	$status	= getgp( 'status' );
	$export=getgp('export');
	$cost_type   = getgp('cost_type');
	$create_date_s = getgp('create_date_s');
	$create_date_e = getgp('create_date_e');
	
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND cc.eid IN (".implode(',',$_eids).")";
		}
		$page_str .= '&ep_name='.$ep_name;
	}

	if( $code ){		//企业编号
		$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
		$where .= " AND cc.eid = '$eid'";
	}


	if( $ct_code ){ //合同编码
		$where .= " AND ct.ct_code = '$ct_code'";
	}
    if ($cost_type){ //收费项目
	    $where .= " AND cc.cost_type = '$cost_type'";
    }
	if( $cti_code ){ //合同项目编码
		$ct_ids=array(-1);
	$query = $db->query("SELECT ct_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$ct_ids[]=$rt[ct_id];
		}
	$where .= " AND ct.ct_id in (".implode(",",$ct_ids).")";
	}

	if( $create_date_s ){ //合同登记时间
		$where .= " AND ct.create_date > '$create_date_s'";
	}
	if( $create_date_e ){ //合同登记时间
		$where .= " AND ct.create_date < '$create_date_e'";
	}

	//合同来源限制
    if ($ctfrom=='01000000'){
	    $where .= " AND ct.ctfrom = '01000000'";
    }
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
			$where .= " AND cc.eid IN (".implode(',',$_eids).")";
		}

		$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
	}
	$fields .= ",e.ep_name,e.ctfrom,ct.ct_code,ct.status,ct.create_date";

	$join = " LEFT JOIN sp_enterprises e ON e.eid = cc.eid";
	$join .= " LEFT JOIN sp_contract ct ON cc.ct_id = ct.ct_id";

	$status_arry = array("0"=>"未登记完",'1'=>'已登记','2'=>'已评审','3'=>'已审批');
	$datas = array();
	/*列表*/
	if (!$export) {
		$total = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost cc $join WHERE 1 $where ");
		$pages = numfpage( $total);
		}


	$sql = "SELECT cc.* $fields FROM sp_contract_cost cc $join WHERE 1 $where ORDER BY ct.create_date DESC $pages[limit] ";
	$res = $db->query($sql);
	while( $rt = $db->fetch_array( $res ) ){
		
		$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
		$temp_arr = explode('|',$rt['iso']);
		$rt[iso]=array();
		foreach($temp_arr as $iso){
			$rt[iso][]=f_iso($iso);
		}
		
		$temp_arr="";
		$rt['status'] = $status_arry[$rt['status']];
		$rt['cost_type']=read_cache("cost_type",$rt['cost_type']);
		$datas[] = $rt;
	}


	if( !$export ){
		tpl( 'contract/cost_list' );
	} else {//导出合同费用列表
		ob_start();
		tpl( 'xls/list_contract_cost' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '合同费用列表', $data );
	}