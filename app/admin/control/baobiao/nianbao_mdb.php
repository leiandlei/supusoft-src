<?php
	$date    = getgp('date');
	$s_date  = $date."-01-01 00:00:00";
	$e_date  = $date."-12-31 23:59:59";
	$banben  = getgp('banben');
	
	if( !getgp('date') ){
		exit( json_encode(array('errorCode'=>1,'errorStr'=>'缺少时间')) );
	}
	require_once ROOT.'/theme/Excel/myexcel.php';
	$join = $select = '';$where =' where 1';
	
	//======================*****//  判断 人员入职时间========
		$personList  = $db->getAll("select * from sp_metas_hr where meta_name='in_date' and meta_value  <='$e_date' and meta_value <> '' and deleted=0");
		foreach($personList as $person)
		{
			$userids[]  = $person['ID'];
		}
		$userids   =  implode(',',array_filter(array_unique($userids))) ;
		if(!empty($userids))
		{
			$wheres  = " and hr.id in(".$userids.")";
		}
	//======================*****//========
	
	$sql     = "select 
				hr.id,hr.name,hr.birthday,hr.sex,hr.technical,hr.audit_job,hr.areacode,hr.card_no,hr.code,hr.unit,hr.job_type,hr.cts_date,hr.cte_date,
				hq.qua_no,hq.iso,hq.qua_type
				from `sp_hr` hr 
				left join sp_hr_qualification hq on hr.id=hq.uid 
				where hq.deleted='0' and hr.deleted='0' and hr.id !=1 and (hr.job_type like '%1004%' or hr.job_type like '%1007%') 

				".$wheres."
				and (hq.iso='A01' or hq.iso='A02' or hq.iso='A03') 
				group by hq.uid,hq.iso";
	$results = $db->getAll($sql);$return = array(array('A'=>'AUDORGID','B'=>'AUDSYSID','C'=>'PERSNAME','D'=>'PERSSEX','E'=>'BIRTHDAY','F'=>'PROFES','G'=>'INCUMB','H'=>'AREAID','I'=>'AUTHENID','J'=>'PERSNO','K'=>'PERSUNIT','L'=>'EDUCATLONAL','M'=>'INSTITUTION','N'=>'SPECIALTY','O'=>'ALLDAYS','P'=>'SYSDAYS','Q'=>'CHARACTAR','R'=>'REGISTER','S'=>'SYSCODE','T'=>'ENGADATE','U'=>'LEAVDATE','V'=>'INFYEAR','W'=>'CHECKMSG'));

	if(!empty($results)){
		$tmpname = 'CM_R199_2_'.time().mt_rand(100,999).'.xlsx';
		if(!is_dir(ROOT.'/app/mdb/temp'))@mkdir(ROOT.'/app/mdb/temp');
		$excel   = new Myexcel($tmpname);
		$excel->setIndex(0);$excel->setTitle('dbo_tblpeople');
		foreach ($results as $value) 
		{
			$data = array();
			$data['0']    = "CNAS-182";//认证机构信息号
			switch ($value['iso']) {//认证领域代码
				case 'A01':
					$data['1']    = 'QMS';
					break;

				case 'A02':
					$data['1']    = 'EMS';
					break;

				case 'A03':
					$data['1']    = 'OHSMS';
					break;

				case 'C02':
					$data['1']    = 'SC';
					break;
				
				default:
					$data['1']    = '';
					break;
			}
			$data['2']    = $value['name'];//姓名
			$data['3']    = ($value['sex']=='1')?'01':'02';//性别 01=男，02=女
			$data['4']    = $value['birthday'];//出生年月
			switch ($value['technical']) {
				case '00':
					$value['technical'] = "00";
					break;
				case '01':
					$value['technical'] = "01";
					break;
				case '02':
					$value['technical'] = "03";
					break;
				case '03':
					$value['technical'] = "02";
					break;
				
				default:
					$value['technical'] = "";
					break;
			}
			$data['5']      = $value['technical'];//职称 填写形式：00-初级职称，01-中级职称，02-副高级职称，03-正高级职称。空=无职称
			switch ($value['audit_job']) {
				case '0':
					$value['audit_job'] = '01';
					break;
				case '1':
					$value['audit_job'] = '02';
					break;
				
				default:
					$value['audit_job'] = '03';
					break;
			}
			$data['6']      = $value['audit_job'];//在职状况 本人在认证机构的在职状况。填写形式：01=兼职，02=专职，03=认证机构管理人员
			$data['7']      = $value['areacode'];//居住地区 本人居住地的代码，见附件《地域代码表》。
			$data['8']      = !empty($value['card_no'])?$value['card_no']:'';//本人的15位或18位身份证号码或军官证号。
			$data['9']      = $value['code'];//本人在认证机构内的人员编号。
			$data['10']     = ($value['audit_job']=='01')?$value['unit']:'中标华信（北京）认证中心有限公司';//本人现在实际的工作单位。
			
			$sql = 'select * from sp_hr_experience where type="j" and add_hr_id='.$value['id'].' and deleted="0" order by department asc';
			$experience = $db->getAll($sql);
			if(!empty($experience))
			{
				$L = $M = $N = array();
				foreach ($experience as $v)
				{
					if ($v['department']=='03') {
						$v['department']='';
					}
					$L[]   = $v['department'];
					$M[]   = $v['area'];
					$N[]   = $v['position'];
				}
				$data['11']   = implode('；',$L);//本人所获得的学历，如多个学历从低到高填写，之间用中文全角分号分隔。填写形式：00-大学专科，01-大学本科，02-硕士学位以上包括硕士学位。空=大学专科以下学历。
				$data['12']   = implode('；',$M);//毕业院校 与所填写的学历相对应的毕业院校。如多个院校则按照第12栏的填写顺序填写，之间用中文全角分号分隔。
				$data['13']   = implode('；',$N);//所学专业 与所填写的学历相对应的专业科目。如多个专业则按照第12栏的填写顺序填写，之间用中文全角分号分隔
			}
			

//			$sql = "select sp_task_audit_team.*,c.is_site from sp_task_audit_team LEFT JOIN sp_project p on p.tid=sp_task_audit_team.tid LEFT JOIN sp_contract c on c.ct_id=p.ct_id where sp_task_audit_team.deleted ='0' and c.deleted ='0' and p.deleted ='0' and uid=".$value['id']." and taskEndDate>='".$s_date."' and taskEndDate<='".$e_date."' GROUP BY tid,uid";
			$sql =("select stt.*,c.is_site 
			from sp_task_audit_team  stt
			LEFT JOIN sp_project p on p.tid=stt.tid 
			LEFT JOIN sp_contract c on c.ct_id=p.ct_id 
			where 
			stt.deleted ='0' 
			and data_for='0'
			and c.deleted ='0' 
			and p.deleted ='0' 
			and stt.uid=".$value['id']." 
			and stt.taskEndDate>='".$s_date."' 
			and stt.taskEndDate<='".$e_date."'
			GROUP BY stt.tid,stt.uid order by taskBeginDate ");
			
			$task_audit_team = $db->getAll($sql);
			$day = 0;
			$timediff = array();
		    if( !empty($task_audit_team) )
		    {
		        foreach ($task_audit_team as $v) 
		        {
		        	if($v['is_site']==0 && $v['audit_type']=='1002')continue;
		            $timediff[] = mkdate($v['taskBeginDate'],$v['taskEndDate']);
		            
		        }
		    }
		
		    $day   =array_sum($timediff);
			$data['14']     = $day;//本年度总审核人日数 单位：天；本年度参加审核的总有效天数。本年度总审核人日数不能超过当年日历天数（366天或365天）；仅为现场天数，不包括文审、路途等时间

//			$sql = "select sp_task_audit_team.*,c.is_site from sp_task_audit_team LEFT JOIN sp_project p on p.tid=sp_task_audit_team.tid LEFT JOIN sp_contract c on c.ct_id=p.ct_id where sp_task_audit_team.deleted ='0' and c.deleted ='0' and p.deleted ='0' and uid=".$value['id']." and sp_task_audit_team.iso='".$value['iso']."' and taskEndDate>='".$s_date."' and taskEndDate<='".$e_date."' GROUP BY tid,uid";	
			$sql =("select stt.*,c.is_site 
			from sp_task_audit_team  stt
			LEFT JOIN sp_project p on p.tid=stt.tid 
			LEFT JOIN sp_contract c on c.ct_id=p.ct_id 
			where 
			stt.deleted ='0' 
			and data_for='0'
			and c.deleted ='0' 
			and p.deleted ='0' 
			and stt.uid=".$value['id']." 
			and stt.taskEndDate>='".$s_date."' 
			and stt.taskEndDate<='".$e_date."'
			and stt.iso='".$value['iso']."'
			GROUP BY stt.tid,stt.uid order by taskBeginDate ");
			$task_audit_team = $db->getAll($sql);
			$day1 = 0;
			$timediff1   = array();
			if( !empty($task_audit_team) )
		    {
		        foreach ($task_audit_team as $v) 
		        {
		        	if($v['is_site']==0 && $v['audit_type']=='1002')continue;
		            $timediff1[] = mkdate($v['taskBeginDate'],$v['taskEndDate']);
		            
		        }
		    }
			$day1  = array_sum($timediff1);
			$data['15']     = $day1;//本年度该领域参加审核人日数 单位：天；本年度参加该领域审核的有效天数。仅为现场天数，不包括文审、路途等时间

			// $value['job_type']   =  explode('|',$value['job_type']);
			// $job_type = array();
			// if( in_array('1004',$value['job_type']) ){
			// 	$job_type[] = '01';
			// }
			// if( in_array('1007',$value['job_type']) ){
			// 	$job_type[] = '02';
			// }
			$sql = "select * from sp_hr_qualification where uid=".$value['id']." and iso='".$value['iso']."'  and deleted='0'";
			$experience1 = $db->getAll($sql);
			if(!empty($experience1))
			{
				$Q= array();
				foreach ($experience1 as $v)
				{
					switch ($v['qua_type']) {
						case '01':
						case '02':
						case '03':
							$Q[]='01';
							break;
						case '04':
							$Q[]='02';
							break;
						default:
							break;
					}
				}
				$data['16'] = implode('；',$Q);//在机构内所负责的工作 填写形式：01-审核人员，02-技术专家。本栏可多选，之间用中文全角分号分隔。
			}
			
			//判断 如果审核员包括技术专家和审核员资质 注册资质显示审核员的资质
			if(count($Q)>1)
			{
				$uidquatype = $db->getOne("select * from sp_hr_qualification where uid=".$value['id']." and iso='".$value['iso']."' and qua_type !=04  and deleted='0'");
				$data['17']    = !empty($uidquatype['qua_no'])?$uidquatype['qua_no']:'无';//注册资格(适用时:注明所注册的专业) 本人在人员注册机构获得的该领域注册资格，如：2005-2-NQXXXXX或2005-1-NEXXXX或2005-0-NSXXXXX等。如果一个人具备多个领域的注册资格，则每行记录只能填一个领域的注册资格。注：如没有注册资格，填“无”。
			}else{
				$data['17']    = !empty($value['qua_no'])?$value['qua_no']:'无';//注册资格(适用时:注明所注册的专业) 本人在人员注册机构获得的该领域注册资格，如：2005-2-NQXXXXX或2005-1-NEXXXX或2005-0-NSXXXXX等。如果一个人具备多个领域的注册资格，则每行记录只能填一个领域的注册资格。注：如没有注册资格，填“无”。
			}
			$audit_codeList = $db->getAll("select * from sp_hr_audit_code where deleted ='0' and uid=".$value['id']." and iso='".$value['iso']."'");
		
			if(!empty($audit_codeList))
			{
				$S = array();
				foreach ($audit_codeList as $v)
				{
					
					if($banben=='1')         //GC版本
					{
						$v['audit_code_2017']  = $db->get_var("select shangbao from sp_settings_audit_code where id='".$v['audit_code_2017']."' and is_stop='0' and deleted=0 ");
						$S[] = $v['audit_code_2017'];						
					}else if($banben=='2'){  //TRC版本
						$v['audit_code']  = $db->get_var("select shangbao from sp_settings_audit_code where id='".$v['audit_code']."' and is_stop='0' and deleted=0 ");
					
						$S[] = $v['audit_code'];						
					}else{
						$v['audit_code_2017']  = $db->get_var("select shangbao from sp_settings_audit_code where id='".$v['audit_code_2017']."' and is_stop='0' and deleted=0 ");
						
						$S[] = $v['audit_code_2017'];						
					}

				}
				$data['18']     = implode('；', array_filter(array_unique($S)));//在机构内所评定的专业 体系审核员需写明该领域的专业小类，产品检查员需写明具体产品类别。各专业小类或产品类别之间用中文全角分号分隔。注：如没有评定专业，填“无”。
			}else{
				$data['18']     = "无";//在机构内所评定的专业 体系审核员需写明该领域的专业小类，产品检查员需写明具体产品类别。各专业小类或产品类别之间用中文全角分号分隔。注：如没有评定专业，填“无”。
			}

			$data['19']    = $value['cts_date'];//聘用日期 本人被该机构聘用时的日期，填写形式：yyyy-mm-dd。
			$data['20']    = $value['cte_date'];//解聘日期 本人被该机构解聘时的日期，即聘用有效期，填写形式：yyyy-mm-dd。
			$data['21']    = getgp('date');//上报信息所属年度 本次上报的信息属于哪个年度，如：2005。
			$data['22']    = "";//检查报告 对以上各栏信息的检查情况记录。
			$return[] = $data;
		}
		$excel->addDataFromArray($return,1);
		$saveName = $excel  -> saveAsFile('app/mdb/temp/',$tmpname);
		echo $saveName['oldName'];
	}
	function timediff( $begin_time, $end_time )
	{
		$begin_time = is_numeric($begin_time)?$begin_time:strtotime($begin_time);
		$end_time   = is_numeric($end_time)?$end_time:strtotime($end_time);
		if ( $begin_time < $end_time ) {
			$starttime = $begin_time;
			$endtime = $end_time;
		} else {
			$starttime = $end_time;
			$endtime = $begin_time;
		}
		$timediff = $endtime - $starttime;
		$days = intval( $timediff / 86400 );
		$remain = $timediff % 86400;
		$hours = intval( $remain / 3600 );
		$remain = $remain % 3600;
		$mins = intval( $remain / 60 );
		$secs = $remain % 60;
		$res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
		return $res;
	}
	