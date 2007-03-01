<?php
$notice = load('notice');
$url    = dirname(dirname(__FILE__));
$a      = getgp('a');
// echo $a;exit;
if ('add' == $a) {
    $tip_msg = '发布公告';
    tpl('notice/notice_edit');
} else if ($a == 'edit') {
    $id = (int)getgp('id');
    if( !empty($id) ){
        $sql = "select * from `sp_notice` where id=".$id;
        $r_info = $db->getOne($sql);
        extract( $r_info );
    }
    $tip_msg = '编辑公告';
    tpl('notice/notice_edit');
} else if ($a == 'save') {
    $id               = getgp('id');
    $params = array(
            'title'   => getgp('title'),
            'type'    => getgp('type'),
            'content' => getgp('content'),
            'status'  => 1
        );
    $unionToken = array();

    switch($params['type'])
    {
    	case 1://公司公告
            $results     = $db->getAll('select * from sp_unionlogin where status=1');
            foreach($results as $item)
            {
                if(!empty($item['unionToken']))
                {
                    $unionToken[]  = array('userid'=>$item['userID'],'openid'=>$item['unionToken']);
                }
            }
            $ggtype   = "公司公告";
            $ggtypead = "审核员专区-公司公告";
            break;
        case 11://指定用户
            if(empty(getgp('receiveuser')))showmsg('请选择用户','error');
            $receiveuser =implode(',',array_filter(array_unique(explode(',', getgp('receiveuser')))));
            
            $results     = $db->getAll('select * from sp_unionlogin where userID in('.$receiveuser.') and status=1');
            foreach($results as $item)
            {
                if(!empty($item['unionToken']))
                {
                    $unionToken[]  = array('userid'=>$item['userID'],'openid'=>$item['unionToken']);
                }
            }
            $ggtype   = "个人通知";
            $ggtypead = "审核员专区-审核员通知";
            break;
        case 2://审核员
            //查询所有审核员并赋值到$receiveuser;
            $receiveuser = array();
            $results = $db->getAll('select hr.*,un.unionToken from sp_hr hr left join sp_unionlogin un on hr.id=un.userID and un.status=1 where hr.job_type like \'%1004%\'');
            foreach($results as $item)
            {
                if(!empty($item['unionToken'])&&!in_array($item['id'],$receiveuser))
                {
                    $receiveuser[] = $item['id'];
                    $unionToken[]  = array('userid'=>$item['id'],'openid'=>$item['unionToken']);
                }
            }
            $receiveuser =implode(',',array_filter(array_unique($receiveuser)));
            $ggtype   = "审核员公告";
            $ggtypead = "审核员专区-审核员通知";
            break;
        default:break;  
    }
     
    $params['receiveuser'] = $receiveuser;
    //推送

    // if( false )
    if( !empty($unionToken) )
    {
        $messageTem =
            '{
                "touser":"%s",
                "template_id":"7qywxhkZYITn3CuQyUwKqS0eGnKaIXHE2cd9x_hD1H4",
                "topcolor":"#FF0000",
                "data":{
                    "first": {
                        "value":"您好，收到一条%s，请到“%s”中查收",
                        "color":"#173177"
                        },
                    "keyword1":{
                        "value":"%s",
                        "color":"#173177"
                        },
                    "keyword2":{
                        "value":"%s",
                        "color":"#173177"
                        },
                    "keyword3":{
                        "value":"%s",
                        "color":"#173177"
                        },
                    "remark":{
                        "value":"如有需要请联系010-88255986",
                        "color":"#173177"
                        }
                }
            }';
        
		global $arrOptions;
        $weObj = load('Wechat',$arrOptions);
        $weObj->checkAuth();
        foreach($unionToken as $item)
        {
            $message = sprintf( $messageTem,$item['openid'],$ggtype,$ggtypead,$params['title'],date('Y-m-d H:i:s'),$ggtypead );
            $temp    = $weObj  -> sendTemplateMessage( $message );
        }
    }
    
    
    
    if ($id) {
            $notice->edit($id, $params);
    } else 
            $id = $notice->add($params);
    //上传公告文档
    if( !empty( $_FILES['fileurl'] ) ){
        $upload           = load('upload');
        $upload->savePath = get_option('upload_notice_dir');
        $value            = array();
        if ($upload->upload()) {
            $info = $upload->getUploadFileInfo();
            if ($_FILES['fileurl']['name']) {
                    $filename = $info[0]['savename'];
                    $notice->edit($id, array(
                        'filename' => $filename,
                        'filename_begin' => $_FILES['fileurl']['name']
                    ));
                }
            
        }
    }
    
    showmsg('success', 'success', '?c=notice&a=list',5);
    
} else if ('del' == $a) {
    $id = getgp('id');
    if ($id) {
        $notice->del($id);
    }
    $REQUEST_URI = '?c=notice&a=list';
    showmsg('success', 'success', $REQUEST_URI);
} else if ($a == 'list') {
    $fields = $join = $where = '';
    $join   = " INNER JOIN sp_hr hr ON hr.id = n.update_uid";
    $datas  = array();
    foreach ($_POST as $k => $v) {
        ${$k} = getgp($k);
    }
    $where = " and n.status >= 0 ";
    if ($name) {
        $sql  = "select id from sp_hr where name like '%$name%' ";
        $uids = array();
        $res  = $db->query($sql);
        while ($row = $db->fetch_array($res)) {
            $uids[] = $row['id'];
        }
        if ($uids) {
            $uid_str = implode("','", $uids);
            $where .= " and n.update_uid in ('$uid_str') ";
        } else {
            $where .= " and n.update_uid='' ";
        }
    }
    if ($title) {
        $where .= " and n.title  like '%$title%' ";
    }
    if ($s_date) {
        $where .= " and n.up_date >= '$s_date' ";
    }
    if ($e_date) {
        $where .= " and n.up_date <= '$e_date' ";
    }
    $total = $db->get_var("SELECT COUNT(*) FROM sp_notice n $join WHERE 1 $where");
    $pages = numfpage($total, 20, "?c=$c&a=$a");
    $sql   = "SELECT n.*,hr.name author FROM sp_notice n $join WHERE 1 $where ORDER BY n.id DESC $pages[limit]";
    $query = $db->query($sql);
    while ($rt = $db->fetch_array($query)) {
        $rt['filename'] = substr($rt['filename'], strlen($rt['id']) + 1);
        $datas[]        = $rt;
    }
    tpl('notice/notice_list');
} else if ($a == 'download') {
    $id          = getgp('id');
    $notice_info = $notice->get($id);
    
    /**下载插入已阅**/
    $viewUser     = $notice_info['viewuser'];
    $arr_viewUser = array();
    if($viewUser != '')$arr_viewUser=explode(',',$viewUser);
    $usesrid = current_user('uid');
    if( !in_array($usesrid,$arr_viewUser) ){
        $arr_viewUser[]=$usesrid;
        $viewUser= implode(',',$arr_viewUser);
        $sql = "update `sp_notice` set viewuser='".$viewUser."' where id=".$id;
        $db->query($sql);
    }

    /**下载插入已阅**/


    $file_dir    = get_option('upload_notice_dir');
    $path        = $file_dir . $notice_info['filename'];
    $path        = iconv('UTF-8', 'GB2312', $path);
    header('Last-Modified: ' . date('D, d M Y H:i:s', time()) . ' GMT');
    header('Expires: ' . date('D, d M Y H:i:s', time()) . ' GMT');
    header('Cache-control: max-age=86400');
    header('Content-Encoding: none');
    //@HBJ 2013-9-18 解决各个浏览器下载兼容问题
    $filename         = $notice_info['title'] . substr(strrchr($notice_info['filename'], '.'), 0);
    $encoded_filename = urlencode($filename);
    $encoded_filename = str_replace("+", "%20", $encoded_filename);
    $ua               = $_SERVER["HTTP_USER_AGENT"];
    if (preg_match("/MSIE/i", $ua)) {
        header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
    } elseif (preg_match("/Firefox/i", $ua)) {
        header('Content-Disposition: attachment; filename*="utf8/' . $filename . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: binary");
    ob_clean();
    flush();
    echo file_get_contents($path);
    exit;
} else if ($a == 'viewUserList'){
    $id            = (int)getgp('id');
    $type          = getgp('type');
    $userlist      = $db->getOne("select * from sp_notice where id=".$id);
    $viewuser      = array_unique(empty($userlist["viewuser"])?array():explode(',', $userlist["viewuser"]));
	$receiveuser   = array_unique(empty($userlist["receiveuser"])?array():explode(',', $userlist["receiveuser"]));
	$alluser       = array_unique(array_merge($viewuser,$receiveuser));
	$results       = array();
   	if( !empty($alluser) )
   	{
   		$join =  $select = '';$where =' where 1';
	    $sql           = 'select %s from `sp_hr`'; 
	    $where        .= ' and deleted =0';
		$where        .= ' and id in('.implode(",", $alluser).')';
		$seach         = getSeach();
		foreach ($seach as $key => $value) {
	        switch ($key) {
	            case 'name':
	                $str = " and `%s` like '%%%s%%'";
	                break;
	            case 'ifread':
	                if($value=='1')
	                {
	                    $where .= ' and id in('.$userlist["viewuser"].')'; 
	                }else{
	                    $where .= ' and id not in('.$userlist["viewuser"].')'; 
	                }
	                break;
	            default:
	                $str = " and `%s` like '%%%s%%'";
	                break;
	        }
	
	        $where .= sprintf($str,$key,$value);
	    }
	    $sql = sprintf($sql,($select=='')?'*':$select).$join.$where;
		$results   = $db->getAll($sql);
		extract($seach,EXTR_OVERWRITE);

		foreach($results as $key=>$item)
		{
			$item['read']  =  in_array($item['id'], $viewuser)?'已阅':'未阅';
			$results[$key] = $item;
		}
   	}
    tpl('notice/viewUserList');
}
?>