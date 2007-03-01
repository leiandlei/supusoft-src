<?php
$where='';
//搜索条件
$ep_name = trim($_GET['ep_name']);
if($ep_name){
	$where.=" AND basedata.name like '%$ep_name%'";
}
$name = trim($_GET['name']);
if($name){
 	$where.=" AND auditor.name like '%$name%'";
}
//判断显示自己的数据
//判断请假的角色默认只看到自己的
$code   = $_SESSION['userinfo']['id'];
$admin  = $_SESSION['userinfo']['username'];
$renyuanarr = array('1','92','123','95','98','99','181');//1.wuqianhui、2.liyan、3.sunpei、4.wangchenxuan、5.wangxiaolu、6.wuhaiyan、7、guanliyuan 
$isin       = in_array($code,$renyuanarr);
if($isin)
{
	$total = $db->get_var("SELECT COUNT(*) FROM sp_task_audit_team auditor  WHERE 1 $where AND auditor.data_for='6'");
	$pages = numfpage($total);
}else{
	$where.= " AND uid = '$code' ";
}

$sql=" SELECT auditor.* FROM sp_task_audit_team auditor  WHERE 1 $where AND auditor.data_for='6' ORDER BY id DESC $pages[limit]";
$query=$db->query($sql);

while($r=$db->fetch_array($query)){
	$task_list[]=$r;
}
foreach ($task_list as $key => $value) {
		$audit_ver_a =explode(";", $value['audit_ver']);
		foreach ($audit_ver_a as $val) {
			$task_list[$key]['audit_ver1'].=$audit_ver_array[$val]['msg'].";";
		}
		$task_list[$key]['audit_ver1'] = substr($task_list[$key]['audit_ver1'],0,-2);
	
		$task_list[$key]['taskBeginDate'] = $value['taskBeginDate'];
		$task_list[$key]['taskEndDate']   =  $value['taskEndDate'];
}
tpl();