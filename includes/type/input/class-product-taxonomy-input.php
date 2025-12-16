<?php
/**
 * WPInputObjectType - ProductTaxonomyInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Taxonomy_Input
 */
class Product_Taxonomy_Input {
	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductTaxonomyInput',
			[
				'description' => __( 'Product taxonomy filter type', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'relation' => [
						'type'        => 'RelationEnum',
						'description' => __( 'Logic relation between each filter.', 'wp-graphql-woocommerce' ),
					],
					'filters'  => [
						'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
						'description' => __( 'Product taxonomy rules to be filter results by', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
