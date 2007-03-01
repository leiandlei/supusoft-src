<?php
//人员代码类 
class hr_code extends model
{
    public $_tb = 'hr_audit_code';
    //新增人员代码: 导数据使用
    function add($args)
    {
        //验证小类是否已经添加 
        $id = $this->db->getField($this->_tb, 'id', $args);
        if ($id)
            return $id;
        //插入数据
        $this->db->insert($this->_tb, $args);
        return $id;
    }
    //获取单个信息
    function get($args)
    {
    }
}