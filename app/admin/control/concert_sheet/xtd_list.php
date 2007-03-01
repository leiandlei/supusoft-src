<?php
/* 
* @Author: anchen
* @Date:   2017-03-23 14:26:19
* @Last Modified by:   mantou
* @Last Modified time: 2017-07-14 18:40:31
*/
//echo gettourl();exit;
require_once (ROOT . '/data/cache/audit_ver.cache.php');
$audit_type_select= f_select('audit_type','',array('1001','1004','1005','1007'));
if (empty(getgp('s_date'))&&empty(getgp('e_date'))) {
	$month = "";
}else{
	$begindate = getgp('s_date');//取数开始日期
	$begindate = substr($begindate, 0,7);
	$startdate = $begindate."-01 00:00:00";
	$enddate   = $begindate."-31 23:59:59";
	$month = "and yjdshsj_start between '".$startdate."' and '".$enddate."'";
}
$usertype = $_SESSION['extraInfo']['userType'];
//查询合作方表
$ctfrom =getgp('ctfrom');
$export =getgp('export');
//计划员姓名
$user = $_SESSION['userinfo']['name'];
//计划员姓名显示结束
$tabs_where = $join =  $select = '';$where =' where 1';

	$sql    = 'select * from `sp_partner_coordinator` ';
	$where .= ' and code="'.$ctfrom.'" and deleted=0 '.$month;
	$seach  = getSeach();
	extract($seach,EXTR_OVERWRITE);
	foreach ($seach as $key => $value) {
		
		switch ($key) {
	        
			case 'name':
			$str = " and `%s` like '%%%s%%'";
			break;
			case 'leader':
			$str = " and `%s` like '%%%s%%'";
			break;
			case 'zuyuan':
			$str = " and `%s` like '%%%s%%'";
			break;
			case 'person':
			$str = " and leader like '%%".$value."%%' or zuyuan like '%%".$value."%%'";
			break;
			case 'audit_type':
			$str = " and `audit_type` = '$value'";
			break;
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
	
	$datas[1]   = $db->getAll($sql.' and status=1'.'  order by orders');
	$datas[2]   = $db->getAll($sql.' and status=2'.'  order by orders');
	$datas[3]   = $db->getAll($sql.' and status=3'.'  order by orders');
	$datas[4]   = $db->getAll($sql.' and status=4'.'  order by orders');
	$datas[5]   = $db->getAll($sql.' and status=5'.'  order by orders');
	$datas[6]   = $db->getAll($sql.' and status=6'.'  order by orders');
	$datas[7]   = $db->getAll($sql.' and status=7'.'  order by orders');
	$datas[8]   = $db->getAll($sql.' and status=8'.'  order by orders');
	$datas[9]   = $db->getAll($sql.' and status=9'.'  order by orders');
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
	//体系名称
	foreach ($results as $key => $value) {
		
		$audit_ver_a =explode(";", $value['audit_ver']);
		foreach ($audit_ver_a as $val) {
			$results[$key]['audit_ver1'].=$audit_ver_array[$val]['msg'].";";
		}
		$results[$key]['audit_ver1'] = substr($results[$key]['audit_ver1'],0,-2);

		$yjdst         = substr($value['yjdshsj_start'],11,2);
		$yjdshsj_start = substr($value['yjdshsj_start'],0,10);
		$stzhongwu     = substr($value['yjdshsj_start'],14,2);
		if($yjdst >='00' && $yjdst <'12') 
		{
			$yjdst_bm = "上午";
		}
		if($yjdst >'12' && $yjdst <='24') 
		{
			$yjdst_bm = "下午";
		}
		if ($yjdst=='12' && $stzhongwu >'00') 
		{
			$yjdst_bm  = "下午";
		}
		if ($yjdst=='12' && $stzhongwu =='00')
		{
			$yjdst_bm  = "上午";
		}
		$results[$key]['yjdshsj_start'] = $yjdshsj_start." ".$yjdst_bm;
		$yjded         = substr($value['yjdshsj_end'],11,2);
		$yjdshsj_end   = substr($value['yjdshsj_end'],0,10);
		$edzhongwu     = substr($value['yjdshsj_end'],14,2);
		if($yjded >='00' && $yjded <'12') 
		{
			$yjded_bm = "上午";
		}
		if($yjded >'12' && $yjded <='24') 
		{
			$yjded_bm = "下午";
		}
		if ($yjded=='12' && $edzhongwu >'00') 
		{
			$yjded_bm  = "下午";
		}
		if ($yjded=='12' && $edzhongwu =='00')
		 {
			$yjded_bm  = "上午";
		}
		$results[$key]['yjdshsj_end'] = $yjdshsj_end." ".$yjded_bm;
		$ejdst         = substr($value['ejdshsj_start'],11,2);
		$ejdshsj_start = substr($value['ejdshsj_start'],0,10);
		$erstzhongwu   = substr($value['ejdshsj_start'],14,2);
		if($ejdst >='00' && $ejdst <'12') 
		{
			$ejdst_bm = "上午";
		}
		if($ejdst >'12' && $ejdst <='24') 
		{
			$ejdst_bm = "下午";
		}
		if ($ejdst=='12' && $erstzhongwu >'00') 
		{
			$ejdst_bm  = "下午";
		}
		if ($ejdst=='12' && $erstzhongwu =='00') 
		{
			$ejdst_bm  = "上午";
		}
		$results[$key]['ejdshsj_start'] = $ejdshsj_start." ".$ejdst_bm;
		$ejded         = substr($value['ejdshsj_end'],11,2);
		$ejdshsj_end   = substr($value['ejdshsj_end'],0,10);
		$eredzhongwu     = substr($value['ejdshsj_end'],14,2);
		if($ejded >='00' && $ejded <'12') 
		{
			$ejded_bm = "上午";
		}
		if($ejded >'12' && $ejded <='24') 
		{
			$ejded_bm = "下午";
		}

		if ($ejded=='12' && $eredzhongwu >'00') 
		{
			$ejded_bm  = "下午";
		}
		if ($ejded=='12' && $eredzhongwu =='00') 
		{
			$ejded_bm  = "上午";
		}
		$results[$key]['ejdshsj_end'] = $ejdshsj_end." ".$ejded_bm;
	}
	
if ($export) 
{
	
	$sql = "SELECT * FROM sp_partner_coordinator  $where and status='".$tab."'";
	$query = $db->query($sql);
	while ($rt = $db->fetch_array($query)) 
	{
		
	    $users[$rt['id']] = $rt;
	}
}

if (!$export) {
    tpl('xtd_list');
} else {
    ob_start();
    tpl('xls/xtd_excel');
    $data = ob_get_contents();
    ob_end_clean(); 
    export_xls('协调单', $data);
}
?>
