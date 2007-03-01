<?php
//编辑计划任务

    $step    = getgp( 'step' );
	$cron_id = (int)getgp('cron_id');
	$cron    = load( 'cron' );


	if( $step ){
		$loop_type     = getgp( 'loop_type' );
		$day = $hour = $minute = 0;
		$curr_time     = current_time( 'mysql' ); //当前时间
		$curr_day      = (int)mysql2date( 'j', $curr_time ); //当前几号 没有0前导
		$curr_hour     = (int)mysql2date( 'G', $curr_time ); //当前小时 没有0前导
		$curr_minute   = (int)mysql2date( 'i', $curr_time ); //当前分钟 有0前导
		$curr_week_day = (int)mysql2date( 'w', $curr_time ); //当前周几 0为周天
		$next_time     = '';
		switch( $loop_type ){
			case 'month'	:
				$day      = getgp( 'month_day' );
				$hour     = getgp( 'month_hour' );
				$f_day    = sprintf("%02d",$day);
				$f_hour   = sprintf("%02d",$hour);
				if( $day >= $curr_day && $hour > $curr_hour ){
					$next_time = mysql2date( 'Y-m-', $curr_time ).$f_day.' '.$f_hour.":00:00";
				} else {
					$_date     = thedate_add( $curr_time, 1, 'month' );
					$next_time = mysql2date( 'Y-m-', $_date ).$f_day.' '.$f_hour.":00:00";
				}
				break;
			case 'week'	:
				$day  = getgp( 'week_day' );
				$hour = getgp( 'week_hour' );

				//计算当周相差天数
				$sub_day = 0;
				if( 0 != $day && $day > $curr_week_day ){
					$sub_day = $day - $curr_week_day;
				} else {
					$sub_day = 7 - $curr_week_day;
				}
				$f_hour = sprintf("%02d",$hour);
				if( ($day >= $curr_week_day or $day == 0) && $hour > $curr_hour ){
					$_date = thedate_add( $curr_time, $sub_day, 'day' );
					$next_time = mysql2date( 'Y-m-d ', $_date ).$f_hour.":00:00";
				} else {
					$sub_day += $day;
					$_date = thedate_add( $curr_time, $sub_day, 'day' );
					$next_time = mysql2date( 'Y-m-d ', $_date ).$f_hour.":00:00";
				}
				break;
			case 'day'	:
				$hour = getgp( 'day_hour' );
				$f_hour = sprintf("%02d",$hour);
				if( $hour > $curr_hour ){
					$next_time = mysql2date( 'Y-m-d ', $curr_time ).$f_hour.":00:00";
				} else {
					$_date = thedate_add( $curr_time, 1, 'day' );
					$next_time = mysql2date( 'Y-m-d ', $_date ).$f_hour.":00:00";
				}
				break;
			case 'hour'	:
				$minute = getgp( 'hour_minute' );
				$f_minute = sprintf("%02d",$minute);
				if( $minute > $curr_minute ){
					$next_time = mysql2date( 'Y-m-d H:', $curr_time ).$f_minute.":00";
				} else {
					$_date = thedate_add( $curr_time, 1, 'hour' );
					$next_time = mysql2date( 'Y-m-d ', $_date ).$f_minute.":00:00";
				}
				break;
			case 'now'	:
			default		:
				$now_type = getgp( 'now_type' );
				switch( $now_type ){
					case 'day'		:
						$day	= getgp( 'now_time' );
						break;
					case 'hour'		:
						$hour	= getgp( 'now_time' );
						break;
					case 'minute'	:
					default			:
						$minute = getgp( 'now_time' );
						break;
				}
				break;
		}
		$new_cron = array(
			'subject'	=> getgp( 'subject' ),
			'loop_type'	=> $loop_type,
			'loop_time'	=> implode( '-', array( $day, $hour, $minute ) ),
			'is_open'	=> (int)getgp( 'is_open' ),
			'run_script'=> getgp( 'run_script' ),
			'next_time'	=> $next_time
		);
		if( !getgp(cron_id) ){
			$cron->add( $new_cron );
		} elseif( 'edit' == $a && $cron_id ){
			
			$cron->edit( $cron_id, $new_cron );
		}
		showmsg( 'success', 'success', "?c=cron&a=list" );
	} else {

		$day_select = $week_select = $hour_select = $minute_select = $now_type_select = $run_script_select = '';

		//生成天数下拉
		for( $i = 1; $i <= 31; $i++ ){
			$day_select .= "<option value=\"$i\">".sprintf("%02d",$i)."日</option>";
		}
		$day_select .= "<option value=\"0\">最后一天</option>";

		//生成周下拉
		$week_array = array( 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六', 0 => '周天' );
		foreach( $week_array as $key => $name ){
			$week_select .= "<option value=\"$key\">$name</option>";
		}

		//生成小时下拉
		for( $i = 0; $i < 24; $i++ ){
			$hour_select .= "<option value=\"$i\">{$i}点</option>";
		}

		//生成分钟下拉
		$minute_array = array( 0, 10, 20, 30, 40, 50 );
		foreach( $minute_array as $min ){
			$minute_select .= "<option value=\"$min\">".sprintf("%02d",$min)."分</option>";
		}

		//生成周其类型下拉
		$now_type_array = array( 'minute' => '分钟', 'hour' => '小时', 'day' => '天' );
		foreach( $now_type_array as $key => $val ){
			$now_type_select .= "<option value=\"$key\">$val</option>";
		}

		//生成执行脚本下拉
		$fd = dir( APP_DIR . '/cron' );
		while( ( $file = $fd->read() ) !== false ){
			if($file ==	'.'	|| $file ==	'..' || is_dir( $fiel ) ){ continue; }
			//$path = substr(preg_replace( "*/{2,}*", "/", $file ), 1); // 替换多个反斜杠
		//echo $path.'<br/>';
			$key = substr( $file, 0, strpos( $file, '.' ) );
			$run_script_select .= "<option value=\"$key\">$file</option>";
		}

		$month_day    = $week_day = '';
		$week_hour    = $day_hour = $month_hour = '';
		$hour_minute  = $now_time = 0;
		$now_type     = 'minute';
		$loop_type_month = $loop_type_week = $loop_type_day = $loop_type_hour = $loop_type_now = '';
		$is_open_Y    = $is_open_N = '';
		if( 'edit' == $a ){
			$row = $cron->get( array( 'cron_id' => $cron_id ) );
			@extract( $row, EXTR_SKIP );
			${'loop_type_'.$loop_type} = ' selected';
			${'is_open_'.($is_open?'Y':'N')} = 'checked';
			$run_script_select = str_replace( "value=\"$run_script\">", "value=\"$run_script\" selected>", $run_script_select );
			switch( $loop_type ){
				case 'month'	:
					list( $month_day, $month_hour ) = explode( '-', $loop_time );
					$day_select = str_replace( "value=\"$month_day\">", "value=\"$month_day\" selected>", $day_select );
					$hour_select = str_replace( "value=\"$month_hour\">", "value=\"$month_hour\" selected>", $hour_select );
					break;
				case 'week'	:
					list( $week_day, $week_hour ) = explode( '-', $loop_time );
					$day_select = str_replace( "value=\"$week_day\">", "value=\"$week_day\" selected>", $day_select );
					$hour_select = str_replace( "value=\"$week_hour\">", "value=\"$week_hour\" selected>", $hour_select );
					break;
				case 'day'	:
					list( , $day_hour ) = explode( '-', $loop_time );
					$hour_select = str_replace( "value=\"$day_hour\">", "value=\"$day_hour\" selected>", $hour_select );
					break;
				case 'hour'	:
					list( , , $hour_minute ) = explode( '-', $loop_time );
					$minute_select = str_replace( "value=\"$hour_minute\">", "value=\"$hour_minute\" selected>", $minute_select );
					break;
				case 'now'	:
				default		:
					list( $now_day, $now_hour, $now_minute ) = explode( '-', $loop_time );
					$now_type = 'minute';

					if( $now_day ){
						$now_type = 'day';
						$now_time = $now_day;
					} elseif( $now_hour ){
						$now_type = 'hour';
						$now_time = $now_hour;
					} else {
						$now_type = 'minute';
						$now_time = $now_minute;
					}
					$now_type_select = str_replace( "value=\"$now_type\">", "value=\"$now_type\" selected>", $now_type_select );
					break;
			}
			$run_script_select = str_replace("value=\"$run_script\">", "value=\"$run_script\" selected>", $run_script_select);
		}


		tpl( 'cron/edit' );
	}