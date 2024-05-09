<?php
/**
 * Mutation - updateProductAttribute
 *
 * Registers mutation for updating a product attribute.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;

/**
 * Class Product_Attribute_Update
 */
class Product_Attribute_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateProductAttribute',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array_merge(
			[
				'id' => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => __( 'Unique identifier for the product.', 'wp-graphql-woocommerce' ),
				],
			],
			Product_Attribute_Create::get_input_fields()
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'attribute' => [
				'type'    => 'ProductAttributeObject',
				'resolve' => static function ( $payload ) {
					return $payload['attribute'];
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			global $wpdb;

			if ( ! wc_rest_check_manager_permissions( 'attributes', 'edit' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to edit attributes.', 'wp-graphql-woocommerce' ) );
			}

			$id     = (int) $input['id'];
			$edited = \wc_update_attribute(
				$id,
				[
					'name'         => $input['name'],
					'slug'         => \wc_sanitize_taxonomy_name( stripslashes( $input['slug'] ) ),
					'type'         => ! empty( $input['type'] ) ? $input['type'] : 'select',
					'order_by'     => ! empty( $input['orderBy'] ) ? $input['orderBy'] : 'menu_order',
					'has_archives' => true === $input['hasArchives'],
				]
			);

			// Checks for errors.
			if ( is_wp_error( $edited ) ) {
				throw new UserError( $edited->get_error_message() );
			}

			$attribute = Product_Mutation::get_attribute( $id );

			/**
			 * Fires after a single product attribute is created or updated via the REST API.
			 *
			 * @param object{'attribute_id': int} $attribute Inserted attribute object.
			 * @param array                       $input     Request object.
			 * @param boolean                     $creating  True when creating attribute, false when updating.
			 */
			do_action( 'graphql_woocommerce_insert_product_attribute', $attribute, $input, false );

			return [ 'attribute' => $attribute ];
		};
	}
}
