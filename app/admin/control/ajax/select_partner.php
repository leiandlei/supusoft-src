<?php
	$join =  $select = '';$where =' where 1';

	$sql  = 'select * from `sp_partner`';
	$where .= ' and `deleted`=0';

	$seach = getSeach();
	extract($seach,EXTR_OVERWRITE);
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 'code':
			case 'name':
				$str = " and `%s` like '%%%s%%'";
				break;
			case 'level':
			default:
				$str = " and `%s`='%s'";
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
?>