<?php
//评分标准管理--应该放到合同模块-需求分析在评定环节做

$nav_title = '评分标准';
//删除配置
if ($_GET['del']) {
    $db->update('access_set_ver', array(
        'deleted' => 1
    ), array(
        'id' => $_GET['del']
    ));
}
if ($_POST) {
    //新增配置
    $adds = deal_arr($_POST['new'], 'name');
    if ($adds) {
        foreach ($adds as $data) {
            $db->insert('access_set_ver', $data);
        }
    }
    //修改配置
    if ($olds = deal_arr($_POST['old'], 'name')) {
        foreach ($olds as $data) {
            $db->update('access_set_ver', $data, array(
                'id' => $data['id']
            ));
        }
    }
    showmsg('success', 'success', "?c=$c&a=$a");
}
//搜索 so
$where=''; 
if($_GET['iso']) $where.=" AND iso='$_GET[iso]'";
if($_GET['name'])$where.=" AND name like '%$_GET[name]%'"; 
//统计分页
$total=$db->find_num('access_set_ver',$where);
$pages = numfpage( $total);
 
//查询配置
$datas = $db->find_results('access_set_ver',$where,'*',$pages);
tpl();