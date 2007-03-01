<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	require_once ROOT . '/data/cache/audit_ver.cache.php';//接口文件
	require_once ROOT . '/data/cache/iso.cache.php';//接口文件
	feiyong::$month = getgp('month');
	
	$uid            = getgp('uid');
	$type           = getgp('type');
	if( empty($uid) )
	{
		$results  = feiyong::getshenheyuan();
		$tpl      = '';
	}else
	{
		feiyong::$uid = $uid;
		switch ($type)
		{
			case 'list':
				$results  = feiyong::shenheyuanlist();	
				$tpl      = 'shenheyuanlist';
				break;
			case 'unlist':
			default:
				$results  = feiyong::shenheyuanlist();
				$tpl      = 'shenheyuanlist';
				break;
		}
	}

	$results  = $results['results'];
	$month    = !empty(feiyong::$month)?feiyong::$month:date('Y-m');
//	
	foreach ($results as $key => $value)
	{
		$tixi   =explode(',',$value['tixi']);
		foreach ($tixi as  $val) 
		{
			@$results[$key]['tixis'] .= $audit_ver_array[$val]['msg'].",";
		}
		if($value['audit_type']=='1002' && $value['is_site']=='1')
		{
			$results[$key]['issite']  = '是';
		}else{
			@$results[$key]['issite']  = '';
		}
		@$results[$key]['tixis']  = substr($results[$key]['tixis'],0,-1);
	}

	//审核日期相减
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
	tpl($tpl);