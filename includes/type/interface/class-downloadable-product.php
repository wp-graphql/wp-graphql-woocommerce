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
			array(
				'description' => __( 'A downloadable product.', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => self::get_fields(),
				'resolveType' => array( Core::class, 'resolve_product_type' ),
			)
		);
	}

	/**
	 * Defines fields of "DownloadableProduct".
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'id'             => array(
				'type'        => array( 'non_null' => 'ID' ),
				'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
			),
			'databaseId'     => array(
				'type'        => array( 'non_null' => 'Int' ),
				'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
			),
			'downloadExpiry' => array(
				'type'        => 'Int',
				'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
			),
			'downloadable'   => array(
				'type'        => 'Boolean',
				'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
			),
			'downloadLimit'  => array(
				'type'        => 'Int',
				'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
			),
			'downloads'      => array(
				'type'        => array( 'list_of' => 'ProductDownload' ),
				'description' => __( 'Product downloads', 'wp-graphql-woocommerce' ),
			),
		);
	}
}
