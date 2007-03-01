<?php


$id = getgp('id');
	$ct_id = getgp('ct_id');
	$sql = "select iso,eid from sp_contract_item where ct_id='$ct_id' ";
	$res = $db->query($sql);
	while($row=$db->fetch_array($res)){
		if($row['iso']){
			$iso_s[]=$row['iso'];
		}
		$eid=$row['eid'];
	}
	if( $iso_s ){
		foreach( $iso_s as $code => $item ){
			$name = $iso_array[$item]['name'];
			//$iso_select .= "<option value=\"$item\">$name</option>";
			$iso_checkbox .= "<input class='mark_t' onclick='mark_ck(this)' type='checkbox' name='iso_check[$code]' value=\"$item\">".$name.'&nbsp;';
		}
	}
	$cost_type_select=f_select('cost_type');
  	$sql = "select * from sp_contract_cost where ct_id='$ct_id' AND cost >= 0 and deleted = 0 order by id asc";
	$res = $db->query($sql);
	while($row=$db->fetch_array($res)){
		$iso_arr = explode('|', $row['iso']);
		foreach($iso_arr as $key=>$value){
			$iso_arr[$key] = $iso_array[$value]['name'];
		}
		$row['iso'] = implode('<br>',$iso_arr);
		$row['cost_type'] = $cost_type_array[$row['cost_type']]['name'];
		$datas[] = $row;
	}
	
	if($id){
		$row = $ctc->get($id);
		extract($row, EXTR_SKIP);
		$arr_iso = explode('|',$iso);
		foreach($arr_iso as $key=>$value){
			$iso_checkbox = str_replace( "value=\"$value\">", "value=\"$value\" checked>" , $iso_checkbox );
		}
		$iso_select = str_replace( "value=\"$iso\">", "value=\"$iso\" selected>" , $iso_select );
		$cost_type_select = str_replace( "value=\"$cost_type\">", "value=\"$cost_type\" selected>" , $cost_type_select );
		$str = '编辑';
	}else{
		$str = '登记';
	}
	tpl( 'contract/cost_edit' );