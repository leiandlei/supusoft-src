<?php
/* 
* @Author: anchen
* @Date:   2017-06-08 13:37:31
* @Last Modified by:   mantou
* @Last Modified time: 2017-07-03 09:26:20
*/

    $uid                = (int)getgp('id');
	$left_nav           = array_reverse($left_nav);
	$left_nav_array_nav = $left_nav['main']['hezuofang'];
	$partner            = $db->get_row("select * from sp_partner where pt_id = '".$uid."'");
	tpl();
?>
