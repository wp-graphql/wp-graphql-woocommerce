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
				'description' => static function () {
					return __( 'A product object', 'graphql-for-ecommerce' );
				},
				'fields'      => [
					'downloadId'      => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
							return __( 'Product download ID', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_id() : null;
						},
					],
					'name'            => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Product download name', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_name() : null;
						},
					],
					'filePathType'    => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Type of file path set', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_type_of_file_path() : null;
						},
					],
					'fileType'        => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'File type', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file_type() : null;
						},
					],
					'fileExt'         => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'File extension', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file_extension() : null;
						},
					],
					'allowedFileType' => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Is file allowed', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->is_allowed_filetype() : null;
						},
					],
					'fileExists'      => [
						'type'        => 'Boolean',
						'description' => static function () {
							return __( 'Validate file exists', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->file_exists() : null;
						},
					],
					'file'            => [
						'type'        => 'String',
						'description' => static function () {
							return __( 'Download file', 'graphql-for-ecommerce' );
						},
						'resolve'     => static function ( $download ) {
							return ! empty( $download ) ? $download->get_file() : null;
						},
					],
				],
			]
		);
	}
}
