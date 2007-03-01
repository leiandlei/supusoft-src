<?php
	$id       = getgp('id');
	// echo "<pre />";
	// print_r($id);exit;
	$exu_id   = getgp('exu_id');
	$fenzhi   = getgp('fenzhi');
	$content  = getgp('content');
	$detail   = array();
	if(!empty($_POST)&&!empty($_POST['save']))
	{
		$exInfo  = $db->getOne('select * from sp_examine where id='.$_POST['ex_id']);
		if( empty($exInfo) )showmsg('考核不存在','error','?c=examine&a=edit','2');
		$exuInfo = $db->getOne('select * from sp_examine_user where id='.$_POST['exu_id']);
		if( empty($exuInfo) )showmsg('用户不存在','error','?c=examine&a=userlist','2');
		if(!empty($fenzhi))
		{
			$exInfo['day']=$fenzhi;
			if($exInfo['operation']=='2')
			{
				$yunsuantype     = "扣减";
				$exuInfo['day']  = $exuInfo['day']-$exInfo['day'];
			}else{
				$yunsuantype     = "增加";
				$exuInfo['day']  = $exuInfo['day']+$exInfo['day'];
			}
		}else{
			
			if($exInfo['operation']=='2')
			{
				$yunsuantype     = "扣减";
				$exuInfo['day']  = $exuInfo['day']-$exInfo['day'];
			}else{
				$yunsuantype     = "增加";
				$exuInfo['day']  = $exuInfo['day']+$exInfo['day'];
			}
		}
		
		$db->update('examine_user',array('day'=>$exuInfo['day']),array('id'=>$exuInfo['id']),false);

		$params = array();
		$params['ex_id']        = $_POST['ex_id'];
		$params['exu_id']       = $_POST['exu_id'];
		$params['userID']       = $_POST['userID'];
		$params['day']          = $exInfo['day'];
		$params['content']       = $_POST['content'];

		$params['createTime']   = date('Y-m-d H:i:s');
		$params['createUserID'] = $_SESSION['userinfo']['id'];
		if( $_POST['id'] )
		{
			$db->update('examine_user_info',$params,array('id'=>$_POST['id']),false);
		}else
		{

			$id = $db->insert('examine_user_info',$params,false);
			if($id)
			{
				//微信推送逻辑
				$messageTem =
				'{
		            "touser":"%s",
		            "template_id":"ijIw6gEMwk3MDL1mfbvCWSCVrPrp81IQxqnasrKAZ3c",
		            "topcolor":"#FF0000",
		            "data":{
		                "first": {
		                    "value":"您好，您%s月的月度综合评价分因（%s）%s %s分，详情请查看“审核员专区-公司考核",
		                    "color":"#173177"
		                    },
		                "keyword1":{
		                    "value":"%s",
		                    "color":"#173177"
		                    },
		                "keyword2":{
		                    "value":"%s",
		                    "color":"#173177"
		                    },
		                "keyword3":{
		                    "value":"%s",
		                    "color":"#173177"
		                    },
		                "remark":{
		                    "value":"对上述减分项如有异议，请联系%s，010-88255986-804",
		                    "color":"#173177"
		                    }
		            }
		        }';
				$weObj = load('Wechat',$arrOptions);
				$weObj->checkAuth();
				
				$unionLogin = $db->getAll('select hr.*,un.unionToken from sp_hr hr left join sp_unionlogin un on hr.id=un.userID and un.status=1 where hr.id='.$_POST['userID']);
				foreach($unionLogin as $item)
				{
					if(!empty($item['unionToken']))
					{
						
						$createUsername = $db->get_var("select name from sp_hr where id =".$params['createUserID']." and deleted=0");
						//截取月份
						$kfdate  =substr($exuInfo['date'],-2);
						$message = sprintf( $messageTem,$item['unionToken'],$kfdate,$exInfo['name'],$yunsuantype,$params['day'],'考核通知',date('Y-m-d H:i:s'),'审核员专区-考核详情',$createUsername);
	            		
	            		$temp    = $weObj  -> sendTemplateMessage( $message );
					}
				}
			}
		}
		$exu_id = $params['exu_id'];
		showmsg( 'success', 'success',"?c=examine&a=userinfolist&exu_id=".$exu_id);
	}
	if($id)
	{
		$detail  = $db->getOne('select * from sp_examine_user_info where id='.$id);
	}else{

		$exuInfo = $db->getOne('select * from sp_examine_user where id='.$exu_id);
		$detail['exu_id']   = $exuInfo['id'];
		$detail['userID']   = $exuInfo['userID'];
	}
	
	$examines = $db->getAll('select * from sp_examine where status=1');
	tpl();
?>