<?php

$agreement = getgp('agreement');
if($agreement){
	$query_agree = $db->query("select id,agreement_e_date from sp_hr");
	while($re = $db->fetch_array($query_agree)){
		if($re[agreement_e_date] != null){
			$db->query("update sp_hr set agreement_e_date=date_add(agreement_e_date,INTERVAL 3 YEAR) where id='{$re[id]}'");
		}
	}
	//echo "update sp_hr set agreement_e_date=date_add(agreement_e_date,INTERVAL 3 YEAR)";exit;
}
$hire_array = array(
    1 => '在职',
    2 => '离职'
);
$fields = $join = $where = '';
$url_param = '?';
extract($_GET, EXTR_SKIP);
foreach ($_GET as $key => $val) {
    if ('paged' == $key) continue;
    $url_param.= "$key=$val&";
} 
$url_param = substr($url_param, 0, -1);


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
 
$ccode = trim($_GET['code']);
if ($ccode) {
    $where.= " AND code like '%". $ccode."%' ";
}
if($tel){
	$where.= " AND tel like '%". $tel."%' ";
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
if (!$export) {
    $hire_total = array(
        1 => 0,
        2 => 0,
        3 => 0
    );
    $query = $db->query("SELECT sp_hr.is_hire,COUNT(*) total FROM sp_hr $join WHERE 1 $where GROUP by sp_hr.is_hire");
    while ($rt = $db->fetch_array($query)) {
        $hire_total[$rt['is_hire']] = $rt['total'];
    }
    $pages = numfpage($hire_total[$is_hire], 20, $url_param);
}
if ($is_hire) {
    $where.= " AND is_hire = '$is_hire' ";
}
$sql = "SELECT * FROM sp_hr $join WHERE 1 $where ORDER BY id DESC $pages[limit]";
 
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
   $rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
    $rt['audit_job'] = f_audit_job($rt['audit_job']);
    $rt['areacode'] = f_region_province($rt['areacode']); //取省地址
    if ($rt['sex'] == '1') {
        $rt['sex'] = '男';
    } elseif ($rt['sex'] == '2') {
        $rt['sex'] = '女';
    } 
    $rt['department'] = f_department($rt['department']);
    $rt['mail'] = $user->meta($rt['id'], 'mail');
	$phone==$rt[tel]?$rt[tel]:$rt[tel]=$rt[tel];
	$phone="";
    $rt['note'] = $user->meta($rt['id'], 'note'); /**/
    $users[$rt['id']] = $rt;
}
   tpl();
?>
