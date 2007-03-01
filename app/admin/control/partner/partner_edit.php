<?php

if( !empty($_POST['sub'])&&$_POST['sub']==1 )
{
	$params['code']               = getgp('code');
	$params['name']               = getgp('name');
	$params['level']              = getgp('level');
	$params['type']               = getgp('type');
	$params['lead']               = getgp('lead');
	$params['lead_tel']           = getgp('lead_tel');
	$params['contacts_name']      = getgp('contacts_name');
	$params['contacts_name_tel']  = getgp('contacts_name_tel');
	$params['qq']                 = getgp('qq');
	$params['email']              = getgp('email');
    $params['jiesuandian']        = getgp('jiesuandian');
    $params['fanshuidian']        = getgp('fanshuidian');
    $params['paizu']              = getgp('paizu');
    
    $params['username']           = getgp('username');
    $params['note']               = getgp('note');
    $params['sys']                = 'concert_sheet:hezuofang_xtd';

    if (!empty(getgp('password'))) 
    {
    	 $params['password']      = getgp('password');
    }
	if(!empty($params['password']))$params['password']=md5($params['password']);
	
//	if(!empty(getgp('s_id'))){
//		$params['wx_name']            = substr((implode(',',getgp('wx_name'))),0,-1);
//  	$params['wx_nameid']          = substr((implode(',',getgp('s_id'))),0,-1);
//	}
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'partner',$params,array('pt_id'=>$id),false );
	}else{//添加
		$id = $db -> insert( 'partner',$params,false );
	}
	showmsg( 'success', 'success',"?c=partner&a=partner_list");
}

//显示
$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_partner` where `pt_id`='.$id;
	$results = $db -> getOne($sql);
	extract($results,EXTR_OVERWRITE);
}
$partner_select = "<option value=\"1\">优质</option>
					<option value=\"2\">一半</option>
					<option value=\"3\">劣质</option>";
$partner_select = str_replace("value=\"$level\">", "value=\"$level\" selected>", $partner_select);

$pzfs_select = "<option value=\"1\">机构派组</option>
				<option value=\"2\">合作方派组</option>
				<option value=\"3\">混合派组</option>";
$pzfs_select = str_replace("value=\"$paizu\">", "value=\"$paizu\" selected>", $pzfs_select);

tpl('partner_edit');



?>
