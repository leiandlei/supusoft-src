<?php

//人员行程

$fields        = $join = $where = '';
$ctfrom_select = f_ctfrom_select();
$name          = getgp('name');
$theYear       = getgp('year');
$theMonth      = getgp('month');
$userid        = $_SESSION['userinfo']['id'];
$username      = $_SESSION['userinfo']['name'];
//筛选自己的行程
$wheres    .= 'and a.uid='.$userid;
//当前使用的年/月
list( $year, $month ) = explode('-', mysql2date( 'Y-m', current_time( 'mysql' ) ) );

if( !$theYear )
$theYear = $year;
if( !$theMonth )
$theMonth = $month;
$usedDate = "{$theYear}-{$theMonth}";
//当前月份天数
$the_month_day = mysql2date( 't', $usedDate );
//周六、天设置
$out_dayzjs = array('日','一','二','三','四','五','六');
$month_zjs  = array();
for( $t_day = 1; $t_day <= $the_month_day; $t_day++ ){
	$zj     = mysql2date( 'w', "{$usedDate}-{$t_day}" );
	$month_zjs[$t_day] = $zj;
}

/*
 *	下拉框
 */

$year_select = $month_select = $province_select = $page_str = '';

for( $i = $theYear - 10; $i <= $theYear+2; $i++ ){
	$year_select .= "<option value=\"$i\"".($theYear == $i ? ' selected' : '' ).">$i</option>";
}

for( $i = 1; $i <= 12; $i++ ){
	$month_select .= "<option value=\"".sprintf("%02d", $i)."\"".($theMonth == $i ? ' selected' : '' ).">".sprintf("%02d", $i)."</option>";

}
$page_str .='&month='.$theMonth;
unset( $ey, $em );
//合同来源限制
$ctfrom = $_SESSION['extraInfo']['ctfrom'];
if(  $ctfrom!='01000000' ){
    $where  .= " AND ctfrom = '$ctfrom'";
    $ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
	$page_str .= '&ctfrom='.$ctfrom;
}
if($name){
	$where  = " and name like '%$name%'";
}
$where     .= " and is_hire = '1' ";
//人员
$fields    .= "id,name,sex,is_hire";

$hrs      = array();
$query    = $db->query( "SELECT $fields FROM sp_hr WHERE 1 $where and job_type LIKE '%1004%' order by easycode,name asc  " );
while($rt = $db->fetch_array( $query ))
{
	$hrs[$rt['id']] = $rt;
}

if($hrs)
{
	$uids  = array_keys( $hrs );
	$sql   = "SELECT a.id,a.note,a.data_for,a.tid,a.iso,a.uid,a.taskBeginDate,a.taskEndDate,a.role,e.ep_name,e.ep_shortname FROM sp_task_audit_team a LEFT JOIN sp_enterprises e ON e.eid = a.eid WHERE 1  AND ( ( YEAR(a.taskBeginDate) = '$theYear' AND MONTH(a.taskBeginDate) = '$theMonth') OR  (YEAR(a.taskEndDate) = '$theYear' AND MONTH(a.taskEndDate) = '$theMonth') ) AND a.deleted = 0 ".$wheres;
	$results    = $db->getALL($sql);
	if (empty($results)) {
		$hrs[$userid]['tasks']['0'] =array(
			'id' => '',
			'note' => '',
			'data_for' => '',
			'tid' => '',
			'iso' => '',
			'uid' => $userid,
			'taskBeginDate' => '',
			'taskEndDate' => '',
			'role' => '',
			'ep_name' => '',
			'ep_shortname' => '',
			);
	}
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) )
	{
		$_query=$db->query("SELECT iso FROM `sp_project` WHERE `tid` = '$rt[tid]' AND `deleted` = '0' order by iso");
		$iso   ="";
		while($_rt=$db->fetch_array($_query))
		{
			$iso.=f_iso($_rt[iso])." ";
		}
		$ep_shortname=$rt[ep_shortname];
		switch( $rt['data_for'] ){
			case 2	: $title = "人员外出：$rt[note]\n"; break;
			case 5	: $title = "培训讲课：$rt[note]\n"; break;
			case 6	: $title = "请假：$rt[note]\n"; break;
			case 0	:
			default	: $title = "审核企业：$rt[ep_name]\n"; $note="$rt[ep_shortname]\n";break;
		}
		if($rt['data_for']=='0')
		$title .= "组内身份：".read_cache("audit_role",$rt[role])."\n"."审核体系：".$iso."\n";
		$title .= "开始时间：$rt[taskBeginDate]\n";
		$title .= "结束时间：$rt[taskEndDate]";
		isset( $hrs[$rt['uid']]['tasks'] ) or $hrs[$rt['uid']]['tasks'] = array();
		isset( $hrs[$rt['uid']]['days'] ) or $hrs[$rt['uid']]['days'] = array();
		list( $taskBeginDate, $taskBeginTime ) = explode( ' ', $rt['taskBeginDate'] );
		list( $taskEndDate, $taskEndTime ) = explode( ' ', $rt['taskEndDate'] );
		//开始/结束 日期
		list( $beginYear, $beginMonth, $beginDay ) = explode( '-', $taskBeginDate );
		list( $endYear, $endMonth, $endDay ) = explode( '-', $taskEndDate );
		//@wangp 派人类型 2013-09-18 11:33
		switch( $rt['data_for'] )
		{ 
			case 1	: $pt = 'emKP'; break;
			case 2	: $pt = 'bcKP'; break;
			case 3	: $pt = 'bcSH'; break;
			case 4	: $pt = 'emTS'; break;
			case 5  : $pt = 'bcTS'; break;
			case 6	: $pt = 'bcQJ'; break;
			case 0	:
			default : $pt = 'bcISO'; break;
		}
		
		if( in_array( $theMonth, array( $beginMonth, $endMonth ) ) )
		{
			//开始日
			$day_start = 0;
			if( $theMonth == $beginMonth )
			{
				$day_start = $beginDay;
				if( $beginMonth == $endMonth )
				{
					$day_end = $endDay;
				} else {
					$day_end = $the_month_day;
				}
			} else {
				$day_start = 1;
				$day_end = $endDay;
			}
			//开始/结束 时间
			list( $beginHour, $beginMin ) = explode( ':', $taskBeginTime );
			list( $endHour, $endMin ) = explode( ':', $taskEndTime );
			$bh = intval( $beginHour );
			$eh = intval( $endHour );
			if( $day_start < $day_end )
			{
				for( $_day = (int)$day_start; $_day <= $day_end; $_day++ )
				{
					if( $_day == (int)$day_start )
					{
						$class = get_day_class( $bh, 17 );
					} elseif( $_day == (int)$day_end ){
						$class = get_day_class( 8, $eh );
					} else {
						$class = get_day_class();
					}
					$hrs[$rt['uid']]['days'][$_day][$class] = "<a style=\"display:none;\" id=\"tatid\">".$rt['id']."</a><a class=\"$class $pt\" title=\"$title\" href=\"javascript:;\"></a>";
				}
			} else {
				// echo "<pre />";
				// print_r($rt);exit;
				$class = get_day_class( $bh, $eh );
				$hrs[$rt['uid']]['days'][(int)$day_start][$class] = "<a style=\"display:none;\" id=\"tatid\">".$rt['id']."</a><a class=\"$class $pt\" title=\"$title\" href=\"javascript:;\"></a>";
			}
		}
		$hrs[$rt['uid']]['tasks'][] = $rt;
	}
}
	function get_day_class( $bh = 8, $eh = 17 )
	{
		$bh = intval( $bh );
		$eh = intval( $eh );
		if( !$bh || !$eh ) return false;
		if( $bh < 9 && $eh > 16 ){
			$class = 'all';
		} elseif( $bh > 12 ){
			$class = 'pm';
		} else {
			$class = 'am';
		}
		return $class;
	}
tpl();

?>