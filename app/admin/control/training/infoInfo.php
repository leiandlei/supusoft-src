<?php
$id = getgp('id');
if( !empty($id) ){
	$sql_info        = "select * from `sp_training_info` where `id`=".$id;//培训信息
	$results_info    = $db->getOne($sql_info);

	$sql_lesson      = "select * from `sp_training_lesson` where `id`=".$results_info['l_id'];//课程信息
	$results_lesson  = $db->getOne($sql_lesson);

	$sql_student     = "select * from `sp_training_student` where `id`=".$results_info['s_id'];//学员信息
	$results_student = $db->getOne($sql_student);

	if( !empty($results_info['file']) ){
		$file = $results_info['file'];
		$sql_file = "select * from `sp_upload_file` where `id` in(".$file.")";
		$file_results = $db->getAll($sql_file);
	}

	if( !empty($_FILES['file']['tmp_name'])&&!empty($_POST['type']) ){
		$upload = load('upload');$upload->saveRule='';$upload->savePath=ROOT.'/uploads/file/'.date('Y-m-d').'/';
		if (!$upload->upload()) {
	       // 上传错误提示错误信息
	       showmsg($upload->getErrorMsg() , 'error','?c=training&a=infoInfo&id='.$id);
	       exit;
    	} else {
    		//上传成功
    		$info = $upload->getUploadFileInfo();
            $savepath = str_replace('\\','/',$info[0]['savepath']);
            $savepath = mysql_real_escape_string($savepath);
    		$params = array(
    				 'name'           => $info[0]['name']
    				,'path'           => $savepath
    				,'url'            => $savepath.$info[0]['savename']
    				,'type'           => $_POST['type']
    				,'createTime'     => date('Y-m-d H:i:s')
    				,'createUserID'   => current_user('uid')
					,'createUserName' => current_user('name')
    				,'modifyTime'     => date("Y-m-d H:i:s")
					,'modifyUserID'   => current_user('uid')
					,'modifyUserName' => current_user('name')
    			);

    		//插入上传文件文件夹
    		$sql = "insert into `sp_upload_file`(`name`,`path`,`url`,`type`,`createTime`,`createUserID`,`createUserName`,`modifyTime`,`modifyUserID`,`modifyUserName`) values('".implode('\',\'',$params)."')";
    		$file_id  = $db -> query($sql);
    		$file = empty($file)?$file_id:$file.','.$file_id;

    		//更新数据
    		$sql = "update `sp_training_info` set `file`='".$file."' where `id`=".$id;
    		$db -> query($sql);

    		//重新搜索上传文件
    		$sql_file = "select * from `sp_upload_file` where `id` in(".$file.")";
			$file_results = $db->getAll($sql_file);
    	}
	}
}else{
	$url = "?c=training&a=lessonList";  
	echo "< script language='javascript' type='text/javascript'>";  
	echo "window.location.href='".$url."'";  
	echo "< /script>";  
}
tpl();
?>
