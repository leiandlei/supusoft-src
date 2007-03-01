<?php
$sql = "select id,code,name from sp_hr WHERE audit_job=1 and (ISNULL(cts_date) or ISNULL(cte_date)) and deleted=0 and is_stop=1";
echo '<pre />';
print_r($sql);exit;
?>
