<?php
/*
*审核计划上报
*/

$_tids = array_unique(explode(',', getgp('tid')));
    foreach ($_tids as $tid) {
        if ($tid)
		$db->update('project', array(
            'is_bao' => 1,
            'bao_date' => current_time('mysql') ,
            'bao_uid' => current_user('uid')
        ) , array(
            'tid' => $tid
        ));
    }
    if ($_tids) {
    	//上报后更新的字段应该是项目表的，而不是任务表的
        
       echo "ok";
    } else {
        echo "no";
    }
