<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$prod_arr=array("","生产地址","服务地址","运营地址");

$A01   =$A02=$A03="□";
$tid   = (int)getgp( 'tid' );
$ctid  = (int)getgp( 'ct_id' );
$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );

$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);
foreach($prod_check as $k){
	$prod_check_arr[]=$prod_arr[$k];
}
$prod_check=join("/",$prod_check_arr);
//行业
$adder=$db->get_row("SELECT prod_addr,prod_addrcode FROM `sp_enterprises` WHERE `eid` = '$eid' and `deleted` = '0'");
extract( $adder, EXTR_SKIP );

$a=substr($tb_date,0,10);
$b=substr($te_date,0,10);
// 项目编号
$ct=$db->get_row("SELECT ct_code,ct_id,scope FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//组长
$leader=$db->get_var("SELECT name FROM `sp_task_audit_team`  WHERE `tid` = '$tid' and `role`='01' and `deleted`='0'");
extract( $leader, EXTR_SKIP );

//范围
$fw=$db->getAll("SELECT scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");
// echo "<pre>";
// print_r($fw);exit;
foreach ($fw as $key => $value) {
	switch ($value['iso']) {
		case 'A01':	
			$scope.='Q:'.$value[scope].'/'.'  ';
			break;
		case 'A02':
			$scope.='E:'.$value[scope].'/'.'  ';
			break;
		case 'A03':
			$scope.='S:'.$value[scope];
			break;
		default:
			break;
	}
}

//审核类型
$type_Q=$db->getAll("select audit_type from `sp_project` where `tid`='$tid' AND `iso`='A01'");
$audit_type_Q='';
foreach( $type_Q as $w_q)
{
	switch ($w_q['audit_type'])
   {
   		case '1002':
		case '1003':
			$audit_type='初审';
			break;
		case '1004':
			$audit_type='监督1';
			break;
		case '1005':
			$audit_type='监督2';
			break;
		case '1007':
			$audit_type='再认证';
			break;
	}
	$audit_type_Q.= 'QMS:'.$audit_type;
}



$type_E=$db->getAll("select audit_type from `sp_project` where `tid`='$tid' AND `iso`='A02'");
$audit_type_E='';
foreach( $type_E as $w_e)
{
	switch ($w_e['audit_type'])
   {
   		case '1002':
		case '1003':
			$audit_type='初审';
			break;
		case '1004':
			$audit_type='监督1';
			break;
		case '1005':
			$audit_type='监督2';
			break;
		case '1007':
			$audit_type='再认证';
			break;
	}
	$audit_type_E.= 'EMS:'.$audit_type;
}


$type_S=$db->getAll("select audit_type from `sp_project` where `tid`='$tid' AND `iso`='A03'");

$audit_type_S='';
foreach( $type_S as $w_s)
{
	switch ($w_s['audit_type'])
   {
   		case '1002':
		case '1003':
			$audit_type='初审';
			break;
		case '1004':
			$audit_type='监督1';
			break;
		case '1005':
			$audit_type='监督2';
			break;
		case '1007':
			$audit_type='再认证';
			break;
	}
	$audit_type_S.= 'OHSMS:'.$audit_type;
}

// //结合度
$jhd=$db->get_row("SELECT * FROM `sp_contract` WHERE `ct_id` = '$ctid'");
extract( $jhd, EXTR_SKIP );
$gltx_jhd   = (empty($gltx_jhd))  ? '' : $gltx_jhd."%" ;
$xtgc_jhd   = (empty($xtgc_jhd))  ? '' : $xtgc_jhd."%" ;
$glps_jhd   = (empty($glps_jhd))  ? '' : $glps_jhd."%" ;
$ns_jhd     = (empty($ns_jhd))    ? '' : $ns_jhd."%" ;
$fzhmb_jhd  = (empty($fzhmb_jhd)) ? '' : $fzhmb_jhd."%" ;
$gjjz_jhd   = (empty($gjjz_jhd))  ? '' : $gjjz_jhd."%" ;
$glzz_jhd   = (empty($glzz_jhd))  ? '' : $glzz_jhd."%" ;
$sum_jhd    = (empty($sum_jhd))   ? '' : $sum_jhd."%" ;

// $audit_type=trim($audit_type,"/");



 //项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);
//审核组信息
$sql_sh    = "select * from sp_task_audit_team stat left join sp_hr_qualification hrq on stat.uid=hrq.uid left join sp_hr hr on stat.uid=hr.id where stat.tid=".$tid;
$query_sh  = $db->query($sql_sh);
while( $rt = $db->fetch_array( $query_sh ) ){
	switch ($rt['qua_type']) {
			case '02'://审核员
				$zige = '审核员';
				break;
			case '03'://实习审核员
				$zige = '实习审核员';
				break;
			case '04'://技术专家
				$zige = '技术专家';
				break;
			case '1001'://验证
				$zige = '验证';
				break;
		}
	switch ( $rt['role'] ) {
		case '1001':
				$name_zh = $rt['name'];
				$zige_zh = $zige;
				$sex_zh  = ($rt['sex']==1)?'男':'女';
				$tel     = $rt['tel'];
			break;
		
		default:
			$zuyuan[] = array(
						 'name' => $rt['name']
						,'zige' => $zige
						,'sex'  => ($rt['sex']==1)?'男':'女'
						,'tel'  => $rt['tel']
				);
			break;
	}
}

//证书范围
$ct=$db->get_row("SELECT cert_scope FROM `sp_certificate` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//电话手机

$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
//$audit_type=$audit_ver=$scope_q=$scope_e=$scope_o=$scope_n="";
$cti_codes=$isos=array();
while( $rt = $db->fetch_array( $query ) )
{
	$total.=$rt[total];

	if(getgp('banben')=='1')//老版本专业代码
	{
		(!empty($rt['pd_audit_code_2017']))?$rt['audit_code_2017']=$rt['pd_audit_code_2017']:$rt['audit_code_2017']=$rt['audit_code_2017'];
		if(!empty($rt['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $rt['audit_code_2017']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$audit_code    .= f_iso($rt['iso']).":".$codeims;;
		}
		(!empty($rt['pd_use_code_2017']))?$rt['use_code_2017']=$rt['pd_use_code_2017']:$rt['use_code_2017']=$rt['use_code_2017'];
		$use_code      .= f_iso($rt['iso']).":".join("；",array_unique(explode("；",$rt['use_code_2017'])));

	}else{//新版本专业代码
		(!empty($rt['pd_audit_code']))?$rt['audit_code']=$rt['pd_audit_code']:$rt['audit_code']=$rt['audit_code'];
		if(!empty($rt['audit_code']))
		{
			$codeList  = array_filter(explode('；', $rt['audit_code']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$audit_code    .= f_iso($rt['iso']).":".$codeims;;
		}
		(!empty($rt['pd_use_code']))?$rt['use_code']=$rt['pd_use_code']:$rt['use_code']=$rt['use_code'];
		$use_code      .= f_iso($rt['iso']).":".join("；",array_unique(explode("；",$rt['use_code'])));
	}


	$isos[$rt[iso]] = f_iso($rt[iso]);
	//$audit_type.=f_iso($rt[iso]).f_audit_type($rt[audit_type])."/";
	$audit_ver.= $audit_ver_array[$rt[audit_ver]][audit_basis]."、";
	$cti_codes[$rt[cti_id]]=$rt[cti_code];
	$$rt['iso']="■";
}

$audit_ver=trim($audit_ver,"、");
 

$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);
$filename = '审表017 管理体系审核报告('.$ep_name.').doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-017.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}',         $ep_name,         $tpldata);
$output = str_replace( '{ep_addr}',         $ep_addr,         $output );
$output = str_replace( '{cta_addrcode}',    $cta_addrcode,    $output );
$output = str_replace( '{delegate}',        $delegate,        $output );
$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{person}',          $person,          $output );
$output = str_replace( '{person_tel}',      $person_tel,      $output );
$output = str_replace( '{person_email}',    $person_mail,     $output );
$output = str_replace( '{scope}',           $scope,           $output );
$output = str_replace( '{name_zh}',         $name_zh,         $output );
$output = str_replace( '{zige_zh}',         $zige_zh,         $output );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{leader}', $leader, $output );
$output = str_replace( '{cert_scope}', $cert_scope, $output );
$output = str_replace( '{tb_date}', $a, $output );
$output = str_replace( '{te_date}', $b, $output );
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
$output = str_replace( '{audit_basis}', $audit_ver , $output );
$output = str_replace( '{prod_addr}', $prod_addr, $output );
$output = str_replace( '{prod_addrcode}', $prod_addrcode, $output );
//结合度
  // print_r($gltx_jhd);exit;
 $output = str_replace( '{gltx_jhd}', $gltx_jhd, $output );
 $output = str_replace( '{xtgc_jhd}', $xtgc_jhd, $output );
 $output = str_replace( '{glps_jhd}', $glps_jhd, $output );
 $output = str_replace( '{ns_jhd}', $ns_jhd, $output );
 $output = str_replace( '{fzhmb_jhd}', $fzhmb_jhd, $output );
 $output = str_replace( '{gjjz_jhd}', $gjjz_jhd, $output );
 $output = str_replace( '{glzz_jhd}', $glzz_jhd, $output );
 $output = str_replace( '{sum_jhd}', $sum_jhd, $output );
 $output = str_replace( '{audit_Q}', $audit_type_Q, $output );
 $output = str_replace( '{audit_E}', $audit_type_E, $output );
 $output = str_replace( '{audit_S}', $audit_type_S, $output );

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