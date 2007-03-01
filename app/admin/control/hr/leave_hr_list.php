<?php
extract( $_GET, EXTR_SKIP );
$hire_array = array(
    1 => '在职',
    2 => '离职'
);
$fields = $join = $where = '';

$ {
    'status_' . $is_hire . '_tab'
} = ' ui-tabs-active ui-state-active';
$ct30 = (int)getgp('ct30');
if ($ct30) {
    $curr_date = mysql2date('Y-m-d', current_time('mysql')); //今天
    $month_1 = thedate_add($curr_date, 1, 'month');
    //即将到期的聘用合同
    $where.= "AND cte_date > '$curr_date' AND cte_date < '$month_1'";
}
$is_hire = max(1, intval($is_hire));
$name = trim($name);
if ($name) {
    $where.= " AND name like '%$name%' ";
}
$easycode = trim($easycode);
if ($easycode) {
    $where.= " AND easycode like '%$easycode%' ";
}
$code = trim($code);
if ($code) {
    $where.= " AND code like '%$code%' ";
}
//人员来源限制
$len = get_ctfrom_level(current_user('ctfrom'));
if ($ctfrom && substr($ctfrom, 0, $len) == substr(current_user('ctfrom') , 0, $len)) {
    $_len = get_ctfrom_level($ctfrom);
    $len = $_len;
} else {
    $ctfrom = current_user('ctfrom');
}
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

$where.= " AND ctfrom >= '$ctfrom' AND ctfrom < '$ctfrom_e' ";

$ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);

if ($department) {
    $where.= " AND department = '$department' ";
    $department_select = str_replace("value=\"$department\">", "value=\"$department\" selected>", $department_select);
}
if ($areacode) { //省份搜索
    $pcode = substr($areacode, 0, 2) . '0000';
    $where.= " AND LEFT(areacode,2) = '" . substr($areacode, 0, 2) . "'";
    $province_select = str_replace("value=\"$pcode\">", "value=\"$pcode\" selected>", $province_select);
}
if ($audit_job || $audit_job == '0') {
    $where.= " AND audit_job = '$audit_job' ";
    $audit_job_select = str_replace("value=\"$audit_job\">", "value=\"$audit_job\" selected>", $audit_job_select);
}
$job_type = getgp('job_type');
if ($job_type) {
    $where.= " AND job_type LIKE '%1004%'";
}
$age_limit = getgp('age_limit');
if ($age_limit) {
    $curr_date = mysql2date('Y-m-d', current_time('mysql'));
    $date_65 = mysql2date('Y-m-d', thedate_add($curr_date, -65, 'year'));
    $note_65 = mysql2date('Y-m-d', thedate_add($date_65, 3, 'month'));
    if ($age_limit == 'age65') {
        $where.= " AND birthday < '$date_65'";
    } elseif ($age_limit == 'age65_prev3') {
        $where.= " AND birthday < '$note_65' AND birthday > '$date_65'";
    }
}
$where.= " AND sp_hr.deleted = 0";

//判断请假的角色默认只看到自己的
$code   = $_SESSION['userinfo']['id'];
$admin  = $_SESSION['userinfo']['username'];
// 判断自己的数据
//屏蔽分页
$renyuanarr = array('1','92','123','95','98','99','181');//1.wuqianhui、2.liyan、3.sunpei、4.wangchenxuan、5.wangxiaolu、6.wuhaiyan、7、guanliyuan 
$isin       = in_array($code,$renyuanarr);
if($isin)
{
    if (!$export) 
    {
        $hire_total = array(
            1 => 0,
            2 => 0,
            3 => 0
        );
        $query = $db->query("SELECT sp_hr.is_hire,COUNT(*) total FROM sp_hr $join WHERE 1 $where GROUP by sp_hr.is_hire");
        while ($rt = $db->fetch_array($query)) {
            $hire_total[$rt['is_hire']] = $rt['total'];
        }
        $pages  = numfpage($hire_total[$is_hire], 20, $url_param);
    }
}else{
    $where.= " AND id = '$code' ";
}
if ($is_hire) 
{
    $where.= " AND is_hire = '$is_hire' ";
}

$sql = "SELECT * FROM sp_hr $join WHERE 1 $where ORDER BY id DESC $pages[limit]";
//判断请假的角色默认只看到自己的
// $user_sys  = $_SESSION['extraInfo']['userType'];
// $admin  = $_SESSION['userinfo']['username'];
// 
// if ($user_sys=="stuff" && $admin!='admin') {
//     $URL  = substr($_SERVER['HTTP_REFERER'],0,strrpos($_SERVER['HTTP_REFERER'],"/"));
//     header("Location: ".$URL."/?c=hr&a=leave_edit&uid=".$code);
//     exit;
// }
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
    $rt['audit_job'] = f_audit_job($rt['audit_job']);
    $rt['areacode'] = f_region_province($rt['areacode']); //取省地址
    //$rt['sex']		= $rt['sex'] ;
    if ($rt['sex'] == '1') {
        $rt['sex'] = '男';
    } elseif ($rt['sex'] == '2') {
        $rt['sex'] = '女';
    }
    $rt['is_hire'] = $rt['is_hire'];
    $rt['department'] = f_department($rt['department']);
    $rt['mail'] = $user->meta($rt['id'], 'mail');
    $rt['note'] = $user->meta($rt['id'], 'note');
    $users[$rt['id']] = $rt;
}

if (!$export) {
    tpl();
} else {
    ob_start();
    tpl('xls/list_hr');
    $data = ob_get_contents();
    ob_end_clean();
    export_xls($hire_array[$is_hire] . '人员', $data);
}
?>
