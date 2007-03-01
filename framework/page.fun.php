<?php
/*
 *	函数名：numfpage
 *	说  明：分页
 *	参  数：$total 总记录数  $size 每页显示的记录数  $url 分页列表的 URL
 *	返回值：类的实例
 */
function numfpage( $total, $size=20, $url=''){
	global $paged;
 	//默认路径
 	$url_param = $result = array();
	if($url!='')
	{
	    foreach ($_GET as $key => $val)
	    {
	        if($key=='paged' || empty($val) )continue;
	        if (is_array($val)) {
	            foreach ($val as $k => $v) {
	            	if($key=='paged' || empty($val) )continue;
	            	// echo "<pre />";
	            	// print_r($key);exit;
	            	$result['input'][] = "<input type='hidden' name='".$key['k']."' value='".$v."'>";
	                $url_param[]= $key['k'].'='.$v;
	            }
	        } else {
	        	$result['input'][] = "<input type='hidden' name='".$key."' value='".$val."'>";
	            $url_param[]= $key.'='.$val;
	        }
	    }
	}else{
		foreach ( array_merge($_POST,$_GET) as $key => $val) {
			if($key=='paged' || empty($val) )continue;
			$url_param[] = $key.'='.$val;
			$result['input'][] = "<input type='hidden' name='".$key."' value='".$val."'>";
		}
	}
	$url_param =$url= implode('&',$url_param);$result['input'] = implode(PHP_EOL,$result['input']);
	$result['numofpage'] = $numofpage = ceil( $total / $size );
	$paged  = max( 1, intval( ($paged) ? $paged : getgp('paged') ) );

	if( $paged > $numofpage ) $paged = $numofpage;
	if( $numofpage <= 1 || !is_numeric( $paged ) ){
		return $result;
	} else {
		$result['limit'] = " LIMIT " . ( $paged - 1 ) * $size . ",$size";

		$pages = "<div class=\"pages\"><ul><li><a href=\"?$url\" style=\"font-weight:bold\">&laquo;</a></li>";
		for( $i = $paged-4; $i <= $paged-1; $i++ ) {
			if($i < 1) continue;
			$_url = '';
			$_url .= (1==$i)?'':"&paged=$i";
			$pages .= "<li><a href=\"?{$url}{$_url}\">{$i}</a></li>";
		}
		$pages .= "<li><b> {$paged} </b></li>";
		if( $paged < $numofpage ){
			$flag = 0;
			for( $i = $paged+1; $i <= $numofpage; $i++ ){
				$_url = '';
				$_url .= (1==$i)?'':"&paged=$i";
				$pages .= "<li><a href=\"?{$url}{$_url}\">{$i}</a></li>";
				$flag++;
				if($flag==5) break;
			}
		}
		$pages .= "<a href=\"?{$url}&paged={$numofpage}\" style=\"font-weight:bold\">&raquo;</a><div class=\"fl\">Total:{$total}&nbsp; Pages:{$paged}/{$numofpage}</div><!--<span class=\"pagesone\"><input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){ this.nextSibling.onclick();return false;}\" /><button onclick=\"location='{$url}'+this.previousSibling.value+'{$mao}';return false;\" type=\"button\">GO</button></span>--></div>";
		$result['pages'] = $pages;
		return $result;
	}
}