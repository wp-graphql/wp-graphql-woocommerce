<?php
/**
 * WPEnum Type - TaxonomyOperatorEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Taxonomy_Operator
 */
class Taxonomy_Operator {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxonomyOperatorEnum',
			array(
				'description' => __( 'Taxonomy query operators', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'IN'         => array( 'value' => 'IN' ),
					'NOT_IN'     => array( 'value' => 'NOT IN' ),
					'AND'        => array( 'value' => 'AND' ),
					'EXISTS'     => array( 'value' => 'EXISTS' ),
					'NOT_EXISTS' => array( 'value' => 'NOT EXISTS' ),
				),
			)
		);
	}
}
