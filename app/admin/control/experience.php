<?php
//审核员模块-人员经历
$exp=load('experience');
$online_arr = array('0'=>'待备案','1'=>'已备案'); 
$paged = getgp('paged');

//循环post提交值
foreach($_POST as $k=>$v){
	$value[$k] = getgp($k);
}
extract($_GET);

$id=getgp('id');
$uid = current_user('uid');

foreach( $online_arr as $code => $item ){
	$online_select .= "<option value=\"$code\">$item</option>";
}
$xueli_select=f_select('education'); //学历
$where = '';
if($s_dates){
	$where .= " and s_date >= '$s_dates' ";
}
if($s_datee){
	$where .= " and s_date <= '$s_datee' ";
}
if($e_dates){
	$where .= " and e_date >= '$e_dates' ";
}
if($e_datee){
	$where .= " and e_date <= '$e_datee' ";
}
if($online=='1'){
	$online_select = str_replace( "value=\"$online\">", "value=\"$online\" selected>" , $online_select );
	$where .= " and online = '1' ";
}else if($online=='0'){
	$online_select = str_replace( "value=\"$online\">", "value=\"$online\" selected>" , $online_select );
	$where .= " and online = '0' ";
}
if($area){
		$where .= " and area like '%$area%' ";
	}
	if($department){
		$where .= " and department like '%$department%' ";
	}
	if($position){
		$where .= " and position like '%$position%' ";
	}
	if($name){
		$where .= " and name like '%$name%' ";
}

$where .= " and deleted='0' and add_hr_id='$uid' ";  //公共条件
 
//数量统计
$total['g'] =$exp->get_num("$where AND type='g'");
$total['s'] =$exp->get_num("$where AND type='s'");
$total['j'] =$exp->get_num("$where AND type='j'");
$total['p'] =$exp->get_num("$where AND type='p'");
 
if($a=='glist'){  
	$where.=" and type='g'"; 
	$pages = numfpage( $total['g'], 20, "?c=$c&a=$a" );  
	//获取分页列表
 	$datas=$exp->get_page($where,$pages); 
	tpl( 'experience/glist' ); 
	
}else if($a=='gedit'){
	if($id){
		$row = $exp->get($id);
		extract($row, EXTR_SKIP);
	}
	tpl( 'experience/gedit' );
}else if($a=='gsave'){  
	if($id){
		$bf_str=serialize($exp->get($id));
		$exp->edit($id,$value);
		$af_str=serialize($exp->get($id));
		log_add(0, $uid, "编辑工作经历", $bf_str, $af_str);
	}else{ 
		$value['type']='g'; //工作经历
		$id=$exp->add($value);
		log_add(0, $uid, "添加工作经历", NULL, serialize($exp->get($id)));
	} 
	$REQUEST_URI='?c=experience&a=glist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='gdel'){
	if($id){
		$exp->del($id);
		log_add(0, $uid, "删除工作经历", NULL, serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=glist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='jlist'){ 
	$pages = numfpage( $total['j'], 20, "?c=$c&a=$a" );
	$where.=" AND type='j'";
	//获取分页列表
 	$datas=$exp->get_page($where,$pages);
tpl( 'experience/jlist' );
}else if($a=='jedit'){
	if($id){
		$row = $exp->get($id);
		extract($row, EXTR_SKIP);
		$xueli_select = str_replace( "value=\"$xueli\">", "value=\"$xueli\" selected>" , $xueli_select );
	} 
	tpl( 'experience/jedit' );
}else if($a=='jsave'){ 
	if($id){ 
		$bf_str=serialize($exp->get($id));	
		$exp->edit($id, $value);
		$af_str=serialize($exp->get($id));
		log_add(0,$uid, "编辑教育经历", $bf_str,$af_str );
	}else{ 
		$value['type']='j';
		$id=$exp->add($value);
		log_add(0, $uid, "添加教育经历", NULL, serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=jlist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='jdel'){
	if($id){
		$exp->del($id);
		log_add(0, $uid, "删除教育经历", NULL, serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=jlist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='slist'){ 
	$pages = numfpage( $total['s'], 20, "?c=$c&a=$a" );
	$where.=" AND type='s'";
	//获取分页列表
 	$datas=$exp->get_page($where,$pages); 
	tpl( 'experience/slist' ); 
}else if($a=='sedit'){
	if($id){
		$row = $exp->get($id);
		extract($row, EXTR_SKIP);
	}
	tpl( 'experience/sedit' );
}else if($a=='ssave'){ 
	if($id){
		$bf_str=serialize($exp->get($id));
		$exp->edit($id, $value);
		$af_str=serialize($exp->get($id));
		log_add(0, $uid, "编辑审核经历", $bf_str,$af_str);
	}else{ 
		$value['type']='s';
		$id=$exp->add($value);
		$af_str=serialize($exp->get($id));
		log_add(0, $uid, "添加审核经历", NULL, $af_str);
	}
	$REQUEST_URI='?c=experience&a=slist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='sdel'){
	if($id){
		$exp->del($id);
		log_add(0,$uid,'删除审核经历',NULL,serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=slist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='plist'){ 
	$pages = numfpage( $total['p'], 20, "?c=$c&a=$a" );
	$where.=" AND type='p'";
	//获取分页列表
 	$datas=$exp->get_page($where,$pages);
	tpl( 'experience/plist' );  
}else if($a=='pedit'){
	if($id){
		$row = $exp->get($id);
		extract($row, EXTR_SKIP);
	}
	tpl( 'experience/pedit' );
}else if($a=='psave'){ 
	if($id){
		$bf_str=serialize($exp->get($id));
		$exp->edit($id, $value);
		$af_str=serialize($exp->get($id));
		log_add(0, $uid, "编辑培训经历", $bf_str, $af_str);
	}else{ 
		$value['type']='p';
		$id=$exp->add($value);
		log_add(0, $uid, "添加培训经历", NULL, serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=plist';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='pdel'){
	if($id){
		$exp->del($id);
		log_add(0, $uid, "删除培训经历", NULL, serialize($exp->get($id)));
	}
	$REQUEST_URI='?c=experience&a=plist';
	showmsg( 'success', 'success', $REQUEST_URI );
}
?>