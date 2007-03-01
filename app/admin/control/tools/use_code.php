<?php

// $isoList=array('0'=> 'A01' ,'1'=> 'A02' ,'2'=> 'A03');
// foreach($isoList as $iso_key=> $iso)
// {

// 	//修改use_code
// 	 $hrcodeList   = $db->getAll("select * from sp_project where iso='".$iso."'");
// 	 foreach($hrcodeList as $hrcode)
// 	 {
// 	 	if(!empty($hrcode['pd_audit_code']))
// 	 	{
// 	 		$hrcodes  = array_filter(explode('；', $hrcode['pd_audit_code']));	
// 	 		$params   = array();
// 	 		if(!empty($hrcodes)&&count($hrcodes)>1)
// 	 		{
// 	 			$codeids = '';
// 	 			foreach($hrcodes as $code)
// 	 			{
// 	 				$codeid    = $db->get_var("select code from sp_settings_audit_code where shangbao ='".$code."'  and iso='".$iso."'" ); 
// 	 				$codeids  .= $codeid.'；';
// 	 			}
// 	 			$params['pd_use_code'] = $codeids;
// 	 		}else{
// 	 			$codeid   = $db->get_var("select code from sp_settings_audit_code where shangbao ='".$hrcode['pd_audit_code']."' and iso='".$iso."'" ); 
// 	 			$params['pd_use_code'] = $codeid;
// 	 		}
// 	 		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
// 	 	}
// 	 }
// }

//  echo "评定修改后use_code修改成功";
//  exit;
 $isoList=array('0'=> 'A01' ,'1'=> 'A02' ,'2'=> 'A03');
foreach($isoList as $iso_key=> $iso)
{

	//修改use_code
	 $hrcodeList   = $db->getAll("select * from sp_project where iso='".$iso."'");
	 foreach($hrcodeList as $hrcode)
	 {
	 	if(!empty($hrcode['audit_code']))
	 	{
	 		$hrcodes  = array_filter(explode('；', $hrcode['audit_code']));	
	 		$params   = array();
	 		if(!empty($hrcodes)&&count($hrcodes)>1)
	 		{
	 			$codeids = '';
	 			foreach($hrcodes as $code)
	 			{
	 				$codeid    = $db->get_var("select code from sp_settings_audit_code where shangbao ='".$code."'  and iso='".$iso."'" ); 
	 				$codeids  .= $codeid.'；';
	 			}
	 			$params['use_code'] = $codeids;
	 		}else{
	 			$codeid   = $db->get_var("select code from sp_settings_audit_code where shangbao ='".$hrcode['audit_code']."' and iso='".$iso."'" ); 
	 			$params['use_code'] = $codeid;
	 		}
	 		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	 	}
	 }
}

 echo "use_code修改成功";
 exit;
?>
