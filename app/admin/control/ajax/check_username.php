<?php

//@zbzytech 可接受两种查询判断
/*
*检测用户账号是否重名
*/
 
$step = getgp('step');
if ($step=='customer') {
	$username = getgp('username');
    $cu_id = getgp('uid');
    //$total = $db->get_var("SELECT COUNT(*) FROM sp_customer WHERE username = '$username' AND  id != '$uid'");

    $total = $db->get_var("SELECT COUNT(*) FROM sp_customer WHERE username = '$username' ");
    echo $total;
    
    exit();
}else{
$username = getgp('username');
    $uid = getgp('uid');
    $total = $db->get_var("SELECT COUNT(*) FROM sp_hr WHERE username = '$username' AND  id != '$uid'");
    echo $total;
    exit();

}
