<?php
$tid = getgp('tid');

$sql = "select * from sp_task_audit_team where tid=".$tid." and deleted='0'";
$r_info = $db->getALL($sql);

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
}

//获取所有数据
$r_info = array();
foreach ($params as $value) {
    $sql = 'select hr.id uid,hr.name name,stat.role teamLeater,hrq.qua_type leaterType,stat.audit_code zhuanyeType,stat.witness witness from sp_task_audit_team stat left join sp_hr_qualification hrq on stat.uid=hrq.uid left join sp_hr hr on stat.uid=hr.id where stat.iso=hrq.iso and stat.deleted=0 and hrq.deleted=0 and hr.deleted=0 and stat.audit_type='.$value['type'].' and stat.tid='.$value['tid'].' and stat.pid='.$value['pid'];
    $query = $db -> query($sql);
    while ( $rts = $db->fetch_array($query) ) {
        $rts['status']=( count($params)<=5 )?count($params):5;
        $r_info[$rts['uid']][$value['tixi']]=array_merge($value,$rts);
    }
}


//替换相应数据 例：如果三个体系都是组长 只要有一个为高级审核员 那两外两个也为高级审核员
foreach ($r_info as $user => $tixi) {
    $leaterType = 02;
    foreach ($tixi as $ke => $value) {
        if($value['teamLeater']!=01)continue;
        if($value['leaterType']<$leaterType)$leaterType=$value['leaterType'];
    }

    if($leaterType != 02){
        foreach ($tixi as $ke => $value) {
            if($value['teamLeater']!=01)continue;
            $r_info[$user][$ke]['leaterType']=$leaterType;
        }
    }
}

/**--微信模板需信息--**/
$arr_wxSend = array();
foreach ($r_info as $user => $tixi) {
    $sql = "select unionToken from sp_unionlogin where userID=".$user." and unionType=4 and status=1";
    $openid = $db->get_var($sql);
    if(empty($openid))continue;
    foreach ($tixi as $key => $value) {
        if(!empty($arr_wxSend[$k][$user]))continue;
        $sql = "select ep_name,areaaddr from sp_enterprises where eid=".$value['eid'];
        $epInfo = $db->getOne($sql);
        $arr_wxSend[$user]=array(
                     'name'      => $value['name']
                    ,'ep_name'   => $epInfo['ep_name']
                    ,'areaaddr'  => $epInfo['areaaddr']
                    ,'startTime' => $value['startTime']
                    ,'endTime'   => $value['endTime']
                    ,'openid'    => $openid
                    ,'type'      => $value['type']
                    ,'tixi'      => $value['tixi']
                );
    }
}
// echo "<pre />";
// print_r($arr_wxSend);exit;
/**--微信模板需信息--**/
$weObj = load('Wechat',$arrOptions);
$weObj->checkAuth();
$data = '{
            "touser":"%s",
            "template_id":"7qywxhkZYITn3CuQyUwKqS0eGnKaIXHE2cd9x_hD1H4",
            "topcolor":"#FF0000",
            "data":{
                "first": {
                    "value":"%s您好，有新的审核任务如下",
                    "color":"#173177"
                    },
                "keyword1":{
                    "value":"%s",
                    "color":"#173177"
                    },
                "keyword2":{
                    "value":"%s",
                    "color":"#173177"
                    },
                "keyword3":{
                    "value":"%s",
                    "color":"#173177"
                    },
                "remark":{
                    "value":"如有需要请联系010-88689817",
                    "color":"#173177"
                    }
            }
        }';
foreach ($arr_wxSend as $user => $v) {
    $weObj->sendTemplateMessage( sprintf( $data,$v['openid'],$v['name'],$v['ep_name'].'-'.$arr_audit_type[$v['type']],$v['startTime'].'至'.$v['endTime'],$v['areaaddr'] ) );
}
//更改是否点击推送的状态列 if_push：状态列 默认是1
$arr1 = $db->getALL("select * from sp_task where id=".$tid." and deleted=0");
foreach ($arr1 as $value) 
{
    $id        = $value['id'];
    $db        = "UPDATE sp_task SET if_push = '2' WHERE id = '".$id."'";
    $query     = mysql_query($db);
}

$REQUEST_URI = '?c=task&a=list&status=3';
showmsg( 'success', 'success', $REQUEST_URI );
?>
