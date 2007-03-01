<?php


$fields = $join = $where = $urls = '';



	extract( $_GET, EXTR_SKIP );
	${'status_'.$is_hire.'_tab'} = ' ui-tabs-active ui-state-active';
$name = trim($name);
	if( $name ){
		$where .= " AND name like '%$name%' ";
		$urls .= '&name='.$name;
	}
	if( $easycode ){
		$where .= " AND easycode like '%$easycode%' ";
		$urls .= '&easycode='.$easycode;
	}
	if( $code ){
		$where .= " AND code like '%$code%' ";
		$urls .= '&code='.$code;
	}
	$is_stop=getgp("is_stop");	
	if(isset($is_stop)){
		$where .= " AND is_stop ='$is_stop' ";
		
	}
	$ctfrom_select = f_ctfrom_select();
	//合同来源限制
	$len = get_ctfrom_level( current_user( 'ctfrom' ) );

	if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
		$_len = get_ctfrom_level( $ctfrom );
		$len = $_len;
	} else {
		$ctfrom = current_user( 'ctfrom' );
	}
	$last = substr($ctfrom,$len - 1,1);
	$ctfrom_e = substr( $ctfrom, 0, $len -1 ).($last+1);
	$_i = 8 - $len;
	for( $i = 0; $i < $_i; $i++ ){
		$ctfrom_e .= '0';
	}
	$where .= " AND ctfrom >= '$ctfrom' AND ctfrom < '$ctfrom_e'";
	$ctfrom_select = str_replace( "value=\"$ctfrom\">", "value=\"$ctfrom\" selected>" , $ctfrom_select );

	$urls .= '&ctfrom='.$ctfrom;



	if( $audit_job || $audit_job=='0' ){
		$where .= " AND audit_job = '$audit_job' ";
		$audit_job_select = str_replace( "value=\"$audit_job\">", "value=\"$audit_job\" selected>" , $audit_job_select );
		$urls .= '&audit_job='.$audit_job;
	}

	$where .= " AND is_hire IN(1,3) AND deleted = 0";
	if( !$export ){
		$total = $db->get_var("SELECT COUNT(*) FROM sp_hr $join WHERE 1 $where  ");
		$pages = numfpage( $total );
	}
	$sql = "SELECT * FROM sp_hr $join WHERE 1 $where ORDER BY id DESC $pages[limit]" ;

	$query = $db->query( $sql);
	while( $rt = $db->fetch_array( $query ) ){

		$rt['ctfrom']		= f_ctfrom( $rt['ctfrom'] );
		$rt['audit_job']	= f_audit_job($rt['audit_job']);
		$rt['areacode']		= f_region_province( $rt['areacode'] );	//取省地址
		//$rt['sex']		= $rt['sex'] ;
		if ($rt['sex']=='1'){$rt['sex']='男';}elseif($rt['sex']=='2'){$rt['sex']='女';}
		$rt['is_hire']		= $rt['is_hire'];
		$rt['department'] 	= f_department($rt['department']);
		$rt['mail']			= $user->meta($rt['id'],'mail' );
		$rt['note']			= $user->meta($rt['id'],'note' );
		$rt['sys']		    = $rt['sys'];
		//拼接判断权限
        $left_nav = array_reverse($left_nav);
        //行程进度
        foreach ($left_nav['main']['single']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['single'] .= $left_navs[0].";"     ;
			}
        }
        //合作方
        foreach ($left_nav['main']['hezuofang']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['hezuofang'] .= $left_navs[0].";"     ;
			}
        }
        //客户管理
        foreach ($left_nav['main']['enterprise']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['enterprise'] .= $left_navs[0].";"     ;
			}
        }
        //合同评审
        foreach ($left_nav['main']['contract']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['contract'] .= $left_navs[0].";"     ;
			}
        }
        //客服维护
        foreach ($left_nav['main']['preserve']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['preserve'] .= $left_navs[0].";"     ;
			}
        }
        //审核方案
        foreach ($left_nav['main']['auditarrange']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['auditarrange'] .= $left_navs[0].";"     ;
			}
        }
        //审核员
        foreach ($left_nav['main']['auditor']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['auditor'] .= $left_navs[0].";"     ;
			}
        }
        //评定管理
        foreach ($left_nav['main']['assess']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['assess'] .= $left_navs[0].";"     ;
			}
        }
        //证书管理
        foreach ($left_nav['main']['cert']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['cert'] .= $left_navs[0].";"     ;
			}
        }
        //变更管理
        foreach ($left_nav['main']['change']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['change'] .= $left_navs[0].";"     ;
			}
        }
        //财务收费
        foreach ($left_nav['main']['finance']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['finance'] .= $left_navs[0].";"     ;
			}
        }
        //培训管理
        foreach ($left_nav['main']['training']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['training'] .= $left_navs[0].";"     ;
			}
        }
        //人力资源
        foreach ($left_nav['main']['people']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['people'] .= $left_navs[0].";"     ;
			}
        }
        //报表管理
        foreach ($left_nav['main']['export']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['export'] .= $left_navs[0].";"     ;
			}
        }
        //文档管理
        foreach ($left_nav['main']['docmanage']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['docmanage'] .= $left_navs[0].";"     ;
			}
        }
        //微信管理
        foreach ($left_nav['main']['weixin']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['weixin'] .= $left_navs[0].";"     ;
			}
        }
        //系统管理
        foreach ($left_nav['main']['system']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['system'] .= $left_navs[0].";"     ;
			}
        }
        //公告管理
        foreach ($left_nav['main']['notice']['options'] as $left_navs) 
        {
    		if(@strpos($rt['sys'], urltoauth($left_navs[1])) !== false) 
    		{
    			
					$rt['notice'] .= $left_navs[0].";"     ;
			}
        }
    	$users[$rt['id']]	= $rt; 
    }
    // echo "<pre />";
    // print_r($users);exit;
    
	if( !$export ){
		tpl('sys/hr_list');
	} else {
		ob_start();
		tpl( 'xls/list_sys_hr' );
		$data = ob_get_contents();
		ob_end_clean();
		export_xls( '人员列表', $data );
	}
?>