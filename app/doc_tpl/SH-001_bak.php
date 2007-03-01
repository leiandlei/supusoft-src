<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );
$check="□";
$checked="■";

$tid  = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );

$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid' and deleted=0");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
$audit_1002=$audit_1007=$check;
$ct = $db->get_row( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//任务审批时间
$sql = 'select `approval_date` from `sp_task` where id='.$tid.' and deleted=0';
$approval_date = $db->get_var($sql);
$approval_date = sprintf(str_replace("-", "%s", $approval_date),'年','月').'日';

/**认证周期审核序列时间**/
$sql  ="select sp.id as pis,sp.eid,sp.ct_code,sp.cti_code,sp.iso,sta.`taskBeginDate`,sta.`audit_type` 
from `sp_project` sp 
left join `sp_task_audit_team` sta on sp.id=sta.pid 
where sp.`ct_id`=".$ctid." and sp.tid!=0  and sp.deleted=0 and sta.deleted=0 
group by sp.iso";
$r_xl = $db->getAll($sql);
$shenheyuqiStr = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tblBorders><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:jc w:val="center"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:fareast="宋体" w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>{ct_code}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr><w:t>{yijieduan_shyq}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr><w:t></w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1814" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="fareast"/><w:lang w:val="EN-US"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:h-ansi="宋体" w:hint="default"/><w:lang w:val="EN-US"/></w:rPr><w:t></w:t></w:r></w:p></w:tc></w:tr>';
$shenheyuqiString = '';
foreach ($r_xl as $value) {
	$str_tmp = str_replace( '{ct_code}', $value['cti_code'], $shenheyuqiStr );//项目编号
	$str_tmp = str_replace( '{yijieduan_shyq}', substr($value['taskBeginDate'],0,10), $str_tmp );//项目编号
	$shenheyuqiString .= $str_tmp;
}
/**认证周期审核序列时间**/

/**证书信息**/
$zhengshuxinxiStr = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:left w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:bottom w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:right w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideH w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideV w:val="single" w:color="auto" w:sz="4" w:space="0"/></w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:jc w:val="center"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="1371" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>{zsxi_ct_code}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1151" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{zsxi_iso}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1991" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t></w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t></w:t></w:r><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>{zsxi_audit_type}</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t></w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1122" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>LLL</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3829" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1524" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="top"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr></w:p></w:tc></w:tr>';
$zhengshuxinxiString = '';
foreach ($r_xl as $value) {
	switch ($value['audit_type']) {
		case '1001':
		case '1002':
			$audit_type='初次认证(一阶段)';
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
	$str_tmp = str_replace( '{zsxi_ct_code}', $value['cti_code'], $zhengshuxinxiStr );
	$str_tmp = str_replace( '{zsxi_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$str_tmp = str_replace( '{zsxi_audit_type}', $audit_type, $str_tmp );
	$zhengshuxinxiString .= $str_tmp;
}
// echo '<pre />';
// print_r($r_xl);exit;
// exit;
/**证书信息**/

/**附件信息**/
$sql = "select es_name,es_addr,es_scope from `sp_enterprises_site` where `deleted`=0 and eid=".$eid.' and deleted=0';
$r_fjxi = $db->getAll($sql);
$fujianxinxiStr = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:left w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:bottom w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:right w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideH w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideV w:val="single" w:color="auto" w:sz="4" w:space="0"/></w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:trHeight w:val="1060" w:hRule="atLeast"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="704" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>{id}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1968" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia"/></w:rPr><w:t>{es_name}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="3472" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>{es_addr}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="4838" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="left"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:sz w:val="16"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t>{es_scope}</w:t></w:r><w:bookmarkStart w:id="0" w:name="_GoBack"/><w:bookmarkEnd w:id="0"/></w:p></w:tc></w:tr>';
$fujianxinxiString='';
foreach ($r_fjxi as $key => $value) {
	$str_tmp = str_replace( '{id}', $key+1, $fujianxinxiStr );
	$str_tmp = str_replace( '{es_name}', $value['es_name'], $str_tmp );
	$str_tmp = str_replace( '{es_addr}', $value['es_addr'], $str_tmp );
	$str_tmp = str_replace( '{es_scope}', $value['es_scope'], $str_tmp );
	$fujianxinxiString .= $str_tmp;
}
/**附件信息**/


/**审核人士认证范围专业分类**/
$sql = "select cti.cti_id,sp.cti_code,sp.iso,sp.audit_type,sp.audit_code,sp.use_code,cti.risk_level,se.ep_amount,se.site_count,cti.total_num,cti.xc_num,cti.yjdxc_num,st.tk_num,sp.scope
from  `sp_task` st 
LEFT JOIN `sp_project` sp on sp.eid=st.eid 
left join `sp_enterprises` se on sp.eid=se.eid 
left join `sp_contract` ct on sp.`eid`=ct.`eid` 
left JOIN `sp_contract_item` cti on sp.ct_id=cti.ct_id 
where cti.cti_id=sp.cti_id and sp.`ct_id`=".$ctid." and st.`id`=".$tid." and sp.tid!=0 
and st.deleted=0 and sp.deleted=0 and se.deleted=0 and ct.deleted=0 and cti.deleted=0";
$r_zyfl = $db->getAll($sql);
// echo '<pre />';
// print_r($r_zyfl);exit;
/**审核人士认证范围专业分类**/

/**专业类别提示**/
foreach ($r_zyfl as  $value) {
	$sql = "select iso,shangbao,msg from sp_settings_audit_code where iso='%s' and shangbao='%s' and `code`='%s' and deleted=0";
	$sql = sprintf($sql,$value['iso'],$value['audit_code'],$value['use_code']);
	$zylb= $db->getOne($sql);
	switch ($zylb['iso']) {
		case 'A01':
			$l_qms = $zylb['shangbao'].':'.$zylb['msg'];
			break;
		case 'A02':
			$l_ems = $zylb['shangbao'].':'.$zylb['msg'];
			break;
		case 'A03':
			$l_ohsms = $zylb['shangbao'].':'.$zylb['msg'];
			break;
		default:
			# code...
			break;
	}
}
/**专业类别提示**/

/**审核组信息**/
$shenhezuStr = '<w:tr><w:tblPrEx><w:tblBorders><w:top w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:left w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:bottom w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:right w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideH w:val="single" w:color="auto" w:sz="4" w:space="0"/><w:insideV w:val="single" w:color="auto" w:sz="4" w:space="0"/></w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPrEx><w:trPr><w:trHeight w:val="270" w:hRule="atLeast"/><w:jc w:val="center"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="959" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体" w:cs="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体" w:cs="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_name}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="850" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_iso}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="709" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_qua_type}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="2126" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:widowControl/><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:color w:val="000000"/><w:kern w:val="0"/><w:sz w:val="18"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_qua_no}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/></w:rPr><w:t>{shenhezu_taskBeginDate}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="708" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_role}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenhezu_audit_code}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1134" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:szCs w:val="21"/></w:rPr><w:t>{shenmezu_audit_job}</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="1382" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="auto"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:widowControl/><w:jc w:val="center"/><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:color w:val="000000"/><w:kern w:val="0"/><w:szCs w:val="22"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:hint="eastAsia" w:ascii="宋体" w:hAnsi="宋体"/><w:color w:val="000000"/><w:szCs w:val="22"/></w:rPr><w:t>{shenhezu_tel}</w:t></w:r></w:p></w:tc></w:tr>';
$shenhezuString = '';
$shenhezu = array();
$sql = "SELECT 
stat.name,stat.role,stat.uid,stat.qua_type,stat.taskBeginDate,stat.audit_code,stat.iso,hr.tel,hr.audit_job,hrq.qua_no 
from sp_task_audit_team stat 
left join sp_hr hr on stat.uid=hr.id 
left join `sp_hr_qualification` hrq ON hr.id=hrq.uid and stat.iso=hrq.iso 
where stat.tid=".$tid." and stat.deleted=0 and hr.deleted=0 and hrq.deleted=0";
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
	$str_tmp = str_replace( '{shenmezu_audit_job}', ($value['audit_job']==1)?'专职':(($value['audit_job']==0)?'兼职':'其他'), $str_tmp );
	$str_tmp = str_replace( '{shenhezu_taskBeginDate}', substr($value['taskBeginDate'],0,10).' 8:30至17:30', $str_tmp );
	$str_tmp = str_replace( '{shenhezu_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$shenhezuString .= $str_tmp;
}
/**审核组信息**/

$filename = $ep_name .' 审核方案策划和审核任务下达书.doc';

//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-001.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata);
$output = str_replace( '{xmlStr}',  $xmlStr, $output );

//$output = str_replace( '{shenheyuqiString}', $shenheyuqiString, $output );//认证周期审核序列时间
// $output = str_replace( '{zhengshuxinxiString}', $zhengshuxinxiString, $output );//证书信息
// $output = str_replace( '{fujianxinxiString}', $fujianxinxiString, $output );//附加信息
// $output = str_replace( '{shenhezuString}', $shenhezuString, $output );//审核组信息

/**专业类别提示**/
$output = str_replace( '{l_qms}', $l_qms, $output );
$output = str_replace( '{l_ems}', $l_ems, $output );
$output = str_replace( '{l_ohsms}', $l_ohsms, $output );
/**专业类别提示**/

/**审核人士认证范围专业分类**/
foreach ($r_zyfl as $key => $value) {
	$key += 1;
	$output = str_replace( '{ct_code_'.$key.'}'   ,$value['cti_code'],   $output );//项目编号
	$output = str_replace( '{iso_'.$key.'}'       ,($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')),        $output );//体系
	$output = str_replace( '{fengxian_'.$key.'}'  ,$value['risk_level'], $output );//风险
	$output = str_replace( '{total_'.$key.'}'     ,$value['ep_amount'],  $output );//体系覆盖人数
	$output = str_replace( '{site_count_'.$key.'}',$value['site_count'], $output );//多场所数
	$output = str_replace( '{pingshen_z_'.$key.'}',$value['total_num'],  $output );//评审总人日
	$output = str_replace( '{pingshen_x_'.$key.'}',$value['xc_num'],     $output );//评审现场人日
	$output = str_replace( '{pingshen_w_'.$key.'}',$value['yjdxc_num'],  $output );//评审文审人日
	$output = str_replace( '{scope_'.$key.'}'     ,$value['scope'],      $output );//范围
	$output = str_replace( '{auditCode_'.$key.'}' ,$value['audit_code'], $output );//专业分类
	$output = str_replace( '{sjxc}'               ,$value['tk_num'],   $output );//实际现场人日
}
/**审核人士认证范围专业分类**/


$output = str_replace( '{leader}', $leader, $output );
$output = str_replace( '{certno}', $certno['certno'], $output );
$output = str_replace( '{cta_addr}', $cta_addr, $output );
$output = str_replace( '{cta_addrCode}', $cta_addrcode, $output );
$output = str_replace( '{prod_addr}', $prod_addr, $output );
$output = str_replace( '{prod_addrcode}', $prod_addrcode, $output );
$output = str_replace( '{ep_addr}', $ep_addr, $output );
$output = str_replace( '{ep_addrcode}', $ep_addrcode, $output );
$output = str_replace( '{ep_amount}', $ep_amount, $output );
$output = str_replace( '{site_count}', $site_count, $output );
$output = str_replace( '{person}', $person, $output );
$output = str_replace( '{person_tel}', $person_tel, $output );
$output = str_replace( '{person_email}', $person_email, $output );
//echo $person_email;exit;
$output = str_replace( '{work_code}', $work_code, $output );
//echo $work_code;exit;
$output = str_replace( '{person_tel}', $person_tel, $output );


$output = str_replace( '{capital}', $capital, $output );
$output = str_replace( '{tb_date}', substr($tb_date,0,10), $output );
$output = str_replace( '{manager_daibiao}', $manager_daibiao, $output );
$output = str_replace( '{delegate}', $delegate, $output );
$output = str_replace( '{ep_fax}', $ep_fax, $output );
$output = str_replace( '{approval_date}', $approval_date, $output );

$output = preg_replace("/\{.+?\}/", "", $output);

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