<?php
/*
 *保存变更
 */
$pid_radio  = getgp("pid_radio");
$biangeng   = getgp("biangeng");
$zsid       = getgp("zsid");
$changeitem = getgp('changeitem');


$pasuequs=getgp("pasuequs");//暂停具体原因
$recalqus=getgp("recalqus");//撤销具体原因
foreach ($changeitem as $key => $value) {
    $oldtemp = 'old_' . $value;
    $newtemp = 'new_' . $value;
    if ($value == '105' || '106' == $value) {
        $cg_af = getgp($oldtemp . '_scope');
        $cg_bf = getgp($newtemp . '_scope');
    } else {
        $cg_af = getgp($oldtemp);
        $cg_bf = getgp($newtemp);
    }
	if($value=='97_01'){
		$cg_reason = getgp('certpasue_value');
		$certpasue_value2=serialize($pasuequs);
	}
	elseif($value=='97_03'){
		$certpasue_value2=serialize($recalqus);
		$cg_reason = getgp('certrecall_value');
		}
	else
		$certpasue_value2=$cg_reason="";
        $zs_info   = $certificate->get($zsid);
        $cge_date  = getgp('cge_date');
    
    $cg_pinfo  = $db->get_row("select id,audit_type from sp_project where cti_id='$zs_info[cti_id]' order by id desc limit 1");
    if(!empty($biangeng))
    {
        $audit_type = '1101';
        $pid_radio  = getgp("biangeng");
    }else{
        $audit_type = $cg_pinfo['audit_type'];
    }
    $default   = array(
        'zsid'             => $zsid,                        //证书id
        'cg_pid'           => $pid_radio, //$cg_pinfo[id],  //变更关联项目id
        'audit_type'       => $audit_type,                  //审核类型
        'iso'              => $zs_info['iso'],              //体系
        'audit_ver'        => $zs_info['audit_ver'],        //标准版本
        'ctfrom'           => $zs_info['ctfrom'],           //合同来源 
        'cg_type'          => $value,                       //变更类型
        'cg_type_report'   => substr($value, 0, 2),         //上报类型
        'cg_reason'        => $cg_reason,                   //暂停撤销原因
        'cg_meta'          => $value,                       //变更字段
        'cg_af'            => $cg_af,                       //变更前
        'cg_bf'            => $cg_bf,                       //变更后
        'cgs_date'         => getgp('cgs_date'),            //变更日期
        'cge_date'         => $cge_date,                    //暂停到期时间
        'status'           => '0',                          //状态
        'certpasue_value2' => $certpasue_value2,            //
        'note'             => getgp('note'),
    );
    $change->add($default);
	//变更评审
	$allow_change_type=array('103','105','106','108');
	if(in_array($value,$allow_change_type)){
		$db->update('project',array('flag'=>1),array('id'=>$pid_radio));
 	}
}
// 日志
do {
    log_add($_POST['eid'], 0, '证书变更', NULL, serialize($default));
} while (false);
$REQUEST_URI = getgp("url");
showmsg('success', 'success', $REQUEST_URI);