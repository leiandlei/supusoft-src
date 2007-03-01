<?php
/* 
$query=$db->query("SELECT * FROM `sp_project` where ct_code=''");
while($rt=$db->fetch_array($query)){
	$db->update("project",array("ct_code"=>"CT-".$rt[ct_id]),array("id"=>$rt[id])) && $i++;

}
echo $i."--".$j;
 */
	$s_date_arr="2014-10-29 00:00:00";
	$e_date_arr="2014-10-31 00:00:00";
	echo mkdate($s_date_arr,$e_date_arr);
	$s_date     = strtotime($s_date_arr);
    $e_date     = strtotime($e_date_arr);
    $time       = $e_date - $s_date;
	$time       = $time / (3600 * 24);
	$t=$time-(int)$time;
	if($t==0)
		$res=1;
	elseif($t<0.3)
		$res=0.5;
	elseif($t>0.3)
		$res=1;
	
	//echo (int)$time+$res;



?> 