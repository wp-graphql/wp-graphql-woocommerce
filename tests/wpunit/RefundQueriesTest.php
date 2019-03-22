<?php

class RefundQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testRefundQuery() {
		 $query = '
			query {
				refund(id: " ") {
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
		';

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
