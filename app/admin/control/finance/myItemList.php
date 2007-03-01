<?php
$where = $join = '';

$select = 'ssd.id,ssd.sid,ssd.name,ssd.teamLeater,ssd.leaterType,ssd.zhuanyeType,ssd.witness,ssd.auditMoney,ssd.typeMoney,ssd.totalMoney,ssd.days,ssd.status';

$join   .= 'left join sp_shytj ss on ssd.sid=ss.id ';
$select .= ',ss.type,ss.isGoTo,ss.startTime,ss.endTime';

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
$where  .= ' and ssd.del=1 and ss.del=1';
$where  .= ' and ssd.uid='.$_SESSION['userinfo']['id'];
$sql = 'select '.$select.',ssd.is_todo from sp_shytj_detail ssd '.$join.'where 1'.$where;
$results = $db->getAll($sql);

tpl('finance/myItemList');
?>
