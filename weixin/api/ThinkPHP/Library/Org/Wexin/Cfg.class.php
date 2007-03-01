<?php
abstract class Cfg {
    protected $Me;
    protected $User;
    protected $access_token = null;
    protected $errcode = null;
    protected $isqy; //是否企业应用
    
    /**
     * 
     * 获取token的次数
     * 用户调用接口时可能遇到token过期，这时就第二次从本地获取token，如果仍然无效，说明本地token文件没有被
     * 其他用户更新，则从微信服务器获取新token，保存到本地，并使用该token，如果仍无效，终止运行
     */
    private $APPID;
    private $APPSECRET;
    private $tokenGetTimes = 0; 
    private $tokenCacheFile = '';
    
    public function __construct($AppID,$AppSecret,$isqy = false){
        $this->isqy = $isqy;
            $this->APPID = $AppID;
            $this->APPSECRET = $AppSecret;
            $this->tokenCacheFile = dirname(__FILE__).'/cache/_token_'.md5($this->APPSECRET).'.php';
        }
        $this->_getToken();
    }


    /**
     * Cfg::showToken()
     * 返回当前使用的access_token
     * @return
     */
    public function showToken(){
        return $this->access_token;
    }
    
    
    /**
     * Cfg::tokenRequest()
     * 
     * @param mixed $url
     * @param string $postData
     * @return
     */
    protected function tokenRequest($url,$postData = '', $isDownload = false){
        try{
            $QueryUrl = false === strpos($url,'?') ? '?' : '&';
            $QueryUrl .= 'access_token='.$this->access_token;
            $wxresponse = bwx_get_contents($url.$QueryUrl,$postData);
            $decode = json_decode($wxresponse, true);
			
            if(null !== $decode && (!isset($decode['errcode']) || $decode['errcode'] == 0)){
                return $decode;
            }elseif(null !== $decode && isset($decode['errcode'])){
                //throw new Bwxexception($decode['errcode'],$decode['errmsg'].' - '.$url);
                return 0;
            }elseif($isDownload){
                
                return $wxresponse;
                        
            }else{
				
                throw new Bwxexception(Errorcode::$CURL, $wxresponse);
            }
        }catch(Bwxexception $e){
            if(null === $this->errcode || $this->errcode == 40001 || $this->errcode == 42001 || $this->errcode == 40014){
                $this->errcode = strval($e->getCode());
                switch($this->errcode){
                    case '40001': //access_token 不对：token没按标准时间过期，强制从微信服务器获取更新
                    case '42001':
                    case '40014':

                        $this->logfile('正在使用的token已经过期');
                        //$this->_getTokenFromWeixin();
                        if(1 === $this->tokenGetTimes){
                            $this->logfile('开始第二次获取token：数据库获取');
                            $this->_getToken(2);
                        }elseif(2 === $this->tokenGetTimes){
                            $this->logfile('开始第三次获取token：从微信获取');
                            $this->_getToken(3);
                        }else{
                            exit ($e->getMessageInfo());
                        }
                        $this->logfile('token已获取，重新请求'.$url);
                        return $this->tokenRequest($url,$postData);
                    break;
                    default:
                        throw $e;
                    break;
                }
                
            }else{
                throw $e;
            }
        }
    }
    
    
    
    /**
     * Cfg::_getTokenFromWeixin()
     * 从微信服务器端获取token并保存在本地
     * @return void
     */
    final private function _getTokenFromWeixin(){
        logfile('通过微信获取token');
        if(!$this->isqy){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->APPID.'&secret='.$this->APPSECRET;
        }else{
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$this->APPID.'&corpsecret='.$this->APPSECRET;
        }
        
        $res = $this->get_contents($url);
        if($res){
            $this->logfile('通过微信获取token成功');
            $arr = json_decode($res, true);
            if(isset($arr['access_token']) && isset($arr['expires_in'])){
                $expires = intval($arr['expires_in'])+time();
                $token = $arr['access_token'];
                $this->logfile('解析微信获取的token成功，写入本地:'.$token);

                return $this->_setTokenFile($token, $expires);
                
            }else{
                $this->logfile('解析微信获取的token失败:'.$res);
                $this->_deleteToken();
            }
        }else{
            $this->logfile('通过微信获取token失败');
            $this->_deleteToken();
        }
        
        return false;
    }
    
    /**
     * Cfg::_getToken()
     * 获取本地服务器保存的token，如果已过期或不存在，数据库获取，如仍不存在或过期，则从微信服务器获取更新
     * @param integer $times
     * @return
     */
    final private function _getToken($times = 1){
        
        $this->tokenGetTimes = $times;
        
        if($this->tokenGetTimes == 1){
            //文件获取
            if(is_file($this->tokenCacheFile)){
                $str = file_get_contents($this->tokenCacheFile);
                $str = substr($str, 8);
                $pos = strpos($str, '|');
                if(false !== $pos){
                    $time = intval(substr($str,0, $pos));
                    $token = substr($str, $pos+1);
                    if($token != '' && $time > time()){//未过期
                        $this->logfile('通过本地文件获取token:'.$token);
                        $this->access_token = $token;
                        return $token;
                    }
                }
            }
            ##$this->_deleteToken();
            $this->tokenGetTimes = 2;
            return $this->_getToken(2);
        }else{
            //微信获取
            $this->tokenGetTimes = 3;
            return $this->_getTokenFromWeixin();
        }
        
    }
    
    
    
    
    /**
     * Cfg::_deleteToken()
     * 删除本地保存的token
     * @return void
     */
    final private function _deleteToken(){
        $this->_setTokenFile('');
        $this->access_token = null;
        logfile('删除token');
    }
    
    final private function _setTokenFile($token, $expires = 0 ){
        $expires = intval($expires);
        if($expires == 0) $expires = 7200+time();
        $file = $this->tokenCacheFile;
        if(is_writable($file) || (!is_file($file) && is_writable(dirname($this->tokenCacheFile)))){
            
            $str = '<?php //'.$expires.'|'.$token;
            file_put_contents($file, $str);
        }
        else{
            logfile($file. ' 不存在或不可写，请修改权限');
            //throw new Bwxexception(Errorcode::$TOKENFILE_UNWRITABLE, $file. ' 不存在或不可写，请修改权限');
        }
        
        $this->access_token = $token;
        return $token;
    }

    public function logfile(){
        $file = dirname(__FILE__).'/logs/'.date('Ymd').'.txt';
        if(is_writable($file) || (!is_file($file) && is_writable(dirname(__FILE__).'/logs'))){
            $fp = fopen($file,'a');
            $args = func_get_args();
            $str = '';
            foreach($args as $content){
                $str .= '【'.date('Y-m-d H:i:s').'】'.$content.PHP_EOL;
            }
            
            $str and fwrite($fp, $str);
            fclose($fp);
        }else{
            exit($file. ' 不存在或不可写，请修改权限或关闭日志功能');
        }
        
    }

    public function get_contents($url = '', $post_data = array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        if(!empty($post_data)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    
}
