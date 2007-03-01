<?php
set_time_limit(0);
date_default_timezone_set('PRC');
/*
 *---------------------------------------------------------------
 *获取公司名录
 *---------------------------------------------------------------
 */
$begindate = getgp('s_date');//取数开始日期
$enddate   = getgp('e_date');//取数截止日期
$arr_data_zsInfo = array();



/**===========获取企业名录表=======**/

//sql初始化
$join = $select = '';
$where = ' where 1';

//证书表
$sql    = "select %s from `sp_certificate` c";
$select.= "c.*";
$where .= " and c.`is_check`='y' and c.`status`='01' and c.deleted=0 and c.first_date >='".$begindate."' and c.first_date <='".$enddate."'";

//企业表
$select.= ",e.code,e.ep_name,e.work_code,e.ctfrom,e.ep_amount,e.capital,e.delegate,e.person,e.ep_phone,e.person_tel,e.person_email,e.cta_addr,e.cta_addrcode,e.bg_addr,e.bg_addrcode";
$join  .= " left join sp_enterprises e ON e.eid=c.eid ";
$where .= " and e.deleted = 0";

//拼装sql
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
$query = $db->query($sql);


//查出的相应字段信息插入到excel
while($row=$db->fetch_array($query)){
    $arr      = array();
    $arr[0]   = $row['code'];
    $arr[1]   = $row['ep_name'];
    $arr[2]   = $row['work_code'];
    $arr[3]   = $row['ctfrom'];
    $arr[4]   = $row['ep_amount'];
    $arr[5]   = $row['capital'];
    $arr[6]   = $row['delegate'];

    $sql      = "select t.* from sp_project p left join sp_task t on p.tid=t.id where p.cti_id=".$row['cti_id']." and p.deleted='0' and t.deleted='0' order by p.id desc";
	$latest   = $db->get_row($sql);
    $arr[7]   = $latest['tb_date']."至".$latest['te_date'];

    $sql      = "select * from sp_task_audit_team tat where tat.pid=(select max(p.id) from sp_project p where p.cti_id=".$row['cti_id']." and p.deleted='0') and tat.deleted='0'";
	$team     = $db->getALL($sql);
	$arr[9]   = '';
	foreach ($team as $value) {
		if ($value['role']=='01') {
			$arr[8] = $value['name'];
		}elseif($value['role']=='02'){
			$arr[9] .= $value['name'].";";
		}
	}
    
    $arr[10]  = $row['s_date']."至".$row['e_date'];
    $arr[11]  = $row['person'];
    $arr[12]  = $row['ep_phone'];
    $arr[13]  = $row['person_tel'];
    $arr[14]  = $row['person_email'];
    $arr[15]  = $row['cta_addr'];
    $arr[16]  = $row['cta_addrcode'];
    $arr[17]  = $row['bg_addr'];
    $arr[18]  = $row['bg_addrcode'];
    $arr_data_zsInfo[] = $arr;
}

/**===========获取企业名录表=======**/

/**=============导出xls==============**/
$tmplate = ROOT.'/app/excel/certificate.xls';
require_once ROOT.'/theme/Excel/myexcel.php';
$tmplate = CONF.'getminglu.xlsx';
$tmpName = $ep_name.getgp('s_date').'-'.getgp('e_date').'企业名录.xlsx';
$tmpPath = 'data/report_data/';
$excel   = new Myexcel($tmplate);
$excel  -> addDataFromArray($arr_data_zsInfo,3);
$excel  -> setIndex(0);
$saveName = $excel  -> saveAsFile($tmpPath,$tmpName);
echo $saveName['oldName'];
exit;
/**=============导出xls==============**/


/**===========用到的函数=============**/
function get_code($code){
	$code=str_replace(array(";",",","，"," "),"；",$code);
	$codes=explode("；",$code);
	foreach($codes as $k=>$v){
		$codes[$k]=substr($v,0,8);

	}
	$codes=array_unique($codes);
	return join("；",$codes);
}
function get_audit_type($audit_type){
	if($audit_type=='1001'||$audit_type=='1002'||$audit_type=='1003'){
		return '01';
	}else if($audit_type=='1004'||$audit_type=='1005'||$audit_type=='1006'){
		return '0301';
	}else if($audit_type=='1007'){
		return '02';
	}else if($audit_type=='1008'||$audit_type=='1009'||$audit_type=='1010'){
		return '0302';
	}else{
		return '0302';
	}
}
function get_audit_num($audit_type){
	$num=0;
	switch($audit_type){
		case '1004':$num=1;break;
		case '1005':$num=2;break;
		case '1006':$num=3;break;
		case '1011':$num=4;break;
		case '1012':$num=5;break;
		case '1013':$num=6;break;
		case '1014':$num=7;break;
		case '1015':$num=8;break;
		case '1016':$num=9;break;
		case '1017':$num=10;break;
		case '1019':$num=11;break;
		case '1020':$num=12;break;
		case '1021':$num=13;break;
		case '1022':$num=14;break;
		default :$num=0;break;	
	}
	return $num;
}
//注：还有一个类型是05变更，需要在程序里判断
function get_audit_type_auditor($audit_type){
	if($audit_type=='1002'){
		return '0101';//初审一阶段
	}else if($audit_type=='1003'){
		return '0102';
	}else if($audit_type=='1007'){
		return '0202';
	}else if($audit_type=='1004' || $audit_type=='1005'){
		return '03';
	}else{
		return '04';//变更在特殊监管  04
	}
}
/**===========用到的函数=============**/
?>
