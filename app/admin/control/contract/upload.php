<?php
$ct_id = (int)getgp('ct_id');  

$ct_info = $db->get_row("SELECT eid,ct_code,ctfrom FROM sp_contract WHERE ct_id = $ct_id");
$eid=$ct_info['eid'];
$step = getgp( 'step' );
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
 		showmsg($upload->getErrorMsg(), 'error',"?c=contract&a=upload&eid=$_POST[eid]&ct_id=$_POST[ct_id]");exit;
	}else{
		// 上传成功 获取上传文件信息
		$info   = $upload->getUploadFileInfo();
		$attach	= load( 'attachment' );
 		foreach($info as $key=>$value) {
			$new_attach = array(
				'eid'			=> $eid,
				'ct_id'			=> $ct_id,
				'name'			=> $value['name'],
				'ctfrom'		=> $ct_info['ctfrom'],
				'ext'			=> $value['extension'],
				'size'			=> filesize( $value['savepath'] . $value['savename'] ),
				'filename'		=> date('Ymd').'/'.$value['savename'],
				//'filename'		=> date('Ymd').$value['savename'],
				'ftype'			=> $filename2fd[$value['name']]['ftype'],
				'description'	=> $filename2fd[$value['name']]['description'],
			);
			$id = $attach->add( $new_attach );
			// 日志
			do {
				log_add($eid, 0, "[说明:文档上传]"."<合同编号:".$ct_info['ct_code'].">", NULL, serialize($new_attach));
			}while(false);
		}
	}
	$returnUrl = getgp('returnUrl');
	if( !empty($returnUrl) )showmsg( 'success', 'success', $returnUrl );
	showmsg( 'success', 'success', "?c=contract&a=upload&ct_id={$ct_id}" );
} else {
	 //合同附件上传类型
	$allow_types = array( '1001', '1002', '1003', '1004', '1005','1006','1007','1008','1009','1010','2001','2009');
 	$arctype_select=f_select('arctype','',$allow_types);

	//已上传的文档
	$uploaded_file = $db->get_var("SELECT uploaded_files FROM sp_contract WHERE ct_id = '$ct_id'");
	$uploaded_files = explode( ',', $uploaded_file );

	//获取合同的体系
	$ct_isos = array();
	$query = $db->query("SELECT iso FROM sp_contract_item WHERE ct_id = '$ct_id' AND deleted = 0");
	while( $rt = $db->fetch_array( $query ) ){
		$ct_isos[] = $rt['iso'];
	}
	$ct_isos = array_unique( $ct_isos );
	$where_arr = array();
	foreach( $ct_isos as $iso ){
		if( 'A01' == $iso )
			$where_arr[] = "iso & 1";
		elseif( 'A02' == $iso )
			$where_arr[] = "iso & 2";
		elseif( 'A03' == $iso )
			$where_arr[] = "iso & 4";
		elseif( 'F' == $iso )
			$where_arr[] = "iso & 8";
	}

	//获取要上传的文档列表
	$xq_attachs = array();
	if( $where_arr ){
		$query = $db->query("SELECT id,filename FROM sp_settings_attach WHERE 1 AND (".implode(' OR ',$where_arr).") AND deleted = 0 AND type='contract_upload'");
		while( $rt = $db->fetch_array( $query ) ){
			$xq_attachs[$rt['id']] = $rt['filename'];
		}
	}

	//已上传的文档
	$ct_archives = array();
	$archive_join = " LEFT JOIN sp_hr hr ON hr.id = a.create_uid";
	$sql = "SELECT a.*,hr.name author FROM sp_attachments a $archive_join WHERE a.ct_id = '$ct_id' AND a.ftype IN ('".implode("','",$allow_types)."') ORDER BY a.id ASC";
	$query = $db->query( $sql );
	while( $rt = $db->fetch_array( $query ) ){
		$rt['ftype_V'] = f_arctype( $rt['ftype'] );
		$ct_archives[$rt['id']] = $rt;
	}

	tpl( 'contract/upload' );
}
?>