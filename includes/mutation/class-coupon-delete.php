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
use WPGraphQL\Utils\Utils;
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
				'mutateAndGetPayload' => [ self::class, 'mutate_and_get_payload' ],
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
					'resolve' => static function ( $payload ) {
						return ! empty( $payload['coupon'] ) ? $payload['coupon'] : null;
					},
				],
			]
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @param array                                $input    Mutation input.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance. Can be
	 * use to get info about the current node in the GraphQL tree.
	 *
	 * @throws \GraphQL\Error\UserError Invalid ID provided | Lack of capabilities.
	 *
	 * @return array
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		// Retrieve order ID.
		$coupon_id = Utils::get_database_id_from_id( $input['id'] );
		if ( empty( $coupon_id ) ) {
			throw new UserError( __( 'Coupon ID provided is missing or invalid. Please check input and try again.', 'wp-graphql-woocommerce' ) );
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
