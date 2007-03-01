<?php
//合同登记列表
$enterprise = load( 'enterprise' );
$fields = $join = $where = $page_str = '';
$ep_name		= getgp( 'ep_name' );
$work_code		= getgp( 'work_code' );
$ctfrom			= getgp( 'ctfrom' )?getgp( 'ctfrom' ):$_SESSION['extraInfo']['ctfrom'];
$areacode		= getgp( 'areacode' );
$person			= getgp( 'person' );
$create_user	= getgp( 'create_user' );
$if_c           = getgp( 'if_c' );

	if( $ep_name ){	//企业搜索
		$where .= " AND e.ep_name LIKE '%".str_replace('%','\%',$ep_name)."%'";
		$page_str .= '&em_name='.$ep_name;
	}

	if($person){
		$where .= " and e.person like '%$person%' ";
		$page_str .= '&person='.$person;
	}
	if($work_code){
		$where .= " and e.work_code like '%$work_code%' ";
		$page_str .= '&work_code='.$work_code;
	}

	//合同来源限制
	$where .= " AND e.ctfrom = '$ctfrom'";
	$ctfrom_select= str_replace( 'value="'.$ctfrom.'"', 'value="'.$ctfrom.'" selected ', $ctfrom_select );
	$page_str .='&ctfrom='.$ctfrom;
	unset( $len, $_len );


	if($areacode){
		$province_select = str_replace( "value=\"$areacode\">", "value=\"$areacode\" selected>" , $province_select );
		$where .= " and e.areacode like '".substr($areacode,0,2)."%' ";
		$page_str .= '&areacode='.$areacode;
	}

	$where .= " AND e.deleted = '0'";
	$join .= " LEFT JOIN sp_hr hr ON hr.id = e.create_uid";
	$where .= " AND e.parent_id = '0'";// 只显示主公司
	if ($if_c) {
		$where .= " AND e.if_c = ".$if_c;// 只显示未登记企业
	}else{
		$where .= " AND e.if_c = '0'";// 只显示未登记企业
		$if_c = 0;
	}
	
	//@zbzytech 加入cu_id用来筛选
	if(array_key_exists('is_customer',$_SESSION['userinfo'])){
		$cu_id = $_SESSION['userinfo']['cu_id'];
		$where .= " AND e.cu_id = $cu_id";
	}
	

	$total = $db->get_var("SELECT COUNT(*) FROM sp_enterprises e $join WHERE 1 AND e.deleted = '0' $where");
	//P($join);
	//var_dump($join);
	$pages = numfpage( $total, 20, "?c=$c&a=$a".$page_str );

	$enterprises = array();
	$sql = "SELECT e.*,hr.name input_user FROM sp_enterprises e $join WHERE 1 AND e.deleted = '0' $where ORDER BY e.eid DESC $pages[limit]";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$metas=array();
		$metas = $enterprise->meta( $rt['eid'] );
		$rt['ctfrom']		= f_ctfrom( $rt['ctfrom'] ); 
 		$rt['cerate_u']		=  $rt['cerate_uid'] ;
		$rt['update_u']		=  $rt['update_uid'] ; 
		$rt = array_merge( $rt, $metas );
		 
		$enterprises[$rt['eid']] = $rt;
	}
	

//var_dump($enterprises);
	tpl( 'contract/alist' );
?>