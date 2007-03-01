<?php
/*
 * 合同评审
 */
require( ROOT . '/data/cache/add_basis.cache.php' );
require( ROOT . '/data/cache/exc_basis.cache.php' );
$add_basis_check=$exc_basis_check="";

$ct_id = (int)getgp( 'ct_id' );
$c_info = $ct->get( array( 'ct_id' => $ct_id ) );


if( $step ){
	$status = (int)getgp( 'status' );
	$is_site = (int)getgp( 'is_site' );
	$site_note = getgp( 'site_note' );
	$site_yjd = getgp( 'site_yjd' );
	// $yjd_num = getgp( 'yjd' );
	// $ejd_num = getgp( 'ejd' );
	// $jd_num = getgp( 'jd' );
	//$ct_ps_uid = getgp('ct_ps_uid');
	if( !$status ) $status = 1;
	 
	$major_person = getgp( 'major_person' );
    $base_num = getgp( 'base_num' );//基础人日数
 
    $jdxc_num = getgp( 'jdxc_num' );//监督现场人日
    $yjdxc_num = getgp( 'yjdxc_num' );//一阶段现场人日
    $ejdxc_num = getgp( 'ejdxc_num' );//二阶段-再认证现场人日
    $zrz_num = getgp( 'zrz_num' );//
    $total_num = getgp( 'total_num' );//
    $ch_num = getgp( 'ch_num' );//
 
    $exc_clauses = getgp( 'exc_clauses' );//删减条款
    $add_basis = getgp( 'add_basis' );//
    $exc_basis = getgp( 'exc_basis' );//
    $add_num = getgp( 'add_num' );//
    $exc_num = getgp( 'exc_num' );//
	
    $risk_level = getgp( 'risk_level' );
	$audit_codes =getgp( 'audit_code' );
	$use_codes = getgp( 'use_code' );
	$scopes =@ array_map( 'strip_tags', getgp( 'scope' ) );
	$review_date = getgp( 'review_date' );
	$review_note = getgp( 'review_note' );
	$combine = getgp( 'combine' );
	$note = getgp( 'note' );
	$mark = getgp( 'mark' );
 
	if( $audit_codes ){
		foreach( $audit_codes as $cti_id => $audit_code ){
			$audit_code=str_replace(array(",","|",";","，"),"；",$audit_code);
			$use_codes[$cti_id]=str_replace(array(",","|",";","，"),"；",$use_codes[$cti_id]);
			$code=$use_codes[$cti_id];
			$code=explode("；",$code);
			$code=array_unique($code);
			$code=join("；",$code);
			$use_codes[$cti_id]=$code;
			$cti_array = array(
				'base_num'		=> $base_num[$cti_id], //
				'total_num'		=> $total_num[$cti_id], //
				'ch_num'		=> $ch_num[$cti_id], //
				'jdxc_num'		=> $jdxc_num[$cti_id], //
				'yjdxc_num'		=> $yjdxc_num[$cti_id], //
				'ejdxc_num'		=> $ejdxc_num[$cti_id], //
				'zrz_num'		=> $zrz_num[$cti_id], //
				'exc_clauses'	=> $exc_clauses[$cti_id], //
				'exc_basis'		=> serialize($exc_basis[$cti_id]), //
				'exc_num'		=> $exc_num[$cti_id], //
				'add_basis'		=> serialize($add_basis[$cti_id]), //
				'add_num'		=> $add_num[$cti_id], //
				'use_code'	=> $use_codes[$cti_id], //
				'audit_code'	=> $audit_code, //
				'scope'			=> $scopes[$cti_id], //
				'risk_level'	=> $risk_level[$cti_id], //
				'mark'	=> $mark[$cti_id], //
				
			);
		
			$cti->edit( $cti_id, $cti_array, $status );

		}
	}
/* 	$_codes=array();
	foreach($use_codes as $code){
		$codes=explode("；",$code);
		$_codes=array_merge($_codes,$codes);
	
	}
	if($_POST['major_person'])
		$zy_name=$_POST['major_person'];
	else{
		$zy_name=$db->get_col("SELECT zy_name FROM `sp_stff` where code in('".join("','",$_codes)."') and zy_name<>''");
		$zy_name=array_unique($zy_name);
		$zy_name=join("；",$zy_name);
	}
 */	//把状态修改成已评审

	$new_ct = array(
		'is_site'	=> $is_site,//是否现场
		'site_note'	=> $site_note,
		//'site_yjd'	=> $site_yjd,
		//'ct_ps_uid' => $ct_ps_uid,//评审人员
		'review_date' => $review_date,
		'review_note' => $review_note,
		'note' => $note,
		// 'combine' => $combine,
		'yjd_num' => getgp("yjd"),
		'ejd_num' => getgp("ejd"),
		'jd_num' => getgp("jd"),
		'zrz_num' => getgp("zrz"),
		'ch_num' => getgp("ch"),
		'review_uid' => current_user( 'uid' ),
		'status' => $status,
		// 'major_person'	=>$zy_name //专业管理人员
	);
	$ct->edit( $ct_id, $new_ct );
	$af_c_info = $ct->get( array( 'ct_id' => $ct_id ) );
	
	log_add($c_info['eid'],'','合同评审,合同号：'.$c_info['ct_code'],serialize($c_info),serialize($af_c_info));
	
	$REQUEST_URI='?c=contract&a=review&ct_id='.$c_info[ct_id].'&eid='.$c_info['eid'];
	showmsg( 'success', 'success', $REQUEST_URI );
} else { //读取数据
	$contract = $ct->get( array( 'ct_id' => $ct_id ) );
	extract( $contract, EXTR_SKIP );
 
	
	//是否禁用保存按钮
	$disabled_save = ( $status > 1 ) ? ' disabled' : '';
	if( $status >= 2 ){
		$checked = ' checked';
	} else {
		$status = 2;
	}

	if('0000-00-00' == $review_date){
		$review_date = '';
	}
	$is_first_V = ($is_first == 'y') ? '是' : '否';
	$is_site_Y = ($is_site)?'checked':'';
	$is_site_N = ($is_site)?'':'checked';

	$join = $where = '';
	$ct_items = array();
	$iso_arr = array();
	$where .= " AND ct_id = '$ct_id' AND deleted=0";
	$sql = "SELECT * FROM sp_contract_item  WHERE 1 $where order by iso";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$add_basis=unserialize($rt['add_basis']);
		foreach($add_basis_array as $k=>$item){
		if($item['is_stop']) continue;
		$ckeck="";
		if(@in_array($k,$add_basis))
			$ckeck="checked";
		$rt['add_basis_check'].="<label><input type=\"checkbox\" name=\"add_basis[$rt[cti_id]][]\" value=\"$k\" $ckeck />".$item['name']."</label><br/>";

	}
		$exc_basis=unserialize($rt['exc_basis']);
		foreach($exc_basis_array as $k=>$item){
			if($item['is_stop']) continue;
			$ckeck="";
			if(@in_array($k,$exc_basis))
				$ckeck="checked";
			$rt['exc_basis_check'].="<label><input type=\"checkbox\" name=\"exc_basis[$rt[cti_id]][]\" value=\"$k\" $ckeck />".$item['name']."</label><br/>";

		}

	//	$marks = explode(',', $rt['mark'] );
	//	$mark_arr = array();
	//	$rt['mark_checkbox'] = str_replace( "name=\"marks[]\"", "name=\"marks[{$rt['cti_id']}][]\"", $mark_checkbox );
	//	$rt['mark_checkbox'] = str_replace( "value=\"01\"/>", "value=\"01\" checked/>",  $rt['mark_checkbox'] );
	//	foreach( $marks as $mk ){
		//	$rt['mark_checkbox'] = str_replace( "value=\"$mk\"/>", "value=\"$mk\" checked/>",  $rt['mark_checkbox'] );
	//	}
		$rt[risk_level]?$rt[risk_level]:$rt[risk_level]='02';
		if($rt[iso]=='A01')$rt[risk_level]='03';
		$rt['risk_level_select'] = str_replace( "value=\"$rt[risk_level]\">", "value=\"$rt[risk_level]\" selected>",  $risk_level_select );
/* 		$rt['exc_basis_check']=$exc_basis_check;
		foreach(unserialize($rt['exc_basis']) as $v){
			$rt['exc_basis_check'] = str_replace( "value=\"$v\"", "value=\"$v\" checked",  $rt['exc_basis_check']);
		
		
		}
		$rt['add_basis_check']=$add_basis_check;
		foreach(unserialize($rt['add_basis']) as $v1){
			$rt['add_basis_check'] = str_replace( "value=\"$v1\"", "value=\"$v1\" checked",  $rt['add_basis_check']);
		
		
		}
 */		$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
		$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
		$rt['is_turn_V'] = ($rt['is_turn']) ? '是' : '否';
		$rt[display]="none";
	//	if($rt[wenshen]){
		//	$rt[wenshen_check]="checked";
		//	$rt[display]="";
	//	}
		
		$ct_items[$rt['cti_id']] = $rt;
		$rt[audit_code]=explode("；",$rt[audit_code]);
		$_audit_codes[$rt['cti_id']]=$rt;
	}

	//子公司
	$child_query=$db->query("SELECT * FROM `sp_enterprises` WHERE `parent_id` = '$c_info[eid]' AND `deleted` = '0' ORDER BY `eid`");
	$childs=$ep_sites=array();
	while($r=$db->fetch_array($child_query)){
		$r[num]=$db->get_row("SELECT * FROM `sp_contract_num` WHERE `eid` = '$r[eid]' and type='1'  and ct_id='$ct_id'");
		foreach($r[num] as $k=>$val)
			if($val=='0')
			$r[num][$k]="";
		$childs[]=$r;
	
	}
	//分场所
	$ep_site_query=$db->query("SELECT * FROM `sp_enterprises_site` WHERE `eid`= '$c_info[eid]'  AND deleted=0 ORDER BY `update_date` DESC");
	while($r1=$db->fetch_array($ep_site_query)){
		$r1[num]=$db->get_row("SELECT * FROM `sp_contract_num` WHERE `eid` = '$r1[es_id]' and type='2'  and ct_id='$ct_id'");
		foreach($r1[num] as $k=>$val)
			if($val=='0')
			$r1[num][$k]="";
		$ep_sites[]=$r1;
	
	}

	//专业审核代码

	$audit_codes = array();
	if( $_audit_codes ){
		foreach( $_audit_codes as $cti_id => $codes ){
			$query = $db->query("SELECT code,shangbao,risk_level,mark FROM sp_settings_audit_code WHERE iso = '{$codes['iso']}' AND  shangbao IN('".implode("','",$codes[audit_code])."') and deleted=0 and is_stop=0 ");
			while( $rt = $db->fetch_array( $query ) ){
				$marks = explode( ',', $rt['mark'] );
				$new_marks = array();
				foreach( $marks as $mk ){
					$mark_V = f_mark( $mk );
					if( $mark_V ){
						$new_marks[] = $mark_V;
					}
				}
				$rt['mark_V'] = implode( ',', $new_marks );
				$rt['risk_level_V'] = f_risk( $rt['risk_level'] );
				$audit_codes[$cti_id][] = $rt;
			}
		}

	}
	$ct_id_f=$db->get_var("SELECT ct_id FROM `sp_contract` WHERE `eid` = '$c_info[eid]' AND `ct_id` <> '$ct_id' AND `deleted` = '0' ORDER BY `ct_id` DESC limit 1");
 
	//$ct_ps_name=$db->get_var("SELECT name FROM `sp_hr` WHERE `id` = '$ct_ps_uid'");
//	!$ct_ps_uid && $ct_ps_uid=current_user("uid");
///	!$ct_ps_name && $ct_ps_name=current_user("name");
	!$review_date && $review_date=date("Y-m-d");
	tpl();
	$REQUEST_URI='?c=contract&a=review&ct_id='.$c_info[ct_id].'&eid='.$c_info['eid'];
}