<?php
/* 
* @Author: lin
* @Date:   2016-05-11 15:34:23
*/
//添加
if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
	$params = $_POST;unset($params['id'],$params['sub']);
	if(!empty($_POST['l_iso']))$params['l_iso']=implode(',',$_POST['l_iso']);;
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'training_lesson',$params,array('id'=>$id),false );
	}else{//添加
		$id = $db -> insert( 'training_lesson',$params,false );
	}
}


//显示
$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_training_lesson` where `id`='.$id;
	$results = $db -> getOne($sql);
	extract($results,EXTR_OVERWRITE);
}
$iso = explode(',',$l_iso);
tpl();
?>
