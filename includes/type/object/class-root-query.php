<?php
/**
 * Registers WooCommerce fields on the RootQuery object.
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.6.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Root_Query
 */
class Root_Query {
	/**
	 * Return WooCommerce GraphQL queries.
	 */
	public static function register_fields() {
		register_graphql_fields(
			'RootQuery',
			array(
				'cart'             => array(
					'type'        => 'Cart',
					'args'        => array(
						'recalculateTotals' => array(
							'type'        => 'Boolean',
							'description' => __( 'Should cart totals be recalculated.', 'wp-graphql-woocommerce' ),
						),
					),
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $_, $args ) {
						$token_invalid = apply_filters( 'graphql_woocommerce_session_token_errors', null );
						if ( $token_invalid ) {
							throw new UserError( $token_invalid );
						}

						$cart = Factory::resolve_cart();
						if ( ! empty( $args['recalculateTotals'] ) && $args['recalculateTotals'] ) {
							$cart->calculate_totals();
						}

						return $cart;
					},
				),
				'cartItem'         => array(
					'type'        => 'CartItem',
					'args'        => array(
						'key' => array(
							'type' => array( 'non_null' => 'ID' ),
						),
					),
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args, AppContext $context ) {
						$item = Factory::resolve_cart_item( $args['key'], $context );
						if ( empty( $item ) ) {
							throw new UserError( __( 'The key input is invalid', 'wp-graphql-woocommerce' ) );
						}

						return $item;
					},
				),
				'cartFee'          => array(
					'type'        => 'CartFee',
					'args'        => array(
						'id' => array(
							'type' => array( 'non_null' => 'ID' ),
						),
					),
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source, array $args ) {
						$fee = Factory::resolve_cart_fee( $args['id'] );
						if ( empty( $fee ) ) {
							throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
						}

						return $fee;
					},
				),
				'coupon'           => array(
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
						$id      = isset( $args['id'] ) ? $args['id'] : null;
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
				),
				'customer'         => array(
					'type'        => 'Customer',
					'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'         => array(
							'type'        => 'ID',
							'description' => __( 'Get the customer by their global ID', 'wp-graphql-woocommerce' ),
						),
						'customerId' => array(
							'type'        => 'Int',
							'description' => __( 'Get the customer by their database ID', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context ) {
						$customer_id = 0;
						if ( ! empty( $args['id'] ) ) {
							$id_components = Relay::fromGlobalId( $args['id'] );
							if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
								throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
							}

							$customer_id = absint( $id_components['id'] );
						} elseif ( ! empty( $args['customerId'] ) ) {
							$customer_id = absint( $args['customerId'] );
						}

						$authorized = ! empty( $customer_id )
							&& ! current_user_can( 'list_users' )
							&& get_current_user_id() !== $customer_id;
						if ( $authorized ) {
							throw new UserError( __( 'Not authorized to access this customer', 'wp-graphql-woocommerce' ) );
						}

						if ( $customer_id ) {
							return Factory::resolve_customer( $customer_id, $context );
						}

						return Factory::resolve_session_customer();
					},
				),
				'order'            => array(
					'type'        => 'Order',
					'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'       => array(
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the order', 'wp-graphql-woocommerce' ),
						),
						'idType'   => array(
							'type'        => 'OrderIdTypeEnum',
							'description' => __( 'Type of ID being used identify order', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$order_id = null;
						switch ( $id_type ) {
							case 'order_number':
								$order_id = \wc_get_order_id_by_order_key( $id );
								break;
							case 'database_id':
								$order_id = absint( $id );
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$order_id = absint( $id_components['id'] );
								break;
						}

						if ( empty( $order_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No order ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						} elseif ( get_post( $order_id )->post_type !== 'shop_order' ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No order exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						// Check if user authorized to view order.
						$post_type = get_post_type_object( 'shop_order' );
						$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
						if ( get_current_user_id() ) {
							$orders = wc_get_orders(
								array(
									'type'          => 'shop_order',
									'post__in'      => array( $order_id ),
									'customer_id'   => get_current_user_id(),
									'no_rows_found' => true,
									'return'        => 'ids',
								)
							);

							if ( in_array( $order_id, $orders, true ) ) {
								$is_authorized = true;
							}
						}

						$order = $is_authorized ? Factory::resolve_crud_object( $order_id, $context ) : null;

						return $order;
					},
				),
				'productVariation' => array(
					'type'        => 'ProductVariation',
					'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'          => array(
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the product variation', 'wp-graphql-woocommerce' ),
						),
						'idType'      => array(
							'type'        => 'ProductVariationIdTypeEnum',
							'description' => __( 'Type of ID being used identify product variation', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$variation_id = null;
						switch ( $id_type ) {
							case 'database_id':
								$variation_id = absint( $id );
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$variation_id = absint( $id_components['id'] );
								break;
						}

						if ( empty( $variation_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product variation ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						} elseif ( get_post( $variation_id )->post_type !== 'product_variation' ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product variation exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						return Factory::resolve_crud_object( $variation_id, $context );
					},
				),
				'refund'           => array(
					'type'        => 'Refund',
					'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'     => array(
							'type'        => array( 'non_null' => 'ID' ),
							'description' => __( 'The ID for identifying the refund', 'wp-graphql-woocommerce' ),
						),
						'idType' => array(
							'type'        => 'RefundIdTypeEnum',
							'description' => __( 'Type of ID being used identify refund', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$refund_id = null;
						switch ( $id_type ) {
							case 'database_id':
								$refund_id = absint( $id );
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$refund_id = absint( $id_components['id'] );
								break;
						}

						if ( empty( $refund_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No refund ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						} elseif ( get_post( $refund_id )->post_type !== 'shop_order_refund' ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No refund exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						// Check if user authorized to view order.
						$post_type = get_post_type_object( 'shop_order_refund' );
						$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
						if ( get_current_user_id() ) {
							$refund   = \wc_get_order( $refund_id );
							$order_id = $refund->get_parent_id();
							$orders   = wc_get_orders(
								array(
									'type'          => 'shop_order',
									'post__in'      => array( $order_id ),
									'customer_id'   => get_current_user_id(),
									'no_rows_found' => true,
									'return'        => 'ids',
								)
							);

							if ( in_array( $order_id, $orders, true ) ) {
								$is_authorized = true;
							}
						}

						$refund = $is_authorized ? Factory::resolve_crud_object( $refund_id, $context ) : null;

						return $refund;
					},
				),
				'shippingMethod'   => array(
					'type'        => 'ShippingMethod',
					'description' => __( 'A shipping method object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'       => array(
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the shipping method', 'wp-graphql-woocommerce' ),
						),
						'idType'   => array(
							'type'        => 'ShippingMethodIdTypeEnum',
							'description' => __( 'Type of ID being used identify product variation', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$method_id = null;
						switch ( $id_type ) {
							case 'database_id':
								$method_id = $id;
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$method_id = $id_components['id'];
								break;
						}

						return Factory::resolve_shipping_method( $method_id );
					},
				),
				'taxRate'          => array(
					'type'        => 'TaxRate',
					'description' => __( 'A tax rate object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'     => array(
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the tax rate', 'wp-graphql-woocommerce' ),
						),
						'idType' => array(
							'type'        => 'TaxRateIdTypeEnum',
							'description' => __( 'Type of ID being used identify tax rate', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$rate_id = null;
						switch ( $id_type ) {
							case 'database_id':
								$rate_id = absint( $id );
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$rate_id = absint( $id_components['id'] );
								break;
						}

						return Factory::resolve_tax_rate( $rate_id, $context );
					},
				),
			)
		);

		// Product queries
		$product_types = apply_filters(
			'woographql_register_product_queries',
			array_keys( \WP_GraphQL_WooCommerce::get_enabled_product_types() )
		);
		foreach( $product_types as $type ) {
			$field_name = "{$type}Product";
			$type_name  = ucfirst( $type ) . 'Product';
			register_graphql_field(
				'RootQuery',
				$field_name,
				array(
					'type'        => $type_name,
					'description' => __( 'A simple product object', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'id'        => array(
							'type'        => 'ID',
							'description' => sprintf(
								/* translators: %s: product type */
								__( 'The ID for identifying the %s product', 'wp-graphql-woocommerce' ),
								$type
							),
						),
						'idType'    => array(
							'type'        => 'ProductIdTypeEnum',
							'description' => __( 'Type of ID being used identify product', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function ( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $type ) {
						$id = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$product_id = null;
						switch ( $id_type ) {
							case 'sku':
								$product_id = \wc_get_product_id_by_sku( $id );
								break;
							case 'slug':
								$post = get_page_by_path( $id, OBJECT, 'product' );
								$product_id = ! empty( $post ) ? absint( $post->ID ) : 0;
								break;
							case 'database_id':
								$product_id = absint( $id );
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$product_id = absint( $id_components['id'] );
								break;
						}

						if ( empty( $product_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $product_id ) );
						} elseif ( \WC()->product_factory->get_product_type( $product_id ) !== $type ) {
							/* translators: Invalid product type message %1$s: Product ID, %2$s: Product type */
							throw new UserError( sprintf( __( 'This product of ID %1$s is not a %2$s product', 'wp-graphql-woocommerce' ), $product_id, $type ) );
						} elseif ( get_post( $product_id )->post_type !== 'product' ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $product_id ) );
						}

						$product = Factory::resolve_crud_object( $product_id, $context );

						return $product;
					},
				)
			);
		}
	}
}
