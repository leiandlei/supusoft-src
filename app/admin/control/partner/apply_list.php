<?php
	$tabs_where = $join =  $select = '';$where =' where 1';

	$sql  = 'select *,pti.status from `sp_partner_info` pti';
	$where .= ' and pti.deleted=0';

	//关联合作方表
	$join .= ' left join `sp_partner` pt on pt.pt_id=pti.pt_id';

	$seach = getSeach();
	extract($seach,EXTR_OVERWRITE);
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 'code':
			case 'name':
				$str = " and pt.%s like '%%%s%%'";
				break;
			case 'level':
			default:
				$str = " and pt.%s ='%s'";
				break;
		}

		$where .= sprintf($str,$key,$value);
	}
	$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
	// print_r($sql);exit;
	/**标签**/
	$tab = getgp('tab')?getgp('tab'):'1';
	$tabs[$tab] = 'ui-state-active';
	
	$datas[1]   = $db->getAll($sql.' and pti.status=1');
	$datas[2]   = $db->getAll($sql.' and pti.status=2');
	$datas[3]   = $db->getAll($sql.' and pti.status=3');
	$datas[4]   = $db->getAll($sql.' and pti.status=4');
	$datas[5]   = $db->getAll($sql.' and pti.status=5');
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
	tpl('apply_list');
?>
