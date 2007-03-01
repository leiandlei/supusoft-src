<?php
/* 
* @Author: mantou
* @Date:   2017-11-20 14:53:13
* @Last Modified by:   mantou
* @Last Modified time: 2017-11-22 14:31:44
*/
require_once ROOT . '/framework/models/feiyong.class.php';//接口文件
    $status          = getgp('status')?getgp('status'):'1';
    feiyong::$month  = getgp('month');
    feiyong::$ctfrom = getgp('ctfrom');
    $ctfrom          = getgp('ctfrom');
    $ctfrom_select   = f_ctfrom_select();//合同来源
    $ctfrom_select   = str_replace( "value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>" , $ctfrom_select );
	$month           = !empty(feiyong::$month)?feiyong::$month:date('Y-m',strtotime("next month"));
    $export          = getgp('export');
	
    switch($status)
    {
        case '1'://未结算
            $results  = feiyong::unyusuansheet();
            break;
        case '2'://已结算
            $results  = feiyong::yusuansheet();
            break;
    }
	$results         = $results['results'];
if (!$export) {
    tpl();
} else {
    ob_start();
    tpl('xls/yusuansheet');
    $data = ob_get_contents();
    ob_end_clean(); 
    export_xls($hire_array[$is_hire] . '预算单', $data);
}
?>
