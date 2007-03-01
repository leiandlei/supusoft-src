<?php
class feiyong
{
	public static $month    = '';//查询月份
	public static $ctfrom   = '';//合同雷媛
	public static $uid      = '';//用户ID
	public static $eid      = '';//企业ID
	/**
	 * 实际审核的体系数
	 * 0为最大 1为单标 2为双标 3为三标
	 *
	 * leader:组长 unleader:组员
	 * senior:高级 unsenior:非高级
	 * major :专业 unmajor :非专业
	 * ws:文审
	 * jd:监督
	 */
	public static $contSetting = array
	(
		'0' => array
		(
			'leader' => array
			(
				'ws' => '400','jd' => '300',
				'senior' => array
				(
					'major'   => '460',
					'unmajor' => '430'
				),
				'unsenior' => array
				(
					'major'   => '450',
					'unmajor' => '420'
				)
			),
			'unleader' => array
			(
				'senior' => array
				(
					'major'   => '400',
					'unmajor' => '370'
				),
				'unsenior' => array
				(
					'major'   => '390',
					'unmajor' => '370'
				)
			)
		),
		'1' => array
		(
			'leader' => array
			(
				'ws' => '200','jd' => '100',
				'senior' => array
				(
					'major'   => '320',
					'unmajor' => '290'
				),
				'unsenior' => array
				(
					'major'   => '310',
					'unmajor' => '280'
				)
			),
			'unleader' => array
			(
				'senior' => array
				(
					'major'   => '260',
					'unmajor' => '230'
				),
				'unsenior' => array
				(
					'major'   => '250',
					'unmajor' => '220'
				)
			)
		),
		'2' => array
		(
			'leader' => array
			(
				'ws' => '250','jd' => '150',
				'senior' => array
				(
					'major'   => '350',
					'unmajor' => '320'
				),
				'unsenior' => array
				(
					'major'   => '340',
					'unmajor' => '310'
				)
			),
			'unleader' => array
			(
				'senior' => array
				(
					'major'   => '290',
					'unmajor' => '260'
				),
				'unsenior' => array
				(
					'major'   => '280',
					'unmajor' => '250'
				)
			)
		),
		'3' => array
		(
			'leader' => array
			(
				'ws' => '300','jd' => '200',
				'senior' => array
				(
					'major'   => '420',
					'unmajor' => '370'
				),
				'unsenior' => array
				(
					'major'   => '390',
					'unmajor' => '360'
				)
			),
			'unleader' => array
			(
				'senior' => array
				(
					'major'   => '340',
					'unmajor' => '310'
				),
				'unsenior' => array
				(
					'major'   => '330',
					'unmajor' => '300'
				)
			)
		),
		'4' => array
		(
			'leader' => array
			(
				'ws' => '350','jd' => '250',
				'senior' => array
				(
					'major'   => '450',
					'unmajor' => '400'
				),
				'unsenior' => array
				(
					'major'   => '450',
					'unmajor' => '420'
				)
			),
			'unleader' => array
			(
				'senior' => array
				(
					'major'   => '370',
					'unmajor' => '340'
				),
				'unsenior' => array
				(
					'major'   => '360',
					'unmajor' => '330'
				)
			)
		)
	);

	//未结算费用列表
	public static function unfeiyonglist()
	{
		global $db;
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
		}else{
			$nowmoth     = date('Y-m');

		}
		//写入task的ct_id、audit_ver、audit_type
//		$sql  = 'select * from sp_task';
//		$data = $db->getAll($sql);
//		foreach($data as $task)
//		{
//			$sql = 'select * from sp_project where tid='.$task['id'];
//			$porject = $db->getOne($sql);
//			if(!empty($porject))
//			{
//				$db->update('task',array('ct_id'=>$porject['ct_id'],'audit_ver'=>$porject['audit_ver'],'audit_type'=>$porject['audit_type']),array('id'=>$task['id']));
//			}
//			
//		}
		//屏蔽企业
		$sql     = 'select * from sp_cost where status=1 and month=\''.$nowmoth.'\' group by eid';
		$results = $db->getAll($sql);
		$notineid = array();
		foreach ($results as $value)
		{
			$notineid[] = $value['eid'];
		}
		$notineid = implode($notineid,',');

		$data = array();
		$join =  $select = '';$where =' where 1';

		$sql    = 'select %s from `sp_task` t';
		$where .= ' and t.deleted=0';
		$where .= ' and t.status in(2,3)';
		$where .= ' and t.audit_type in(1003,1004,1005,1007)';
		$where .= ' and date_format(t.te_date,"%Y-%m")=\''.$nowmoth.'\'';
		if(!empty($notineid))
		$where .= ' and t.eid not in('.$notineid.')';

		$sql =   sprintf($sql,($select=='')?'*':$select).$join.$where;
		$results = $db->getAll($sql);
		foreach($results as $task)if($task['audit_type']=='1003')array_unshift($results,$db->getOne('select * from sp_task where deleted=0 and eid='.$task['eid'].' and ct_id='.$task['ct_id'].' and audit_type=\'1002\''));
		
		foreach ($results as $task)
		{
			if( empty($data[$task['eid']]) )
			{
				$sql = 'select * from sp_enterprises where deleted=0 and eid='.$task['eid'];
				$enterprises = $db->getOne($sql);
			}
			if(empty($enterprises))continue;

			$sql      = 'select * from sp_project where deleted=0 and audit_type in(1002,1003,1004,1005,1007) and eid='.$task['eid'].' and ct_id='.$task['ct_id'];
			$projects = $db->getAll($sql);

			if( !empty($projects) )
			{
				foreach ($projects as $project)
				{	
					$sql            = 'select * from sp_task_audit_team tat left join sp_hr hr on hr.id=tat.uid and hr.deleted=0 where tat.deleted=0 and tat.eid='.$task['eid'].' and tat.tid='.$task['id'].' and tat.pid='.$project['id'];
					$taskAuditTeams = $db->getAll($sql);
					if(!empty($taskAuditTeams))
					{
						foreach ($taskAuditTeams as $taskAuditTeam)
						{
							if( empty($enterprises)||empty($task)||empty($taskAuditTeam)||empty($project) )continue;

							if(empty($data[$task['eid']]))
							$data[$task['eid']] = $enterprises;

							if(empty($data[$task['eid']]['task'][$task['id']]))
							$data[$task['eid']]['task'][$task['id']]=$task;

							if( empty($data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]) )
							$data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']] = $taskAuditTeam;
							
							if( empty($data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]['project'][$project['audit_ver']]) )
							$data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]['project'][$project['audit_ver']] = $project;
						}
					}
						
				}
			}
		}
		return self::getArrayForResults('0','',$data);

	}

	//未结算费用列表详情
	public static function unfeiyonginfo()
	{
		global $db;
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
		}else{
			$nowmoth     = date('Y-m');

		}
		if(empty(self::$eid))return self::getArrayForResults('1','缺少参数');
		$tabs_where = $join =  $select = '';$where =' where 1';

		$sql    = 'select %s from `sp_task` t';
		$where .= ' and t.deleted=0';
		$where .= ' and t.eid='.self::$eid;
		$where .= ' and t.status in(2,3)';
		$where .= ' and t.audit_type in(1003,1004,1005,1007)';
		$where .= ' and date_format(t.te_date,"%Y-%m")=\''.$nowmoth.'\'';

		$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where;
		$results = $db->getAll($sql);
		foreach($results as $task)if($task['audit_type']=='1003')array_unshift($results,$db->getOne('select * from sp_task where deleted=0 and eid='.$task['eid'].' and ct_id='.$task['ct_id'].' and audit_type=\'1002\''));
		$data    = array();

		foreach ($results as $task)
		{
			$sql = 'select * from sp_enterprises where deleted=0 and eid='.$task['eid'];
			$enterprises = $db->getOne($sql);
			if(empty($enterprises))continue;

			$sql      = 'select * from sp_project where deleted=0 and audit_type in(1002,1003,1004,1005,1007) and eid='.$task['eid'].' and ct_id='.$task['ct_id'];
			$projects = $db->getAll($sql);
			if( !empty($projects) )
			{
				foreach ($projects as $project)
				{	
					$sql            = 'select * from sp_task_audit_team tat left join sp_hr hr on hr.id=tat.uid and hr.deleted=0 where tat.deleted=0 and tat.eid='.$task['eid'].' and tat.tid='.$task['id'].' and tat.pid='.$project['id'];
					$taskAuditteam  = $db->getAll($sql);
					
							
					
					if(!empty($taskAuditteam))
					{
						 
						foreach ($taskAuditteam as $taskAuditTeam)
						{   
							//判断为是否部分实习
							$sqls            = 'select tat.name,tat.qua_type from sp_task_audit_team tat left join sp_hr hr on hr.id=tat.uid and hr.deleted=0 where tat.deleted=0 and tat.eid='.$task['eid'].' and tat.tid='.$task['id'].' and tat.name="'.$taskAuditTeam['name'].'"' ;
							$taskAuditteams  = $db->getAll($sqls);
					        $taskAuditTeam['qua_types']   =$taskAuditteams ;
							///判断为是否部分实习结束
							$taskAuditTeam['contract'] = $db->getOne('select * from sp_contract where deleted=0 and eid='.$taskAuditTeam['eid'].' and ct_id='.$project['ct_id']);
							if( empty($enterprises)||empty($task)||empty($taskAuditTeam)||empty($project) )continue;

							if(empty($data))
							$data = $enterprises;

							if(empty($data['task'][$task['id']]))
							{
								$data['task'][$task['id']]=$task;
								$data['task'][$task['id']]['audit_ver']  = $taskAuditTeam['audit_ver'];
								$data['task'][$task['id']]['audit_type'] = $taskAuditTeam['audit_type'];
							}
							

							if( empty($data['task'][$task['id']]['team'][$taskAuditTeam['id']]) )
							$data['task'][$task['id']]['team'][$taskAuditTeam['id']] = $taskAuditTeam;
							
							if( empty($data['task'][$task['id']]['team'][$taskAuditTeam['id']]['project'][$project['audit_ver']]) )
							$data['task'][$task['id']]['team'][$taskAuditTeam['id']]['project'][$project['audit_ver']] = $project;
						}

					}
						
				}
			}
		}

		return self::getArrayForResults('0','',$data);
	}

	//已结算费用列表
	public static function feiyonglist()
	{
		global $db;
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
		}else{
			$nowmoth     = date('Y-m');

		}
		$sql     = 'select eid from sp_cost where status=1 and month=\''.$nowmoth.'\' group by eid';
		$results = $db->getAll($sql);
		$ineid   =  array();
		foreach ($results as $value)
		{
			$ineid[] = $value['eid'];
		}
		$ineid = implode($ineid,',');

		$data = $results = array();
		if(!empty($ineid))
		{
			$join =  $select = '';$where =' where 1';

			$sql    = 'select %s from `sp_task` t';
			$where .= ' and t.deleted=0';
			$where .= ' and t.status in(2,3)';
			$where .= ' and date_format(t.te_date,"%Y-%m")=\''.$nowmoth.'\'';
			if(!empty($ineid))
			$where .= ' and t.eid in('.$ineid.')';

			$sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
			$results = $db->getAll($sql);
		}

		foreach ($results as $task)
		{
			if( empty($data[$task['eid']]) )
			{
				$sql = 'select * from sp_enterprises where deleted=0 and eid='.$task['eid'];
				$enterprises = $db->getOne($sql);
			}
			if(empty($enterprises))continue;

			$sql      = 'select * from sp_project where deleted=0 and audit_type in(1002,1003,1004,1005,1007) and eid='.$task['eid'].' and tid='.$task['id'];
			$projects = $db->getAll($sql);

			if( !empty($projects) )
			{
				foreach ($projects as $project)
				{	
					$sql            = 'select * from sp_task_audit_team tat left join sp_hr hr on hr.id=tat.uid and hr.deleted=0 where tat.deleted=0 and tat.eid='.$task['eid'].' and tat.tid='.$task['id'].' and tat.pid='.$project['id'];
					$taskAuditTeams = $db->getAll($sql);
					if(!empty($taskAuditTeams))
					{
						foreach ($taskAuditTeams as $taskAuditTeam)
						{
							if( empty($enterprises)||empty($task)||empty($taskAuditTeam)||empty($project) )continue;

							if(empty($data[$task['eid']]))
							$data[$task['eid']] = $enterprises;

							if(empty($data[$task['eid']]['task'][$task['id']]))
							$data[$task['eid']]['task'][$task['id']]=$task;

							if( empty($data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]) )
							$data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']] = $taskAuditTeam;
							
							if( empty($data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]['project'][$project['audit_ver']]) )
							$data[$task['eid']]['task'][$task['id']]['teams'][$taskAuditTeam['id']]['project'][$project['audit_ver']] = $project;
						}
					}
						
				}
			}
		}
		return self::getArrayForResults('0','',$data);
	}

	//已结算费用列表详情
	public static function feiyonginfo()
	{
		global $db;self::getlastMonth();
		if(empty(self::$month)||empty(self::$eid))return self::getArrayForResults('1','缺少参数');

		$sql   = 'select * from sp_hr hr left join sp_cost c on hr.id=c.uid and hr.deleted=0 where status=1 and c.month=\''.self::$month.'\' and c.eid='.self::$eid;
		$detail         = $db->getOne('select * from sp_enterprises where deleted=0 and eid='.self::$eid);
		$detail['cost'] = $db->getAll($sql);
		//判断为是否部分实习
		foreach ($detail['cost'] as $key => $taskAuditTeam) {
			$sqls            = 'select tat.name,tat.qua_type from sp_task_audit_team tat left join sp_hr hr on hr.id=tat.uid and hr.deleted=0 where tat.deleted=0 and tat.eid='.self::$eid.' and tat.tid='.$taskAuditTeam['tid'].'  and  tat.name="'.$taskAuditTeam['name'].'"' ;
		    $taskAuditteams  = $db->getAll($sqls);
            $detail['cost'][$key]['qua_types']   =$taskAuditteams;
		}
		
		///判断为是否部分实习结束

		return self::getArrayForResults('0','',$detail);
	}

	//费用保存
	public static function feiyongsave($data=array())
	{
		global $db;$results = array();
		if( !empty($data) )
		{
			if( !empty($data['id']) )
			{
				for ($i=0,$count=count($data['id']); $i < $count; $i++)
				{
					$save = array(
									'rt'            => $data['rt'][$i],
									'qtrt'          => $data['qtrt'][$i],
									'hjrt'          => $data['hjrt'][$i],
									'jtf'           => $data['jtf'][$i],
									'ws'            => $data['ws'][$i],
									'rtf'           => $data['rtf'][$i],
									'zfy'           => $data['zfy'][$i],
								);
					$results[] = $db->update('cost',$save,array('id'=>$data['id'][$i]),false);
				}
			}elseif(!empty($data['uid']))
			{
				for ($i=0,$count=count($data['uid']); $i < $count; $i++)
				{
					$save = array(
									'eid'           => $data['eid'],
									'ct_id'         => $data['ct_id'],
									'tid'           => $data['tid'][$i],
									'ctfrom'        => $data['ctfrom'],
									'uid'           => $data['uid'][$i],
									'month'         => $data['month'],
									'audit_type'    => $data['audit_type'][$i],
									'project'       => $data['project'][$i],
									'tixi'          => $data['tixi'][$i],
									'qua_type'      => $data['qua_type'][$i],
									'audit_code'    => $data['audit_code'][$i],
									'role'          => $data['role'][$i],
									'taskBeginDate' => $data['taskBeginDate'][$i],
									'taskEndDate'   => $data['taskEndDate'][$i],
									'tk_num'        => $data['tk_num'][$i],
									'is_site'       => $data['is_site'][$i],
									'rt'            => $data['rt'][$i],
									'qtrt'          => $data['qtrt'][$i],
									'hjrt'          => $data['hjrt'][$i],
									'jtf'           => $data['jtf'][$i],
									'ws'            => $data['ws'][$i],
									'rtf'           => $data['rtf'][$i],
									'zfy'           => $data['zfy'][$i],
								);

					$results[] = $db->insert('cost',$save,false);
				}
			}
				
		}

		if( empty($results) )
		{
			return self::getArrayForResults('1','失败');
		}else{
			return self::getArrayForResults('0','成功',$results);
		}
	}

	//合作方列表
	public static function gethezuofang()
	{
		global $db;self::getlastMonth();
		$sql     = 'select ctfrom from sp_cost where status=1 and month=\''.self::$month.'\' group by ctfrom';
		// P($sql);
		$results = $db->getAll($sql);
		foreach ($results as $value)
		{
			$inctfrom[] = $value['ctfrom'];
		}

		$results = array();
		if(!empty($inctfrom))
		{
			$sql     = 'select * from sp_partner where deleted=0 and code in ('.implode(',',$inctfrom).')';
			// P($sql);
			$results = $db->getAll($sql);
		}
		return self::getArrayForResults('0','',$results);
	}

	//合作方未结算费用列表
	public static function unhezuofanglist()
	{
		global $db;self::getlastMonth();

		if(empty(self::$month)||empty(self::$ctfrom))return self::getArrayForResults('1','缺少参数');
		$ineid = $notineid = array();

		$sql     = 'select eid from sp_cost where status=1 and month=\''.self::$month.'\' and ctfrom=\''.self::$ctfrom.'\' group by eid';
		$results = $db->getAll($sql);
		foreach ($results as $value)
		{
			$ineid[] = $value['eid'];
		}

		$sql     = 'select * from sp_cost_partner where status=1 and month=\''.self::$month.'\'';
		$results = $db->getAll($sql);
		foreach ($results as $value)
		{
			$notineid[] = $value['eid'];
		}

		$ineid  = implode($ineid,',');$notineid  = implode($notineid,',');

		$data = $results = array();
		if( !empty($ineid) )
		{
			$join =  $select = '';$where =' where 1';

			$sql    = 'select %s from `sp_partner` ct';
			$where .= ' and ct.deleted=0';

			$join  .= ' left join `sp_enterprises` e on e.ctfrom = ct.code';
			$where .= ' and e.deleted=0';
			$where .= ' and e.eid in('.$ineid.')';

			if(!empty($notineid))
			$where .= ' and e.eid not in('.$notineid.')';

			$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where;
			$results = $db->getAll($sql);
		}
		

		foreach ($results as $value)
		{
			$sql                 = 'select * from sp_cost where status=1 and eid='.$value['eid'].' and month=\''.self::$month.'\'';
			$value['shenheyuan'] = $db->getAll($sql);

			$ct_id   = !empty($value['shenheyuan'][0]['ct_id'])?$value['shenheyuan'][0]['ct_id']:'';
			if( !empty($ct_id) )
			{
				$value['cost'] = $db->getAll('select * from sp_contract_cost where deleted=0 and ct_id='.$ct_id);
				$value['item'] = $db->getAll('select * from sp_contract_item where deleted=0 and ct_id='.$ct_id);
			}
			$data[] = $value;
		}
		return self::getArrayForResults('0','',$data);

	}
	//合作方已结算费用列表
	public static function hezuofanglist()
	{
		global $db;self::getlastMonth();
		if(empty(self::$month)||empty(self::$ctfrom))return self::getArrayForResults('1','缺少参数');

		$join =  $select = '';$where =' where 1';

		$sql    = 'select %s from `sp_cost_partner` cp';
		$where .= ' and cp.status=1';
		$where .= ' and cp.ctfrom=\''.self::$ctfrom.'\'';
		$where .= ' and cp.month=\''.self::$month.'\'';

		$join  .= ' left join `sp_partner` p on p.pt_id = cp.pt_id';
		$where .= ' and p.code=cp.ctfrom';
		$where .= ' and p.deleted=0';

		$join  .= ' left join `sp_enterprises` e on e.eid = cp.eid';
		$where .= ' and e.deleted=0';

		$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where;
		$results = $db->getAll($sql);

		$ct_id   = '';
		if( !empty($results[0]) )
		{
			$sql   = 'select ct_id from sp_cost where status=1 and month=\''.$results[0]['month'].'\' and ctfrom=\''.$results[0]['ctfrom'].'\' and eid='.$results[0]['eid'];
			$ct_id = $db->get_var($sql);
		}

		$data = array();
		if( !empty($ct_id) )
		{
			foreach ($results as $value)
			{
				$value['cost'] = $db->getAll('select * from sp_contract_cost where deleted=0 and ct_id='.$ct_id);
				$value['item'] = $db->getAll('select * from sp_contract_item where deleted=0 and ct_id='.$ct_id);
				$data[]        = $value;
			}
		}
		$data = empty($data)?$results:$data;
		return self::getArrayForResults(0,'',$data);

	}
	//合作方保存
	public static function hezuofangsave($data=array())
	{
		global $db;$results = array();
		if( !empty($data) )
		{
			if(!empty($data['id']))
			{
				for ($i=0,$count=count($data['id']); $i < $count; $i++)
				{ 
					$save = array(
									'nianduguanlifei' => $data['nianduguanlifei'][$i],
									'jiesuanzongjine' => $data['jiesuanzongjine'][$i],
									'shenhechengben'  => $data['shenhechengben'][$i],
									'qitafeiyong'     => $data['qitafeiyong'][$i],
									'ctcost'          => $data['ctcost'][$i],
									'sscost'          => $data['sscost'][$i],
									'edingshuishou'   => $data['edingshuishou'][$i],
									'fankuanjine'     => $data['fankuanjine'][$i],
									'jiesuanjine'     => $data['jiesuanjine'][$i],
								);
					$results[] = $db->update('cost_partner',$save,array('id'=>$data['id'][$i]),false);
				}
			}else
			{
				for ($i=0,$count=count($data['pt_id']); $i < $count; $i++)
				{ 
					$save = array(
									'pt_id'           => $data['pt_id'][$i],
									'ctfrom'          => $data['ctfrom'][$i],
									'eid'             => $data['eid'][$i],
									'month'           => $data['month'][$i],
									'nianduguanlifei' => $data['nianduguanlifei'][$i],
									'jiesuanzongjine' => $data['jiesuanzongjine'][$i],
									'shenhechengben'  => $data['shenhechengben'][$i],
									'qitafeiyong'     => $data['qitafeiyong'][$i],
									'ctcost'          => $data['ctcost'][$i],
									'sscost'          => $data['sscost'][$i],
									'edingshuishou'   => $data['edingshuishou'][$i],
									'fankuanjine'     => $data['fankuanjine'][$i],
									'jiesuanjine'     => $data['jiesuanjine'][$i],
								);
					$results[] =  $db->insert('cost_partner',$save,false);
						
				}

			}
		}

		if( empty($results) )
		{
			return self::getArrayForResults('1','失败');
		}else{

			$data =self::getArrayForResults('1','成功',$results);
			
			return $data;
			
		}

	}

	//合作方统计
	public static function hezuofangtongji()
	{
		global $db;self::getlastMonth();
		$hezuofangs = self::gethezuofang();
		$hezuofangs = $hezuofangs['results'];
		$results    = array();
		foreach ($hezuofangs as  $hezuofang)
		{
			self::$ctfrom = $hezuofang['code'];
			$hezuofang['enterprises']              = self::hezuofanglist()['results'];
			$hezuofang['partnertongji']['upmoth']  = $db->getOne('select * from sp_cost_partner_tongji where status=1 and month=\''.self::getlastMonth(self::$month).'\' and ctfrom=\''.self::$ctfrom.'\'');
			$hezuofang['partnertongji']['nowmoth'] = $db->getOne('select * from sp_cost_partner_tongji where status=1 and month=\''.self::$month.'\' and ctfrom=\''.self::$ctfrom.'\'');
			$results[]                  = $hezuofang;
		}
		return self::getArrayForResults('0','',$results);
	}

	//合作方统计保存
	public static function hezuofangtongjisave( $data = array() )
	{
		if( (empty($data['hj']['ctfrom'])||empty($data['hj']['month']))&&empty($data['cp']['id']) )return self::getArrayForResults('1','失败');

		global $db;$results = array();
		//合作方保存
		if( !empty($data['cp']['id']) )
		{
			for ($i=0,$count=count($data['cp']['id']); $i < $count; $i++)
			{ 
				$save = array(
								'sscost'          => $data['cp']['sscost'][$i],
								'edingshuishou'   => $data['cp']['edingshuishou'][$i],
								'fankuanjine'     => $data['cp']['fankuanjine'][$i],
								'jiesuanjine'     => $data['cp']['jiesuanjine'][$i],
							);
				$results['cp'][] = $db->update('cost_partner',$save,array('id'=>$data['cp']['id'][$i]),false);
			}
		}

		//结转保存
		if( !empty($data['hj']['ctfrom'])&&!empty($data['hj']['month']) )
		{
			for ($i=0,$count=count($data['hj']['ctfrom']); $i < $count; $i++)
			{ 
				$sql  = 'select * from sp_cost_partner_tongji where status=1 and ctfrom=\''.$data['hj']['ctfrom'][$i].'\' and month=\''.$data['hj']['month'][$i].'\'';
				$cpt  = $db->getOne($sql);
				if( empty($cpt) )
				{
					$save = array(
								'ctfrom'    => $data['hj']['ctfrom'][$i],
								'month'     => $data['hj']['month'][$i],
								'yufukuan'  => $data['hj']['yufukuan'][$i],
								'koukuan'   => $data['hj']['koukuan'][$i],
								'jiezhuan'  => $data['hj']['jiezhuan'][$i],
							);
					$results['hj'][] =  $db->insert('cost_partner_tongji',$save,false);
				}else
				{
					$save = array(
								'yufukuan'  => $data['hj']['yufukuan'][$i],
								'koukuan'   => $data['hj']['koukuan'][$i],
								'jiezhuan'  => $data['hj']['jiezhuan'][$i],
							);
					$results['hj'][] = $db->update('cost_partner_tongji',$save,array('id'=>$cpt['id']),false);
				}
			}
		}
		return self::getArrayForResults('0','',$results);
	}

	//审核员列表
	public static function getshenheyuan()
	{
		global $db;
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
		}else{
			$nowmoth     = date('Y-m');

		}
		$sql = 'select * from sp_cost where status=1 and month=\''.$nowmoth.'\' group by uid';
		$results = $db->getAll($sql);
		foreach ($results as $value)
		{
			$inuid[] = $value['uid'];
		}
		$results = array();
		if(!empty($inuid))
		{
			$sql  = 'select * from sp_hr where deleted=0 and id in ('.implode(',',$inuid).')';
			$data = $db->getAll($sql);
			foreach ($data as $vo)
			{
				$sql         = 'select * from sp_cost_hr where status=1 and uid='.$vo['id'].' and month=\''.$nowmoth.'\'';
				$vo['cost']  = $db->getOne($sql);
				$results[]   = $vo;
			}
		}
		return self::getArrayForResults('0','',$results);
	}

	//审核员结算列表
	public static function shenheyuanlist()	
	{
		global $db;
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
		}else{
			$nowmoth     = date('Y-m');

		}
		$join =  $select = '';$where =' where 1';

		$sql    = 'select %s from `sp_cost` c';
		$where .= ' and c.status=1';
		$where .= ' and c.month=\''.$nowmoth.'\'';
		$where .= ' and c.uid='.self::$uid;

		$join  .= ' left join `sp_enterprises` e on e.eid = c.eid';
		$where .= ' and e.deleted=0';

		$sql     = sprintf($sql,($select=='')?'*':$select).$join.$where;
		$results = $db->getAll($sql);
		$results['extend']['hrcost']        = $db->getOne('select * from sp_cost_hr where status=1 and uid='.self::$uid.' and month=\''.$nowmoth.'\'');
		$results['extend']['kaohe']         = $db->getOne('select * from sp_examine_user where  userID='.self::$uid.' and date=\''.$nowmoth.'\'');
		$results['extend']['hr']            = $db->getOne('select * from sp_hr where deleted=0 and id='.self::$uid);
		$results['extend']['qualification'] = $db->getAll('select * from sp_hr_qualification where deleted=0 and uid='.self::$uid.' and status=1');
		
		$waichu = $db->getAll('select * from sp_task_audit_team where deleted=0 and data_for=2 and uid='.self::$uid.' and (DATE_FORMAT(taskEndDate, "%Y-%m")=\''.$nowmoth.'\' or DATE_FORMAT(taskBeginDate, "%Y-%m")=\''.$nowmoth.'\')');
		$shenhe = $db->getAll('select * from sp_task_audit_team where deleted=0 and data_for=0 and uid='.self::$uid.' and (DATE_FORMAT(taskEndDate, "%Y-%m")=\''.$nowmoth.'\' or DATE_FORMAT(taskBeginDate, "%Y-%m")=\''.$nowmoth.'\')');
		$waichuday = 0;$shenheday = 0;
		foreach($waichu as $item)
		{
			if( substr($item['taskBeginDate'],0,10)!=substr($item['taskEndDate'],0,10) )
			{
				$dates = static::getDateFromRange($item['taskBeginDate'],$item['taskEndDate']);
				$count = count($dates);
				if($count>2)
				{
					for($i=1;$i+1<$count;$i++)
					{
						$waichuday = $waichuday+1;
					}
				}else{
					if(substr($item['taskBeginDate'],0,10)==$nowmoth)
					{
						if(substr($item['taskBeginDate'],11,2)<12)
						{
							$waichuday = $waichuday+1;
						}else{
							$waichuday = $waichuday+0.5;
						}
					}else
					{
						if(substr($item['taskEndDate'],11,2)<13)
						{
							$waichuday = $waichuday+0.5;
						}else{
							$waichuday = $waichuday+1;
						}
					}
				}
			}else{
				$waichuday = $waichuday+static::timediff($item['taskBeginDate'],$item['taskEndDate']);
			}
		}
		$results['waichuday'] = $waichuday;
		
		foreach($shenhe as $item)
		{
			if( substr($item['taskBeginDate'],0,10)!=substr($item['taskEndDate'],0,10) )
			{
				$dates = static::getDateFromRange($item['taskBeginDate'],$item['taskEndDate']);
				$count = count($dates);
				if($count>2)
				{
					for($i=1;$i+1<$count;$i++)
					{
						$shenheday = $shenheday+1;
					}
				}else{
					if(substr($item['taskBeginDate'],0,10)==$nowmoth)
					{
						if(substr($item['taskBeginDate'],11,2)<12)
						{
							$shenheday = $shenheday+1;
						}else{
							$shenheday = $shenheday+0.5;
						}
					}else
					{
						if(substr($item['taskEndDate'],11,2)<13)
						{
							$shenheday = $shenheday+0.5;
						}else{
							$shenheday = $shenheday+1;
						}
					}
				}
			}else{
				$shenheday = $shenheday+static::timediff($item['taskBeginDate'],$item['taskEndDate']);
			}
		}
		$results['shenheday'] = $shenheday;
		return self::getArrayForResults('0','',$results);
	}

	//审核员结算保存
	public static function shenheyuansave($data=array())
	{
		global $db;$results = array();
		if( !empty($data) )
		{
			if(!empty($data['id']))
			{
				$save = array(
								'jibenjine' => $data['jibenjine'],
								'buzhujine' => $data['buzhujine'],
								'hejijine'  => $data['hejijine'],
								'qitajine'  => $data['qitajine'],
								'shuijine'  => $data['shuijine'],
								'shijijine' => $data['shijijine'],
							);
				$results[] = $db->update('cost_hr',$save,array('id'=>$data['id']),false);
			}else
			{
				$save = array(
								'uid'       => $data['uid'],
								'month'     => $data['month'],
								'jibenjine' => $data['jibenjine'],
								'buzhujine' => $data['buzhujine'],
								'hejijine'  => $data['hejijine'],
								'qitajine'  => $data['qitajine'],
								'shuijine'  => $data['shuijine'],
								'shijijine' => $data['shijijine'],
							);
				$results[] = $db->insert('cost_hr',$save,false);
			}
		}
		if( empty($results) )
		{
			return self::getArrayForResults('1','失败');
		}else{
			return self::getArrayForResults('0','成功',$results);
		}
	}
	//预算单(已结算)
	public static function  yusuansheet()
	{
		global $db;
		$join    =  $select = '';$where =' where 1';

		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
			$begin_time  = self::$month.'-01 00:00:00';//筛选
			$engin_time  = self::$month.'-31 23:59:59';//
		}else{
			$nowmoth     = date('Y-m',strtotime("next month"));
			$begin_time  = date('Y-m',strtotime("next month")).'-01 00:00:00';//下月月初
			$engin_time  = date('Y-m',strtotime("next month")).'-31 23:59:59';//下月月末
		}
		if(!empty(self::$ctfrom))
		{
			$where1  =  " and ctfrom =".self::$ctfrom;
		}else{
			$where1  =  "";

		}
		$ifyusuanList  = $db->getAll('select * from sp_cost_yusuan where month=\''.$nowmoth.'\' group by eid');
		if(!empty($ifyusuanList))//判断新表是否存在本月预算数据
		{
			//已结算的企业
			$results  = $db->getAll("select * from sp_cost_yusuan where status=2 and month='".$nowmoth."' ".$where1." group by eid");
			$ineid    = array();
			foreach ($results as $value)
			{
				$ineid[] = $value['eid'];
			}
			//已结算的企业结束
			if(!empty($ineid))
			{
				$ineid = implode($ineid,',');
				$sql       = 'select %s from `sp_enterprises` t1';
				$select   .= ' t1.*';
				$where    .= ' and t1.eid in ('.$ineid.') and t1.deleted=0';
				$sql       = sprintf($sql,($select=='')?'*':$select).$join.$where;
				$results   = $db->getAll($sql);
				foreach ($results as $key => $value) 
				{
					
					$results[$key]['enterprises'] = $db->getOne("select * from sp_cost_yusuan where eid ='".$value['eid']."' and month='".$nowmoth."' ");

					//合同费用
					$ctList        =  $db->get_row("select t2.ct_id,t2.tb_date,t2.audit_type from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");

					$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."' and cost_type = '".$ctList['audit_type']."'");
	
					$dk_type       = $db->get_row("select * from sp_contract_cost_detail where cost_id ='".$ct_costList['id']."' and deleted= 0");
					if(!empty($dk_type)&&!empty($dk_type['dk_cost']))
					{
						$results[$key]['dktype']  = "1";
						$results[$key]['dk_type'] = "已到款".':'.$dk_type['dk_cost'];
					}else{
						$results[$key]['dktype']  = "2";
						$results[$key]['dk_type'] = "未到款";
						
					}
					
					switch($ct_costList['cost_type'])
					{
						case '1003';
							$results[$key]['audit_type'] = "初审";
							break;
						case '1004';
							$results[$key]['audit_type'] = "监一";
							break;
						case '1005';
							$results[$key]['audit_type'] = "监二";
							break;
						default:break;
					}
					$results[$key]['audittype'] = $ct_costList['cost_type'];
					$results[$key]['cost']      = $ct_costList['cost'];
					$results[$key]['tb_date']   = $ctList['tb_date'];
				}
			}
		}

		
		
		return self::getArrayForResults(0,'',$results);

	}
	//预算单(未结算)
	public static function  unyusuansheet()
	{
		global $db;
		$join    =  $select = '';$where =' where 1';

		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
			$begin_time  = self::$month.'-01 00:00:00';//筛选
			$engin_time  = self::$month.'-31 23:59:59';//
		}else{
			$nowmoth     = date('Y-m',strtotime("next month"));
			$begin_time  = date('Y-m',strtotime("next month")).'-01 00:00:00';//下月月初
			$engin_time  = date('Y-m',strtotime("next month")).'-31 23:59:59';//下月月末
		}
		if(!empty(self::$ctfrom))
		{
			$where1  = ' and st.ctfrom ='.self::$ctfrom;
		}else{
			$where1  = '';

		}
		//屏蔽已结算的企业
		$results  = $db->getAll('select * from sp_cost_yusuan where status=2 and month=\''.$nowmoth.'\' group by eid');
		$notineid = array();
		foreach ($results as $value)
		{
			$notineid[] = $value['eid'];
		}
		if(!empty($notineid))
		{
			$notineid = implode($notineid,',');
			$where .= ' and t1.eid not in('.$notineid.')';
		}
		//屏蔽已结算的企业结束
		$yusuanList  = $db->getAll("select * from sp_task st where st.tb_date   between  '".$begin_time."' and '".$engin_time."' and st.audit_type in(1003,1004,1005) and  st.deleted=0 and st.status=3".$where1);
		if(!empty($yusuanList))
		{
			foreach ($yusuanList as $yusuan)$yusuanEids[]  = $yusuan['eid'];
			$yusuanEid = implode(',',array_unique($yusuanEids));
			$sql       = 'select %s from `sp_enterprises` t1';
			$select   .= ' t1.*';
			$where    .= ' and t1.eid in ('.$yusuanEid.') and t1.deleted=0';
			$sql       = sprintf($sql,($select=='')?'*':$select).$join.$where;
			$results   = $db->getAll($sql);
			foreach ($results as $key => $value) 
			{
				
				$results[$key]['enterprises'] = $db->getOne("select * from sp_cost_yusuan where eid ='".$value['eid']."' and month='".date('Y-m',strtotime("next month"))."' ");
				//合同费用
				$ctList        =  $db->get_row("select t2.ct_id,t2.tb_date,t2.audit_type from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");

				$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."' and cost_type = '".$ctList['audit_type']."'");
	
				$dk_type       = $db->get_row("select * from sp_contract_cost_detail where cost_id ='".$ct_costList['id']."' and deleted= 0");
				if(!empty($dk_type)&&!empty($dk_type['dk_cost']))
				{
					$results[$key]['dktype']  = "1";
					$results[$key]['dk_type'] = "已到款".':'.$dk_type['dk_cost'];
				}else{
					$results[$key]['dktype']  = "2";
					$results[$key]['dk_type'] = "未到款";
					
				}
				
				switch($ct_costList['cost_type'])
				{
					case '1003';
						$results[$key]['audit_type'] = "初审";
						break;
					case '1004';
						$results[$key]['audit_type'] = "监一";
						break;
					case '1005';
						$results[$key]['audit_type'] = "监二";
						break;
					default:break;
				}
				$results[$key]['audittype'] = $ct_costList['cost_type'];
				$results[$key]['cost']      = $ct_costList['cost'];
				$results[$key]['tb_date']   = $ctList['tb_date'];
			}
		}
		
		return self::getArrayForResults(0,'',$results);

	}
	//预算单保存
	public static function yusuansave( $data = array() )
	{	
		if( (empty($data['ctfrom'])||empty($data['month']))&&empty($data['id']) )return self::getArrayForResults('1','失败');
		
		global $db;$results = array();
		
		//结转保存
		if(!empty($data['month']) )
		{
			
			for ($i=0,$count=count($data['ctfrom']); $i < $count; $i++)
			{ 
				$sql  = 'select * from sp_cost_yusuan where  ctfrom=\''.$data['ctfrom'][$i].'\' and  month=\''.$data['month'][$i].'\' and  eid=\''.$data['eid'][$i].'\'';
				$cpt  = $db->getOne($sql);
				if( empty($cpt) )
				{
					$save = array(
								'ctfrom'             => $data['ctfrom'][$i],
								'month'              => $data['month'][$i],
								'eid'                => $data['eid'][$i],
								'audit_type'         => $data['audittype'][$i],
								'hetongjine'         => $data['cost'][$i],
								'tb_date'            => $data['tb_date'][$i],
								'qitafeiyong'        => $data['qitafeiyong'][$i],
								'dk_type'            => $data['dk_type'][$i],
								'status'             => '2',
								'createTime'         => date('Y-m-d h:i:s'),

							);
					$results[] =  $db->insert('cost_yusuan',$save,false);
				}else
				{
					$save = array(
								'audit_type'         => $data['audittype'][$i],
								'hetongjine'         => $data['cost'][$i],
								'tb_date'            => $data['tb_date'][$i],
								'qitafeiyong'        => $data['qitafeiyong'][$i],
								'dk_type'            => $data['dk_type'][$i],
								'modifyTime'         => date('Y-m-d h:i:s'),
							);
					$results[] = $db->update('cost_yusuan',$save,array('id'=>$cpt['id']),false);
				}
			}
		}
		return self::getArrayForResults('0','',$results);
	}
	//结算单(未结算列表)
	public static function  unjiesuansheet()
	{
		global $db;
		$join    =  $select = '';$where =' where 1';
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
			$begin_time  = self::$month.'-01 00:00:00';//筛选
			$engin_time  = self::$month.'-31 23:59:59';
		}else{
			$nowmoth     = date('Y-m');
			$begin_time  = date('Y-m').'-01 00:00:00';//本月月初
			$engin_time  = date('Y-m').'-31 23:59:59';//本月月初
		}
		//屏蔽已结算的企业
		$results  = $db->getAll('select * from sp_cost_jiesuan where status=2 and month=\''.$nowmoth.'\' group by eid');
		$notineid = array();
		foreach ($results as $value)
		{
			$notineid[] = $value['eid'];
		}
		if(!empty($notineid)){
			$notineid = implode($notineid,',');
			$where .= ' and t1.eid not in('.$notineid.')';
		}
		//屏蔽已结算的企业结束
		if(self::$ctfrom=='01060000')//wl002 发证日期在本月
		{
			$jiesuanList  = $db->getAll("select * from sp_certificate sct where sct.first_date   between  '".$begin_time."' and '".$engin_time."' and sct.ctfrom='".self::$ctfrom."' and  sct.deleted=0 and sct.status=01");

		}elseif(!empty(self::$ctfrom)){     
			$jiesuanList  = $db->getAll("select * from sp_task st where st.te_date   between  '".$begin_time."' and '".$engin_time."' and st.ctfrom='".self::$ctfrom."' and  st.audit_type in(1003,1004,1005) and  st.deleted=0 and st.status=3");

		}else{
			$jiesuanList  = $db->getAll("select * from sp_task st where st.te_date   between  '".$begin_time."' and '".$engin_time."' and  st.audit_type in(1003,1004,1005) and  st.deleted=0 and st.status=3");

		}
		if(!empty($jiesuanList))
		{
			foreach ($jiesuanList as $jiesuan)$jiesuanEids[]  = $jiesuan['eid'];
			$jiesuanEid = implode(',',array_unique($jiesuanEids));
			$sql       = 'select %s from `sp_enterprises` t1';
			$select   .= ' t1.*';
			$where    .= ' and t1.eid in ('.$jiesuanEid.') and t1.deleted=0';
			$sql       = sprintf($sql,($select=='')?'*':$select).$join.$where;
			$results   = $db->getAll($sql);
			foreach ($results as $key => $value) 
			{
					$results[$key]['enterprises'] = $db->getOne("select * from sp_cost_jiesuan where eid ='".$value['eid']."' and month='".$nowmoth."' ");
						if(self::$ctfrom=='01060000' )//判断合同金额是否存在新表
						{
							//合同费用
							$ctList    = $db->get_row("select * from sp_certificate where eid = '".$value['eid']."' and first_date   between  '".$begin_time."' and '".$engin_time."' and status=01 ");	
							$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."'  order by  id");
							
						}else{

							$ctList    =  $db->get_row("select * from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");
							$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."' and cost_type ='".$ctList['audit_type']."' ");

						}
						if(!empty($ct_costList))
						{
						    $dk_type       =  $db->get_row("select * from sp_contract_cost_detail where cost_id ='".$ct_costList['id']."' and deleted= 0");
							$results[$key]['enterprises']['hetongjine']  = $ct_costList['cost'];
							$results[$key]['enterprises']['shishoujine'] = $dk_type['dk_cost'];
						}
						// //合同费用结束
						if(self::$ctfrom=='01060000')
						{
							//几标 按照发证日期筛选
							$results[$key]['couniso'] = $db->get_var("select count(*) from sp_certificate where eid = '".$value['eid']."' and first_date   between  '".$begin_time."' and '".$engin_time."' and deleted=0");
						}else{
							//几标 按照审核结束时间筛选
							$tid    =  $db->get_var("select t2.id from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");
							$results[$key]['couniso'] = $db->get_var("select count(*) from sp_project where tid = '".$tid."' and deleted=0");
						}
						
						//审核成本	
						$hrList = $db->getAll("select t1.* from sp_cost  t1 left join sp_hr t2  on t1.uid=t2.id where  t2.ctfrom='01000000' and t1.tid ='".$tid."'  and t1.status=1 and t2.deleted=0");
						$shenhechengben = array();
						$results[$key]['enterprises']['shenhechengben']  ='0';
						foreach ($hrList as  $hrs)$shenhechengben[]      = $hrs['zfy'];
						$results[$key]['enterprises']['shenhechengben']  = array_sum($shenhechengben);	
				}

		}

		return self::getArrayForResults(0,'',$results);

	}
	//结算单(已结算列表)
	public static function  jiesuansheet()
	{
		global $db;
		$join    =  $select = '';$where =' where 1';
		if(!empty(self::$month))
		{
			$nowmoth     = self::$month;
			$begin_time  = self::$month.'-01 00:00:00';//筛选
			$engin_time  = self::$month.'-31 23:59:59';
		}else{
			$nowmoth     = date('Y-m');
			$begin_time  = date('Y-m').'-01 00:00:00';//本月月初
			$engin_time  = date('Y-m').'-31 23:59:59';//本月月初
		}
		if(!empty(self::$ctfrom))
		{
			$ctfrom  =  "and ctfrom='".self::$ctfrom."'";
		}else{
			$ctfrom  = '';
		}
		//已结算的企业
		$results  = $db->getAll("select * from sp_cost_jiesuan where status=2 and month='".$nowmoth."'  ".$ctfrom." group by eid");

		$ineid = array();
		foreach ($results as $value)
		{
			$ineid[] = $value['eid'];
		}
		// $costJesuanList  = $db->getAll('select * from sp_cost_jiesuan where  month=\''.$nowmoth.'\' group by eid');
		// if(!empty($costJesuanList))
		// {
			if(!empty($ineid))
			{
				$ineid     = implode($ineid,',');
				$sql       = 'select %s from `sp_enterprises` t1';
				$select   .= ' t1.*';
				$where    .= ' and t1.eid in ('.$ineid.') and t1.deleted=0';
				$sql       = sprintf($sql,($select=='')?'*':$select).$join.$where;
				$results   = $db->getAll($sql);
				foreach ($results as $key => $value) 
				{
					$results[$key]['enterprises'] = $db->getOne("select * from sp_cost_jiesuan where eid ='".$value['eid']."' and month='".$nowmoth."' ");
						if(self::$ctfrom=='01060000' )//判断合同金额是否存在新表
						{
							//合同费用
							$ctList    = $db->get_row("select * from sp_certificate where eid = '".$value['eid']."' and first_date   between  '".$begin_time."' and '".$engin_time."' and status=01 ");	
							$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."'  order by  id");
							
						}else{

							$ctList    =  $db->get_row("select * from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");
							$ct_costList   =  $db->get_row("select * from sp_contract_cost t3 where ct_id = '".$ctList['ct_id']."' and cost_type ='".$ctList['audit_type']."' ");

						}
						if(!empty($ct_costList))
						{
						    $dk_type       =  $db->get_row("select * from sp_contract_cost_detail where cost_id ='".$ct_costList['id']."' and deleted= 0");
							$results[$key]['enterprises']['hetongjine']  = $ct_costList['cost'];
							$results[$key]['enterprises']['shishoujine'] = $dk_type['dk_cost'];
						}
						// //合同费用结束
						if(self::$ctfrom=='01060000')
						{
							//几标 按照发证日期筛选
							$results[$key]['couniso'] = $db->get_var("select count(*) from sp_certificate where eid = '".$value['eid']."' and first_date   between  '".$begin_time."' and '".$engin_time."' and deleted=0");
						}else{
							//几标 按照审核结束时间筛选
							$tid     =  $db->get_var("select t2.id from sp_task t2 where t2.eid ='".$value['eid']."' and t2.te_date   between  '".$begin_time."' and '".$engin_time."' and t2.audit_type in(1003,1004,1005) and t2.status=3");
							if(!empty($tid))
							{
								$nowiso  =	$db->get_var("select count(*) from sp_project where tid = '".$tid."' and deleted=0");
							}else{
								$nowiso = $db->get_var("select count(*) from sp_certificate where eid = '".$value['eid']."' and first_date   between  '".$begin_time."' and '".$engin_time."' and deleted=0");

							}
							$results[$key]['couniso'] = $nowiso;
							
						}
						
						//审核成本	
						$hrList = $db->getAll("select t1.* from sp_cost  t1 left join sp_hr t2  on t1.uid=t2.id where  t2.ctfrom='01000000' and t1.tid ='".$tid."'  and t1.status=1 and t2.deleted=0");
						$shenhechengben = array();
						$results[$key]['enterprises']['shenhechengben']  ='0';
						foreach ($hrList as  $hrs)$shenhechengben[]      = $hrs['zfy'];
						$results[$key]['enterprises']['shenhechengben']  = array_sum($shenhechengben);	
				}
			}

		// }
		
		
		return self::getArrayForResults(0,'',$results);

	}

	//结算单保存
	public static function jiesuansave( $data = array() )
	{	


		if( (empty($data['ctfrom'])||empty($data['month']))&&empty($data['id']) )return self::getArrayForResults('1','失败');

		global $db;$results = array();

		//结转保存
		if(!empty($data['month']) )
		{
			for ($i=0,$count=count($data['ctfrom']); $i < $count; $i++)
			{ 
				$sql  = 'select * from sp_cost_jiesuan where  ctfrom=\''.$data['ctfrom'][$i].'\' and  month=\''.$data['month'][$i].'\' and  eid=\''.$data['eid'][$i].'\'';
				$cpt  = $db->getOne($sql);
				if( empty($cpt) )
				{
					$save = array(
								'ctfrom'             => $data['ctfrom'][$i],
								'month'              => $data['month'][$i],
								'eid'                => $data['eid'][$i],
								'jiezhuan'           => $data['jiezhuan'],
								'yufukuan'           => $data['yufukuan'],
								'koushui'            => $data['koushui'],
								'ep_amount'          => $data['ep_amount'][$i],
								'jiesuandian'        => $data['jiesuandian'][$i],
								'jiesuanjia'         => $data['jiesuanjia'][$i],
								'qitafeiyong'        => $data['qitafeiyong'][$i],
								'jiesuanzongjine'    => $data['jiesuanzongjine'][$i],
								'shenhechengben'     => $data['shenhechengben'][$i],
								'hetongjine'         => $data['hetongjine'][$i],
								'shishoujine'        => $data['shishoujine'][$i],
								'edingshuishou'      => $data['edingshuishou'][$i],
								'fankuanjine'        => $data['fankuanjine'][$i],
								'jiesuanjine'        => $data['jiesuanjine'][$i],
								'status'             => '2',
								'createTime'         => date('Y-m-d h:i:s'),

							);
					
					$results[] =  $db->insert('cost_jiesuan',$save,false);
				}else
				{
					$save = array(
								'jiezhuan'           => $data['jiezhuan'],
								'yufukuan'           => $data['yufukuan'],
								'koushui'            => $data['koushui'],
								'jiesuandian'        => $data['jiesuandian'][$i],
								'jiesuanjia'         => $data['jiesuanjia'][$i],
								'qitafeiyong'        => $data['qitafeiyong'][$i],
								'jiesuanzongjine'    => $data['jiesuanzongjine'][$i],
								'shenhechengben'     => $data['shenhechengben'][$i],
								'hetongjine'         => $data['hetongjine'][$i],
								'shishoujine'        => $data['shishoujine'][$i],
								'edingshuishou'      => $data['edingshuishou'][$i],
								'fankuanjine'        => $data['fankuanjine'][$i],
								'jiesuanjine'        => $data['jiesuanjine'][$i],
								'modifyTime'         => date('Y-m-d h:i:s'),
							);
					$results[] = $db->update('cost_jiesuan',$save,array('id'=>$cpt['id']),false);
				}
			}
		}
		return self::getArrayForResults('0','',$results);
	}

	/**
	 * 获取上个月月份
	 * @param  string $date [description]
	 * @return [type]       [description]
	 */
	public static function getlastMonth($date='')
	{
		if( !empty($date) )
		{
			$date = strtotime($date);
			$date = date('Y-m',strtotime(date('Y',$date).'-'.(date('m',$date)-1)));
		}else if( empty(static::$month) )
		{
			$date = date('Y-m-d H:i:s');
			$date = strtotime($date);
			$date = date('Y-m',strtotime(date('Y',$date).'-'.(date('m',$date)-1)));
			static::$month = $date;
		}else
		{
			$date = static::$month;
		}
	    return $date;
	}

	/**
	 * 获得组装后的结果数组
	 * @param  integer $errorCode 错误码，0为正常
	 * @param  string  $errorStr  错误描述
	 * @param  array   $result    返回数据
	 * @param  array   $extraInfo 返回额外数据
	 * @return array             结果数组
	 */

		

	public static function getArrayForResults($errorCode=0,$errorStr='',$result = array())
	{
		
		$data = array(
					'errorCode' => $errorCode,
					'errorStr'  => $errorStr,
					'results'   => $result
			);

		return $data;
	}

	/**
	 * 判断结果数组是否正确获得结果
	 * @param  array  $tmpResult 结果数组
	 * @return boolean            是否正确获得
	 */
	public static function isResults($tmpResult=null)
	{
		return (is_array($tmpResult) && array_key_exists('errorCode',$tmpResult) );
	}

	/**
	 * 判断结果数组是否正确获得结果
	 * @param  array  $tmpResult 结果数组
	 * @return boolean            是否正确获得
	 */
	public static function isResultsOK($tmpResult=null)
	{
		return (self::isResults($tmpResult) && $tmpResult['errorCode']==0);
	}
	
	//计算人日
	public static function timediff( $begin_time, $end_time )
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
	public static function getDateFromRange($startdate, $enddate)
	{
		// 保存每天日期
	    $date = array();
	    $stimestamp = strtotime($startdate);
	    $etimestamp = strtotime($enddate);
	    // 计算日期段内有多少天
	    $days = ($etimestamp-$stimestamp)/86400;
	    for($i=0; $i<$days; $i++)
	    {
	        $date[] = date('Y-m-d', $stimestamp+(86400*$i));
	    }
	    return $date;
	}

}