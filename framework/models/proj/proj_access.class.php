<?php
//评定模型： 对应多个表：评分表，还有评分配置表
class proj_access extends model
{
    //保存评分记录-针对多个合同项目评分,根据合同项目获取评分项目
    function save_more($data)
    {
        //本次没有评分
        if (!$data)
            return false;
        foreach ($data as $cti_id => $results) {
            //获取当前配置评分项信息 
            foreach ($results as $id => $item_active) {
                $access_set_ver_info = $this->db->find_one('access_set_ver', array(
                    'id' => $id
                ));
                $new_result          = array(
                    'cti_id' => $cti_id,
                    'name' => $access_set_ver_info['name'],
                    'access_set_ver_id' => $id,
                    'item' => $access_set_ver_info['item']
                    //    'item_active' => $item_active
                );
                //是否打分
                if (!$item_active['num']) {
                    $new_result['item_active'] = $item_active['active'];
                } else {
                    $tmp_active                = explode('-', $item_active['active']);
                    $new_result['item_active'] = $tmp_active[0] . '-' . $item_active['num'];
                }
                //判断系统配置中是否已经评定
                $access_result_id = $this->db->getField('access_result', 'id', array(
                    'cti_id' => $cti_id,
                    'access_set_ver_id' => $id
                ));
                if (!$access_result_id and $new_result['name']) {
                    $this->db->insert('access_result', $new_result);
                } else {
                    $this->db->update('access_result', $new_result, array(
                        'id' => $access_result_id
                    ));
                }
            }
        }
    }
}