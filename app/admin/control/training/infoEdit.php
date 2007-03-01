<?php
/* 
* @Author: lin
* @Date:   2016-05-11 15:34:23
*/

//添加
if( !empty($_POST)){
	$params = $_POST;unset($params['id'],$params['submit']);
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'training_info',$params,array('id'=>$id),false );
		showmsg ( '修改成功','success', "?c=training&a=infoEdit&id=".$id );
	}else{//添加
		$id = $db -> insert( 'training_info',$params,false );
		showmsg ( '添加成功','success', "?c=training&a=infoEdit&id=".$id );
	}
}


//显示
$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_training_info` sti left join `sp_training_student` sts on sti.s_id=sts.id left join `sp_training_lesson` stl on sti.l_id=stl.id where sti.`status`=1 and sts.`status`=1 and stl.`status`=1 and sti.`id`='.$id;
	$results = $db -> getOne($sql);
	extract($results,EXTR_SKIP );
}
$iso = explode(',',$l_iso);
tpl();
?>
