<?php
//添加
if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
	$params = $_POST;unset($params['id'],$params['sub']);
	$params['tixi_check']=implode(',',$params['tixi_check']);
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'partner_enterprises',$params,array('pt_id'=>$id),false );
	}else{//添加
		$id = $db -> insert( 'partner_enterprises',$params,false );
	}
}

//体系
$tixi=$db->getAll("select iso from `sp_settings_audit_vers`  where deleted='0' ");
$arr_iso=array();
foreach( $tixi as $v)
{
	switch ($v['iso'])
   {
		case 'A01':
			$arr_iso[]='QMS';
			break;
		case 'A02':
			$arr_iso[]='EMS';
			break;
		case 'A03':
			$arr_iso[]='OHSMS';
			break;
	}
}

  	$arr_iso = array_unique($arr_iso);
  	
	
    // echo "<pre />";
    // print_r($xmly);exit;
	//显示
	$getID = getgp('id');
	$id = !empty($id)?$id:(!empty($getID)?$getID:'');
	if( !empty($id) )
	{
		$sql = 'select *,pte.status,pte.name,pte.pt_id from `sp_partner_enterprises` pte left join `sp_partner` pt on pt.pt_id=pte.pt_id and pte.deleted=0 where pte.`pt_id`='.$id;
		$results = $db -> getOne($sql);
		extract($results,EXTR_OVERWRITE);
	}
	$tixi_check=explode(',',$tixi_check);
	//默认选中合同来源
    $ctfrom_select = f_ctfrom_select($results['xmly']);
    $ctfrom_select  = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);

	tpl('apply_edit1');

?>
