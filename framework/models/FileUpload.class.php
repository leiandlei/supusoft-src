<?php
class FileUpload {

	//返回一组有用信息，用于提示用户。
	private $save_info = array(); 

	//错误信息
	private $error = '';

	//用户上传的文件
	public $user_post_file = '';

	//存放用户上传文件的路径
	public $save_file_path = '';

	//文件最大尺寸
	public $max_file_size = 2097152;

	//默认允许用户上传的文件类型
	public $allow_type = array('gif', 'jpg', 'png', 'zip', 'rar', 'txt', 'doc','docx','xls', 'pdf');
 
 	public function __get($name){
        if(isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name,$value){
        if(isset($this->$name)) {
            $this->$name = $value;
        }
    }

    public function __isset($name){
        return isset($this->$name);
    }
 
	public function __construct($config=array()) {
		if( is_array($config) ){
			$this->user_post_file = $config['file'];
			$this->save_file_path = $config['path'];
			$this->max_file_size  = $config['size'];  //如果用户不填写文件大小，则默认为2M.
			if ($type != '')$this->allow_type = $config['type'];
		}
		return $config;
	}
 
 
	public function upload() {

		//检测上传文件
		if(empty($this->user_post_file)&&empty($_FILES['file'])){
			$this->error .= $error = '没有上传文件';
            $this->halt($error);
            return false;
		}
		if(empty($this->user_post_file))$this->user_post_file=!empty($_FILES['file'])?$_FILES['file']:'';

        // 检查上传目录
        if(!is_dir($this->save_file_path)) {
            // 检查目录是否编码后的
            if(is_dir(base64_decode($this->save_file_path))) {
                $this->save_file_path	=	base64_decode($this->save_file_path);
            }else{
                // 尝试创建目录
                if(!mkdir(iconv("UTF-8", "GBK",$this->save_file_path),0777,true)){
                    $this->error .= $error = '上传目录'.$this->save_file_path.'不存在';
                    $this->halt($error);
                    return false;
                }
            }
        }else {
            if(!is_writeable($this->save_file_path)) {
                $this->error .= $error = '上传目录'.$this->save_file_path.'不可写';
                $this->halt($error);
                return false;
            }
        }
        if( is_array($this->user_post_file['name']) ){
        	for ($i = 0; $i < count($this->user_post_file['name']); $i++) {
				//如果当前文件上传功能，则执行下一步。
				if ($this->user_post_file['error'][$i] == 0) {
					//取当前文件名、临时文件名、大小、扩展名，后面将用到。
					$file['name']      = $this->user_post_file['name'][$i];
					$file['tmpname']   = $this->user_post_file['tmp_name'][$i];
					$file['size']      = $this->user_post_file['size'][$i];
					$file['mime_type'] = $this->user_post_file['type'][$i];
					$file['type']      = $this->getFileExt($this->user_post_file['name'][$i]);
					$file['error']     = $this->user_post_file['error'][$i];
					
					//存储当前文件的有关信息，以便其它程序调用。
					$this->save_info[] = $this->save($file);
				}
			}
        }else{
    		$file['name']      = $this->user_post_file['name'];
			$file['tmpname']   = $this->user_post_file['tmp_name'];
			$file['size']      = $this->user_post_file['size'];
			$file['mime_type'] = $this->user_post_file['type'];
			$file['type']      = $this->getFileExt($this->user_post_file['name']);
			$file['error']     = $this->user_post_file['error'];

			//存储当前文件的有关信息，以便其它程序调用。
			$this->save_info[] = $this->save($file);
        }
		return $this->save_info; //返回上传成功的文件数目
	}

	//保存文件
	private function save( $file=array() ){
		extract($file,EXTR_OVERWRITE);
		//检测当前上传文件大小是否合法。
		if (!$this->checkSize($size)) {
			$this->error .= $error = $name."超过允许上传的大小 <br />";
			$this->halt($error);
			return false;
		}
		//检测当前上传文件扩展名是否合法。
		if (!$this->checkType($type)) {
			$this->error .= $error = $name."不是允许上传的文件类型 <br />";
			$this->halt($error);
			return false;
		}
		//检测当前上传文件是否非法提交。
		if(!is_uploaded_file($tmpname)) {
			$this->error .= $error = "无效文件: ".$name." <br />";
			$this->halt($error);
			return false;
		}

		//为防止上传的中文名乱码，先转成gb2312，最后再转回
		$name=iconv("UTF-8","gb2312", $name);
		
		//移动文件后，重命名文件用。
		$basename = $this->getBaseName($name, ".".$type);
		
		//移动后的文件名
		$saveas = $basename."-".time().rand(100,999).".".$type;

		//组合新文件名再存到指定目录下，格式：存储路径 + 文件名 + 时间 + 扩展名
		$final_file_path = $this->save_file_path.$saveas;

		if(!move_uploaded_file($tmpname, $final_file_path)) {
			$this->error .= $error;
			$this->halt($error);
			return false;
		}
		$name=iconv("gb2312","UTF-8", $name);
		$saveas=iconv("gb2312","UTF-8", $saveas);
		$final_file_path=iconv("gb2312","UTF-8", $final_file_path);
		
		//返回前文件的有关信息，以便其它程序调用。
		return array(
					 "name"      => $name
					,"type"      => $type
					,"mime_type" => $mime_type
					,"size"      => $size
					,"saveas"    => $saveas
					,"path"      => $final_file_path
				);
	}
 
	//获取保存文件名
	public function getSaveInfo() {
		return $this->save_info;
	}

	//检测文件是否符合大小
	public function checkSize($size) {
		if(empty($this->max_file_size))return true;
		if ($size > $this->max_file_size)return false;else return true;
	}

	//检测文件是否合法允许上传
	public function checkType($extension) {
		if(empty($this->allow_type))return true;
		foreach ($this->allow_type as $type) {
			if (strcasecmp($extension , $type) == 0)
			return true;
		}
		return false;
	}
	 
	 
	public function halt($msg) {
		return sprintf("<b>文件上传失败 Error:</b> %s <br>\n", $msg);
	}

	//获取文件后缀名
	public function getFileExt($filename) {
		$stuff = pathinfo($filename);
		return $stuff['extension'];
	}
	
	//获取文件名
	public function getBaseName($filename, $type) {
		$basename = basename($filename, $type);
		return $basename;
	}

	//获取错误信息
	public function getErrorMsg(){
		return '<b>文件上传失败 Error:</b>'.$this->error;
	}
}
?>