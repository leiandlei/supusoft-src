<?php
/*
 *保存评定人员
 */

 

	$tid = (int)getgp( 'tid' );//跳转用
	$pid = (int)getgp( 'pid' );

	$comment_a_uid =getgp( 'comment_a_name');
	
	foreach( $comment_a_uid as $pd_id => $uid ){
		$comment_a_name[$pd_id] =f_username($uid);
	}


	$zy_name = getgp( 'zy_name');
	
	$comment_b_uid = getgp( 'comment_b_name');
	foreach( $comment_b_uid as $pd_id => $uid ){
		if($uid)
			$comment_b_name[$pd_id] =f_username($uid);
	}

	$comment_c_uid = getgp( 'comment_c_name');
	foreach( $comment_c_uid as $pd_id =>$uid ){
		if($uid)$comment_c_name[$pd_id] = f_username($uid);
	}

	$comment_d_uid = getgp( 'comment_d_name');
	foreach( $comment_d_uid as $pd_id =>$uid ){
		if($uid)$comment_d_name[$pd_id] = f_username($uid);
	}

	foreach( $comment_a_uid as $pd_id => $uid ){
		$db->update( 'project', array(  'comment_a_uid' => $uid,
										'comment_a_name'=>$comment_a_name[$pd_id],
										'comment_b_uid'	=> $comment_b_uid[$pd_id],
										'comment_b_name'=> $comment_b_name[$pd_id],
										'comment_c_uid'	=> $comment_c_uid[$pd_id],
										'comment_c_name'=> $comment_c_name[$pd_id],
										'comment_d_uid'	=> $comment_d_uid[$pd_id],
										'comment_d_name'=> $comment_d_name[$pd_id],										
                                        ),
								array( 'id' => $pd_id ) );
	}

	showmsg( 'success', 'success', "?c=assess&a=edit&pd_id=$pd_id&tid=$tid#tab-hr" );
 