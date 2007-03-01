<?php
/* 
* @Name: 批量生成评定数据
* @Author: lmff08
* @Date:   2017-11-06 16:15:37
* @Last Modified by:   anchen
* @Last Modified time: 2017-11-06 16:16:51
*/
$sql = "select id,mark,audit_code,exc_clauses_new,scope from sp_project where 1";
$pd  = $db->getAll($sql);
// foreach ($pd as $value) {
// 	$db->update('project', array(
// 	    'pd_mark' => $value['mark'],
// 	    'pd_audit_code' => $value['audit_code'],
// 	    'pd_exc_clauses' => $value['exc_clauses_new'],
// 	    'pd_scope' => $value['scope']
// 	), array(
// 	    'id' => $value['id']
// 	));
// }
?>
