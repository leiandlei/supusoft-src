<?php
/* 
* @Author: anchen
* @Date:   2017-03-23 13:23:42
* @Last Modified by:   anchen
* @Last Modified time: 2017-03-24 16:46:25
*/
$ctid = (int)getgp( 'ct_id' );
//添加
	if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
		$params = $_POST;unset($params['id'],$params['sub']);
    	$params['tixi_check']=implode(',',$params['tixi_check']);
		if( !empty($_POST['id']) ){//修改
			$id=$_POST['id'];
			$db -> update( 'partner_enterprises',$params,array('pt_id'=>$id),false );
		}else{//添加
			unset($params['id'],$params['sub']);
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
  // print_r($arr_iso);exit;
  // 默认选中合同来源
  $ctfrom_select = f_ctfrom_select($results['']);
  $ctfrom_select  = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);

//显示
$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_partner_enterprises` where `pt_id`='.$id;
	$results = $db -> getOne($sql);
	extract($results,EXTR_OVERWRITE);
}

tpl('apply_add');
?>
