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
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Coupon;

/**
 * Class Coupon_Delete
 */
class Coupon_Delete {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteCoupon',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => array( __CLASS__, 'mutate_and_get_payload' ),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array(
			'id'          => array(
				'type'        => array( 'non_null' => 'ID' ),
				'description' => __( 'Unique identifier for the object.', 'wp-graphql-woocommerce' ),
			),
			'forceDelete' => array(
				'type'        => 'Boolean',
				'description' => __( 'Delete the object. Set to "false" by default.', 'wp-graphql-woocommerce' ),
			)
		);
    }

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array_merge(
			Coupon_Create::get_output_fields(),
			array(
				'coupon' => array(
					'type'    => 'Coupon',
					'resolve' => function( $payload ) {
						//wp_send_json( $payload );
						return ! empty( $payload['coupon'] ) ? $payload['coupon'] : null;
					},
				),
			)
		);
    }

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		$id     = $input['id'];
		$coupon = new Coupon( $id );

		if ( ! $coupon->ID ) {
			throw new UserError( __( 'Invalid ID.', 'wp-graphql-woocommerce' ) );
		}

		if ( ! wc_rest_check_post_permissions( 'shop_coupon', 'delete', $coupon->ID ) ) {
			throw new UserError(
				sprintf(
					/* translators: %s: post type */
					__( 'Sorry, you are not allowed to delete %s.', 'woocommerce' ),
					'shop_coupon'
				)
			);
		}

		$forceDelete = isset( $input['forceDelete'] ) ? $input['forceDelete'] : false;
		$coupon->id;
		$coupon->read_meta_data( true );
		$coupon->delete( $forceDelete );

		return compact( 'coupon' );
	}
}
