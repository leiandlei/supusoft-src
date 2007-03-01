<?php
$join =  $select = '';$where =' where 1';

$sql    = 'select %s from `sp_examine`'; 

$seach = getSeach();
foreach ($seach as $key => $value)
{
	switch ($key)
	{
		default:
			$str = " and `%s` like '%%%s%%'";
			break;
	}
	$where .= sprintf($str,$key,$value);
}

/**分页**/
if (!$export)
{
    $total = $db->get_var(sprintf($sql,'count(id) as total').$join.$where);
    $pages = numfpage($total);
}
$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where.$pages['limit'];
$results = $db->getAll($sql);
tpl();