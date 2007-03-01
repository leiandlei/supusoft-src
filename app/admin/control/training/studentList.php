<?php
$join =  $select = '';$where =' where 1';

$sql    = 'select %s from `sp_training_student`'; 
$where .= ' and `status`=1';

$seach = getSeach();
extract($seach,EXTR_OVERWRITE);
foreach ($seach as $key => $value) {
	switch ($key) {
		case 's_xueli':
		case 's_xuewei':
		case 's_card':
			$str = " and `%s`='%s'";
			break;
		case 's_name':
		default:
			$str = " and `%s` like '%%%s%%'";
			break;
	}

	$where .= sprintf($str,$key,$value);
}

$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
//echo $sql;exit;
/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql   = $sql.$pages['limit'];
}

$results = $db->getAll($sql);
tpl();