<?php
//监督维护列表
//项目状态数组
$project_status_array = array(
    '0' => '未安排',
    '1' => '待派人', //1-待派人|已安排
    '2' => '待审批',
    '3' => '已审批',
    '5' => '维护',
    '6' => '退回', //6-转审核部又退回监督维护
    
);
if ($_SESSION['extraInfo']['ctfrom']=='01000000') {
    $ctf='';
}else{
    $ctf = $_SESSION['extraInfo']['ctfrom'];
    $hezuofang = 1;
}
$enterprise = load( 'enterprise' );

//审核类型
$audit_type_select = '';
if ($audit_type_array) {
    foreach ($audit_type_array as $code => $item) {
        if (!in_array($code, array('1001', '1002','1002','1008','1009','1010'))) 
			$audit_type_select.= "<option value=\"$code\">$item[name]</option>";
    }
}
extract($_GET, EXTR_SKIP);
$svStatus = (int)getgp('svStatus') ? $_GET['svStatus'] : 0;
$status_0 = $status_1 = $status_2 = $status_3 = '';
$ {
    'status_' . $svStatus
} = ' ui-tabs-active ui-state-active"';
/*$ep_name = getgp('ep_name');
$ctfrom			   = getgp( 'ctfrom' )?getgp( 'ctfrom' ):$ctf;$areacode = getgp('areacode');
$work_code = getgp('work_code');
$person = getgp('person');
$audit_ver = getgp('audit_ver');
*/
//省份下拉
$province_select =f_province_select();
unset($code, $item);
$fields = $join = $where = $page_str = '';
//要获取的字段
$fields.= "p.*,e.ep_name,e.ctfrom,e.areacode,e.ep_level,e.person,e.ep_phone,e.person_tel,e.person_email,cti.audit_code as code";
//要关联的表
$join.= " LEFT JOIN sp_enterprises e ON e.eid = p.eid";
$join.= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";
// $join.= " LEFT JOIN sp_certificate c ON c.eid = p.eid";
if ($ep_name) {
    $_eids     = array();
    $_query    = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%" . str_replace('%', '\%', trim($ep_name)) . "%'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids[] = $rt['eid'];
    }
    if ($_eids) {
        $where.= " AND p.eid IN (" . implode(',', $_eids) . ")";
    }else
		$where.= " AND p.id<-1";
}
//省份
if( $areacode ){
	$pcode     = substr($areacode,0,2) . '0000';
	$_eids     = array(-1);
	$_query    = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where.= " AND p.eid IN (" . implode(',', $_eids) . ")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}
if ($ct_code=trim($ct_code)) {
    $where.= " AND p.ct_code = '$ct_code'";
}
if ($cti_code=trim($cti_code)) {
    $where.= " AND p.cti_code like '%$cti_code%'";
}
if ($iso) {
    $where.= " AND p.iso = '$iso'";
    $iso_select = str_replace("value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
if ($audit_ver) { //标准版本
    $where.= " AND p.audit_ver='$audit_ver'";
	$audit_ver_select = str_replace("value=\"$audit_ver\">", "value=\"$audit_ver\" selected>", $audit_ver_select);
}
if ($audit_type) {
    $where.= " AND p.audit_type = '$audit_type'";
    $audit_type_select = str_replace("value=\"$audit_type\">", "value=\"$audit_type\" selected>", $audit_type_select);
}
if ($pre_date_start) {
    $where.= " AND p.pre_date >= '$pre_date_start'";
}
if ($pre_date_end) {
    $where.= " AND p.pre_date <= '$pre_date_end'";
}
if ($final_date_start) {
    $where.= " AND p.final_date >= '$final_date_start'";
}
if ($final_date_end) {
    $where.= " AND p.final_date <= '$final_date_end'";
}

if ($last_date_start&&$last_date_end) {
    $last_date_start_1 = explode('-', $last_date_start);$last_date_start_1[0] = $last_date_start_1[0]-1;
    $last_date_start_1 = implode('-', $last_date_start_1);
    // $last_date_start_1 = date("Y-m-d",strtotime("+1 day",strtotime("$last_date_start_1")));
    $last_date_end_1   = explode('-', $last_date_end);$last_date_end_1[0] = $last_date_end_1[0]-1;
    $last_date_end_1   = implode('-', $last_date_end_1);
    // $last_date_end_1   = date("Y-m-d",strtotime("+1 day",strtotime("$last_date_end_1")));
    $lastdatasql = "select pid,cti_id,max(te_date) as te_date,s_date,audit_type from (select p.id as pid,p.cti_id,t.te_date as te_date,cf.s_date,p.audit_type 
                    FROM sp_project p 
                    LEFT JOIN sp_task t ON t.eid=p.eid 
                    LEFT JOIN sp_certificate cf ON p.cti_id=cf.cti_id and cf.eid=p.eid 
                    where 
                    (
                        (t.te_date>='".$last_date_start_1."' and t.te_date<='".$last_date_end_1."') 
                        or 
                        (cf.s_date>='".$last_date_start_1."' and cf.s_date<='".$last_date_end_1."')
                    ) and p.deleted=0 and cf.deleted=0 and p.audit_type!='1009'
                    order by p.cti_id,p.audit_type desc) as test GROUP BY cti_id";
    // print_r($lastdatasql);exit;
    $data        = $db->getAll( $lastdatasql);
    $cti_ids     = array();$outcti_id = array();
    foreach ($data as $item)
    {
        if( in_array($item['cti_id'],$outcti_id) ){
            continue;
        }else{
            $outcti_id[] = $item['cti_id'];
        }

        if( in_array($item['audit_type'], array('1004')) )
        {
            if( $item['s_date']>=$last_date_start_1&&$item['s_date']<=$last_date_end_1 )
            {
                $cti_ids[] = $item['pid'];
            }
        }else{
            if( $item['te_date']>=$last_date_start_1&&$item['te_date']<=$last_date_end_1 )
            {
                $cti_ids[]        = $item['pid'];
            }
        }
    }
    $cti_ids = implode(',', $cti_ids);$cti_ids = $cti_ids?$cti_ids:'0';
    $where.= " AND p.id in (".$cti_ids.")"; 
}


//合同来源限制
//$ctfrom = $_SESSION['extraInfo']['ctfrom'];
if($ctfrom){
    $where .= " AND p.ctfrom = '$ctfrom'";
}
$ctfrom_select = str_replace("value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>", $ctfrom_select);
$allow_type=array('1003', '1002','1007','1008','1009','1010','2001','2002','3001');
$where .="   AND p.audit_type NOT IN ('".join("','",$allow_type)."') AND p.deleted = '0' ";
//$where.=" AND sv_status=$svStatus";
if ($export=='3') {
    $svStatus= 4;
    $where_4 = $where;  
}else{
    $where_0 = $where . " AND p.status IN('5','6') AND  p.sv_status NOT IN('1','2','3')";
    $where_1 = $where . " AND p.sv_status=1";
    $where_2 = $where . " AND p.sv_status=2";
    $where_3 = $where . " AND p.sv_status=3";
   
}

if (!$export) {
    //为维护统计
    $total_0 = $db->get_var("SELECT COUNT(*) FROM sp_project p  WHERE 1 $where_0");
    //待定统计
    $total_1 = $db->get_var("SELECT COUNT(*) FROM sp_project p  WHERE 1 $where_1");
    //接受统计
    $total_2 =(int)$db->get_var("SELECT COUNT(*) FROM sp_project p  WHERE 1 $where_2");
    //不接受统计
    $total_3 = $db->get_var("SELECT COUNT(*) FROM sp_project p  WHERE 1 $where_3");
    $pages = numfpage($ { 'total_' . $svStatus});
}
$resdb = array();
if ($export=='3') {
    $query = $db->query("SELECT $fields FROM sp_project p $join WHERE 1 ${'where_' . $svStatus} ORDER BY p.sv_status ASC");
}else{
   $query = $db->query("SELECT $fields FROM sp_project p $join WHERE 1 ${'where_' . $svStatus} ORDER BY p.pre_date,e.areacode ASC $pages[limit]"); 
}

$data=array();
while ($rt = $db->fetch_array($query)) {
    $cert                = $db->get_row("SELECT id,certno,e_date,cert_scope FROM sp_certificate WHERE cti_id='$rt[cti_id]'  AND deleted = '0' order by status asc limit 1");
    $rt['certno']        = $cert['certno'];
    $rt['e_date']        = $cert['e_date'];
    $rt['zsid']          = $cert['id'];
    // $rt['scope']         = $cert['cert_scope'];
    $rt['status']        = $project_status_array[$rt['status']];

    if (!empty($rt['code'])) {
        $a1 = "";
        $a2 = explode("；",$rt['code']);
        foreach ($a2 as $value){
            $a3 = $db->get_var("SELECT shangbao FROM sp_settings_audit_code WHERE id='$value'");
            $a1 .= $a3.";";
        }
        $rt['code'] = $a1;
    }
    
    $rt['code']          = LongToBr($rt['code'], array(
        ";",
        "；"
    ));
	$rt['use_code']      = LongToBr($rt['use_code'],array('；',';'));
    // $rt['province']      = f_region_province($rt['areacode']);
    $rt['province']      = read_cache("region",$rt['areacode']);
    $rt['audit_type_V']  = f_audit_type($rt['audit_type']);
    $rt['audit_ver_V']   = f_audit_ver($rt['audit_ver']);
    $rt['ctfrom_V']      = f_ctfrom($rt['ctfrom']);
    $rt['up_date']       = mysql2date('Y-m-d', $rt['up_date']);
    if ('0000-00-00' == $rt['up_date'] || '1970-01-01' == $rt['up_date']) $rt['up_date'] = '';
    $rt['audit_ver']     = f_audit_ver($rt['audit_ver']);
	$rt['ep_mail']       = $db->get_var("SELECT meta_value FROM `sp_metas_ep` WHERE `ID` = '$rt[eid]' AND `meta_name` = 'person_mail'");
    $rt['signe_name']    = $db->get_var("SELECT signe_name FROM `sp_contract` WHERE `ct_id` = '$rt[ct_id]'");
	$rt['last_date']     = $db->get_var("SELECT t.te_date FROM sp_project p LEFT JOIN sp_task t ON t.id=p.tid WHERE p.cti_id='$rt[cti_id]' AND p.deleted=0 AND t.deleted=0 ORDER BY t.te_date DESC");
	$rt['last_date']     = substr($rt['last_date'],0,10);
    $rt['person_mail']   = $enterprise->meta($rt['eid'],'person_mail');

    $team_lead = $team_member = array();
    $team_lead           = $db->get_row("SELECT tat.pid,tat.name,tat.role FROM `sp_project` p LEFT JOIN `sp_task_audit_team` tat ON p.id=tat.pid WHERE p.cti_id='$rt[cti_id]' AND `role`='01' AND p.deleted=0 AND tat.deleted=0 ORDER BY tat.id DESC");
    $team_member         = $db->getAll("SELECT name,role FROM `sp_task_audit_team` WHERE `pid`='$team_lead[pid]' AND `role`='02' AND `deleted`=0");
    $rt['team_lead']     = $team_lead['name'];
    foreach ($team_member as $v) {
        $rt['team_member'] .= $v['name']."、";
    }
    $rt['team_member']   = substr($rt['team_member'],0,-3);
    $rt['team_member']   = $rt['team_member']?$rt['team_member']:"";
    // print_r($rt['team_member']);exit;

    $resdb[$rt['id']]    = $rt;
	if($export=='2')
    {
		$rt['use_code']  = str_replace(array("<br/>","<br />"),";",$rt['use_code']);
		$rt['code']      = str_replace(array("<br/>","<br />"),";",$rt['code']);
		$tid             = $db->get_var("SELECT tid FROM `sp_project` WHERE `cti_id` = '$rt[cti_id]' AND `tid` <> '0' AND `deleted` = '0' ORDER BY `id` DESC");
		$t_info          = $db->get_row("SELECT tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
		$shsj            = substr($t_info[tb_date],0,10)."至".substr($t_info[te_date],0,10);
		$e_info          = $enterprise->get(array("eid"=>$rt[eid]));
		$data[]          = array($rt[ep_name],$rt[ctfrom_V],$rt[code],$rt[team_lead],$rt[team_member],f_iso($rt[iso])."-".$rt['audit_type_V'],$rt[st_num],$shsj,"",$rt['e_date'],$e_info[person],$e_info[ep_phone]."/".$e_info[person_tel],$e_info['person_mail'],$rt['province'],$rt['comment_note']);

	}
}
if (!$export) {
    tpl('audit/list_super');
} elseif($export==2){
    do_excel($data,"审核计划");
    EXIT;
} elseif($export==3){
    ob_start();
    tpl('xls/list_super_all');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('监督维护项目列表', $data);
}else {
    ob_start();
    tpl('xls/list_super');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('监督维护项目列表', $data);
}

?>   
