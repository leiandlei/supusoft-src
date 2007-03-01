<?php   
class Access    
{   
    var $databasepath,$constr,$dbusername,$dbpassword,$link;    
    public function Access($databasepath,$dbusername,$dbpassword)   
    {   
        $this->databasepath=$databasepath;  
        $this->username=$dbusername;    
        $this->password=$dbpassword;    
        $this->connect();   
    }   

    public function connect()   
    {   
        $this->constr="DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=" . realpath($this->databasepath);  
        $this->link=odbc_connect($this->constr,$this->username,$this->password,SQL_CUR_USE_ODBC);   
        if(!$this->link)echo "数据库连接失败!";    
        $this -> query('set');
        return $this->link;   
    }   

    public function query($sql) 
    {   
        return @odbc_exec($this->link,$sql);    
    }   

    public function first_array($sql)   
    {   
        return odbc_fetch_array($this->query($sql));    
    }   

    public function fetch_row($query)   
    {   
        return odbc_fetch_row($query);  
    }   

    public function total_num($sql)//取得记录总数 
    {   
        return odbc_num_rows($this->query($sql));   
    }   

    public function close()//关闭数据库连接函数  
    {       
        odbc_close($this->link);    
    }   

    public function insert($table,$data)//插入记录函数    
    {   
        $ins = $field= array();
        foreach ($data as $key => $value) {
            $field[] = $key;
            $ins[]   = preg_match("/[\x7f-\xff]/",$value)?iconv('UTF-8','GB2312',$value):$value;
        }
        $field = implode(',',$field);$ins = implode("','",$ins);
        $sql="INSERT INTO ".$table." (".$field.") VALUES ('".$ins."')";
        echo $sql.'<br />';
        return $this->query($sql);  
    }   

    public function getinfo($table,$field,$id,$colnum)//取得当条记录详细信息  
    {   
        $sql="SELECT * FROM ".$table." WHERE ".$field."=".$id."";   
        $query=$this->query($sql);  
        if($this->fetch_row($query))    
        {   
            for ($i=1;$i<$colnum;$i++)  
            {   
          $info[$i]=odbc_result($query,$i); 
             }  
         }  
         return $info;  
    }   

    public function getlist($table,$field,$colnum,$condition,$sort="ORDER BY id DESC")//取得记录列表      
    {   
         $sql="SELECT * FROM ".$table." ".$condition." ".$sort; 
         $query=$this->query($sql); 
         $i=0;  
         while ($this->fetch_row($query))    
         {  
        $recordlist[$i]=getinfo($table,$field,odbc_result($query,1),$colnum);   
        $i++;   
          } 
          return $recordlist;   
    }   

    public function getfieldlist($table,$field,$fieldnum,$condition="",$sort="")//取得记录列表    
    {   
         $sql="SELECT ".$field." FROM ".$table." ".$condition." ".$sort;    
         $query=$this->query($sql); 
         $i=0;  
         while ($this->fetch_row($query))    
         {  
         for ($j=0;$j<$fieldnum;$j++)   
        {   
                   $info[$j]=odbc_result($query,$j+1);  
        }       
        $rdlist[$i]=$info;  
        $i++;   
         }  
         return $rdlist;    
    }   

    public function updateinfo($table,$field,$id,$set)//更新记录    
    {   
        $sql="UPDATE ".$table." SET ".$set." WHERE ".$field."=".$id;    
        $this->query($sql); 
    }   

    public function deleteinfo($table,$field,$id)//删除记录 
    {   
         $sql="DELETE FROM ".$table." WHERE ".$field."=".$id;   
         $this->query($sql);    
    }   

    public function deleterecord($table,$condition)//删除指定条件的记录  
    {   
         $sql="DELETE FROM ".$table." WHERE ".$condition;   
         $this->query($sql);    
    }   

    public function getcondrecord($table,$condition="")// 取得指定条件的记录数    
    {   
         $sql="SELECT COUNT(*) AS num FROM ".$table." ".$condition; 
         $query=$this->query($sql); 
         $this->fetch_row($query);  
         $num=odbc_result($query,1);    
         return $num;               
    }   
}   
?>  
