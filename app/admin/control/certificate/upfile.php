<?php
//审核资料
$tid = (int)getgp('tid');
$ct_id = (int)getgp('ct_id');
    $upload = load('upload');
	$upload->savePath = get_option('upload_ep_dir') . date('Ymd') . '/'; //LINUX 对于最后一个'\\'的识别造成错误 所以修改为/
    $filename2fd = array();
	foreach ($_FILES['archive']['name'] as $key => $value) {
        if (!empty($value)) {
			$f=$db->get_var("SELECT * FROM `sp_attachments` WHERE `name` = '$value' AND `tid` = '$tid'");
			if($f){
			
				$REQUEST_URI = "?c=certificate&a=save_edit&tid=$tid";
				showmsg($value.'该文件重名，请重新上传！', 'error', $REQUEST_URI);
				exit;
			}
            $filename2fd[$value] = array(
                'ftype' => $_POST['ftype'][$key],
            );
        }
    }

    if (!$upload->upload()) {
        // 上传错误提示错误信息
		$tid = (int)getgp('tid');
		$REQUEST_URI = "?c=certificate&a=save_edit&tid=$tid";
        showmsg($upload->getErrorMsg() , 'error',$REQUEST_URI);
        exit;
    } else {
        // 上传成功 获取上传文件信息
        $info = $upload->getUploadFileInfo();
        $eid = (int)getgp('eid');
        
        
        $attach = load('attachment');
        $e_row = $db->get_row("SELECT * FROM sp_enterprises WHERE eid = '$eid'");
        foreach ($info as $key => $value) {
            $new_attach = array(
                'eid' => $eid,
                'tid' => $tid,
				'ct_id' => $ct_id,
                'name' => $value['name'],
                'ctfrom' => $e_row['ctfrom'],
                'ext' => $value['extension'],
                'size' => filesize($value['savepath'] . $value['savename']) ,
                'filename' => date('Ymd') . '/' . $value['savename'],
                'ftype' => $filename2fd[$value['name']]['ftype'],
            );
            $id = $attach->add($new_attach);

        }
    }
    $REQUEST_URI = "?c=certificate&a=save_edit&tid=$tid";
    showmsg('success', 'success', $REQUEST_URI);