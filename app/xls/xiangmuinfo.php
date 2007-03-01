<?php
require_once ROOT.'/data/arr_config.php';
require_once ROOT.'/data/cache/ctfrom.cache.php';
/**===========证书=======**/
//sql初始化
$join = $select = '';
$where = ' where 1';

//证书表
$sql    = "select %s from `sp_certificate` c";
$select.= "c.*";
$where .= " and c.`status`='01' and c.deleted=0 and c.first_date>='2017-01-01'";

//审核项目表
$join  .= " left join sp_project p ON c.cti_id=p.cti_id";
$where .= " and p.deleted = '0'";

//审核项目表
$select.= ",tat.name,tat.taskBeginDate,tat.taskEndDate";
$join  .= " left join sp_task_audit_team tat ON p.id=tat.pid";
$where .= " and tat.deleted = '0' and tat.role = '01'";

//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
$query = $db->query( $sql );
$a = 0;
while( $row = $db->fetch_array( $query ) ){
	// echo "<pre />";
	// print_r($row);exit;
	$a++;
	$arr    = array();
	$arr[0] = $a;
	$arr[1] = $row['cert_name'];
	$arr[2] = $row['ep_addr'];
	$arr[3] = $row['cert_scope'];
	$arr[4] = $row['name'];
	$arr[5] = $arr_audit_iso[$row['iso']];
	$arr[6] = $row['taskBeginDate']."至".$row['taskEndDate'];
	$arr[7] = $ctfrom_array[$row['ctfrom']]['name'];
	$arr_data_zsInfo[] = $arr;
}
/**===========证书=======**/

/**=============导出xls==============**/
require_once ROOT.'/theme/Excel/myexcel.php';
$tmplate = ROOT.'/app/excel/xiangmuinfo.xls';
$tmpName = '20177777.xls';
$tmpPath = 'data/report_data/';
$excel   = new Myexcel($tmplate);
// $excel  -> addDataFromArray($arr);
$excel  -> setIndex(0);
$excel  -> addDataFromArray($arr_data_zsInfo,2);

$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
echo $saveName['oldName'];
exit;
/**=============导出xls==============**/
?>
