<?php
/**
 * Registers WooCommerce fields on the RootQuery object.
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.6.0
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use Automattic\WooCommerce\StoreApi\Utilities\ProductQueryFilters;
use Automattic\WooCommerce\Utilities\OrderUtil;
use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

/**
 * Class - Root_Query
 */
class Root_Query {
	/**
	 * Registers WC-related root queries.
	 *
	 * @return void
	 */
	public static function register_fields() {
		register_graphql_fields(
			'RootQuery',
			[
				'cart'             => [
					'type'        => 'Cart',
					'args'        => [
						'recalculateTotals' => [
							'type'        => 'Boolean',
							'description' => __( 'Should cart totals be recalculated.', 'wp-graphql-woocommerce' ),
						],
					],
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $_, $args ) {
						$token_invalid = apply_filters( 'graphql_woocommerce_session_token_errors', null );
						if ( $token_invalid ) {
							throw new UserError( $token_invalid );
						}

						$cart = Factory::resolve_cart();
						if ( ! empty( $args['recalculateTotals'] ) ) {
							$cart->calculate_totals();
						}

						return $cart;
					},
				],
				'cartItem'         => [
					'type'        => 'CartItem',
					'args'        => [
						'key' => [
							'type' => [ 'non_null' => 'ID' ],
						],
					],
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source, array $args ) {
						$item = Factory::resolve_cart()->get_cart_item( $args['key'] );
						if ( empty( $item ) || empty( $item['key'] ) ) {
							throw new UserError( __( 'Failed to retrieve cart item.', 'wp-graphql-woocommerce' ) );
						}

						return $item;
					},
				],
				'cartFee'          => [
					'type'        => 'CartFee',
					'args'        => [
						'id' => [
							'type' => [ 'non_null' => 'ID' ],
						],
					],
					'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source, array $args ) {
						$fees   = Factory::resolve_cart()->get_fees();
						$fee_id = $args['id'];

						if ( empty( $fees[ $fee_id ] ) ) {
							throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
						}

						return $fees[ $fee_id ];
					},
				],
				'coupon'           => [
					'type'        => 'Coupon',
					'description' => __( 'A coupon object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [ 'type' => [ 'non_null' => 'ID' ] ],
						'idType' => [
							'type'        => 'CouponIdTypeEnum',
							'description' => __( 'Type of ID being used identify coupon', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
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

						// Check if user authorized to view coupon.
						/**
						 * Get coupon post type.
						 *
						 * @var \WP_Post_Type $post_type
						 */
						$post_type     = get_post_type_object( 'shop_coupon' );
						$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
						if ( ! $is_authorized ) {
							return null;
						}

						if ( empty( $coupon_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No coupon ID was found corresponding to the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						$coupon = get_post( $coupon_id );
						if ( ! is_object( $coupon ) || 'shop_coupon' !== $coupon->post_type ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No coupon exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						return Factory::resolve_crud_object( $coupon_id, $context );
					},
				],
				'customer'         => [
					'type'        => 'Customer',
					'description' => __( 'A customer object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'         => [
							'type'        => 'ID',
							'description' => __( 'Get the customer by their global ID', 'wp-graphql-woocommerce' ),
						],
						'customerId' => [
							'type'        => 'Int',
							'description' => __( 'Get the customer by their database ID', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						$current_user_id = get_current_user_id();

						// Default the customer to the current user.
						$customer_id = $current_user_id;

						// If a customer ID has been provided, resolve to that ID instead.
						if ( ! empty( $args['id'] ) ) {
							$id_components = Relay::fromGlobalId( $args['id'] );
							if ( ! isset( $id_components['id'] ) || ! absint( $id_components['id'] ) ) {
								throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
							}

							$customer_id = absint( $id_components['id'] );
						} elseif ( ! empty( $args['customerId'] ) ) {
							$customer_id = absint( $args['customerId'] );
						}

						// If a user does not have the ability to list users, they can only view their own customer object.
						$unauthorized = ! empty( $customer_id )
							&& ! current_user_can( 'list_users' )
							&& $current_user_id !== $customer_id;
						if ( $unauthorized ) {
							throw new UserError( __( 'Not authorized to access this customer', 'wp-graphql-woocommerce' ) );
						}

						// If we have a customer ID, resolve to that customer.
						if ( $customer_id ) {
							return Factory::resolve_customer( $customer_id, $context );
						}

						// Resolve to the session customer.
						return Factory::resolve_session_customer();
					},
				],
				'order'            => [
					'type'        => 'Order',
					'description' => __( 'A order object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the order', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'OrderIdTypeEnum',
							'description' => __( 'Type of ID being used identify order', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$order_id = null;
						switch ( $id_type ) {
							case 'order_key':
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
						}

						if ( 'shop_order' !== OrderUtil::get_order_type( $order_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No order exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						// Check if user authorized to view order.
						/**
						 * Get order post type.
						 *
						 * @var \WP_Post_Type $post_type
						 */
						$post_type     = get_post_type_object( 'shop_order' );
						$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
						if ( ! $is_authorized && get_current_user_id() ) {
							/** @var \WC_Order[] $orders */
							$orders = wc_get_orders(
								[
									'type'          => 'shop_order',
									'post__in'      => [ $order_id ],
									'customer_id'   => get_current_user_id(),
									'no_rows_found' => true,
									'return'        => 'ids',
								]
							);

							if ( in_array( $order_id, $orders, true ) ) {
								$is_authorized = true;
							}
						}

						// Throw if authorized to view order.
						if ( ! $is_authorized ) {
							throw new UserError( __( 'Not authorized to access this order', 'wp-graphql-woocommerce' ) );
						}

						return Factory::resolve_crud_object( $order_id, $context );
					},
				],
				'productVariation' => [
					'type'        => 'ProductVariation',
					'description' => __( 'A product variation object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the product variation', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'ProductVariationIdTypeEnum',
							'description' => __( 'Type of ID being used identify product variation', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
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
						}

						$variation = get_post( $variation_id );
						if ( ! is_object( $variation ) || 'product_variation' !== $variation->post_type ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product variation exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						return Factory::resolve_crud_object( $variation_id, $context );
					},
				],
				'refund'           => [
					'type'        => 'Refund',
					'description' => __( 'A refund object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => [ 'non_null' => 'ID' ],
							'description' => __( 'The ID for identifying the refund', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'RefundIdTypeEnum',
							'description' => __( 'Type of ID being used identify refund', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
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
						}

						if ( 'shop_order_refund' !== OrderUtil::get_order_type( $refund_id ) ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No refund exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $id ) );
						}

						// Check if user authorized to view order.
						/**
						 * Get refund post type.
						 *
						 * @var \WP_Post_Type $post_type
						 */
						$post_type     = get_post_type_object( 'shop_order_refund' );
						$is_authorized = current_user_can( $post_type->cap->edit_others_posts );
						if ( get_current_user_id() ) {
							$refund = \wc_get_order( $refund_id );
							if ( ! is_object( $refund ) || ! is_a( $refund, \WC_Order_Refund::class ) ) {
								throw new UserError( __( 'Failed to retrieve refund', 'wp-graphql-woocommerce' ) );
							}
							$order_id = $refund->get_parent_id();

							/** @var \WC_Order[] $orders */
							$orders = wc_get_orders(
								[
									'type'          => 'shop_order',
									'post__in'      => [ $order_id ],
									'customer_id'   => get_current_user_id(),
									'no_rows_found' => true,
									'return'        => 'ids',
								]
							);

							if ( in_array( $order_id, $orders, true ) ) {
								$is_authorized = true;
							}
						}//end if

						// Throw if authorized to view refund.
						if ( ! $is_authorized ) {
							throw new UserError( __( 'Not authorized to access this refund', 'wp-graphql-woocommerce' ) );
						}

						return Factory::resolve_crud_object( $refund_id, $context );
					},
				],
				'shippingMethod'   => [
					'type'        => 'ShippingMethod',
					'description' => __( 'A shipping method object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the shipping method', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'ShippingMethodIdTypeEnum',
							'description' => __( 'Type of ID being used identify product variation', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						if ( ! \wc_rest_check_manager_permissions( 'shipping_methods', 'read' ) ) {
							throw new UserError( __( 'Sorry, you cannot view shipping methods.', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
						}

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
				],
				'shippingZone'     => [
					'type'        => 'ShippingZone',
					'description' => __( 'A shipping zone object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the shipping zone', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'ShippingZoneIdTypeEnum',
							'description' => __( 'Type of ID being used identify shipping zone', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						if ( ! \wc_shipping_enabled() ) {
							throw new UserError( __( 'Shipping is disabled.', 'wp-graphql-woocommerce' ), 404 );
						}

						if ( ! \wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
							throw new UserError( __( 'Permission denied.', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
						}

						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$zone_id = null;
						switch ( $id_type ) {
							case 'database_id':
								$zone_id = $id;
								break;
							case 'global_id':
							default:
								$id_components = Relay::fromGlobalId( $id );
								if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
									throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
								}
								$zone_id = $id_components['id'];
								break;
						}

						return $context->get_loader( 'shipping_zone' )->load( $zone_id );
					},
				],
				'taxRate'          => [
					'type'        => 'TaxRate',
					'description' => __( 'A tax rate object', 'wp-graphql-woocommerce' ),
					'args'        => [
						'id'     => [
							'type'        => 'ID',
							'description' => __( 'The ID for identifying the tax rate', 'wp-graphql-woocommerce' ),
						],
						'idType' => [
							'type'        => 'TaxRateIdTypeEnum',
							'description' => __( 'Type of ID being used identify tax rate', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args, AppContext $context ) {
						if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
							throw new UserError( __( 'Sorry, you cannot view tax rates.', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
						}
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
				],
				'countries'        => [
					'type'        => [ 'list_of' => 'CountriesEnum' ],
					'description' => __( 'Countries', 'wp-graphql-woocommerce' ),
					'resolve'     => static function () {
						$wc_countries = new \WC_Countries();
						$countries    = $wc_countries->get_countries();

						return array_keys( $countries );
					},
				],
				'allowedCountries' => [
					'type'        => [ 'list_of' => 'CountriesEnum' ],
					'description' => __( 'Countries that the store sells to', 'wp-graphql-woocommerce' ),
					'resolve'     => static function () {
						$wc_countries = new \WC_Countries();
						$countries    = $wc_countries->get_allowed_countries();

						return array_keys( $countries );
					},
				],
				'countryStates'    => [
					'type'        => [ 'list_of' => 'CountryState' ],
					'args'        => [
						'country' => [
							'type'        => [ 'non_null' => 'CountriesEnum' ],
							'description' => __( 'Target country', 'wp-graphql-woocommerce' ),
						],
					],
					'description' => __( 'Countries that the store sells to', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $_, $args ) {
						$country      = $args['country'];
						$wc_countries = new \WC_Countries();
						$states       = $wc_countries->get_shipping_country_states();

						if ( ! empty( $states ) && ! empty( $states[ $country ] ) ) {
							$formatted_states = [];
							foreach ( $states[ $country ] as $code => $name ) {
								$formatted_states[] = compact( 'name', 'code' );
							}

							return $formatted_states;
						}

						return [];
					},
				],
				'collectionStats'  => [
					'type'        => 'CollectionStats',
					'args'        => [
						'calculatePriceRange'        => [
							'type'        => 'Boolean',
							'description' => __( 'If true, calculates the minimum and maximum product prices for the collection.', 'wp-graphql-woocommerce' ),
						],
						'calculateRatingCounts'      => [
							'type'        => 'Boolean',
							'description' => __( 'If true, calculates rating counts for products in the collection.', 'wp-graphql-woocommerce' ),
						],
						'calculateStockStatusCounts' => [
							'type'        => 'Boolean',
							'description' => __( 'If true, calculates stock counts for products in the collection.', 'wp-graphql-woocommerce' ),
						],
						'taxonomies'                 => [
							'type' => [ 'list_of' => 'CollectionStatsQueryInput' ],
						],
						'where'                      => [
							'type' => 'CollectionStatsWhereArgs',
						],
					],
					'description' => __( 'Statistics for a product taxonomy query', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $_, $args ) {
						/** @var array<string, mixed> $data */
						$data    = [
							'min_price'           => null,
							'max_price'           => null,
							'attribute_counts'    => null,
							'stock_status_counts' => null,
							'rating_counts'       => null,
						];
						$filters = new ProductQueryFilters();

						// Process client-side filters.
						$request = Collection_Stats_Type::prepare_rest_request( $args['where'] ?? [] );

						// Format taxonomies.
						if ( ! empty( $args['taxonomies'] ) ) {
							$calculate_attribute_counts = [];
							foreach ( $args['taxonomies'] as $attribute_to_count ) {
								$attribute = [ 'taxonomy' => $attribute_to_count['taxonomy'] ];
								// Set the query type.
								if ( ! empty( $attribute_to_count['relation'] ) ) {
									$attribute['query_type'] = strtolower( $attribute_to_count['relation'] );
								}

								// Add the attribute to the list of attributes to count.
								$calculate_attribute_counts[] = $attribute;
							}

							// Set the attribute counts to calculate.
							$request->set_param( 'calculate_attribute_counts', $calculate_attribute_counts );
						}

						$request->set_param( 'calculate_price_range', $args['calculatePriceRange'] ?? false );
						$request->set_param( 'calculate_stock_status_counts', $args['calculateStockStatusCounts'] ?? false );
						$request->set_param( 'calculate_rating_counts', $args['calculateRatingCounts'] ?? false );

						if ( ! empty( $request['calculate_price_range'] ) ) {
							/**
							 * A Rest request object for external filtering
							 *
							 * @var \WP_REST_Request $filter_request
							 */
							$filter_request = clone $request;
							$filter_request->set_param( 'min_price', null );
							$filter_request->set_param( 'max_price', null );

							/** @var object{min_price: float, max_price: float} */
							$price_results     = $filters->get_filtered_price( $filter_request );
							$data['min_price'] = $price_results->min_price;
							$data['max_price'] = $price_results->max_price;
						}

						if ( ! empty( $request['calculate_stock_status_counts'] ) ) {
							/**
							 * A Rest request object for external filtering
							 *
							 * @var \WP_REST_Request $filter_request
							 */
							$filter_request = clone $request;
							$counts         = $filters->get_stock_status_counts( $filter_request );

							$data['stock_status_counts'] = [];

							foreach ( $counts as $key => $value ) {
								$data['stock_status_counts'][] = (object) [
									'status' => $key,
									'count'  => $value,
								];
							}
						}

						if ( ! empty( $request['calculate_attribute_counts'] ) ) {
							$taxonomy__or_queries  = [];
							$taxonomy__and_queries = [];
							foreach ( $request['calculate_attribute_counts'] as $attributes_to_count ) {
								if ( ! isset( $attributes_to_count['taxonomy'] ) ) {
									continue;
								}

								if ( empty( $attributes_to_count['query_type'] ) || 'or' === $attributes_to_count['query_type'] ) {
									$taxonomy__or_queries[] = $attributes_to_count['taxonomy'];
								} else {
									$taxonomy__and_queries[] = $attributes_to_count['taxonomy'];
								}
							}

							$data['attribute_counts'] = [];
							if ( ! empty( $taxonomy__or_queries ) ) {
								foreach ( $taxonomy__or_queries as $taxonomy ) {
									/**
									 * A Rest request object for external filtering
									 *
									 * @var \WP_REST_Request $filter_request
									 */
									$filter_request    = clone $request;
									$filter_attributes = $filter_request->get_param( 'attributes' );

									if ( ! empty( $filter_attributes ) ) {
										$filter_attributes = array_filter(
											$filter_attributes,
											static function ( $query ) use ( $taxonomy ) {
												return $query['attribute'] !== $taxonomy;
											}
										);
									}

									$filter_request->set_param( 'attributes', $filter_attributes );
									$counts = $filters->get_attribute_counts( $filter_request, [ $taxonomy ] );

									$data['attribute_counts'][ $taxonomy ] = [];
									foreach ( $counts as $key => $value ) {
										$data['attribute_counts'][ $taxonomy ][] = (object) [
											'taxonomy' => $taxonomy,
											'termId'   => $key,
											'count'    => $value,
										];
									}
								}
							}

							if ( ! empty( $taxonomy__and_queries ) ) {
								$counts = $filters->get_attribute_counts( $request, $taxonomy__and_queries );

								foreach ( $taxonomy__and_queries as $taxonomy ) {
									$data['attribute_counts'][ $taxonomy ] = [];
									foreach ( $counts as $key => $value ) {
										$data['attribute_counts'][ $taxonomy ][] = (object) [
											'taxonomy' => $taxonomy,
											'termId'   => $key,
											'count'    => $value,
										];
									}
								}
							}
						}

						if ( ! empty( $request['calculate_rating_counts'] ) ) {
							/**
							 * A Rest request object for external filtering
							 *
							 * @var \WP_REST_Request $filter_request
							 */
							$filter_request        = clone $request;
							$counts                = $filters->get_rating_counts( $filter_request );
							$data['rating_counts'] = [];

							foreach ( $counts as $key => $value ) {
								$data['rating_counts'][] = (object) [
									'rating' => $key,
									'count'  => $value,
								];
							}
						}

						return $data;
					},
				],
			]
		);

		// Product queries.
		$unsupported_type_enabled = woographql_setting( 'enable_unsupported_product_type', 'off' );

		$product_type_keys = array_keys( WooGraphQL::get_enabled_product_types() );
		if ( 'on' === $unsupported_type_enabled ) {
			$product_type_keys[] = 'unsupported';
		}

		$product_type_keys = apply_filters( 'woographql_register_product_queries', $product_type_keys );

		$product_types = WooGraphQL::get_enabled_product_types();
		if ( 'on' === $unsupported_type_enabled ) {
			$product_types['unsupported'] = WooGraphQL::get_supported_product_type();
		}

		foreach ( $product_type_keys as $type_key ) {
			$field_name = "{$type_key}Product";
			$type_name  = $product_types[ $type_key ] ?? null;

			if ( empty( $type_name ) ) {
				continue;
			}

			register_graphql_field(
				'RootQuery',
				$field_name,
				[
					'type'              => $type_name,
					/* translators: Product type slug */
					'description'       => sprintf( __( 'A %s product object', 'wp-graphql-woocommerce' ), $type_key ),
					'deprecationReason' => 'Use "product" instead.',
					'args'              => [
						'id'     => [
							'type'        => 'ID',
							'description' => sprintf(
								/* translators: %s: product type */
								__( 'The ID for identifying the %s product', 'wp-graphql-woocommerce' ),
								$type_name
							),
						],
						'idType' => [
							'type'        => 'ProductIdTypeEnum',
							'description' => __( 'Type of ID being used identify product', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'           => static function ( $source, array $args, AppContext $context ) use ( $type_key, $unsupported_type_enabled ) {
						$id      = isset( $args['id'] ) ? $args['id'] : null;
						$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

						$product_id = null;
						switch ( $id_type ) {
							case 'sku':
								$product_id = \wc_get_product_id_by_sku( $id );
								break;
							case 'slug':
								$post       = get_page_by_path( $id, OBJECT, 'product' );
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
						}

						if ( \WC()->product_factory->get_product_type( $product_id ) !== $type_key && 'off' === $unsupported_type_enabled ) {
							/* translators: Invalid product type message %1$s: Product ID, %2$s: Product type */
							throw new UserError( sprintf( __( 'This product of ID %1$s is not a %2$s product', 'wp-graphql-woocommerce' ), $product_id, $type_key ) );
						}

						$product = get_post( $product_id );
						if ( ! is_object( $product ) || 'product' !== $product->post_type ) {
							/* translators: %1$s: ID type, %2$s: ID value */
							throw new UserError( sprintf( __( 'No product exists with the %1$s: %2$s', 'wp-graphql-woocommerce' ), $id_type, $product_id ) );
						}

						return Factory::resolve_crud_object( $product_id, $context );
					},
				]
			);
		}//end foreach
	}
}
