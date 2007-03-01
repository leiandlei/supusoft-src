<?php
include_once ROOT.'/framework/PHPZip.class.php';
include_once ROOT.'/framework/fileLock.class.php';
$archive  = new PHPZip();
$filename = date("YmdHi").'.zip';
switch($a)
{
	case "auditor":
		$ctid   = getgp('ct_id');
		$ctid_arr=explode('、',$ctid);
		$ct_id_dir=implode(',',$ctid_arr);
		$ct_id="(".implode(',',$ctid_arr).")";//修改成数组查询

		$tid = getgp('tid');
		$banben  = getgp('banben');//设置下载版本 1为旧代码版本 2为新代码版本
		$tmpPath = 'data/runtime/down/auditor/ctid'.$ct_id_dir.'/tid'.$tid.'/';!is_dir($tmpPath)&&mkdir($tmpPath,0777,true);
		$lock    = new fileLock($filename,$tmpPath);
		$lock    -> lock();
		if( !file_exists($tmpPath.$filename) )
		{
			$fileInfo          = array();
			$fileInfo['ct_id'] = $ct_id;
			$fileInfo['tid']   = $tid;
		
			$sql    = "SELECT * FROM sp_task_audit_team WHERE tid='$tid' ";
			$audit  = $db->getALL($sql);
			$sql    = "SELECT iso FROM sp_contract_item WHERE ct_id in $ct_id ";
			$iso    = $db->getALL($sql);
		
			$qua_type      = array();
			$audit_type    = $audit['0'];
			foreach ($audit as $value) {
				$qua_type[]= $value['qua_type'];
			}
		
			$list = array();
			$num=1;
		
			//01体系文件
			$sql   = "SELECT * FROM sp_attachments where 1 and ct_id in  $ct_id and ftype='1003' and deleted='0' order by tid desc";
			$total = $db->get_row($sql);
			if(empty($total)){
                $sql   = "SELECT eid FROM sp_contract where 1 and ct_id in  $ct_id  and deleted='0' ";
                $eids = $db->getOne($sql);
                foreach ($eids as $val_eid){
                    $sql="SELECT * FROM sp_attachments where 1 and eid =  $val_eid and ftype='1003' and deleted='0' ";
				}
                $total = $db->getAll($sql);
                foreach ($total as $value) {
					$list[$num]['name'] = "01体系文件/".$value['name'];
					$list[$num]['url']  = "uploads/ep/".$value['filename'];
					$num=$num+1;
				}
			}else{
				$list[$num]['name'] = "01体系文件/".$total['name'];
				$list[$num]['url']  = "uploads/ep/".$total['filename'];
				$num=$num+1;
			}
			// echo "<pre />";
			// print_r($list);exit;
		
			//02企业资质
			$sql="SELECT * FROM sp_attachments where 1 and ct_id in $ct_id and ftype='1002' and deleted='0' ";
			$total=$db->getAll($sql);
            if(!$total){
                $sql   = "SELECT eid FROM sp_contract where 1 and ct_id in  $ct_id  and deleted='0' ";
                $eids = $db->getOne($sql);
                foreach ($eids as $val_eid){
                    $sql="SELECT * FROM sp_attachments where 1 and eid =  $val_eid and ftype='1002' and deleted='0' ";
                }
                $total = $db->getAll($sql);
            }
			foreach ($total as $value) {
				$list[$num]['name'] = "02企业资质/".$value['name'];
				$list[$num]['url']  = "uploads/ep/".$value['filename'];
				$num=$num+1;
			}
			/**03审核资料**/
			foreach( $iso as $v){
				switch ($v['iso']){
					case 'A01':
					    $wjm='SH-002-1';
					    break;
					case 'A02':
					    $wjm='SH-002-2';
					    break;
					case 'A03':
					    $wjm='SH-002-3';
					    break;
				}
				$str_wjm.= $wjm.'|';
			}
			$str_wjm= substr($str_wjm,0,strlen($str_wjm)-1);
			$is_site_arr =$db->getAll("SELECT is_site FROM sp_contract where deleted=0 and ct_id in $ct_id order by create_date desc");
			foreach ($is_site_arr as $val_site){
				$is_site=$val_site['is_site'];
			}
			$arr_type_1=array(1003,1007);
			$arr_type_2=array(1004,1005,1009);
			if ($audit_type['audit_type']=='1002') {
				if($is_site==1){
                    $params = "SH-001|SH-003|SH-004|SH-005|SH-006|SH-007|SH-009|SH-010|SH-011|SH-012|SH-016|SH-019";
				}else {
                    $params="SH-001|SH-003|SH-005|SH-012|SH-016|SH-019";
                }
			}elseif (in_array($audit_type['audit_type'],$arr_type_1)) {
				$params="SH-001|".$str_wjm."|SH-004|SH-005|SH-006|SH-007|SH-008|SH-009|SH-010|SH-011|SH-012|SH-013|SH-014|SH-015|SH-017|SH-018|SH-019|SH-020|SH-021|SH-022|SH-023";
			}elseif (in_array($audit_type['audit_type'],$arr_type_2)) {
				$params="SH-001|".$str_wjm."|SH-003|SH-004|SH-005|SH-006|SH-007|SH-008|SH-009|SH-010|SH-011|SH-012|SH-013|SH-014|SH-015|SH-017|SH-018|SH-019|SH-020|SH-021|SH-024";
			}
		
			$newa = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'index'));
		
			 $url  = 'http://'.$_SERVER['SERVER_NAME'].$newa.'?c=doc';
			
			$file = $urlArray = array();
			$valueArray = explode('|', $params);

			$dates =date('YmdHis');
			foreach ($valueArray as $str) {
		
			    $urlArray[] = $url.'&a='.$str.'&downs=1&ct_id='.$ctid.'&tid='.$tid.'&dates='.$dates.'&banben='.$banben;
			}

			foreach ($urlArray as $value) {
			    $file[] = getUrlContent($value);

			}

			$file_list   = $archive -> visitFile(ROOT.'/data/downs'.'/'.$dates);
			foreach ($file_list as $key => $value) {
				$file_name   = substr($value,strrpos($value,'downs')+6);
				$file_name   = iconv( 'gbk', 'UTF-8', $file_name );
				$file_name_1 = substr($file_name,0,strrpos($file_name,'.'));
				$file_name_2 = substr($file_name_1,15);
				$list[$num]['name'] = "03审核资料/".$file_name_2;
				$list[$num]['url']  = "data/downs/".$file_name;
				$num=$num+1;
			}
		
			$list[$num]['name'] = "03审核资料/申请认证组织、获证组织告知书";
			$list[$num]['url']  = "files/audit_file/gaozhishu.pdf";
			$num=$num+1;
		
			if (in_array("03",$qua_type)) {
				$list[$num]['name'] = "03审核资料/CCAA审核经历记录表";
				$list[$num]['url']  = "files/audit_file/jinglijilu.doc";
				$num=$num+1;
			}
			/**03审核资料**/
//			echo "<pre/>";
//			var_dump($list);
//			exit;
			//04其他证明
			$list[$num]['name'] = "04其他证明/1.交通票据/1.交通票据";
			$list[$num]['url']  = "files/audit_file/1.txt";
			$num=$num+1;
			$list[$num]['name'] = "04其他证明/2.企业资质-审核员签字版/2.企业资质-审核员签字版";
			$list[$num]['url']  = "files/audit_file/1.txt";
			$num=$num+1;
		
			//其他文件
			$list[$num]['name'] = "00审核员工作守则";
			$list[$num]['url']  = "files/audit_file/00.doc";
			$num=$num+1;
			// $list[$num]['name'] = "00-1审核档案最低标准要求";
			// $list[$num]['url']  = "files/audit_file/00-1.docx";
			$list[$num]['name'] = "00-1中标华信审核案卷交档规范(1.3版)";
			$list[$num]['url']  = "files/audit_file/00-1.pdf";			
			$num=$num+1;
			$list[$num]['name'] = "00-2审核一致性要点提示";
			$list[$num]['url']  = "files/audit_file/00-2.doc";
			$num=$num+1;


			//企业过往审核资料
			$sql="SELECT * FROM sp_attachments where 1 and ct_id in $ct_id and ftype='5003' and deleted='0' ";
			$total=$db->getAll($sql);	
			foreach ($total as $value) {
				$list[$num]['name'] = $value['name'];
				$list[$num]['url']  = "uploads/ep/".$value['filename'];
				$num=$num+1;
			}
			$iscreate = $archive -> create_zip($list,$tmpPath.$filename,true);
			$archive  -> deldir('data/downs/');
		}
		$lock    -> unlock();
		break;
	case "contract":
		$ctid   = getgp('ct_id');
		$ctid_arr=explode('、',$ctid);
		$ct_id="(".implode(',',$ctid_arr).")";//修改成数组查询

		$eid = getgp('eid');
		$tmpPath = 'data/runtime/down/contract/eid'.$eid.'/ctid'.$ct_id.'/';!is_dir($tmpPath)&&mkdir($tmpPath,0777,true);
		$lock    = new fileLock($filename,$tmpPath);
		$lock    -> lock();
		echo "1";exit;
		if( !file_exists($tmpPath.$filename) )
		{
			$fileInfo = array();
			$fileInfo['ct_id'] = $ct_id;
			$fileInfo['eid'] = $eid;
			$sql="SELECT iso FROM sp_contract_item WHERE ct_id in $ct_id ";
			$iso=$db->getALL($sql);
		
			$list = array();
			$num=1;
		
			/**03审核资料**/
			foreach( $iso as $v){
				switch ($v['iso']){
					case 'A01':
					    $wjm='HB001-1|HB002-1';
					    break;
					case 'A02':
					    $wjm='HB001-2|HB002-2';
					    break;
					case 'A03':
					    $wjm='HB001-3|HB002-3';
					    break;
				}
				$str_wjm.= $wjm.'|';
			}
			$str_wjm= substr($str_wjm,0,strlen($str_wjm)-1);
		
			$params=$str_wjm."|HB-008";
		
			$newa = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'index'));
			$url = 'http://'.$_SERVER['SERVER_NAME'].$newa.'?c=doc';
			$file = $urlArray = array();
			$dates =date('YmdHis');
			$valueArray = explode('|', $params);
			foreach ($valueArray as $str) {
			    $urlArray[] = $url.'&a='.$str.'&downs=1&ct_id='.$ctid.'&eid='.$eid.'&dates='.$dates.'&banben='.$banben;
			}
		
			foreach ($urlArray as $value) {
			    $file[] = getUrlContent($value);
			}
			$file_list  = $archive -> visitFile(ROOT.'/data/downs'.'/'.$dates);

			foreach ($file_list as $key => $value) {
				$file_name          = substr($value,strrpos($value,'downs')+6);
				$file_name          = iconv( 'gbk', 'UTF-8', $file_name );
				$file_name_1        = substr($file_name,0,strrpos($file_name,'.'));
				$list[$num]['name'] = $file_name_1;
				$list[$num]['url']  = "data/downs/".$file_name;
				$num=$num+1;
			}
			
			$iscreate =  $archive -> create_zip($list,$tmpPath.$filename,true);
			$archive  -> deldir('data/downs/');
		}
		$lock    -> unlock();
		break;
	default:
		break;
}
echo $iscreate;exit;
?>
