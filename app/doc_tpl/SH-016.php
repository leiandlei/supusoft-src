<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
// echo "<pre />";
// print_r($ep_name);exit;
$tid    = (int)getgp( 'tid' );
$ctid   = (int)getgp( 'ct_id' );
$t_info = $db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );



$a=substr($tb_date,0,10);
$b=substr($te_date,0,10);

$query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$audit_type="";
$zhuanjia=array();
while( $rt = $db->fetch_array( $query ) ){
	$audit_type .=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
	$zhuanjia[]  =$rt['zy_name'];
}
$zhuanjia=array_unique($zhuanjia);
//结合度
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

// if (count($iso)==1) {
// 	$gltx_jhd  ='';
// 	$xtgc_jhd  ='';
// 	$glps_jhd  ='';
// 	$ns_jhd    ='';
// 	$fzhmb_jhd ='';
// 	$gjjz_jhd  ='';
// 	$glzz_jhd  ='';
// 	$sum_jhd   ='';
// }else{
// 	$gltx_jhd  =$gltx_jhd."%";
// 	$xtgc_jhd  =$xtgc_jhd."%";
// 	$glps_jhd  =$glps_jhd."%";
// 	$ns_jhd    =$ns_jhd."%";
// 	$fzhmb_jhd =$fzhmb_jhd."%";
// 	$gjjz_jhd  =$gjjz_jhd."%";
// 	$glzz_jhd  =$glzz_jhd."%";
// 	$sum_jhd   =$sum_jhd."%";
// }

//审核依据
$shenheyiju=$db->getAll("select ssav.audit_basis from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_shenheyiju='';
foreach( $shenheyiju as $v)
{
	$str_shenheyiju .= $v['audit_basis'].', ';
}
$str_shenheyiju = substr($str_shenheyiju,0,strlen($str_shenheyiju)-1);

 //合同编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
$ct_code=$ct['ct_code'];

//范围
$fw=$db->getAll("SELECT scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");

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
// print_r(count($value['iso']));exit;//zhanghao

//行业
$hy=$db->get_row("SELECT industry,prod_addr,prod_addrcode,ep_name FROM `sp_enterprises` WHERE `eid` = '$eid'");
extract( $hy, EXTR_SKIP );
// print_r($ep_name);exit;
$sql = "SELECT name FROM `sp_settings` WHERE `type` = 'industry' and code in(".substr(str_replace('；',',',$hy['industry']),0,strlen(str_replace('；',',',$hy['industry']))-1).")";
$query = $db->query($sql);
$industry = '';
while ( $rts = $db->fetch_array($query)){
	$industry .= $rts['name'].',';
}
$industry = substr($industry,0,strlen($industry)-1);

//多现场
$guding=$db->get_var("SELECT count(es_type) FROM `sp_enterprises_site` WHERE `eid` = '$eid' and es_type=1000");
extract( $guding, EXTR_SKIP );
//临时
$linshi =$db->get_var("SELECT count(es_type) FROM `sp_enterprises_site` WHERE `eid` = '$eid' and es_type=1001");
extract( $linshi, EXTR_SKIP );
//组长
$leader=$db->get_var("SELECT name FROM `sp_task_audit_team`  WHERE `tid` = '$tid' and `role`='01' and `deleted`='0'");
extract( $leader, EXTR_SKIP );
//项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);
// echo "<pre />";
// print_r($str_xiangmu);exit;
//附加信息
$sql = 'select * from `sp_metas_ep` where ID='.$eid.' and deleted=0';
$extra = $db->getAll($sql);
foreach ($extra as $value) {
	$extra_ep[$value['meta_name']] = $value['meta_value'];
}

// while( $rt = $db->fetch_array( $query ) ){
// 	if( $rt['role']=="1001" ){
// 		$leader=$rt['name'];
// 	} else {
// 		$auditors[$rt['uid']]=$rt['name'];
// 	} 
	
// }
$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);

//专业代码
$zhuanyedaima=$db->getAll("select iso,audit_code,audit_code_2017 from `sp_contract_item` where `ct_id`='$ctid'");
foreach ($zhuanyedaima as $v) {
	if(!empty($v['audit_code_2017']))
	{
		$codeList  = array_filter(explode('；', $v['audit_code_2017']));
		$codeims   = '';

		foreach($codeList as $code)
		{

			if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		}
		$v['audit_code_2017'] = $codeims;
	}
	if(!empty($v['audit_code']))
	{
		$codeList  = array_filter(explode('；', $v['audit_code']));
		$codeims   = '';

		foreach($codeList as $code)
		{

			if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		}
		$v['audit_code'] = $codeims;
	}
	if(getgp('banben')=='1')//旧版本专业代码
	{
		switch ($v['iso']) 
		{
			case 'A01':
				$audit_code="QMS:".$v['audit_code_2017']."   ";
				break;

			case 'A02':
				$audit_code.="EMS:".$v['audit_code_2017']."   ";
				
				break;

			case 'A03':
				$audit_code.="OHSMS:".$v['audit_code_2017']."   ";
				break;
			
			default:
				break;
		}
	}else{ //新版本专业代码
		switch ($v['iso']) 
		{
			case 'A01':
				$audit_code="QMS:".$v['audit_code']."   ";
				break;

			case 'A02':
				$audit_code.="EMS:".$v['audit_code']."   ";
				
				break;

			case 'A03':
				$audit_code.="OHSMS:".$v['audit_code']."   ";
				break;
			
			default:
				break;
		}
	}
	
}
// //体系
// $tixi=$db->getAll("select iso,use_code from `sp_contract_item` where `ct_id`='$ctid'");
// foreach ($tixi as $tixiv) {
// 	switch ($tixiv['iso']) {
// 		case 'A01':
// 			$use_code="QMS:".$tixiv['use_code']." ";
// 			break;

// 		case 'A02':
// 			$use_code.="EMS:".$tixiv['use_code']." ";
// 			break;

// 		case 'A03':
// 			$use_code.="OHSMS:".$tixiv['use_code']."";
// 			break;
		
// 		default:
// 			break;
// 	}
// }
$filename = '审表016 一阶段审核报告('.$ep_name.').doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-016.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

 $output = str_replace( '{ep_name}', $ep_name, $tpldata );
// echo "<pre />";
// print_r($output);exit;
 // $output = str_replace( '{ep_names}', $ep_name, $output );

 $output = str_replace( '{leader}', $leader, $output );
 $output = str_replace( '{ct_code}', $ct_code, $output );
 $output = str_replace( '{ep_addr}', $ep_addr, $output );
 $output = str_replace( '{prod_addr}', $prod_addr, $output );
 $output = str_replace( '{prod_addrcode}', $prod_addrcode, $output );
 $output = str_replace( '{person}', $person, $output );
 $output = str_replace( '{person_tel}', $person_tel, $output );
 $output = str_replace( '{person_mail}', $extra_ep['person_mail'], $output );
 $output = str_replace( '{ep_fax}', $ep_fax, $output );
 $output = str_replace( '{scope}', $scope, $output );
 $output = str_replace( '{guding}', $guding, $output );
 $output = str_replace( '{linshi}', $linshi, $output );
 // 体系运行时间
 $output = str_replace( '{start_date}',$jhd['start_date'], $output );
//结合度
// print_r(count($v['iso']));exit;
 $output = str_replace( '{gltx_jhd}', $gltx_jhd, $output );
 $output = str_replace( '{xtgc_jhd}', $xtgc_jhd, $output );
 $output = str_replace( '{glps_jhd}', $glps_jhd, $output );
 $output = str_replace( '{ns_jhd}', $ns_jhd, $output );
 $output = str_replace( '{fzhmb_jhd}', $fzhmb_jhd, $output );
 $output = str_replace( '{gjjz_jhd}', $gjjz_jhd, $output );
 $output = str_replace( '{glzz_jhd}', $glzz_jhd, $output );
 $output = str_replace( '{sum_jhd}', $sum_jhd, $output );
 
 //项目编号
 $output = str_replace( '{cti_code}', $str_xiangmu , $output );
 $output = str_replace( '{audit_basis}', $str_shenheyiju , $output );
 ////邮编
 $output = str_replace( '{cta_addrcode}', $cta_addrcode, $output );
 $output = str_replace( '{tb_date}', $a, $output );
 $output = str_replace( '{te_date}', $b, $output );
 $output = str_replace( '{ep_amount}', $ep_amount, $output );
 $output = str_replace( '{industry}', $industry, $output );
 $output = str_replace( '{audit_code}', $audit_code, $output );
 // echo $hy['ep_name'];exit;
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