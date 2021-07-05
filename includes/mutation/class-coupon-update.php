<?php
/**
 * Mutation - createUpdate
 *
 * Registers mutation for update an coupon.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.9.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

/**
 * Class Coupon_Update
 */
class Coupon_Update {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateCoupon',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => Coupon_Create::get_output_fields(),
				'mutateAndGetPayload' => array( Coupon_Create::class, 'mutate_and_get_payload' ),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array_merge(
			Coupon_Create::get_input_fields(),
			array(
				'id'   => array(
					'type'        => array( 'non_null' => 'ID' ),
					'description' => __( 'Unique identifier for the object.', 'wp-graphql-woocommerce' ),
				),
				'code' => array(
					'type'        => 'String',
					'description' => __( 'Coupon code.', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}
}
