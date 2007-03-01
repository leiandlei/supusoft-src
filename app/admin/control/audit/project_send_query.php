<?php
/*
*审核经历查询、已派人项目查询
*/
set_time_limit(0);
$task_status = array(
0	=> '未安排',
1	=> '待派人',
2	=> '待审批',
3	=> '已审批'
);

$fields = $where = $join = '';

/* 搜索开始 */
$ep_name        = trim(getgp( 'ep_name' ));
$name           = trim(getgp( 'name' ));
$ctfrom         = getgp( 'ctfrom' );
$ct_code        = trim(getgp( 'ct_code' ));
$cti_code       = trim(getgp( 'cti_code' ));
$iso            = getgp( 'iso' );
$audit_ver      = getgp( 'audit_ver' );
$audit_type     = getgp( 'audit_type' );
$audit_start_s  = getgp('audit_start_s');
$audit_start_e  = getgp('audit_start_e');
$audit_end_s    = getgp('audit_end_s');
$audit_end_e    = getgp('audit_end_e');
$audit_code     = getgp('audit_code');
$audit_code_2017= getgp('audit_code_2017');
$witness_s      = getgp('witness_s');
$export         = getgp( 'export' );
$audit_ver      = getgp( 'audit_ver' );
$is_leader      = getgp('is_leader');

if($name=trim($name))
{
	$uid=$db->get_var("select id from sp_hr where name='$name' and is_hire='1' and deleted='0'");
	$qua='注册时间：';
	$query=$db->query("SELECT * FROM `sp_hr_qualification` WHERE `uid` = '$uid' AND `status` = '1' order by iso");
		while($r=$db->fetch_array($query))
		{
			$qua.=f_iso($r[iso]).":".$r[s_date]."  ";
		}
}

$mark_add_checkbox = $mark_checkbox = '';
if( $witness_array ){ //见证类型
	foreach( $witness_array as $code => $item ){
		$witness_s_select .= "<option value=\"$code\">$item</option>";
	}
}
$is_leader_radio='<input type="radio"  name="is_leader" value="1"/>是<input type="radio"  name="is_leader" value="2"/>否';
if($is_leader){
	$is_leader_radio=str_replace("value=\"$is_leader\"","value=\"$is_leader\" checked",$is_leader_radio);
	$_auditor_ids = array();
	if($is_leader==1)
		$where .=" and role='01'";
	else
		$where .=" and role!='01'";}

if( $ep_name )
{
	$_eids     = array();
	$_query    = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	if( $_eids ){
		$where .= " AND ta.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND ta.id < -1";
	}
}
//省份
if( $areacode=getgp("areacode") )
{
	$pcode     = substr($areacode,0,2) . '0000';
	$_eids     = array(-1);
	$_query    = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where .= " AND ta.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


if( $name )
{
	$uid=array(-1);
	$_query    = $db->query("SELECT id FROM sp_hr WHERE name = '$name' or easycode like '%$name%' and is_hire=1");
	while( $rt = $db->fetch_array( $_query ) )
	{
		$uid[] = $rt['id'];
	}
	$uid    =array_unique($uid);
	$where .= " AND ta.uid in (".implode(',',$uid).")";
}

//合同编号
if( $ct_code ){
   $where .= " AND p.ct_code = '$ct_code'";
	
}

//合同项目编号
if( $cti_code ){
	$where .= " AND p.cti_code like '%$cti_code%'";
}

if( $iso ){
	$where .= " AND ta.iso ='$iso'";
	$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
}
$audit_ver_select = str_replace( "value=\"A010103\">", "value=\"A010102,A010103\">", $audit_ver_select);
if($audit_ver){ //标准版本
	$_audit_ver = explode(',', $audit_ver);
	if(count($_audit_ver)>1)
	{
		$or = array();
		foreach($_audit_ver as $item)
		{
			$or[] = "ta.audit_ver ='".$item."'";
		}
		$where .= " AND (".implode(' or ', $or).")";
	}else{
		$where .= " AND ta.audit_ver = '$audit_ver'";
	}
	
    $audit_ver_select = str_replace( "value=\"$audit_ver\">", "value=\"$audit_ver\" selected>", $audit_ver_select);
}

if( $audit_code_2017 ){
    $where .= " AND ta.audit_code_2017 LIKE '%$audit_code_2017%'";
}

if( $audit_code ){
    $where .= " AND ta.audit_code LIKE '%$audit_code%'";
}

if( $audit_type ){
	$where .= " AND ta.audit_type = '$audit_type'";
	$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>" , $audit_type_select );

}
/*if($witness_s!=''){
	$witness_s_select=str_replace("value=\"$witness_s\"","value=\"$witness_s\" selected",$witness_s_select);
	$_auditor_ids = array();
	$_query = $db->query("SELECT auditor_id FROM sp_task_audit_team WHERE  witness= '$witness_s'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_auditor_ids[] = $rt['auditor_id'];
	}
	if( $_auditor_ids ){
		$where .= " AND ta.id IN (".implode(',',$_auditor_ids).")";
	} else {
		$where .= " AND ta.id < -1";
	}
}
*/

if($audit_start_s){
	$where .= " AND ta.taskBeginDate >= '$audit_start_s 00:00:00' ";
}
if($audit_start_e){
	$where .= " AND ta.taskBeginDate <= '$audit_start_e 23:00:00' ";
}
if($audit_end_s){
	$where .= " AND ta.taskEndDate >= '$audit_end_s 00:00:00' ";
}
if($audit_end_e){
	$where .= " AND ta.taskEndDate <= '$audit_end_e 23:00:00' ";
}
if($role=getgp("role")){
	$where .= " AND ta.role = '$role' ";
}
if($qua_type=getgp("qua_type")){
	$where .= " AND ta.qua_type = '$qua_type' ";
}
//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );

if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
	$_len = get_ctfrom_level( $ctfrom );
	$len  = $_len;
} else {
	$ctfrom = current_user( 'ctfrom' );
}
switch( $len ){
	case 2	: $add = 1000000; break;
	case 4	: $add = 10000; break;
	case 6	: $add = 100; break;
	case 8	: $add = 1; break;
}
$ctfrom_e = sprintf("%08d",$ctfrom+$add);
if( '01000000' != $ctfrom )
	$where .= " AND ta.ctfrom >= '$ctfrom' AND ta.ctfrom < '$ctfrom_e'";

$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
$fields .= "ta.*,t.status,e.ep_name,e.person,e.ctfrom,e.person,e.person_tel,e.eid";
$fields .=",p.cti_code,p.ct_id,p.use_code code";
$join   .= " LEFT JOIN sp_enterprises e ON e.eid = ta.eid";
$join   .= " LEFT JOIN sp_task t ON t.id = ta.tid";
$join   .= " LEFT JOIN sp_project p ON (p.id = ta.pid AND p.iso=ta.iso)";
// $join .= " JOIN sp_contract_item sc ON p.cti_id = sc.cti_id";

$where .= " AND ta.deleted = '0'";
$where .= " AND ta.data_for = '0'";
if( !$export ){
	$total = $db->get_var("SELECT COUNT(*) FROM sp_task_audit_team ta $join  WHERE 1 $where ");
	$pages = numfpage( $total);
}

$resdb = $aids = array();
$sql = "SELECT $fields FROM sp_task_audit_team ta $join WHERE 1 $where order by ta.taskEndDate desc $pages[limit]" ;
$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) )
{
	$rt['ctfrom_V']      = f_ctfrom( $rt['ctfrom'] );
	$rt['taskBeginDate'] = mysql2date( 'Y-m-d H:i', $rt['taskBeginDate'] );
	$rt['taskEndDate']   = mysql2date( 'Y-m-d H:i', $rt['taskEndDate'] );
	//获取组长信息
	
	
	$rt['leader']=$db->find_var('task_audit_team',array('tid'=>$rt['tid'],'role'=>'01','deleted'=>0),'name');
	
	
	$rt['status_V']   = $task_status[$rt['status']];
    $rt['audit_type']   =f_audit_type( $rt['audit_type'] );
    $rt['audit_ver']  =f_audit_ver( $rt['audit_ver'] );
    $rt['role']       =read_cache('audit_role',$rt['role']);
    $rt['audit_code_2017'] =LongToBr($rt['audit_code_2017'],array('；',';'));
    $rt['audit_code'] =LongToBr($rt['audit_code'],array('；',';'));
    
    //$rt['code']=LongToBr($rt['code'],array('；',';'));
    $rt['qua_type']   =f_qua_type( $rt['qua_type'] );
    		
	if(!$rt['witness'])
	{

		$rt['witness']="";
		$_row=$db->get_row("SELECT * FROM `sp_task_audit_team` WHERE `tid` = '$rt[tid]' AND `deleted` = '0' AND iso='$rt[iso]' AND `witness` <> '' AND `witness_person` ='".$rt[uid]."'");
	
		if(is_numeric($_row[witness_person]))
		{
			$witness_name  = $db->get_var("select name from sp_hr where id =".$_row[witness_person]." and deleted =0");
			$_row['witness'] && $rt['witness']=$_row[name]." ".r_sys_cache("witness",$_row['witness'])." ".$witness_name;
		}else{
			$_row['witness'] && $rt['witness']=$_row[name]." ".r_sys_cache("witness",$_row['witness'])." ".$_row[witness_person];
			
		}
	
	}else{

		if(is_numeric($rt[witness_person]))
		{
			$witness_name  = $db->get_var("select name from sp_hr where id =".$rt[witness_person]." and deleted =0");
			$rt['witness']=$rt[name]." ".r_sys_cache("witness",$rt['witness'])." ".$witness_name;
		}else{
			$rt['witness']=$rt[name]." ".r_sys_cache("witness",$rt['witness'])." ".$rt[witness_person];
			
		}

		
	}

	$rt[dates] = mkdate($rt[taskBeginDate],$rt[taskEndDate]);
	if($export)
	{ 
	    $sql1     = "select uid,name,role,use_code,qua_type from sp_task_audit_team where tid='{$rt[tid]}' and deleted=0 and iso='$rt[iso]'";
	    $query1   = $db->query( $sql1 );
		$_auditors=array();
		    while ($rt1 = $db->fetch_array($query1))
		    {
		    	if($rt1[role] == '01'){
		    		$rt[leader] = $rt1[name]."(".f_qua_type($rt1['qua_type']).$rt1[use_code].")";
					$rt[leader] .=$db->get_var("select tel from sp_hr where id='{$rt1[uid]}'"); 		
		    	}else{
		    		$_auditors[$rt1[uid]]= $rt1[name]."(".read_cache("qualification", $rt1['qua_type'] ).$rt1[use_code].")";
		    	}
		    }
		$rt[peizu_person].=$db->get_var("select tel from sp_hr where name='$rt[peizu_person]'");
		$rt[member]=join(",",$_auditors);
		unset($_auditors);
	}
	$resdb[$rt['id']] = $rt;
	
}

if( !$export )
{
	tpl( 'audit/project_send_query' );
} else 
{
	ob_start();
	tpl( 'xls/list_project_send_query' );
	$data = ob_get_contents();
	ob_end_clean();
	export_xls( '项目派人列表', $data );
}
?>