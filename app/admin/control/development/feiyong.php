<?php
	require_once ROOT . '/framework/models/feiyong.class.php';
	require_once ROOT . '/data/cache/audit_type.cache.php';
	$type = getgp('type')?getgp('type'):'unlist';
	feiyong::$month  = getgp('month');

	switch($type)
	{
		case 'list':
			$results  = feiyong::feiyonglist();
			break;
		case 'unlist':
		default:
			$results  = feiyong::unfeiyonglist();
			break;
	}
	$results         = $results['results'];
	$month           = !empty(feiyong::$month)?feiyong::$month:date('Y-m');
	$contSetting     = feiyong::$contSetting;
	
	$leixing = $tixi = $rentian = $feiyong = array();
//	echo '<pre />';
//	print_r($results);exit;
	foreach( $results as $k => $vo )
	{
		$feiyong[$k] = 0;
		foreach ($vo['task'] as $ke => $task)
		{
			$rentian[$k] = $rentian[$k]+$task['tk_num'];
			foreach ($task['teams'] as $key => $team)
			{
				foreach ($team['project'] as $keys =>$project)
				{
					if($feiyong[$k]=='0')
					{
						$sql = 'select * from sp_cost where ct_id='.$project["ct_id"].' and month=\''.$month.'\'';
						$tmp = $db->getAll($sql);
						if(!empty($tmp))
						{
							foreach($tmp as $itme)
							{
								$feiyong[$k] = $feiyong[$k]+$itme['zfy'];
							}
						}else{
							$feiyong[$k] = null;
						}
					}
					$tixi[$k][]    = $arr_audit_iso[$project['iso']];
					$leixing[$k][] = $audit_type_array[$project['audit_type']]['name'];
				}
			}
		}
	}
	tpl();
?>