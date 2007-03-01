<?php
set_time_limit(0);

/*
 *---------------------------------------------------------------
 * 导出59项月报
 * 附加说明
 *---------------------------------------------------------------
 */
require_once ABSPATH.'/data/cache/audit_ver.cache.php';
$new_data = array();
$a64 = $begindate  = getgp('s_date');//58.取数开始日期
$a65 = $enddate = getgp('e_date');//59.取数截止日期
$enddate.=" 23:59:59";
//$data_arr = array();
$pids = array();
$tids = array();

function get_audit_type($audit_type){
	if($audit_type=='1001'||$audit_type=='1002'||$audit_type=='1003'){
		return '01';
	}else if(in_array($audit_type,array('1004', '1005','1006','1011','1012','1013','1014','1015','1016','1017','1018','1019'))){
		return '03';
	}else if($audit_type=='1007'){
		return '02';
	}else{
		return '04';
	}
}

function get_audit_type_auditor($audit_type){
	if($audit_type=='1002'){
		return '0101';//初审一阶段
	}else if($audit_type=='1003'){
		return '0102';
	}else if($audit_type=='1007'){
		return '0201';
	}else if($audit_type=='1004' || $audit_type=='1005'){
		return '0301';
	}else{
		return '04';//变更在特殊监管  04
	}
}

function get_audit_num($audit_type){
	$num=0;
	switch($audit_type){
		case '1004':$num=1;break;
		case '1005':$num=2;break;
		case '1006':$num=3;break;
		case '1011':$num=4;break;
		case '1012':$num=5;break;
		case '1013':$num=6;break;
		case '1014':$num=7;break;
		case '1015':$num=8;break;
		case '1016':$num=9;break;
		case '1017':$num=10;break;
		case '1019':$num=11;break;
		case '1020':$num=12;break;
		case '1021':$num=13;break;
		case '1022':$num=14;break;
		default :$num=0;break;
	
	
	}
	return $num;



}
function get_code($code){
	$code=str_replace(array(";",",","，"," "),"；",$code);
	$codes=explode("；",$code);
	foreach($codes as $k=>$v){
		$codes[$k]=substr($v,0,8);

	}
	$codes=array_unique($codes);
	return join("；",$codes);
}


function do_excel($data_cert,$data_auditor,$energy,$title){
	require_once ABSPATH.'/include/Excel/PHPExcel.php';
	// require_once ABSPATH.'/include/Excel/PhpExcel/Writer/Excel2007.php';
	require_once ABSPATH.'/include/Excel/PhpExcel/Writer/Excel5.php';
	include_once ABSPATH.'/include/Excel/PhpExcel/IOFactory.php'; 
	//$objExcel = new PHPExcel(); 
	$objReader = new PHPExcel_Reader_Excel5;
	$objExcel = $objReader->load(DATA_DIR."report_tpl.xls");
	$objExcel->setActiveSheetIndex(0);  
	// 设置工作薄名称
	$objActSheet = $objExcel->getActiveSheet();
	$i=8;
	foreach($data_cert as $_val){
		// $objActSheet->setCellValueExplicit('AF'.$i,$result[1],PHPExcel_Cell_DataType::TYPE_STRING);
        // $objActSheet->getStyle('AF'.$i)->getNumberFormat()->setFormatCode("@");
		// $j=$i+1;
		$k="C";
		foreach($_val as $val){
			$objActSheet->setCellValueExplicit($k.$i,$val,PHPExcel_Cell_DataType::TYPE_STRING);
			$k++;
			}
		$i++;
	}
		// $objActSheet->getStyle('AF')->getNumberFormat()->setFormatCode(PHPExcel_Cell_DataType::TYPE_STRING);

	
	// $msgWorkSheet = new PHPExcel_Worksheet($objExcel, '获证组织基本信息表_附表'); //创建一个工作表
	// $objExcel->addSheet($msgWorkSheet); //插入工作表
	/* $objExcel->setActiveSheetIndex(1); //切换到新创建的工作表
	$objActSheet = $objExcel->getActiveSheet();
	$i=8;
	foreach($data_auditor as $_val){
		$k="C";
		foreach($_val as $val){
			$objActSheet->setCellValueExplicit($k.$i,$val,PHPExcel_Cell_DataType::TYPE_STRING);
			$k++;
			}
		$i++;
			
	}
	
	$objExcel->setActiveSheetIndex(2); //切换工作表
	$objActSheet = $objExcel->getActiveSheet();
	$i=8;
	foreach($energy as $_val){
		$k="C";
		foreach($_val as $val){
			$objActSheet->setCellValueExplicit($k.$i,$val,PHPExcel_Cell_DataType::TYPE_STRING);
			$k++;
			}
		$i++;
			
	} */
	
	/* $filename = date("Y-m-d").'-'.$title.'.xls';
	ob_end_clean();//清除缓冲区,避免乱码
	header("Content-Type: application/force-download");  
	header("Content-Type: application/octet-stream");  
	header("Content-Type: application/download");  
	header('Content-Disposition:inline;filename="'.iconv( 'UTF-8', 'gbk', $filename ).'"');  
	header("Content-Transfer-Encoding: binary");  
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
	header("Pragma: no-cache");  
	$objWriter->save('php://output');   */
	$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
	$savedir="data/excel/".date("Y-m-d")."-c.xls";
	// if(file_exists($savedir))
		// @unlink($savedir);
	$objWriter->save($savedir);
	return $savedir;

}

$arr1 =get_option("zdep_id");
$arr2 ="北京国金恒信管理体系认证有限公司";





$temp_arr=array();

//变更信息
$sql = "SELECT z.cert_name,z.status,z.s_date,z.e_date,z.cert_scope,z.main_certno ,z.first_date ,z.change_date,z.certno,z.cert_addr,z.mark,z.audit_code,z.is_change,z.old_certno,z.old_cert_name,z.change_type,z.cert_name_e,z.ORG_EN_BORDER,
c.cg_type_report,c.pass_date,c.cgs_date,c.cge_date,c.cg_reason,c.cg_pid as pid,c.cg_type,
e.ep_name,e.ep_name_e,e.work_code,e.statecode,e.ep_addrcode,e.areacode,e.ctfrom,e.ep_oldname,e.eid,e.ep_amount,e.capital,e.currency,e.nature,e.delegate,e.industry,e.union_count,e.prod_addr,
p.iso,p.audit_type,p.audit_ver,p.ct_id,p.cti_id,p.ct_code,p.st_num,p.tid,p.comment_a_name,p.comment_b_name,p.sp_date
FROM `sp_certificate_change` c LEFT JOIN sp_certificate z on z.id=c.zsid LEFT JOIN sp_enterprises e on e.eid=z.eid left join sp_project p on p.id=c.cg_pid
WHERE c.deleted='0' AND c.zsid<>0 and c.cgs_date >= '$begindate' and c.cgs_date <= '$enddate' ORDER BY c.id DESC ";

//and c.cg_type_report IN ('04','05','06','97')
$res = $db->query($sql);
while($row=$db->fetch_array($res)){
	//$row=chk_arr($row);
	$cti_info = $db->get_row("select total,renum,risk_level from sp_contract_item where cti_id='$row[cti_id]' ");
	$task_info = $db->get_row("select tb_date,te_date from sp_task t  where t.id='$row[tid]' and deleted=0");
	if(!$task_info[tb_date]){
		$task_info = $db->get_row("select tb_date,te_date from sp_task  where eid='$row[eid]' and deleted=0 and iso like '%$row[iso]%' and te_date<'$enddate' order by te_date desc");
		if(!$row[comment_a_name]){
			$p_info=$db->get_row("SELECT st_num,comment_a_name,comment_b_name,sp_date FROM `sp_project` WHERE `iso` = '$row[iso]' AND tid='$task_info[id]' and deleted=0");
			$row[st_num]=$p_info[st_num];
			$row[comment_a_name]=$p_info[comment_a_name];
			$row[comment_b_name]=$p_info[comment_b_name];
			$row[sp_date]=$p_info[sp_date];
		}
	}
	$arr=array("1"=>$arr1,"2"=>$arr2);
	$arr[3] = $row['cert_name']; 		//
	$arr[4] = $row['cert_name_e']; 	//组织机构英文名称
	$arr[5] = $row['ep_oldname'];	//组织机构原名称
	$arr[6] = $row['work_code'];		//6.组织机构代码
	$arr[6] = str_replace("-","",$arr[6]);
	$arr[7] = rtrim($row['industry'],"；");	//8.所属行业
	$arr[8] = $row['statecode'];	//10.组织机构所在地方代码
	$arr[9] = $row['areacode'];		//9.组织机构所在地区代码
	$arr[10] = $row['cert_addr'];	//11.证书地址
	$arr[11] = $row['ep_addrcode'];	//12.组织邮政编码
	$arr[12] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_phone' and ID='$row[eid]' ");			//13.组织联系电话
	$arr[13] = $db->get_var("select meta_value from sp_metas_ep where used='enterprise' and meta_name='ep_fax' and ID='$row[eid]' "); 			//14.组织联系传真
	$arr[14] = $row['delegate']; 	//15.组织法定代表人
	$arr[15] = $row['nature']; 		//16.组织性质代码
	$arr[16] = $row['capital']; 		//17.组织注册资本
	$arr[17] = $row['currency'];		//18.组织注册资本币种
	$arr[18] = $row['ep_amount']; 	//19.组织组织人数
	$arr[19] = $cti_info['total']; 	//20.体系人数
	$arr[20] = ($row['first_date'] == '0000-00-00')?$row[s_date]:$row['first_date']; 	//21.初次获证日期
	$arr[21] = $row['certno']; 		//22.证书号码
	$row['audit_ver']=='A090103' && $row['audit_ver']='A090102';
	$arr[22] = $row['audit_ver'];	//26.认证项目代码
	$arr[23] = get_code($row['audit_code']);	
	$row['iso']=='A09' && $arr[23]='2.1';	
	$arr[24] = $audit_ver_array[$row[audit_ver]][audit_basis];//24、认证依据
	$arr[25] = $row[union_count]>0?"1":"0"; 					//25.是否多现场
	$arr[26] = $row[prod_addr]; //25.多现场名称地址多个用全角分号分隔。
	/*	
	if($arr[25]=='1'){
		$site_query=$db->query("SELECT * FROM `sp_enterprises` WHERE `parent_id` = '$row[eid]' AND `deleted` = '0'");
		while($r=$db->fetch_array($site_query)){
			$arr[26].=$r[ep_name]
		
		}
	
	
	}
	*/
	$arr[27] = $row['cert_scope'];	//27.认证覆盖范围
	$arr[28] = ''; 					//28、EC9000证书对应的QMS覆盖范围
	if($row[audit_ver]=="A010201")
		$arr[28] = $row['cert_scope'];
	$arr[29] = ''; 					//29、获证组织能源管理体系边界
	if($row[iso]='A09')
		$arr[29]=$db->get_var("SELECT ORG_EN_BORDER FROM `sp_energy` WHERE `pid` = '$row[pid]'");
	$arr[30] = '05'; 					//30、认证审核活动代码 变更 04
	$arr[31] = $cti_info[renum]; 			//31.再认证次数
	$arr[32] = get_audit_num($row[audit_type]); 					//32、监督次数
	$arr[33] = substr($task_info['tb_date'],0,10); 	//33.审核开始日期
	$arr[34] = substr($task_info['te_date'],0,10);	//34.审核结束日期
	$arr[35] = $row[st_num];//35、审核人日数
	$arr[36] ="01";//36、结合审核类型
	$cti_ids=$db->get_col("SELECT cti_id FROM `sp_project` WHERE `tid` = '$row[tid]' AND `deleted` = '0'");
	if(count(array_unique($cti_ids))==2)
		$arr[36] ="02";
	if(count(array_unique($cti_ids))==3)
		$arr[36] ="03";
	if(count(array_unique($cti_ids))>3)
		$arr[36] ="04";
		
	$arr[37] = $row[comment_a_name]."；".$row[comment_b_name];		//评定人员
	$arr[37] = trim($arr[37],"；");
	$arr[38] = $row[sp_date];//38、认证决定日期
	$arr[39] = $row['s_date'];		//证书发证日期
	$arr[40] = $row['e_date'];		//证书到期日期
	$arr[41] = $row['status'];	//证书状态
	$arr[42] = '';					//暂停原因
	$arr[43] = '';					//暂停开始时间
	$arr[44] = '';					//暂停结束时间
	$arr[45] = '';					//撤销原因
	$arr[46] = '';					//撤销日期
	if($row['status']=='02'){
		$arr[42]=$row[cg_reason];
		$arr[43]=$row[cgs_date];
		$arr[44]=$row[cge_date];
	}
	if($row['status']=='03'){
		$arr[45]=$row[cg_reason];
		$arr[46]=$row[cgs_date];
	}
	$arr[47] = '0';					//47、是否是子证书
	$arr[48] = '';					//48、主认证证书号
	if($row[main_certno]){
		$arr[47] = '1';
		$arr[48] = $row[main_certno];
	}
	$arr[49] = $row['cgs_date'];		//变更日期
	$arr[50] = $row[cg_type_report];					//31.变更类型代码
	$arr[51] = $row[is_change];					//33.shi否换证
	$arr[52] = ($row[change_date]=="0000-00-00")?"":$row[change_date]; 
	$arr[53] = $row[change_type]; 					//34.换证原因
	$arr[54] = $row[old_cert_name]; 					//35.原证书注册号
	$arr[55] = $row[old_certno]; 					//36.原证书颁发机构
	if(!$row[is_change]){
		$arr[52]=$arr[53]=$arr[54]=$arr[55]="";
	
	}
	$arr[56] = ""; 					//56、证书使用的认可号
	$arr[57] = $row[mark]; 					//57、证书使用的认可标志代码
	$arr[58] = $cti_info[risk_level];	//风险系数
	if(!$arr[58] && $row[iso]='A01')
		$arr[58]='03';
	$arr[59] = $db->get_var("SELECT cost FROM `sp_contract_cost` WHERE `ct_id` = '$row[ct_id]'  and cost_type='$row[audit_type]' and deleted=0 and iso='$row[iso]'");	//收费金额
	$arr[59]=sprintf("%.2f", $arr[59]);
	$arr[60] = "01";		//收费币种
	$invoice=$db->get_col("SELECT invoice FROM `sp_contract_cost_detail` WHERE  `pid` = '$row[pid]'  AND `invoice` <> '' and deleted=0");	//收费发票号
	$arr[61] = join("；",$invoice);
	!$arr[61] && $arr[61]=$row[ct_code];
	$arr[62] ="";//62、认证证书附件文件名
	$arr[63] ="";//63、信息记录是否可公开
	$arr[64] = $a64;					//取数开始时间
	$arr[65] = $a65;					//取数结束时间
	$temp_arr=$data_arr[] = $arr;
}



//输出Access
$type=getgp("type");
if($type=="access"){
    
$cert_arr = array(
	'ZDEP_ID'		=> 'a1',
	'ZSUBAUDORG'	=> 'a2',
	'ZORGNAME'		=> 'a3',
	'ZORGNAMEENG'	=> 'a4',
	'ZORGOLDNAME'	=> 'a5',
	'ZORGID'		=> 'a6',
	'ZAUDFLAGS'		=> 'a7',
	'ZTRADES'		=> 'a8',
	'ZCouCode'		=> 'a9',
	'ZAreaCode'		=> 'a10',
	'ZORGADDR'		=> 'a11',
	'ZORGZIP'		=> 'a12',
	'ZORGTEL'		=> 'a13',
	'ZORGFAX'		=> 'a14',
	'ZORGLEADER'	=> 'a15',
	'ZORGTYPEID'	=> 'a16',
	'ZORGREGCAP'	=> 'a17',
	'ZCAPMONTYPE'	=> 'a18',
	'ZORGSIZE'		=> 'a19',
	'ZORGSYSSIZE'	=> 'a20',
	'ZFAACDATE'		=> 'a21',
	'ZOSCID'		=> 'a22',
	'ZSUBCERT'		=> 'a23',
	'ZMOSCID'		=> 'a24',
	'ZMULTIPLACE'	=> 'a25',
	'ZProCode'		=> 'a26',
	'ZProCodeDES'	=> 'a27',
	'ZSPESORTS'		=> 'a28',
	'ZREGIRANGES'	=> 'a29',
	'ZAUDCODE'		=> 'a30',
	'ZTYPECODES'	=> 'a31',
	'ZAUDCHGDUR'	=> 'a32',
	'ZCHGGROUP'		=> 'a33',
	'ZCERTSTATUS'	=> 'a34',
	'ZOLDREGIID'	=> 'a35',
	'ZOLDORGNAME'	=> 'a36',
	'ZNEWREGDATE'	=> 'a37',
	'ZAGAINTIMES'	=> 'a38',
	'ZSURTIMES'		=> 'a39',
	'ZAUDSTADATE'	=> 'a40',
	'ZAUDENDDATE'	=> 'a41',
	'ZAUDDAYSS'		=> 'a42',
	'ZISUNIT'		=> 'a43',
	'ZASSMANLIST'	=> 'a44',
	'ZAUDMONEY'		=> 'a45',
	'ZGETMONTYPE'	=> 'a46',
	'ZINVOICENO'	=> 'a47',
	'ZRISKCOEF'		=> 'a48',
	'ZREGIDATE'		=> 'a49',
	'ZREGITODATE'	=> 'a50',
	'ZCHANGEID'		=> 'a51',
	'ZPCID'			=> 'a52',
	'ZSUSSTADATE'	=> 'a53',
	'ZSUSENDDATE'	=> 'a54',
	'ZRCID'			=> 'a55',
	'ZRCDATE'		=> 'a56',
	'ZCHANGEDATE'	=> 'a57',
	'ZBEGINDATE'	=> 'a58',
	'ZENDDATE'		=> 'a59',

);

$auditor_map = array(
	'ZDEP_ID'		=> 'a1',
	'ZOSCID'		=> 'a2',
	'ZAUDSTADATE'	=> 'a3',
	'ZAUDSTATUS'	=> 'a4',
	'ZAUDENDDATE'	=> 'a5',
	'ZGroMemName'	=> 'a6',
	'ZIDType'		=> 'a7',
	'ZID_NUMBER'	=> 'a8',
	'ZQuaID'		=> 'a9',
	'ZMemCertCode'	=> 'a10',
	'ZMEMROLE'		=> 'a11',
	'ZISCERT'		=> 'a12',
	'ZISSPECIAL'	=> 'a13',
	'ZEVIMARK'		=> 'a14',
);





$tpl_db = DATA_DIR . 'SUPU201208.mdb';

$target_db = DATA_DIR . 'access/' . mysql2date( 'Y-m-d', current_time( 'mysql') ) . '.mdb';



if( file_exists( $target_db ) ){
	@unlink( $target_db );
}
$is_copyed = copy( $tpl_db, $target_db );
if( !$is_copyed ){
	writeover( $target_db, readover( $tpl_db ) );
}

function access_magic( $string ){
	if( is_array( $string ) ){
		foreach( $string as $key => $val ) {
			$string[$key] = str_replace( "'", "''", $val);
		}
	} else {
		$string = str_replace( "'", "''", $string);
	}
	return $string;
}
foreach( $data_arr as $key => $item ){
	$ins_arr = array();
	foreach( $cert_arr as $k => $v ){
		$ins_arr[$k] = iconv( 'UTF-8', 'GBK', $item[$v] );
	}
	$ins_arr = access_magic( $ins_arr );
	if(!$item[$v]) continue;
	$sql = "INSERT INTO ZBCERT_GET ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";

	//load('report')->query( $sql, "/".$target_db );
	load('report')->query( $sql, $target_db );
}

foreach( $auditors as $key => $item ){
	$ins_arr = array();
	foreach( $auditor_map as $k => $v ){
		$ins_arr[$k] = iconv( 'UTF-8', 'GBK', $item[$v] );
	}
	$ins_arr = access_magic( $ins_arr );

	$sql = "INSERT INTO ZBVERIFYMEM ( ".implode(', ', array_keys($ins_arr) )." ) VALUES ( '".implode("','",$ins_arr)."')";
	//echo "$key | $sql <br/><br/>";
	//echo "$sql <br/>";
	//load('report')->query( $sql, "/".$target_db );
	load('report')->query( $sql,$target_db );
}



echo sysinfo('url') . '/' . strstr($target_db, 'data');

exit;
}else{

//输出Execl文件
/**/ 
echo do_excel($data_arr,$auditors,$energy,"获证组织基本信息表");
exit;
}
?>
