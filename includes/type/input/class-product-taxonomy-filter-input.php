<?php
/**
 * WPInputObjectType - ProductTaxonomyFilterInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.1
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Product_Taxonomy_Filter_Input
 */
class Product_Taxonomy_Filter_Input {

	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_input_type(
			'ProductTaxonomyFilterInput',
			[
				'description' => __( 'Product filter', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'taxonomy' => [
						'type'        => [ 'non_null' => 'ProductTaxonomyEnum' ],
						'description' => __( 'Which field to select taxonomy term by.', 'wp-graphql-woocommerce' ),
					],
					'terms'    => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'A list of term slugs', 'wp-graphql-woocommerce' ),
					],
					'ids'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => __( 'A list of term ids', 'wp-graphql-woocommerce' ),
					],
					'operator' => [
						'type'        => 'TaxonomyOperatorEnum',
						'description' => __( 'Filter operation type', 'wp-graphql-woocommerce' ),
					],
				],
			]
		);
	}
}
