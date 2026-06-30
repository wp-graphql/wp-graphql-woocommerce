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
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;
use WPGraphQL\WooCommerce\Data\Mutation\Coupon_Mutation;
use WPGraphQL\WooCommerce\Model\Coupon;

/**
 * Class Coupon_Create
 */
class Coupon_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createCoupon',
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
			'code'                      => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => static function () {
					return __( 'Coupon code.', 'graphql-for-ecommerce' );
				},
			],
			'amount'                    => [
				'type'        => 'Float',
				'description' => static function () {
					return __( 'The amount of discount. Should always be numeric, even if setting a percentage.', 'graphql-for-ecommerce' );
				},
			],
			'discountType'              => [
				'type'        => 'DiscountTypeEnum',
				'description' => static function () {
					return __( 'Determines the type of discount that will be applied.', 'graphql-for-ecommerce' );
				},
			],
			'description'               => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Coupon description.', 'graphql-for-ecommerce' );
				},
			],
			'dateExpires'               => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'The date the coupon expires, in the site\'s timezone.', 'graphql-for-ecommerce' );
				},
			],
			'dateExpiresGmt'            => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'The date the coupon expires, as GMT.', 'graphql-for-ecommerce' );
				},
			],
			'individualUse'             => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'If true, the coupon can only be used individually. Other applied coupons will be removed from the cart.', 'graphql-for-ecommerce' );
				},
			],
			'productIds'                => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => static function () {
					return __( 'List of product IDs the coupon can be used on.', 'graphql-for-ecommerce' );
				},
			],
			'excludedProductIds'        => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => static function () {
					return __( 'List of product IDs the coupon cannot be used on.', 'graphql-for-ecommerce' );
				},
			],
			'usageLimit'                => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'How many times the coupon can be used in total.', 'graphql-for-ecommerce' );
				},
			],
			'usageLimitPerUser'         => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'How many times the coupon can be used per customer.', 'graphql-for-ecommerce' );
				},
			],
			'limitUsageToXItems'        => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Max number of items in the cart the coupon can be applied to.', 'graphql-for-ecommerce' );
				},
			],
			'freeShipping'              => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'If true and if the free shipping method requires a coupon, this coupon will enable free shipping.', 'graphql-for-ecommerce' );
				},
			],
			'productCategories'         => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => static function () {
					return __( 'List of category IDs the coupon applies to.', 'graphql-for-ecommerce' );
				},
			],
			'excludedProductCategories' => [
				'type'        => [ 'list_of' => 'Int' ],
				'description' => static function () {
					return __( 'List of category IDs the coupon does not apply to.', 'graphql-for-ecommerce' );
				},
			],
			'excludeSaleItems'          => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'If true, this coupon will not be applied to items that have sale prices.', 'graphql-for-ecommerce' );
				},
			],
			'minimumAmount'             => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Minimum order amount that needs to be in the cart before coupon applies.', 'graphql-for-ecommerce' );
				},
			],
			'maximumAmount'             => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Maximum order amount allowed when using the coupon.', 'graphql-for-ecommerce' );
				},
			],
			'emailRestrictions'         => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => static function () {
					return __( 'List of email addresses that can use this coupon.', 'graphql-for-ecommerce' );
				},
			],
			'metaData'                  => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => static function () {
					return __( 'Meta data.', 'graphql-for-ecommerce' );
				},
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
			'coupon' => [
				'type'    => 'Coupon',
				'resolve' => static function ( $payload ) {
					return new Coupon( $payload['id'] );
				},
			],
			'code'   => [
				'type'    => 'String',
				'resolve' => static function ( $payload ) {
					return $payload['code'];
				},
			],
		];
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
		if ( ! empty( $input['id'] ) ) {
			$coupon_id = Utils::get_database_id_from_id( $input['id'] );
		} else {
			$coupon_id = 0;
		}

		if ( false === $coupon_id ) {
			throw new UserError( __( 'Coupon ID provided is invalid. Please check input and try again.', 'graphql-for-ecommerce' ) );
		}

		$coupon = new \WC_Coupon( $coupon_id );

		if ( 0 === $coupon_id && ! wc_rest_check_post_permissions( 'shop_coupon', 'create' ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to create resources.', 'graphql-for-ecommerce' ) );
		}

		if ( 0 !== $coupon_id && ! wc_rest_check_post_permissions( 'shop_coupon', 'edit', $coupon_id ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to edit this resource.', 'graphql-for-ecommerce' ) );
		}

		$coupon_args = Coupon_Mutation::prepare_args( $input );

		foreach ( $coupon_args as $key => $value ) {
			switch ( $key ) {
				case 'code':
					$coupon_code  = wc_format_coupon_code( $value );
					$id           = $coupon->get_id() ? $coupon->get_id() : 0;
					$id_from_code = wc_get_coupon_id_by_code( $coupon_code, $id );

					if ( $id_from_code ) {
						throw new UserError( __( 'The coupon code already exists', 'graphql-for-ecommerce' ) );
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
					if ( is_callable( [ $coupon, "set_{$key}" ] ) ) {
						$coupon->{"set_{$key}"}( $value );
					}
					break;
			}//end switch
		}//end foreach

		return [ 'id' => $coupon->save() ];
	}
}
