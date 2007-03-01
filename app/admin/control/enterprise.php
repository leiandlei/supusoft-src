<?php 
//客户模块  主公司，关联公司（子公司） 分场所
//LY 创建enterprise实例
$enterprise = load( 'enterprise' );

 
$step = getgp('step');
//var_dump($step);
$arctype_select = f_select('arctype');//文档类型下拉 

if( !empty($_SESSION['userinfo']['pt_id'])&&$_SESSION['userinfo']['pt_id']==$_SESSION['userinfo']['id'] ){
	$ctfrom = $_SESSION['userinfo']['code'];
}
$ctfrom_select = f_ctfrom_select( !empty($ctfrom)?$ctfrom:'' ); //合同来源下拉

$ep_level_select=f_select('ep_level'); //客户级别
$ep_type_select=f_select('ep_type'); // 客户类别-搜索
$nature_select=f_select('nature');//企业性质
$statecode_select=f_select('statecode');//国家代码  
$currency_select=f_select('currency');//注册资本币种 
$province_select = f_province_select();//省分下拉 (搜索用)

$union_type_radios = f_select('union_type');//关联公司类型单选
//分场所类型下拉
$site_type_select =f_select('site_type');
//@zbzytech 加入cu_id用来筛选
if(array_key_exists('is_customer',$_SESSION['userinfo'])){
	$cu_id = $_SESSION['userinfo']['cu_id'];
}
unset( $code, $item );
//LY 根据不同的a参数选择不同的控制器加载
if( empty( $a ) || 'list' == $a ){ //企业列表
 
	require_once( CTL_DIR. '/enterprise/list.php' );
	 
} elseif( 'list_edit' == $a ) {
	//LY a为add或者edit方法时，加载edit。php
	require_once( CTL_DIR. '/enterprise/list_edit.php' );
} elseif( 'add' == $a || 'edit' == $a ) {
	//LY a为add或者edit方法时，加载edit。php
	require_once( CTL_DIR. '/enterprise/edit.php' );
} elseif( 'edit_site' == $a ){
	require_once( CTL_DIR. '/enterprise/edit_site.php' );
} elseif( 'list_site' == $a ){
	require_once( CTL_DIR. '/enterprise/list_site.php' );
} elseif( 'del_site' == $a ){
	$es_id = (int)getgp( 'es_id' );

	$eid = $db->get_var("select eid from sp_enterprises_site where es_id='$es_id' ");
	$db->query("DELETE FROM sp_enterprises_site WHERE es_id = '$es_id'");
	//更新主公司信息
	$sql = "update sp_enterprises set site_count = site_count - 1 where eid = $eid ";
	$db->query($sql); 
	showmsg( 'success', 'success', "?c=enterprise&a=list_site&eid={$eid}" );

}elseif( 'list_attach' == $a ) { //组织文档
	require_once( CTL_DIR. '/enterprise/list_attach.php' ); 
} elseif( 'delattach' == $a ) { //删除组织文档
	$aid = (int)getgp( 'aid' );
	$eid = (int)getgp( 'eid' );
	$attach = load( 'attachment' );
 	// 日志 
	$bf_str = $attach->get($aid);
	do {
		log_add($bf_str['eid'], 0, "[说明:组织文档-删除]", NULL, serialize($bf_str));
	}while(false);
	$attach->del( $aid );
	showmsg( 'success', 'success', $_SERVER['HTTP_REFERER'] );

} elseif( 'del' == $a ) {//删除企业
	$eid = (int)getgp( 'eid' );
	if( $eid ){
		// 日志
		do {
			log_add($eid, 0, "[说明:客户信息删除]", NULL, NULL);
		}while(false);
		$parent_id = $db->get_var("select parent_id from sp_enterprises where eid='$eid' ");
		//删除企业下的审核任务
		$task = load( 'task' );
		$tids = array();
		$query = $db->query("SELECT id FROM sp_task WHERE eid = $eid");
		while( $rt = $db->fetch_array( $query ) ){
			$task->del_send( $rt['id'] );
		}
		$task->del( array( 'eid' => $eid ) );
		//删除企业下的审核项目
		$audit = load( 'audit' );
		$audit->del( array( 'eid' => $eid ) );
		//删除企业下的合同项目
		$cti = load( 'contract.item' );
		$cti->del( array( 'eid' => $eid ) );
		//删除企业下的合同
		$ct = load( 'contract' );
		$ct->del( array( 'eid' => $eid ) );
		//删除企业文档

		//删除企业
		$enter = load( 'enterprise' );
		$enter->del( array( 'eid' => $eid ) );
		//更新主公司信息
		$sql = "update sp_enterprises set union_count = union_count - 1 where eid = '$parent_id' ";
		$db->query($sql);
		//删除子公司信息
		$sql = "select eid from sp_enterprises where parent_id='$eid' ";
		$res = $db->query($sql);
		while($row=$db->fetch_array($res)){
			$enter->del( array( 'eid' => $row['eid'] ) );
			// 日志
			do {
				log_add($row['eid'], 0, "[说明:客户信息-删除]", NULL, NULL);
			}while(false);
		}
		showmsg( 'success', 'success', "?c=enterprise&a=list" );
	}   
}

?>