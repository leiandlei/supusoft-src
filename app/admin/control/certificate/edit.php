<?php
/*
 *证书登记
 */
 /**
 * 产生CNAS证书编号 流水号部分为*号
 * 06913Q12270R0S
 * 03213S20123R1M
 * 03214E2026R0M
 * 03214Q20025R0M
 * 1-3位069机构号 4-5位13年份 6-7位Q1版本号 891011位2270流水号 后三位R是固定值0复评次数S体系人数规模(sml小中(50<中的<=1000人)大)
 * @param string $iso 体系
 * @param int $renum 复评次数
 * @param int $total 体系人数
 * @param int $code   流水号
 * @return string
 */

function chns_auro_no($iso, $renum, $total, $code)
{
    $iso2ver = array(
        'A01' => 'Q1',
        'A02' => 'E1',
        'A03' => 'S1',
        'A12' => 'En'
    );
    $no      = '';
    $no .= substr(get_option('zdep_id'), -3);
	$no .="-";
    $no .= date('y');
	$no .="-";
    $no .= $iso2ver[$iso];
	$no .="-";
	if ($code < 999 && $code >= 99)
        $no .= "0" . ++$code;
    elseif ($code < 99 && $code >= 9)
        $no .= "00" . ++$code;
    elseif ($code < 9)
        $no .= "000" . ++$code;
    else
        $no .= ++$code;
	$no .="-";
    $no .= 'R';
    $no .= $renum;
 	$no .="-";
   if ($total <= 50) {
        $no .= 'S';
    } elseif ($total > 1000) {
        $no .= 'L';
    } else {
        $no .= 'M';
    }
    return $no;
}

//换证原因
	if($pid=getgp("pid")){
		$p_info=$db->find_one("project",array("id"=>$pid));
		$e_info=$db->find_one("enterprises",array("eid"=>$p_info[eid]));
		$zs_info=$db->find_one("certificate",array("cti_id"=>$p_info['cti_id'],"eid"=>$p_info['eid'],"status"=>'01',"deleted"=>0));
	}
	$f=0;
	if($zsid)
		$f=1;
	else{
		!$zs_info && $f=1;
	}

	if($f){//添加和修改
		if($zsid){
			$row = $certificate->get($zsid);

			extract(chk_arr($row), EXTR_OVERWRITE );
			

		}else{
			$new_cert = array(
							'eid'			=> $p_info['eid'],	//企业id
							'ct_id'			=> $p_info['ct_id'],	//合同id
							'cti_id'		=> $p_info['cti_id'],	//合同项目id
							'iso'			=> $p_info['iso'],	//体系
							'audit_ver'		=> $p_info['audit_ver'],	//体系版本
							'mark'			=> $p_info['pd_mark'],	//标志
							'audit_code'	=> $p_info['pd_audit_code'],	//审核代码
							'cert_name'		=> $e_info['ep_name'],
							'cert_name_e'	=> $e_info['ep_name_e'],
							'cert_scope' 	=> $p_info['pd_scope'],
							'cert_scope_e' 	=> $p_info['scope_e'],
						);

			extract($new_cert, EXTR_OVERWRITE );
		}


		//@HBJ 2013-9-18 如果初始证书编号是空的并且是CNAS，生成默认的证书编号
		if(empty($certno)) {
			//获取流水号
//			$sql_liushui    = "select certno from sp_certificate where iso='".$iso."' order by certno desc limit 1";
//			$certno_liushui = '';
//			$certno_liushui = $db->get_var($sql_liushui);
//			$certno_liushui = empty($certno_liushui)?'':substr($certno_liushui,6,5);
//			$certno  = '199'.substr(date('Y'),2);//固定
//			
//			$certno .= (substr($arr_audit_iso[$iso],0,1)=='O')?'S':substr($arr_audit_iso[$iso],0,1);//体系
//			
//			$certno .= empty($certno_liushui)?'00001':(str_repeat('0',5-strlen($certno_liushui+1)).($certno_liushui+1));//流水
//			
//			$certno .= 'R'.$certno_ctiInfo['renum'];
//			$certno .= ($certno_ctiInfo['total']<51)?'S':(($certno_ctiInfo['total']>50&&$certno_ctiInfo['total']<1001)?'M':'L');
			//判断新一年的证书编号重新开始
			//获取复评次数和总人数
			$sql_ctiInfo    = "select total,renum from sp_contract_item where cti_id=".$cti_id;
			$certno_ctiInfo = array();
			$certno_ctiInfo = $db->getOne($sql_ctiInfo);
			$sql_liushui    = $db->get_var("select certno from sp_certificate where iso='".$iso."' order by certno desc limit 1");
			
			if(substr($sql_liushui,3,2) != substr(date('Y'),2))//新的一年证书码不存在
			{
				$liushui = '00001';
			}else{
				$liushui = (str_repeat('0',5-strlen(substr($sql_liushui,6,5)+1)).(substr($sql_liushui,6,5)+1));
			}
			$certno  = '199'.substr(date('Y'),2);//固定
			$certno .= (substr($arr_audit_iso[$iso],0,1)=='O')?'S':substr($arr_audit_iso[$iso],0,1);//体系
			$certno .= $liushui;
			$certno .= 'R'.$certno_ctiInfo['renum'];
			$certno .= ($certno_ctiInfo['total']<51)?'S':(($certno_ctiInfo['total']>50&&$certno_ctiInfo['total']<1001)?'M':'L');
//			print_r($certno);exit;
		}
		
		//取总经理审批时间
		if($s_date=='0000-00-00' || !$s_date){
			$pd_info = $db->get_row("select sp_date from sp_project where id='$pid' ");

			$s_date  = $pd_info['sp_date'];
			$e_date  = get_addday($pd_info['sp_date'], 36,-1);
			$first_date = '';
		}
		$e_ct_id = $ct_id;
		//$tid = $db->get_var("select tid from sp_assess where id='$pd_id' ");
	
		//子证书的父id
		$parent_id = getgp('parent_id');
		if( $parent_id ){
			$where = " AND eid = '$parent_id'";
			$main_certno= $certno;
			$certno     = '';
			$old_eid    = $eid;
			$new_eid    = $parent_id;
			$is_check   = 'e';
			$cert_addr  =NULL;
			$cert_addr_e=NULL;
			$cert_scope =$db->get_var("SELECT scope FROM `sp_contract_num` WHERE `eid` = '$parent_id' and ct_id='$ct_id' and type='1'");

		} else {
			$old_eid = $new_eid = $eid;
			$where = " AND eid = '$eid'";
		}

		$en_info = $db->get_row("select * from sp_enterprises where 1 $where");
		if(!$cert_post      || $parent_id )$cert_post     =$en_info['ep_addrcode'];
		if(!$zc_addr        || $parent_id )$zc_addr       =$en_info['ep_addr'];
		if(!$zc_addr_e      || $parent_id )$zc_addr_e     =$en_info['ep_addr_e'];
		if(!$zc_addr_post   || $parent_id )$zc_addr_post  =$en_info['ep_addrcode'];
		if(!$tx_addr        || $parent_id )$tx_addr       =$en_info['cta_addr'];
		if(!$tx_addr_e      || $parent_id )$tx_addr_e     =$en_info['cta_addr_e'];
		if(!$tx_addr_post   || $parent_id )$tx_addr_post  =$en_info['ep_addrcode'];
		
		if(!$sc_addr        || $parent_id )$sc_addr       =$en_info['prod_addr'];
		if(!$sc_addr_e      || $parent_id )$sc_addr_e     =$en_info['prod_addr_e'];
		if(!$sc_addr_post   || $parent_id )$sc_addr_post  =$en_info['prod_addrcode'];
		if(!$cert_name_e    || $parent_id )$cert_name_e   =$en_info['ep_name_e'];
		if(!$cert_name      || $parent_id )$cert_name     =$en_info['ep_name'];
		if(!$cert_post_code || $parent_id )$cert_post_code=$en_info['areacode'];

		
		//证书地址
		if(!$cert_addr){//中文地址
			if( $en_info['ep_addr'] ==$en_info['prod_addr']){
				$cert_addr = $en_info['ep_addr'];
			}else{
				$cert_addr = $en_info['ep_addr'].','.$en_info['prod_addr'];
			}
 		}

		if(!$cert_addr_e){//英文地址
			if($en_info['ep_addr_e'] ==$en_info['prod_addr_e']){
				$cert_addr_e = $en_info['ep_addr_e'];
			}else{
				$cert_addr_e = $en_info['ep_addr_e'].','.$en_info['prod_addr_e'];
			}
 		}
		if($report_date=='0000-00-00'  || !$report_date){
			$report_date = date("Y-m-d");
		}
		if($change_date=='0000-00-00'  || !$report_date){
			$change_date = '';
		}
		 
	}else{
	extract($p_info);
	
	}

	//显示子证书 应急证书
	if($is_check=='y') $show_en=true;

	$old_zsid = $new_zsid = $zsid;
	$certreplace_select = str_replace( "value=\"$change_type\">", "value=\"$change_type\" selected>" , $certreplace_select );
	$is_change_select   = '<option value="0">否</option><option value="1">是</option>';
	$is_change_select   = str_replace( "value=\"$is_change\">", "value=\"$is_change\" selected>" , $is_change_select );

	//证书状态： 已登记，未登记，未登记完
	if($is_check=='e'){
		$str_check = "<input type='hidden' name='old_check'  value='$is_check'/><input type='checkbox' name='is_check'  value='y'/>已保存&nbsp;";
	}else if($is_check=='n'){
		$str_check = "<input type='hidden' name='old_check'  value='$is_check'/><input type='checkbox' name='is_check' value='e'/>已保存&nbsp;";
	}else if($is_check=='y'){
		$str_check = "<input type='hidden' name='old_check'  value='$is_check'/><input type='hidden' name='is_check' value='y'/>";
	}

	if(getgp('parent_id')){
		$str_check = NULL;//不出现是否登记完的复选框
	}

	if($is_check=='y'){
		$sql = "select eid,ep_name from sp_enterprises where parent_id='$eid' and deleted = '0'";
		// print_r($sql);exit;
		$res = $db->query($sql);
		$sub_certs = array();
		while($p_info = $db->fetch_array($res)){
			$sub_certs[] = $p_info;
		}
	}

	//已有证书信息列表
	$sql = "select * from sp_certificate where eid='$eid' and status >0 and iso='$iso' and deleted =0 order by e_date desc";
	$res = $db->query($sql);
	
	$certs = array();
	$addr = array();
	while($p_info = $db->fetch_array($res)){
		$p_info['status'] = f_certstate($p_info['status']);
		$certs[]  = $p_info;

		$addr['ep_addr']     = $p_info['ep_addr'];//注册地址
		$addr['ep_addr_e']   = $p_info['ep_addr_e'];//注册地址英文

		$addr['cta_addr']    = $p_info['cta_addr'];//通讯地址
		$addr['cta_addr_e']  = $p_info['cta_addr_e'];//通讯地址英文

		$addr['bg_addr']     = $p_info['bg_addr'];//办公地址
		$addr['bg_addr_e']   = $p_info['bg_addr_e'];//办公地址英文
        //生产地址引企业表的数据
  	    $addr['prod_addr']   = $en_info['prod_addr'];//其他地址
		$addr['prod_addr_e'] = $en_info['prod_addr_e'];//其他地址英文
		// $prod_check = str_replace('\"','"',$en_info['prod_check']);
		// $prod_check = str_replace("\'","'",$prod_check);
		// $prod_check = unserialize($prod_check);//类型
		$addr['prod_check']  = $arr_addr_prod_check[$prod_check['0']];

	}


 	/** 地址 证书登记时从sp_enterprises读出 **/
	if( empty($addr) ){
		$addr['ep_addr']   = $en_info['ep_addr'];//注册地址
		$addr['ep_addr_e'] = $en_info['ep_addr_e'];//注册地址英文
		
		$addr['cta_addr']  = $en_info['cta_addr'];//通讯地址
		$addr['cta_addr_e']= $en_info['cta_addr_e'];//通讯地址英文

		$addr['bg_addr']   = $en_info['bg_addr'];//办公地址
		$addr['bg_addr_e'] = $en_info['bg_addr_e'];//办公地址英文
		$prod_check        = str_replace('\"','"',$en_info['prod_check']);
        $prod_check        = str_replace('\"','"',$en_info['prod_check']);
		$prod_check        = unserialize($prod_check);//类型
		foreach ($prod_check as $key => $value) {
			if( $value==1 ){
				$addr['prod_addr']   = $en_info['prod_addr'];//其他地址
				$addr['prod_addr_e'] = $en_info['prod_addr_e'];//其他地址英文
				$addr['prod_check']  = $arr_addr_prod_check[$value];
			}
		}
	}
	
	/** 地址 **/
	tpl();