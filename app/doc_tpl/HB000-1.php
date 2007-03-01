<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
require( ROOT . '/data/cache/add_basis.cache.php' );
require( ROOT . '/data/cache/exc_basis.cache.php' );
//$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';
$ctid = (int)getgp( 'ct_id' );
$eid = (int)getgp( 'eid' );
$add_basis_check=$exc_basis_check="";
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
//增减人日
$where .= " AND ct_id = '$ctid' AND iso='A01'";
$sql = "SELECT * FROM sp_contract_item  WHERE 1 $where";
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$add_basis=unserialize($rt['add_basis']);
	foreach($add_basis_array as $k=>$item){
		if($item['is_stop']) continue;
		if(in_array($item['code'],$add_basis))
		$add_basis_str .= $item['name'];
	}
	$exc_basis=unserialize($rt['exc_basis']);
	foreach($exc_basis_array as $k=>$item){
		if($item['is_stop']) continue;
		if(in_array($item['code'],$exc_basis))
		$exc_basis_str .= $item['name'];
	}
}



//固定
$ese =$db->getAll("SELECT es_name FROM sp_enterprises_site WHERE eid = '$eid' AND es_type='1000' AND deleted = 0 ");
$escounte=count($ese);

$filename = ' 合表000-1 临时场所分布表.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/HB000-1.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{exc_clauses}', $exc_clauses, $output );
$output = str_replace( '{sum_jhd}', $sum_jhd, $output );
$output = str_replace( '{escount}', $escount, $output );
$output = str_replace( '{escounte}', $escounte, $output );
$output = str_replace( '{audit_basis}',$basis, $output );

if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath = CONF.'downs';
	//没有目录创建目录
	if(!is_dir($filePath)) {
	    mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filename) ){
		@unlink ($filePath.'/'.$filename); 
	}

	file_put_contents($filePath.'/'.$filename,$output);
	
	if( file_exists($filePath.'/'.$filename) ){
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>
