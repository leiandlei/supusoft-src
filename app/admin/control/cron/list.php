<?php


$crons = array();
	$query = $db->query("SELECT * FROM sp_cron WHERE 1 $where");
	while( $rt = $db->fetch_array( $query ) ){
		$desc = $loop_type_array[$rt['loop_type']];

		list( $day, $hour, $minute ) = explode( '-', $rt['loop_time'] );

		switch( $rt['loop_type'] ){
			case 'month':
				$desc .= sprintf( $out_format['month_day'], $day, $hour, $minute );
				break;
			case 'week'	:
				$desc .= sprintf( $out_format['week_day'], $week_day_array[$day], $hour, $minute );
				break;
			case 'day'	:
				$desc .= sprintf( $out_format['day'], $hour, $minute );
				break;
			case 'hour'	:
				$desc .= sprintf( $out_format['hour'], $minute );
				break;
			case 'now'	:
			default		:
				list( $now_day, $now_hour, $now_minute ) = explode( '-', $rt['loop_time'] );
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
				$desc .= sprintf( $out_format['now'], $minute, $now_type_array[$now_type] );
				break;
		} // end switch(.....
		$rt['desc'] = $desc;
		$rt['is_open_V'] = ($rt['is_open'])?'开启':'关闭';
		$crons[$rt['cron_id']] = $rt;
	}

	tpl( 'cron/list' );