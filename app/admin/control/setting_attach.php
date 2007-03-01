<?php

$iso_checkbox_array = array(
	1	=> 'Q',
	2	=> 'E',
	4	=> 'S',
	8	=> 'T'
);

$iso_checkbox = '';
foreach( $iso_checkbox_array as $int => $name ){
	$iso_checkbox .= "<label><input type=\"checkbox\" name=\"iso[]\" value=\"$int\" />$name</label> ";
}
unset( $int, $name );

 

if( empty( $a ) || 'list' == $a ) {
	$resdb = array();
	//$total = $db->get_var("SELECT COUNT(*) FROM sp_settings_attach WHERE 1" );
	//$pages = numfpage( $total, 20, "?c=setting_attach&a=list");
	$sql="SELECT * FROM sp_settings_attach WHERE 1  AND deleted=0 AND type='$_GET[type]'  ORDER BY vieworder ASC";
	$query = $db->query( $sql);// $pages[limit]
	while( $rt = $db->fetch_array( $query ) ){
		$rt['iso_checkbox'] = str_replace( "name=\"iso[]\"", "name=\"iso[{$rt['id']}][]\"", $iso_checkbox );
		foreach( $iso_checkbox_array as $val => $tmp ){
			if( $rt['iso'] & $val ){
				$rt['iso_checkbox'] = str_replace( "value=\"$val\" />", "value=\"$val\" checked/>", $rt['iso_checkbox'] );
			}
		}
		$resdb[$rt['id']] = $rt;
	}
	 

	tpl('setting/list_attach');
} elseif( 'save' == $a ) {

	$isos		= array_map( 'array_sum', getgp( 'iso' ) );
	$filenames = array_map( 'trim', getgp( 'filename' ) );
	$orders = array_map( 'intval', getgp( 'vieworder' ) );
 

	if( $filenames ){
		foreach( $filenames as $id => $filename ){
			$db->query("UPDATE sp_settings_attach SET 
						iso		= '{$isos[$id]}',
						filename = '{$filename}',
						vieworder = '{$orders[$id]}' WHERE id = '$id'");
		}
	}

	$new = getgp( 'new' );

	if( $new ){
		$ADDSQL = array();
		foreach( $new['filename'] as $key => $val ){
			if( !$val ) continue;
			$iso	= array_sum( $new['iso'] );
			$filename = $val;
			$vieworder = $new['vieworder'][$key];
			$ADDSQL[] = "( '$iso', '$filename','$vieworder','$_GET[type]' )";
		}

		if( $ADDSQL ){
			$add_sql = "INSERT INTO sp_settings_attach ( iso, filename, vieworder,type ) VALUES " . implode(',', $ADDSQL );

			$db->query( $add_sql );
		}
	}

	//更新缓存
	//update_attach();

	showmsg( 'success', 'success', "?c=$c&a=list&type=$_GET[type]" );

}elseif('del'==$a){
	
 	$db->update( 'settings_attach', array( 'deleted' => '1' ), array( 'id' => $_GET['id']) );
	 
	showmsg( 'success', 'success', "?c=$c&a=$_GET[to]&type=$_GET[type]" );

}



?>