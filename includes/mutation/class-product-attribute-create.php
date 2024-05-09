<?php
/**
 * Mutation - createProductAttribute
 *
 * Registers mutation for creating a product attribute.
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
 * Class Product_Attribute_Create
 */
class Product_Attribute_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createProductAttribute',
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
		return [
			'name'        => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'Name of the attribute.', 'wp-graphql-woocommerce' ),
			],
			'slug'        => [
				'type'        => 'String',
				'description' => __( 'Slug of the attribute.', 'wp-graphql-woocommerce' ),
			],
			'type'        => [
				'type'        => 'String',
				'description' => __( 'Type of the attribute.', 'wp-graphql-woocommerce' ),
			],
			'orderBy'     => [
				'type'        => 'String',
				'description' => __( 'Order by which the attribute should be sorted.', 'wp-graphql-woocommerce' ),
			],
			'hasArchives' => [
				'type'        => 'Boolean',
				'description' => __( 'Whether the attribute has archives.', 'wp-graphql-woocommerce' ),
			],
		];
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
			if ( ! wc_rest_check_manager_permissions( 'attributes', 'create' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to create attributes.', 'wp-graphql-woocommerce' ) );
			}

			$attribute_id = wc_create_attribute(
				[
					'name'         => $input['name'],
					'slug'         => \wc_sanitize_taxonomy_name( stripslashes( $input['slug'] ) ),
					'type'         => ! empty( $input['type'] ) ? $input['type'] : 'select',
					'order_by'     => ! empty( $input['orderBy'] ) ? $input['orderBy'] : 'menu_order',
					'has_archives' => true === $input['hasArchives'],
				]
			);

			// Checks for errors.
			if ( is_wp_error( $attribute_id ) ) {
				throw new UserError( $attribute_id->get_error_message() );
			}

			$attribute = Product_Mutation::get_attribute( $attribute_id );

			/**
			 * Fires after a single product attribute is created or updated via the REST API.
			 *
			 * @param object{'attribute_id': int} $attribute Inserted attribute object.
			 * @param array                       $input     Request object.
			 * @param boolean                     $creating  True when creating attribute, false when updating.
			 */
			do_action( 'graphql_woocommerce_insert_product_attribute', $attribute, $input, true );


			return [ 'attribute' => $attribute ];
		};
	}
}
