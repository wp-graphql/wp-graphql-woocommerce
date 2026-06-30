<?php
/**
 * Defines the "DownloadableProduct" interface.
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

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
				'description' => static function () {
					return __( 'A downloadable product.', 'graphql-for-ecommerce' );
				},
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => 'wc_graphql_resolve_product_type',
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
				'description' => static function () {
					return __( 'Product or variation global ID', 'graphql-for-ecommerce' );
				},
			],
			'databaseId'     => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => static function () {
					return __( 'Product or variation ID', 'graphql-for-ecommerce' );
				},
			],
			'downloadExpiry' => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Download expiry', 'graphql-for-ecommerce' );
				},
			],
			'downloadable'   => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Is downloadable?', 'graphql-for-ecommerce' );
				},
			],
			'downloadLimit'  => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Download limit', 'graphql-for-ecommerce' );
				},
			],
			'downloads'      => [
				'type'        => [ 'list_of' => 'ProductDownload' ],
				'description' => static function () {
					return __( 'Product downloads', 'graphql-for-ecommerce' );
				},
			],
		];
	}
}
