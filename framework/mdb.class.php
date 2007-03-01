<?php
//access 数据库 文件操作类
class mdb
{
    //生成access格式报表 
    public $report_tpl_dir = 'application/imp/source/'; //报表数据库模板目录
    public $report_runtime_dir = 'application/imp/'; //报表生成的临时文件目录 
    //导数据acces格式数据源  
    public $_db = ''; //数据库名
    public $_tb = ''; //数据库表名
    private $conn = ''; //数据库链接
    private $debug = '0'; //php链接acces模式： ADO 链接数据库 PDO链接数据库 等多种数据库链接方式
    //链接数据库
    function conn($db)
    {
        if ($this->debug) { //ADO连接access2007数据库
            // $constr     = 'DRIVER={Microsoft Access Driver (*.mdb)};DBQ='.$db;
            $constr     = "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=" . realpath($database);
            $this->conn = new com("ADODB.Connection");
            $this->conn->Open($constr);
        } else { //pdo连接数据库
            $this->conn = new PDO("odbc:driver={microsoft access driver (*.mdb)};dbq=" . $db);
        }
    }
    function imp_init($_db, $_tb)
    {
        $this->_db = $_db;
        $this->_tb = $_tb;
        $tpl_dir   = ROOT . '/' . $this->report_tpl_dir . $this->_db . '.mdb';
        $this->conn($tpl_dir);
    }
    ///////////////////////////////导出mdb报表////////////////////////////////////
    //初始化：文件名与数据库名称，并复制
    function export_init($_db, $_tb)
    {
        $this->_db = $_db;
        $this->_tb = $_tb;
        //不限制执行时间
        set_time_limit(0);
        //模板数据库
        $tpl_dir                  = $this->report_tpl_dir . $this->_db . '.mdb';
        //获取临时路径 
        $this->report_runtime_dir = ROOT . '/' . $this->report_runtime_dir . $this->_db . '_temp.mdb';
        if (file_exists($this->report_runtime_dir)) {
            unlink($this->report_runtime_dir);
        }
        //复制模板数据库
        copy($tpl_dir, $this->report_runtime_dir);
        $this->conn($this->report_runtime_dir);
    }
    //2.插入数据
    function insert($map)
    {
        //更改编码与特殊字符处理
        foreach ($map as $k => $v) {
            if (!$v) {
                unset($map[$k]);
            }
            $map[$k] = iconv('UTF-8', 'GBK', str_replace("'", '’', $v));
            unset($v);
        }
        $sql = "INSERT INTO {$this->_tb}( " . implode(', ', array_keys($map)) . " ) VALUES ( '" . implode("','", $map) . "')";
        //	
        if ($this->debug) {
            //echo $sql;
            $query = $this->conn->Execute($sql);
        } else {
            $query = $this->conn->exec($sql);
            if (!$query) {
                echo $sql;
                echo '<br>';
            }
        }
    }
   
}