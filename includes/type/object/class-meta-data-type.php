<?php
/**
 * WPObject Type - Order_Item_Type
 *
 * Registers MetaData type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WP_GraphQL_WooCommerce;

/**
 * Class Meta_Data_Type
 */
class Meta_Data_Type {

	/**
	 * Register Order type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'MetaData',
			array(
				'description' => __( 'Extra data defined on the WC object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'    => array(
						'type'        => 'ID',
						'description' => __( 'Meta ID.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					),
					'key'   => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta key.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->key ) ? (string) $source->key : null;
						},
					),
					'value' => array(
						'type'        => 'String',
						'description' => __( 'Meta value.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->value ) ? (string) $source->value : null;
						},
					),
				),
			)
		);
	}

	/**
	 * Definition/Resolution of the metaData field used across the schema.
	 *
	 * @return array
	 */
	public static function get_metadata_field_definition() {
		return array(
			'type'        => array( 'list_of' => 'MetaData' ),
			'description' => __( 'Object meta data', 'wp-graphql-woocommerce' ),
			'args'        => array(
				'key'      => array(
					'type'        => 'String',
					'description' => __( 'Retrieve meta by key', 'wp-graphql-woocommerce' ),
				),
				'keysIn'   => array(
					'type'        => array( 'list_of' => 'String' ),
					'description' => __( 'Retrieve multiple metas by key', 'wp-graphql-woocommerce' ),
				),
				'multiple' => array(
					'type'        => 'Boolean',
					'description' => __( 'Retrieve meta with matching keys', 'wp-graphql-woocommerce' ),
				),
			),
			'resolve'     => function( $source, array $args ) {
				// Set unique flag.
				$single = ! empty( $args['multiple'] ) ? ! $args['multiple'] : true;

				// Check "key" argument and format meta_data objects.
				if ( ! empty( $args['key'] ) && $source->meta_exists( $args['key'] ) ) {
					$data = $source->get_meta( $args['key'], $single );
					if ( ! is_array( $data ) ) {
						$data = array_filter(
							$source->get_meta_data(),
							function( $meta ) use ( $data ) {
								return $meta->value === $data;
							}
						);
					}
				} elseif ( ! empty( $args['keysIn'] ) ) { // Check "keysIn" argument and format meta_data objects.
					$keys = $args['keysIn'];

					$found = array();
					$data = array_filter(
						$source->get_meta_data(),
						function( $meta ) use ( $keys, $single, &$found ) {
							if ( in_array( $meta->key, $keys, true ) ) {
								if ( $single ) {
									if ( ! in_array( $meta->key, $found, true ) ) {
										$found[] = $meta->key;
										return true;
									}
									return false;
								}
								return true;
							}
						}
					);
				} else { // If no arguments set return all meta (in accordance with unique flag).
					$found = array();
					$data = array_filter(
						$source->get_meta_data(),
						function( $meta ) use ( $single, &$found ) {
							if ( $single ) {
								if ( ! in_array( $meta->key, $found, true ) ) {
									$found[] = $meta->key;
									return true;
								}
								return false;
							}
							return true;
						}
					);
				}

				return ! empty( $data ) ? $data : null;
			},
		);
	}
}
