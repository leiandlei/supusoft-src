<?php
$a=$_GET['a']; //获取方法
if($a=='Project'){ //检测没有体系和标准版本的项目
	$join='';
	$join.=" LEFT JOIN sp_contract_item cti ON p.cti_id=cti.cti_id";
	$join.=" LEFT JOIN sp_enterprises ep ON p.eid=ep.eid";
	
	
	$sql="select cti.cti_code,ep.ep_name,p.id from sp_project p $join where p.iso='' or p.audit_ver=''";
	$query=mysql_query($sql);
	while($rs=mysql_fetch_assoc($query)){
		$row[]=$rs;
	} 
	tpl( 'Check/project' );
}elseif($a=='Super'){//检测监督维护中监查日期和维护时间不能为空 

	$join='';
	$join.=" LEFT JOIN sp_contract_item cti ON p.cti_id=cti.cti_id";
	$join.=" LEFT JOIN sp_enterprises ep ON p.eid=ep.eid"; 
	$sql="select cti.cti_code,ep.ep_name,p.id,p.pre_date,p.final_date from sp_project p $join where (p.pre_date='0000-00-00' or p.final_date='0000-00-00') AND p.audit_type IN ('1004','1005') AND p.deleted = '0' ";
	$query=mysql_query($sql);
	while($rs=mysql_fetch_assoc($query)){
		$row[]=$rs;
	}
	tpl( 'Check/super' );
}
?>