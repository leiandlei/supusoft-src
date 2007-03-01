<?php
$join =  $select = '';$where =' where 1';
$sql  = 'select * from `sp_training_lesson`';
$where .= ' and `status`=1';

$seach = getSeach();
extract($seach,EXTR_OVERWRITE);
foreach ($seach as $key => $value) {
	switch ($key) {
		case 'l_teacher':
		case 'l_name':
		case 'l_note':
		default:
			$str = " and `%s` like '%%%s%%'";
			break;
	}

	$where .= sprintf($str,$key,$value);
}
$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;

/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql = $sql.$pages['limit'];
}

$results = $db->getAll($sql);
tpl();