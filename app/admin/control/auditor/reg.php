<?php
//注册资格

 if (!$uid) {
        echo '错误提示，没有关联人员';
        exit;
    } else {
        $where = " and uid='$uid' and status='1' ";
    }
    require_once (ROOT . '/data/cache/qualification.cache.php');
    require_once (ROOT . '/data/cache/audit_ver.cache.php');
    require_once (ROOT . '/data/cache/job_type.cache.php'); //人员性质 多项
    require_once (ROOT . '/data/cache/audit_job.cache.php'); //审核性质
    require_once (ROOT . '/data/cache/iso.cache.php');
   
    $status_arr = array(
        '1' => "<font color='blue'>有效</font>",
        '0' => "<font color='red'>失效</font>"
    );
    $qualification = load('qualification');
    $row = $qualification->get($id);
    $user_info = $user->get($uid);
    if ($row != NULL) {
        extract($row, EXTR_SKIP);
    }
    $total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification $join WHERE 1 $where");
    $pages = numfpage($total, 5, "?c=$c&a=$a&uid=$uid&id=$id");
    $query = $db->query("SELECT * FROM sp_hr_qualification $join WHERE 1 $where ORDER BY id DESC $pages[limit]");
    while ($rt = $db->fetch_array($query)) {
        $datas[] = $rt;
    }
    $youxiao = 'checked';
	$qualification_select=f_select('qualification');
    if ($audit_ver_array) {
        foreach ($audit_ver_array as $code => $item) {
            $iso_ver_select.= "<option value=\"$item[audit_ver]\">$item[msg]</option>";
        }
    }
    tpl();