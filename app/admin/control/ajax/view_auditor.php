 <?php

/*
*选择派人：弹窗
*/

$uid=getgp("uid");
$name=$db->get_var("SELECT name FROM `sp_hr` WHERE `id` = '$uid'");
$u_arr=array();
$n_year = date("Y");
$date=date("Y-m-d");
$query=$db->query("SELECT * FROM `sp_hr_qualification` WHERE `uid` = '$uid' AND `status` = '1' order by iso");

$iso="";
$audit_type = array('1001', '1002','1007','1008','1009','1010','2001','2002','2003','3001','1005');

while($r=$db->fetch_array($query)){
	if(strtotime(thedate_add($r[s_date],2,"year"))<strtotime($date)){
		$date_b=thedate_add($r[s_date],2,"year");
		$date_e=$r[e_date]." 00:00:00";
	}elseif(strtotime(thedate_add($r[s_date],1,"year"))<strtotime($date)){
		$date_b=thedate_add($r[s_date],1,"year");
		$date_e=thedate_add($r[s_date],2,"year");
	
	}else{
		$date_b=$r[s_date]." 00:00:00";
		$date_e=thedate_add($r[s_date],1,"year");
	
	}
	$u_arr[$r[iso]][e_date]=$r[e_date];
	$u_arr[$r[iso]]['type_stage']=$db->get_var("select COUNT(*) from sp_task_audit_team where uid = {$uid} and audit_type IN ('1003','1007') and taskEndDate >= '{$date_b}' and taskEndDate <= '{$date_e}' and iso='$r[iso]' and deleted=0");
	$u_arr[$r[iso]]['type_supervise'] = $db->get_var("select COUNT(*) from sp_task_audit_team where uid = {$uid} and audit_type NOT IN ('".join("','",$allow_type)."') and taskEndDate >= '{$date_b}' and taskEndDate <= '{$date_e}' and iso='$r[iso]' and deleted=0");
	$u_arr[$r[iso]]['witness'] = $db->get_var("select COUNT(*) from sp_task_audit_team where uid = {$uid} and witness=2 and taskEndDate >= '{$date_b}' and taskEndDate <= '{$date_e}' and iso='$r[iso]' and deleted=0");

}
echo " 人员:".$name;
echo "<table class='grid-table'><tr><td>体系资格</td><td>到期时间</td><td>完整审核</td><td>监督审核</td><td>内部见证</td></tr>";
foreach($u_arr as $iso=>$val){
echo "<tr><td>".f_iso($iso);
echo "</td><td>".$val['e_date'];
echo "</td><td>".$val['type_stage'];
echo "</td><td>".$val['type_supervise'];
echo "</td><td>".$val['witness'];
echo "</td></tr>";
}
echo "</table>";
