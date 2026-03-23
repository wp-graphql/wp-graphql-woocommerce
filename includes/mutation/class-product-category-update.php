<?php
/**
 * Mutation - UpdateProductCategory (additional fields)
 *
 * Registers additional WooCommerce-specific input fields and processes them
 * on the core WPGraphQL UpdateProductCategory mutation.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since   0.22.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

/**
 * Class - Product_Category_Update
 */
class Product_Category_Update {
	/**
	 * Registers the additional input fields and hooks.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_fields(
			'UpdateProductCategoryInput',
			Product_Category_Create::get_input_fields()
		);

		add_action( 'graphql_update_product_cat', [ Product_Category_Create::class, 'process_additional_fields' ], 10, 2 );
	}
}
