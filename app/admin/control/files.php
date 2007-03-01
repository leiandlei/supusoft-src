<?php
$file = load('files');
$file_set = load('files_setting');
$datas = array();
$sql =  "SELECT * FROM sp_settings_files WHERE 1 and status > 0 and parent='0' ORDER BY num ASC" ;
$query = $db->query($sql);
while( $rt = $db->fetch_array( $query ) ){
	$datas[] = $rt;
	$sql = "select * from sp_settings_files where 1 and status >0 and parent='$rt[id]' order by num asc";
	$res = $db->query($sql);
	while($row=$db->fetch_array($res)){
		$datas[] = $row;
		$sql2 = "select * from sp_settings_files where 1 and status >0 and parent='$row[id]' order by num asc";
		$res2 = $db->query($sql2);
		while($row2=$db->fetch_array($res2)){
			$datas[] = $row2;
			$sql3 = "select * from sp_settings_files where 1 and status >0 and parent='$row2[id]' order by num asc";
			$res3 = $db->query($sql3);
			while($row3=$db->fetch_array($res3)){
				$datas[] = $row3;
				$sql4 = "select * from sp_settings_files where 1 and status >0 and parent='$row3[id]' order by num asc";
				$res4 = $db->query($sql4);
				while($row4=$db->fetch_array($res4)){
					$datas[] = $row4;
				}
			}
		}
	}
}
$files_select = '';
foreach($datas as $value){
	$c ='';
	for($i=2;$i<=$value['lv'];$i++){
		$c .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$files_select .= "<option value='$value[id]' >$c $value[name]</option>";
}
if($a == 'list'){
	$fields = $join = $where = '';
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	if(!$files)$files=getgp('files');
	$where = ' and f.status > 0';
	
	if($files){
		$menu_str1 = "<a href='?c=files&a=list' >全部</a>&nbsp;&nbsp;>>&nbsp;&nbsp;";
		$files_select = str_replace( " value='$files' ", " value='$files' selected " , $files_select );
		$where = " and f.fid = '$files' ";
		$menu_info = $db->get_row("select * from sp_settings_files where id='$files' ");
		$menu_str = $menu_info['name'];
		if($menu_info['parent']){
			$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
			$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
			if($menu_info['parent']){
				$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
				$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
				if($menu_info['parent']){
					$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
					$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
					if($menu_info['parent']){
						$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
						$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
					}
				}
			}
		}
		$menu_str = $menu_str1.$menu_str;
	}else{
		$menu_str = "全部";
	}
	if($up_name){
		$where = " and h.name like '%$up_name%' ";
	}
	if($filename){
		$where .= " and f.filename  like '%$filename%' ";
	}
	if($s_date){
		$where .= " and f.up_date >= '$s_date' ";
	}
	if($e_date){
		$where .= " and f.up_date <= '$e_date' ";
	}
	$join = " inner join sp_hr h on f.update_uid=h.id ";
	$total = $db->get_var("SELECT COUNT(*) FROM sp_files f $join WHERE 1 $where");
	$pages = numfpage( $total, 20, "?c=$c&a=$a" );
	$sql = "SELECT f.*,h.name FROM sp_files f $join WHERE 1 $where ORDER BY f.id DESC $pages[limit]" ;
	$query = $db->query( $sql);
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$rt['filename'] = substr($rt['filename'], strlen($rt['id'])+1);
		$rt['fid'] = $db->get_var("select name from sp_settings_files where id='$rt[fid]' ");
		$rt[update_uid]=f_username($rt['update_uid']);
		$datas[] = $rt;
	}
	tpl( 'files/files_list' );
}elseif($a == 'dlist'){
	$fields = $join = $where = '';
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	if(!$files)$files=getgp('files');
	$where = ' and f.status > 0';
	
	if($files){
		$menu_str1 = "<a href='?c=files&a=list' >全部</a>&nbsp;&nbsp;>>&nbsp;&nbsp;";
		$files_select = str_replace( " value='$files' ", " value='$files' selected " , $files_select );
		$where = " and f.fid = '$files' ";
		$menu_info = $db->get_row("select * from sp_settings_files where id='$files' ");
		$menu_str = $menu_info['name'];
		if($menu_info['parent']){
			$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
			$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
			if($menu_info['parent']){
				$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
				$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
				if($menu_info['parent']){
					$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
					$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
					if($menu_info['parent']){
						$menu_info = $db->get_row("select * from sp_settings_files where id='$menu_info[parent]' ");
						$menu_str = "<a href='?c=files&a=list&files=$menu_info[id]' >".$menu_info['name']."</a>&nbsp;&nbsp;>>&nbsp;&nbsp;".$menu_str;
					}
				}
			}
		}
		$menu_str = $menu_str1.$menu_str;
	}else{
		$menu_str = "全部";
	}
	if($up_name){
		$where = " and h.name like '%$up_name%' ";
	}
	if($filename){
		$where .= " and f.filename  like '%$filename%' ";
	}
	if($s_date){
		$where .= " and f.up_date >= '$s_date' ";
	}
	if($e_date){
		$where .= " and f.up_date <= '$e_date' ";
	}
	$join = " inner join sp_hr h on f.update_uid=h.id ";
	$total = $db->get_var("SELECT COUNT(*) FROM sp_files f $join WHERE 1 $where");
	$pages = numfpage( $total, 20, "?c=$c&a=$a" );
	$sql = "SELECT f.*,h.name FROM sp_files f $join WHERE 1 $where ORDER BY f.id DESC $pages[limit]" ;
	$query = $db->query( $sql);
	$datas = array();
	while( $rt = $db->fetch_array( $query ) ){
		$rt['filename'] = substr($rt['filename'], strlen($rt['id'])+1);
		$rt['fid'] = $db->get_var("select name from sp_settings_files where id='$rt[fid]' ");
		$rt[update_uid]=f_username($rt['update_uid']);
		$datas[] = $rt;
	}
	tpl( 'files/files_dlist' );
}else if($a=='del'){
	$id=getgp('id');
	if($id){
		$file->del($id);
	}
	$REQUEST_URI='?c=files&a=list';
	showmsg( 'success', 'success', $REQUEST_URI );
}else if($a=='down'){
	 
	$id = getgp('id');
	$files_info = $file->get($id);
	$file_dir = get_option('upload_oa_file_dir');
	$file_name = substr($files_info['filename'], strlen($id)+1);
	$file = fopen($file_dir . $file_name,"r"); // 打开文件
	// 输入文件标签
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length: ".filesize($file_dir . $file_name));
	Header("Content-Disposition: attachment; filename=" . iconv("utf-8","gbk",$file_name));
	// 输出文件内容
	echo fread($file,filesize($file_dir . $file_name));
	fclose($file);
	exit();
}else if($a=='add'){

	tpl( 'files/files_edit' );
}else if('save' == $a){
	$url = get_option('upload_oa_file_dir');
 	
	foreach($_POST as $k=>$v){
		${$k} = getgp($k);
	}
	foreach($_FILES['archive']['name'] as $key=>$value){
		if($value){
			$array = array(
				'note' => $note[$key],
				'fid' => $ftype[$key],
			);
			$id = $file->add($array); //插入数组
			move_uploaded_file($_FILES["archive"]["tmp_name"][$key],iconv("utf-8","gbk",$url.$value));
			$file->edit($id, array('filename'=>$id.'_'.$value));
		}
	}
	$REQUEST_URI='?c=files&a=list';
	showmsg( 'success', 'success', $REQUEST_URI );
}

?>