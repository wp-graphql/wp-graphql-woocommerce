<?php

use GraphQLRelay\Relay;

class OrderQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $order;
	private $order_helper;
	private $product_helper;
	private $customer_helper;

	public function setUp() {
		parent::setUp();

		$this->shop_manager    = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer        = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->order_helper    = $this->getModule('\Helper\Wpunit')->order();
		$this->product_helper  = $this->getModule('\Helper\Wpunit')->product();
		$this->customer_helper = $this->getModule('\Helper\Wpunit')->customer();
		$this->order           = $this->order_helper->create();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testOrderQuery() {
		$id    = Relay::toGlobalId( 'shop_order', $this->order );

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
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array( 'data' => array( 'order' => null ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		// Clear loader cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_cpt' );

		/**
		 * Assertion Two
		 *
		 * tests query as shop manager
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array( 'data' => array( 'order' => $this->order_helper->print_query( $this->order ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testOrderQueryAndIds() {
		$id    = Relay::toGlobalId( 'shop_order', $this->order );

		$query = '
			query ($id: ID!, $idType: OrderIdTypeEnum ) {
				order(id: $id, idType: $idType) {
					id
				}
			}
		';

		// Must be an "shop_manager" or "admin" to query orders not owned by the user.
		wp_set_current_user( $this->shop_manager );

		/**
		 * Assertion One
		 *
		 * tests "ID" ID type.
		 */
		$variables = array(
			'id'     => $id,
			'idType' => 'ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables
			)
		);
		$expected  = array( 'data' => array( 'order' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * tests "DATABASE_ID" ID type.
		 */
		$variables = array(
			'id'     => $this->order,
			'idType' => 'DATABASE_ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables
			)
		);
		$expected  = array( 'data' => array( 'order' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * tests "ORDER_NUMBER" ID type
		 */
		$variables = array(
			'id'     => $this->order_helper->get_order_key( $this->order ),
			'idType' => 'ORDER_NUMBER',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables
			)
		);
		$expected  = array( 'data' => array( 'order' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testOrdersQueryAndWhereArgs() {
		$query      = new \WC_Order_Query();
		$old_orders = $query->get_orders();
		foreach ( $old_orders as $order ) {
			$this->order_helper->delete_order( $order );
		}
		unset( $old_orders );
		unset( $query );

		$customer = $this->customer_helper->create();
		$product  = $this->product_helper->create_simple();
		$orders   = array(
			$this->order_helper->create(
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
			$this->order_helper->create(
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
		wp_set_current_user( $this->customer );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array( 'data' => array( 'orders' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * tests query with required capabilities
		 */
		wp_set_current_user( $this->shop_manager );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
			'data' => array(
				'orders' => array(
					'nodes' => $this->order_helper->print_nodes( $orders ),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * tests "statuses" where argument
		 */
		$variables = array( 'statuses' => array( 'COMPLETED' ) );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'orders' => array(
					'nodes' => $this->order_helper->print_nodes(
						$orders,
						array(
							'filter' => function( $id ) {
								$order = new WC_Order( $id );
								return $order->get_status() === 'completed';
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 *
		 * tests "customerId" where argument
		 */
		$variables = array( 'customerId' => $customer );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'orders' => array(
					'nodes' => $this->order_helper->print_nodes(
						$orders,
						array(
							'filter' => function( $id ) use ( $customer ) {
								$order = new WC_Order( $id );
								return $order->get_customer_id() === $customer;
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Five
		 *
		 * tests "customerIn" where argument
		 */
		$variables = array( 'customersIn' => array( $customer ) );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'orders' => array(
					'nodes' => $this->order_helper->print_nodes(
						$orders,
						array(
							'filter' => function( $id ) use ( $customer ) {
								$order = new WC_Order( $id );
								return $order->get_customer_id() === $customer;
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Six
		 *
		 * tests "productId" where argument
		 */
		$variables = array( 'productId' => $product );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'orders' => array(
					'nodes' =>  $this->order_helper->print_nodes(
						$orders,
						array(
							'filter' => function( $id ) use ( $product ) {
								return $this->order_helper->has_product( $id, $product );
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Seven
		 *
		 * tests `orders` query as existing customer, should return customer's
		 * orders only
		 */
		wp_set_current_user( $customer );
		$actual    = graphql( compact( 'query' ) );
		$expected  = array(
			'data' => array(
				'orders' => array(
					'nodes' =>  $this->order_helper->print_nodes(
						$orders,
						array(
							'filter' => function( $id ) use ( $customer ) {
								$order = new WC_Order( $id );
								return $order->get_customer_id() === $customer;
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
