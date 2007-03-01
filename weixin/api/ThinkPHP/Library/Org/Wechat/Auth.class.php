<?php
namespace Org\Wechat;
/**
 * 微信oAuth认证示例
 */
class Auth extends Wechat{
	public $open_id;
	public $wxuser;

	public function __construct($options,$scope='snsapi_base'){
		parent::__construct($options);
		$this->wxuser['return']=self::wxoauth($scope);
	}

	public function wxoauth($scope='snsapi_base'){
		$code = isset($_GET['code'])?$_GET['code']:'';
		$token_time = isset($_SESSION['token_time'])?$_SESSION['token_time']:0;
		if(!$code && isset($_SESSION['open_id']) && isset($_SESSION['user_token']) && $token_time>time()-3600){
			if (!$this->wxuser) {
				$this->wxuser = $_SESSION['wxuser'];
			}
			$this->open_id = $_SESSION['open_id'];
			return array(
						 'errorCode' =>0
						,'errorStr'  => ''
						,'results'   => array(
											 'key'   => 'openID'
											,'value' => $this->open_id
										)
					); 
		}else{
			if ($code) {
				$json = self::getOauthAccessToken();
				if (!$json) {
					unset($_SESSION['wx_redirect']);
					return array(
								 'errorCode' =>1
								,'errorStr'  => '获取用户授权失败，请重新确认'
							);
				}
				$_SESSION['open_id'] = $this->open_id = $json["openid"];
				$access_token = $json['access_token'];
				$_SESSION['user_token'] = $access_token;
				$_SESSION['token_time'] = time();
				$userinfo = self::getUserInfo($this->open_id);
				if ($userinfo && !empty($userinfo['nickname'])) {
					$this->wxuser = array(
							'open_id'=>$this->open_id,
							'nickname'=>$userinfo['nickname'],
							'sex'=>intval($userinfo['sex']),
							'location'=>$userinfo['province'].'-'.$userinfo['city'],
							'avatar'=>$userinfo['headimgurl']
					);
				} elseif (strstr($json['scope'],'snsapi_userinfo')!==false) {
					$userinfo = self::getOauthUserinfo($access_token,$this->open_id);
					if ($userinfo && !empty($userinfo['nickname'])) {
						$this->wxuser = array(
								'open_id'=>$this->open_id,
								'nickname'=>$userinfo['nickname'],
								'sex'=>intval($userinfo['sex']),
								'location'=>$userinfo['province'].'-'.$userinfo['city'],
								'avatar'=>$userinfo['headimgurl']
						);
					} else {
						return array(
									 'errorCode' =>0
									,'errorStr'  => ''
									,'results'   => array(
														'openID'=>$this->open_id
													)
								);
					}
				}
				if ($this->wxuser) {
					$_SESSION['wxuser'] = $this->wxuser;
					$_SESSION['open_id'] =  $json["openid"];
					unset($_SESSION['wx_redirect']);
					return array(
								 'errorCode' =>0
								,'errorStr'  => ''
								,'results'   => array(
													 'key'   => 'openID'
													,'value' => $this->open_id
												)
							);
				}
				$scope = 'snsapi_userinfo';
			}

			$_SESSION['wx_redirect'] = $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			$oauth_url = self::getOauthRedirect($url,"wxbase",$scope);
			return array(
						 'errorCode' => 1
						,'errorStr'  => '获取授权'
						,'results'   => array(
											 'key'   => 'authUrl'
											,'value' => $oauth_url
										)
					);
		}
	}
}