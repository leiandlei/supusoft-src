<?php
//保存证书
$pid=getgp("pid");
$f_check = $is_check = getgp('is_check');	//
	$old_check = getgp('old_check');

	if(( 'y' == $old_check || $old_check=='n' ) && ($is_check=='e' || $is_check == 'y' ) ){
		$is_checks = 'y';
	}else if($old_check=='n' && !$is_check){
		$is_checks = 'e';
	}else if($old_check=='e' && $is_check){
		$is_checks = 'y';
	}else if($old_check=='e' && !$is_check){
		$is_checks = 'e';
	}

  	extract( $_POST, EXTR_SKIP );
	//判断证书号是否重复
  	if(getgp('addnew')==1){
		$sql = "select * from sp_certificate where certno='".getgp('certno')."' and status in ('01','02') AND deleted=0";
  	}else{
		$sql = "select * from sp_certificate where id != '".$zsid."' and certno='".getgp('certno')."' and status in ('01','02')   AND deleted=0";
  	}
	$info = $db->get_row($sql);
	if($info){
		echo "<script type='text/javascript'>alert('证书号码不能重复！');history.go(-1);</script>";die();
	}
	/* if($e_date= getgp('e_date')){
		//修改时间 证书有效
		if($e_date>current_time('mysql'))
			$db->update("certificate",array('e_date'=>$e_date,'status'=> 1),array("id"=>$zsid));

	} */

	if($old_eid==$new_eid AND getgp('addnew')!=1){
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
			// 'cert_addr'		=> getgp('cert_addr'),  //证书地址
			// 'cert_addr_e'	=> getgp('cert_addr_e'),	//证书地址英文
			'ep_addr'       => getgp('ep_addr'),//注册地址
			'ep_addr_e'     => getgp('ep_addr_e'),//注册地址英文
			'cta_addr'      => getgp('cta_addr'),//通讯地址
			'cta_addr_e'    => getgp('cta_addr_e'),//通讯地址英文
			'bg_addr'       => getgp('bg_addr'),//办公地址
			'bg_addr_e'     => getgp('bg_addr_e'),//办公地址英文
			'prod_check'    => getgp('prod_check'),//类型
			'prod_addr'     => getgp('prod_addr'),//其他地址
			'prod_addr_e'   => getgp('prod_addr_e'),//其他地址英文
 			'is_change'		=> getgp('is_change'), 	//
			'cert_scope'	=> getgp('cert_scope'),	//
			'cert_scope_e'	=> getgp('cert_scope_e'),	//
			'change_type'	=> getgp('change_type'),	//
			'change_date'	=> getgp('change_date'), 	//
			'note'			=> getgp('note'), 	//
			'sort'			=> substr(getgp('certno'),10,4) 	//
		);
		if( empty($is_checks) ){
			$is_check_new = getgp('is_check_new');
			if( !empty($is_check_new) )
			{
				$default['is_check'] = $is_check_new;
			}
		}
		if($zsid){
			// echo "<pre />";
			// print_r($default);exit;
			$certificate->edit($zsid, $default);
			$eid=$db->get_var("SELECT eid FROM `sp_certificate` WHERE `id` = '$zsid'");
			}
		else{
			if($pid){
				$p_info=$db->find_one("project",array("id"=>$pid));
				$eid=$p_info['eid'];
				$new_cert = array(
						'eid'			=> $p_info['eid'],	//企业id
						'ct_id'			=> $p_info['ct_id'],	//合同id
						'cti_id'		=> $p_info['cti_id'],	//合同项目id
						'iso'			=> $p_info['iso'],	//体系
						'audit_ver'		=> $p_info['audit_ver'],	//体系版本
						'mark'			=> $p_info['pd_mark'],	//标志
						'ct_code'	    => $p_info['ct_code'],	//
						'cti_code'	    => $p_info['cti_code'],	//
						'ctfrom'	    => $p_info['ctfrom'],	//
						// 'total'	=> $p_info['total'],	//
						// 'use_code'	=> $p_info['use_code'],	//
						// 'audit_code'	=> $p_info['audit_code'],	//审核代码
								);	
				$default=array_merge($default,$new_cert);
			}
			// echo "<pre />";
			// print_r($default);exit;
			$zsid = $certificate->add($default);
			
			
		}		
		$db->update("project",array("ifchangecert"=>2),array("id"=>$pid));
		$sms_arr=array("eid"=>$eid,
						"temp_id"=>$zsid,
						"flag"=>1);
		 
		 $get_status=$zs_info[status];
		
	}else{//添加子证书
		$zs_info = $certificate->get($zsid);
		$new_cert = array(
			'eid'			=> $new_eid,	//企业id
			'ct_id'			=> $zs_info['ct_id'],	//合同id
			'ct_code'		=> $zs_info['ct_code'],	//合同
			'cti_id'		=> $zs_info['cti_id'],	//合同项目id
			'cti_code'		=> $zs_info['cti_code'],	//合同项目
			'iso'			=> $zs_info['iso'],	//体系
			'audit_ver'		=> $zs_info['audit_ver'],	//体系版本
			'mark'			=> $zs_info['mark'],	//标志
			// 'audit_code'	=> $zs_info['audit_code'],	//评定代码
			// 'use_code'		=> $zs_info['use_code'],	//使用代码
			'ctfrom'		=> $zs_info['ctfrom'],
			'certno'		=> getgp('certno'),	//证书号码
			'old_certno'	=> getgp('old_certno'),	//原证书号
			'main_certno'	=> getgp('main_certno'),	//主证书号
			'first_date'	=> getgp('first_date'),	//初次获证时间
			's_date'		=> getgp('s_date'),	//证书开始时间
			'e_date'		=> getgp('e_date'),	//证书结束时间
			'old_cert_name' => getgp('old_cert_name'),
			'cert_name'		=> getgp('cert_name'),	//证书企业名称
			'cert_name_e'	=> getgp('cert_name_e'),  //证书企业名称英文
			// 'cert_addr'		=> getgp('cert_addr'),  //证书地址
			// 'cert_addr_e'	=> getgp('cert_addr_e'),	//证书地址英文
			'ep_addr'       => getgp('ep_addr'),//注册地址
			'ep_addr_e'     => getgp('ep_addr_e'),//注册地址英文
			'cta_addr'      => getgp('cta_addr'),//通讯地址
			'cta_addr_e'    => getgp('cta_addr_e'),//通讯地址英文
			'prod_check'    => getgp('prod_check'),//类型
			'prod_addr'     => getgp('prod_addr'),//其他地址
			'prod_addr_e'   => getgp('prod_addr_e'),//其他地址英文

			'cert_scope'	=> getgp('cert_scope'),	//
			'cert_scope_e'	=> getgp('cert_scope_e'),	//
			'change_type'	=> getgp('change_type'),	//
			'change_date'	=> getgp('change_date'), 	//
 			'note'			=> getgp('note'), 	//
			'cert_scope' 	=> getgp('cert_scope'),
			'cert_scope_e' 	=> getgp('cert_scope_e'),
		);
		$cert = load( 'certificate' );
		$zs_id=$cert->add( $new_cert );
		$sms_arr=array("eid"=>$new_eid,
						"temp_id"=>$zs_id,
						"flag"=>1);
		unset($zs_id);
	}
	$sms=load("sms");
	$sms_info=$sms->get(array("temp_id"=>$sms_arr[temp_id],"flag"=>1));
	if($sms_info[id])
		$sms->edit($sms_info[id],$sms_arr);
	else
		$sms->add($sms_arr);
			//跳转页面
		$REQUEST_URI='?c=certificate&a=list';

	showmsg( 'success', 'success', $REQUEST_URI );

    
