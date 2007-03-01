<?php

/*
*选择行业
*/
	//列表
	$sql="select audit_ver,msg from sp_settings_audit_vers where is_stop='0' and deleted='0'";

 	$audit_ver=$db->getAll($sql);

	//显示模板
	tpl('ajax/select_audit_ver');

