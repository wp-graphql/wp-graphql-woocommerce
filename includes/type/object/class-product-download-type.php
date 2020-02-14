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
	 */
	public static function register() {
		register_graphql_object_type(
			'ProductDownload',
			array(
				'description' => __( 'A product object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'downloadId'      => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Product download ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_id() : null;
						},
					),
					'name'            => array(
						'type'        => 'String',
						'description' => __( 'Product download name', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_name() : null;
						},
					),
					'filePathType'    => array(
						'type'        => 'String',
						'description' => __( 'Type of file path set', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_type_of_file_path() : null;
						},
					),
					'fileType'        => array(
						'type'        => 'String',
						'description' => __( 'File type', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_file_type() : null;
						},
					),
					'fileExt'         => array(
						'type'        => 'String',
						'description' => __( 'File extension', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_file_extension() : null;
						},
					),
					'allowedFileType' => array(
						'type'        => 'Boolean',
						'description' => __( 'Is file allowed', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->is_allowed_filetype() : null;
						},
					),
					'fileExists'      => array(
						'type'        => 'Boolean',
						'description' => __( 'Validate file exists', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->file_exists() : null;
						},
					),
					'file'            => array(
						'type'        => 'String',
						'description' => __( 'Download file', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $download ) {
							return ! empty( $download ) ? $download->get_file() : null;
						},
					),
				),
			)
		);
	}
}
