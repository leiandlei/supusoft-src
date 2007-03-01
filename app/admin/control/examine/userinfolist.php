<?
$join =  $select = '';$where =' where 1';
$seleRow = array();
if(array_key_exists('rows', $_REQUEST))
{
    $seleRow = explode(',', $_REQUEST['rows']);
    unset($_REQUEST['rows']);
}
$exu_id   = getgp('exu_id');

$seach_name   = getgp('seach_name');
// echo "<pre />";
// print_r($seach_name);exit;
$export       = getgp('export');
$date         = getgp('date');
$name    = getgp('name');
if( empty(getgp('seach_name')))
{
   $seach_name = '';
}else{
   $where  .= " and  sp_examine.id=".$seach_name;
}
// if(empty($exu_id))showmsg('请选择用户','error','?c=examine&a=userlist','2');



$sql     = 'select %s from `sp_examine_user_info`';
$select .= 'sp_examine_user_info.*';
$where  .= " and sp_examine_user_info.exu_id=".$exu_id ." and sp_examine_user_info.deleted=0  order by sp_examine_user_info.id desc ";

$join   .= ' join `sp_hr` on sp_hr.id=sp_examine_user_info.userID';
$select .= ',sp_hr.name';

$join   .= ' join `sp_examine` on sp_examine.id=sp_examine_user_info.ex_id';
$select .= ',sp_examine.name as ename,sp_examine.types';


/**分页**/
if (!$export)
{
    $total = $db->get_var(sprintf($sql,'count(sp_examine_user_info.id) as total').$join.$where);
    $pages = numfpage($total);
}
$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where.$pages['limit'];

$results = $db->getAll($sql);
// echo "<pre />";
// print_r($results);exit;
foreach ($results as $key=> $value) 
{
   $username  = $db->get_var("select name from sp_hr where id = '".$value['createUserID']."' and deleted=0");
   $results[$key]['creatname'] =$username; 
}
$examines = $db->getAll('select * from sp_examine where status=1 and is_stop=0');

  if (!$export) {
        tpl('examine/userinfolist');
    } else{
        
        ob_start();
        tpl('examine/userinfolist_xls');
        $data = ob_get_contents();
        ob_end_clean(); 
        export_xls($date.'月'.$name.'考核情况表', $data);
    }