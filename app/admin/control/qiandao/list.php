<?php
$tid              =  getgp('tid');
if(!$tid==0){
	$join = $select = '';$where = ' where 1  ';
	//培训表
	$sql    = "select %s from `sp_task_qd` qd ";
	$select.= "qd.qd_date,qd.qd_dateTime,qd.qd_addr,qd.qd_type,qd.qd_lat,qd.qd_lng";
	$where .= ' and qd.`status`=1 and qd.tid="'.$tid.'" ';

	//企业表
	$join  .= " left join `sp_enterprises` e on qd.eid=e.eid";
	$select.= ",e.ep_name";
	$where .= ' and e.`deleted`=0';

	//审核计划
	$join  .= " left join `sp_task` t on t.id=qd.tid";
	$select.= ",t.tb_date,t.te_date";
	$where .= ' and t.`deleted`=0';

	//人员表
	$join  .= " left join `sp_hr` hr on hr.id=qd.userID";
	$select.= ",hr.name";
	$where .= ' and hr.`deleted`=0';

	/**条件搜索**/
	$seach = getSeach();
	if( !empty($seach) ){
		foreach ($seach as $key => $value) {
			switch ($key) {
				case 'ep_name'://企业名称
					$str = " and e.`%s` like '%%%s%%'";
					break;
				case 'name'://人员名称
					$str = " and hr.`%s` like '%%%s%%'";
					break;
				default:
					break;
			}
			$where .= sprintf($str,$key,$value);
		}
		extract($seach,EXTR_OVERWRITE);
	}
	
	/**条件搜索**/
	$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
	$qdtype  = array('1'=>'到场签到','2'=>'离场签到');
	$results = $db->getAll($sql);
	rsort($results);

	/**分页**/
	if (!$export) {
	    $total   = count($results);
	    $pages   = numfpage($total);

	    if( !empty($pages['limit']) ){
	    	$pages['pages'] = explode(',',substr($pages['limit'],7));
	    	$results        = array_slice($results,$pages['pages'][0],$pages['pages'][1]);
	    }
	}
}else{
	$join = $select = '';$where = ' where 1';
	//培训表
	$sql    = "select %s from `sp_task_qd` qd";
	$select.= "qd.qd_date,qd.qd_dateTime,qd.qd_addr,qd.qd_type,qd.qd_lat,qd.qd_lng";
	$where .= ' and qd.`status`=1';

	//企业表
	$join  .= " left join `sp_enterprises` e on qd.eid=e.eid";
	$select.= ",e.ep_name";
	$where .= ' and e.`deleted`=0';

	//审核计划
	$join  .= " left join `sp_task` t on t.id=qd.tid";
	$select.= ",t.tb_date,t.te_date";
	$where .= ' and t.`deleted`=0';

	//人员表
	$join  .= " left join `sp_hr` hr on hr.id=qd.userID";
	$select.= ",hr.name";
	$where .= ' and hr.`deleted`=0';

	/**条件搜索**/
	$seach = getSeach();
	if( !empty($seach) ){
		foreach ($seach as $key => $value) {
			switch ($key) {
				case 'ep_name'://企业名称
					$str = " and e.`%s` like '%%%s%%'";
					break;
				case 'name'://人员名称
					$str = " and hr.`%s` like '%%%s%%'";
					break;
				default:
					break;
			}
			$where .= sprintf($str,$key,$value);
		}
		extract($seach,EXTR_OVERWRITE);
	}
	/**条件搜索**/
	$where .= ' order by qd.id desc';

	$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
	// echo $sql;exit;
	$qdtype  = array('1'=>'到场签到','2'=>'离场签到');
	$results = $db->getAll($sql);
}
/**分页**/
tpl();