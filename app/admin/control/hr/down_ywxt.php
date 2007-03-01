<?php
require_once CONF . 'cache/job_type.cache.php';
$uid  = (int)getgp('uid');
$data = $user -> get($uid);

$filename = '业务系统申请表.doc';
$tpldata = readover(DOCTPL_PATH . 'doc/HR-ywxtsq.xml');
$output = str_replace('{name}', $data['name'], $tpldata);
//姓名
$output = str_replace('{datetime}', date('Y-m-d'), $output);
//证书编号
$output = str_replace('{job_type}', $job_type_array[$data['job_type']]['name'], $output);
//发证日期
header("Content-type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=" . iconv('UTF-8', 'gbk', $filename));
echo $output;
exit ;
?>