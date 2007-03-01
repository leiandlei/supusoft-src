<?php
// print_r('./framework/PHPZip.class.php');exit;
require( DATA_DIR . 'cache/audit_ver.cache.php' );
require('./framework/PHPZip.class.php' );
$checked   = '■';
$nochecked = '□';
$tid    = (int)getgp( 'tid' );
$ctid   = (int)getgp( 'ct_id' );

$t_info = $db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

//统一社会代码
$code  = "select * from  sp_enterprises  where sp_enterprises.deleted=0 and eid=".$eid;

$r_c   = $db->getOne($code);

$daima ='';
$code  =strlen($r_c['work_code']);
if($code=='18'){
	$daima='统一社会信用代码';
	$work_code=$r_c['work_code'];
}else{
	$daima='组织机构代码';
	$work_code=substr($work_code,0,8).'-'.substr($work_code,8,1);
}

$query     = $db->query( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");

$audit_type="";
$zhuanjia  =array();
while( $rt = $db->fetch_array( $query ) ){
	$audit_type.=f_iso($rt[iso]).":".read_cache("audit_type",$rt[audit_type]);
	$zhuanjia[]=$rt['zy_name'];
}
$zhuanjia=array_unique($zhuanjia);
  //项目编号
$xiangmu    =$db->getAll("select sp.cti_code,sp.scope from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
$scope='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
	$scope = $v['scope'];
}

$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);

//体系个数
$iso=$db->getAll("SELECT iso,exc_clauses FROM `sp_contract_item` WHERE `ct_id` = '$ctid' AND `deleted` = '0' group by iso");
// echo "<pre />";
// print_r($ctid);exit;
foreach ($iso as $key => $value) {
	switch ($value['iso']) {
		case 'A01':
			$tx['A01']=$db->get_row("SELECT cti_code,scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ctid' AND `iso`='A01' AND `deleted` = '0'");		
			$tx['A01']['name'] = '审表014-1 组织认证证书 子证书表达要求说明(';
			$tx['A01']['exc_clauses']  = $value['exc_clauses'];
			$exc_clauses               = $value['exc_clauses'];  
			break;
		case 'A02':
			$tx['A02']=$db->get_row("SELECT  cti_code,scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ctid' AND `iso`='A02' AND `deleted` = '0'");
			$tx['A02']['name'] = '审表014-2 组织认证证书 子证书表达要求说明(';
			$tx['A02']['exc_clauses']  = $value['exc_clauses'];
			$exc_clauses               = $value['exc_clauses'];  
			break;
		case 'A03':
			$tx['A03']=$db->get_row("SELECT cti_code,scope,iso FROM `sp_contract_item` WHERE `ct_id` = '$ctid'  AND `iso`='A03' AND `deleted` = '0'");
			$tx['A03']['name'] = '审表014-3 组织认证证书 子证书表达要求说明(';
			$tx['A03']['exc_clauses'] = $value['exc_clauses'];
			$exc_clauses               = $value['exc_clauses'];  
			break;		
		default:
			break;
	}
}
$isos=count($iso);

//体系 
// //a=$db->getAll("select sp.iso from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
// $str_iso='';
// // echo "<pre />";
// // print_r($a);exit;
// foreach( $iso as $v)
// {
// 	switch ($v['iso'])
//    {
// 		case 'A10':
// 			$iso='知识产权管理体系';
// 			break;
// 		case 'A01':
// 			$iso='QMS';
// 			break;
// 		case 'A02':
// 			$iso='EMS';
// 			break;
// 		case 'A03':
// 			$iso='OHSMS';
// 			break;
// 	}
// 	$str_iso.= $iso.',';
// }
// $str_iso= substr($str_iso,0,strlen($str_iso)-1);



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

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-014.xml' );

//企业信息部分
$arr_search  = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name     = str_ireplace($arr_search,$arr_replace,$ep_name);
$output      = str_replace( '{ep_name}', $ep_name, $tpldata );
$output      = str_replace( '{ct_code}', $ct_code, $output );
$ep_name_e   = str_replace('&','&amp;',$ep_name_e);
$output      = str_replace( '{ep_name_e}', $ep_name_e, $output );
$output      = str_replace( '{ep_addr}', $ep_addr, $output );
$output      = str_replace( '{ep_addr_e}', $ep_addr_e, $output );
$output      = str_replace( '{cta_addr}', $cta_addr, $output );
$output      = str_replace( '{cta_addr_e}', $cta_addr_e, $output );
$output      = str_replace( '{bg_addr}', $bg_addr, $output );
$output      = str_replace( '{bg_addr_e}', $bg_addr_e, $output );
$output      = str_replace( '{prod_addr}', $prod_addr, $output );
$output      = str_replace( '{prod_addr_e}', $prod_addr_e, $output );
$output      = str_replace( '{ep_addrcode}', $ep_addrcode, $output );
$output      = str_replace( '{ep_phone}', $ep_phone, $output );
$output      = str_replace( '{ep_fax}', $ep_fax, $output );
$output      = str_replace( '{daima}', $daima, $output );
$output      = str_replace( '{work_code}', $work_code, $output );


if( getgp('downs')==1 ){
	if($isos>1)
	{
		foreach ($tx as $key => $value) 
		{
			$filename  = $value['name'].$ep_name.').doc';
			$filenames = iconv( 'UTF-8', 'gbk', $filename );
			
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
			if( file_exists($filePath.'/'.$filenames) ){
				@unlink ($filePath.'/'.$filenames); 
			}
			$oldOutput = $output;
			$oldOutput = str_replace( '{cti_code}',$value['cti_code'], $oldOutput );
			$oldOutput = str_replace( '{scope}',   $value['scope'] , $oldOutput );
			$oldOutput = str_replace( '{iso}',     $arr_audit_iso[$value['iso']], $oldOutput );
			$oldOutput = str_replace( '{exc_clauses}', $value['exc_clauses'], $oldOutput );
			file_put_contents($filePath.'/'.$filenames,$oldOutput);
		}
	}else{ 

		$output   = str_replace( '{cti_code}',$str_xiangmu, $output );
		$output   = str_replace( '{scope}',   $scope , $output   );
		$output   = str_replace( '{iso}',     $arr_audit_iso[$value['iso']], $output );
		$output   = str_replace( '{exc_clauses}', $exc_clauses, $output );
		$filename = '审表014 组织认证证书 子证书表达要求说明('.$ep_name.').doc';
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
	}
}else{

		if($isos>1)
		{

			foreach ($tx as $key => $value) 
			{
				$filename  = $value['name'].$ep_name.').doc';
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
				$oldOutput = $output;
				$oldOutput = str_replace( '{cti_code}',$value['cti_code'], $oldOutput );
				$oldOutput = str_replace( '{scope}',   $value['scope'] , $oldOutput );
				$oldOutput = str_replace( '{iso}',     $arr_audit_iso[$value['iso']], $oldOutput );
				$oldOutput = str_replace( '{exc_clauses}', $value['exc_clauses'], $oldOutput );
				file_put_contents($filePath.'/'.$filenames,$oldOutput);
			}   
		    $archive = new PHPZip();   //遍历指定文件夹

		    $archive->ZipAndDownload(ROOT.'/data/downs/'.$dates,'打包下载');  //压缩并直接下载
		    $archive->deldir(ROOT.'/data/downs/'.$dates,false);  //删除目录下所有文件和文件夹}
		}else{
	        $filename = '审表014 组织认证证书 子证书表达要求说明('.$ep_name.').doc';
			$output   = str_replace( '{cti_code}',$str_xiangmu, $output );
			$output   = str_replace( '{scope}',   $scope, $output );
			$output   = str_replace( '{iso}',     $arr_audit_iso[$value['iso']], $output );
			$output   = str_replace( '{exc_clauses}', $exc_clauses, $output );
			header("Content-type: application/octet-stream");
			header("Accept-Ranges: bytes");
			header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ));
			echo $output;exit;
		}
	}
?>