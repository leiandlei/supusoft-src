<?php
$pd_id   = (int)getgp('pd_id');
$ct_id   = (int)getgp('ct_id');
$eid     = (int)getgp('eid');
$tid     = (int)getgp('tid');
$ftype   = getgp('ftype');
$ct_info = $db->get_row("SELECT eid,ct_code,ctfrom FROM sp_contract WHERE ct_id = $ct_id");
if( !empty($_FILES['file']['name']) ){
	$upload = load('upload');$upload->savePath = get_option('upload_ep_dir') . date('Ymd').'/';
	if(!$upload->upload()) {
		// 上传错误提示错误信息 
 		showmsg($upload->getErrorMsg(), 'error',"?c=contract&a=upload&eid=$_POST[eid]&ct_id=$_POST[ct_id]");exit;
	}else{
		// 上传成功 获取上传文件信息
		$info   = $upload->getUploadFileInfo();
		$attach	= load( 'attachment' );
 		foreach($info as $key=>$value) {
			$new_attach = array(
				'eid'			=> $eid,
				'ct_id'			=> $ct_id,
				'tid'           => $tid,
				'name'			=> $value['name'],
				'ctfrom'		=> $ct_info['ctfrom'],
				'ext'			=> $value['extension'],
				'size'			=> filesize( $value['savepath'] . $value['savename'] ),
				'filename'		=> date('Ymd').'/'.$value['savename'],
				'ftype'			=> $ftype
			);

			$id = $attach->add( $new_attach );
			// 日志
			log_add($eid, 0, "[说明:文档上传]"."<合同编号:".$ct_info['ct_code'].">", NULL, serialize($new_attach));
		}
	}
	showmsg( 'success', '上传成功', "?c=assess&a=edit&pd_id={$pd_id}&ct_id={$ct_id}&tid={$tid}" );
}else{
	showmsg( 'error', '上传失败', "?c=assess&a=edit&pd_id={$pd_id}&ct_id={$ct_id}&tid={$tid}" );
}
?>
