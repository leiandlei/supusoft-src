<?php
//企业：分场所

/*if( !class_exists( 'enterprise' ) )
require_once( MODEL_DIR . 'enterprise.class.php' );*/

class enterprise_site extends enterprise{



	function add( $args ){
		if( empty( $args ) ) return false; 
		global $db; 
		$es_id = $db->insert( 'enterprises_site', $args );
		if( $es_id ){
			parent::site_count( $args['eid'], 1 );
			return $es_id;
		}
		return false;
	}

	function get( $args ){
		if( empty( $args ) ) return false;
		$args = parse_args( $args );
		global $db;
		$where = $db->sqls( $args, 'AND' );
		return $db->get_row( "SELECT * FROM sp_enterprises_site WHERE $where" );
	}


	function edit( $st_id, $args ){
		if( empty( $st_id ) || empty( $args ) ) return false;
		global $db;
		$af_info = $this->get(array( 'es_id' => $st_id ));
		$args = parse_args( $args );
		$db->update( 'enterprises_site', $args, array( 'es_id' => $st_id ) );
		$bf_info = $this->get(array( 'es_id' => $st_id ));
	}

	function del( $args ){
		if( empty( $args ) ) return false;
		global $db;
		$eid = $args['eid'];
		$db->update( 'enterprises_site', array( 'deleted' => 1 ), $args );
	}

}

?>