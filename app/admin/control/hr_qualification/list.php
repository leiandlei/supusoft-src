<?php

//传入模板数据
	foreach( $is_leader_arr as $code => $item ){
		$is_leader_select .= "<option value=\"$code\">$item</option>";
	}
	$code =$qualification= '';
	$fields = $join = $where = '';
	$url_param = '?';
	//分页原理
  	foreach($_GET as $k=>$v){
		if( 'paged' == $k ) continue;
		$url_param .= "$k=$v&";
	}

	$url_param = substr( $url_param, 0, -1 );
 	extract( $_GET, EXTR_SKIP );
	$status = (int)$status;
	//查询条件
	$where = "";
	$name = trim($name);
	if( $name ){
		$where .= " AND h.name like '%$name%' ";
	}
	$h_code = trim($h_code);
	if( $h_code ){
		$where .= " AND h.code like '%$h_code%' ";
	}
	$easycode = trim($easycode);
	if( $easycode ){
		$where .= " AND h.easycode like '%$easycode%' ";
	}

	$major1 = trim($major1);
	$major2 = trim($major2);
	$major3 = trim($major3);
	$major4 = trim($major4);
	$major5 = trim($major5);
	if( $major1 ){
		$where .= " AND hq.major1 like '%$major1%' ";
	}
	if( $major2 ){
		$where .= " AND hq.major1 like '%$major2%' ";
	}
	if( $major3 ){
		$where .= " AND hq.major1 like '%$major3%' ";
	}
	if( $major4 ){
		$where .= " AND hq.major1 like '%$major4%' ";
	}
	if( $major5 ){
		$where .= " AND hq.major1 like '%$major5%' ";
	}
	if($m_separate){
		$where .= " AND h.m_separate ='$m_separate'";
	}
	
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
	$where .= " AND hq.ctfrom >= '$ctfrom' AND hq.ctfrom < '$ctfrom_e' ";

	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

	if( $department ){
		$where .= " AND h.department = '$department' ";
		$department_select = str_replace( "value=\"$department\">", "value=\"$department\" selected>" , $department_select );
	}

	if( $audit_job || $audit_job=='0' ){
		$where .= " AND h.audit_job = '$audit_job' ";
		$audit_job_select = str_replace( "value=\"$audit_job\">", "value=\"$audit_job\" selected>" , $audit_job_select );
	}
	if($iso){
		$where .= " AND iso = '$iso' ";
		$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
	}
	if($status){
		//$where .= " AND hq.status = '$status' ";
		$status_select = str_replace( "value=\"$status\">", "value=\"$status\" selected>" , $status_select );
	}
	$qua_no = trim($qua_no);
	if($qua_no){
		$where .= " AND hq.qua_no like '%$qua_no%' ";
	}
	//人员资格
 	$qualification=getgp('qualification');
	if($qualification){
 		$where .= " AND hq.qua_type in ($qualification) ";
		$qualification_select = str_replace( "value=\"$qualification\">", "value=\"$qualification\" selected>" , $qualification_select );
	}
	if($is_leader){
		$where .= " AND hq.is_leader = '$is_leader' ";
		$is_leader_select = str_replace( "value=\"$is_leader\">", "value=\"$is_leader\" selected>" , $is_leader_select );
	}
	//结束时间
	$e_date_s = getgp('e_date_s');
	if($e_date_s) $where .= " AND hq.e_date >= '$e_date_s' ";
	$e_date_e = getgp('e_date_e');
	if($e_date_e) $where .= " AND hq.e_date <= '$e_date_e' ";
	
	$cert30 = (int)getgp('cert30');
	if( $cert30 ){
		$curr_date = mysql2date( 'Y-m-d', current_time('mysql') );//今天
		$month_1 = thedate_add( $curr_date, 3, 'month' );
		//即将到期的审核员证书
		$where .= " AND e_date > '$curr_date' AND e_date < '$month_1'";
	}
	
	$status_0_tab = $status_1_tab = '';
	${'status_'.$status.'_tab'} = ' ui-tabs-active ui-state-active';


	$age_limit = getgp( 'age_limit' );
	$s_date=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-65));
	if( $age_limit){
		$s_date=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-$age_limit));
		$where .= " AND h.birthday < '$s_date'";

	}
	//资格到期
	$e_date_limit=getgp('e_date_limit');
	if($e_date_limit){
		$e_date=date('Y-m-d',mktime(0,0,0,date('m')+$e_date_limit,date('d'),date('Y')));
		$where .=" AND hq.e_date<'$e_date'";
	}
	
	//年度确认
	$year_limit=getgp('year_limit');
	$to_year = date('Y');
	$oneyearago = date('Y',strtotime('-1 YEAR',strtotime($to_year)));
	$twoyearago = date('Y',strtotime('-2 YEAR',strtotime($to_year)));
	$threeyearago = date('Y',strtotime('-3 YEAR',strtotime($to_year)));
	
	$oneyearago_two = $oneyearago."-02-00";
	$oneyearago_three = $oneyearago."-03-00";
	$oneyearago_eight = $oneyearago."-08-00";
	$oneyearago_night = $oneyearago."-09-00";
	
	$twoyearago_two = $twoyearago."-02-00";
	$twoyearago_three = $twoyearago."-03-00";
	$twoyearago_eight = $twoyearago."-08-00";
	$twoyearago_night = $twoyearago."-09-00";
	
	$threeyearago_night = $threeyearago."-09-00";
	
	if($year_limit){
		if($year_limit == '2'){
			$where .=" AND ((hq.s_date > '{$threeyearago_night}' and hq.s_date < '{$twoyearago_two}') or (hq.s_date > '{$twoyearago_night}' and hq.s_date < '{$oneyearago_two}'))";
		}else{
			$where .=" AND ((hq.s_date > '{$twoyearago_three}' and hq.s_date < '{$twoyearago_night}') or (hq.s_date > '{$oneyearago_three}' and hq.s_date < '{$oneyearago_night}'))";
		}
	}
	
	//连表查询
	$join = ' as hq left join sp_hr as h on hq.uid=h.id ';

	if( $is_hire ){
		$where .= " AND h.is_hire = '$is_hire'";
		$is_hire_sql1 = '';
		$is_hire_sql2 = '';
		$f_is_hire = str_replace( "value=$is_hire>", "value=\"$is_hire\" selected>" , $f_is_hire );
	}else{
		$is_hire_sql1 = ' AND h.is_hire IN (1,3)';
		$is_hire_sql2 = ' AND h.is_hire IN (1,2,3)';
	}
	$where .=" AND h.deleted=0";
	$where .=" AND hq.deleted=0";

	//多体系搜索
	$ckb_iso = getgp('ckb_iso');
	if( !empty($ckb_iso) ){
		$pages['limit']='';
	}else{
		$state_total = array(
	 			'1'=>$db->get_var("SELECT COUNT(*) FROM sp_hr_qualification $join WHERE 1 $where AND hq.status = '1'$is_hire_sql1"),
				'0'=>$db->get_var("SELECT COUNT(*) FROM sp_hr_qualification $join WHERE 1 $where and hq.status='0'$is_hire_sql2"),
				'11'=>$db->get_var("SELECT COUNT(*) FROM sp_hr_qualification $join WHERE 1 $where AND hq.status = '1'$is_hire_sql1")
			);
		$pages = numfpage( $state_total[$status], 20, $url_param );
	}
    if( $export ){
		$pages['limit']='';
	}
	$where .= " AND hq.status = '$status'";
	$where .= " AND hq.deleted = '0'";
	$sql = "SELECT *,h.id as uid,hq.id as qid,h.birthday,h.is_hire as is_hire,h.tel,h.unit,h.areacode_str,
			(YEAR(CURDATE())-YEAR(h.birthday))-(RIGHT(CURDATE(),5)<RIGHT(h.birthday,5)) AS age
			FROM sp_hr_qualification $join WHERE 1 $where ORDER BY h.id DESC $pages[limit]" ;
	$query = $db->query($sql);
	while( $rt = $db->fetch_array( $query ) ){
		$rt['audit_times'] = $db->get_var("select COUNT(*) from sp_task_audit_team where uid = {$rt[uid]} and iso='{$rt[iso]}' and taskBeginDate >= '{$rt[s_date]}' and taskEndDate <= '{$rt[e_date]}'");
		$rt['leader_times'] = $db->get_var("select COUNT(*) from sp_task_audit_team where uid = {$rt[uid]} and role='01' and iso='{$rt[iso]}' and taskBeginDate >= '{$rt[s_date]}' and taskEndDate <= '{$rt[e_date]}'");
		
		// $rt['name'] = ( $rt['birthday'] < $s_date) ? '<span class="cRed">'.$rt['name'].'</span>' : $rt['name'];
		$rt['ctfrom'] = $ctfrom_array[$rt['ctfrom']]['name'];
		if($rt['iso']!='OTHER') {
			$rt['iso_num'] = $rt['iso'];
			$rt['iso']     = f_iso($rt['iso']);
		}

		$rt['audit_job'] = read_cache("audit_job",$rt['audit_job']);
		if($rt['iso']!='OTHER') {
			$rt['qua_type'] = read_cache("qualification",$rt['qua_type']);
		}
		//读取个人经历列表
		if ($export and $rt[uid]) {
			
			$hr_info=$db->get_row("SELECT department,position FROM sp_hr_experience  where deleted='0' AND add_hr_id=$rt[uid] and type='j' order by department desc");
			$rt[department]=read_cache("education",$hr_info[department]);
			$rt[position]=$hr_info[position];
			$rt[email]=$db->get_var("SELECT meta_value FROM `sp_metas_hr` WHERE `ID` = '$rt[uid]' AND `meta_name` = 'mail'");
		}
		$uids[] = $rt[uid]; 
		
		$r[] = chk_arr($rt);
	}
	$qualis=array();
	if( !empty($ckb_iso) ){
		//多体系筛选数据
		foreach ($r as $v) {
			$re[$v['uid']][$v['iso_num']] = $v;
		}

		foreach ($re as $key => $value) {
			foreach ($ckb_iso as $va) {
				if( !array_key_exists($va,$value) ){
					unset($re[$key]);
					break;
				}
			}
		}

		foreach ($re as $val) {
			foreach ($val as $valu) {
				$qualis[] = $valu;
			}
		}
	}else{
		$qualis = $r;
	}

	if( !$export ){
		tpl('hr/qualification_list');
	} else {
		ob_start();
		tpl( 'xls/list_qualification' );
		$data = ob_get_contents();
		ob_end_clean();

		export_xls( '人员注册资格列表', $data );
	}
