<?php
//异常处理：扩展系统类

class error extends Exception{
	
	//获取错误信息
	function get_message(){
		$msg = $this->message;
		$run_trace=$this->getTrace();
		krsort($run_trace);
		$k=1;
		foreach($run_trace as $v){
			$traceMessageHtml.='<tr class="bg1"><td>'.$k.'</td><td>'.$v['file'].'</td><td>'.$v['line'].'</td><td>'.$this->_getLineCode($v['file'], $v['line']).'</td></tr>';
 		$k++;	
		} 
		  
			
		unset($k);unset($run_trace); 
				echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><title>'.$_SERVER['HTTP_HOST'].' - PHP Error</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
<style type="text/css">
<!--
body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
#container { width: 90%;margin-left:auto;margin-right:auto; }
#message   { width: 90%; color: black; }
.red  {color: red;}
a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
.bg1{ background-color: #FFFFCC;}
.bg2{ background-color: #EEEEEE;}
.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
.info {background: none repeat scroll 0 0 #F3F3F3;border: 0px solid #aaaaaa;border-radius: 10px 10px 10px 10px;color: #000000;font-size: 11pt;line-height: 160%;margin-bottom: 1em;padding: 1em;}
.help {
background: #F3F3F3;border-radius: 10px 10px 10px 10px;font: 12px verdana, arial, sans-serif;text-align: center;line-height: 160%;padding: 1em;}
.mind {
background: none repeat scroll 0 0 #FFFFCC;
border: 1px solid #aaaaaa;
color: #000000;
font: arial, sans-serif;
font-size: 9pt;
line-height: 160%;
margin-top: 1em;
padding: 4px;}
	-->
	</style></head><body><div id="container"><h1>调试（DEBUG）</h1><div class="info">错误描述：'.$msg.'</div><div class="info"><p><strong>PHP Trace</strong></p> 
	<table cellpadding="5" cellspacing="1" width="100%" class="table"> 
	<tr class="bg2">
	<td style="width:5%">序号.</td>
	<td style="width:40%">文件</td>
	<td style="width:5%">行</td>
	<td style="width:48%">代码</td>
	</tr>'.$traceMessageHtml.'</table>   
	
	</div> </div></body></html>';
 		} 
	private function _getLineCode($file,$line) {
		$fp = fopen($file,'r');
		$i = 0;
		while(!feof($fp)) {
			$i++;
			$c = fgets($fp);
			if($i==$line) {
				return $c;
				break;
			}
		}
	} 
}