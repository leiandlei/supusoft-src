<?php 
class login{
	var $appname="login"; //网站名称
	var $username; //用户名
	var $userpass; //密码
	var $authtable="account"; //验证用数据表
	var $col_username="username"; //用户名字段
	var $col_password="password"; //用户密码字段
	var $col_banned="banned"; //是否被禁止字段
	var $use_cookie=true; //使用cookie保存sessionid
	var $cookiepath='/'; //cookie路径
	var $cookietime=0; //cookie有效时间
	var $err_mysql="mysql error"; //mysql出错提示
	var $err_auth="username invalid or wrong password"; //用户名无效提示
	var $err_user="user invalid"; //用户无效提示(被封禁)
	function Login($appname=""){
		$this->appname='login'; //初始化网站名称

	}
	function isLoggedin(){ //判断是否登录
		if(isset($_COOKIE['sid'])){ //如果cookie中保存有sid
			session_id($_COOKIE['sid']);
			session_start();
			if($_SESSION['appname']!=$this->appname ) {
				//halt();
				return false;
			}
			//为了防止不同的程序使用同一个登录类产生冲突，加了个appname作为区分标记
			return true;
		}else{ //如果cookie中未保存sid,则直接检查session
			session_start();
			if(isset($_SESSION['appname']))
			return true;
		}
		return false;
	}

	function userAuth($username,$userpass){ //用户认证
		global $db;
		$this->username=$username;
		$this->userpass=$userpass;
		/*
		$query="select * from sp_hr where username='$username';";
		$result=mysql_query($query,$db->dbh);
		*/
		$user = $db->get_row("SELECT * FROM sp_hr WHERE 1 AND username = '".mysql_real_escape_string($username)."' AND deleted = 0");
		
		if( $user ){ //找到此用户
		    if( empty($user['sys'])&&$user['username']!='admin'){
				return '无权限不能登录!';
			}
			if( intval( $user['is_hire'] ) == 2 ){
				return '离职人员不可以登录!';
			}
			if( intval( $user['is_stop'] ) == 0 ){
				return '此账户被冻结，请联系管理员!';
			}
			if( md5( $userpass ) == $user['password'] ){ //密码匹配
				$this->userinfo=$user;
				$this->extraInfo = array('userType'=>'stuff','userID'=>$user['id'],'ctfrom'=>$user['ctfrom']);
				$this->setSession();
				if(!setcookie('loginName',$user['username'], current_time('timestamp') + 60*60*24*365 )){
					writeover( ROOT . '/data/log2.txt', 'no');
				}
				return 'ok';
			}else{ //密码不匹配
				return '密码不匹配';
			}
		}else{ //没有找到此用户
			return '没有此用户';
		}
	}

	function hezuofangAuth($username,$userpass){
		global $db;
		$this->username=$username;
		$this->userpass=$userpass;
		/*
		$query="select * from sp_hr where username='$username';";
		$result=mysql_query($query,$db->dbh);
		*/
		$user = $db->get_row("SELECT * FROM sp_partner WHERE username = '".mysql_real_escape_string($username)."' AND deleted = 0");
		
		if( $user ){ //找到此用户
			if( md5( $userpass ) == $user['password'] ){ //密码匹配
				$user['id'] = $user['pt_id'];
				
				$this->userinfo=$user;
				$this->extraInfo = array('userType'=>'hezuofang','userID'=>$user['pt_id'],'ctfrom'=>$user['code']);
				$this->setSession();
				if(!setcookie('loginName',$user['username'], current_time('timestamp') + 60*60*24*365 )){
					writeover( ROOT . '/data/log2.txt', 'no');
				}
				return 'ok';
			}else{ //密码不匹配
				return '密码不匹配';
			}
		}else{ //没有找到此用户
			return '没有此用户';
		}
	}

	//@zbzytech 加入逻辑使之变为根据登陆判定不同的登陆结果并进行
	function customerAuth($username,$userpass,$usertype){ //外部人员认证
		global $db;
		$this->username=$username;
		$this->userpass=$userpass;
		$this->usertype=$usertype;
		/*
		$query="select * from sp_hr where username='$username';";
		$result=mysql_query($query,$db->dbh);
		*/
		$user = $db->get_row("SELECT * FROM sp_customer WHERE 1 AND username = '$username' AND deleted = 0");
		if( $user ){ //找到此用户
			if( intval( $user['is_hire'] ) == 2 ){
				return '离职人员不可以登录!';
			}
			if( intval( $user['is_stop'] ) == 0 ){
				return '此账户被冻结，请联系管理员!';
			}
			if( md5( $userpass ) == $user['password'] ){ //密码匹配
				//@zbzytech 多一个判断$user
				$user['is_customer'] = 'yes';
				$this->userinfo=$user;
				$this->extraInfo = array('userType'=>'customer','userID'=>$user['cu_id'],'ctfrom'=>$user['ctfrom']);
				$this->setSession();
				if(!setcookie('loginName',$user['username'], current_time('timestamp') + 60*60*24*365 )){
					writeover( ROOT . '/data/log2.txt', 'no');
				}
				return 'ok';
			}else{ //密码不匹配
				return '密码不匹配';
			}
		}else{ //没有找到此用户
			return '没有此用户';
		}
	}

	function setSession(){ //置session
		$sid=uniqid('sid'); //生成sid
		session_id($sid);
		session_start();
		$_SESSION['appname']=$this->appname; //保存程序名
		$_SESSION['extraInfo']=$this->extraInfo;//格外信息
		$_SESSION['userinfo']=$this->userinfo; //保存用户信息（表中所有字段）
		$_SESSION['userinfo']['usertype'] = empty($this->usertype)?'1':'2';//1是机构端 2企业端
		 
		//  list($uid, $user_name, $password, $email) = uc_user_login($this->username,$this->userpass);
		 $_SESSION['userinfo']['uc_uid']=$uid;

		if($this->use_cookie){ //如果使用cookie保存sid
			setcookie('sid',$sid,null,$this->cookiepath,'60.30.69.43');
			if(!setcookie('sid',$sid,null,$this->cookiepath)){
				
				$this->errReport("set cookie failed");
				$this->err="set cookie failed";
			}
		}
		else
		setcookie('sid','',time()-3600); //清除cookie中的sid

	}
	function userLogout(){ //用户注销
		session_start();
		$_SESSION['uc_uid']=$_SESSION['userinfo']['uc_uid'];
		unset($_SESSION['userinfo']); //清除session中用户信息

		unset($_SESSION['appname']); //清除session中程序名

		if(setcookie('sid','',time()-3600)) //清除cookie中的sid

		return 'ok';
		else
		return false;
	}
	function errReport($str){ //报错
		if($this->error_report)
		echo "ERROR: $str";
	}
}
?>