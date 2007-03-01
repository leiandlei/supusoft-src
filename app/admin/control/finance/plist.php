<?php
//财务收费登记列表
require_once(ROOT . '/data/cache/region.cache.php');
//合同来源
$ctfrom_select   = f_ctfrom_select();
//省分下拉 (搜索用)
$province_select = f_province_select();

$fields          = $join = $where = $item_join = $item_where = $page_str = '';
$status          = (int) getgp('status');

${'tab_' . $status } = ' ui-tabs-active ui-state-active';

//搜索开始
//$a        = getgp('a');
$ep_name  = getgp('ep_name');
$code     = getgp('code');
$ct_code  = getgp('ct_code');
$cti_code = getgp('cti_code');
$ctfrom   = getgp('ctfrom');
$areacode = getgp('areacode');
$export   = getgp('export');
$dk_bdate  =getgp('dk_bdate');
$dk_edate  =getgp('dk_edate');
$ys_bdate  =getgp('ys_bdate');
$ys_edate  =getgp('ys_edate');
if ($ep_name) {
    $_eids  = $db->get_col("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%" . str_replace('%', '\%', $ep_name) . "%' and deleted=0");
	$_eids=array_merge($_eids,array(-1));
    $where .= " AND ct.eid IN (" . implode(',', $_eids) . ")";
}
if ($code) { //企业编号
    $eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code'");
    $where .= " AND ct.eid = '$eid'";
}
if ($ct_code) { //合同编码
    $where .= " AND ct.ct_code = '$ct_code'";
}
if ($cti_code) { //合同项目编码
    $ct_id = $db->get_var("SELECT ct_id FROM sp_contract_item WHERE cti_code = '$cti_code'");
    $where .= " AND ct.ct_id = '$ct_id'";
}
if($ctfrom)
{
    $where .= " AND e.ctfrom = '$ctfrom'";
	$ctfrom_select= str_replace( 'value="'.$ctfrom. '"', 'value="'.$ctfrom.'" selected ', $ctfrom_select );
	
}
if($dk_bdate &&!$dk_edate)
{
    $cc_id = $db->get_var("SELECT ct_id FROM sp_contract_cost_detail WHERE dk_date >= '$dk_bdate'");
    if($cc_id){
    implode($cc_id,',');
    //echo $dk_date;
     $cc_id='('.rtrim( $cc_id).')';
     $where .= " AND ct.ct_id in".$cc_id;
     // $status  =1;
    }else{
        $where .='and 0';
    }
        
}
if($dk_edate &&!$dk_bdate)
{
    $cc_id = $db->get_var("SELECT ct_id FROM sp_contract_cost_detail WHERE dk_date <= '$dk_edate'");
    if($cc_id){
        implode($cc_id,',');
        //echo $dk_date;
        $cc_id='('.rtrim( $cc_id).')';
        $where .= " AND ct.ct_id in".$cc_id;
        // $status  =1;
    }else{
        $where .='and 0';
    }
}
if($dk_edate &&$dk_bdate)
{
    $cc_id = $db->get_var("SELECT ct_id FROM sp_contract_cost_detail WHERE dk_date <= '$dk_edate' and  dk_date >= '$dk_bdate'");
    if($cc_id){
        implode($cc_id,',');
        //echo $dk_date;
        $cc_id='('.rtrim( $cc_id).')';
        $where .= " AND ct.ct_id in".$cc_id;
        // $status  =1;
    }else{
        $where .='and 0';
    }

}
//应收费时间
if($ys_bdate)
{
        $where .= " AND cc.sftime > '$ys_bdate'";
}
if($ys_edate)
{
    $where .= " AND cc.sftime < '$ys_edate'";
}

//合同来源限制
$len = get_ctfrom_level(current_user('ctfrom'));
if ($ctfrom && substr($ctfrom, 0, $len) == substr(current_user('ctfrom'), 0, $len)) {
    $_len = get_ctfrom_level($ctfrom);
    $len  = $_len;
} else {
    $ctfrom = current_user('ctfrom');
}
$last     = substr($ctfrom, $len - 1, 1);
$ctfrom_e = substr($ctfrom, 0, $len - 1) . ($last + 1);
$_i       = 8 - $len;
for ($i = 0; $i < $_i; $i++) {
    $ctfrom_e .= '0';
}
//$where .= " AND ct.ctfrom >= '$ctfrom' AND ct.ctfrom < '$ctfrom_e'";
if ($areacode) {
    $pcode  = substr($areacode, 0, 2) . '0000';
	$_eids  = $db->get_col("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '" . substr($areacode, 0, 2) . "' and deleted=0");
	$_eids=array_merge($_eids,array(-1));
    $where .= " AND ct.eid IN (" . implode(',', $_eids) . ")";
    $province_select = str_replace("value=\"$pcode\">", "value=\"$pcode\" selected>", $province_select);
}
$status_arry = array(
    '1' => '已登记',
    '2' => '已评审',
    '3' => '已审批'
);
$datas       = array();
$fields .= ",e.ep_name,e.ctfrom,ccd.dk_cost,ccd.dk_date";
$join     = " LEFT JOIN sp_enterprises e ON e.eid = cc.eid";
$join     .= " LEFT JOIN sp_contract ct ON ct.ct_id = cc.ct_id";
$join     .= " LEFT JOIN sp_contract_cost_detail ccd ON ccd.cost_id = cc.id";

/*列表*/
$where .= " AND cc.deleted = '0'";//已经删除的不显示

if (!$export) {
    $total[0] = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost cc $join WHERE 1 $where  and cc.status=0");
    $total[1] = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost cc $join WHERE 1 $where  and cc.status=1");
    $pages = numfpage($total[$status]);
}
$where .=" AND cc.status='$status'";
$sql = "SELECT cc.*,cc.status as ccstatus,ct.ct_code $fields FROM sp_contract_cost cc $join WHERE 1 $where ORDER BY cc.id DESC $pages[limit]";

$res = $db->query($sql);
while ($rt = $db->fetch_array($res)) {
	//计算项目编号   
    $sql      = "select cti_code from sp_contract_item where ct_id='$rt[ct_id]' AND deleted=0 ";
    $in_res   = $db->query($sql); 
    $ct_codes = array();
    while ($row = $db->fetch_array($in_res)) {
        $ct_codes[] = $row['cti_code'];
    } 
	$rt['cti_codes'] = implode('<br>', $ct_codes);
	
	//计算认证体系
    $aaa   = array(); 
        $temp_arr = explode('|', $rt['iso']);
        foreach ($temp_arr as $v) {
            $aaa[] = f_iso($v) ; 
        } 
    $rt['iso'] = implode('<br>', $aaa);
    
	
    $rt['ctfrom_V']  = f_ctfrom($rt['ctfrom']);
   
    $rt['status']    = $status_arry[$rt['status']];
    $datas[]         = $rt;
}
//echo "<pre/>";
//var_dump($datas);exit;
if (!$export) {
    tpl();
} else { //导出合同费用列表
    ob_start();
    tpl('xls/list_contract_cost');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('合同费用列表', $data);
}