<?php
//业务代码模块

require_once( ROOT . '/data/cache/qualification.cache.php' );
require_once( ROOT . '/data/cache/skill_source.cache.php' ); //能力来源
require_once( ROOT . '/data/cache/evaluation_methods.cache.php' ); //评价方式

require_once( ROOT . '/data/cache/job_type.cache.php' );		//人员性质 多项
require_once( ROOT . '/data/cache/audit_job.cache.php' );	//审核性质
require_once( ROOT . '/data/cache/iso.cache.php' ); //体系
 
 
$hr_is_hire=array('1'=>'在职','2'=>'离职','3'=>'停用');

$f_is_hire=f_is_hire();  //人员状态-用于搜索-插入
$is_hire_array=array(''); 

$qualification = load('qualification');
$auditcode = load('auditcode');
$user = load('user');

//$a = trim(getgp('a'));
 
//$m = trim(getgp('m'));
$id = (int)getgp('id' );
$qid = (int)getgp('qid' );
$uid = (int)getgp('uid');
if( $audit_job_array ){
	foreach( $audit_job_array as $code => $item ){
		$audit_job_select .= "<option value=\"$item[code]\">$item[name]</option>";
	}
}

//能力来源复选框
$skill_source_checkbox ='';
if( $skill_source_array ){
	foreach( $skill_source_array as $code => $item ){
		$skill_source_checkbox .= "<input type='checkbox' name='skill_source[$code]' value=\"$item[code]\">".$item[name].'&nbsp;';
	}
} 
//评价方式复选框-app_edit
$evaluation_methods_checkbox ='';
if( $evaluation_methods_array ){
	foreach( $evaluation_methods_array as $code => $item ){
		$evaluation_methods_checkbox .= "<input type='checkbox' name='evaluation_methods[$code]' value=\"$item[code]\">".$item[name].'&nbsp;';
	}
}

//合同来源
$ctfrom_select = f_ctfrom_select();

if( $iso_array ){
	foreach( $iso_array as $code => $item ){
		if($item['is_stop']==0) {
			$iso_select .= "<option value=\"$item[code]\">$item[name]</option>";
		}
	}
}
if( $status_array ){
	foreach( $status_array as $code => $item ){
		$status_select .= "<option value=\"$code\">$item</option>";
	}
}
if( $qualification_array ){
	foreach( $qualification_array as $code => $item ){
		$qualification_select .= "<option value=\"$item[code]\">$item[name]</option>";
	}
}
//引入模块控制下的方法
$action=CTL_DIR.$c.'/'.$a.'.php'; 
if(file_exists($action)){
	include_once($action); 
}else{
	echo '该方法不存在，请检查对应程序';
	echo '<br />方法名称：'.$a;	
}   
?>