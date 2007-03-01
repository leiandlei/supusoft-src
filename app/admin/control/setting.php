<?php
//生成的缓存类型
$cache_types = array( 'nature', 'industry', 'arctype','statecode', 'currency', 'card_type',
					'political', 'technical', 'education', 'qualification', 'skill_source',
					'job_type', 'department', 'attachtype', 'audit_role', 'iso', 'audit_ver',
					'audit_type', 'risk_level', 'mark', 'certstate', 'certpasue', 'certrecall',
					'union_type', 'site_type', 'cost_type','ep_type','ep_level',
					'region','ct_type','choose_type','insurance','certreplace','certchange',
					'audit_job',//是否专职
					'evaluation_methods',//评价方式
					'post',//评价方式
					'business',//业务设置
					'functions',//职能设置
					'employment_methods',//用工方式
					'employment_nature',//聘用性质
					'area',// 区域设置
					'send_company', //快递公司
					'class', //课程设置
					'question', //课程设置
					'auditor_arctype',//审核员文档类型
					'audit_person',
					'add_basis',
					'exc_basis',
					'pasuequs',
					'recalqus',
 					 );

if( $a=='index'){
	$nav_title='系统设置';
	tpl('setting/index');
} elseif( 'list' == $a ) {
	$type = trim( getgp( 'type' ) );

	$resdb = array();

	 $query = $db->query( "SELECT * FROM sp_settings WHERE type = '$type' AND deleted=0 ORDER BY vieworder ASC,code ASC" );
			while( $rt = $db->fetch_array( $query ) ){
				$resdb[$rt['id']] = $rt;
			}
			
 	tpl('setting/list');

} elseif( 'list_kh' == $a ) {
	$join =  $select = '';$where =' where 1';
	$sql    = 'select %s from `sp_examine`';
	$type = trim( getgp( 'type' ) );     
	$seach = getSeach();
	foreach ($seach as $key => $value)
	{
		switch ($key)
		{
			default:
				$str = " and `%s` like '%%%s%%'";
				break;
		}
		$where .= sprintf($str,$key,$value);
	}
	$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where.$pages['limit'];

    $results = $db->getAll($sql);
        
    // $array_tite = array();
    // $sql    = "select name,types from sp_examine";
    // $array_tite = $db->getAll($sql);
//     	echo "<pre />";
// print_r($results);exit;
 	tpl('setting/list_kh');

}elseif( 'save' == $a ) {
	$source = trim( getgp( 'source' ) );
	$type = trim( getgp( 'type' ) );
	$codes = array_map( 'trim', getgp( 'code' ) );
	$names = array_map( 'trim', getgp( 'name' ) );

	$order = array_map( 'intval', getgp( 'vieworder' ) );
	$is_stops = array_map( 'intval', getgp( 'is_stop' ) );

	$old_settings = array();
	$query = $db->query("SELECT * FROM sp_settings WHERE type = '$type'");
	while( $rt = $db->fetch_array( $query ) ){
		$olds[$rt['id']] = $rt;
	}

	if( $names ){
		foreach( $names as $id => $name ){

			if( $name != $olds[$id]['name'] || $codes[$id] !== $olds[$id]['code'] || $is_stops[$id] != $olds[$id]['is_stop'] || $order[$id] != $olds[$id]['vieworder'] ){
				$sql="UPDATE sp_settings SET code = '{$codes[$id]}', type = '$type',
					name = '{$names[$id]}',
					is_stop = '{$is_stops[$id]}',
					vieworder = '{$order[$id]}' WHERE id = '$id'";

$db->query($sql);
			}
		}
	}

	$new = getgp( 'new' );

	if( $new ){
		$ADDSQL = array();
		foreach( $new['name'] as $key => $val ){
			if( !$val ) continue;
			$name = $val;
			$code = $new['code'][$key];
			$is_stop = (int)$new['is_stop'][$key];
			$vieworder = $new['vieworder'][$key];
			$ADDSQL[] = "('$type', '$code','$name','$vieworder','$is_stop')";
		}

		if( $ADDSQL ){
			$add_sql = "INSERT INTO sp_settings ( type, code, name, vieworder, is_stop ) VALUES " . implode(',', $ADDSQL );
			$db->query( $add_sql );
		}
	}

	update_cache( $type );
	showmsg( 'success', 'success', "?c=$c&a=$source&type=$type" );
} elseif( 'jumpset' == $a ){
	$jump = getgp( 'jump' );
	set_option( 'jumptype', $jump );
	showmsg( 'success', 'success', "?c=diy_menu" );
}elseif('del'==$a){
  	$db->update( 'settings', array( 'deleted' => '1' ), array( 'id' => $_GET['id']) );
	update_cache(  $_GET['type']);
	showmsg( 'success', 'success', "?c=$c&a=$_GET[to]&type=$_GET[type]" );
}elseif('cache_all' == $a){ //生成所有缓存
	//生成setting缓存
	foreach($cache_types as $v){
		update_cache($v);
	}
	update_ver_cache(); //生成版本：标准
	update_ctfrom(); //合同来源
	update_region(); //行政区域
	showmsg( 'success', 'success', "?c=setting" );
}elseif('save_sh' == $a)
{ 
	$source = trim( getgp( 'source' ) );
	$type = trim( getgp( 'type' ) );
// 	echo "<pre />";
// print_r($source);exit;
    $order = array_map( 'intval', getgp( 'vieworder' ) );
	$names = array_map( 'trim', getgp( 'name' ) );

	$codes = array_map( 'trim', getgp( 'day' ) );
	$is_stops = array_map( 'intval', getgp( 'is_stop' ) );
    $types = array_map( 'intval', getgp( 'types_s' ) );
    $fanweis = array_map( 'intval', getgp( 'fanwei_s' ) );
    $operations = array_map( 'intval', getgp( 'operation_s' ) );
      
	$old_settings = array();
	$query = $db->query("SELECT * FROM sp_examine ");
		while( $rt = $db->fetch_array( $query ) )
		{

			$olds[$rt['id']] = $rt;
		}
    
		if( $names )
		{
			foreach( $names as $id => $name )
			{		
				if( $name != $olds[$id]['name'] ||  $codes[$id] !== $olds[$id]['day'] || $fanweis[$id] !== $olds[$id]['fanwei_s'] || $operations[$id] !== $olds[$id]['operation_s'] || $types[$id] !== $olds[$id]['types_s'] || $is_stops[$id] != $olds[$id]['is_stop'] || $order[$id] != $olds[$id]['vieworder'] ){
					$sql="UPDATE sp_examine SET 
					name = '{$names[$id]}',
					day = '{$codes[$id]}',
					fanwei = '{$fanweis[$id]}',
					operation = '{$operations[$id]}',
					types = '{$types[$id]}',
					is_stop = '{$is_stops[$id]}'
					WHERE id = '$id'";

					$db->query($sql);
				}
			}
		}
		$new = getgp( 'new' );
		if( $new ){
			$ADDSQL = array();
			foreach( $new['name'] as $key => $val ){
				if( !$val ) continue;
				$name       = $val;
				$code       = $new['day'][$key];
				$types      = (int)$new['types_s'][$key];
				$fanweis    = (int)$new['fanwei_s'][$key];
				$operations = (int)$new['operation_s'][$key];
				$is_stop    = (int)$new['is_stop'][$key];
                // echo "<pre />";
                // print_r($types);exit;
				$vieworder  = $new['vieworder'][$key];
				$ADDSQL[]   = "( '$vieworder','$name','$types','$code','$fanweis','$operations','$is_stop')";
			}
			if( $ADDSQL ){
				$add_sql = "INSERT INTO sp_examine ( vieworder,name,types,day,fanwei,operation,is_stop ) VALUES " . implode(',', $ADDSQL );

				$db->query( $add_sql );
			}			
		}
		showmsg( 'success', 'success', "?c=$c&a=$source" );
}