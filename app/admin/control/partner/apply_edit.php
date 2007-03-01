<?php
	//添加
	if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
		$params = $_POST;unset($params['id'],$params['sub']);
		if( !empty($_POST['id']) ){//修改
			$id=$_POST['id'];
			$db -> update( 'partner_info',$params,array('pti_id'=>$id),false );
		}else{//添加
			$id = $db -> insert( 'partner_info',$params,false );
		}
	}

	//显示
	$getID = getgp('id');
	$id = !empty($id)?$id:(!empty($getID)?$getID:'');
	if( !empty($id) ){
		$sql = 'select *,pti.status from `sp_partner_info` pti left join `sp_partner` pt on pt.pt_id=pti.pt_id and pti.deleted=0 where pti.`pti_id`='.$id;
		$results = $db -> getOne($sql);
		extract($results,EXTR_OVERWRITE);
	}
	tpl('apply_edit');

?>
