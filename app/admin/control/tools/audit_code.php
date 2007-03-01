<?php
//修改
$hrcodeList   = $db->getAll("select * from sp_hr_audit_code where iso='A01' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode(',', $hrcode['audit_code_2017']));
		
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A01'" ); 
				$codeids  .= $codeid.',';
			}
			$params['audit_code_2017'] = $codeids;
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1' and is_stop='0' and iso='A01'" ); 
			$params['audit_code_2017'] = $codeid;
		}	
		$db -> update( 'hr_audit_code',$params,array('id'=>$hrcode['id']),false );
	}
}
echo "A01人员代码修改成功";
echo "<pre />";
//修改
$hrcodeList   = $db->getAll("select * from sp_hr_audit_code where iso='A02' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode(',', $hrcode['audit_code_2017']));
		
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A02'" ); 
				$codeids  .= $codeid.',';
			}
			$params['audit_code_2017'] = $codeids;
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1' and is_stop='0' and iso='A02'" ); 
			$params['audit_code_2017'] = $codeid;
		}	
		$db -> update( 'hr_audit_code',$params,array('id'=>$hrcode['id']),false );
	}
}
echo "A02人员代码修改成功";
echo "<pre />";
// // exit;
// // //修改
$hrcodeList   = $db->getAll("select * from sp_hr_audit_code where iso='A03' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode(',', $hrcode['audit_code_2017']));
		
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A03'" ); 
				$codeids  .= $codeid.',';
			}
			$params['audit_code_2017'] = $codeids;
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1' and is_stop='0' and iso='A03'" ); 
			$params['audit_code_2017'] = $codeid;
		}	
		$db -> update( 'hr_audit_code',$params,array('id'=>$hrcode['id']),false );
	}
}
echo "A03人员代码修改成功";
echo "<pre />";
// exit;

























$hrcodeList   = $db->getAll("select * from sp_project where iso='A01' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['pd_audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['pd_audit_code_2017']));
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A01'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['pd_audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['pd_audit_code_2017']."' and banben='1' and is_stop='0'  and iso='A01'" ); 
			
			$params['pd_audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}	
	
}
echo "A01sp_project修改成功";
echo "<pre />";

$hrcodeList   = $db->getAll("select * from sp_project where iso='A02' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['pd_audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['pd_audit_code_2017']));
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A02'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['pd_audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['pd_audit_code_2017']."' and banben='1' and is_stop='0' and iso='A02'" ); 
			
			$params['pd_audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}	
	
}
echo "A02sp_project修改成功";
echo "<pre />";
// // exit;
$hrcodeList   = $db->getAll("select * from sp_project where iso='A03' ");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['pd_audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['pd_audit_code_2017']));
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1'  and is_stop='0' and iso='A03'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['pd_audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['pd_audit_code_2017']."' and banben='1'  and is_stop='0'  and iso='A03'" ); 
			
			$params['pd_audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}	
	
}
echo "A03sp_project修改成功";
echo "<pre />";
// exit;



























// 
$hrcodeList   = $db->getAll("select * from sp_project  where iso='A01'");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(array_unique(explode('；', $hrcode['audit_code_2017'])));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1'  and is_stop='0' and iso='A01'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1' and is_stop='0'  and iso='A01'" ); 			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}
}
echo "A01audit_code_2017chenggong";
echo "<pre />";
// // exit;


$hrcodeList   = $db->getAll("select * from sp_project  where iso='A02'");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(array_unique(explode('；', $hrcode['audit_code_2017'])));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A02'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1' and is_stop='0' and iso='A02'" ); 
			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}
}
echo "A02audit_code_2017chenggong";
echo "<pre />";
// // exit;

$hrcodeList   = $db->getAll("select * from sp_project  where iso='A03'");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(array_unique(explode('；', $hrcode['audit_code_2017'])));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1'  and is_stop='0' and iso='A03'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and banben='1'  and is_stop='0' and iso='A03'" ); 
			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'project',$params,array('id'=>$hrcode['id']),false );
	}
	
	
}
echo "A03audit_code_2017chenggong";
echo "<pre />";
























$hrcodeList   = $db->getAll("select * from sp_contract_item where iso='A01'");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['audit_code_2017']));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and is_stop='0' and banben='1' and iso='A01'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and is_stop='0' and banben='1' and iso='A01'" ); 
			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'contract_item',$params,array('cti_id'=>$hrcode['cti_id']),false );
	}
	
	
}
echo "A01sp_contract_item-》audit_code_2017字段修改成功";
echo "<pre />";
//
$hrcodeList   = $db->getAll("select * from sp_contract_item where iso='A02'");
foreach($hrcodeList as $hrcode)
{
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['audit_code_2017']));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A02'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and is_stop='0' and banben='1' and iso='A02'" ); 
			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'contract_item',$params,array('cti_id'=>$hrcode['cti_id']),false );
	}
	
	
}
echo "A02sp_contract_item-》audit_code_2017字段修改成功";
echo "<pre />";
// 
$hrcodeList   = $db->getAll("select * from sp_contract_item where iso='A03'");
foreach($hrcodeList as $hrcode)
{
	
	
	if(!empty($hrcode['audit_code_2017']))
	{
		$hrcodes  = array_filter(explode('；', $hrcode['audit_code_2017']));
	
		$params   = array();
		if(!empty($hrcodes)&&count($hrcodes)>1)
		{
			$codeids = '';
			foreach($hrcodes as $code)
			{
				$codeid    = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$code."' and banben='1' and is_stop='0' and iso='A03'" ); 
				
				$codeids  .= $codeid.'；';
			}
			$params['audit_code_2017'] = $codeids;
			
		}else{
			$codeid   = $db->get_var("select id from sp_settings_audit_code where shangbao ='".$hrcode['audit_code_2017']."' and is_stop='0' and banben='1' and iso='A03'" ); 
			
			$params['audit_code_2017'] = $codeid;
		}
	
		$db -> update( 'contract_item',$params,array('cti_id'=>$hrcode['cti_id']),false );
	}
	
	
}
echo "A03sp_contract_item-》audit_code_2017字段修改成功";
echo "<pre />";
exit;
//require_once ROOT . '/theme/Excel/myexcel1.php'; //框架引导文件
//$execl  = new Myexcel('2017.xlsx');
//$execl  -> setIndex(0);
//$data   = $execl->readData('A2',$execl->getMaxCellName().$execl->getMaxRowNumber());
//foreach($data as $item)
//{
//
//		$params = array();
//		$params['iso']        		 = $item['A'];
//		$params['mark']      		 = $item['G'];
//		$params['dalei']     		 = substr($item['C'], 0,2);
//		$params['zhonglei']  	 	 = substr($item['C'], 3,2);
//		$params['xiaolei']   	 	 = substr($item['C'], 6,2);
//		$params['msg']       	 	 = $item['E'];
//		$params['code']       	 	 = $item['B'];
//		$params['industry']     	 = $item['D'];
//		$params['risk_level']        = $item['F'];
//		$params['banben']            = "2";
//		$params['shangbao']     	 = $item['C'];
//		$params['create_uid']        = '1';
//		$params['create_user']       = "管理员";
//		$params['create_date']       = date('Y-m-d H:i:m');
//
//		$db -> insert( 'settings_audit_code',$params,false );
//}
//echo "成功";
//exit;
//添加
//require_once ROOT . '/theme/Excel/myexcel1.php'; //框架引导文件
//$execl  = new Myexcel('2017.xlsx');
//$execl  -> setIndex(0);
//$data   = $execl->readData('A2',$execl->getMaxCellName().$execl->getMaxRowNumber());
//
//foreach($data as $code)
//{
//	$daima = !empty($code['A'])?$code['A']:(!empty($code['B'])?$code['B']:(!empty($code['C'])?$code['C']:''));if(empty($daima))continue;
//	$daima = explode('.', $daima);foreach($daima as $key=>$value)if(strlen($value)=='1')$daima[$key] = '0'.$value;
//	switch(count($daima))
//	{
//		case '1':
//			$A01[$daima[0]]['mag'] = $code['D'];
//			$A02[$daima[0]]['mag'] = $code['D'];
//			$A03[$daima[0]]['mag'] = $code['D'];
//			break;
//		case '2':
//			$A01[$daima[0]]['code'][$daima[1]]['mag'] = $code['D'];
//			$A02[$daima[0]]['code'][$daima[1]]['mag'] = $code['D'];
//			$A03[$daima[0]]['code'][$daima[1]]['mag'] = $code['D'];
//			break;
//		case '3':
//			$A01[$daima[0]]['code'][$daima[1]]['code'][$daima[2]]['mag'] = $code['D'];
//			$A02[$daima[0]]['code'][$daima[1]]['code'][$daima[2]]['mag'] = $code['D'];
//			$A03[$daima[0]]['code'][$daima[1]]['code'][$daima[2]]['mag'] = $code['D'];
//			break;
//		default:
//			continue;
//			break;
//	}
//}
//$isoList=array('A01'=> $A01 ,'A02'=> $A02 ,'A03'=> $A03);
//foreach($isoList as $iso_key=> $iso)
//{
//
//	foreach($iso as $dalei_key => $dalei)
//	{
//		
//		foreach($dalei['code'] as $zhonglei_key =>$zhonglei)
//		{
//	
//			foreach($zhonglei['code'] as $xiaolei_key =>$xiaolei)
//			{
//				$params = array();
//				$params['iso']        		 = $iso_key;
//				$params['mark']      		 = '01';
//				$params['dalei']     		 = $dalei_key;
//				$params['zhonglei']  	 	 = $zhonglei_key;
//				$params['xiaolei']   	 	 = $xiaolei_key;
//				$params['msg']       	 	 = $xiaolei['mag'];
//				$params['industry']     	 = $dalei['mag'];
//				$params['risk_level']        = '03';
//				$params['banben']            = "2";
//				$params['shangbao']     	 = $dalei_key.'.'.$zhonglei_key.'.'.$xiaolei_key;
//				$params['create_uid']        = '1';
//				$params['create_user']       = "管理员";
//				$params['create_date']       = date('Y-m-d H:i:m');
//
//				$db -> insert( 'settings_audit_code',$params,false );
//			}	
//		}	
//	}
//}
//echo "成功";exit;


?>
