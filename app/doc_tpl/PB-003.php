<?php
require('./framework/PHPZip.class.php' );

$ctid=(int)getgp('ct_id');
$tid=(int)getgp('tid');

$t_info   = $db->get_row("select eid,tb_date,te_date from `sp_task` where id=$tid");
extract($t_info, EXTR_SKIP );

$p_time   = $db->get_row("select * from sp_task_audit_team where eid ='".$t_info['eid']."' and audit_type='1002'");

$ep_info  = $db->get_row("select ep_name from sp_enterprises where eid='$eid'");
extract($ep_info, EXTR_SKIP );

$p_time  = substr($p_time['taskBeginDate'],0,10);

$tb_date = substr($tb_date,0,10);

$te_date = substr($te_date,0,10);

$sh_time = $p_time." 至 ".$te_date;

$info = $db->getAll ("select * from `sp_project` where tid='$tid' AND deleted='0'");

foreach ($info as $val) {
	$audit_type   = $val['audit_type'];
	$ct_code      = $val['ct_code'];
	$sp_date      = $val['sp_date'];
    $comment_date = $val['comment_date'];
}
  
switch ($audit_type){
	case '1001':
	case '1002':
	case '1003':
		$audit_type='初审';
		break;

	case '1004':
		$audit_type='监一';		
		break;

	case '1005':
		$audit_type='监二';
		break;

	case '1007':
		$audit_type='再认证';				
		break;

	case '1006':
		$audit_type='监三';			
		break;

	case '1008':
		$audit_type='专项审核';		
		break;
	
	case '1009':
		$audit_type='特殊监督';			
		break;	

	case '1101':
		$audit_type='变更';				
		break;	

	case '99':
		$audit_type='其他';		
		break;				
	default:
		break;
}

$js_time     = $comment_date;//接收时间

//体系个数
foreach ($info as $v) {

	$iso[] = $v['audit_ver'];

	switch ($v['audit_ver']) {
		case 'A010101':
			$tx['A01']=$db->get_row("SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `iso`='A01' AND `deleted` = '0'");		
			$audit_team['A01'] = $db->getAll ("select name from `sp_task_audit_team` where `tid`='$tid' AND `audit_ver`='A010101' AND `deleted`='0'");
			break;
		case 'A010102':
		case 'A010103':
			$tx['A01']=$db->get_row("SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `iso`='A01' AND `deleted` = '0'");		
			$audit_team['A01'] = $db->getAll ("select name from `sp_task_audit_team` where `tid`='$tid' AND `audit_ver`='A010103' AND `deleted`='0'");
			break;
		case 'A020101':
			$tx['A02']=$db->get_row("SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `iso`='A02' AND `deleted` = '0'");
            $audit_team['A02'] = $db->getAll ("select name from `sp_task_audit_team` where `tid`='$tid' AND `audit_ver`='A020101' AND `deleted`='0'");
			break;
		case 'A020102':
			$tx['A02']=$db->get_row("SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `iso`='A02' AND `deleted` = '0'");
            $audit_team['A02'] = $db->getAll ("select name from `sp_task_audit_team` where `tid`='$tid' AND `audit_ver`='A020102' AND `deleted`='0'");
			break;
		case 'A030102':
			$tx['A03']=$db->get_row("SELECT * FROM `sp_project` WHERE `tid` = '$tid'  AND `iso`='A03' AND `deleted` = '0'");
			$audit_team['A03'] = $db->getAll ("select name from `sp_task_audit_team` where `tid`='$tid' AND `audit_ver`='A030102' AND `deleted`='0'");
			break;		
		default:
			break;
	}
}
$isos=count($iso);
// echo "<pre />";
// print_r($audit_team);exit;

$filename = '认证注册决定审批表('.$ep_name.').doc';


//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/PB-003.xml' );


//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');

$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );

$output = str_replace( '{ct_code}', $ct_code, $output );

// print_r($js_time);exit;
$output   = str_replace( '{js_time}', $js_time, $output);
$output   = str_replace( '{sh_time}', $sh_time, $output);

$output   = str_replace( '{audit_type}', $audit_type, $output);

$output   = str_replace( '{sp_date}', $sp_date, $output);
if( getgp('downs')==1 ){
	if($isos>1)
	{
		foreach ($tx as $key => $value) 
		{

			switch($value['audit_ver']){
					case 'A010101':
						$audit_basis='GB/T 19001-2008';
						       $tixi='QMS';
						break;
					case 'A010103':
						$audit_basis='ISO9001:2015';
						       $tixi='QMS';
						break;
					case 'A020101':
						$audit_basis='GB/T 24001-2004';
							   $tixi='EMS';
						break;
					case 'A020102':
						$audit_basis='GB/T 24001-2016';
							   $tixi='EMS';
						break;
					case 'A030102':
						$audit_basis='GB/T 28001-2011';
						       $tixi='OHSMS';
						break;
			    }
			if(getgp('banben')=='1')
			{
				(!empty($value['pd_audit_code_2017']))?$audit_code_2017=$value['pd_audit_code_2017']:$audit_code_2017=$value['audit_code_2017'];
				if(!empty($audit_code_2017))
				{
					$codeList  = array_filter(explode('；', $audit_code_2017));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code_2017'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code_2017'];
			}else{
				(!empty($value['pd_audit_code']))?$audit_code=$value['pd_audit_code']:$audit_code=$value['audit_code'];
				if(!empty($audit_code))
				{
					$codeList  = array_filter(explode('；', $audit_code));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code'];
			}
			

			$iso = $value[iso];

		    $items = " ";
		    foreach ($audit_team[$iso] as $val) {
				$items .= $val[name].',';//审核组成员
			}
			$item = substr($items,0,-1);

			$filenames = iconv( 'UTF-8', 'gbk', $key.'-'.$filename );
			
			$filePath = CONF.'downs';
			//没有目录创建目录
			if(!is_dir($filePath)) {
			    mkdir($filePath, 0777, true);
			}
			
			//如果存在就删除文件
			if( file_exists($filePath.'/'.$filenames) ){
				@unlink ($filePath.'/'.$filenames); 
			}
     

			$oldOutput = $output;
			$oldOutput = str_replace( '{audit_basis}', $audit_basis, $oldOutput );
			$oldOutput = str_replace( '{audit_code}', $audit_code, $oldOutput );
			$oldOutput = str_replace( '{item}', $item, $oldOutput);
			file_put_contents($filePath.'/'.$filenames,$oldOutput);
		}
	}else{
		foreach ($tx as $key => $value){
			switch($value['audit_ver']){
					case 'A010101':
						$audit_basis='GB/T 19001-2008';
						       $tixi='QMS';
						break;
					case 'A010103':
						$audit_basis='ISO9001:2015';
						       $tixi='QMS';
						break;
					case 'A020101':
						$audit_basis='GB/T 24001-2004';
							   $tixi='EMS';
						break;
					case 'A020102':
						$audit_basis='GB/T 24001-2016';
							   $tixi='EMS';
						break;
					case 'A030102':
						$audit_basis='GB/T 28001-2011';
						       $tixi='OHSMS';
						break;
			    }
			 if(getgp('banben')=='1')
			{
				(!empty($value['pd_audit_code_2017']))?$audit_code_2017=$value['pd_audit_code_2017']:$audit_code_2017=$value['audit_code_2017'];
				if(!empty($audit_code_2017))
				{
					$codeList  = array_filter(explode('；', $audit_code_2017));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code_2017'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code_2017'];
			}else{
				(!empty($value['pd_audit_code']))?$audit_code=$value['pd_audit_code']:$audit_code=$value['audit_code'];
				if(!empty($audit_code))
				{
					$codeList  = array_filter(explode('；', $audit_code));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code'];
			}   
			// $audit_code = $tixi.':'.$value['audit_code'];

			$iso = $value[iso];

		    $items = " ";
		    foreach ($audit_team[$iso] as $val) {
				$items .= $val[name].',';//审核组成员
			}
			$item = substr($items,0,-1);
		}
		$output = str_replace( '{audit_basis}',$audit_basis, $output );
		$output = str_replace( '{audit_code}',   $audit_code , $output );
		$output = str_replace( '{item}',     $item, $output );
		$filename = iconv( 'UTF-8', 'gbk', $filename );
		$filePath = CONF.'downs';
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

			    switch($value['audit_ver']){
					case 'A010101':
						$audit_basis='GB/T 19001-2008';
						       $tixi='QMS';
						break;
					case 'A010103':
						$audit_basis='ISO9001:2015';
						       $tixi='QMS';
						break;
					case 'A020101':
						$audit_basis='GB/T 24001-2004';
							   $tixi='EMS';
						break;
					case 'A020102':
						$audit_basis='GB/T 24001-2016';
							   $tixi='EMS';
						break;
					case 'A030102':
						$audit_basis='GB/T 28001-2011';
						       $tixi='OHSMS';
						break;
			    }
			    if(getgp('banben')=='1')
				{
					(!empty($value['pd_audit_code_2017']))?$audit_code_2017=$value['pd_audit_code_2017']:$audit_code_2017=$value['audit_code_2017'];
					if(!empty($audit_code_2017))
					{
						$codeList  = array_filter(explode('；', $audit_code_2017));
						$codeims   = '';
						foreach($codeList as $code)
						{
							if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
						}
						$value['audit_code_2017'] = $codeims;
					}
					$audit_code = $tixi.':'.$value['audit_code_2017'];
				}else{
					(!empty($value['pd_audit_code']))?$audit_code=$value['pd_audit_code']:$audit_code=$value['audit_code'];
					if(!empty($audit_code))
					{
						$codeList  = array_filter(explode('；', $audit_code));
						$codeims   = '';
						foreach($codeList as $code)
						{
							if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
						}
						$value['audit_code'] = $codeims;
					}
					$audit_code = $tixi.':'.$value['audit_code'];
				}
			    // $audit_code = $tixi.':'.$value[audit_code];
                  
			    $iso = $value[iso];
                   
			    $items = " ";
			    foreach ($audit_team[$iso] as $val) {
					$items .= $val[name].',';//审核组成员
				}
				$item = substr($items,0,-1);
			
			$filenames = iconv( 'UTF-8', 'gbk', $key.'-'.$filename );
			
			$filePath = CONF.'downs';
			//没有目录创建目录
			if(!is_dir($filePath)) {
			    mkdir($filePath, 0777, true);
			}
			
			//如果存在就删除文件
			if( file_exists($filePath.'/'.$filenames) ){
				@unlink ($filePath.'/'.$filenames); 
			}

			$oldOutput = $output;
			$oldOutput = str_replace( '{audit_basis}', $audit_basis, $oldOutput );
			$oldOutput = str_replace( '{audit_code}', $audit_code, $oldOutput );
			$oldOutput = str_replace( '{item}',$item, $oldOutput);
			file_put_contents($filePath.'/'.$filenames,$oldOutput);
		}
	    $archive = new PHPZip();   //遍历指定文件夹
	    $archive->ZipAndDownload(ROOT.'/data/downs/','打包下载');  //压缩并直接下载
	    // exit;
	    $archive->deldir(ROOT.'/data/downs/',false);  //删除目录下所有文件和文件夹}
	}else{
		foreach ($tx as $key => $value){
			switch($value['audit_ver']){
					case 'A010101':
						$audit_basis='GB/T 19001-2008';
						       $tixi='QMS';
						break;
					case 'A010103':
						$audit_basis='ISO9001:2015';
						       $tixi='QMS';
						break;
					case 'A020101':
						$audit_basis='GB/T 24001-2004';
							   $tixi='EMS';
						break;
					case 'A020102':
						$audit_basis='GB/T 24001-2016';
							   $tixi='EMS';
						break;
					case 'A030102':
						$audit_basis='GB/T 28001-2011';
						       $tixi='OHSMS';
						break;
			    }
			if(getgp('banben')=='1')
			{
				(!empty($value['pd_audit_code_2017']))?$audit_code_2017=$value['pd_audit_code_2017']:$audit_code_2017=$value['audit_code_2017'];
				if(!empty($audit_code_2017))
				{
					$codeList  = array_filter(explode('；', $audit_code_2017));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code_2017'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code_2017'];
			}else{
				(!empty($value['pd_audit_code']))?$audit_code=$value['pd_audit_code']:$audit_code=$value['audit_code'];
				if(!empty($audit_code))
				{
					$codeList  = array_filter(explode('；', $audit_code));
					$codeims   = '';
					foreach($codeList as $code)
					{
						if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
					}
					$value['audit_code'] = $codeims;
				}
				$audit_code = $tixi.':'.$value['audit_code'];
			}
			// $audit_code = $tixi.':'.$value[audit_code];

			$iso = $value[iso];

		    $items = " ";
		    foreach ($audit_team[$iso] as $val) {
				$items .= $val[name].',';//审核组成员
			}
			$item = substr($items,0,-1);
		}
		$output = str_replace( '{audit_basis}',$audit_basis, $output );
		
		$output = str_replace( '{audit_code}',   $audit_code , $output );
		$output = str_replace( '{item}',     $item, $output );
		header("Content-type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ));
		echo $output;exit;
	}
}
?>
