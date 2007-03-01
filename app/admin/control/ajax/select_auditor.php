<?php

/*
*选择派人
*/
    require_once (ROOT . '/data/cache/audit_job.cache.php');
    require_once (ROOT . '/data/cache/ctfrom.cache.php');
    require_once (ROOT . '/data/cache/region.cache.php');
    require_once (ROOT . '/data/cache/qualification.cache.php');
    
    $tid = getgp('tid');
    //取任务信息
    $t_info = $db->get_row("SELECT * FROM sp_task WHERE id = '$tid' and deleted = 0");
    $taskBeginDate = getgp("taskBeginDate")." ".getgp("taskBeginTime");
    $taskEndDate = getgp("taskEndDate")." ".getgp("taskEndTime");
    //取任务的 体系、专业代码
    $t_isos = $t_use_codes = $t_use_codes_2017 = $t_projects =$_audit_code_arr = $_audit_code_arr_2017 = array();
    $query = $db->query("SELECT * FROM sp_project WHERE tid = '$tid' AND deleted = 0");
  
    while ($rt = $db->fetch_array($query)) {
           
        $t_isos[$rt['iso']] = f_iso($rt['iso']);
        //$t_use_codes[$rt['iso']] = $rt['audit_code'];
        $t_use_codes_2017[$rt['iso']]     = empty($rt['pd_use_code_2017'])?$rt['use_code_2017']:$rt['pd_use_code_2017'];
        $_audit_code_arr_2017[$rt['iso']] = empty($rt['pd_audit_code_2017'])?$rt['audit_code_2017']:$rt['pd_audit_code_2017'];

        $t_use_codes[$rt['iso']]          = empty($rt['pd_use_code'])?$rt['use_code']:$rt['pd_use_code'];
        $_audit_code_arr[$rt['iso']]      = empty($rt['pd_audit_code'])?$rt['audit_code']:$rt['pd_audit_code'];
        $t_projects[] = $rt;
    };

    //人员性质下拉
    $audit_job_select = '';
    if ($audit_job_array) {
        foreach ($audit_job_array as $code => $item) {
            $audit_job_select.= "<option value=\"$code\">$item[name]</option>";
        }
        
    }
    //审核员资格下拉
    $qua_type = getgp('qua_type'); //审核员资格
    $qua_type_select = '';
    if ($qualification_array) {
        foreach ($qualification_array as $code => $item) {
            $qua_type_select.= "<option value=\"$code\">$item[name]</option>";
        }
        if($qua_type){
        	$qua_type_select.= str_replace("value=\"$qua_type\">", "value=\"$qua_type\" selected>", $qua_type_select);
        }
    }
    
    //省份下拉
    $province_select = f_province_select();
    $areacode = getgp('areacode'); //省份
    $ctfrom = getgp('ctfrom'); //合同来源
   
    $name = trim(getgp('name')); //用户
    $easycode = trim(getgp('easycode')); //易记码
    $audit_job = getgp('audit_job');
    $iso = getgp('iso'); //搜索 ISO
    $audit_codes = getgp('audit_code'); //搜索 专业代码

    $auditor_users = $codes = array();
    $fields = $join = $where = $q_where = '';
    if ($areacode) {
        //@HBJ 2013年9月12日 09:04:37 areacode从人员表查询
        $where.= " AND LEFT(hr.areacode,2) = '" . substr($areacode, 0, 2) . "'";
		$province_select = str_replace( "value=\"$areacode\">", "value=\"$areacode\" selected>" , $province_select );
    }
    //合同来源
	/*
    if ($ctfrom && '01000000' != $ctfrom) {
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
        $in_uids = array();
        $query = $db->query("SELECT id FROM sp_hr WHERE 1 AND ctfrom >= '$ctfrom' AND ctfrom < '$ctfrom_e' and deleted=0 and    is_hire=1");
        while ($rt = $db->fetch_array($query)) {
            $in_uids[] = $rt['id'];
        }
        if ($in_uids) {
            $where.= " AND hqa.uid IN (" . implode(',', $in_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        }
    }
	*/
    /* 专业代码匹配 */
    //认证体系
    /**/
	if (!$iso) {
        $isos = array_keys($t_isos);
        
    }else
		$isos[]=$iso;
	
	//$isos = array_keys($t_isos);

	if(!$audit_codes)
		$audit_codes = $t_use_codes;
	$audit_codes=array_map("trim",$audit_codes);
	
	if(!$audit_codes_2017)
		$audit_codes_2017 = $t_use_codes_2017;

    $where_iso = array();
    foreach ($isos as $_iso) {
        if ($_iso) {
            //$where_iso[] = "(hqa.iso = '$iso'".( $audit_codes[$iso] ? " AND hac.audit_code IN ('".str_replace( array(';','；',' '), "','", $audit_codes[$iso] )."')" : '' ).")";
            $where_iso[] = "(hqa.iso = '$_iso')";
        }
    }

    $where.= ($where_iso) ? " AND (" . implode(' OR ', $where_iso) . ")" : '';
    //审核员
    if ($name) {
        $_uids = array();
        $query = $db->query("SELECT id FROM sp_hr WHERE name LIKE '%$name%' and is_hire='1' ");
        while ($rt = $db->fetch_array($query)) {
            $_uids[] = $rt['id'];
        }
        if ($_uids) {
            $where.= " AND hqa.uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        }
    }
    //易记码
    if ($easycode) {
        $_uids = array();
        $query = $db->query("SELECT id FROM sp_hr WHERE easycode LIKE '$easycode%' and is_hire='1'");
        while ($rt = $db->fetch_array($query)) {
            $_uids[] = $rt['id'];
        }
        if ($_uids) {
            $where.= " AND hqa.uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        }
    }
    //专业特长
    $major = trim(getgp('major'));
    if( $major ){
    	$where .= " AND hr.major like '%$major%' ";
    }
    
    //人员分层
    $m_separate = getgp('m_separate');
    if($m_separate){
    	$where .= " AND hr.m_separate ='$m_separate'";
    }
    //审核员资格查询
    $qua_type = getgp('qua_type');
    if($qua_type){
    	$where .= " AND hqa.qua_type ='$qua_type'";
    }
	if($audit_code_2017=trim(getgp("audit_code_2017")))
	{
    	$_uids = array();
		//旧版本搜索
		$codeList   = $db->getAll("select * from sp_settings_audit_code where  `shangbao` like '%".$audit_code_2017."%' and iso in('".join("','",$isos)."') and banben='1' and deleted =0  ");
        foreach($codeList as $code)$codeid[] = $code['id'];
		$codeid =  implode(',',$codeid);
		if(!empty($codeid))
		{
			$query = $db->query("SELECT uid FROM `sp_hr_audit_code` WHERE `audit_code_2017` in ($codeid)   and iso in('".join("','",$isos)."') and deleted=0");
	        while ($rt = $db->fetch_array($query)) {
	            $_uids[] = $rt['uid'];
	        }
			$_uids=array_unique($_uids);
		}
		if ($_uids) {
        $where.= " AND hqa.uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        } 
        
        
    }
	if($audit_code=trim(getgp("audit_code")))
	{
    	$_uids = array();
		//新版本搜索
		$codeList   = $db->getAll("select * from sp_settings_audit_code where  `shangbao` like '%".$audit_code."%' and iso in('".join("','",$isos)."') and banben='2' and deleted =0  ");
        foreach($codeList as $code)$codeid[] = $code['id'];
		$codeid =  implode(',',$codeid);
        if(!empty($codeid))
		{
			$query = $db->query("SELECT uid FROM `sp_hr_audit_code` WHERE `audit_code`  in ($codeid)   and iso in('".join("','",$isos)."') and deleted=0");
	        while ($rt = $db->fetch_array($query)) {
	            $_uids[] = $rt['uid'];
	        }
		}
        
		$_uids=array_unique($_uids);
        if ($_uids) {
            $where.= " AND hqa.uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        } 
    }

    //专/兼职
    if ($audit_job) {
        $_uids = array();
        $query = $db->query("SELECT id FROM sp_hr WHERE audit_job = '$audit_job' and is_hire='1' ");
        while ($rt = $db->fetch_array($query)) {
            $_uids[] = $rt['id'];
        }
        if ($_uids) {
            $where.= " AND hqa.uid IN (" . implode(',', $_uids) . ")";
        } else {
            $where.= " AND hqa.id < -1";
        }
        $audit_job_select = str_replace("value=\"$audit_job\">", "value=\"$audit_job\" selected>", $audit_job_select);
    }
    $hr_quas = array();
    $fields = $join = '';
    $fields = "hqa.*,hr.name,hr.sex,hr.audit_job,hr.areacode,hr.tel,hr.ctfrom,hr.major,hr.m_separate,hr.day_quota";
    $join  = " LEFT JOIN sp_hr hr ON hr.id = hqa.uid";
    $where.= " AND hqa.status = 1 and hqa.deleted=0";
    $where.= " and hr.is_hire='1'  and hr.deleted=0";
    $total = $db->get_var("SELECT COUNT(*) FROM sp_hr_qualification hqa $join WHERE 1 $where");
    $pages = numfpage($total,10);
    
    $n_year = date('Y');
    $sql = "SELECT $fields FROM sp_hr_qualification hqa $join WHERE 1 $where ORDER BY hr.easycode,hr.areacode  $pages[limit]";

    $query = $db->query($sql);

    while ($rt = $db->fetch_array($query)) {
        $_audit_codes = array();
		$_use_codes   = array();
        $_audit_codes_2017 = array();
        $_use_codes_2017   = array();

        if ($audit_codes[$rt['iso']]) {

           $query2 = $db->query("SELECT use_code FROM sp_hr_audit_code WHERE iso = '$rt[iso]' AND uid = '$rt[uid]' AND use_code IN ('" . str_replace(array(
                '；',
                ';',
                ' '
            ) , "','", $audit_codes[$rt['iso']]) . "') AND deleted = 0");
            while ($rt2 = $db->fetch_array($query2)) {
                
                //echo "SELECT audit_code FROM `sp_hr_audit_code` WHERE `use_code` = '$rt2[use_code]' AND `audit_code` IN ('" . str_replace(array('；',';',' ') , "','", $_audit_code_arr[$rt['iso']]) . "') and uid=$rt[uid] AND deleted = 0";exit;
                $arr_audit_codes = $db->getAll("SELECT audit_code FROM `sp_hr_audit_code` WHERE `use_code` = '$rt2[use_code]' AND `audit_code` IN ('" . str_replace(array('；',';',' ') , "','", $_audit_code_arr[$rt['iso']]) . "') and uid=$rt[uid] AND deleted = 0");
                foreach ($arr_audit_codes as $value) 
                {
                	if(!empty($value['audit_code']))
                	{
                		$value['audit_code'] = $db->get_var("select shangbao from sp_settings_audit_code where id=".$value['audit_code']);
                    	$_audit_codes[] = $value['audit_code'];
                    	$_use_codes[]   = $rt2['use_code'];
                	}
                    
                }
            }
			
        }

        if ($audit_codes_2017[$rt['iso']]) 
        {

           $query2 = $db->query("SELECT use_code_2017 FROM sp_hr_audit_code WHERE iso = '$rt[iso]' AND uid = '$rt[uid]' AND use_code_2017 IN ('" . str_replace(array(
                '；',
                ';',
                ' '
            ) , "','", $audit_codes_2017[$rt['iso']]) . "') AND deleted = 0");

            while ($rt2 = $db->fetch_array($query2)) {
            
                $arr_audit_codes_2017 = $db->getAll("SELECT audit_code_2017 FROM `sp_hr_audit_code` WHERE `use_code_2017` = '$rt2[use_code_2017]' AND `audit_code_2017` IN ('" . str_replace(array('；',';',' ') , "','", $_audit_code_arr_2017[$rt['iso']]) . "') and uid=$rt[uid] AND deleted = 0");
               
                foreach ($arr_audit_codes_2017 as $value) 
                {
                	if(!empty($value['audit_code_2017']))
                	{
                		$value['audit_code_2017'] = $db->get_var("select shangbao from sp_settings_audit_code where id=".$value['audit_code_2017']);
                   	 	$_audit_codes_2017[] = $value['audit_code_2017'];
                    	$_use_codes_2017[]   = $rt2['use_code_2017'];
                	}
                    
                }
            }
            
        }
        $_audit_codes && $rt['audit_code'] = implode('；', array_unique($_audit_codes));
        $_use_codes && $rt['use_code'] = implode(',', array_unique($_use_codes));
        $_audit_codes_2017 && $rt['audit_code_2017'] = implode('；', array_unique($_audit_codes_2017));
        $_use_codes_2017 && $rt['use_code_2017'] = implode(',', array_unique($_use_codes_2017));

        $rt['sex'] = ($rt['sex'] == 1) ? '男' : '女';
        $rt['audit_job'] = f_audit_job($rt['audit_job']);
        $rt['is_leader'] = ($rt['is_leader']==1) ? '是' : '否';
        $rt['ctfrom'] = f_ctfrom($rt['ctfrom']);
        $rt['address'] = $db->get_var("SELECT meta_value FROM sp_metas_hr WHERE meta_name = 'address' AND used = 'user' AND ID = $rt[uid]");
		$s=$db->get_row("SELECT tat.uid,tat.taskBeginDate,tat.taskEndDate,e.ep_name FROM sp_task_audit_team tat
			LEFT JOIN sp_enterprises e ON e.eid = tat.eid
			WHERE tat.deleted = 0 AND (
				(tat.taskBeginDate >= '$taskBeginDate' AND tat.taskBeginDate <= '$taskEndDate')
			OR
				( tat.taskEndDate >= '$taskBeginDate' AND tat.taskEndDate <= '$taskEndDate' ) 
			OR ( tat.taskBeginDate <= '$taskBeginDate' AND tat.taskEndDate >= '$taskEndDate' )) and tat.uid='$rt[uid]' and tat.eid!=$t_info[eid]");
        $rt['is_plan'] = !$s[uid] ? '否' : "<a href=\"javascript:;\" title=\"审核企业：{$s[ep_name]} 审核时间：{$s[taskBeginDate]} - {$s[taskEndDate]}\">是</a>";
       $rt['province']		= f_region_province( $rt['areacode'] );

        $hr_quas[] = $rt;
    }

    
    $audit_code = str_replace(',', '；', $audit_code);
    tpl('ajax/select_auditor');
