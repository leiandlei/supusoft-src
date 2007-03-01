<?php


$id = getgp('id');
    if ($id) {
        $exp->del($id);
    }
    $REQUEST_URI = '?c=hr&a=edit&uid='.$_GET['uid'];
    showmsg('success', 'success', $REQUEST_URI . "#{$_REQUEST['anchor']}");
