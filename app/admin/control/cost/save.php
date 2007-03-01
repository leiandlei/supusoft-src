<?php


$id = getgp('id');
	$ct_id = getgp('ct_id');
	$eid = $db->get_var("select eid from sp_contract where ct_id='$ct_id' ");
	$iso_check = getgp('iso_check');
	$iso = implode('|', $iso_check);
	$value = array(
		'eid'		=> $eid,
		'ct_id'		=> $ct_id,
		'iso' 		=> $iso,
		'cost_type'	=> getgp('cost_type'),
		'cost'		=> getgp('cost'),
		'sftime'	=> getgp('sftime'),
		'note'		=> getgp('note'),
	);
	if($id){
		$ctc->edit($id,$value);
	}else{
		$ctc->add($value);
	}
	$REQUEST_URI='?c=cost&a=edit&ct_id='.$ct_id;
	showmsg( 'success', 'success', $REQUEST_URI );
	exit;