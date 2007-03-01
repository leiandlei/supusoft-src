<?php
	if($_POST)
	{
		$date = $_POST['date']?substr($_POST['date'],0,7):date('Y-m');
		$sql  = 'select * from sp_hr where id !=1 and  deleted=0';
		$data = $db->getAll($sql);
		foreach($data as $item)
		{
			$sql    = 'select * from sp_examine_user where userID='.$item['id'].' and date=\''.$date.'\'';
			$detail = $db->getOne($sql);
			if(empty($detail))
			{
				$db->insert('examine_user',array
				(
					 'userID'=> $item['id']
					,'day'   => 10
					,'date'  => $date
					,'createTime'  => date('Y-m-d H:i:s')
					,'createUserID'=>$_SESSION['userinfo']['id']
				),false);
			}
		}
		showmsg( 'success', 'success',"?c=examine&a=userlist");
	}
	tpl();