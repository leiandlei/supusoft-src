<?php 

require_once ROOT.'/theme/Excel/PHPExcel.php'; 

/**对excel里的日期进行格式转化*/ 
function GetData($val){ 
$jd = GregorianToJD(1, 1, 1970); 
$gregorian = JDToGregorian($jd+intval($val)-25569); 
return $gregorian;/**显示格式为 “月/日/年” */ 
} 
function excelTime($date, $time = false) {
if(function_exists('GregorianToJD')){
if (is_numeric( $date )) {
$jd = GregorianToJD( 1, 1, 1970 );
$gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
$date = explode( '/', $gregorian );
$date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
. ($time ? " 00:00:00" : '');
return $date_str;
}
}else{
$date=$date>25568?$date+1:25569;
/*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
$ofs=(70 * 365 + 17+2) * 86400;
$date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');
}
  return $date;
}

$filePath = CONF."imp/123.xlsx";

$PHPExcel = new PHPExcel(); 

/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/ 
$PHPReader = new PHPExcel_Reader_Excel2007(); 
if(!$PHPReader->canRead($filePath)){ 
$PHPReader = new PHPExcel_Reader_Excel5(); 
if(!$PHPReader->canRead($filePath)){ 
echo 'no Excel'; 
return ; 
} 
} 

$PHPExcel = $PHPReader->load($filePath); 
/**读取excel文件中的第一个工作表*/ 
$currentSheet = $PHPExcel->getSheet(0); 
/**取得最大的列号*/ 
echo $allColumn = $currentSheet->getHighestColumn(); 
/**取得一共有多少行*/ 
echo $allRow = $currentSheet->getHighestRow(); 
/**从第二行开始输出，因为excel表中第一行为列名*/ 
$data=array();
for($currentRow = 2;$currentRow <= $allRow;$currentRow++){ 
/**从第A列开始输出*/ 
for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){ 

$data[$currentRow][$currentColumn]=$currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/ 
// echo $val."------"; 

} 
// echo "</br>"; 
} 
p($data);
echo "\n"; 
?> 