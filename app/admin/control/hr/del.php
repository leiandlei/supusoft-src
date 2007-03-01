<?php

//删除人员
$uid = getgp('uid');
    if ($uid) {
        $user->del($uid);
    }
    $REQUEST_URI = '?c=hr&a=list';
    showmsg('success', 'success', $REQUEST_URI);