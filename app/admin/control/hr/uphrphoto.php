<?php
$upload           = load('upload');
$upload->savePath = get_option('upload_hr_photo_dir');
$upload->savename = $_REQUEST['uid'] . ".jpg";
if (!$upload->upload()) {
    // 上传错误提示错误信息
    showmsg($upload->getErrorMsg(), 'error');
    exit;
} else {
    // 上传成功 获取上传文件信息
    $info = $upload->getUploadFileInfo();
    @unlink($info[0]['savepath'] . $_REQUEST['uid'] . ".jpg");
    @rename($info[0]['savepath'] . $info[0]['savename'], $info[0]['savepath'] . $_REQUEST['uid'] . ".jpg");
}
$audit_e = getgp('audit_e');
if ($audit_e) {
    showmsg('success', 'success', "?c=auditor&a=my");
} else {
    showmsg('success', 'success', "?c=hr&a=edit&uid={$_REQUEST['uid']}#edit-hrphoto");
}
 
  