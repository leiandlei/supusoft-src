<?php
function showFile($dir)
{
    $files = array();
    if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != "..") {
                    if (is_dir($dir . "/" . $file)) {
                        $files[$file] = showFile($dir . "/" . $file);
                    } else {
                        $files[] = $dir . "/" . $file;
                    }
                }
            }
        }
    }
    closedir($handle);
    return $files;
}
function showArray($arr){
	$temp=array();
	foreach($arr as $k=>$v){
		if(is_array($v)){
			$temp[iconv("gb2312","UTF-8",$k)]=showArray($v);
		}else{
			$temp[iconv("gb2312","UTF-8",$k)]=iconv("gb2312","UTF-8",$v);
		}
	}
	return $temp;
}
$hostdir="files";
$filenames=showArray(showFile($hostdir));
$arr=array_keys($filenames);
function printArray($filenames,$arr){
	foreach($filenames as $k=>$item){
		if(!is_numeric($k))
			if(in_array($k,$arr))
				echo "<li><span class='red'>".$k."</span></li>";
			else
				echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k."</li>";
		if(is_array($item)){
			//@zbzytech 避免少一个参数的错误echo printArray($item);
			echo printArray($item,$arr);
			}
		else
			if(!strpos($item,'Thumbs.db') and !strpos($item,'~$'))
			echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$item'><span class='blue'>".str_replace("/","",strrchr($item,"/"))."</span></a></i>";
		



	}
}
tpl("file_list");

?>