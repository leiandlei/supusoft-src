<?php
require( DATA_DIR . 'cache/audit_ver.cache.php' );

$tid       = (int)getgp( 'tid' );
//$ctid      = (int)getgp( 'ct_id' );
$ctid=$_GET['ct_id'];
$ctid_arr=array_unique(explode('、',$ctid));
$ctid_i=implode(',',$ctid_arr);
$ctid="(".implode(',',$ctid_arr).")";//修改成数组查询
$time      = date("Y-m-d",time());
$checked   = '■';
$nochecked = '□';
$arr_audit = $db->getAll("select audit_ver,audit_code,audit_code_2017 from sp_contract_item where ct_id in ".$ctid." and deleted=0");

$t_info    = $db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
//查询audit_ver
$t_audit_ver=$db->get_row("SELECT audit_ver FROM `sp_task` WHERE `id` = $tid");
extract( $t_info, EXTR_SKIP );
extract( $t_audit_ver, EXTR_SKIP );
//$audit_ver=array();
//foreach($t_audit_ver as $key=>$val){
//    $audit_ver[]=$val['audit_ver'];
//};
//$audit_ver='('.implode(array_unique($audit_ver),',').')';
//var_dump($t_audit_ver);
//exit;
//sp_certificate ID
$sid=array();
$sid_arr=$db->getAll("SELECT id FROM `sp_certificate` WHERE `ct_id` in $ctid ");
foreach($sid_arr as $key=>$val){
    $sid[]=$val['id'];
};
$sid='('.implode($sid,',').')';
$ra=array();
$zbsh=$nochecked;
$qchecked=$echecked=$schecked="□";
if($sid_arr){
//    $rs=$db->getAll("SELECT id FROM `sp_certificate_change` WHERE `zsid` in $sid and `audit_ver` = '".$audit_ver."' and `cg_type` = 104 and `cg_af`=99");
    $rs_once = $db->getAll( "SELECT * FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0' ORDER BY audit_type_note desc");
    if($rs_once){
        foreach ($rs_once as $val){
            if($val['iso']=="A01"){
                if($val['audit_type_note']=="标准转换"){
                    $zbsh=$checked;
                    $qchecked="■";
                }else{
                    $qchecked="□";
                }
            }elseif($val['iso']=="A02"){
                if($val['audit_type_note']=="标准转换"){
                    $zbsh=$checked;
                    $echecked="■";
                }else{
                    $echecked="□";
                }
            }else{
                if($val['audit_type_note']=="标准转换"){
                    $zbsh=$checked;
                    $schecked="■";
                }else{
                    $schecked="□";
                }
            }
        }
    }
}
//
//if(count($rs)>0){
//    $zbsh=$checked;
//    $zbsh1=$checked;
//    $zbsh2=$checked;
//    //转版审核判定
//}else{
//	 $zbsh=$nochecked;
//    $zbsh1=$nochecked;
//    $zbsh=$nochecked;
//    //转版审核判定
//}

$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );
//计算审核时间


$shtime =  timediff($tb_date,$te_date);

if( $shtime['hour']>=8 )
{
    $shtimes = $shtime['day']+1;
}else{
    $shtimes = $shtime['day']+0.5;
}

/**专业支持人员**/
$zhichi = '';

foreach ($arr_audit as $value) {
	$arr_code = array();$code_where = '';
	/////////////////////////////=========================//

	if(getgp('banben'=='1'))//旧版本专业代码
	{
		if(!empty($value['audit_code_2017']))
		{
			$codeList  = array_filter(explode('；', $value['audit_code_2017']));
			$codeims   = '';

			foreach($codeList as $code)
			{

				if(!empty($code))$codeims .= $db->get_var("select dalei from sp_settings_audit_code where id=".$code).'；';
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

				if(!empty($code))$codeims .= $db->get_var("select dalei from sp_settings_audit_code where id=".$code).'；';
			}
			$value['audit_code'] = $codeims;
		}
	}
	/////////////////////////////=========================//
	$arr_audit_code = explode('；',$value['audit_code']);

	foreach ($arr_audit_code as $val) {
		// $code = explode('.',$val);
		$arr_code[] = $val;
	}
         $arr_code=array_unique($arr_code);
	if( !empty($arr_code) ){
		foreach ($arr_code as $val) {
			if($val)  {
				 $code_where .= "dalei='".$val."'  or ";
			}             

		}
		$code_where = substr($code_where,0,strlen($code_where)-4);
	}else{
		$code_where = '';
	}
	switch ($value['audit_ver']) {
		case 'A010101':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A010102':
		case 'A010103':
			$str_ios   = 'Q:';
			$iso_where = 'A01';
			break;
		case 'A020101':
			$str_ios   = 'E:';
			$iso_where = 'A02';
			break;
		case 'A020102':
			$str_ios   = 'E:';
			$iso_where = 'A02';
			break;
		case 'A030102':
			$str_ios   = 'S:';
			$iso_where = 'A03';
			break;

		default:
			break;
	}

    $zhichi_all="";
	if($code_where) {
        $sql = "select id from sp_settings_audit_code where iso='" . $iso_where . "' and " . $code_where . " and deleted=0";
        $setting_code_arr = $db->getAll($sql);
        if ($setting_code_arr) {
            $setting_code_id = "(";
            foreach ($setting_code_arr as $val) {
                $setting_code_id .= "'" . $val['id'] . "',";
            }
            $setting_code_id = rtrim($setting_code_id, ",") . ")";

            $sql = "select id,uid from sp_hr_audit_code where audit_code in " . $setting_code_id . " and deleted=0";
            $sql_r = $db->getAll($sql);

//            $zhichi_all[] ="select id,uid from sp_hr_audit_code where audit_code in ".$setting_code_id." and deleted=0";
            if (!empty($sql_r)) {
                foreach ($sql_r as $va) {
                    $rs = $db->get_row("select name from sp_hr where id=" . $va['uid'] . " and deleted=0");
//                    $zhichi_all[] = $value['audit_code'];
                    $zhichi_all[] = $rs['name'];
                    unset($rs);

                }
            }
        }
    }else{
            $zhichi_all[] ="暂无专业代码";
        }




    $zhichi_all = array_unique($zhichi_all);

    //随机引出6人
    if(count($zhichi_all)>"5"){
        $aKeys = array_rand($zhichi_all,5);
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
    unset($zhichi_all);
    $zhichi .= $str_ios." ";


}
/**专业支持人员**/
/**计算审核时间函数**/
 function timediff( $begin_time, $end_time )
{
    $begin_time  = is_numeric($begin_time)?$begin_time:strtotime($begin_time);
    $end_time    = is_numeric($end_time)?$end_time:strtotime($end_time);
    if ( $begin_time < $end_time )
    {
      $starttime = $begin_time;
      $endtime   = $end_time;
    } else {
      $starttime = $end_time;
      $endtime   = $begin_time;
    }
    $timediff    = $endtime - $starttime;
    $days        = intval( $timediff / 86400 );
    $remain      = $timediff % 86400;
    $hours       = intval( $remain / 3600 );
    $remain      = $remain % 3600;
    $mins        = intval( $remain / 60 );
    $secs        = $remain % 60;
    $res         = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
return $res;
}

/**附件信息**/
$sql = "select es_name,es_addr,es_scope,es_type from `sp_enterprises_site` where `deleted`=0 and eid=".$eid.' and deleted=0';
$r_fjxi = $db->getAll($sql);

$fujianxinxiStr = '<w:tr w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidTr="00E32D31">
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="915" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidRDefault="00E32D31" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{id}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="1733" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidRDefault="00E32D31" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{es_name}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="2661" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
						</w:tcPr>
						<w:p w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidRDefault="00E32D31" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
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
							<w:tcW w:w="2721" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidRDefault="00E32D31" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{es_addr}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="2727" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00E32D31" w:rsidRPr="00E566C1" w:rsidRDefault="00E32D31" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{es_scope}</w:t>
							</w:r>
						</w:p>
					</w:tc>
				</w:tr>';

$fujianxinxiString='';
foreach ($r_fjxi as $value) {
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
/**附件信息**/

/**审核范围**/
$shenhefanweiStr = '<w:tr w:rsidR="00E566C1" w:rsidRPr="00E566C1" w:rsidTr="00E566C1">
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="1320" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00080213" w:rsidRPr="00E566C1" w:rsidRDefault="00080213" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{iso}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="3000" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00080213" w:rsidRPr="00E566C1" w:rsidRDefault="00080213" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{ep_name}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="3400" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00080213" w:rsidRPr="00E566C1" w:rsidRDefault="00080213" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{bg_addr}</w:t>
							</w:r>
						</w:p>
					</w:tc>
					<w:tc>
						<w:tcPr>
							<w:tcW w:w="6900" w:type="dxa"/>
							<w:tcBorders>
								<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
								<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
							</w:tcBorders>
							<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
							<w:hideMark/>
						</w:tcPr>
						<w:p w:rsidR="00080213" w:rsidRPr="00E566C1" w:rsidRDefault="00080213" w:rsidP="00E566C1">
							<w:pPr>
								<w:spacing w:line="0" w:lineRule="atLeast"/>
								<w:jc w:val="center"/>
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体"/>
								</w:rPr>
							</w:pPr>
							<w:r w:rsidRPr="00E566C1">
								<w:rPr>
									<w:rFonts w:ascii="宋体" w:hAnsi="宋体" w:hint="eastAsia"/>
								</w:rPr>
								<w:t>{scope}</w:t>
							</w:r>
						</w:p>
					</w:tc>
				</w:tr>';
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

/**审核组成员**/
$shenhezuStr = '<w:tr w:rsidR="007C6E0F" w:rsidRPr="0000407C" w:rsidTr="003C45A6">
							<w:trPr>
								<w:trHeight w:val="425"/>
								<w:jc w:val="center"/>
							</w:trPr>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="565" w:type="dxa"/>
									<w:vMerge/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="0000407C" w:rsidRDefault="007C6E0F" w:rsidP="007174C1">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:rPr>
											<w:szCs w:val="21"/>
										</w:rPr>
									</w:pPr>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="894" w:type="dxa"/>
									<w:gridSpan w:val="3"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_name}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1279" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_role}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="993" w:type="dxa"/>
									<w:gridSpan w:val="2"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_qua_type}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="2411" w:type="dxa"/>
									<w:gridSpan w:val="2"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="00A01C6D" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_qua_no}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1156" w:type="dxa"/>
									<w:gridSpan w:val="2"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_audit_code}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="835" w:type="dxa"/>
									<w:gridSpan w:val="2"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:ind w:firstLineChars="50" w:firstLine="120"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_sex}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1770" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t>{shenhezu_tel}</w:t>
									</w:r>
								</w:p>
							</w:tc>
							<w:tc>
								<w:tcPr>
									<w:tcW w:w="1080" w:type="dxa"/>
									<w:shd w:val="clear" w:color="auto" w:fill="auto"/>
									<w:vAlign w:val="center"/>
								</w:tcPr>
								<w:p w:rsidR="007C6E0F" w:rsidRPr="00FB31E8" w:rsidRDefault="00FB31E8" w:rsidP="00325845">
									<w:pPr>
										<w:snapToGrid w:val="0"/>
										<w:jc w:val="center"/>
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
									</w:pPr>
									<w:r w:rsidRPr="00FB31E8">
										<w:rPr>
											<w:rFonts w:ascii="楷体_GB2312" w:eastAsia="楷体_GB2312" w:hAnsi="宋体" w:hint="eastAsia"/>
											<w:sz w:val="24"/>
											<w:szCs w:val="24"/>
										</w:rPr>
										<w:t></w:t>
									</w:r>
								</w:p>
							</w:tc>
						</w:tr>';
$shenhezuString = '';
$shenhezu = array();
$sql = "SELECT 
stat.name,hr.sex,stat.role,stat.uid,stat.qua_type,stat.taskBeginDate,stat.audit_code,stat.audit_code_2017,stat.iso,hr.tel,hr.audit_job,hrq.qua_no,hr.unit,hr.technical 
from sp_task_audit_team stat 
left join sp_hr hr on stat.uid=hr.id 
left join `sp_hr_qualification` hrq ON hr.id=hrq.uid and stat.iso=hrq.iso and stat.qua_type=hrq.qua_type
where stat.tid=".$tid." and stat.qua_type in('01','02','03','04') and stat.deleted='0'";
$shenhezu = $db->getALl($sql);
foreach ($shenhezu as $key => $value) {
	if (!empty($value['technical'])) {
		switch ($value['technical']) {
		case '00':
			$technical = '助理工程师';
			break;
		case '01':
			$technical = '工程师';
			break;
		case '02':
			$technical = '高级工程师';
			break;
		default:
			break;
		}
	}else{
		$technical = '';
	}
	switch ($value['qua_type']) 
	{
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
				$value['qua_no'] = $value['unit']."/".$technical;
				break;
			default:
				$qua_type = '其他';
				break;
		}
	$str_tmp = str_replace( '{shenhezu_name}',       $value['name'], $shenhezuStr );
	$str_tmp = str_replace( '{shenhezu_qua_no}',     $value['qua_no'], $str_tmp );
	$str_tmp = str_replace( '{shenhezu_tel}',        $value['tel'], $str_tmp );
	if(getgp('banben')=='1')//旧版本专业代码
	{
		$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code_2017'], $str_tmp );
	}else{//新版本专业代码
		$str_tmp = str_replace( '{shenhezu_audit_code}', $value['audit_code'], $str_tmp );
	}
	$str_tmp = str_replace( '{shenhezu_qua_type}',   $qua_type, $str_tmp );

	if($value['role']=='01')$zuzhang=$value['name'];
	$str_tmp = str_replace( '{shenhezu_role}',     ($value['role']=='01')?'组长':'组员', $str_tmp );
	$str_tmp = str_replace( '{shenhezu_sex}',      ($value['sex']=='1')?'男':'女', $str_tmp );
	// $str_tmp = str_replace( '{shenmezu_audit_job}', ($value['audit_job']==1)?'专职':(($value['audit_job']==0)?'兼职':'其他'), $str_tmp );
	// $str_tmp = str_replace( '{shenhezu_taskBeginDate}', substr($value['taskBeginDate'],0,10).' 8:30至17:30', $str_tmp );
	// $str_tmp = str_replace( '{shenhezu_iso}', ($value['iso']=='A01')?'QMS':(($value['iso']=='A02')?'EMS':(($value['iso']=='A03')?'OHSMS':'其他')), $str_tmp );
	$shenhezuString .= $str_tmp;
}
/**审核组信息**/

//
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);
//生产地址
$prod_addr   = $db->get_row("select * from sp_enterprises where eid='".$t_info['eid']."' and deleted=0");
//审核信息
$arr_project = $db->getAll('select audit_ver,audit_type from `sp_project` where deleted=0 and `tid` = '.$tid);

$filename = '审表007 管理体系审核计划('.$ep_name.').doc';
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
$output = str_replace( '{zuzhang}', $zuzhang, $output );
$output = str_replace( '{shrq}', $tb_date.'至'.$te_date, $output );
$output = str_replace( '{shtimes}', $shtimes, $output );
//echo $ep_name;exit;
$output = str_replace('{ct_code}', $ct_code, $output);


$output = str_replace( '{prod_addr}', $prod_addr['prod_addr'] , $output );
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
$output = str_replace('{ep_addr}', $ep_addr, $output);
$output = str_replace('{bg_addr}', $bg_addr, $output);
$output = str_replace('{person}', $person, $output);
$output = str_replace('{person_tel}', $person_tel, $output);
$output = str_replace('{ep_fax}', $ep_fax, $output);
$output = str_replace('{scope}', $scope, $output);
$output = str_replace('{cta_addrcode}', $cta_addrcode, $output);
$output = str_replace('{time}', $time, $output);
$output = str_replace('{nian}', substr($t_info['tb_date'],5,2), $output);
$output = str_replace('{yue}',  substr($t_info['tb_date'],8,2), $output);
$tb_time = substr($t_info['tb_date'],0,10);
if (date('Y.m.d')>=$tb_time) {
	$tb_time = date_format(date_sub(date_create($tb_time),date_interval_create_from_date_string("1 day")),"Y-m-d");
	$output  = str_replace('{date_rq}', $tb_time, $output);
}else{
	$output  = str_replace('{date_rq}', date('Y.m.d'), $output);
}
// $output = str_replace('{date_rq}', date('Y.m.d'), $output);
/**checked替换**/
//var_dump($arr_project);exit;
foreach ($arr_project as $value) {
	switch ($value['audit_ver']) {
		case 'A010101'://Q08
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{ck101}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck201}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );

					break;
				case '1003'://二阶段
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck202}', $checked , $output );
					$output = str_replace( '{ck901}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
				    $output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck901}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
				    $output = str_replace( '{ck200}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck204}', $checked , $output );
					$output = str_replace( '{ck2040}', '一' , $output );
					break;
				case '1005';//监督2
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck901}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck204}', $checked , $output );
					$output = str_replace( '{ck2040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck901}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck203}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck901}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck205}', $checked , $output );
					break;
			}
			break;
		case 'A010102':
		case 'A010103'://Q15
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{ck101}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck201}', $checked , $output );
					 $output = str_replace( '{ck902}', $checked , $output );//解注释
					$output = str_replace( '{ck90201}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck202}', $checked , $output );
					$output = str_replace( '{ck902}', $checked , $output );
					$output = str_replace( '{ck90201}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck902}', $checked , $output );
					$output = str_replace( '{ck90201}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck204}', $checked , $output );
					$output = str_replace( '{ck2040}', '一' , $output );
					break;
				case '1005';//监督2
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck902}', $checked , $output );
					$output = str_replace( '{ck90201}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
				    $output = str_replace( '{ck200}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck204}', $checked , $output );
					$output = str_replace( '{ck2040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck902}', $checked , $output );
					$output = str_replace( '{ck90201}', $checked , $output );
					$output = str_replace( '{ck203}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck102}', $checked , $output );
					$output = str_replace( '{ck2001}', $checked , $output );
					$output = str_replace( '{ck200}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck902}', $checked , $output );
					$output = str_replace( '{ck90201}', $checked , $output );
					$output = str_replace( '{ck205}', $checked , $output );
					break;
			}
			break;

		case 'A020101'://E
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck301}', $checked , $output );
					$output = str_replace( '{ck90101}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck302}', $checked , $output );
					$output = str_replace( '{ck911}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
					$output = str_replace( '{ck3001}', $checked , $output );
				    $output = str_replace( '{ck300}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck304}', $checked , $output );
					$output = str_replace( '{ck911}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					$output = str_replace( '{ck3040}', '一' , $output );
					break;
				case '1005';//监督2 
					$output = str_replace( '{ck911}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					$output = str_replace( '{ck3001}', $checked , $output );
				    $output = str_replace( '{ck300}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck304}', $checked , $output );
					$output = str_replace( '{ck3040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck911}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck303}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck911}', $checked , $output );
					$output = str_replace( '{ck91101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck305}', $checked , $output );
					break;
			}
			break;

		case 'A020102'://E15
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck301}', $checked , $output );
					 $output = str_replace( '{ck912}', $checked , $output );//解注释
					$output = str_replace( '{ck91201}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck302}', $checked , $output );
					$output = str_replace( '{ck912}', $checked , $output );
					$output = str_replace( '{ck91201}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
					$output = str_replace( '{ck3001}', $checked , $output );
				    $output = str_replace( '{ck300}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck304}', $checked , $output );
					$output = str_replace( '{ck912}', $checked , $output );
					$output = str_replace( '{ck91201}', $checked , $output );
					$output = str_replace( '{ck3040}', '一' , $output );
					break;
				case '1005';//监督2
					$output = str_replace( '{ck912}', $checked , $output );
					$output = str_replace( '{ck91201}', $checked , $output );
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck304}', $checked , $output );
					$output = str_replace( '{ck3040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck912}', $checked , $output );
					$output = str_replace( '{ck91201}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck303}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck3001}', $checked , $output );
					$output = str_replace( '{ck300}', $checked , $output );
					$output = str_replace( '{ck912}', $checked , $output );
					$output = str_replace( '{ck91201}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck305}', $checked , $output );
					break;
			}
			break;

		case 'A030102'://S
			switch ($value['audit_type']) {
				case '1002'://一阶段
				    $output = str_replace( '{ck400}', $checked , $output );
					$output = str_replace( '{ck401}', $checked , $output );
					$output = str_replace( '{ck99101}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{ck4001}', $checked , $output );
					$output = str_replace( '{ck400}', $checked , $output );
					$output = str_replace( '{ck402}', $checked , $output );
					$output = str_replace( '{ck991}', $checked , $output );
					$output = str_replace( '{ck99101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
				    $output = str_replace( '{ck991}', $checked , $output );
				    $output = str_replace( '{ck99101}', $checked , $output );
					$output = str_replace( '{ck4001}', $checked , $output );
					$output = str_replace( '{ck400}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck404}', $checked , $output );
					$output = str_replace( '{ck4040}', '一' , $output );
					break;
				case '1005';//监督2
					$output = str_replace( '{ck991}', $checked , $output );
					$output = str_replace( '{ck99101}', $checked , $output );
					$output = str_replace( '{ck4001}', $checked , $output );
					$output = str_replace( '{ck400}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck404}', $checked , $output );
					$output = str_replace( '{ck4040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck4001}', $checked , $output );
					$output = str_replace( '{ck400}', $checked , $output );
					$output = str_replace( '{ck991}', $checked , $output );
					$output = str_replace( '{ck99101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck403}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck4001}', $checked , $output );
					$output = str_replace( '{ck400}', $checked , $output );
					$output = str_replace( '{ck991}', $checked , $output );
					$output = str_replace( '{ck99101}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck405}', $checked , $output );
					break;
			}
			break;
		default:
			$output = str_replace( '{ck6001}', $checked , $output );
			$output = str_replace( '{ck600}', $checked , $output );
			switch ($value['audit_type']) {
				case '1002'://一阶段
					$output = str_replace( '{ck601}', $checked , $output );
					break;
				case '1003'://二阶段
					$output = str_replace( '{ck602}', $checked , $output );
					$output = str_replace( '{ck103}', $checked , $output );
					break;
				case '1004';//监督1
					$output = str_replace( '{ck400}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck604}', $checked , $output );
					$output = str_replace( '{ck6040}', '一' , $output );
					break;
				case '1005';//监督2
					$output = str_replace( '{ck400}', $checked , $output );
				    $output = str_replace( '{ck104}', $checked , $output );
					$output = str_replace( '{ck604}', $checked , $output );
					$output = str_replace( '{ck6040}', '二' , $output );
					break;
				case '1007'://再认证
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck603}', $checked , $output );
					break;
				default://其他
					$output = str_replace( '{ck103}', $checked , $output );
					$output = str_replace( '{ck605}', $checked , $output );
					break;
			}
			break;
	}
}
$output = str_replace( '{ck105}', $zbsh , $output );
$output = str_replace( '{ck1051}', $qchecked , $output );
$output = str_replace( '{ck1052}', $echecked, $output );
$output = str_replace( '{ck2040}', '  ' , $output );
$output = str_replace( '{ck3040}', '  ' , $output );
$output = str_replace( '{ck4040}', '  ' , $output );
$output = str_replace( '{ck5040}', '  ' , $output );
$output = str_replace( '{ck6040}', '  ' , $output );

$output = preg_replace("/\{ck.+?\}/", $nochecked, $output);
/**checked替换**/

$output = preg_replace("/\{.+?\}/", "", $output);
//echo $scope;exit;

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