<?php
class Api{

	public $userInfo        = array();
	public $API_URL         = '';
	public $SECRET_BROWSER  = 'ksdhbfiuyh98182y379812hi9';
	public $SECRET_PASSWORD = 'f983r2ewioeoiwaeefadsafew';
	public $arrHeader = array
						(
							 'Userid'        => 0              //当前用户ID
							,'Requesttime'   => null           //请求时间
							,'Logintime'     => null           //最后登录时间
							,'Clientversion' => 1.0            //版本号
							,'Devicetype'    => 1              //类型 1:浏览器设备 2:PC 3:安卓 4:iOS 5:其他 默认浏览器设备
							,'Checkcode'     => null           //用户和登陆时间组成加密字符
						);

	public function __construct($config=array()){
		if( !empty($config) ){
			foreach ($config as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	public function set($key,$value=array()){
		$this->{$key} = $value;
		return $this->{$key};
	}


	/**
	 * 调用接口API
	 * @strApiType		  string  API接口和函数 demo/demo
	 * @arrActionData	   array   表单提交的数据
	 * @requestType		 string  POST or GET
	 */
	public function httpToApi($strApiType, $arrActionData=array(), $strRequestType= 'POST', $arrHeader = array())
	{
		if( !$this->canaction() ){
			return $this->getArrayForResults('1','缺少参数');
		}

		if(empty($arrHeader)){
			$arrHeader = $this->arrHeader;
		}

		if( ( !empty($this->userInfo['id'])||!empty($this->userInfo['userID']) ) && empty($arrHeader['Userid']) ){
			$arrHeader['Userid']	= !empty($this->userInfo['userID'])?$this->userInfo['userID']:$this->userInfo['id'];
			$arrHeader['Logintime'] = !empty($this->userInfo['extraInfo']['authInfo']['Logintime'])?$this->userInfo['extraInfo']['authInfo']['Logintime']:null;
			$arrHeader['Checkcode'] = !empty($this->userInfo['extraInfo']['authInfo']['Checkcode'])?$this->userInfo['extraInfo']['authInfo']['Checkcode']:null;
		}
		if( empty($arrHeader['Logintime']) ){
			$arrHeader['Logintime'] = time();
		}

		if(empty($arrHeader['Checkcode'])){
			$arrHeader['Checkcode'] = $this->getCheckCode($arrHeader['Userid'],$arrHeader['Logintime']);
		}
		
		$arrHeader['Requesttime'] = time();
		$arrSecret = is_array($arrActionData)?array_merge($arrHeader, $arrActionData):$arrHeader;
		$arrSecret['secret']  = $this->SECRET_BROWSER;
		
		if( !empty($arrSecret['Sign']) )unset($arrSecret['Sign']);
		$arrHeader['Sign']	= $this->getSign($arrSecret);
		if (!empty($strApiType)){
			$strUrl = $this->API_URL .'/'. $strApiType;
			if ($strRequestType == 'POST' ){
				$results = $this->getUrlContent($strUrl, $arrActionData, $arrHeader);
			}else{
				if (is_array($arrActionData) ){
					$arr_url = array();
					foreach ($arrActionData as $k => $v){
						$arr_url[] = "{$k}={$v}";
					}
					$strUrl .= '?'.implode('&',$arr_url);
				}
				$results = $this->getUrlContent($strUrl, array(), $arrHeader);
			}
			return $results;
		}
		return false;
	}

	/**
	 * @strUrl				string	抓取的URL地址
	 * @arrActionData		 array	 POST提交数据
	 * @arrActionHeader	   array	 请求头
	 */
	public function getUrlContent($strUrl,  $arrActionData = array(), $arrActionHeader = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $strUrl);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);//启用一个全局的DNS缓存
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//  返回内容
		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// 跟踪重定向
		// curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPV4协议解析域名
		if(!empty($arrActionHeader)){
			$arrActionHeaderData = $this->getArrActionHeader($arrActionHeader);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $arrActionHeaderData);//  设定请求头
		}
		if(!empty($arrActionData)) {
			curl_setopt($ch, CURLOPT_POST, count($arrActionData));//  POST 提交
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arrActionData);//  提交数据
		}
		$results = curl_exec($ch);
		curl_close($ch);
		return json_decode($results,true);//返回小写的 相应数据
	}

	/**
	 * 判断目标key是否都存在，返回首个不存在的key
	 * @param  string|array $p_keys 多个key可用逗号隔开的字符串或组成数组
	 * @param  bool		 $p_allowBlank 是否允许空值
	 * @return bool		 true/false
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
	 * 将用户和登陆时间组成加密字符
	 * @param  integer $p_userID 用户ID
	 * @param  string  $p_time   时间戳
	 * @return string			加密后字符
	 */
	protected function getCheckCode($p_userID, $p_time)
	{
		return md5($p_userID.md5($p_time.$this->SECRET_PASSWORD));
	}

	/**
	 * 将密码再次加密
	 * @param  string $p_password 原始密码（一般此时已经经过初步MD5加密）
	 * @return string			 加密后字符串（用于存储到数据库中）
	 */
	public function getEncodedPwd($p_password)
	{
		if (!is_null($p_password))
		{
			return md5(md5($p_password).$this->SECRET_PASSWORD.substr(md5($p_password),3,8));
		}
		return null;
	}

	/**
	 * 获取签名
	 * @param  [type] $arrSecret [description]
	 * @return [type]			[description]
	 */
	protected function  getSign($arrSecret)
	{
		$arrSort = array();
		foreach ($arrSecret as $key => $value){
			if( is_array($value)||is_object($value) )continue;
			array_push($arrSort  , sprintf("%s=%s", @$key, @$value  ));
		}
		sort($arrSort, SORT_STRING);
		return  md5(implode( $arrSort ));
	}

	// 转换请求头
	public function  getArrActionHeader($arrActionHeader){
		$arrActionHeaderInfo = array();
		foreach ($arrActionHeader as $key => $value){
			$arrActionHeaderInfo[] = sprintf("%s:%s", $key, $value);
		}
		return $arrActionHeaderInfo;
	}

	//判断是否是正确的数组
	public function isResultsOK($results=array()){
		if(empty($results))return false;
		if($results['errorCode']=='0')return true;return false;
	}

	//验证登陆
	public function checkLogin($userInfo=''){
		$session  = array();
		$userInfo = !empty($userInfo)?$userInfo:$this->userInfo;
		if( empty($userInfo) )return false;

		$sessionSign = $userInfo['sessionSign'];
		foreach($userInfo as $key => $value){
			if($key=='sessionSign')continue;
			$session[$key]=$value;
		}

		$sign = md5(md5(json_encode($session)).$this->SECRET_PASSWORD);
		if( $sign!=$sessionSign ){
			$this->userInfo=null;
			return false;
		}

		empty($this->userInfo)&&$this->userInfo = $userInfo;
		return true;
	}

	//返回
	public function getArrayForResults($errcode = 0, $msg = '', $data = array(),$extraInfo=array()){
		return array(
					'errorCode'   => $errcode,
					'errorStr'    => $msg,
					'extraInfo'	  => $extraInfo,
					'results'	  => $data
			);
	}

	public function canaction(){
		if( empty($this->API_URL)||empty($this->SECRET_BROWSER)||empty($this->SECRET_PASSWORD) ){
			return false;
		}
		return true;
	}
}