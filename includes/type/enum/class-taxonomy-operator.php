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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxonomyOperatorEnum',
			[
				'description' => __( 'Taxonomy query operators', 'wp-graphql-woocommerce' ),
				'values'      => [
					'IN'         => [ 'value' => 'IN' ],
					'NOT_IN'     => [ 'value' => 'NOT IN' ],
					'AND'        => [ 'value' => 'AND' ],
					'EXISTS'     => [ 'value' => 'EXISTS' ],
					'NOT_EXISTS' => [ 'value' => 'NOT EXISTS' ],
				],
			]
		);
	}
}
