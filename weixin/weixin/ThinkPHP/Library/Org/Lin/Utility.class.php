<?php
namespace Org\Lin;
class Utility
{
    //  递归 数组 KEY 转小写
    public static function changeKeyCaseArr(   $arrResultsInfo = array() , $arrDataAreaIDs = array() )
    {

        $arr = array();
        $arrResultsInfo  = array_change_key_case($arrResultsInfo);

        if ( isset($arrResultsInfo['areathird']))
        {
            self::changeArrAreaName($arrResultsInfo, $arrDataAreaIDs);
        }



        foreach ($arrResultsInfo as $key => $val)
        {
            $val =  is_array($val)   ?  self::changeKeyCaseArr($val,$arrDataAreaIDs) : $val;
            $arr[$key] = $val;
        }

        return $arr;
    }

    public static function getUrlContent($url,$data=array(),$header = array()){
        $ch = curl_init(); //初始化CURL句柄 
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// 跟踪重定向
        if( !empty($data) ){
            curl_setopt($ch, CURLOPT_POST,1);//  POST 提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
        }
        if( !empty($header) ){
            $header = self::getArrActionHeader($header);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);//设置HTTP头信息
        }

        $results = curl_exec($ch);//执行预定义的CURL 
        curl_close($ch);
        // print_r($results);exit;
        return json_decode($results,true);
    }

    //获取分页信息
    public static function getPageInfo($count){
        $size      = !empty($_REQUEST['size'])?$_REQUEST['size']:1;
        $nowpage   = !empty($_REQUEST['page'])?$_REQUEST['page']:10;
        $countPage = (int)ceil( $count/$size );
        $upPage    = ($nowpage - 1 < 1)?1:$nowpage - 1;
        $nextPage  = ($nowpage + 1 > $countPage)?$countPage:$nowpage + 1;

        return array(
                'size'      => $size
               ,'nowpage'   => $nowpage
               ,'countPage' => $countPage
               ,'upPage'    => $upPage
               ,'nextPage'  => $nextPage
               ,'countTotal'=> $count
            );
    }

    //excel 导出
    //$params = array(
    //      'A' => 学号
    //     ,'B' => 姓名
    //     ,'C' => 性别
    //     ,'D' => 年龄
    //     ,'E' => 班级
    //)
    //$data = array(
    //     array('1','小王','男','20','100'),
    //     array('2','小李','男','20','101'),
    //     array('3','小张','女','20','102'),
    //     array('4','小赵','女','20','103')
    // );
    public static function excelExport( $params = array(),$data=array(),$outputFileName='excel.xls' ){
        $resultPHPExcel = new PHPExcel(); 
        //设置参数 
        
        //设值
        foreach ($params as $key => $value) {
            $resultPHPExcel->getActiveSheet()->setCellValue($key.'1',$value); 
        }
         
        $i = 2; 
        foreach($data as $item){ 
            foreach ($params as $key => $value) {
               $resultPHPExcel->getActiveSheet()->setCellValue($key . $i, $item[$value]);
            }
            $i ++; 
        }
        //设置导出文件名 
        $xlsWriter = new PHPExcel_Writer_Excel5($resultPHPExcel); 
        header("Content-Type: application/force-download"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Type: application/download"); 
        header('Content-Disposition:inline;filename="'.$outputFileName.'"'); 
        header("Content-Transfer-Encoding: binary"); 
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header("Pragma: no-cache"); 
        return $xlsWriter->save( "php://output" );
    }

    //获取from信息
    public static function getParamsByFrom($data){
        $array = array();
        foreach ($data as $key => $value) {
            if( isset($value['value']) ){
                $array[$value['name']] = $value['value'];
            }
        }
        return $array;
    }

}

