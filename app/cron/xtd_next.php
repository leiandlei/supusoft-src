<?php
/* 
* @Author: anchen
* @Date:   2017-06-12 15:56:46
* @Last Modified by:   anchen
* @Last Modified time: 2017-06-12 17:07:26
*/
//查询数据库中为待派组的信息列表
  $date     = date('Y-m-d H:i:s');
  $xtd_list =$db->getAll("select * from sp_partner_coordinator where status='04' and ejdshsj_end < '".$date."' and deleted=0 ");
  foreach ($xtd_list as $key => $value)
  {
  	    $id               = $value['id'];
  		$params['status'] = '05';
  		$db -> update( 'partner_coordinator',$params,array('id'=>$id),false );
  }
?>
.