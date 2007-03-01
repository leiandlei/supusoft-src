<?php
$join = $select = '';$where = ' where 1';
//培训表
$sql    = "select %s from `sp_training_info` sti";
$select.= "*,sti.id";
$where .= ' and sti.`status`=1';

//学生表
$join  .= " left join `sp_training_student` sts on sti.s_id=sts.id";
$where .= ' and sts.`status`=1';

//培训表
$join  .= " left join `sp_training_lesson`  stl on sti.l_id=stl.id";
$where .= ' and stl.`status`=1';

/**条件搜索**/
$seach = getSeach();
if( !empty($seach) ){
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 's_name'://学生姓名
				$str = " and sts.`%s` like '%%%s%%'";
				break;
			case 's_job'://学生姓名
				$str = " and sts.`%s` like '%%%s%%'";
				break;
			case 'l_name'://课程
				$str = " and stl.`%s` like '%%%s%%'";
				break;
			case 'i_zstype'://证书类型
				$str = " and sti.`%s`=%s";
				break;
			case 'i_note'://培训备注
				$str = " and sti.`%s` like '%%%s%%'";
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
/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql = $sql.$pages['limit'];
}
/**分页**/
$results = $db->getAll($sql);
tpl();