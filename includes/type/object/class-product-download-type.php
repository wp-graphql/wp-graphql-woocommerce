<?php
/**
 * WPObject Type - Product_Download_Type
 *
 * Registers proper ProductDownload type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class Product_Download_Type
 */
class Product_Download_Type {
	/**
	 * Register ProductDownload type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductDownload',
			[
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'downloadId'      => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Product download ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_id() : null;
						},
					],
					'name'            => [
						'type'        => 'String',
						'description' => __( 'Product download name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_name() : null;
						},
					],
					'filePathType'    => [
						'type'        => 'String',
						'description' => __( 'Type of file path set', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_type_of_file_path() : null;
						},
					],
					'fileType'        => [
						'type'        => 'String',
						'description' => __( 'File type', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file_type() : null;
						},
					],
					'fileExt'         => [
						'type'        => 'String',
						'description' => __( 'File extension', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file_extension() : null;
						},
					],
					'allowedFileType' => [
						'type'        => 'Boolean',
						'description' => __( 'Is file allowed', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->is_allowed_filetype() : null;
						},
					],
					'fileExists'      => [
						'type'        => 'Boolean',
						'description' => __( 'Validate file exists', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->file_exists() : null;
						},
					],
					'file'            => [
						'type'        => 'String',
						'description' => __( 'Download file', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file() : null;
						},
					],
				],
			]
		);
	}
}
