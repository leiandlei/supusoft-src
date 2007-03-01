<?php
 
//审核员-专业能力列表
$code = '';
$fields = $join = $where = '';
foreach ($_GET as $k => $v) {
    $ {
        $k
    } = getgp($k);
}
$where = " and hac.uid='$uid' ";
//代码
if ($audit_code) {
    $where.= " AND audit_code like '%$audit_code%' ";
}
if ($audit_code) {
    $where.= " AND use_code like '%$use_code%' ";
}
//体系
if ($iso) {
    $where.= " AND iso = '$iso' ";
    $iso_select = str_replace("value=\"$iso\">", "value=\"$iso\" selected>", $iso_select);
}
f_arctype($code);
$join = ' left join sp_hr as h on h.id=hac.uid ';
//统计
$total = $db->get_var("SELECT COUNT(*) FROM sp_hr_audit_code as hac $join WHERE 1 $where");
$pages = numfpage($total, 20, "?c=$c&a=$a");
$sql = "SELECT *,hac.id as id,h.id as uid FROM sp_hr_audit_code hac $join WHERE 1 $where and hac.deleted=0 ORDER BY hac.id DESC $pages[limit]";
$query = $db->query($sql);
while ($rt = $db->fetch_array($query)) {
    $rt['hrid'] = $rt['uid'];
    $rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
    $rt['source'] = f_source($rt['source']); //合同来源
    $rt['qua_type'] = f_qua_type($rt['qua_type']);
    $rt['audit_job'] = f_audit_job($rt['audit_job']); //专职兼职
    $rt['department'] = f_department($rt['department']);
    $rt['iso'] = f_iso($rt['iso']);
    $datas[] = $rt;
}
 
tpl();
?>

