<?php
$sql  = "select * from sp_certificate_change where status=1 and deleted=0 order by pass_date asc";
$data = $db->getAll($sql);
foreach($data as $change)
{
	switch($change['cg_meta'])
	{
		case '97_02'://暂停
			$db->update('certificate',array('status'=>'01'),array('id'=>$change['zsid']));
			break;
		default:continue;break;
	}
}
?>

