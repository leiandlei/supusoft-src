<?php

$uid = current_user('uid');
	$hr_info = $user->get($uid);
	tpl('sys/resetpw');