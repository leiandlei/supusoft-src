<?php
//下载类
class attachment extends model{
 	public $table = 'attachments';
 	public  $attachdir=''; //下载路径
	
 	function add( $args ){
		global $db;
		$default = array(
			'create_uid'	=> current_user('uid'),
			'create_date'		=> current_time('mysql')
		);
		$args = parse_args( $args, $default );
		$id = $db->insert( $this->table, $args ); 
		return $id;
	}

	function edit( $id, $args ){
		global $db;
		$db->update( $this->table, $args, array( 'id' => $id ) );
	}

	function get( $id ){
		global $db;
		$row = $db->get_row("SELECT * FROM sp_".$this->table." WHERE id = '$id'");
		return $row;
	}

	function down( $id){
		$file_types = array(
			'gif' => 'image/gif',
			'jpg' => 'image/pjpeg',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'bmp' => 'image/bmp',
			'png' => 'image/x-png',
			'pdf' => 'application/pdf',
			'txt'=> 'text/plain',
			'zip' => 'application/x-zip-compressed',
			'rar' => 'application/octet-stream',	
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint'
		);
		$doc = $this->get( $id );
	 
		$attachdir = $this->attachdir;
		$path = $attachdir.$doc['filename'];
		$path = str_replace('uploads/hr/uploads/hr','uploads/hr', $path);
		$path = iconv( 'UTF-8', 'GB2312', $path );
		//$ctype = $file_types[$archive['ext']];
		header('Last-Modified: '.date('D, d M Y H:i:s',time()).' GMT');
		header('Expires: '.date('D, d M Y H:i:s',time()).' GMT');
		header('Cache-control: max-age=86400');
		header('Content-Encoding: none');
		
		//@HBJ 2013-9-18 解决各个浏览器下载兼容问题
		$filename = $doc['name'];    
		$encoded_filename = urlencode($filename);    
		$encoded_filename = str_replace("+", "%20", $encoded_filename);    
		$ua = $_SERVER["HTTP_USER_AGENT"]; 
		if (preg_match("/MSIE/i", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');    
		} elseif (preg_match("/Firefox/i", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8/' . $filename . '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');    
		}
		header('Content-Type: application/octet-stream'); 
		 
		header("Content-Transfer-Encoding: binary");
		echo file_get_contents( $path );
		exit;
	}

	function batdown( $aids ){
		global $db;
		$attachdir = $this->attachdir;
		$files = $names = array();
		
		//@HBJ 2013-9-18 修正人员附件不同造成的批量下载不能下载的问题
		if(isset($_REQUEST['table'])) {
			$table = $_REQUEST['table'];
		}
		else {
			$table = $this->table;
		}
		$query = $db->query( "SELECT * FROM sp_".$table." WHERE id IN (".implode(',',$aids).")" );
		
		while( $rt = $db->fetch_array( $query ) ){
			$file_path = ($attachdir ? $attachdir . '/' : '' ) . $rt['filename'];
			$files[] = iconv( 'UTF-8', 'GB2312', $file_path );
			$names[] = iconv( 'UTF-8', 'GB2312', $rt['name'] );
		}
		$zip = load( 'phpzip' );
		$zip->zipDown( $files, $names );
	}

	function del( $aid ){
		if( empty( $aid ) ) return false;
		global $db;
		$doc = $this->get( $aid );
		$attachdir =$this->attachdir;
		$path =  ($attachdir ? $attachdir . '/' : '' ) . $doc['filename'];
		if( file_exists( $path ) ){
			@unlink( $path );
		}
		if( !file_exists( $path ) ){ 
			$db->del( $this->table, array( 'id' => $aid ) );
		 
			return true;
		}
		return false;
	}

}

?>