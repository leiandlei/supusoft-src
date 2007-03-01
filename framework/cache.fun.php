<?php
//缓存驱动： 缓存：文件缓存
function f_select($select_field,$select_val='',$allow_types=''){
	  $cache_file_dir=ROOT.'/data/cache/'.$select_field.'.cache.php';
	if(file_exists($cache_file_dir)){
		require $cache_file_dir;
 	}else{
 		echo '系统配置缓存文件'.$cache_file_dir,'不存在';
	}
	$field_arr=${$select_field."_array"};
	$allow_types=$allow_types?$allow_types:array_keys($field_arr); //通过的类型。默认为全
	if( $field_arr ){
		foreach( $field_arr as $code => $item ){
 			if($item['is_stop']=='0' and in_array($code,$allow_types)){ //过滤停用的选项
 				$field_option .= "<option value=\"$code\">$item[name]</option>";
			 }
 		 }
	}

 	//如果有搜索条件 则替换现有的，否则替换编辑时候的下拉，最后是不替换
	//$select_val=$_GET[$select_field]?$_GET[$select_field]:$select_val;
	$select_val=$select_val?$select_val:$_GET[$select_field];
	
	  $field_option = str_replace( "value=\"$select_val\">", "value=\"$select_val\" selected>" , $field_option );
	return $field_option;
}

//用下拉的方式读取缓存
function read_cache( $cache_name,$code){

	if( !isset( $GLOBALS[$cache_name.'_array'] ) ){
		require( ROOT . '/data/cache/'.$cache_name.'.cache.php' );
		$GLOBALS[$cache_name.'_array'] = ${$cache_name."_array"}; //$iso_array $GLOBALS['iso_array'];
	} else {
		global ${$cache_name."_array"};
	}
	return ${$cache_name."_array"}[$code]['name'];
}


function sys_cache_select($select_field,$select_val=''){

 	$sys_cache_file='data/sys_cache/'.$select_field.'.cache.php';
	if(file_exists($sys_cache_file)){
		$field_arr=include('data/sys_cache/'.$select_field.'.cache.php');
	}else{
		echo '系统缓存文件路径不存在，请监查路径：'.$sys_cache_file;
	}

	if( $field_arr ){
		foreach( $field_arr as $code => $item ){
 			if($item['is_stop']=='0'){ //过滤停用的选项
			$field_option .= "<option value=\"$code\">$item[name]</option>";
			}
		 }
	}
	//如果有搜索条件 则替换现有的，否则替换编辑时候的下拉，最后是不替换
	$select_val=$_GET[$select_field]?$_GET[$select_field]:$select_val;
	$field_option = str_replace( "value=\"$select_val\">", "value=\"$select_val\" selected>" , $field_option );
	return $field_option;
}

//用下拉的方式读取缓存
function r_sys_cache($cache_name,$code){
	$field_arr=include('data/sys_cache/'.$cache_name.'.cache.php');
 	return $field_arr[$code]['name'];
}

//读取数组缓存
function f_checkbox($file_name,$arr=''){
	require DATA_DIR.'cache/'.$file_name.'.cache.php'; //添加课程
 	$new_arr=explode('；',$arr);
	foreach($new_arr as $v){
		if(in_array($v,array_keys(${$file_name."_array"}))){
			  $str.=${$file_name."_array"}[$v]['name'].' ';
		};
	}
	return $str;
}
/*
 * 函数名：f_ctfrom_select
 * 功  能：合同来源下拉框
 * 参  数：无
 * 返回值：下拉框内容
 */
/*function f_ctfrom_select($select_field='ctfrom'){
	if( !isset( $GLOBALS['ctfrom_array'] ) ){
		require( ROOT . '/data/cache/ctfrom.cache.php' );
		$GLOBALS['ctfrom_array'] = $ctfrom_array;
	} else {
		global $ctfrom_array;
	}
	$user_ctfrom = current_user('ctfrom');
	$len = get_ctfrom_level( $user_ctfrom );
	$result = '';
	$used_ctfrom = substr( $user_ctfrom, 0, $len );
	foreach( $ctfrom_array as $code => $item ){
		if( substr( $code, 0, $len ) != $used_ctfrom ) continue;
		$selected="";
		if($code=="01000000")
			$selected="selected";
		$field_option .= "<option value=\"$code\" $selected>{$item[space]}{$item[name]}</option>";
	}
    

	//如果有搜索条件 则替换现有的，否则替换编辑时候的下拉，最后是不替换
	$select_val=$_GET[$select_field]?$_GET[$select_field]:$select_val;
	$field_option = str_replace( "value=\"$select_val\">", "value=\"$select_val\" selected>" , $field_option );
 	//var_dump($field_option);
	return $field_option;
}*/

function f_ctfrom_select($code=''){
	global $db;
	if( empty($code) ){
		if( $_SESSION['extraInfo']['userType']!='stuff'&&!empty($_SESSION['extraInfo']['ctfrom']) ){
			$code = $_SESSION['extraInfo']['ctfrom'];
		}
	}
	if( $_SESSION['extraInfo']['userType']!='stuff' ){
		$ctfrom_array = $db->getAll("SELECT pt_id,code,name from `sp_partner` where status=1 and code='".$code."' and deleted=0");
	}else{
		$ctfrom_array = $db->getAll("SELECT code,name from `sp_settings_ctfrom` where  deleted=0");
	}
	foreach( $ctfrom_array as $item ){
		$selected = ($item['code']==$code)?'selected':'';
		$field_option .= "<option value=\"".$item['code']."\" $selected>".$item['name']."</option>";
	}
	return $field_option;
}
/*
 *合同来源
 *编码转汉字
 *
 */
function f_ctfrom( $code , $all = false ){
	if( !isset( $GLOBALS['ctfrom_array'] ) ){
		require( ROOT . '/data/cache/ctfrom.cache.php' );
		$GLOBALS['ctfrom_array'] = $ctfrom_array;
	} else {
		global $ctfrom_array;
	}
	$result = '';
	if( $all ){
		$c1 = substr( $code, 0, 2 );
		$c2 = substr( $code, 2, 2 );
		$c3 = substr( $code, 4, 2 );
		$c4 = substr( $code, 6, 2 );

		if( '00' != $c1 && '00' == $c2 ){
			$result = f_ctfrom( $c1 . '000000' );
		} elseif( '00' != $c2 && '00' == $c3 ){
			$result = f_ctfrom( $c1 . '000000' ) . '->' . f_ctfrom( $c1 . $c2 . '0000' );
		} elseif( '00' != $c3 && '00' == $c4 ){
			$result = f_ctfrom( $c1 . '000000' ) . '->' . f_ctfrom( $c1 . $c2 . '0000' );
			$result .= '->' . f_ctfrom( $c1 . $c2 . $c3 . '00' );
		} else {
			$result = '无';
		}
	} else {
		$result = $ctfrom_array[$code]['name'];
	}
	return $result;
}


/*
 * 函数名：f_ctfrom_var
 * 功  能：获取合同来源的内容
 * 参  数：$code 编码 $field 字段名
 * 返回值：
 */
function f_ctfrom_var( $code, $field = 'name' ){
	if( !isset( $GLOBALS['ctfrom_array'] ) ){
		require( ROOT . '/data/cache/ctfrom.cache.php' );
		$GLOBALS['ctfrom_array'] = $ctfrom_array;
	} else {
		global $ctfrom_array;
	}
	return $ctfrom_array[$code][$field];
}

//省份下拉(登记用 搜索用)
function f_province_select(){
	require (ROOT . '/data/cache/region.cache.php'); //省份

	if ($region_array) {
		foreach ($region_array as $code => $item) {
			if ('0000' == substr($code, 2, 4)) $province_select.= "<option value=\"$code\">$item[name]</option>";
		}
	}
	return $province_select;
}


/*
 *收费类型
 *
 */
function f_cost_type( $code ){
	if( !isset( $GLOBALS['cost_type_array'] ) ){
		require( ROOT . '/data/cache/cost_type.cache.php' );
		$GLOBALS['cost_type_array'] = $cost_type_array;
	} else {
		global $cost_type_array;
	}
	return $cost_type_array[$code]['name'];
}
/*
 *审核类型
 *编码转汉字
 *
 */
function f_audit_type( $code ){
	if( !isset( $GLOBALS['audit_type_array'] ) ){
		require( ROOT . '/data/cache/audit_type.cache.php' );
		$GLOBALS['audit_type_array'] = $audit_type_array;
	} else {
		global $audit_type_array;
	}
	return $audit_type_array[$code]['name'];
}

/*
 *标准
 *编码转汉字
 *
 */
function f_audit_ver( $code ){
	if(strpos($code, ',')==false) {
		if( !isset( $GLOBALS['audit_ver_array'] ) ){
			require( ROOT . '/data/cache/audit_ver.cache.php' );
			$GLOBALS['audit_ver_array'] = $audit_ver_array;
		} else {
			global $audit_ver_array;
		}
		return $audit_ver_array[$code]['msg'];
	}else{
		$codes = explode(',', $code);
		$code_string = '';
		foreach($codes as $k=>$code) {
			$code_string .= f_audit_ver($code) ;
			if($k+1<count($codes)) {
				$code_string .= '<br>';
			}
		}
		return $code_string;
	}
}
/*
 *文档类型
 *编码转汉字
 *
 */
function f_arctype( $code ){
	if( !isset( $GLOBALS['arctype_array'] ) ){
		require( ROOT . '/data/cache/arctype.cache.php' );
		$GLOBALS['arctype_array'] = $arctype_array;
	} else {
		global $arctype_array;
	}

	return $arctype_array[$code]['name'];
}

/*
 * 函数名：f_source
 * 功  能：获取能力来源
 * 参  数：$code 参力来源代码
 * 返回值：能力来源文字
 */
function f_source($code){
	if( !isset( $GLOBALS['skill_source_array'] ) ){
		require( ROOT . '/data/cache/skill_source.cache.php' );
		$GLOBALS['skill_source_array'] = $skill_source_array;
	} else {
		global $skill_source_array;
	}

	//@HBJ 2013-9-22 修复函数bug, 多个能力来源也能够显示
	$code = explode('；', $code);
	$return = array();
	foreach($code as $code) {
		$return[] = $skill_source_array[$code]['name'];
	}unset($code);

	return implode('&nbsp;&nbsp;',$return);
}
/*
*关联估司类型
*编码转汉字
*
*/
function f_union_type( $code ){
	if( !isset( $GLOBALS['union_type_array'] ) ){
		require( ROOT . '/data/cache/union_type.cache.php' );
		$GLOBALS['union_type_array'] = $union_type_array;
	} else {
		global $union_type_array;
	}
	return $union_type_array[$code]['name'];
}

/*
*分场所类型
*编码转汉字
*
*/
function f_es_type( $code ){
	if( !isset( $GLOBALS['site_type_array'] ) ){
		require( ROOT . '/data/cache/site_type.cache.php' );
		$GLOBALS['site_type_array'] = $site_type_array;
	} else {
		global $site_type_array;
	}
	return $site_type_array[$code]['name'];
}
/*
 *用户名
 *
 */
function f_username( $id ){
	global $db;
	return $db->get_var("select name from sp_hr where id='$id' ");
}
/*
 *用户名
 *
 */
function f_en_name( $id ){
	global $db;
	return $db->get_var("select ep_name from sp_enterprises where eid='$id' ");
}
/*
 *风险等级
 *
 */
function f_risk( $code ){
	if( !isset( $GLOBALS['risk_level_array'] ) ){
		require_once( ROOT . '/data/cache/risk_level.cache.php' );
		$GLOBALS['risk_level_array'] = $risk_level_array;
	} else {
		global $risk_level_array;
	}
	return $risk_level_array[$code]['name'];
}
/*
 *是否专职
 *
 */
function f_audit_job( $code ){
	if( !isset( $GLOBALS['audit_job_array'] ) ){
		require_once( ROOT . '/data/cache/audit_job.cache.php' );
		$GLOBALS['audit_job_array'] = $audit_type_array;
	} else {
		global $audit_job_array;
	}
	return $audit_job_array[$code]['name'];
}
/*
 *审核组内身份
 *
 */
function f_audit_role( $code ){
	if( !isset( $GLOBALS['audit_role_array'] ) ){
		require_once( ROOT . '/data/cache/audit_role.cache.php' );
		$GLOBALS['audit_role_array'] = $audit_role_array;
	} else {
		global $audit_role_array;
	}
	return $audit_role_array[$code]['name'];
}
/*
 *资格
 *
 */
function f_qua_type( $code ){
	if( !isset( $GLOBALS['qualification_array'] ) ){
		require( ROOT . '/data/cache/qualification.cache.php' );
		$GLOBALS['qualification_array'] = $qualification_array;
	} else {
		global $qualification_array;
	}
	
	return $qualification_array[$code]['name'];
}
/*
 *体系
 *
 */
function f_iso( $code ){
	if( !isset( $GLOBALS['iso_array'] ) ){
		require( ROOT . '/data/cache/iso.cache.php' );
		$GLOBALS['iso_array'] = $iso_array;
	} else {
		global $iso_array;
	}

	return $iso_array[$code]['name'];
}
function f_education( $code ){
	if( !isset( $GLOBALS['education_array'] ) ){
		require( ROOT . '/data/cache/education.cache.php' );
		$GLOBALS['education_array'] = $education_array;
	} else {
		global $education_array;
	}
	return $education_array[$code]['name'];
}
/*
 *证书状态
 *
 */
function f_certstate( $code ){
	if( !isset( $GLOBALS['certstate_array'] ) ){
		require( ROOT . '/data/cache/certstate.cache.php' );
		$GLOBALS['certstate_array'] = $certstate_array;
	} else {
		global $certstate_array;
	}
	$code = sprintf('%02d',$code);
	return $certstate_array[$code]['name'];
}
/*
 *证书变更
 *
 */
function f_changeitem( $code ){
	if( !isset( $GLOBALS['changeitem_array'] ) ){
		require( ROOT . '/data/cache/changeitem.cache.php' );
		$GLOBALS['changeitem_array'] = $changeitem_array;
	} else {
		global $changeitem_array;
	}
 	return $changeitem_array[$code]['name'];
}
/*
 *认可标志
 *
 */
function f_mark( $code ){
	if( !isset( $GLOBALS['mark_array'] ) ){
		require( ROOT . '/data/cache/mark.cache.php' );
		$GLOBALS['mark_array'] = $mark_array;
	} else {
		global $mark_array;
	}

	//@HBJ 2013-9-18 修复函数bug, 多个标志也能够显示
	$code = explode(',', $code);
	$return = array();
	foreach($code as $code) {
		$return[] = $mark_array[$code]['name'];
	}unset($code);

	return implode('&nbsp;&nbsp;',$return);
}
/*
 *人员状态:
 *
 */
function f_is_hire(){
	$arr=array('1'=>'在职','2'=>'离职','3'=>'停用');
	foreach($arr as $k=>$v){
		$str.="<option value=$k>$v</option>";

	};
	return $str;

}


/*
 *人员附件类型
 *
 */
function f_atachtype( $code ){
	if( !isset( $GLOBALS['attachtype_array'] ) ){
		require( ROOT . '/data/cache/attachtype.cache.php' );
		$GLOBALS['attachtype_array'] = $attachtype_array;
	} else {
		global $attachtype_array;
	}
	return $attachtype_array[$code]['name'];
}


/*
 *区划代码取前两位
 *转换汉字区划-省
 */
function f_region_province( $code ){
	if( !isset( $GLOBALS['region_array'] ) ){
		require( ROOT . '/data/cache/region.cache.php' );
		$GLOBALS['region_array'] = $region_array;
	} else {
		global $region_array;
	}
	$pcode = substr( $code, 0, 2 ) . '0000';
	return $region_array[$code]['name'];
}



/*
 * 区划代码取前两位
 * 转换汉字区划-省
 */
function f_department( $code ){
	if( !isset( $GLOBALS['department_array'] ) ){
		require( ROOT . '/data/cache/department.cache.php' );
		$GLOBALS['department_array'] = $department_array;
	} else {
		global $department_array;
	}
	return $department_array[$code]['name'];
}

/*
 * 区划代码
 * 转换汉字区划-省市县
 *
 */
function f_region_all( $code ){
	if( !isset( $GLOBALS['region_array'] ) ){
		require( ROOT . '/data/cache/region.cache.php' );
		$GLOBALS['region_array'] = $region_array;
	} else {
		global $region_array;
	}
	$result = '';
	$pcode = substr( $code, 0, 2 ) . '0000';
	$ccode = substr( $code, 0, 4 ) . '00';
	$result .= $region_array[$pcode]['name'];
	$result .= $region_array[$ccode]['name'];
	$result .= $region_array[$code]['name'];
	return $result;

}
/*
 * 函数名：update_cache
 * 参  数：$type 类型
 * 功  能：生成缓存
 */
function update_cache( $type = '' ){
	$where = '';
	$caches = array();
	if( $type && in_array( $type, $cache_types ) ){
		$where .= "AND type = '$type' AND deleted=0 ";
	} else {
		return false;
	}
	$query = $db->query("SELECT code,name,type,is_stop FROM sp_settings WHERE 1 $where ORDER BY vieworder ASC,code ASC");
	while( $rt = $db->fetch_array( $query ) ){
		isset( $caches[$rt['type']] ) or $caches[$rt['type']] = array();
		$caches[$rt['type']][$rt['code']] = $rt;
	}
	if( $caches ){
		foreach( $caches as $pre => $cache ){
			file_put_contents( ROOT . '/data/cache/'.$pre.'.cache.php', "<?php\r\n\${$pre}_array = " . sp_var_export($cache) . "\r\n;?>" );
		}
	}
}



/*
 * 函数名：update_ver_cache
 * 功  能：生成 标准/版本 缓存
 * 参  数：无
 * 返回值：无
 */
function update_ver_cache(){
	global $db;
	$caches = array();
	$query = $db->query("SELECT * FROM sp_settings_audit_vers WHERE 1  AND deleted=0  ORDER BY vieworder ASC");
	while( $rt = $db->fetch_array( $query ) ){
		$rt['code']=$rt['audit_ver'];
		$rt['name']=$rt['msg'];
		$caches[$rt['audit_ver']] = $rt;
	}
	file_put_contents( ROOT . '/data/cache/audit_ver.cache.php', "<?php\r\n\$audit_ver_array = " . sp_var_export($caches) . "\r\n;?>" );
}



/*
 * 函数名：update_ctfrom
 * 功  能：生成 合同来源 缓存
 * 参  数：无
 * 返回值：无
 */
function update_ctfrom(){
	global $db;
	$caches = array();
	$query = $db->query("SELECT * FROM sp_settings_ctfrom WHERE 1  AND deleted=0  AND is_stop = 0 $where ORDER BY code ASC");
	$select_options = '';
	while( $rt = $db->fetch_array( $query ) ){
		$split = ' &nbsp; &nbsp; &nbsp;';
		$space = '';
		if( '000000' == substr( $rt['code'], 2, 6 ) ){
			$space = '';
		} elseif( '0000' == substr( $rt['code'], 4, 4 ) ){
			$space = $split;
		} elseif( '00' == substr( $rt['code'], 6, 2 ) ){
			$space = $split.$split;
		} else {
			$space = $split.$split.$split;
		}
		$rt['space'] = $space;
		$caches[$rt['code']] = $rt;
		$select_options .= "<option value=\"$rt[code]\">{$rt[space]}{$rt[name]}</option>";
	}
	$cache_string = "<?php\r\n\$ctfrom_array = " . sp_var_export($caches) . "\r\n;\r\n\$ctfrom_select = '$select_options';\r\n?>";
	file_put_contents( ROOT . '/data/cache/ctfrom.cache.php', $cache_string );
}

/*
 * 函数名：update_region
 * 功  能：生成 行政区划 缓存
 * 参  数：无
 * 返回值：无
 */
function update_region(){
	global $db;
	$caches = array();
	 $sql="SELECT * FROM sp_settings_region WHERE 1  AND deleted=0  AND is_stop = 0 $where ORDER BY code ASC";

	$query = $db->query("SELECT * FROM sp_settings_region WHERE 1  AND deleted=0  AND is_stop = 0 $where ORDER BY code ASC");
	$prov = $city = $dist = array();
	while( $rt = $db->fetch_array( $query ) ){
		if( '0000' == substr( $rt['code'], 2, 4 ) ){
			$prov[] = array( 'code' => $rt['code'], 'name' => $rt['name'] );
		} elseif( '00' == substr( $rt['code'], 4, 2 ) ){
			$city[] = array( 'code' => $rt['code'], 'name' => $rt['name'] );
		} elseif( '00' != substr( $rt['code'], 4, 2 ) ){
			$dist[] = array( 'code' => $rt['code'], 'name' => $rt['name'] );
		}
	}
 

	$cache_string = json_encode( array( 'province' => $prov, 'city' => $city, 'district' => $dist ) );
	file_put_contents( ROOT . '/data/cache/region.json', $cache_string );
}
