<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );



$tid = (int)getgp( 'tid' );

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );


$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type="";
$zhuanjia=array();
while( $rt = $db->fetch_array( $query ) ){
    $audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
    $zhuanjia[]=$rt['zy_name'];
}
$zhuanjia=array_unique($zhuanjia);
 
//审核组信息


$leader = $auditors = array();
$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";
$query = $db->query( $sql);

while( $rt = $db->fetch_array( $query ) ){
    if( $rt['role']=="1001" ){
        $leader=$rt[name];
    } else {
        $auditors[$rt[uid]]=$rt['name'];
    } 
    
}

$filename = $ep_name .' 审核档案归档清单.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/JS-001.xml' );
header("Content-type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{leader}', $leader, $output );


echo $output;
?>