<?php
require( ROOT . '/data/cache/pasuequs.cache.php' );
require( ROOT . '/data/cache/recalqus.cache.php' );

$url=$_SERVER['HTTP_REFERER'];
$ztdq_select="";
for($i=1;$i<=12;$i++)
	$ztdq_select .='<option value="'.$i.'">'.$i.'个月</option>';
$certpasue_check="";
foreach($pasuequs_array as $k=>$item){
	$certpasue_check.="<label><input type=\"checkbox\" name=\"pasuequs[]\" value=\"$k\"  />$item[name]</label><br/>";
}

$recalqus_check="";
foreach($recalqus_array as $k=>$item){
	$recalqus_check.="<label><input type=\"checkbox\" name=\"recalqus[]\" value=\"$k\"  />$item[name]</label><br/>";
}


$type=getgp('type');
$change_zt_display=$change_cx_display="none";//原因是否隐藏@zxl2013-12-17 09:59:38
$zs_info = $certificate->get($zsid);
$cp_info = $enterprise->get(array('eid'=>$zs_info['eid']));
$eid = $zs_info['eid'];
$ct_id=$zs_info['ct_id'];
$cgid=getgp("change_id");
$ct_info = $contract->get(array('ct_id'=>$zs_info['ct_id']));
$cti_info = $db->get_row("select * from sp_contract_item where cti_id='$zs_info[cti_id]' ");
$cg_info = $db->get_row("select * from sp_certificate_change where id='$cgid' ");
$sql = "select audit_type from sp_project where ct_id='$zs_info[ct_id]' and iso='$zs_info[iso]' and deleted='0' and status='5' and audit_type in ('1003','1004','1005','1007') order by id desc limit 1";
$c_audit_type = $db->get_var($sql);
if(!$c_audit_type){
	$c_audit_type = $db->get_var("select audit_type from sp_project where ct_id='$zs_info[ct_id]' and iso='$zs_info[iso]' and deleted='0' and audit_type in ('1003','1004','1005','1007') order by id desc limit 1");
}


$proj_ls = $db->get_results("select id,tid,audit_type,status from sp_project where cti_id='$zs_info[cti_id]' and iso='$zs_info[iso]' and deleted='0' and audit_type!='1002' AND audit_type<>''  order by id asc");


 

$type_1003 = $type_1004 = $type_1005 = $type_1007 = '';
${'type_'.$c_audit_type} = 'checked';
$sql = "select audit_type from sp_project where ct_id='$zs_info[ct_id]' and iso='$zs_info[iso]' and deleted='0' and audit_type in ('1003','1004','1005','1007','1008','1009','1010') order by id asc ";
$res = $db->query($sql);
$audit_type_radio = '';
while($row=$db->fetch_array($res)){
	if($c_audit_type==$row['audit_type']){
		$audit_type_radio .= "<input type='radio' name='audit_type' value='".$row['audit_type']."' checked />".f_audit_type($row['audit_type'])."&nbsp;&nbsp;";
	}else{
		$audit_type_radio .= "<input type='radio' name='audit_type' value='".$row['audit_type']."' />".f_audit_type($row['audit_type'])."&nbsp;&nbsp;";
	}
}

// echo "<pre />";
// print_r($certchange_array);exit;
if($certchange_array){
	$changeitem_li = '';
	foreach($certchange_array as $key=>$value){
		$checked="";
		if ($value['is_stop']) continue;
		if($key==$type)
			$checked="checked";
		if($key == '97' || $key == '97_01' || $key == '97_02' || $key == '97_03' || $key == '97_04'){
			$changeitem_state_li .= "<li><label><input type='checkbox' name='changeitem[]' id='$key' value='$key' onclick=ck_changeitem('$key')  $checked />".$key.'.'.$value['name']."</label></li>";
		}else{
			$changeitem_content_li .= "<li><label><input type='checkbox' name='changeitem[]' id='$key' value='$key' onclick=ck_changeitem('$key')  $checked />".$key.'.'.$value['name']."</label></li>";
		}
	}
}
if($type=="97_01")
	$change_zt_display="";
if($type=="97_03")
	$change_cx_display="";


if( $nature_array ){
	$nature_select =  '';
	foreach( $nature_array as $code => $item ){
		if($code != $cp_info['nature']){
			$nature_select .= "<option value=\"$code\">$item[code]-$item[name]</option>";
		}
	}

}
$certpasue_select=f_select('certpasue');
// 注意产品跟体系的时候 标准要分别取,为了测试默认取了a01

if($audit_ver_array){
	$ver_temp = substr($zs_info['audit_ver'],0,3);
	foreach($audit_ver_array as $key=>$value){
		if($value['audit_ver'] != $zs_info['audit_ver']){
			if($ver_temp==$value['iso'] && $value['is_stop'] == 0 ){
				$audit_ver_radio.= "<input type='radio' name='new_108' value=\"$value[audit_ver]\">".$value[audit_basis].'<br>';
			}
		}
	}
}

if($mark_array){
	foreach($mark_array as $key=>$value){
		if($value['code'] != $zs_info['mark'] && $value['is_stop'] == 0 ){//@wangp 更换标志只显示未停用标志 2013-09-17 16:03
			$mark_radio.= "<input type='radio' name='new_104' value=\"$value[code]\">".$value[name].'<br>';
		}
	}

}
//变更信息
if($cg_info){
	if($cg_info['cg_meta']){
		$name_tmp = 'cg_meta_'.$cg_info['cg_meta'];
		$$name_tmp = $cg_info['cg_bf'];
	}
}else{
	$cg_info['cgs_date'] = date('Y-m-d');
}
$ztdq_select=str_replace("value=\"$cg_info[ztdq]\"","value=\"$cg_info[ztdq]\" selected",$ztdq_select);
if(strlen($cg_info[certpasue_value2])>2){
	$certpasue2_select=str_replace("value=\"09\"","value=\"09\" selected",$certpasue2_select);
	$qita=$cg_info[certpasue_value2];
}else
	$certpasue2_select=str_replace("value=\"$cg_info[certpasue_value2]\"","value=\"$cg_info[certpasue_value2]\" selected",$certpasue2_select);



tpl();

?>