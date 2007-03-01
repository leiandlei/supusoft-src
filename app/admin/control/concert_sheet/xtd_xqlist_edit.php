<?php
/* 
* @Author: anchen
* @Date:   2017-04-14 10:13:58
* @Last Modified by:   mantou
* @Last Modified time: 2017-07-14 11:53:41
*/
$getID    = getgp('id');
$ctfrom   = getgp('ctfrom');
$usertype = $_SESSION['extraInfo']['userType'];

//计划员姓名
$user    = $_SESSION['userinfo']['name'];
$userids = $_SESSION['userinfo']['id'];
//修改
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
		$params['audit_ver']       = implode(',',$params['audit_ver']);
		$params['audit_type']      = getgp('audit_type');
		$params['renshu']          = getgp('renshu');
		$params['scope']           = getgp('scope');
		$params['partner_note']    = getgp('partner_note');
		$params['zhuanye_2017']    = getgp('zhuanye_2017');
		$params['zhuanye']         = getgp('zhuanye');
		$params['total_num']       = getgp('total_num');
		$params['zjrr']            = getgp('zjrr');
		$params['leader']          = getgp('leader');
		$params['zuyuan']          = getgp('zuyuan');
		$params['jszj']            = getgp('jszj');
		$params['manage_scope']    = getgp('manage_scope');
		$params['note']            = getgp('note');
		$params['yjdshsj_start']   = getgp('yjdshsj_start');
        $params['yjdshsj_end']     = getgp('yjdshsj_end');
        $params['ejdshsj_start']   = getgp('ejdshsj_start');
        $params['ejdshsj_end']     = getgp('ejdshsj_end');
        $params['modifyUser']      = $_SESSION['userinfo']['id'];
        $params['modifyTime']      = date('Y-m-d H:i:s');
		
        if ($usertype=='stuff') 
        {
           $params['status']       = getgp('status');
           $params['contract_time']= getgp('contract_time');
        }
        if ($userids=="98"|| $userids=="265")
        {
        	$params['sj_preson']       = getgp('sj_preson');
        }
	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		$db -> update( 'partner_coordinator',$params,array('id'=>$id),false );

	}else{//添加
		$id = $db -> insert( 'partner_coordinator',$params,false );
	}
	
	showmsg( 'success', 'success',"?c=concert_sheet&a=xtd_list&ctfrom=$ctfrom");
}

	//显示
	
	$getID = getgp('id');
	$id = !empty($id)?$id:(!empty($getID)?$getID:'');
	if( !empty($id) )
	{
		$sql      = 'select * from `sp_partner_coordinator`  where deleted=0  and `id`='.$id;
		$results = $db -> getOne($sql);
		extract($results,EXTR_OVERWRITE);
	}
	
	//判断实际安排人员修改权限
	// $str1   = $_SESSION['userinfo']['job_type'];
	// $str2   = '1003';
	// $str3   = '1010';
	// if(strpos($str1,$str2) === false){     //使用绝对等于
 //       $job_type ='0';
 //    }else{
 //       if(strpos($str1,$str3) === false)
 //       {  
	// 		$job_type ='0';
 //       }else{
 //       	    $job_type ='1';
 //       }
 //    }
tpl();
?>
