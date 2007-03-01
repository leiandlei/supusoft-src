<?php 
//添加于编辑业务代码
$id = getgp('id');
$iso = getgp('iso'); //@HBJ 2013年9月12日 12:01:28 此行缺失$iso得不到
if (!getgp('pass_date')) {
    echo "<script>alert('通过日期不能为空');history.go(-1);</script>";
    exit;
}

//17A代码字符串
if(!empty(getgp('use_code')))$use_code = explode('；', getgp('use_code'));

if(!empty(getgp('audit_code')))$audit_code = explode('；', getgp('audit_code'));
//2017
if(!empty(getgp('audit_code_2017')))$audit_code_2017 = explode('；', getgp('audit_code_2017'));

if(!empty(getgp('use_code_2017')))$use_code_2017 = explode('；', getgp('use_code_2017'));

$arr_code = array();
foreach ($audit_code as $key => $value) {
    $arr_code[] = array('audit_code'=>$value,'use_code'=>$use_code[$key]);
}

$source=implode('；',getgp('skill_source'));
//三级风险小类代码
$source_arr = explode("；",$source);

if (in_array('1004',$source_arr)) {
    foreach ($audit_code as $value) {
        $sql = "SELECT fenzu FROM sp_settings_audit_code WHERE shangbao='".$value."' and banben=2 and iso='".$iso."' and deleted=0 and is_stop=0 ";
        $fenzu = $db->get_var($sql);
        if (!empty($fenzu)&&$fenzu!='0') {
            $sql = "SELECT code,shangbao FROM sp_settings_audit_code WHERE fenzu='".$fenzu."' and banben=2 and deleted=0 and is_stop=0 ";
            $rs = $db->getALL($sql);
        }
    }
    foreach ($rs as $k => $v) {
        $arr_code[$k]['audit_code'] = $v['shangbao'];
        $arr_code[$k]['use_code']   = $v['code'];
    }
}


//验证业务代码
$rs_2017 = $db->get_results("SELECT code FROM sp_settings_audit_code WHERE 1 AND code IN ('" . join("','", $use_code_2017) . "') AND iso = '$iso' and banben =1 and deleted=0 and is_stop=0 ");
$rs      = $db->get_results("SELECT code FROM sp_settings_audit_code WHERE 1 AND code IN ('" . join("','", $use_code) . "') AND iso = '$iso'  and banben =2 and deleted=0 and is_stop=0 ");


if (!$rs && !$rs_2017) {
    echo "<script>alert('提交小类系统中有不存在的，请仔细检查');history.go(-1)</script>";
    tpl('hr/hr_code_edit');
    exit;
}

$qua_id=getgp("qua_id");
$qua_info=$db->get_row("SELECT * FROM `sp_hr_qualification` WHERE `id` = '$qua_id' ");
//读取人员已有的代码
$sql = "select id,use_code,audit_code from sp_hr_audit_code where qua_id='".$qua_id."' AND deleted='0' ";
$res = $db->query($sql);
while ($row = $db->fetch_array($res)) {
    //系统中数据项
    $db_info[$row['id']] = $row;
    $my_audit_code[]     = $row['audit_code'];
    $my_use_code[]       = $row['use_code'];
}; 

$uid = (int)getgp('uid');
$hr = $db->get_row("SELECT ctfrom, areacode FROM sp_hr WHERE id = '$uid'");
//合并能力来源
    foreach ($arr_code as $k=>$p) 
    {
    	
        $is_canDo = true;
        foreach ($db_info as  $value) 
        {
        	if( ($p['audit_code']==$value['audit_code'])&&($p['use_code']==$value['use_code']) )
            {
        		$is_canDo = false;
             	continue; 
            } 
        }
        if ($is_canDo) 
        { //判断是否已经具有该资质
            $sql = "select `code` from `sp_settings_audit_code` where `shangbao`='".$p."' and `iso`='".$iso."' and `code`='".$use_code[$k]."' and deleted=0 and is_stop=0 ";
            $arr_useCode = $db->getOne($sql);
            	if(!empty($p['audit_code']))
	            {
	            	$audit_code     = $db->get_var("select id from sp_settings_audit_code where shangbao='".$p['audit_code']."' and iso='".$iso."' and banben=2 and deleted=0 and is_stop=0 ");
	            }
				if(!empty($p['use_code']))
	            {
	            	$use_code       = $p['use_code'];
	            }
            	$default     = array(
	                'uid'             => $uid,
	                'qua_id'          => $qua_id,
	                'qua_type'        => $qua_info['qua_type'],
	                'ctfrom'          => $hr['ctfrom'],
	                'areacode'        => $hr['areacode'],
	                'iso'             => getgp('iso') ,
	                'audit_code'      => $audit_code,
	                'use_code'        => $use_code, //小类代码17A
	                'source'          => $source , //能力来源
	    			// 'evaluation_methods'=>$evaluation_methods, //评定方法 
	                // 'audit_year' => getgp('audit_year') , //
	                // 'audit_study' => getgp('audit_study') , //
	                // 'audit_count' => getgp('audit_count') , //
	                // 'audit_day' => getgp('audit_day') , //
	                'pass_date'  => getgp('pass_date') , //
	                // 'is_assess' => getgp('is_assess') , //是否专业评定
	    			 // 'is_profession' => getgp('is_profession') , //是否专业评定
	                'note'       => getgp('note') , //
	                'evaluater'  => getgp('evaluater') //评定人员
             	);
				if (empty($id)) 
				{
	                $auditcode->add($default);
	                //日志
	                $af_str = serialize($auditcode->get($id));
	                log_add(0, $uid, "添加业务代码", '', $af_str);
           		 }
            
        }else{
		    echo "<script>alert('人员已具有该资质，请检查');history.go(-1)</script>";
		    tpl('hr/hr_code_edit');
		    exit;

        }
    }
	//2017
	if(!empty($audit_code_2017))
    {
    	foreach($audit_code_2017 as $key=> $audit_code)
    	{
    		$audit_codeid_2017  = $db->get_var("select id from sp_settings_audit_code where shangbao='".$audit_code."'and iso='".$iso."' and banben=1 and deleted=0 and is_stop=0 ");
    		$default     = array
    			(
	                'uid'             => $uid,
	                'qua_id'          => $qua_id,
	                'qua_type'        => $qua_info['qua_type'],
	                'ctfrom'          => $hr['ctfrom'],
	                'areacode'        => $hr['areacode'],
	                'iso'             => getgp('iso') ,
	                'audit_code_2017' => $audit_codeid_2017,
	                'use_code_2017'   => $use_code_2017[$key], //小类代码17A
	                'source'          => $source , //能力来源
	                'pass_date'       => getgp('pass_date') , //
	                'note'            => getgp('note') , //
	                'evaluater'       => getgp('evaluater') //评定人员
	             );
			if (empty($id)) 
			{
                $auditcode->add($default);
                //日志
                $af_str = serialize($auditcode->get($id));
                log_add(0, $uid, "添加业务代码", '', $af_str);
            }	 
    	}
    }
	//
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
        // echo '<pre />';
        // print_r($default);exit;
        $auditcode->edit($id, $default);
        //日志
        $af_str = serialize($auditcode->get($id));
        log_add(0, $uid, "编辑业务代码", $bf_str, $af_str);
    }
  $REQUEST_URI = '?c=hr_code&a=edit&uid=' . getgp('uid') . '&iso=' . getgp('iso').'&qua_id='.$qua_id;
  showmsg( 'success', 'success', $REQUEST_URI );

?>

