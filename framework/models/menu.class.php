<?php
//个人中心。菜单

class menu extends model{


	function add( $args ){
		global $db;
		$default = array(
			'parent_id'	=> 0,
			'uid'		=> current_user( 'uid' ),
			'mtype'		=> 'item',// 类型
			'name'		=> '',// 菜单名
			'jump'		=> '',// 链接
			'target'	=> 'rightmain',// 链接
			'vieworder'	=> 0 // 体系
		);

		$args = parse_args( $args, $default );
		$id = $db->insert( 'user_menus', $args );
		return $id;
	}

	function add_menu( $name ){
		return $this->add( array( 'mtype' => 'menu', 'name' => $name ) );
	}

	function add_item( $parent_id, $name, $jump, $target = 'rightmain', $vieworder = 0 ){
		return $this->add( array(
				'parent_id' => $parent_id,
				'mtype'		=> 'item',
				'name'		=> $name,
				'jump'		=> $jump,
				'target'	=> $target,
				'vieworder' => $vieworder
			) );
	}


	function edit( $mid, $args ){
		global $db;
		$args = parse_args( $args );
		$db->update( 'user_menus', $args, array( 'id' => $mid ) );
	}

	function get( $args ){
		if( empty( $args ) || !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$where = $db->sqls( $args, 'AND' );
		$row = $db->get_row("SELECT * FROM sp_user_menus WHERE $where");
		return $row;
	}

	function del( $args ){
		if( empty( $args ) or !is_array( $args ) ) return false;
		global $db;
		$args = parse_args( $args );
		$db->update( 'user_menus', array( 'deleted' => 1 ), $args );
	}



}

?>