<?php
$iso_select = f_select('iso');//体系
if( empty( $a ) || 'audit_ver' == $a ) {
	$resdb = array();
	$query = $db->query( "SELECT * FROM sp_settings_audit_vers WHERE deleted=0  ORDER BY vieworder ASC" );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['iso_select'] = str_replace( "value=\"$rt[iso]\">", "value=\"$rt[iso]\" selected>" , $iso_select );
		$resdb[$rt['vid']] = $rt;
	}
	update_ver_cache();
	tpl('setting/audit_ver');

} elseif( 'save' == $a ) {
	$isos			= array_map( 'trim', getgp( 'iso' ) );
	$vers			= array_map( 'trim', getgp( 'ver' ) );
	$msgs			= array_map( 'trim', getgp( 'msg' ) );
	$audit_basiss	= array_map( 'trim', getgp( 'audit_basis' ) );
	$notes			= array_map( 'trim', getgp( 'note' ) );
	$order			= array_map( 'intval', getgp( 'vieworder' ) );
	$is_stops		= array_map( 'intval', getgp( 'is_stop' ) );
	$is_shangbaos	= array_map( 'intval', getgp( 'is_shangbao' ) );

	$old_settings = array();
	$query = $db->query("SELECT * FROM sp_settings_audit_vers");
	while( $rt = $db->fetch_array( $query ) ){
		$olds[$rt['vid']] = $rt;
	}

	if( $vers ){
		foreach( $vers as $id => $ver ){
			if( $ver				!= $olds[$id]['audit_ver'] || 
				$isos[$id]			!= $olds[$id]['iso'] || 
				$isos[$id]			!= $olds[$id]['msg'] || 
				$audit_basiss[$id]	!= $olds[$id]['audit_basis'] ||
				$notes[$id]			!= $olds[$id]['note'] ||
				$is_stops[$id]		!= $olds[$id]['is_stop'] || 
				$is_shangbaos[$id]	!= $olds[$id]['is_shangbao'] || 
				$order[$id]			!= $olds[$id]['vieworder'] ){
				$db->query("UPDATE sp_settings_audit_vers SET 
					iso			= '{$isos[$id]}', 
					audit_ver	= '$ver',
					msg			= '{$msgs[$id]}',
					audit_basis = '{$audit_basiss[$id]}',
					note		= '{$notes[$id]}',
					is_stop		= '{$is_stops[$id]}',
					is_shangbao	= '{$is_shangbaos[$id]}',
					vieworder	= '{$order[$id]}' WHERE vid = '$id'");
			}
			
		}
	}

	$new = getgp( 'new' );

	if( $new ){
		$ADDSQL = array();
		foreach( $new['ver'] as $key => $val ){
			if( !$val ) continue;
			$ver			= $val;
			$iso			= $new['iso'][$key];
			$msg			= $new['msg'][$key];
			$audit_basis	= $new['audit_basis'][$key];
			$note			= $new['note'][$key];
			$is_stop		= (int)$new['is_stop'][$key];
			$is_shangbao	= (int)$new['is_shangbao'][$key];
			$vieworder		= $new['vieworder'][$key];
			$ADDSQL[]		= "( '$iso', '$ver', '$msg', '$audit_basis', '$note', '$vieworder', '$is_stop', '$is_shangbao' )";
		}

		if( $ADDSQL ){
			$add_sql = "INSERT INTO sp_settings_audit_vers ( iso, audit_ver, msg, audit_basis, note, vieworder, is_stop, is_shangbao ) VALUES " . implode(',', $ADDSQL );

			$db->query( $add_sql );
		}
	}
	showmsg('success', 'success', "?c=setting_audit_ver&a=audit_ver");
}elseif('del'==$a){
  	$db->update( 'settings_audit_vers', array( 'deleted' => '1' ), array( 'vid' => $_GET['id']) );
 	showmsg( 'success', 'success', "?c=$c&a=$_GET[to]" );
}
?>