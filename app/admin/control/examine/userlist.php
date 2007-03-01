<?php
// $sql  =  "select name from sp_hr where job_type='1004'";
// $results = $db->getAll($sql);
$name_name  =getgp('renyuan_name');
$export     = getgp('export');
$date      =getgp('date');
if( !empty(getgp('date'))){
$date = getgp('date');
$date = substr($date,0,7);
}else{
$date = $date?substr($date,0,7):date('Y-m');
}
if( empty(getgp('renyuan_name')))
{
   $name = '';
}else{
   $name = getgp('renyuan_name');
   $name =" and sp_hr.name like '%".$name."%'";
}

// echo "<pre />";
// print_r($name);exit;
$join =  $select = '';$where =' where 1';

$sql     = 'select %s from `sp_examine_user`';
$select .= 'sp_examine_user.*';

$join    = ' join `sp_hr` on sp_hr.id=sp_examine_user.userID';
$select .= ',sp_hr.name,code';

$where  .= " and sp_examine_user.date='".$date."'";
$where  .=  $name;
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
    $total = $db->get_var(sprintf($sql,'count(sp_examine_user.id) as total').$join.$where);
    $pages = numfpage($total);
}
$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where.$pages['limit'];

$results = $db->getAll($sql);
// echo "<pre />";
// print_r($results);exit;
// 
  if (!$export) {
        tpl('examine/userlist');
    } else{
        
        ob_start();
        tpl('examine/userlist_xls');
        $data  = ob_get_contents();
        
        // echo "<pre />";
        // print_r($data);exit;
        ob_end_clean(); 
        export_xls($date.'月考核情况表', $data);
    }

tpl();