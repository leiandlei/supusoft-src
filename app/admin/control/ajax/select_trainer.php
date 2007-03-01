<?php


//@wangp 选择培训人员 2013-09-18 16:11
    $taskBeginDate = getgp('start_date');
    $taskEndDate = getgp('end_date');
    //已派人员
    $plan_hrs = array();
    if ($taskBeginDate && $taskEndDate) {
        $query = $db->query("SELECT ta.uid,ta.taskBeginDate,ta.taskEndDate,e.ep_name FROM sp_task_auditor ta
			LEFT JOIN sp_enterprises e ON e.eid = ta.eid
			WHERE ta.deleted = 0 AND (
				(ta.taskBeginDate >= '$taskBeginDate' AND ta.taskBeginDate <= '$taskEndDate')
			OR
				( ta.taskEndDate >= '$taskBeginDate' AND ta.taskEndDate <= '$taskEndDate' ) )");
        while ($rt = $db->fetch_array($query)) {
            $plan_hrs[$rt['uid']] = $rt;
        }
    }
    //读取人员
    $total = $db->get_var("SELECT COUNT(*) FROM sp_hr");
    $pages = numfpage($total, 20, "");
    $hrs = array();
    $query = $db->query("SELECT * FROM sp_hr WHERE is_hire = '1' $pages[limit]");
    while ($rt = $db->fetch_array($query)) {
        $rt['sex_V'] = ($rt['sex'] == 1) ? '男' : '女';
        $hrs[] = $rt;
    }
    tpl('ajax/select_trainer');