<?php
//导数据类
class db_imp
{
    public $obj_from = '';
    public $audit_job_arr = array('专职' => '1', '' => 9);
    function get_from()
    {
        $db_source = load('db.mysql', true);
        $db_source->connect(get_option('db.db_host'), get_option('db.db_user'), get_option('db.db_pwd'), 'bscc_source');
        //初始化
        $db_source->_pre    = '';
        $db_source->db_type = '';
        $this->obj_from     = $db_source;
        set_time_limit(0);
        @ini_set('memory_limit', '128M');
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
开始导数据';
        echo '<br>';
        return $db_source;
    }
    //过滤数据
    function filter($str)
    {
        if (is_array) {
            foreach ($str as $key => $val) {
                if (!$val)
                    unset($str[$key]);
                $str[$key] = self::filter($val);
            }
        } else {
            $str = trim($str);
        }
        return $str;
    }
}