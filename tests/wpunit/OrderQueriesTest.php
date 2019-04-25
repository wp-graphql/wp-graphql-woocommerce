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
		$id = Relay::toGlobalId( 'shop_order', $this->order );

		$query = '
			query orderQuery( $id: ID! ) {
				order( id: $id ) {
					id
					orderId
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
						downloadId
					}
					needsPayment
					needsProcessing
				}
			}
		';
		
		/**
		 * Assertion One
		 * 
		 * tests query as customer
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'orderQuery', $variables );
		$expected = array( 'data' => array( 'order' => $this->order_helper->print_restricted_query( $this->order ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		// Clear loader cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_post_crud' );

		/**
		 * Assertion Two
		 * 
		 * tests query as shop manager
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'orderQuery', $variables );
		$expected = array( 'data' => array( 'order' => $this->order_helper->print_query( $this->order ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testOrderByQueryAndArgs() {
		$id = Relay::toGlobalId( 'shop_order', $this->order );

		$query = '
			query orderByQuery( $id: ID, $orderId: Int, $orderKey: String ) {
				orderBy( id: $id, orderId: $orderId, orderKey: $orderKey ) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * tests query and "id" arg
		 */
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'orderByQuery', $variables );
		$expected = array( 'data' => array( 'orderBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * tests query and "orderId" arg
		 */
		$variables = array( 'orderId' => $this->order );
		$actual = do_graphql_request( $query, 'orderByQuery', $variables );
		$expected = array( 'data' => array( 'orderBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * tests query and "orderNumber" arg
		 */
		$variables = array( 'orderKey' => $this->order_helper->get_order_key( $this->order ) );
		$actual = do_graphql_request( $query, 'orderByQuery', $variables );
		$expected = array( 'data' => array( 'orderBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testOrdersQueryAndWhereArgs() {
		$customer = $this->customer_helper->create();
		$product  = $this->product_helper->create_simple();
		$orders   = array(
			$this->order,
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
			query ordersQuery( $statuses: [OrderStatusEnum], $customerId: Int, $customersIn: [Int] $productId: Int ) {
				orders( where: {
					statuses: $statuses,
					customerId: $customerId,
					customersIn: $customersIn,
					productId: $productId,
					orderby: { field: MENU_ORDER, order: ASC }
				} ) {
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
		$actual = do_graphql_request( $query, 'ordersQuery' );
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
		$actual = do_graphql_request( $query, 'ordersQuery' );
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
		$actual    = do_graphql_request( $query, 'ordersQuery', $variables );
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
		$actual    = do_graphql_request( $query, 'ordersQuery', $variables );
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
		$actual    = do_graphql_request( $query, 'ordersQuery', $variables );
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
		$actual    = do_graphql_request( $query, 'ordersQuery', $variables );
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
	}
}
