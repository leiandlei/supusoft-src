<?php
$id = getgp('id');

	if($id){
		$value = array(
			'is_sms' => '2',
			'sms_name'=>getgp('sms_name'),
			'sms_person' =>getgp('sms_person'),
			'sms_tel' =>getgp('sms_tel'),
			'sms_no' =>getgp('sms_no'),
			'sms_date' => getgp('sms_date'),
			'sms_addr' => getgp('sms_addr'),
			'sms_note' => getgp('sms_note'),
		);
		load("sms")->edit($id, $value);
		
		
	}
	// $REAUEST_URI = "?c=certificate&a=elist&is_sms=2";
		//showmsg( 'success', 'success', $REAUEST_URI );
	echo"<script>alert('SUCCESS');window.history.go(-2);</script>";