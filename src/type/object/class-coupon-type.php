<?php
/**
 * WPObject Type - Coupon_Type
 *
 * Registers Coupon WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Coupon;

/**
 * Class Coupon_Type
 */
class Coupon_Type {
	/**
	 * Register Coupon type and queries to the WPGraphQL schema
	 */
	public static function register() {
		wc_register_graphql_object_type(
			'Coupon',
			array(
				'description'       => __( 'A coupon object', 'wp-graphql-woocommerce' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
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
				'resolve_node'      => function( $node, $id, $type, $context ) {
					if ( 'shop_coupon' === $type ) {
						$node = Factory::resolve_crud_object( $id, $context );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Coupon::class ) ) {
						$type = 'Coupon';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'coupon',
			array(
				'type'        => 'Coupon',
				'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id' => array(
						'type' => array(
							'non_null' => 'ID',
						),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );
					if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}
					$coupon_id = absint( $id_components['id'] );
					return Factory::resolve_crud_object( $coupon_id, $context );
				},
			)
		);

		$post_by_args = array(
			'id'       => array(
				'type'        => 'ID',
				'description' => __( 'Get the coupon by its global ID', 'wp-graphql-woocommerce' ),
			),
			'couponId' => array(
				'type'        => 'Int',
				'description' => __( 'Get the coupon by its database ID', 'wp-graphql-woocommerce' ),
			),
			'code'     => array(
				'type'        => 'String',
				'description' => __( 'Get the coupon by its code', 'wp-graphql-woocommerce' ),
			),
		);

		register_graphql_field(
			'RootQuery',
			'couponBy',
			array(
				'type'        => 'Coupon',
				'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
				'args'        => $post_by_args,
				'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$coupon_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}
						$coupon_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['couponId'] ) ) {
						$coupon_id = absint( $args['couponId'] );
					} elseif ( ! empty( $args['code'] ) ) {
						$coupon_id = \wc_get_coupon_id_by_code( $args['code'] );
					}

					$coupon = Factory::resolve_crud_object( $coupon_id, $context );
					if ( get_post( $coupon_id )->post_type !== 'shop_coupon' ) {
						/* translators: no coupon found error message */
						throw new UserError( sprintf( __( 'No coupon exists with this id: %1$s', 'wp-graphql-woocommerce' ), $args['id'] ) );
					}

					return $coupon;
				},
			)
		);
	}
}
