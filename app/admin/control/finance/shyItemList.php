<?php
$join = $select = '';$where = ' where 1';
if( getgp('name') ){
	$where = ' and ssd.name like \'%'.getgp('name').'%\'';
}

//审核员统计详情
$sql     = "select %s from `sp_shytj_detail` ssd";
$where  .= ' and ssd.leaterType!=3 and ssd.del=1';
$select .= "ssd.id,ssd.sid,ssd.name,ssd.teamLeater,ssd.leaterType,ssd.zhuanyeType,ssd.witness,ssd.auditMoney,ssd.typeMoney,ssd.totalMoney,ssd.days,ssd.status";

//审核员统计
$join   .= " left join sp_shytj ss on ssd.sid=ss.id";
$where  .= ' and ss.`del`=1';
$select .= ",ss.type,ss.isGoTo,ss.startTime,ss.endTime";

//企业表
$join   .= ' left join sp_enterprises se on ss.eid=se.eid';
$where  .= ' and se.`deleted`=0';
$select .= ',se.ep_name,se.eid';

//合同表
$join   .= ' left join sp_contract ct on ss.ct_id=ct.ct_id';
$where  .= ' and ct.`deleted`=0';
$select .= ',ct.ct_code';

//合同项目表
$join   .= ' left join sp_contract_item cti on ss.cti_id=cti.cti_id';
$where  .= ' and cti.`deleted`=0';
$select .= ',cti.cti_code';

//审核计划
$join   .= ' left join sp_task t on ss.tid=t.id' ;
$where  .= ' and t.`deleted`=0';
$select .= '';

//审核项目
$join   .= ' left join sp_project p on ss.pid=p.id ';
$where  .= ' and p.`deleted`=0';
$select .= '';

/**条件搜索**/
$seach = getSeach();
if( !empty($seach) ){
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 'name'://审核员姓名
				$str = " and ssd.`%s` like '%%%s%%'";
				break;
			case 'ep_name'://企业名
				$str = " and se.`%s` like '%%%s%%'";
				break;
			case 'startTime'://开始时间
				$str = " and ss.`%s`>='%s'";
				break;
			case 'endTime'://结束时间
				$str = " and ss.`%s`<='%s'";
				break;
			default:
				break;
		}
		$where .= sprintf($str,$key,$value);
	}
	extract($seach,EXTR_OVERWRITE);
}

$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql = $sql.$pages['limit'];
}
/**分页**/

$r = $db->getAll($sql);

$results = array();
foreach ($r as $v) {
	if($v['teamLeater']!=1){
		if($v['typeMoney']!='0.00'){
			$v['typeMoney']='0.00';
		}
	}
	if( !array_key_exists($v['name'], $results[$v['eid']][$v['type']]) )$results[$v['eid']][$v['type']][$v['name']]=$v;
}
// echo '<pre />';
// print_r($results);exit;

tpl('finance/shyItemList');
?>
