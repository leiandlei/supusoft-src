<?php
/*
 * 审核安排
 */
$tid     = (int)getgp('tid');
if(!empty($tid))$ct_id   = $db->get_var("select ct_id from sp_project where `tid` = $tid and deleted='0'");
if ($step) {
    $audit_ver = getgp('audit_ver');
    $audit_ver = implode(',', $audit_ver);
    $iso       = getgp('iso');
    $iso       = implode(',',$iso);
    // echo $audit_ver;
    // exit;
	$pid      = getgp('pid');
    //添加/修改 
    $tb_date  = getgp('tb_date');
    $te_date  = getgp('te_date');
    $wb_db    = getgp('wb_db');
    $is_site  = getgp('is_site');
    $eid      = (int)getgp('eid');
    $ctfrom   = $db->get_var("select ctfrom from sp_enterprises where `eid` = $eid and deleted='0'");
    $new_task = array(
        'eid'       => (int)getgp('eid'),
        'ct_id'     => getgp('ct_id'),
        'audit_ver' => $audit_ver,
        'audit_type'=> getgp('audit_type'),
        'iso'       => $iso, 
        'tk_num'    => getgp('jh_num'), //总人天
        'tb_date'   => $tb_date, //开始日期
        'te_date'   => $te_date, //结束日期
        'jiehe'     => getgp('jiehe'), //结合度
        'zizhi'     => getgp('zizhi'), //资质提示
        'fwbg_note' => getgp('fwbg_note'), //审核范围变更提示
        'rrbg_note' => getgp('rrbg_note'), //审核人日变更及增减理由提示
        'tsxx_note' => getgp('tsxx_note'), //审核方案管理人员提示审核组信息
        'zyxx_note' => getgp('zyxx_note'), //申请评审/合同评审的重要信息传递
        'qita_note' => getgp('qita_note'), //其他应许特别关注的问题
        'note'      => getgp('task_note'), //备注
        'self_note' => getgp('self_note'), //备注 
        'ctfrom'    => $ctfrom, //备注 
        'wb_db'     => $wb_db,//外包倒班情况
        'if_push'   => getgp('if_push'),   // 微信任务是否推送  1、不推 2推送
    );

    /* 处理体系 */
    $st_nums  = getgp('st_num');
	if(!$st_nums){
		echo '<script>alert("人日不能为空")</script>';
		showmsg("人日不能为空","error","?c=audit&a=list_wait_arrange");
 		exit; 
	}
    $pids     = array_keys($st_nums);
    if ($tid) {
		$_uids=$db->getCol('task_audit_team','uid',array('tid'=>$tid,'deleted'=>'0'));
        $_uids = array_merge($_uids, array(
            -1
        )); 
        $tat   = $db->get_row("SELECT id,eid FROM `sp_task_audit_team` WHERE `uid` IN (" . join(",", $_uids) . ") AND ((`taskBeginDate` >= '$tb_date' AND  `taskBeginDate`<='$te_date') or ( `taskEndDate` <= '$te_date' AND `taskEndDate` >= '$tb_date') or (`taskBeginDate` <= '$tb_date' AND `taskEndDate` >= '$te_date')) AND `deleted` = '0' AND tid <>'$tid'");
        //修改计划同步修改每个人的时间
        $task->edit($tid, $new_task, 1);
        if ($tat[id] and $tat[eid] != getgp('eid')) {
            showmsg("有审核员行程与计划时间冲突", "error", null,10);
        }
		
    } else {
        $tid = $task->add($new_task);
		$task_info=$task->get(array('id',$tid));
		log_add($task_info['eid'],0,'新增任务','',serialize($task_info));
    }
	
   
	//=============================外包倒班、一阶段是否非现场 合同表同步修改=============================//
    $db->update("contract", array("wb_db" => $wb_db,'is_site'=>$is_site), array("ct_id" => $ct_id));
	//=============================外包倒班、一阶段是否非现场 合同表同步修改=============================//
    
    if ($st_nums) 
    {
        foreach ($st_nums as $pid => $st_num) 
        {
            $audit->edit($pid, array(
                'tid' => $tid,
                'status' => '1',
               // 'st_num' => $st_num //更新人天数
            ));
			$p_list  = $db->get_row("select eid,audit_type from sp_project where id=".$pid);
			$enterpriseList =  $db->getAll('select * from sp_project where eid in('.getAlleid($p_list['eid']).') and audit_type='.$p_list['audit_type'].' and tid=0 and deleted =0'); 
		
			foreach($enterpriseList as $enterpriseList)
			{
				$audit->edit($enterpriseList['id'], array(
                'tid'    => $tid,
                'status' => '1',
           		 ));
			}
			
			
        }
    }
	showmsg("已修改，请核对审核员时间安排", null, "?c=task&a=edit&tid=$tid",10);
//  showmsg('success', 'success', "?c=task&a=edit_send&tid=$tid");
} else {
    //取信息
    $pids     = getgp('pid');
    $tsct_id  = getgp('ct_id');
	
    $projects = array();
    if ($pids) {
        $where = " AND id IN (" . implode(',', $pids) . ")";
    }elseif($tid) {
        $task = load('task');
        $row  = $task->get(array(
            'id' => $tid
        ));

        extract($row, EXTR_SKIP); //获取sp_task信息 
        
        if(!empty($tsct_id))
        {
            $where   = " AND tid = '$tid' and ct_id = '$tsct_id'";
        }else{
            $where   = " AND tid = '$tid'";
        }
    }
    $eid        = 0;
    $tk_num = 0.0;
    $ct_ids     = array(
        -1
    ); 
    // 微信任务是否推送

    $if_push_Y = ($if_push=='1')?'':'checked';
    $if_push_N = ($if_push=='1')?'checked':'';
   
	/*本次安排的项目*/
    $sql        = "SELECT id,eid,cti_id,ct_id,tid,cti_code,audit_ver,audit_type,st_num,iso FROM sp_project WHERE 1 $where order by iso";  
    
    $query      = $db->query($sql); 
    while ($rt = $db->fetch_array($query)) {
        $rt['audit_type_V']   = f_audit_type($rt['audit_type']);
        $rt['audit_ver_V']    = f_audit_ver($rt['audit_ver']);
        $ct_ids[]             = $rt['ct_id'];
        $eid                  = $rt['eid'];
        $cti_info             = $db->get_row("SELECT * FROM `sp_contract_item` WHERE `cti_id` = '$rt[cti_id]' and deleted=0");
        if ($rt['audit_type'] == '1003') {
            $sql              = "select st_num from sp_project where eid='$rt[eid]' and cti_id='$rt[cti_id]'  and audit_type='1002' and deleted=0 limit 1";

            $rt['yijieduan']  = $db->get_var($sql); // 一阶段审核人日
        }
		$t_num += $rt['st_num'];


        $jh_num += $rt['st_num'];

        $projects[$rt['id']] = $rt;
		if(in_array($rt[audit_type],array('1003','1007')))
			$ch_ct_id=$rt[ct_id];
 }
    $pids            = array_keys($projects);
    ${'bm_' . $tb_h} = ' selected';
    ${'em_' . $te_h} = ' selected';
    if ($projects) {
        $eids = $audit_types = array();
        foreach ($projects as $project) {
            $eids[]        = $project['eid'];
            $audit_types[] = $project['audit_type'];
        }
        $eids = array_unique($eids);       
 
        if (count($eids) > 1){
            foreach ($eids as $key  =>  $value) {
                $parent_id          =   $db->get_var("select parent_id from sp_enterprises where eid='$value'");
                if(  $parent_id     ==  0){
                    $parent_eid     =   $value;
                    $parent_eids[]  =   $value;
                };
                $parent_eids_num    =   count($parent_eids);
                // print_r($parent_eid.'-');print_r($parent_eids);print_r('-'.$parent_eids_num);echo '<hr/>';  
                if ($value!=$parent_eid && $parent_id!=$parent_eid || $parent_eids_num>=2) {
                    showmsg('不同企业不能结合审核！', 'success', $REQUEST_URI);
                };  
            }           
        }//若多家公司非母子公司关系,则出现'不同企业不能结合审核！'的提示信息
        
        // if (count($eids) > 1)
        //     showmsg('不同企业不能结合审核！', 'success', $REQUEST_URI);
        $audit_types = array_unique($audit_types);
        if (in_array('1002', $audit_types) && in_array('1003', $audit_types))
            showmsg('一阶段与二阶段不能结合审核！', 'success', $REQUEST_URI);
			
    }
    $is_site=$db->get_var("SELECT is_site FROM `sp_contract` ct WHERE ct.`ct_id`='$ct_id' and `deleted`='0'");
    $is_site_Y = ($is_site)?'checked':'';
    $is_site_N = ($is_site)?'':'checked';
    $ct_ids      = array_unique($ct_ids);
    /* 企业下的未安排项目 */
    $ct_projects = array();
    $query       = $db->query("SELECT * FROM sp_project WHERE eid ='$eid' AND id NOT IN (" . implode(',', $pids) . ") AND status IN (0,5,6) AND deleted=0  ORDER BY id DESC");
    while ($rt = $db->fetch_array($query)) {
        $rt['audit_type_V']     = f_audit_type($rt['audit_type']);
        $rt['audit_ver_V']      = f_audit_ver($rt['audit_ver']);
		if(!empty($rt['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $rt['audit_code_2017']));
			$codeims   = '';
			foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			$rt['audit_code_2017'] = $codeims;
		}
		if(!empty($rt['audit_code']))
		{
			$codeList  = array_filter(explode('；', $rt['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			$rt['audit_code'] = $codeims;
		}
		
        $ct_projects[$rt['id']] = $rt;
    }
	//print_r($projects);exit;
	//只是取ct_id
	if($tid){
		$ct_id =$db->get_var("SELECT ct_id FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0' ORDER BY `ct_id` DESC ");
	    $tk_num=$db->get_var("SELECT tk_num FROM `sp_task` WHERE `id` =$tid");
    }
	else{
	   $ct_id  = $db->get_var("SELECT ct_id FROM `sp_project` WHERE `id` IN(".join(",",$pids).") AND `deleted` = '0' ORDER BY `ct_id` DESC ");  
	   $tk_num = $db->get_var("SELECT st_num FROM `sp_project` WHERE `id` IN(".join(",",$pids).") AND `deleted` = '0' ORDER BY `ct_id` DESC ");  
    }
	//外包倒班情况
    $wb_db_old=$db->get_var("SELECT wb_db FROM `sp_contract` ct WHERE ct.`ct_id`='$ct_id' and `deleted`='0'");
    tpl('task/edit');
}
	function copctfrom()
	{
	    $tkctfrom = $db->getAll("select * from  sp_task where deleted=0");
	    foreach ($tkctfrom as $value) 
	    {
	        $enctfrom = $db->get_var("select * from  sp_enterprises where eid='".$value['eid']."' deleted=0");
	        if ($value['ctfrom']!=$enctfrom) 
	        {
	            $uptkctfrom['ctfrom'] = $enctfrom;
	            $db -> update( 'task',$uptkctfrom,array('id'=>$value['id']),false );
	        }
	    }
	
	}
	//获取子母公司所有的eid
	function getAlleid($eid=0)
	{
		global $db;
		if(empty($eid))return null;$eidList = array();
		$enterprise = $db->getOne('select * from sp_enterprises where eid='.$eid);
	
		if( $enterprise['parent_id']!='0' )
		{
			$eidList   = getEnterpriseList($enterprise['parent_id']);
		}else{
			$_enterpriseList = $db->getAll('select * from sp_enterprises where parent_id='.$enterprise['eid']);
			$eidList[] = $enterprise['eid'];
			foreach($_enterpriseList as $item)
			{
				
				$eidList[] = $item['eid'];
			}
		}
		$eidList   =  implode(array_filter(array_unique($eidList)),',') ;
		return $eidList;
	}
?>
