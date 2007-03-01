<?php
//word包
final class export{
 	public $file_name=''; //导出文件名
	public $data='';//导出数据  
	//导出word文档 
	function export_doc($data){  
		$this->data = readover( DOCTPL_PATH .$_GET['a'].'.xml' ); //要求控制器文件名与模板文件名一致，并且模板格式为XML
		foreach($data as $k=>$v){ 
			$this->data= str_replace( 'supul'.$k.'supur',$v, $this->data );
		}   
		header("Content-type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $this->file_name.'.doc' ) );
		echo $this->data;
		 
	}  
	
	/*
 * 函数名：xml_str
 * 说  明：把字符转义为实体字符
 * 参  数：$str
 * 返回值：返回转义后的字符串
 应用：导出word中编辑特殊字符
 */
function xml_str($str)
{
    $arr_search  = array(
        '<',
        '>',
        '&',
        '\'',
        '"'
    );
    $arr_replace = array(
        '&lt;',
        '&gt;',
        '&amp;',
        '&apos;',
        '&quot;'
    );
    $str         = str_ireplace($arr_search, $arr_replace, $str);
    return $str;
}
 
	
}