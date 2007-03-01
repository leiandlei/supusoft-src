<?php
/*
企业模型
*/
class enterprise extends model
{
    public $_tb = 'enterprises'; //企业表名
    public $_pk = 'eid'; //企业表主键
    function add($args)
    {
        global $db;
        $eid = $db->insert('enterprises', $args);
        //处理附加属性
        if (getgp('meta'))
            $metas = array_map('strip_tags', getgp('meta'));
        if ($metas) {
            $ADDSQL = array();
            foreach ($metas as $meta => $value) {
                $ADDSQL[] = "( '$eid', '$meta', '$value', 'enterprise' )";
            }
            if ($ADDSQL) {
                $sql = "INSERT INTO sp_metas_ep ( ID, meta_name, meta_value, used ) VALUES " . implode(',', $ADDSQL);
                $sql .= " ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
                $db->query($sql);
            }
        }
        return $eid;
    }
    //获取企业列表:企业列表查询，合同登记列表
    function ep_list()
    {
    }
    //用例：合同列表查询，合同登记列表
    function ep_page_list($args)
    {
        //拼接系统默认搜索条件
    }
    function _list_sql()
    {
    }
    //获取企业信息
    function get($args, $meta = true)
    {
        if (empty($args) || !is_array($args))
            return false;
        global $db;
        $where  = $db->sqls($args, 'AND');
        $result = $db->get_row("SELECT * FROM sp_{$this->_tb} WHERE $where");
        $metas  = ($meta) ? $this->meta($result['eid']) : array();
        if ($metas)
            $result = array_merge($result, $metas);
        return $result;
    }
    function edit($eid, $args)
    {
        if (empty($eid))
            return false;
        global $db;
        $args     = parse_args($args);
        $old_info = $this->get(array(
            'eid' => $eid
        ), false);
        unset($old_info['update_date'], $old_info['update_uid']);
        $n_arr = array_diff_assoc($args, $old_info);
        $o_arr = array();
        foreach (array_keys($n_arr) as $key) {
            $o_arr[$key] = $old_info[$key];
        }

        if ($n_arr) {
            $n_arr['update_date'] = current_time('mysql');
            $n_arr['update_uid']  = current_user('uid');
            $db->update('enterprises', $n_arr, array(
                'eid' => $eid
            ));
        }
        // if (isset($n_arr['ctfrom'])) {
        //     $ctfrom = $n_arr['ctfrom'];
        //     //同步合同来源
        //     $db->update('contract', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('contract_item', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('project', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('task', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('task_auditor', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('assess', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('certificate', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('ifcation', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        //     $db->update('attachments', array(
        //         'ctfrom' => $ctfrom
        //     ), array(
        //         'eid' => $eid
        //     ));
        // }
        //处理附加属性
        $metas = array_map('strip_tags', getgp('meta'));
        if ($metas) {
            foreach ($metas as $meta => $value) {
                $this->meta($eid, $meta, $value);
            }
        }
    }
    function del($args)
    {
        if (empty($args) || !is_array($args))
            return false;
        global $db;
        $args = parse_args($args);
        $eid  = $args['eid'];
        $db->update('enterprises', array(
            'deleted' => 1
        ), $args);
    }
    function meta($eid, $meta_name = '', $meta_value = '')
    {
        if (empty($eid))
            return false;
        global $db;
        $result = '';
        if ($meta_name && $meta_value) {
            $old_metas = $this->meta($eid);
            if (isset($old_metas[$meta_name])) {
                if ($meta_value != $old_metas[$meta_name]) {
                    $db->update("metas_ep", array(
                        "meta_value" => $meta_value
                    ), array(
                        "ID" => $eid,
                        "meta_name" => $meta_name
                    ));
                }
            } else {
                $db->insert("metas_ep", array(
                    "meta_value" => $meta_value,
                    "ID" => $eid,
                    "meta_name" => $meta_name,
                    "used" => "enterprise"
                ));
            }
        } elseif ($meta_name) {
            $result = $db->get_var("SELECT meta_value FROM sp_metas_ep WHERE ID = '$eid' AND meta_name = '$meta_name' AND used = 'enterprise'");
        } else {
            $result = array();
            $query  = $db->query("SELECT * FROM sp_metas_ep WHERE ID = '$eid' AND used = 'enterprise'");
            while ($rt = $db->fetch_array($query)) {
                $result[$rt['meta_name']] = $rt['meta_value'];
            }
        }
        return $result;
    }
    function union_count($eid, $number = 0)
    {
        return $this->_count($eid, 'union', $number);
    }
    function site_count($eid, $number = 0)
    {
        return $this->_count($eid, 'site', $number);
    }
    function _count($eid, $field, $number = 0)
    {
        if (empty($eid) || empty($field))
            return false;
        global $db;
        $db->query("UPDATE sp_enterprises SET {$field}_count = {$field}_count + $number WHERE eid = '$eid'");
        return true;
    }
}
?>