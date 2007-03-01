<?php
ini_set("display_errors", "Off");
require_once '../../../../data/db_config.php';
$DB = getCon( $db_config['db_host'],$db_config['db_user'],$db_config['db_pwd'],$db_config['db_name'] );

//ajax返回
function ajaxReturn($errorCode = 0,$errorStr = '',$data = array()){
    $results = array(
             'errorCode' => $errorCode
            ,'errorStr'  => $errorStr
            ,'data'      => $data
        );
    return json_encode($results);
}

function getCon( $host,$user,$pwd,$name ){
	$con = mysql_connect($host,$user,$pwd);
	mysql_query("set names 'utf8'");
	mysql_select_db($name,$con);
	return $con;
}

//执行sql
function query($sql){
    $qeury = mysql_query($sql);
    return $qeury;     
}

/**
 * 插入语句
 * @param [type] $table [表名]
 * @param [type] $data  [数据]
 */
function add($table, $data){
        $fields = $values = array();
        foreach($data as  $field => $value){
            $fields[] = '`'.$field.'`';
            $values[] = '"'.mysql_real_escape_string($value).'"';
        } 
        $sql   = 'INSERT INTO `'.$table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        return query($sql);
}

function add_serializeArray($table,$dataArray){
    $fields = $values = array();
    foreach ($dataArray as $value) {
        $fields[] = '`'.$value['name'].'`';
        $values[] = '"'.mysql_real_escape_string($value['value']).'"';
    }
    $sql = 'INSERT INTO `'.$table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
    return query($sql);
}

function selectOne($sql){
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
    return $row;
}

function selectAll($sql){
    $row = array();
    $query = mysql_query($sql);
    while($data = mysql_fetch_assoc($query)){
        $row[]=$data;
    }
     return $row;
}

//检查是否是身份证号
function isIdCard($number) {
    // 转化为大写，如出现x
    $number = strtoupper($number);
    //加权因子 
    $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码串 
    $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    //按顺序循环处理前17位 
    $sigma = 0;
    for ($i = 0;$i < 17;$i++) { 
        //提取前17位的其中一位，并将变量类型转为实数 
        $b = @(int) $number{$i}; 
  
        //提取相应的加权因子 
        $w = $wi[$i];
  
        //把从身份证号码中提取的一位数字和加权因子相乘，并累加
        $sigma += $b * $w; 
    }
    //计算序号 
    $snumber = $sigma % 11; 
  
    //按照序号从校验码串中提取相应的字符。 
    $check_number = $ai[$snumber];
  
    if (@$number{17} == @$check_number) {
        return true;
    } else {
        return false;
    }
}

function getUrlContent($url){
        $ch = curl_init(); //初始化CURL句柄 
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// 跟踪重定向
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        $results = curl_exec($ch);//执行预定义的CURL
        curl_close($ch);
        return $results;
    }
?>
