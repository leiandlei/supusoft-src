<?php

$a = getgp('a'); //不能删除 
if(!$a){
	$a = 'login';
}

$user = load('login');

if($a == 'login'){
	$supuLoginName = $_COOKIE['loginName'];
	tpl('login');

}elseif($a == 'reg'){
	//debug();
	tpl('reg');
}elseif($a=='login_in'){ //异步传输验证用户是否登录
	
	$username = getgp('uname');
	$password = getgp('upwd');
	$usertype = getgp('utype');
	//@zbzytech 加入判断 不同的人进入不同的
	switch ($usertype) {
		case 'stuff'://机构
			$result = $user->userAuth($username,$password,$usertype);
			// 记录日志
			log_add(0, 0, "机构{$username}登录，结果：{$result}", NULL, NULL);
			break;

		case 'hezuofang'://合作方
			$result = $user->hezuofangAuth($username,$password);
			// 记录日志
			log_add(0, 0, "合作方{$username}登录，结果：{$result}", NULL, NULL);
			break;

		case 'customer'://企业
			$result = $user->customerAuth($username,$password,$usertype);
			// 记录日志
			log_add(0, 0, "企业{$username}登录，结果：{$result}", NULL, NULL);
			break;
		
		default:
			$result=null;
			break;
	}
	echo $result;

}elseif($a=='login_out'){
	// 记录日志
	log_add(0, 0, "帐号" . current_user('username') . "退出", NULL, NULL);
	$result = $user->userLogout();
	echo $result;
}
?>