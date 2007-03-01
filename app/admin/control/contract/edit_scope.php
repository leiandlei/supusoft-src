<?php
//变成评审-修改项目信息
$type  = getgp("type");
$step  = (int) getgp('step');
//$cgid  = (int) getgp('cgid'); //变更评审
$pid   = getgp('pid');
$ct_id = getgp('ct_id');
//评审项目
if ($step) {  
    $scope           = getgp('scope');
    //专业代码与使用代码
    $audit_code      = getgp('audit_code');
    $use_code        = getgp("use_code");
	
    $audit_code_2017 = getgp('audit_code_2017');//2017
    $use_code_2017   = getgp("use_code_2017");//2017
	
    $audit_note      = getgp('audit_note');
    $review_status   = getgp('review_status');
    $arr             = array(
        'scope'              => $scope,
        'audit_code'         => $audit_code,
        'use_code'           => $use_code,
        'audit_code_2017'    => $audit_code_2017,
        'use_code_2017'      => $use_code_2017,
        "st_num"             => getgp('st_num'),
        "mark"               => getgp('mark'),
        "total"              => getgp('total'),
        "exc_clauses_new"    => getgp('exc_clauses'),
        "pd_type"            => '0',
		
    );
     //判断状态 1:未评审 2:已评审
    if ($review_status=='1')
     {
        $arr['flag']="2";
     }else
     {
        $arr['flag']="1";       
     }
    //结合变更选项
    if (is_array($_POST['audit_type_note'])) {
        $arr['audit_type_note'] = implode('；', $_POST['audit_type_note']);
        if (in_array('标准转换', $_POST['audit_type_note'])) {
            if ($_POST['audit_ver'])
                $arr['audit_ver'] = $_POST['audit_ver'];
        }
    }
	if($_POST['zy_name'])
		$zy_name=$_POST['zy_name'];
	else{
		$use_codes =explode("；",$use_code);
		$zy_name   =$db->get_col("SELECT zy_name FROM `sp_stff` where code in('".join("','",$use_codes)."') and zy_name<>''");
		$zy_name   =array_unique($zy_name);
		$zy_name   =join("；",$zy_name);
	}
	$arr['zy_name']   =$zy_name;
	//$arr['review_note']=getgp('review_note');
	$arr['audit_note']=getgp('note');
	$bf_proj_info     =load('audit')->get(array('id'=>$pid));
	
	$db->update('project', $arr, array(
        'id' => $pid
    ));
    //以下是变更数据进入contract_item表
    $sql="select cti_id,audit_type from sp_project where id='$pid' and deleted='0'";

    $cti_id=$db->get_row($sql);
    switch ($cti_id['audit_type']) {
        case '1002':
            $num="yjdxc_num";
            break;
        case '1003':
        case '1007':
            $num="ejdxc_num";
            break;
        case '1004':
        case '1005':
            $num="jdxc_num";
            break;
        default:
            break;
    }
    $arr_ct=array(
        'scope'           => $arr['scope'], //评审范围
        'audit_code'      => $arr['audit_code'], //专业代码
        'use_code'        => $arr['use_code'], //使用代码
        'audit_code_2017' => $arr['audit_code_2017'], //专业代码
        'use_code_2017'   => $arr['use_code_2017'], //使用代码
        $num              => $arr['st_num'], //现场人日
        'total'           => $arr['total'], //体系人数
        "exc_clauses"     => getgp('exc_clauses'),  //删减条款
        'mark'            => $arr['mark'],
        'audit_ver'       =>$arr['audit_ver'],
    );
    if($arr['audit_ver']){
        $arr_ct=array( 'audit_ver'       =>$arr['audit_ver'],);
    }
    $db->update('contract_item',$arr_ct, array(
        'cti_id' => $cti_id['cti_id']
    ));
	$af_proj_info=load('audit')->get(array('id'=>$pid));
	
	log_add($bf_proj_info['eid'],0,'变更评审:项目号'.$bf_proj_info['cti_code'].'审核阶段：'.read_cache('audit_type',$af_proj_info['audit_type']),serialize($bf_proj_info),serialize($af_proj_info));
	
   



/*     //更新专业管理人员
	$bf_ct_info=load('contract')->get(array('ct_id'=>$bf_proj_info['ct_id']));
    $db->update('contract', array(
        'major_person' => getgp('major_person'),
        'review_note' => getgp('review_note'),//评审备注
        'note' => getgp('note') //项目组备注
    ), array(
        'ct_id' => $ct_id
    ));
	$af_ct_info=load('contract')->get(array('ct_id'=>$bf_proj_info['ct_id']));
	
	 	log_add($bf_proj_info['eid'],0,'变更评审:合同号'.$bf_proj_info['ct_code'].'审核阶段：'.read_cache('audit_type',$af_proj_info['audit_type']),serialize($bf_ct_info),serialize($af_ct_info));
 */ 
    $REQUEST_URI = "?c=contract&a=edit_scope&pid=$pid&type=review";
    $review_status && $REQUEST_URI = "?c=contract&a=add_review";
    showmsg('success', 'success', $REQUEST_URI);
}
//项目信息
$p_info = load('audit')->get(array(
    'id' => $pid
));
$ct_id  = $p_info['ct_id'];
//p($p_info);
extract($p_info);
//项目体系下排除当前的的所有标准 
$audit_vers              = $db->find_results('settings_audit_vers', " and   iso='$p_info[iso]' and audit_ver!='$p_info[audit_ver]'", 'audit_ver,audit_basis');
//结合审核项目
$p_info['old_audit_ver'] = explode('；', $p_info['audit_type_note']);
//专业审核代码
$audit_codes             = array();
$codes                   = empty(array_filter(explode('；', $p_info['pd_audit_code'])))?array_filter(explode('；', $p_info['audit_code'])):array_filter(explode('；', $p_info['pd_audit_code']));
if(!empty($codes))$query = $db->query("SELECT id,code,shangbao,risk_level,mark,banben FROM sp_settings_audit_code WHERE  id IN(" . implode(",",$codes) . ") AND iso='$p_info[iso]'");
while ($rt = $db->fetch_array($query)) 
{
    if (!$rt['shangbao'])
        continue;
    $marks     = explode(',', $rt['mark']);
    $new_marks = array();
    foreach ($marks as $mk) {
        $mark_V = f_mark($mk);
        if ($mark_V) {
            $new_marks[] = $mark_V;
        }
    }
    $rt['mark_V']        = implode(',', $new_marks);
    $rt['risk_level_V']  = f_risk($rt['risk_level']);
    $audit_codes[]       = $rt;
}
unset($codes);
//专业审核代码2017

$audit_codes_2017             = array();
$codes_2017                   = empty(array_filter(explode('；', $p_info['pd_audit_code_2017'])))?array_filter(explode('；', $p_info['audit_code_2017'])):array_filter(explode('；', $p_info['pd_audit_code_2017']));

if(!empty($codes_2017))$query = $db->query("SELECT id,code,shangbao,risk_level,mark,banben FROM sp_settings_audit_code WHERE  id IN(" . implode(",", $codes_2017) . ") AND iso='$p_info[iso]'");

while ($rt = $db->fetch_array($query)) 
{
    if (!$rt['shangbao'])continue;
    $marks     = explode(',', $rt['mark']);
    $new_marks = array();
    foreach ($marks as $mk) 
    {
        $mark_V = f_mark($mk);
        if ($mark_V) 
        {
            $new_marks[] = $mark_V;
        }
    }
    $rt['mark_V']        = implode(',', $new_marks);
    $rt['risk_level_V']  = f_risk($rt['risk_level']);
    $audit_codes_2017[]  = $rt;
}

unset($codes);
//合同信息
$ct_info = $db->find_one('contract', array(
    'ct_id' => $ct_id
));
//证书变更列表信息
$cert_chang_ls=load('change')->gets(array('cg_pid'=>$pid));
//p($cert_chang_ls);

//证书
$sql="select code,name from sp_settings where type='mark' and deleted='0' and is_stop='0'";
$settings_c = $db->getAll($sql);

$exc_clauses_c = $db->get_var("select exc_clauses from sp_contract_item where ct_id='$ct_id'");
// $exc_clauses_p = $db->get_var("select exc_clauses_new from sp_project where id='$pid'");
// $exc_clauses   = empty($exc_clauses_c)?$exc_clauses_p:$exc_clauses_p;

tpl();
 