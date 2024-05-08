<?php
/**
 * Mutation - deleteProductVariation
 *
 * Registers mutation for deleting a product variation.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Variation_Delete
 */
class Product_Variation_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteProductVariation',
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
            'id'    => [
                'type'        => [ 'non_null' => 'ID' ],
                'description' => __( 'Unique identifier for the product.', 'wp-graphql-woocommerce' ),
            ],
			'force' => [
				'type'        => 'Boolean',
				'description' => __( 'Whether to bypass trash and force deletion.', 'wp-graphql-woocommerce' ),
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
			'variation'   => [
				'type'    => 'ProductVariation',
				'resolve' => static function ( $payload ) {
					return $payload['variation'];
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
            $variation_id = $input['id'];
			$force        = isset( $input['force'] ) ? $input['force'] : false;
			$object       = new Product_Variation( $variation_id );
			$result       = false;

			if ( ! $object || 0 === $object->get_id() ) {
				throw new UserError( __( 'Invalid product variation ID.', 'wp-graphql-woocommerce' ) );
			}

			$supports_trash = EMPTY_TRASH_DAYS > 0 && is_callable( [ $object, 'get_status' ] );

			/**
			 * Filter whether an object is trashable.
			 *
			 * Return false to disable trash support for the object.
			 *
			 * @param boolean     $supports_trash  Whether the object type support trashing.
			 * @param \WC_Product $object          The object being considered for trashing support.
			 */
			$supports_trash = apply_filters( "graphql_woocommerce_product_variation_object_trashable", $supports_trash, $object );

			if ( ! wc_rest_check_post_permissions( 'product_variation', 'delete', $object->get_id() ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to delete product variations', 'wp-graphql-woocommerce' ) );
			}

			$variation_to_be_deleted = \wc_get_product( $object->get_id() );

			if ( $force ) {
				$variation_to_be_deleted->delete( true );
				$result = 0 === $variation_to_be_deleted->get_id();
			} else {
				// If we don't support trashing for this type, error out.
				if ( ! $supports_trash ) {
					throw new UserError( __( 'This product variation does not support trashing.', 'wp-graphql-woocommerce' ) );
				}

				if ( is_callable( array( $variation_to_be_deleted, 'get_status' ) ) ) {
					if ( 'trash' === $variation_to_be_deleted->get_status() ) {
						throw new UserError( __( 'Product variation is already in the trash.', 'wp-graphql-woocommerce' ) );
					}
	
					$variation_to_be_deleted->delete();
					$result = 'trash' === $variation_to_be_deleted->get_status();
				}
			}

			if ( ! $result ) {
				throw new UserError( __( 'Failed to delete product variation.', 'wp-graphql-woocommerce' ) );
			}

			if ( 0 !== $variation_to_be_deleted->get_parent_id() ) {
				\wc_delete_product_transients( $variation_to_be_deleted->get_parent_id() );
			}

			/**
			 * Fires after a single object is deleted or trashed via the REST API.
			 *
			 * @param Product_Variation $object  The deleted or trashed object.
			 * @param array             $input   The mutation input.
			 */
			do_action( "graphql_woocommerce_delete_product_variation_object", $object, $input );

            return [ 'variation' => $object ];
        };
    }
}
