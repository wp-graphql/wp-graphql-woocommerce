<?php
/**
 * Defines the fields for downloadable products.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Downloadable_Products
 */
class Downloadable_Products {
	/**
	 * Registers the "DownloadableProducts" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'DownloadableProducts',
			[
				'description' => __( 'Downloadable products.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines "DownloadableProducts" fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'id'             => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			],
			'databaseId'     => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			],
			'virtual'        => [
				'type'        => 'Boolean',
				'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
			],
			'downloadExpiry' => [
				'type'        => 'Int',
				'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
			],
			'downloadable'   => [
				'type'        => 'Boolean',
				'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
			],
			'downloadLimit'  => [
				'type'        => 'Int',
				'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
			],
			'downloads'      => [
				'type'        => [ 'list_of' => 'ProductDownload' ],
				'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
