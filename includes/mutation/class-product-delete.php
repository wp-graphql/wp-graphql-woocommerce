<?php
/**
 * Mutation - deleteProduct
 *
 * Registers mutation for deleting a product.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Product_Delete
 */
class Product_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteProduct',
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
			'product' => [
				'type'    => 'Product',
				'resolve' => static function ( $payload ) {
					return $payload['product'];
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
			$product_id = $input['id'];
			$force      = isset( $input['force'] ) ? $input['force'] : false;
			$object     = new Product( $product_id );
			$result     = false;

			if ( 0 === $object->ID ) {
				throw new UserError( __( 'Invalid product ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( 'variation' === $object->get_type() ) {
				throw new UserError( __( 'Variations cannot be deleted with this mutation. Use "deleteProductVariations" instead.', 'wp-graphql-woocommerce' ) );
			}

			$supports_trash = EMPTY_TRASH_DAYS > 0 && is_callable( [ $object, 'get_status' ] );

			/**
			 * Filter whether an object is trashable.
			 *
			 * Return false to disable trash support for the object.
			 *
			 * @param boolean                              $supports_trash Whether the object type support trashing.
			 * @param \WPGraphQL\WooCommerce\Model\Product $object         The object being considered for trashing support.
			 */
			$supports_trash = apply_filters( 'graphql_woocommerce_product_object_trashable', $supports_trash, $object );

			if ( ! wc_rest_check_post_permissions( 'product', 'delete', $object->ID ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to delete products', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Get the product to be deleted.
			 * 
			 * @var \WC_Product $product_to_be_deleted
			 */
			$product_to_be_deleted = \wc_get_product( $object->ID );
			
			if ( $force ) {
				if ( $product_to_be_deleted->is_type( 'variable' ) ) {
					foreach ( $product_to_be_deleted->get_children() as $child_id ) {
						$child = wc_get_product( $child_id );
						if ( ! empty( $child ) ) {
							$child->delete( true );
						}
					}
				} else {
					// For other product types, if the product has children, remove the relationship.
					foreach ( $product_to_be_deleted->get_children() as $child_id ) {
						$child = wc_get_product( $child_id );
						if ( ! empty( $child ) ) {
							$child->set_parent_id( 0 );
							$child->save();
						}
					}
				}

				$product_to_be_deleted->delete( true );
				$result = 0 === $product_to_be_deleted->get_id();
			} else {
				// If we don't support trashing for this type, error out.
				if ( ! $supports_trash ) {
					throw new UserError( __( 'This product does not support trashing.', 'wp-graphql-woocommerce' ) );
				}

				if ( is_callable( [ $product_to_be_deleted, 'get_status' ] ) ) {
					if ( 'trash' === $product_to_be_deleted->get_status() ) {
						throw new UserError( __( 'Product is already in the trash.', 'wp-graphql-woocommerce' ) );
					}
	
					$product_to_be_deleted->delete();

					/**
					 * @var string $status
					 */
					$status = $product_to_be_deleted->get_status();
					$result = 'trash' === $status;
				}
			}

			if ( ! $result ) {
				throw new UserError( __( 'Failed to delete product.', 'wp-graphql-woocommerce' ) );
			}

			if ( 0 !== $product_to_be_deleted->get_parent_id() ) {
				\wc_delete_product_transients( $product_to_be_deleted->get_parent_id() );
			}

			/**
			 * Fires after a single object is deleted or trashed via the REST API.
			 *
			 * @param \WPGraphQL\WooCommerce\Model\Product $object  The deleted or trashed object.
			 * @param array   $input   The mutation input.
			 */
			do_action( 'graphql_woocommerce_delete_product_object', $object, $input );

			return [ 'product' => $object ];
		};
	}
}
