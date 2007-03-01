<?php 
/*
*首先将文件名改成中文 用excel_read读取数据
*/
// $files=array("2013_1004",'2014_1004','2014_1007','2014_1004_11','2014_1007_11');
$files=array("up1","up2","up3");
foreach($files as $_file){
$report=excel_read(CONF."imp/".$_file.".xlsx");
$i=0;
foreach($report['Sheet1'] as $k=>$item){
	if(!$item[B] or $k<2) continue;
	foreach($item as $_k=>$_v){
		if(in_array($_k,array("V","AL","AO","AP","AX","AY","BB","BC","BE","BF")))
			$item[$_k]=GetData($_v);
	
	}
	$item['AA']=str_replace("A01001","A010101",$item['AA']);
	$item['AA']=str_replace("A02001","A020101",$item['AA']);
	$item['AA']=str_replace("A0300102","A030102",$item['AA']);
	$item['AE']=='301' && $item['AE']='0301';
	$item['AE']=='1' && $item['AE']='01';
	$item['AE']=='4' && $item['AE']='04';
	//导入企业信息 
    $new_ep   = array(
        'work_code' => $item['G'],
        'ep_name' => $item['D'],
        'ep_name_e' => $item['E'],
        'ep_oldname' => $item['F'],
        'industry' => $item['I'],
        'statecode' => $item['J'],
        'areacode' => $item['K'],
        'areaaddr' => get_region_by_country($item['K']),
        'ep_addr' => $item['L'],
        'cta_addr' => $item['L'],
        'prod_addr' => $item['L'],
        'ep_addrcode' => $item['M'],
        'cta_addrcode' => $item['M'],
        'prod_addrcode' => $item['M'],
        'ep_phone' => $item['N'],
        'ep_fax' => $item['O'],
        'delegate' => $item['P'],
        'nature' => $item['Q'],
        'capital' => $item['R'],
        'currency' => $item['S'],
        'ep_amount' => $item['T'],
        //合同来源
        'ctfrom' => '01000000',
        'old_id' => "1",
    );
	$eid      = get_eid($new_ep);
	 //新增证书
    $new_cert = array(
        'eid' => $eid,
        'ctfrom' => '01000000',
        'is_check' => 'y',
        'mark' => $item['H'],
		'iso' => substr($item['AA'], 0, 3),
		'audit_ver' => $item['AA'],
		'cert_scope' => $item['AD'],
        'cert_name' => $item['D'],
        'cert_name_e' => $item['E'],
		'cert_addr'=>$item['L'],
		//'cert_addr_e'=>$item['L'],
        's_date' => $item['AX'],
        'e_date' => $item['AY'],
        'first_date' => $item['V'],
        'certno' => $item['W'],
        'status' => sprintf('%02d',$item['AZ']),
        'old_id' => 1,
    );
    //换证信息
    if ($item['AH']) {
        $new_cert['is_change']     = 1;
        $new_cert['change_type']   = $item['AI'];
        $new_cert['old_certno']    = $item['AJ'];
        $new_cert['old_cert_name'] = $item['AK'];
        $new_cert['change_date']   = $item['AL'];
    }
	$cert_id=$db->getField("certificate",'id',array('certno' => $item['W']));
	if($cert_id){
		$db->update("certificate",$new_cert,array("id"=>$cert_id));
		echo $item['W']."<br/>";
		// continue;
		}
	else
		$cert_id = $db->insert("certificate",$new_cert);
		
	//合同
	$new_ct = array(
		'eid' => $eid,
		'ctfrom' => '01000000',
		'accept_date'=>$item['AO'],
		'pre_date'=>$item['AO'],
		'status' => '3'
	);
	$ct_id=$db->getField("contract","ct_id",array("eid"=>$eid));
	if($ct_id)
		$db->update('contract', $new_ct,array("ct_id"=>$ct_id));
	else{
		$ct_id  = $db->insert('contract', $new_ct);
		//生成合同编码
		$db->update('contract', array(
			'ct_code' => 'CT-' . $ct_id
		), array(
			'ct_id' => $ct_id
		));
	}
	//合同项目
	$new_cti = array(
		'eid' => $eid,
		'ctfrom' => '01000000',
		'ct_id' => $ct_id,
		'mark' => $item['H'],
		'total' => $item['U'],
		'iso' => substr($item['AA'], 0, 3),
		'audit_ver' => $item['AA'],
		'audit_code' => $item['AC'],
		'use_code' => get_code($item['AC'],substr($item['AA'], 0, 3)),
		'scope' => $item['AD'],
		'audit_type'=>map_audit_type(trim($item['AE']),$item['AN']),
		'renum' => $item['AM'],
		'risk_level' => $item['AW'],
		'old_id' => 1,
	);
	//身份令牌:验证
	if(!$new_cti['audit_type']){
		echo $item['AE'];
		echo '<br>';	
		
	}
	$cti_id=$db->getField("contract_item","cti_id",array("iso"=>$new_cti[iso],"eid"=>$new_cti[eid]));
	if($cti_id)
		$db->update('contract_item', $new_cti,array("cti_id"=>$cti_id));
	else{
		$cti_id  = $db->insert('contract_item', $new_cti);
		//生成合同编码 
		$db->update('contract_item', array(
			'cti_code' => 'CTI-' . $cti_id
		), array(
			'cti_id' => $cti_id
		));
	}
	//关联证书与合同项目
	//集成合同项目信息到证书表
	$cti_info=$db->find_one('contract_item',array('cti_id'=>$cti_id));
	
	$db->update('certificate', array(
		'ct_id' => $ct_id,
		'cti_id' => $cti_id,
		'ct_code'=>'CT-' . $ct_id,
		'cti_code'=>$cti_info['cti_code'],
	), array(
		'id' => $cert_id
	));

   /*  //导入变更信息
    if ($item['AF']) {
        // $cert_id   = $db->getField('certificate', 'id', array(
            // 'certno' => $new_cert['certno']
        // ));
        $new_chang = array(
            'zsid' => $cert_id,
            'status' => 1,
            'ctfrom' => '01000000',
            'cg_type' => $item['AF'],
            'cg_type_report' => $item['AF'],
            'cgs_date' => $item['BF']
        );
        //系统变更类型 
        if ($item['BB']) {
            $new_chang['cg_type']   = '97_01';
            $new_chang['cg_reason'] = $item['BA'];
            $new_chang['cge_date']  = $item['BC'];
        }
        if ($item['BE']) {
            $new_chang['cg_type']   = '97_03';
            $new_chang['cg_reason'] = $item['BD'];
            $new_chang['cge_date']  = $item['BE'];
        }
        load('change')->add($new_chang);
    } */
	$cert_info=$db->find_one("certificate",array('certno' => $item['W']));
	// 新增任务	 
    $new_task = array(
        'eid' => $cert_info[eid],
        'tb_date' => $item['AO'],
        'te_date' => $item['AP'],
		'tk_num'=>$item['AQ'],
        'ctfrom' => '01000000',
        'status' => '3',
        'old_id' => 1,
    );
    $tid      = get_tid($new_task, true);

	//增加项目 
    $new_proj = array(
        'eid' => $cert_info[eid],
        'ct_id' => $cert_info[ct_id],
        'cti_id' => $cert_info[cti_id],
        'ct_code' => $cert_info[ct_code],
        'cti_code' => $cert_info[cti_code],
        'iso' => $cert_info[iso],
        'audit_ver' => $cert_info[audit_ver],
        'total' => $item['U'],
        'scope' => $item['AD'],
		'audit_code' => $item['AC'],
		'use_code' => $new_cti['use_code'],
        'ctfrom' => '01000000',
        'old_id' => 1,
        'st_num' => $item['AQ'],
        'status' => '3',
        'tid' => $tid,
        'pd_type' => '1',
        'redata_status' => '1', 
        'redata_date' => $item['BF'], 
        'to_jwh_date' => $item['BF'], 
        'assess_date' => $item['BF'], 
        'sp_date' => $item['BF'], 
        'pre_date' => $item['AO'], 
    );
 
	//集成合同项目表信息 
	 
	
	 
    //计算评定人员
    $pd       = explode('；', $item['AS']);
	if($pd[1]){
		$new_proj['comment_a_name'] = $pd[0];
		$new_proj['comment_a_uid'] = $db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$pd[0]'");
		$new_proj['comment_b_name'] = $pd[1];
		$new_proj['comment_b_uid'] = $db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$pd[1]'");
	
	}else{
		$new_proj['comment_a_name'] = $pd[0];
		$new_proj['comment_a_uid'] = $db->get_var("SELECT id FROM `sp_hr` WHERE `name` = '$pd[0]'");
	}
    
	
	
	
    //p($pd);
    //处理审核类型
    if ($item['AE'] == '01') { //初审生成一阶段 二阶段  
        $new_proj['audit_type'] = '1002';
		$temp=$db->getField("project",'id',array("audit_type"=>'1002',"cti_id"=>$new_proj['cti_id']));
		if(!$temp)
        load('audit')->add($new_proj) && $i++;
        $new_proj['audit_type'] = '1003';
		$db->update("contract_item",array("ejdxc_num"=>$item['AQ']),array("cti_id"=>$cti_id));
      
    } elseif ($item['AE'] == '02') {
        $new_proj['audit_type'] = '1007';
		$db->update("contract_item",array("ejdxc_num"=>$item['AQ']),array("cti_id"=>$cti_id));
    } elseif ($item['AE'] == '0301') { //监督
		$db->update("contract_item",array("jdxc_num"=>$item['AQ']),array("cti_id"=>$cti_id));
        //监督次数
        if ($item['AN'] == '0') {
            $new_proj['audit_type'] = '';
        } elseif ($item['AN'] == '1') {
            $new_proj['audit_type'] = '1004';
        } elseif ($item['AN'] == '2') {
            $new_proj['audit_type'] = '1005';
        } else {
            $new_proj['audit_type'] = '1006';
        }
       
    }elseif($item['AE'] == '04'){
		
		$new_proj['audit_type'] = '1009';
		  
	}else{
	 	$new_proj['audit_type'] = '99'; 
	}
	$temp=$db->getField("project",'id',array("audit_type"=>$new_proj['audit_type'],"cti_id"=>$new_proj['cti_id']));
	if(!$temp)
	$db->insert( 'project', $new_proj ) && $i++;
}
$j=0; 
foreach($report['Sheet2'] as $k=>$item){
	if(!$item[B] or $k<2) continue;
	foreach($item as $_k=>$_v){
		if(in_array($_k,array("D","F")))
			$item[$_k]=GetData($_v);
	
	}
	$item['J']=sprintf('%02d',$item['J']);
	$item['L']=sprintf('%02d',$item['L']);
	$item['E']=='301' && $item['E']='0301';
	$item['E']=='1' && $item['E']='01';
	$item['E']=='4' && $item['E']='04';
	$uid=get_uid(array("name"=>$item['G']));

	/* $new_hr  = array(
        'name' => $item['G'],
        'card_type' => sprintf('%02d',$item['H']),
        'card_no' => $item['I'],
        'audit_job' => $item['N'],
		'job_type'=>'1004'
    );
    $uid     = get_uid($new_hr, true);
    //资格信息
	if(strpos($item['C'],'Q'))
		$iso="A01";
	if(strpos($item['C'],'E'))
		$iso="A02";
	if(strpos($item['C'],'S'))
		$iso="A03";
    $new_qua = array(
        'uid' => $uid,
        'qua_type' => $item['J'],
        'qua_no' => $item['K'],
        'iso' => $iso,
    );
	$qua_id=get_hr_qua($new_qua, true);
 */
// 新增任务	 
	$cert_info=$db->find_one("certificate",array('certno' => trim($item['C'])));
	if(!$cert_info) continue;
	$p_info=$db->find_one("project",array("cti_id"=>$cert_info['cti_id']));
	// if($item['E']=='01'){
	
	// }
    $new_tat = array(
        'tid' => $p_info[tid],
        'eid' => $p_info[eid],
        'ctfrom' => '01000000',
        'uid' => $uid,
        'name' => $item['G'],
        'pid' => $p_info[id],
        'audit_ver' => $p_info[audit_ver],
        'iso' => $p_info[iso],
        'audit_code' => $p_info[audit_code],
        'use_code' => $p_info[use_code],
        'audit_type' => $p_info[audit_type],
        'role' => '10' . $item['L'],
        'old_id' => 1,
        'qua_type' => $item['J'],
        'taskBeginDate' => $item['D'],
        'taskEndDate' => $item['F']
    );
    $tat_id  = $db->getField('task_audit_team', 'id', array('uid' => $uid,'taskBeginDate' => $item['D'],'iso' => $p_info[iso],'taskEndDate' => $item['F']));
    //$pid and 
    if (!$tat_id){
    //新增派人
        $db->insert('task_audit_team', $new_tat) && $j++;
	}  
}
}
ECHO "SUCCESS PROJECT $i TAT $j";
function get_code($audit_code,$iso){
		global $db;
		$r="";
		$audit_code=str_replace(array(";","｜","|"),"；",$audit_code);
		$r=explode("；",$audit_code);
		$use_code="";
		foreach($r as $_val){
			$c=$db->get_var("SELECT code FROM `sp_settings_audit_code` WHERE `shangbao` = '$_val' and iso='$iso'");
			$c && $use_code.=$c."；";
			unset($c);
		}
		$use_code=rtrim($use_code,'；');
		return $use_code;
}
?>