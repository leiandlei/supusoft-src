<?php
/* 
* @Author: zhanghao
* @Date:   2017-03-30 14:08:50
* @Last Modified by:   anchen
* @Last Modified time: 2017-04-01 09:33:31
*/
$join = $select = '';$where =' where 1';

$sql    = 'select %s from `sp_hr_qualification` hrq';
$join   = ' left join `sp_hr` hr on hr.id=hrq.uid';
$where .= ' and hrq.qua_type in(01,02,03) and hr.deleted=0 and hrq.deleted=0';
$group  = ' group by hr.name';
$seach = getSeach();
extract($seach,EXTR_OVERWRITE);

foreach ($seach as $key => $value) {
	switch ($key) {
		case 'name':
		default:
			$str = " and hr.`%s` like '%%%s%%'";
			break;
	}
	  $where .= sprintf($str,$key,$value);
}

$sql = sprintf($sql,($select=='')?'*':$select).$join.$where.$group;
// print_r($sql);exit;
/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql   = $sql.$pages['limit'];
}
$results =$db->getAll($sql);
tpl();
?>
