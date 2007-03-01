<?php
//编辑
// echo '<pre />';
//  print_r($_FILES);exit;
if( !empty($_POST['sub'])&&$_POST['sub']==1 ){
	$params = array(
		 'code'           => $_POST['code']
		,'name'	          => $_POST['name']
		,'color'          => $_POST['color']
		,'weight'         => $_POST['weight']
		,'note'           => $_POST['note']
		,'type_level'     => $_POST['type_level']
		,'type_shuxing'   => $_POST['type_shuxing']
		,'status'         => 1
		,'modifyTime'     => date("Y-m-d H:i:s")
	);
	if( !empty($_FILES['file']['tmp_name']) ){
		$upload = load('FileUpload');$upload->save_file_path=ROOT.'/uploads/file/'.date('Y-m-d').'/';
		if (!$upload->upload()) {
	       // 上传错误提示错误信息
	       showmsg($upload->getErrorMsg() , 'error','?c=docmanage&a=doc_edit');
	       exit;
    	} else {
    		//上传成功
    		$info = $upload->getSaveInfo();
    		$url  = '';
    		foreach ($info as $value) {
    			$url .= $value['path'].'|||';
    		}
    		$url = substr($url,0,strlen($url)-3);
    		$url = str_replace('\\','/',$url);
    		$url = mysql_real_escape_string($url);
    		$params['content'] = $url;
    	}
	}

	if( !empty($_POST['id']) ){//修改
		$id=$_POST['id'];
		if( !empty($params['content']) ){
			$oldContent = $db->get_var('select `content` from `sp_docmanage` where id='.$id);
			if( !empty($oldContent) ){
				$oldContentArray = explode('|||',$oldContent);
				foreach ($oldContentArray as $value) {
					if( file_exists($value) ){
						//@unlink($value);
					}
				}
			}
		}
		$db -> update( 'docmanage',$params,array('id'=>$id),false );
	}else{//添加
		$params['createTime']     = date("Y-m-d H:i:s");
		$id = $db -> insert( 'docmanage',$params,false );
	}
}

$getID = getgp('id');
$id = !empty($id)?$id:(!empty($getID)?$getID:'');
if( !empty($id) ){
	$sql = 'select * from `sp_docmanage` where `id`='.$id;
	$results = $db -> getOne($sql); 
	extract($results,EXTR_OVERWRITE);
}
tpl();
?>
