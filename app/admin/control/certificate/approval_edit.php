<?php
if($_POST){
if($_POST[is_check])
	$_POST[is_check]="y";
else
	$_POST[is_check]="n";
$certificate->edit($_POST[id],$_POST);
showmsg("success","success","?c=certificate&a=approval_edit&id=3&ct_id$_POST[id]");
}
$id=getgp("id");
$pid=getgp("pid");
$cert=$certificate->get($id);

extract(chk_arr($cert));

$certreplace_select = str_replace( "value=\"$change_type\">", "value=\"$change_type\" selected>" , $certreplace_select );
$is_change_select = '<option value="0">否</option><option value="1">是</option>';
$is_change_select = str_replace( "value=\"$is_change\">", "value=\"$is_change\" selected>" , $is_change_select );

tpl();