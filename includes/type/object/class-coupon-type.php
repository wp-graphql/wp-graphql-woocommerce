<?php
/**
 * WPObject Type - Coupon_Type
 *
 * Registers Coupon WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Coupon_Type
 */
class Coupon_Type {

	/**
	 * Register Coupon type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'Coupon',
			array(
				'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
					'id'                 => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the coupon', 'wp-graphql-woocommerce' ),
					),
					'couponId'           => array(
						'type'        => 'Int',
						'description' => __( 'The Id of the order. Equivalent to WP_Post->ID', 'wp-graphql-woocommerce' ),
					),
					'code'               => array(
						'type'        => 'String',
						'description' => __( 'Coupon code', 'wp-graphql-woocommerce' ),
					),
					'date'               => array(
						'type'        => 'String',
						'description' => __( 'Date coupon created', 'wp-graphql-woocommerce' ),
					),
					'modified'           => array(
						'type'        => 'String',
						'description' => __( 'Date coupon modified', 'wp-graphql-woocommerce' ),
					),
					'description'        => array(
						'type'        => 'String',
						'description' => __( 'Explanation of what the coupon does', 'wp-graphql-woocommerce' ),
					),
					'discountType'       => array(
						'type'        => 'DiscountTypeEnum',
						'description' => __( 'Type of discount', 'wp-graphql-woocommerce' ),
					),
					'amount'             => array(
						'type'        => 'Float',
						'description' => __( 'Amount off provided by the coupon', 'wp-graphql-woocommerce' ),
					),
					'dateExpiry'         => array(
						'type'        => 'String',
						'description' => __( 'Date coupon expires', 'wp-graphql-woocommerce' ),
					),
					'usageCount'         => array(
						'type'        => 'Int',
						'description' => __( 'How many times the coupon has been used', 'wp-graphql-woocommerce' ),
					),
					'individualUse'      => array(
						'type'        => 'Boolean',
						'description' => __( 'Individual use means this coupon cannot be used in conjunction with other coupons', 'wp-graphql-woocommerce' ),
					),
					'usageLimit'         => array(
						'type'        => 'Int',
						'description' => __( 'Amount of times this coupon can be used globally', 'wp-graphql-woocommerce' ),
					),
					'usageLimitPerUser'  => array(
						'type'        => 'Int',
						'description' => __( 'Amount of times this coupon can be used by a customer', 'wp-graphql-woocommerce' ),
					),
					'limitUsageToXItems' => array(
						'type'        => 'Int',
						'description' => __( 'The number of products in your cart this coupon can apply to (for product discounts)', 'wp-graphql-woocommerce' ),
					),
					'freeShipping'       => array(
						'type'        => 'Boolean',
						'description' => __( 'Does this coupon grant free shipping?', 'wp-graphql-woocommerce' ),
					),
					'excludeSaleItems'   => array(
						'type'        => 'Boolean',
						'description' => __( 'Excluding sale items mean this coupon cannot be used on items that are on sale (or carts that contain on sale items)', 'wp-graphql-woocommerce' ),
					),
					'minimumAmount'      => array(
						'type'        => 'Float',
						'description' => __( 'Minimum spend amount that must be met before this coupon can be used', 'wp-graphql-woocommerce' ),
					),
					'maximumAmount'      => array(
						'type'        => 'Float',
						'description' => __( 'Maximum spend amount that must be met before this coupon can be used ', 'wp-graphql-woocommerce' ),
					),
					'emailRestrictions'  => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Only customers with a matching email address can use the coupon', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);

		register_graphql_field(
			'RootQuery',
			'coupon',
			array(
				'type'        => 'Coupon',
				'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'     => array( 'type' => array( 'non_null' => 'ID' ) ),
					'idType' => array(
						'type'        => 'CouponIdTypeEnum',
						'description' => __( 'Type of ID being used identify coupon', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					$coupon_id = null;
					switch ( $id_type ) {
						case 'code':
							$coupon_id = \wc_get_coupon_id_by_code( $id );
							break;
						case 'database_id':
							$coupon_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $args['id'] );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$coupon_id = absint( $id_components['id'] );
							break;
					}

					if ( empty( $coupon_id ) ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No coupon ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					} elseif ( get_post( $coupon_id )->post_type !== 'shop_coupon' ) {
						/* translators: %1$s: ID type, %2$s: ID value */
						throw new UserError( sprintf( __( 'No coupon exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
					}

					return Factory::resolve_crud_object( $coupon_id, $context );
				},
			)
		);
	}
}
