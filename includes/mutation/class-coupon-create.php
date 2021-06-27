<?php
/**
 * Mutation - createCoupon
 *
 * Registers mutation for creating an coupon.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.9.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Coupon_Mutation;
use WPGraphQL\WooCommerce\Model\Coupon;

/**
 * Class Coupon_Create
 */
class Coupon_Create {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createCoupon',
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
			'code'                      => array(
				'type'        => array( 'non_null' => 'String' ),
				'description' => __( 'Coupon code.', 'wp-graphql-woocommerce' ),
			),
			'amount'                    => array(
				'type'        => 'Float',
				'description' => __( 'The amount of discount. Should always be numeric, even if setting a percentage.', 'wp-graphql-woocommerce' ),
			),
			'discountType'              => array(
				'type'        => 'DiscountTypeEnum',
				'description' => __( 'Determines the type of discount that will be applied.', 'wp-graphql-woocommerce' ),
			),
			'description'               => array(
				'type'        => 'String',
				'description' => __( 'Coupon description.', 'wp-graphql-woocommerce' ),
			),
			'dateExpires'               => array(
				'type'        => 'String',
				'description' => __( 'The date the coupon expires, in the site\'s timezone.', 'wp-graphql-woocommerce' ),
			),
			'dateExpiresGmt'            => array(
				'type'        => 'String',
				'description' => __( 'The date the coupon expires, as GMT.', 'wp-graphql-woocommerce' ),
			),
			'individualUse'             => array(
				'type'        => 'Boolean',
				'description' => __( 'If true, the coupon can only be used individually. Other applied coupons will be removed from the cart.', 'wp-graphql-woocommerce' ),
			),
			'productIds'                => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'List of product IDs the coupon can be used on.', 'wp-graphql-woocommerce' ),
			),
			'excludedProductIds'        => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'List of product IDs the coupon cannot be used on.', 'wp-graphql-woocommerce' ),
			),
			'usageLimit'                => array(
				'type'        => 'Int',
				'description' => __( 'How many times the coupon can be used in total.', 'wp-graphql-woocommerce' ),
			),
			'usageLimitPerUser'         => array(
				'type'        => 'Int',
				'description' => __( 'How many times the coupon can be used per customer.', 'wp-graphql-woocommerce' ),
			),
			'limitUsageToXItems'        => array(
				'type'        => 'Int',
				'description' => __( 'Max number of items in the cart the coupon can be applied to.', 'wp-graphql-woocommerce' ),
			),
			'freeShipping'              => array(
				'type'        => 'Boolean',
				'description' => __( 'If true and if the free shipping method requires a coupon, this coupon will enable free shipping.', 'wp-graphql-woocommerce' ),
			),
			'productCategories'         => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'List of category IDs the coupon applies to.', 'wp-graphql-woocommerce' ),
			),
			'excludedProductCategories' => array(
				'type'        => array( 'list_of' => 'Int' ),
				'description' => __( 'List of category IDs the coupon does not apply to.', 'wp-graphql-woocommerce' ),
			),
			'excludeSaleItems'          => array(
				'type'        => 'Boolean',
				'description' => __( 'If true, this coupon will not be applied to items that have sale prices.', 'wp-graphql-woocommerce' ),
			),
			'minimumAmount'             => array(
				'type'        => 'String',
				'description' => __( 'Minimum order amount that needs to be in the cart before coupon applies.', 'wp-graphql-woocommerce' ),
			),
			'maximumAmount'             => array(
				'type'        => 'String',
				'description' => __( 'Maximum order amount allowed when using the coupon.', 'wp-graphql-woocommerce' ),
			),
			'emailRestrictions'         => array(
				'type'        => array( 'list_of' => 'String' ),
				'description' => __( 'List of email addresses that can use this coupon.', 'wp-graphql-woocommerce' ),
			),
			'metaData'                  => array(
				'type'        => array( 'list_of' => 'MetaDataInput' ),
				'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
			),
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
			'coupon' => array(
				'type'    => 'Coupon',
				'resolve' => function( $payload ) {
					return new Coupon( $payload['id'] );
				},
			),
			'code'   => array(
				'type'    => 'String',
				'resolve' => function( $payload ) {
					return $payload['code'];
				},
			),
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
	 * @return callable
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

		$coupon = new \WC_Coupon( $coupon_id );

		if ( 0 === $coupon_id && ! wc_rest_check_post_permissions( 'shop_coupon', 'create' ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to create resources.', 'wp-graphql-woocommerce' ) );
		}

		if ( 0 !== $coupon_id && ! wc_rest_check_post_permissions( 'shop_coupon', 'edit', $coupon_id ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to edit this resource.', 'wp-graphql-woocommerce' ) );
		}

		$coupon_args = Coupon_Mutation::prepare_args( $input );

		foreach ( $coupon_args as $key => $value ) {
			switch ( $key ) {
				case 'code':
					$coupon_code  = wc_format_coupon_code( $value );
					$id           = $coupon->get_id() ? $coupon->get_id() : 0;
					$id_from_code = wc_get_coupon_id_by_code( $coupon_code, $id );

					if ( $id_from_code ) {
						throw new UserError( __( 'The coupon code already exists', 'wp-graphql-woocommerce' ) );
					}

					$coupon->set_code( $coupon_code );
					break;
				case 'meta_data':
					if ( is_array( $value ) ) {
						foreach ( $value as $meta ) {
							$coupon->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
						}
					}
					break;
				case 'description':
					$coupon->set_description( wp_filter_post_kses( $value ) );
					break;
				default:
					if ( is_callable( array( $coupon, "set_{$key}" ) ) ) {
						$coupon->{"set_{$key}"}( $value );
					}
					break;
			}
		}

		return array( 'id' => $coupon->save() );
	}
}
