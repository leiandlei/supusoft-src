<?php
//再认证列表

	extract( $_GET, EXTR_SKIP );

	$status = (int)$status;

	$status_0 = $status_1 = '';
	${'status_'.$status} = ' ui-tabs-active ui-state-active"';

	$fields = $join = $where = '';


	$fields = $join = $where = $page_str = '';

	//要获取的字段
	$fields .= "i.*,e.ep_name,e.ctfrom,e.areacode,e.ep_level";//
	$fields .= ",c.pre_date,c.signe_name";
	$fields .= ",hr.name create_user,p.ct_code,p.cti_code,p.use_code";
	$fields .= ",cert.e_date,cert.status cert_status,t.tb_date,cert.iso,p.audit_code,cert.audit_ver";
	//要关联的表
	$join .= " LEFT JOIN sp_enterprises e ON e.eid = i.eid";
	$join .= " LEFT JOIN sp_contract c ON c.ct_id = i.ct_id";
	//$join .= " LEFT JOIN sp_contract_item ci ON ci.cti_id = i.cti_id";
	$join .= " LEFT JOIN sp_project p ON p.id = i.pid";
	$join .= " LEFT JOIN sp_certificate cert ON cert.id = i.zs_id";
	$join .= " LEFT JOIN sp_hr hr ON hr.id = i.create_uid";
	$join .= " LEFT JOIN sp_task t ON t.id = p.tid";

	if( $ep_name=trim($ep_name) ){
		$_eids = array();
		$query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%$ep_name%'");
		while( $rt = $db->fetch_array( $query ) ){
			$_eids[] = $rt['eid'];
		}
		if( $_eids ){
			$where .= " AND i.eid IN (".implode(',',$_eids).")";
		} else {
			$where .= " AND i.id < -1";
		}
	}
//省份
	if( $areacode ){
		$pcode = substr($areacode,0,2) . '0000';
		$_eids = array(-1);
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}
		$where .= " AND i.eid IN (".implode(',',$_eids).")";
		unset( $_eids, $_query, $rt, $_eids );
		
		$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
	}


	if( $ct_code=trim($ct_code) ){
		$ct_ids = array();
		$query = $db->query("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code'");
		while( $rt = $db->fetch_array( $query ) ){
			$ct_ids[] = $rt['ct_id'];
		}
		if( $ct_ids ){
			$where .= " AND i.ct_id IN (".implode(',',$ct_ids).")";
		} else {
			$where .= " AND i.id < -1";
		}
	}

	if( $cti_code=trim($cti_code) ){
		$cti_ids = array();
		$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%'");
		while( $rt = $db->fetch_array( $query ) ){
			$cti_ids[] = $rt['cti_id'];
		}
		if( $cti_ids ){
			$where .= " AND i.cti_id IN (".implode(',',$cti_ids).")";
		} else {
			$where .= " AND i.id < -1";
		}
	}

	if($date_start=getgp("date_start")){
		$zs_ids=$db->get_col("SELECT * FROM `sp_certificate` WHERE `e_date` >= '$date_start' AND `deleted` = '0'");
		$zs_ids=array_merge($zs_ids,array(-1));
		$where .=" AND i.zs_id IN (".join(",",$zs_ids).")";
		}
	if($date_end=getgp("date_end")){
		$zs_ids=$db->get_col("SELECT * FROM `sp_certificate` WHERE `e_date` <= '$date_end' AND `deleted` = '0'");
		$zs_ids=array_merge($zs_ids,array(-1));
		$where .=" AND i.zs_id IN (".join(",",$zs_ids).")";
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
	$where .= " AND i.ctfrom >= '$ctfrom' AND i.ctfrom < '$ctfrom_e'";

	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );


	$where .= " AND i.deleted = '0'";
	if( !$export ){//不导出 EXECL
		$sv_total = array(0,0);

		$sv_total[0] = $db->get_var("SELECT COUNT(*) total FROM sp_ifcation i WHERE 1 $where AND i.status='0' ");
		$sv_total[1] = $db->get_var("SELECT COUNT(*) total FROM sp_ifcation i WHERE 1 $where AND i.status='1' ");
		$sv_total[2] = $db->get_var("SELECT COUNT(*) total FROM sp_ifcation i WHERE 1 $where AND i.status='2' ");
		$sv_total[3] = $db->get_var("SELECT COUNT(*) total FROM sp_ifcation i WHERE 1 $where AND i.status='3' ");
		$pages = numfpage( $sv_total[$status]);
	}

	$where .= " AND i.status = $status";
	$resdb = array();
	$sql =  "SELECT $fields FROM sp_ifcation i $join WHERE 1 $where ORDER BY i.id DESC $pages[limit]" ;
	$query = $db->query($sql);
	$enterprise=load("enterprise");
	
	while( $rt = $db->fetch_array( $query ) ){
		// $rt['province'] = f_region_province( $rt['areacode'] );
		$rt['province'] = read_cache("region",$rt['areacode']);
		$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
		$rt['iso_V'] = f_iso( $rt['iso'] );
		$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
		$rt['tb_date'] = mysql2date( 'Y-m-d', $rt['tb_date'] );
		$rt['up_date'] = mysql2date( 'Y-m-d', $rt['up_date'] );
		$rt['cert_status_V'] = f_certstate( $rt['cert_status'] );
		$rt['audit_code'] = LongToBr($rt['audit_code'], array(
        ";",
        "；"
    ));
	$rt['use_code']=LongToBr($rt['use_code'],array('；',';'));
	$resdb[$rt['id']] = $rt;
	if($export=='2'){
		$rt['use_code']=str_replace(array("<br/>","<br />"),";",$rt['use_code']);
		$rt['audit_code']=str_replace(array("<br/>","<br />"),";",$rt['audit_code']);
		$tid=$db->get_var("SELECT tid FROM `sp_project` WHERE `cti_id` = '$rt[cti_id]' AND `tid` <> '0' AND `deleted` = '0' ORDER BY `id` DESC");
		$t_info=$db->get_row("SELECT tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
		$shsj=substr($t_info[tb_date],0,10)."至".substr($t_info[te_date],0,10);
		$e_info=$enterprise->get(array("eid"=>$rt[eid]));
		$content=$e_info[person].$e_info[ep_phone]."\n".$e_info[person_tel]."\n".$e_info[person_mail];
		$data[]=array($rt[ep_name],$rt[use_code],$rt[audit_code],"","",f_iso($rt[iso])."-再认证",$rt[num],$shsj,"",$rt[e_date],$e_info[person],$e_info[ep_phone]."/".$e_info[person_tel],$e_info[person_mail],$rt['province']);
	}
}

	if( !$export ){//输出HTML页面
		tpl( 'audit/list_ifcation' );
	}elseif($export==2){

do_excel($data,"审核计划");
EXIT;


} else {//导出EXECL
	ob_start();
    tpl('xls/list_super');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('再认证维护项目列表', $data);
	}
?>