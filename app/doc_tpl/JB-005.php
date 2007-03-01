<?PHP
$uid = (int)getgp('uid');
$sql = "select hr.`name`,hr.`sex`,hr.`birthday`,hr.`technical`,hre.`department`,hre.`position` from sp_hr hr left join sp_hr_experience hre on hr.id=hre.add_hr_id where hre.type='j' and hr.deleted=0 and hre.deleted=0 and hr.id=%s order by hre.`department` desc";
$sql = sprintf($sql,$uid);
$results = $db->getOne($sql);

$filename = '认证人员专业能力评定表.doc';
$tpldata  = readover( DOCTPL_PATH . 'doc/JB-005.xml' );
$output   = str_replace( '{name}', $results['name'], $tpldata);//姓名
$output   = str_replace( '{sex}', $arr_hr_sex[$results['sex']], $output);//性别
$output   = str_replace( '{shengri}', $results['birthday'], $output);//出生年月
$output   = str_replace( '{zhicheng}', $arr_hr_technical[$results['technical']], $output);//专业职称
$output   = str_replace( '{xueli}', $arr_hr_department[$results['department']], $output);//学历
$output   = str_replace( '{xuewei}', $results['position'], $output);//所学专业
$output   = preg_replace("/\{.[^-]+?\}/", "", $output);
if( getgp('downs')==1 ){
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
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}