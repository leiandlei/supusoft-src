<?php
/*
 *组织名称检测
 */
$ep_name = getgp('ep_name');
//@HBJ 2013-09-26 修复已删除提示重复的问题
$total   = $db->get_var("SELECT COUNT(*) FROM sp_enterprises WHERE ep_name = '$ep_name' AND deleted = 0");
echo $total;
exit();