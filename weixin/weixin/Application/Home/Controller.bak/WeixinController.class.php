<?php
namespace Home\Controller;
use Think\Controller;
use Org\Wechat as wx;
class WeixinController extends ApiController {
    public function index(){
    	global $arrOptions;
		$weObj = new wx\TPWechat($arrOptions);
	 	// $weObj->valid();exit;
		// $menu = $weObj->getMenu();
		// $menu = file_get_contents('./Public/common/menu/menu_weixin.json');
		// $menu = $weObj->createMenu(json_decode($menu,true));
		// print_r($menu);exit;

	   	$weObj    -> getRev();             //接受消息
	    $msgType  =  $weObj->getRevType(); //事件类型
	    $event    =  $weObj->getRevEvent();//事件信息
		$data     =  $weObj->getRevData(); //收到的信息
		$user     =  $weObj->getRevFrom(); //消息发送者

		// $results = self::httpToApi('Weixin/getUserInfoByOpenID',array('unionToken'=>$user,'unionType'=>4));
		// $weObj->text(json_encode($data))->reply();exit;
	   	switch($msgType) {
	   		//点击事件
	   		case wx\Wechat::MSGTYPE_EVENT:
	   			switch ($event['event']) {
	   				//点击菜单拉取消息
	   				case wx\Wechat::EVENT_MENU_CLICK:
	   					//哪个按钮
	   					switch ($event['key']) {
	   						//企业简介
	   						case 'qiyejianjie':
								$text = "        中标华信(北京)认证中心有限公司（简称LLL）是经中国认证认可监督管理委员会（CNCA）正式批准的综合类认证服务实体。中标华信(北京)认证中心首批获得认监委批准并首个获得服务认证资格的认证机构。机构同时具有质量、环境、职业健康安全管理体系认证资格，一次认证可同时颁发管理体系认证和服务认证证书。为有需求的认证客户提供方便的专业化一站式服务。
        中标华信(北京)认证中心吸引了行业内认证界的高端人才，机构拥有行业内多年参与认证相关标准制定的知名专家和多名高级审核员及技术专家，技术力量雄厚。2015年本机构参加了服务认证审查员能力评价行业标准的制定。2016年中标华信(北京)认证中心又率先获得认证认可协会CCAA服务认证审查员培训课程确认，成为行业内具有培养服务认证审查员资格的认证机构。
        中标华信(北京)认证中心中文网站（www.lll.cn），网站面向全国宣传中心的认证工作，也是获证企业获证后的服务之家，为所有关注认证事业的企业及同仁提供一个信息交流沟通的平台。
        中标华信(北京)认证中心成立以来，确立了公证、负责、独立、权威的质量方针，努力实现技术、业务和各项资源的全面整合及有效管理，以科学、规范、严谨的工作作风和态度，精湛的技能、高素质的人员队伍、严明的纪律和优质的服务，在国内外认证领域树立了良好的行业形象，为我们的客户提供专业化认证以及相关的技术服务。";
			   					$weObj->text($text)->reply();
			   					break;

			   					case 'lianxifangshi':
								$text = "中心全称：中标华信（北京）认证中心有限公司
办公地址：北京市石景山区石景山路3号玉泉大厦5层
邮编：100049
电话：010-88255986
E-mail:lll@lll.cn
简历投递：lll_cn@163.com
网址：www.lll.cn";
			   					$weObj->text($text)->reply();
			   					break;

			   					case 'zhaopinxinxi':
								$text = "中标华信认证中心现诚招以下人员

审核员（专/兼职）：
        具有国家CCAA注册审核员或实习审核员资格；工作认真负责，善于沟通、客观公正。

认证业务管理培训生：
        在理解公司发展战略和组织结构的基础上，作为管理培训生加入到公司的人才储备计划，熟悉整个公司运作流程，在合同评审、审核方案策划、人员评定、客户服务等部门轮岗，轮岗考核后作为管理团队的培养对象。

应聘联系人：
伍老师 010-88255986/13269923231

简历投递：
lll_cn@163.com";
			   					$weObj->text($text)->reply();
			   					break;
	   						default:
	   							$weObj->text("暂不支持功能")->reply();
	   							break;
	   					}
	   					break;
					
					case wx\Wechat::EVENT_SUBSCRIBE:        //订阅
	   						$text = "        中标华信(北京)认证中心有限公司（简称LLL）是经中国认证认可监督管理委员会（CNCA）正式批准的综合类认证服务实体。中标华信(北京)认证中心首批获得认监委批准并首个获得服务认证资格的认证机构。机构同时具有质量、环境、职业健康安全管理体系认证资格，一次认证可同时颁发管理体系认证和服务认证证书。为有需求的认证客户提供方便的专业化一站式服务。
        中标华信(北京)认证中心吸引了行业内认证界的高端人才，机构拥有行业内多年参与认证相关标准制定的知名专家和多名高级审核员及技术专家，技术力量雄厚。2015年本机构参加了服务认证审查员能力评价行业标准的制定。2016年中标华信(北京)认证中心又率先获得认证认可协会CCAA服务认证审查员培训课程确认，成为行业内具有培养服务认证审查员资格的认证机构。
        中标华信(北京)认证中心中文网站（www.lll.cn），网站面向全国宣传中心的认证工作，也是获证企业获证后的服务之家，为所有关注认证事业的企业及同仁提供一个信息交流沟通的平台。
        中标华信(北京)认证中心成立以来，确立了公证、负责、独立、权威的质量方针，努力实现技术、业务和各项资源的全面整合及有效管理，以科学、规范、严谨的工作作风和态度，精湛的技能、高素质的人员队伍、严明的纪律和优质的服务，在国内外认证领域树立了良好的行业形象，为我们的客户提供专业化认证以及相关的技术服务。";
			   				$weObj->text($text)->reply();
	   					break;
	   				case wx\Wechat::EVENT_MENU_VIEW:        //点击菜单跳转链接
	   				case wx\Wechat::EVENT_SCAN:             //扫描带参数二维码
	   				case wx\Wechat::EVENT_MENU_SCAN_PUSH:   //扫码推事件(客户端跳URL)
	   				case wx\Wechat::EVENT_MENU_SCAN_WAITMSG://扫码推事件(客户端不跳URL)		
	   				case wx\Wechat::EVENT_MENU_PIC_SYS:     //弹出系统拍照发图
	   				case wx\Wechat::EVENT_MENU_PIC_PHOTO:   //弹出拍照或者相册发图
	   				case wx\Wechat::EVENT_MENU_PIC_WEIXIN:  //弹出微信相册发图器
	   				case wx\Wechat::EVENT_MENU_LOCATION:    //弹出地理位置选择器
			   		case wx\Wechat::EVENT_UNSUBSCRIBE:      //取消订阅
			   		case wx\Wechat::EVENT_LOCATION:         //上报地理位置 允许获取位置后每次进入都会收到此处的位置信息用不到不作处理
			   			break;
	   				default:
			   			$weObj->text("暂不支持功能")->reply();
			   			break;
	   			}
	   			break;

	   		//发图片
	   		case  wx\Wechat::MSGTYPE_IMAGE:
	   			$results = $this->httpToApi('Weixin/huichuan',array('unionToken'=>$user,'unionType'=>4,'url'=>$data['PicUrl']));
	   			switch ($results['errorCode']) {
	   				case '0':
	   					$weObj->text('发送成功')->reply();
	   					break;
	   				case '1'://错误信息
	   					$weObj->text($results['errorStr'])->reply();
	   					break;
	   				case '2'://没有绑定账号
	   					$url = 'http://'.$_SERVER['HTTP_HOST'].U('index/index');
	   					$str = $results['errorStr'].'点击绑定账号:'.$url;
	   					$weObj->text($str)->reply();
	   					break;
	   			}
	   			break;
	   		//发文字
	   		case  wx\Wechat::MSGTYPE_TEXT:
	   			$results = $this->httpToApi('Weixin/huichuan',array('unionToken'=>$user,'unionType'=>4,'content'=>$data['Content']));
	   			switch ($results['errorCode']) {
	   				case '0':
	   					$weObj->text('发送成功')->reply();
	   					break;
	   				case '1'://错误信息
	   					$weObj->text($results['errorStr'])->reply();
	   					break;
	   				case '2'://没有绑定账号
	   					$url = 'http://'.$_SERVER['HTTP_HOST'].U('index/index');
	   					$str = $results['errorStr'].'点击绑定账号:'.$url;
	   					$weObj->text($str)->reply();
	   					break;
	   			}
	   			break;
	   		case  wx\Wechat::MSGTYPE_LOCATION://发地理位置
	   		case  wx\Wechat::MSGTYPE_MUSIC://发音频
	   		case  wx\Wechat::MSGTYPE_VOICE://语音输入
	   		case  wx\Wechat::MSGTYPE_VIDEO://发视频
	   		case  wx\Wechat::MSGTYPE_NEWS: //图文消息
	   		default:
	   			$weObj->text('暂不支持该功能哦')->reply();
	   			break;
	   	}
    }
}