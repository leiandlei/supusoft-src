<?php
    $ctfrom     = getgp( 'ctfrom' );
    $usertype   = $_SESSION['extraInfo']['userType'];
	$userid      = $_SESSION['userinfo']['id'];
//添加
    $data       = $db->getAll("select orders from sp_partner_coordinator where code='".$ctfrom."'");
    foreach ($data as $value)
    {
      $orders[] = $value['orders'];
    }
    $maxs    = max($orders)+1;
	if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
		unset($params['id'],$params['sub']);
		$params['code']            = $ctfrom;
		$params['name']            = getgp('name');
		$params['areaaddr']        = getgp('areaaddr');
		$params['epaddr']          = getgp('epaddr');
		$params['person']          = getgp('person');
		$params['person_tel']      = getgp('person_tel');
		$params['station']         = getgp('station');
		$params['audit_ver']       = getgp('audit_ver');
		$params['renshu']          = getgp('renshu');
		$params['scope']           = getgp('scope');
		$params['partner_note']    = getgp('partner_note');
		$params['zhuanye']         = getgp('zhuanye');
		$params['total_num']       = getgp('total_num');
		$params['zjrr']            = getgp('zjrr');
		$params['leader']          = getgp('leader');
		$params['zuyuan']          = getgp('zuyuan');
		$params['jszj']            = getgp('jszj');
		$params['manage_scope']    = getgp('manage_scope');
		$params['note']            = getgp('note');
		$params['yjdshsj_start']   = getgp('yjdshsj_start') . ' ' . getgp('yjdst_time');
        $params['yjdshsj_end']     = getgp('yjdshsj_end') . ' ' . getgp('yjded_time');
        $params['ejdshsj_start']   = getgp('ejdshsj_start') . ' ' . getgp('ejdst_time');
        $params['ejdshsj_end']     = getgp('ejdshsj_end') . ' ' . getgp('ejded_time');
		$params['audit_ver']       = implode(',',$params['audit_ver']);
		$params['createTime']      = date('Y-m-d H:i:s');
		$params['createUserID']    = $userid;
		
		$params['orders']          = $maxs;
		if( !empty($_POST['id']) ){//修改
			$id=$_POST['id'];
			$db -> update( 'partner_coordinator',$params,array('id'=>$id),false );
		}else{//添加
			unset($params['id'],$params['sub']);
			$id = $db -> insert( 'partner_coordinator',$params,false );
		}
		showmsg( 'success', 'success',"?c=concert_sheet&a=xtd_list&ctfrom=$ctfrom");
	}
tpl();
?>
