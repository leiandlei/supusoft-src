<?php
/*
 *---------------------------------------------------------------
 * 自动生成下一个审核阶段
 *---------------------------------------------------------------
 */
set_time_limit(0);
// 半年前的时间段
$month_6 = thedate_add(date('Y-m-d H:i:s'), -6, 'month');

//测试 立即生成监一 正常应该注释掉
//$month_6 = thedate_add(date('Y-m-d H:i:s'), +1, 'month');

//能源的监督频次是3个月
//$month_3 = thedate_add(date('Y-m-d H:i:s'), -3, 'month');
$month_3 = date('Y-m-d H:i:s');
// 生成全部的
if (0) {
    $month_6 = thedate_add(date('Y-m-d H:i:s'), 99, 'month');
}
// 日志文件
$log_file = APP_DIR . "cron/log/create_next-" . date('Y-m') . '.log';
// 本次生成记录数
$log_num  = 0;
// 下一阶段函数
//type <>1 适用于能源  
function get_next_type($audit_type = '', $type = 1)
{
    $result = '';
    if ($type == 1) {
        switch ($audit_type) {
            case '1007':
            case '1003':
                $result = '1004';
                break;
            case '1004':
                $result = '1005';
                break;
            case '1005':
                $result = '1007';
                break;
            default:
                $result = '';
                break;
        }
    } else {
        if ($audit_type == '1006')
            $result = '1011';
        else
            $result = $audit_type + 1;
    }
    return $result;
}
/* $_query = $db->query("SELECT eid FROM `sp_enterprises` WHERE `ep_name` LIKE '0*%'");
$eids   = array(
-1
);
while ($rt = $db->fetch_array($_query)) {
$eids[] = $rt[eid];
}
unset($_query);
$where      = " and p.eid NOT IN (" . join(",", $eids) . ")";
*/
// ALTER TABLE `sp_project` ADD `create_next` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '是否生成下一阶段' AFTER `audit_type` 
/*
 *---------------------------------------------------------------
 * 下一轮监督
 *---------------------------------------------------------------
 */
$audit      = load('audit');

// 取原二阶段、监一未生成的数据
$allow_type = array(
    "1003",
    "1004",
	"1007"
);
//array_pop($audit_type);
$iso_allow  = "A12";
$where .= " AND p.iso !='$iso_allow'";
$join  = " LEFT JOIN sp_task t ON p.tid = t.id";
$sql   = "SELECT p.*, t.te_date FROM sp_project p $join WHERE p.tid!=0 AND p.deleted=0 AND p.audit_type  IN ('" . implode("','", $allow_type) . "') AND t.te_date <= '$month_6' AND p.create_next=0 and p.sp_type = '1' $where";
// echo "<pre />";
// print_r($sql);exit;
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    //项目编号
    $str = substr($rt['cti_code'],5,4);
    $year = (int)$str;
    $year = $year + 1;
    $_cti_code = substr_replace($rt['cti_code'],$year,5,4);
	// 取证书信息
    $zs_info = $db->get_row("SELECT * FROM `sp_certificate` WHERE `cti_id` = '$rt[cti_id]' AND `deleted` = '0' and eid='$rt[eid]' and status IN ('01','02') order by e_date desc");
    if (!$zs_info[id])
        continue;
    $_audit_type = get_next_type($rt[audit_type]);
    //if(!$$_audit_type)continue;
    $sql         = "SELECT id FROM sp_project WHERE cti_id = '$rt[cti_id]' AND audit_type = '$_audit_type' AND deleted = 0";
    //如果下一阶段数据已经存在则更新状态
    if ($db->get_var($sql)) {
        $audit->edit($rt['id'], array(
            'create_next' => 1
        ));
        //如果下一阶段数据不存在则生成
    } else { 
        $st_num=$rt[st_num];
        $cti_info = $db->get_row("SELECT jdxc_num FROM sp_contract_item WHERE cti_id = '$rt[cti_id]'");
		if($rt[audit_type]!='1004')
			$st_num=$cti_info['jdxc_num'];
		//如果当前是二阶段 或者是再认证 取合同评审时监督人日 监督二取监督一的 现场人日数
        $new      = array(
            'eid' => $rt['eid'],
            'ct_id' => $rt['ct_id'], // 合同ID
            'ct_code' => $rt['ct_code'], // 
            'cti_id' => $rt['cti_id'], // 合同项目 
            'cti_code' => $_cti_code, // 
            'ctfrom' => $rt['ctfrom'], // 合同来源
            'st_num' => $st_num, // 取监督现场人日
            'iso' => $rt['iso'], // 体系
            'total' => $rt['total'], // 体系人数
            'audit_ver' => $rt['audit_ver'], // 标准版本
            'audit_code' => $rt['audit_code'], // 审核代码
            'use_code' => $rt['use_code'], // 使用代码
            'mark' => $zs_info['mark'], // 
            'scope' => $zs_info[cert_scope], // 
            'pre_date' => get_addday($rt['te_date'], 11, -1), //预审日期
            'final_date' => get_addday($rt['te_date'], 12, -1), // 最后监审日
            'audit_type' => $_audit_type, // 认证类型
            'status' => 5 //监督维护
        );
        //生成下一阶段
        $audit->add(magic_gpc($new, 1)) && $log_num++;
        // 更新状态
        $audit->edit($rt['id'], array(
            'create_next' => 1
        ));
    }
}

// 日志是同名文件 .txt 含执行时间、生成记录数
$file_res = fopen($log_file, "a");

fwrite($file_res, date('Y-m-d H:i:s') . "		$log_num 监督" . "\r\n"); //写入一行 \n为换行
// fclose($file_res);
/*
暂时不考虑能源
unset($sql, $query);
//生成能源的
$sql   = "SELECT p.*, t.te_date FROM sp_project p $join WHERE p.tid!=0 AND p.deleted=0 AND p.audit_type NOT IN ('" . implode("','", $allow_type) . "') AND t.te_date <= '$month_3' AND p.create_next=0  AND p.eid NOT IN (" . join(",", $eids) . ") AND p.iso='A12'";
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
$zs_info = $db->get_row("SELECT * FROM `sp_certificate` WHERE `cti_id` = '$rt[cti_id]' AND `deleted` = '0' and status='01'");
if (!$zs_info[id])
continue;
$_audit_type = get_next_type($rt[audit_type], 0);
$sql         = "SELECT id FROM sp_project WHERE cti_id = '$rt[cti_id]' AND audit_type = '$_audit_type' AND deleted = 0";
//如果下一阶段数据已经存在则更新状态
if ($db->get_var($sql)) {
$audit->edit($rt['id'], array(
'create_next' => 1
));
//如果下一阶段数据不存在则生成
} else {
// 取证书的范围
$_p_info = $db->get_row("SELECT * FROM sp_project WHERE cti_id = '$rt[cti_id]'  AND deleted = 0 and audit_type !='1007' order by audit_type desc");
$new     = array(
'eid' => $rt['eid'],
'ct_id' => $rt['ct_id'], // 合同ID
'cti_id' => $rt['cti_id'], // 合同项目ID
'ct_code' => $rt['ct_code'], // 
'cti_code' => $rt['cti_code'], // 
'ctfrom' => $rt['ctfrom'], // 合同来源
'iso' => $rt['iso'], // 体系
'st_num' => $_p_info['st_num'] - $_p_info['add_num'] + $_p_info['sup_add_num'], // 人日
'audit_ver' => $rt['audit_ver'], // 版本
'audit_code' => $rt['audit_code'], // 审核代码
'use_code' => $rt['use_code'], // 使用代码
'mark' => $zs_info['mark'], // 
'ctfrom' => $rt['ctfrom'], // 
'scope' => $zs_info[cert_scope], // 
'scope_e' => $zs_info[cert_scope_e], //  
'pre_date' => get_addday($rt['te_date'], 5, -1), //预审日期
'final_date' => get_addday($rt['te_date'], 6, -1), // 最后监审日
'audit_type' => $_audit_type, // 认证类型
'status' => 5
);
// 生成下一阶段
// 评定通过生成下一阶段
$audit->add(magic_gpc($new, 1)) && $log_num++;
// 更新状态
$audit->edit($rt['id'], array(
'create_next' => 1
));
}
}
*/
// 日志是同名文件 .txt 含执行时间、生成记录数
/*$file_res = fopen($log_file, "a");
fwrite($file_res, date('Y-m-d H:i:s') . "		$log_num" . "\r\n");//写入一行 \n为换行
fclose($file_res);*/
/*
 *---------------------------------------------------------------
 * 监二生成再认证
 *---------------------------------------------------------------
 */
// 取原监二未生成的数据
$audit_type = array(
    '1005'
);
$join       = " LEFT JOIN sp_task t ON p.tid = t.id";
$sql        = "SELECT p.*, t.te_date FROM sp_project p $join WHERE p.tid!=0 AND p.deleted=0 AND p.audit_type IN ('" . implode("','", $audit_type) . "') AND t.te_date <= '$month_6' AND p.create_next=0  and p.sp_type='1' $where";
  // echo "<pre />";
  //   print_r($sql);exit;
$query      = $db->query($sql);
$log_num    = 0;
while ($rt = $db->fetch_array($query)) {
    $zs_info = $db->get_row("SELECT * FROM `sp_certificate` WHERE `cti_id` = '$rt[cti_id]' AND eid='$rt[eid]' AND `deleted` = '0' and status IN ('01','02')");

    if (!$zs_info[id])
        continue;
    $sql = "SELECT COUNT(*) FROM sp_ifcation WHERE cti_id = '$rt[cti_id]' AND status = 0 and deleted=0";
    
    //如果下一阶段数据已经存在则更新状态
    if ($db->get_var($sql)) {
        $audit->edit($rt['id'], array(
            'create_next' => 1
        ));
        //如果下一阶段数据不存在则生成
    } else {
        //再认证数据
        $new_ifcation = array(
            'ctfrom' => $rt['ctfrom'],
            'eid' => $rt['eid'],
            'ct_id' => $rt['ct_id'],
            'cti_id' => $rt['cti_id'],
            'pid' => $rt['id'],
            'zs_id' => $zs_info['id'],
            'certno' => $zs_info['certno'],
			'num'=>$db->get_var("SELECT zrz_num FROM `sp_contract_item` WHERE `cti_id` = '$rt[cti_id]'"),
            // 'create_date' => date('Y-m-d'),
            'ifcation_date' => get_addday($rt['te_date'], 12, -1), // 最后监审日
            'status' => 0
        );
        // 生成下一阶段（再认证）
        // 最后监审日必须大于当前时间
        $db->insert('ifcation', magic_gpc($new_ifcation, 1)) && $log_num++;
        // 更新状态
        $audit->edit($rt['id'], array(
            'create_next' => 1
        ));
    }
}

fwrite($file_res, date('Y-m-d H:i:s') . "		$log_num 再认证" . "\r\n"); //写入一行 \n为换行
fclose($file_res);
