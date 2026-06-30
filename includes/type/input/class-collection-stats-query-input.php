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
				'description' => static function () {
					return __( 'Taxonomy query', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'taxonomy' => [
						'type'        => [ 'non_null' => 'ProductAttributeEnum' ],
						'description' => static function () {
							return __( 'Product Taxonomy', 'graphql-for-ecommerce' );
						},
					],
					'relation' => [
						'type'        => 'RelationEnum',
						'description' => static function () {
							return __( 'Taxonomy relation to query', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
