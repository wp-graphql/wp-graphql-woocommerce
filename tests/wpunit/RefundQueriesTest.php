<?php

use GraphQLRelay\Relay;
class RefundQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $order_helper;
	private $refund_helper;
	private $order;
	private $refund;

	public function setUp() {
		parent::setUp();

		$this->shop_manager    = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer        = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->order_helper    = $this->getModule('\Helper\Wpunit')->order();
		$this->refund_helper   = $this->getModule('\Helper\Wpunit')->refund();
		$this->customer_helper = $this->getModule('\Helper\Wpunit')->refund();
		$this->order           = $this->order_helper->create(
			array( 'customer_id' => $this->customer )
		);
		$this->refund          = $this->refund_helper->create( $this->order );
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testRefundQuery() {
		$id    = Relay::toGlobalId( 'shop_order_refund', $this->refund );

		$query = '
			query ( $id: ID! ) {
				refund( id: $id ) {
					id
					databaseId
					title
					reason
					amount
					refundedBy {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Test query and failed results for users lacking required caps
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => $id );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'refund' => $this->refund_helper->print_failed_query( $this->refund )
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_cpt' );

		/**
		 * Assertion Two
		 *
		 * Test query and results for users with required caps
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => $id );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'refund' => $this->refund_helper->print_query( $this->refund )
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testRefundQueryAndIds() {
		$id    = Relay::toGlobalId( 'shop_order_refund', $this->refund );

		$query = '
			query ( $id: ID!, $idType: RefundIdTypeEnum ) {
				refund( id: $id, idType: $idType ) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Test query and "id" argument
		 */
		wp_set_current_user( $this->customer );
		$variables = array(
			'id'     => $id,
			'idType' => 'ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array( 'data' => array( 'refund' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Test query and "refundId" argument
		 */
		$variables = array(
			'id'     => $this->refund,
			'idType' => 'DATABASE_ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array( 'data' => array( 'refund' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testRefundsQueryAndWhereArgs() {
		$refunds = array(
			$this->refund,
			$this->refund_helper->create( $this->order_helper->create() ),
			$this->refund_helper->create( $this->order_helper->create(), array( 'status' => 'pending' ) ),
			$this->refund_helper->create( $this->order_helper->create() ),
		);

		$query = '
			query ( $statuses: [String], $orderIn: [Int] ) {
				refunds( where: {
					statuses: $statuses,
					orderIn: $orderIn,
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
		 * Test query and failed results for users lacking required caps
		 */
		wp_set_current_user( $this->customer );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array( 'data' => array( 'refunds' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Test query and results for users with required caps
		 */
		wp_set_current_user( $this->shop_manager );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
			'data' => array(
				'refunds' => array(
					'nodes' => $this->refund_helper->print_nodes( $refunds )
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Test "statuses" where argument results should be empty
		 * Note: This argument is functionally useless Refunds' "post_status" is always set to "completed".
		 */
		$variables = array( 'statuses' => array( 'completed' ) );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'refunds' => array(
					'nodes' => $this->refund_helper->print_nodes(
						$refunds,
						array(
							'filter' => function( $id ) {
								$refund = new WC_Order_Refund( $id );
								return 'completed' === $refund->get_status();
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
		 * Test "orderIn" where argument
		 */
		$variables = array( 'orderIn' => array( $this->order ) );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'refunds' => array(
					'nodes' => $this->refund_helper->print_nodes(
						$refunds,
						array(
							'filter' => function( $id ) {
								$refund = new WC_Order_Refund( $id );
								return $refund->get_parent_id() === $this->order;
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

	public function testOrderToRefundsConnection() {
		$order   = $this->order_helper->create();
		$refunds = array(
			$this->refund_helper->create( $order, array( 'amount' => 0.5 ) ),
			$this->refund_helper->create( $order, array( 'status' => 'pending', 'amount' => 0.5 ) ),
		);

		$query   = '
			query ( $id: ID! ) {
				order(id: $id) {
					refunds {
						nodes {
							id
						}
					}
				}
			}
		';

		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => $this->order_helper->to_relay_id( $order ) );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'order' => array(
					'refunds' => array(
						'nodes' => $this->refund_helper->print_nodes(
							$refunds,
							array(
								'filter' => function( $id ) use( $order ) {
									$refund = new WC_Order_Refund( $id );
									return $refund->get_parent_id() === $order;
								},
							)
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCustomerToRefundsConnection() {
		$order   = $this->order_helper->create( array( 'customer_id' => $this->customer ) );
		$refunds = array(
			$this->refund,
			$this->refund_helper->create( $order, array( 'amount' => 0.5 ) ),
			$this->refund_helper->create( $order, array( 'status' => 'pending', 'amount' => 0.5 ) ),
		);

		$query   = '
			query {
				customer {
					refunds {
						nodes {
							id
						}
					}
				}
			}
		';

		wp_set_current_user( $this->customer );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
			'data' => array(
				'customer' => array(
					'refunds' => array(
						'nodes' => $this->refund_helper->print_nodes( $refunds ),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
