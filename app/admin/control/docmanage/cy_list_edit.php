<?php
$where = ' where 1 and `status`=3';
$seach = getSeach();
extract($seach,EXTR_OVERWRITE);
foreach ($seach as $key => $value) {
	switch ($key) {
		case 'type_level':
		case 'type_shuxing':
			$str = " and `%s`=%s";
			break;

		case 'code':
		case 'name':
		case 'note':
		default:
			$str = " and `%s` like '%%%s%%'";
			break;
	}

	$where .= sprintf($str,$key,$value);
}
$sql = 'select * from `sp_docmanage`'.$where.' order by `weight` desc';
$results = $db->getAll($sql);
tpl('docmanage/cy_list');
?>
