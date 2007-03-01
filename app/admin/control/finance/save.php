<?php
/*
 *财务收费保存
 */
//$pid     = getgp('pid'); 新5.0不继承pid
$ccd_id  = getgp('ccd_id'); //收费明细ID 
 
$cost_id = getgp('cost_id');

$dk_cost = getgp('dk_cost');
$cost_info=load('cost')->get($cost_id);
$ccstatus=getgp('ccstatus');
$status=getgp("status");
 //计算费用是否交完
foreach($_POST['pid'] as $k=>$pid){ 
// echo (int)$_POST['is_finance'][$k];
	load('audit')->edit($pid,array('is_finance'=>(int)$_POST['is_finance'][$k]));  
	
}

 if(getgp('invoice')){
    $new_ccstatus=2;
 }else{
     $new_ccstatus=$ccstatus;
 }
//财务费用区域 
$value   = array(
    'eid' => $cost_info[eid],
    'ct_id' => $cost_info[ct_id],
   // 'pid' => $pid,
    'cost_id' => $cost_id,
    'iso' => $cost_info[iso],
    'audit_type' => $cost_info[cost_type],
    'status'=>$new_ccstatus
);
 if(getgp('invoice')){
     $value['invoice']=getgp('invoice');
     $value['invoice_cost']=getgp('invoice_cost');
     $value['invoice_date']=getgp('invoice_date');
     $value['fpnote']=getgp('fpnote');
 }elseif(getgp('dk_cost')){
     $value['currency']=getgp('currency');
     $value['dk_date']=getgp('dk_date');
     $value['dk_cost']=getgp('dk_cost');
     $value['note']=getgp('note');
 }
if ($ccd_id) {
    $af = $ctcf->get($ccd_id);
    $ctcf->edit($ccd_id, $value);
    $bf = $ctcf->get($ccd_id);
    log_add($cost_info[eid], current_user("uid"), "财务收费修改", serialize($af), serialize($bf));

    if ($status) {
        $db->update("contract_cost",array("status"=>1),array("id"=>$cost_id));
       $sql = "update sp_project p,sp_contract_cost c set is_finance=2 where c.ct_id=p.ct_id AND c.eid=p.eid AND c.cost_type=p.audit_type and c.id=$cost_id";
        $db->query($sql);
    }

    if ($invoice = getgp('invoice')) {
        $sms_arr = array(
//            "pid" => $pid,
            "eid" => $cost_info[eid],
            "temp_id" => $ccd_id,
            "flag" => '3'
        );
        load("sms")->add($sms_arr);
    }
} else {
    $ccd_id = $ctcf->add($value);
    $bf     = $ctcf->get($ccd_id);
    log_add($cost_info[eid], current_user("uid"), "财务收费添加", NULL, serialize($bf));

    if ($status) {
        $db->update("contract_cost",array("status"=>1),array("id"=>$cost_id));
        $sql = "update sp_project p,sp_contract_cost c set is_finance=2 where c.ct_id=p.ct_id AND c.eid=p.eid AND c.cost_type=p.audit_type and c.id=$cost_id";
        $db->query($sql);
    }
}
$REQUEST_URI = "?c=finance&a=edit&cost_id=$cost_id&ccstatus=$ccstatus";
showmsg('success', 'success', $REQUEST_URI);