<?php
// print_r($_GET);exit;
//添加-编辑企业 获取页面请求，查询列表用extract获取get变量，修改与添加用 $_GET 或$_post  
$step   = getgp("step");
$ctfrom	= getgp( 'ctfrom' )?getgp( 'ctfrom' ):$_SESSION['extraInfo']['ctfrom'];
 // var_dump($REAUEST_URI);
 // var_dump($_SESSION);
//LY 自动查询组织机构代码s

if($step=='org'){
    $work_code=getgp("work_code");
    $_code=str_replace("-","",trim($work_code));
    $orgClass=getOrgInfo($_code);
    if($orgClass['message']=='success'){
        $orgInfos=$orgClass['orgInfos'];
        $ep_info=array(
            'ep_name'       =>$orgInfos['orgName'],
            'areacode'      =>$orgInfos['areaCode'],
            'areaaddr'      =>$orgInfos['areaName'],
            'delegate'      =>$orgInfos['legalName'],
            'ep_addr'       =>$orgInfos['orgAddress'],
            'capital'       =>$orgInfos['registeredCapital'],
            'ep_addrcode'   =>$orgInfos['zipCode'],
            'work_code'     =>$work_code,
            );
        extract($ep_info,EXTR_OVERWRITE);
        $statecode_select = str_replace("value=\"156\"", "value=\"156\" selected ", $statecode_select);
//组织编号 接收POST 循环输出 *zhanghao
        if(empty($code)){

            $sql = 'select eid,code from sp_enterprises where deleted=0 ORDER BY code desc';
            $arr_info = $db->getOne($sql);
            $arr_code = explode('-',$arr_info['code']);
            if( count($arr_code)==1 ){
                $code=$arr_code[0]+1;
                $len = strlen($code);
                for ($len; $len < 4; $len++) { 
                    $code = '0'.$code;
                }
            }
         }
         if(!empty($_GET['parent_id'])){
            $sql = "select eid,code from sp_enterprises where parent_id=".$_GET['parent_id']." and deleted=0 ORDER BY code desc";
            $arr_info = $db->getOne($sql);
            if( !empty($arr_info['code']) ) { 
                $arr_code = explode('-',$arr_info['code']);
                if( count($arr_code)==1 ){
                    $len = strlen($arr_code[0]);
                    $code = $arr_code[0];
                    for ($len; $len < 4; $len++) { 
                        $code = '0'.$code;
                    }
                        $code=$arr_code[0].'-1';
                }else{
                    $len = strlen($arr_code[0]);
                    $code = $arr_code[0];
                    for ($len; $len < 4; $len++) { 
                        $code = '0'.$code;
                    }
                    $code=$code.'-'.($arr_code[1]+1);
                }
            }else{
                $sql = "select eid,code from sp_enterprises where eid=".$_GET['parent_id']." and deleted=0 ORDER BY code desc";
                $arr_info = $db->getOne($sql);
                if( !empty($arr_info['code']) ){
                    $arr_code = explode('-',$arr_info['code']);
                    if( count($arr_code)==1 ){
                        $len = strlen($arr_code[0]);
                        $code = $arr_code[0];
                        for ($len; $len < 4; $len++) { 
                            $code = '0'.$code;
                        }
                            $code=$code.'-1';
                    }else{
                        $len = strlen($arr_code[0]);
                        $code = $arr_code[0];
                        for ($len; $len < 4; $len++) { 
                            $code = '0'.$code;
                        }
                        $code=$code.'-'.($arr_code[1]+1);
                    }
                }else{
                    $sql = 'select eid,code from sp_enterprises where deleted=0 ORDER BY code desc';
                    $arr_info = $db->getOne($sql);
                    $arr_code = explode('-',$arr_info['code']);
                    if( count($arr_code)==1 ){
                        $code=$arr_code[0]+1;
                        $len = strlen($code);
                        for ($len; $len < 4; $len++) { 
                          $code = '0'.$code;
                        }
                          $code = $code.'-1';
                    }else{
                          $len = strlen($arr_code[0]);
                          $code = $arr_code[0];
                        for ($len; $len < 4; $len++) { 
                          $code = '0'.$code;
                        }
                        $code=$arr_code[0].'-'.($arr_code[1]+1);
                    }
                }
            }
         }
        tpl('enterprise/edit');
    }else{
       echo "<script>alert('请检查组织机构代码是否正确');window.history.go(-1);</script>";
       exit;
    }


}else{

//LY add读取为0，edit读取为相应eid 
$eid       = (int) getgp('eid');

//LY 母公司上级eid为0
$parent_id = (int) getgp('parent_id');

if ($_POST) {     
    // echo "<pre />";
    // print_r($_POST);exit;

    $new_enterprise = $_POST; 
    
    $new_enterprise['prod_check']=serialize($new_enterprise['prod_check']);
    $new_enterprise['parent_id']=$parent_id;
    $new_enterprise['work_code']=str_replace("-","",trim($new_enterprise['work_code']));
    $new_enterprise['update_date'] = date('Y-m-d H:i:s');
    unset($new_enterprise['step'], $new_enterprise['meta']);
    if( !empty($new_enterprise['zhanghumima']) ){
        $new_enterprise['zhanghumima'] = md5($new_enterprise['zhanghumima']);
    }
    if ($eid) {
        // echo "111";exit;
        //LY 如果eid不为0，则为修改已有企业数据           
        $af_str = serialize($enterprise->get(array(
            'eid' => $eid
        )));

        /********批量修改ctfrom*************/
        $pleid   = $new_enterprise['eid'];
        $plctrom = $new_enterprise['ctfrom'];
        //sp_contract表
        $params['ctfrom'] = $plctrom;
        $db -> update( 'contract',$params,array('eid'=>$pleid),false );
        //sp_contract_item表
        $db -> update( 'contract_item',$params,array('eid'=>$pleid),false);
        // sp_enterprises
        $db -> update( 'enterprises',$params,array('eid'=>$pleid),false);
        //sp_project表
        $db -> update( 'project',$params,array('eid'=>$pleid),false );
        //sp_task表
        $db -> update( 'task',$params,array('eid'=>$pleid),false );
        //sp_task_audit_team表
        $db -> update( 'task_audit_team',$params,array('eid'=>$pleid),false );
        //sp_tat_temp
        $db -> update( 'tat_temp',$params,array('eid'=>$pleid),false );
        //sp_ifcation
        $db -> update( 'ifcation',$params,array('eid'=>$pleid),false );
        //sp_attachments表
        $db -> update( 'attachments',$params,array('eid'=>$pleid),false );
        //sp_certificate表
        $db -> update( 'certificate',$params,array('eid'=>$pleid),false );
        //sp_certificate_change
        $zslist  = $db->getAll("select * from sp_certificate where eid =".$pleid." and deleted=0");
        foreach ($zslist as  $value) 
        {
            $params1['ctfrom'] = $value['ctfrom'];
             $db -> update( 'certificate_change',$params1,array('zsid'=>$value['id']),false );
        }
         /********批量修改ctfrom*************/



        $enterprise->edit($eid, $new_enterprise);
        $bf_str = $enterprise->get(array(
            'eid' => $eid
        ));
        // 日志： 统一写到控制器
        if ($bf_str['parent_id']) {
            $content = "[说明:关联公司修改]";
        } else {
            $content = "[说明:客户信息修改]";
        }
        log_add($eid, 0, $content, $af_str, serialize($bf_str));
    } else {
        //LY 如果eid为0则为新企业，将数据插入数据库
        $eid    = $enterprise->add($new_enterprise);
        //var_dump($eid);
        $bf_str = $enterprise->get(array(
            'eid' => $eid
        ));
        // 日志
        do {
            if ($bf_str['parent_id']) {
                $content = "[说明:关联公司登记]";
            } else {
                $content = "[说明:客户信息登记]";
            }
            log_add($eid, 0, $content, NULL, serialize($bf_str));
        } while (false);
        if ($parent_id)
            $enterprise->union_count($parent_id, 1);
    }
// 如果是客户，则转至只显示自己列表的页面
if(array_key_exists('is_customer',$_SESSION['userinfo']))
    {
        $cu_id = $_SESSION['userinfo']['cu_id'];
        showmsg('success', 'success', "?c=enterprise&a=list_edit");
    }else
    {
        showmsg('success', 'success', "?c=enterprise&a=list");
    }

}

$enterprises_archives = array();
$statecode            = '156';
$nav_title            = '企业登记';
    if ('edit' == $a or $parent_id) {
        //$eid = (int)getgp( 'eid' );
        $where_arr = ($parent_id) ? array(
            'eid' => $parent_id
        ) : array(
            'eid' => $eid
        );

        $row       = $enterprise->get($where_arr);
        extract($row, EXTR_SKIP);
        $parent_id = $row['parent_id'];
        if( $cu_id==0 ){
            $cuid_name='自建';
        }else{
            $customer = load('customer');
            $customer_info = $customer -> get($cu_id);
            if( empty($customer_info) ){
                $cuid_name='自建';
            }else{
                $cuid_name=$customer_info['name'];
            }
        }
        // if($parent_id)$work_code="";
        //print_r($parent_id);exit;   *zhanghao
        //数据库中解析prod_check
        $prod_check=str_replace('\"','"',$prod_check);
        $prod_check=str_replace("\'","'",$prod_check);
        $prod_check=str_replace("&amp;quot;",'"',$prod_check);
        $prod_check=unserialize($prod_check);
//        var_dump($prod_check);exit;
       //$statecode = $row['statecode'];
        //if ('edit' == $a)
        //$parent_id = $row['parent_id'];
        //合同来源
        $ctfrom_select   = f_ctfrom_select( $row['ctfrom'] );
        //客户级别
        $ep_level_select = str_replace("value=\"$ep_level\">", "value=\"$ep_level\" selected>", $ep_level_select);
        //企业性质
        $nature_select   = str_replace("value=\"$nature\">", "value=\"$nature\" selected>", $nature_select);
        //注册资本币种
        $currency_select = str_replace("value=\"$currency\">", "value=\"$currency\" selected>", $currency_select);
       //企业附件列表
        $archive_total   = $db->get_var("SELECT COUNT(*) FROM sp_attachments WHERE eid = '$eid'");
        $archive_join    = " LEFT JOIN sp_hr hr ON hr.id = ea.create_uid";
        $sql             = "SELECT ea.*,hr.name author FROM sp_attachments ea $archive_join WHERE ea.eid = '$eid' ORDER BY ea.id DESC LIMIT 10";
        $query           = $db->query($sql);
        while ($rt = $db->fetch_array($query)) {
            $rt['ftype_V']                   = f_arctype($rt['ftype']);
            $enterprises_archives[$rt['id']] = $rt;
        }
    }
///组织编号循环输出code   *zhanghao
 if(empty($code)){ 
    $sql = 'select eid,code from sp_enterprises where deleted=0 ORDER BY code desc';
    $arr_info = $db->getOne($sql);
    $arr_code = explode('-',$arr_info['code']);
    $code=$arr_code[0]+1;
    $len = strlen($code);
    for ($len; $len < 4; $len++) { 
        $code = '0'.$code;
    }
 }
 if(!empty($_GET['parent_id'])){
    $sql = "select eid,code from sp_enterprises where parent_id=".$_GET['parent_id']." and deleted=0 ORDER BY code desc";
    $arr_info = $db->getOne($sql);
    if( !empty($arr_info['code']) ) { 
        $arr_code = explode('-',$arr_info['code']);
        if( count($arr_code)==1 ){
            $len = strlen($arr_code[0]);
            $code = $arr_code[0];
            for ($len; $len < 4; $len++) { 
                $code = '0'.$code;
            }
            $code=$arr_code[0].'-1';
        }else{
            $len = strlen($arr_code[0]);
            $code = $arr_code[0];
            for ($len; $len < 4; $len++) { 
                $code = '0'.$code;
            }
            $code=$code.'-'.($arr_code[1]+1);
        }

    }else{
        $sql = "select eid,code from sp_enterprises where eid=".$_GET['parent_id']." and deleted=0 ORDER BY code desc";
        $arr_info = $db->getOne($sql);
        if( !empty($arr_info['code']) ){
            $arr_code = explode('-',$arr_info['code']);
            if( count($arr_code)==1 ){
                $len = strlen($arr_code[0]);
                $code = $arr_code[0];
                for ($len; $len < 4; $len++) { 
                    $code = '0'.$code;
                }
                $code=$code.'-1';
            }else{
                $len = strlen($arr_code[0]);
                $code = $arr_code[0];
                for ($len; $len < 4; $len++) { 
                    $code = '0'.$code;
                }
                $code=$code.'-'.($arr_code[1]+1);
            }
        }else{
            $sql = 'select eid,code from sp_enterprises where deleted=0 ORDER BY code desc';
            $arr_info = $db->getOne($sql);
            $arr_code = explode('-',$arr_info['code']);
            if( count($arr_code)==1 ){
                $code=$arr_code[0]+1;
                $len = strlen($code);
                for ($len; $len < 4; $len++) { 
                    $code = '0'.$code;
                }
                $code = $code.'-1';
            }else{
                $len = strlen($arr_code[0]);
                $code = $arr_code[0];
                for ($len; $len < 4; $len++) { 
                    $code = '0'.$code;
                }
                $code=$arr_code[0].'-'.($arr_code[1]+1);
            }
        }
    }
 }
 //@zbzytech 空数据 为声明数组导致问题
 if(!is_array($prod_check)) $prod_check = array();

//国家代码
$statecode_select = str_replace("value=\"$statecode\">", "value=\"$statecode\" selected>", $statecode_select);
//合同来源
//print_r($ctfrom_select);exit;
tpl('enterprise/edit');
}