<?php
require_once( ROOT . '/data/cache/risk_level.cache.php' );
require_once ROOT . '/data/cache/mark.cache.php';
require_once ROOT . '/data/cache/iso.cache.php';
$mark_checkbox = '';
if( $mark_array ){
	$mark_checkbox .= "<ul>";
	foreach( $mark_array as $code => $item ){
 		if($item['is_stop']=='0'){
		$mark_checkbox .= "<li><label><input type=\"checkbox\" name=\"mark[]\" value=\"$code\" /> {$item[name]}</label></li>";
		}
	}
	$mark_checkbox .= "</ul>";
	unset( $code, $item );
}

 

//风险等级
$risk_level_select = '';
if( $risk_level_array ){
	foreach( $risk_level_array as $item ){
		$risk_level_select .= "<option value=\"$item[code]\">$item[code]|$item[name]</option>";
	}
}

//三级小类分组
$fenzu_select = '';
$fenzu_array = array(
	'1'=>"E00-1",
	'2'=>"E00-2",
	'3'=>"E00-3",
	'4'=>"S00-1",
	'5'=>"S00-2",
	'6'=>"S00-3",
	'7'=>"S00-4"
	);
if( $fenzu_array ){
	foreach( $fenzu_array as $key => $item ){
		$fenzu_select .= "<option value=\"$key\">$item</option>";
	}
}

if( empty( $a ) || 'list' == $a ) {
	$iso_select=f_select('iso');//认证体系
	$use_code	= getgp( 'use_code' );
	$audit_code	= getgp( 'audit_code' );
	$desc		= getgp( 'desc' );
	$hy			= getgp( 'hy' );
	$banben	    = getgp( 'banben' );
	$iso	    = getgp( 'iso' );

	
	$where = '';
	if( $use_code )
		$where .= " AND code like '%$use_code%'";
	if( $audit_code )
		$where .= " AND shangbao like '%$audit_code%'";
	if( $desc )
		$where .= " AND msg = '$desc'";
	if( $hy )
		$where .= " AND industry LIKE '%$hy%'";
	if( $banben )
		$where .= " AND banben = '$banben'";
	if( $iso ){
		$where .= " AND iso = '$iso'";
		$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
	}
	$resdb = array();
	$total = $db->get_var("SELECT COUNT(*) FROM sp_settings_audit_code WHERE 1  AND deleted=0  $where" );
	$pages = numfpage( $total, 20, "?c=setting_audit_code&a=list");
	$query = $db->query( "SELECT * FROM sp_settings_audit_code WHERE 1 AND deleted=0 $where ORDER BY vieworder ASC,code ASC $pages[limit]" );
	while( $rt = $db->fetch_array( $query ) ){
		$marks = explode( ',', $rt['mark'] );
		$rt['mark_checkbox'] = $mark_checkbox;
		foreach( $marks as $mk ){
			$rt['mark_checkbox'] = str_replace("value=\"$mk\"","value=\"$mk\" checked", $rt['mark_checkbox'] );
			$rt['mark_checkbox'] =str_replace("[]","[$rt[id]][]",$rt['mark_checkbox']);
		}
		$rt['risk_level_select'] = str_replace( "value=\"{$rt['risk_level']}\">", "value=\"{$rt['risk_level']}\" selected>", $risk_level_select );
		$rt['fenzu_select'] = str_replace( "value=\"{$rt['fenzu']}\">", "value=\"{$rt['fenzu']}\" selected>", $fenzu_select );
		$resdb[$rt['id']] = $rt;
	}

	tpl('setting/list_audit_code');
} elseif( 'save' == $a ) {

	$codes		= array_map( 'trim', getgp( 'code' ) );
	$isos		= array_map( 'trim', getgp( 'iso' ) );
	$shangbaos	= array_map( 'trim', getgp( 'shangbao' ) );
	$msgs		= array_map( 'trim', getgp( 'msg' ) );
	$marks		= getgp( 'mark' );
	$industrys	= getgp( 'industry' );
	$risk_levels= getgp( 'risk_level' );
	$order		= array_map( 'intval', getgp( 'vieworder' ) );
	$is_stops	= array_map( 'intval', getgp( 'is_stop' ) );
//	$fenzu		= array_map( 'trim', getgp( 'fenzu' ) );
	$banben		= array_map( 'trim', getgp( 'banben' ) );
	if( $codes ){
		foreach( $codes as $id => $code ){
			$db->query("UPDATE sp_settings_audit_code SET 
						code	   = '{$code}',
						iso		   = '{$isos[$id]}',
						shangbao   = '{$shangbaos[$id]}',
						msg	  	   = '{$msgs[$id]}',
						industry   = '{$industrys[$id]}',
						risk_level = '{$risk_levels[$id]}',
						mark	   = '".implode(',',$marks[$id])."',
						is_stop    = '{$is_stops[$id]}',
						vieworder  = '{$order[$id]}',
						banben	   = '{$banben[$id]}' WHERE id = '{$id}'");
		}
	} 
	$new = getgp( 'new' ); 

	if( $new ){
		$ADDSQL = array();
		foreach( $new['code'] as $key => $code ){
			if( !$code ) continue;
			$code		= $code;
			$iso		= $new['iso'][$key];
			$shangbao	= $new['shangbao'][$key];
			$msg		= $new['msg'][$key];
			$mark		= $new['mark'][$key];
			$industry	= $new['industry'][$key];
			$is_stop	= (int)$new['is_stop'][$key];
			$vieworder	= $new['vieworder'][$key];
			$risk_level	= $new['risk_level'][$key];
			$banben	    = $new['banben'][$key];
			$ADDSQL[]	= "( '$iso', '$code', '$shangbao', '$msg', '$mark','$industry','$risk_level','$banben','$vieworder', '$is_stop' )";
		} 
		if( $ADDSQL ){
			$add_sql = "INSERT INTO sp_settings_audit_code ( iso,code, shangbao, msg, mark, industry, risk_level, banben, vieworder, is_stop ) VALUES " . implode(',', $ADDSQL );
 			$db->query( $add_sql );
		}
	} 
	showmsg( 'success', 'success', "?c=$c&a=list&paged=$paged" );

}elseif('del'==$a){
  	$db->update( 'settings_audit_code', array( 'deleted' => '1' ), array( 'id' => $_GET['id']) );
 	showmsg( 'success', 'success', "?c=$c&a=$_GET[to]" );
} 