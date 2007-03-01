<?php
$ep_name = getgp( 'ep_name' );
$cti_code = getgp( 'cti_code' );
$eid = (int)getgp( 'eid' );

$p_states = array('未安排','待派人','待审批','已审批','','维护','维护（退）');
$ct_states = array('未登记完','待评审','待审批','已审批');
$fields = $join = $where = '';

if(current_user('usertype')==2)$where .= " AND e.cu_id=".current_user('cu_id');//添加筛选条件 需要符合自己的id

if( $ep_name or $cti_code or $eid ){
	if( $ep_name=trim($ep_name) ){
		$where .= " AND e.ep_name like '%$ep_name%'";
	} 
	if( $eid ){
		$where .= " AND e.eid = '$eid'";
	}

	if( $cti_code=trim($cti_code) ){
		$where .= " AND cti.cti_code like '%$cti_code%'";
	}
}
if($te_dates=getgp("te_dates")){
	$where .=" AND t.te_date>='$te_dates'";

}
if($te_datee=getgp("te_datee")){
	$where .=" AND t.te_date<='$te_datee'";

}
//合同来源限制
$ctfrom = $_SESSION['extraInfo']['ctfrom'];
if(  $ctfrom!='01000000' ){
    $where .= " AND e.ctfrom = '$ctfrom'";
}

$fields .= "p.id,p.iso,p.audit_type,p.final_date,p.redata_date,p.status AS state,cti.cti_code,ct.create_date AS ct_create_date,";
$fields .= "ct.review_date AS ct_review_date,ct.approval_date AS ct_approval_date,ct.status AS ct_state,";
$fields .= "t.tb_date,t.te_date,t.save_date,t.rect_date,t.bufuhe,p.comment_date,p.comment_pass_date,p.sp_date,p.eid,p.cti_id,e.ep_name";
 
$join .= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";
 
$join .= " LEFT JOIN sp_task t ON t.id = p.tid";
if(array_key_exists('is_customer',$_SESSION['userinfo'])){
	$join .= " LEFT JOIN sp_customer c ON c.cu_id = e.cu_id";
} 
$projects = array();
$sql = "SELECT $fields FROM sp_project p $join WHERE 1 $where order by t.te_date desc";
$query = $db->query( $sql );
//p($sql );
while( $rt = $db->fetch_array( $query ) ){ 
	$rt['iso'] = f_iso( $rt['iso'] );
	$rt['audit_type'] = f_audit_type($rt['audit_type']);
	$rt['state'] = $p_states[$rt['state']];
	$rt['ct_state'] = $ct_states[$rt['ct_state']]; 
	$rt['tb_date'] = mysql2date( 'Y-m-d', $rt['tb_date'] );
	$rt['te_date'] = mysql2date( 'Y-m-d', $rt['te_date'] ); 
	$cert_info=$db->get_row("SELECT id as zsid,certno,s_date as cert_start,e_date as cert_end,status cert_state FROM `sp_certificate` WHERE `cti_id` = '$rt[cti_id]' AND `eid` = '$rt[eid]' AND `deleted` = '0' ORDER BY `e_date` DESC");
	if($cert_info){
	$rt=array_merge($rt,$cert_info);
	$rt['cert_state'] = f_certstate( $rt['cert_state'] );
	}
	$rt[send_date]=$db->get_var("SELECT sms_date FROM `sp_sms` WHERE `temp_id` = '$rt[zsid]' AND `deleted` = '0' and flag=1");
	$rt[bufuhe]?$rt[bufuhe]="是":$rt[bufuhe]="否";
	$projects[] = chk_arr($rt);
}
tpl();