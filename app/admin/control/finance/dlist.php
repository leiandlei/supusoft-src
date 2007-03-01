<?php
// 财务收费明细
 
extract($_GET, EXTR_SKIP);
$fileds = $join = $where = '';
$status=getgp("status");
if ($ep_name) {
    $_eids1 = array(-1);
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%" . str_replace('%', '\%', trim($ep_name)) . "%'");
    while ($rt = $db->fetch_array($_query)) {
        $_eids1[] = $rt['eid'];
    }
    
    $where.= " AND ccd.eid IN (".implode(',', $_eids1).")";
    
}

/*if($_GET['data_for']) {
    $where .= " AND ccd.data_for LIKE '%".trim($_GET['data_for'])."%' ";
}*/
//省份
if( $areacode=getgp("areacode") ){
    $pcode = substr($areacode,0,2) . '0000';
    $_eids = array(-1);
    $_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
    while( $rt = $db->fetch_array( $_query ) ){
        $_eids[] = $rt['eid'];
    }
    $where.= " AND ccd.eid IN (" . implode(',', $_eids) . ")";
    unset( $_eids, $_query, $rt, $_eids );
    
    $province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}



if($ct_code=trim($ct_code)){
    $_pids = array(-1);
    $_query = $db->query("SELECT id FROM sp_project WHERE ct_code LIKE '%$ct_code%' and deleted=0" );
    while ($rt = $db->fetch_array($_query)) {
        $_pids[] = $rt['id'];
    }
    $where.= " AND ccd.pid IN (" . implode(',', $_pids) . ")";

}
if($cti_code=trim($cti_code)){
    $_pids = array(-1);
    $_query = $db->query("SELECT id FROM sp_project WHERE cti_code LIKE '%$cti_code%' and deleted=0" );
    while ($rt = $db->fetch_array($_query)) {
        $_pids[] = $rt['id'];
    }
    $where.= " AND ccd.pid IN (" . implode(',', $_pids) . ")";

}
//合同来源限制
//$len = get_ctfrom_level(current_user('ctfrom'));
//if ($ctfrom && substr($ctfrom, 0, $len) == substr(current_user('ctfrom') , 0, $len)) {
//  $_len = get_ctfrom_level($ctfrom);
//  $len = $_len;
//} else {
//  $ctfrom = current_user('ctfrom');
//}
switch ($len) {
    case 2:
        $add = 1000000;
        break;

    case 4:
        $add = 10000;
        break;

    case 6:
        $add = 100;
        break;

    case 8:
        $add = 1;
        break;
}
$ctfrom_e = sprintf("%08d", $ctfrom + $add);

//$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
//$ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
if($ctfrom){
    $where.= " AND ct.ctfrom = '$ctfrom' ";
    $ctfrom_select = str_replace("value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>", $ctfrom_select);
    
}
if ($invoice) {
    $where.= " AND ccd.invoice = '$invoice'";
}
if ($dk_date_start) {
    $where.= " AND ccd.dk_date >= '$dk_date_start'";
}
if ($dk_date_end) {
    $where.= " AND ccd.dk_date <= '$dk_date_end'";
}

if ($invoice_dates) {
    $where.= " AND ccd.invoice_date >= '$invoice_dates'";
}
if ($invoice_datee) {
    $where.= " AND ccd.invoice_date <= '$invoice_datee'";
}

$join.= " LEFT JOIN sp_contract ct on ct.ct_id=ccd.ct_id ";
$join.= " LEFT JOIN sp_enterprises e on e.eid=ccd.eid ";
$join.= " LEFT JOIN sp_contract_cost cc on cc.id=ccd.cost_id ";
if($tb_date)
{
    $where.= " AND st.tb_date >= '$tb_date'";
    
}
if($te_date)
{
    $where.= " AND st.te_date <= '$te_date'";
}
if($tb_date || $te_date)
{
    $join.= "  JOIN sp_task st on ct.ct_id=st.ct_id  and ccd.audit_type=st.audit_type";
}
$where .=" and ccd.deleted='0'";

/*列表*/
if (!$export) {
    switch ($status)
    {
        case 0:
            $where1.= " and ccd.status='0'";
            $tab_0="ui-tabs-active ui-state-active";
            break;
        case 1:
            $where1.= " and ccd.status='1'";
            $tab_1="ui-tabs-active ui-state-active";
            break;
        case 2:
            $where1.= " and ccd.status='2'";
            $tab_2="ui-tabs-active ui-state-active";
            break;
        default:
            $where1.= " and ccd.status='0'";
            $tab_1="ui-tabs-active ui-state-active";
            break;
    }
    $total = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost_detail ccd $join WHERE 1 $where $where1 ");
    $pages = numfpage($total);
}
//echo "SELECT COUNT(*) FROM sp_contract_cost_detail ccd $join WHERE 1 $where and ccd.status='0' ";exit;
  //   ccd   为 sp_contract_cost_detail表的别名；
  $sql = "select ccd.*,ct.ct_code,e.ep_name,ct.ctfrom,cc.cost_type,cc.iso cost_iso,cc.cost  from sp_contract_cost_detail ccd $join where 1 $where $where1  order by ccd.id desc $pages[limit]";
$total_0 = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost_detail ccd $join WHERE 1 $where and ccd.status='0' ");

$total_1 = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost_detail ccd $join WHERE 1 $where and ccd.status='1' ");
$total_2 = $db->get_var("SELECT COUNT(*) FROM sp_contract_cost_detail ccd $join WHERE 1 $where and ccd.status='2' ");
// print_r($sql);exit;
$res = $db->query($sql);
while ($rt = $db->fetch_array($res)) 
{
    $rt['iso'] = f_iso($rt['iso']);
    $cti_codes=$db->getCol('contract_item','cti_code',array('deleted'=>0,'ct_id'=>$rt['ct_id'])); 
    $rt['cti_code']=implode('<br>',$cti_codes);
    $rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
  //  if ('0000-00-00' == $rt['invoice_date']) $rt['invoice_date'] = '';
    
    $rt['cost_type'] = read_cache('cost_type',$rt['cost_type']);
    

    $cost_iso = explode("|",$rt[cost_iso]);
    foreach($cost_iso as $key=>$value){
        $cost_iso[$key] = read_cache('iso',$value);
    }
    $rt['cost_iso'] = implode('<br>',$cost_iso);
    
    
    //if($rt[dk_cost]) $debt[$rt['cost_id']] = $rt[dk_cost];
/*    foreach($rt as $k=>$v){
        if(in_array($v,array('0000-00-00','0000-00-00 00:00:00')))
            $rt[$k]='';
    
    }*/
    $datas[$rt['id']] = $rt;
}
 
if (!$export) {
    tpl('finance/dlist');
} else {
    ob_start();
    tpl('xls/list_finance_dlist');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls('财务收费明细列表', $data);
}

