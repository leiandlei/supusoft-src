<?php  
$status_arr = array('1'=>"<font color='blue'>有效</font>",'0'=>"<font color='red'>失效</font>");
$is_leader_arr  = array('1'=>'组长','2'=>'见习组长','3'=>'否' );
$f_is_hire=f_is_hire();  //人员状态

$qualification = load('qualification');
$user = load('user');

$id = (int)getgp('id' );
$uid = (int)getgp('uid');
$is_hire=getgp('is_hire');
if( $audit_job_array ){
	foreach( $audit_job_array as $code => $item ){
		$audit_job_select .= "<option value=\"$item[code]\">$item[name]</option>";
	}
}
//合同来源
$ctfrom_select = f_ctfrom_select();
$iso_select=f_select('iso');
$iso_select.='<option value="OTHER">其他</option>';
$qualification_select=f_select('qualification');

if( $status_array ){
	foreach( $status_array as $code => $item ){
		$status_select .= "<option value=\"$item[code]\">$item</option>";
	}
}

//引入模块控制下的方法
$action=CTL_DIR.$c.'/'.$a.'.php';
if(file_exists($action)){
	include_once($action);
}else{
	echo '该方法不存在，请检查对应程序';
	echo '<br />方法名称：'.$a;
}
?>