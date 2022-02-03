<?php

use WPGraphQL\Type\WPEnumType;

class OrderQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedOrderData( $order_id ) {
		$order = \wc_get_order( $order_id );

		$expected = array(
			$this->expectedObject(
				'order',
				array(
					$this->expectedField( 'id', $this->toRelayId( 'shop_order', $order_id ) ),
					$this->expectedField( 'databaseId', $order->get_id() ),
					$this->expectedField( 'currency', $this->maybe( $order->get_currency() ) ),
					$this->expectedField( 'orderVersion', $this->maybe( $order->get_version() ) ),
					$this->expectedField( 'date', $order->get_date_created()->__toString() ),
					$this->expectedField( 'modified', $order->get_date_modified()->__toString() ),
					$this->expectedField( 'status', WPEnumType::get_safe_name( $order->get_status() ) ),
					$this->expectedField( 'discountTotal', \wc_graphql_price( $order->get_discount_total(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'discountTax', \wc_graphql_price( $order->get_discount_tax(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'shippingTotal', \wc_graphql_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'shippingTax', \wc_graphql_price( $order->get_shipping_tax(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'cartTax', \wc_graphql_price( $order->get_cart_tax(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'total', \wc_graphql_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'totalTax', \wc_graphql_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'subtotal', \wc_graphql_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ),
					$this->expectedField( 'orderNumber', $order->get_order_number() ),
					$this->expectedField( 'orderKey', $order->get_order_key() ),
					$this->expectedField( 'createdVia', $this->maybe( $order->get_created_via() ) ),
					$this->expectedField( 'pricesIncludeTax', $order->get_prices_include_tax() ),
					$this->expectedField( 'parent', self::IS_NULL ),
					$this->expectedField(
						'customer',
						$this->maybe(
							array(
								$order->get_customer_id(),
								array( 'id' => $this->toRelayId( 'customer', $order->get_customer_id() ) ),
							),
							self::IS_NULL
						)
					),
					$this->expectedField( 'customerIpAddress', $this->maybe( $order->get_customer_ip_address() ) ),
					$this->expectedField( 'customerUserAgent', $this->maybe( $order->get_customer_user_agent() ) ),
					$this->expectedField( 'customerNote', $this->maybe( $order->get_customer_note() ) ),
					$this->expectedObject(
						'billing',
						array(
							$this->expectedField( 'firstName', $this->maybe( $order->get_billing_first_name() ) ),
							$this->expectedField( 'lastName', $this->maybe( $order->get_billing_last_name() ) ),
							$this->expectedField( 'company', $this->maybe( $order->get_billing_company() ) ),
							$this->expectedField( 'address1', $this->maybe( $order->get_billing_address_1() ) ),
							$this->expectedField( 'address2', $this->maybe( $order->get_billing_address_2() ) ),
							$this->expectedField( 'city', $this->maybe( $order->get_billing_city() ) ),
							$this->expectedField( 'state', $this->maybe( $order->get_billing_state() ) ),
							$this->expectedField( 'postcode', $this->maybe( $order->get_billing_postcode() ) ),
							$this->expectedField( 'country', $this->maybe( $order->get_billing_country() ) ),
							$this->expectedField( 'email', $this->maybe( $order->get_billing_email() ) ),
							$this->expectedField( 'phone', $this->maybe( $order->get_billing_phone() ) ),
						)
					),
					$this->expectedObject(
						'shipping',
						array(
							$this->expectedField( 'firstName', $this->maybe( $order->get_shipping_first_name() ) ),
							$this->expectedField( 'lastName', $this->maybe( $order->get_shipping_last_name() ) ),
							$this->expectedField( 'company', $this->maybe( $order->get_shipping_company() ) ),
							$this->expectedField( 'address1', $this->maybe( $order->get_shipping_address_1() ) ),
							$this->expectedField( 'address2', $this->maybe( $order->get_shipping_address_2() ) ),
							$this->expectedField( 'city', $this->maybe( $order->get_shipping_city() ) ),
							$this->expectedField( 'state', $this->maybe( $order->get_shipping_state() ) ),
							$this->expectedField( 'postcode', $this->maybe( $order->get_shipping_postcode() ) ),
							$this->expectedField( 'country', $this->maybe( $order->get_shipping_country() ) ),
						)
					),
					$this->expectedField( 'paymentMethod', $this->maybe( $order->get_payment_method() ) ),
					$this->expectedField( 'paymentMethodTitle', $this->maybe( $order->get_payment_method_title() ) ),
					$this->expectedField( 'transactionId', $this->maybe( $order->get_transaction_id() ) ),
					$this->expectedField( 'dateCompleted', $this->maybe( $order->get_date_completed() ) ),
					$this->expectedField( 'datePaid', $this->maybe( $order->get_date_paid() ) ),
					$this->expectedField( 'cartHash', $this->maybe( $order->get_cart_hash() ) ),
					$this->expectedField( 'shippingAddressMapUrl', $this->maybe( $order->get_shipping_address_map_url() ) ),
					$this->expectedField( 'hasBillingAddress', $order->has_billing_address() ),
					$this->expectedField( 'hasShippingAddress', $order->has_shipping_address() ),
					$this->expectedField( 'isDownloadPermitted', $order->is_download_permitted() ),
					$this->expectedField( 'needsShippingAddress', $order->needs_shipping_address() ),
					$this->expectedField( 'hasDownloadableItem', $order->has_downloadable_item() ),
					$this->expectedField( 'needsPayment', $order->needs_payment() ),
					$this->expectedField( 'needsProcessing', $order->needs_processing() ),
				)
			),
		);

		return $expected;
	}

	// tests
	public function testOrderQuery() {
		$order_id = $this->factory->order->createNew();
		$id       = $this->toRelayId( 'shop_order', $order_id );

		$query = '
			query ($id: ID!) {
				order(id: $id) {
					id
					databaseId
					currency
					orderVersion
					date
					modified
					status
					discountTotal
					discountTax
					shippingTotal
					shippingTax
					cartTax
					total
					totalTax
					subtotal
					orderNumber
					orderKey
					createdVia
					pricesIncludeTax
					parent {
						id
					}
					customer {
						id
					}
					customerIpAddress
					customerUserAgent
					customerNote
					billing {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
						email
						phone
					}
					shipping {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
					}
					paymentMethod
					paymentMethodTitle
					transactionId
					dateCompleted
					datePaid
					cartHash
					shippingAddressMapUrl
					hasBillingAddress
					hasShippingAddress
					isDownloadPermitted
					needsShippingAddress
					hasDownloadableItem
					downloadableItems {
						nodes {
							url
							accessExpires
							downloadId
							downloadsRemaining
							name
							product {
								databaseId
							}
							download {
								downloadId
							}
						}
					}
					needsPayment
					needsProcessing
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Tests query as customer, should return "null" because the customer isn't authorized.
		 */
		$this->loginAsCustomer();
		$variables = array( 'id' => $id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array( $this->expectedField( 'order', self::IS_NULL ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests query as shop manager
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = $this->expectedOrderData( $order_id );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testOrderQueryAndIds() {
		$order_id = $this->factory->order->createNew();
		$id       = $this->toRelayId( 'shop_order', $order_id );

		$query = '
			query ($id: ID!, $idType: OrderIdTypeEnum ) {
				order(id: $id, idType: $idType) {
					id
				}
			}
		';

		// Must be an "shop_manager" or "admin" to query orders not owned by the user.
		$this->loginAsShopManager();

		/**
		 * Assertion One
		 *
		 * Tests "ID" ID type.
		 */
		$variables = array(
			'id'     => $id,
			'idType' => 'ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array( $this->expectedField( 'order.id', $id ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "DATABASE_ID" ID type.
		 */
		$variables = array(
			'id'     => $order_id,
			'idType' => 'DATABASE_ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests "ORDER_NUMBER" ID type
		 */
		$variables = array(
			'id'     => $this->factory->order->get_order_key( $order_id ),
			'idType' => 'ORDER_NUMBER',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testOrdersQueryAndWhereArgs() {
		// Create and delete scrap/old order(s).
		$this->factory->order->createNew();
		$query      = new \WC_Order_Query();
		$old_orders = $query->get_orders();
		foreach ( $old_orders as $order ) {
			$this->logData( 'Order ' . $order->get_id() . ' deleted.' );
			$this->factory->order->delete_order( $order );
		}

		// Create order for query response.
		$customer = $this->factory->customer->create();
		$product  = $this->factory->product->createSimple();
		$orders   = array(
			$this->factory->order->createNew(
				array(),
				array(
					'line_items' => array(
						array(
							'product' => $product,
							'qty'     => 4,
						),
					),
				)
			),
			$this->factory->order->createNew(
				array(
					'status'      => 'completed',
					'customer_id' => $customer,
				),
				array(
					'line_items' => array(
						array(
							'product' => $product,
							'qty'     => 2,
						),
					),
				)
			),
		);

		$query = '
			query ($statuses: [OrderStatusEnum], $customerId: Int, $customersIn: [Int] $productId: Int) {
				orders(where: {
					statuses: $statuses,
					customerId: $customerId,
					customersIn: $customersIn,
					productId: $productId,
					orderby: { field: MENU_ORDER, order: ASC }
				}) {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Tests query with no without required capabilities
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array( $this->expectedField( 'orders.nodes', array() ) );

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Tests query with required capabilities
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array(
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $orders[0] ) ) ),
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $orders[1] ) ) ),
			$this->not()->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $old_orders[0] ) ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Three
		 *
		 * Tests "statuses" where argument
		 */
		$variables = array( 'statuses' => array( 'COMPLETED' ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $orders[1] ) ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Four
		 *
		 * Tests "customerId" where argument
		 */
		$variables = array( 'customerId' => $customer );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Five
		 *
		 * Tests "customerIn" where argument
		 */
		$variables = array( 'customersIn' => array( $customer ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Six
		 *
		 * Tests "productId" where argument
		 */
		$variables = array( 'productId' => $product );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Seven
		 *
		 * Tests `orders` query as existing customer, should return customer's
		 * orders only
		 */
		$this->loginAs( $customer );
		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, $expected );
		$this->clearLoaderCache( 'wc_post' );
	}
}
