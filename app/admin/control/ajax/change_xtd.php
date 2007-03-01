<?php
/* 
* @Author: anchen
* @Date:   2017-05-27 11:09:59
* @Last Modified by:   anchen
* @Last Modified time: 2017-05-27 12:03:18
*/
$current_id   = getgp('current_id'); //本身id
$exchange_id  = getgp('exchange_id');//移动位置id
$param['orders']   = $db->get_var("select orders from  sp_partner_coordinator where id='".$current_id."'");
$params['orders']  = $db->get_var("select orders from  sp_partner_coordinator where id='".$exchange_id."'");
$ups          = $db -> update( 'partner_coordinator',$param,array('id'=>$exchange_id),false );
$downs        = $db -> update( 'partner_coordinator',$params,array('id'=>$current_id),false );
if ($ups !== FALSE && $downs !== FALSE){
		  exit('1');
		} else {
		  exit('2');
		}

// echo "<pre />";
// print_r($arr1);exit;




?>
