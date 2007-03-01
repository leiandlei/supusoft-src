<?php
/*
 *收费登记
 */
$cost_id = getgp('cost_id'); //应收账款
//费用编辑 
$ccd_id  = getgp('ccd_id'); //财务收费明细ID
$ccstatus=getgp("ccstatus");
$sf_edit=getgp("sf_edit");

if ($ccd_id) {
    $ccd_info = $db->get_row("SELECT * FROM sp_contract_cost_detail where id='$ccd_id'");
    $ct_id    = $ccd_info['ct_id'];
    $cost_id  = $ccd_info['cost_id'];
}
if ($cost_id) { //费用登记 
    $cost_info = load('cost')->get($cost_id);
    $ct_id     = $cost_info['ct_id']; //合同ID 
}
//申请人信息
$ct_info    = $db->get_row("select eid,finance_require from sp_contract where ct_id='$ct_id'");
$enterprise = load("enterprise")->get(array(
    "eid" => $ct_info[eid]
));
//交易记录
$sql        = "select ccd.*,cc.cost,cc.iso as cc_iso from sp_contract_cost_detail ccd LEFT JOIN sp_contract_cost cc on cc.id=ccd.cost_id where  ccd.deleted = 0 and ccd.ct_id='$ct_id'  order by ccd.dk_date desc";
$res        = $db->query($sql);
while ($row = $db->fetch_array($res)) {
    $row      = chk_arr($row);
    $aaa      = array();
    $temp_arr = explode('|', $row['cc_iso']);
    foreach ($temp_arr as $v) {
        $aaa[] = f_iso($v);
    }
    $row['iso'] = implode('<br>', $aaa);
    //p($row);
    $datas2[]   = $row;
}
if ($ccd_id) {
    $str = '编辑';
    extract($ccd_info, EXTR_OVERWRITE);
} else {
    $str = '登记';
    //需要收取的收费项目
} 
//合同收费项目
$ct_cost  = $db->get_row("select * from sp_contract_cost where id='$cost_id' AND  deleted = 0  ");
$aaa      = array();
$temp_arr = explode('|', $ct_cost['iso']);
foreach ($temp_arr as $v) {
    $aaa[] = f_iso($v);
}
$ct_cost['iso'] = implode('<br>', $aaa);
$query          = $db->query("SELECT dk_cost FROM `sp_contract_cost_detail` WHERE `cost_id` = '$datas[id]' AND `deleted` = '0' ");
$datas[dk_cost] = 0.0;
while ($r = $db->fetch_array($query)) {
    $datas[dk_cost] += $r[dk_cost];
}
//根据合同ID获取审核项目列表
$projs=load('audit')->gets(array('ct_id'=>$ct_id)); 
tpl();
