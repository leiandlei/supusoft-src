<?php
$titles=array('ep_name'=>"企业名称",'areacode'=>"行政区划代码",'areaaddr'=>"行政区划名称",'delegate'=>"法人",'ep_addr'=>"注册地址",'capital'=>"注册资金",'ep_addrcode'=>"邮政编码","work_code"=>"组织机构代码");
$eid= getgp('eid');
$work_code=getgp("work_code");
$work_code=str_replace("-","",trim(getgp("work_code")));
	$orgClass=getOrgInfo($work_code);
	if($orgClass->message=='success'){
		$orgInfos=$orgClass->orgInfos;
	$ep_info=array(
		'ep_name'		=>$orgInfos->orgName,
		'areacode'		=>$orgInfos->areaCode,
		'areaaddr'		=>$orgInfos->areaName,
		'delegate'		=>$orgInfos->legalName,
		'ep_addr'		=>$orgInfos->orgAddress,
		'capital'		=>$orgInfos->registeredCapital,
		'ep_addrcode'		=>$orgInfos->zipCode,
		// 'work_code'		=>trim(getgp("work_code")),
		);
	}else{
		echo "<script>alert('请检测组织机构代码');window.history.go(-1);</script>";
		exit;
		}
$ep_info_s=load("enterprise")->get(array("eid"=>$eid));

tpl();