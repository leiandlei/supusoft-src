<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );

$prod_arr=array("","生产地址","服务地址","运营地址");



$A01=$A02=$A03="□";

$tid = (int)getgp( 'tid' );

$ct_id= (int)getgp( 'ct_id' );

$t_info=$db->get_row("SELECT eid,tb_date,te_date,approval_date FROM `sp_task` WHERE `id` = '$tid'");

extract( $t_info, EXTR_SKIP );

$ep_info=load("enterprise")->get(array("eid"=>$eid));

extract( $ep_info, EXTR_SKIP );

$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check=unserialize($prod_check);

foreach($prod_check as $k){

	$prod_check_arr[]=$prod_arr[$k];



}

$prod_check=join("/",$prod_check_arr);







//体系

$tx=$db->getAll("SELECT iso FROM `sp_project` WHERE `eid` = '$eid' AND `deleted` = '0' GROUP BY iso");

foreach ($tx as $key => $value) {

	switch ($value['iso']) {

		case 'A01':

			$iso.='QMS'.'、';

			break;

		case 'A02':

			$iso.='EMS'.'、';

			break;

		case 'A03':

			$iso.='OHSMS';

			break;

		default:

			break;

	}

}	



//审核类型

$leader=$db->getAll("SELECT uid,name,taskBeginDate,taskEndDate,audit_type FROM `sp_task_audit_team`  WHERE `tid` = '$tid' and role='01' and deleted='0' ");

$uid=$leader['0']['uid'];

$tel=$db->get_row("SELECT tel FROM `sp_hr`  WHERE `id` = '$uid' and deleted='0'");

$mon = substr($leader[0]['taskEndDate'], 5 , 2);

$day = substr($leader[0]['taskEndDate'], 8 , 2)+3;

if ($day>30) {

	$mon = $mon + 1;

}else{

	$mon = ltrim($mon, 0);

}

// echo "<pre>";

// print_r($tel);exit;

foreach ($leader as $key => $value) {

	switch ($value['audit_type']) {

		case '1002':

			$leader[$key]['cost_type_name'] = '初审一阶段';

			$cost_type = 1003;

			$text = '；二阶段审核预计在'.$mon.'月份。一阶段';

			break;

		case '1003':

			$leader[$key]['cost_type_name'] = '初审二阶段';

			$cost_type = $value['audit_type'];

			$text = '。二阶段';

			break;

		case "1004":

			$leader[$key]['cost_type_name'] = '监一';

			$cost_type = $value['audit_type'];

			$text = '。监一';

			break;

		case "1005":

			$leader[$key]['cost_type_name'] = '监二';

			$cost_type = $value['audit_type'];

			$text = '。监二';

			break;

		case "1007":

			$leader[$key]['cost_type_name'] = '再认证';

			$cost_type = $value['audit_type'];

			$text = '。再认证';

			break;

		default:

			$cost_type = $value['audit_type'];

			break;

	}

	$sql = "select cost from sp_contract_cost where ct_id=".$ct_id." and `cost_type` in(".$cost_type.")";

	$leader[$key]['cost'] = $db->get_var($sql);

}

$tbd=substr($leader[0]['taskBeginDate'],11, 2);

$tnd=substr($leader[0]['taskEndDate'],11, 2);



$leader[0]['taskBeginDate']=substr($leader[0]['taskBeginDate'],0,10);

$leader[0]['taskEndDate']=substr($leader[0]['taskEndDate'],0,10);

$task_bd=sprintf(str_replace("-", "%s",$leader[0]['taskBeginDate']),'年','月').'日';

$task_ed=sprintf(str_replace("-", "%s",$leader[0]['taskEndDate']),'年','月').'日';



// echo '<pre />';

// print_r($task_bd);exit;

switch ($tbd) {

	case '08':

		$leader[0]['taskBeginDate']=$task_bd.' 上午 ';

		break;

	case '13':

		$leader[0]['taskBeginDate']=$task_bd.' 下午 ';

		break;

}



switch ($tnd) {

	case '12':

		$leader[0]['taskEndDate']=$task_ed.' 上午 ';

		break;

	case '17':

		$leader[0]['taskEndDate']=$task_ed.' 下午 ';

		break;

}



// echo '<pre />';

// print_r($leader);exit;

$sql = "SELECT p.ct_code,t.tb_date,tat.name FROM `sp_project` p join sp_task t on p.eid=t.eid join sp_task_audit_team tat on tat.tid=t.id where p.audit_type='1002' and tat.role='1001' and p.eid=".$eid." GROUP BY p.iso";

$query = $db->query($sql);

$results = array();



	$tb_date = $rt['tb_date'] = substr($rt['tb_date'],0,10);

	$a=substr($tb_date,0,7);

	$results[$key] = $rt;



$cti_code=$db->getAll("SELECT cti_code FROM `sp_project`  WHERE `tid` = '$tid' and deleted='0'");

foreach ($cti_code as $v) {

	$bianhao.=$v['cti_code'].";";

}

$bianhao=substr($bianhao,0,-1);

// echo '<pre />';

// print_r($bianhao);exit;

$filename = '审表004 审核通知书('.$ep_name.').doc';



//读入模板文件 

$tpldata = readover( DOCTPL_PATH . 'doc/SH-004.xml' );



// //企业信息部分

$arr_search = array('<','>','&','\'','"');

$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');

$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

// // print_r($ep_name);exit;

$output = str_replace( '{ep_name}', $ep_name, $tpldata );

// //费用

// $output = str_replace( '{cost}', $leader[0]['cost'], $output );

// $output = str_replace( '{ct_code}', $ct_code, $output );

$output = str_replace( '{cti_code}', $bianhao, $output );

// //名字

$output = str_replace( '{name}', $leader[0]['name'], $output );

$output = str_replace( '{tel}', $tel['tel'], $output );

$output = str_replace( '{taskBeginDate}', $leader[0]['taskBeginDate'], $output);

$output = str_replace( '{taskEndDate}', $leader[0]['taskEndDate'], $output );

$output = str_replace( '{audit_type}', $leader[0]['cost_type_name'], $output );

$approval_date = str_replace('-','%s',$approval_date);

$approval_date = sprintf($approval_date,'年','月').'日';

$output = str_replace( '{approval_date}', $approval_date, $output );

$output = str_replace( '{iso}', $iso, $output );



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

		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );;

	}

}else{

	header("Content-type: application/octet-stream");

	header("Accept-Ranges: bytes");

	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );

	echo $output;exit;

}

?>