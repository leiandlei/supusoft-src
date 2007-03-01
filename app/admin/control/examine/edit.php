<?php
	$id = $_GET['id'];$detail = array();
	if(!empty($_POST)&&!empty($_POST['save']))
	{
		$params = array();
		$params['name']    = $_POST['name'];
		$params['content'] = $_POST['content'];
		$params['day']     = $_POST['day'];
		$params['status']  = 1;
		$params['modifyTime']    = date('Y-m-d H:i:s');
		$params['modifyUserID']  = $_SESSION['userinfo']['id'];
		$params['modifyUserIP']  = 0;
		if( $_POST['id'] )
		{
			$detail = $db->update('examine',$params,array('id'=>$_POST['id']),false);
		}else
		{
			$params['createTime']    = date('Y-m-d H:i:s');
			$params['createUserID']  = $_SESSION['userinfo']['id'];
			$detail = $db->insert('examine',$params,false);
		}
	}

	if($id)
	{
		$detail = $db->getOne('select * from sp_examine where id='.$id);
	}
	tpl();