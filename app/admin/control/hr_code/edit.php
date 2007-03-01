<?php
$iso = getgp('iso'); //小类业务代码所属体系
$id = getgp('id'); //小类ID  功能：删除用
$is_banben = empty(getgp('is_banben'))?'2':getgp('is_banben');

$where = '';
if (!$iso) {
    echo '错误提示，没有关联人员';
    exit;
}
//人员专业能力评价
$res = $db->query("select id,name from sp_hr where job_type like '%1001%' and is_hire='1' ");
while($row = $db->fetch_array($res)){
	$evaluater_select  .= "<option value='$row[name]'>".$row['name']."</option>";
}


//删除业务代码
if (getgp('action')) {
    $sql = "update sp_hr_audit_code set deleted='1' where id='" . getgp('id') . "' ";
    $db->query($sql);
    $code_info = $auditcode->get($id);
    $uid       = $code_info['uid'];
    log_add('', $uid, '删除业务代码', '', serialize($code_info));
    unset($id);
}
$status          = getgp('status');
$audit_code_2017 = trim(getgp('audit_code_2017'));
$audit_code      = trim(getgp('audit_code'));
$use_code        = trim(getgp('use_code'));
if ($status) {
    $where.= " and hac.status ='$status' ";
}
//以下为旧代码
if ($audit_code_2017) 
{
	$codeid =$db->getAll("select * from sp_settings_audit_code where shangbao like '$audit_code_2017%' and deleted=0 and is_stop=0 ");
	foreach($codeid as $code)
	{
		$codes[]   = $code['id'];
	}
	$audit_codeids = implode(',', $codes);
    $where1.= " and hac.audit_code_2017 in ($audit_codeids)";
}

//以下为新代码
if ($audit_code) 
{
	$codeid =$db->getAll("select * from sp_settings_audit_code where shangbao like '$audit_code%' and deleted=0 and is_stop=0 ");
	foreach($codeid as $code)
	{
		$codes[]   = $code['id'];
	}
	$audit_codeids = implode(',', $codes);
    $where2.= " and hac.audit_code in ($audit_codeids)";
}
if ($use_code) {
    $where.= " and hac.use_code like '$use_code%' ";
}

$status_select = str_replace("value=\"$status\">", "value=\"$status\" selected>", $status_select);
$tip_msg = '新增人员代码';
if (!$rows[iso]) {
    $rows[iso] = $iso;
}

if (!empty($row)) {
    extract($row, EXTR_SKIP);
}

$iso_V = f_iso($iso);
 
//全部业务代码
$join.= " LEFT JOIN sp_hr_qualification hqa ON hqa.id=hac.qua_id";
$where.= " AND hac.uid = '$uid' AND hac.iso = '$iso' and hac.deleted='0' ";

if($audit_code){
	$sql = "SELECT  hac.use_code,hac.audit_code,hac.audit_code_2017 FROM sp_hr_audit_code hac $join WHERE 1 $where $where2";
	
}else if($audit_code_2017){
	$sql = "SELECT  hac.use_code,hac.audit_code,hac.audit_code_2017 FROM sp_hr_audit_code hac $join WHERE 1 $where $where1";
}else{
	$sql = "SELECT  hac.use_code,hac.audit_code,hac.audit_code_2017 FROM sp_hr_audit_code hac $join WHERE 1 $where ";
}
$query = $db->query($sql);
$count = 0;
$code_str = '';

while ($rt = $db->fetch_array($query)) 
{
    if(!empty($rt['audit_code_2017']))
    {
    	$count++;
		$ucode_str_2017 .= $rt['audit_code_2017'] . ',';
    }
	if(!empty($rt['audit_code']))
	{
		$count1++;
		$ucode_str_2018 .= $rt['audit_code'] . ',';
	}
    

}

//2017

if(!empty($ucode_str_2017))
{
		
	$ucode_str_2017      = implode(',',array_unique(array_filter(explode(',', $ucode_str_2017))));
	
	$fenzuLists_2017     = $db->getAll("select * from sp_settings_audit_code where  code <> '' and id in($ucode_str_2017) and banben=1  and  deleted =0  and is_stop=0 ");

    $result_2017 =   array();
	foreach($fenzuLists_2017 as $key =>$fenzu_2017)
	{
	    $result_2017[$fenzu_2017['code']][]    =   $fenzu_2017;
	}
	$fenzu_code_2017 ='';

	foreach($result_2017 as $k => $res)
	{
		$fenzu_arrs_2017 = '';
		for($i=0;$i < count($res);$i++)
		{	
			if($k==($res[$i]['code']))
			{
                $fenzu_arrs_2017 .= $res[$i]['shangbao'].'；';
			}
		}
		$fenzu_code_2017  .= '<b>'.$k.'：（'.'</b>'.$fenzu_arrs_2017.'<b>'.'）'.'</b>'.'</br>';
	}
}
//2018

if(!empty($ucode_str_2018))
{
	$ucode_str_2018      = implode(',',array_unique(array_filter(explode(',', $ucode_str_2018))));
	
	$fenzuLists_2018     = $db->getAll("select * from sp_settings_audit_code where  code <> '' and id in($ucode_str_2018) and banben=2 and deleted=0 and is_stop=0 ");
	$result_2018 =   array();
	foreach($fenzuLists_2018 as $key =>$fenzu_2018)
	{
	    $result_2018[$fenzu_2018['code']][]    =   $fenzu_2018;
	}
	$fenzu_code_2018 ='';
	foreach($result_2018 as $k => $res)
	{
		$fenzu_arrs_2018 = '';
		for($i=0;$i < count($res);$i++)
		{		
			if($k==$res[$i]['code'])
			{
                $fenzu_arrs_2018 .= $res[$i]['shangbao'].'；';
			}
		}
		$fenzu_code_2018  .= '<b>'.$k.'：（'.'</b>'.$fenzu_arrs_2018.'<b>'.'）'.'</b>'.'</br>';
		
		
	}

}

//已经登记的业务代码
//$total = $db->get_var("SELECT COUNT(hac.id) FROM sp_hr_audit_code hac $join WHERE 1 $where  group by hac.id");
$pages      = numfpage($count,5);

//是否认证评定
$arr_ping = array(
    '0' => '不能',
    '1' => '可以'
);
$hacs_2017 = array();
if($audit_code_2017)
{   
   
        $query     = $db->query("SELECT hac.*,hqa.qua_type,hqa.status qua_status FROM sp_hr_audit_code hac $join WHERE 1 $where $where1"); 
    
}else{

	if($count=='0')
	{
		$query     = $db->query("SELECT hac.*,hqa.qua_type,hqa.status qua_status FROM sp_hr_audit_code hac $join WHERE 1 $where  limit 0,5 ");
	}else{
		$query     = $db->query("SELECT hac.*,hqa.qua_type,hqa.status qua_status FROM sp_hr_audit_code hac $join WHERE 1 $where  $pages[limit] ");
	}

	
}


while ($rt = $db->fetch_array($query)) {
	if(!empty($rt['audit_code_2017']))
	{
		$codetype               = $db->get_row("select * from sp_settings_audit_code where id='".$rt['audit_code_2017']."'");
		if($codetype['banben']=='2')continue;
	    $rt['qua_type_V']       = f_qua_type($rt['qua_type']);
	    $rt['iso_V']            = f_iso($rt['iso']);
	    $rt['source']           = skill_source_V($rt['source']);
	    $rt['audit_job']        = f_audit_job($rt['audit_job']);
	    $rt['status_V']         = $status_arr[$rt['status']];
	    $rt['is_assess']        = $arr_ping[$rt['is_assess']];
		$rt['audit_code_2017']  = $codetype['shangbao'];
	    $hacs_2017[] = $rt;
    }
}

$pages1     = numfpage($count1,5);
$hacs = array();
$where3 =" and hac.audit_code is not null";
$query     = $db->query("SELECT hac.*,hqa.qua_type,hqa.status qua_status FROM sp_hr_audit_code hac $join WHERE 1 $where $where2 $where3 $pages1[limit]");

while ($rt = $db->fetch_array($query)) 
{
		
		$codetype               = $db->get_row("select * from sp_settings_audit_code where id='".$rt['audit_code']."' ");

		if($codetype['banben']=='1')continue;
	    $rt['qua_type_V']       = f_qua_type($rt['qua_type']);
	    $rt['iso_V']            = f_iso($rt['iso']);
	    $rt['source']           = skill_source_V($rt['source']);
	    $rt['audit_job']        = f_audit_job($rt['audit_job']);
	    $rt['status_V']         = $status_arr[$rt['status']];
	    $rt['is_assess']        = $arr_ping[$rt['is_assess']];
		$rt['audit_code']       = $codetype['shangbao'];
	
	    $hacs[] = $rt;	
}


$youxiao = 'checked';
if ($audit_ver_array) {
    foreach ($audit_ver_array as $code => $item) {
        $iso_ver_select.= "<option value=\"$item[audit_ver]\">$item[audit_ver]</option>";
    }
}

function skill_source_V($skill_array){
	 global $skill_source_array;
	 $skill_array = explode('；', $skill_array); 
	 foreach($skill_array as $v){
		$rs.=$skill_source_array[$v]['name'].'；';
		 
 	 }
	return $rs; 
}
 
//===============================================编辑情况======================
$user_info = $user->get($uid); //获取编辑器信息
 if($id){ 
	 $tip_msg = '编辑人员代码'; 
   
    $rows = $auditcode->get($id);  //获取需要编辑的内容
    if(!empty($rows['audit_code_2017']))
    {
    	$counts   = explode(',',$rows['audit_code_2017']);
		if(count($counts) =='1'&&is_numeric($rows['audit_code_2017']))
		{
    		$rows['audit_code_2017'] = $db->get_var("select shangbao from sp_settings_audit_code where id=".$rows['audit_code_2017']);	
		}

    }
	if(!empty($rows['audit_code'])&&is_numeric($rows['audit_code']))
    {
    	$counts   = explode(',',$rows['audit_code']);
		if(count($counts) =='1')
		{
    		$rows['audit_code'] = $db->get_var("select shangbao from sp_settings_audit_code where id=".$rows['audit_code']);
		}
    }

    //能力来源显示编辑信息
    $skll_array = explode('；', $rows['source']);
    foreach ($skll_array as $value) {
        $skill_source_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $skill_source_checkbox);
    }
    //评定方法信息
    $evaluation_methods_array = explode('；', $rows['evaluation_methods']);
    foreach ($evaluation_methods_array as $value) {
        $evaluation_methods_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $evaluation_methods_checkbox);
    }
    //评定人员
    if($rows[evaluater]){
    	$evaluater_select = str_replace("<option value='$rows[evaluater]'>", "<option value='$rows[evaluater]' selected>", $evaluater_select);
    } 
    //是否认证决定
 	//是否专业管理
	if($rows['is_assess']=='0'){
		$check_assess0='checked';
	}else{
		$check_assess1='checked';
	}
	//是否专业管理
	if($rows['is_profession']=='0'){
		$check_profession0='checked';
	}else{
		$check_profession1='checked';
	}

}

/**人员信息**/
    //人员登记模块
require_once (ROOT . '/data/cache/card_type.cache.php'); //证件类型
require_once (ROOT . '/data/cache/political.cache.php'); //政治面貌
require_once (ROOT . '/data/cache/job_type.cache.php'); //人员性质 多项
require_once (ROOT . '/data/cache/audit_job.cache.php'); //审核性质
require_once (ROOT . '/data/cache/ct_type.cache.php'); //人员 合同类型
require_once (ROOT . '/data/cache/technical.cache.php'); //人员职称
require_once (ROOT . '/data/cache/choose_type.cache.php'); //选用类型
require_once (ROOT . '/data/cache/insurance.cache.php'); //社保登记
require_once (ROOT . '/data/cache/region.cache.php'); //省份
require_once (ROOT . '/data/cache/department.cache.php'); //部门
require_once (ROOT . '/data/cache/attachtype.cache.php'); //附件类型
require_once (ROOT . '/data/cache/employment_nature.cache.php'); //聘用性质
require_once (ROOT . '/data/cache/department.cache.php'); //所在部门
require_once (ROOT . '/data/cache/post.cache.php'); //所在岗位
require_once (ROOT . '/data/cache/business.cache.php'); //业务类别
require_once (ROOT . '/data/cache/functions.cache.php'); //业务职能
require_once (ROOT . '/data/cache/employment_methods.cache.php'); //业务职能
require_once( ROOT . '/data/cache/education.cache.php' ); //教育经历

$user=load('user');
$step = getgp('step');
$exp=load('experience');


//合同来源
$ctfrom_select = f_ctfrom_select();
//省份下拉(登记用 搜索用)
$province_select = f_province_select();
//聘用性质
$employment_nature_select = f_select('employment_nature');
//政治面貌
$political_select =f_select('political');
//证件类型
$card_type_select = f_select('card_type');
$audit_job_select = f_select('audit_job');//是否专职
$technical_select = f_select('technical');  //是否专职
$choose_type_select =f_select('choose_type');//选用类型
$insurance_select =f_select('insurance');//社保登记
$xueli_select=f_select('education'); //人员学历
$attachtype_select = f_select('attachtype');//附件类型
//部门设置
$department_checkbox = '';
if ($department_array) {
    foreach ($department_array as $code => $item) {
        $department_checkbox.= "<input type='checkbox' name='department[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//岗位
$post_checkbox = '';
if ($post_array) {
    foreach ($post_array as $code => $item) {
        $post_checkbox.= "<input type='checkbox' name='post[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//业务类别
$business_checkbox = '';
if ($business_array) {
    foreach ($business_array as $code => $item) {
        $business_checkbox.= "<input type='checkbox' name='business[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//业务职能
$functions_checkbox = '';
if ($functions_array) {
    foreach ($functions_array as $code => $item) {
        $functions_checkbox.= "<input type='checkbox' name='functions[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
//人员性质
$job_type_checkbox = '';
if ($job_type_array) {
    foreach ($job_type_array as $code => $item) {
        $job_type_checkbox.= "<input type='checkbox' name='job_type[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
 
 


//用工方式
$employment_methods_checkbox = '';
if ($employment_methods_array) {
    foreach ($employment_methods_array as $code => $item) {
        $employment_methods_checkbox.= "<input type='checkbox' name='employment_methods[$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;';
    }
}
 


$ct_type_checkbox = '';//合同类型
if ($ct_type_array) {
    foreach ($ct_type_array as $code => $item) {
        $ct_type_checkbox.= "<input type='checkbox' name='meta[ct_type][$code]' value=\"$item[code]\">" . $item[name] . '&nbsp;&nbsp;';
    }
}


unset($code, $item);
    if ($_GET['uid']) { //编辑时候显示需要编辑的信息
        
        $upload_hr_photo_dir=get_option('upload_hr_photo_dir').$_GET['uid'].'.jpg'; //人员上传头像路径
        
        if(!file_exists($upload_hr_photo_dir)){
            $upload_hr_photo_dir=get_option('upload_hr_photo_dir').'nophoto.jpg'; //人员上传头像路径
        }
     
        
        $uid = (int)getgp('uid');
        $row = $user->get($uid);
        extract(chk_arr($row), EXTR_SKIP);
        $is_leader = $row['is_leader'];
        $is_hire = $row['is_hire'];
        $audit_job = $row['audit_job'];
        //人员来源
        $ctfrom_select = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
        //聘用性质
        $employment_nature_select = str_replace("value=\"$employment_nature\">", "value=\"$employment_nature\" selected>", $employment_nature_select);
        //政治面貌
        $political_select = str_replace("value=\"$political\">", "value=\"$political\" selected>", $political_select);
        //证件类型
        $card_type_select = str_replace("value=\"$card_type\">", "value=\"$card_type\" selected>", $card_type_select);
        //所在部门
        $department_array = explode('；', $row['department']);
        foreach ($department_array as $value) {
            $department_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $department_checkbox);
        }
        //所在岗位
        $post_array = explode(';', $row['post']);
        foreach ($post_array as $value) {
            $post_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $post_checkbox);
        }
        //业务类别
        $business_array = explode('；', $row['business']);
        foreach ($business_array as $value) {
            $business_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $business_checkbox);
        }
        //业务职能
        $functions_array = explode('；', $row['functions']);
        foreach ($functions_array as $value) {
            $functions_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $functions_checkbox);
        }
        //人员性质
        $arr_job_type = explode('|', $job_type);
        foreach ($arr_job_type as $key => $value) {
            $job_type_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $job_type_checkbox);
        }
        //合同类型
        $arr_ct_type = explode('|', $ct_type);
        foreach ($arr_ct_type as $key => $value) {
            $ct_type_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $ct_type_checkbox);
        }
       
         //业务类型
        $data_fors_array = explode('；', $row['data_fors']);
        foreach ($data_fors_array as $value) {
            $data_fors_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $data_fors_checkbox);
        }
        //性别
        if ($sex == '1') {
            $sex_M = 'checked';
            $sex_F = '';
        } elseif ($sex == '2') {
            $sex_M = '';
            $sex_F = 'checked';
        }

         if ($signe_name == '1') {
            $signe_name_y = 'checked';
            $signe_name_n = '';
        } elseif ($signe_name == '0') {
            $signe_name_y = '';
            $signe_name_n = 'checked';
        }

        //选用类型
        $choose_type_select = str_replace("value=\"$choose_type\">", "value=\"$choose_type\" selected>", $choose_type_select);
        //社保登记
        $insurance_select = str_replace("value=\"$insurance\">", "value=\"$insurance\" selected>", $insurance_select);
        //审核员性质
        $audit_job_select = str_replace("value=\"$audit_job\">", "value=\"$audit_job\" selected>", $audit_job_select);
        
        //人员职称
        $technical_select = str_replace("value=\"$technical\">", "value=\"$technical\" selected>", $technical_select);
        //是否组长
        if ($is_leader == 1) {
            $is_leader_Y = 'checked';
            $is_leader_N = '';
        } else {
            $is_leader_Y = '';
            $is_leader_N = 'checked';
        }
        //在聘情况
        if ($is_hire == 1) {
            $is_hire_Y = 'checked';
            $is_hire_N = '';
            $is_hire_T = '';
        } else if ($is_hire == 2) {
            $is_hire_Y = '';
            $is_hire_N = 'checked';
            $is_hire_T = '';
        } else if ($is_hire == 3) {
            $is_hire_Y = '';
            $is_hire_N = '';
            $is_hire_T = 'checked';
        }
        //读取个人经历列表
        if ($uid) {
            $query = '';
            $sql = "SELECT * FROM sp_hr_experience he where he.deleted='0' AND he.add_hr_id=$uid";
            $query = $db->query($sql);
            while ($rt = $db->fetch_array($query)) {
                if ($rt['type'] == 'g') { //读取工作经历
                    $glist[] = $rt;
                } elseif ($rt['type'] == 'j') { //读取教育经历
                    $jlist[] = $rt;
                } elseif ($rt['type'] == 'p') { //读取培训经历
                    $plist[] = $rt;
                }
            };
        }
    }
    //if结束
    
    //提示信息
    $j_tip_msg = '添加教育经历';
    $g_tip_msg = '添加工作经历';
    $p_tip_msg = '添加培训经历';
    if ($_GET['jid']) { //要修改的培训信息
        $j_tip_msg = '编辑教育经历';
        $jExpInfo = $exp->get($_GET['jid']);
    } else if ($_GET['gid']) { //要修改的培训信息
        $g_tip_msg = '编辑工作经历';
        $gExpInfo = $exp->get($_GET['gid']);
    } else if ($_GET['pid']) { //要修改的培训信息
        $p_tip_msg = '编辑培训经历';
        $pExpInfo = $exp->get($_GET['pid']);
    }
    //人员文档
    $hr_archives = array();
    if ($uid) {
        $query = $db->query("SELECT * FROM sp_hr_archives WHERE uid = '$uid'"); // LIMIT 10
        while ($rt = $db->fetch_array($query)) {
            $rt['ftype_V'] = f_atachtype($rt['ftype']);
            $hr_archives[] = $rt;
        }
    }
    $startYear = date('Y') - 70;
    $endYear = date('Y') + 5;
    $mt_rand = mt_rand(1, 999999);
/**人员信息**/

tpl('hr/hr_code_edit');
?>

