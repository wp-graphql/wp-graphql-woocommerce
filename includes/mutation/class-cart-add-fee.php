<?php
/**
 * Mutation - addFee
 *
 * Registers mutation for add an additional fee to the cart.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.1.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

/**
 * Class - Cart_Add_Fee
 */
class Cart_Add_Fee {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'addFee',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		$input_fields = array(
			'name'     => array(
				'type'        => array( 'non_null' => 'String' ),
				'description' => __( 'Unique name for the fee.', 'wp-graphql-woocommerce' ),
			),
			'amount'   => array(
				'type'        => 'Float',
				'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
			),
			'taxable'  => array(
				'type'        => 'Boolean',
				'description' => __( 'Is the fee taxable?', 'wp-graphql-woocommerce' ),
			),
			'taxClass' => array(
				'type'        => 'TaxClassEnum',
				'description' => __( 'The tax class for the fee if taxable.', 'wp-graphql-woocommerce' ),
			),
		);

		return $input_fields;
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'cartFee' => array(
				'type'    => 'CartFee',
				'resolve' => function ( $payload ) {
					$fees = \WC()->cart->get_fees();
					return $fees[ $payload['id'] ];
				},
			),
			'cart'    => Cart_Mutation::get_cart_field( true ),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			Cart_Mutation::check_session_token();

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				throw new UserError( __( 'You do not have the appropriate capabilities to perform this action.', 'wp-graphql-woocommerce' ) );
			}

			if ( empty( $input['name'] ) ) {
				throw new UserError( __( 'No name provided for fee.', 'wp-graphql-woocommerce' ) );
			}

			if ( ! isset( $input['amount'] ) ) {
				throw new UserError( __( 'No amount set for the fee.', 'wp-graphql-woocommerce' ) );
			}

			// Get cart fee args.
			$cart_fee_args = Cart_Mutation::prepare_cart_fee( $input, $context, $info );

			// Add cart fee.
			\WC()->cart->add_fee( ...$cart_fee_args );

			// Return payload.
			return array( 'id' => \sanitize_title( $input['name'] ) );
		};
	}
}
