<?php
require_once( ROOT . '/data/cache/ctfrom.cache.php' );

$join   = $select = '';$where = " where 1 ";
$sql    = "select %s from sp_hr_qualification hrq";
$select.= "hrq.id,hrq.uid,hrq.iso,hrq.qua_type,hrq.s_date,hrq.e_date,hrq.zige_update,hrq.peixun_keshi,hrq.shenhe_num";
$where .= " and hrq.qua_type!='03' and hrq.qua_type!='04' and hrq.status=1 and hrq.deleted=0 and hrq.`e_date`>'".date('Y-m-d')."'";

$join   = " left join sp_hr hr on hrq.uid=hr.id";
$select.= ",hr.name,hr.code,hr.tel,hr.audit_job,hr.ctfrom";
$where .= " and hr.deleted=0";

/**条件搜索**/
$seach = getSeach();
if( !empty($seach) ){
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 'name':
			case 'code':
			case 'easycode':
				$str = " and hr.`%s` like '%%%s%%'";
				break;
			default:
				break;
		}
		$where .= sprintf($str,$key,$value);
	}
	extract($seach,EXTR_OVERWRITE);
}

$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
$results = $db->getAll($sql);
foreach( $results as $key=>$item )
{
	$sql = 'select count(*) as count from(select count(*) from sp_task_audit_team where deleted=0 and audit_type!=\'1002\' and audit_type=\'1003\' and iso=\''.$item['iso'].'\' and qua_type in(\'01\',\'02\') and uid='.$item['uid'].' group by tid) as t1';
	$results[$key]['shenhe_1003'] = $db -> get_var($sql);
	$results[$key]['shenhe_1003'] = $results[$key]['shenhe_1003']?$results[$key]['shenhe_1003']:0;
	
	$sql = 'select count(*) as count from(select count(*) from sp_task_audit_team where deleted=0 and audit_type!=\'1002\' and audit_type=\'1007\' and iso=\''.$item['iso'].'\' and qua_type in(\'01\',\'02\') and uid='.$item['uid'].' group by tid) as t1';
	$results[$key]['shenhe_1007'] = $db -> get_var($sql);
	$results[$key]['shenhe_1007'] = $results[$key]['shenhe_1007']?$results[$key]['shenhe_1007']:0;
	
	$sql = 'select count(*) as count from(select count(*) from sp_task_audit_team where deleted=0 and audit_type!=\'1002\' and iso=\''.$item['iso'].'\' and qua_type in(\'01\',\'02\') and uid='.$item['uid'].' group by tid) as t1';
	$results[$key]['shenhe_num']  = $db -> get_var($sql);
	$results[$key]['shenhe_num']  = $results[$key]['shenhe_num']?$results[$key]['shenhe_num']:0;
}
$r_yes   = array();
$r_no    = array();

foreach ($results as $key => $value) {
	$year=getYear($value['s_date'],$value['e_date']);
	//$results[$key]['year']=$year;
	$zige = "";
	foreach ($year as $k => $v) {
		$monthNum = getMonthNum($v,date('Y-m-d'));

		if(($monthNum > 3) )continue;//如果不是今年直接跳过
		
		$monthNum = getMonthNum($v,$results[$key]['zige_update']);

		if( $monthNum <= 3 )$zige=101;//已年度确认
		else $zige=102;//未年度确认

		$monthNum = getMonthNum($value['e_date'],date('Y-m-d'));
		
		if( $monthNum < 3 ){
			$monthNum = getMonthNum($results[$key]['zige_update'],$v);
			if( $monthNum <= 3 )$zige=202;//已再认证
			else $zige=201;//未再认证
		}
		
	}

	if( $zige != "" ){
		$value['zige'] = $zige;
		if( !in_array($zige,array(101,201)) ){
			$r_no[]=$value;
		}else{
			$r_yes[] = $value;
		}
	}else{
		$value['zige'] = 301;
		$r_yes[] = $value;
	}
	
}

$status = (int)getgp('status');
if( empty($status) || $status==1 ){
	$status_1_tab = 'ui-tabs-active ui-state-active';
	$results = $r_no;
}else{
	$status_2_tab = 'ui-tabs-active ui-state-active';
	$results = $r_yes;
}

tpl('hr/zige_status_list');

function getMonthNum( $date1, $date2, $tags='-' ){
	$date1 = explode($tags,$date1);
	$date2 = explode($tags,$date2);
	return ($date1[0] - $date2[0]) * 12 + ($date1[1] - $date2[1]);
}
function getYear($date1,$date2){
	$date_1 = substr($date1,0,4);
	$date_2 = substr($date2,0,4);
	$num   = abs($date_1-$date_2);
	
	$results[0] = $date1;
	for ($i=1; $i < $num; $i++) { 
		$results[$i] = $date_1+$i.substr($date1,4);
	}
	$results[] = $date2;
	return $results;
}