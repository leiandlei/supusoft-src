<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	require_once ROOT . '/data/cache/audit_type.cache.php';
	$type = getgp('type')?getgp('type'):'info';
	feiyong::$month  = getgp('month');
	feiyong::$eid    = getgp('eid');

	switch ($type) 
	{
		case 'feiyongedit':
			$results = feiyong::feiyonginfo();
			break;
		case 'feiyonginfo':
		default:
			$results = feiyong::unfeiyonginfo();
			break;
	}
	$detail         = $results['results'];
	$month          = feiyong::$month;
	$contSetting    = feiyong::$contSetting;
	if ($type=='feiyonginfo')
	{
		//未结算类型
	    $leixing = $tixi = $tasks = array();
		foreach ($detail['task'] as $task)
		{
			if( empty($leixing[$task['audit_type']]) )

			$leixing[$task['audit_type']] = $audit_type_array[$task['audit_type']]['name'];//类型转中文

			if( empty($tasks[$task['id']]) )

			$tasks[$task['id']] = $task;

			foreach ($task['team'] as $team)
			{
				foreach ($team['project'] as $project)
				{
					if( empty($tixi[$project['iso']]) )

					$tixi[$project['iso']] = $arr_audit_iso[$project['iso']];//体系转中文
				
				}
			}
		}
	} 
	
	if ($type=='feiyongedit') 
	{
			//已结算类型
	      $leixing = $tixi = $tasks = array();
  			foreach ($detail['cost'] as $key => $cost)
  			{
  				if( empty($tasks[$cost['audit_type']]) )
  				$tasks[$cost['audit_type']] = array('audit_type'=>$cost['audit_type'],'tb_date'=>$cost['taskBeginDate'],'te_date'=>$cost['taskEndDate'],'tk_num'=>$cost['tk_num']);

  				if( empty($leixing[$cost['audit_type']]) )
					$leixing[$cost['audit_type']] = $audit_type_array[$cost['audit_type']]['name'];
					$tixi   = array_merge($tixi,explode(',',$cost['tixi'])); 
					
					
				   
  			}
  			$tixis = array_unique($tixi);
  			foreach ($tixis as $val) 
					{	
						switch ($val) {
							 	case 'A010101':
							 		$detail['audit_ver1'].='QMS08;';
							 		break;
							    case 'A020101':
							 		$detail['audit_ver1'].='EMS;';
							 		break;
							 	case 'A030102':
							 		$detail['audit_ver1'].='OHSMS;';
							 		break;
							 	case 'C0299':
							 		$detail['audit_ver1'].='服务认证;';
							 		break;
							 	case 'A010103':
							 		$detail['audit_ver1'].='QMS15;';
							 		break;
							 	case 'A010202':
							 		$detail['audit_ver1'].='建工;';
							 		break;
							}
					}
	}
	
	//审核日期下的类型
	foreach ($tasks as $key => $task)
	{

		switch ($task['audit_type']) {
			case '1001':
				$tasks[$key]['task_type']='初审';
				break;
			case '1002':
				$tasks[$key]['task_type']='一阶段';
				break;
			case '1003':
				$tasks[$key]['task_type']='二阶段';
				break;
			case '1004':
				$tasks[$key]['task_type']='监一';
				break;
			case '1005':
				$tasks[$key]['task_type']='监二';
				break;
			case '1006':
				$tasks[$key]['task_type']='监三';
				break;
			case '1007':
				$tasks[$key]['task_type']='再认证';
				break;
			case '1008':
				$tasks[$key]['task_type']='专项审核';
				break;
			case '1009':
				$tasks[$key]['task_type']='特殊监督';
				break;
			case '1101':
				$tasks[$key]['task_type']='变更';
				break;
			case '99':
				$tasks[$key]['task_type']='其他';
				break;
		}
		//审核日期
		$tasks[$key]['dates'] = timediff($tasks[$key]['tb_date'],$tasks[$key]['te_date']);
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
	
	tpl($type);