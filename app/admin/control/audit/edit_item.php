<?php
/*
 *	增加特殊审核项
 */
require( ROOT . '/data/cache/audit_ver.cache.php' );
$cti_id = (int)getgp('cti_id');
$ct_id=getgp("ct_id");
$pid=getgp("pid");
if( $step ){
	if($step=="del"){
		$pid=getgp("pid");
		$db->update("project",array("deleted"=>1),array("id"=>$pid));
		showmsg("success","success","?c=audit&a=list_item");
		exit;
	}

	$audit_type		= getgp( 'audit_type' );
	$audit_ver		= getgp( 'audit_ver' );
	$audit_kind		= (int)getgp( 'audit_kind' );
	$ifchangecert	= getgp( 'ifchangecert' );
	$pre_date		= getgp( 'pre_date' );
	$scope			= getgp("scope");
	$type_allow=array("1008","1009","1010","3001","2001","2002","2003");
	if(!in_array($audit_type,$type_allow)){
		$sql = "select * from sp_project where cti_id=$cti_id and deleted='0' and audit_type='$audit_type' order by audit_type asc ";
		$info = $db->get_row($sql);
		if($info){
			echo "<script type='text/javascript'>alert('同一种审核类型不允许重复');history.go(-1);</script>";
			exit;//@HBJ 2013-09-26 修复bug避免同一种审核类型重复
		}
	}
	if($audit_type=="1030" && !$audit_ver){
		echo "<script type='text/javascript'>alert('标准转换请选择新标准');history.go(-1);</script>";
			exit;
	}
	$row = $cti->get( array( 'cti_id' => $cti_id ) );
	if($audit_type!="1030")
		$audit_ver=$row['audit_ver'];
	$new_item = array(
		'eid'		=> $row['eid'],
		'cti_id'	=> $row['cti_id'],
		'ct_id'		=> $row['ct_id'],
		'ct_code'	=> $db->get_var("SELECT ct_code FROM `sp_contract` WHERE `ct_id` = '$ct_id' "),
		'cti_code'	=> $row['cti_code'],
		'ctfrom'	=> $row['ctfrom'],
		'iso'		=> $row['iso'],
		'audit_ver'	=> $audit_ver,
		'audit_code'=> $row['audit_code'],
		'use_code'	=> $row['use_code'],
		'st_num'	=> $row['xcsh_num'],
		'audit_type'=> $audit_type,
		'pre_date'	=> $pre_date,
		'scope'		=> $scope,
		'flag'		=> 1,
		'ifchangecert'	=> $ifchangecert
	);
	if( 1 == $audit_kind ){
		$new_item[status]=0;
		
	} elseif( 2 == $audit_kind ){
		$new_item[status]=3;
		
	}
	if($pid){
		$audit->edit($pid,$new_item);
		$url="?c=audit&a=list_item";
		}
	else{
		$audit->add( $new_item );
		$url="?c=audit&a=list_contract_item";
		}
	showmsg( 'success', 'success', $url );
} else {

	$sql = "select * from sp_project where cti_id='$cti_id' and deleted='0'  order by audit_type asc ";
	$res = $db->query($sql);
	$ddatas = array();
	while($row = $db->fetch_array($res)){
		$audit_ver=$row[audit_ver];
		$ddatas[] = $row;
	}

	if($audit_ver_array){
		$ver_temp = substr($audit_ver,0,3);
		foreach($audit_ver_array as $key=>$value){
			if($value['audit_ver'] != $audit_ver){
				if($ver_temp==$value['iso'] && $value['is_stop'] == 0 ){
					$audit_ver_radio.= "<input type='radio' name='audit_ver' value=\"$value[audit_ver]\">".$value[audit_basis].'<br>';
			 	}
			}
		}
	}
	$scope=$db->get_var("SELECT scope FROM `sp_contract_item` WHERE `cti_id` = '$cti_id' ");
	if($pid){
		$p_info=$db->get_row("SELECT cti_id,audit_type,pre_date,scope,ifchangecert FROM `sp_project` WHERE `id` = '$pid' ");
		extract($p_info,EXTR_OVERWRITE);
	
	}
	$audit_type_select2=str_replace("value=\"$audit_type\"","value=\"$audit_type\" selected",$audit_type_select2);
	tpl( 'audit/edit_item' );
}


?>