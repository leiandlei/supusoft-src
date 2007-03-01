<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$tid = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );
$checked   = '■';
$nochecked = '□';
$arr_audit = $db->getAll("select audit_ver,audit_code from sp_contract_item where ct_id=".$ctid." and deleted=0");

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

/**专业支持人员**/
$zhichi = '';
foreach ($arr_audit as $value) {
	$arr_code = array();$code_where = '(';
	$arr_audit_code = explode(';',$value['audit_code']);

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
	$arr_jueding = $db->getAll("select hr.id,hr.name from `sp_hr` hr JOIN sp_hr_qualification hrq on hr.id=hrq.uid where hr.job_type not like'%1006%' and hrq.qua_type!='1000' and hrq.iso='".$iso_where."' and hrq.e_date>'".date('Y-m-d')."' and hrq.deleted=0 and hr.deleted=0");
	if( !empty($arr_jueding) ){
		foreach ($arr_jueding as $va) {
			$sql    = "select id from sp_hr_audit_code where uid=".$va['id']." and ".$code_where." and deleted=0";
			$sql_r  = $db->get_var($sql);
			$str_ios .= $va['name'];
			if(!empty($sql_r))$str_ios .= "(大)";$str_ios .= "、";
		}
		if(strlen($str_ios)==2)$str_ios='';
		$zhichi .= $str_ios;
	}
}
/**专业支持人员**/

/**附件信息**/
$sql = "select es_name,es_addr,es_scope from `sp_enterprises_site` where `deleted`=0 and eid=".$eid.' and deleted=0';
$r_fjxi = $db->getAll($sql);
$fujianxinxiStr = '<w:tr wsp:rsidR="007713D9" wsp:rsidTr="007713D9">
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="704" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="004D2340" w:rsidRDefault="00666F28">
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
										<w:t>{id}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1968" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="004D2340" w:rsidRDefault="00666F28">
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
										<w:t>{es_name}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="3472" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="004D2340" w:rsidRDefault="00666F28">
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
										<w:t>{es_addr}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="4838" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
								</w:tcPr>
								<w:p w:rsidR="004D2340" w:rsidRDefault="00666F28">
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
	$fujianxinxiString .= $str_tmp;
}
/**附件信息**/

/**审核范围**/
$shenhefanweiStr = '<w:tr wsp:rsidR="007713D9" wsp:rsidTr="007713D9"><w:tc><w:tcPr><w:tcW w:w="1101" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tcBorders></w:tcPr><w:p wsp:rsidR="007713D9" wsp:rsidRDefault="007713D9"><w:pPr><w:spacing w:line="360" w:line-rule="exact"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体"/><wx:font wx:val="宋体"/><w:sz-cs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><wx:font wx:val="宋体"/></w:rPr><w:t>{iso}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="nil"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tcBorders></w:tcPr><w:p wsp:rsidR="007713D9" wsp:rsidRDefault="001051B4"><w:pPr><w:spacing w:line="360" w:line-rule="exact"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体"/><wx:font wx:val="宋体"/><w:sz-cs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><wx:font wx:val="宋体"/></w:rPr><w:t>{ep_name}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="nil"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tcBorders></w:tcPr><w:p wsp:rsidR="007713D9" wsp:rsidRDefault="001051B4"><w:pPr><w:spacing w:line="360" w:line-rule="exact"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体"/><wx:font wx:val="宋体"/><w:sz-cs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><wx:font wx:val="宋体"/></w:rPr><w:t>{bg_addr}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="850" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="nil"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tcBorders></w:tcPr><w:p wsp:rsidR="007713D9" wsp:rsidRDefault="001051B4"><w:pPr><w:spacing w:line="360" w:line-rule="exact"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体"/><wx:font wx:val="宋体"/><w:sz-cs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><wx:font wx:val="宋体"/></w:rPr><w:t>{scope}</w:t></w:r></w:p></w:tc></w:tr>';
$shenhefanweiString='';
$ct=$db->getAll("SELECT e.ep_name,e.bg_addr,p.iso,p.scope FROM `sp_project` p LEFT JOIN sp_enterprises e on p.eid=e.eid WHERE p.`tid` = '$tid' AND p.`deleted` = '0' and e.deleted='0'");
foreach ($ct as $value) {
	switch ($value['iso']) {
		case 'A01':
			$shenhefanweiiso = 'QMS';
			break;
		case 'A02':
			$shenhefanweiiso = 'EMS';
			break;
		case 'A03':
			$shenhefanweiiso = 'OHSMS';
			break;
	}
	$str_tmp = str_replace( '{iso}', $shenhefanweiiso, $shenhefanweiStr );
	$str_tmp = str_replace( '{ep_name}', $value['ep_name'], $str_tmp );
	$str_tmp = str_replace( '{bg_addr}', $value['bg_addr'], $str_tmp );
	$str_tmp = str_replace( '{scope}', $value['scope'], $str_tmp );
	$shenhefanweiString .= $str_tmp;
}
/**审核范围**/

/**审核组信息**/
$shenhezuStr = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tblBorders><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:trHeight w:val="367" w:h-rule="atLeast"/><w:jc w:val="center"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="1096" w:type="dxa"/><w:vmerge w:val="continue"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:widowControl/><w:snapToGrid w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:fareast="黑体" w:hint="fareast"/><w:b/><w:sz-cs w:val="21"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1066" w:type="dxa"/><w:gridSpan w:val="2"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_name}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1126" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_role}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1400" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_qua_type}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1593" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_qua_no}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1296" w:type="dxa"/><w:gridSpan w:val="2"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_audit_code}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="842" w:type="dxa"/><w:gridSpan w:val="2"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_sex}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1650" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:adjustRightInd w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:i-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>{shenhezu_tel}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="897" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:widowControl/><w:snapToGrid w:val="off"/><w:spacing w:line="320" w:line-rule="exact"/><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="fareast"/><w:b/><w:sz-cs w:val="21"/></w:rPr></w:pPr></w:p></w:tc></w:tr>';
$shenhezuString = '';
$shenhezu = array();
$sql = "SELECT 
stat.name,hr.sex,stat.role,stat.uid,stat.qua_type,stat.taskBeginDate,stat.audit_code,stat.iso,hr.tel,hr.audit_job,hrq.qua_no 
from sp_task_audit_team stat 
left join sp_hr hr on stat.uid=hr.id 
left join `sp_hr_qualification` hrq ON hr.id=hrq.uid and stat.iso=hrq.iso 
where stat.tid=".$tid." and hrq.qua_type in('01','02','03') and stat.deleted=0";

//echo $sql;exit;
$shenhezu = $db->getALl( $sql);
foreach ($shenhezu as $key => $value) {
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
	$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_qua_type}',   $qua_type, $str_tmp );

	$str_tmp = str_replace( '{shenhezu_role}',      ($value['role']=='01')?'组长':'组员', $str_tmp );
	$str_tmp = str_replace( '{shenhezu_sex}',      ($value['sex']=='1')?'男':'女', $str_tmp );
	$str_tmp = str_replace( '{shenmezu_audit_job}', ($value['audit_job']==1)?'专职':(($value['audit_job']==0)?'兼职':'其他'), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_taskBeginDate}', substr($value['taskBeginDate'],0,10).' 8:30至17:30', $str_tmp );
	$str_tmp = str_replace( '{shenhezu_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$shenhezuString .= $str_tmp;
}
/**审核组信息**/

//
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`ct_id`='$ctid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);

//审核信息
$arr_project = $db->getAll('select audit_ver,audit_type from `sp_project` where deleted=0 and `tid` = '.$tid);
$filename = $ep_name .'管理体系审核计划.doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-007.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);
$output = str_replace('{ep_name}', $ep_name, $tpldata);
$output = str_replace( '{shenhezuString}', $shenhezuString, $output );//审核组信息
$output = str_replace( '{fujianxinxiString}', $fujianxinxiString, $output );//附件信息
$output = str_replace( '{shenhefanweiString}', $shenhefanweiString, $output );//审核范围
$output = str_replace( '{zhichi}', $zhichi, $output );

//echo $ep_name;exit;
$output = str_replace('{ct_code}', $ct_code, $output);
//echo $ep_name;exit;
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
$output = str_replace('{ep_addr}', $ep_addr, $output);
$output = str_replace('{person}', $person, $output);
$output = str_replace('{person_tel}', $person_tel, $output);
$output = str_replace('{ep_fax}', $ep_fax, $output);
$output = str_replace('{scope}', $scope, $output);
$output = str_replace('{cta_addrcode}', $cta_addrcode, $output);
$output = str_replace('{date_rq}', date('Y.m.d'), $output);
/**checked替换**/
foreach ($arr_project as $value) {
	switch ($value['audit_ver']) {
		case 'A010101':
			$output = str_replace( '{{checked_16}}', $checked , $output );
			$output = str_replace( '{{checked_45}}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{{checked_1}}', $checked , $output );
					$output = str_replace( '{{checked_17}}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{{checked_2}}', $checked , $output );
					$output = str_replace( '{{checked_8}}', $checked , $output );
					$output = str_replace( '{{checked_18}}', $checked , $output );
					break;
			}
			break;
		case 'A010103':
			$output = str_replace( '{{checked_16}}', $checked , $output );
			$output = str_replace( '{{checked_46}}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{{checked_1}}', $checked , $output );
					$output = str_replace( '{{checked_17}}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{{checked_2}}', $checked , $output );
					$output = str_replace( '{{checked_9}}', $checked , $output );
					$output = str_replace( '{{checked_18}}', $checked , $output );
					break;
			}
			break;

		case 'A020101':
			$output = str_replace( '{{checked_22}}', $checked , $output );
			$output = str_replace( '{{checked_48}}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{{checked_1}}', $checked , $output );
					$output = str_replace( '{{checked_23}}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{{checked_2}}', $checked , $output );
					$output = str_replace( '{{checked_11}}', $checked , $output );
					$output = str_replace( '{{checked_24}}', $checked , $output );
					break;
			}
			break;
		case 'A020101':
			$output = str_replace( '{{checked_22}}', $checked , $output );
			$output = str_replace( '{{checked_49}}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{{checked_1}}', $checked , $output );
					$output = str_replace( '{{checked_23}}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{{checked_2}}', $checked , $output );
					$output = str_replace( '{{checked_12}}', $checked , $output );
					$output = str_replace( '{{checked_24}}', $checked , $output );
					break;
			}
			break;

		case 'A030102':
			$output = str_replace( '{{checked_28}}', $checked , $output );
			$output = str_replace( '{{checked_47}}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{{checked_1}}', $checked , $output );
					$output = str_replace( '{{checked_29}}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{{checked_2}}', $checked , $output );
					$output = str_replace( '{{checked_10}}', $checked , $output );
					$output = str_replace( '{{checked_30}}', $checked , $output );
					break;
			}
			break;
	}
}
$output = preg_replace("/\{\{.+?\}\}/", $nochecked, $output);
/**checked替换**/

$output = preg_replace("/\{.+?\}/", "", $output);
//echo $scope;exit;

if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	$filePath = CONF.'downs';
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