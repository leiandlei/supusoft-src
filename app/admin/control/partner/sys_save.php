<?php
/* 
* @Author: anchen
* @Date:   2017-06-08 15:02:19
* @Last Modified by:   anchen
* @Last Modified time: 2017-06-08 15:59:27
*/

	$check_sys = getgp('check_sys');
	$sys       = getgp('sys');
	$uid       = getgp('uid');
	$sys       = @ implode("|",$check_sys);
	$value     = '';
	if($uid){
		$value=array(
			'sys' => $sys
		);
		$db -> update( 'partner',$value,array('pt_id'=>$uid),false );
		$REQUEST_URI="?c=partner&a=partner_list";
		showmsg( 'success', 'success', $REQUEST_URI );
	}else{
		echo 'error';
	}

?>
