<?php 
//删除证书 
	$zid=getgp('zsid'); 
	$pid=getgp('pid'); 
	$type = getgp("type");
	if($type=='alist'){
		
		$db->update("project",array("ifchangecert"=>2),array("id"=>$pid));
		
	}else{

	if($zid){
		if(!empty($pid)){
			$db->update("project",array("ifchangecert"=>1),array("id"=>$pid));
		}
		$zsid    = $certificate->del($zid);
		// $zhengshulist = $db->get_row("select * from sp_certificate where id='".$zid."' and  deleted =0");
		// $xiangmulist  = $db->get_row("select * from sp_project where ct_id='".$zhengshulist['ct_id']."' and cti_id ='".$zhengshulist['cti_id']."' order by id desc ");
		// $db -> update( 'project',array("ifchangecert"=>1),array('id'=>$xiangmulist['id']) );
		
	}  
	$cert_info=$certificate->get($zid);
	log_add($cert_info['eid'], 0, "[说明:删除证书].编号：".$cert_info['certno'],'','');
	}
	showmsg( 'success', 'success', $_SERVER['HTTP_REFERER']);