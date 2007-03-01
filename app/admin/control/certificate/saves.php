<?php
	$default = array(
			'certno'		=> getgp('certno'),	//证书号码
			'old_certno'	=> getgp('old_certno'),	//原证书号
			'main_certno'	=> getgp('main_certno'),	//主证书号
			'first_date'	=> getgp('first_date'),	//初次获证时间
			's_date'		=> getgp('s_date'),	//证书开始时间
			'e_date'		=> getgp('e_date'),	//证书结束时间
			'old_cert_name' => getgp('old_cert_name'),
			//'report_date'	=> getgp('report_date'),	//上报日期
			'cert_name'		=> getgp('cert_name'),	//证书企业名称
			'cert_name_e'	=> getgp('cert_name_e'),  //证书企业名称英文
			'cert_addr'		=> getgp('cert_addr'),  //证书地址
			'cert_addr_e'	=> getgp('cert_addr_e'),	//证书地址英文
 			'is_change'		=> getgp('is_change'), 	//
			'cert_scope'	=> getgp('cert_scope'),	//
			'cert_scope_e'	=> getgp('cert_scope_e'),	//
			'change_type'	=> getgp('change_type'),	//
			'change_date'	=> getgp('change_date'), 	//
			'note'			=> getgp('note'), 	//
			'sort'			=> substr(getgp('certno'),10,4),
			'is_check'		=> 'n',
		);
		
		if($zsid){
			$certificate->edit($zsid, $default);
			}
		$REQUEST_URI='?c=certificate&a=lists';

	showmsg( 'success', 'success', $REQUEST_URI );