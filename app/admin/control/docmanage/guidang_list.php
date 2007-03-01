<?php
$guidang = (int)getgp('guidang');
$guidang = empty($guidang)?'0':'1';
${'guidang_'.$guidang.'_tab'}=' ui-state-active ';

$join = $select = '';$where = ' where 1';

//审核项目
$sql    = " select %s from `sp_project` p";
$select.= "p.ct_id,p.tid,p.audit_type,p.ct_code";
$where .= " AND p.ctfrom >= '01000000' AND p.ctfrom < '02000000' AND p.deleted=0 and ifchangecert=2";

//企业表
$join  .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$select.= ",e.ep_name,e.eid";
$where .= " and e.deleted=0";

// //证书表
// $join  .= " left join sp_certificate cf on cf.cti_id=p.cti_id";
// $select.= "";
// $where .= " and cf.deleted=0 and cf.is_check='y'";

//合同表
$join  .= " left join sp_contract c on c.eid=e.eid";
$select.= ",c.guidang";
$where .= " and c.guidang=".$guidang." and c.deleted=0";
 
/**条件搜索**/
$seach = getSeach();
if( !empty($seach) ){
	foreach ($seach as $key => $value) {
		switch ($key) {
			case 'ep_name'://企业名称
				$str = " and e.`%s` like '%%%s%%'";
				break;
			default:
				break;
		}
		$where .= sprintf($str,$key,$value);
	}
	extract($seach,EXTR_OVERWRITE);
}
/**条件搜索**/

$sql = sprintf($sql,($select=='')?'*':$select).$join.$where." group by p.eid";
$total_0 = count($db->getAll(preg_replace("/c.guidang=[0|1]/","c.guidang=0",$sql)));
$total_1 = count($db->getAll(preg_replace("/c.guidang=[0|1]/","c.guidang=1",$sql)));

/**分页**/
if (!$export) {
    $total = count($db->getAll($sql));
    $pages = numfpage($total);
    $sql = $sql.$pages['limit'];
}
/**分页**/

$results_all = $db->getAll($sql);
$results = array();
foreach ($results_all as $value) {
	if(in_array($value['audit_type'],array(1001,1002,1003)) )$value['audit_type']='初审';
	if($value['audit_type']==1004)$value['audit_type'] = '监一';
	if($value['audit_type']==1005)$value['audit_type'] = '监二';
	if($value['audit_type']==1006)$value['audit_type'] = '监三';
	if($value['audit_type']==1007)$value['audit_type'] = '再认证';
	if($value['audit_type']==1008)$value['audit_type'] = '专项审核';
	if($value['audit_type']==1009)$value['audit_type'] = '特殊监督';
	if($value['audit_type']==1101)$value['audit_type'] = '变更';
	if($value['audit_type']==99)$value['audit_type']   = '其他';
	$results[] = $value;
}
// echo '<pre />';
// print_r($results);exit;

tpl();