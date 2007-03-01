<?php
	$iso = getgp('iso');
	$audit_code      =  getgp('audit_code');
	$audit_code_2017 =  getgp('audit_code_2017');
	$success         = '<img src="theme/images/success.png" width="24">';
	$error           = '<img src="theme/images/error.png" width="24">';
	$results = array();
	if(!empty($audit_code))
	{
		$audit_code = array_filter(explode('；',$audit_code));
		foreach ($audit_code as $code) {
			$zhuanye                         = $db->get_var("select shangbao from sp_settings_audit_code where id =".$code);
			$results[$zhuanye]['shenheyuan'] = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('01','02') and iso='".$iso."' and audit_code='".$code."' limit 1");
			$results[$zhuanye]['zhuanjia']   = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('04') and iso='".$iso."' and audit_code='".$code."' limit 1");
			$results[$zhuanye]['pingding']   = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('1000') and iso='".$iso."' and audit_code='".$code."' limit 1");
		}
	}elseif(!empty($audit_code_2017))
	{
		
		$audit_code_2017 = array_filter(explode('；',$audit_code_2017));
		
		foreach ($audit_code_2017 as $code) 
		{
			$zhuanye                         = $db->get_var("select shangbao from sp_settings_audit_code where id =".$code);
			$results[$zhuanye]['shenheyuan'] = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('01','02') and iso='".$iso."' and audit_code_2017='".$code."' limit 1");
			$results[$zhuanye]['zhuanjia']   = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('04') and iso='".$iso."' and audit_code_2017='".$code."' limit 1");
			$results[$zhuanye]['pingding']   = $db->getOne("select `id` from `sp_hr_audit_code` where deleted=0 and qua_type in('1000') and iso='".$iso."' and audit_code_2017='".$code."' limit 1");
		}
		
	}
	tpl();