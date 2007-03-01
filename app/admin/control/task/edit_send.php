<?php
//////////////////新增派人////////////////
//多体系派人
$tid       = getgp("tid");
$ct_id     = getgp("ct_id");
$task_info = $db->get_row(" SELECT * FROM sp_task WHERE id=$_GET[tid]"); //取任务信息

//合同信息
if(!empty($ct_id))
{
   $query     = $db->query("select ct_id,iso,use_code,use_code_2017,cti_code,audit_code,audit_code_2017 from sp_project where tid='$tid' and ct_id='$ct_id' and deleted=0 order by iso");
}else{
   $query     = $db->query("select ct_id,iso,use_code,use_code_2017,cti_code,audit_code,audit_code_2017 from sp_project where tid='$tid' and deleted=0 order by iso");   
}
$ct_ids    = array();
$use_code  = array();
while ($r  = $db->fetch_array($query)) {

	if(!empty($r['audit_code_2017']))
	{
		$codeList  = array_filter(explode('；', $r['audit_code_2017']));
		$codeims   = '';
		foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		$r['audit_code_2017'] = $codeims;
	}
	if(!empty($r['audit_code']))
	{
		$codeList  = array_filter(explode('；', $r['audit_code']));
		$codeims   = '';
		foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
		$r['audit_code'] = $codeims;
	}
	
		
    $ct_ids[$r[iso]]   = $r[ct_id];
    $use_code[$r[iso]] = $r;  
    $log_cti_codes.=$r['cti_code'].';'; //日志使用
}

$finance_require = $is_site_V = "";
foreach ($ct_ids as $iso => $ct_id) {
    $ct_info = $db->get_row("select finance_require,is_site from sp_contract where ct_id='$ct_id' and deleted=0");
    if ($ct_info[finance_require])
        $finance_require .= $ct_info[finance_require] . " ";
    if ($ct_info[is_site])
        $is_site_V .= f_iso($iso) . ":是  ";
    else
        $is_site_V .= f_iso($iso) . ":否  ";
}

unset($ct_ids, $query);

 
//默认任务时间
if (!$_GET['auditor_id']) {

    $auditor_info['taskBeginDate'] = $task_info['tb_date'];
    $auditor_info['taskEndDate']   = $task_info['te_date'];
    
}
$isos_list = $audit_ver = $audit_type = array();

$_query    = $db->query("SELECT id,iso,audit_ver,audit_type FROM sp_project where tid='$_GET[tid]' and deleted=0 order by iso");
while ($_r = $db->fetch_array($_query)) {
    $isos_list[$_r[id]] = $_r[iso];
    $audit_type[]       = $_r[audit_type];
    $audit_ver[]        = $_r[audit_ver];
}
unset($_query, $_r);
$pid = $db->get_results(" SELECT id FROM sp_project where tid=$_GET[tid] "); //审核项目主键
////////////删除派人信息////////////
//删除派人明细表
if ($_GET['type'] == 'del') 
{
    $db->update('task_audit_team', array(
        'deleted' => 1
    ), array(
        'id' => $_GET['id']
    ));
   showmsg("success","success","?c=task&a=edit_send&tid=$tid");

}
////////编辑派人信息///////////////
 // 显示需要编辑的信息
if ($id = $_GET['id']) 
{ 

    $auditor_info = $db->find_one('task_audit_team', array(
             'id' => $id
    ));
    $query = $db->query("SELECT * FROM `sp_task_audit_team` WHERE `uid` = '$auditor_info[uid]' AND `taskBeginDate` = '$auditor_info[taskBeginDate]' AND `taskEndDate` = '$auditor_info[taskEndDate]'");
    while ($rt = $db->fetch_array($query)) {
        $task_audit_team_list[$rt['iso']] = $rt;

    }
    ///点击修改被见证人员编号变成名字
    foreach ($task_audit_team_list as $key => $value) 
    {
        if (!empty($task_audit_team_list[$key]['witness_person'])) 
        {
        	
            $sql = "select name from sp_hr where id=".$task_audit_team_list[$key]['witness_person']." and deleted=0";
            $task_audit_team_list[$key]['witness_person_name'] = $db->get_var($sql);
			
        }
//		if(!empty($value['audit_code_2017']))
//		{
////			echo "<pre />";
////			print_r($value);exit;
//			$codeList1  = array_filter(explode('；', $value['audit_code_2017']));
//
//			$codeims1   = '';
//			
//			foreach($codeList1 as $code1)$codeims1 .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code1).'；';
//			$task_audit_team_list[$key]['audit_code_2017'] = $codeims1;
//		}
//		if(!empty($value['audit_code']))
//		{
//			$codeList2  = array_filter(explode('；', $value['audit_code']));
//			
//			$codeims2   = '';
//			foreach($codeList2 as $code2)$codeims2 .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code2).'；';
//			$task_audit_team_list[$key]['audit_code'] = $codeims2;
//		}
    
   	}

    if ($_POST) {

        //更新派人详细表
        foreach ($_POST['iso'] as $k => $v) 
        {
            //role 审核员角色 1004 是空
            if (!$v || !$_POST['qua_type'][$k])
                continue;
            if ($_POST['role'][$k] == '01') 
            {
                $f = $db->get_var("SELECT uid FROM `sp_task_audit_team` WHERE `tid` = '$tid'  AND `iso` = '$v' AND `role` = '01' and deleted=0");
                if ($f and $f != $_POST['uid']) 
                {
                    showmsg('一个体系只能有一个组长', "error", "?c=task&a=edit_send&tid=$tid#tab-contract");
                    exit;
                }
            }
            $p_info              = $db->get_row("SELECT id,iso,audit_type,audit_ver,ctfrom FROM `sp_project` WHERE `id` = '$k' ");
//          $up_item             = array(
//              'uid'            => $_POST['uid'],
//              'sort'           => $_POST['sort'],
//              'name'           => $_POST[name],
//              'ctfrom'         => $p_info[ctfrom], //合同来源从合同项目表
//              'audit_ver'      => $p_info[audit_ver],
//              'audit_type'     => $p_info[audit_type],
//              'role'           => $_POST['role'][$k],
//              'witness'        => $_POST['witness'][$k],
//              'witness_person' =>$_POST['witness_person'][$k],
//              'qua_type'       => $_POST['qua_type'][$k],
//              'audit_code'     => $_POST['audit_code'][$k],
//              'use_code'       => $_POST['use_code'][$k],
//              'taskBeginDate'  => $_POST['taskBeginDate'],
//              'taskEndDate'    => $_POST['taskEndDate']
//          );更新时间不更新到审核员任务的时间
			$up_item             = array(
                'uid'            => $_POST['uid'],
                'sort'           => $_POST['sort'],
                'name'           => $_POST[name],
                'ctfrom'         => $p_info[ctfrom], //合同来源从合同项目表
                'audit_ver'      => $p_info[audit_ver],
                'audit_type'     => $p_info[audit_type],
                'role'           => $_POST['role'][$k],
                'witness'        => $_POST['witness'][$k],
                'witness_person' =>$_POST['witness_person'][$k],
                'qua_type'       => $_POST['qua_type'][$k],
                'audit_code'     => $_POST['audit_code'][$k],
                'use_code'       => $_POST['use_code'][$k],
                'audit_code_2017'=> $_POST['audit_code_2017'][$k],
                'use_code_2017'  => $_POST['use_code_2017'][$k],
                'taskBeginDate'  => $_POST['taskBeginDate'],
                'taskEndDate'    => $_POST['taskEndDate']
            );
            // echo "<pre />";
            //  print_r($up_item);exit;
            $_auditor_info = $db->find_one('task_audit_team', array(
                'id' => $id
            ));
            $db->update('task_audit_team', $up_item, array(
                'uid' => $_auditor_info[uid],
                "tid" => $tid,
                "iso" => $v
            ));
        }
     
    
        unset($_POST, $_auditor_info, $auditor_info, $task_audit_team_list, $_GET['id']);
        $auditor_info['taskBeginDate'] = $task_info['tb_date'];
        $auditor_info['taskEndDate']   = $task_info['te_date'];
    }
}
 //保存新增派人信息      
if ($_POST) 
{ 
    if ($uid = $_POST['uid']) 
    {
        //派人明细表 
        foreach ($_POST['iso'] as $k => $v) 
        {
            if (!$v || !$_POST['qua_type'][$k])//必须选择组内身份和资格
                continue;
            if ($_POST['role'][$k] == '01') 
            {
                $f = $db->get_var("SELECT uid FROM `sp_task_audit_team` WHERE `tid` = '$tid'  AND `iso` = '$v' AND `role` = '01' and deleted=0");
                if ($f and $f != $_POST['uid']) 
                {
                    showmsg('一个体系只能有一个组长', "error", "?c=task&a=edit_send&tid=$tid#tab-contract");
                    exit;
                }
            }      
            
            $p_info              = $db->get_row("SELECT id,iso,audit_type,audit_ver,ctfrom FROM `sp_project` WHERE `id` = '$k' ");
            $new_item            = array(
                'eid'            => $task_info['eid'],
                'tid'            => $_GET['tid'],
                'pid'            => $p_info['id'],
                'uid'            => $uid,
                'sort'           => $_POST['sort'],
                'name'           => $_POST['name'], 
                'ctfrom'         => $p_info['ctfrom'],
                'audit_ver'      => $p_info['audit_ver'],
                'audit_type'     => $p_info['audit_type'],
                'iso'            => $v,
                'role'           => $_POST['role'][$k],
                'witness'        => $_POST['witness'][$k],
                'witness_person' => $_POST['witness_person'][$k],
                'qua_type'       => $_POST['qua_type'][$k],
                'audit_code'     => $_POST['audit_code'][$k],
                'use_code'       => $_POST['use_code'][$k],
                'audit_code_2017'=> $_POST['audit_code_2017'][$k],
                'use_code'       => $_POST['use_code'][$k],
                'taskBeginDate'  => $_POST['taskBeginDate'],
                'taskEndDate'    => $_POST['taskEndDate']
            );
			if($new_item['taskBeginDate']!='0000-00-00 00:00:00' && $new_item['taskEndDate']!='0000-00-00 00:00:00')
			{
				$tat         = $db->get_row("SELECT * FROM `sp_task_audit_team` WHERE uid='".$new_item['uid']."' AND ((`taskBeginDate` >= '".$new_item['taskBeginDate']."' AND  `taskBeginDate`<= '".$new_item['taskEndDate']."') or ( `taskEndDate` <= '".$new_item['taskEndDate']."' AND `taskEndDate` >= '".$new_item['taskBeginDate']."') or (`taskBeginDate` <= '".$new_item['taskBeginDate']."' AND `taskEndDate` >= '".$new_item['taskEndDate']."'))  AND `deleted` = '0' AND tid <> '".$new_item['tid']."'");
				// print_r($tat);exit;
                if($task_info['eid']!=$tat['eid'] && !empty($tat))
				{
					if(!empty($new_item['uid']))
					{
						$hrname  = $db->get_var("select name from sp_hr where id=".$new_item['uid']);
					}
					// showmsg('审核派人审核员'.$hrname.'时间冲突', "error", "?c=task&a=edit_send&tid=$tid",10);
     //                exit;
				}
			}
            $db->insert('task_audit_team', $new_item);
        }
    }


  //1待派人 2已派人
    $_POST['status']=$_POST['status']?$_POST['status']:1;
        $db->update('task', array(
            'status' => $_POST['status']
        ), array(
                'id' => $_GET['tid']
        ));
      $task_info['status']   = $_POST['status'];
      if($task_info['status']==2)
      {
        log_add($task_info['eid'],'','已派人，项目号：'.$log_cti_codes);
      }
    
}

$action       = $_GET['id'] ? '修改' : '新增';
//任务已派人列表

$auditor_list = $db->get_results(" SELECT * FROM sp_task_audit_team  WHERE tid=$_GET[tid] AND deleted=0 order by sort,iso");
foreach ($auditor_list as $key => $value) 
{
    if (!empty($auditor_list[$key]['witness_person'])) 
    {
        $sql = "select name from sp_hr where id=".$auditor_list[$key]['witness_person']." and deleted=0";
        $auditor_list[$key]['witness_person_name'] = $db->get_var($sql);
    }

}

tpl();
?>
