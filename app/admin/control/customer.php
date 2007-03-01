<?php 
$step = getgp('step');//不能删除 要传递
$a = getgp('a'); //不能删除 
if(!$a){
	$a = 'login';
}

$customer = load('customer');

//var_dump($customer);

if($a == 'login'){
	$supuLoginName = $_COOKIE['loginName'];
	tpl('login');

}elseif($a == 'reg'){
	//debug();
	tpl('reg');
}elseif( 'add' == $a || 'edit' == $a ) {
	//LY a为add或者edit方法时，加载edit。php
	require_once( CTL_DIR. '/customer/edit.php' );
}
?>