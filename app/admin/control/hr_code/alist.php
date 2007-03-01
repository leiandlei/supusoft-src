<?php
require_once('data/cache/ctfrom.cache.php');

//小类列表
$is_hire=getgp('is_hire');
	$fields = $join = $where = '';
	
	$where = "  and hq.status = '1'";

	$hr_where_arr = array();
	 $name= trim(getgp('name'));
	 $easycode = trim(getgp('easycode'));
	 $h_code = trim(getgp('h_code'));
	 $qua_no = trim(getgp('qua_no'));
	 $audit_job = getgp('audit_job');
	 $qua_type  = getgp('qua_type');
	 $iso       = getgp('iso');
	if( $name ){
		$where.=" AND hr.name like '%$name%'";
	}
	if( $easycode ){
		$where.=" AND hr.easycode like '%$easycode%' ";
	}
	if( $h_code ){
		$where.=" AND hr.code like '%$h_code%' ";
	}
	if($is_hire){
		$where.=" AND hr.is_hire = $is_hire";
		
	}else{
		$where.=" AND hr.is_hire IN (1,3)";
		 
	}
	

	//合同来源
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
	$where .= " AND hq.ctfrom >= '$ctfrom' AND hq.ctfrom < '$ctfrom_e'";
	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
 
	if($iso){
		$where .= " AND hq.iso = '$iso' ";
		$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
	}
	if($status){
		$where .= " AND hq.status = '$status' ";
		$status_select = str_replace( "value=\"$status\">", "value=\"$status\" selected>" , $status_select );
	}
	if($qua_no){
		$where .= " AND hq.qua_no like '%$qua_no%' ";
	}
	if($qua_type){
		$where .= " AND hq.qua_type = '$qua_type'";

		$qualification_select = str_replace( "value=\"$qua_type\">", "value=\"$qua_type\" selected>" , $qualification_select );
	}

	
	if( $audit_job || $audit_job=='0' ){
		$where .= " AND hr.audit_job = '$audit_job' ";
		$audit_job_select = str_replace( "value=\"$audit_job\">", "value=\"$audit_job\" selected>" , $audit_job_select );
	}
	 
	$join = " LEFT JOIN sp_hr hr ON hq.uid=hr.id";
	$where .= " AND hr.deleted=0";
	$where .= " AND hq.deleted=0";
	 
	$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification hq $join WHERE 1 $where");

	$pages = numfpage( $total );
	$sql = "SELECT hq.*,hr.is_hire,hr.name,hr.code,hr.ctfrom,hr.audit_job FROM sp_hr_qualification hq $join WHERE 1 $where ORDER BY hq.id DESC $pages[limit]";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['name'] = $rt['name'];
		$rt['audit_job'] = f_audit_job($rt['audit_job']);
		$rt['ctfrom'] = $ctfrom_array[$rt['ctfrom']]['name'];
		$rt['iso_V'] = f_iso($rt['iso']);
		$rt['department'] = f_department($rt['department']);
		$rt['is_hire_V']=$hr_is_hire[$rt['is_hire']];
		$rt['qua_type'] = $qualification_array[$rt['qua_type']]['name'];
		$rt['status'] = $status_arr[$rt['status']];
		$qualis[] = $rt;
	}
	tpl('hr/hr_code_alist'); 


?>
