<?php
//人员专业经历：人员模块
require_once( ROOT . '/data/cache/education.cache.php' );
extract( $_GET, EXTR_SKIP );
$exp=load('experience');
$user = load('user');

$export=getgp('export'); //导出数据

$online_arr = array('0'=>'待备案','1'=>'已备案');
$paged = getgp('paged');
$id=getgp('id');
$uid = current_user('uid');

foreach( $online_arr as $code => $item ){
	$online_select .= "<option value=\"$code\">$item</option>";
}
$where = $join = '';
$join .= " inner join sp_hr h on h.id=he.add_hr_id ";
if($s_dates){
	$where .= " and he.s_date >= '$s_dates' ";
}
if($s_datee){
	$where .= " and he.s_date <= '$s_datee' ";
}
if($e_dates){
	$where .= " and he.e_date >= '$e_dates' ";
}
if($e_datee){
	$where .= " and he.e_date <= '$e_datee' ";
}
if($online=='1'){
	$online_select = str_replace( "value=\"$online\">", "value=\"$online\" selected>" , $online_select );
	$where .= " and he.online = '1' ";
}else if($online=='0'){
	$online_select = str_replace( "value=\"$online\">", "value=\"$online\" selected>" , $online_select );
	$where .= " and he.online = '0' ";
}
$add_name = trim($add_name);
if($add_name){
	$where .= " and h.name like '%$add_name%' ";
} 

	$where.=" AND he.deleted='0'";
	$total['g'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 AND type='g'  $where");
	$total['j'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 AND type='j'  $where");
	$total['s'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 AND type='s'  $where");
	$total['p'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 AND type='p'  $where");
	$add_hr = trim($add_hr);
	if($add_hr){
		$where .= " and h.name like '%$name%' ";
	}
	$area = trim($area);
	if($area){
		$where .= " and he.area like '%$area%' ";
	}
	$department = trim($department);
	if($department){
		$where .= " and he.department like '%$department%' ";
	}
	$position = trim($position);
	if($position){
		$where .= " and he.position like '%$position%' ";
	}
	$name = trim($name);
	if($name){
		$where .= " and he.name like '%$name%' ";
	}

//更新备案状态
if($_POST){
	 foreach($_POST as $k=>$v){
		 $db->update( 'hr_experience', array( 'online' =>'1'), array( 'id' => $k ) );
 	 }
}
if($a=='glist'){

 	$where .=" AND he.type='g'";
	$total['g'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 $where");
  	if(!$export){
		 $pages = numfpage( $total['g']);
	}

	$sql = "SELECT h.name as add_hr,he.* FROM sp_hr_experience he $join where h.deleted='0'  $where order by he.id  $pages[limit]";
	$query = $db->query( $sql );
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$datas[] = chk_arr($rt);
	}

	 if( !$export ){
		tpl( 'hr/exp_glist' );
	} else {
		ob_start();
		tpl( 'xls/list_hr_glist' );
		$datas = ob_get_contents();
		ob_end_clean();
		export_xls( '工作经历表', $datas );
	}
}else if($a=='gdel'){
	if($id){
		$exp->del($id);
	}
	$exp_info=$exp->get($id);
	$uid=$exp_info['add_hr_id'];

	log_add('',$uid ,'删除工作经历','',serialize($exp->get($id)));
	$REQUEST_URI='?c=hr_exp&a=glist';
	 showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='jlist'){
	$where .=" AND type='j'";
	$total['j'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience  he $join where 1 $where");
	if(!$export){
	$pages = numfpage( $total['j']);
	}
	$sql = "SELECT h.name as add_hr,he.* FROM sp_hr_experience he $join where h.deleted='0'  $where order by he.id asc $pages[limit]";
	$query = $db->query( $sql );
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$rt['department']=$education_array[$rt['department']]['name'];
		$datas[] = chk_arr($rt);
	}

  if( !$export ){
		tpl( 'hr/exp_jlist' );
	} else {
	ob_start();
		tpl( 'xls/list_hr_jlist' );
		$datas = ob_get_contents();
		ob_end_clean();
		export_xls( '教育经历', $datas );
	}


}else if($a=='jdel'){
	if($id){
		$exp->del($id);
	}
	$exp_info=$exp->get($id);
	$uid=$exp_info['add_hr_id'];
	log_add('',$uid ,'删除教育经历','',serialize($exp->get($id)));
	$REQUEST_URI='?c=hr_exp&a=jlist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='slist'){
	$where.=" AND type='s'";
	$total['s'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 $where");
	if(!$export){
	$pages = numfpage( $total['s'] );
	}
	$sql = "SELECT h.name as add_hr,he.* FROM sp_hr_experience he $join where h.deleted='0' $where order by he.id asc $pages[limit]";
	$query = $db->query( $sql );
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$datas[] = chk_arr($rt);
	}
 if( !$export ){
		tpl( 'hr/exp_slist' );
	} else {
		ob_start();
		tpl( 'xls/list_hr_slist' );
		$datas = ob_get_contents();
		ob_end_clean();
		export_xls( '审核经历', $datas );
	}

}else if($a=='sdel'){
	if($id){
		$exp->del($id);
	}
	$exp_info=$exp->get($id);
	$uid=$exp_info['add_hr_id'];
	log_add('',$uid ,'删除审核经历','',serialize($exp->get($id)));
	$REQUEST_URI='?c=hr_exp&a=slist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='plist'){
	$where.=" AND type='p'";
	$total['p'] = $db->get_var("SELECT COUNT(*) FROM sp_hr_experience he $join where 1 $where");
	if(!$export){
	$pages = numfpage( $total['p'] );
	}
	$sql = "SELECT h.name as add_hr,he.* FROM sp_hr_experience he $join where h.deleted='0'  $where order by he.id asc $pages[limit]";
	$query = $db->query( $sql );
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$datas[] = chk_arr($rt);
	}
	if( !$export ){
		tpl( 'hr/exp_plist' );
	} else {
		ob_start();
		tpl( 'xls/list_hr_plist' );
		$datas = ob_get_contents();
		ob_end_clean();
		export_xls( '培训经历', $datas );
	}
}else if($a=='pdel'){
	if($id){
		$exp->del($id);
	}
	$exp_info=$exp->get($id);
	$uid=$exp_info['add_hr_id'];
	log_add('',$uid ,'删除培训经历','',serialize($exp->get($id)));
	$REQUEST_URI='?c=hr_exp&a=plist';
	showmsg( 'success', 'success', $REQUEST_URI );
}
?>