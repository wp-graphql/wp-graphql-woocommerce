<?php
/**
 * Defines the "DownloadableProduct" interface.
 * 
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL\WooCommerce\Core_Schema_Filters as Core;

/**
 * Class Downloadable_Product
 */
class Downloadable_Product {
	/**
	 * Registers the "DownloadableProduct" type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'DownloadableProduct',
			[
				'description' => __( 'A downloadable product.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => [ Core::class, 'resolve_product_type' ],
			]
		);
	}

	/**
	 * Defines fields of "DownloadableProduct".
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
