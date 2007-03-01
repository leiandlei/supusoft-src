<?php
$data = $db_source->find_results('hr');
$data = $mdl_imp->filter($data);
foreach ($data as $v) {
    //过滤
    unset($v['a3'], $v['a4'], $v['a5'], $v['a6'], $v['a14'], $v['a7'], $v['a8'], $v['a9'], $v['a10'], $v['a11'], $v['a20']);
    if (!$v['a2'])
        continue;
    if ($v['id'] == 1)
        continue;
    $v         = $mdl_imp->filter($v);
    //补充人员信息
    $user_data = array(
        'name' => $v['a2'],
        'tel' => $v['a16'],
        'phone' => $v['a17'],
        'audit_job' => $mdl_imp->audit_job_arr[$v['18']]
    );
    $uid       = get_uid($user_data, 1);
    //人员资格
    $qua_data  = $v['a12'];
    $qua_data  = str_replace(array(
        '生产型',
        '生产',
        '销售',
        '服务',
        '型企业',
        '企业',
        '型',
        '生',
        ' ',
        '、：'
    ), ';', $qua_data);
    $qua_data  = str_replace(array(
        '；',
        '，',
        ',',
        '、',
        '：',
        '企业',
        '型',
        '生',
        ' '
    ), array(
        ';',
        ';',
        ';',
        ';',
        ';',
        ';'
    ), $qua_data);
    $qua_id    = $db->getField('hr_qualification', 'id', array(
        'uid' => $uid,
        'qua_type' => array(
            '01',
            '02',
            '03'
        )
    ));
    if (!$qua_id) {
        $qua_id   = $db->insert('hr_qualification', array(
            'uid' => $uid,
            'iso' => 'C02',
            'qua_type' => '02'
        ));
        $qua_type = '02';
    } else {
        $qua_type = $db->getField('hr_qualification', 'qua_type', array(
            'uid' => $uid,
            'qua_type' => array(
                '01',
                '02',
                '03'
            )
        ));
    }
    if ($qua_data) {
        $qua_arr = explode(';', $qua_data);
        foreach ($qua_arr as $code_k => $code) {
            if (!$code)
                continue;
            $new_code = array(
                'use_code' => $code,
                'uid' => $uid,
				'ctfrom'=>'01000000',
                'iso' => 'C02',
                'qua_id' => $qua_id,
                'qua_type' => $qua_type
            );
            load('hr.code')->add($new_code);
        }
    }
}