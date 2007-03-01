<?php

$ctfrom  =  getgp('ctfrom');
$id      =  getgp('id');
$ct_info =  $db->get_row("SELECT id,code FROM sp_partner_coordinator WHERE code = '".$ctfrom."' and id='".$id."'");
$pco_id  =  $ct_info['id'];


$step    =  getgp( 'step' );
if( $step ){
	//@HBJ 2013-09-16 重写全部的上传部分，采用upload上传类 
	//@zbzytech 有错误 LINUX 对于最后一个'\\'的识别造成错误
  	// $upload = load('upload');$upload->savePath = get_option('upload_ep_dir') . date('Ymd').'\\';
	$upload = load('upload');$upload->savePath = get_option('upload_ep_dir') . date('Ymd').'/';
	$filename2fd = array();
	foreach($_FILES['archive']['name'] as $key=>$value) {
		if(!empty($value)) {
			$filename2fd[$value] = array(
											'ftype'				=>$_POST['ftype'][$key],
											'description'		=>$_POST['description'][$key],
										);
		}
	}
	if(!$upload->upload()) {
		// 上传错误提示错误信息 
 		showmsg($upload->getErrorMsg(), 'error',"?c=concert_sheet&a=upload&id=$_POST[id]&ctfrom=$_POST[ctfrom]");exit;
	}else{
		// 上传成功 获取上传文件信息

		$info   = $upload->getUploadFileInfo();
		$attach	= load( 'attachment' );
 		foreach($info as $key=>$value) {
			$new_attach = array(
				'pco_id'	    => $pco_id,
				'ctfrom'		=> $ctfrom,
				'name'			=> $value['name'],
				'ext'			=> $value['extension'],
				'size'			=> filesize( $value['savepath'] . $value['savename'] ),
				'filename'		=> date('Ymd').'/'.$value['savename'],
				'ftype'			=> $filename2fd[$value['name']]['ftype'],
				'description'	=> $filename2fd[$value['name']]['description'],
			);
			$id = $attach->add( $new_attach );
		}
	}
	$returnUrl = getgp('returnUrl');
	if( !empty($returnUrl) )showmsg( 'success', 'success', $returnUrl );
	showmsg( 'success', 'success', "?c=concert_sheet&a=upload&id=$pco_id&ctfrom=$ctfrom" );
} else {
	//已上传的文档
	$ct_archives  = array();
	$sql          = "select sa.name,sa.ext,sa.create_date,sa.create_user,sa.description,sa.id from sp_attachments as sa LEFT join sp_partner_coordinator as apc on sa.pco_id=apc.id where apc.code =sa.ctfrom and apc.id='".$pco_id."' and apc.deleted =0";
	$ct_archives  =$db->getAll($sql);
	tpl( 'concert_sheet/upload' );
}
?>