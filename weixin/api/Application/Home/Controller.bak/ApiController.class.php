<?php
namespace Home\Controller;
use Think\Controller;
class ApiController extends Controller {
	public $auto     = '';
	public $userID   = 0;
	public $userInfo = array();
    Public function _initialize(){
	    //初始化的时候检查用户权限
	    $results = self::getAuthForApi();
	    if ($results['errorCode']!=0){
	    	//echo '<pre />';print_r($results);exit;
	    	echo json_encode($results);exit;
	    }
	    $this->auto = self::getAuto();
   	}

   	/**
   	 * 获取POST和GET数据
   	 * @return array 返回所有参数
   	 */
   	public function getParams(){
   		$params = array();
		foreach ($_POST as $key => $value) {
			$params[$key]=$value;
		}
		foreach ($_GET as $key => $value) {
			$params[$key]=$value;
		}
		return $params;
   	}

   	/**
   	 * 获取参数中的分页信息
   	 * @return array 分页信息
   	 */
   	public function getPageInfo($count=''){
   		if(empty($count))return array('size'=>0,'upPage'=>0,'nowpage'=>0,'nextPage'=>0,'countPage'=>0,'countTotal'=>0,'limit'=> array('start'=>0,'end'=>0));
        
        (int)$size      = abs(!empty($_REQUEST['size'])?$_REQUEST['size']:12 );
        (int)$nowpage   = abs(!empty($_REQUEST['page'])?$_REQUEST['page']:1);
        $countPage      = (int)ceil( $count/$size );
        $upPage         = ($nowpage - 1 < 1)?1:$nowpage - 1;
        $nextPage       = ($nowpage + 1 > $countPage)?$countPage:$nowpage + 1;
        return array(
                'size'      => $size
               ,'upPage'    => $upPage
               ,'nowpage'   => $nowpage
               ,'nextPage'  => $nextPage
               ,'countPage' => $countPage
               ,'countTotal'=> $count
               ,'limit'     => array(
               						 'start' => ($nowpage-1)*$size
               						,'end'   => $nowpage*$size
               					)
            );
    }

   	/**
   	 * 获取搜索条件
   	 * @return array 搜索条件
   	 */
	public function getSeach(){
	    $seach = array();
	    $where = '';
	    foreach ($_REQUEST as $key => $value) {
	        if( strstr($key,'seach_') ){
	            if( !empty($value) ){
	                $seach[substr($key,6)]=$value;
	            }   
	        }
	    }
	    return $seach;
	}

	/**
     * 判断目标key是否都存在，返回首个不存在的key
     * @param  string|array $p_keys 多个key可用逗号隔开的字符串或组成数组
     * @param  bool         $p_allowBlank 是否允许空值
     * @return bool         true/false
     */
    public function getUnsetRequest($p_keys,$p_allowBlank=false){
        $p_keys = explode(',', $p_keys);
        $unsetKey = null;
        foreach ($p_keys as $p_key) {
            if (!array_key_exists($p_key, $_REQUEST) || (!$p_allowBlank && $_REQUEST[$p_key]==null ))
            {
                $unsetKey = $p_key;
                break;
            }
        }
        return $unsetKey;
    }
    /**
	 * XML转数组
	 * @param string $xml XML字符串
	 * @return array XML数组
	 */
	public function xmlToArray($xml) {
	    return json_decode(json_encode((array) simplexml_load_string($xml)), true);
	}

   	/**
   	 * 获取当前用户类型
   	 * @return string 返回用户类型(
   	 *         						admin    管理员
   	 *         						normal   正常用户
   	 *         						visitor  游客
   	 *         						draft    未激活
   	 *         						pending  禁言
   	 *         						disabled 封号
   	 *         					  )
   	 */
   	public function getAuto($userID=''){
		$this->userID = empty($userID)?$_SERVER['HTTP_USERID']:$userID;
		if( empty($this->userID)||$this->userID==0 ){
			$auto = 'visitor';//游客
		}else{
			$this->userInfo = $userInfo = M(USEERTABLE)->where("id='%s'",$this->userID)->find();
			if( empty($this->userInfo) ){
				$auto = 'visitor';//游客
			}else{
				$auto = 'normal';//正常用户
				// switch ($userInfo['status']) {
				// 	case 1://正常用户
				// 		$auto = 'normal';
				// 		break;
				// 	case 2://封号用户
				// 		$auto = 'disabled';
				// 		break;
				// 	case 3://禁言用户
				// 		$auto = 'pending';
				// 		break;
				// 	default://未激活
				// 		$auto = 'draft';
				// 		break;
				// }
				if( $auto=='normal' ){
					if($userInfo['username']=='admin'){
						$auto = 'admin';
					}
					if($userInfo['is_stop']!=1)$auto = 'draft';//未激活
				}
			}
		}
		return $auto;
   	}

   	/**
	 * 将用户和登陆时间组成加密字符
	 * @param  integer $p_userID 用户ID
	 * @param  string  $p_time   时间戳
	 * @return string            加密后字符
	 */
	protected function getCheckCode($p_userID, $p_time)
	{
		return md5($p_userID.md5($p_time.USER_RANDCODE));
	}

	/**
	 * 将密码再次加密
	 * @param  string $p_password 原始密码（一般此时已经经过初步MD5加密）
	 * @return string             加密后字符串（用于存储到数据库中）
	 */
    public function getEncodedPwd($p_password)
    {
    	if (!is_null($p_password))
    	{
	    	return md5(md5($p_password).SECRET_PASSWORD.substr(md5($p_password),3,8));
    	}
    	return null;
    }


    /**
     * 提取请求中的headers信息，
     * 并复制一份首字母大写其他字母小写的key值，
     * 最后存储到$_HEADERS变量中供使用
     * @return array 优化后的headers信息
     */
	public function getallheadersUcfirst()
	{
		if ($_HEADERS === null)
		{
			if (@!function_exists('getallheaders')) {
        		$_HEADERS = self::getallheaders();
		    }else{
		        $_HEADERS = getallheaders();
		    }
			foreach ($_HEADERS as $key => $value) {
				$_HEADERS[ucfirst(strtolower($key))] = $value;
			}
		}
		return $_HEADERS;
	}

	public function getHeaderValue($p_key)
	{
		$_headers = self::getallheadersUcfirst();
		$p_key = ucfirst(strtolower($p_key));
		if (array_key_exists($p_key,$_headers))
		{
			return $_headers[$p_key];
		}
		return null;
	}
	public function getUserID()
	{
		$p_userID = null;
		if (@!function_exists('getallheaders')) {
    		$infoHeader = self::getallheaders();
	    }else{
	        $infoHeader = getallheaders();
	    }
		if(self::getCheckCode(self::getHeaderValue('Userid'),self::getHeaderValue('Logintime')) == self::getHeaderValue('Checkcode'))
		{
			$p_userID = self::getHeaderValue('Userid');
		}
		return $p_userID ;
	}

	/**
	 * 获得组装后的结果数组
	 * @param  integer $errorCode 错误码，0为正常
	 * @param  string  $errorStr  错误描述
	 * @param  array   $result    返回数据
	 * @param  array   $extraInfo 返回额外数据
	 * @return array             结果数组
	 */
    public function getArrayForResults($errorCode=0,$errorStr='',$result = array(),$extraInfo=array(),$auto='')
    {
    	return array(
					 'errorCode'   => $errorCode
					,'errorStr'    => $errorStr
					,'resultCount' => (is_array($result) && array_values($result)===$result?count($result):1)
					,'extraInfo'   => $extraInfo
					,'auto'        => empty($auto)?$this->auto:$auto
					,'results'	   => $result
    		);
    }

    /**
     * 判断结果数组是否正确获得结果
     * @param  array  $tmpResult 结果数组
     * @return boolean            是否正确获得
     */
    public function isResults($tmpResult=null)
    {
    	return (is_array($tmpResult) && array_key_exists('errorCode',$tmpResult) );
    }

    /**
     * 判断结果数组是否正确获得结果
     * @param  array  $tmpResult 结果数组
     * @return boolean            是否正确获得
     */
    public function isResultsOK($tmpResult=null)
    {
    	return (self::isResults($tmpResult) && $tmpResult['errorCode']==0);
    }

    /**
     * 判断结果数组是否正确获得结果，并取出其中的结果
     * @param  array  $tmpResult 结果数组
     * @return boolean            是否正确获得
     */
    public function getResults($tmpResult=null)
    {
    	if (self::isResultsOK($tmpResult))
    	{
    		return $tmpResult['results'];
    	}
    	return null;
    }

    /**
     * 获取头信息
    */
    public function getallheaders() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function getHeaderAuthInfoForUserID($p_userID)
	{
		$p_time = self::getHeaderValue('Logintime');
		return array(
				 'Userid'=>$p_userID
				,'Logintime'=>$p_time
				,'Checkcode'=>self::getCheckCode($p_userID,$p_time)
			);
	}


    /**
     * 验证是否具有访问权限
     * @return [type] [description]
     */
    public function getAuthForApi()
    {
    	$isAuthed = false;

		$_HEADERS = self::getallheadersUcfirst();
		if (array_key_exists('Sign', $_HEADERS))
		{
			//定义一个空的数组
			$signArr = array();

			//将所有头信息和数据组合成字符串格式：%s=%s，存入上面的数组
			foreach (array('Clientversion','Devicetype','Requesttime','Userid','Logintime','Checkcode') as $_key) {
				if (array_key_exists($_key,$_HEADERS))
				{
					array_push($signArr, sprintf('%s=%s', $_key, $_HEADERS[$_key]));
				}
				else
				{
					return self::getArrayForResults(1,'请求信息错误',array('errorContent'=>'缺少头信息：'.$_key));
				}
			}
			if (abs($_HEADERS['Requesttime'] - time()) > 300 )
			{
				return self::getArrayForResults(1,'该操作已过期，请重试。');
			}

			$pag = array();
			foreach ($_POST as $key => $value) {
				$pag[$key]=$value;
			}
			foreach ($_GET as $key => $value) {
				$pag[$key]=$value;
			}

			//将一串约定好的密钥字符串也放入数组。（不同的项目甚至不同的版本中，可以使用不同的密钥）
			switch ($_HEADERS['Devicetype']) {

				case 1://浏览器设备
					$pag['secret'] = SECRET_BROWSER;
					break;
				case 2://pc设备，服务器
					$pag['secret'] = SECRET_PC;
					break;
				case 3://安卓
					$pag['secret'] = SECRET_ANDROID;
					break;
				case 4://iOS
					$pag['secret'] = SECRET_IOS;
					break;
				case 5://WP
					$pag['secret'] = SECRET_OTHER;
					break;

				default:
					$pag['secret'] = SECRET_OTHER;
					break;
			}

			//将所有表单数据也组成字符串后，放入数组。（注：file类型不包含）
			foreach ($pag as $_key => $_value) {
				array_push($signArr, sprintf('%s=%s', $_key, $_value));
			}

			//对数组进行自然排序
			sort($signArr, SORT_STRING);
			//对这个字符串进行MD5加密，即可获得Sign
			$tmpStr = md5(implode( $signArr ));
			if( $tmpStr != $_HEADERS['Sign'] ){
				$isAuthed = array(
					 'status'      => false
					,'tmpArr'      => $signArr
					,'tmpArrString'=> implode( $signArr )
					,'tmpArrMd5'   => $tmpStr
					,'Sign'        => $_HEADERS['Sign']
					);
			}else{
				$isAuthed = true;
			}
		}else{
			return self::getArrayForResults(1,'请求信息错误',array('errorContent'=>'缺少头信息：'.'Sign'));
		}

		if ($isAuthed === true){
			return self::getArrayForResults(0,'',$isAuthed);
		}else{
			return self::getArrayForResults(1,'校验失败',$isAuthed);
		}
    }

    /**
     * @strUrl                string    抓取的URL地址
     * @arrActionData         array     POST提交数据
     * @arrActionHeader       array     请求头
     */
    public function getUrlContent($strUrl,  $arrActionData = array(), $arrActionHeader = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//  返回内容
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// 跟踪重定向
        if(!empty($arrActionHeader)){
            $arrActionHeaderData = self::getArrActionHeader($arrActionHeader);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arrActionHeaderData);//  设定请求头
        }
        if(!empty($arrActionData)) {
            curl_setopt($ch, CURLOPT_POST, count($arrActionData));//  POST 提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrActionData);//  提交数据
        }

        $results = curl_exec($ch);
        curl_close($ch);
        return $results;
    }

    public function get_photo($url,$filename='',$savefile='Public/File/zhaopianhuichuan/'){ 
	    $imgArr = array('gif','bmp','png','ico','jpg','jepg');

	    if(!$url) return false;
	    
	    if(!$filename) {
	    	$ext=strtolower(end(explode('.',$url)));
	    	$ext=empty($ext)?'png':$ext;

	    	if(!in_array($ext,$imgArr))$ext='png';
	    	$filename=date("YmdHis").'.'.$ext;
	    }

	    if(!is_dir($savefile))mkdir($savefile, 0777,ture);
	    if(!is_readable($savefile)) chmod($savefile, 0777);
	    
	    $filename = $savefile.$filename;

	    ob_start();
	    readfile($url);
	    $img = ob_get_contents();
	    ob_end_clean();
	    
	    file_put_contents($filename,$img);
	    return $_SERVER['HTTP_ORIGIN'].__ROOT__.'/'.$filename;     
	}
}