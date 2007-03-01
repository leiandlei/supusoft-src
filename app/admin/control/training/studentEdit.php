<?php
/* 
* @Author: lin
* @Date:   2016-05-11 15:34:23
*/

//添加
if( !empty($_POST['save'])&&$_POST['save']==1 ){
	$params = $_POST;unset($params['id'],$params['save']);
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'training_student',$params,array('id'=>$id),false );
	}else{//添加
		$id = $db -> insert( 'training_student',$params,false );
	}
}


//显示
$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_training_student` where `id`='.$id;
	$results = $db -> getOne($sql);
	extract($results,EXTR_OVERWRITE);
}

tpl();
?>
