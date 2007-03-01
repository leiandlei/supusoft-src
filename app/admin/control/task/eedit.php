<?php

//证书邮寄
	if($id = getgp('id')){
		$row = load("sms")->get($id);
		extract($row, EXTR_SKIP);
		
	}

	if($sms_date == '0000-00-00'){
		$sms_date = '';
	}

	tpl();