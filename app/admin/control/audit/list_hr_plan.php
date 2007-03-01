<?php

//人员行程

$fields        = $join = $where = '';
$ctfrom_select = f_ctfrom_select();
$name          = getgp( 'name' );
$ctfrom        = getgp( 'ctfrom' );
$export        = getgp("export");
$theYear       = getgp( 'year' );
$theMonth      = getgp( 'month' );
$hezuofang     = getgp("b");
$usertype      = $_SESSION['extraInfo']['userType'];
$ctfrom_sys    = $_SESSION['extraInfo']['ctfrom'];
if ($usertype==$hezuofang) 
{
	$ctfrom    = $ctfrom_sys;
	$wheres    .= 'and a.ctfrom='.$ctfrom;
}else{
	$wheres    .= '';
}
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
	$zj = mysql2date( 'w', "{$usedDate}-{$t_day}" );
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
// $ctfrom = $_SESSION['extraInfo']['ctfrom'];
// if(  $ctfrom!='01000000' ){
//     $where .= " AND ctfrom = '$ctfrom'";
//     $ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );
// 	$page_str .= '&ctfrom='.$ctfrom;
// }

// if (!$ctfrom) {
// 	$wheres .= 'and a.ctfrom='.$ctfrom;
// }
if ($ctfrom) {
	$where .= 'and ctfrom='.$ctfrom;
}
if($name){
	$where = " and name like '%$name%'";
}
$where .= " and is_hire = '1' and deleted=0";
//人员
$fields .= "id,name,sex,is_hire";
//$total = $db->get_var("SELECT COUNT(*) FROM sp_hr WHERE 1 $where and job_type LIKE '%1004%'");
//$pages = numfpage( $total, 20, "?c=$c&a=$a".$page_str );

$hrs = array();
$query = $db->query( "SELECT $fields FROM sp_hr WHERE 1 $where  order by easycode,name asc  " );
while( $rt = $db->fetch_array( $query ) ){

	$hrs[$rt['id']] = $rt;
}

$tempList  = array();
//判断时间限制 隔月限制 整月显示
if(!empty($theYear)&&!empty($theMonth))
{
	//     2018-10-17 08:00:00
	//同一年 2019-03-28 17:00:00
	$itemList   = $db->getAll("select * from sp_task_audit_team a where   ( (YEAR(a.taskEndDate) = '$theYear' AND MONTH(a.taskEndDate) > '$theMonth') or ( YEAR(a.taskBeginDate) = '$theYear' AND MONTH(a.taskBeginDate) < '$theMonth')) ");
	$itemids    = array();
	foreach($itemList as $item)
	{
		//同一年项目	
		if($theYear==substr($item['taskEndDate'],0,4) && $theYear==substr($item['taskBeginDate'],0,4) && intval(substr($item['taskBeginDate'],5,2) < $theMonth && $theMonth< intval(substr($item['taskEndDate'],5,2))))
		{

			$itemids[] = $item['id'];
		}elseif(substr($item['taskEndDate'],0,4) != substr($item['taskBeginDate'],0,4)){//不同年项目
           	if($theYear==substr($item['taskBeginDate'],0,4) && intval(substr($item['taskBeginDate'],5,2)) < $theMonth && $theMonth <= 12)
           	{
           		$itemids[] = $item['id'];
           	}else if($theYear==substr($item['taskEndDate'],0,4) && $theMonth< intval(substr($item['taskEndDate'],5,2)) && $theMonth>=1)
           	{
           		$itemids[] = $item['id'];
           	}

		}
	}
	if(!empty($itemids))$wheres .= " or a.id in(".implode(',', array_filter(array_unique($itemids))).")";
}
if( $hrs ){
	$uids = array_keys( $hrs );
	/*
	 //注册资格
	 $sql = "SELECT uid,iso,qua_type FROM sp_hr_qualification WHERE status = '1' AND uid IN (".implode(',',$uids).")" ;
	 $query = $db->query( $sql);
	 while( $rt = $db->fetch_array( $query ) ){
		isset( $hrs[$rt['uid']]['qua_types'] ) or $hrs[$rt['uid']]['qua_types'] = array();
		$hrs[$rt['uid']]['qua_types'][] = f_iso( $rt['iso'] ) . "： " . f_qua_type( $rt['qua_type'] );
		}
		*/
	
	$sql   = "SELECT a.tid,a.note,a.data_for,a.iso,a.uid,a.taskBeginDate,a.taskEndDate,a.role,e.ep_name,e.ep_shortname FROM sp_task_audit_team a LEFT JOIN sp_enterprises e ON e.eid = a.eid WHERE 1 AND a.uid IN (".implode(',',$uids).") AND ( ( YEAR(a.taskBeginDate) = '$theYear' AND MONTH(a.taskBeginDate) = '$theMonth') OR  (YEAR(a.taskEndDate) = '$theYear' AND MONTH(a.taskEndDate) = '$theMonth') ) AND a.deleted = 0 ".$wheres." ";

	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) )
	{
		//判断相同时间相同项目
		
		if($rt['tid']==$taskid && $rt['tid']!=0 && $tasuid==$rt['uid'])continue;
		$_query=$db->query("SELECT iso FROM `sp_project` WHERE `tid` = '$rt[tid]' AND `deleted` = '0' order by iso");
		$iso="";
		while($_rt=$db->fetch_array($_query))
		{
			$iso.=f_iso($_rt[iso])." ";
		}
		$ep_shortname=$rt[ep_shortname];
		
		switch( $rt['data_for'] ){
			case 2	: $title = "人员外出：$rt[note]\n"; break;
			// case 2	: $title = "经销商考评：$rt[ep_name]\n"; break;
			// case 3	: $title = "二方审核：$rt[ep_name]\n"; break;
			case 5	: $title = "培训：$rt[note]\n"; break;
			// case 5	: $title = "TS业务：$rt[ep_name]\n"; break;
			case 6	: $title = "请假：$rt[note]\n"; break;
			case 0	:
			default	: $title = "审核企业：$rt[ep_name]\n"; $note="$rt[ep_shortname]\n";break;
		}
		if($rt['data_for']=='0')
		$title .= "组内身份：".read_cache("audit_role",$rt[role])."\n"."审核体系：".$iso."\n";
		$title .= "开始时间：$rt[taskBeginDate]\n";
		$title .= "结束时间：$rt[taskEndDate]";
		
		isset( $hrs[$rt['uid']]['tasks'] ) or $hrs[$rt['uid']]['tasks'] = array();
		// isset( $hrs[$rt['uid']]['days'] ) or $hrs[$rt['uid']]['days'] = array();
		list( $taskBeginDate, $taskBeginTime ) = explode( ' ', $rt['taskBeginDate'] );
		list( $taskEndDate, $taskEndTime ) = explode( ' ', $rt['taskEndDate'] );
		//开始/结束 日期
		list( $beginYear, $beginMonth, $beginDay ) = explode( '-', $taskBeginDate );

		list( $endYear, $endMonth, $endDay ) = explode( '-', $taskEndDate );
		//判断时间限制 隔月限制 整月显示
		$end     = intval(substr($rt['taskEndDate'],5,2));
		$beg     = intval(substr($rt['taskBeginDate'],5,2));
		$jieduan = $end-$beg;
	
			if($theYear==substr($rt['taskEndDate'],0,4) && $theYear==substr($rt['taskBeginDate'],0,4) && intval(substr($rt['taskBeginDate'],5,2)) < intval($theMonth) && intval($theMonth)< intval(substr($rt['taskEndDate'],5,2)) && $jieduan>1)
			{
	
					$rt['taskBeginDate'] = $theYear.'-'.$theMonth.'-01 08:00:00';
					$rt['taskEndDate']   = $theYear.'-'.$theMonth.'-31 17:30:00';
			}elseif(substr($rt['taskEndDate'],0,4) != substr($rt['taskBeginDate'],0,4)){
	           	if($theYear==substr($rt['taskBeginDate'],0,4) && intval(substr($rt['taskBeginDate'],5,2)) < intval($theMonth) && intval($theMonth) <= 12)
	           	{
	           		$rt['taskBeginDate'] = $theYear.'-'.$theMonth.'-01 08:00:00';
					$rt['taskEndDate']   = $theYear.'-'.$theMonth.'-31 17:30:00';
	           	}else if($theYear==substr($rt['taskEndDate'],0,4) && intval($theMonth)< intval(substr($rt['taskEndDate'],5,2)) && intval($theMonth)>=1)
	           	{
	           		$rt['taskBeginDate'] = $theYear.'-'.$theMonth.'-01 08:00:00';
					$rt['taskEndDate']   = $theYear.'-'.$theMonth.'-31 17:30:00';
	           	}
			}
		
		
		
		//结束

		//@wangp 派人类型 2013-09-18 11:33
		switch( $rt['data_for'] ){ 
			case 1	: $pt = 'emKP'; break;
			case 2	: $pt = 'bcKP'; break;
			case 3	: $pt = 'bcSH'; break;
			case 4	: $pt = 'emTS'; break;
			case 5  : $pt = 'bcTS'; break;
			case 6	: $pt = 'bcQJ'; break;
			case 0	:
			default : $pt = 'bcISO'; break;
		}

		
		if( substr($rt['taskBeginDate'],0,7)==$usedDate||substr($rt['taskEndDate'],0,7)==$usedDate )
		{
			
			$dates = getDateFromRange($rt['taskBeginDate'],$rt['taskEndDate']);
			foreach ($dates as $date)
			{
				
				
				if( substr($date,0,7)!=$usedDate )continue;
				
				if( substr($rt['taskBeginDate'],0,10)==substr($rt['taskEndDate'],0,10) )//一天内的项目
				{

					if(intval(substr($rt['taskBeginDate'],11,2))<11)  //开始时间小于11点  -- 上午
					{
						if(intval(substr($rt['taskEndDate'],11,2))<15)//结束时间小于15点  -- 上午
						{
							
							//判断是否为一天两个审核项目
							if(!empty($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'])&&$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']!='am')
							{	
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']    = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']);
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']   = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']);
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'][]  = 'am';
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link'][] = "<a class=\"$class $pt\" title=\"$title,$bantitle\" href=\"javascript:;\"></a>";
							}else{
								$class    = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'am';
							}
						}else{
							//全天
							$class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'all';
						}
					}else
					{
						//下午
						//判断是否为一天两个审核项目
						if(!empty($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'])&&$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']!='pm')
						{
							$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']    = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']);
							$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']   = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']);
							$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'][]  = 'pm';
							$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link'][] = "<a class=\"$class $pt\" title=\"$title,$bantitle\" href=\"javascript:;\"></a>";
						}else{
							$class    = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'pm';
						}
					}
				}else //多天内的项目
				{
					if( $date==substr($rt['taskBeginDate'],0,10) )
					{
						if( intval(substr($rt['taskBeginDate'],11,2))>12 )
						{
							//下午
							//判断是否为一天两个审核项目
							if(!empty($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'])&&$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']!='pm')
							{
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']    = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']);
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']   = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']);
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'][]  = 'pm';
								$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link'][] = "<a class=\"$class $pt\" title=\"$title,$bantitle\" href=\"javascript:;\"></a>";
							}else{
								$class    = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'pm';
							}
						}else{
							//全天
							$class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'all';
						}
					}else if($date==substr($rt['taskEndDate'],0,10))
					{
						if( intval(substr($rt['taskEndDate'],11,2))>15 )
						{
							//全天
							$class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'all';
						}else{
							//上午
//                            $class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'all';
							//判断是否为一天两个审核项目
							if(!empty($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'])&&$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']!='am')
                            {
                                $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']    = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day']);
                                $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']   = array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']);
                                $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'][]  = 'am';
                                $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link'][] = "<a class=\"$class $pt\" title=\"$title,$bantitle\" href=\"javascript:;\"></a>";
                            }else{
                                $class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'am';
                            }
						}
					}else
					{
						$class = $hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['day'] = 'all';
					}
				}
				if( !is_array($hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']) )
				{
					$hrs[$rt['uid']]['task'][intval(substr($date,8,2))]['link']    = "<a class=\"$class $pt\" title=\"$title,$bantitle\" href=\"javascript:;\"></a>";
				}
			}

		}

		$hrs[$rt['uid']]['tasks'][]    = $rt;
		$taskid  = $rt['tid'];
		$tasuid  = $rt['uid'];
		
		
	}
}

if(!$export){
	tpl( 'audit/list_hr_plan' );
} else {
		ob_start();
		tpl( 'xls/list_hr_plan' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '人员行程', $data );
}

function get_day_class($bh,$eh){
	$bh = strtotime($bh);
	$eh = strtotime($eh);
	if( !$bh || !$eh ) return false;
	if( $bh < strtotime('11:35') && $eh > strtotime('15:35'))
	{
		$class = 'all';
	} elseif( $bh > strtotime('11:35') ){
		$class = 'pm';
	} else {
		$class = 'am';
	}
	return $class;
}
	//计算人日
	function timediff( $begin_time, $end_time )
	{
		$day = 0;
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
		if( $res['hour']>=8 )
		{
			$day = $day+$res['day']+1;
		}else{
			$day = $day+$res['day']+0.5;
		}
		return $day;
	}

	//获取日期内所有天数
	function getDateFromRange($startdate, $enddate)
	{
		// 保存每天日期
	    $date = array();
	    $stimestamp =strtotime(date('Y-m-d 00:00:00', strtotime($startdate)));
	    $etimestamp = strtotime(date('Y-m-d 23:59:59', strtotime($enddate)));
	    // 计算日期段内有多少天
	    $days = ($etimestamp-$stimestamp)/86400;
	    for($i=0; $i<$days; $i++)
	    {
	        $date[] = date('Y-m-d', $stimestamp+(86400*$i));
	    }
	    return $date;
	}
?>