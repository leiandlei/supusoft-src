<?php
/*
 * 合同评审审批
 */
$ct_id         = getgp('ct_id');
$approval_date = getgp('appr_date'); //审批日期
$approval_note = getgp('appr_note');//审批注释
if (!$ct_id)
    print_json(array(
        'status' => 'no',
        'msg' => '合同ID未异常'
    ));
$curr_status = $db->get_var("SELECT status FROM sp_contract WHERE ct_id = '$ct_id'");
if (3 == $curr_status)
    print_json(array(
        'status' => 'no',
        'msg' => '合同已审批过'
    ));
$ct_items = array();
$sql      = "SELECT * FROM sp_contract_item WHERE 1 AND ct_id = '$ct_id' and deleted=0";
$query    = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $ct_items[$rt['cti_id']] = $rt;
}
if (!$ct_items)
    print_json(array(
        'status' => 'no',
        'msg' => '未找到相关的合同项目'
    ));
$audit   = load('audit');
//合同信息
$ct_info = $db->get_row("SELECT * FROM `sp_contract` WHERE `ct_id` = '$ct_id' ");
if ($ct_items) {
    foreach ($ct_items as $cti_id => $cti_item) {
        if ('1001' == $cti_item['audit_type']) {
            $audit_types = array(
                '1002',
                '1003'
            );
            foreach ($audit_types as $audit_type) 
            {
                if ($audit_type == "1002") {
                    $st_num = $cti_item[yjdxc_num];
                } else
                    $st_num = $cti_item[ejdxc_num];
                $audit->add(array(
                    'eid'        	 => $cti_item['eid'], //组织ID
                    'ct_id'      	 => $cti_item['ct_id'], //合同ID
                    'ct_code'    	 =>$ct_info['ct_code'], //合同ID
                    'cti_id'     	 => $cti_item['cti_id'], //合同项目ID
                    'cti_code'   	 => $cti_item['cti_code'], //合同项目ID
                    'ctfrom'     	 => $cti_item['ctfrom'], //合同来源
                    'iso'        	 => $cti_item['iso'], //体系
                    'total'          => $cti_item['total'],
                    'mark'           => $cti_item['mark'],
                    'audit_ver' 	 => $cti_item['audit_ver'], //标准版本
                    'audit_code'	 => $cti_item['audit_code'], //审核代码
                    'use_code' 	  	 => $cti_item['use_code'], //审核代码
                    'audit_code_2017'=> $cti_item['audit_code_2017'], //审核代码2017
                    'use_code_2017'  => $cti_item['use_code_2017'], //审核代码2017
                    'audit_type' 	 => $audit_type, //审核类型
                    'scope' 	     => $cti_item['scope'], //审批范围
                    'pre_date' 		 => $ct_info['pre_date'], //预审日期
                    "st_num" 	     => $st_num,
                    "zy_name" 		 => $ct_info['major_person'],
                    "audit_note" 	 => $ct_info['note'],
                ));
            }
        } else {
            if ($cti_item['audit_type'] == "1007")
                $st_num = $cti_item[ejdxc_num];
            else
                $st_num = $cti_item[jdxc_num];
            $project_id = $audit->add(array(
                'eid' 			   => $cti_item['eid'], //组织ID
                'ct_id' 		   => $ct_info['ct_id'], //合同ID
                'ct_code' 		   => $ct_info['ct_code'], //合同ID
				'cti_id' 	 	   => $cti_item['cti_id'], //合同项目ID
				'cti_code'	 	   => $cti_item['cti_code'], //合同项目ID
                'ctfrom' 		   => $cti_item['ctfrom'], //合同来源
                'iso' 		       => $cti_item['iso'], //体系
                'total' 	       => $cti_item['total'],
				'mark' 	 	  	   => $cti_item['mark'],
                'audit_ver' 	   => $cti_item['audit_ver'], //标准版本
                'audit_code' 	   => $cti_item['audit_code'], //审核代码
                'use_code' 		   => $cti_item['use_code'], //审核代码
                'audit_type' 	   => $cti_item['audit_type'], //审核类型
                'audit_code_2017'  => $cti_item['audit_code_2017'], //审核代码2017
                'use_code_2017'    => $cti_item['use_code_2017'], //审核代码2017
                'scope' 	 	   => $cti_item['scope'], //审批范围
                'pre_date' 	 	   => $ct_info['pre_date'], //预审日期
                "st_num" 	       => $st_num,
				"zy_name" 		   => $ct_info['major_person'],
                "audit_note" 	   => $ct_info['note'],
            ));
        }
    }
}
$af_info = $db->get_row("select * from sp_contract where ct_id='$ct_id' ");
$db->update('contract', array(
    'status' => 3,
    'approval_date' => $approval_date,
    //'approval_sys_date' => date("Y-m-d"),
    'approval_note' => $approval_note,
    'approval_user' => current_user('name'),
    'approval_uid' => current_user('uid')
), array(
    'ct_id' => $ct_id
));
$bf_info = $db->get_row("select * from sp_contract where ct_id='$ct_id' ");
// 日志
do {
    log_add($bf_info['eid'], 0, "合同审批(状态:已评审->已审批) 合同编号:" . $bf_info['ct_code'] , serialize($af_info), serialize($bf_info));
} while (false);
print_json(array(
    'status' => 'ok',
    'msg' => 'success'
));