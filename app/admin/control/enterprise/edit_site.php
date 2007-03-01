<?php
//添加-编辑分场所 ajax
$es_id = (int)getgp( 'es_id' );
$eid = (int)getgp( 'eid' );

$step = getgp( 'step' ); //判断是否保存
$es   = load('enterprise_site');

 
if( $step ){ 
	$new_es = array(
		'eid'		=> $eid,
		'es_type'	=> getgp( 'es_type' ),
		'es_name'	=> getgp( 'es_name' ),
		'es_name_e'	=> getgp( 'es_name_e' ),
		'es_addr'	=> getgp( 'es_addr' ),
		'es_addr_e'	=> getgp( 'es_addr_e' ),
		'es_tel'	=> getgp( 'es_tel' ),
		'es_fax'	=> getgp( 'es_fax' ),
		'es_person'	=> getgp( 'es_person' ),
		'es_mobile'	=> getgp( 'es_mobile' ),
		'es_num'	=> getgp( 'es_num' ),
		'es_km'	    => getgp( 'es_km' ),
		'es_dpart'	=> getgp( 'es_dpart' ),
		'es_scope'	=> getgp( 'es_scope' ),
		'es_scope_e'=> getgp( 'es_scope_e' ),
		'es_note'	=> getgp( 'es_note' )
	);
	
	if( $es_id ){
		$bf_str = serialize($es->get(array('eid'=>$eid, 'es_id'=>$es_id)));
		$es->edit( $es_id, $new_es );
		// 日志
		do {
			log_add($eid, 0, "[说明:分场所修改]", $bf_str, serialize($es->get(array('eid'=>$eid, 'es_id'=>$es_id))));
		}while(false);
		showmsg( 'success', 'success', "?c=enterprise&a=list_site&eid={$eid}" );
	} else {
	

		$es_id = $es->add( $new_es );

		// 日志
		do {
			log_add($eid, 0, "[说明:分场所登记]", NULL, serialize($new_es));
		}while(false);
		if($es_id){ //判断是否添加成功
			print json_encode( array( 'state' => 'ok' ) );
			exit;
		} 
	}
} else {
	$row = $enterprise->get( array( 'eid' => $eid ) );
	extract( $row, EXTR_SKIP );

	if( $es_id ){
		$es_row = $db->get_row("SELECT * FROM sp_enterprises_site WHERE es_id = '$es_id'"); 
		extract( $es_row, EXTR_SKIP );
		$site_type_select = str_replace( "value=\"$es_type\">", "value=\"$es_type\" selected>", $site_type_select );
	} 

	tpl( 'enterprise/edit_site' );
}
?>