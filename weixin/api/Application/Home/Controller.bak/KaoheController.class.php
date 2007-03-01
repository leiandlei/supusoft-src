<?php
namespace Home\Controller;
use Think\Controller;
class KaoheController extends ApiController
{
	/**
	 * sp_examine_user列表
	 */
	public function examine_user(){
        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
                if($this->auto=='normal'){
                    $where['eu.userID']=$this->userID;
                }
            	$model = M('examine_user eu');
				$count = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->count('eu.id');
				$page  = self::getPageInfo($count);//获取分页
                $list  = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->limit($page['limit']['start'],$page['limit']['end'])->select();
            	$results = self::getArrayForResults( 0,'',$list );
            	break;
            case 'visitor'://游客
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        /**---程序开始---**/
        exit(json_encode($results));
	}

	/**
	 * sp_examine_user_info列表
	 */
	public function examine_user_info(){
        /**---程序开始---**/
        $join = $where = array();$order = $field='';
        
        switch ($this->auto){//获取用户类型
            case 'admin'://管理员
            case 'normal'://正常用户
                if($this->auto=='normal'){
                    $where['eui.userID']=$this->userID;
                }
				$where['eui.exu_id'] = I('post.exu_id');
				
            	$model  = M('examine_user_info eui');
				$field  = 'eui.day,eui.createTime,eui.content as econtent';
				
				$join[] = ' LEFT JOIN sp_examine on eui.ex_id=sp_examine.id';
				$field .= ',sp_examine.name,sp_examine.content,sp_examine.day as initday,sp_examine.types';
				
				$join[] = ' LEFT JOIN sp_examine_user on eui.exu_id=sp_examine_user.id';
				$field .= ',sp_examine_user.day as countday,sp_examine_user.date,sp_examine.day as initday';
				
				$count = $model ->join($join)->where($where)->field(empty($field)?'*':$field)->count('eui.id');
				$page  = self::getPageInfo($count);//获取分页
                $list  = $model ->join($join)->where($where)->order('eui.id DESC')->field(empty($field)?'*':$field)->limit($page['limit']['start'],$page['limit']['end'])->select();
            	$results = self::getArrayForResults( 0,'',$list );
            	break;
            case 'visitor'://游客
            case 'draft'://未激活
            case 'pending'://禁言
            case 'disabled'://封号
            default:
                $results = self::getArrayForResults( 1,'您没有权限执行该操作' );
                break;
        }
        /**---程序开始---**/
        exit(json_encode($results));
	}
}