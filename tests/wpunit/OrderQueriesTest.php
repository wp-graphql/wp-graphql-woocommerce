<?php

use WPGraphQL\Type\WPEnumType;

class OrderQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedOrderData( $order_id ) {
		$order = \wc_get_order( $order_id );

		$expected = array(
			$this->expectedObject( 'order.id', $this->toRelayId( 'shop_order', $order_id ) ),
			$this->expectedObject( 'order.databaseId', $order->get_id() ),
			$this->expectedObject( 'order.currency', $this->maybe( $order->get_currency(), 'null' ) ),
			$this->expectedObject( 'order.orderVersion', $this->maybe( $order->get_version(), 'null' ) ),
            $this->expectedObject( 'order.date', $order->get_date_created()->__toString() ),
            $this->expectedObject( 'order.modified', $order->get_date_modified()->__toString() ),
			$this->expectedObject( 'order.status', WPEnumType::get_safe_name( $order->get_status() ) ),
			$this->expectedObject( 'order.discountTotal', \wc_graphql_price(  $order->get_discount_total(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.discountTax', \wc_graphql_price( $order->get_discount_tax(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.shippingTotal', \wc_graphql_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.shippingTax', \wc_graphql_price( $order->get_shipping_tax(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.cartTax', \wc_graphql_price( $order->get_cart_tax(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.total', \wc_graphql_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.totalTax', \wc_graphql_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.subtotal', \wc_graphql_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ),
			$this->expectedObject( 'order.orderNumber', $order->get_order_number() ),
			$this->expectedObject( 'order.orderKey', $order->get_order_key() ),
			$this->expectedObject( 'order.createdVia', $this->maybe( $order->get_created_via(), 'null' ) ),
			$this->expectedObject( 'order.pricesIncludeTax', $order->get_prices_include_tax() ),
			$this->expectedObject( 'order.parent', 'null' ),
			$this->expectedObject(
				'order.customer',
				$this->maybe(
					array(
						$order->get_customer_id(),
						array( 'id' => $this->toRelayId( 'customer', $order->get_customer_id() ) ),
					),
					'null'
				)
			),
			$this->expectedObject( 'order.customerIpAddress', $this->maybe( $order->get_customer_ip_address(), 'null' ) ),
			$this->expectedObject( 'order.customerUserAgent', $this->maybe( $order->get_customer_user_agent(), 'null' ) ),
			$this->expectedObject( 'order.customerNote', $this->maybe( $order->get_customer_note(), 'null' ) ),
			$this->expectedObject(
				'order.billing',
				array(
					'firstName' => $order->get_billing_first_name(),
					'lastName'  => $this->maybe( $order->get_billing_last_name(), null ),
					'company'   => $this->maybe( $order->get_billing_company(), null ),
					'address1'  => $this->maybe( $order->get_billing_address_1(), null ),
					'address2'  => $this->maybe( $order->get_billing_address_2(), null ),
					'city'      => $this->maybe( $order->get_billing_city(), null ),
					'state'     => $this->maybe( $order->get_billing_state(), null ),
					'postcode'  => $this->maybe( $order->get_billing_postcode(), null ),
					'country'   => $this->maybe( $order->get_billing_country(), null ),
					'email'     => $this->maybe( $order->get_billing_email(), null ),
					'phone'     => $this->maybe( $order->get_billing_phone(), null ),
				)
			),
			$this->expectedObject(
				'order.shipping',
				array(
					'firstName' => $this->maybe( $order->get_billing_first_name(), null ),
					'lastName'  => $this->maybe( $order->get_billing_last_name(), null ),
					'company'   => $this->maybe( $order->get_billing_company(), null ),
					'address1'  => $this->maybe( $order->get_billing_address_1(), null ),
					'address2'  => $this->maybe( $order->get_billing_address_2(), null ),
					'city'      => $this->maybe( $order->get_billing_city(), null ),
					'state'     => $this->maybe( $order->get_billing_state(), null ),
					'postcode'  => $this->maybe( $order->get_billing_postcode(), null ),
					'country'   => $this->maybe( $order->get_billing_country(), null ),
				)
			),
			$this->expectedObject( 'order.paymentMethod', $this->maybe( $order->get_payment_method(), 'null' ) ),
			$this->expectedObject( 'order.paymentMethodTitle', $this->maybe( $order->get_payment_method_title(), 'null' ) ),
			$this->expectedObject( 'order.transactionId', $this->maybe( $order->get_transaction_id(), 'null' ) ),
			$this->expectedObject( 'order.dateCompleted', $this->maybe( $order->get_date_completed(), 'null' ) ),
			$this->expectedObject( 'order.datePaid', $this->maybe( $order->get_date_paid(), 'null' ) ),
			$this->expectedObject( 'order.cartHash', $this->maybe( $order->get_cart_hash(), 'null' ) ),
			$this->expectedObject( 'order.shippingAddressMapUrl', $this->maybe( $order->get_shipping_address_map_url(), 'null' ) ),
			$this->expectedObject( 'order.hasBillingAddress', $order->has_billing_address() ),
			$this->expectedObject( 'order.hasShippingAddress', $order->has_shipping_address() ),
			$this->expectedObject( 'order.isDownloadPermitted', $order->is_download_permitted() ),
			$this->expectedObject( 'order.needsShippingAddress', $order->needs_shipping_address() ),
			$this->expectedObject( 'order.hasDownloadableItem', $order->has_downloadable_item() ),
			$this->expectedObject( 'order.needsPayment', $order->needs_payment() ),
			$this->expectedObject( 'order.needsProcessing', $order->needs_processing() ),
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
		 * tests query as customer, should return "null" because the customer isn't authorized.
		 */
		$this->loginAsCustomer();
		$variables = array( 'id' => $id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array( $this->expectedObject( 'order', 'null' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * tests query as shop manager
		 */
		$this->loginAsShopManager();
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->expectedOrderData( $order_id );

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
		 * tests "ID" ID type.
		 */
		$variables = array(
			'id'     => $id,
			'idType' => 'ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array( $this->expectedObject( 'order.id', $id ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * tests "DATABASE_ID" ID type.
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
		 * tests "ORDER_NUMBER" ID type
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
					'status'   => 'completed',
					'customer_id' => $customer,
				),
				array(
					'line_items'    => array(
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
		 * tests query with no without required capabilities
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array( $this->expectedObject( 'orders.nodes', array() ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * tests query with required capabilities
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array(
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $orders[0] ) ) ),
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $orders[1] ) ) ),
			$this->not()->expectedNode( 'orders.nodes', array( 'id' => $this->toRelayId( 'shop_order', $old_orders[0] ) ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * tests "statuses" where argument
		 */
		$variables = array( 'statuses' => array( 'COMPLETED' ) );
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = array(
			$this->expectedNode( 'orders.nodes', array( 'id' => $this->relay_id( 'shop_order', $orders[1] ) ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * tests "customerId" where argument
		 */
		$variables = array( 'customerId' => $customer );
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * tests "customerIn" where argument
		 */
		$variables = array( 'customersIn' => array( $customer ) );
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Six
		 *
		 * tests "productId" where argument
		 */
		$variables = array( 'productId' => $product );
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Seven
		 *
		 * tests `orders` query as existing customer, should return customer's
		 * orders only
		 */
		wp_set_current_user( $customer );
		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, $expected );
	}
}
