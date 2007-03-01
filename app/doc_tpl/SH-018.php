<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );

$prod_arr=array("","生产地址","服务地址","运营地址");



$A01=$A02=$A03="□";

$tid = (int)getgp( 'tid' );

$ctid = (int)getgp( 'ct_id' );

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");

extract( $t_info, EXTR_SKIP );



$ep_info=load("enterprise")->get(array("eid"=>$eid));

extract( $ep_info, EXTR_SKIP );





//审核结束日期

$te_date = substr($te_date,0,10);


$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);

foreach($prod_check as $k){

	$prod_check_arr[]=$prod_arr[$k];



}

$prod_check=join("/",$prod_check_arr);

//项目编号

$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");

$str_xiangmu='';

foreach( $xiangmu as $v)

{

	$str_xiangmu .= $v['cti_code'].'/';

}

$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);



// 项目编号

$ct=$db->get_row("SELECT ct_code,ct_id,scope FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

extract( $ct, EXTR_SKIP );

//认证范围

$sql="SELECT scope from `sp_contract_item` WHERE ct_id=".$ct_id;

$query = $db->get_row( $sql);

extract( $query, EXTR_SKIP );



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



//体系 

$a=$db->getAll("select sp.iso from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");

$str_iso='';

// echo "<pre />";

// print_r($a);exit;

foreach( $a as $v)

{

	switch ($v['iso'])

   {

		case 'A10':

			$iso='知识产权管理体系';

			break;

		case 'A01':

			$iso='质量管理体系';

			break;

		case 'A02':

			$iso='环境管理体系';

			break;

		case 'A03':

			$iso='职业安全健康管理体系';

			break;

	}

	$str_iso.= $iso.',';

}

$str_iso= substr($str_iso,0,strlen($str_iso)-1);







//证书范围

$ct=$db->get_row("SELECT cert_scope FROM `sp_certificate` WHERE `ct_id` = '$ct_id' AND `deleted` = '0'");

extract( $ct, EXTR_SKIP );



//电话手机



// $query = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

// $audit_type=$audit_ver=$scope_q=$scope_e=$scope_o=$scope_n="";

// $cti_codes=$isos=array();

// while( $rt = $db->fetch_array( $query ) ){

// 	$rt[use_code]=explode("；",$rt[use_code]);

// 	$rt[use_code]=join("；",array_unique($rt[use_code]));

// 	$scope.=$rt[scope];

// 	$total.=$rt[total];

// 	$audit_code.=f_iso($rt[iso]).":".$rt[audit_code];

// 	$use_code.=f_iso($rt[iso]).":".$rt[use_code];

// 	$isos[$rt[iso]]=f_iso($rt[iso]);

// 	$audit_type.=f_iso($rt[iso]).f_audit_type($rt[audit_type])."/";

// 	$audit_ver.= $audit_ver_array[$rt[audit_ver]][audit_basis]."、";

// 	$cti_codes[$rt[cti_id]]=$rt[cti_code];

// 	$$rt['iso']="■";

// }



// $audit_type=trim($audit_type,"/");

// $audit_ver=trim($audit_ver,"、");

 

$leader = $auditors = array();

$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";

$query = $db->query( $sql);



while( $rt = $db->fetch_array( $query ) ){

	if( $rt['role']=="1001" ){

		$leader=$rt[name];

	} else {

		$auditors[$rt[uid]]=$rt['name'];

	} 

	

}

$audit_date=mysql2date( 'Y年n月j日',$tb_date)." 至 ".mysql2date( 'Y年n月j日',$te_date);

$filename = '审表018 末次会议记录('.$ep_name.').doc';

//读入模板文件 

$tpldata = readover( DOCTPL_PATH . 'doc/SH-018.xml' );



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

$output = str_replace( '{tb_date}', $tb_date, $output );

$output = str_replace( '{te_date}', $te_date, $output );

$output = str_replace( '{cti_code}', $str_xiangmu , $output );

$output = str_replace( '{iso}', $str_iso , $output );

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

