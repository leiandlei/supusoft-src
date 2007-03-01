<?php
	header("Content-Type: text/html; charset=utf-8");
	use \Workerman\Worker;
	use \Workerman\Lib\Timer;
	require_once 'workerman/Autoloader.php';
	require_once 'lib/Wechat.class.php';
	require_once 'lib/Api.class.php';
	empty($_SESSION)&&session_start();
	$accessToken= $list = array();
	$arrOptions = array(
					 'appid'          => 'wx767442a77474c183'
					,'appsecret'      => '89250f4d7afaa84fd0cd030f08589146'
					,'encodingaeskey' => 'Ph2KYWNqmUP5lEk8jVfGCm3W4H4idqHqi9fkUWEMJka'
					,'token'          => 'lll'
					,'debug'          => '1'
				);
	$apiConfig  = array(
					// 'API_URL' => 'http://ceshi.lll.cn/discfortest/cams/weixin/api/index.php/Home/'
					'API_URL' => 'http://cams.lll.cn/weixin/api/index.php/Home'
				);
	$messageTem =
			'{
	            "touser":"%s",
	            "template_id":"7qywxhkZYITn3CuQyUwKqS0eGnKaIXHE2cd9x_hD1H4",
	            "topcolor":"#FF0000",
	            "data":{
	                "first": {
	                    "value":"%s您好，今天的审核任务不要忘记签到哦",
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
	                    "value":"如有需要请联系010-88689817",
	                    "color":"#173177"
	                    }
	            }
	        }';
	

	$api   = new Api($apiConfig);
	$api -> set('userInfo',array());
	$weObj = new Wechat($arrOptions);

	/**
	 * 写入日志
	 * @param  string $p_content 内容
	 * @return
	 */
	function file_put_log($p_content='',$logName='')
    {
        file_put_contents(
        				 sprintf(__dir__.'/logs/%s.log',$logName.'--'.date('Y-m-d'))
        				,sprintf(
	        						"[%s] [%s] \n"
	                                ,date('H:i:s')
	                                ,is_string($p_content)?$p_content:json_encode($p_content, JSON_UNESCAPED_UNICODE)
                                )
                        ,FILE_APPEND);
    }

    //获取列表
	function getShenHeList($date = '')
	{
		global $api,$list;
		$date = !empty($date)?$date:date('Y-m-d H:i:s');
		$day  = substr($date,0,10);
		$time = substr($date,11);
		
		$times = array('06:00:00','13:00:00');
		if( in_array($time,$times) )
		{
			echo $date.'  获取数据.....'."\n";
			$list    = array();
			$results = $api -> httpToApi('Task/getAuditTaskList',array('date'=>$date));
			$results = $results['results'];
			if( !empty($results) ){
				foreach ($results as $v)
				{
					//判断是否点击了推送和是否确认现场审核
					if ($v['if_push']=='1' || ($v['audit_type']=='1002'&&$v['is_site']=='0'))continue; 
					
					if( $day==substr($v['taskbegindate'],0,10) )
					{
						$datekey = substr($v['taskbegindate'],11,3).(substr($v['taskbegindate'],14,2)-5).substr($v['taskbegindate'],16,3);
						$list[$datekey]['jinchang'][] = $v;
					}else
					{
						$list['08:25:00']['jinchang'][] = $v;
					}

					if( $day==substr($v['taskenddate'],0,10) )
					{
						$datekey = substr($v['taskenddate'],11,3).(substr($v['taskenddate'],14,2)-5).substr($v['taskenddate'],16,3);
						$list[$datekey]['tuichang'][] = $v;
					}else
					{
						$list['17:25:00']['tuichang'][] = $v;
					}
				}
				file_put_log(json_encode($list),'getlist');
				echo $date.'  获取数据成功'."\n\n";
			}
		}
		return $list;
	}

	//微信推送
	function WechatToMsg($date = '')
	{
		$date    = !empty($date)?$date:date('Y-m-d H:i:s');
		$day     = substr($date,0,10);
		$time    = substr($date,11);
		$results = getShenHeList($date);

		if( empty($results)||empty($results[$time]) )return false;
		$results = $results[$time];

		global $weObj,$messageTem;
		$weObj->checkAuth();

		foreach ($results as $type => $vo)
		{

			if(empty($vo))continue;
			$templateMessage = array();
			foreach ($vo as $v)
			{
				// if( !in_array($v['uid'],array('101','154')) )continue;
				if( empty($v['uniontoken']) )continue;
				$message = '';
				switch ($type)
				{
					case 'jinchang':
					case 'tuichang':
						$message = sprintf( $messageTem,$v['uniontoken'],$v['name'],$v['ep_name'],$v['taskbegindate'].'至'.$v['taskenddate'],$v['areaaddr'] );
						break;
					default:
						break;
				}
				if( !empty($message) )
				{
					// $templateMessage[] = $message;
					$templateMessage[] = $weObj->sendTemplateMessage( $message );
				}
				
			}
			if( !empty($templateMessage) )
			{
				echo $date.'  推送成功共'.count($templateMessage).'条'."\n\n";
				file_put_log($templateMessage,'message');
			}
		}
		return true;
	}
	$task = new Worker();
	// 开启多少个进程运行定时任务，注意多进程并发问题
	$task->count = 1;
	$task->onWorkerStart = function($task)
	{
	    // 每$time_interval秒执行一次
	    $time_interval = 1;
	    Timer::add($time_interval,'WechatToMsg');
	};

	// 运行worker
	Worker::runAll();