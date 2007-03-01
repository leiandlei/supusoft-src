<?php
$where = ' where 1 and `status`=1';
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
/*分页*/
// if (!$export) {
//     $total = $db->get_var("SELECT COUNT(*) FROM sp_training  WHERE 1 $where");
//     $pages = numfpage($total);
// }
$sql = 'select * from `sp_training_lesson`'.$where." $pages[limit]";
$results = $db->getAll($sql);
tpl();