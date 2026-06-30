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
				'description' => static function () {
					return __( 'Product filter', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'taxonomy' => [
						'type'        => [ 'non_null' => 'ProductTaxonomyEnum' ],
						'description' => static function () {
							return __( 'Which field to select taxonomy term by.', 'graphql-for-ecommerce' );
						},
					],
					'terms'    => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
							return __( 'A list of term slugs', 'graphql-for-ecommerce' );
						},
					],
					'ids'      => [
						'type'        => [ 'list_of' => 'Int' ],
						'description' => static function () {
							return __( 'A list of term ids', 'graphql-for-ecommerce' );
						},
					],
					'operator' => [
						'type'        => 'TaxonomyOperatorEnum',
						'description' => static function () {
							return __( 'Filter operation type', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
