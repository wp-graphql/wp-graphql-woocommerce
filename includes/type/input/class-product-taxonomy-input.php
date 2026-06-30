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
				'description' => static function () {
					return __( 'Product taxonomy filter type', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'relation' => [
						'type'        => 'RelationEnum',
						'description' => static function () {
							return __( 'Logic relation between each filter.', 'graphql-for-ecommerce' );
						},
					],
					'filters'  => [
						'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
						'description' => static function () {
							return __( 'Product taxonomy rules to be filter results by', 'graphql-for-ecommerce' );
						},
					],
					'or'       => [
						'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
						'description' => static function () {
							return __( 'Product taxonomy rules connected by OR logic', 'graphql-for-ecommerce' );
						},
					],
					'and'      => [
						'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
						'description' => static function () {
							return __( 'Product taxonomy rules connected by AND logic', 'graphql-for-ecommerce' );
						},
					],
				],
			]
		);
	}
}
