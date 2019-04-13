<?php

use GraphQLRelay\Relay;
class RefundQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $admin;
	private $shopManager;
	private $customer;
	private $refund;

	public function setUp() {
		// before
		parent::setUp();

		$this->admin = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		$this->shopManager = $this->factory->user->create(
			array(
				'role' => 'shop_manager',
			)
		);
		$this->customer = $this->factory->user->create(
			array(
				'role' => 'customer',
			)
		);
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testRefundQuery() {
		 $query = '
			query refundQuery( $id: ID! ) {
				refund(id: $id) {
					id
					refundId
					title
					reason
					amount
					refundedBy {
						id
					}
					items {
						nodes {
							id
						}
					}
				}
			}
		';

		$variables = array( 'id' => Relay::toGlobalId( 'shop_order_refund', $refund_id ) );
		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testRefundsQuery() {
		$query = '
			query {
				refunds() {
					nodes {
						refundId
						dateCreated
						amount
						reason
						refundedBy {
							id
						}
						refundPayment
						lineItems {
							nodes {
								id
							}
						}
					}
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}
}
