<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$check="□";
$checked="■";

$tid  = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );

$arr_audit = $db->getAll("select audit_ver,audit_code from sp_contract_item where ct_id=".$ctid." and deleted=0");

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid' and deleted=0");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$audit_1002=$audit_1007=$check;
$ct = $db->get_row( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );
$iso = array();

//任务审批时间
$sql = 'select `approval_date` from `sp_task` where id='.$tid.' and deleted=0';
$approval_date = $db->get_var($sql);
$approval_date = sprintf(str_replace("-", "%s", $approval_date),'年','月').'日';

//外包倒班
$sql = 'select `wb_db` from `sp_contract` where ct_id='.$ctid.' and deleted=0';
$wb_db = $db->get_var($sql);
if ($wb_db=='') {
	$wb_db = "无";
}

//人日比例
$sql = 'select `xc_num`,`jdxc_num`,`audit_type` from `sp_contract_item` where ct_id='.$ctid.' and deleted=0';
$xc_num = $db->getAll($sql);
$array_audit_type = array("1004", "1005");
if(in_array($xc_num['audit_type'],$arr_audit_type,TRUE)){
	foreach ($xc_num as $v) {
		$xc_num_z += $v['jdxc_num'];
	}//监审现场人日
}else{
	foreach ($xc_num as $v) {
		$xc_num_z += $v['xc_num'];
	}//评审现场人日
}

// echo "<pre />";
// print_r($xc_num);exit;

//组织机构代码
if (strlen($work_code)==9) {
	$arr_work_code="组织机构代码";
}else{
	$arr_work_code="统一社会信用代码";
}

//附加信息
$sql = 'select * from `sp_metas_ep` where ID='.$eid.' and deleted=0';
$extra = $db->getAll($sql);
foreach ($extra as $value) {
	$extra_ep[$value['meta_name']] = $value['meta_value'];
}
// echo "<pre />";
// print_r($extra_ep);exit;

/**专业支持人员**/
$zhichi = '';

foreach ($arr_audit as $value) {
	$arr_code = array();$code_where = '(';
	$arr_audit_code = explode(';',$value['audit_code']);

	foreach ($arr_audit_code as $val) {
		$code = explode('.',$val);
		$arr_code[] = $code[0];
	}
	

	if( !empty($arr_code) ){
		foreach ($arr_code as $val) {
			$code_where .= "audit_code like '".$val."%' or audit_code like '%,".$val."%' or ";
		}
		$code_where = substr($code_where,0,strlen($code_where)-4).')';
	}else{
		$code_where = '';
	}
	// echo "<pre />";
	// print_r($code_where);exit;
	switch ($value['audit_ver']) {
		case 'A010101':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A020101':
			$str_ios = 'E:';
			$iso_where = 'A02';
			break;
		case 'A030102':
			$str_ios = 'S:';
			$iso_where = 'A03';
			break;

		default:
			break;
	}
	$arr_zhichi = $db->getAll("select hr.id,hr.name from `sp_hr` hr JOIN sp_hr_qualification hrq on hr.id=hrq.uid where hr.job_type not like'%1006%' and hrq.qua_type!='1000' and hrq.iso='".$iso_where."' and hrq.e_date>'".date('Y-m-d')."' and hrq.deleted=0 and hr.deleted=0");
	if( !empty($arr_zhichi) ){
		foreach ($arr_zhichi as $va) {
			$sql    = "select id from sp_hr_audit_code where uid=".$va['id']." and ".$code_where." and deleted=0";
			$sql_r  = $db->get_var($sql);
			if(!empty($sql_r)){
				$zhichi_all[] = $va['name'];
			}
		}
		$zhichi_all = array_unique($zhichi_all);

		//随机引出6人
		if(count($zhichi_all)>"6"){
			$aKeys = array_rand($zhichi_all,6);
			$aRand=array();  // 保存随机后的数组

			//组合随机数组
			foreach($aKeys as $v){
				// $aRand[$v]=$zhichi_all[$v];
				$str_ios .= $zhichi_all[$v]."、";
			}
		}else{
			foreach ($zhichi_all as $v) {
				$str_ios .= $v."、";
			}
		}
		if(strlen($str_ios)==2)$str_ios='';

		$zhichi .= $str_ios." ";
	}
}
/**专业支持人员**/

/**认证决定人员**/
$jueding = '';

foreach ($arr_audit as $value) {
	$arr_code = array();$code_where = '(';
	$arr_audit_code = explode(';',$value['audit_code']);

	foreach ($arr_audit_code as $val) {
		$code = explode('.',$val);
		$arr_code[] = $code[0];
	}
	

	if( !empty($arr_code) ){
		foreach ($arr_code as $val) {
			$code_where .= "audit_code like '".$val."%' or audit_code like '%,".$val."%' or ";
		}
		$code_where = substr($code_where,0,strlen($code_where)-4).')';
	}else{
		$code_where = '';
	}
	// echo "<pre />";
	// print_r($code_where);exit;
	switch ($value['audit_ver']) {
		case 'A010101':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A020101':
			$str_ios = 'E:';
			$iso_where = 'A02';
			break;
		case 'A030102':
			$str_ios = 'S:';
			$iso_where = 'A03';
			break;

		default:
			break;
	}
	$arr_jueding = $db->getAll("select hr.id,hr.name from `sp_hr` hr JOIN sp_hr_qualification hrq on hr.id=hrq.uid where hr.job_type like'%1006%' and hrq.qua_type='1000' and hrq.iso='".$iso_where."' and hrq.e_date>'".date('Y-m-d')."' and hrq.deleted=0 and hr.deleted=0");

	if( !empty($arr_jueding) ){
		foreach ($arr_jueding as $va) {
			$sql    = "select id from sp_hr_audit_code where uid=".$va['id']." and ".$code_where." and deleted=0";
			$sql_r  = $db->get_var($sql);
			if(!empty($sql_r)){
				$jueding_all[] = $va['name']."(大)" ;
			}else{
				$jueding_all[] = $va['name'] ;
			}
		}
		$jueding_all = array_unique($jueding_all);

		//随机引出6人
		if(count($jueding_all)>"6"){
			$aKeys = array_rand($jueding_all,6);
			$aRand=array();  // 保存随机后的数组

			//组合随机数组
			foreach($aKeys as $v){
				// $aRand[$v]=$jueding_all[$v];
				$str_ios .= $jueding_all[$v]."、";
			}
		}else{
			foreach ($jueding_all as $v) {
				$str_ios .= $v."、";
			}
		}
		if(strlen($str_ios)==2)$str_ios='';

		$jueding .= $str_ios." ";
	}
}

/**标准**/
foreach ($arr_audit as $v) {
	$audit_vers[] = $v['audit_ver'];
}
$sql_biaozhun   = "select audit_basis from `sp_settings_audit_vers` where `audit_ver` in('".implode('\',\'', $audit_vers)."')";
$biaozhun       = $db->getAll($sql_biaozhun);
$shenhebiaozhun = array();
foreach ($biaozhun as $value) {
	$shenhebiaozhun[] = $value['audit_basis'];
}
(string)$shenhebiaozhun = implode('、', $shenhebiaozhun);
/**标准**/

/**审核人士认证范围专业分类**/
$shenhezu = array();
$sql = "SELECT 
stat.name,stat.role,stat.uid,stat.qua_type,stat.taskBeginDate,stat.taskEndDate,stat.audit_code,stat.iso,hr.tel,hr.audit_job,hrq.qua_no 
from sp_task_audit_team stat 
left join sp_hr hr on stat.uid=hr.id 
left join `sp_hr_qualification` hrq ON hr.id=hrq.uid and stat.iso=hrq.iso and stat.qua_type=hrq.qua_type 
where stat.tid=".$tid." and hrq.qua_type in('01','02','03','04') and stat.deleted=0 and hr.deleted=0 and hrq.deleted=0";

$shenhezu = $db->getALl( $sql);
$role_name='';
foreach ($shenhezu as $key => $value) {
	if( $value['role']=='01' )$role_name=$value['name'];
	switch ($value['qua_type']) {
			case '01':
				$qua_type = '高级审核员';
				break;
			case '02':
				$qua_type = '审核员';
				break;
			case '03':
				$qua_type = '实习审核员';
				break;
			case '04':
				$qua_type = '技术专家';
				break;
			default:
				$qua_type = '其他';
				break;
		}
	$str_tmp = str_replace( '{shenhezu_name}',       $value['name'], $shenhezuStr );
	$str_tmp = str_replace( '{shenhezu_qua_no}',     $value['qua_no'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_tel}',        $value['tel'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_qua_type}',   $qua_type, $str_tmp );
	$str_tmp = str_replace( '{shenhezu_role}',      ($value['role']=='01')?'组长':'组员', $str_tmp );
	$str_tmp = str_replace( '{shenmezu_audit_job}', ($value['audit_job']==1)?'专职':(($value['audit_job']==0)?'兼职':'其他'), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_taskBeginDate}', substr($value['taskBeginDate'],0,16).' 至 '.substr($value['taskEndDate'],0,16), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$shenhezuString .= $str_tmp;
}



/**专业类别提示**/
foreach ($r_zyfl as  $value) {
	$ac = explode('；',$value['audit_code']);
	$uc = explode('；',$value['use_code']);

	$sql = "select iso,code,shangbao,msg from sp_settings_audit_code where iso='%s' and deleted=0";
	$sql = sprintf($sql,$value['iso']);
	$zylb= $db->getALl($sql);
	
	foreach ($zylb as $val) {
		if (in_array("$val[shangbao]",$ac) && in_array("$val[code]",$uc)) {
			switch ($val['iso']) {
			case 'A01':
				$l_qms .= $val['shangbao'].':'.$val['msg'].'; ';
				break;
			case 'A02':
				$l_ems .= $val['shangbao'].':'.$val['msg'].'; ';
				break;
			case 'A03':
				$l_ohsms .= $val['shangbao'].':'.$val['msg'].'; ';
				break;
			default:
				break;
			}
		
		}

	}
}
$l_qms = substr($l_qms, 0,-2);
$l_ems = substr($l_ems, 0,-2);
$l_ohsms = substr($l_ohsms, 0,-2);
//认证范围 删减条款
$ctscope=$db->getAll("SELECT scope,iso,exc_clauses_new FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
//判断如果sp_project 删减条款为空则取sp_contract_item的删减条款

foreach ($ctscope as $key => $value) 
{
	switch ($value['iso']) 
		{
			case 'A01':	
				if (empty($value['exc_clauses']))
				{
					$exc_clause_old = $db->get_row("SELECT iso,exc_clauses,scope FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND iso='A01' AND `deleted` = '0'");
					$scope.='Q:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='Q:'.$exc_clause_old['exc_clauses'].'  ';
				}else{
					$scope.='Q:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='Q:'.$value['exc_clauses_new'].'  ';
				} 
				break;
			case 'A02':
				if (empty($value['exc_clauses']))
				{
					$exc_clause_old = $db->get_row("SELECT iso,exc_clauses,scope FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND iso='A02' AND `deleted` = '0'");
					$scope.='E:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='E:'.$exc_clause_old['exc_clauses'].'  ';
				}else{
					$scope.='E:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='E:'.$value['exc_clauses_new'].'  ';
				}
				break;
			case 'A03':
				if (empty($value['exc_clauses']))
				{
					$exc_clause_old = $db->get_row("SELECT iso,exc_clauses,scope FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND iso='A03' AND `deleted` = '0'");
					$scope.='S:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='S:'.$exc_clause_old['exc_clauses'].'  ';
				}else{
					$scope.='S:'.$value['scope'].'/'.'  ';
					$exc_clauses1.='S:'.$value['exc_clauses_new'].'  ';
				}
				break;
			default:
				break;
		}

	
}

/**专业类别提示**/

$tishixinxi = $db->get_row("SELECT zizhi,fwbg_note,rrbg_note,tsxx_note,zyxx_note,qita_note FROM `sp_task` WHERE `id`='$tid'");

$sql_certnow = "select certno,cert_scope from sp_certificate where ct_id='$ct_id'";
$certnow = $db->getAll($sql_certnow);
foreach ($certnow as $value) {
	$cert_str .= $value['certno'].":".$value['cert_scope']."; ";
}
$cert_str = substr($cert_str, 0,-2);
$filename = '审表019 认证组织信息确认、变更反馈单('.$ep_name.').doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-019.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata);

$output = str_replace( '{shenhemudi}', $shenhemudi, $output );//审核目的
$output = str_replace( '{jueding}', $jueding, $output );//认证决定人员
$output = str_replace( '{zhichi}', $zhichi, $output );//支持人员

/**专业类别提示**/
$output = str_replace( '{l_qms}', $l_qms, $output );
$output = str_replace( '{l_ems}', $l_ems, $output );
$output = str_replace( '{l_ohsms}', $l_ohsms, $output );
/**专业类别提示**/

$output = str_replace( '{role_name}', $role_name, $output );
$output = str_replace( '{certno}', $certno['certno'], $output );
$output = str_replace( '{bg_addr}', $bg_addr, $output );
$output = str_replace( '{bg_addrcode}', $bg_addrcode, $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{cta_addrCode}', $cta_addrcode, $output );
$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check = unserialize($prod_check);
if(in_array(1,$prod_check)){//生产地址
	$output = str_replace( '{prod_addr}', $prod_addr, $output );
	$output = str_replace( '{prod_addrcode}', $prod_addrcode, $output );
}

$output = str_replace( '{ep_addr}', $ep_addr, $output );
$output = str_replace( '{ep_addrCode}', $ep_addrcode, $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{site_count}', $site_count, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $extra_ep['person_mail'], $output );
// echo $extra_ep['person_mail'];exit;
$output = str_replace( '{arr_work_code}', $arr_work_code, $output );
$output = str_replace( '{work_code}', $work_code, $output );
//echo $work_code;exit;
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{scope}',$scope,$output );
$output = str_replace( '{exc_clauses}',$exc_clauses1,$output );
$output = str_replace( '{capital}', $capital, $output );
$output = str_replace( '{tb_date}', substr($tb_date,0,16).' 至 '.substr($te_date,0,16), $output );
$output = str_replace( '{shenhebiaozhun}',$shenhebiaozhun , $output );//审核标准

$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{delegate}', $delegate, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{approval_date}', $approval_date, $output );

$output = str_replace( '{zizhi}'     ,$tishixinxi['zizhi'],       $output );//资质
$output = str_replace( '{wb_db}'     ,$wb_db,                     $output );//外包倒班
$output = str_replace( '{fwbg_note}' ,$tishixinxi['fwbg_note'],   $output );
$output = str_replace( '{rrbg_note}' ,$tishixinxi['rrbg_note'],   $output );
$output = str_replace( '{tsxx_note}' ,$tishixinxi['tsxx_note'],   $output );
$output = str_replace( '{zyxx_note}' ,$tishixinxi['zyxx_note'],   $output );
$output = str_replace( '{qita_note}' ,$tishixinxi['qita_note'],   $output );

$output = str_replace( '{cert_str}'  ,$cert_str,  $output );//有效证书编号及范围

$output = preg_replace("/\{.[^-]+?\}/", "", $output);

if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	if(!empty(getgp('dates'))){
		$filePath = CONF.'downs'.'/'.getgp('dates');
	}else{
		$filePath = CONF.'downs';
	}
	
	//没有目录创建目录
	if(!is_dir($filePath)) {
		mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filename) ){
		@unlink ($filePath.'/'.$filename); 
	}

	file_put_contents($filePath.'/'.$filename,$output);
	
	if( file_exists($filePath.'/'.$filename) ){
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
	
?>