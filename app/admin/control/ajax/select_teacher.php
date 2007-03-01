<?php
/*
 *选择专业管理人员（弹窗）
 */	
	extract($_GET, EXTR_SKIP);
	$where="";
    if($name=trim($name)){
		$where .=" and hr.name like '%$name%'";
	
	}
	if($easycode=trim($easycode)){
		$where .=" and hr.easycode like '%$easycode%'";
	
	}
    
	$_codes=explode("；",$use_code);
	$_codes=array_unique($_codes);
	!$_codes[0] && $_codes=array(-1);		
	
	// print_r($step);exit;
	if($step=='1'){
		//专业管理人员
		$where.=" AND ha.qua_type='2001'";
	
	}elseif($step=='2'){
		//技委会人员
		$where.=" AND ha.qua_type='2002'";
	
	}elseif($step=='3'){
		//评定人员（业务代码登记）
		$sql = "select code,name,sex,easycode from sp_hr where 1 ".$where." and `job_type` like '%1001%' and deleted='0'";
	}
	// print_r($sql);exit;
		
	$sql = isset($sql)?$sql:"SELECT hac.use_code,hr.name,hr.code,hr.easycode,hr.sex FROM `sp_hr_audit_code` hac LEFT JOIN sp_hr hr on hr.id=hac.uid LEFT JOIN sp_hr_qualification ha ON ha.id=hac.qua_id  WHERE hac.`iso` = '$iso' AND `use_code` in ('".implode("','",$_codes)."') AND hac.`deleted` = '0' $where";
	
	$hrs = array();
	$query=$db->query($sql);
    while ($rt = $db->fetch_array($query)) {
		$rt[sex]==1?$rt[sex]="男":$rt[sex]="女";
        $hrs[] = $rt;
    }
    tpl();