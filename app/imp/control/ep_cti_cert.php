<?php
//$date = '201407'; //标识
//echo $date;
//include 'init.php';
//通过行政编码转汉字
$datas = $db_source->get_results("select * from zbcert_get_$date");
foreach ($datas as $v) {
    //导入企业信息 
    $new_ep   = array(
        'work_code' => $v['ZORGID'],
        'ep_name' => $v['ZORGNAME'],
        'ep_name_e' => $v['ZORGNAMEENG'],
        'ep_oldname' => $v['ZORGOLDNAME'],
        'industry' => $v['ZTRADES'],
        'statecode' => $v['ZCouCode'],
        'areacode' => $v['ZAreaCode'],
        'areaaddr' => get_region_by_country($v['ZAreaCode']),
        'ep_addr' => $v['ZORGADDR'],
        'cta_addr' => $v['ZORGADDR'],
        'prod_addr' => $v['ZORGADDR'],
        'ep_addrcode' => $v['ZORGZIP'],
        'cta_addrcode' => $v['ZORGZIP'],
        'prod_addrcode' => $v['ZORGZIP'],
        'ep_phone' => $v['ZORGTEL'],
        'ep_fax' => $v['ZORGFAX'],
        'delegate' => $v['ZORGLEADER'],
        'nature' => $v['ZORGTYPEID'],
        'capital' => $v['ZORGREGCAP'],
        'currency' => $v['ZCAPMONTYPE'],
        'ep_amount' => $v['ZORGSIZE'],
        //合同来源
        'ctfrom' => '01000000',
        'old_id' => $date . '|' . $v['ZSQL_ID']
    );
    // p($v);
    $eid      = get_eid($new_ep);
    //新增证书
    $new_cert = array(
        'eid' => $eid,
        'ctfrom' => '01000000',
        'is_check' => 'y',
        'mark' => $v['ZAUDFLAGS'],
        'cert_name' => $v['ZORGNAME'],
        's_date' => $v['ZREGIDATE'],
        'e_date' => $v['ZREGITODATE'],
        'first_date' => $v['ZFAACDATE'],
        'certno' => $v['ZOSCID'],
        'status' => $v['ZCHANGEID'],
        'old_id' => $date . '|' . $v['ZSQL_ID']
    );
    //换证信息
    if ($v['ZCHGGROUP']) {
        $new_cert['is_change']     = 1;
        $new_cert['change_type']   = $v['ZCERTSTATUS'];
        $new_cert['old_certno']    = $v['ZOLDREGIID'];
        $new_cert['old_cert_name'] = $v['ZOLDORGNAME'];
        $new_cert['change_date']   = $v['ZNEWREGDATE'];
    }
    $cert_id = get_cert_id($new_cert, true);
    if ($cert_id) {
        //合同
        $new_ct = array(
            'eid' => $eid,
            'ctfrom' => '01000000',
            'status' => '3'
        );
        $ct_id  = $db->insert('contract', $new_ct);
        //生成合同编码
        $db->update('contract', array(
            'ct_code' => 'CT-' . $ct_id
        ), array(
            'ct_id' => $ct_id
        ));
        //合同项目
        $new_cti = array(
            'eid' => $eid,
            'ctfrom' => '01000000',
            'ct_id' => $ct_id,
            'mark' => $v['ZAUDFLAGS'],
            'total' => $v['ZORGSYSSIZE'],
            'iso' => substr($v['ZProCode'], 0, 3),
            'audit_ver' => $v['ZProCode'],
            'audit_code' => $v['ZSPESORTS'],
            'use_code' => $v['ZSPESORTS'],
			'audit_type'=>map_audit_type(trim($v['ZAUDCODE']),$v['ZSURTIMES']),
			
            'scope' => $v['ZREGIRANGES'],
            'renum' => $v['ZAGAINTIMES'],
            'risk_level' => $v['ZRISKCOEF'],
            'old_id' => $date . '|' . $v['ZSQL_ID']
        );
		//身份令牌:验证
		if(!$new_cti['audit_type']){
			echo $v['ZAUDCODE'];
			echo '<br>';	
			
		}
		
        $cti_id  = $db->insert('contract_item', $new_cti);
        //生成合同编码 
        $db->update('contract_item', array(
            'cti_code' => 'CTI-' . $cti_id
        ), array(
            'cti_id' => $cti_id
        ));
        //关联证书与合同项目
		//集成合同项目信息到证书表
		$cti_info=load('contract_item')->get(array('cti_id'=>$cti_id));
		
        $db->update('certificate', array(
            'ct_id' => $ct_id,
            'cti_id' => $cti_id,
			
			
            'ct_code'=>'CT-' . $ct_id,
			 'cti_code'=>$cti_info['cti_code'],
			 'iso'=>$cti_info['iso'],
			  'total'=>$cti_info['total'],
			   'audit_ver'=>$cti_info['audit_ver'],
			   'audit_code'=>$cti_info['audit_code'],
			   'use_code'=>$cti_info['use_code'],
			   'cert_scope'=>$cti_info['scope'],
			   
        ), array(
            'id' => $cert_id
        ));
    }
    //导入变更信息
    if ($v['ZTYPECODES']) {
        $cert_id   = $db->getField('certificate', 'id', array(
            'certno' => $new_cert['certno']
        ));
        $new_chang = array(
            'zsid' => $cert_id,
            'status' => 1,
            'ctfrom' => '01000000',
            'cg_type' => $v['ZTYPECODES'],
            'cg_type_report' => $v['ZTYPECODES'],
            'cgs_date' => $v['ZCHANGEDATE']
        );
        //系统变更类型 
        if ($v['ZSUSSTADATE']) {
            $new_chang['cg_type']   = '97_01';
            $new_chang['cg_reason'] = $v['ZPCID'];
            $new_chang['cge_date']  = $v['ZSUSENDDATE'];
        }
        if ($v['ZRCDATE']) {
            $new_chang['cg_type']   = '97_03';
            $new_chang['cg_reason'] = $v['ZRCID'];
            $new_chang['cge_date']  = $v['ZRCDATE'];
        }
        load('change')->add($new_chang);
    }
}