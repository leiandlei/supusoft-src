<?php
//培训管理列表  获取页面请求，查询列表用extract获取get变量，修改与添加用 $_GET 或$_post
$fields = $join = $where = '';
extract($_GET, EXTR_SKIP); //获取搜索项 

/*搜索条件*/
$where = ' where 1';
if( !empty($_REQUEST['tr_name']) ){
    $tr_name = $_REQUEST['tr_name'];
    $where  .= " and `tr_name` like '%".$tr_name."%'";
}

if( !empty($_REQUEST['tr_sex']) ){
    $tr_sex = $_REQUEST['tr_sex'];
    $where  .= " and `tr_sex` like '%".$tr_sex."%'";
}

if( !empty($_REQUEST['tr_code']) ){
    $tr_code = $_REQUEST['tr_code'];
    $where  .= " and `tr_code` = ".$tr_code;
}

if( !empty($_REQUEST['tr_enter']) ){
    $tr_enter = $_REQUEST['tr_enter'];
    $where  .= " and `tr_enter` like '%".$tr_enter."%'";
}
/*分页*/
// if (!$export) {
//     $total = $db->get_var("SELECT COUNT(*) FROM sp_training  WHERE 1 $where");
//     $pages = numfpage($total);
// }
/*列表*/
$enterprises = $results =array();
$query       = $db->query("SELECT * FROM sp_training $where ORDER BY trs_date DESC $pages[limit]");
while( $rt = $db->fetch_array( $query ) ){
    $results[] = $rt;
}
tpl();