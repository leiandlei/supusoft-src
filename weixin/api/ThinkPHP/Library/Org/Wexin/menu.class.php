<?php
/**
 * Menu
 * 创建、获取、删除 底部菜单
 * @package 微信
 * @author zhang
 * @access public
 */
class Menu extends Cfg {
    
    /**
     * Menu::create()
     * 
     * @return String
     */
    public function create($jsonStr){
        
        $baseUrl = 'https://api.weixin.qq.com/cgi-bin/menu/create';
        $rs = $this->tokenRequest($baseUrl, $jsonStr);
        if($rs['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Menu::get()
     * 
     * @return Array
     */
    public function get(){
        
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get';
        return $this->tokenRequest($url);
    }
    
    /**
     * Menu::delete()
     * 
     * @return Array
     */
    public function delete(){
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete';
        $rs = $this->tokenRequest($url);
        if($rs['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
}

//$Menu = new Menu();
//$Menu->create();