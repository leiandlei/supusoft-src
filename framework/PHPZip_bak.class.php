<?php
    class PHPZip
    {
        private $ctrl_dir     = array();
        private $datasec      = array();
 
 
        /**********************************************************
         * 压缩部分
         **********************************************************/
        // ------------------------------------------------------ //
        // #遍历指定文件夹
        //
        // $archive  = new PHPZip();
        // $filelist = $archive->visitFile(文件夹路径);
        // print "当前文件夹的文件:<p>\r\n";
        // foreach($filelist as $file)
        //     printf("%s<br>\r\n", $file);
        // ------------------------------------------------------ //
        var $fileList = array();
        public function visitFile($path)
        {
            global $fileList;
            $path = str_replace("\\", "/", $path);
            $fdir = dir($path);
         
            while(($file = @$fdir->read()) !== false)
            {
                if($file == '.' || $file == '..'){ continue; }
         
                $pathSub    = preg_replace("*/{2,}*", "/", $path."/".$file);  // 替换多个反斜杠
                $fileList[] = is_dir($pathSub) ? $pathSub."/" : $pathSub;
                if(is_dir($pathSub)){ $this->visitFile($pathSub); }
            }
            $fdir->close();
            return $fileList;
        }
         
         
        private function unix2DosTime($unixtime = 0)
        {
            $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
     
            if($timearray['year'] < 1980)
            {
                $timearray['year']    = 1980;
                $timearray['mon']     = 1;
                $timearray['mday']    = 1;
                $timearray['hours']   = 0;
                $timearray['minutes'] = 0;
                $timearray['seconds'] = 0;
            }
     
            return (  ($timearray['year'] - 1980) << 25)
                    | ($timearray['mon'] << 21)
                    | ($timearray['mday'] << 16)
                    | ($timearray['hours'] << 11)
                    | ($timearray['minutes'] << 5)
                    | ($timearray['seconds'] >> 1);
        }
         
         
        var $old_offset = 0;
        private function addFile($data, $filename, $time = 0)
        {
            $filename = str_replace('\\', '/', $filename);
            $dtime    = dechex($this->unix2DosTime($time));
            $hexdtime = '\x' . $dtime[6] . $dtime[7]
                      . '\x' . $dtime[4] . $dtime[5]
                      . '\x' . $dtime[2] . $dtime[3]
                      . '\x' . $dtime[0] . $dtime[1];
            eval('$hexdtime = "' . $hexdtime . '";');
     
            $fr       = "\x50\x4b\x03\x04";
            $fr      .= "\x14\x00";
            $fr      .= "\x00\x00";
            $fr      .= "\x08\x00";
            $fr      .= $hexdtime;
            $unc_len  = strlen($data);
            $crc      = crc32($data);
            $zdata    = gzcompress($data);
            $c_len    = strlen($zdata);
            $zdata    = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
            $fr      .= pack('V', $crc);
            $fr      .= pack('V', $c_len);
            $fr      .= pack('V', $unc_len);
            $fr      .= pack('v', strlen($filename));
            $fr      .= pack('v', 0);
            $fr      .= $filename;
     
            $fr      .= $zdata;
     
            $fr      .= pack('V', $crc);
            $fr      .= pack('V', $c_len);
            $fr      .= pack('V', $unc_len);
     
            $this->datasec[] = $fr;
            $new_offset      = strlen(implode('', $this->datasec));
     
            $cdrec  = "\x50\x4b\x01\x02";
            $cdrec .= "\x00\x00";
            $cdrec .= "\x14\x00";
            $cdrec .= "\x00\x00";
            $cdrec .= "\x08\x00";
            $cdrec .= $hexdtime;
            $cdrec .= pack('V', $crc);
            $cdrec .= pack('V', $c_len);
            $cdrec .= pack('V', $unc_len);
            $cdrec .= pack('v', strlen($filename) );
            $cdrec .= pack('v', 0 );
            $cdrec .= pack('v', 0 );
            $cdrec .= pack('v', 0 );
            $cdrec .= pack('v', 0 );
            $cdrec .= pack('V', 32 );
     
            $cdrec .= pack('V', $this->old_offset );
            $this->old_offset = $new_offset;
     
            $cdrec .= $filename;
            $this->ctrl_dir[] = $cdrec;
            return $this->ctrl_dir;
        }
         
         
        var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
        private function file()
        {
            $data    = implode('', $this->datasec);
            $ctrldir = implode('', $this->ctrl_dir);
     
            return   $data
                   . $ctrldir
                   . $this->eof_ctrl_dir
                   . pack('v', sizeof($this->ctrl_dir))
                   . pack('v', sizeof($this->ctrl_dir))
                   . pack('V', strlen($ctrldir))
                   . pack('V', strlen($data))
                   . "\x00\x00";
        }
     
         
        // ------------------------------------------------------ //
        // #压缩到服务器
        //
        // $archive = new PHPZip();
        // $archive->Zip("需压缩的文件所在目录", "ZIP压缩文件名"); 
        // ------------------------------------------------------ //
        public function Zip($dir, $saveName)
        {
            if(@!function_exists('gzcompress')){ return; }
     
            ob_end_clean();
            $filelist = $this->visitFile($dir);
            if(count($filelist) == 0){ return; }
     
            foreach($filelist as $file)
            {
                if(!file_exists($file) || !is_file($file)){ continue; }
                 
                $fd       = fopen($file, "rb");
                $content  = @fread($fd, filesize($file));
                fclose($fd);
 
                // 1.删除$dir的字符(./folder/file.txt删除./folder/)
                // 2.如果存在/就删除(/file.txt删除/)
                $file = substr($file, strlen($dir));
                if(substr($file, 0, 1) == "\\" || substr($file, 0, 1) == "/"){ $file = substr($file, 1); }
                 
                $this->addFile($content, $file);
            }
            $out = $this->file();
     
            $fp = fopen($saveName, "wb");
            fwrite($fp, $out, strlen($out));
            fclose($fp);
        }
     
     
        // ------------------------------------------------------ //
        // #压缩并直接下载
        //
        // $archive = new PHPZip();
        // $archive->ZipAndDownload("需压缩的文件所在目录");
        // ------------------------------------------------------ //
        public function ZipAndDownload($dir,$filename='')
        {
            if(@!function_exists('gzcompress')){ return; }

            ob_end_clean();
            $filelist = $this->visitFile($dir);
            if(count($filelist) == 0){ return; }
     
            foreach($filelist as $file)
            {
                if(!file_exists($file) || !is_file($file)){ continue; }
                 
                $fd       = fopen($file, "rb");
                $content  = @fread($fd, filesize($file));
                fclose($fd);
     
                // 1.删除$dir的字符(./folder/file.txt删除./folder/)
                // 2.如果存在/就删除(/file.txt删除/)
                $file = substr($file, strlen($dir));
                if(substr($file, 0, 1) == "\\" || substr($file, 0, 1) == "/"){ $file = substr($file, 1); }
                 
                $this->addFile($content, $file);
            }
            $out = $this->file();
     
            @header('Content-Encoding: none');
            @header('Content-Type: application/zip');
            @header('Content-Disposition: attachment ; filename='.$filename.rand(1,999999).date("YmdHis", time()).'.zip');
            @header('Pragma: no-cache');
            @header('Expires: 0');
            print($out);
        }


        /*  @creates a compressed zip file  将多个文件压缩成一个zip文件的函数  
        *   @$files 数组类型  实例 1:array("1.jpg","2.jpg");下载文件名为 1.jpg,2.jpg
        *                          2:array(
        *                               array('name'=>'哈哈','url'=>'1.jpg')
        *                              ,array('name'=>'呵呵','url'=>'2.jpg')
        *                            ) 下载名为 哈哈.jpg,呵呵.jpg
        *   @destination  目标文件的路径  如"c:/androidyue.zip"  
        *   @$down      是否下载
        *   @$overwrite 是否为覆盖与目标文件相同的文件  
         */    
        function create_zip($files = array(),$destination = '',$down=true,$overwrite = false) {  
            //如果zip文件已经存在并且设置为不重写返回false    
            if(file_exists($destination) && !$overwrite) { return false; }    
  
            $valid_files = array();    
            //获取到真实有效的文件名    
            if(is_array($files)) {
                foreach($files as $file) {
                    if(is_array($files)){
                        if(file_exists(iconv("UTF-8","GB2312",$file['url']))) {    
                            $valid_files[] = array(
                                     'name' => iconv("UTF-8","GB2312",$file['name'])
                                    ,'url'  => iconv("UTF-8","GB2312",$file['url'])
                                );    
                        }
                    }else{
                       if(file_exists(iconv("UTF-8","GB2312",$file))) {    
                            $valid_files[] = iconv("UTF-8","GB2312",$file);    
                        } 
                    }   
                }    
            }

            //如果存在真实有效的文件    
            if(count($valid_files)) {  
                //create the archive    
                $zip = new ZipArchive();
                //打开文件       如果文件已经存在则覆盖，如果没有则创建    
                if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {    
                    return false;    
                }    
                //add the files    
                //向压缩文件中添加文件
                foreach($valid_files as $file) {
                    if( is_array($file) ){
                        $file_info_arr = pathinfo($file['url']);
                        $filename      = $file['name'].'.'.$file_info_arr['extension'];
                        $zip->addFile($file['url'],$filename); 
                    }else{
                        $file_info_arr= pathinfo($file);  
                        $filename =$file_info_arr['basename'];   
                        $zip->addFile($file,$filename); 
                    }   
                }    
                //debug    
                //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;     
                //关闭文件    
                $zip->close();

                //check to make sure the file exists    
                //检测文件是否存在  
                if(file_exists($destination)){
                    if( $down ){

                        $out = file_get_contents($destination);
                        @header('Content-Encoding: none');
                        @header('Content-Type: application/zip');
                        @header('Content-Disposition: attachment ; filename='.basename($destination));
                        @header('Pragma: no-cache');
                        @header('Expires: 0');
                        print($out);
                    }else{
                        return $destination;
                    }
                    
                }else{
                    return false;
                }
            }else{    
                //如果没有真实有效的文件返回false    
                return false;    
            }    
        }
         
         
         
         
         
        /**********************************************************
         * 解压部分
         **********************************************************/
        // ------------------------------------------------------ //
        // ReadCentralDir($zip, $zipfile)
        // $zip是经过@fopen($zipfile, 'rb')打开的
        // $zipfile是zip文件的路径
        // ------------------------------------------------------ //
        private function ReadCentralDir($zip, $zipfile)
        {
            $size     = filesize($zipfile);
            $max_size = ($size < 277) ? $size : 277;
             
            @fseek($zip, $size - $max_size);
            $pos   = ftell($zip);
            $bytes = 0x00000000;
             
            while($pos < $size)
            {
                $byte  = @fread($zip, 1);
                $bytes = ($bytes << 8) | Ord($byte);
                $pos++;
                if($bytes == 0x504b0506){ break; }
            }
             
            $data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', fread($zip, 18));
 
            $centd['comment']      = ($data['comment_size'] != 0) ? fread($zip, $data['comment_size']) : '';  // 注释
            $centd['entries']      = $data['entries'];
            $centd['disk_entries'] = $data['disk_entries'];
            $centd['offset']       = $data['offset'];
            $centd['disk_start']   = $data['disk_start'];
            $centd['size']         = $data['size'];
            $centd['disk']         = $data['disk'];
            return $centd;
        }
         
         
        private function ReadCentralFileHeaders($zip)
        {
            $binary_data = fread($zip, 46);
            $header      = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);
 
            $header['filename'] = ($header['filename_len'] != 0) ? fread($zip, $header['filename_len']) : '';
            $header['extra']    = ($header['extra_len']    != 0) ? fread($zip, $header['extra_len'])    : '';
            $header['comment']  = ($header['comment_len']  != 0) ? fread($zip, $header['comment_len'])  : '';
 
     
            if($header['mdate'] && $header['mtime'])
            {
                $hour    = ($header['mtime']  & 0xF800) >> 11;
                $minute  = ($header['mtime']  & 0x07E0) >> 5;
                $seconde = ($header['mtime']  & 0x001F) * 2;
                $year    = (($header['mdate'] & 0xFE00) >> 9) + 1980;
                $month   = ($header['mdate']  & 0x01E0) >> 5;
                $day     = $header['mdate']   & 0x001F;
                $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
            } else {
                $header['mtime'] = time();
            }
            $header['stored_filename'] = $header['filename'];
            $header['status'] = 'ok';
            if(substr($header['filename'], -1) == '/'){ $header['external'] = 0x41FF0010; }  // 判断是否文件夹
            return $header;
        }
     
     
        private function ReadFileHeader($zip)
        {
            $binary_data = fread($zip, 30);
            $data        = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
     
            $header['filename']        = fread($zip, $data['filename_len']);
            $header['extra']           = ($data['extra_len'] != 0) ? fread($zip, $data['extra_len']) : '';
            $header['compression']     = $data['compression'];
            $header['size']            = $data['size'];
            $header['compressed_size'] = $data['compressed_size'];
            $header['crc']             = $data['crc'];
            $header['flag']            = $data['flag'];
            $header['mdate']           = $data['mdate'];
            $header['mtime']           = $data['mtime'];
     
            if($header['mdate'] && $header['mtime']){
                $hour    = ($header['mtime']  & 0xF800) >> 11;
                $minute  = ($header['mtime']  & 0x07E0) >> 5;
                $seconde = ($header['mtime']  & 0x001F) * 2;
                $year    = (($header['mdate'] & 0xFE00) >> 9) + 1980;
                $month   = ($header['mdate']  & 0x01E0) >> 5;
                $day     = $header['mdate']   & 0x001F;
                $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
            }else{
                $header['mtime'] = time();
            }
     
            $header['stored_filename'] = $header['filename'];
            $header['status']          = "ok";
            return $header;
        }
     
     
        private function ExtractFile($header, $to, $zip)
        {
            $header = $this->readfileheader($zip);
             
            if(substr($to, -1) != "/"){ $to .= "/"; }
            if(!@is_dir($to)){ @mkdir($to, 0777); }
            $pthss = '';
            $pth = explode("/", dirname($header['filename']));
            for($i=0; isset($pth[$i]); $i++){
                if(!$pth[$i]){ continue; }
                $pthss .= $pth[$i]."/";
                if(!is_dir($to.$pthss)){ @mkdir($to.$pthss, 0777); }
            }
             
            
            $header['external'] = !empty($header['external'])?$header['external']:'';
            if(!($header['external'] == 0x41FF0010) && !($header['external'] == 16))
            {
                if($header['compression'] == 0)
                {
                    $fp = @fopen($to.$header['filename'], 'wb');
                    if(!$fp){ return(-1); }
                    $size = $header['compressed_size'];
                     
                    while($size != 0)
                    {
                        $read_size   = ($size < 2048 ? $size : 2048);
                        $buffer      = fread($zip, $read_size);
                        $binary_data = pack('a'.$read_size, $buffer);
                        @fwrite($fp, $binary_data, $read_size);
                        $size       -= $read_size;
                    }
                    fclose($fp);
                    touch($to.$header['filename'], $header['mtime']);
                 
                }else{
                     
                    $fp = @fopen($to.$header['filename'].'.gz', 'wb');
                    if(!$fp){ return(-1); }
                    $binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
                     
                    fwrite($fp, $binary_data, 10);
                    $size = $header['compressed_size'];
                     
                    while($size != 0)
                    {
                        $read_size   = ($size < 1024 ? $size : 1024);
                        $buffer      = fread($zip, $read_size);
                        $binary_data = pack('a'.$read_size, $buffer);
                        @fwrite($fp, $binary_data, $read_size);
                        $size       -= $read_size;
                    }
                     
                    $binary_data = pack('VV', $header['crc'], $header['size']);
                    fwrite($fp, $binary_data, 8);
                    fclose($fp);
                     
                    $gzp = @gzopen($to.$header['filename'].'.gz', 'rb') or die("Cette archive est compress!");
                     
                    if(!$gzp){ return(-2); }
                    $fp = @fopen($to.$header['filename'], 'wb');
                    if(!$fp){ return(-1); }
                    $size = $header['size'];
                     
                    while($size != 0)
                    {
                        $read_size   = ($size < 2048 ? $size : 2048);
                        $buffer      = gzread($gzp, $read_size);
                        $binary_data = pack('a'.$read_size, $buffer);
                        @fwrite($fp, $binary_data, $read_size);
                        $size       -= $read_size;
                    }
                    fclose($fp); gzclose($gzp);
                     
                    touch($to.$header['filename'], $header['mtime']);
                    @unlink($to.$header['filename'].'.gz');
                }
            }
            return true;
        }
         
         
        // ------------------------------------------------------ //
        // #解压文件
        //
        // $archive   = new PHPZip();
        // $zipfile   = "ZIP压缩文件名";
        // $savepath  = "解压缩目录名";
        // $array     = $archive->GetZipInnerFilesInfo($zipfile);
        // $filecount = 0;
        // $dircount  = 0;
        // $failfiles = array();
        // set_time_limit(0);  // 修改为不限制超时时间(默认为30秒)
        //
        // for($i=0; $i<count($array); $i++) {
        //     if($array[$i][folder] == 0){
        //         if($archive->unZip($zipfile, $savepath, $i) > 0){
        //             $filecount++;
        //         }else{
        //             $failfiles[] = $array[$i][filename];
        //         }
        //     }else{
        //         $dircount++;
        //     }
        // }
        // set_time_limit(30);
        //printf("文件夹:%d&nbsp;&nbsp;&nbsp;&nbsp;解压文件:%d&nbsp;&nbsp;&nbsp;&nbsp;失败:%d<br>\r\n", $dircount, $filecount, count($failfiles));
        //if(count($failfiles) > 0){
        //    foreach($failfiles as $file){
        //        printf("&middot;%s<br>\r\n", $file);
        //    }
        //}
        // ------------------------------------------------------ //
        public function unZip($zipfile, $to, $index = Array(-1))
        {
            $ok  = 0;
            $zip = @fopen($zipfile, 'rb');
            if(!$zip){ return(-1); }
             
            $cdir      = $this->ReadCentralDir($zip, $zipfile);
            $pos_entry = @$cdir['offset'];
             
            if(!is_array($index)){ $index = array($index); }

            $index_count=count($index);
            for($i=0; $i<$index_count; $i++)
            {
                if(intval($index[$i]) != $index[$i] || $index[$i] > $cdir['entries'])
                {
                    return(-1);
                }
            }
             
            for($i=0; $i<$cdir['entries']; $i++)
            {
                @fseek($zip, $pos_entry);
                $header          = $this->ReadCentralFileHeaders($zip);
                $header['index'] = $i;
                $pos_entry       = ftell($zip);
                @rewind($zip);
                fseek($zip, $header['offset']);
                if(in_array("-1", @$index) || in_array($i, @$index))
                {
                    $stat[$header['filename']] = $this->ExtractFile($header, $to, $zip);
                }
            }
             
            fclose($zip);
            return $stat;
        }
         
         
         
         
         
        /**********************************************************
         * 其它部分
         **********************************************************/
        // ------------------------------------------------------ //
        // #获取被压缩文件的信息
        //
        // $archive = new PHPZip();
        // $array = $archive->GetZipInnerFilesInfo(ZIP压缩文件名);
        // for($i=0; $i<count($array); $i++) {
        //     printf("<b>&middot;%s</b><br>\r\n", $array[$i][filename]);
        //     foreach($array[$i] as $key => $value)
        //         printf("%s => %s<br>\r\n", $key, $value);
        //     print "\r\n<p>------------------------------------<p>\r\n\r\n";
        // }
        // ------------------------------------------------------ //
        public function GetZipInnerFilesInfo($zipfile)
        {
            $zip = @fopen($zipfile, 'rb');
            if(!$zip){ return(0); }
            $centd = $this->ReadCentralDir($zip, $zipfile);
             
            @rewind($zip);
            @fseek($zip, $centd['offset']);
            $ret = array();
 
            for($i=0; $i<$centd['entries']; $i++)
            {
                $header          = $this->ReadCentralFileHeaders($zip);
                $header['index'] = $i;
                $info = array(
                    'filename'        => $header['filename'],                   // 文件名
                    'stored_filename' => $header['stored_filename'],            // 压缩后文件名
                    'size'            => $header['size'],                       // 大小
                    'compressed_size' => $header['compressed_size'],            // 压缩后大小
                    'crc'             => strtoupper(dechex($header['crc'])),    // CRC32
                    'mtime'           => date("Y-m-d H:i:s",$header['mtime']),  // 文件修改时间
                    'comment'         => $header['comment'],                    // 注释
                    'folder'          => ($header['external'] == 0x41FF0010 || $header['external'] == 16) ? 1 : 0,  // 是否为文件夹
                    'index'           => $header['index'],                      // 文件索引
                    'status'          => $header['status']                      // 状态
                );
                $ret[] = $info;
                unset($header);
            }
            fclose($zip);
            return $ret;
        }
         
         
        // ------------------------------------------------------ //
        // #获取压缩文件的注释
        //
        // $archive = new PHPZip();
        // echo $archive->GetZipComment(ZIP压缩文件名);
        // ------------------------------------------------------ //
        public function GetZipComment($zipfile)
        {
            $zip = @fopen($zipfile, 'rb');
            if(!$zip){ return(0); }
            $centd = $this->ReadCentralDir($zip, $zipfile);
            fclose($zip);
            return $centd[comment];
        }

        /**
         * 删除目录下所有文件和文件夹
         * @param  [string] $dir 路径
         * @return [bool]
         */
        public function deldir($dir,$isdeldir=true) {
            //先删除目录下的文件：
            $dh=opendir($dir);
            while ($file=readdir($dh)) {
                if($file!="." && $file!="..") {
                    $fullpath=$dir."/".$file;
                    if(!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        deldir($fullpath);
                    }
                }
            }

            closedir($dh);

            if($isdeldir){
                //删除当前文件夹：
                if(rmdir($dir)) {
                    return true;
                } else {
                    return false;
                }
            }
                
        }

        /**
         * 下载大文件
         * @param  string $sourceFile [要下载的临时文件名]
         * @param  string $outFile    [下载保存到客户端的文件名]
         */
        public function downBig($sourceFile='',$outFile=''){
            $file_extension = strtolower(substr(strrchr($sourceFile, "."), 1)); //获取文件扩展名  
            //echo $sourceFile;  
            if (!ereg("[tmp|txt|rar|pdf|doc]", $file_extension))exit ("非法资源下载");  
            //检测文件是否存在  
            if (!is_file($sourceFile)) {  
                die("<b>404 File not found!</b>");  
            }  
            $len = filesize($sourceFile); //获取文件大小  
            $filename = basename($sourceFile); //获取文件名字  
            $outFile_extension = strtolower(substr(strrchr($outFile, "."), 1)); //获取文件扩展名  
            //根据扩展名 指出输出浏览器格式  
            switch ($outFile_extension) {  
                case "exe" :  
                    $ctype = "application/octet-stream";  
                    break;  
                case "zip" :  
                    $ctype = "application/zip";  
                    break;  
                case "mp3" :  
                    $ctype = "audio/mpeg";  
                    break;  
                case "mpg" :  
                    $ctype = "video/mpeg";  
                    break;  
                case "avi" :  
                    $ctype = "video/x-msvideo";  
                    break;  
                default :  
                    $ctype = "application/force-download";  
            }  
            //Begin writing headers  
            header("Cache-Control:");  
            header("Cache-Control: public");  
              
            //设置输出浏览器格式  
            header("Content-Type: $ctype");  
            header("Content-Disposition: attachment; filename=" . $outFile);  
            header("Accept-Ranges: bytes");  
            $size = filesize($sourceFile);  
            //如果有$_SERVER['HTTP_RANGE']参数  
            if (isset ($_SERVER['HTTP_RANGE'])) {  
                /*Range头域 　　Range头域可以请求实体的一个或者多个子范围。  
                例如，  
                表示头500个字节：bytes=0-499  
                表示第二个500字节：bytes=500-999  
                表示最后500个字节：bytes=-500  
                表示500字节以后的范围：bytes=500- 　　  
                第一个和最后一个字节：bytes=0-0,-1 　　  
                同时指定几个范围：bytes=500-600,601-999 　　  
                但是服务器可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以200 （OK）。  
                */  
                // 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值 bytes=4390912-  
                list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);  
                //if yes, download missing part  
                str_replace($range, "-", $range); //这句干什么的呢。。。。  
                $size2 = $size -1; //文件总字节数  
                $new_length = $size2 - $range; //获取下次下载的长度  
                header("HTTP/1.1 206 Partial Content");  
                header("Content-Length: $new_length"); //输入总长  
                header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928   95%的时候  
            } else {  
                //第一次连接  
                $size2 = $size -1;  
                header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928  
                header("Content-Length: " . $size); //输出总长  
            }  
            //打开文件  
            $fp = fopen("$sourceFile", "rb");  
            //设置指针位置  
            fseek($fp, $range);  
            //虚幻输出  
            while (!feof($fp)) {  
                //设置文件最长执行时间  
                set_time_limit(0);  
                print (fread($fp, 1024 * 8)); //输出文件  
                flush(); //输出缓冲  
                ob_flush();  
            }  
            fclose($fp);  
            exit (); 
        }
    }
?>