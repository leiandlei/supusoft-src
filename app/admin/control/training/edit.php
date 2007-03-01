<?php
//添加-编辑企业 获取页面请求，查询列表用extract获取get变量，修改与添加用 $_GET 或$_post  
//echo "here";
$step=getgp("step");
//var_dump($step);
//var_dump($REAUEST_URI);

//LY add读取为0，edit读取为相应eid 
$tid       = (int) getgp('tid');
//echo "here";
//var_dump($tid);
//echo "【post:】";
//var_dump($_POST);
//LY 如有POST，则为相赠或修改需求
if ($_POST) { 
    $new_training= $_POST; 
	$new_enterprise['prod_check']=serialize($new_enterprise['prod_check']);
	$new_enterprise['parent_id']=$parent_id;
	$new_enterprise['work_code']=str_replace("-","",trim($new_enterprise['work_code']));
    unset($new_enterprise['step'], $new_enterprise['meta']);
    if ($eid) {
        //LY 如果eid不为0，则为修改已有企业数据
        //总部的人才能修改合同来源
        $u_ctfrom = current_user('ctfrom');                        
        if ('01000000' != $u_ctfrom) {
            unset($new_enterprise['ctfrom']);
        }
        $af_str = serialize($enterprise->get(array(
            'eid' => $eid
        )));
        
        $enterprise->edit($eid, $new_enterprise);
        $bf_str = serialize($enterprise->get(array(
            'eid' => $eid
        )));
        // 日志： 统一写到控制器
        do {
            if ($bf_str['parent_id']) {
                $content = "[说明:关联公司修改]";
            } else {
                $content = "[说明:客户信息修改]";
            }
            log_add($eid, 0, $content, $af_str,$bf_str);
        } while (false);
    } else {
        //LY 如果eid为0则为新企业，讲数据插入数据库
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
    showmsg('success', 'success', "?c=enterprise&a=list");
}
//echo "here";
//在页面中显示数据库中已有信息
// $enterprises_archives = array();
// $statecode            = '156';
// $nav_title            = '企业登记';
//var_dump($a);
 if ('edit' == $a) {
    $tid = (int)getgp( 'tid' );
     $where_arr = array(
         'tid' => $tid
     );
     //var_dump($where_arr);
     $row       = $training->get($where_arr);
    // var_dump($row);
     extract($row, EXTR_SKIP);
     //var_dump($row);
	if($parent_id)$work_code="";
    //数据库中解析prod_check
    $prod_check=str_replace('\"','"',$prod_check);
    $prod_check=str_replace("\'","'",$prod_check);
    $prod_check=str_replace("&amp;quot;",'"',$prod_check);
    $prod_check=unserialize($prod_check);
  //  $statecode = $row['statecode'];
    //if ('edit' == $a)
        //$parent_id = $row['parent_id'];
    //合同来源
    // $ctfrom_select   = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
    // //客户级别
    // $ep_level_select = str_replace("value=\"$ep_level\">", "value=\"$ep_level\" selected>", $ep_level_select);
    // //企业性质
    // $nature_select   = str_replace("value=\"$nature\">", "value=\"$nature\" selected>", $nature_select);
    // //注册资本币种
    // $currency_select = str_replace("value=\"$currency\">", "value=\"$currency\" selected>", $currency_select);
   //企业附件列表
    // $archive_total   = $db->get_var("SELECT COUNT(*) FROM sp_attachments WHERE eid = '$eid'");
    // $archive_join    = " LEFT JOIN sp_hr hr ON hr.id = ea.create_uid";
    //$sql             = "SELECT * FROM sp_training WHERE tid = '$tid'";
    //var_dump($tid);
    //var_dump($sql);
    //$query = $db->query($sql);
    //$rt = $db->fetch_array($query);
    //var_dump($rt);
    // while ($rt = $db->fetch_array($query)) {
    //     $training_archives[$rt['tid']] = $rt;
    // }
    //echo "end";
    //var_dump($training_archives);
 }
 // @zbzytech 空数据 为声明数组导致问题
if(!is_array($prod_check)) $prod_check = array();

//国家代码
$statecode_select = str_replace("value=\"$statecode\">", "value=\"$statecode\" selected>", $statecode_select);
echo "endhere";
tpl('training/edit');
 