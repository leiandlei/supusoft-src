<?php
	require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
	feiyong::$month = getgp('month');
	
	$ctfrom         = getgp('ctfrom');
	$type           = getgp('type');
	
	if( empty($ctfrom) )
	{
		$results  = feiyong::gethezuofang();

		$tpl      = '';
	}else
	{
		feiyong::$ctfrom = $ctfrom;
		switch ($type)
		{
			case 'list':
				$results  = feiyong::hezuofanglist();
				$tpl      = 'hezuofanglist';
				break;
			case 'unlist':
			default:
				$results  = feiyong::unhezuofanglist();
				$tpl      = 'hezuofangunlist';
				break;
		}
	}

	$results  = $results['results'];
	// echo "<pre />";
	// print_r($results);exit;
	$month    = feiyong::$month;
	
	tpl($tpl);
