<?php
$tid     = (int)getgp('tid');
$eid     = (int)getgp('eid');
$ctid    = (int)getgp('ct_id');
$banben  = getgp('banben');
require('./framework/PHPZip.class.php' );
$sql     = "select p.cti_id,e.ep_name,p.ct_code,p.audit_type,p.audit_code,p.pd_audit_code,p.audit_code_2017,p.pd_audit_code_2017,ta.taskBeginDate,ta.taskEndDate,p.to_jwh_date,ta.name,ta.iso,ta.audit_type from sp_project p LEFT JOIN sp_task_audit_team ta on p.id=ta.pid left JOIN sp_enterprises e on p.eid= e.eid where p.tid=".$tid." and p.deleted=0 and ta.deleted=0 and e.deleted=0";

$results = $db->getAll($sql);
$audit  = (in_array($results[0]['audit_type'],array('1003','1002')))?'初次认证':$arr_audit_type[$results[0]['audit_type']];//证书状态
$ssql = $db->get_row("select `tid` from `sp_project` where audit_type='1002' AND eid=$eid ");
$tid=$ssql['tid'];
$time =$db->get_row("select `tb_date` from `sp_task` where id=$tid");
switch ($results[0]['audit_type'] ) {
	case '1001':
	case '1002':
	case '1003':
		$results[0]['audit_type']='初审';
		break;
	case '1004':
	case '1005':
	case '1006':
		$results[0]['audit_type']='监督';
		break;
	case '1007':
		$results[0]['audit_type']='再认证';
		break;	
	default:
		break;
}


$ep_name     = $results[0]['ep_name'];//组织名称
$ct_code     = $results[0]['ct_code'];//合同号
$js_time     = $results[0]['to_jwh_date'];//接收时间

$sh_time     = substr($time['tb_date'],0,10).'至'.substr($results[0]['taskEndDate'],0,10);//审核时间


$item = $iso = $audit_code = array();//审核组成员-体系-专业代码
foreach ($results as $value) 
{
	if($banben=='1')
	{
		(!empty($value['pd_audit_code_2017']))?$value['audit_code_2017']=$value['pd_audit_code_2017']:$value['audit_code_2017']=$value['audit_code_2017'];
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';
			
			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			
			$value['audit_code_2017'] = $codeims;
		}
		$audit_code[] = $value['audit_code_2017'];
	}else{
		(!empty($value['pd_audit_code']))?$value['audit_code']=$value['pd_audit_code']:$value['audit_code']=$value['audit_code'];
		if(!empty($value['audit_code']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code']));
			$codeims   = '';
			
			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
		$audit_code[] = $value['audit_code'];//专业代码
	}
	$item[]       = $value['name'];//审核组成员
	$iso[]        = $arr_audit_iso[$value['iso']];//体系
}

// 判断三标是cnas或者lll
$sql  = "select * from sp_contract_item where ct_id='".$ctid."' and deleted=0 ";
$mark = $db->getALL($sql);
foreach ($mark as $k => $v) {
	switch ($v['mark']) {
		case '01':
			$str1.=$arr_audit_iso[$v['iso']].",";
			break;
		case '99':
			$str2.=$arr_audit_iso[$v['iso']].",";
			break;
		default:
			break;
	}
}
$str1 = substr($str1,0,-1);
$str2 = substr($str2,0,-1);
if (!empty($str1)) {
	$CNS  = "CNAS: ";
}else{
	$CNS  = "";
}
if (!empty($str2)) {
	$CS  = "LLL: ";
}else{
	$CS  = "";
}

$str="".$CNS."".$str1." "."".$CS."".$str2;
   
//认证体系输出

$audit_code = array_unique($audit_code);
$audit_code = implode(',',$audit_code);
$item       = array_unique($item);      $item       = implode(',',$item);
$iso        = array_unique($iso); 
$tpldatas1 =  readover( DOCTPL_PATH . 'doc/RZ-002-1.xml' );
$tpldatas2 =  readover( DOCTPL_PATH . 'doc/RZ-002-2.xml' );
$tpldatas  = array($tpldatas1,$tpldatas2);
$i = 1;
foreach ($tpldatas as $value) 
{
	if($i=='1')
	{
		$ep_names  = '一评';
	}else{
		$ep_names  = '二评';
	}
	$filename  = $ep_names.'认证决定审查.doc';
	$filenames = iconv( 'UTF-8', 'gbk', $filename );
	$dates     = date('YmdHis');
	$filePath  = CONF.'downs'.'/'.$dates;
	
	//没有目录创建目录
	if(!is_dir($filePath)) {
	    mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filenames) ){
		@unlink ($filePath.'/'.$filenames); 
	}
	$output   = str_replace( '{ep_name}', $ep_name, $value);
	$output   = str_replace( '{ct_code}', $ct_code, $output);
	$output   = str_replace( '{js_time}', $js_time, $output);
	$output   = str_replace( '{audit_type}', $audit, $output);
	$output   = str_replace( '{sh_time}', $sh_time, $output);
	$output   = str_replace( '{audit_code}', $audit_code, $output);
	$output   = str_replace( '{item}', $item, $output);
	$output   = str_replace( '{iso}', $str, $output);
	$output   = str_replace( '{xingzhi}', $results[0]['audit_type'], $output);

	$output   = preg_replace("/\{.[^-]+?\}/", "", $output);
	file_put_contents($filePath.'/'.$filenames,$output);
	$i++;
}   
$archive = new PHPZip();   //遍历指定文件夹

$archive->ZipAndDownload(ROOT.'/data/downs/'.$dates,'认证决定审查表');  //压缩并直接下载
$archive->deldir(ROOT.'/data/downs/'.$dates,false);  //删除目录下所有文件和文件夹}


