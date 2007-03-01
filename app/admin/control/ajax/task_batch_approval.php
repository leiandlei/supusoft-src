<?php
/*
*任务审批（批量）
*/

    $approval_date = getgp('approval_date');
    $tids = explode(',', getgp('tids'));
    $tids = array_unique($tids);
    if (!$tids) print_json(array(
        'state' => 'no',
        'msg' => '请选择要审批的项目！'
    ));
    if (!$approval_date) print_json(array(
        'state' => 'no',
        'msg' => '请填写审批日期！'
    ));
    $audit = load('audit');
    //审核项目 状态变更
    $params = $status = array();
    $query = $db->query("SELECT id,tid FROM sp_project WHERE tid IN (" . implode(',', $tids) . ")");
    while ($rt = $db->fetch_array($query)) {
        $tid = $rt['tid'];
        $sql = 'SELECT p.id,p.eid,p.ct_id,p.cti_id,p.st_num,p.audit_type,p.audit_ver,t.tb_date,t.te_date,ct.is_site FROM sp_project p JOIN sp_task t on p.tid=t.id JOIN sp_contract ct ON p.ct_id=ct.ct_id where p.tid='.$tid;
        $query_a = $db->query($sql);
        while ($rt_a = $db->fetch_array($query_a)) {
            $params[$tid][$rt_a['audit_ver']] = array(
                     'eid'        => $rt_a['eid']
                    ,'ct_id'      => $rt_a['ct_id']
                    ,'cti_id'     => $rt_a['cti_id']
                    ,'tid'        => $tid
                    ,'pid'        => $rt_a['id']
                    ,'tixi'       => $rt_a['audit_ver']
                    ,'type'       => $rt_a['audit_type']
                    ,'isGoTo'     => $rt_a['is_site']
                    ,'startTime'  => $rt_a['tb_date']
                    ,'endTime'    => $rt_a['te_date']
                    ,'totalDay'   => $rt_a['st_num']
                    ,'del'     => 1
                    ,'createTime' => date('Y-m-d H:is')
                );
        }
        $audit->edit($rt['id'], array(
            'status' => 3
        ));
    }
    //获取所有数据
    $r_info = array();
    foreach ($params as $k => $tixi) {
        foreach ($tixi as $key => $value) {
            $sql = "insert into sp_shytj(`eid`,`ct_id`,`cti_id`,`tid`,`pid`,`tixi`,`type`,`isGoTo`,`startTime`,`endTime`,`totalDay`,`del`,`createTime`) value('".implode('\',\'',$value)."')";
            $db -> query($sql);$id = mysql_insert_id();
            $sql = 'select hr.id uid,hr.name name,stat.role teamLeater,hrq.qua_type leaterType,stat.audit_code zhuanyeType,stat.witness witness from sp_task_audit_team stat left join sp_hr_qualification hrq on stat.uid=hrq.uid left join sp_hr hr on stat.uid=hr.id where stat.iso=hrq.iso and stat.deleted=0 and hrq.deleted=0 and hr.deleted=0 and stat.audit_type='.$value['type'].' and stat.tid='.$value['tid'].' and stat.pid='.$value['pid'];
            $query = $db -> query($sql);
            while ( $rts = $db->fetch_array($query) ) {
                $rts['status']=( count($tixi)<=5 )?count($tixi):5;;
                $rts['sid']=$id;
                $r_info[$k][$rts['uid']][$key]=array_merge($value,$rts);
            }
        }
    }

    //替换相应数据 例：如果三个体系都是组长 只要有一个为高级审核员 那两外两个也为高级审核员
    foreach ($r_info as $key => $e) {
        foreach ($e as $user => $tixi) {
            
            $leaterType = 02;
            foreach ($tixi as $ke => $value) {
                if($value['teamLeater']!=01)continue;
                if($value['leaterType']<$leaterType)$leaterType=$value['leaterType'];
            }

            if($leaterType != 02){
                foreach ($tixi as $ke => $value) {
                    if($value['teamLeater']!=01)continue;
                    $r_info[$key][$user][$ke]['leaterType']=$leaterType;
                }
            }

        }
    }

    foreach ($r_info as $k => $e) {
        foreach ($e as $user => $tixi) {
            foreach ($tixi as $key => $value) {
                //几个体系
                $statu      = empty($value['status'])?1:$value['status'];
                //初始费用
                $auditMoney = 220;
                $typeMoney  = 0;

                //组长初始费用
                if( $value['teamLeater'] == '01' ){
                    $auditMoney = 280;
                    //审核类型
                    switch ( $value['type'] ) {
                        case '1001'://初审
                        //case '1003'://二阶段
                            $typeMoney = 200;
                            break;

                        case '1002'://一阶段
                            $typeMoney = 200;
                            //如果去现场 文审费用为0
                            if( $value['isGoTo'] ==1 )$typeMoney =0;
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
                    //多体系文审
                    $typeMoney  += ($statu-1)*50;

                    //如果去现场 文审费用为0
                    if($value['type']=='1002' && $value['isGoTo'] ==1)$typeMoney =0;

                    //如果是二阶段 文审为0
                    if($value['type']=='1003')$typeMoney =0;
                }
                //多体系初始费用
                $auditMoney += ($statu-1)*30;

                //专业 +30
                if( !empty($value['zhuanyeType']) && $value['zhuanyeType']!=0 ){
                    $auditMoney += 30;
                }

                //见证
                if($value['witness']!=0){
                    switch ($value['teamLeater']) {
                        case '01':
                            $auditMoney += 100;
                            break;
                        
                        default:
                            $auditMoney += 50;
                            break;
                    }
                }

                //审核员类型
                switch ($value['leaterType']) {
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

                $days = abs((strtotime($value['endTime']) - strtotime($value['startTime'])))/86400;
                if(!is_int($days)){
                    if( $days>0 && $days<=0.186 ){
                        $days = 0.5;
                    }elseif($days>0.186 && $days<1){
                        $days = 1;
                    }elseif( $days <= (floor($days) + 0.186)){
                        $days = floor($days) + 0.5;
                    }else{
                        $days = floor($days) + 1;
                    }
                }

                if($value['type']=='1002' && $value['isGoTo'] ==0){
                    $days=0;//没有去现场 现场人日为0
                    $auditMoney='0.00';
                }
                $totalMoney = $auditMoney*$days+$typeMoney;
                $info_add[] = array(
                     'sid'         => $value['sid']
                    ,'uid'         => $value['uid']
                    ,'name'        => $value['name']
                    ,'teamLeater'  => $value['teamLeater']
                    ,'leaterType'  => $value['leaterType']
                    ,'zhuanyeType' => $value['zhuanyeType']
                    ,'witness'     => $value['witness']
                    ,'auditMoney'  => $auditMoney
                    ,'typeMoney'   => $typeMoney
                    ,'totalMoney'  => $totalMoney
                    ,'days'        => $days
                    ,'status'      => $statu
                    ,'createTime'  => date('Y-m-d H:i:s')
                );
            }
        }  
    }

    $sql = 'insert into sp_shytj_detail(`sid`,`uid`,`name`,`teamLeater`,`leaterType`,`zhuanyeType`,`witness`,`auditMoney`,`typeMoney`,`totalMoney`,`days`,`status`,`createTime`) values';
    foreach ($info_add as $detail) {
        $sql .= '(\''.implode('\',\'',$detail).'\'),';
    }
    $sql = substr($sql,0,strlen($sql)-1);
    $db -> query($sql);

    //审核任务 状态更更
    foreach($tids as $tid){
        $db->update('task', array(
                'status' => 3,//已审批
                'approval_uid' => current_user('uid'),
                'approval_user' => current_user('name'),
                'approval_date' => $approval_date,
                // 'approval_note' => $approval_note,
        ) , array(
            'id' => $tid
        ));
        $eid=$db->get_var("SELECT eid FROM `sp_task` WHERE `id` = '$tid' ");
        $sms_arr=array("eid"=>$eid,
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

    echo json_encode(array(
        'state' => 'ok',
        'msg' => 'success'
    ));exit;