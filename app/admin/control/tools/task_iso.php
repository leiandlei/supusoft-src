<?php
for ($tid=3; $tid < 1100; $tid++) { 
	$project = $db->getAll("SELECT id,iso,audit_ver,tid FROM sp_project WHERE deleted='0' AND tid=".$tid);
	$iso = $audit_ver = "";
	foreach ($project as $value) {
		$iso       .= $value['iso'].",";
		$audit_ver .= $value['audit_ver'].",";
	}
	// $sql = "UPDATE sp_task SET iso='".$iso."' , audit_ver='".$audit_ver."' WHERE deleted='0' AND id=".$tid;
	$query = $db->query($sql);
}
?>
