<?php
/*
 * 任务审批
 */
    $tid = (int)getgp('tid');
    $approval_date = getgp('approval_date');
    $approval_note = getgp('approval_note');
    if (!$approval_date) print_json(array(
        'state' => 'no',
        'msg' => '请填写审批日期！'
    ));
    $task = load('task');
    $row = $task->get(array(
        'id' => $tid
    ));
    $audit = load('audit');
    if ($row) {
        $params = array();
        $query = $db->query('SELECT p.id,p.eid,p.ct_id,p.cti_id,p.st_num,p.audit_type,p.audit_ver,t.tb_date,t.te_date,ct.is_site FROM sp_project p JOIN sp_task t on p.tid=t.id JOIN sp_contract ct ON p.ct_id=ct.ct_id where p.tid='.$tid);
        while ($rt = $db->fetch_array($query)) {
            $params[] = array(
                     'eid'        => $rt['eid']
                    ,'ct_id'      => $rt['ct_id']
                    ,'cti_id'     => $rt['cti_id']
                    ,'tid'        => $tid
                    ,'pid'        => $rt['id']
                    ,'tixi'       => $rt['audit_ver']
                    ,'type'       => $rt['audit_type']
                    ,'isGoTo'     => $rt['is_site']
                    ,'startTime'  => $rt['tb_date']
                    ,'endTime'    => $rt['te_date']
                    ,'totalDay'   => $rt['st_num']
                    ,'del'     => 1
                    ,'createTime' => date('Y-m-d H:is')
                );
            $audit->edit($rt['id'], array(
	            'status' => 3,//已审批
                'redata_status' => '0'
            ));
        }
        
        $status = ( count($params)<=5 )?count($params):5;
        foreach ($params as $key => $value) {
            $sql = "insert into sp_shytj(`eid`,`ct_id`,`cti_id`,`tid`,`pid`,`tixi`,`type`,`isGoTo`,`startTime`,`endTime`,`totalDay`,`del`,`createTime`) value('".implode('\',\'',$value)."')";
            $db -> query($sql);$id = mysql_insert_id();

            $sql = 'select hr.id uid,hr.name name,stat.role teamLeater,hrq.qua_type leaterType,stat.audit_code zhuanyeType,stat.witness witness from sp_task_audit_team stat left join sp_hr_qualification hrq on stat.uid=hrq.uid left join sp_hr hr on stat.uid=hr.id where stat.audit_type='.$value['type'].' and stat.tid='.$value['tid'].' group by hr.id';

            $query = $db -> query($sql);
            while ( $rts = $db->fetch_array($query) ) {
                //初始费用
                $auditMoney = 220;
                $typeMoney  = 0;

                //组长初始费用
                if( $rts['teamLeater'] == '1001' ){
                    $auditMoney = 280;
                    //审核类型
                    switch ( $value['type'] ) {
                        case '1001'://初审
                        case '1003'://二阶段
                            $typeMoney = 200;
                            break;

                        case '1002'://一阶段
                            $typeMoney = 200;
                            if( $value['isGoTo'] ==0 )$typeMoney =0;
                            break;

                        case '1004'://监一
                        case '1005'://监二
                        case '1006'://监三
                            $typeMoney = 100;
                            break;

                        case '1007'://再认证
                            $typeMoney = 200;
                            break;
                        
                        default://其他
                            break;
                    }
                }

                //多体系初始费用
                $auditMoney += ($status-1)*30;
                $typeMoney  += ($status-1)*50;

                //专业 +30
                if( !empty($rts['zhuanyeType']) && $rts['zhuanyeType'] !=0 )$auditMoney += 30;

                //见证
                if($rts['witness']!=0){
                    switch ($rts['teamLeater']) {
                        case '1001':
                            $auditMoney += 100;
                            break;
                        
                        default:
                            $auditMoney += 50;
                            break;
                    }
                }

                //审核员类型
                switch ($rts['leaterType']) {
                    case '01'://高级+10
                        $auditMoney += 10;
                        break;

                    case '03'://实习审核员没有钱
                        $auditMoney = 0;
                        break;

                    case '04'://技术专家每天200
                        $auditMoney = 200;
                        break;

                    default:
                        # code...
                        break;
                }

                $days = (strtotime($value['endTime']) - strtotime($value['startTime']))/86400;
                if(!is_int($days))$days=floor($days)+1;
                $totalMoney = ( $auditMoney + $typeMoney )*$days;

                $params[$key]['detail'][] = array(
                     'sid'         => $id
                    ,'uid'         => $rts['uid']
                    ,'name'        => $rts['name']
                    ,'teamLeater'  => $rts['teamLeater']
                    ,'leaterType'  => $rts['leaterType']
                    ,'zhuanyeType' => $rts['zhuanyeType']
                    ,'witness'     => $rts['witness']
                    ,'auditMoney'  => $auditMoney
                    ,'typeMoney'   => $typeMoney
                    ,'totalMoney'  => $totalMoney
                    ,'days'        => $days
                    ,'status'      => $status
                    ,'createTime'  => date('Y-m-d H:i:s')
                );
            }
            
            
        }

        $sql = 'insert into sp_shytj_detail(`sid`,`uid`,`name`,`teamLeater`,`leaterType`,`zhuanyeType`,`witness`,`auditMoney`,`typeMoney`,`totalMoney`,`days`,`status`,`createTime`) values';
        foreach ($params as $value) {
            foreach ($value['detail'] as $detail) {
                $sql .= '(\''.implode('\',\'',$detail).'\'),';
            }
        }
        $sql = substr($sql,0,strlen($sql)-1);

        $db -> query($sql);
        $task->edit($tid, array(
            'status' => 3,//已审批
			'approval_uid' => current_user('uid'),
			'approval_user' => current_user('name'),
            'approval_date' => $approval_date,
            'approval_note' => $approval_note,
        ));
		$sms_arr=array("eid"=>$row['eid'],
						"temp_id"=>$tid,
						"is_sms"=>'0',
						"flag"=>4);
		$sms=load("sms");
		$sms_info=$sms->get(array("temp_id"=>$sms_arr[temp_id],"flag"=>4));
		if($sms_info[id])
			$sms->edit($sms_info[id],$sms_arr);
		else
			$sms->add($sms_arr);
    }
	log_add($row['eid'],0,'任务审批，任务ID：'.$tid);
    print_json(array(
        'state' => 'ok',
        'msg' => 'success'
    ));