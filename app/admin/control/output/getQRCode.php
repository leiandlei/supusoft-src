<?php
	require_once ROOT.'/framework/PHPQRcode.class.php';
	$zsid = getgp('zsid');
	if(empty($zsid)){
		echo '缺少参数';exit;
	}
	
	$filename = "uploads/QRcode/zs".$zsid."code.png";
	if( !file_exists($filename) ){
		$url = "http://cams.lll.cn/?c=output&a=certificateInfo&zsid=".$zsid;
   		QRcode::png_logo($url, $filename,"uploads/QRcode/logo.png",'H',5,0); //生成png图片 
	}
   	// echo '<img src="'.$filename.'" />';