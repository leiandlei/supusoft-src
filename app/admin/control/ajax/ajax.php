<?php
	$event = null;
    if (array_key_exists('event', $_REQUEST)) {
        $event = $_REQUEST['event'];
        $params  = array();
        if(array_key_exists('data_params',$_REQUEST))$params = $_REQUEST['data_params'];
    }
    if (is_null($event)) {
        exit();
    }

    $results = null;
    switch ($event) {
    	//更改客户申请
        case 'updateshenhexinxi':
            $xmlStr = '{
                        "touser":"%s",
                        "template_id":"qYbAD9U1cGLWuKraoTb8MYQJucY0EHQ4asLdCxjsbHs",
                        "topcolor":"#FF0000",
                        "data":{
                            "first": {
                                "value":"%s您好，企业%s申请认证已通过",
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
                            "remark":{
                                "value":"如有需要请联系010-88689817",
                                "color":"#173177"
                                }
                        }
                    }';
            if($params['status']=='1'){
                $xmlStr = sprintf($xmlStr,$params['json']['uniontoken'],$params['json']['tel_person'],$params['json']['ep_name'],$params['json']['tel_person'],'通过');
                Api::httpToApi('Wechat/sendTemplateMessage',array('data'=>$xmlStr));
            }

            $data = array('id'=>$params['json']['id'],'status'=>$params['status']);
            $results = Api::httpToApi('Renzhengapply/updateApply',$data);
            break;

        case 'updatekefuliuyan':
            $xmlStr = '{
                        "touser":"%s",
                        "template_id":"8EEczqFaB8LkuSiiYgQOWYB1RRrrpwzLwi5KAHWHghU",
                        "topcolor":"#FF0000",
                        "data":{
                            "first": {
                                "value":"%s您好，您的留言已查阅",
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
                            "remark":{
                                "value":"感谢您的使用。如有需要请联系010-88689817",
                                "color":"#173177"
                                }
                        }
                    }';
            if($params['status']=='1'){
                $xmlStr = sprintf($xmlStr,$params['json']['uniontoken'],$params['json']['name'],'已查阅',date('Y-m-d H:i:s'));
                Api::httpToApi('Wechat/sendTemplateMessage',array('data'=>$xmlStr));
            }

            $data = array('id'=>$params['json']['id'],'status'=>$params['status']);
            $results = Api::httpToApi('Message/updateMessage',$data);
            break;

        case 'updateshenheyuanxinxi':
            $xmlStr = '{
                        "touser":"%s",
                        "template_id":"pThW82VduihqA-F6n3GTBzc1bnneXppI_W33PWTV7ZU",
                        "topcolor":"#FF0000",
                        "data":{
                            "first": {
                                "value":"%s您好，您的回传信息已查阅",
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
                                "value":"感谢您的使用。如有需要请联系010-88689817",
                                "color":"#173177"
                                }
                        }
                    }';
            if($params['status']=='1'){
                $xmlStr = sprintf($xmlStr,$params['json']['unionlogin']['uniontoken'],$params['json']['hr']['name'],empty($params['json']['content'])?'微信留言':$params['json']['content'],$params['json']['createtime'],date('Y-m-d H:i:s'),'已查阅');
                Api::httpToApi('Wechat/sendTemplateMessage',array('data'=>$xmlStr));
            }

            $data = array('id'=>$params['json']['id'],'status'=>$params['status']);
            $results = Api::httpToApi('TaskHc/update',$data);
            break;

        //审核员回复
        case 'shenheyuanhiufu':
            $xmlStr = '{
                        "touser":"%s",
                        "template_id":"Jq9ADAyXTD0IdJ05ZfT3lX2zi-MdmLGMteHevBZcQwg",
                        "topcolor":"#FF0000",
                        "data":{
                            "first": {
                                "value":"%s您好，您的回传信息回复如下:",
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
                            "remark":{
                                "value":"%s",
                                "color":"#173177"
                                }
                        }
                    }';
            $xmlStr  = sprintf($xmlStr,$params['json']['unionlogin']['uniontoken'],$params['json']['hr']['name'],$_SESSION['userinfo']['name'],empty($params['data']['tel'])?'010-88689817':$params['data']['tel'],$params['data']['content']);
            $results = Api::httpToApi('Wechat/sendTemplateMessage',array('data'=>$xmlStr));
            break;
            
    	default:
    		break;
    }
    exit( json_encode($results) );