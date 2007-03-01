<?php

/*
*选择用户
*/

$select = '';$where =' ';

$sql    = "select %s from `sp_hr`  where 1";
$where .= " and  deleted=0";
//模糊查询
$seach = getSeach();
extract($seach,EXTR_OVERWRITE);
foreach ($seach as $key => $value) {
	switch ($key) {
		case 'name':
		default:
			$str = " and `%s` like '%%%s%%'";
			break;
	}
	  $where .= sprintf($str,$key,$value);
}
$sql = sprintf($sql,($select=='')?'*':$select).$where;
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql   = $sql.$pages['limit'];
}
$person =$db->getAll($sql);
//显示模板
tpl('ajax/select_toUserID');

