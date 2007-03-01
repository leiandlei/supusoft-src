<?php
/* 
* @Author: anchen
* @Date:   2017-03-23 14:26:19
* @Last Modified by:   anchen
* @Last Modified time: 2017-06-12 09:26:31
*/
$tabs_where = $join =  $select = '';$where =' where 1';

	$sql  = 'select * from `sp_partner_enterprises` ';
	$where .= ' and deleted=0';


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
				$str = " and `%s` like '%%%s%%'";
				break;
		}

		$where .= sprintf($str,$key,$value);
	}
	$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
	/**标签**/
	$tab = getgp('tab')?getgp('tab'):'1';
	$tabs[$tab] = 'ui-state-active';
	
	$datas[1]   = $db->getAll($sql.' and status=1');
	$datas[2]   = $db->getAll($sql.' and status=2');
	$datas[3]   = $db->getAll($sql.' and status=3');
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
	// echo '<pre />';
	// print_r($results);exit;
	 
tpl('apply_list1');
?>
