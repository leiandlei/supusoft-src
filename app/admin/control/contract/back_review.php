<?php

$ct_id = (int)getgp( 'ct_id' );
$message = getgp( 'message' );
$ct = load( 'contract' );
//$db->update( 'contract', array( 'status' => 1 ), array( 'ct_id' => $ct_id ) );
$ct->edit( $ct_id, array( 'status' => 1, 'back_date' => current_time( 'mysql' ) ) );
exit( json_encode( array( 'status' => 'ok' ) ) );