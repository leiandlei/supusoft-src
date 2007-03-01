<?php
//添加-编辑企业 获取页面请求，查询列表用extract获取get变量，修改与添加用 $_GET 或$_post  
echo "here";
$step=getgp("step");
//var_dump($step);
//var_dump($step);

//LY add读取为0，edit读取为相应eid 
$tid       = (int) getgp('tid');
var_dump($tid);
// //LY 母公司上级eid为0
// $parent_id = (int) getgp('parent_id');
//var_dump($_POST);
if ($_POST) { 
    $new_training = $_POST; 
    //var_dump($new_enterprise);
    //p();
	// $new_enterprise['prod_check']=serialize($new_enterprise['prod_check']);
	// $new_enterprise['parent_id']=$parent_id;
	// $new_enterprise['work_code']=str_replace("-","",trim($new_enterprise['work_code']));
    unset($new_training['step'], $new_training['meta']);
    if ($tid) {
        //LY 如果tid不为0，则为修改已有培训数据，需要修改

        // $u_ctfrom = current_user('ctfrom');                        
        // if ('01000000' != $u_ctfrom) {
        //     unset($new_enterprise['ctfrom']);
        // }
         $af_str = serialize($training->get(array(
             'tid' => $tid
         )));
        
        $training->edit($tid, $new_training);
         $bf_str = $training->get(array(
             'eid' => $eid
        ));
        // 日志： 统一写到控制器
        do {
            log_add($tid, 0, "[说明:客户信息修改]", $af_str, serialize($bf_str));
        } while (false);
    } else {
        //LY 如果eid为0则为新培训信息，将数据插入数据库
        $tid    = $training->add($new_training);
        //var_dump($eid);
        $bf_str = $training->get(array(
            'tid' => $tid
        ));
        // 日志
        do {
            log_add($tid, 0, "[说明:培训信息登记]", NULL, serialize($bf_str));
        } while (false);

    }
    showmsg('success', 'success', "?c=training&a=list");
}
// -------------------------------------------------------------------------
// $enterprises_archives = array();
// $statecode            = '156';
// $nav_title            = '企业登记';
//  if ('edit' == $a or $parent_id) {
//     //$eid = (int)getgp( 'eid' );
//     $where_arr = ($parent_id) ? array(
//         'eid' => $parent_id
//     ) : array(
//         'eid' => $eid
//     );
//     $row       = $enterprise->get($where_arr);
//     extract($row, EXTR_SKIP);
// 	if($parent_id)$work_code="";
// 	$prod_check=unserialize($prod_check);
//   //  $statecode = $row['statecode'];
//     //if ('edit' == $a)
//         //$parent_id = $row['parent_id'];
//     //合同来源
//     $ctfrom_select   = str_replace("value=\"$ctfrom\">", "value=\"$ctfrom\" selected>", $ctfrom_select);
//     //客户级别
//     $ep_level_select = str_replace("value=\"$ep_level\">", "value=\"$ep_level\" selected>", $ep_level_select);
//     //企业性质
//     $nature_select   = str_replace("value=\"$nature\">", "value=\"$nature\" selected>", $nature_select);
//     //注册资本币种
//     $currency_select = str_replace("value=\"$currency\">", "value=\"$currency\" selected>", $currency_select);
//    //企业附件列表
//     $archive_total   = $db->get_var("SELECT COUNT(*) FROM sp_attachments WHERE eid = '$eid'");
//     $archive_join    = " LEFT JOIN sp_hr hr ON hr.id = ea.create_uid";
//     $sql             = "SELECT ea.*,hr.name author FROM sp_attachments ea $archive_join WHERE ea.eid = '$eid' ORDER BY ea.id DESC LIMIT 10";
//     $query           = $db->query($sql);
//     while ($rt = $db->fetch_array($query)) {
//         $rt['ftype_V']                   = f_arctype($rt['ftype']);
//         $enterprises_archives[$rt['id']] = $rt;
//     }
 }
 //@zbzytech 空数据 为声明数组导致问题
 // if(!is_array($prod_check)) $prod_check = array();

//国家代码
// $statecode_select = str_replace("value=\"$statecode\">", "value=\"$statecode\" selected>", $statecode_select);
echo "here";
tpl('training/edit');
 ?>