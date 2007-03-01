<?php


if(!$uid){
		echo '错误提示，没有关联人员';
		exit;
	}else{
		$where = " and uid='$uid'  ";
	} 
	$tip_msg = '新增资格';
	$user_info = $user->get( $uid );

	$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification $join WHERE 1 $where");
	$pages = numfpage( $total, 10, "?c=$c&a=$a&uid=$uid&id=$id" );
	$sql = "SELECT * FROM sp_hr_qualification $join WHERE 1 $where ORDER BY status desc $pages[limit]" ;

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		// $rt['status'] = $status_arr[$rt['status']]; //状态 
	
		$rt['qua_type_V']=read_cache('qualification',$rt['qua_type']); 
		$datas[] = $rt;
	}

	$youxiao = 'checked';
	$is_leader_check3 = 'checked';
	//编辑情况
	if($id){
		$row = $qualification->get( $id );
		extract( $row, EXTR_SKIP );
		$tip_msg = '编辑资格'; 
		if($is_leader=='1'){
			$is_leader_check1 = 'checked';
			$is_leader_check2 = '';
			$is_leader_check3 = '';
		}else if($is_leader=='2'){
			$is_leader_check1 = '';
			$is_leader_check2 = 'checked';
			$is_leader_check3 = '';
		}else if($is_leader=='2'){
			$is_leader_check1 = '';
			$is_leader_check2 = '';
			$is_leader_check3 = 'checked';
		}

	}
	tpl('hr/qua_edit');