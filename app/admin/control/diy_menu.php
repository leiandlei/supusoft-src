<?php
$step = getgp( 'step' );
if( empty( $a ) || 'index' == $a ){  
	$jumptype_uc = $jumptype_default = '';
	${'jumptype_'.get_option('jumptype')} = 'checked';
	$menus = $items = array();
	$query = $db->query( "SELECT * FROM sp_user_menus WHERE uid = '".current_user('uid')."' ORDER BY vieworder" );
	while( $rt = $db->fetch_array( $query ) ){
		if( 'menu' == $rt['mtype'] ){
			$menus[$rt['id']] = $rt;
		} else {
			isset( $items[$rt['parent_id']] ) or $items[$rt['parent_id']] = array();
			$items[$rt['parent_id']][$rt['id']] = $rt;
		}
	} 
	tpl( 'diy_menu' );
} elseif( 'add' == $a || 'edit' == $a ){
	$menu = load( 'menu' );
	$parent_id	= (int)getgp( 'parent_id' );

	if( $step ){
		$name		= getgp( 'name' );
		$vieworder	= (int)getgp( 'vieworder' );
		$jump		= getgp( 'jump' );
		$target		= getgp( 'target' );

		if( 'add' == $a ){
			if( $parent_id ){
				$menu->add_item( $parent_id, $name, $jump, $target, $vieworder );
			} else {
				$menu->add_menu( $name );
			}
		} else {
			$mid = (int)getgp( 'mid' );
			$new_item = array(
				'parent_id' => $parent_id,
				'name'		=> $name,
				'jump'		=> $jump,
				'target'	=> $target,
				'vieworder'	=> $vieworder
			);
			$menu->edit( $mid, $new_item );
		}

		showmsg( 'success', 'success', "?c=$c&a=index" );

	} else {
		$target_rightmain = $target__blank = '';
		if( 'edit' == $a ){
			$mid = (int)getgp( 'mid' );
			$row = $menu->get( array( 'id' => $mid ) );
			extract( $row, EXTR_SKIP );
			$parent_id = $row['parent_id'];

			${'target_'.$target} = 'selected';
		}



		tpl( 'diy_menu_edit' );
	}
} elseif( 'batch' == $a ){

}
?>