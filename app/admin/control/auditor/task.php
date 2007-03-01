<?php
/*
*审核员审核任务
*/
extract( $_GET, EXTR_SKIP );
$audit_finish = (int)$audit_finish;
$audit_finish_0_tab = $audit_finish_1_tab  = '';
${'audit_finish_'.$audit_finish.'_tab'} = ' ui-tabs-active ui-state-active';
$fields = $join = $where = '';
$rect_array=array("无","已整改","<span style='color:#00f'>未整改</span>");
//搜索条件
$ep_name = trim($ep_name);
if( $ep_name )
{
	$_eids = array();
	$query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%{$ep_name}%'");
	while( $rt = $db->fetch_array( $query ) )
	{
		$_eids[] = $rt['eid'];
	}      
	if( $_eids ){
		$where .= " AND tat.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND tat.id < -1";
	}
}
$person = trim($person);
if( $person ){
	$_eids = array();
	$query = $db->query("SELECT eid FROM sp_enterprises WHERE person LIKE '%$person%'");
	while( $rt = $db->fetch_array( $query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND tat.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND tat.id < -1";
	}
}
if($ct_code=trim($ct_code)){
	$where .=" AND p.ct_code ='$ct_code'";

}
if($cti_code=trim($cti_code)){
	$where .=" AND p.cti_code like '%$cti_code%'";

}
if($use_code=trim($use_code)){

	$where .="  and tat.use_code like '%$use_code%'";

}
if($use_code_2017=trim($use_code_2017)){

	$where .="  and tat.use_code_2017 like '%$use_code_2017%'";

}
if($sh_date_s){
	$where .= " AND tat.taskBeginDate > '$sh_date_s'";

}
if($sh_date_e){
	$where .= " AND tat.taskEndDate < '$sh_date_e'";

}
if($username)
{
	$where .="  and tat.name like '%$username%'";
	
}
//$join .= " LEFT JOIN sp_task_auditor ta ON ta.id = tat.auditor_id";
$join .= " LEFT JOIN sp_project p ON p.id = tat.pid";
//$join .= " LEFT JOIN sp_assess a ON a.pid = tat.pid";
$join .= " LEFT JOIN sp_task t ON t.id = tat.tid";
$join .= " LEFT JOIN sp_enterprises e ON e.eid = t.eid";


$fields .= "tat.*,p.audit_type,p.cti_code,p.ct_code,p.ct_id,p.id,p.comment_pass_date,p.comment_pass,p.sp_date,p.sv_note,t.jh_sp_date,p.rect_finish,t.jh_sp_note,t.jh_sp_name,t.bufuhe,t.upload_file_date,t.jh_sp_status,e.ep_name";

$where .= "  AND tat.deleted =  '0' AND tat.role != ''";
if(current_user('uid')!=1)
$where .= " AND tat.uid = '".current_user('uid')."'";
$where .=" AND t.status=3";
$where .=" AND p.deleted=0";
//$where .= " AND tat.taskBeginDate > '2012-01-01'";


//状态标签

//时间限制审核结束4个月
$date=thedate_add(date("Y-m-d H:i:s"),-4,"month");
//当前审核员的派人信息
$projects = $pids = array();

$finish_total = array(0,0);
if( !$export ){
	$finish_total[0]= count(getAllList("SELECT * FROM sp_task_audit_team tat $join WHERE 1 $where AND p.is_finish = '0'"));
	$finish_total[1]= count(getAllList("SELECT * FROM sp_task_audit_team tat $join WHERE 1 $where AND p.is_finish = '1'"));
	$pages = numfpage($finish_total[$audit_finish]);
}

$where.=" AND p.is_finish='$audit_finish'";
$sql = "SELECT $fields FROM sp_task_audit_team tat $join WHERE 1 $where  order by tat.taskBeginDate";
//print_r($sql);exit;
$projects = getAllList($sql);

if( !empty($pages['limit']) ){
	$limits   = explode(',',substr($pages['limit'],7));
	$projects = array_slice($projects,$limits[0],$limits[1]);
}
if( !$export ){
		tpl('auditor/task');
} else {//导出客户文档列表
		ob_start();
		tpl( 'xls/list_task_auditor' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '审核员任务列表', $data );
	}

function getAllList($sql=''){
	if($sql=='')return false;
	global $db;
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		//审核天数
		$rt[audit_num]=mkdate($rt[taskBeginDate],$rt[taskEndDate]);   
		//天数
		if($rt[taskEndDate]<=date("Y-m-d"))
			$rt[num]=mkdate($rt[taskEndDate],date("Y-m-d")."17:00:00");
		//合同来源
		$rt['ctfrom'] = f_ctfrom( $rt['ctfrom'] );   
		//标准+审核类型
		$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] ) . '：' . f_audit_type( $rt['audit_type']);
		// 专业代码
		$rt['audit_code']=LongToBr($rt['audit_code'],array('；','；'));
		//分组代码
		$rt['use_code']=LongToBr($rt['use_code'],array('；','；'));
		//是否有不符合
		if($rt['comment_pass'] == '1' ){
			$rt['comment_pass_V']='(是)';
			}
		elseif($rt['comment_pass'] == '2')
			$rt['comment_pass_V']='(否)';
		
		if($rt[taskEndDate]<$date)
			$rt[f]=0;
		else
			$rt[f]=1;
		if(current_user("uid")==1 || $audit_finish==0)
			$rt[f]=1;
		if(!$audit_finish)
			if($rt['bufuhe']){
				if($rt[num]>40)
					$rt['color']="red";
			}else{
				if($rt[num]>25)
					$rt['color']="red";
			}
		//上传时间
		if($rt[upload_file_date] && $rt[upload_file_date]>"0000-00-00 00:00:00")
			$rt['color']="";
		// 整改状态
		$rt[rect]=$rect_array[$rt[rect_finish]];

		$rt[plan_status]=$rt[jh_sp_status]?"YES":"NO";
		$projects[] = chk_arr($rt);
	}

	$array=array();
	foreach ($projects as $key => $value) {
		$array[$value['tid']][$value['uid']][]= $value;
	}
	$array_info = array();
	foreach ($array as $t){
		// echo "<pre />";
		// print_r($t);exit;
		foreach ($t as $u){
			if(count($u)>1){
				$a = array();
				foreach($u as $ke => $vl){
					foreach ($vl as $k => $v) {
						if( empty($a[$k]) ){
							$a[$k] = $v;
						}else{
							if( $a[$k]!=$v )$a[$k] = $a[$k].'、'.$v;
						}
					}
				}
				$array_info[] = $a;
			}else{
				$array_info[] = $u[0];
			}
		}	
	}
	return $array_info;
}
?>