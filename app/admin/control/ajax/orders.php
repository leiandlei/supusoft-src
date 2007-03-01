<?php
/* 
* @Author: anchen
* @Date:   2017-06-07 17:21:17
* @Last Modified by:   mantou
* @Last Modified time: 2017-06-27 11:32:36
*/
// $code = getgp('code');
// if($code){
// 	$sql  =$db->getAll("select * from sp_partner_coordinator where code='".$code."'");
// 	$i  = 0;
// 	foreach ($sql as $value) 
// 	{
// 		$i++;
// 		$id               = $value['id'];
// 		$orders           = $i;
// 		$a = $i."<br />";
// 		echo $a;
// 		$uid_list  = "UPDATE sp_partner_coordinator SET orders = '".$orders."' WHERE id = '".$id."' ";
// 		$query     = mysql_query($uid_list);
// 	}	
// }
   $contract =$db->getAll("select * from sp_contract_item where deleted = 0");
   foreach ($contract as $value) {

   		   $id   = $value['cti_id'];
   		   $db   = "UPDATE sp_contract_item SET new_scope = '".$value['scope']."' WHERE cti_id = '".$id."'";
   		   $query= mysql_query($db);
          
   }
 echo "成功";

?>
