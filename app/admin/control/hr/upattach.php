<?php


//@HBJ 2013-09-16 重写全部的上传部分，采用upload上传类
    $upload =load('upload');$upload->savePath = get_option('upload_hr_dir') . date('Ymd').'/';
    $filename2fd = array();
    foreach ($_FILES['archive']['name'] as $key => $value) {
        if (!empty($value)) {
            $filename2fd[$value] = array(
                'ftype' => $_POST['ftype'][$key],
                'description' => $_POST['description'][$key],
            );
        }
    }
    if (!$upload->upload()) {
        // 上传错误提示错误信息
        showmsg($upload->getErrorMsg() , 'error');
        exit;
    } else {
        // 上传成功 获取上传文件信息
        $info = $upload->getUploadFileInfo();
        $uid = (int)getgp('uid');
		
        $attach = load('attachment');
		$attach->table='hr_archives';
        foreach ($info as $key => $value) {
            $new_attach = array(
                'uid' => $uid,
                'name' => $value['name'],
                'ext' => $value['extension'],
                'size' => filesize($value['savepath'] . $value['savename']) ,
                'filename' => $value['savepath'].$value['savename'],
                'ftype' => $filename2fd[$value['name']]['ftype'],
                'description' => $filename2fd[$value['name']]['description'],
            );
            $id = $attach->add($new_attach);
        }
    }

    if( $_REQUEST['hrcodelist']==1 )
        showmsg('success', 'success', "?c=hr_code&a=edit&uid={$uid}&iso=iso={$_REQUEST['iso']}&id={$_REQUEST['id']}&qua_id={$_REQUEST['qua_id']}#tab-archive");
    showmsg('success', 'success', "?c=hr&a=edit&uid={$uid}#tab-archive");