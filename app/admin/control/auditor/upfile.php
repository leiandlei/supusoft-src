<?php
//审核资料
$tid = (int)getgp('tid');
$ct_id = (int)getgp('ct_id');
 //@HBJ 2013-09-16 重写全部的上传部分，采用upload上传
    $upload = load('upload');$upload->savePath = get_option('upload_ep_dir') . date('Ymd') . '/';
    $filename2fd = array();
	foreach ($_FILES['archive']['name'] as $key => $value) {
        if (!empty($value)) {
			$f=$db->get_var("SELECT * FROM `sp_attachments` WHERE `name` = '$value' AND `tid` = '$tid'");
			if($f){
			
				$REQUEST_URI = "?c=auditor&a=task_edit&tid=$tid&ct_id=$ct_id";
				showmsg($value.'该文件重名，请重新上传！', 'error', $REQUEST_URI);
				exit;
			}
            $filename2fd[$value] = array(
                'ftype' => $_POST['ftype'][$key],
                'description' => $_POST['description'][$key],
                'sort' => $_POST['sort'][$key],
            );
        }
    }
    if (!$upload->upload()) {
        // 上传错误提示错误信息
		$tid = (int)getgp('tid');
		$REQUEST_URI = "?c=auditor&a=task_edit&tid=$tid&ct_id=$ct_id";
        showmsg($upload->getErrorMsg() , 'error',$REQUEST_URI);
        exit;
    } else {
        // 上传成功 获取上传文件信息
        $info = $upload->getUploadFileInfo();
        $eid = (int)getgp('eid');
        
        
        $attach = load('attachment');
        $e_row = $db->get_row("SELECT * FROM sp_enterprises WHERE eid = '$eid'");
        foreach ($info as $key => $value) {
			$ftype='3001';
			$filename2fd[$value['name']]['ftype'] && $ftype=$filename2fd[$value['name']]['ftype'];
			if($ftype=='3003')$db->update("task",array("upload_plan_date"=>date("Y-m-d H:i:s")),array("id"=>$tid));
            $new_attach = array(
                'eid' => $eid,
                'tid' => $tid,
				'ct_id' => $ct_id,
                'name' => $value['name'],
                'ctfrom' => $e_row['ctfrom'],
                'ext' => $value['extension'],
                'size' => filesize($value['savepath'] . $value['savename']) ,
                'filename' => date('Ymd') . '/' . $value['savename'],
                'ftype' => $ftype,
                'description' => $filename2fd[$value['name']]['description'],
                'sort' => $filename2fd[$value['name']]['sort'],
            );
            $id = $attach->add($new_attach);
        }
    }
    $REQUEST_URI = "?c=auditor&a=task_edit&tid=$tid&ct_id=$ct_id";
    showmsg('success', 'success', $REQUEST_URI);
