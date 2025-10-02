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
			array(
				'description' => __( 'Taxonomy query', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'taxonomy' => array(
						'type'        => array( 'non_null' => 'ProductAttributeEnum' ),
						'description' => __( 'Product Taxonomy', 'wp-graphql-woocommerce' ),
					),
					'relation' => array(
						'type'        => 'RelationEnum',
						'description' => __( 'Taxonomy relation to query', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
