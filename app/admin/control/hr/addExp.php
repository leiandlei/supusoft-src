<?php
//人员经历；工作-教育-培训经历

 $_POST['add_hr_id']=$_GET['uid'];
		$_POST['add_date']=current_time('mysql');
		$_POST['edit_hr_id']=current_user('uid');
		$_POST['edit_date']=current_time('mysql');
	if($_GET['expId']==''){ //添加个人工作经历
   		  $db->insert( 'hr_experience', $_POST );
		 $REQUEST_URI = '?c=hr&a=edit&uid='.$_GET['uid'];
   		  showmsg('success', 'success', $REQUEST_URI. "#{$_REQUEST['anchor']}");
 	}else{ //修改个人工作经历
		$exp->edit( $_GET['expId'], $_POST );
		  $REQUEST_URI = '?c=hr&a=edit&uid='.$_GET['uid'];
   		 showmsg('success', 'success', $REQUEST_URI. "#{$_REQUEST['anchor']}");
	}