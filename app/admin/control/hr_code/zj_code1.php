<?php
//导出技术专家代码1
$_query=$db->query("SELECT zj_uid,code FROM `sp_stff` WHERE `zj_uid` IS NOT NULL");
$uids=array();
while($_rt=$db->fetch_array($_query)){
	
	$iso="";
	if(substr($_rt[code],0,1)=='Q')
		$iso='A01';
	elseif(substr($_rt[code],0,1)=='E')
		$iso='A02';
	elseif(substr($_rt[code],0,1)=='S')
		$iso='A03';
	$uids[$_rt[zj_uid]]=$iso;
}
p($uids);
exit;
exit;
foreach($uids as $uid=>$iso){
	$query=$db->query("SELECT * FROM `sp_hr_audit_code` WHERE `uid` ='$uid' AND iso='$iso' AND  `deleted` = '0' and length(use_code)=7");
	$hacs=array();
	while($rt=$db->fetch_array($query)){
		$hr_info=$db->get_row("SELECT code,name,is_hire,audit_job,ctfrom FROM `sp_hr` WHERE `id` = '$rt[uid]' ");
		$rt=@array_merge($rt,$hr_info);
		$rt['qua_type_V'] = f_qua_type( $rt['qua_type'] );
		$rt['iso_V'] = f_iso( $rt['iso'] );
		$rt['ctfrom_V'] = f_ctfrom( $rt['ctfrom'] );
		$rt['source'] = f_source( $rt['source'] );
		$rt['audit_job'] = f_audit_job( $rt['audit_job'] );
		$rt['status_V'] = $status_array[$rt['status']];
		$rt['is_assess_V'] = ($rt['is_assess'])?'是':'否';
		$rt['is_hire_V']=$hr_is_hire[$rt['is_hire']];
		$temp=$db->get_col("SELECT shangbao FROM `sp_settings_audit_code` WHERE `iso` = '$rt[iso]' and code='$rt[use_code]'");
		foreach($temp as $k=>$v){
			$temp[$k]=substr($v,0,8);
			
		}
		$rt[audit_code]=join(";",$temp);
		$hacs[$rt['id']] = chk_arr($rt);

	}
}

ob_start();
	tpl( 'xls/list_hr_code' );
	$data = ob_get_contents();
	ob_end_clean();
	export_xls( '人员专业代码列表', $data );

?>