<?php
//模型基类
abstract class model
{
    protected $db; //数据库操作对象
    protected $_pre = 'sp_'; //数据库前缀
    protected $_tb = ''; //表名
    protected $_pk = ''; //数据表主键
    function __construct()
    {
        global $db;
        $this->db = $db; //变成内部变量 
    }
}