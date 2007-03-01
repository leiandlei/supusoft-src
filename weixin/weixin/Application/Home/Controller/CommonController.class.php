<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends ApiController {
    Public function _initialize(){
       // 初始化的时候检查用户权限
       // echo '<pre />';
       // print_r($_SESSION);exit;
      if( !$this->checkLogin() ){
          session('actionName',null);
           cookie('actionName',null);
          session('actionName',CONTROLLER_NAME."/".ACTION_NAME);
           cookie('actionName',CONTROLLER_NAME."/".ACTION_NAME);
          $this->redirect('Login/index');exit;
      }
      //$this -> Menu = sprintf('<ul class="nav nav-list">%s</ul>',$this->getMenu($this -> getMenuInfo()));
    }

   	public function checkLogin(){
      // print_r($_SESSION);exit;
      if( !$_SESSION[SANDC_KEY]['id'] ){
        session(SANDC_KEY,null);
        cookie(SANDC_KEY,null);
        // @unset($_SESSION);
        return false;
      }
   		return true;
   	}


    public function getParamsForFrom( $array ){
        $params=array();
        foreach ($array as $value) {
            $params[$value['name']]=$value['value'];
        }
        return $params;
    }

    //获取分页信息
    public static function getPageInfo($count){
        $size      = !empty($_REQUEST['size'])?$_REQUEST['size']:1;
        $nowpage   = !empty($_REQUEST['page'])?$_REQUEST['page']:10;
        $countPage = (int)ceil( $count/$size );
        $upPage    = ($nowpage - 1 < 1)?1:$nowpage - 1;
        $nextPage  = ($nowpage + 1 > $countPage)?$countPage:$nowpage + 1;

        return array(
                'size'      => $size
               ,'nowpage'   => $nowpage
               ,'countPage' => $countPage
               ,'upPage'    => $upPage
               ,'nextPage'  => $nextPage
               ,'countTotal'=> $count
            );
    }

   	//获取菜单
   	Public function getMenu( $menu_array ){
   			$a    = explode('/',$_SERVER['REQUEST_URI']);
   			$self = array($a[count($a)-2],$a[count($a)-1]);
   			$self = implode(':',$self);
   			if(strripos($self,'.'))$self = substr($self,0,strripos($self,'.'));
			$info = '';
			foreach ($menu_array as $detail) {
                
				//if( !strstr($_SESSION['userInfo']['sys'],$detail['sys']) )continue;
				if($detail['show']==0)continue;
				if( is_array($detail['next']) ){
                    $active = '';
                    if(strpos( self::getMenu($detail['next']),'class=active' )){
                        $active = 'active open';
                    }
					$icon = $detail["icon"];
					$name = $detail["name"];
					$info .= '
					<li class="'.$active.'">
						<a href="#" class="dropdown-toggle">
							<i class="'.$icon.'"></i>
							<span class="menu-text"> '.$name.' </span>
							<b class="arrow icon-angle-down"></b>
						</a>
						<ul class="submenu">%s</ul>
					</li>';
					$info = sprintf($info, self::getMenu($detail['next']));
				}else{
                    $active = ($self==$detail['sys'])?'active':'';
					$icon = $detail["icon"];
					$name = $detail["name"];
					$next = $detail["next"];
					$info .= '
					<li class='.$active.'>
						<a href="'.U($next).'">
							<i class="'.$icon.'"></i>
							<span class="menu-text"> '.$name.' </span>
						</a>
					</li>';
				}
			}
		return $info;
   	}

   	//菜单详情
   	public function getMenuInfo(){
        $filename = "./Public/common/menu.json";
        $menu = file_get_contents($filename);
   		return json_decode($menu,true);
   	}
}