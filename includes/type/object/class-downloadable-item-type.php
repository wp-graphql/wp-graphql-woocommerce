<?php
/**
 * WPObject Type - Downloadable_Item_Type
 *
 * Registers the "DownloadableItem" type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.4.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WC_Product_Download;

/**
 * Class Downloadable_Item_Type
 */
class Downloadable_Item_Type {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_object_type(
			'DownloadableItem',
			array(
				'description' => __( 'A downloadable item', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'downloadId'         => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Downloadable item unique identifier', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['download_id'] ) ? $source['download_id'] : null;
						},
					),
					'url'                => array(
						'type'        => 'String',
						'description' => __( 'Download URL of the downloadable item.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['download_url'] ) ? $source['download_url'] : null;
						},
					),
					'name'               => array(
						'type'        => 'String',
						'description' => __( 'Name of the downloadable item.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['download_name'] ) ? $source['download_name'] : null;
						},
					),
					'downloadsRemaining' => array(
						'type'        => 'Int',
						'description' => __( 'Number of times the item can be downloaded.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return isset( $source['downloads_remaining'] ) && 'integer' === gettype( $source['downloads_remaining'] )
								? $source['downloads_remaining']
								: null;
						},
					),
					'accessExpires'      => array(
						'type'        => 'String',
						'description' => __( 'The date the downloadable item expires', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['access_expires'] ) ? $source['access_expires'] : null;
						},
					),
					'product'            => array(
						'type'        => 'Product',
						'description' => __( 'Product of downloadable item.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source, array $args, AppContext $context ) {
							return Factory::resolve_crud_object( $source['product_id'], $context );
						},
					),
					'download'           => array(
						'type'        => 'ProductDownload',
						'description' => __( 'ProductDownload of the downloadable item', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							$download_id = $source['download_id'];
							$product_id  = $source['product_id'];
							$files       = array_filter( (array) get_post_meta( $product_id, '_downloadable_files', true ) );

							if ( empty( $download_id )
								|| empty( $product_id )
								|| empty( $files )
								|| ! in_array( $download_id, array_keys( $files ), true ) ) {
								return null;
							}

							$download_data = $files[ $download_id ];
							$download = new WC_Product_Download();
							$download->set_id( $download_id );
							$download->set_name(
								$download_data['name']
									? $download_data['name']
									: wc_get_filename_from_url( $download_data['file'] )
							);
							$download->set_file(
								apply_filters(
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
									'woocommerce_file_download_path',
									$download_data['file'],
									\WC()->product_factory->get_product( $product_id ),
									$download_id
								)
							);

							return $download;
						},
					),
				),
			)
		);
	}
}
