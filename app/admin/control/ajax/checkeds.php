<?php
	
	$checkeds=getgp('checkeds');
	// p($checkeds);exit;
	$res = $db->update("certificate",array('is_check'=>'e'),array('id'=>$checkeds));
	$REQUEST_URI="?c=certificate&a=approval_list";

	showmsg( 'success', 'success', $REQUEST_URI );