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
		$this->order           = $this->order_helper->create();
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
			query refundQuery( $id: ID! ) {
				refund( id: $id ) {
					id
					refundId
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
		$actual    = do_graphql_request( $query, 'refundQuery', $variables );
		$expected  = array(
			'data' => array(
				'refund' => $this->refund_helper->print_failed_query( $this->refund )
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_post_crud' );

		/**
		 * Assertion Two
		 * 
		 * Test query and results for users with required caps
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'refundQuery', $variables );
		$expected  = array(
			'data' => array(
				'refund' => $this->refund_helper->print_query( $this->refund )
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testRefundByQueryAndArgs() {
		$id    = Relay::toGlobalId( 'shop_order_refund', $this->refund );

		$query = '
			query refundByQuery( $id: ID, $refundId: Int ) {
				refundBy( id: $id, refundId: $refundId ) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Test query and "id" argument
		 */
		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'refundByQuery', $variables );
		$expected  = array( 'data' => array( 'refundBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * Test query and "refundId" argument
		 */
		$variables = array( 'refundId' => $this->refund );
		$actual    = do_graphql_request( $query, 'refundByQuery', $variables );
		$expected  = array( 'data' => array( 'refundBy' => array( 'id' => $id ) ) );

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
			query refundsQuery( $statuses: [String], $orderIn: [Int] ) {
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
		$actual    = do_graphql_request( $query, 'refundsQuery' );
		$expected  = array( 'data' => array( 'refunds' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * Test query and results for users with required caps
		 */
		wp_set_current_user( $this->shop_manager );
		$actual    = do_graphql_request( $query, 'refundsQuery' );
		$expected  = array(
			'data' => array(
				'refunds' => array(
					'nodes' => $this->refund_helper->print_nodes( $refunds )
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * Test "statuses" where argument results should be empty
		 * Note: This argument is functionally useless Refunds' "post_status" is always set to "completed".
		 */
		$variables = array( 'statuses' => array( 'completed' ) );
		$actual    = do_graphql_request( $query, 'refundsQuery', $variables );
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

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Four
		 * 
		 * Test "orderIn" where argument
		 */
		$variables = array( 'orderIn' => array( $this->order ) );
		$actual    = do_graphql_request( $query, 'refundsQuery', $variables );
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

		$this->assertEqualSets( $expected, $actual );
	}
}
