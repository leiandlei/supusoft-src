<?php
/**
 *  DAO 类： 包括主数据库与导数据操作
 */
class db_mysql
{
    public $dbh = null; 
	public $_tbl;
    public $_pre = 'sp_'; //导数据会更改
    public $insert_id;
    public $query_arr = array();
    public $rows_affected = 0;
	public $db_type='db'; //数据类型， 主数据库-db-还是导数据-from，默认为导数据
  
    function connect($dbhost, $dbuser, $dbpwd, $dbname)
    {
        $this->charset = str_replace('-', '', 'utf-8');
        $this->dbh     = @mysql_connect($dbhost, $dbuser, $dbpwd, true);
        if (!$this->dbh) {
            debug('不能连接到数据库');
        }
        if ($this->charset) {
            @mysql_query("SET character_set_connection=$this->charset, character_set_results=$this->charset, character_set_client=binary", $this->dbh);
        }
        @mysql_query("SET sql_mode=''", $this->dbh);
        $this->select_db($dbname, $this->dbh);
    }
    //执行sql语句：
    //参数： $sql :
    function query($sql)
    {
        if (empty($sql))
            return false;
        $this->sql = $sql;
        //$this->query_arr[]=$sql;
        if (DEBUG) {
            $start = microtime();
        }
        if (!($query = mysql_query($sql, $this->dbh))) {
            halt($sql);
        }
        if (preg_match("/^\\s*(insert|delete|update|replace|alter) /i", $sql)) {
            $this->rows_affected = mysql_affected_rows($this->dbh);
            if (preg_match("/^\\s*(insert|replace) /i", $sql)) {
                $query = $this->insert_id = mysql_insert_id($this->dbh);
            }
        }
        if (DEBUG) {
            $end                       = microtime();
            $num                       = count($this->query_arr);
            $this->query_arr[$num + 1] = $sql . '执行时间：' . substr(($end - $start), 0, 7);
        }
        return $query;
    }
    function replace($table, $args)
    {
        $this->_insert_replace($table, $args, 'REPLACE');
    }
    function _insert_replace($table, $args, $type = 'INSERT')
    {
        if (!in_array(strtoupper($type), array(
            'REPLACE',
            'INSERT'
        )))
            return false;
        $setsql = $this->sqls($args);
        $sql    = "{$type} INTO sp_$table SET $setsql";
        $this->query($sql);
        return $this->insert_id;
    }
	//组建where语句
    function sqls($where, $spe = ',')
    {
		if(is_array($where)){
        $setsql = array();
        foreach ($where as $key => $val) {
            if (is_array($val)) {
                $setsql[] = "$key IN ('" . implode("','", $val) . "')";
            } else {
                $setsql[] = "$key = '$val'";
            }
        }
        return implode(" $spe ", $setsql);
		}else{
			return $where;
			
		}
    }
    function fetch_object($query)
    {
        return mysql_fetch_object($query);
    }
    function free_result($query)
    {
        mysql_free_result($query);
    }
    function select_db($db, $dbh = null)
    {
        if (is_null($dbh))
            $dbh = $this->dbh;
        if (!mysql_select_db($db, $dbh)) {
            return;
        }
    }
    //获取所有
    function getAll($sql)
    {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_assoc($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return false;
        }
    }
    //获取一条
    function getOne($sql)
    {
        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_assoc($res);
            return $row;
        } else {
            return false;
        }
    }
    /*	【功能】获取一条记录的某个字段值[简化]
    【参数】where 中必须是单引号，不能是双引号，外面必须
    【注意】是双引号 @wzm 2014-10-21 11:20:22
    【案例】
    */
    function getField($short_tbl, $field = '', $where = '')
    {
        if (is_array($where)) {
            $wheresql = $this->sqls($where, 'AND');
        } else {
            $wheresql = $where;
        }
        return $this->get_var(" SELECT $field from {$this->_pre}{$short_tbl} WHERE $wheresql");
    }
    //获取一列值
    function getCol($short_tbl, $field = '', $where = '')
    {
        $fields = array();
        $field  = $field ? $field : 'eid';
        if (is_array($where)) {
            $where = $this->sqls($where, 'AND');
        }
        $query = $this->query("SELECT $field FROM {$this->_pre}{$short_tbl} WHERE $where");
        while ($rt = $this->fetch_array($query)) {
            $fields[] = $rt[$field];
        }
        return $fields;
    }
    //@gxd
    function get_col($sql)
    {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_row($res)) {
                $arr[] = $row[0];
            }
            return $arr;
        } else {
            return false;
        }
    }
    //SQL获取一条数据
    function get_row($sql, $output = 'ARRAY')
    {
        if (empty($sql))
            return null;
        $query = $this->query($sql);
        if ('OBJECT' == $output) {
            return $this->fetch_object($query);
        } else {
            return $this->fetch_array($query);
        }
    }
    //快捷查询-查询一条数据 
    public function find_one($table, $where = '', $fields = '*')
    {
        global $db;
        if (is_array($where)) {
            foreach ($where as $key => $val) {
                $wheresql[] = " AND $key='$val'";
            }
            $where = implode(' ', $wheresql);
        }
        $sql = "SELECT $fields FROM sp_$table WHERE 1 $where";
        return $db->get_row($sql);
    }
    //列表中使用的
    function fetch_array($query, $result_type = MYSQL_ASSOC)
    {
        return mysql_fetch_array($query, $result_type);
    }
    //获取单一变量
    function get_var($sql)
    {
        $query = $this->query($sql);
        $value = mysql_fetch_array($query, MYSQL_NUM);
        return isset($value[0]) ? $value[0] : null;
    }
    //表名，查询条件,查询字段
    function find_var($table, $where = '', $field = '')
    {
        $res = $this->find_one($table, $where, $field);
        return $res[$field];
    }
    //更新数据
    function update($table, $args, $where,$is_default=true)
    {
		if($is_default){
            $default  = array(
                "update_uid" => current_user('uid'),
                "update_date" => current_time('mysql'),
                "update_user" => current_user('name')
            );
            $args     = parse_args($default, $args);
        }
        $setsql   = $this->sqls($args);
        $wheresql = $this->sqls($where, 'AND');
        $where    = ($wheresql) ? "WHERE $wheresql" : '';
        $sql      = "UPDATE sp_$table SET $setsql $where";
        $this->query($sql);
        //halt($sql);
        return $this->rows_affected;
    }
    //删除数据-直接从数据库表中删除
    function del($table, $where_arr = array())
    {
        $wheresql = $this->sqls($where_arr, 'AND');
        $where    = ($wheresql) ? "WHERE $wheresql" : '';
        $this->query("DELETE FROM sp_$table $where");
        return $this->rows_affected;
    }
    //删除数据-标记删除。并不是真正删除
    function delete($table, $where_arr = array())
    {
        $wheresql = $this->sqls($where_arr, 'AND');
        $where    = ($wheresql) ? "WHERE $wheresql" : '';
        $this->query("UPDATE sp_$table SET deleted=1 $where");
        return $this->rows_affected;
    }
    //获取全部数据
    function get_results($sql, $id = false, $output = 'ARRAY')
    {
        $arr    = array();
        $query  = $this->query($sql);
        $method = 'fetch_' . strtolower($output);
        while ($data = $this->$method($query)) {
            $id ? $arr[$data[$id]] = $data : $arr[] = $data;
        }
        return $arr;
    }
    //获取列表  过滤deleted  要求数据表中必须有deleted字段， 获取全局内容
    //应用范围： 对数据库表中信息基本没有处理。或者基本没有外键。最粗糙的处理方式
    public function find_results($table='', $where = '', $fields = '*', $pages='')
    {
        if (is_array($where)) {
            $where = 'AND ' . $this->sqls($where);
        }
        if ($this->db_type == 'db') {
            $where .= " AND  deleted='0' ";
        }
        $this->sql = "select $fields from {$this->_pre}{$table} WHERE 1 $where  $pages[limit] ";
        return $this->get_results($this->sql);
    }
    //简单数量统计
    function find_num($table, $where = '', $joins = '')
    {
        $sql   = "SELECT COUNT(*) FROM {$this->_pre}{$table} $joins WHERE 1 $where";
        $total = $this->get_var($sql);
        return $total;
    }
    //插入数据
    function insert($table, $args,$is_default=true)
    {

        if( $is_default ){
            $default = array(
                "create_uid" => current_user('uid'),
                "create_date" => current_time('mysql'),
                "create_user" => current_user('name')
            );
            $args    = parse_args($args, $default);
        }
        $args    = parse_args($args, $default);
        // echo "<pre />";
        // print_r($args);exit;
        $this->_insert_replace($table, $args, 'INSERT');
        return $this->insert_id;
    }
    //处理附表
    function meta($id, $meta_name = '', $meta_value = '', $used = '')
    {
        if (empty($id))
            return false;
        if ($used == 'enterprise')
            $table = 'sp_metas_ep';
        elseif ($used == 'user')
            $table = 'sp_metas_hr';
        else
            $table = 'sp_metas_ot';
        //	global $db;
        $result = '';
        if ($meta_name && $meta_value) {
            /*		$sql = "INSERT INTO $table ( ID, meta_name, meta_value, used )
            VALUES ( '$id', '$meta_name', '$meta_value', '$used' )
            ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )";
            $this->query( $sql );*/
            $old_metas = $this->meta($id);
            if (isset($old_metas[$meta_name])) {
                if ($meta_value != $old_metas[$meta_name]) {
                    $this->update($table, array(
                        "meta_value" => $meta_value
                    ), array(
                        "ID" => $id,
                        "meta_name" => $meta_name
                    ));
                }
            } else {
                $this->insert($table, array(
                    "meta_value" => $meta_value,
                    "ID" => $id,
                    "meta_name" => $meta_name,
                    "used" => '$used'
                ));
            }
        } elseif ($meta_name) {
            $result = $this->get_var("SELECT meta_value FROM $table WHERE ID = '$id' AND meta_name = '$meta_name' AND used = '$used'");
        } else {
            $result = array();
            $query  = $this->query("SELECT * FROM $table WHERE ID = '$id' AND used = '$used'");
            while ($rt = $this->fetch_array($query)) {
                $result[$rt['meta_name']] = $rt['meta_value'];
            }
        }
        return $result;
    }
    //删减多个表 
    public function drop_more($tbs)
    {
        foreach ($tbs as $tb) {
            $this->drop($tb);
        }
    }
    /* 	 清空并删减数据库：主键从1开始
    @param int  $table_name 表名
    @return 没有返回值
    应用场景：导数据清空主数据库中的数据
    例如：$db->drop('sp_contract_item'); //清空合同项目表 */
    public function drop($table_name)
    {
        $this->query("TRUNCATE TABLE $table_name");
        $this->query("ALTER TABLE $table_name AUTO_INCREMENT =1");
    }
}
?>