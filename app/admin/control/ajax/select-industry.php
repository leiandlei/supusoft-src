<?php

/*
*选择行业
*/

 
	//搜索条件
	$where='';
	$where=" AND type='industry'";
	//行业编码
	$code=$_GET['code'];
	if($code){
		$where.=" AND code like '$code%' ";
	}
	//行业名称
	$name=$_GET['name'];
	if($name){
		$where.=" AND name like '%$name%' ";
	}
	$where .=" AND length(code)>=4";
	$total = $db->get_var("SELECT COUNT(*) FROM sp_settings e WHERE 1 $where");
	$pages = numfpage( $total, 20, $url_param );

	//列表
	$sql="select * from sp_settings where 1 $where ORDER BY `code` $pages[limit] ";
 	$codes=$db->get_results($sql);

	//显示模板
	tpl('ajax/select_industry');

