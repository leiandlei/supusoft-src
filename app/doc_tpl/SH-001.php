<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$check="□";
$checked="■";

$tid  = getgp( 'tid' );

$ctid=$_GET['ct_id'];
$ctid_arr=explode('、',$ctid);
$ctid="(".implode(',',$ctid_arr).")";//修改成数组查询

$arr_audit = $db->getAll("select audit_ver,audit_code,audit_code_2017 from sp_contract_item where ct_id in ".$ctid." and deleted=0");

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid' and deleted=0");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$audit_1002=$audit_1007=$check;
$ct = $db->get_row( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0' ORDER BY audit_type_note desc");
extract( $ct, EXTR_SKIP );

$rs_once = $db->getAll( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0' ");
//var_dump($rs_once);exit;
$qchecked=$echecked=$schecked="□";
if($rs_once){
    foreach ($rs_once as $val){
        if($val['iso']=="A01"){
            if($val['audit_type_note']=="标准转换"){
                $qchecked="■";
            }else{
                $qchecked="□";
            }
        }elseif($val['iso']=="A02"){
            if($val['audit_type_note']=="标准转换"){
                $echecked="■";
            }else{
                $echecked="□";
            }
        }else{
            if($val['audit_type_note']=="标准转换"){
                $schecked="■";
            }else{
                $schecked="□";
            }
        }
    }
}

$iso = array();

//任务审批时间
$sql = 'select `approval_date` from `sp_task` where id='.$tid.' and deleted=0';
$approval_date = $db->get_var($sql);
$approval_date = sprintf(str_replace("-", "%s", $approval_date),'年','月').'日';

//外包倒班
$wb_db = $db->get_var('select `wb_db` from `sp_task` where id='.$tid.' and deleted=0');
if ($wb_db=='') {
	$wb_db = "无";
}
$sql     = 'select `is_site` from `sp_contract` where ct_id in '.$ctid.' and deleted=0';
$is_site = $db->get_var($sql);
if ($ct['audit_type']=='1002') {
	if ($is_site=='1'){
		$is_sites = "-现场";
	}else{
		$is_sites = "-非现场";
	}
}

//人日比例
$sql = 'select `xc_num`,`jdxc_num`,`audit_type` from `sp_contract_item` where ct_id in '.$ctid.' and deleted=0';
$xc_num = $db->getAll($sql);
$array_audit_type = array("1004", "1005");
if(in_array($xc_num['audit_type'],$arr_audit_type,TRUE)){
	foreach ($xc_num as $v) {
		$xc_num_z += $v['jdxc_num'];
	}//监审现场人日
}else{
	foreach ($xc_num as $v) {
		$xc_num_z += $v['xc_num'];
	}//评审现场人日
}


//组织机构代码
if (strlen($work_code)==9) {
	$arr_work_code="组织机构代码";
}else{
	$arr_work_code="统一社会信用代码";
}

//附加信息
$sql = 'select * from `sp_metas_ep` where ID='.$eid.' and deleted=0';
$extra = $db->getAll($sql);
foreach ($extra as $value) {
	$extra_ep[$value['meta_name']] = $value['meta_value'];
}

/**专业支持人员**/
$zhichi = '';

foreach ($arr_audit as $value) 
{
	$arr_code = array();$code_where = '(';
	if(getgp('banben'=='1'))//旧版本专业代码
	{
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}else{  //新版本专业代码
		if(!empty($value['audit_code']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}
	$arr_audit_code = explode('；',$value['audit_code']);

	foreach ($arr_audit_code as $val) {
		$code = explode('.',$val);
		$arr_code[] = $code[0];
	}
	

	if( !empty($arr_code) ){
		foreach ($arr_code as $val) {
			$code_where .= "audit_code like '".$val."%' or audit_code like '%,".$val."%' or ";
		}
		$code_where = substr($code_where,0,strlen($code_where)-4).')';
	}else{
		$code_where = '';
	}
	switch ($value['audit_ver']) {
		case 'A010101':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A020101':
			$str_ios = 'E:';
			$iso_where = 'A02';
			break;
		case 'A030102':
			$str_ios = 'S:';
			$iso_where = 'A03';
			break;

		default:
			break;
	}
	$arr_zhichi = $db->getAll("select hr.id,hr.name from `sp_hr` hr JOIN sp_hr_qualification hrq on hr.id=hrq.uid where hr.job_type not like'%1006%' and hrq.qua_type!='1000' and hrq.iso='".$iso_where."' and hrq.e_date>'".date('Y-m-d')."' and hrq.deleted=0 and hr.deleted=0");
	if( !empty($arr_zhichi) ){
		foreach ($arr_zhichi as $va) {
			$sql    = "select id from sp_hr_audit_code where uid=".$va['id']." and ".$code_where." and deleted=0";
			$sql_r  = $db->get_var($sql);
			if(!empty($sql_r)){
				$zhichi_all[] = $va['name'];
			}
		}
		$zhichi_all = array_unique($zhichi_all);

		//随机引出6人
		if(count($zhichi_all)>"6"){
			$aKeys = array_rand($zhichi_all,6);
			$aRand=array();  // 保存随机后的数组

			//组合随机数组
			foreach($aKeys as $v){
				// $aRand[$v]=$zhichi_all[$v];
				$str_ios .= $zhichi_all[$v]."、";
			}
		}else{
			foreach ($zhichi_all as $v) {
				$str_ios .= $v."、";
			}
		}
		if(strlen($str_ios)==2)$str_ios='';

		$zhichi .= $str_ios." ";
	}
}
/**专业支持人员**/

/**认证决定人员**/
$jueding = '';

foreach ($arr_audit as $value) 
{
	$arr_code = array();$code_where = '(';
	if(getgp('banben'=='1'))//旧版本专业代码
	{
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}else{  //新版本专业代码
		if(!empty($value['audit_code']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}
	$arr_audit_code = explode('；',$value['audit_code']);

	foreach ($arr_audit_code as $val) {
		$code = explode('.',$val);
		$arr_code[] = $code[0];
	}
	

	if( !empty($arr_code) ){
		foreach ($arr_code as $val) {
			$code_where .= "audit_code like '".$val."%' or audit_code like '%,".$val."%' or ";
		}
		$code_where = substr($code_where,0,strlen($code_where)-4).')';
	}else{
		$code_where = '';
	}
	switch ($value['audit_ver']) {
		case 'A010101':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A020101':
			$str_ios = 'E:';
			$iso_where = 'A02';
			break;
		case 'A030102':
			$str_ios = 'S:';
			$iso_where = 'A03';
			break;

		default:
			break;
	}
	$arr_jueding = $db->getAll("select hr.id,hr.name from `sp_hr` hr JOIN sp_hr_qualification hrq on hr.id=hrq.uid where hr.job_type like'%1006%' and hrq.qua_type='1000' and hrq.iso='".$iso_where."' and hrq.e_date>'".date('Y-m-d')."' and hrq.deleted=0 and hr.deleted=0");

	if( !empty($arr_jueding) ){
		foreach ($arr_jueding as $va) {
			$sql    = "select id from sp_hr_audit_code where uid=".$va['id']." and ".$code_where." and deleted=0";
			$sql_r  = $db->get_var($sql);
			if(!empty($sql_r)){
				$jueding_all[] = $va['name']."(大)" ;
			}else{
				$jueding_all[] = $va['name'] ;
			}
		}
		$jueding_all = array_unique($jueding_all);

		//随机引出6人
		if(count($jueding_all)>"6"){
			$aKeys = array_rand($jueding_all,6);
			$aRand=array();  // 保存随机后的数组

			//组合随机数组
			foreach($aKeys as $v){
				// $aRand[$v]=$jueding_all[$v];
				$str_ios .= $jueding_all[$v]."、";
			}
		}else{
			foreach ($jueding_all as $v) {
				$str_ios .= $v."、";
			}
		}
		if(strlen($str_ios)==2)$str_ios='';

		$jueding .= $str_ios." ";
	}
}

/**认证决定人员**/

/**认证周期审核序列时间**/
$sql = "select sp.id as pis,sp.eid,sp.cti_id,sp.ct_code,sp.cti_code,sp.iso,sp.mark,sta.`taskBeginDate`,sta.`audit_type` 
from `sp_project` sp 
left join `sp_task_audit_team` sta on sp.id=sta.pid 
where sp.`ct_id` in ".$ctid." and sp.tid=$tid and sp.deleted=0 and sta.deleted=0 and sta.role='01' ";
$r_xl = $db->getAll($sql);
$shenheyuqiStr = '<w:tr w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidTr="00BB6566">
							<w:trPr>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{ct_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{yijieduan_shyq}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{erjieduan_shyq}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{jianyi_shyq}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{jianer_shyq}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1814" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{jiansan_shyq}</w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';

$shenheyuqiString = '';
foreach ($r_xl as $value) 
{
	$str_tmp = str_replace( '{ct_code}', $value['cti_code'], $shenheyuqiStr );//项目编号
	switch ($value['audit_type'])
	{
		case '1002'://一阶段
			$str_tmp = str_replace( '{yijieduan_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );
			$shenhemudi = "确认受审核方的管理体系是否具备认证条件，为策划二阶段获取充分信息。 ";
			
			break;
		case '1003'://二阶段
			$sql =  "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1002' and sp.deleted=0 and sta.deleted=0";
			$val_yi  = $db->getOne($sql);
			$str_tmp = str_replace( '{yijieduan_shyq}', substr($val_yi['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{erjieduan_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianyi_shyq}', (substr($value['taskBeginDate'],0,4)+1).substr($value['taskBeginDate'],4,6), $str_tmp );
			$str_tmp = str_replace( '{jianer_shyq}', (substr($value['taskBeginDate'],0,4)+2).substr($value['taskBeginDate'],4,6), $str_tmp );
			$str_tmp = str_replace( '{jiansan_shyq}',(substr($value['taskBeginDate'],0,4)+3).substr($value['taskBeginDate'],4,6), $str_tmp );
			$shenhemudi = "确认受审核方的管理体系是否符合审核准则的要求，评价所实施管理体系的有效性，以决定是否：■推荐/□保持/□恢复认证注册资格。";
			
			break;
		case '1004':
			$sql  = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1002' and sp.deleted=0 and sta.deleted=0";
			$val_yi  = $db->getOne($sql);
			$sql2    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1003' and sp.deleted=0 and sta.deleted=0";
			$val_er  = $db->getOne($sql2);
			$str_tmp = str_replace( '{yijieduan_shyq}', substr($val_yi['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{erjieduan_shyq}', substr($val_er['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianyi_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianer_shyq}', (substr($value['taskBeginDate'],0,4)+1).substr($value['taskBeginDate'],4,6), $str_tmp );
			$str_tmp = str_replace( '{jiansan_shyq}',(substr($value['taskBeginDate'],0,4)+2).substr($value['taskBeginDate'],4,6), $str_tmp );
			$shenhemudi = "确认受审核方的管理体系是否符合审核准则的要求，评价所实施管理体系的有效性，以决定是否：保持认证注册资格。";
		    
		    
		    break;
		case '1005':
			$sql =  "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1002' and sp.deleted=0 and sta.deleted=0";
			$val_yi  = $db->getOne($sql);
			$sql2    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1003' and sp.deleted=0 and sta.deleted=0";
			$val_er  = $db->getOne($sql2);
            $sql3    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1004' and sp.deleted=0 and sta.deleted=0";
			$val_jy  = $db->getOne($sql3);
			$str_tmp = str_replace( '{yijieduan_shyq}', substr($val_yi['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{erjieduan_shyq}', substr($val_er['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianyi_shyq}', substr($val_jy['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianer_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jiansan_shyq}',(substr($value['taskBeginDate'],0,4)+1).substr($value['taskBeginDate'],4,6), $str_tmp );
			$shenhemudi = "确认受审核方的管理体系是否符合审核准则的要求，评价所实施管理体系的有效性，以决定是否：保持认证注册资格。";
			
			break;
		case '1007':
			$sql     = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1002' and sp.deleted=0 and sta.deleted=0";
			$val_yi  = $db->getOne($sql);
			$sql4    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1003' and sp.deleted=0 and sta.deleted=0";
			$val_er  = $db->getOne($sql4);
			$sql2    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1004' and sp.deleted=0 and sta.deleted=0";
			$val_jy  = $db->getOne($sql2);
            $sql3    = "select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
					 from `sp_project` sp 
					 left join `sp_task_audit_team` sta 
					 on sp.id=sta.pid where sp.`ct_id` in ".$ctid." and sp.iso='".$value['iso']."' and sta.audit_type='1005' and sp.deleted=0 and sta.deleted=0";
			$val_je  = $db->getOne($sql3);
			$str_tmp = str_replace( '{yijieduan_shyq}', substr($val_yi['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{erjieduan_shyq}', substr($val_er['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianyi_shyq}', substr($val_jy['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jianer_shyq}', substr($val_je['taskBeginDate'],0,10), $str_tmp );
			$str_tmp = str_replace( '{jiansan_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );
			$shenhemudi = "确认受审核方的管理体系是否符合审核准则的要求，评价所实施管理体系的有效性，以决定是否：推荐认证注册资格。";
			
			break;
		default:
			break;
	} 
	$shenheyuqiString .= $str_tmp;
}
/**认证周期审核序列时间**/

/**证书信息**/
$zhengshuxinxiStr = '<w:tr w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidTr="001B13B0">
							<w:trPr>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1164" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:cs="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{zsxi_ct_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1164" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{zsxi_iso}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1608" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{zsxi_audit_type}{is_sites}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1292" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{str_mark}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="4378" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{certno}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1382" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{yx_time}</w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';

$zhengshuxinxiString = '';

foreach ($r_xl as $value) {
	switch ($value['audit_type']) {
		case '1001':
		case '1002':
			$audit_type='初次认证(一阶段)';
			break;
		case '1003':
			$audit_type='初次认证(二阶段)';
			break;
		case '1004':
			$audit_type='监一';
			break;
		case '1005':
			$audit_type='监二';
			break;
		case '1007':
			$audit_type='再认证';
			break;
	}
	switch ($value['mark']) {
		case '01':
			$str_mark = 'CNAS';
			break;
		case '02':
			$str_mark = 'UKAS';
			break;
		case '99':
			$str_mark = 'LLL';
			break;
		default:
			break;
	}
	$sql_certinfo = "select certno,s_date,e_date from sp_certificate where cti_id='$value[cti_id]'";
	$certinfo = $db->get_row($sql_certinfo);
	$yx_time = $certinfo['s_date']."至".$certinfo['e_date'];

	$str_tmp = str_replace( '{zsxi_ct_code}', $value['cti_code'], $zhengshuxinxiStr );
	$str_tmp = str_replace( '{zsxi_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$str_tmp = str_replace( '{zsxi_audit_type}', $audit_type, $str_tmp );
	$str_tmp = str_replace( '{is_sites}', $is_sites, $str_tmp );
	$str_tmp = str_replace( '{str_mark}', $str_mark, $str_tmp );
	$str_tmp = str_replace( '{certno}', $certinfo['certno'], $str_tmp );
	$str_tmp = str_replace( '{yx_time}', $yx_time, $str_tmp );
	$zhengshuxinxiString .= $str_tmp;
	$iso[] = $value['iso'];
}
/**证书信息**/

/**标准**/

foreach ($rs_once as $v) {
	$audit_vers[] = $v['audit_ver'];
}
//var_dump($audit_vers);exit;

$sql_biaozhun   = "select audit_basis from `sp_settings_audit_vers` where `audit_ver` in('".implode('\',\'', $audit_vers)."')";
$biaozhun       = $db->getAll($sql_biaozhun);

$shenhebiaozhun = array();
foreach ($biaozhun as $value) {
	$shenhebiaozhun[] = $value['audit_basis'];
}
(string)$shenhebiaozhun = implode('、', $shenhebiaozhun);
/**标准**/

/**附件信息**/
$sql = "select es_name,es_addr,es_scope,es_type from `sp_enterprises_site` where `deleted`=0 and eid=".$eid.' and deleted=0';
$r_fjxi = $db->getAll($sql);
$fujianxinxiStr = '<w:tr w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidTr="00983E99">
							<w:trPr>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="817" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{id}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="2693" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{es_name}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1282" w:type="dxa"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{es_type}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="2657" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{es_addr}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="3539" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{es_scope}</w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';

$fujianxinxiString='';
foreach ($r_fjxi as $key => $value) {
	$str_tmp = str_replace( '{id}', $key+1, $fujianxinxiStr );
	$str_tmp = str_replace( '{es_name}', $value['es_name'], $str_tmp );
	$str_tmp = str_replace( '{es_addr}', $value['es_addr'], $str_tmp );
	$str_tmp = str_replace( '{es_scope}', $value['es_scope'], $str_tmp );
	switch ($value['es_type']) {
		case '1000':
			$types = "固定场所";
			break;
		case '1001':
			$types = "临时场所";
			break;
	}
	$str_tmp = str_replace( '{es_type}', $types, $str_tmp );
	$fujianxinxiString .= $str_tmp;
}
//是否标准转换
//echo $audit_type_note;exit;
if (($audit_type_note)=="标准转换") 
{
	$biaozhunchnange = "■本次审核$qchecked QMS、$echecked EMS为转版审核，通过审核以决定是否向中心技术委员会推荐换发新版认证标准的认证证书。
";
}
/**附件信息**/

/**审核人士认证范围专业分类**/
$sql = "select cti.cti_id,sp.cti_code,cti.exc_clauses,sp.iso,sp.audit_type,sp.audit_code,sp.audit_code_2017,sp.pd_audit_code,sp.pd_audit_code_2017,sp.use_code,sp.use_code_2017,sp.pd_use_code,sp.pd_use_code_2017,cti.risk_level,se.ep_amount,se.site_count,cti.jdxc_num,cti.base_num,cti.total_num,cti.xc_num,cti.yjdxc_num,st.tk_num,sp.scope,st.jiehe
from  `sp_task` st 
LEFT JOIN `sp_project` sp on sp.eid=st.eid 
left join `sp_enterprises` se on sp.eid=se.eid 
left join `sp_contract` ct on sp.`eid`=ct.`eid` 
left JOIN `sp_contract_item` cti on sp.ct_id=cti.ct_id 
where cti.cti_id=sp.cti_id and sp.`ct_id` in ".$ctid." and st.`id`=".$tid." and sp.tid=".$tid." 
and st.deleted=0 and sp.deleted=0 and se.deleted=0 and ct.deleted=0 and cti.deleted=0 ";

$r_zyfl = $db->getAll($sql);

$renzhengfanweiStr = '<w:tr w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidTr="00660276">
							<w:trPr>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1034" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{cti_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="680" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{iso}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="681" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{fengxian}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1177" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="000212B6" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="16"/>
										</w:rPr>
										<w:t>{ep_amount}/{site_count}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1781" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="16"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="16"/>
										</w:rPr>
										<w:t>{total_num}/{xc_num}</w:t>
									</w:r>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="16"/>
										</w:rPr>
										<w:t>/</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="709" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00A77339" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00A77339">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{tk_num}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="709" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{jiehe}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="942" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="004B37F0" w:rsidRDefault="00C30773" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:szCs w:val="21"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{exc_clauses}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="2169" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{scope}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1106" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00C30773" w:rsidRPr="00516FDA" w:rsidRDefault="00C30773" w:rsidP="006C4960">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00516FDA">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="18"/>
											<w:szCs w:val="18"/>
										</w:rPr>
										<w:t>{audit_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';
$renzhengfanweiString = '';
foreach ($r_zyfl as $value) {
	$tmpstr = str_replace( '{cti_code}'   ,$value['cti_code'],   $renzhengfanweiStr );//项目编号
	$tmpstr = str_replace( '{iso}'        ,($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')),        $tmpstr );//体系
	$tmpstr = str_replace( '{fengxian}'   ,$arr_risk_level[$value['risk_level']], $tmpstr );//风险
	$tmpstr = str_replace( '{ep_amount}'  ,$value['ep_amount'],  $tmpstr );//体系覆盖人数
	$tmpstr = str_replace( '{site_count}' ,$value['site_count'], $tmpstr );//多场所数
	//评审人日各时期所需时间//
	switch ($ct['audit_type']) {
		case '1002':
		case '1003':
			$tmpstr = str_replace( '{total_num}'  ,$value['total_num'],  $tmpstr );//评审总人日
			break;

		case '1004':
		case '1005':
			$tmpstr = str_replace( '{total_num}'  ,$value['total_num']*0.3,  $tmpstr );//评审总人日
			break;
		
		case '1007':
			$tmpstr = str_replace( '{total_num}'  ,$value['total_num']*0.7,  $tmpstr );//评审总人日
			break;

		default:
			break;
	}
	$arr_audit_type = array("1004", "1005");
	if(in_array($value['audit_type'],$arr_audit_type,TRUE)){
		$tmpstr     = str_replace( '{xc_num}'	  ,$value['jdxc_num'],     $tmpstr );//监审现场人日
		$proportion = round($value['jdxc_num']/$xc_num_z,2);
		$tmpstr     = str_replace( '{jiehe}'     ,$proportion,   $tmpstr );//比例
	}else{
		$tmpstr     = str_replace( '{xc_num}'	  ,$value['xc_num'],     $tmpstr );//评审现场人日
		$proportion = round($value['xc_num']/$xc_num_z,2);
		$tmpstr     = str_replace( '{jiehe}'     ,$proportion,   $tmpstr );//比例
	}
	
	$tmpstr = str_replace( '{yjdxc_num}'  ,$value['yjdxc_num'],  $tmpstr );//评审文审人日
	$tmpstr = str_replace( '{scope}'      ,$value['scope'],      $tmpstr );//范围
	if(getgp('banben')=='1')
	{
		(!empty($value['pd_audit_code_2017']))?$value['audit_code_2017']=$value['pd_audit_code_2017']:$value['audit_code_2017']=$value['audit_code_2017'];
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}else{
		(!empty($value['pd_audit_code']))?$value['audit_code']=$value['pd_audit_code']:$value['audit_code']=$value['audit_code'];
		if(!empty($value['audit_code']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}

	$tmpstr = str_replace( '{audit_code}' ,$value['audit_code'], $tmpstr );//专业分类
	$tmpstr = str_replace( '{tk_num}'     ,$value['tk_num']*$proportion,   $tmpstr );//实际现场人日
	
	$tmpstr = str_replace( '{exc_clauses}',empty($value['exc_clauses'])?'无':$value['exc_clauses'],$tmpstr );//实际现场人日
	$renzhengfanweiString .= $tmpstr;
}
/**审核人士认证范围专业分类**/

/**审核组信息**/
$shenhezuStr = '<w:tr w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidTr="00B31EE4">
							<w:trPr>
								<w:trHeight w:val="270"/>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="841" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:cs="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:cs="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_name}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="741" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="00130409" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
										<w:t>{shenhezu_iso}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="653" w:type="dxa"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_qua_type}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="2126" w:type="dxa"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="00130409">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_qua_no}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1843" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_taskBeginDate}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="708" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_role}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1560" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
											<w:sz w:val="18"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="000212B6">
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_audit_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1134" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenmezu_audit_job}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1382" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00130409" w:rsidRPr="000212B6" w:rsidRDefault="002406D4" w:rsidP="000212B6">
									<w:pPr>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
										</w:rPr>
										<w:t>{shenhezu_tel}</w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';

$shenhezuString = '';
$shenhezu = array();
$sql = "SELECT 
stat.name,stat.role,stat.uid,stat.qua_type,stat.taskBeginDate,stat.taskEndDate,stat.audit_code,stat.audit_code_2017,stat.iso,hr.tel,hr.audit_job,hrq.qua_no 
from sp_task_audit_team stat 
left join sp_hr hr on stat.uid=hr.id 
left join `sp_hr_qualification` hrq ON hr.id=hrq.uid and stat.iso=hrq.iso and stat.qua_type=hrq.qua_type 
where stat.tid=".$tid." and stat.qua_type in('01','02','03','04') and stat.deleted=0 and hr.deleted=0 and hrq.deleted=0";

$shenhezu = $db->getALl( $sql);
$role_name='';
foreach ($shenhezu as $key => $value) {
	if( $value['role']=='01' )$role_name=$value['name'];
	switch ($value['qua_type']) {
			case '01':
				$qua_type = '高级审核员';
				break;
			case '02':
				$qua_type = '审核员';
				break;
			case '03':
				$qua_type = '实习审核员';
				break;
			case '04':
				$qua_type = '技术专家';
				break;
			default:
				$qua_type = '其他';
				break;
		}
	$str_tmp = str_replace( '{shenhezu_name}',       $value['name'], $shenhezuStr );
	$str_tmp = str_replace( '{shenhezu_qua_no}',     $value['qua_no'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_tel}',        $value['tel'], $str_tmp );
	if(getgp('banben')=='1')
	{
		$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code_2017'], $str_tmp );
	}else{
		$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code'], $str_tmp );
	}
	$str_tmp = str_replace( '{shenhezu_qua_type}',   $qua_type, $str_tmp );
	$str_tmp = str_replace( '{shenhezu_role}',      ($value['role']=='01')?'组长':'组员', $str_tmp );
	$str_tmp = str_replace( '{shenmezu_audit_job}', ($value['audit_job']==1)?'专职':(($value['audit_job']==0)?'兼职':'其他'), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_taskBeginDate}', substr($value['taskBeginDate'],0,16).' 至 '.substr($value['taskEndDate'],0,16), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$shenhezuString .= $str_tmp;
}



/**专业类别提示**/
foreach ($r_zyfl as  $value) 
{
	
	if(getgp('banben')=='1')
	{
		(!empty($value['pd_audit_code_2017']))?$value['audit_code_2017']=$value['pd_audit_code_2017']:$value['audit_code_2017']=$value['audit_code_2017'];
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';
			
			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			
			$value['audit_code_2017'] = $codeims;
			
		}
	}else{
		(!empty($value['pd_audit_code']))?$value['audit_code']=$value['pd_audit_code']:$value['audit_code']=$value['audit_code'];
		if(!empty($value['audit_code']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code']));
			$codeims   = '';
			foreach($codeList as $code)
			{
				if(!empty($code))$codeims .= $db->get_var("select shangbao from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
			
		}
	}
	if(getgp('banben')=='1')//旧版本专业代码分组
	{
		(!empty($value['pd_use_code_2017']))?$value['use_code_2017']=$value['pd_use_code_2017']:$value['use_code_2017']=$value['use_code_2017'];
	}else{                  //新版本专业代码分组
		(!empty($value['pd_use_code']))?$value['use_code']=$value['pd_use_code']:$value['use_code']=$value['use_code'];
	}
	$ac      = array_filter(array_unique(explode('；',$value['audit_code'])));
	$uc      = array_filter(array_unique(explode('；',$value['use_code'])));
	$ac_2017 = array_filter(array_unique(explode('；',$value['audit_code_2017'])));
	$uc_2017 = array_filter(array_unique(explode('；',$value['use_code_2017'])));

	if(getgp('banben')=='1')
	{
		$sql = sprintf("select iso,code,shangbao,msg from sp_settings_audit_code where iso='%s' and banben=1 and deleted=0",$value['iso']);
		
		$zylb= $db->getALl($sql);

		foreach ($zylb as $val) 
		{
			
			if (in_array("$val[shangbao]",$ac_2017) && in_array("$val[code]",$uc_2017)) 
			{
				
				switch ($val['iso']) {
				case 'A01':
					$l_qms .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				case 'A02':
					$l_ems .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				case 'A03':
					$l_ohsms .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				default:
					break;
				}
			
			}
	
		}
	}else{
		$sql = sprintf("select iso,code,shangbao,msg from sp_settings_audit_code where iso='%s' and banben=2 and deleted=0",$value['iso']);
		$zylb= $db->getALl($sql);
		foreach ($zylb as $val) 
		{
			if (in_array("$val[shangbao]",$ac) && in_array("$val[code]",$uc)) 
			{
				switch ($val['iso']) {
				case 'A01':
					$l_qms .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				case 'A02':
					$l_ems .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				case 'A03':
					$l_ohsms .= $val['shangbao'].':'.$val['msg'].'; ';
					break;
				default:
					break;
				}
			
			}
	
		}
	}

	
}
$l_qms = substr($l_qms, 0,-2);
$l_ems = substr($l_ems, 0,-2);
$l_ohsms = substr($l_ohsms, 0,-2);


/**专业类别提示**/

$tishixinxi = $db->get_row("SELECT zizhi,fwbg_note,rrbg_note,tsxx_note,zyxx_note,qita_note FROM `sp_task` WHERE `id`='$tid'");

$sql_certnow = "select certno,cert_scope from sp_certificate where ct_id='$ct_id'";
$certnow = $db->getAll($sql_certnow);
foreach ($certnow as $value) {
	$cert_str .= $value['certno'].":".$value['cert_scope']."; ";
}
$cert_str = substr($cert_str, 0,-2);



$filename = '审表001 审核方案策划和审核任务下达书('.$ep_name.').doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-001.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata);

$output = str_replace( '{shenheyuqiString}', $shenheyuqiString, $output );//认证周期审核序列时间
$output = str_replace( '{zhengshuxinxiString}', $zhengshuxinxiString, $output );//证书信息
$output = str_replace( '{fujianxinxiString}', $fujianxinxiString, $output );//附件信息
$output = str_replace( '{shenhezuString}', $shenhezuString, $output );//审核组信息
$output = str_replace( '{renzhengfanweiString}', $renzhengfanweiString, $output );//认证范围及分类
$output = str_replace( '{shenhemudi}', $shenhemudi, $output );//审核目的
$output = str_replace( '{biaozhunchnange}', $biaozhunchnange, $output );//审核目的
$output = str_replace( '{jueding}', $jueding, $output );//认证决定人员
$output = str_replace( '{zhichi}', $zhichi, $output );//支持人员

/**专业类别提示**/
$output = str_replace( '{l_qms}', $l_qms, $output );
$output = str_replace( '{l_ems}', $l_ems, $output );
$output = str_replace( '{l_ohsms}', $l_ohsms, $output );
/**专业类别提示**/

$output = str_replace( '{role_name}', $role_name, $output );
$output = str_replace( '{certno}', $certno['certno'], $output );
$output = str_replace( '{bg_addr}', $bg_addr, $output );
$output = str_replace( '{bg_addrcode}', $bg_addrcode, $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{cta_addrCode}', $cta_addrcode, $output );
$prod_check =str_replace('\"','"',$prod_check);
$prod_check =str_replace("\'","'",$prod_check);
$prod_check=str_replace("&amp;quot;",'"',$prod_check);
$prod_check = unserialize($prod_check);
if(in_array(1,$prod_check)){//生产地址
	$output = str_replace( '{prod_addr}', $prod_addr, $output );
	$output = str_replace( '{prod_addrcode}', $prod_addrcode, $output );
}

$output = str_replace( '{ep_addr}', $ep_addr, $output );
$output = str_replace( '{ep_addrCode}', $ep_addrcode, $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{site_count}', $site_count, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $extra_ep['person_mail'], $output );
$output = str_replace( '{arr_work_code}', $arr_work_code, $output );
$output = str_replace( '{work_code}', $work_code, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );

$output = str_replace( '{capital}', $capital, $output );
$output = str_replace( '{tb_date}', substr($tb_date,0,16).' 至 '.substr($te_date,0,16), $output );
$output = str_replace( '{shenhebiaozhun}',$shenhebiaozhun , $output );//审核标准

$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{delegate}', $delegate, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{approval_date}', $approval_date, $output );

$output = str_replace( '{zizhi}'     ,$tishixinxi['zizhi'],       $output );//资质
$output = str_replace( '{wb_db}'     ,$wb_db,                     $output );//外包倒班
$output = str_replace( '{fwbg_note}' ,$tishixinxi['fwbg_note'],   $output );
$output = str_replace( '{rrbg_note}' ,$tishixinxi['rrbg_note'],   $output );
$output = str_replace( '{tsxx_note}' ,$tishixinxi['tsxx_note'],   $output );
$output = str_replace( '{zyxx_note}' ,$tishixinxi['zyxx_note'],   $output );
$output = str_replace( '{qita_note}' ,$tishixinxi['qita_note'],   $output );

$output = str_replace( '{cert_str}'  ,$cert_str,  $output );//有效证书编号及范围
foreach ($r_xl as $value) 
{
	switch ($value['audit_type'])
	{
		case '1002':
		case '1003';
			$output  = str_replace( '{ck100}', $checked , $output );
		    break;
		case '1004';
		case '1005';
			$output  = str_replace( '{ck100}', $checked , $output );
			$output  = str_replace( '{ck102}', $checked , $output );
		    break;
		case '1007';
			$output  = str_replace( '{ck103}', $checked , $output );
		    break;
	}
}
if (!empty($r_fjxi)) 
{
	$output  = str_replace( '{ck101}', $checked , $output );
}
$output = preg_replace("/\{ck.+?\}/", $check, $output);
$output = preg_replace("/\{.[^-]+?\}/", "", $output);

if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	if(!empty(getgp('dates'))){
		$filePath = CONF.'downs'.'/'.getgp('dates');
	}else{
		$filePath = CONF.'downs';
	}
	
	//没有目录创建目录
	if(!is_dir($filePath)) {
		mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filename) ){
		@unlink ($filePath.'/'.$filename); 
	}

	file_put_contents($filePath.'/'.$filename,$output);
	
	if( file_exists($filePath.'/'.$filename) ){
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
	
?>