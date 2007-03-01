<?php
$where = $join = '';

$date_input = getgp('date_input')?getgp('date_input'):date('Y-m',strtotime("-1 month"));

$select = 'ssd.id,ssd.sid,ssd.name,ssd.teamLeater,ssd.leaterType,ssd.zhuanyeType,ssd.witness,ssd.auditMoney,ssd.typeMoney,ssd.totalMoney,ssd.days,ssd.status';

$join   .= 'left join sp_shytj ss on ssd.sid=ss.id ';
$select .= ',ss.type,ss.isGoTo,ss.startTime,ss.endTime,ss.totalDay';

$join   .= 'left join sp_enterprises se on ss.eid=se.eid ';//企业表
$select .= ',se.ep_name';

$join   .= 'left join sp_contract ct on ss.ct_id=ct.ct_id ';//合同表
$select .= ',ct.ct_code';

$join   .= 'left join sp_contract_item cti on ss.cti_id=cti.cti_id ';//合同项目表
$select .= ',cti.cti_code';

$join   .= 'left join sp_task t on ss.tid=t.id ' ;//审核计划
$select .= '';

$join   .= 'left join sp_project p on ss.pid=p.id ';//审核项目
$select .= '';

$where .= ' and ssd.is_todo=1 and ssd.del=1 and ss.del=1';
$where .= ' and ssd.uid='.$_SESSION['userinfo']['id'];
$where .= ' and (MONTH(ss.startTime)='.substr($date_input,5).' or MONTH(ss.endTime)='.substr($date_input,5).') and (YEAR(ss.startTime)='.substr($date_input,0,4).' OR YEAR(ss.endTime)='.substr($date_input,0,4).')';
$sql = 'select '.$select.',ssd.is_todo from sp_shytj_detail ssd '.$join.'where 1'.$where;

$results = $db->getAll($sql);
foreach ($results as $key => $detail) {
	if( substr($detail['startTime'],5,2) != substr($detail['endTime'],5,2) ){
		$startDate = date('Y-m-01', strtotime(date($date_input.'-01')));
	   	$endDate   = date('Y-m-d', strtotime("$startDate +1 month -1 day"));
		//月底在本月
		if(  substr($detail['startTime'],5,2) == substr($date_input,5,2) ){
			$startDay = substr($detail['startTime'],8,2);
			$endDay   = substr($endDate,8,2);
		}

		//月初在本月
		if(  substr($detail['endTime'],5,2) == substr($date_input,5,2) ){
			$startDay = substr($startDate,8,2);
			$endDay   = substr($detail['endTime'],8,2);
		}
	}else{
		$startDay = substr($detail['startTime'],8,2);
		$endDay   = substr($detail['endTime'],8,2);
	}

	$results[$key]['dateArray'] = range($startDay,$endDay);
}

$list = array();
foreach ($results as $key => $detail ) {
	if( array_key_exists($detail['name'],$list) ){
		$list[$detail['name']]['dateArray']  = array_merge($list[$detail['name']]['dateArray'],$detail['dateArray']); 
		$list[$detail['name']]['totalMoney'] = $list[$detail['name']]['totalMoney']+$detail['totalMoney'];
	}else{
		$list[$detail['name']] = $detail;
	}
	$list[$detail['name']]['dateArray'] = array_unique($list[$detail['name']]['dateArray']);
	$list[$detail['name']]['dateArrayCount'] = count($list[$detail['name']]['dateArray']);
}

$results = array();
foreach ($list as $key => $detail) {
	if( $detail['dateArrayCount'] >= 15 && $detail['dateArrayCount'] < 20 ){
		$detail['butie'] = 200;
	}else if($detail['dateArrayCount'] >= 20){
		$detail['butie'] = 1200;
	}else{
		$detail['butie'] = 0;
	}
	sort($detail['dateArray']);
	$results[] = $detail;
}
tpl('finance/myList');
?>
