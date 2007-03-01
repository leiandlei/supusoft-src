<?php
	$tabs_where = $join =  $select = '';$where =' where 1';

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
	/**标签**/
	$tab = getgp('tab')?getgp('tab'):'1';
	$tabs[$tab] = 'ui-state-active';
	
	$datas[1]   = $db->getAll($sql.' and status=1');
	$datas[2]   = $db->getAll($sql.' and status=0');
	foreach ($datas as $key => $value) {
		$total[$key] = count($value);
	}
	/**标签**/
	/**分页**/
	if (!$export) {
	    $pages = numfpage($total['tabs']);
	    if( empty($pages['limit']) ){
	    	$results = $datas[$tab];
	    }else{
	    	$limits  = explode(',',substr($pages['limit'],7));
	    	$results = array_slice($datas[$tab],$limits[0],$limits[1]);
	    }
	}
	/**分页**/

tpl('partner_list');
?>
