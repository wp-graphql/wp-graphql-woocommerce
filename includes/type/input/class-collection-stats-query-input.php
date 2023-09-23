<?php
/**
 * WPInputObjectType - CollectionStatsQueryInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.18.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Collection_Stats_Query_Input
 */
class Collection_Stats_Query_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'CollectionStatsQueryInput',
			[
				'description' => __( 'Taxonomy query', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'taxonomy' => [
						'type'        => [ 'non_null' => 'ProductAttributeEnum' ],
						'description' => __( 'Product Taxonomy', 'wp-graphql-woocommerce' ),
					],
					'relation' => [
						'type'        => [ 'non_null' => 'RelationEnum' ],
						'description' => __( 'Taxonomy relation to query', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
