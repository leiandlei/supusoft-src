<?php 
//excle控制器文件名与方法名一致
$ctl_doc= APP_DIR . '/xls/'.$a.'.php';
if(file_exists($ctl_doc)){
	require_once $ctl_doc; 
}else{
	echo '导出excl控制器不存在';	
}   
?>