<?php
/*
 *人员模块文档信息
 */
$id = getgp('id');
    $ftype = getgp('ftype');
    if ($id) {
        $sql = "update sp_hr_archives set ftype='$ftype' where id='$id' ";
        $db->query($sql);
    }