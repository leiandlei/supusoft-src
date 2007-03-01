<?php


$uid = current_user('uid');
	$hr_info = $user->get($uid);
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	if($hr_info['password']==md5($password1)){
		$user->edit($uid, array('password'=>md5($password2)));
		echo "<script>alert('密码修改成功');history.back(-1); </script>";
	}else{
		echo "<script>alert('原始密码不正确');history.back(-1); </script>";
	}
	//tpl('sys/resetpw');