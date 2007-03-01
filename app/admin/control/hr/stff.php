<?php
if($_POST){
	if($_POST[zy_name]){
		$zy_uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$_POST[zy_name]'");
		if(!$zy_uid)
			$msg="专业管理人员不在人员基础数据中，请检查！";
	}
	if($_POST[zj_name]){
		$zj_uid=$db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$_POST[zj_name]'");
		if(!$zj_uid)
			$msg="技术专家不在人员基础数据中，请检查！";
	}
	if(!$msg){
		$up=array("code"=>$_POST[code],"zy_uid"=>$zy_uid,"zy_name"=>$_POST[zy_name],"zj_uid"=>$zj_uid,"zj_name"=>$_POST[zj_name]);
		if($id=$_POST[id])
			$db->update("stff",$up,array("id"=>$id));
		else
			$db->insert("stff",$up);
		showmsg("success","success","?c=hr&a=stff");
		}
	else{
		echo "<script>alert('".$msg."');window.history.go(-1);</script>";
	}

}else{
	if($id=getgp("id")){
		if(getgp("type")=="del"){
			$db->update("stff",array("deleted"=>'1'),array("id"=>$id));
			showmsg("success","success","?c=hr&a=stff");
		}else
			$code=$db->get_row("SELECT * FROM `sp_stff` WHERE id='$id'");

	}
	$use_code		= trim(getgp( 'use_code' ));
	if( $zy_name=getgp("zy_name") ){ //
		$where=" AND zy_name like '%$zy_name%'";
	}
	if( $zj_name=getgp("zj_name") ){ //
		$where=" AND zj_name like '%$zj_name%'";
	}

	if($use_code){
		$where.=" AND code like '%$use_code%'";
	
	}
	$where .=" AND deleted=0";
	$total=$db->get_var("SELECT COUNT(*) FROM `sp_stff` WHERE 1 $where");

	$pages = numfpage( $total,10 );

	//列表
	$datas = array();
	$sql = "SELECT * FROM `sp_stff` WHERE 1 $where  $pages[limit]";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$datas[]=$rt;
		
	}

	
	tpl();
	}