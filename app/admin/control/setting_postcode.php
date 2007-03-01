<?php


if( empty( $a ) || 'list' == $a ) {
	$where='';
	$code=$_GET['code']; //编码
	$province=$_GET['province'];
	$city=$_GET['city'];
	$area=$_GET['area'];
	$is_stop=$_GET['is_stop'];
	if($_GET['province']){
		$where.=" AND province like '%$province%'";
 	};
	if($city){
		$where.=" AND city like '%$_GET[city]%'";
 	}
	if($area){
		$where.=" AND area like '%$_GET[area]%'";
 	}
	if($is_stop){
		$where.=" AND  is_stop='$is_stop'";
 	} 
	 
	$resdb = array();
	$total = $db->get_var("SELECT COUNT(*) FROM sp_settings_postcode WHERE 1  $where AND deleted=0 " );
	$pages = numfpage( $total, 20, "?c=setting_postcode&a=list");
	$query = $db->query( "SELECT * FROM sp_settings_postcode WHERE 1 $where  AND deleted=0  ORDER BY vieworder ASC,code ASC $pages[limit]" );
	while( $rt = $db->fetch_array( $query ) ){
		$resdb[$rt['id']] = $rt;
	}
//echo "SELECT * FROM sp_settings_postcode WHERE 1 ORDER BY vieworder ASC,code ASC $pages[limit]";
	tpl('setting/list_postcode');
} elseif( 'save' == $a ) {
	$codes		= array_map( 'trim', getgp( 'code' ) );
	$provinces	= array_map( 'trim', getgp( 'province' ) );
	$citys		= array_map( 'trim', getgp( 'city' ) );
	$areas		= array_map( 'trim', getgp( 'area' ) );
	$order		= array_map( 'intval', getgp( 'vieworder' ) );
	$is_stops	= array_map( 'intval', getgp( 'is_stop' ) );

	if( $codes ){
		foreach( $codes as $id => $code ){
			$db->query("UPDATE sp_settings_postcode SET 
			
						code	= '{$code}',
						province= '{$provinces[$id]}',
						city	= '{$citys[$id]}',
						area	= '{$areas[$id]}',
						is_stop = '{$is_stops[$id]}',
						vieworder = '{$order[$id]}' WHERE id = '{$id}'");
		}
	}

	$new = getgp( 'new' );
	 
	if( $new ){
		$ADDSQL = array();
		foreach( $new['code'] as $key => $val ){
			if( !$val ) continue; 
			$code = $new['code'][$key];
			$province = $new['province'][$key];
			$city = $new['city'][$key];
			$area = $new['area'][$key];  
			$is_stop = (int)$new['is_stop'][$key];
			$vieworder = $new['vieworder'][$key];
			$ADDSQL[] = "( '$code','$province','$city','$area','$vieworder','$is_stop')";
		}

		if( $ADDSQL ){
			 $add_sql = "INSERT INTO sp_settings_postcode ( code, province, city, area, vieworder, is_stop ) VALUES " . implode(',', $ADDSQL );

			 $db->query( $add_sql );
		}
	} 
	 showmsg( 'success', 'success', "?c=$c&a=list&paged=$paged" );

}elseif('del'==$a){ 
 	$db->update( 'settings_postcode', array( 'deleted' => '1' ), array( 'id' => $_GET['id']) ); 
	showmsg( 'success', 'success', "?c=$c&a=$_GET[to]" );

}



?>