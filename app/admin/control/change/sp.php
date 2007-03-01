<?php
/*
 *保存变更审批
 */

$cgid = getgp('cgid');

if ($cgid) {
    $cg_info     = $change->get($cgid);
    $zs_info     = $certificate->get($cg_info['zsid']);
    $zsid        = $zs_info['id'];
	if(!empty($zsid))$db->update('certificate'  ,array('is_check'=>'n'),array('id'=>$zsid));


    $eid         = $zs_info['eid'];
    $cti_id      = $zs_info['cti_id'];
    $change_name = "";
    switch ($cg_info['cg_meta']) {
        case '0101':
            $db->update('enterprises', array(
                'ep_name' => $cg_info['cg_bf'],
                'ep_oldname' => $cg_info['cg_af']
            ), array(
                'eid' => $eid
            ));
            $change_name = "cert_name";
            break;
        case '0102':
        $db->update('certificate'  ,array('ep_addr'=>$cg_info['cg_bf']),array('id'=>$zsid));
            $change_name = "cert_addr";
            //软件操作还要修改企业地址
            break;
        case '103':
            $db->update('enterprises',array('ep_amount'=>$cg_info['cg_bf']),array('eid'=>$eid));
            break;
        case '104':
            $db->update('contract_item',array('mark'=>$cg_info['cg_bf']),array('cti_id'=>$cti_id));
            $db->update('certificate'  ,array('mark'=>$cg_info['cg_bf']),array('id'=>$zsid));
            break;
        case '105':
        case '106':
            $db->update('certificate', array(
                'cert_scope' => $cg_info['cg_bf']
            ), array(
                'id' => $zsid
            ));
            break;
        case '108':
            $db->update('certificate', array(
                'audit_ver' => $cg_info['cg_bf']
            ), array(
                'id' => $zsid
            ));
            break;
        case '97_01':
        case '97_02':
        case '97_03':
        case '97_04':
            $db->update('certificate', array(
                'status' => $cg_info['cg_bf']
            ), array(
                'id' => $zsid
            ));
            break;
    }
    if ($change_name)
        $db->update('certificate', array(
            $change_name => $cg_info['cg_bf'],
            'is_change' => 1, //是否换证
            'change_date' => $cg_info['cgs_date'], //换证时间
            'is_check' => 'n'
        ), array(
            'id' => $zsid
        ));
		//更改变更状态
    $sql = "UPDATE sp_certificate_change SET status='1', pass_date='" . mysql2date('Y-m-d', current_time('mysql')) . "' WHERE id = '$cgid'";
    $db->query($sql);
}
$REQUEST_URI = '?c=change&a=list&status=1';
showmsg('success', 'success', $REQUEST_URI);