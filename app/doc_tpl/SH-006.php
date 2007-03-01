<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );

$br = '</w:t></w:r></w:p><w:p><w:pPr><w:rPr><w:rFonts w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="fareast"/></w:rPr><w:t>';

$prod_arr=array("","生产地址","服务地址","运营地址");



$tid     = (int)getgp( 'tid' );

$ctid    = (int)getgp( 'ct_id' );

$t_info  = $db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");

extract( $t_info, EXTR_SKIP );

$ep_info = load("enterprise")->get(array("eid"=>$eid));

extract( $ep_info, EXTR_SKIP );

$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);

foreach($prod_check as $k){

	$prod_check_arr[]=$prod_arr[$k];



}

$prod_check=join("/",$prod_check_arr);

//项目编号

$xiangmu    = $db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");

$str_xiangmu='';

foreach( $xiangmu as $v)

{

	$str_xiangmu .= $v['cti_code'].'/';

}

$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);



// 合同编号

$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

extract( $ct, EXTR_SKIP );





$enterprises = $db->get_row("SELECT areacode,ep_addr,person FROM `sp_enterprises` where eid=$eid");

extract( $enterprises, EXTR_SKIP );



$query      = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

$audit_type = $audit_ver=$scope_q=$scope_e=$scope_o=$scope_n="";

$cti_codes  = $isos=array();

while( $rt = $db->fetch_array( $query ) ){

	$scope.=$rt[scope];

	$audit_code.=f_iso($rt[iso]).":".$rt[audit_code];

	$use_code.=f_iso($rt[iso]).":".$rt[use_code];

	$isos[$rt[iso]]=f_iso($rt[iso]);

	$audit_type.=f_iso($rt[iso]).f_audit_type($rt[audit_type])."/";

	$audit_ver.= $audit_ver_array[$rt[audit_ver]][audit_basis]."、";

	$cti_codes[$rt[cti_id]]=$rt[cti_code];

	$_audit_type[$rt[ct_id]]=$rt['audit_type'];

	

}



$audit_type = trim($audit_type,"/");

$audit_ver  = trim($audit_ver,"、");



//签字日期

$tb_year = substr($tb_date, 0,4);

$tb_mon  = substr($tb_date, 5,2);

$tb_mon  = ltrim($tb_mon,"0");

$tb_day  = substr($tb_date, 8,2);

$tb_day  = ltrim($tb_day,"0");

$tb_date = $tb_year."年".$tb_mon."月".$tb_day."日";

$te_year = substr($te_date, 0,4);

$te_mon  = substr($te_date, 5,2);

$te_mon  = ltrim($te_mon,"0");

$te_day  = substr($te_date, 8,2);

$te_day  = ltrim($te_day,"0");

$te_date = $te_year."年".$te_mon."月".$te_day."日";

// echo $te_date;exit;



//多现场信息

$_audit_type=array_unique($_audit_type);

$body="";

$i=1;

foreach($_audit_type as $_ct_id=>$_v){

	if($_v=='1004')

		$type="jy";

	elseif($_v=='1005')

		$type="je";

	elseif(in_array($_v,array("1002","1003","1007")))

		$type="cs";

	$_query=$db->query("SELECT * FROM `sp_contract_num` WHERE `ct_id` = '$_ct_id' AND $type IS NOT NULL");

	while($_rt=$db->fetch_array($_query)){

		if($_rt['type']=='1'){//子公司

			$_e_info=$db->get_row("SELECT ep_name,ep_addr FROM `sp_enterprises` WHERE `eid` = '$_rt[eid]'");

			$body.="名称$i ：$_e_info[ep_name] 地址$i ：$_e_info[ep_addr]   审核范围：$_rt[scope] ".$br;

		}else{//分场所

			$_es_info=$db->get_row("SELECT es_name,es_addr FROM `sp_enterprises_site` WHERE `es_id` = '$_rt[eid]'");

			$body.="名称$i ：$_es_info[es_name] 地址$i ：$_es_info[es_addr]   审核范围：$_rt[scope] ".$br;

		}

		$i++;

	}

}



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



$filename = '审表006 审核员规范声明('.$ep_name.').doc';

//读入模板文件 

$tpldata = readover( DOCTPL_PATH . 'doc/SH-006.xml' );



//print_r($tab);

//企业信息部分

$arr_search = array('<','>','&','\'','"');

$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');

$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );

$output = str_replace( '{ep_addr}', $ep_addr, $output );

$output = str_replace( '{ep_fax}', $ep_fax, $output );

$output = str_replace( '{ct_code}', $ct_code, $output );

$output = str_replace( '{areacode}', $areacode, $output );

$output = str_replace( '{ep_addr}', $ep_addr, $output );

$output = str_replace( '{person}', $person, $output );

$output = str_replace( '{person_tel}', $ep_phone."/".$person_tel, $output );

$output = str_replace( '{name_zh}', $name_zh, $output );

$output = str_replace( '{zige_zh}', $zige_zh, $output );

$output = str_replace( '{sex_zh}' , $sex_zh, $output );

$output = str_replace( '{tel}'    , $tel, $output );

$output = str_replace( '{cti_code}', $str_xiangmu , $output );

$output = str_replace( '{tb_date}', $tb_date , $output );

$output = str_replace( '{te_date}', $te_date , $output );

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