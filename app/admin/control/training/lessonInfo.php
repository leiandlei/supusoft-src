<?php
$id = getgp('id');
if( !empty($id) ){
	$sql = "select * from `sp_training_lesson` where id=".$id;
	$results = $db->getOne($sql);
	extract($results,EXTR_OVERWRITE);
	if( !empty($file) ){
		$sql_file = "select * from `sp_upload_file` where `id` in(".$file.")";
		$file_results = $db->getAll($sql_file);
	}

	if( !empty($_FILES['file']['tmp_name'])&&!empty($_POST['type']) ){
		$upload = load('upload');$upload->saveRule='';$upload->savePath=ROOT.'/uploads/file/'.date('Y-m-d').'/';
		if (!$upload->upload()) {
	       // 上传错误提示错误信息
	       showmsg($upload->getErrorMsg() , 'error','?c=training&a=lessonInfo&id='.$id);
	       exit;
    	} else {
    		//上传成功
    		$info = $upload->getUploadFileInfo();
            $savepath = str_replace('\\','/',$info[0]['savepath']);
            $savepath = mysql_real_escape_string($savepath);
    		$params = array(
    				 'name'       => $info[0]['name']
    				,'path'       => $savepath
    				,'url'        => $savepath.$info[0]['savename']
    				,'type'       => $_POST['type']
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
    		$sql = "update `sp_training_lesson` set `file`='".$file."' where `id`=".$id;
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
