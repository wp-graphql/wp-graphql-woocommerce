<?php
/**
 * Mutation - deleteCoupon
 *
 * Registers mutation for deleting an coupon.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.9.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Coupon;

/**
 * Class Coupon_Delete
 */
class Coupon_Delete {

	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteCoupon',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ __CLASS__, 'mutate_and_get_payload' ],
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
			'id'          => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'Unique identifier for the object.', 'wp-graphql-woocommerce' ),
			],
			'forceDelete' => [
				'type'        => 'Boolean',
				'description' => __( 'Delete the object. Set to "false" by default.', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array_merge(
			Coupon_Create::get_output_fields(),
			[
				'coupon' => [
					'type'    => 'Coupon',
					'resolve' => function( $payload ) {
						return ! empty( $payload['coupon'] ) ? $payload['coupon'] : null;
					},
				],
			]
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @param array       $input    Mutation input.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     ResolveInfo instance. Can be
	 * use to get info about the current node in the GraphQL tree.
	 *
	 * @throws UserError Invalid ID provided | Lack of capabilities.
	 *
	 * @return array
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		// Retrieve order ID.
		$coupon_id = 0;
		if ( ! empty( $input['id'] ) && is_numeric( $input['id'] ) ) {
			$coupon_id = absint( $input['id'] );
		} elseif ( ! empty( $input['id'] ) ) {
			$id_components = Relay::fromGlobalId( $input['id'] );
			if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
				throw new UserError( __( 'The "id" provided is invalid', 'wp-graphql-woocommerce' ) );
			}

			$coupon_id = absint( $id_components['id'] );
		}
		$coupon = new Coupon( $coupon_id );

		if ( ! $coupon->ID ) {
			throw new UserError( __( 'Invalid ID.', 'wp-graphql-woocommerce' ) );
		}

		if ( ! wc_rest_check_post_permissions( 'shop_coupon', 'delete', $coupon->ID ) ) {
			/**
			 * Get coupon post type.
			 *
			 * @var \WP_Post_Type $post_type_object
			 */
			$post_type_object = get_post_type_object( 'shop_coupon' );
			throw new UserError(
				sprintf(
					/* translators: %s: post type */
					__( 'Sorry, you are not allowed to delete %s.', 'wp-graphql-woocommerce' ),
					lcfirst( $post_type_object->label )
				)
			);
		}

		$fields_to_cache = $info->getFieldSelection( 2 );
		foreach ( $fields_to_cache['coupon'] as $field => $_ ) {
			$cached = $coupon->$field;
		}

		$force_delete = isset( $input['forceDelete'] ) ? $input['forceDelete'] : false;
		$coupon->delete( $force_delete );

		return compact( 'coupon' );
	}
}
