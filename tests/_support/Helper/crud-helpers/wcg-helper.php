<?php

require_once __DIR__ . '/includes/class-dummy.php';

use GraphQLRelay\Relay;

abstract class WCG_Helper {
	/**
	 * Stores instance of Dummy
	 */
	protected $dummy;
	protected $node_type;

	protected function __construct() {
		$this->dummy = new Dummy();
	}

	public static function instance(){
		return new static();
	}

	public abstract function to_relay_id( $id );

	public abstract function print_query( $id );

	public function print_nodes( $ids, $processors = array() ) {
		$default_processors = array(
			'mapper' => function( $id ) {
				return array( 'id' => Relay::toGlobalId( $this->node_type, $id ) ); 
			},
			'sorter' => function( $id_a, $id_b ) {
				if ( $id_a == $id_b ) {
					return 0;
				}

				return ( $id_a > $id_b ) ? -1 : 1;
			},
			'filter' => function( $id ) {
				return true;
			}
		);

		$processors = array_merge( $default_processors, $processors );

		$results = array_filter( $ids, $processors['filter'] );
		if( ! empty( $results ) ) {
			usort( $results, $processors['sorter'] );
		}

		return array_values( array_map( $processors['mapper'], $results ) );
	}
}