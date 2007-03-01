<?php 
//添加于编辑业务代码
$id = getgp('id');
$iso = getgp('iso'); //@HBJ 2013年9月12日 12:01:28 此行缺失$iso得不到
if (!getgp('pass_date')) {
    echo "<script>alert('通过日期不能为空');history.go(-1);</script>";
    exit;
}
//17A代码字符串
$use_code = getgp('use_code');
$arr_code = explode('；', $use_code);
$arr_code = array_unique($arr_code); //去除数组中重复值
foreach ($arr_code as $k => $v) {
    if ($v == '') {
        unset($arr_code[$k]); //去除空值
     }
};

//验证业务代码
$where = " AND iso = '$iso'";
$where.= " AND code IN ('" . join("','", $arr_code) . "')";
$sql = "SELECT code FROM sp_settings_audit_code WHERE 1 $where";
$rs = $db->get_results($sql);
if (!$rs) {
    echo "<script>alert('提交小类系统中有不存在的，请仔细检查');history.go(-1)</script>";
    tpl('hr/hr_code_edit');
    exit;
}

$qua_id=getgp("qua_id");
$qua_info=$db->get_row("SELECT * FROM `sp_hr_qualification` WHERE `id` = '$qua_id' ");
//读取人员已有的代码
$sql = "select use_code from sp_hr_audit_code where qua_id='$qua_id' AND deleted='0' ";
$res = $db->query($sql);
while ($row = $db->fetch_array($res)) {
    $db_info[] = $row['use_code'];
}; //系统中数据项
$uid = (int)getgp('uid');
$hr = $db->get_row("SELECT ctfrom, areacode FROM sp_hr WHERE id = '$uid'");

//合并能力来源
$source=implode('；',getgp('skill_source'));
// 合并评定方法
// $evaluation_methods=implode('；',getgp('evaluation_methods'));
 
foreach ($arr_code as $k=>$p) {
    if (!in_array($p, $db_info) && !empty($p)) { //判断是否已经具有该资质
        $sql = "select `shangbao` from `sp_settings_audit_code` where `code`='".$p."'";
        $arr_audit = $db->getAll($sql);
        $arr_auditCode = array();
        foreach ($arr_audit as $key => $value) {
            $arr_auditCode[]=$value['shangbao'];
        }

        $default = array(
            'uid' => $uid,
            'qua_id' => $qua_id,
            'qua_type' => $qua_info[qua_type],
            'ctfrom' => $hr['ctfrom'],
            'areacode' => $hr['areacode'],
            'iso' => getgp('iso') ,
            'audit_code'=> implode(',',$arr_auditCode),
            'use_code' => $p, //小类代码17A
            'source' => $source , //能力来源
			// 'evaluation_methods'=>$evaluation_methods, //评定方法 
            // 'audit_year' => getgp('audit_year') , //
            // 'audit_study' => getgp('audit_study') , //
            // 'audit_count' => getgp('audit_count') , //
            // 'audit_day' => getgp('audit_day') , //
            'pass_date' => getgp('pass_date') , //
            // 'is_assess' => getgp('is_assess') , //是否专业评定
			 // 'is_profession' => getgp('is_profession') , //是否专业评定
            'note' => getgp('note') , //
            'evaluater' => getgp('evaluater') //评定人员
         );
        if (empty($id)) {
            $id = $auditcode->add($default);
            //日志
            $af_str = serialize($auditcode->get($id));
            log_add(0, $uid, "添加业务代码", '', $af_str);
        }
    }
}
$id = getgp('id');
if ($id) { //编辑业务代码
    $default = array(
        'uid' => $uid,
        'ctfrom' => $hr['ctfrom'],
        'areacode' => $hr['areacode'],
        'iso' => getgp('iso') ,
        'source' => $source , //能力来源
		// 'evaluation_methods'=>$evaluation_methods, //评定方法 
        // 'audit_year' => getgp('audit_year') , //
        // 'audit_study' => getgp('audit_study') , //
        // 'audit_count' => getgp('audit_count') , //
        // 'audit_day' => getgp('audit_day') , //
        'pass_date' => getgp('pass_date') , //合同来源
        // 'is_assess' => getgp('is_assess') , //企业简称
		// 'is_profession' => getgp('is_profession') , //是否专业评定
        'note' => getgp('note') , //企业名称
    	'evaluater' => getgp('evaluater') //评定人员
    );
    $bf_str = serialize($auditcode->get($id));
	  
    $auditcode->edit($id, $default);
    //日志
    $af_str = serialize($auditcode->get($id));
    log_add(0, $uid, "编辑业务代码", $bf_str, $af_str);
}
  $REQUEST_URI = '?c=hr_code&a=edit&uid=' . getgp('uid') . '&iso=' . getgp('iso').'&qua_id='.$qua_id;
  showmsg( 'success', 'success', $REQUEST_URI );

?>

