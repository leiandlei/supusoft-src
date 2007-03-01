<?php
 
$file =load('files_setting');
 
$step = getgp( 'step' );
if( empty( $a ) || 'list' == $a ) {
	$datas = array();
	$sql =  "SELECT * FROM sp_settings_files WHERE 1  AND deleted=0  and status > 0 and parent='0' ORDER BY num ASC" ;
	$query = $db->query($sql);
	while( $rt = $db->fetch_array( $query ) ){
		$datas[] = $rt;
		$sql = "select * from sp_settings_files where 1  AND deleted=0  and status >0 and parent='$rt[id]' order by num asc";
		$res = $db->query($sql);
		while($row=$db->fetch_array($res)){
			$datas[] = $row;
			$sql2 = "select * from sp_settings_files where 1  AND deleted=0  and status >0 and parent='$row[id]' order by num asc";
			$res2 = $db->query($sql2);
			while($row2=$db->fetch_array($res2)){
				$datas[] = $row2;
				$sql3 = "select * from sp_settings_files where 1  AND deleted=0  and status >0 and parent='$row2[id]' order by num asc";
				$res3 = $db->query($sql3);
				while($row3=$db->fetch_array($res3)){
					$datas[] = $row3;
					$sql4 = "select * from sp_settings_files where 1  AND deleted=0  and status >0 and parent='$row3[id]' order by num asc";
					$res4 = $db->query($sql4);
					while($row4=$db->fetch_array($res4)){
						$datas[] = $row4;
					}
				}
			}
		}
	}

	tpl('setting/list_files');
}else if($a=='add'){
	$id=getgp('id');
	$step = 'add';
	if($id){
		$files_info = $file->get($id);
		$up_name = $files_info['name'];
		$parent = $id;
		$lv = $files_info['lv']+1;
	}else{
		$up_name = '顶级目录';
	}
	$title_tip = '增加目录';
	tpl('setting/edit_files');
}else if($a=='edit'){
	$id=getgp('id');
	$row = $file->get($id);
	extract( $row, EXTR_SKIP );
	if($parent){
		$up_name = $db->get_var("select name from sp_settings_files where id='$parent' ");
	}else{
		$up_name = '顶级目录';
	}
	$title_tip = '编辑目录';
	tpl('setting/edit_files');
}else if($a=='save'){
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	if(!$lv)$lv=1;
	if(!$num)$num=0;
	$value['parent'] = $parent;
	$value['name'] = trim($name);
	$value['num'] = $num;
	$value['lv'] = $lv;
	if($id&&!$step){
		$file->edit($id, $value);
	}else{
		$file->add($value);
	}
	$REQUEST_URI='?c=setting_files&a=list';
	showmsg( 'success', 'success', $REQUEST_URI );
} elseif($a== 'del'){
	$id=getgp('id');
	$file->del($id);
	$REQUEST_URI='?c=setting_files&a=list';
	showmsg( 'success', 'success', $REQUEST_URI );

}else if($a=='check_id'){
	$id=getgp('id');
	$sql = "select * from sp_settings_files where parent='$id' and status = 1";
	$info = $db->get_row($sql);
	if($info){
		echo 'ok';
	}else{
		echo 'no';
	}
	exit;
}

?>