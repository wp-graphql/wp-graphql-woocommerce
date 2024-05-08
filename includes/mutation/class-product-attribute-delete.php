<?php
/**
 * Mutation - deleteProductAttribute
 *
 * Registers mutation for deleting a product attribute.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Product_Attribute_Delete
 */
class Product_Attribute_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteProductAttribute',
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
            'id' => [
                'type'        => [ 'non_null' => 'ID' ],
                'description' => __( 'Unique identifier for the product.', 'wp-graphql-woocommerce' ),
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
			'attribute'   => [
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
			$attribute = Product_Mutation::get_attribute( $input['id'] );

			if ( is_wp_error( $attribute ) ) {
				return $attribute;
			}

			$deleted = \wc_delete_attribute( $attribute->attribute_id );

			if ( false === $deleted ) {
				throw new UserError( __( 'Failed to delete attribute.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Fires after a single attribute is deleted via the REST API.
			 *
			 * @param stdObject        $attribute     The deleted attribute.
			 */
			do_action( 'graphql_woocommerce_delete_product_attribute', $attribute );

            return [ 'attribute' => $attribute ];
        };
    }
}
