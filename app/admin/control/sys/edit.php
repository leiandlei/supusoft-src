<?php


// 权限表需先执行 UPDATE `sp_hr` SET `sys`= NULL WHERE 1
	$uid      = (int)getgp( 'uid' );
	$hr_info  = $user->get( $uid );
	$left_nav = array_reverse($left_nav); 
	tpl('sys/hr_edit');
?>