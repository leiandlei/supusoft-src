<?php

 $id = getgp('id');
	if($id){
		$auditcode->del($id);
	}
	$REQUEST_URI='?c=hr_code&a=list';
	showmsg( 'success', 'success', $REQUEST_URI );

?>