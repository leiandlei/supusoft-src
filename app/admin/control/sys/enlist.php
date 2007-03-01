<?php


$fields = $join = $where = '';
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	$where = " and type='en' ";
	if($name){
		$sql = "select eid from sp_enterprises where ep_name like '%$name%' ";
		$uids = array();
		$res = $db->query($sql);
		while($row = $db->fetch_array($res)){
			$uids[] = $row['eid'];
		}
		if($uids){
			$uid_str = implode("','",$uids);
			$where .= " and eid in ('$uid_str') ";
		}else{
			$where .= " and eid='' ";
		}
	}
	if($up_name){
		$sql = "select id from sp_hr where name like '%$up_name%' ";
		$uids = array();
		$res = $db->query($sql);
		while($row = $db->fetch_array($res)){
			$uids[] = $row['id'];
		}
		if($uids){
			$uid_str = implode("','",$uids);
			$where .= " and up_uid in ('$uid_str') ";
		}else{
			$where .= " and up_uid='' ";
		}
	}
	if($msg){
		$where .= " and msg  like '%$msg%' ";
	}
	if($s_date){
		$where .= " and up_date >= '$s_date' ";
	}
	if($e_date){
		$where .= " and up_date <= '$e_date' ";
	}
	$total = $db->get_var("SELECT COUNT(*) FROM sp_log $join WHERE 1 $where");
	$pages = numfpage( $total, 20, "?c=$c&a=$a" );
	$sql = "SELECT  id,eid,msg,ip,up_uid,up_date FROM sp_log $join WHERE 1 $where ORDER BY id asc $pages[limit]" ;
	$query = $db->query( $sql);
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$rt['eid'] = f_en_name($rt['eid']);
		$rt['up_uid'] = f_username($rt['up_uid']);
		$datas[]	= $rt;
	}

	tpl('sys/logenlist');