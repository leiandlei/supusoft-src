<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$prod_arr=array("","生产地址","服务地址","运营地址");

$A01=$A02=$A03="□";
$tid = (int)getgp( 'tid' );
$ct_id= (int)getgp( 'ct_id' );
$t_info=$db->get_row("SELECT eid,tb_date,te_date,approval_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check =str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);
foreach($prod_check as $k){
	$prod_check_arr[]=$prod_arr[$k];
}
$prod_check=join("/",$prod_check_arr);

$results = array();
// $sql = "SELECT p.ct_code,p.iso,t.tb_date,tat.name FROM `sp_project` p join sp_task t on p.eid=t.eid join sp_task_audit_team tat on tat.tid=t.id where p.audit_type='1002' and tat.role='1001' and p.eid=".$eid." GROUP BY p.iso";
// $query = $db->query($sql);
// while ($rt = $db->fetch_array($query)) {
// 	$key = $rt['iso'];
// 	switch ($rt['iso']) {
// 		case 'A01':
// 			$rt['iso'] = 'QMS';
// 			break;
// 		case 'A02':
// 			$rt['iso'] = 'EMS';
// 			break;
// 		case 'A03':
// 			$rt['iso'] = 'OHSMS';
// 			break;
		
// 		default:
// 			$rt['iso'] = '其他';
// 			break;
// 	}
// 	$results[$key] = $rt;
// }

$tx=$db->getAll("SELECT iso FROM `sp_project` WHERE `eid` = '$eid' AND `deleted` = '0' GROUP BY iso");
foreach ($tx as $key => $value) {
	switch ($value['iso']) {
		case 'A01':
			$iso.='QMS'.'、';
			break;
		case 'A02':
			$iso.='EMS'.'、';
			break;
		case 'A03':
			$iso.='OHSMS';
			break;
		default:
			break;
	}
}	

//组长
$leader=$db->getAll("SELECT name,taskBeginDate,taskEndDate,audit_type FROM `sp_task_audit_team`  WHERE `tid` = '$tid' and role='01'");
// echo "<pre>";
// print_r($leader);exit;
foreach ($leader as $key => $value) {
	switch ($value['audit_type']) {
		case '1002':
		case '1003':
			$cost_type = '1002,1003';
			$leader[$key]['cost_type_name'] = '初次审核';
			break;
		case "1004":
			$leader[$key]['cost_type_name'] = '监一';
			$cost_type = $value['audit_type'];
			break;
		case "1005":
			$leader[$key]['cost_type_name'] = '监二';
			$cost_type = $value['audit_type'];
			break;
		case "1007":
			$leader[$key]['cost_type_name'] = '再认证';
			$cost_type = $value['audit_type'];
			break;
		default:
			$cost_type = $value['audit_type'];
			break;
	}
	//费用
	$sql = "select cost from sp_contract_cost where ct_id=".$ct_id." and `cost_type` in(".$cost_type.")";
	$leader[$key]['cost'] = $db->get_var($sql);
}
$cti_code=$db->getAll("SELECT cti_code FROM `sp_project`  WHERE `tid` = '$tid' and deleted='0'");
foreach ($cti_code as $v) {
	$bianhao.=$v['cti_code'].";";
}
$bianhao=substr($bianhao,0,-1);


$filename = '审表005 缴纳认证费用通知单('.$ep_name.').doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-005.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);


$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{cost}', $leader[0]['cost'], $output );
$output = str_replace( '{cti_code}', $bianhao, $output );
$approval_date = str_replace('-','%s',$approval_date);
$approval_date = sprintf($approval_date,'年','月').'日';
$output = str_replace( '{approval_date}', $approval_date, $output );
$output = str_replace( '{iso}', $iso, $output );


if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath =CONF.'downs'.'/'.getgp('dates');
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
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );;
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>