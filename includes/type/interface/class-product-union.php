<?php
/**
 * Defines the union between product types and product variation types.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Product_Union
 */
class Product_Union {
	/**
	 * Registers the Type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductUnion',
			[
				'description' => __( 'Union between the product and product variation types', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines ProductUnion fields. All child type must have these fields as well.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array_merge(
			[
				'id'         => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
				],
				'databaseId' => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
				],
			],
			Product::get_fields()
		);
	}
}
