<?php


$aca = load('auditcodeapp');
    $acaid = getgp('acaid');
    if ($acaid) {
        $aca->del($acaid);
    }
    $REQUEST_URI = '?c=hr_code&a=clist&status=' . getgp('status');
    showmsg('success', 'success', $REQUEST_URI);