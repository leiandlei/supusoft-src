<?php
/*
*申请小类
*/
$iso = getgp('iso');
    $acaid = getgp('acaid');
    if ($acaid) {
        $acapp = load('auditcodeapp');
        $row = $acapp->get($acaid);
        if ($row) {
            extract($row, EXTR_SKIP);
        }
    }
    $iso_v = f_iso($iso);
    tpl('auditor/appcode_edit');