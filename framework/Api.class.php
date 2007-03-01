<?php
/**
 * @author Kiral
 * @version 2016-07-26
 */
class Api{
	/**
     * 调用接口API
     * @strApiType          string  API接口和函数 demo/demo
     * @arrActionData       array   表单提交的数据
     * @requestType         string  POST or GET
     */
    public static function httpToApi($strApiType, $arrActionData=array(), $strRequestType= 'POST', $arrHeader = array())
    {
        if(empty($arrHeader)){
            global $arrHeader;
            $arrHeader = $arrHeader;
        }
        
        if( !empty( $_SESSION[SANDC_KEY]['id'] ) && empty($arrHeader['Userid']) ){
            $arrHeader['Userid']    = $_SESSION[SANDC_KEY]['id'];
            $arrHeader['Logintime'] = $_SESSION[SANDC_KEY]['extraInfo']['logintime'];
            $arrHeader['Checkcode'] = $_SESSION[SANDC_KEY]['extraInfo']['checkcode'];
        }

        if( empty($arrHeader['Requesttime'])){
            $arrHeader['Requesttime'] = time();
        }

        if( empty($arrHeader['Logintime']) ){
            $arrHeader['Logintime'] = time();
        }

        if(empty($arrHeader['Checkcode'])){
            $arrHeader['Checkcode'] = self::getCheckCode($arrHeader['Userid'],$arrHeader['Logintime']);
        }

        $arrSecret =(array_merge($arrHeader, $arrActionData));
        $arrSecret['secret']  = SECRET_BROWSER;
        if( !empty($arrSecret['Sign']) ){unset($arrSecret['Sign']);}
        $arrHeader['Sign']    = self::getSign($arrSecret);

        if (!empty($strApiType)){
            $strUrl = API_URL . $strApiType;
            if ($strRequestType == 'POST' ){
                return self::getUrlContent($strUrl, $arrActionData, $arrHeader);
            }else{
                if (is_array($arrActionData) ){
                    foreach ($arrActionData as $k => $v){
                        $strUrl .= "&{$k}={$v}";
                    }
                }
                return self::getUrlContent($strUrl, array(), $arrHeader);
            }
        }
        return false;
    }

    /**
     * @strUrl                string    抓取的URL地址
     * @arrActionData         array     POST提交数据
     * @arrActionHeader       array     请求头
     */
    public static function getUrlContent($strUrl,  $arrActionData = array(), $arrActionHeader = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);//启用一个全局的DNS缓存
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//  返回内容
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// 跟踪重定向
        // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
        // curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPV4协议解析域名
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
        // echo "<pre />";
        // print_r($results);exit;
        return json_decode($results,true);//返回小写的 相应数据
    }

    /**
     * 将用户和登陆时间组成加密字符
     * @param  integer $p_userID 用户ID
     * @param  string  $p_time   时间戳
     * @return string            加密后字符
     */
    protected static function getCheckCode($p_userID, $p_time)
    {
        return md5($p_userID.md5($p_time.SECRET_PASSWORD));
    }

    /**
     * 将密码再次加密
     * @param  string $p_password 原始密码（一般此时已经经过初步MD5加密）
     * @return string             加密后字符串（用于存储到数据库中）
     */
    public static function getEncodedPwd($p_password)
    {
        if (!is_null($p_password))
        {
            return md5(md5($p_password).SECRET_PASSWORD.substr(md5($p_password),3,8));
        }
        return null;
    }

    /**
     * 获取签名
     * @param  [type] $arrSecret [description]
     * @return [type]            [description]
     */
    protected static function  getSign($arrSecret)
    {
        $arrSort = array();
        foreach ($arrSecret as $key => $value)
        {
            array_push($arrSort  , sprintf("%s=%s", @$key, @$value  ));
        }
        sort($arrSort, SORT_STRING);
        return  md5(implode( $arrSort ));
    }

    // 转换请求头
    public static function  getArrActionHeader($arrActionHeader){
        $arrActionHeaderInfo = array();
        foreach ($arrActionHeader as $key => $value){
            $arrActionHeaderInfo[] = sprintf("%s:%s", $key, $value);
        }
        return $arrActionHeaderInfo;
    }

    //返回
    public static function getArrayForResults($errcode = 0, $msg = '', $data = array()){
        $data = array('errorCode'=>$errcode, 'errorStr'=>$msg, 'results'=>$data);
        return json_encode($data);
    }
}
?>
