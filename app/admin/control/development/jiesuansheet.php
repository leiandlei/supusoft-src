<?php
/* 
* @Author: mantou
* @Date:   2017-11-20 14:53:13
* @Last Modified by:   mantou
* @Last Modified time: 2017-11-22 12:48:17
*/
require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
    feiyong::$month  = getgp('month');
    $status          = getgp('status')?getgp('status'):'1';
	$month           = !empty(feiyong::$month)?feiyong::$month:date('Y-m');
    $export          = getgp('export');
    feiyong::$ctfrom = getgp('ctfrom');
    $ctfrom          = getgp('ctfrom');
    $ctfrom_select   = f_ctfrom_select();//合同来源
    $ctfrom_select   = str_replace( "value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>" , $ctfrom_select );
    switch($status)
	{
		case '1'://未结算
			$results  = feiyong::unjiesuansheet();
			break;
		case '2'://已结算
			$results  = feiyong::jiesuansheet();
			break;
	}
	foreach ($results['results'] as $key => $value) 
	{
		
		switch ($value['couniso']) {
			case '1':
					$results['results'][$key]['countisos']  = "单体系";
				break;
			case '2':
					$results['results'][$key]['countisos']  = "双体系";
				break;
			case '3':
					$results['results'][$key]['countisos']  = "三体系";
				break;
			default:break;
		}
		
		
	}
	//上月结转
	$jiezhuan  = $results['results'][0]['enterprises']['jiezhuan'];
	$yufukuan  = $results['results'][0]['enterprises']['yufukuan'];
	$koushui   = $results['results'][0]['enterprises']['koushui'];
	$results   = $results['results'];

if (!$export) {

    tpl();
} else {
    ob_start();
    tpl('xls/jiesuansheet');
    $data = ob_get_contents();
    ob_end_clean(); 
    export_xls($hire_array[$is_hire] . '结算单', $data);
}
?>
