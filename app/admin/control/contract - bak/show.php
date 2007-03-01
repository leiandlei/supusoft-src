<?php

$pd_types=array("","通过","待定","不通过","暂停");
$t_status=array("","待派人","待审批","已审批");
$ct_id = (int)getgp( 'ct_id' );
	if( $ct_id ){
		$projects = array();
		$fields = $join = '';
		$fields .= "p.*,";
		$fields .= "cti.is_turn,cti.total,cti.risk_level";
		$fields .= ",t.tb_date,t.te_date,t.tk_num,t.status as t_status,t.id as tid";
		//$fields .= ",cert.certno,cert.status as c_status,cert.s_date,cert.e_date,cert.id as zsid";


		$join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = p.cti_id";
		$join .= " LEFT JOIN sp_contract ct ON ct.ct_id = p.ct_id";
		$join .= " LEFT JOIN sp_task t ON t.id = p.tid";
		//$join .= " LEFT JOIN sp_certificate cert ON cert.pid=p.id";
		//$join .= " LEFT JOIN sp_task_audit_team tat ON tat.pid=p.id";
		//$join .= " LEFT JOIN sp_task_auditor ta ON ta.id=tat.auditor_id";
		$sql = "SELECT $fields FROM sp_project p $join WHERE p.ct_id = '$ct_id' AND p.deleted=0 and t.deleted=0 order by t.te_date DESC";

		$query = $db->query( $sql );
		while( $rt = $db->fetch_array( $query ) ){
			$rt['audit_type_V'] = f_audit_type( $rt['audit_type'] );
			$rt['audit_ver_V'] = f_audit_ver( $rt['audit_ver'] );
			/*
			$marks = explode( ',', $rt['mark'] );
			$marks2 = array();
			foreach( $marks as $mark ){
				$marks2[] = f_mark( $mark );
			}
			*/
			$rt['is_site_V'] = ($rt['is_iste'] == 'y')?'是':'否';
			$rt['is_turn_V'] = ($rt['is_turn'])?'是':'否';
			//$rt['mark_V'] = f_mark( $rt[mark] );
			$rt['tb_date'] = mysql2date( 'Y-m-d', $rt['tb_date'] );
			$rt['te_date'] = mysql2date( 'Y-m-d', $rt['te_date'] );
			$rt['pd_type']=$pd_types[$rt['pd_type']];
			$rt['t_status']=$t_status[$rt['t_status']];
			$rt['risk_level']=read_cache("risk_level",$rt[risk_level]);
			$rt['final_date']=="0000-00-00" && $rt['final_date']="";
			if($rt[tid]){
				$res=$db->get_results("select * from sp_task_audit_team where 1 AND tid='$rt[tid]' and deleted=0");
				$rt['leader']=$rt['auditor']="";
				$auditor=array();
				foreach($res as $val){
					if($val['role']=='1001')
						$rt['leader']=$val['name'];
					else
						$auditor[$rt[uid]]=$val['name'];

				}
				$rt['auditor']=join(" ",$auditor);
				unset($res,$val,$_res);
			}
			$cert_info=$db->get_row("SELECT cert.certno,cert.status as c_status,cert.s_date,cert.e_date,cert.id as zsid FROM `sp_certificate` cert WHERE `cti_id` = '$rt[cti_id]'  order by id desc");
			if($cert_info){
				$rt=$rt+$cert_info;
			}
			
			unset($cert_info);
			if($rt[zsid])
				$rt[change]=$db->get_results("select cg_af,cg_bf,note from sp_certificate_change where 1 AND zsid='$rt[zsid]'");
			//p($rt[change]);
			$rt=chk_arr($rt);
			$projects[$rt['id']] = $rt;$rt=NULL;
		}
	}
	tpl( 'contract/show' );