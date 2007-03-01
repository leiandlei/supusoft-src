<?php
/*
 * 评定模块认证决定
 */ 
require CONF.'/cache/iso.cache.php'; //子证书
 
$tid    = ( int ) getgp ( 'tid' );
$ct_id  = ( int ) getgp ( 'ct_id' );
$step   = getgp ( 'step' );
// $sp_type=getgp ( 'sp_type' );
// echo "<pre />";
// print_r($sp_type);exit;
// @zxl 2013-11-25 13:44:34 删除评定问题
$type  = getgp ( 'type' ); 
$id    = getgp ( 'id' );
$pd_id    = getgp ( 'pd_id' );
if ($id and $type == 'del') {
	$db->query ( "delete from sp_assess_notes where id=$id" );
} 
//评分操作
if($_POST['access']){
	load('proj.access')->save_more($_POST['access']);  
	showmsg('success', 'success', "?c=assess&a=edit&tid=$_GET[tid]".'#tab-result');
} 
// 认证标志
require_once (ROOT . '/data/cache/mark.cache.php');
$mark_checkbox = '';
if ($mark_array) {
	foreach ( $mark_array as $code => $item ) {
		if ($item ['is_stop'])
			continue;
		$mark_checkbox .= "<label><input type=\"radio\" name=\"\" class=\"mark-item\"  value=\"$code\"/>$item[name]</label> &nbsp; ";
	}
}
$mark_checkbox1=$mark_checkbox;
if ($step) {
	// 验证是否指定评定人员
	$audit_codes       = array_map ( 'trim', getgp ( 'audit_code' ));
	$audit_codes_2017  = array_map ( 'trim', getgp ( 'audit_code_2017' ));//2017
	$use_codes         = array_map ( 'trim', getgp ( 'use_code' ) );
	$use_code_2017     = array_map ( 'trim', getgp ( 'use_code_2017' ) );//2017
	
	$marks        = getgp ( 'marks' );
	$scopes       = getgp ( 'scope',$scopes );

	$scopes       = array_map ( 'trim',$scopes  );
	$app_scopes   = getgp ( 'app_scope' );

	$app_scopes   = array_map ( 'trim',$app_scopes  ); // 申请范围

	$cti_id       = getgp ( 'cti_id' );
	$cti_id       = array_map ( 'trim',$cti_id  );
	$assess_date  = getgp ( 'assess_date' );
	$assess_dates = array_map ( 'trim',$assess_date  );
	$sp_date      = getgp ( 'sp_date' );
	$sp_dates     = empty($sp_date)?date('Y-m-d'):array_map ( 'trim', $sp_date ); // 总经理审批时间
	$pd_type      = getgp ( 'pd_type' );
	$pd_types     = array_map ( 'intval', $pd_type ); // 通过 待定 不通过
	$sp_types      = getgp ( 'sp_type',$sp_types );
	// $sp_types     = array_map ( 'intval', $sp_types ); // 经理审批通过  不通过

	$note         = getgp ( 'note' );
	$notes        = array_map ( 'trim', $note );
	$ifchangecert = getgp ( 'ifchangecert' );
	
	$if_cert      = array_map ( 'trim',$ifchangecert  ); // 是否发证，是否在证书登记中显示
   	
	if ($audit_codes || $audit_codes_2017) 
	{ 
		$audit = load ( 'audit' );
		$cert  = load ( 'certificate' );
		//2017
		foreach ( $audit_codes as $pd_id => $audit_code ) 
		{
			$new_pd = array (
					'pd_audit_code_2017'    => $audit_codes_2017 [$pd_id],
					'pd_use_code_2017'      => $use_code_2017 [$pd_id],
			);
			
			$audit->edit ( $pd_id, $new_pd );
			$row  = $audit->get ( array (
					'id' => $pd_id 
			) );
			
			if($row['audit_type']=='1003'){
				$db->update("project",$new_pd,array("audit_type"=>'1002',"cti_id"=>$row[cti_id]));
				
			}
			$conpd_new = array(
					    'audit_code_2017' => $new_pd['pd_audit_code_2017'],
						'use_code_2017'   => $new_pd['pd_use_code_2017'],
			     );
			
			if(!empty($conpd_new)){
				$db->update("contract_item",$conpd_new,array("cti_id"=>$row['cti_id']));
			}
		}
		foreach ( $audit_codes as $pd_id => $audit_code ) 
		{
			
			$new_pd = array (
					'pd_audit_code'   => $audit_codes [$pd_id],
					'pd_use_code'     => $use_codes [$pd_id],
					'pd_scope'        => $scopes [$pd_id],
					'comment_date'    => $assess_dates[$pd_id],
					'sp_date'         => $sp_dates [$pd_id],
					'pd_type'         => $pd_types [$pd_id],//评定通过
					'sp_type'         => $sp_types,//审批通过
					'ifchangecert'    => $if_cert [$pd_id],
					'comment_note'    => $notes [$pd_id],
					'pd_mark'         => $marks [$pd_id],
			);
		
			$audit->edit ( $pd_id, $new_pd );
			$row  = $audit->get ( array (
					'id' => $pd_id 
			) );
			
			if($row['audit_type']=='1003'){
				$db->update("project",$new_pd,array("audit_type"=>'1002',"cti_id"=>$row[cti_id]));
				
			}
			$conpd_new = array(
					   'audit_code' =>$new_pd['pd_audit_code'],
					   'use_code'   =>$new_pd['pd_use_code'],
					   'exc_clauses'=>$new_pd['pd_exc_clauses'],
					   'scope'      =>$new_pd['pd_scope'],
					   'mark'       =>$new_pd['pd_mark'],
			     );
			if(!empty($conpd_new)){
				$db->update("contract_item",$conpd_new,array("cti_id"=>$row['cti_id']));
			}
			// 评定通过
			if (1 == intval ( $pd_types [$pd_id] )) 
			{
				if($row['rect_finish']!='2')$audit->edit ( $pd_id, array('is_finish' => '1'));
					if (1 != intval ( $if_cert [$pd_id] )) 
					{
						$sms_arr = array (
							"pid"     => $row [id],
							"eid"     => $row [eid],
							"temp_id" =>$db->get_var("SELECT id FROM sp_certificate WHERE cti_id = '$row[cti_id]' AND deleted=0"),//此处取证书id
							"flag"    => 2,
							"is_sms"  =>0
						);
						$id=$db->get_var("SELECT id FROM `sp_sms` WHERE `pid` = '$sms_arr[pid]' AND `flag` = '2' AND `deleted` = '0'");
						if($id)
						{
							load ( "sms" )->edit ( $id,$sms_arr );
						}else{
							load ( "sms" )->add ( $sms_arr );
						}
							
					}
			}
		}
		showmsg ( 'success', 'success', "?c=assess&a=edit_tg&pd_id=$pd_id&ct_id=$ct_id&tid=$tid#tab-edit" );
	 
	} else {
 
		showmsg ( 'error','error', "?c=assess&a=edit_tg&pd_id=$pd_id&tid=$tid#tab-edit" );
	}
	// echo "<pre />";
	// print_r($pd_id);exit;
} else { // 显示信息
	$pd_id = ( int ) getgp ( 'pd_id' );

	$url=$_SERVER[HTTP_REFERER];
	$is_pder = false;
	// 当前任务的审核文档
	
	$res = $db->query("select * from sp_attachments where tid='$tid' and tid<>0 ORDER BY `sort`");

	while($rt = $db->fetch_array($res)){

		$rt['uid'] = f_username($rt['create_uid']);
		$rt ['ftype_V'] = f_arctype ( $rt ['ftype'] );
		$task_archives [$rt ['id']] = $rt;
	}
	$_uids=$db->get_col("SELECT uid FROM `sp_task_audit_team` WHERE `tid` = '$tid' AND `deleted` = '0'");
	$_uids=array_merge($_uids,array(-1));
	$comment_a_name_select = "";
	$sql = "SELECT id,name FROM `sp_hr` WHERE `is_hire` = '1' AND `job_type` LIKE '%1006%' and id not in (".join(",",$_uids).") ";
	$query = $db->query ( $sql );
	while ( $row = $db->fetch_array ( $query ) ) {
		$comment_a_name_select .= "<option value=\"$row[id]\">$row[name]</option>";
	}
	
	$comment_b_name_select = "";
	$sql = "SELECT id,name FROM `sp_hr` WHERE `is_hire` = '1' AND `job_type` LIKE '%1006%' and id not in (".join(",",$_uids).") ";
	$query = $db->query ( $sql );
	while ( $row = $db->fetch_array ( $query ) ) {
		$comment_b_name_select .= "<option value=\"$row[id]\">$row[name]</option>";
	}

	$comment_c_name_select = "";
	$sql = "SELECT id,name FROM `sp_hr` WHERE `is_hire` = '1' AND `job_type` LIKE '%1007%' and id not in (".join(",",$_uids).") ";
	$query = $db->query ( $sql );
	while ( $row = $db->fetch_array ( $query ) ) {
		$comment_c_name_select .= "<option value=\"$row[id]\">$row[name]</option>";
	}

	// 认证决定 
	$pds   = array (); 
	$join .= " LEFT JOIN sp_task t ON t.id = p.tid"; 
	$where = " AND p.tid = '$tid'";
	$where .= " AND p.deleted = 0"; 
	$where .= " AND p.pd_type = 1";
	// $where .= " AND p.id = '$pd_id'";


	$sql   = "SELECT p.*,t.te_date  FROM sp_project p $join WHERE 1 $where";
	$query = $db->query ( $sql );

	$zy_select = array ();
	$ct_info=$db->get_row("SELECT * FROM `sp_contract` WHERE `ct_id` = '$ct_id'");
	// p($ct_info);
	$iso_arr=array();
	$_uids1 = array();
	$_uids1=array_merge($_uids1,array(-1));
	while ( $rt = $db->fetch_array ( $query ) ) 
	{
		$rt['audit_codeid_2017'] = $rt['audit_code_2017'];
		
		if(!empty($rt['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $rt['audit_code_2017']));
			$codeims   = '';
			
			foreach($codeList as $code)
			{
				
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$rt['audit_code_2017'] = $codeims;
		}
		if(!empty($rt['pd_audit_code_2017']))
		{
			$rt['pd_audit_codeid_2017'] = $rt['pd_audit_code_2017'];
			$codeList  = array_filter(explode('；', $rt['pd_audit_code_2017']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}	

			$rt['pd_audit_code_2017'] = $codeims;
		}
		if(!empty($rt['pd_audit_code']))
		{
			$rt['pd_audit_codeid'] = $rt['pd_audit_code'];
			
			$codeList  = array_filter(explode('；', $rt['pd_audit_code']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$rt['pd_audit_code'] = $codeims;
		}
		if(!empty($rt['audit_code']))
		{
			$rt['audit_codeid'] = $rt['audit_code'];
			
			$codeList  = array_filter(explode('；', $rt['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$rt['audit_code'] = $codeims;
		}

		$use_codes=explode("；",$rt['use_code']);
		$use_codes=array_unique($use_codes);
		$rt['use_code']=JOIN("；",$use_codes);
		$rt["comment_a_select"]= str_replace("value=\"$rt[comment_a_uid]\"","value=\"$rt[comment_a_uid]\" selected",$comment_a_name_select);
		$rt["comment_b_select"]= str_replace("value=\"$rt[comment_b_uid]\"","value=\"$rt[comment_b_uid]\" selected",$comment_b_name_select);
		$rt["comment_c_select"]= str_replace("value=\"$rt[comment_c_uid]\"","value=\"$rt[comment_c_uid]\" selected",$comment_c_name_select);
		$rt ['audit_type_V']   = f_audit_type ( $rt ['audit_type'] );
		$rt ['audit_ver_V']    = f_audit_ver ( $rt ['audit_ver'] );
		//认可标识判断 若存在评定已修改的标识 引最新的标识
		if(!empty($rt['pd_mark']))
		{
			$rt ['mark'] = $rt['pd_mark'];
		}else{
			$rt ['mark'] = $rt['mark'];
		}
		!$rt ['mark'] && $rt ['mark']='01';
		$checkbox=str_replace("name=\"\"","name=\"marks[$rt[id]]\"",$mark_checkbox);
		$rt ['mark_checkbox'] = str_replace ( "value=\"$rt[mark]\"", "value=\"$rt[mark]\" checked", $checkbox);
		if (! $rt ['assess_date'] || $rt ['assess_date'] == '0000-00-00') 
		{
			$rt ['assess_date'] = date ( 'Y-m-d' );
		}
		if (! $rt ['sp_date'] || $rt ['sp_date'] == '0000-00-00') 
		{
			$rt ['sp_date'] = date ( 'Y-m-d' );
		}
		if (in_array ( $rt ['audit_type'], array (
				'1001',
				//'1002',
				'1003',
				'1007' 
		) )) {
			$rt ['if_cert'] = 1;
		}
		if ($rt ['if_cert']) {
			$checks [$rt ['id']] ['y'] = 'checked';
			$checks [$rt ['id']] ['n'] = '';
		} else {
			$checks [$rt ['id']] ['y'] = '';
			$checks [$rt ['id']] ['n'] = 'checked';
		}
		
		if (empty ( $rt ['final_date'] ) || $rt ['final_date'] < $rt ['te_date']) {
			if($rt[iso]=="A12"){
				$rt ['pre_date']   = get_addday ( $rt ['te_date'], 5, - 1 );
				$rt ['final_date'] = get_addday ( $rt ['te_date'], 6, - 1 );
			}else{
				$rt ['pre_date']   = get_addday ( $rt ['te_date'], 11, - 1 );
				$rt ['final_date'] = get_addday ( $rt ['te_date'], 12, - 1 );
			}
		} else {
			if($rt[iso]=="A12"){
				$rt ['pre_date']   = get_addday ( $rt ['te_date'], 5, - 1 );
				$rt ['final_date'] = get_addday ( $rt ['te_date'], 6, - 1 );
			}else{
				$rt ['pre_date']   = get_addday ( $rt ['te_date'], 11, - 1 );
				$rt ['final_date'] = get_addday ( $rt ['te_date'], 12, - 1 );
			}
		}

		$rt ['pd_type_1'] = $rt ['pd_type_2'] = $rt ['pd_type_3'] = $rt ['pd_type_4'] = '';
		$rt ['pd_type_' . $rt ['pd_type']] = ' selected';
		
		$eid = $rt ['eid'];
		$e_ct_id = $rt ['ct_id'];
 
		$rt ['comment_a_pass_1'] = $rt ['comment_a_pass_2'] = $rt ['comment_b_pass_1'] = $rt ['comment_b_pass_2'] = '';
		$rt ['comment_a_pass_' . $rt ['comment_pass']] = $rt ['comment_b_pass_' . $rt ['comment_b_pass']] = ' checked';
		
		$rt [cert_scope] = $db->get_var ( "select cert_scope from sp_certificate where cti_id='{$rt[cti_id]}' and deleted=0" );
		//评分用
		$cti_ids[$rt['cti_id']] = $rt['cti_id'];
		$pds [$rt ['id']]       = $rt;
		$iso_arr[$rt[cti_id]]   = f_iso($rt[iso]);
	}
	      // echo "<pre />";
       //    print_r($pds);exit;
	$result=$db->get_results("select * from sp_assess_notes WHERE 1 AND tid='$tid' AND deleted='0' ORDER BY id");
	//子公司，子证书范围
	$child_query=$db->query("SELECT ep_name,eid FROM `sp_enterprises` WHERE `parent_id` = '$eid' AND `deleted` = '0' ORDER BY `eid` ");
	$scope_childs=array();
	while($r=$db->fetch_array($child_query)){
		$r[num]=$db->get_results("SELECT * FROM `sp_contract_num` WHERE `eid` = '$r[eid]' and type='1'",'cti_id');
		$r[num] && $scope_childs[]=$r;
	
	}

	// $scope_childs=$db->get_results("SELECT * FROM `sp_contract_num` WHERE `eid` in (".join(",",$child_ids).") AND `type` = '1'");
	// echo "<pre />";
	// print_r($scope_childs);exit;
	$task_info=$db->get_row("SELECT * FROM `sp_task` WHERE `id` = '$tid'");
	$task_info[tb_date]=trim($task_info[tb_date],":00:00");
	$task_info[te_date]=trim($task_info[te_date],":00:00");
 
	tpl ();
}
// 评定结束

?>