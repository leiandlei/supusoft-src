<?php
/**
 * Wxuser 
 * @package 微信
 * @access public
 */
class Wxuser extends Cfg{

    
    /**
     * Wxuser::getUsers()
     * 获取关注着列表
     * @param string $nextOpenid
     * @return Array
     */
    public function getUsers($nextOpenid = ''){
        $api = 'https://api.weixin.qq.com/cgi-bin/user/get?next_openid='.$nextOpenid;
        
        $data = $this->tokenRequest($api);
        if(isset($data['total'])){
            return $data;
        }
        return false;
    }
    /**
     * Wxuser::getInfo()
     * 获取用户信息，包括昵称、性别、头像、地址、关注时间。如果用户没关注微信，则获取不到个人信息
     * 
     * 返回值中的头像 headimgurl 最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     * @param string $userOpenid 用户openid
     * @return Array
     * 
     * @refer http://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html
     */
    public function getInfo($userOpenid, $lang = 'zh_CN'){
        $info = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/user/info?openid='.$userOpenid.'&lang='.$lang);
        if(is_array($info) && $info['subscribe'] == 1){
            return $info;
        }
        return array();
    }
    
    /**
     * Wxuser::batchgetInfo()
     * 批量获取用户信息，最多支持一次拉取100条
     * @param mixed $openidArray 可直接为openid数组或混合指定语言，如不指定语言，使用默认值。如下：
     * array(
     *  'otvxTs4dckWG7imySrJd6jSi0CWE',
     *  'otvxTs4dckWG7imySrJd6jSi0CWE',
     *  array('openid'=>'otvxTs4dckWG7imySrJd6jSi0CWE','lang'=>'zh_CN'),
     *  'otvxTs4dckWG7imySrJd6jSi0CWE')
     * @param string $lang 国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN
     * @return void
     */
    public function batchgetInfo($openidArray, $lang = 'zh_CN'){
        $data = array();
        foreach($openidArray as $openid){
            if(is_string($openid)){
                $data[] = array('openid'=>$openid, 'lang'=>$lang);
            }elseif(is_array($openid) && isset($openid['openid'])){
                $data[] = array('openid'=>$openid['openid'], 'lang'=>isset($openid['lang']) && !empty($openid['lang']) ? $openid['lang'] : $lang);
            }else{
                continue;
            }
        }
        $json = bwx_json_encode(array('user_list'=>$data));
        $data = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/user/info/batchget', $json);
        return $data['user_info_list'];
    }
    
    /**
     * Wxuser::getGroups()
     * 获取分组
     * @return Array
     */
    public function getGroups(){
        return $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/get');
        
    }
    
    /**
     * Wxuser::getUserGroup()
     * 获取用户所在组
     * @param mixed $openid
     * @return void
     */
    public function getUserGroup($openid){
        $json = '{"openid":"'.$openid.'"}';
        $data = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/getid',$json);
        return !empty($data) && isset($data['groupid']) ? $data['groupid'] : false;
    }
    
    /**
     * Wxuser::createGroups()
     * 创建分组
     * @param string $groupName 
     * @return Bool
     */
    public function createGroup($groupName){
        $json = '{"group":{"name":"'.$groupName.'"}}';
        $arr = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/create',$json);
        if(isset($arr['group']) && $arr['group']['id'] > 0){
            return $arr['group'];
        }else{
            return false;
        }
    }
    
    
    /**
     * Wxuser::updateGroup()
     * 修改分组名称
     * @param integer $groupId
     * @param string $newName
     * @return Bool
     */
    public function updateGroup($groupId, $newName){
        $json = '{"group":{"id":'.$groupId.',"name":"'.$newName.'"}}';
        $arr = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/update',$json);
        if(isset($arr['errcode']) && $arr['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
    
    
    /**
     * Wxuser::deleteGroup()
     * 删除分组【该接口微信暂未开放 2013.12.20】
     * @param integer $groupId
     * @return Bool
     */
    public function deleteGroup($groupId = -1){
        $json = '{"group":{"id":'.$groupId.'}}';
        $arr = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/delete',$json);
        if(isset($arr['errcode']) && $arr['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Wxuser::moveUser()
     * 移动用户到指定分组
     * @param string $userOpenid
     * @param integer $toGroupid
     * @return Bool
     */
    public function moveUser($userOpenid, $toGroupid = 0){
        $json = '{"openid":"'.$userOpenid.'","to_groupid":'.$toGroupid.'}';
        $arr = $this->tokenRequest('https://api.weixin.qq.com/cgi-bin/groups/members/update',$json);
        if(isset($arr['errcode']) && $arr['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
 
}
//echo '<pre>';
//$wx= new Wxuser;
/*
$users = $wx->getUsers();
foreach($users['data']['openid'] as $oid){
    $wx->getInfo($oid);
}
*/
//$user = 'oh9GgjtG8hertzCoiQq7HEcOWItg';
//$wx->getInfo($user);
//$wx->getGroups();
//$wx->createGroup("云传媒");
//$wx->updateGroup(101,"CloudM");
//$wx->moveUser($user,100);
//$wx->getGroups();
